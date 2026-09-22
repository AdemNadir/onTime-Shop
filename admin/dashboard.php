<?php
$pageTitle = 'Analytique';
require_once __DIR__ . '/includes/admin_header.php';
$pdo = getDB();

// Only count non-cancelled orders in revenue/profit
$where = "status != 'cancelled'";

$totals = $pdo->query("SELECT COUNT(*) AS order_count, COALESCE(SUM(total),0) AS revenue,
                        COALESCE(SUM(shipping_fee),0) AS shipping_total
                        FROM orders WHERE $where")->fetch();

$productStats = $pdo->query("SELECT COALESCE(SUM(oi.subtotal),0) AS product_revenue,
                              COALESCE(SUM(oi.cost_price * oi.quantity),0) AS product_cost,
                              COALESCE(SUM(oi.quantity),0) AS units_sold
                              FROM order_items oi
                              JOIN orders o ON o.id = oi.order_id
                              WHERE o.$where")->fetch();

$profit = $productStats['product_revenue'] - $productStats['product_cost'];

$pendingCount = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn();
$lowStock = $pdo->query("SELECT COUNT(*) FROM products WHERE stock <= 5 AND active = 1")->fetchColumn();

// Revenue for last 14 days (for the chart)
$daily = $pdo->query("SELECT DATE(created_at) AS d, COALESCE(SUM(total),0) AS rev
                       FROM orders WHERE $where AND created_at >= DATE_SUB(CURDATE(), INTERVAL 13 DAY)
                       GROUP BY DATE(created_at) ORDER BY d")->fetchAll();
$dailyMap = [];
foreach ($daily as $row) $dailyMap[$row['d']] = (float)$row['rev'];
$chartLabels = [];
$chartData = [];
for ($i = 13; $i >= 0; $i--) {
    $d = date('Y-m-d', strtotime("-$i days"));
    $chartLabels[] = date('d/m', strtotime($d));
    $chartData[] = $dailyMap[$d] ?? 0;
}

// Top 5 products by units sold
$topProducts = $pdo->query("SELECT oi.product_name, SUM(oi.quantity) AS units, SUM(oi.subtotal) AS revenue,
                             SUM(oi.subtotal) - SUM(oi.cost_price * oi.quantity) AS profit
                             FROM order_items oi JOIN orders o ON o.id = oi.order_id
                             WHERE o.$where
                             GROUP BY oi.product_name ORDER BY units DESC LIMIT 5")->fetchAll();

// Recent orders
$recentOrders = $pdo->query("SELECT * FROM orders ORDER BY created_at DESC LIMIT 6")->fetchAll();
?>
<div class="topbar">
    <h1>Analytique</h1>
    <div class="user">Connecte: <strong><?= htmlspecialchars($_SESSION['admin_username']) ?></strong></div>
</div>

<div class="stat-grid">
    <div class="stat-card">
        <div class="label">Chiffre d'affaires</div>
        <div class="value"><?= formatPrice($totals['revenue']) ?></div>
        <div class="sub"><?= (int)$totals['order_count'] ?> commandes (hors annulees)</div>
    </div>
    <div class="stat-card">
        <div class="label">Profit net produits</div>
        <div class="value profit"><?= formatPrice($profit) ?></div>
        <div class="sub">Cout total: <?= formatPrice($productStats['product_cost']) ?></div>
    </div>
    <div class="stat-card">
        <div class="label">Unites vendues</div>
        <div class="value"><?= (int)$productStats['units_sold'] ?></div>
        <div class="sub">Toutes commandes confondues</div>
    </div>
    <div class="stat-card">
        <div class="label">A traiter</div>
        <div class="value" style="color:#b54708;"><?= (int)$pendingCount ?></div>
        <div class="sub"><?= (int)$lowStock ?> produits en stock faible</div>
    </div>
</div>

<div class="card">
    <h3>Chiffre d'affaires - 14 derniers jours</h3>
    <canvas id="revenueChart" height="80"></canvas>
</div>

<div style="display:grid;grid-template-columns:1.2fr 1fr;gap:24px;align-items:start;">
    <div class="card">
        <h3>Commandes recentes</h3>
        <table class="data-table">
            <thead><tr><th>#</th><th>Client</th><th>Wilaya</th><th>Total</th><th>Statut</th></tr></thead>
            <tbody>
            <?php foreach ($recentOrders as $o): ?>
                <tr onclick="location.href='order_view.php?id=<?= $o['id'] ?>'" style="cursor:pointer;">
                    <td>#<?= $o['id'] ?></td>
                    <td><?= htmlspecialchars($o['customer_name']) ?></td>
                    <td><?= htmlspecialchars($o['wilaya']) ?></td>
                    <td><?= formatPrice($o['total']) ?></td>
                    <td><span class="status-badge status-<?= $o['status'] ?>"><?= $o['status'] ?></span></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($recentOrders)): ?><tr><td colspan="5" style="text-align:center;color:var(--text-muted);">Aucune commande pour le moment</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="card">
        <h3>Top produits (unites vendues)</h3>
        <table class="data-table">
            <thead><tr><th>Produit</th><th>Vendus</th><th>Profit</th></tr></thead>
            <tbody>
            <?php foreach ($topProducts as $tp): ?>
                <tr>
                    <td><?= htmlspecialchars($tp['product_name']) ?></td>
                    <td><?= (int)$tp['units'] ?></td>
                    <td style="color:#2a9d5c;font-weight:600;"><?= formatPrice($tp['profit']) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($topProducts)): ?><tr><td colspan="3" style="text-align:center;color:var(--text-muted);">Pas encore de ventes</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/[email protected]/dist/chart.umd.min.js"></script>
<script>
new Chart(document.getElementById('revenueChart'), {
    type: 'line',
    data: {
        labels: <?= json_encode($chartLabels) ?>,
        datasets: [{
            label: 'Chiffre d\'affaires (<?= CURRENCY ?>)',
            data: <?= json_encode($chartData) ?>,
            borderColor: '#c9a876',
            backgroundColor: 'rgba(201,168,118,0.15)',
            fill: true,
            tension: 0.35,
            pointRadius: 3,
        }]
    },
    options: {
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true } }
    }
});
</script>
<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
