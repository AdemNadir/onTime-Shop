<?php require_once __DIR__ . '/functions.php'; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - Ontime' : 'Ontime - Montres Elegantes' ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
</head>
<body>
<nav class="navbar">
    <div class="container">
        <a href="<?= SITE_URL ?>/index.php" class="logo">ON<span>TIME</span></a>
        <ul class="nav-links">
            <li><a href="<?= SITE_URL ?>/index.php">Accueil</a></li>
            <li><a href="<?= SITE_URL ?>/index.php#produits">Collection</a></li>
            <li><a href="<?= SITE_URL ?>/index.php#contact">Contact</a></li>
        </ul>
        <a href="<?= SITE_URL ?>/cart.php" class="nav-cart">
            🛒 Panier <span class="cart-badge"><?= cartCount() ?></span>
        </a>
    </div>
</nav>
