<?php
$pageTitle = 'Produit';
require_once __DIR__ . '/includes/admin_header.php';
$pdo = getDB();

$id = isset($_GET['id']) ? (int)$_GET['id'] : (isset($_POST['id']) ? (int)$_POST['id'] : 0);
$product = ['name'=>'','slug'=>'','description'=>'','price'=>'','cost_price'=>'','stock'=>0,'image'=>null,'category_id'=>null,'active'=>1];
if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if ($row) $product = $row;
}
$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $costPrice = (float)($_POST['cost_price'] ?? 0);
    $stock = (int)($_POST['stock'] ?? 0);
    $categoryId = $_POST['category_id'] ? (int)$_POST['category_id'] : null;
    $active = isset($_POST['active']) ? 1 : 0;

    if ($name === '') $errors[] = 'Le nom est obligatoire.';
    if ($price <= 0) $errors[] = 'Le prix doit etre superieur a 0.';

    // Slug: auto-generate from name, keep unique
    $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $name), '-'));
    if ($slug === '') $slug = 'produit-' . time();

    $imageFilename = $product['image'];
    if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['jpg','jpeg','png','webp'];
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) {
            $errors[] = 'Format image non supporte (jpg, png, webp uniquement).';
        } else {
            $newName = $slug . '-' . time() . '.' . $ext;
            if (move_uploaded_file($_FILES['image']['tmp_name'], UPLOAD_DIR . $newName)) {
                $imageFilename = $newName;
            } else {
                $errors[] = 'Echec de l\'upload de l\'image.';
            }
        }
    }

    if (empty($errors)) {
        if ($id) {
            // Ensure slug uniqueness excluding self
            $check = $pdo->prepare("SELECT id FROM products WHERE slug = ? AND id != ?");
            $check->execute([$slug, $id]);
            if ($check->fetch()) $slug .= '-' . $id;

            $stmt = $pdo->prepare("UPDATE products SET name=?, slug=?, description=?, price=?, cost_price=?, stock=?, image=?, category_id=?, active=? WHERE id=?");
            $stmt->execute([$name, $slug, $description, $price, $costPrice, $stock, $imageFilename, $categoryId, $active, $id]);
        } else {
            $check = $pdo->prepare("SELECT id FROM products WHERE slug = ?");
            $check->execute([$slug]);
            if ($check->fetch()) $slug .= '-' . time();

            $stmt = $pdo->prepare("INSERT INTO products (name, slug, description, price, cost_price, stock, image, category_id, active) VALUES (?,?,?,?,?,?,?,?,?)");
            $stmt->execute([$name, $slug, $description, $price, $costPrice, $stock, $imageFilename, $categoryId, $active]);
        }
        header('Location: products.php');
        exit;
    }
    $product = array_merge($product, ['name'=>$name,'description'=>$description,'price'=>$price,'cost_price'=>$costPrice,'stock'=>$stock,'category_id'=>$categoryId,'active'=>$active,'image'=>$imageFilename]);
}
?>
<div class="topbar">
    <h1><?= $id ? 'Modifier le produit' : 'Nouveau produit' ?></h1>
    <a href="products.php" class="btn btn-outline">← Retour</a>
</div>

<?php foreach ($errors as $e): ?><div class="alert alert-error"><?= htmlspecialchars($e) ?></div><?php endforeach; ?>

<form method="post" enctype="multipart/form-data" class="card">
    <input type="hidden" name="id" value="<?= $id ?>">
    <div class="product-form-grid">
        <div>
            <label style="font-size:13px;font-weight:600;display:block;margin-bottom:8px;">Image du produit</label>
            <div class="image-preview" data-image-preview>
                <?php if ($product['image']): ?>
                    <img src="<?= UPLOAD_URL . htmlspecialchars($product['image']) ?>">
                <?php else: ?>
                    <span style="font-size:50px;">⌚</span>
                <?php endif; ?>
            </div>
            <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp" data-image-input>
        </div>
        <div>
            <div class="form-row">
                <label>Nom du produit</label>
                <input type="text" name="name" required value="<?= htmlspecialchars($product['name']) ?>">
            </div>
            <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                <div>
                    <label>Prix de vente (<?= CURRENCY ?>)</label>
                    <input type="number" step="0.01" name="price" required value="<?= htmlspecialchars($product['price']) ?>">
                </div>
                <div>
                    <label>Prix de revient / cout (<?= CURRENCY ?>)</label>
                    <input type="number" step="0.01" name="cost_price" value="<?= htmlspecialchars($product['cost_price']) ?>">
                </div>
            </div>
            <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                <div>
                    <label>Stock</label>
                    <input type="number" name="stock" value="<?= htmlspecialchars($product['stock']) ?>">
                </div>
                <div>
                    <label>Categorie</label>
                    <select name="category_id">
                        <option value="">-- Aucune --</option>
                        <?php foreach ($categories as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= $product['category_id'] == $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
    </div>
    <div class="form-row">
        <label>Description</label>
        <textarea name="description" rows="4"><?= htmlspecialchars($product['description']) ?></textarea>
    </div>
    <div class="form-row">
        <label><input type="checkbox" name="active" <?= $product['active'] ? 'checked' : '' ?> style="width:auto;margin-right:6px;"> Produit visible sur la boutique</label>
    </div>
    <button type="submit" class="btn btn-primary">💾 Enregistrer</button>
</form>
<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
