<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Mon panier';
$pdo = getDB();

// --- Handle cart actions (add / update / remove) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $pid = (int)($_POST['product_id'] ?? 0);
        $qty = max(1, (int)($_POST['qty'] ?? 1));
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND active = 1");
        $stmt->execute([$pid]);
        $prod = $stmt->fetch();
        if ($prod) {
            if (!isset($_SESSION['cart'][$pid])) {
                $_SESSION['cart'][$pid] = ['qty' => 0];
            }
            $_SESSION['cart'][$pid]['qty'] = min($prod['stock'], $_SESSION['cart'][$pid]['qty'] + $qty);
        }
    } elseif ($action === 'update') {
        $pid = (int)($_POST['product_id'] ?? 0);
        $qty = max(1, (int)($_POST['qty'] ?? 1));
        if (isset($_SESSION['cart'][$pid])) $_SESSION['cart'][$pid]['qty'] = $qty;
    } elseif ($action === 'remove') {
        $pid = (int)($_POST['product_id'] ?? 0);
        unset($_SESSION['cart'][$pid]);
    }
    header('Location: cart.php');
    exit;
}

// --- Build cart display data from DB (always fresh price/stock) ---
$cartItems = [];
$subtotal = 0;
if (!empty($_SESSION['cart'])) {
    $ids = array_keys($_SESSION['cart']);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
    $stmt->execute($ids);
    $products = [];
    foreach ($stmt->fetchAll() as $row) $products[$row['id']] = $row;
    foreach ($_SESSION['cart'] as $pid => $item) {
        if (!isset($products[$pid])) continue;
        $p = $products[$pid];
        $line = $p['price'] * $item['qty'];
        $subtotal += $line;
        $cartItems[] = ['product' => $p, 'qty' => $item['qty'], 'line_total' => $line];
    }
}

include __DIR__ . '/includes/header.php';
?>
<section class="section">
    <div class="container">
        <div class="section-title" style="margin-bottom:30px;">
            <h2>Mon panier</h2>
        </div>

        <?php if (empty($cartItems)): ?>
            <div class="empty-state">
                Votre panier est vide.<br><br>
                <a href="index.php" class="btn btn-dark">Continuer mes achats</a>
            </div>
        <?php else: ?>
        <table class="cart-table">
            <thead><tr><th>Produit</th><th>Prix</th><th>Quantite</th><th>Total</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($cartItems as $item): $p = $item['product']; ?>
                <tr>
                    <td>
                        <div class="prod-cell">
                            <?php if ($p['image']): ?><img src="<?= UPLOAD_URL . htmlspecialchars($p['image']) ?>" alt=""><?php endif; ?>
                            <span><?= htmlspecialchars($p['name']) ?></span>
                        </div>
                    </td>
                    <td><?= formatPrice($p['price']) ?></td>
                    <td>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
                            <input type="number" name="qty" value="<?= (int)$item['qty'] ?>" min="1" max="<?= (int)$p['stock'] ?>"
                                   style="width:60px;padding:6px;border-radius:6px;border:1px solid #e4e7ec;"
                                   onchange="this.form.submit()">
                        </form>
                    </td>
                    <td><?= formatPrice($item['line_total']) ?></td>
                    <td>
                        <form method="post">
                            <input type="hidden" name="action" value="remove">
                            <input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
                            <button type="submit" class="remove-link">Retirer</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <div style="max-width:340px;margin-left:auto;margin-top:24px;">
            <div class="summary-row total"><span>Sous-total</span><span><?= formatPrice($subtotal) ?></span></div>
            <a href="checkout.php" class="btn btn-primary btn-block" style="margin-top:16px;">Passer la commande</a>
        </div>
        <?php endif; ?>
    </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
