# 🗺️ Пълен план на проекта: WP-AI-HelpDesk (Chatbot AI Engine)

**GitHub:** https://github.com/Andon-ov/WP-AI-HelpDesk  
**Текуща версия:** 1.2.0  
**Последна актуализация:** 15 март 2026  
**Автор:** Andon-ov  

---

## 1. 🎯 Какво прави плъгинът

WordPress плъгин за AI чатбот с BYOK (Bring Your Own Key) модел. Администраторът въвежда собствен API ключ от избран AI доставчик. Ботът скенира съдържанието на сайта, запазва го в JSON файл и използва него като контекст при всеки разговор.

**Тестван на:** chefandgastro.com (образователна платформа за гастрономия)  
**Вторична цел:** Да работи и за clover-shop.run.place (WooCommerce магазин)

---

## 2. 🏗️ Файлова структура

```
chatbot-ai-engine/
├── chatbot-ai-engine.php        # Главен файл — цялата PHP логика
├── assets/
│   ├── script.js                # Frontend JavaScript — UI и AJAX
│   └── style.css                # CSS стилове на чат widget-а
├── includes/
│   └── helper-functions.php     # Помощни функции (get_, is_, should_)
├── smoke-test.php               # Диагностичен скрипт (НЕ за production!)
└── languages/                   # Папка за преводи (празна в момента)
```

### ВАЖНО: smoke-test.php
Файлът е в корена на плъгина. Пътят `require_once('../../../wp-load.php')` предполага структура `/wp-content/plugins/chatbot-ai-engine/smoke-test.php`. Трябва да се достъпва директно в браузъра само от admin потребител.

---

## 3. 🔧 Техническа архитектура

### 3.1 PHP класове и методи (chatbot-ai-engine.php)

**Клас:** `Chatbot_AI_Engine` (Singleton pattern)

| Метод | Какво прави | Проблеми |
|-------|-------------|----------|
| `activate()` | Записва default settings при активиране | Не презаписва съществуващи settings |
| `sanitize_settings()` | Почиства и валидира входните данни от Admin | Lipsa на validation за temperature при non-Groq providers |
| `enqueue_frontend_assets()` | Зарежда JS/CSS и праща config към JS | `isAdminBar` е добавен, `welcomeMessage` е добавен |
| `handle_ajax_message()` | Обработва съобщенията от чата | INIT_GREETING е премахнат в новата версия |
| `get_site_context()` | Търси в JSON индекса и връща контекст | **КРИТИЧЕН БУГ:** при empty result връща "" вместо насочване |
| `sync_site_knowledge()` | Скенира сайта и записва JSON файла | **КРИТИЧЕН БУГ:** продуктите не се индексират правилно |
| `call_ai_api()` | Прави HTTP request към AI provider | nl2br + white-space:pre-wrap = двойни празни редове |
| `log_conversation()` | Записва разговорите в CSV файл | Добавен в v1.2.0, липсва header row при нов файл |
| `render_shortcode()` | [chatbot] shortcode | Непълна имплементация, toggleWindow не е дефиниран глобално |

### 3.2 JavaScript (script.js)

**Структура:** IIFE (Immediately Invoked Function Expression), обект `ChatbotAIEngine`

| Метод | Какво прави |
|-------|-------------|
| `init()` | Инициализира session ID, DOM, зарежда история, показва greeting |
| `createDOM()` | Програмно генерира целия HTML на чата |
| `bindEvents()` | Закача event listeners |
| `loadMessages()` | Зарежда история от localStorage |
| `toggleWindow()` | Отваря/затваря прозореца |
| `clearHistory()` | Изтрива историята с confirm() |
| `sendMessage()` | Взима input, праща към PHP AJAX |
| `addMessage()` / `renderMessage()` | Рендира съобщение в DOM |
| `showLoading()` / `removeLoading()` | Анимиран typing индикатор |
| `saveMessages()` | Записва в localStorage |

**Ключови данни от PHP → JS (wp_localize_script):**
```javascript
chatbotAIEngine = {
    ajaxUrl: 'https://site.com/wp-admin/admin-ajax.php',
    nonce: 'xxxxx',
    position: 'bottom-right',
    isAdminBar: true/false,
    i18n: {
        placeholder: 'Пишете съобщение...',
        send: 'Изпрати',
        loading: '...',
        error: 'Грешка',
        chatTitle: 'Chef & Gastro Assistant',
        closeChat: 'Затвори',
        goodbye: 'Заповядайте отново! 👋',
        welcomeMessage: 'Здравейте [Анdon]! С какво мога да помогна?'
    }
}
```

