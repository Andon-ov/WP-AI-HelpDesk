# 👨‍💻 Разработка - Chatbot AI Engine

## Съдържание

1. [Архитектура](#архитектура)
2. [Структура на плъгина](#структура-на-плъгина)
3. [Развойна среда](#развойна-среда)
4. [Разширяване на функциите](#разширяване-на-функциите)
5. [Тестване](#тестване)
6. [Beste практики](#beste-практики)
7. [Debugging](#debugging)

---

## 🏗️ Архитектура

### Дизайн модел:

```
┌─────────────────────────────────────┐
│   WordPress Admin Panel              │
│   (Settings & Configuration)         │
└────────────┬────────────────────────┘
             │
             ▼
┌─────────────────────────────────────┐
│   Chatbot_AI_Engine (Main Class)    │
│   - Hooks                            │
│   - Settings Registration            │
│   - AJAX Handler                     │
└────────────┬────────────────────────┘
             │
      ┌──────┴──────┐
      │             │
      ▼             ▼
┌──────────────┐ ┌──────────────────┐
│ Frontend     │ │ AI API Interface │
│ (JS + CSS)   │ │ (OpenAI, Groq..) │
└──────────────┘ └──────────────────┘
```

---

## 📁 Структура на плъгина

### Файлова организация:

```
chatbot-ai-engine/
├── chatbot-ai-engine.php          # Главния файл на плъгина
├── assets/
│   ├── script.js                  # JavaScript за фронтенда
│   └── style.css                  # CSS стилове
├── languages/                     # За преводи
│   └── chatbot-ai-engine.pot      # POT файл за преводи
├── includes/                      # (За бъдеща експанзия)
│   ├── class-settings.php
│   ├── class-api-client.php
│   └── class-chat-history.php
└── README.md                      # Документация
```

### Рекомендирана структура за растеж:

```
chatbot-ai-engine/
├── chatbot-ai-engine.php
├── assets/
│   ├── js/
│   │   ├── admin.js               # Admin JavaScript
│   │   └── frontend.js            # Frontend JavaScript
│   ├── css/
│   │   ├── admin.css              # Admin стилове
│   │   └── frontend.css           # Frontend стилове
│   └── images/
│       └── icon.svg               # Иконка на плъгина
├── includes/
│   ├── class-chatbot-ai-engine.php    # Главина клас
│   ├── class-api-handler.php           # API логика
│   ├── class-message-handler.php       # Съобщение логика
│   ├── class-settings.php              # Настройки
│   └── functions.php                   # Помощни функции
├── admin/
│   ├── class-admin-settings.php        # Admin настройки
│   └── views/
│       └── settings-page.php           # HTML за настройки
├── templates/
│   └── chatbot-widget.php              # Widget шаблон
├── tests/
│   ├── test-api-handler.php
│   └── test-message-handler.php
├── languages/
│   ├── chatbot-ai-engine.pot
│   ├── chatbot-ai-engine-bg_BG.po
│   └── chatbot-ai-engine-bg_BG.mo
├── readme.txt                     # WordPress readme
├── README.md                      # GitHub readme
└── CHANGELOG.md                   # История на změн
```

---

## 🛠️ Развойна среда

### Локално развитие:

#### Инструменти:

```bash
# WordPress е необходимо
WordPress 5.0+
PHP 7.4+
MySQL 5.7+ / MariaDB 10.2+
```

#### Настройка:

```bash
# Клонирайте или изтеглите плъгина
cd wp-content/plugins/
git clone https://github.com/your-repo/chatbot-ai-engine.git

# Инсталирайте зависимости
composer install        # Ако използвате Composer
npm install            # Ако използвате NPM
```

---

### Docker развойна среда:

```dockerfile
# Dockerfile за развитие
FROM wordpress:php7.4-apache

# Инсталирайте допълнителни пакети
RUN apt-get update && apt-get install -y vim curl git

# Копирайте плъгина
COPY ./chatbot-ai-engine /var/www/html/wp-content/plugins/chatbot-ai-engine

WORKDIR /var/www/html

# Инициализирайте WordPress
CMD ["apache2-foreground"]
```

```bash
# Начало
docker-compose up
```

---

### WordPress Debugging:

```php
// wp-config.php
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );

// Логирай всички грешки, уведомления и предупреждения
define( 'ERROR_REPORTING', E_ALL );
```

---

## 🔧 Разширяване на функциите

### Пример 1: Добавяне на регистрирана история на чатовете

**Файл:** `includes/class-chat-history.php`

```php
<?php

class Chatbot_AI_Engine_Chat_History {

    private $table_name;

    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'chatbot_conversations';
        $this->init();
    }

    public function init() {
        add_action( 'chatbot_ai_engine_after_api_call', array( $this, 'save_conversation' ), 10, 2 );
    }

    public function create_table() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS {$this->table_name} (
            id BIGINT(20) NOT NULL AUTO_INCREMENT,
            user_id BIGINT(20),
            user_message TEXT NOT NULL,
            bot_response TEXT NOT NULL,
            timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY timestamp (timestamp)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );
    }

    public function save_conversation( $response, $user_message ) {
        global $wpdb;

        $wpdb->insert(
            $this->table_name,
            array(
                'user_id'      => get_current_user_id(),
                'user_message' => $user_message,
                'bot_response' => $response,
            ),
            array( '%d', '%s', '%s' )
        );
    }

    public function get_user_conversations( $user_id, $limit = 50 ) {
        global $wpdb;

        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$this->table_name} WHERE user_id = %d ORDER BY timestamp DESC LIMIT %d",
                $user_id,
                $limit
            )
        );
    }
}
```

**Използване:**
```php
add_action( 'plugins_loaded', function() {
    require_once CHATBOT_AI_ENGINE_PATH . 'includes/class-chat-history.php';
    new Chatbot_AI_Engine_Chat_History();
} );
```

---

### Пример 2: Интеграция на RAG (Retrieval-Augmented Generation)

**Файл:** `includes/class-rag-handler.php`

```php
<?php

class Chatbot_AI_Engine_RAG_Handler {

    private $vector_store;

    public function __construct() {
        $this->init();
    }

    public function init() {
        add_filter( 'chatbot_ai_engine_system_prompt', array( $this, 'enhance_prompt_with_context' ) );
        add_filter( 'chatbot_ai_engine_user_message', array( $this, 'add_context_to_message' ) );
    }

    /**
     * Добавете контекст към съобщението от вашата база данни
     */
    public function add_context_to_message( $message ) {
        if ( function_exists( 'wp_kses_post' ) ) {
            // Потърсите в вашия вектор хранилище
            $relevant_docs = $this->search_vector_store( $message, 3 );

            if ( ! empty( $relevant_docs ) ) {
                $context = "Related information:\n";
                foreach ( $relevant_docs as $doc ) {
                    $context .= "- " . $doc['content'] . "\n";
                }

                $message = $context . "\n\nUser question: " . $message;
            }
        }

        return $message;
    }

    /**
     * Потърсете вектор хранилище (пример)
     */
    private function search_vector_store( $query, $limit = 3 ) {
        // Това е база за интеграция с външно вектор хранилище
        // Например: Pinecone, Weaviate, Milvus и т.н.

        return array();
    }

    public function enhance_prompt_with_context( $prompt ) {
        $prompt .= " Use the provided context to answer questions.";
        return $prompt;
    }
}
```

---

### Пример 3: Кастомни команди

**Файл:** `includes/class-command-handler.php`

```php
<?php

class Chatbot_AI_Engine_Command_Handler {

    public function __construct() {
        add_filter( 'chatbot_ai_engine_api_response', array( $this, 'process_commands' ) );
    }

    public function process_commands( $message ) {
        // Проверете за специални команди
        if ( strpos( $message, '/help' ) === 0 ) {
            return $this->show_help();
        }

        if ( strpos( $message, '/time' ) === 0 ) {
            return $this->get_current_time();
        }

        if ( strpos( $message, '/weather' ) === 0 ) {
            return $this->get_weather();
        }

        return $message;
    }

    private function show_help() {
        return "Available commands:\n/help - Show this message\n/time - Get current time\n/weather - Get weather";
    }

    private function get_current_time() {
        return "Current time: " . date( 'Y-m-d H:i:s' );
    }

    private function get_weather() {
        return "Weather functionality would go here.";
    }
}
```

---

## 🧪 Тестване

### Unit тестване с PHPUnit:

**Файл:** `tests/test-api-handler.php`

```php
<?php

class Test_API_Handler extends WP_UnitTestCase {

    public function test_api_connection() {
        // Тест за API връзката
        $settings = array(
            'api_provider' => 'openai',
            'api_url'      => 'https://api.openai.com/v1/chat/completions',
            'api_key'      => 'test-key',
            'model'        => 'gpt-3.5-turbo',
        );

        $this->assertNotEmpty( $settings['api_key'] );
        $this->assertStringContains( 'openai', $settings['api_provider'] );
    }

    public function test_message_sanitization() {
        // Тест за санитизация
        $message = '<script>alert("xss")</script>Hello';
        $sanitized = sanitize_text_field( $message );

        $this->assertNotContains( '<script>', $sanitized );
    }
}
```

**Изпълнение:**
```bash
phpunit tests/ --colors
```

---

### JavaScript тестване:

**Файл:** `tests/test-frontend.js`

```javascript
describe('ChatbotAIEngine', function() {
    it('should initialize', function() {
        expect(ChatbotAIEngine).toBeDefined();
    });

    it('should send message', function(done) {
        ChatbotAIEngine.sendToServer('test message');
        setTimeout(() => {
            expect(ChatbotAIEngine.state.messages.length).toBeGreaterThan(0);
            done();
        }, 1000);
    });
});
```

**Изпълнение:**
```bash
npm test
```

---

## ✅ Beste практики

### 1. WordPress Coding Standards

```php
✅ Правилно:
add_action( 'wp_enqueue_scripts', function() {
    wp_enqueue_script( 'my-script', CHATBOT_AI_ENGINE_URL . 'assets/script.js' );
} );

❌ Неправилно:
<script src="<?php echo CHATBOT_AI_ENGINE_URL; ?>assets/script.js"></script>
```

---

### 2. Безопасност

```php
✅ Правилно:
$message = sanitize_text_field( $_POST['message'] );
check_ajax_referer( 'nonce_name', 'nonce_field' );
wp_verify_nonce( $_REQUEST['_wpnonce'], 'action' );

❌ Неправилно:
$message = $_POST['message'];
// Без nonce проверка
```

---

### 3. Производство

```php
✅ Правилно:
if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
    error_log( 'Debug message' );
}

❌ Неправилно:
echo 'Debug output';
var_dump( $data );
```

---

### 4. Документация

```php
✅ Правилно:
/**
 * Process user message
 *
 * @param string $message User message
 * @return string Processed message
 */
public function process_message( $message ) {
    // код
}

❌ Неправилно:
function process_message( $message ) {
    // код
}
```

---

## 🔍 Debugging

### WordPress Debug Mode

```php
// wp-config.php
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );
```

**Проверете логовете:**
```bash
tail -f /wp-content/debug.log
```

---

### JavaScript Debug

```javascript
// Отворете конзолата в браузъра (F12)
console.log('Message:', message);
debugger; // Спиране при тази точка
```

---

### PHP Debugging с XDebug

```bash
# Инсталирайте XDebug
pecl install xdebug

# Конфигурирайте php.ini
xdebug.mode=debug
xdebug.client_host=127.0.0.1
xdebug.client_port=9003
```

---

### Временни отпечатъци и профилиране

```php
// Измерете времето на изпълнение
$start = microtime( true );

// Вашия код тук
call_ai_api( $message, $prompt, $settings );

$end = microtime( true );
$duration = ( $end - $start ) * 1000; // ms

error_log( 'API call took ' . $duration . 'ms' );
```

---

## 📦 Версиониране

Следвайте Semantic Versioning:

```
Версия: MAJOR.MINOR.PATCH
Пример: 1.2.3

MAJOR:  Пробивни промени
MINOR:  Нови функции
PATCH:  Фикс на дефекти
```

---

## 📝 Издавче и разпространение

### WordPress Plugin Directory:

1. Подгответе плъгина
2. Тестирайте完全
3. Подайте на plugins.wordpress.org
4. Чакайте на одобрение
5. Получите хранилище

---

### GitHub Releases:

```bash
git tag -a v1.0.0 -m "Version 1.0.0"
git push origin v1.0.0
```

---

## 🚀 Пътна карта на развитие

### Версия 1.0.0 (Текущ)
- ✅ Базова функционалност
- ✅ BYOK поддръжка
- ✅ Множество доставчици
- ✅ Базова документация

### Версия 1.1.0 (Планирано)
- 🔲 История на чатовете
- 🔲 Потребителски преводи
- 🔲 Расширена персонализация

### Версия 1.2.0 (Планирано)
- 🔲 RAG интеграция
- 🔲 Embeddings поддръжка
- 🔲 Кастомни команди

### Версия 2.0.0 (Му

лтипроцесен)
- 🔲 REST API
- 🔲 Блок Gutenberg
- 🔲 Админ дашборд
- 🔲 Аналитика

---

## 🤝 Допринос

### За разработчици:

1. Раздвоете проекта
2. Създайте feature branch (`git checkout -b feature/xyz`)
3. Направете промени
4. Напишете тестове
5. Направете commit
6. Натиснете на GitHub
7. Отворете Pull Request

---

## 📚 Допълнителни ресурси

- [WordPress Plugin Handbook](https://developer.wordpress.org/plugins/)
- [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/)
- [PHP Best Practices](https://www.php.net/manual/en/)
- [JavaScript Best Practices](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Guide)

---

**Версия:** 1.0.0
**Последна актуализация:** 14 март 2024
