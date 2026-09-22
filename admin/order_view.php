<?php
$pageTitle = 'Commande';
require_once __DIR__ . '/includes/admin_header.php';
$pdo = getDB();

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$id]);
$order = $stmt->fetch();
if (!$order) { header('Location: orders.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newStatus = $_POST['status'] ?? $order['status'];
    $tracking = trim($_POST['tracking_number'] ?? '');
    $notes = trim($_POST['notes'] ?? '');
    $stmt = $pdo->prepare("UPDATE orders SET status=?, tracking_number=?, notes=? WHERE id=?");
    $stmt->execute([$newStatus, $tracking, $notes, $id]);
    header("Location: order_view.php?id=$id&updated=1");
    exit;
}

$items = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
$items->execute([$id]);
$items = $items->fetchAll();
?>
<div class="topbar">
    <h1>Commande #<?= $order['id'] ?></h1>
    <a href="orders.php" class="btn btn-outline">← Retour</a>
</div>

<?php if (isset($_GET['updated'])): ?><div class="alert alert-success">Commande mise a jour.</div><?php endif; ?>

<div style="display:grid;grid-template-columns:1.3fr 0.7fr;gap:24px;align-items:start;">
    <div class="card">
        <h3>Articles</h3>
        <table class="data-table">
            <thead><tr><th>Produit</th><th>Prix</th><th>Qte</th><th>Total</th></tr></thead>
            <tbody>
            <?php foreach ($items as $it): ?>
                <tr>
                    <td><?= htmlspecialchars($it['product_name']) ?></td>
                    <td><?= formatPrice($it['price']) ?></td>
                    <td><?= (int)$it['quantity'] ?></td>
                    <td><?= formatPrice($it['subtotal']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <div style="margin-top:16px;max-width:280px;margin-left:auto;">
            <div style="display:flex;justify-content:space-between;padding:6px 0;font-size:14px;color:var(--text-muted);"><span>Sous-total</span><span><?= formatPrice($order['subtotal']) ?></span></div>
            <div style="display:flex;justify-content:space-between;padding:6px 0;font-size:14px;color:var(--text-muted);"><span>Livraison</span><span><?= formatPrice($order['shipping_fee']) ?></span></div>
            <div style="display:flex;justify-content:space-between;padding:10px 0;font-size:17px;font-weight:700;color:var(--navy-800);border-top:1.5px solid #eef0f3;"><span>Total</span><span><?= formatPrice($order['total']) ?></span></div>
        </div>
    </div>

    <div>
        <div class="card">
            <h3>Client</h3>
            <p><strong><?= htmlspecialchars($order['customer_name']) ?></strong></p>
            <p>📞 <?= htmlspecialchars($order['phone']) ?></p>
            <p>📍 <?= htmlspecialchars($order['wilaya']) ?><?= $order['commune'] ? ', ' . htmlspecialchars($order['commune']) : '' ?></p>
            <?php if ($order['address']): ?><p style="color:var(--text-muted);font-size:13.5px;margin-top:6px;"><?= nl2br(htmlspecialchars($order['address'])) ?></p><?php endif; ?>
            <p style="margin-top:10px;font-size:13px;color:var(--text-muted);">Livraison: <?= $order['delivery_type'] === 'stopdesk' ? 'Stop Desk' : 'A domicile' ?></p>
        </div>

        <div class="card">
            <h3>Statut & suivi</h3>
            <form method="post">
                <div class="form-row">
                    <label>Statut</label>
                    <select name="status">
                        <?php foreach (['pending'=>'En attente','confirmed'=>'Confirmee','shipped'=>'Expediee','delivered'=>'Livree','cancelled'=>'Annulee'] as $val => $label): ?>
                            <option value="<?= $val ?>" <?= $order['status'] === $val ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-row">
                    <label>N° de suivi ZR Express</label>
                    <input type="text" name="tracking_number" value="<?= htmlspecialchars($order['tracking_number'] ?? '') ?>" placeholder="ex: CourierDz-123">
                </div>
                <div class="form-row">
                    <label>Notes internes</label>
                    <textarea name="notes" rows="3"><?= htmlspecialchars($order['notes'] ?? '') ?></textarea>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Mettre a jour</button>
            </form>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
