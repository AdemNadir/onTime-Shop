<?php
$pageTitle = 'Commandes';
require_once __DIR__ . '/includes/admin_header.php';
$pdo = getDB();

$statusFilter = $_GET['status'] ?? '';
$sql = "SELECT * FROM orders";
$params = [];
if ($statusFilter && in_array($statusFilter, ['pending','confirmed','shipped','delivered','cancelled'])) {
    $sql .= " WHERE status = ?";
    $params[] = $statusFilter;
}
$sql .= " ORDER BY created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();
?>
<div class="topbar"><h1>Commandes</h1></div>

<div class="card">
    <div class="toolbar">
        <input type="text" class="search-input" placeholder="Rechercher..." data-search-input="#orders-table">
        <div style="display:flex;gap:8px;flex-wrap:wrap;">
            <a href="orders.php" class="btn btn-sm <?= !$statusFilter ? 'btn-primary' : 'btn-outline' ?>">Tous</a>
            <a href="orders.php?status=pending" class="btn btn-sm <?= $statusFilter==='pending' ? 'btn-primary' : 'btn-outline' ?>">En attente</a>
            <a href="orders.php?status=confirmed" class="btn btn-sm <?= $statusFilter==='confirmed' ? 'btn-primary' : 'btn-outline' ?>">Confirmees</a>
            <a href="orders.php?status=shipped" class="btn btn-sm <?= $statusFilter==='shipped' ? 'btn-primary' : 'btn-outline' ?>">Expediees</a>
            <a href="orders.php?status=delivered" class="btn btn-sm <?= $statusFilter==='delivered' ? 'btn-primary' : 'btn-outline' ?>">Livrees</a>
        </div>
    </div>
    <table class="data-table" id="orders-table">
        <thead><tr><th>#</th><th>Client</th><th>Telephone</th><th>Wilaya</th><th>Total</th><th>Statut</th><th>Date</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($orders as $o): ?>
            <tr>
                <td>#<?= $o['id'] ?></td>
                <td><?= htmlspecialchars($o['customer_name']) ?></td>
                <td><?= htmlspecialchars($o['phone']) ?></td>
                <td><?= htmlspecialchars($o['wilaya']) ?></td>
                <td><?= formatPrice($o['total']) ?></td>
                <td><span class="status-badge status-<?= $o['status'] ?>"><?= $o['status'] ?></span></td>
                <td><?= date('d/m/Y', strtotime($o['created_at'])) ?></td>
                <td><a href="order_view.php?id=<?= $o['id'] ?>" class="btn btn-outline btn-sm">Voir</a></td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($orders)): ?><tr><td colspan="8" style="text-align:center;color:var(--text-muted);">Aucune commande</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>
<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
