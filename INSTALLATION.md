# 📥 Инсталационен Наръчник - Chatbot AI Engine

## Съдържание

1. [Системни требования](#системни-требования)
2. [Методи на инсталация](#методи-на-инсталация)
3. [Активиране на плъгина](#активиране-на-плъгина)
4. [Първа конфигурация](#първа-конфигурация)
5. [Проверка на инсталацията](#проверка-на-инсталацията)
6. [Отстраняване на неуспешна инсталация](#отстраняване-на-неуспешна-инсталация)
7. [Деинсталация](#деинсталация)

---

## ✅ Системни требования

### Минимални намания:

| Компонент |版本 |
|-----------|------|
| WordPress | 5.0 или по-нова |
| PHP | 7.4 или по-нова |
| MySQL / MariaDB | 5.7 / 10.2+ |
| Памет | Минимум 256 MB |

### Проверка на вашата система:

Отидете в **WordPress Admin → Tools → Site Health** и проверете:
- ✅ PHP версия
- ✅ MySQL версия
- ✅ WordPress версия
- ✅ Разрешения на файлове

---

## 📥 Методи на инсталация

### Метод 1: Чрез WordPress Admin (Препоръчано)

#### Стъпка 1: Качване на плъгина

**Ако имате плъгин във файл:**

1. Отидете в **WordPress Admin → Plugins → Add New**
2. Кликнете на **Upload Plugin**
3. Кликнете на **Choose File** и изберете `chatbot-ai-engine.zip`
4. Кликнете на **Install Now**
5. Уведомлението ще покаже прогреса

**Ако имате разархивиран плъгин:**

1. На вашия компютър разведете архива
2. Използвайте FTP или SSH за качване в `/wp-content/plugins/`
3. Убедитесь, че структурата е: `/wp-content/plugins/chatbot-ai-engine/`

#### Стъпка 2: Активиране

1. Отидете в **Plugins → Installed Plugins**
2. Намерете "Chatbot AI Engine"
3. Кликнете на **Activate**
4. Ще видите потвърждение на успешна активация

### Метод 2: Чрез FTP/SFTP

1. Изтеглете плъгина на вашия компютър
2. Разведете архива
3. Свържете се със сървъра чрез FTP программа (FileZilla, WinSCP, Cyberduck)
4. Навигирайте до `/wp-content/plugins/`
5. Качите папката `chatbot-ai-engine` с всичкия й съдържание
6. В WordPress Admin активирайте плъгина

### Метод 3: Чрез SSH/Командния ред

```bash
# Влезте в сървъра чрез SSH
ssh user@yourserver.com

# Навигирайте до plugins директорията
cd ~/public_html/wp-content/plugins/

# Или за други структури:
cd /var/www/html/wp-content/plugins/

# Качете файла (натрапе .zip файл преди това)
wget http://yoursite.com/chatbot-ai-engine.zip
# или
curl -O http://yoursite.com/chatbot-ai-engine.zip

# Разведете архива
unzip chatbot-ai-engine.zip

# Проверете структурата
ls -la chatbot-ai-engine/

# Установете правилни разрешения (optional но препоръчано)
chmod 755 chatbot-ai-engine/
chmod 644 chatbot-ai-engine/*.php
chmod 644 chatbot-ai-engine/assets/*
```

### Метод 4: Чрез WP-CLI

```bash
# Инсталирайте плъгина
wp plugin install /path/to/chatbot-ai-engine.zip

# Активирайте плъгина
wp plugin activate chatbot-ai-engine

# Проверете статуса
wp plugin status chatbot-ai-engine
```

---

## ✅ Активиране на плъгина

### В WordPress Admin:

1. **Логнете се** като администратор
2. Отидете в **Plugins меню**
3. Потърсете "Chatbot AI Engine"
4. Кликнете на синия бутон "Activate"
5. Ще видите успешното съобщение: "Plugin activated successfully"

### През командния ред (WP-CLI):

```bash
wp plugin activate chatbot-ai-engine
```

### Проверка на активацията:

```bash
wp plugin status chatbot-ai-engine
# Резултат: "Plugin chatbot-ai-engine is active."
```

---

## ⚙️ Първа конфигурация

### Стъпка 1: Достъп до панелът

1. Отидете в **WordPress Admin Dashboard**
2. В левия меню ще видите нова иконка "AI Chatbot"
3. Кликнете на нея

### Стъпка 2: Базова конфигурация

На страницата с настройки ще видите:

```
☐ Enable Chatbot                    [Маркирайте тази кутия]
  ↓
API Provider:                       [Izberite provider]
  - OpenAI (default)
  - Groq
  - Anthropic
  - Custom

API URL:                            [Автоматично попълнено]
API Key:                            [Вашия ключ]
Model Name:                         [напр. gpt-3.5-turbo]
System Prompt:                      [Инструкции за AI]
Max Tokens:                         [1000]
Temperature:                        [0.7]
Position:                           [Bottom Right]
```

### Стъпка 3: Попълнете данните

1. **Включване:** Отметнете кутията "Enable Chatbot"
2. **Доставчик:** Изберете вашия AI доставчик (напр. OpenAI)
3. **API Ключ:** Вставете вашия API ключ
4. **Model:** Напишете модела (напр. gpt-3.5-turbo)
5. **System Prompt:** Нагласете инструкциите за чатбота
6. **Позиция:** Изберете къде да се показва чатбота

### Стъпка 4: Запазване

1. Кликнете на **"Save Changes"** бутон
2. Очаквайте потвърждението "Settings saved."
3. Готово! Чатбота е активен.

---

## 🔍 Проверка на инсталацията

### Проверка 1: Плъгин активен

```bash
# През командния ред
wp plugin status | grep chatbot-ai-engine

# Резултат трябва да含有 "active"
```

### Проверка 2: Файлове на място

**Структура:**
```
/wp-content/plugins/chatbot-ai-engine/
├── chatbot-ai-engine.php          ✅
├── assets/
│   ├── script.js                  ✅
│   └── style.css                  ✅
└── languages/                     ✅ (за преводи)
```

Проверка чрез FTP или командния ред:
```bash
ls -la /wp-content/plugins/chatbot-ai-engine/
```

### Проверка 3: Меню за администрирване

1. Отидете в **WordPress Admin**
2. В левия меню трябва да видите "**AI Chatbot**" опция
3. Ако не видите, опитайте да обновите страницата

### Проверка 4: Фронтенд теста

1. Посетете вашия сайт
2. В долния десен ъгъл трябва да видите синия мехурче иконка
3. Кликнете на нея - трябва да се отвори чат прозорец
4. Напишете тестово съобщение

**Ако видите чатбота:**
✅ Инсталацията е успешна!

**Ако не видите чатбота:**
- Проверете дали сте включили в настройките
- Проверете конзолата на браузъра (F12)
- Вижте TROUBLESHOOTING.md

### Проверка 5: Логове

```bash
# Проверете WordPress логове
tail -50 /wp-content/debug.log

# Проверете PHP логове
tail -50 /var/log/php-errors.log

# Проверете Apache/Nginx логове
tail -50 /var/log/apache2/error.log
tail -50 /var/log/nginx/error.log
```

---

## ❌ Отстраняване на неуспешна инсталация

### Грешка: "Plugin failed to activate"

**Решение:**
```bash
# Проверете синтаксиса на PHP
php -l /wp-content/plugins/chatbot-ai-engine/chatbot-ai-engine.php

# Проверете разрешения на файлове
chmod 755 /wp-content/plugins/chatbot-ai-engine/
chmod 644 /wp-content/plugins/chatbot-ai-engine/*.php
```

### Грешка: Липсват файли

**Проверете структура:**
```bash
ls -la /wp-content/plugins/chatbot-ai-engine/chatbot-ai-engine.php
ls -la /wp-content/plugins/chatbot-ai-engine/assets/script.js
ls -la /wp-content/plugins/chatbot-ai-engine/assets/style.css
```

**Решение:** Преинсталирайте плъгина

### Грешка: Белите екрани

1. Включете режим на отстраняване:
```php
// wp-config.php
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );
```

2. Проверете `/wp-content/debug.log`

### Грешка: Администрирането е недостъпно

1. Деактивирайте всички други плъгини
2. Активирайте ChatBot AI Engine отново
3. Ако работи, активирайте други плъгини един по един

---

## 🗑️ Деинсталация

### Метод 1: През WordPress Admin

1. Отидете в **Plugins → Installed Plugins**
2. Намерете "Chatbot AI Engine"
3. Кликнете на **Deactivate**
4. След деактивирането кликнете на **Delete**
5. Потвърдете: **Yes, Delete these files and data**

### Метод 2: Ръчна деинсталация

```bash
# Отстранете папката
rm -rf /wp-content/plugins/chatbot-ai-engine/

# Или чрез FTP, просто изтрийте папката
```

### Метод 3: WP-CLI

```bash
# Деактивирайте
wp plugin deactivate chatbot-ai-engine

# Удалите
wp plugin delete chatbot-ai-engine
```

---

## 📊 Проверка на здравето на плъгина

### WordPress Site Health

1. Отидете в **Tools → Site Health**
2. Проверете дали няма критични грешки
3. Ако има предупреждения за плъгина, отворете их за детайли

### PHP версия

```bash
php -v
# Резултат трябва да бъде >= 7.4
```

### MySQL версия

```bash
mysql --version
# Резултат трябва да бъде >= 5.7
```

---

## 🎓 Следващи стъпки

След успешна инсталация:

1. **Конфигурирайте плъгина** - Вижте [CONFIGURATION.md](./CONFIGURATION.md)
2. **Персонализирайте дизайна** - Вижте [CUSTOMIZATION.md](./CUSTOMIZATION.md)
3. **Разширете функцията** - Вижте [DEVELOPMENT.md](./DEVELOPMENT.md)
4. **Решете проблеми** - Вижте [TROUBLESHOOTING.md](./TROUBLESHOOTING.md)

---

## 💡 Полезни команди

```bash
# Проверка на версия на WordPress
wp core version

# Списък на активни плъгини
wp plugin list --status=active

# Проверка на конкретен плъгин
wp plugin list | grep chatbot

# Таблица на WordPress опции
wp option get chatbot_ai_engine_settings
```

---

## 📋 Контролен списък

При инсталация убедитесь че:

- [ ] WordPress е версия 5.0 или по-нова
- [ ] PHP е версия 7.4 или по-нова
- [ ] Плъгинът е качен в `/wp-content/plugins/`
- [ ] Файлите са на място (chatbot-ai-engine.php, assets/)
- [ ] Плъгинът е активиран
- [ ] Меню "AI Chatbot" се вижда в админ панела
- [ ] Конфигурирани са основните настройки
- [ ] Чатбота се вижда на фронтенда

---

**Нужна помощ?** Вижте [TROUBLESHOOTING.md](./TROUBLESHOOTING.md)

**Дата на последна актуализация:** 14 март 2024
