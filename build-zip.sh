#!/bin/bash

# Името на плъгина и версията
PLUGIN_NAME="chatbot-ai-engine"
VERSION="v1.2.0"
ZIP_NAME="${PLUGIN_NAME}-${VERSION}.zip"

echo "🚀 Започвам създаването на ПРОФЕСИОНАЛЕН архив ${ZIP_NAME}..."

# Премахване на стари архиви
rm -f *.zip

# Създаване на временно копие за чист строеж
mkdir -p "$PLUGIN_NAME"
cp -r assets "$PLUGIN_NAME/"
cp -r includes "$PLUGIN_NAME/"
cp chatbot-ai-engine.php "$PLUGIN_NAME/"
cp README.md "$PLUGIN_NAME/"

# Генериране на архива
zip -r "$ZIP_NAME" "$PLUGIN_NAME"

# Почистване на временната папка
rm -rf "$PLUGIN_NAME"

if [ $? -eq 0 ]; then
    echo "✅ Успех! Архивът е готов и чист: ${ZIP_NAME}"
    ls -lh "$ZIP_NAME"
else
    echo "❌ Грешка при създаването на архива."
fi
