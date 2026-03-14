<?php
/**
 * Plugin Name: Chatbot AI Engine
 * Plugin URI: https://example.com/chatbot-ai-engine
 * Description: Abstract AI chatbot plugin with BYOK (Bring Your Own Key) support for multiple AI providers
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://example.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: chatbot-ai-engine
 * Domain Path: /languages
 * Requires at least: 5.0
 * Requires PHP: 7.4
 *
 * @package Chatbot_AI_Engine
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants
define( 'CHATBOT_AI_ENGINE_VERSION', '1.0.0' );
define( 'CHATBOT_AI_ENGINE_PATH', plugin_dir_path( __FILE__ ) );
define( 'CHATBOT_AI_ENGINE_URL', plugin_dir_url( __FILE__ ) );
define( 'CHATBOT_AI_ENGINE_BASENAME', plugin_basename( __FILE__ ) );

// Encryption salt for API key protection
if ( ! defined( 'CHATBOT_AI_ENGINE_SALT' ) ) {
	define( 'CHATBOT_AI_ENGINE_SALT', wp_salt( 'auth' ) );
}

/**
 * Main plugin class
 */
class Chatbot_AI_Engine {

	/**
	 * Plugin instance
	 *
	 * @var Chatbot_AI_Engine
	 */
	private static $instance = null;

	/**
	 * Get plugin instance
	 *
	 * @return Chatbot_AI_Engine
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->init_hooks();
		$this->register_global_functions();
	}

	/**
	 * Register global helper functions
	 */
	private function register_global_functions() {
		if ( ! function_exists( 'get_chatbot_ai_engine_settings' ) ) {
			include_once CHATBOT_AI_ENGINE_PATH . 'includes/helper-functions.php';
		}
	}

	/**
	 * Initialize hooks
	 */
	private function init_hooks() {
		// Plugin activation
		register_activation_hook( __FILE__, array( $this, 'activate' ) );

		// Plugin deactivation
		register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

		// Load plugin text domain
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );

		// Register admin menu
		add_action( 'admin_menu', array( $this, 'register_admin_menu' ) );

		// Register settings
		add_action( 'admin_init', array( $this, 'register_settings' ) );