### 3.3 Knowledge Base система (Hybrid RAG)

**Стъпка 1 — Sync (ръчно от Admin):**
```
Admin натиска "Index Site Content Now"
→ AJAX: chatbot_sync_knowledge
→ sync_site_knowledge()
→ Скенира post types: post, page, product, wprm_recipe, glossary, tribe_events
→ За products: взима цена от _price meta (без WooCommerce)
→ Записва в /wp-content/uploads/chatbot-ai-knowledge.json
```

**Стъпка 2 — Търсене при всяко съобщение:**
```
Потребителят пише → handle_ajax_message()
→ get_site_context($msg)
→ Keyword extraction + Bulgarian stemming
→ Scoring: title match = +50, content = +15, tags = +30
→ Multi-keyword bonus: score *= hits
→ Top 5 резултата
→ Ако 0 резултата → WP_Query fulltext search (fallback)
→ Ако пак 0 → връща ""  ← БУГ: трябва да насочва към сайта
→ Контекстът се добавя към system prompt
→ call_ai_api()
```

**JSON структура на един запис:**
```json
{
    "id": 24741,
    "title": "Лекция: Какво всъщност е вкусът?",
    "type": "product",
    "content": "ЦЕНА: 9 EUR | Описание на курса...",
    "url": "https://chefandgastro.com/product/...",
    "tags": "курс рецепта обучение готвене цена абонамент"
}
```

### 3.4 Security

- **Nonce:** `chatbot_ai_engine_nonce` — верифициран при всеки AJAX
- **API Key криптиране:** AES-256-CBC с WordPress auth salt като ключ
- **Проблем:** Ако WordPress сайтът смени salt-а (напр. при хак или ръчна промяна на wp-config.php), API ключът се губи безвъзвратно
- **History sanitization:** `sanitize_textarea_field()` за всяко history съобщение, валидация на role (само 'user'/'assistant')

---

## 4. ⚙️ Admin настройки

Страница: **WordPress Admin → AI Chatbot**

| Поле | Опции | Default | Бележки |
|------|-------|---------|----------|
| Enable Chatbot | checkbox | off | |
| AI Provider | openai, groq, anthropic, gemini, custom | groq | При смяна автоматично сменя URL и model |
| API URL | text | auto | За Groq: https://api.groq.com/openai/v1/chat/completions |
| API Key | password | - | Криптиран при запис, показва ••• |
| Model Name | text | llama-3.3-70b-versatile | |
| Welcome Message | text | Здравейте{user_name}!... | {user_name} се replace-ва в PHP |
| System Prompt | textarea | виж по-долу | |
| Max Tokens | number | 1000 | |
| Temperature | number 0-2 | 0.5 | |
| Position | select | bottom-right | bottom-left, top-right, top-left |

**Текущ System Prompt (v1.2.0):**
```
Ти си официалният AI асистент на {site_name}.

ПРАВИЛА:
1. ПРИОРИТЕТ: Винаги проверявай секцията ИНФОРМАЦИЯ ОТ САЙТА.
2. ЛИНКОВЕ: Винаги давай пълни линкове от данните.
3. ПРИ ОТСЪСТВИЕ НА ДАННИ: Използвай общи знания, но насочи към сайта.
4. ТОН: Бъди професионален кулинарен експерт. Отговаряй на български.
```

**Препоръчан System Prompt за Chef & Gastro:**
```
Ти си AI експерт на "Chef & Gastro" (chefandgastro.com).

КАКВО ПРАВИШ:
- Намираш курсове, рецепти и статии от DATASET-а
- Обясняваш кулинарни техники и термини професионално
- Следиш контекста на разговора за логични отговори

ПРАВИЛА ЗА DATASET:
1. Ако в DATASET има курсове/продукти — ПОКАЖИ ГИ с цена и линк
2. Линкове в Markdown: [Заглавие](URL)
3. При празен DATASET — отговори от знанията си и насочи към:
   - Рецепти: [Рецепти](https://chefandgastro.com/рецепти/)
   - Магазин: [Магазин](https://chefandgastro.com/porachka/)
4. НИКОГА не измисляй цени или линкове извън DATASET
5. Само на български, кратко и ясно

ФУТЪР — добавяй САМО когато е релевантно:
- Курсове/обучение → "🎓 [Академия](https://academy.chefandgastro.com)"
- Рецепти → "📖 [Още рецепти](https://chefandgastro.com/рецепти/)"
```

