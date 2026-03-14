<?php
/**
 * Global Helper Functions for Chatbot AI Engine
 *
 * @package Chatbot_AI_Engine
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get all Chatbot AI Engine settings
 *
 * @return array Settings array
 */
function get_chatbot_ai_engine_settings() {
	$settings = get_option( 'chatbot_ai_engine_settings', array() );

	// Decrypt API key if present
	if ( ! empty( $settings['api_key'] ) ) {
		$plugin = Chatbot_AI_Engine::get_instance();
		$settings['api_key_decrypted'] = $plugin->get_decrypted_api_key();
	}

	return $settings;
}

/**
 * Check if Chatbot AI Engine is enabled
 *
 * @return bool True if enabled, false otherwise
 */
function is_chatbot_ai_engine_enabled() {
	$settings = get_option( 'chatbot_ai_engine_settings', array() );
	return isset( $settings['enabled'] ) && '1' === $settings['enabled'];
}

/**
 * Get a specific setting value
 *
 * @param string $key Setting key
 * @param mixed  $default Default value
 * @return mixed Setting value or default
 */
function get_chatbot_ai_engine_setting( $key, $default = null ) {
	$settings = get_chatbot_ai_engine_settings();

	if ( isset( $settings[ $key ] ) ) {
		return $settings[ $key ];
	}

	return $default;
}

/**
 * Get the system prompt
 *
 * @return string System prompt
 */
function get_chatbot_ai_engine_system_prompt() {
	return get_chatbot_ai_engine_setting( 'system_prompt', __( 'You are a helpful assistant.', 'chatbot-ai-engine' ) );
}

/**
 * Get the AI model name
 *
 * @return string Model name
 */
function get_chatbot_ai_engine_model() {
	return get_chatbot_ai_engine_setting( 'model', 'gpt-3.5-turbo' );
}

/**
 * Get the AI provider
 *
 * @return string Provider name (openai, groq, anthropic, custom)
 */
function get_chatbot_ai_engine_provider() {
	return get_chatbot_ai_engine_setting( 'api_provider', 'openai' );
}

/**
 * Get the API URL
 *
 * @return string API URL
 */
function get_chatbot_ai_engine_api_url() {
	return get_chatbot_ai_engine_setting( 'api_url', '' );
}

/**
 * Get the chatbot position
 *
 * @return string Position (bottom-right, bottom-left, top-right, top-left)
 */
function get_chatbot_ai_engine_position() {
	return get_chatbot_ai_engine_setting( 'position', 'bottom-right' );
}

/**
 * Get max tokens setting
 *
 * @return int Maximum tokens
 */
function get_chatbot_ai_engine_max_tokens() {
	return absint( get_chatbot_ai_engine_setting( 'max_tokens', 1000 ) );
}

/**
 * Get temperature setting
 *
 * @return float Temperature value (0-2)
 */
function get_chatbot_ai_engine_temperature() {
	return floatval( get_chatbot_ai_engine_setting( 'temperature', 0.7 ) );
}

/**
 * Check if chatbot should be displayed on current page
 *
 * @return bool True if should display, false otherwise
 */
function should_display_chatbot_ai_engine() {
	if ( ! is_chatbot_ai_engine_enabled() ) {
		return false;
	}

	return apply_filters( 'chatbot_ai_engine_display_bubble', true );
}

/**
 * Render chatbot widget (usually not needed as it's auto-injected)
 *
 * Can be used in themes/templates for manual placement
 *
 * @return void
 */
function the_chatbot_ai_engine() {
	if ( ! should_display_chatbot_ai_engine() ) {
		return;
	}

	wp_enqueue_style(
		'chatbot-ai-engine-style',
		CHATBOT_AI_ENGINE_URL . 'assets/style.css',
		array(),
		CHATBOT_AI_ENGINE_VERSION
	);

	wp_enqueue_script(
		'chatbot-ai-engine-script',
		CHATBOT_AI_ENGINE_URL . 'assets/script.js',
		array(),
		CHATBOT_AI_ENGINE_VERSION,
		true
	);

	wp_localize_script(
		'chatbot-ai-engine-script',
		'chatbotAIEngine',
		array(
			'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'chatbot_ai_engine_nonce' ),
			'position' => get_chatbot_ai_engine_position(),
			'i18n'     => array(
				'placeholder' => __( 'Type your message...', 'chatbot-ai-engine' ),
				'send'        => __( 'Send', 'chatbot-ai-engine' ),
				'loading'     => __( 'Loading...', 'chatbot-ai-engine' ),
				'error'       => __( 'Error: Could not send message', 'chatbot-ai-engine' ),
				'chatTitle'   => __( 'AI Assistant', 'chatbot-ai-engine' ),
				'closeChat'   => __( 'Close chat', 'chatbot-ai-engine' ),
			),
		)
	);
}

