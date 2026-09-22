<?php
$pageTitle = 'Parametres';
require_once __DIR__ . '/includes/admin_header.php';

$saved = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    updateSetting('store_name', trim($_POST['store_name'] ?? 'Ontime'));
    updateSetting('zrexpress_token', trim($_POST['zrexpress_token'] ?? ''));
    updateSetting('zrexpress_key', trim($_POST['zrexpress_key'] ?? ''));
    $saved = true;
}

$storeName = getSetting('store_name', 'Ontime');
$zrToken = getSetting('zrexpress_token');
$zrKey = getSetting('zrexpress_key');
?>
<div class="topbar"><h1>Parametres</h1></div>

<?php if ($saved): ?><div class="alert alert-success">Parametres enregistres.</div><?php endif; ?>

<div class="card" style="max-width:560px;">
    <h3>Boutique</h3>
    <form method="post">
        <div class="form-row">
            <label>Nom de la boutique</label>
            <input type="text" name="store_name" value="<?= htmlspecialchars($storeName) ?>">
        </div>

        <h3 style="margin-top:24px;">API ZR Express</h3>
        <p style="font-size:13px;color:var(--text-muted);margin-bottom:14px;">
            Ces valeurs sont stockees en base pour reference. Le calcul des frais de livraison utilise
            actuellement <code>config/config.php</code> (constantes ZREXPRESS_TOKEN / ZREXPRESS_KEY) -
            copiez les memes valeurs la-bas pour activer les tarifs en temps reel.
        </p>
        <div class="form-row">
            <label>Token</label>
            <input type="text" name="zrexpress_token" value="<?= htmlspecialchars($zrToken) ?>">
        </div>
        <div class="form-row">
            <label>Key</label>
            <input type="text" name="zrexpress_key" value="<?= htmlspecialchars($zrKey) ?>">
        </div>
        <button type="submit" class="btn btn-primary">Enregistrer</button>
    </form>
</div>
<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
