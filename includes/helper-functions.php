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

if ( ! function_exists( 'log_chatbot_ai_engine' ) ) {
	/**
	 * Log chatbot activity
	 */
	function log_chatbot_ai_engine( $message, $data = array(), $level = 'info' ) {
		if ( 'error' === $level || ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ) {
			$log_entry = sprintf( '[Chatbot AI Engine][%s] %s', strtoupper( $level ), $message );
			if ( ! empty( $data ) ) {
				$log_entry .= ' | Data: ' . ( is_scalar( $data ) ? $data : wp_json_encode( $data ) );
			}
			error_log( $log_entry );
		}
	}
}

if ( ! function_exists( 'get_chatbot_ai_engine_settings' ) ) {
	/**
	 * Get all settings
	 */
	function get_chatbot_ai_engine_settings() {
		$settings = get_option( 'chatbot_ai_engine_settings', array() );
		if ( ! empty( $settings['api_key'] ) && class_exists( 'Chatbot_AI_Engine' ) ) {
			$plugin = Chatbot_AI_Engine::get_instance();
			$settings['api_key_decrypted'] = $plugin->get_decrypted_api_key();
		} else {
			$settings['api_key_decrypted'] = '';
		}
		return $settings;
	}
}

if ( ! function_exists( 'is_chatbot_ai_engine_enabled' ) ) {
	/**
	 * Check if enabled
	 */
	function is_chatbot_ai_engine_enabled() {
		$settings = get_option( 'chatbot_ai_engine_settings', array() );
		return isset( $settings['enabled'] ) && '1' === $settings['enabled'];
	}
}

if ( ! function_exists( 'get_chatbot_ai_engine_setting' ) ) {
	/**
	 * Get specific setting
	 */
	function get_chatbot_ai_engine_setting( $key, $default = null ) {
		$settings = get_chatbot_ai_engine_settings();
		return isset( $settings[ $key ] ) ? $settings[ $key ] : $default;
	}
}

if ( ! function_exists( 'get_chatbot_ai_engine_system_prompt' ) ) {
	/**
	 * Get system prompt
	 */
	function get_chatbot_ai_engine_system_prompt() {
		return get_chatbot_ai_engine_setting( 'system_prompt', __( 'You are a helpful assistant.', 'chatbot-ai-engine' ) );
	}
}

if ( ! function_exists( 'get_chatbot_ai_engine_model' ) ) {
	/**
	 * Get model name
	 */
	function get_chatbot_ai_engine_model() {
		return get_chatbot_ai_engine_setting( 'model', 'gpt-3.5-turbo' );
	}
}

if ( ! function_exists( 'get_chatbot_ai_engine_provider' ) ) {
	/**
	 * Get active provider
	 */
	function get_chatbot_ai_engine_provider() {
		return get_chatbot_ai_engine_setting( 'api_provider', 'openai' );
	}
}

if ( ! function_exists( 'get_chatbot_ai_engine_api_url' ) ) {
	/**
	 * Get API URL
	 */
	function get_chatbot_ai_engine_api_url() {
		return get_chatbot_ai_engine_setting( 'api_url', '' );
	}
}

if ( ! function_exists( 'get_chatbot_ai_engine_position' ) ) {
	/**
	 * Get position
	 */
	function get_chatbot_ai_engine_position() {
		return get_chatbot_ai_engine_setting( 'position', 'bottom-right' );
	}
}

if ( ! function_exists( 'get_chatbot_ai_engine_max_tokens' ) ) {
	/**
	 * Get max tokens
	 */
	function get_chatbot_ai_engine_max_tokens() {
		return absint( get_chatbot_ai_engine_setting( 'max_tokens', 1000 ) );
	}
}

if ( ! function_exists( 'get_chatbot_ai_engine_temperature' ) ) {
	/**
	 * Get temperature
	 */
	function get_chatbot_ai_engine_temperature() {
		return floatval( get_chatbot_ai_engine_setting( 'temperature', 0.7 ) );
	}
}

if ( ! function_exists( 'should_display_chatbot_ai_engine' ) ) {
	/**
	 * Check if should display
	 */
	function should_display_chatbot_ai_engine() {
		if ( ! is_chatbot_ai_engine_enabled() ) {
			return false;
		}
		return apply_filters( 'chatbot_ai_engine_display_bubble', true );
	}
}

