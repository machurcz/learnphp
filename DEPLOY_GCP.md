# Nasazení na Google Cloud Platform (GCP)

Tato aplikace je připravena pro nasazení na Google App Engine.

## Předpoklady

1. **Google Cloud účet**
   - Vytvořte účet na [https://console.cloud.google.com/](https://console.cloud.google.com/)
   - Nový uživatelé dostávají $300 kreditu zdarma na 90 dní

2. **Google Cloud SDK (gcloud CLI)**
   - Stáhněte z [https://cloud.google.com/sdk/docs/install](https://cloud.google.com/sdk/docs/install)
   - Nebo nainstalujte přímo:
     ```bash
     # Linux
     curl https://sdk.cloud.google.com | bash
     exec -l $SHELL

     # macOS
     brew install --cask google-cloud-sdk

     # Windows
     # Stáhněte installer z odkazu výše
     ```

## Krok za krokem

### 1. Přihlášení a inicializace

```bash
# Přihlášení k Google Cloud
gcloud auth login

# Vytvoření nového projektu (nebo použijte existující)
gcloud projects create learnphp-app --name="LearnPHP Registration"

# Nastavení aktivního projektu
gcloud config set project learnphp-app

# Povolení App Engine API
gcloud services enable appengine.googleapis.com

# Inicializace App Engine
gcloud app create --region=europe-west3
```

**Doporučené regiony pro Česko:**
- `europe-west3` (Frankfurt, Německo) - nejblíže
- `europe-west1` (Belgie)
- `europe-central2` (Varšava, Polsko)

### 2. Konfigurace API klíče

Otevřete soubor `app.yaml` a zkontrolujte/upravte Merk API klíč:

```yaml
env_variables:
  APP_ENV: 'production'
  MERK_API_KEY: 'váš_api_klíč_zde'
```

### 3. Nasazení aplikace

```bash
# Přejděte do složky projektu
cd /cesta/k/learnphp

# Nasazení na App Engine
gcloud app deploy

# Při prvním nasazení zadejte 'Y' pro potvrzení
```

### 4. Otevření aplikace

```bash
# Otevře aplikaci v prohlížeči
gcloud app browse
```

Vaše aplikace bude dostupná na URL:
```
https://learnphp-app.ey.r.appspot.com/register-company.php
```

(URL bude záviset na vašem project ID)

## Další užitečné příkazy

### Zobrazení logů

```bash
# Zobrazení real-time logů
gcloud app logs tail -s default

# Zobrazení logů v prohlížeči
gcloud app logs read
```

### Správa verzí

```bash
# Seznam všech verzí
gcloud app versions list

# Smazání staré verze
gcloud app versions delete VERSION_ID
```

### Zastavení aplikace

```bash
# Zastavení všech instancí (pořád platíte za úložiště)
gcloud app versions stop VERSION_ID

# Úplné smazání služby
gcloud app services delete default
```

### Vlastní doména

```bash
# Přidání vlastní domény
gcloud app domain-mappings create "www.vase-domena.cz" \
  --certificate-management=automatic
```

## Náklady

**App Engine Standard (PHP):**
- **F1 instance (výchozí):**
  - První 28 hodin instance/den ZDARMA
  - Dalších 9 hodin/den: $0.05/hodina

- **Odchozí provoz:**
  - První 1 GB/den ZDARMA
  - 0-10 TB/měsíc: $0.12/GB

- **Cloud Storage (pro statické soubory):**
  - První 5 GB ZDARMA
  - $0.026/GB/měsíc

**Pro malou aplikaci = prakticky ZDARMA v rámci free tier!**

Více informací: [https://cloud.google.com/appengine/pricing](https://cloud.google.com/appengine/pricing)

## Škálování

Upravte `app.yaml` pro změnu škálování:

```yaml
automatic_scaling:
  min_instances: 1        # Minimální počet instancí (0 = automatické vypínání)
  max_instances: 10       # Maximální počet instancí
  target_cpu_utilization: 0.65  # Cíl vytížení CPU pro škálování
```

## Monitoring

Sledujte výkon aplikace v GCP Console:

1. Přejděte na [https://console.cloud.google.com/](https://console.cloud.google.com/)
2. App Engine → Dashboard
3. Zde vidíte:
   - Počet požadavků
   - Latenci
   - Chyby
   - Využití paměti

## Použití Secret Manager (Doporučeno pro produkci)

Pro větší bezpečnost uložte API klíč v Secret Manager:

```bash
# Povolení Secret Manager API
gcloud services enable secretmanager.googleapis.com

# Vytvoření secretu
echo -n "bHVmmzbrCuwpmAqa68z4inv6bd1xJFLK" | \
  gcloud secrets create merk-api-key --data-file=-

# Přidání oprávnění pro App Engine
gcloud secrets add-iam-policy-binding merk-api-key \
  --member="serviceAccount:learnphp-app@appspot.gserviceaccount.com" \
  --role="roles/secretmanager.secretAccessor"
```

Pak upravte `config.production.php` pro načítání ze Secret Manager.

## Troubleshooting

### Chyba při nasazení

```bash
# Detailní logy nasazení
gcloud app deploy --verbosity=debug
```

### 502/503 chyby

- Zkontrolujte logy: `gcloud app logs tail`
- Zvyšte timeout v `app.yaml`
- Zkontrolujte PHP verzi

### API nefunguje

- Ověřte API klíč v `app.yaml`
- Zkontrolujte environment variables: `gcloud app describe`
- Testujte proxy endpoint přímo

## Podpora

- [GCP dokumentace](https://cloud.google.com/appengine/docs/standard/php)
- [App Engine fórum](https://groups.google.com/g/google-appengine)
- [Stack Overflow](https://stackoverflow.com/questions/tagged/google-app-engine)
