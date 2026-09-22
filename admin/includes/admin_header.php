<?php
require_once __DIR__ . '/../../includes/functions.php';
requireAdmin();
$current = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - Admin Ontime' : 'Admin Ontime' ?></title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>
<div class="admin-layout">
    <aside class="sidebar">
        <div class="brand">ON<span>TIME</span></div>
        <nav>
            <a href="dashboard.php" class="<?= $current === 'dashboard.php' ? 'active' : '' ?>">📊 Analytique</a>
            <a href="products.php" class="<?= in_array($current, ['products.php','product_form.php']) ? 'active' : '' ?>">⌚ Produits</a>
            <a href="orders.php" class="<?= in_array($current, ['orders.php','order_view.php']) ? 'active' : '' ?>">📦 Commandes</a>
            <a href="settings.php" class="<?= $current === 'settings.php' ? 'active' : '' ?>">⚙️ Parametres</a>
            <a href="../index.php" target="_blank">🔗 Voir la boutique</a>
        </nav>
        <div class="logout">
            <a href="logout.php" class="btn btn-outline btn-block">Deconnexion</a>
        </div>
    </aside>
    <main class="main">
