<?php
// ============================================================
// GLOBAL CONFIG - edit these values for your setup
// ============================================================
session_start();

define('DB_HOST', 'localhost');
define('DB_NAME', 'ontime_shop');
define('DB_USER', 'root');
define('DB_PASS', '');          // default XAMPP MySQL password is empty

define('SITE_NAME', 'Ontime');
define('SITE_URL', 'http://localhost/WEBSITES/OnTime'); // change when you deploy online
define('CURRENCY', 'DA');

define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('UPLOAD_URL', SITE_URL . '/uploads/');

// ZR Express API credentials (get these from your ZR Express professional account)
// Docs: https://procolis.com  -- token + key are sent as headers on every request
define('ZREXPRESS_TOKEN', '');   // <-- put your token here
define('ZREXPRESS_KEY', '');     // <-- put your key here
define('ZREXPRESS_TARIF_URL', 'https://procolis.com/api_v1/tarification');
define('ZREXPRESS_ADD_COLIS_URL', 'https://procolis.com/api_v1/add_colis');

error_reporting(E_ALL);
ini_set('display_errors', 1);