		// Enqueue frontend scripts and styles
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );

		// AJAX handlers
		add_action( 'wp_ajax_chatbot_send_message', array( $this, 'handle_ajax_message' ) );
		add_action( 'wp_ajax_nopriv_chatbot_send_message', array( $this, 'handle_ajax_message' ) );
	}

	/**
	 * Plugin activation
	 */
	public function activate() {
		// Initialize default settings if they don't exist
		if ( ! get_option( 'chatbot_ai_engine_settings' ) ) {
			$default_settings = array(
				'enabled'      => '0',
				'api_key'      => '',
				'api_provider' => 'openai',
				'api_url'      => 'https://api.openai.com/v1/chat/completions',
				'model'        => 'gpt-3.5-turbo',
				'system_prompt' => __( 'You are a helpful assistant.', 'chatbot-ai-engine' ),
				'position'     => 'bottom-right',
			);
			update_option( 'chatbot_ai_engine_settings', $default_settings );
		}

		// Flush rewrite rules
		flush_rewrite_rules();

		// Trigger activation action
		do_action( 'chatbot_ai_engine_activated' );
	}

	/**
	 * Plugin deactivation
	 */
	public function deactivate() {
		// Clean up if needed
		flush_rewrite_rules();
	}

	/**
	 * Load plugin text domain
	 */
	public function load_textdomain() {
		load_plugin_textdomain(
			'chatbot-ai-engine',
			false,
			dirname( CHATBOT_AI_ENGINE_BASENAME ) . '/languages'
		);
	}

	/**
	 * Register admin menu
	 */
	public function register_admin_menu() {
		add_menu_page(
			__( 'Chatbot AI Engine', 'chatbot-ai-engine' ),
			__( 'AI Chatbot', 'chatbot-ai-engine' ),
			'manage_options',
			'chatbot-ai-engine',
			array( $this, 'render_settings_page' ),
			'dashicons-format-status',
			90
		);
	}

	/**
	 * Register settings
	 */
	public function register_settings() {
		register_setting(
			'chatbot_ai_engine_settings_group',
			'chatbot_ai_engine_settings',
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize_settings' ),
				'show_in_rest'      => false,
			)
		);

		add_settings_section(
			'chatbot_ai_engine_main',
			__( 'AI Chatbot Configuration', 'chatbot-ai-engine' ),
			array( $this, 'render_settings_section' ),
			'chatbot_ai_engine_settings_group'
		);
	}

	/**
	 * Sanitize settings
	 *
	 * @param array $settings Settings array
	 * @return array Sanitized settings
	 */
	public function sanitize_settings( $settings ) {
		if ( ! is_array( $settings ) ) {
			return array();
		}

		$sanitized = array();

		// Sanitize enabled
		$sanitized['enabled'] = isset( $settings['enabled'] ) ? '1' : '0';

		// Sanitize API key - encrypt for security
		$api_key = isset( $settings['api_key'] ) ? sanitize_text_field( $settings['api_key'] ) : '';
		if ( ! empty( $api_key ) ) {
			// Don't re-encrypt if it's the placeholder (indicates existing key)
			if ( '••••••••••••••••' !== $api_key ) {
				$sanitized['api_key'] = $this->encrypt_api_key( $api_key );
			} else {
				$existing = get_option( 'chatbot_ai_engine_settings', array() );
				$sanitized['api_key'] = isset( $existing['api_key'] ) ? $existing['api_key'] : '';
			}
		} else {
			$sanitized['api_key'] = '';
		}

		// Sanitize API provider
		$allowed_providers = array( 'openai', 'groq', 'anthropic', 'custom' );
		$sanitized['api_provider'] = isset( $settings['api_provider'] ) && in_array( $settings['api_provider'], $allowed_providers, true ) ? $settings['api_provider'] : 'openai';

		// Sanitize API URL
		$sanitized['api_url'] = isset( $settings['api_url'] ) ? esc_url_raw( $settings['api_url'] ) : '';

		// Sanitize model name
		$sanitized['model'] = isset( $settings['model'] ) ? sanitize_text_field( $settings['model'] ) : 'gpt-3.5-turbo';

		// Sanitize system prompt
		$sanitized['system_prompt'] = isset( $settings['system_prompt'] ) ? sanitize_textarea_field( $settings['system_prompt'] ) : __( 'You are a helpful assistant.', 'chatbot-ai-engine' );

		// Sanitize position
		$allowed_positions = array( 'bottom-right', 'bottom-left', 'top-right', 'top-left' );
		$sanitized['position'] = isset( $settings['position'] ) && in_array( $settings['position'], $allowed_positions, true ) ? $settings['position'] : 'bottom-right';

		// Sanitize max tokens
		$sanitized['max_tokens'] = isset( $settings['max_tokens'] ) ? absint( $settings['max_tokens'] ) : 1000;

		// Sanitize temperature
		$temperature = isset( $settings['temperature'] ) ? floatval( $settings['temperature'] ) : 0.7;
		$sanitized['temperature'] = min( 2.0, max( 0.0, $temperature ) );

		// Apply settings filter
		$sanitized = apply_filters( 'chatbot_ai_engine_settings', $sanitized );

		// Trigger settings updated action
		do_action( 'chatbot_ai_engine_settings_updated', $sanitized );

		return $sanitized;
	}

	/**
	 * Render settings section
	 */
	public function render_settings_section() {
		echo '<p>' . esc_html__( 'Configure your AI chatbot settings below. You can use your own API key from any supported provider.', 'chatbot-ai-engine' ) . '</p>';
	}

	/**
	 * Render settings page
	 */
	public function render_settings_page() {
		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'chatbot-ai-engine' ) );
		}

		$settings = get_option( 'chatbot_ai_engine_settings', array() );
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

			<form method="post" action="options.php">
				<?php settings_fields( 'chatbot_ai_engine_settings_group' ); ?>

				<table class="form-table">
					<tr>
						<th scope="row">
							<label for="chatbot_enabled"><?php esc_html_e( 'Enable Chatbot', 'chatbot-ai-engine' ); ?></label>
						</th>
						<td>
							<input type="checkbox" id="chatbot_enabled" name="chatbot_ai_engine_settings[enabled]" value="1" <?php checked( isset( $settings['enabled'] ) ? $settings['enabled'] : 0, 1 ); ?> />
							<p class="description"><?php esc_html_e( 'Check this to enable the chatbot on your website', 'chatbot-ai-engine' ); ?></p>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="chatbot_api_provider"><?php esc_html_e( 'AI Provider', 'chatbot-ai-engine' ); ?></label>
						</th>
						<td>
							<select id="chatbot_api_provider" name="chatbot_ai_engine_settings[api_provider]" onchange="updateApiUrl(this.value)">
								<option value="openai" <?php selected( $settings['api_provider'] ?? '', 'openai' ); ?>><?php esc_html_e( 'OpenAI', 'chatbot-ai-engine' ); ?></option>
								<option value="groq" <?php selected( $settings['api_provider'] ?? '', 'groq' ); ?>><?php esc_html_e( 'Groq', 'chatbot-ai-engine' ); ?></option>
								<option value="anthropic" <?php selected( $settings['api_provider'] ?? '', 'anthropic' ); ?>><?php esc_html_e( 'Anthropic', 'chatbot-ai-engine' ); ?></option>
								<option value="custom" <?php selected( $settings['api_provider'] ?? '', 'custom' ); ?>><?php esc_html_e( 'Custom API', 'chatbot-ai-engine' ); ?></option>
							</select>
							<p class="description"><?php esc_html_e( 'Select your AI provider', 'chatbot-ai-engine' ); ?></p>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="chatbot_api_url"><?php esc_html_e( 'API URL', 'chatbot-ai-engine' ); ?></label>
						</th>
						<td>
							<input type="url" id="chatbot_api_url" name="chatbot_ai_engine_settings[api_url]" value="<?php echo esc_url( $settings['api_url'] ?? '' ); ?>" class="regular-text" />
							<p class="description"><?php esc_html_e( 'Your AI provider API endpoint', 'chatbot-ai-engine' ); ?></p>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="chatbot_api_key"><?php esc_html_e( 'API Key', 'chatbot-ai-engine' ); ?></label>
						</th>
						<td>
							<input type="password" id="chatbot_api_key" name="chatbot_ai_engine_settings[api_key]" value="<?php echo ! empty( $settings['api_key'] ) ? '••••••••••••••••' : ''; ?>" class="regular-text" placeholder="<?php esc_attr_e( 'Enter your API key or leave blank to keep existing', 'chatbot-ai-engine' ); ?>" />
							<p class="description"><?php esc_html_e( 'Your API key is encrypted and stored securely. Leave blank to keep the existing key.', 'chatbot-ai-engine' ); ?></p>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="chatbot_model"><?php esc_html_e( 'Model Name', 'chatbot-ai-engine' ); ?></label>
						</th>
						<td>
							<input type="text" id="chatbot_model" name="chatbot_ai_engine_settings[model]" value="<?php echo esc_attr( $settings['model'] ?? 'gpt-3.5-turbo' ); ?>" class="regular-text" placeholder="e.g., gpt-3.5-turbo" />
							<p class="description"><?php esc_html_e( 'The model ID from your AI provider', 'chatbot-ai-engine' ); ?></p>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="chatbot_system_prompt"><?php esc_html_e( 'System Prompt', 'chatbot-ai-engine' ); ?></label>
						</th>
						<td>
							<textarea id="chatbot_system_prompt" name="chatbot_ai_engine_settings[system_prompt]" class="large-text" rows="5"><?php echo esc_textarea( $settings['system_prompt'] ?? __( 'You are a helpful assistant.', 'chatbot-ai-engine' ) ); ?></textarea>
							<p class="description"><?php esc_html_e( 'System instructions for the AI model', 'chatbot-ai-engine' ); ?></p>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="chatbot_max_tokens"><?php esc_html_e( 'Max Tokens', 'chatbot-ai-engine' ); ?></label>
						</th>
						<td>
							<input type="number" id="chatbot_max_tokens" name="chatbot_ai_engine_settings[max_tokens]" value="<?php echo absint( $settings['max_tokens'] ?? 1000 ); ?>" min="1" max="4000" />
							<p class="description"><?php esc_html_e( 'Maximum tokens for API response', 'chatbot-ai-engine' ); ?></p>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="chatbot_temperature"><?php esc_html_e( 'Temperature', 'chatbot-ai-engine' ); ?></label>
						</th>
						<td>
							<input type="number" id="chatbot_temperature" name="chatbot_ai_engine_settings[temperature]" value="<?php echo esc_attr( $settings['temperature'] ?? 0.7 ); ?>" min="0" max="2" step="0.1" />
							<p class="description"><?php esc_html_e( 'Higher values (0-2) = more creative, lower = more deterministic', 'chatbot-ai-engine' ); ?></p>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="chatbot_position"><?php esc_html_e( 'Chatbot Position', 'chatbot-ai-engine' ); ?></label>
						</th>
						<td>
							<select id="chatbot_position" name="chatbot_ai_engine_settings[position]">
								<option value="bottom-right" <?php selected( $settings['position'] ?? '', 'bottom-right' ); ?>><?php esc_html_e( 'Bottom Right', 'chatbot-ai-engine' ); ?></option>
								<option value="bottom-left" <?php selected( $settings['position'] ?? '', 'bottom-left' ); ?>><?php esc_html_e( 'Bottom Left', 'chatbot-ai-engine' ); ?></option>
								<option value="top-right" <?php selected( $settings['position'] ?? '', 'top-right' ); ?>><?php esc_html_e( 'Top Right', 'chatbot-ai-engine' ); ?></option>
								<option value="top-left" <?php selected( $settings['position'] ?? '', 'top-left' ); ?>><?php esc_html_e( 'Top Left', 'chatbot-ai-engine' ); ?></option>
							</select>
							<p class="description"><?php esc_html_e( 'Where the chatbot widget appears on your site', 'chatbot-ai-engine' ); ?></p>
						</td>
					</tr>
				</table>

				<?php submit_button(); ?>
			</form>
		</div>

		<script>
			function updateApiUrl(provider) {
				const apiUrlField = document.getElementById('chatbot_api_url');
				const urls = {
					'openai': 'https://api.openai.com/v1/chat/completions',
					'groq': 'https://api.groq.com/openai/v1/chat/completions',
					'anthropic': 'https://api.anthropic.com/v1/messages',
					'custom': ''
				};
				apiUrlField.value = urls[provider] || '';
			}
		</script>
		<?php
	}

	/**
	 * Enqueue frontend assets
	 */
	public function enqueue_frontend_assets() {
		$settings = get_option( 'chatbot_ai_engine_settings', array() );

		// Check if chatbot is enabled
		if ( ! isset( $settings['enabled'] ) || '1' !== $settings['enabled'] ) {
			return;
		}

		// Apply filter to control display
		if ( ! apply_filters( 'chatbot_ai_engine_display_bubble', true ) ) {
			return;
		}

		// Enqueue CSS
		wp_enqueue_style(
			'chatbot-ai-engine-style',
			CHATBOT_AI_ENGINE_URL . 'assets/style.css',
			array(),
			CHATBOT_AI_ENGINE_VERSION
		);

		// Enqueue JavaScript
		wp_enqueue_script(
			'chatbot-ai-engine-script',
			CHATBOT_AI_ENGINE_URL . 'assets/script.js',
			array(),
			CHATBOT_AI_ENGINE_VERSION,
			true
		);

		// Localize script
		wp_localize_script(
			'chatbot-ai-engine-script',
			'chatbotAIEngine',
			array(
				'ajaxUrl'          => admin_url( 'admin-ajax.php' ),
				'nonce'            => wp_create_nonce( 'chatbot_ai_engine_nonce' ),
				'position'         => $settings['position'] ?? 'bottom-right',
				'i18n'             => array(
					'placeholder'    => __( 'Type your message...', 'chatbot-ai-engine' ),
					'send'           => __( 'Send', 'chatbot-ai-engine' ),
					'loading'        => __( 'Loading...', 'chatbot-ai-engine' ),
					'error'          => __( 'Error: Could not send message', 'chatbot-ai-engine' ),
					'chatTitle'      => __( 'AI Assistant', 'chatbot-ai-engine' ),
					'closeChat'      => __( 'Close chat', 'chatbot-ai-engine' ),
				),
			)
		);
	}

	/**
	 * Handle AJAX message request
	 */
	public function handle_ajax_message() {
		// Verify nonce
		check_ajax_referer( 'chatbot_ai_engine_nonce', 'nonce' );

		// Get user message
		$user_message = isset( $_POST['message'] ) ? sanitize_text_field( $_POST['message'] ) : '';

		if ( empty( $user_message ) ) {
			wp_send_json_error( array( 'message' => __( 'Message cannot be empty', 'chatbot-ai-engine' ) ) );
		}

		// Validate message length
		if ( strlen( $user_message ) > 5000 ) {
			wp_send_json_error( array( 'message' => __( 'Message is too long', 'chatbot-ai-engine' ) ) );
		}

		// Apply user message filter
		$user_message = apply_filters( 'chatbot_ai_engine_user_message', $user_message );

		// Get settings
		$settings = get_option( 'chatbot_ai_engine_settings', array() );

		// Validate settings
		if ( empty( $settings['api_key'] ) || empty( $settings['api_url'] ) || empty( $settings['model'] ) ) {
			wp_send_json_error( array( 'message' => __( 'Chatbot is not properly configured', 'chatbot-ai-engine' ) ) );
		}

		// Prepare the dynamic system prompt with filter
		$system_prompt = $settings['system_prompt'] ?? __( 'You are a helpful assistant.', 'chatbot-ai-engine' );
		$system_prompt = apply_filters( 'chatbot_ai_engine_system_prompt', $system_prompt );

		// Trigger before API call action
		do_action( 'chatbot_ai_engine_before_api_call', $user_message, $settings );

		// Call AI API
		$response = $this->call_ai_api( $user_message, $system_prompt, $settings );

		if ( is_wp_error( $response ) ) {
			wp_send_json_error( array( 'message' => __( 'API Error: ', 'chatbot-ai-engine' ) . $response->get_error_message() ) );
		}

		// Trigger after API call action
		do_action( 'chatbot_ai_engine_after_api_call', $response, $user_message );

		wp_send_json_success( array( 'message' => $response ) );
	}

	/**
	 * Call AI API
	 *
	 * @param string $user_message User message
	 * @param string $system_prompt System prompt
	 * @param array  $settings Plugin settings
	 * @return string|WP_Error Response or error
	 */
	private function call_ai_api( $user_message, $system_prompt, $settings ) {
		$provider = $settings['api_provider'] ?? 'openai';
		$api_key = $this->decrypt_api_key( $settings['api_key'] );
		$api_url = $settings['api_url'];
		$model = $settings['model'];
		$max_tokens = absint( $settings['max_tokens'] ?? 1000 );
		$temperature = floatval( $settings['temperature'] ?? 0.7 );

		// Prepare request body based on provider
		$body = $this->prepare_api_body( $provider, $user_message, $system_prompt, $model, $max_tokens, $temperature );

		// Apply API body filter
		$body = apply_filters( 'chatbot_ai_engine_api_body', $body, $provider );

		$headers = $this->prepare_api_headers( $provider, $api_key );

		// Make the API request
		$response = wp_remote_post(
			$api_url,
			array(
				'headers'   => $headers,
				'body'      => $body,
				'timeout'   => 30,
				'sslverify' => true,
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$response_code = wp_remote_retrieve_response_code( $response );
		$response_body = wp_remote_retrieve_body( $response );
		$response_data = json_decode( $response_body, true );

		if ( 200 !== $response_code ) {
			$error_message = isset( $response_data['error']['message'] ) ? $response_data['error']['message'] : 'Unknown error';
			return new WP_Error( 'api_error', $error_message );
		}

		// Extract response based on provider
		$ai_message = $this->extract_ai_response( $provider, $response_data );

		if ( empty( $ai_message ) ) {
			return new WP_Error( 'empty_response', __( 'Empty response from AI API', 'chatbot-ai-engine' ) );
		}

		// Apply API response filter
		$ai_message = apply_filters( 'chatbot_ai_engine_api_response', $ai_message, $provider );

		return $ai_message;
	}

	/**
	 * Prepare API request body
	 *
	 * @param string $provider AI Provider
	 * @param string $user_message User message
	 * @param string $system_prompt System prompt
	 * @param string $model Model name
	 * @param int    $max_tokens Max tokens
	 * @param float  $temperature Temperature
	 * @return string JSON encoded body
	 */
	private function prepare_api_body( $provider, $user_message, $system_prompt, $model, $max_tokens, $temperature ) {
		$body = array(
			'model'       => $model,
			'temperature' => $temperature,
			'messages'    => array(
				array(
					'role'    => 'system',
					'content' => $system_prompt,
				),
				array(
					'role'    => 'user',
					'content' => $user_message,
				),
			),
		);

		// Add max_tokens based on provider support
		if ( in_array( $provider, array( 'openai', 'groq', 'custom' ), true ) ) {
			$body['max_tokens'] = $max_tokens;
		}

		return wp_json_encode( $body );
	}

	/**
	 * Prepare API request headers
	 *
	 * @param string $provider AI Provider
	 * @param string $api_key API key
	 * @return array Headers array
	 */
	private function prepare_api_headers( $provider, $api_key ) {
		$headers = array(
			'Content-Type' => 'application/json',
		);

		switch ( $provider ) {
			case 'anthropic':
				$headers['x-api-key'] = $api_key;
				$headers['anthropic-version'] = '2023-06-01';
				break;
			default:
				$headers['Authorization'] = 'Bearer ' . $api_key;
				break;
		}

		return $headers;
	}

	/**
	 * Extract AI response
	 *
	 * @param string $provider AI Provider
	 * @param array  $response_data Response data
	 * @return string AI response message
	 */
	private function extract_ai_response( $provider, $response_data ) {
		if ( 'anthropic' === $provider ) {
			if ( isset( $response_data['content'][0]['text'] ) ) {
				return sanitize_text_field( $response_data['content'][0]['text'] );
			}
		} else {
			if ( isset( $response_data['choices'][0]['message']['content'] ) ) {
				return sanitize_text_field( $response_data['choices'][0]['message']['content'] );
			}
		}

		return '';
	}

	/**
	 * Encrypt API key for secure storage
	 *
	 * @param string $key API key to encrypt
	 * @return string Encrypted key
	 */
	private function encrypt_api_key( $key ) {
		if ( empty( $key ) ) {
			return '';
		}

		$method = 'aes-256-cbc';
		$iv_length = openssl_cipher_iv_length( $method );
		$iv = openssl_random_pseudo_bytes( $iv_length );

		$encrypted = openssl_encrypt( $key, $method, CHATBOT_AI_ENGINE_SALT, 0, $iv );

		if ( false === $encrypted ) {
			return base64_encode( $key ); // Last resort fallback
		}

		// Store IV + Encrypted key
		return base64_encode( $iv . $encrypted );
	}

	/**
	 * Decrypt API key from storage
	 *
	 * @param string $encrypted Encrypted key
	 * @return string Decrypted key
	 */
	private function decrypt_api_key( $encrypted ) {
		if ( empty( $encrypted ) ) {
			return '';
		}

		$data = base64_decode( $encrypted, true );
		if ( false === $data ) {
			return $encrypted;
		}

		$method = 'aes-256-cbc';
		$iv_length = openssl_cipher_iv_length( $method );

		if ( strlen( $data ) <= $iv_length ) {
			// Probably old base64-only format
			return str_replace( CHATBOT_AI_ENGINE_SALT, '', $data );
		}

		$iv = substr( $data, 0, $iv_length );
		$encrypted_text = substr( $data, $iv_length );

		$decrypted = openssl_decrypt( $encrypted_text, $method, CHATBOT_AI_ENGINE_SALT, 0, $iv );

		return ( false !== $decrypted ) ? $decrypted : $encrypted;
	}

	/**
	 * Get decrypted API key (public method for helper functions)
	 *
	 * @return string Decrypted API key
	 */
	public function get_decrypted_api_key() {
		$settings = get_option( 'chatbot_ai_engine_settings', array() );
		$encrypted = isset( $settings['api_key'] ) ? $settings['api_key'] : '';
		return $this->decrypt_api_key( $encrypted );
	}
}

// Initialize the plugin
Chatbot_AI_Engine::get_instance();
