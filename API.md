# 🔌 API Референция - Chatbot AI Engine

## Съдържание

1. [Филтри](#филтри)
2. [Хукове (Actions)](#хукове-actions)
3. [Функции](#функции)
4. [Примери за разширение](#примери-за-разширение)
5. [AJAX API](#ajax-api)
6. [Константи](#константи)
7. [Обекти и класове](#обекти-и-класове)

---

## 🔽 Филтри

Филтрите позволяват да промените данни преди да бъдат обработени.

### `chatbot_ai_engine_settings`

Филтър за модификация на всички настройки.

**Хук:**
```php
apply_filters( 'chatbot_ai_engine_settings', $settings );
```

**Параметри:**
- `$settings` (array) - Текущите настройки

**Върнача стойност:**
- (array) - Модифицирани настройки

**Пример:**
```php
add_filter( 'chatbot_ai_engine_settings', function( $settings ) {
    // Промяна на температурата
    $settings['temperature'] = 0.5;
    return $settings;
} );
```

---

### `chatbot_ai_engine_api_body`

Филтър за модификация на API заявката.

**Хук:**
```php
apply_filters( 'chatbot_ai_engine_api_body', $body, $provider );
```

**Параметри:**
- `$body` (string) - JSON енкодирана заявка
- `$provider` (string) - AI доставчик (openai, groq, etc.)

**Върнача стойност:**
- (string) - Модифицирана заявка

**Пример:**
```php
add_filter( 'chatbot_ai_engine_api_body', function( $body, $provider ) {
    if ( 'openai' === $provider ) {
        $decoded = json_decode( $body, true );
        // Добавете стойност за тестване
        $decoded['user'] = get_current_user_id();
        return wp_json_encode( $decoded );
    }
    return $body;
}, 10, 2 );
```

---

### `chatbot_ai_engine_api_response`

Филтър за модификация на отговора от AI.

**Хук:**
```php
apply_filters( 'chatbot_ai_engine_api_response', $response, $provider );
```

**Параметри:**
- `$response` (string) - Текста на отговора
- `$provider` (string) - AI доставчик

**Върнача стойност:**
- (string) - Модифициран отговор

**Пример:**
```php
add_filter( 'chatbot_ai_engine_api_response', function( $response ) {
    // Добавете емотикон към всеки отговор
    return $response . ' 👍';
}, 10, 1 );
```

---

### `chatbot_ai_engine_user_message`

Филтър за модификация на потребителското съобщение.

**Хук:**
```php
apply_filters( 'chatbot_ai_engine_user_message', $message );
```

**Параметри:**
- `$message` (string) - Съобщението от потребителя

**Върнача стойност:**
- (string) - Модифицирано съобщение

**Пример:**
```php
add_filter( 'chatbot_ai_engine_user_message', function( $message ) {
    // Преобразувайте в главни букви
    return strtoupper( $message );
} );
```

---

### `chatbot_ai_engine_system_prompt`

Филтър за модификация на системния промпт. Прилага се динамично при всяка AJAX заявка към AI доставчика.

**Хук:**
```php
apply_filters( 'chatbot_ai_engine_system_prompt', $prompt );
```

**Параметри:**
- `$prompt` (string) - Текущия системен промпт от настройките

**Върнача стойност:**
- (string) - Модифициран промпт

**Пример (Динамичен контекст спрямо страницата):**
```php
add_filter( 'chatbot_ai_engine_system_prompt', function( $prompt ) {
    if ( is_cart() ) {
        return 'Вие сте асистент по продажбите. Помогнете на потребителя да завърши поръчката си.';
    }
    return $prompt;
} );
```

---

### `chatbot_ai_engine_display_bubble`

Филтър за контролиране на показване на чатбота.

**Хук:**
```php
apply_filters( 'chatbot_ai_engine_display_bubble', $display );
```

**Параметри:**
- `$display` (bool) - Да ли да се показва чатбота

**Върнача стойност:**
- (bool) - Модифицирана стойност

**Пример:**
```php
add_filter( 'chatbot_ai_engine_display_bubble', function( $display ) {
    // Показвайте чатбота само в определени часове
    $hour = (int) date( 'H' );
    return ( $hour >= 9 && $hour <= 17 );
} );
```

---

## 🎬 Хукове (Actions)

Хуковете позволяват да кажете код да се изпълни в определени моменти.

### `chatbot_ai_engine_before_api_call`

Действие преди API заявката.

**Хук:**
```php
do_action( 'chatbot_ai_engine_before_api_call', $user_message, $settings );
```

**Параметри:**
- `$user_message` (string) - Съобщението от потребителя
- `$settings` (array) - Текущите настройки

**Пример:**
```php
add_action( 'chatbot_ai_engine_before_api_call', function( $message, $settings ) {
    // Логирайте съобщението
    error_log( 'User message: ' . $message );
}, 10, 2 );
```

---

### `chatbot_ai_engine_after_api_call`

Действие след успешна API заявка.

**Хук:**
```php
do_action( 'chatbot_ai_engine_after_api_call', $response, $user_message );
```

**Параметри:**
- `$response` (string) - Отговора от AI
- `$user_message` (string) - Оригиналното съобщение

**Пример:**
```php
add_action( 'chatbot_ai_engine_after_api_call', function( $response, $message ) {
    // Запазете интеракцията в кастомна таблица
    global $wpdb;
    $wpdb->insert(
        $wpdb->prefix . 'chatbot_conversations',
        array(
            'user_message' => $message,
            'bot_response' => $response,
            'timestamp'    => current_time( 'mysql' ),
        )
    );
}, 10, 2 );
```

---

### `chatbot_ai_engine_settings_updated`

Действие при обновяване на настройките.

**Хук:**
```php
do_action( 'chatbot_ai_engine_settings_updated', $settings );
```

**Параметри:**
- `$settings` (array) - Нови настройки

**Пример:**
```php
add_action( 'chatbot_ai_engine_settings_updated', function( $settings ) {
    // Пикнете кеш някога след промяна
    wp_cache_flush();
} );
```

---

### `chatbot_ai_engine_activated`

Действие при активация на плъгина.

**Хук:**
```php
do_action( 'chatbot_ai_engine_activated' );
```

**Пример:**
```php
add_action( 'chatbot_ai_engine_activated', function() {
    // Създайте кастомна таблица
    global $wpdb;
    $wpdb->query( "
        CREATE TABLE IF NOT EXISTS {$wpdb->prefix}chatbot_conversations (
            id BIGINT(20) NOT NULL AUTO_INCREMENT,
            user_message TEXT NOT NULL,
            bot_response TEXT NOT NULL,
            user_id BIGINT(20),
            timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY timestamp (timestamp)
        ) ENGINE=InnoDB;
    " );
} );
```

---

## 🔧 Функции

### `get_chatbot_ai_engine_settings()`

Получете всички настройки на чатбота.

**Синтаксис:**
```php
$settings = get_chatbot_ai_engine_settings();
```

**Върнача стойност:**
```php
array (
    'enabled'        => '1',
    'api_provider'   => 'openai',
    'api_url'        => 'https://api.openai.com/v1/chat/completions',
    'api_key'        => 'sk-...',
    'model'          => 'gpt-3.5-turbo',
    'system_prompt'  => 'You are a helpful assistant.',
    'max_tokens'     => 1000,
    'temperature'    => 0.7,
    'position'       => 'bottom-right',
)
```

**Пример:**
```php
$settings = get_chatbot_ai_engine_settings();
echo 'Model: ' . $settings['model'];
```

---

### `is_chatbot_ai_engine_enabled()`

Проверете дали е включен.

**Синтаксис:**
```php
$enabled = is_chatbot_ai_engine_enabled();
```

**Върнача стойност:**
```php
true  // Да, е включен
false // Не, е изключен
```

**Пример:**
```php
if ( is_chatbot_ai_engine_enabled() ) {
    echo 'Chatbot is active!';
}
```

---

## 💡 Примери за разширение

### Пример 1: Кастомна история на чатовете

Създайте таблица за съхранение на чатова история:

```php
// В вашия плъгин или theme functions.php

add_action( 'chatbot_ai_engine_after_api_call', function( $response, $user_message ) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'chatbot_conversations';

    $wpdb->insert(
        $table_name,
        array(
            'user_id'      => get_current_user_id(),
            'user_message' => $user_message,
            'bot_response' => $response,
            'timestamp'    => current_time( 'mysql' ),
        ),
        array( '%d', '%s', '%s', '%s' )
    );
}, 10, 2 );
```

---

### Пример 2: Контролиран отговор по роля

Модифицирайте промпта в зависимост от ролята:

```php
add_filter( 'chatbot_ai_engine_system_prompt', function( $prompt ) {
    $user = wp_get_current_user();

    if ( user_can( $user, 'manage_options' ) ) {
        $prompt .= ' This user is a site administrator.';
    } elseif ( user_can( $user, 'edit_posts' ) ) {
        $prompt .= ' This user is an author.';
    }

    return $prompt;
} );
```

---

### Пример 3: Филтриран отговор

Добавете префикс към всеки отговор на AI:

```php
add_filter( 'chatbot_ai_engine_api_response', function( $response ) {
    $settings = get_chatbot_ai_engine_settings();
    $provider = $settings['api_provider'] ?? 'unknown';

    return sprintf(
        '[%s] %s',
        ucfirst( $provider ),
        $response
    );
} );
```

---

### Пример 4: Контролиране на видимост

Показвайте чатбота само на определени страници:

```php
add_filter( 'chatbot_ai_engine_display_bubble', function( $display ) {
    // Покажи чатбота само на страницата "Контакти"
    if ( is_page( 'contact' ) || is_page( 'support' ) ) {
        return true;
    }

    return false;
} );
```

---

### Пример 5: Кастомна система промпт по страница

```php
add_filter( 'chatbot_ai_engine_system_prompt', function( $prompt ) {
    if ( is_page( 'products' ) ) {
        return 'You are a product specialist. Help customers find products.';
    } elseif ( is_page( 'support' ) ) {
        return 'You are a support agent. Help resolve customer issues.';
    }

    return $prompt;
} );
```

---

## 🔗 AJAX API

### Потребителя - приложен AJAX запрос

**Точка вход:**
```
POST /wp-admin/admin-ajax.php
```

**Параметри:**

```javascript
{
    action: 'chatbot_send_message',
    message: 'User message here',
    nonce: 'nonce-from-localized-script'
}
```

**Пример (JavaScript):**
```javascript
const formData = new FormData();
formData.append( 'action', 'chatbot_send_message' );
formData.append( 'message', 'Hello!' );
formData.append( 'nonce', chatbotAIEngine.nonce );

fetch( chatbotAIEngine.ajaxUrl, {
    method: 'POST',
    body: formData
} )
.then( response => response.json() )
.then( data => {
    if ( data.success ) {
        console.log( 'Response:', data.data.message );
    } else {
        console.error( 'Error:', data.data.message );
    }
} );
```

---

### Отговор на AJAX

**Успешен отговор:**
```json
{
    "success": true,
    "data": {
        "message": "Response from AI"
    }
}
```

**Грешка:**
```json
{
    "success": false,
    "data": {
        "message": "Error message here"
    }
}
```

---

## 🔑 Константи

### Вградени константи

```php
CHATBOT_AI_ENGINE_VERSION  // '1.0.0'
CHATBOT_AI_ENGINE_PATH     // '/wp-content/plugins/chatbot-ai-engine/'
CHATBOT_AI_ENGINE_URL      // 'http://site.com/wp-content/plugins/chatbot-ai-engine/'
CHATBOT_AI_ENGINE_BASENAME // 'chatbot-ai-engine/chatbot-ai-engine.php'
```

**Пример:**
```php
echo 'Version: ' . CHATBOT_AI_ENGINE_VERSION;
echo 'Path: ' . CHATBOT_AI_ENGINE_PATH;
```

---

## 📦 Обекти и класове

### Главния клас

```php
class Chatbot_AI_Engine {

    /**
     * Получи инстанция
     */
    public static function get_instance() { }

    /**
     * Активация
     */
    public function activate() { }

    /**
     * Деактивация
     */
    public function deactivate() { }

    /**
     * Регистрирай настройки
     */
    public function register_settings() { }

    /**
     * Обработи AJAX съобщение
     */
    public function handle_ajax_message() { }

    /**
     * Позови AI API
     */
    private function call_ai_api( $user_message, $system_prompt, $settings ) { }
}
```

**Достъп до инстанцია:**
```php
$plugin = Chatbot_AI_Engine::get_instance();
```

---

## 🧪 Пример за разширяващ плъгин

### Дърво на файлове:

```
wp-content/plugins/chatbot-ai-engine-extension/
├── chatbot-ai-engine-extension.php
└── src/
    └── class-extension.php
```

### Основния файл (chatbot-ai-engine-extension.php):

```php
<?php
/**
 * Plugin Name: Chatbot AI Engine - Extension
 * Plugin URI: https://example.com
 * Description: Extends Chatbot AI Engine
 * Version: 1.0.0
 * Author: Your Name
 * License: GPL v2
 * Text Domain: chatbot-ai-engine-ext
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once __DIR__ . '/src/class-extension.php';

// Инициализирай
add_action( 'plugins_loaded', function() {
    if ( class_exists( 'Chatbot_AI_Engine' ) ) {
        new Chatbot_AI_Engine_Extension();
    }
} );
```

### Разширяващия клас (src/class-extension.php):

```php
<?php

class Chatbot_AI_Engine_Extension {

    public function __construct() {
        $this->init_hooks();
    }

    private function init_hooks() {
        // Филтри
        add_filter( 'chatbot_ai_engine_api_response', array( $this, 'modify_response' ) );
        add_filter( 'chatbot_ai_engine_system_prompt', array( $this, 'modify_prompt' ) );

        // Действия
        add_action( 'chatbot_ai_engine_after_api_call', array( $this, 'log_conversation' ), 10, 2 );
    }

    public function modify_response( $response ) {
        return $response;
    }

    public function modify_prompt( $prompt ) {
        return $prompt;
    }

    public function log_conversation( $response, $user_message ) {
        // Логирай разговора
    }
}
```

---

## 📚 Допълнителни ресурси

- [WordPress Plugin Handbook](https://developer.wordpress.org/plugins/)
- [WordPress Filters & Hooks](https://developer.wordpress.org/plugins/hooks/)
- [WordPress REST API](https://developer.wordpress.org/rest-api/)

---

**Версия:** 1.0.0
**Последна актуализация:** 14 март 2024