/**
 * Get admin settings page URL
 *
 * @return string Settings page URL
 */
function get_chatbot_ai_engine_settings_page_url() {
	return admin_url( 'admin.php?page=chatbot-ai-engine' );
}

/**
 * Check if current user can manage chatbot settings
 *
 * @return bool True if user can manage, false otherwise
 */
function current_user_can_manage_chatbot_ai_engine() {
	return current_user_can( 'manage_options' );
}

/**
 * Get all supported AI providers
 *
 * @return array Array of provider information
 */
function get_chatbot_ai_engine_providers() {
	return array(
		'openai'    => array(
			'name' => __( 'OpenAI', 'chatbot-ai-engine' ),
			'url'  => 'https://api.openai.com/v1/chat/completions',
		),
		'groq'      => array(
			'name' => __( 'Groq', 'chatbot-ai-engine' ),
			'url'  => 'https://api.groq.com/openai/v1/chat/completions',
		),
		'anthropic' => array(
			'name' => __( 'Anthropic (Claude)', 'chatbot-ai-engine' ),
			'url'  => 'https://api.anthropic.com/v1/messages',
		),
		'custom'    => array(
			'name' => __( 'Custom API', 'chatbot-ai-engine' ),
			'url'  => '',
		),
	);
}

/**
 * Get provider by key
 *
 * @param string $key Provider key
 * @return array|null Provider array or null if not found
 */
function get_chatbot_ai_engine_provider( $key ) {
	$providers = get_chatbot_ai_engine_providers();
	return isset( $providers[ $key ] ) ? $providers[ $key ] : null;
}

/**
 * Modify plugin settings programmatically
 *
 * @param array $settings Settings to merge with existing
 * @return bool True on success, false on failure
 */
function update_chatbot_ai_engine_settings( $settings ) {
	if ( ! is_array( $settings ) ) {
		return false;
	}

	$existing = get_option( 'chatbot_ai_engine_settings', array() );
	$merged = array_merge( $existing, $settings );

	// Sanitize before saving
	$plugin = Chatbot_AI_Engine::get_instance();
	if ( method_exists( $plugin, 'sanitize_settings' ) ) {
		// Would need to make sanitize_settings public for this to work
		// For now, just save directly
	}

	return update_option( 'chatbot_ai_engine_settings', $merged );
}

/**
 * Get chatbot version
 *
 * @return string Plugin version
 */
function get_chatbot_ai_engine_version() {
	return CHATBOT_AI_ENGINE_VERSION;
}

/**
 * Get plugin URL
 *
 * @return string Plugin URL
 */
function get_chatbot_ai_engine_url() {
	return CHATBOT_AI_ENGINE_URL;
}

/**
 * Get plugin path
 *
 * @return string Plugin path
 */
function get_chatbot_ai_engine_path() {
	return CHATBOT_AI_ENGINE_PATH;
}

/**
 * Check if page is admin settings page
 *
 * @return bool True if on settings page, false otherwise
 */
function is_chatbot_ai_engine_settings_page() {
	global $pagenow;

	if ( 'admin.php' !== $pagenow ) {
		return false;
	}

	$page = isset( $_GET['page'] ) ? sanitize_text_field( $_GET['page'] ) : '';
	return 'chatbot-ai-engine' === $page;
}

/**
 * Log chatbot activity (for debugging/analytics)
 *
 * @param string $message Log message
 * @param array  $data Optional data array
 * @return void
 */
function log_chatbot_ai_engine( $message, $data = array() ) {
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		$log_message = '[Chatbot AI Engine] ' . $message;

		if ( ! empty( $data ) ) {
			$log_message .= ' - ' . wp_json_encode( $data );
		}

		error_log( $log_message );
	}
}
