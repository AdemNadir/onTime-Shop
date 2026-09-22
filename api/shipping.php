<?php
/**
 * AJAX endpoint: returns the shipping fee for a given wilaya + delivery type.
 * Called from checkout.php via fetch() every time the customer picks a wilaya.
 *
 * Tries the ZR Express (Procolis) "tarification" API first (real-time official rates).
 * If no API credentials are set yet, or the API call fails for any reason,
 * it automatically falls back to the static price table in includes/functions.php
 * so the checkout NEVER breaks.
 *
 * IMPORTANT: ZR Express requires a professional account. Once you have one,
 * log into your ZR Express dashboard, open their API documentation page, and confirm:
 *   1) the exact request header names they expect (commonly "token" and "key",
 *      shown here as an example - some accounts use "id" + "token")
 *   2) the exact tarification endpoint URL and response field names
 * Then adjust the getZRExpressFee() function below - everything else keeps working.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

$wilayaId   = isset($_GET['wilaya_id']) ? (int)$_GET['wilaya_id'] : 0;
$deliveryType = isset($_GET['type']) && $_GET['type'] === 'stopdesk' ? 'stopdesk' : 'domicile';

$wilayas = getWilayas();
if (!isset($wilayas[$wilayaId])) {
    echo json_encode(['success' => false, 'message' => 'Wilaya invalide']);
    exit;
}
$wilayaName = $wilayas[$wilayaId];

function getZRExpressFee($wilayaId, $wilayaName, $deliveryType) {
    if (empty(ZREXPRESS_TOKEN) || empty(ZREXPRESS_KEY)) {
        return null; // not configured yet -> caller will use fallback
    }

    $ch = curl_init(ZREXPRESS_TARIF_URL);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 6,
        CURLOPT_HTTPHEADER => [
            'token: ' . ZREXPRESS_TOKEN,
            'key: ' . ZREXPRESS_KEY,
            'Content-Type: application/json',
        ],
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if (!$response || $httpCode !== 200) return null;

    $data = json_decode($response, true);
    if (!is_array($data)) return null;

    // ZR Express returns a list of wilayas with "Domicile" / "Stopdesk" fee fields.
    // Match by wilaya name (case-insensitive) - adjust the field names below
    // to match your account's exact response once you can inspect it.
    foreach ($data as $row) {
        $rowWilaya = $row['Wilaya'] ?? $row['wilaya'] ?? '';
        if (mb_stripos($rowWilaya, $wilayaName) !== false) {
            if ($deliveryType === 'stopdesk' && isset($row['Tarif_Stopdesk'])) {
                return (float)$row['Tarif_Stopdesk'];
            }
            if (isset($row['Tarif'])) {
                return (float)$row['Tarif'];
            }
        }
    }
    return null;
}

$fee = getZRExpressFee($wilayaId, $wilayaName, $deliveryType);
$source = 'zrexpress';

if ($fee === null) {
    $fee = getFallbackShippingFee($wilayaName, $deliveryType);
    $source = 'fallback';
}

echo json_encode([
    'success' => true,
    'wilaya' => $wilayaName,
    'delivery_type' => $deliveryType,
    'fee' => $fee,
    'fee_formatted' => formatPrice($fee),
    'source' => $source, // "zrexpress" = live API rate, "fallback" = local static table
]);
