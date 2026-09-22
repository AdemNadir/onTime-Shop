<?php
require_once __DIR__ . '/../config/db.php';

function formatPrice($price) {
    return number_format((float)$price, 0, ',', '.') . ' ' . CURRENCY;
}

function getSetting($key, $default = '') {
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
    $stmt->execute([$key]);
    $row = $stmt->fetch();
    return $row ? $row['setting_value'] : $default;
}

function updateSetting($key, $value) {
    $pdo = getDB();
    $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
                            ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
    $stmt->execute([$key, $value]);
}

// --- Admin auth helpers ---
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']);
}

function requireAdmin() {
    if (!isAdminLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

// --- Cart helpers (session based, no DB needed for cart itself) ---
function getCart() {
    if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
    return $_SESSION['cart'];
}

function cartCount() {
    $count = 0;
    foreach (getCart() as $item) $count += $item['qty'];
    return $count;
}

// --- Algeria's 58 wilayas, with approximate fallback delivery fees (DA) ---
// These are used ONLY if the ZR Express API call fails or credentials are not set yet.
// Once you add your ZREXPRESS_TOKEN / ZREXPRESS_KEY in config/config.php, real-time
// rates from ZR Express are used automatically (see api/shipping.php).
function getWilayas() {
    return [
        1 => 'Adrar', 2 => 'Chlef', 3 => 'Laghouat', 4 => 'Oum El Bouaghi', 5 => 'Batna',
        6 => 'Bejaia', 7 => 'Biskra', 8 => 'Bechar', 9 => 'Blida', 10 => 'Bouira',
        11 => 'Tamanrasset', 12 => 'Tebessa', 13 => 'Tlemcen', 14 => 'Tiaret', 15 => 'Tizi Ouzou',
        16 => 'Alger', 17 => 'Djelfa', 18 => 'Jijel', 19 => 'Setif', 20 => 'Saida',
        21 => 'Skikda', 22 => 'Sidi Bel Abbes', 23 => 'Annaba', 24 => 'Guelma', 25 => 'Constantine',
        26 => 'Medea', 27 => 'Mostaganem', 28 => 'M\'Sila', 29 => 'Mascara', 30 => 'Ouargla',
        31 => 'Oran', 32 => 'El Bayadh', 33 => 'Illizi', 34 => 'Bordj Bou Arreridj', 35 => 'Boumerdes',
        36 => 'El Tarf', 37 => 'Tindouf', 38 => 'Tissemsilt', 39 => 'El Oued', 40 => 'Khenchela',
        41 => 'Souk Ahras', 42 => 'Tipaza', 43 => 'Mila', 44 => 'Ain Defla', 45 => 'Naama',
        46 => 'Ain Temouchent', 47 => 'Ghardaia', 48 => 'Relizane', 49 => 'Timimoun',
        50 => 'Bordj Badji Mokhtar', 51 => 'Ouled Djellal', 52 => 'Beni Abbes', 53 => 'In Salah',
        54 => 'In Guezzam', 55 => 'Touggourt', 56 => 'Djanet', 57 => 'El M\'Ghair', 58 => 'El Meniaa',
    ];
}

// Fallback flat fees per wilaya zone (edit freely) - used only when ZR Express API is not reachable
function getFallbackShippingFee($wilayaName, $deliveryType = 'domicile') {
    $nearby = ['Skikda', 'Constantine', 'Annaba', 'Guelma', 'Jijel', 'Mila', 'Souk Ahras', 'El Tarf'];
    $far    = ['Tamanrasset', 'Adrar', 'Illizi', 'Tindouf', 'In Salah', 'In Guezzam', 'Bordj Badji Mokhtar', 'Djanet'];

    if (in_array($wilayaName, $nearby)) $base = 400;
    elseif (in_array($wilayaName, $far)) $base = 1400;
    else $base = 700;

    if ($deliveryType === 'stopdesk') $base -= 100; // stopdesk usually cheaper than home delivery
    return max($base, 200);
}
