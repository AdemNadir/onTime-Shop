<?php
require_once __DIR__ . '/../includes/functions.php';

if (isAdminLoggedIn()) { header('Location: dashboard.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE username = ?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password_hash'])) {
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Identifiants incorrects.';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Connexion Admin - Ontime</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/admin.css">
</head>
<body class="login-body">
<div class="login-box">
    <h1>ON<span>TIME</span> <small>Admin</small></h1>
    <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <form method="post">
        <div class="form-row"><label>Nom d'utilisateur</label><input type="text" name="username" required autofocus></div>
        <div class="form-row"><label>Mot de passe</label><input type="password" name="password" required></div>
        <button type="submit" class="btn btn-primary btn-block">Se connecter</button>
    </form>
    <p class="login-hint">Identifiants par defaut: <strong>admin</strong> / <strong>password</strong> (a changer)</p>
</div>
</body>
</html>
