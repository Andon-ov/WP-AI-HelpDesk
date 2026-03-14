#!/bin/bash

# Името на плъгина и версията
PLUGIN_NAME="chatbot-ai-engine"
VERSION="v1.0.7"
ZIP_NAME="${PLUGIN_NAME}-${VERSION}.zip"

echo "🚀 Започвам създаването на ${ZIP_NAME}..."

# Премахване на стари архиви, ако съществуват
rm -f *.zip

# Създаване на архива с изключване на ненужни файлове
zip -r "$ZIP_NAME" . -x \
    "*.git*" \
    "*.DS_Store" \
    "build-zip.sh" \
    "GEMINI.md" \
    "node_modules/*" \
    "*.zip"

if [ $? -eq 0 ]; then
    echo "✅ Успех! Архивът е готов: ${ZIP_NAME}"
    ls -lh "$ZIP_NAME"
else
    echo "❌ Грешка при създаването на архива."
fi
