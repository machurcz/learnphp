<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrace firmy</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 500px;
        }

        h1 {
            color: #333;
            margin-bottom: 30px;
            text-align: center;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: bold;
        }

        input[type="text"] {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            transition: border-color 0.3s;
        }

        input[type="text"]:focus {
            outline: none;
            border-color: #667eea;
        }

        .required {
            color: #e74c3c;
        }

        button {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.2s;
        }

        button:hover {
            transform: translateY(-2px);
        }

        button:active {
            transform: translateY(0);
        }

        .success-message {
            background: #2ecc71;
            color: white;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }

        .error-message {
            background: #e74c3c;
            color: white;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }

        .info-text {
            font-size: 12px;
            color: #777;
            margin-top: 5px;
        }

        /* Autocomplete styles */
        .autocomplete-wrapper {
            position: relative;
        }

        .autocomplete-suggestions {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 2px solid #667eea;
            border-top: none;
            border-radius: 0 0 5px 5px;
            max-height: 300px;
            overflow-y: auto;
            z-index: 1000;
            display: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .autocomplete-suggestions.show {
            display: block;
        }

        .suggestion-item {
            padding: 12px;
            cursor: pointer;
            border-bottom: 1px solid #eee;
            transition: background-color 0.2s;
        }

        .suggestion-item:last-child {
            border-bottom: none;
        }

        .suggestion-item:hover,
        .suggestion-item.active {
            background-color: #f0f0ff;
        }

        .suggestion-name {
            font-weight: bold;
            color: #333;
            margin-bottom: 4px;
        }

        .suggestion-details {
            font-size: 12px;
            color: #666;
        }

        .loading {
            padding: 12px;
            text-align: center;
            color: #666;
            font-style: italic;
        }

        .no-results {
            padding: 12px;
            text-align: center;
            color: #999;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Registrace firmy</h1>

        <?php
        $success = false;
        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Získání dat z formuláře
            $firma = trim($_POST['firma'] ?? '');
            $ico = trim($_POST['ico'] ?? '');
            $dic = trim($_POST['dic'] ?? '');
            $adresa = trim($_POST['adresa'] ?? '');

            // Validace
            if (empty($firma)) {
                $errors[] = 'Název firmy je povinný';
            }

            if (empty($ico)) {
                $errors[] = 'IČO je povinné';
            } elseif (!preg_match('/^\d{8}$/', $ico)) {
                $errors[] = 'IČO musí obsahovat 8 číslic';
            }

            if (!empty($dic) && !preg_match('/^CZ\d{8,10}$/', $dic)) {
                $errors[] = 'DIČ musí být ve formátu CZ následované 8-10 číslicemi';
            }

            if (empty($adresa)) {
                $errors[] = 'Adresa je povinná';
            }

            // Pokud nejsou chyby, zpracovat registraci
            if (empty($errors)) {
                // Zde by bylo uložení do databáze
                // Pro demonstraci pouze uložíme do souboru
                $data = [
                    'firma' => $firma,
                    'ico' => $ico,
                    'dic' => $dic,
                    'adresa' => $adresa,
                    'datum_registrace' => date('Y-m-d H:i:s')
                ];

                // Uložení do JSON souboru
                $filename = 'registrace.json';
                $existing_data = [];

                if (file_exists($filename)) {
                    $existing_data = json_decode(file_get_contents($filename), true) ?? [];
                }

                $existing_data[] = $data;
                file_put_contents($filename, json_encode($existing_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

                $success = true;
            }
        }

        if ($success) {
            echo '<div class="success-message">Firma byla úspěšně zaregistrována!</div>';
        }

        if (!empty($errors)) {
            echo '<div class="error-message">';
            foreach ($errors as $error) {
                echo htmlspecialchars($error) . '<br>';
            }
            echo '</div>';
        }
        ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="firma">Název firmy <span class="required">*</span></label>
                <div class="autocomplete-wrapper">
                    <input
                        type="text"
                        id="firma"
                        name="firma"
                        value="<?php echo htmlspecialchars($_POST['firma'] ?? ''); ?>"
                        autocomplete="off"
                        placeholder="Začněte psát název firmy..."
                        required
                    >
                    <div class="autocomplete-suggestions" id="suggestions"></div>
                </div>
                <div class="info-text">Našeptávač firem od 3 znaků</div>
            </div>

            <div class="form-group">
                <label for="ico">IČO <span class="required">*</span></label>
                <input
                    type="text"
                    id="ico"
                    name="ico"
                    value="<?php echo htmlspecialchars($_POST['ico'] ?? ''); ?>"
                    pattern="\d{8}"
                    maxlength="8"
                    required
                >
                <div class="info-text">8 číslic</div>
            </div>

            <div class="form-group">
                <label for="dic">DIČ</label>
                <input
                    type="text"
                    id="dic"
                    name="dic"
                    value="<?php echo htmlspecialchars($_POST['dic'] ?? ''); ?>"
                    pattern="CZ\d{8,10}"
                    placeholder="CZ12345678"
                >
                <div class="info-text">Formát: CZ následované 8-10 číslicemi</div>
            </div>

            <div class="form-group">
                <label for="adresa">Adresa <span class="required">*</span></label>
                <input
                    type="text"
                    id="adresa"
                    name="adresa"
                    value="<?php echo htmlspecialchars($_POST['adresa'] ?? ''); ?>"
                    placeholder="Ulice 123, 120 00 Praha"
                    required
                >
            </div>

            <button type="submit">Registrovat firmu</button>
        </form>
    </div>

    <script>
        // Autocomplete funkcionalita pro našeptávač firem
        (function() {
            const firmaInput = document.getElementById('firma');
            const icoInput = document.getElementById('ico');
            const dicInput = document.getElementById('dic');
            const adresaInput = document.getElementById('adresa');
            const suggestionsBox = document.getElementById('suggestions');

            let debounceTimer;
            let currentSuggestions = [];
            let selectedIndex = -1;

            // Debounce funkce pro optimalizaci API volání
            function debounce(func, delay) {
                return function(...args) {
                    clearTimeout(debounceTimer);
                    debounceTimer = setTimeout(() => func.apply(this, args), delay);
                };
            }

            // Funkce pro volání API přes proxy
            async function fetchSuggestions(query) {
                if (query.length < 3) {
                    hideSuggestions();
                    return;
                }

                showLoading();

                try {
                    const response = await fetch('merk-proxy.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            query: query,
                            suggestBy: 'name'
                        })
                    });

                    const data = await response.json();

                    if (data.error) {
                        showError(data.error);
                        return;
                    }

                    currentSuggestions = data.suggestions || [];
                    displaySuggestions(currentSuggestions);
                } catch (error) {
                    showError('Chyba při načítání návrhů: ' + error.message);
                }
            }

            // Zobrazení načítání
            function showLoading() {
                suggestionsBox.innerHTML = '<div class="loading">Načítám návrhy...</div>';
                suggestionsBox.classList.add('show');
            }

            // Zobrazení chyby
            function showError(message) {
                suggestionsBox.innerHTML = `<div class="no-results">${message}</div>`;
                suggestionsBox.classList.add('show');
            }

            // Zobrazení návrhů
            function displaySuggestions(suggestions) {
                selectedIndex = -1;

                if (suggestions.length === 0) {
                    suggestionsBox.innerHTML = '<div class="no-results">Žádné výsledky</div>';
                    suggestionsBox.classList.add('show');
                    return;
                }

                let html = '';
                suggestions.forEach((company, index) => {
                    html += `
                        <div class="suggestion-item" data-index="${index}">
                            <div class="suggestion-name">${escapeHtml(company.name)}</div>
                            <div class="suggestion-details">
                                IČO: ${escapeHtml(company.ico || 'N/A')} |
                                ${escapeHtml(company.full_address || company.address || 'Adresa neuvedena')}
                            </div>
                        </div>
                    `;
                });

                suggestionsBox.innerHTML = html;
                suggestionsBox.classList.add('show');

                // Přidání click event listenerů
                suggestionsBox.querySelectorAll('.suggestion-item').forEach(item => {
                    item.addEventListener('click', () => {
                        const index = parseInt(item.dataset.index);
                        selectSuggestion(index);
                    });
                });
            }

            // Skrytí návrhů
            function hideSuggestions() {
                suggestionsBox.classList.remove('show');
                suggestionsBox.innerHTML = '';
                currentSuggestions = [];
                selectedIndex = -1;
            }

            // Výběr návrhu
            function selectSuggestion(index) {
                if (index >= 0 && index < currentSuggestions.length) {
                    const company = currentSuggestions[index];

                    firmaInput.value = company.name;
                    icoInput.value = company.ico || '';
                    dicInput.value = company.dic || '';
                    adresaInput.value = company.full_address || company.address || '';

                    hideSuggestions();
                    firmaInput.focus();
                }
            }

            // Navigace klávesnicí
            function navigateSuggestions(direction) {
                const items = suggestionsBox.querySelectorAll('.suggestion-item');
                if (items.length === 0) return;

                // Odstranění předchozího výběru
                if (selectedIndex >= 0) {
                    items[selectedIndex].classList.remove('active');
                }

                // Aktualizace indexu
                selectedIndex += direction;

                if (selectedIndex < 0) {
                    selectedIndex = -1;
                } else if (selectedIndex >= items.length) {
                    selectedIndex = items.length - 1;
                }

                // Přidání nového výběru
                if (selectedIndex >= 0) {
                    items[selectedIndex].classList.add('active');
                    items[selectedIndex].scrollIntoView({ block: 'nearest' });
                }
            }

            // Escape HTML
            function escapeHtml(text) {
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }

            // Event listenery
            firmaInput.addEventListener('input', debounce(function(e) {
                const query = e.target.value.trim();
                fetchSuggestions(query);
            }, 300));

            firmaInput.addEventListener('keydown', function(e) {
                const isOpen = suggestionsBox.classList.contains('show');

                switch(e.key) {
                    case 'ArrowDown':
                        if (isOpen) {
                            e.preventDefault();
                            navigateSuggestions(1);
                        }
                        break;
                    case 'ArrowUp':
                        if (isOpen) {
                            e.preventDefault();
                            navigateSuggestions(-1);
                        }
                        break;
                    case 'Enter':
                        if (isOpen && selectedIndex >= 0) {
                            e.preventDefault();
                            selectSuggestion(selectedIndex);
                        }
                        break;
                    case 'Escape':
                        if (isOpen) {
                            e.preventDefault();
                            hideSuggestions();
                        }
                        break;
                }
            });

            // Kliknutí mimo autocomplete zavře návrhy
            document.addEventListener('click', function(e) {
                if (!firmaInput.contains(e.target) && !suggestionsBox.contains(e.target)) {
                    hideSuggestions();
                }
            });

            // Focus na input otevře návrhy, pokud už existují
            firmaInput.addEventListener('focus', function() {
                if (currentSuggestions.length > 0 && firmaInput.value.length >= 3) {
                    suggestionsBox.classList.add('show');
                }
            });
        })();
    </script>
</body>
</html>
