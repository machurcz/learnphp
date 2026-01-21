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
                <input
                    type="text"
                    id="firma"
                    name="firma"
                    value="<?php echo htmlspecialchars($_POST['firma'] ?? ''); ?>"
                    required
                >
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
</body>
</html>
