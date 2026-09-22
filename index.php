
<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Accueil';

$pdo = getDB();
$products = $pdo->query("SELECT p.*, c.name AS cat_name FROM products p
                          LEFT JOIN categories c ON p.category_id = c.id
                          WHERE p.active = 1 ORDER BY p.created_at DESC")->fetchAll();
$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();

include __DIR__ . '/includes/header.php';
?>


<section class="hero">
    <div class="container">
        <div class="hero-text">
            <span class="hero-eyebrow">Nouvelle collection 2026</span>
            <h1>Le temps, <span>avec elegance.</span></h1>
            <p>Decouvrez la collection Ontime - des montres au design raffine, livrees partout en Algerie en 24/48h.</p>
            <div class="hero-actions">
                <a href="#produits" class="btn btn-primary">Voir la collection</a>
                <a href="#contact" class="btn btn-outline">Nous contacter</a>
            </div>
            <div class="hero-stats">
                <div><strong><?= count($products) ?>+</strong><span>Modeles disponibles</span></div>
                <div><strong>58</strong><span>Wilayas livrees</span></div>
                <div><strong>4.8/5</strong><span>Satisfaction client</span></div>
            </div>
        </div>
        <div class="hero-visual">
            <div id="watch3d"></div>
            <div class="hero-visual-hint">GLISSEZ POUR FAIRE TOURNER</div>
        </div>
    </div>
</section>

<div class="trust-bar">
    <div class="container">
        <div class="trust-item"><span class="icon">🚚</span><div><strong>Livraison rapide</strong><span>Partout en Algerie via ZR Express</span></div></div>
        <div class="trust-item"><span class="icon">💳</span><div><strong>Paiement a la livraison</strong><span>Payez en recevant votre colis</span></div></div>
        <div class="trust-item"><span class="icon">🛡️</span><div><strong>Garantie qualite</strong><span>Produits verifies et garantis</span></div></div>
        <div class="trust-item"><span class="icon">↩️</span><div><strong>Retour facile</strong><span>Echange sous 7 jours</span></div></div>
    </div>
</div>

<section class="section" id="produits">
    <div class="container">
        <div class="section-title">
            <span class="eyebrow">Notre collection</span>
            <h2>Montres Ontime</h2>
            <p>Chaque piece est selectionnee pour son design et sa qualite.</p>
        </div>

        <div class="filters">
            <button class="filter-chip active" data-category="all">Tous</button>
            <?php foreach ($categories as $cat): ?>
                <button class="filter-chip" data-category="<?= (int)$cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></button>
            <?php endforeach; ?>
        </div>

        <?php if (empty($products)): ?>
            <div class="empty-state">Aucun produit disponible pour le moment. Ajoutez-en depuis le tableau de bord admin.</div>
        <?php else: ?>
        <div class="product-grid">
            <?php foreach ($products as $p): ?>
            <div class="product-card" data-category="<?= (int)$p['category_id'] ?>">
                <a href="product.php?slug=<?= urlencode($p['slug']) ?>">
                    <div class="img-wrap">
                        <?php if ($p['image']): ?>
                            <img src="<?= UPLOAD_URL . htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>">
                        <?php else: ?>
                            <span style="font-size:40px;">⌚</span>
                        <?php endif; ?>
                        <?php if ($p['stock'] <= 5 && $p['stock'] > 0): ?><span class="badge">Stock limite</span><?php endif; ?>
                    </div>
                </a>
                <div class="info">
                    <div class="cat"><?= htmlspecialchars($p['cat_name'] ?? '') ?></div>
                    <a href="product.php?slug=<?= urlencode($p['slug']) ?>"><h3><?= htmlspecialchars($p['name']) ?></h3></a>
                    <div class="price"><?= formatPrice($p['price']) ?></div>
                    <form method="post" action="cart.php">
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
                        <button type="submit" class="add-btn" <?= $p['stock'] <= 0 ? 'disabled' : '' ?>>
                            <?= $p['stock'] <= 0 ? 'Rupture de stock' : 'Ajouter au panier' ?>
                        </button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
<script type="module" src="assets/js/watch3d.js"></script>
