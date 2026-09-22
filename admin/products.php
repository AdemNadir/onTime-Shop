<?php
$pageTitle = 'Produits';
require_once __DIR__ . '/includes/admin_header.php';
$pdo = getDB();

if (isset($_GET['toggle'])) {
    $pdo->prepare("UPDATE products SET active = 1 - active WHERE id = ?")->execute([(int)$_GET['toggle']]);
    header('Location: products.php'); exit;
}

$products = $pdo->query("SELECT p.*, c.name AS cat_name FROM products p
                          LEFT JOIN categories c ON p.category_id = c.id
                          ORDER BY p.created_at DESC")->fetchAll();
?>
<div class="topbar">
    <h1>Produits</h1>
    <a href="product_form.php" class="btn btn-primary">+ Nouveau produit</a>
</div>

<div class="card">
    <div class="toolbar">
        <input type="text" class="search-input" placeholder="Rechercher un produit..." data-search-input="#products-table">
        <span style="color:var(--text-muted);font-size:13.5px;"><?= count($products) ?> produits</span>
    </div>
    <table class="data-table" id="products-table">
        <thead><tr><th></th><th>Nom</th><th>Categorie</th><th>Prix</th><th>Cout</th><th>Stock</th><th>Statut</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($products as $p): ?>
            <tr>
                <td>
                    <?php if ($p['image']): ?>
                        <img class="thumb" src="<?= UPLOAD_URL . htmlspecialchars($p['image']) ?>">
                    <?php else: ?>
                        <div class="thumb" style="display:flex;align-items:center;justify-content:center;">⌚</div>
                    <?php endif; ?>
                </td>
                <td><strong><?= htmlspecialchars($p['name']) ?></strong></td>
                <td><?= htmlspecialchars($p['cat_name'] ?? '-') ?></td>
                <td><?= formatPrice($p['price']) ?></td>
                <td><?= formatPrice($p['cost_price']) ?></td>
                <td><?= (int)$p['stock'] ?></td>
                <td>
                    <a href="?toggle=<?= $p['id'] ?>" class="status-badge <?= $p['active'] ? 'status-delivered' : 'status-cancelled' ?>">
                        <?= $p['active'] ? 'Actif' : 'Masque' ?>
                    </a>
                </td>
                <td>
                    <a href="product_form.php?id=<?= $p['id'] ?>" class="btn btn-outline btn-sm">Modifier</a>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($products)): ?><tr><td colspan="8" style="text-align:center;color:var(--text-muted);">Aucun produit. Cliquez sur "Nouveau produit" pour commencer.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>
<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