if ( ! function_exists( 'the_chatbot_ai_engine' ) ) {
	/**
	 * Render widget
	 */
	function the_chatbot_ai_engine() {
		if ( ! should_display_chatbot_ai_engine() ) {
			return;
		}
		$url = defined( 'CHATBOT_AI_ENGINE_URL' ) ? CHATBOT_AI_ENGINE_URL : '';
		$version = defined( 'CHATBOT_AI_ENGINE_VERSION' ) ? CHATBOT_AI_ENGINE_VERSION : '1.0.0';
		if ( ! empty( $url ) ) {
			wp_enqueue_style( 'chatbot-ai-engine-style', $url . 'assets/style.css', array(), $version );
			wp_enqueue_script( 'chatbot-ai-engine-script', $url . 'assets/script.js', array(), $version, true );
			wp_localize_script( 'chatbot-ai-engine-script', 'chatbotAIEngine', array(
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
			) );
		}
	}
}

if ( ! function_exists( 'get_chatbot_ai_engine_settings_page_url' ) ) {
	/**
	 * Get settings URL
	 */
	function get_chatbot_ai_engine_settings_page_url() {
		return admin_url( 'admin.php?page=chatbot-ai-engine' );
	}
}

if ( ! function_exists( 'current_user_can_manage_chatbot_ai_engine' ) ) {
	/**
	 * Check user caps
	 */
	function current_user_can_manage_chatbot_ai_engine() {
		return current_user_can( 'manage_options' );
	}
}

if ( ! function_exists( 'get_chatbot_ai_engine_providers' ) ) {
	/**
	 * Get providers
	 */
	function get_chatbot_ai_engine_providers() {
		return array(
			'openai'    => array( 'name' => __( 'OpenAI', 'chatbot-ai-engine' ), 'url' => 'https://api.openai.com/v1/chat/completions' ),
			'groq'      => array( 'name' => __( 'Groq', 'chatbot-ai-engine' ), 'url' => 'https://api.groq.com/openai/v1/chat/completions' ),
			'anthropic' => array( 'name' => __( 'Anthropic (Claude)', 'chatbot-ai-engine' ), 'url' => 'https://api.anthropic.com/v1/messages' ),
			'gemini'    => array( 'name' => __( 'Google Gemini', 'chatbot-ai-engine' ), 'url' => 'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent' ),
			'custom'    => array( 'name' => __( 'Custom API', 'chatbot-ai-engine' ), 'url' => '' ),

		);
	}
}

if ( ! function_exists( 'get_chatbot_ai_engine_provider_by_key' ) ) {
	/**
	 * Get provider by key
	 */
	function get_chatbot_ai_engine_provider_by_key( $key ) {
		$providers = get_chatbot_ai_engine_providers();
		return isset( $providers[ $key ] ) ? $providers[ $key ] : null;
	}
}

if ( ! function_exists( 'update_chatbot_ai_engine_settings' ) ) {
	/**
	 * Update settings
	 */
	function update_chatbot_ai_engine_settings( $settings ) {
		if ( ! is_array( $settings ) ) return false;
		$existing = get_option( 'chatbot_ai_engine_settings', array() );
		$merged = array_merge( $existing, $settings );
		if ( class_exists( 'Chatbot_AI_Engine' ) ) {
			$plugin = Chatbot_AI_Engine::get_instance();
			$sanitized = $plugin->sanitize_settings( $merged );
			return update_option( 'chatbot_ai_engine_settings', $sanitized );
		}
		return update_option( 'chatbot_ai_engine_settings', $merged );
	}
}

if ( ! function_exists( 'get_chatbot_ai_engine_version' ) ) {
	/**
	 * Get version
	 */
	function get_chatbot_ai_engine_version() {
		return defined( 'CHATBOT_AI_ENGINE_VERSION' ) ? CHATBOT_AI_ENGINE_VERSION : '1.0.0';
	}
}

if ( ! function_exists( 'get_chatbot_ai_engine_url' ) ) {
	/**
	 * Get plugin URL
	 */
	function get_chatbot_ai_engine_url() {
		return defined( 'CHATBOT_AI_ENGINE_URL' ) ? CHATBOT_AI_ENGINE_URL : '';
	}
}

if ( ! function_exists( 'get_chatbot_ai_engine_path' ) ) {
	/**
	 * Get plugin path
	 */
	function get_chatbot_ai_engine_path() {
		return defined( 'CHATBOT_AI_ENGINE_PATH' ) ? CHATBOT_AI_ENGINE_PATH : '';
	}
}

if ( ! function_exists( 'is_chatbot_ai_engine_settings_page' ) ) {
	/**
	 * Check if settings page
	 */
	function is_chatbot_ai_engine_settings_page() {
		global $pagenow;
		if ( 'admin.php' !== $pagenow ) return false;
		$page = isset( $_GET['page'] ) ? sanitize_text_field( $_GET['page'] ) : '';
		return 'chatbot-ai-engine' === $page;
	}
}
