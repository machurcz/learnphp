<?php
/**
 * Proxy server pro Merk API
 * Skrývá API klíč před klientským kódem
 */

header('Content-Type: application/json; charset=utf-8');

// Povolení CORS pro lokální vývoj
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Načtení konfigurace
require_once 'config.php';

// Kontrola metody
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Získání vstupních dat
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['query']) || strlen($input['query']) < 3) {
    echo json_encode(['suggestions' => []]);
    exit;
}

$query = $input['query'];
$suggestBy = $input['suggestBy'] ?? 'name'; // name, email, ico

// Kontrola API klíče
if (!defined('MERK_API_KEY') || MERK_API_KEY === 'YOUR_API_KEY_HERE') {
    http_response_code(500);
    echo json_encode([
        'error' => 'API klíč není nakonfigurován. Upravte soubor config.php',
        'suggestions' => []
    ]);
    exit;
}

// Příprava požadavku na Merk API
$apiUrl = MERK_API_URL . '?' . http_build_query([
    'query' => $query,
    'type' => $suggestBy,
    'limit' => 10
]);

// Inicializace cURL
$ch = curl_init($apiUrl);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . MERK_API_KEY,
        'Accept: application/json'
    ],
    CURLOPT_TIMEOUT => 5,
    CURLOPT_SSL_VERIFYPEER => true
]);

// Provedení požadavku
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

// Zpracování chyby
if ($error) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Chyba při komunikaci s API: ' . $error,
        'suggestions' => []
    ]);
    exit;
}

if ($httpCode !== 200) {
    http_response_code($httpCode);
    echo json_encode([
        'error' => 'API vrátilo chybu (HTTP ' . $httpCode . ')',
        'suggestions' => []
    ]);
    exit;
}

// Dekódování odpovědi
$data = json_decode($response, true);

// Formátování odpovědi pro frontend
$suggestions = [];

if (isset($data['data']) && is_array($data['data'])) {
    foreach ($data['data'] as $company) {
        $suggestions[] = [
            'name' => $company['name'] ?? '',
            'ico' => $company['ico'] ?? '',
            'dic' => $company['dic'] ?? '',
            'address' => $company['address'] ?? '',
            'city' => $company['city'] ?? '',
            'zip' => $company['zip'] ?? '',
            'full_address' => trim(
                ($company['address'] ?? '') . ', ' .
                ($company['zip'] ?? '') . ' ' .
                ($company['city'] ?? '')
            )
        ];
    }
}

echo json_encode([
    'suggestions' => $suggestions,
    'count' => count($suggestions)
], JSON_UNESCAPED_UNICODE);
