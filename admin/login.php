<?php
session_start();

// Si déjà connecté, redirige vers le dashboard
if (isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

require_once '../src/config/database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $mdp = $_POST['mot_de_passe'] ?? '';

    if (!$email || !$mdp) {
        $error = 'Merci de remplir tous les champs.';
    } else {
        $stmt = $pdo->prepare('SELECT * FROM admins WHERE email = ?');
        $stmt->execute([$email]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($mdp, $admin['mot_de_passe'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_nom'] = $admin['nom'];
            header('Location: index.php');
            exit;
        } else {
            $error = 'Email ou mot de passe incorrect.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion — Back-office CAT</title>
    <link rel="stylesheet" href="../public/assets/css/style.css">
    <style>
        body { background: #F2F3F4; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .login-box { background: #fff; padding: 48px 40px; border-radius: 10px; box-shadow: 0 4px 24px rgba(0,0,0,0.10); width: 100%; max-width: 420px; }
        .login-logo { text-align: center; font-size: 2rem; font-weight: 800; color: #1A5276; letter-spacing: 3px; margin-bottom: 8px; }
        .login-subtitle { text-align: center; color: #888; font-size: 0.9rem; margin-bottom: 36px; }
        .login-box h1 { font-size: 1.4rem; color: #1C2833; margin-bottom: 28px; text-align: center; }
    </style>
</head>
<body>
<div class="login-box">
    <div class="login-logo">CAT</div>
    <div class="login-subtitle">Back-office administration</div>

    <?php if ($error): ?>
        <div class="form-error">⚠️ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="login.php">
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required autofocus>
        </div>
        <div class="form-group">
            <label for="mot_de_passe">Mot de passe</label>
            <input type="password" id="mot_de_passe" name="mot_de_passe" required>
        </div>
        <button type="submit" class="btn-primary" style="width:100%;border:none;cursor:pointer;padding:14px;font-size:1rem;margin-top:8px;">
            Se connecter
        </button>
    </form>
</div>
</body>
</html>