<?php
/**
 * Plugin Name: Chatbot AI Engine
 * Plugin URI: https://github.com/Andon-ov/WP-AI-HelpDesk
 * Description: Specialized AI Chatbot for Chef & Gastro. Supports Groq, OpenAI, Anthropic and Gemini.
 * Version: 1.1.9
 * Author: Andon-ov
 * Author URI: https://github.com/Andon-ov
 * License: GPL v2 or later
 * Text Domain: chatbot-ai-engine
 *
 * @package Chatbot_AI_Engine
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'CHATBOT_AI_ENGINE_VERSION', '1.1.9' );
define( 'CHATBOT_AI_ENGINE_PATH', plugin_dir_path( __FILE__ ) );
define( 'CHATBOT_AI_ENGINE_URL', plugin_dir_url( __FILE__ ) );
define( 'CHATBOT_AI_ENGINE_BASENAME', plugin_basename( __FILE__ ) );

class Chatbot_AI_Engine {

	private static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function __construct() {
		$this->init_hooks();
		$this->register_global_functions();
	}

	private function register_global_functions() {
		if ( ! function_exists( 'get_chatbot_ai_engine_settings' ) ) {
			include_once CHATBOT_AI_ENGINE_PATH . 'includes/helper-functions.php';
		}
	}

	private function init_hooks() {
		register_activation_hook( __FILE__, array( $this, 'activate' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
		add_action( 'admin_menu', array( $this, 'register_admin_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );
		add_action( 'wp_ajax_chatbot_send_message', array( $this, 'handle_ajax_message' ) );
		add_action( 'wp_ajax_nopriv_chatbot_send_message', array( $this, 'handle_ajax_message' ) );
		add_action( 'wp_ajax_chatbot_sync_knowledge', array( $this, 'handle_sync_knowledge' ) );
	}

	public function activate() {
		log_chatbot_ai_engine( 'Chef & Gastro Academy Chatbot v1.1.9 activating...' );
		if ( ! get_option( 'chatbot_ai_engine_settings' ) ) {
			$default_settings = array(
				'enabled'      => '0',
				'api_key'      => '',
				'api_provider' => 'groq',
				'api_url'      => 'https://api.groq.com/openai/v1/chat/completions',
				'model'        => 'llama-3.3-70b-versatile',
				'system_prompt' => "Ти си официалният AI асистент на {site_name} ({site_url}). 
Твоята цел е да помагаш на потребителите да намерят КУРСОВЕ, рецепти и кулинарни знания.

ПРАВИЛА:
1. ПЪРВО ПРОВЕРИ DATASET: Използвай САМО информацията от предоставените данни за конкретни рецепти и курсове.
2. ЦЕНИ: Винаги казвай цената на курса, ако я има в DATASET.
3. ЛИНКОВЕ: Винаги давай пълни, кликаеми линкове.
4. ОТГОВОР: Бъди професионален кулинарен инструктор. Отговаряй на български.",
				'position'     => 'bottom-right',
				'temperature'  => 0.2
			);
			update_option( 'chatbot_ai_engine_settings', $default_settings );
		}
		flush_rewrite_rules();
	}

	public function deactivate() {
		flush_rewrite_rules();
	}

	public function load_textdomain() {
		load_plugin_textdomain( 'chatbot-ai-engine', false, dirname( CHATBOT_AI_ENGINE_BASENAME ) . '/languages' );
	}

	public function register_admin_menu() {
		add_menu_page( __( 'Chatbot AI Engine', 'chatbot-ai-engine' ), __( 'AI Chatbot', 'chatbot-ai-engine' ), 'manage_options', 'chatbot-ai-engine', array( $this, 'render_settings_page' ), 'dashicons-format-status', 90 );
	}

	public function register_settings() {
		register_setting( 'chatbot_ai_engine_settings_group', 'chatbot_ai_engine_settings', array( 'type' => 'array', 'sanitize_callback' => array( $this, 'sanitize_settings' ) ) );
		add_settings_section( 'chatbot_ai_engine_main', __( 'AI Chatbot Configuration', 'chatbot-ai-engine' ), array( $this, 'render_settings_section' ), 'chatbot_ai_engine_settings_group' );
		add_settings_section( 'chatbot_ai_engine_knowledge', __( 'Site Knowledge Base', 'chatbot-ai-engine' ), array( $this, 'render_knowledge_section' ), 'chatbot_ai_engine_settings_group' );
	}

	public function sanitize_settings( $settings ) {
		if ( ! is_array( $settings ) ) return array();
		$sanitized = array();
		$sanitized['enabled'] = isset( $settings['enabled'] ) ? '1' : '0';
		$api_key = isset( $settings['api_key'] ) ? sanitize_text_field( $settings['api_key'] ) : '';
		if ( ! empty( $api_key ) && '••••••••••••••••' !== $api_key ) {
			$sanitized['api_key'] = $this->encrypt_api_key( $api_key );
		} else {
			$existing = get_option( 'chatbot_ai_engine_settings', array() );
			$sanitized['api_key'] = $existing['api_key'] ?? '';
		}
		$allowed_providers = array( 'openai', 'groq', 'anthropic', 'gemini', 'custom' );
		$sanitized['api_provider'] = in_array( $settings['api_provider'], $allowed_providers ) ? $settings['api_provider'] : 'openai';
		$sanitized['api_url'] = esc_url_raw( $settings['api_url'] ?? '' );
		$sanitized['model'] = sanitize_text_field( $settings['model'] ?? '' );
		$sanitized['system_prompt'] = sanitize_textarea_field( $settings['system_prompt'] ?? '' );
		$sanitized['position'] = sanitize_text_field( $settings['position'] ?? 'bottom-right' );
		$sanitized['max_tokens'] = absint( $settings['max_tokens'] ?? 1000 );
		$temperature = floatval( $settings['temperature'] ?? 0.7 );
		if ( 'groq' === $sanitized['api_provider'] && $temperature > 0.5 ) $temperature = 0.3;
		$sanitized['temperature'] = min( 2.0, max( 0.0, $temperature ) );
		return apply_filters( 'chatbot_ai_engine_settings', $sanitized );
	}

	public function render_settings_section() { echo '<p>' . esc_html__( 'Configure your AI chatbot settings below.', 'chatbot-ai-engine' ) . '</p>'; }

	public function render_knowledge_section() {
		$upload_dir = wp_upload_dir();
		$file_path = $upload_dir['basedir'] . '/chatbot-ai-knowledge.json';
		$last_sync = file_exists( $file_path ) ? date( 'Y-m-d H:i:s', filemtime( $file_path ) ) : __( 'Never', 'chatbot-ai-engine' );
		echo '<p><strong>' . esc_html__( 'Last Sync:', 'chatbot-ai-engine' ) . '</strong> ' . esc_html( $last_sync ) . '</p>';
		echo '<p><strong>' . esc_html__( 'File Path:', 'chatbot-ai-engine' ) . '</strong> <code>' . esc_html( $file_path ) . '</code></p>';
		echo '<button type="button" id="chatbot-sync-btn" class="button button-secondary">' . esc_html__( 'Index Site Content Now', 'chatbot-ai-engine' ) . '</button>';
		echo '<span id="sync-spinner" class="spinner" style="float:none; margin-left:10px; vertical-align:middle;"></span>';
		echo '<p id="sync-result" style="margin-top:10px; font-weight:bold;"></p>';
		?>
		<script>
		document.addEventListener('DOMContentLoaded', function() {
			const btn = document.getElementById('chatbot-sync-btn');
			if (btn) btn.addEventListener('click', function() {
				btn.disabled = true; document.getElementById('sync-spinner').classList.add('is-active');
				const formData = new FormData();
				formData.append('action', 'chatbot_sync_knowledge');
				formData.append('nonce', '<?php echo wp_create_nonce("chatbot_ai_engine_nonce"); ?>');
				fetch(ajaxurl, { method: 'POST', body: formData }).then(r => r.json()).then(d => {
					document.getElementById('sync-spinner').classList.remove('is-active'); btn.disabled = false;
					const res = document.getElementById('sync-result');
					res.style.color = d.success ? 'green' : 'red'; res.innerText = d.data.message;
				});
			});
		});
		</script>
		<?php
	}

	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Denied' );
		$settings = get_option( 'chatbot_ai_engine_settings', array() );
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<form method="post" action="options.php">
				<?php settings_fields( 'chatbot_ai_engine_settings_group' ); ?>
				<table class="form-table">
					<tr><th>Enable Chatbot</th><td><input type="checkbox" name="chatbot_ai_engine_settings[enabled]" value="1" <?php checked( $settings['enabled'] ?? 0, 1 ); ?> /></td></tr>
					<tr><th>AI Provider</th><td>
						<select name="chatbot_ai_engine_settings[api_provider]" onchange="updateApiUrl(this.value)">
							<option value="openai" <?php selected($settings['api_provider'] ?? '', 'openai'); ?>>OpenAI</option>
							<option value="groq" <?php selected($settings['api_provider'] ?? '', 'groq'); ?>>Groq</option>
							<option value="anthropic" <?php selected($settings['api_provider'] ?? '', 'anthropic'); ?>>Anthropic</option>
							<option value="gemini" <?php selected($settings['api_provider'] ?? '', 'gemini'); ?>>Gemini</option>
						</select>
					</td></tr>
					<tr><th>API URL</th><td><input type="url" id="chatbot_api_url" name="chatbot_ai_engine_settings[api_url]" value="<?php echo esc_url( $settings['api_url'] ?? '' ); ?>" class="regular-text" /></td></tr>
					<tr><th>API Key</th><td><input type="password" name="chatbot_ai_engine_settings[api_key]" value="<?php echo ! empty( $settings['api_key'] ) ? '••••••••••••••••' : ''; ?>" class="regular-text" /></td></tr>
					<tr><th>Model Name</th><td><input type="text" id="chatbot_model" name="chatbot_ai_engine_settings[model]" value="<?php echo esc_attr( $settings['model'] ?? '' ); ?>" class="regular-text" /></td></tr>
					<tr><th>System Prompt</th><td><textarea name="chatbot_ai_engine_settings[system_prompt]" class="large-text" rows="10"><?php echo esc_textarea( $settings['system_prompt'] ?? '' ); ?></textarea></td></tr>
					<tr><th>Temperature</th><td><input type="number" name="chatbot_ai_engine_settings[temperature]" value="<?php echo esc_attr( $settings['temperature'] ?? 0.7 ); ?>" min="0" max="2" step="0.1" /></td></tr>
					<tr><th>Position</th><td>
						<select name="chatbot_ai_engine_settings[position]">
							<option value="bottom-right" <?php selected($settings['position'] ?? '', 'bottom-right'); ?>>Bottom Right</option>
							<option value="bottom-left" <?php selected($settings['position'] ?? '', 'bottom-left'); ?>>Bottom Left</option>
						</select>
					</td></tr>
				</table>
				<hr><?php do_settings_sections( 'chatbot_ai_engine_settings_group' ); ?>
				<?php submit_button(); ?>
			</form>
		</div>
		<script>
			function updateApiUrl(p) {
				const u = { 'openai': 'https://api.openai.com/v1/chat/completions', 'groq': 'https://api.groq.com/openai/v1/chat/completions', 'anthropic': 'https://api.anthropic.com/v1/messages', 'gemini': 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent' };
				const m = { 'openai': 'gpt-3.5-turbo', 'groq': 'llama-3.3-70b-versatile', 'anthropic': 'claude-3-5-sonnet-20240620', 'gemini': 'gemini-1.5-flash' };
				document.getElementById('chatbot_api_url').value = u[p] || '';
				document.getElementById('chatbot_model').value = m[p] || '';
			}
		</script>
		<?php
	}

	public function enqueue_frontend_assets() {
		$s = get_option( 'chatbot_ai_engine_settings', array() );
		if ( ! ($s['enabled'] ?? 0) ) return;
		wp_enqueue_style( 'chatbot-ai-style', CHATBOT_AI_ENGINE_URL . 'assets/style.css', array(), CHATBOT_AI_ENGINE_VERSION );
		wp_enqueue_script( 'chatbot-ai-script', CHATBOT_AI_ENGINE_URL . 'assets/script.js', array(), CHATBOT_AI_ENGINE_VERSION, true );
		wp_localize_script( 'chatbot-ai-script', 'chatbotAIEngine', array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce' => wp_create_nonce( 'chatbot_ai_engine_nonce' ),
			'position' => $s['position'] ?? 'bottom-right',
			'i18n' => array( 'placeholder' => 'Пишете съобщение...', 'send' => 'Изпрати', 'loading' => '...', 'error' => 'Грешка', 'chatTitle' => 'Chef & Gastro Assistant', 'closeChat' => 'Затвори', 'goodbye' => 'Заповядайте отново! 👋' )
		));
	}

	public function handle_ajax_message() {
		check_ajax_referer( 'chatbot_ai_engine_nonce', 'nonce' );
		$msg = sanitize_text_field( $_POST['message'] ?? '' );
		if ( empty($msg) ) wp_send_json_error( array('message' => 'Empty') );
		
		$settings = get_option( 'chatbot_ai_engine_settings', array() );
		$prompt = $settings['system_prompt'] ?? '';
		$prompt = str_replace( array('{site_url}', '{site_name}'), array(get_site_url(), get_bloginfo('name')), $prompt );
		
		$context = $this->get_site_context( $msg );
		if ( ! empty($context) ) $prompt .= $context;

		$response = $this->call_ai_api( $msg, $prompt, $settings );
		if ( is_wp_error($response) ) wp_send_json_error( array('message' => $response->get_error_message()) );
		wp_send_json_success( array('message' => $response) );
	}

	private function get_site_context( $msg ) {
		$file = wp_upload_dir()['basedir'] . '/chatbot-ai-knowledge.json';
		$index = file_exists($file) ? json_decode( file_get_contents($file), true ) : array();
		$keywords = array_filter( explode( ' ', mb_strtolower( preg_replace( '/[[:punct:]]/u', ' ', $msg ) ) ), fn($w) => mb_strlen(trim($w)) > 2 );
		
		$matches = array();
		if ( ! empty($index) && ! empty($keywords) ) {
			foreach ( $index as $item ) {
				$score = 0; $title = mb_strtolower($item['title']); $content = mb_strtolower($item['content']); $hits = 0;
				foreach ( $keywords as $kw ) {
					$found = false;
					if ( mb_strpos($title, $kw) !== false ) { $score += 50; $found = true; } // Massive boost for title
					if ( mb_strpos($content, $kw) !== false ) { $score += 15; $found = true; }
					if ($found) $hits++;
				}
				if ($hits > 1) $score *= $hits;
				if ($score > 0) $matches[] = array( 'score' => $score, 'data' => array('name' => $item['title'], 'info' => $item['content'], 'link' => $item['url']) );
			}
		}

		usort( $matches, fn($a, $b) => $b['score'] - $a['score'] );
		$top = array_slice( $matches, 0, 5 );
		
		// DB Fallback
		if ( empty($top) ) {
			$query = new WP_Query( array( 'post_type' => array('post', 'page', 'product', 'wprm_recipe', 'glossary'), 'post_status' => 'publish', 's' => $msg, 'posts_per_page' => 3 ) );
			if ( $query->have_posts() ) {
				foreach ( $query->posts as $p ) $top[] = array('name' => $p->post_title, 'info' => mb_substr(wp_strip_all_tags($p->post_content), 0, 500), 'link' => get_permalink($p->ID));
			}
			wp_reset_postdata();
		} else {
			$top = array_column($top, 'data');
		}

		if ( empty($top) ) return "\n\n### NO DATA FOUND. Guide the user to {site_url}/рецепти/.";

		$ctx = "\n\n### DATASET (FACTS FROM SITE):\n" . wp_json_encode($top, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
		$ctx .= "\nSTRICT RULES: 1. Use ONLY names and links from DATASET. 2. NEVER invent links. 3. Answer in natural Bulgarian.";
		return $ctx;
	}

	public function handle_sync_knowledge() {
		check_ajax_referer( 'chatbot_ai_engine_nonce', 'nonce' );
		$res = $this->sync_site_knowledge();
		if ( is_wp_error($res) ) wp_send_json_error( array('message' => $res->get_error_message()) );
		wp_send_json_success( array('message' => "Successfully indexed $res items.") );
	}

	private function sync_site_knowledge() {
		@set_time_limit( 600 ); if ( function_exists( 'wp_raise_memory_limit' ) ) wp_raise_memory_limit( 'admin' );
		$post_types = array( 'post', 'page', 'product', 'wprm_recipe', 'glossary', 'tribe_events' );
		$data = array(); 

		foreach ( $post_types as $type ) {
			$query = new WP_Query( array( 'post_type' => $type, 'post_status' => 'publish', 'posts_per_page' => -1, 'fields' => 'ids' ) );
			foreach ( $query->posts as $id ) {
				$p = get_post($id); if (!$p) continue;
				$text = "";
				if ('wprm_recipe' === $type) {
					$ing = get_post_meta($id, 'wprm_ingredients', true);
					$text = "Ingredients: ";
					if (is_array($ing)) foreach($ing as $g) if(!empty($g['ingredients'])) foreach($g['ingredients'] as $i) $text .= ($i['name']??'').", ";
					$text .= " | Summary: " . get_post_meta($id, 'wprm_summary', true) . " | Method: " . $p->post_content;
				} elseif ('tribe_events' === $type) {
					$cost = get_post_meta($id, '_EventCost', true);
					if ( empty($cost) ) {
						$t_query = new WP_Query(array('post_type' => 'product', 'meta_key' => '_tribe_wooticket_for_event', 'meta_value' => $id, 'posts_per_page' => 1));
						if ( $t_query->have_posts() ) $cost = get_post_meta($t_query->posts[0], '_price', true);
						wp_reset_postdata();
					}
					if ( empty($cost) ) $cost = get_post_meta($id, '_ticket_price', true);
					$text = "DATE: ".get_post_meta($id, '_EventStartDate', true)." | PRICE: ".($cost ? $cost . " лв." : "Check Academy")." | INFO: ".$p->post_content;
				} elseif ('glossary' === $type) {
					$text = "DEFINITION: " . $p->post_content;
				} else {
					$text = $p->post_content;
				}
				$clean = wp_strip_all_tags(strip_shortcodes($text));
				$data[] = array('id' => $id, 'title' => get_the_title($id), 'type' => $type, 'content' => mb_substr(preg_replace('/\s+/', ' ', $clean), 0, 1000), 'url' => get_permalink($id));
			}
		}

		foreach ( array('product_cat', 'category', 'wprm_course') as $tax ) {
			if ( taxonomy_exists($tax) ) {
				$terms = get_terms( array( 'taxonomy' => $tax, 'hide_empty' => true ) );
				if ( ! is_wp_error($terms) ) foreach ( $terms as $t ) $data[] = array('id' => 't_'.$t->term_id, 'title' => $t->name, 'type' => 'category', 'content' => "Browse items in $t->name", 'url' => get_term_link($t));
			}
		}

		$file = wp_upload_dir()['basedir'] . '/chatbot-ai-knowledge.json';
		file_put_contents( $file, wp_json_encode($data, JSON_UNESCAPED_UNICODE) );
		return count($data);
	}

	private function call_ai_api( $msg, $prompt, $settings ) {
		$key = $this->get_decrypted_api_key();
		$body = array( 'model' => $settings['model'], 'temperature' => floatval($settings['temperature']), 'messages' => array( array('role' => 'system', 'content' => $prompt), array('role' => 'user', 'content' => $msg) ) );
		$response = wp_remote_post( $settings['api_url'], array( 'headers' => array( 'Authorization' => 'Bearer ' . $key, 'Content-Type' => 'application/json' ), 'body' => wp_json_encode($body), 'timeout' => 30 ) );
		if ( is_wp_error($response) ) return $response;
		$data = json_decode( wp_remote_retrieve_body($response), true );
		$raw = $data['choices'][0]['message']['content'] ?? '';
		if ( empty($raw) ) return '';
		$text = preg_replace('/\[([^\]]+)\]\(([^\)]+)\)/', '<a href="$2" target="_blank">$1</a>', $raw);
		$text = preg_replace('/(?<!href=")(https?:\/\/[^\s<]+)/', '<a href="$1" target="_blank">$1</a>', $text);
		return wp_kses($text, array('strong' => array(), 'br' => array(), 'p' => array(), 'ul' => array(), 'li' => array(), 'a' => array('href' => array(), 'target' => array(), 'title' => array())));
	}

	private function encrypt_api_key($k) { $m = 'aes-256-cbc'; $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($m)); return base64_encode($iv . openssl_encrypt($k, $m, $this->get_encryption_key(), 0, $iv)); }
	private function decrypt_api_key($e) {
		$d = base64_decode($e, true); if (!$d) return $e;
		$m = 'aes-256-cbc'; $ivl = openssl_cipher_iv_length($m);
		$iv = substr($d, 0, $ivl); $txt = substr($d, $ivl);
		$res = openssl_decrypt($txt, $m, $this->get_encryption_key(), 0, $iv);
		return $res !== false ? $res : $e;
	}
	private function get_encryption_key() { return function_exists('wp_salt') ? wp_salt('auth') : (defined('AUTH_KEY') ? AUTH_KEY : 'salt'); }
	public function get_decrypted_api_key() {
		$s = get_option( 'chatbot_ai_engine_settings', array() );
		return $this->decrypt_api_key( $s['api_key'] ?? '' );
	}
}

Chatbot_AI_Engine::get_instance();
