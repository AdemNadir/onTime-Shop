<?php
require_once __DIR__ . '/includes/functions.php';

$slug = $_GET['slug'] ?? '';
$pdo = getDB();
$stmt = $pdo->prepare("SELECT p.*, c.name AS cat_name FROM products p
                        LEFT JOIN categories c ON p.category_id = c.id
                        WHERE p.slug = ? AND p.active = 1");
$stmt->execute([$slug]);
$product = $stmt->fetch();

if (!$product) {
    header('Location: index.php');
    exit;
}
$pageTitle = $product['name'];
include __DIR__ . '/includes/header.php';
?>
<section class="section">
    <div class="container">
        <div class="product-detail">
            <div class="gallery">
                <?php if ($product['image']): ?>
                    <img src="<?= UPLOAD_URL . htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                <?php else: ?>
                    <div class="img-wrap" style="aspect-ratio:1/1;display:flex;align-items:center;justify-content:center;border-radius:14px;">
                        <span style="font-size:70px;">⌚</span>
                    </div>
                <?php endif; ?>
            </div>
            <div>
                <div class="cat"><?= htmlspecialchars($product['cat_name'] ?? '') ?></div>
                <h1><?= htmlspecialchars($product['name']) ?></h1>
                <div class="price"><?= formatPrice($product['price']) ?></div>
                <p class="desc"><?= nl2br(htmlspecialchars($product['description'])) ?></p>

                <?php if ($product['stock'] > 0): ?>
                <p class="stock-info">✓ En stock (<?= (int)$product['stock'] ?> disponibles)</p>
                <form method="post" action="cart.php">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>">
                    <div class="qty-selector" data-qty-group>
                        <button type="button" data-qty-minus>-</button>
                        <input type="number" name="qty" value="1" min="1" max="<?= (int)$product['stock'] ?>" data-qty-input>
                        <button type="button" data-qty-plus>+</button>
                    </div>
                    <button type="submit" class="btn btn-dark btn-block">Ajouter au panier</button>
                </form>
                <?php else: ?>
                    <p class="stock-info" style="color:#d92d20;">Rupture de stock</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
