<?php
/**
 * Produkční konfigurace pro GCP
 * Čte API klíč z environment variables
 */

// Načtení API klíče z environment variables nebo fallback na lokální config
$apiKey = getenv('MERK_API_KEY');

if (!$apiKey) {
    // Fallback pro lokální vývoj
    if (file_exists(__DIR__ . '/config.php')) {
        require_once __DIR__ . '/config.php';
        return;
    }

    // Výchozí hodnota (měla by být přepsána)
    $apiKey = 'YOUR_API_KEY_HERE';
}

define('MERK_API_KEY', $apiKey);
define('MERK_API_URL', 'https://api.merk.cz/v1/suggest');
