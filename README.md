# learnphp
učím se php

## Registrace firmy s našeptávačem

Aplikace pro registraci firem s automatickým našeptáváním z databáze Merk.cz

### Funkce

- ✅ Formulář pro registraci firmy (název, IČO, DIČ, adresa)
- ✅ Automatické našeptávání firem při psaní názvu (od 3 znaků)
- ✅ Automatické vyplnění IČO, DIČ a adresy po výběru firmy
- ✅ Validace vstupů (IČO 8 číslic, DIČ formát CZ+čísla)
- ✅ Uložení do JSON souboru
- ✅ Navigace klávesnicí (šipky, Enter, Escape)
- ✅ Responzivní design

### Instalace

1. **Naklonujte repozitář**
   ```bash
   git clone https://github.com/machurcz/learnphp.git
   cd learnphp
   ```

2. **Nastavte konfiguraci API**
   ```bash
   cp config.example.php config.php
   ```

3. **Získejte API klíč**
   - Zaregistrujte se zdarma na [https://www.merk.cz/](https://www.merk.cz/)
   - V nastavení účtu najdete váš API klíč
   - Vložte klíč do `config.php`:
     ```php
     define('MERK_API_KEY', 'váš_api_klíč_zde');
     ```

4. **Spusťte PHP server**
   ```bash
   php -S localhost:8000
   ```

5. **Otevřete v prohlížeči**
   ```
   http://localhost:8000/register-company.php
   ```

### Struktura projektu

```
learnphp/
├── register-company.php   # Hlavní registrační formulář
├── merk-proxy.php        # Proxy server pro Merk API
├── config.php            # Konfigurace API klíče (není v gitu)
├── config.example.php    # Příklad konfigurace
├── registrace.json       # Uložené registrace (není v gitu)
└── README.md
```

### API Dokumentace

- [Merk API dokumentace](https://api.merk.cz/docs/)
- [Merk strojový přístup](https://www.merk.cz/api-strojovy-pristup/)
- [JavaScript SDK](https://github.com/impercz/merk-suggest-company-js)

### Použití

1. Začněte psát název firmy do pole "Název firmy"
2. Po 3 znacích se zobrazí našeptávač s návrhy
3. Vyberte firmu kliknutím nebo šipkami + Enter
4. IČO, DIČ a adresa se automaticky vyplní
5. Klikněte na "Registrovat firmu"

### Klávesové zkratky

- **Šipka dolů**: Další návrh
- **Šipka nahoru**: Předchozí návrh
- **Enter**: Výběr označeného návrhu
- **Escape**: Zavření našeptávače

### Bezpečnost

- API klíč je skrytý v server-side kódu (proxy)
- Ochrana proti XSS pomocí `htmlspecialchars()` a `escapeHtml()`
- Validace na straně serveru i klienta
- CORS povoleno pouze pro lokální vývoj
