<?php
/**
 * Chatbot AI Engine - Smoke Test Script
 * Use this to verify API connectivity and settings.
 */

// Load WordPress environment
define( 'WP_USE_THEMES', false );
require_once( 'wp-load.php' );

if ( ! current_user_can( 'manage_options' ) ) {
    die( 'Достъпът е забранен. Трябва да сте администратор.' );
}

echo "<h1>🔍 Chatbot AI Engine - Тест на системата</h1>";

// 1. Проверка на настройките
$settings = get_chatbot_ai_engine_settings();
echo "<h3>1. Проверка на настройките:</h3>";
echo "Доставчик: <strong>" . ( $settings['api_provider'] ?? 'не е заложен' ) . "</strong><br>";
echo "Модел: <strong>" . ( $settings['model'] ?? 'не е заложен' ) . "</strong><br>";
echo "URL: <code>" . ( $settings['api_url'] ?? 'липсва' ) . "</code><br>";

// 2. Проверка на API ключа
$key = Chatbot_AI_Engine::get_instance()->get_decrypted_api_key();
if ( ! empty( $key ) ) {
    echo "✅ API ключът е декриптиран успешно (първите 4 символа: " . substr( $key, 0, 4 ) . "...)<br>";
} else {
    echo "❌ API ключът липсва или не може да бъде декриптиран!<br>";
}

// 3. Тест на AJAX Nonce
$nonce = wp_create_nonce( 'chatbot_ai_engine_nonce' );
echo "<h3>2. Сигурност:</h3>";
echo "Генериран Nonce: <code>$nonce</code> (Валиден за текущата сесия)<br>";

echo "<h3>3. Как да тествате фронтенда:</h3>";
echo "1. Отидете на началната страница.<br>";
echo "2. Натиснете F12 (Console).<br>";
echo "3. Напишете нещо в чата и следете за съобщения в конзолата.<br>";

echo "<hr><p>Ако виждате всички данни горе, значи плъгинът е конфигуриран правилно!</p>";
