# 🛠️ Отстраняване на грешки - Chatbot AI Engine

## Съдържание

1. [Чатбота не се показва](#чатбота-не-се-показва)
2. [API грешки](#api-грешки)
3. [Съобщения за грешки](#съобщения-за-грешки)
4. [Перформанс проблеми](#перформанс-проблеми)
5. [Безопасност проблеми](#безопасност-проблеми)
6. [Браузърни конфликти](#браузърни-конфликти)
7. [Лог файлове](#лог-файлове)
8. [FAQ](#faq)

---

## 👻 Чатбота не се показва

### Проблем 1: Мехурчето не е видимо

**Симптоми:**
- На страницата няма синия мехурче
- Конзолата е чиста (F12)

**Решение - Стъпка 1: Проверете активацията**

```bash
# Проверете дали плъгина е активиран
wp plugin status chatbot-ai-engine
```

**Если резултат е "Plugin chatbot-ai-engine is not installed":**
- Плъгина не е инсталиран
- Вижте [INSTALLATION.md](./INSTALLATION.md)

**Ако резултат е "Plugin chatbot-ai-engine is inactive":**
- Активирайте плъгина

```bash
wp plugin activate chatbot-ai-engine
```

---

**Решение - Стъпка 2: Проверете настройките**

```bash
# Проверете дали е включено
wp option get chatbot_ai_engine_settings --format=json | grep enabled
```

**Ако резултат е `"enabled":"0"`:**
1. Отидете в **WordPress Admin → AI Chatbot**
2. Отметнете полето **"Enable Chatbot"**
3. Кликнете на **"Save Changes"**

---

**Решение - Стъпка 3: Проверете конзолата**

1. Отворете браузъра в страницата
2. Натиснете **F12** за разработчикски инструменти
3. Отидете в таб **"Console"**
4. Проверете за Червени грешки

**Ако видите грешка:**
```javascript
// Пример грешка:
GET /wp-content/plugins/chatbot-ai-engine/assets/script.js 404

// Решение: Файлът липсва
// Преинсталирайте плъгина
```

---

### Проблем 2: Прозорецът се отваря, но няма съдържание

**Симптоми:**
- Мехурчето е видимо
- Кликам на него, но не вижда ничего

**Причины:**
1. CSS файл не е зареден
2. JavaScript грешка
3. Липсва DOM елемент

**Решение:**

```javascript
// Отворете конзолата (F12 → Console)
// Напишете:
console.log( document.getElementById('chatbot-ai-engine-window') );

// Ако резултат е null:
// DOM елемента не е създаден
// Проверете браузъра на JavaScript грешки
```

Проверете CSS:

```javascript
// Отворете конзолата (F12 → Console)
const window = document.getElementById('chatbot-ai-engine-window');
console.log( getComputedStyle(window).display );

// Резултат трябва да бъде "flex" или "block"
// Ако е "none", CSS е скрито
```

---

## 🔗 API грешки

### Грешка 1: "Chatbot is not properly configured"

**Симптоми:**
- При написване на съобщение см съобщение за грешка
- Конзола показва: "Chatbot is not properly configured"

**Причины:**
- Липсва API ключ
- Липсва API URL
- Липсва име на модел

**Решение:**

1. Отидете в **Admin → AI Chatbot**
2. Проверете следните полета:
   - [ ] API Key - попълнено ли е?
   - [ ] API URL - попълнено ли е?
   - [ ] Model Name - попълнено ли е?

3. Ако някое поле е празно, попълнете го
4. Кликнете "Save Changes"

---

### Грешка 2: "401 Unauthorized" или "Invalid API Key"

**Симптоми:**
- Съобщение: "Error 401" или "Unauthorized"
- Конзола показва API грешка

**Причины:**
- API ключа е неправилен
- API ключа е изтекъл
- API ключа е за грешния акаунт

**Решение:**

```bash
# Проверете API ключа
wp option get chatbot_ai_engine_settings --format=json | grep api_key
```

**За OpenAI:**
1. Посетете https://platform.openai.com/api-keys
2. Проверете дали ключа е още активен
3. Ако е "Expired", генерирайте нов ключ
4. Обновете ключа в админ панела

**За Groq:**
1. Посетете https://console.groq.com/keys
2. Проверете дали ключа е правилен
3. Вставете новия ключ в админ панела

---

### Грешка 3: "404 Not Found" - API endpoint

**Симптоми:**
- Съобщение: "Error 404"
- Конзола показва URL грешка

**Причины:**
- API URL е неправилен
- API е했여이모리없는 доставчик

**Решение:**

Проверете API URL:

```bash
# За OpenAI (правилен)
https://api.openai.com/v1/chat/completions

# За Groq (правилен)
https://api.groq.com/openai/v1/chat/completions

# За Anthropic (правилен)
https://api.anthropic.com/v1/messages
```

Тестирайте URL със curl:

```bash
curl -i -H "Authorization: Bearer YOUR_API_KEY" \
     -H "Content-Type: application/json" \
     https://api.openai.com/v1/models
```

Ако получавате 400, URL е грешен.

---

### Грешка 4: "CORS Error" при API заявка

**Симптоми:**
- Конзола показва: "Access to XMLHttpRequest blocked by CORS policy"
- Съобщението се изпраща, но няма отговор

**Причини:**
- API не поддържа CORS
- Браузърен домейн блокиран

**Решение:**

Това е нормално за frontend запросите. Решението е:

1. **Използвайте proxy на backend**

Добавете в вашия `functions.php`:

```php
add_action( 'wp_ajax_chatbot_send_message', function() {
    // Вече имаме AJAX - текущия плъгин го направи!
    // Просто проверете че комуникацията е от backend
} );
```

2. **За CORS нарушения:**
   - Някой AI доставчици имат CORS ограничения
   - Това е нормално - плъгина използва backend AJAX

---

## ❌ Съобщения за грешки

### "Fatal error: Allowed memory exceeded"

**Решение:**

```php
// wp-config.php
define( 'WP_MEMORY_LIMIT', '256M' );
define( 'WP_MAX_MEMORY_LIMIT', '512M' );
```

Vagy чрез `.htaccess`:

```apache
php_value memory_limit 256M
php_value max_execution_time 300
```

---

### "Call to undefined function"

**Решение:**

```bash
# Проверете дали wp-load.php е зареден
wp cli version

# Если wp не е разпознават:
cd /path/to/wordpress
wp plugin activate chatbot-ai-engine
```

---

### "MySQL Gone Away"

**Симптоми:**
- "MySQL has gone away" грешка
- При дълги API заявки

**Решение:**

```php
// wp-config.php
define( 'MYSQL_CLIENT_FLAGS', MYSQLI_CLIENT_INTERACTIVE );

// Или увеличете timeout
define( 'MYSQL_QUERY_TIMEOUT', 300 );
```

Също така, увеличете максималното време обработка:

```bash
# .htaccess
php_value max_execution_time 300
```

---

## ⚡ Перформанс проблеми

### Проблем 1: API е бавна

**Симптоми:**
- Отговорот отнема много време
- Потребителя чака над 10 секунди

**Причини:**
- API е натоварена
- Максимални токени е твърде висока
- Model е твърде мощен

**Решение:**

1. **Намалете максималните токени:**
   - Admin → AI Chatbot
   - Max Tokens: 500-1000 вместо 4000

2. **Използвайте по-бърз модел:**
   - gpt-3.5-turbo е по-бързо от gpt-4
   - mixtral-8x7b-32768 на Groq е много бързо

3. **Увеличете timeout:**
```php
// wp-config.php
define( 'CHATBOT_API_TIMEOUT', 60 ); // 60 секунди
```

---

### Проблем 2: JavaScript мбавен/замръзен

**Симптоми:**
- Браузъра е мрязък при отваряне на чатбота
- Целия сайт се забавя

**Причини:**
- Кеш позхеме
- Конфликт със други плъгини

**Решение:**

1. **Изчистете кеша:**
```bash
wp cache flush
wp cache flush plugins
```

2. **Проверете за конфликти:**
   - Деактивирайте всички други плъгини
   - Активирайте ChatBot
   - Проверете дали е по-бързо
   - Активирайте други плъгини един по един

3. **Оптимизирайте JavaScript:**
   - Използвайте кеш бюстер
   - Минифицирайте JS

---

### Проблем 3: Съхранението на браузър е пълно

**Симптоми:**
- Съобщение за грешка при запазване
- localStorage предупреждение

**Решение:**

```javascript
// Изчистете старите съобщения
localStorage.clear();

// Или селективно:
Object.keys(localStorage).forEach(key => {
    if (key.startsWith('chatbot-ai-engine')) {
        localStorage.removeItem(key);
    }
});
```

---

## 🔒 Безопасност проблеми

### Проблем 1: API ключ е видим

**Симптоми:**
- API ключ се вижда в HTML/JavaScript
- XSS уязвимост

**Решение:**

API ключът НЕ трябва да бъде на фронтенда. Проверете:

```bash
# Проверете дали ключа е експониран
grep -r "api_key" /wp-content/plugins/chatbot-ai-engine/assets/
```

Ако видите ключ, НЕ трябва да бъде там!

**Правилно решение:**
- Ключа е само на backend (в WordPress опции)
- Фронтенда комуникира чрез AJAX
- Backend обработва API запросите

---

### Проблем 2: SQL Injection

**Симптоми:**
- Странни символи в база данни
- Данни бяха модифицирани без причина

**Решение:**

Плъгина използва `sanitize_* ` функции, но ако разширявате:

```php
// ❌ Неправилно:
$query = "SELECT * FROM table WHERE id = " . $_GET['id'];

// ✅ Правилно:
$query = $wpdb->prepare(
    "SELECT * FROM table WHERE id = %d",
    $_GET['id']
);
```

---

### Проблем 3: XSS (Cross-Site Scripting)

**Симптоми:**
- Скрипти се изпълняват вътре в чатбота
- Потребителски входи показват странен HTML

**Решение:**

Плъгина екранира всички изходи, но ако разширявате:

```php
// ❌ Неправилно:
echo $_POST['message'];

// ✅ Правилно:
echo wp_kses_post( $message );
echo esc_html( $message );
echo esc_attr( $message );
```

---

## 🌐 Браузърни конфликти

### Проблем 1: jQuery конфликт

**Симптоми:**
- JavaScript грешка: "$ is not defined"
- Чатбота не работи

**Решение:**

Плъгина използва Vanilla JS, но ако имате jQuery конфликт:

```javascript
// Проверете дали jQuery е зареден
console.log( typeof jQuery );

// Ако резултат е "undefined", jQuery не е зареден
// Проверете други плъгини
```

---

### Проблем 2: CSS конфликт

**Симптоми:**
- Чатбота изглежда странно
- Бутони не работят
- Цветовете са неправилни

**Решение:**

1. **Отворете DevTools (F12)**
2. **Инспектирайте елемента**
3. **Проверете кой CSS го пречи**

```css
/* Проверете за conflicting стилове */
#chatbot-ai-engine-window {
    /* Вашия стил може да овърайд */
}
```

**Решение със specificity:**
```css
/* Добавете !important (последна мярка) */
#chatbot-ai-engine-window {
    background: white !important;
}
```

---

### Проблем 3: Z-index конфликт

**Симптоми:**
- Чатбота е скрит зад други елементи
- Мехурчето е невидимо

**Решение:**

```css
/* Увеличете z-index */
#chatbot-ai-engine-container {
    z-index: 999999 !important;
}
```

---

## 📋 Лог файлове

### Проверка на WordPress логовете

```bash
# Read последние 50 записа
tail -50 /wp-content/debug.log

# Или чрез WordPress CLI
wp debug log tail

# Проверете за конкретни грешки
grep -i "chatbot" /wp-content/debug.log
```

### PHP логовете

```bash
# Apache
tail -50 /var/log/apache2/error.log

# Nginx
tail -50 /var/log/nginx/error.log

# PHP-FPM
tail -50 /var/log/php-fpm.log
```

### Включете системния лог

```php
// wp-config.php
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );
define( 'SCRIPT_DEBUG', true );
```

---

## ❓ FAQ

### В: Как да проверя дали API работи?

**О:** Използвайте curl:

```bash
curl -H "Authorization: Bearer YOUR_API_KEY" \
     -H "Content-Type: application/json" \
     -d '{"model":"gpt-3.5-turbo","messages":[{"role":"user","content":"Hi"}],"max_tokens":100}' \
     https://api.openai.com/v1/chat/completions
```

---

### В: Защо чатбота е бавен?

**О:** Проверете:
1. Скорост на интернета
2. Натоварка на API
3. Max tokens настройка
4. Model избор

---

### В: Мога ли да ограничи кой вижда чатбота?

**О:** Да! Използвайте филтър:

```php
add_filter( 'chatbot_ai_engine_display_bubble', function( $display ) {
    // Покажи само за автори
    return current_user_can( 'edit_posts' );
} );
```

---

### В: Мога ли да запазя чатова история?

**О:** Да! Вижте [DEVELOPMENT.md](./DEVELOPMENT.md) за пример

---

### В: Как да променя цвета на чатбота?

**О:** Вижте [CUSTOMIZATION.md](./CUSTOMIZATION.md)

---

### В: Чатбота работи в админ панела?

**О:** НЕ намерено. Чатбота се показва само на фронтенда

---

### В: Мога ли да используемуют поделени API ключове?

**О:** НЕ препоръчвам! Всеки сайт трябва собствен ключ

---

### В: Как да получа технична поддръжка?

**О:** Проверете:
1. Документация в [README.md](./README.md)
2. [CONFIGURATION.md](./CONFIGURATION.md)
3. Оставете GitHub issue

---

## 🆘 Критични проблеми

### Белия екран

```bash
# Включете DEBUG режима
wp config set WP_DEBUG true --raw

# Проверете логовете
tail -100 /wp-content/debug.log
```

---

### База данни е недостъпна

```bash
# Проверете MySQL свързване
wp db cli

# Или
mysql -u username -p database_name
```

---

### Файлови разрешения

```bash
# Проверете разрешения
ls -la /wp-content/plugins/chatbot-ai-engine/

# Поправете разрешения
chmod 755 /wp-content/plugins/chatbot-ai-engine/
chmod 644 /wp-content/plugins/chatbot-ai-engine/*.php
```

---

## 📞 Получаване на помощ

Ако проблемът персистира:

1. **Съберете информация:**
   - WordPress версия: `wp core version`
   - PHP версия: `php -v`
   - Плъгин версия: Че Admin → AI Chatbot

2. **Проверете документацията:**
   - Технотеген текущото este руководство
   - Проверете CONFIGURATION.md

3. **Отворете GitHub issue:**
   - Дайте много детайли
   - Включете логовете
   - Опишете стъпките за приложение

---

**Версия:** 1.0.0
**Последна актуализация:** 14 март 2024
