<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Commande confirmee';
$orderId = $_SESSION['last_order_id'] ?? null;
if (!$orderId) { header('Location: index.php'); exit; }

$pdo = getDB();
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$orderId]);
$order = $stmt->fetch();
include __DIR__ . '/includes/header.php';
?>
<section class="section">
    <div class="container" style="max-width:560px;text-align:center;">
        <div style="font-size:60px;margin-bottom:16px;">✅</div>
        <h2 style="color:var(--navy-800);margin-bottom:10px;">Merci, <?= htmlspecialchars($order['customer_name']) ?> !</h2>
        <p style="color:var(--text-muted);margin-bottom:26px;">
            Votre commande #<?= $order['id'] ?> a bien ete enregistree. Notre equipe vous contactera au
            <?= htmlspecialchars($order['phone']) ?> pour confirmation avant l'expedition.
        </p>
        <div class="summary-card" style="text-align:left;margin-bottom:24px;">
            <div class="summary-row"><span>Wilaya</span><span><?= htmlspecialchars($order['wilaya']) ?></span></div>
            <div class="summary-row"><span>Livraison</span><span><?= formatPrice($order['shipping_fee']) ?></span></div>
            <div class="summary-row total"><span>Total</span><span><?= formatPrice($order['total']) ?></span></div>
        </div>
        <a href="index.php" class="btn btn-dark">Retour a la boutique</a>
    </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
