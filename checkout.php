<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Finaliser la commande';
$pdo = getDB();

// Build cart data
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
if (empty($cartItems)) {
    header('Location: cart.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $wilayaId = (int)($_POST['wilaya_id'] ?? 0);
    $commune = trim($_POST['commune'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $deliveryType = ($_POST['delivery_type'] ?? 'domicile') === 'stopdesk' ? 'stopdesk' : 'domicile';
    $shippingFee = (float)($_POST['shipping_fee'] ?? 0);

    $wilayas = getWilayas();
    if ($name === '') $errors[] = 'Veuillez entrer votre nom complet.';
    if ($phone === '' || !preg_match('/^0[5-7][0-9]{8}$/', $phone)) $errors[] = 'Numero de telephone invalide (ex: 0555123456).';
    if (!isset($wilayas[$wilayaId])) $errors[] = 'Veuillez choisir une wilaya.';
    if ($deliveryType === 'domicile' && $address === '') $errors[] = 'Veuillez entrer votre adresse pour la livraison a domicile.';

    if (empty($errors)) {
        $wilayaName = $wilayas[$wilayaId];
        $total = $subtotal + $shippingFee;

        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("INSERT INTO orders (customer_name, phone, wilaya, commune, address, delivery_type, shipping_fee, subtotal, total, status)
                                    VALUES (?,?,?,?,?,?,?,?,?, 'pending')");
            $stmt->execute([$name, $phone, $wilayaName, $commune, $address, $deliveryType, $shippingFee, $subtotal, $total]);
            $orderId = $pdo->lastInsertId();

            $itemStmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, product_name, price, cost_price, quantity, subtotal)
                                        VALUES (?,?,?,?,?,?,?)");
            $stockStmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");

            foreach ($cartItems as $item) {
                $p = $item['product'];
                $itemStmt->execute([$orderId, $p['id'], $p['name'], $p['price'], $p['cost_price'], $item['qty'], $item['line_total']]);
                $stockStmt->execute([$item['qty'], $p['id']]);
            }

            $pdo->commit();
            $_SESSION['cart'] = [];
            $_SESSION['last_order_id'] = $orderId;
            header('Location: order-confirmation.php');
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = 'Une erreur est survenue, veuillez reessayer.';
        }
    }
}

include __DIR__ . '/includes/header.php';
?>
<section class="section">
    <div class="container">
        <div class="section-title" style="margin-bottom:30px;"><h2>Finaliser la commande</h2></div>

        <?php foreach ($errors as $err): ?><div class="alert alert-error"><?= htmlspecialchars($err) ?></div><?php endforeach; ?>

        <form method="post" class="checkout-grid" id="checkout-form">
            <div class="form-card">
                <div class="form-row">
                    <label>Nom complet</label>
                    <input type="text" name="name" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                </div>
                <div class="form-row">
                    <label>Telephone</label>
                    <input type="tel" name="phone" placeholder="0555123456" required value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                </div>
                <div class="form-row-2">
                    <div class="form-row">
                        <label>Wilaya</label>
                        <select name="wilaya_id" id="wilaya_id" required>
                            <option value="">-- Choisir --</option>
                            <?php foreach (getWilayas() as $id => $name): ?>
                                <option value="<?= $id ?>" <?= (isset($_POST['wilaya_id']) && $_POST['wilaya_id'] == $id) ? 'selected' : '' ?>>
                                    <?= $id ?> - <?= htmlspecialchars($name) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-row">
                        <label>Commune</label>
                        <input type="text" name="commune" value="<?= htmlspecialchars($_POST['commune'] ?? '') ?>">
                    </div>
                </div>
                <div class="form-row">
                    <label>Type de livraison</label>
                    <div class="delivery-toggle">
                        <label><input type="radio" name="delivery_type" id="dt-domicile" value="domicile" checked><span>🏠 A domicile</span></label>
                        <label><input type="radio" name="delivery_type" id="dt-stopdesk" value="stopdesk"><span>🏢 Stop Desk</span></label>
                    </div>
                </div>
                <div class="form-row">
                    <label>Adresse complete</label>
                    <textarea name="address" rows="3"><?= htmlspecialchars($_POST['address'] ?? '') ?></textarea>
                </div>
                <input type="hidden" name="shipping_fee" id="shipping_fee_input" value="0">
            </div>

            <div class="summary-card">
                <h3 style="margin-bottom:16px;color:var(--navy-800);">Resume de la commande</h3>
                <?php foreach ($cartItems as $item): $p = $item['product']; ?>
                    <div class="summary-row"><span><?= htmlspecialchars($p['name']) ?> x<?= (int)$item['qty'] ?></span><span><?= formatPrice($item['line_total']) ?></span></div>
                <?php endforeach; ?>
                <div class="summary-row"><span>Sous-total</span><span><?= formatPrice($subtotal) ?></span></div>
                <div class="summary-row"><span>Livraison</span><span id="shipping-fee-display">-- <?= CURRENCY ?></span></div>
                <div id="shipping-status">Choisissez une wilaya pour calculer les frais de livraison</div>
                <div class="summary-row total"><span>Total</span><span id="total-display"><?= formatPrice($subtotal) ?></span></div>
                <button type="submit" class="btn btn-primary btn-block" style="margin-top:18px;">Confirmer la commande</button>
                <p style="font-size:12px;color:var(--text-muted);margin-top:12px;text-align:center;">Paiement a la livraison</p>
            </div>
        </form>
    </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>

<script>
const SUBTOTAL = <?= (float)$subtotal ?>;
const CURRENCY = '<?= CURRENCY ?>';

function formatDA(n) {
    return Math.round(n).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.') + ' ' + CURRENCY;
}

function refreshShipping() {
    const wilayaId = document.getElementById('wilaya_id').value;
    const deliveryType = document.querySelector('input[name="delivery_type"]:checked').value;
    const statusEl = document.getElementById('shipping-status');
    const feeEl = document.getElementById('shipping-fee-display');
    const totalEl = document.getElementById('total-display');
    const hiddenInput = document.getElementById('shipping_fee_input');

    if (!wilayaId) {
        feeEl.textContent = '-- ' + CURRENCY;
        totalEl.textContent = formatDA(SUBTOTAL);
        hiddenInput.value = 0;
        return;
    }

    statusEl.textContent = 'Calcul des frais de livraison...';
    fetch(`api/shipping.php?wilaya_id=${wilayaId}&type=${deliveryType}`)
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                feeEl.textContent = formatDA(data.fee);
                totalEl.textContent = formatDA(SUBTOTAL + data.fee);
                hiddenInput.value = data.fee;
                statusEl.textContent = data.source === 'zrexpress'
                    ? 'Tarif officiel ZR Express'
                    : 'Tarif estime (configurez votre API ZR Express dans config.php pour un tarif exact)';
            } else {
                statusEl.textContent = 'Impossible de calculer les frais.';
            }
        })
        .catch(() => { statusEl.textContent = 'Erreur reseau, reessayez.'; });
}

document.getElementById('wilaya_id').addEventListener('change', refreshShipping);
document.querySelectorAll('input[name="delivery_type"]').forEach(r => r.addEventListener('change', refreshShipping));
</script>
