# ⚙️ Конфигурационен Наръчник - Chatbot AI Engine

## Съдържание

1. [Администрирането панел](#администрирането-панел)
2. [Основни настройки](#основни-настройки)
3. [Конфигурация на AI доставчици](#конфигурация-на-ai-доставчици)
4. [Разширени настройки](#разширени-настройки)
5. [Системен промпт примери](#системен-промпт-примери)
6. [Позиции на чатбота](#позиции-на-чатбота)
7. [Параметри на чатбота](#параметри-на-чатбота)
8. [Съхранение на настройки](#съхранение-на-настройки)

---

## 📱 Администрирането панел

### Достъп:

1. Логнете се в WordPress Admin
2. В левия меню кликнете на **AI Chatbot**
3. Ще видите формата с настройки

### Маркер на формата:

```
☐ Enable Chatbot (Включить чатбота)
├── AI Provider (Избор на доставчик)
├── API URL (API адрес)
├── API Key (API ключ)
├── Model Name (Име на модела)
├── System Prompt (Системен промпт)
├── Max Tokens (Максимални токени)
├── Temperature (Креативност)
└── Chatbot Position (Позиция)
```

---

## ✅ Основни настройки

### 1. Enable Chatbot (Включване/Отключване)

**Описание:** Активира или деактивира чатбота на сайта

**Опции:**
- ☐ Отключено (Чатбота не е видим)
- ☑ Включено (Чатбота е видим за всички)

**Стойност по подразбиране:** OFF (0)

**Примерна конфигурация:**
```
☑ Enable Chatbot → Включено
```

---

### 2. AI Provider (Доставчик на AI)

**Описание:** Избора на AI платформа

**Налични опции:**
| Опция | Опис | URL |
|-------|------|-----|
| **OpenAI** | ChatGPT, GPT-4 | `https://api.openai.com/v1/chat/completions` |
| **Groq** | Бързостен AI | `https://api.groq.com/openai/v1/chat/completions` |
| **Anthropic** | Claude модели | `https://api.anthropic.com/v1/messages` |
| **Custom** | Всяка OpenAI-съвместима API | Напълно персонализирана |

**Стойност по подразбиране:** OpenAI

**Примерна конфигурация:**
```
AI Provider: OpenAI ▼
```

**Динамична подмяна:**
При промяна на доставчика, API URL се обновява автоматично!

---

### 3. API URL (Адрес на API)

**Описание:** Пълния адрес на API endpoint

**Стойности по подразбиране:**

| Доставчик | API URL |
|-----------|---------|
| OpenAI | `https://api.openai.com/v1/chat/completions` |
| Groq | `https://api.groq.com/openai/v1/chat/completions` |
| Anthropic | `https://api.anthropic.com/v1/messages` |

**За Custom API:**
```
https://your-api.example.com/v1/chat/completions
```

**Примерна конфигурация (OpenAI):**
```
API URL: https://api.openai.com/v1/chat/completions
```

**Валидация:**
- ✅ Трябва да да е валидна URL адрес
- ✅ Трябва да начина с `https://`
- ✅ Се проверява при запазване

---

### 4. API Key (API Ключ)

**Описание:** Тайния ключ за аутентификация

**Как да намерите вашия ключ:**

#### За OpenAI:
1. Посетете https://platform.openai.com/api-keys
2. Кликнете на "Create new secret key"
3. Копирайте ключа (виждаща се само веднъж!)
4. Вставете в полето

#### За Groq:
1. Посетете https://console.groq.com/keys
2. Кликнете на "Create API Key"
3. Копирайте ключа
4. Вставете в полето

#### За Anthropic (Claude):
1. Посетете https://dashboard.anthropic.com/
2. Отидете в API keys
3. Кликнете на "Create Key"
4. Копирайте ключа

**Пример:**
```
API Key: sk-proj-1234567890abcdef... (видимо като парола)
```

**Безопасност:**
⚠️ **НИКОГА** не делитье вашия API ключ!
- Се съхранява защитено в базата данни
- Се показва като парола (точки) в админ панела
- Само администраторите могат да видят

---

### 5. Model Name (Име на модела)

**Описание:** Специфичния модел за използване

**За OpenAI:**
```
gpt-3.5-turbo       (Бързо, евтино)
gpt-4               (Мощно, по-скъпо)
gpt-4-turbo        (Барансира мощност и скорост)
```

**За Groq:**
```
mixtral-8x7b-32768
llama2-70b-4096
```

**За Anthropic:**
```
claude-3-opus
claude-3-sonnet
claude-3-haiku
```

**Примерна конфигурация:**
```
Model Name: gpt-3.5-turbo
```

**Совет:**
За начало използвайте `gpt-3.5-turbo` (бързо и евтино)

---

### 6. System Prompt (Системен промпт)

**Описание:** Инструкции за поведението на чатбота

**По подразбиране:**
```
You are a helpful assistant.
```

**Пример за различни разпоредби:**

#### 1. Общ асистент:
```
You are a helpful AI assistant. Answer questions clearly and concisely.
Be polite and respectful. If you don't know something, say so.
```

#### 2. Поддръжка чатбот:
```
You are customer support representative. Help customers with their issues.
Be professional and empathetic. Always offer solutions.
Ask clarifying questions if needed.
```

#### 3. Образователен asistant:
```
You are an educational tutor. Explain concepts clearly.
Use examples and analogies. Encourage learning.
Provide step-by-step explanations.
```

#### 4. Продажбен агент:
```
You are a sales representative. Help customers find products.
Describe benefits and features. Be enthusiastic.
Suggest relevant products based on needs.
```

#### За Болгарски чатбот:
```
Ти си полезен AI асистент. Отговаряй на въпросите ясно и кратко.
Бъди учтив и показвай уважение. Ако не знаеш нещо, кажи го.
Отговаряй на Български език.
```

**Примерна конфигурация:**
```
System Prompt: You are a friendly customer support assistant.
Help users with their questions about our products and services.
Be helpful, professional, and courteous at all times.
```

**Совет:** По-дълги и детайлни промптове дават по-добри резултати!

---

## 🤖 Конфигурация на AI доставчици

### OpenAI (По подразбиране)

**Стъпки за конфигурация:**

1. **Създайте профил:**
   - Посетете https://platform.openai.com
   - Регистрирайте се
   - Попълнете плащане информация

2. **Генерирайте API ключ:**
   - Отидете в API → API Keys
   - Кликнете на "Create new secret key"
   - Копирайте ключа (виждаща се само веднъж!)

3. **Конфигурирайте в WordPress:**
   ```
   AI Provider:    OpenAI
   API URL:        https://api.openai.com/v1/chat/completions
   API Key:        sk-proj-...
   Model:          gpt-3.5-turbo
   Max Tokens:     1000
   Temperature:    0.7
   ```

4. **Тестирайте:**
   - Запазете настройките
   - Напишете тестово съобщение в чатбота
   - Проверете дали получавате отговор

**Ценови информация:**
- GPT-3.5-turbo: ~$0.0005 per 1K tokens
- GPT-4: ~$0.03 per 1K tokens

**Документация:** https://platform.openai.com/docs/api-reference/chat/create

---

### Groq (Бързостен)

**Преимущества:**
- ⚡ Много по-бързо от OpenAI
- 💰 По-евтино
- 🚀 Идеално за реално време приложения

**Стъпки за конфигурация:**

1. **Създайте профил:**
   - Посетете https://console.groq.com
   - Регистрирайте се

2. **Генерирайте API ключ:**
   - API Keys → Create API Key
   - Копирайте ключа

3. **Конфигурирайте:**
   ```
   AI Provider:    Groq
   API URL:        https://api.groq.com/openai/v1/chat/completions
   API Key:        gsk_...
   Model:          mixtral-8x7b-32768
   Max Tokens:     1000
   Temperature:    0.7
   ```

**Документация:** https://console.groq.com/docs/speech-text

---

### Anthropic (Claude)

**Преимущества:**
- 🧠 Claude е много умър
- 💡 Отлично за аналитика и писане
- 🔒 Добра поддръжка на контекст

**Стъпки за конфигурация:**

1. **Създайте профил:**
   - Посетете https://dashboard.anthropic.com
   - Регистрирайте се

2. **Генерирайте API ключ:**
   - API keys → New API Key
   - Копирайте ключа

3. **Конфигурирайте:**
   ```
   AI Provider:    Anthropic
   API URL:        https://api.anthropic.com/v1/messages
   API Key:        sk-ant-...
   Model:          claude-3-sonnet-20240229
   Max Tokens:     1000
   Temperature:    0.7
   ```

**Забележка:** Anthropic използва различна структура на заявка!

**Документация:** https://docs.anthropic.com/claude/reference/getting-started-with-the-api

---

### Персонализирана API

За всяка друга OpenAI-съвместима API:

**Стъпки:**

1. **Получете API ключ** от вашия провайдер
2. **Намерете API URL** (обикновено в документация)
3. **Конфигурирайте:**
   ```
   AI Provider:    Custom
   API URL:        https://your-provider.com/v1/chat/completions
   API Key:        your-api-key-here
   Model:          model-name-given-by-provider
   ```

**Ejemplo за LocalAI:**
```
API Provider:    Custom
API URL:         http://localhost:8080/v1/chat/completions
API Key:         test-key
Model:           gpt-3.5-turbo
```

---

## 🎛️ Разширени настройки

### Max Tokens (Максимални токени)

**Описание:** Максимален брой "думи" в отговора

**Стойност:** 1 до 4000
**Препоръчана:** 1000
**По подразбиране:** 1000

**Как разбирате tokenите:**
- ~1 token = ~4 символа
- 1000 токени ≈ 4000 символа ≈ 500-800 думи

**Примери:**
```
300 tokens    = Кръткосят одговор
1000 tokens   = Среден отговор
2048 tokens   = Дълъг отговор
4000 tokens   = Много дълъг отговор
```

**Повечето приложения:** 1000 токена е достатъчно

---

### Temperature (Креативност)

**Описание:** Контролира креативността на отговорите

**Стойност:** 0.0 до 2.0
**По подразбиране:** 0.7

**Скала:**

```
0.0 ────────────────────────────── 2.0
│                                     │
Детерминирана        Креативна
(винаги един отговор) (разнообразни отговори)
```

**Примери:**

| Стойност | Описание | Користване |
|----------|---------|----------|
| **0.0** | Винаги същия отговор | Факт-ориентирани|
| **0.3** | Малки вариации | Техническа поддръжка |
| **0.7** | Балансирана | Генерална употреба (препоръчано) |
| **1.0** | По-креативна | Създаване на съдържание |
| **1.5+** | Много креативна | Творческо писане |

**Препоръки:**
- **0.3-0.5** за поддръжка чатботи
- **0.7** за общи приложения
- **1.0+** за креативни задачи

---

### Chatbot Position (Позиция на чатбота)

**Описание:** Където се показва мехурчето на чатбота

**Опции:**
- 📌 Bottom Right (Долен десен край) - По подразбиране
- 📌 Bottom Left (Долен ляв край)
- 📌 Top Right (Горен десен край)
- 📌 Top Left (Горен ляв край)

**Примерна конфигурация:**
```
Chatbot Position: Bottom Right ▼
```

---

## 💬 Системен промпт примери

### За E-Commerce сайт:

```
You are a helpful product assistant for our online store.
You help customers find products, answer product questions.
You provide information about pricing, shipping, and returns.
Be friendly and helpful. Suggest products based on customer needs.
Always direct to customer service for complex issues.
```

### За Блог:

```
You are a knowledgeable content expert.
Help readers understand articles and blog posts.
Answer questions about the content.
Suggest related articles if appropriate.
Be engaging and encourage discussion.
```

### За SaaS приложение:

```
You are a technical support specialist.
Help users troubleshoot problems with the software.
Provide step-by-step solutions.
Direct to documentation when appropriate.
Escalate complex issues to support team.
Be patient and clear in explanations.
```

### За Училище/Образование:

```
You are an educational assistant.
Help students understand concepts and course material.
Answer questions about assignments.
Provide clarifications and examples.
Encourage critical thinking.
Direct to instructors for grades or official matters.
```

### На Български:

```
Ти си полезен асистент за клиенти на нашия уебсайт.
Помагаш на потребителите да намерят отговори на техните въпроси.
Отговаряй ясно, точно и вежливо.
Ако вопрос е извън твоята компетентност, посочи го.
Отговаряй всегда на Български език.
```

---

## 📍 Позиции на чатбота

### Визуално представяне:

```
┌─────────────────────┐
│ Top Left ◯   ◯ Top Right │
│                       │
│ Съдържание            │
│                       │
│                       │
│ Bot Left ◯   ◯ Bottom Right│
└─────────────────────┘
```

### Преглед:

| Позиция | Про | Контра |
|---------|-----|---------|
| **Bottom Right** | Стандартна, не пречи | Някой сайтове нямали място |
| **Bottom Left** | Алтернатива | Нетипична |
| **Top Right** | Видима | Пречи на навигацията |
| **Top Left** | Видима | Пречи на логото |

**Препоръка:** Bottom Right е най-добрата опция

---

## ⚙️ Параметри на чатбота

### Таблица на всички параметри:

| Параметър | Тип | Min | Max | Default | Описание |
|-----------|-----|-----|-----|---------|----------|
| enabled | Boolean | - | - | 0 | Включено ли |
| api_provider | String | - | - | openai | Доставчик |
| api_url | URL | - | - | - | API адрес |
| api_key | String | - | - | - | API ключ |
| model | String | - | - | gpt-3.5-turbo | Модел |
| system_prompt | Text | - | - | Helper text | Промпт |
| max_tokens | Integer | 1 | 4000 | 1000 | Макс токени |
| temperature | Float | 0.0 | 2.0 | 0.7 | Креативност |
| position | String | - | - | bottom-right | Позиция |

---

## 💾 Съхранение на настройки

### Местоположение в база данни:

```
WordPress таблица: wp_options
Ключ: chatbot_ai_engine_settings
Тип: Array (serialized)
```

### Структура:

```php
$settings = array(
    'enabled'        => '1',
    'api_provider'   => 'openai',
    'api_url'        => 'https://...',
    'api_key'        => 'sk-...',
    'model'          => 'gpt-3.5-turbo',
    'system_prompt'  => 'You are...',
    'max_tokens'     => 1000,
    'temperature'    => 0.7,
    'position'       => 'bottom-right',
);
```

### Как да видите настройките (WP-CLI):

```bash
wp option get chatbot_ai_engine_settings --format=json
```

### Экспорт на настройки:

```bash
wp option get chatbot_ai_engine_settings > settings.json
```

### Импорт на настройки:

```bash
wp option update chatbot_ai_engine_settings < settings.json
```

---

## 🔐 Безопасност на парола

### Как са защитени настройки:

1. ✅ **Nonce верификация** - CSRF защита
2. ✅ **Пермисии** - Само администратори
3. ✅ **Санитизация** - Входни данни очищени
4. ✅ **Валидация** - Параметри проверени
5. ✅ **Екраниране** - Изход защитен

### Добрите навици:

```
✅ Използвайте силна парола за WordPress админ
✅ Огранична достъп до администрирането
✅ Редовно обновявайте WordPress и плъгини
✅ Не делитье API ключове публично
✅ Используйте HTTPS за безопасност
```

---

## 🧪 Тестирате конфигурацията

### Стъпка по стъпка тестване:

1. **Запазете настройките**
   - Кликнете "Save Changes"
   - Очаквайте потвърждението

2. **Посетете вашия сайт**
   - Отворете фронтенда
   - Проверете дали мехурчето се вижда

3. **Изпробвайте съобщение**
   - Кликнете на мехурче
   - Напишете обикновено съобщение
   - Натиснете Enter

4. **Проверете отговора**
   - Очаквайте отговор от AI
   - Проверете дали е релевантен
   - Тестирайте с несколко съобщения

5. **Ако има грешка:**
   - Отворете конзолата (F12)
   - Проверете за JavaScript грешки
   - Вижте TROUBLESHOOTING.md

---

## 📚 Следващи стъпки

1. **Персонализирайте дизайна** → [CUSTOMIZATION.md](./CUSTOMIZATION.md)
2. **Разширете функции** → [DEVELOPMENT.md](./DEVELOPMENT.md)
3. **Решете проблеми** → [TROUBLESHOOTING.md](./TROUBLESHOOTING.md)

---

**Последна актуализация:** 14 март 2024