---

## 5. 🤖 Поддържани AI Providers

| Provider | URL | Препоръчан модел | Бележки |
|----------|-----|-----------------|----------|
| Groq | https://api.groq.com/openai/v1/chat/completions | llama-3.3-70b-versatile | Безплатен, бърз, **текущо използван** |
| OpenAI | https://api.openai.com/v1/chat/completions | gpt-4o-mini | Платен |
| Anthropic | https://api.anthropic.com/v1/messages | claude-3-5-sonnet | Изисква различни headers — **НЕ е тестван** |
| Gemini | https://generativelanguage.googleapis.com/... | gemini-1.5-flash | Безплатен tier |
| Custom | всякакъв OpenAI-compatible URL | - | |

**Важно:** Anthropic използва различна API структура (x-api-key header, различен response format). В `call_ai_api()` НЕ е имплементирана специална обработка за Anthropic — ще гръмне.

---

## 6. 🐛 Известни бъгове (честен списък)

### КРИТИЧНИ:
1. **Продукти не се индексират** — JSON-ът на chefandgastro.com съдържа само `post` типове. Причина: WooCommerce не е зареден при AJAX sync. Fix: използвай `get_post_meta($id, '_price', true)` вместо `wc_get_product()`.

2. **nl2br + white-space:pre-wrap = двойни редове** — В `call_ai_api()` на ред 408 е `nl2br()`, а в CSS `white-space: pre-wrap`. Двете заедно дублират празните редове. Fix: премахни `nl2br()`.

3. **get_site_context() връща "" при 0 matches** — На ред 324 при липса на резултати се връща празен стринг. Ботът остава без контекст и халюцинира. Fix: върни инструкция "No data found, guide to site".

### СРЕДНИ:
4. **Greeting в history** — Welcome съобщението се включва в conversation history и ботът го "имитира" в следващия отговор. Fix: `slice(1)` при history generation.

5. **render_shortcode() е непълен** — `ChatbotAIEngine.toggleWindow()` не е публична функция, shortcode-ът не работи.

6. **log_conversation() без header row** — CSV логът няма заглавен ред при нов файл.

7. **Позициониране при admin bar** — Container-ът е `position: fixed`, прозорецът е `position: absolute`. При admin bar прозорецът може да излиза извън viewport.

### МАЛКИ:
8. **Temperature не се валидира per-provider** — Groq препоръчва max 0.5, но UI позволява до 2.0.

9. **localStorage bloat** — Историята расте неограничено. Трябва `slice(-20)` при save.

10. **`sanitize_text_field` за welcome_message** — Реже специални символи като emoji (👋).

---

## 7. ✅ Работещи функции (потвърдено)

- ✅ Groq API connectivity с llama-3.3-70b-versatile
- ✅ Conversation history (последните 6 съобщения)
- ✅ Greeting при първо отваряне (sessionStorage флаг)
- ✅ localStorage persistence между pages
- ✅ Clear history с confirm()
- ✅ Admin bar positioning
- ✅ Bulgarian stemming за keyword matching
- ✅ Stop words филтриране
- ✅ Multi-keyword scoring bonus
- ✅ WP_Query fallback при 0 JSON matches
- ✅ Markdown → HTML link conversion
- ✅ API key AES-256-CBC криптиране
- ✅ Nonce security
- ✅ Responsive (mobile fullscreen)
- ✅ Dark mode CSS support
- ✅ Conversation CSV logging

---

## 8. 📋 Pending задачи (приоритизирани)

### 🔴 Критично (трябва веднага):
1. **Fix sync за products** — замени WooCommerce функциите с директен `get_post_meta`
2. **Премахни nl2br()** от `call_ai_api()`
3. **Fix get_site_context()** — върни fallback text вместо ""

### 🟠 Важно (следваща версия):
4. **Slice history от индекс 1** — изключи greeting от history
5. **Оправи render_shortcode()** — направи `toggleWindow` публична
6. **Anthropic support** — добави специфичен handler за Anthropic API format
7. **Fix CSV header** в `log_conversation()`

