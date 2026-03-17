<?php
/**
 * Smoke Test for Chatbot AI Engine
 */

require_once( '../../../wp-load.php' );

if ( ! current_user_can( 'manage_options' ) ) {
    wp_die( 'Достъпът е забранен.' );
}

echo "<h1>🔍 Chatbot AI Engine - Smoke Test</h1>";

// 1. Проверка на настройките
$settings = get_option( 'chatbot_ai_engine_settings' );
echo "<h3>1. Настройки:</h3>";
echo "<pre>" . print_r( $settings, true ) . "</pre>";

// 2. Проверка на API ключа
$engine = Chatbot_AI_Engine::get_instance();
$api_key = $engine->get_decrypted_api_key();
echo "<h3>2. API Ключ:</h3>";
echo "Ключът е зареден: " . ( ! empty( $api_key ) ? '✅ Да' : '❌ НЕ' ) . "<br>";
if ( ! empty( $api_key ) ) {
    echo "Първи 5 символа: " . substr( $api_key, 0, 5 ) . "...<br>";
}

// 3. Проверка на базата данни (Knowledge Base)
$upload_dir = wp_upload_dir();
$file_path = $upload_dir['basedir'] . '/chatbot-ai-knowledge.json';
echo "<h3>3. Knowledge Base:</h3>";
echo "Път до файла: <code>$file_path</code><br>";
if ( file_exists( $file_path ) ) {
    $size = filesize( $file_path );
    echo "Файлът съществува: ✅ Да (" . size_format( $size ) . ")<br>";
    $last_mod = date( "Y-m-d H:i:s", filemtime( $file_path ) );
    echo "Последна промяна: $last_mod<br>";
} else {
    echo "Файлът съществува: ❌ НЕ<br>";
}

// 4. WooCommerce продукти (Вашият диагностичен код)
echo "<h3>4. WooCommerce продукти:</h3>";
if ( function_exists('wc_get_products') ) {
    $products = wc_get_products( array('limit' => -1, 'status' => 'publish') );
    echo "Намерени продукти в сайта: <strong>" . count($products) . "</strong><br>";
    foreach ($products as $p) {
        echo "- [{$p->get_id()}] {$p->get_name()} | Цена: {$p->get_price()} EUR | URL: {$p->get_permalink()}<br>";
    }
} else {
    echo "❌ WooCommerce не е активен!<br>";
}

// 5. Разбор на Knowledge JSON
echo "<h3>5. Съдържание на Knowledge JSON (статистика):</h3>";
if ( file_exists( $file_path ) ) {
    $json_content = file_get_contents($file_path);
    $data = json_decode($json_content, true);
    if ( is_array($data) ) {
        $types = array_count_values(array_column($data, 'type'));
        echo "Общо записи в JSON: <strong>" . count($data) . "</strong><br>";
        foreach ($types as $type => $count) {
            echo "- Тип <strong>{$type}</strong>: {$count} записа<br>";
        }
        
        echo "<h4>Примерен запис (първия):</h4>";
        echo "<pre>" . print_r( $data[0] ?? 'Няма записи', true ) . "</pre>";
    } else {
        echo "❌ JSON файлът е невалиден или празен!<br>";
    }
} else {
    echo "❌ JSON файлът не беше намерен за анализ.<br>";
}

echo "<hr><p>Тестът завърши.</p>";
