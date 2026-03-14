# 🎨 Персонализирана Помагача - Chatbot AI Engine

## Съдържание

1. [CSS персонализация](#css-персонализация)
2. [CSS променливи](#css-променливи)
3. [Персонализирани дизайни](#персонализирани-дизайни)
4. [JavaScript персонализация](#javascript-персонализация)
5. [Темати](#темати)
6. [Отзивчив дизайн](#отзивчив-дизайн)
7. [Примери](#примери)

---

## 🎨 CSS персонализация

### Начини на персонализация:

| Метод | Сложност | Препоръка |
|-------|---------|----------|
| CSS променливи | Лесно | За начинаещи ✅ |
| Кастомна CSS | Средно | За напреднали |
| JavaScript хукове | Трудно | За разработчици |

---

## 🎚️ CSS променливи

### Disponible променливи:

```css
:root {
    /* Основни цветове */
    --chatbot-primary: #007bff;              /* Основен цвят */
    --chatbot-primary-dark: #0056b3;         /* Тъмен вариант */

    /* Съобщение на потребителя */
    --chatbot-bg-user: #007bff;              /* Фон */
    --chatbot-text-user: #ffffff;            /* Текст */

    /* Съобщение на бота */
    --chatbot-bg-bot: #f0f0f0;               /* Фон */
    --chatbot-text-bot: #333333;             /* Текст */

    /* Грешка */
    --chatbot-bg-error: #f8d7da;             /* Фон */
    --chatbot-text-error: #721c24;           /* Текст */

    /* Други */
    --chatbot-border: #e0e0e0;               /* Граница */
    --chatbot-shadow: 0 5px 40px rgba(...);  /* Сянка */
    --chatbot-border-radius: 12px;           /* Закръглени ъгли */
    --chatbot-transition: all 0.3s ease;     /* Анимация */
}
```

---

## 🎨 Персонализирани дизайни

### Начална конфигурация

Във вашия theme `style.css` или child theme добавете:

```css
/* Кастомни CSS променливи */
:root {
    --chatbot-primary: #YOUR_COLOR;
    --chatbot-primary-dark: #YOUR_DARKER_COLOR;
}
```

---

### Дизайн 1: Светла тема (Light Blue)

```css
:root {
    --chatbot-primary: #3498db;              /* Светла синя */
    --chatbot-primary-dark: #2980b9;         /* Тъмна синя */
    --chatbot-bg-user: #3498db;
    --chatbot-text-user: #ffffff;
    --chatbot-bg-bot: #ecf0f1;
    --chatbot-text-bot: #2c3e50;
    --chatbot-border-radius: 18px;
}
```

---

### Дизайн 2: Тъмна тема (Dark Mode)

```css
@media (prefers-color-scheme: dark) {
    :root {
        --chatbot-primary: #6c5ce7;          /* Лилаво */
        --chatbot-primary-dark: #5f3dc4;
        --chatbot-bg-user: #6c5ce7;
        --chatbot-text-user: #ffffff;
        --chatbot-bg-bot: #2f3542;
        --chatbot-text-bot: #dfe6e9;
        --chatbot-border: #444;
    }
}
```

---

### Дизайн 3: Зелена еко-тема

```css
:root {
    --chatbot-primary: #27ae60;              /* Зелена */
    --chatbot-primary-dark: #229954;
    --chatbot-bg-user: #27ae60;
    --chatbot-text-user: #ffffff;
    --chatbot-bg-bot: #eafaf1;
    --chatbot-text-bot: #145a32;
    --chatbot-border-radius: 20px;
    --chatbot-shadow: 0 4px 30px rgba(39, 174, 96, 0.2);
}
```

---

### Дизайн 4: Розова кокетна тема

```css
:root {
    --chatbot-primary: #e91e63;              /* Розова */
    --chatbot-primary-dark: #c2185b;
    --chatbot-bg-user: #e91e63;
    --chatbot-text-user: #ffffff;
    --chatbot-bg-bot: #fce4ec;
    --chatbot-text-bot: #880e4f;
    --chatbot-border-radius: 16px;
}
```

---

### Дизайн 5: Оранжева енергийна

```css
:root {
    --chatbot-primary: #ff9800;              /* Оранжева */
    --chatbot-primary-dark: #f57c00;
    --chatbot-bg-user: #ff9800;
    --chatbot-text-user: #ffffff;
    --chatbot-bg-bot: #fff3e0;
    --chatbot-text-bot: #e65100;
    --chatbot-border-radius: 14px;
}
```

---

### Дизайн 6: Редметална (Корпоративна)

```css
:root {
    --chatbot-primary: #d32f2f;              /* Редботи */
    --chatbot-primary-dark: #b71c1c;
    --chatbot-bg-user: #d32f2f;
    --chatbot-text-user: #ffffff;
    --chatbot-bg-bot: #fafafa;
    --chatbot-text-bot: #212121;
    --chatbot-border: #bdbdbd;
    --chatbot-border-radius: 8px;           /* По-малко закръглване */
}
```

---

### Дизайн 7: Градиент Синя-Зелена

```css
:root {
    --chatbot-primary: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    --chatbot-primary-dark: #764ba2;
    --chatbot-bg-user: #667eea;
    --chatbot-text-user: #ffffff;
    --chatbot-bg-bot: #f3f4ff;
    --chatbot-text-bot: #333;
}
```

---

## 📝 JavaScript персонализация

### Персонализиране на селекторите

За кастомни селектори, добавете това преди скрипта на чатбота:

```javascript
<script>
window.chatbotAIEngineConfig = {
    position: 'bottom-right',
    themeColor: '#007bff',
    customMessages: {
        placeholder: 'Have a question?',
        send: 'Go',
        loading: 'Thinking...',
        error: 'Oops! Something went wrong'
    }
};
</script>
```

---

### Персонализирани иконки

Замълчет иконката на мехурчето в HTML:

```html
<svg>YOUR_CUSTOM_SVG</svg>
```

**Пример за сърце:**
```javascript
// В assets/script.js линия 24
bubble.innerHTML = `
    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
        <path d="M12 21.35l-1.45-1.32..."/>
    </svg>
`;
```

---

## 🎨 Темати

### Тема 1: Минималистична

```css
/* Премахнете сянки и закръглени ъгли */
#chatbot-ai-engine-window {
    border-radius: 0px;
    box-shadow: none;
    border: 1px solid #ccc;
}

#chatbot-ai-engine-bubble {
    border-radius: 4px;
    box-shadow: none;
}
```

---

### Тема 2: Материална дизайн

```css
/* Добавете материални сенки */
#chatbot-ai-engine-bubble {
    box-shadow: 0 3px 5px -1px rgba(0,0,0,.2),
                0 6px 10px 0 rgba(0,0,0,.14),
                0 1px 18px 0 rgba(0,0,0,.12);
}

#chatbot-ai-engine-window {
    box-shadow: 0 5px 5px -3px rgba(0,0,0,.2),
                0 8px 10px 1px rgba(0,0,0,.14),
                0 3px 14px 2px rgba(0,0,0,.12);
}
```

---

### Тема 3: Glassmorphism (Стъкло)

```css
#chatbot-ai-engine-window {
    background: rgba(255, 255, 255, 0.7);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.5);
}

#chatbot-ai-engine-bubble {
    background: rgba(0, 123, 255, 0.7);
    backdrop-filter: blur(10px);
}
```

---

### Тема 4: Нийоморф (Мекот)

```css
#chatbot-ai-engine-window {
    background: #e0e5ec;
    box-shadow: -7px -7px 14px #ffffff,
                7px 7px 14px #b8bec3;
    border: none;
}

#chatbot-ai-engine-bubble {
    background: #e0e5ec;
    box-shadow: -7px -7px 14px #ffffff,
                7px 7px 14px #b8bec3;
}
```

---

### Тема 5: Ретро-вълна

```css
:root {
    --chatbot-primary: #ff006e;
    --chatbot-primary-dark: #c1121f;
    --chatbot-bg-user: #ff006e;
    --chatbot-text-user: #ffffff;
    --chatbot-bg-bot: #0f3460;
    --chatbot-text-bot: #00d4ff;
    --chatbot-border-radius: 0px;
}

#chatbot-ai-engine-window {
    border: 3px solid #ff006e;
}
```

---

## 📱 Отзивчив дизайн

### За мобилни устройства

```css
@media (max-width: 768px) {
    #chatbot-ai-engine-window {
        width: 100% !important;
        height: 100% !important;
        border-radius: 0 !important;
    }

    .chatbot-ai-engine-message-bubble {
        font-size: 16px; /* Избегнете мобилния zoom */
        max-width: 90%;
    }
}
```

---

### За големи екрани

```css
@media (min-width: 1200px) {
    #chatbot-ai-engine-window {
        width: 450px;
        height: 700px;
    }
}
```

---

## 📝 Примери

### Пример 1: Обиколка на CSS файла

Създайте файл `custom-chatbot.css` в вашия theme:

```css
/* 1. Поставете променливите */
:root {
    --chatbot-primary: #6c5ce7;
    --chatbot-primary-dark: #5f3dc4;
    --chatbot-bg-user: #6c5ce7;
    --chatbot-text-user: #ffffff;
    --chatbot-bg-bot: #f5f5f5;
    --chatbot-text-bot: #333;
    --chatbot-border-radius: 16px;
}

/* 2. Добавете нови стилове */
#chatbot-ai-engine-window {
    width: 380px;
    height: 550px;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.chatbot-ai-engine-header h3 {
    font-size: 18px;
    font-weight: 700;
}

/* 3. Персонализирайте съобщенията */
.chatbot-ai-engine-message-user .chatbot-ai-engine-message-bubble {
    border-radius: 18px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}
```

Кръгче в `functions.php`:

```php
wp_enqueue_style(
    'custom-chatbot-style',
    get_template_directory_uri() . '/custom-chatbot.css',
    array( 'chatbot-ai-engine-style' ),
    filemtime( get_template_directory() . '/custom-chatbot.css' )
);
```

---

### Пример 2: Темен режим (Dark Mode)

```css
@media (prefers-color-scheme: dark) {
    :root {
        --chatbot-primary: #bb86fc;
        --chatbot-primary-dark: #9970dd;
        --chatbot-bg-user: #bb86fc;
        --chatbot-text-user: #000000;
        --chatbot-bg-bot: #1f1f1f;
        --chatbot-text-bot: #e0e0e0;
        --chatbot-border: #333;
    }

    #chatbot-ai-engine-window {
        background: #121212;
    }

    #chatbot-ai-engine-messages {
        background: #121212;
    }

    .chatbot-ai-engine-input-wrapper {
        background: #1f1f1f;
        border-top-color: #333;
    }

    #chatbot-ai-engine-input {
        background: #2c2c2c;
        color: #e0e0e0;
        border-color: #444;
    }
}
```

---

### Пример 3: Позиция в горния край

```css
/* Промяна на позицията чрез CSS */
#chatbot-ai-engine-container.chatbot-ai-engine-position-bottom-right {
    top: 20px;
    bottom: auto;
}

#chatbot-ai-engine-window {
    bottom: 100px;
    top: auto;
}
```

---

### Пример 4: Скрит чатбот (Показване при щракване)

```css
/* Скритост по подразбиране */
#chatbot-ai-engine-bubble {
    opacity: 0.7;
    transition: opacity 0.3s ease;
}

#chatbot-ai-engine-bubble:hover {
    opacity: 1;
}
```

---

### Пример 5: Анимирана иконка

```css
@keyframes chatbotPulse {
    0%, 100% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.1);
    }
}

#chatbot-ai-engine-bubble {
    animation: chatbotPulse 2s infinite;
}
```

---

## 🔧 Напалегал CSS

Ако вашия CSS не работи, използвайте `!important`:

```css
#chatbot-ai-engine-bubble {
    background: #ff6b6b !important;
    border-radius: 10px !important;
}
```

⚠️ **Бележка:** Използвайте `!important` последна мярка!

---

## 📚 CSS класове

### Основни класове:

```css
#chatbot-ai-engine-container         /* Главния контейнер */
#chatbot-ai-engine-bubble            /* Мехурче */
#chatbot-ai-engine-window            /* Прозорец */
#chatbot-ai-engine-messages          /* Съобщения */
.chatbot-ai-engine-message           /* Единично съобщение */
.chatbot-ai-engine-message-user      /* Съобщение на потребителя */
.chatbot-ai-engine-message-bot       /* Съобщение на бота */
.chatbot-ai-engine-message-error     /* Съобщение за грешка */
.chatbot-ai-engine-header            /* Заглавие */
.chatbot-ai-engine-input-wrapper     /* Входния модул */
#chatbot-ai-engine-input             /* Входния полета */
#chatbot-ai-engine-send-btn          /* Бутон за изпращане */
```

---

## 🎨 Палета на цветовете

### Препоръчание за цветовете:

| Цвят | Hex | RGB | Употреба |
|------|-----|-----|----------|
| Синя | #007bff | 0,123,255 | Основна |
| Зелена | #28a745 | 40,167,69 | Успех |
| Червена | #dc3545 | 220,53,69 | Грешка |
| Жълта | #ffc107 | 255,193,7 | Внимание |
| Тъмна | #343a40 | 52,58,64 | Текст |

---

## 💾 Съхранение на персонализациите

### Опция 1: Theme CSS

Поставете в `wp-content/themes/your-theme/style.css`

### Опция 2: Child Theme

Създайте child theme с собствения CSS

### Опция 3: Custom Plugin

Създайте плъгин само за персонализацията

```php
<?php
/**
 * Plugin Name: Chatbot Customizer
 */

add_action( 'wp_enqueue_scripts', function() {
    wp_enqueue_style(
        'chatbot-custom',
        plugin_dir_url( __FILE__ ) . 'custom.css'
    );
} );
```

---

## 🧪 Проверка на персонализациите

1. Отворете DevTools (F12)
2. Посетете "Elements/Inspector"
3. Намерете `#chatbot-ai-engine-window`
4. Проверите CSS стилове
5. Тестирайте промени in real-time

---

## 📚 Допълнителни ресурси

- [CSS Variables MDN](https://developer.mozilla.org/en-US/docs/Web/CSS/var)
- [Responsive Design Patterns](https://web.dev/responsive-web-design-basics/)
- [CSS-in-JS Libraries](https://github.com/styled-components/styled-components)

---

**Версия:** 1.0.0
**Последна актуализация:** 14 март 2024
