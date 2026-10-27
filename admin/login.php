<?php
require_once __DIR__ . '/session.php';

// Si déjà connecté, redirige vers le dashboard
if (isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

csrf_check();

require_once '../src/init.php';

// ── Anti brute-force : 5 échecs max par IP sur 15 minutes ────────────────────
const LOGIN_MAX_ECHECS = 5;
const LOGIN_FENETRE    = 15 * 60;

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $mdp = $_POST['mot_de_passe'] ?? '';

    if (limite_atteinte('login', LOGIN_MAX_ECHECS, LOGIN_FENETRE)) {
        $error = 'Trop de tentatives. Réessayez dans 15 minutes.';
    } elseif (!$email || !$mdp) {
        $error = 'Merci de remplir tous les champs.';
    } else {
        $stmt = $pdo->prepare('SELECT * FROM admins WHERE email = ?');
        $stmt->execute([$email]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($mdp, $admin['mot_de_passe'])) {
            limite_reinitialiser('login');
            session_regenerate_id(true);
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_nom'] = $admin['nom'];
            header('Location: index.php');
            exit;
        } else {
            limite_enregistrer('login', LOGIN_FENETRE);
            sleep(1);
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
    <meta name="robots" content="noindex, nofollow">
    <link rel="icon" type="image/png" href="../public/favicon.png">
    <link rel="stylesheet" href="../public/assets/css/style.css">
    <style>
        body { background: var(--vert-dark); display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 16px; }
        .login-box { background: #fff; padding: 40px 36px; border-radius: 10px; border-top: 4px solid var(--jaune); box-shadow: 0 8px 32px rgba(0,0,0,0.25); width: 100%; max-width: 420px; }
        .login-logo { display: block; width: 88px; height: auto; margin: 0 auto 10px; }
        .login-subtitle { text-align: center; color: #888; font-size: 0.9rem; margin-bottom: 14px; }
        .login-back { display: block; text-align: center; margin-bottom: 24px; color: var(--vert); font-size: 0.85rem; text-decoration: none; opacity: 0.75; }
        .login-back:hover { opacity: 1; }
        @media (max-width: 480px) { .login-box { padding: 30px 20px; } }
    </style>
</head>
<body>
<div class="login-box">
    <img src="../public/favicon.png" alt="CAT" class="login-logo">
    <div class="login-subtitle">Back-office administration</div>
    <a href="<?= BASE_URL ?>/index.php" class="login-back">← Retour au site</a>

    <?php if ($error): ?>
        <div class="form-error">⚠️ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="login.php">
        <?= csrf_field() ?>
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