### 🟡 Подобрения (бъдеще):
8. **Suggestion chips** — бутони с предварително зададени въпроси под greeting
9. **Admin analytics** — страница с CSV log viewer и статистики
10. **Auto-sync** — WP Cron за автоматичен sync при нов content
11. **Per-page context** — detect current page и подай само релевантни данни
12. **Rate limiting** — max X съобщения на час per IP
13. **Multi-instance** — различни конфигурации за различни сайтове чрез filters

---

## 9. 🚀 Инсталация от нула

### Изисквания:
- WordPress 5.0+
- PHP 7.4+ с OpenSSL extension
- MySQL 5.7+
- Groq API ключ (безплатен на console.groq.com)

### Стъпки:
1. Качи папката `chatbot-ai-engine` в `/wp-content/plugins/`
2. Структурата трябва да е: `chatbot-ai-engine/chatbot-ai-engine.php`
3. Активирай от WordPress Admin → Plugins
4. Отиди в Admin → AI Chatbot
5. Попълни: API Key (Groq), Model: `llama-3.3-70b-versatile`
6. Постави system prompt (виж секция 4)
7. Постави welcome message с `{user_name}` placeholder
8. Запази → Enable Chatbot ✓
9. Натисни **"Index Site Content Now"** и изчакай
10. Провери: `https://yoursite.com/wp-content/uploads/chatbot-ai-knowledge.json`
11. Трябва да виждаш JSON с products, posts, pages

### Верификация:
- Отвори `smoke-test.php` в браузъра като admin
- Трябва да виждаш ✅ за API key
- В секция 4 трябва да има WooCommerce продукти
- В секция 5 трябва да има `"type":"product"` в JSON

---

## 10. 🔄 Workflow за развитие

```
1. Промени кода локално
2. Промени версията: CHATBOT_AI_ENGINE_VERSION в chatbot-ai-engine.php
3. Build (ако има npm/webpack стъпка — в момента няма, просто копирай)
4. Качи файловете на сървъра чрез FTP/SFTP или Git
5. Hard refresh в браузъра: Ctrl+Shift+R
6. Тествай в чата
7. Провери /wp-content/debug.log за PHP грешки
```

**Версиониране при development:**
```php
// Ред 19 в chatbot-ai-engine.php:
define( 'CHATBOT_AI_ENGINE_VERSION', defined('WP_DEBUG') && WP_DEBUG ? time() : '1.2.0' );
// При WP_DEBUG=true → всяко зареждане е нова версия (без кеш)
// При production → фиксирана версия '1.2.0'
```

---

## 11. 📊 Текущо състояние на JSON индекса

**URL:** https://chefandgastro.com/wp-content/uploads/chatbot-ai-knowledge.json  
**Размер:** ~2MB  
**Записи:** ~2072  

| Тип | Брой | Проблем |
|-----|------|---------|
| post | 957 | ✅ Рецепти, статии — работят |
| page | 91 | ✅ Страниците — работят |
| product | 4 | ❌ Само 4 продукта, без цени |
| wprm_recipe | 320 | ✅ Рецепти с съставки |
| glossary | 583 | ✅ Лексикон |
| tribe_events | 5 | ⚠️ Събитията — цените са "Check Academy" |
| category | 112 | ✅ Категории |

**Продуктите на Chef & Gastro:**
- Лекция: Какво всъщност е вкусът? — 9 EUR
- Меню, което продава: Основи на ресторантския маркетинг — 200 EUR
- Chef & Gastro Club — 1 година — 49 EUR
- Chef & Gastro Club — 1 месец — 5 EUR

---

## 12. 💡 Архитектурни решения и защо

| Решение | Защо | Алтернатива |
|---------|------|-------------|
| JSON файл вместо DB таблица | По-прост deploy, достатъчно за <5000 записа | MySQL FULLTEXT при >20k записа |
| Keyword matching вместо embeddings | Безплатно, без допълнителен API | OpenAI embeddings (платено) |
| sessionStorage за greeting flag | Greeting се показва при всяка нова сесия | Никога повторно = по-лошо UX |
| localStorage за история | Персистентност без сървър | Server-side sessions (по-сложно) |
| AES-256-CBC за API key | WordPress стандарт | Plain text (небезопасно) |
| Singleton pattern за PHP клас | WordPress стандарт | Multiple instances (конфликти) |
