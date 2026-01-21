#!/bin/bash

# Deploy script pro Google Cloud Platform
# Použití: ./deploy.sh

set -e

echo "🚀 Nasazení aplikace na Google Cloud Platform"
echo "=============================================="
echo ""

# Kontrola gcloud CLI
if ! command -v gcloud &> /dev/null; then
    echo "❌ gcloud CLI není nainstalováno!"
    echo "Nainstalujte z: https://cloud.google.com/sdk/docs/install"
    exit 1
fi

echo "✅ gcloud CLI nalezeno"

# Kontrola přihlášení
if ! gcloud auth list --filter=status:ACTIVE --format="value(account)" &> /dev/null; then
    echo "❌ Nejste přihlášeni do Google Cloud"
    echo "Spusťte: gcloud auth login"
    exit 1
fi

ACCOUNT=$(gcloud auth list --filter=status:ACTIVE --format="value(account)")
echo "✅ Přihlášen jako: $ACCOUNT"

# Kontrola projektu
PROJECT=$(gcloud config get-value project 2>/dev/null)

if [ -z "$PROJECT" ]; then
    echo ""
    echo "⚠️  Není nastaven žádný projekt"
    echo "Dostupné projekty:"
    gcloud projects list
    echo ""
    read -p "Zadejte ID projektu (nebo 'new' pro vytvoření nového): " PROJECT_INPUT

    if [ "$PROJECT_INPUT" = "new" ]; then
        read -p "Zadejte ID nového projektu (např. learnphp-app): " NEW_PROJECT
        echo "Vytvářím projekt $NEW_PROJECT..."
        gcloud projects create $NEW_PROJECT
        PROJECT=$NEW_PROJECT
    else
        PROJECT=$PROJECT_INPUT
    fi

    gcloud config set project $PROJECT
fi

echo "✅ Používám projekt: $PROJECT"

# Kontrola App Engine
if ! gcloud app describe &>/dev/null; then
    echo ""
    echo "⚠️  App Engine ještě není inicializován pro tento projekt"
    echo "Dostupné regiony:"
    echo "  - europe-west3 (Frankfurt) - doporučeno pro ČR"
    echo "  - europe-west1 (Belgie)"
    echo "  - europe-central2 (Varšava)"
    echo ""
    read -p "Zadejte region [europe-west3]: " REGION
    REGION=${REGION:-europe-west3}

    echo "Inicializuji App Engine v regionu $REGION..."
    gcloud app create --region=$REGION
fi

echo "✅ App Engine je připraven"
echo ""

# Kontrola API klíče v app.yaml
echo "🔑 Kontrola API konfigurace..."
if grep -q "YOUR_API_KEY_HERE" app.yaml; then
    echo "⚠️  VAROVÁNÍ: API klíč není nakonfigurován v app.yaml!"
    read -p "Chcete zadat API klíč teď? (y/n): " CONFIGURE_KEY

    if [ "$CONFIGURE_KEY" = "y" ]; then
        read -p "Zadejte Merk API klíč: " API_KEY
        sed -i "s/YOUR_API_KEY_HERE/$API_KEY/" app.yaml
        echo "✅ API klíč aktualizován"
    fi
fi

echo ""
echo "📦 Připravuji nasazení..."
echo "   Projekt: $PROJECT"
echo "   Runtime: php81"
echo ""

# Dotaz na potvrzení
read -p "Pokračovat s nasazením? (y/n): " CONFIRM

if [ "$CONFIRM" != "y" ]; then
    echo "❌ Nasazení zrušeno"
    exit 0
fi

echo ""
echo "🚀 Nasazuji aplikaci..."
gcloud app deploy --quiet

echo ""
echo "✅ Nasazení dokončeno!"
echo ""

# Získání URL
APP_URL=$(gcloud app browse --no-launch-browser 2>&1 | grep -oP 'https://[^\s]+')

echo "🌐 Vaše aplikace je dostupná na:"
echo "   $APP_URL/register-company.php"
echo ""

read -p "Otevřít aplikaci v prohlížeči? (y/n): " OPEN_BROWSER

if [ "$OPEN_BROWSER" = "y" ]; then
    gcloud app browse
fi

echo ""
echo "📊 Užitečné příkazy:"
echo "   Logy:      gcloud app logs tail -s default"
echo "   Dashboard: https://console.cloud.google.com/appengine?project=$PROJECT"
echo "   Verze:     gcloud app versions list"
echo ""
echo "✨ Hotovo!"
