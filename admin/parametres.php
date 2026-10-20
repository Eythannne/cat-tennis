<?php
require_once 'auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $champs = ['nom_club', 'adresse', 'email_contact', 'telephone', 'description'];
    foreach ($champs as $cle) {
        $valeur = trim($_POST[$cle] ?? '');
        $stmt = $pdo->prepare("INSERT INTO parametres (cle, valeur) VALUES (?,?) ON DUPLICATE KEY UPDATE valeur=?");
        $stmt->execute([$cle, $valeur, $valeur]);
    }
    $saved = true;
}

$params = [];
foreach ($pdo->query("SELECT cle, valeur FROM parametres")->fetchAll() as $row) {
    $params[$row['cle']] = $row['valeur'];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paramètres — Admin CAT</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Segoe UI',sans-serif; background:#f4f6f9; color:#333; display:flex; min-height:100vh; }
        .sidebar { width:240px; background:#1A5276; color:#fff; padding:20px 0; flex-shrink:0; display:flex; flex-direction:column; }
        .sidebar-logo { padding:0 20px 30px; font-size:1.5rem; font-weight:700; letter-spacing:2px; }
        .sidebar nav a { display:block; padding:12px 20px; color:#cce4f7; text-decoration:none; font-size:0.95rem; }
        .sidebar nav a:hover, .sidebar nav a.active { background:rgba(255,255,255,0.15); color:#fff; }
        .sidebar-footer { padding:20px; margin-top:auto; }
        .sidebar-footer a { color:#cce4f7; text-decoration:none; font-size:0.85rem; }
        .main { flex:1; padding:30px; max-width:700px; }
        h1 { font-size:1.6rem; margin-bottom:24px; color:#1A5276; }
        .card { background:#fff; border-radius:8px; padding:24px; box-shadow:0 1px 4px rgba(0,0,0,0.08); }
        .card h2 { font-size:1rem; color:#555; margin-bottom:20px; }
        .form-group { margin-bottom:16px; }
        .form-group label { display:block; font-size:0.85rem; font-weight:600; margin-bottom:5px; color:#555; }
        .form-group input, .form-group textarea { width:100%; padding:10px 12px; border:1px solid #ddd; border-radius:6px; font-size:0.9rem; font-family:inherit; }
        .form-group textarea { height:80px; resize:vertical; }
        .btn { padding:10px 24px; border:none; border-radius:6px; cursor:pointer; font-size:0.9rem; font-weight:600; }
        .btn-primary { background:#1A5276; color:#fff; }
        .alert { padding:10px 16px; background:#d5f5e3; color:#1e8449; border-radius:6px; margin-bottom:20px; font-size:0.9rem; }
    </style>
</head>
<body>
<div class="sidebar">
    <div class="sidebar-logo">CAT</div>
    <nav>
        <a href="index.php">📊 Dashboard</a>
        <a href="articles.php">📰 Articles</a>
        <a href="messages.php">✉️ Messages</a>
        <a href="evenements.php">📅 Événements</a>
        <a href="galerie.php">📷 Galerie</a>
        <a href="parametres.php" class="active">⚙️ Paramètres</a>
    </nav>
    <div class="sidebar-footer"><a href="logout.php">🚪 Déconnexion</a></div>
</div>
<div class="main">
    <h1>⚙️ Paramètres du site</h1>
    <?php if (isset($saved)): ?>
        <div class="alert">✅ Paramètres enregistrés.</div>
    <?php endif; ?>
    <div class="card">
        <h2>Informations du club</h2>
        <form method="POST">
            <div class="form-group">
                <label>Nom du club</label>
                <input type="text" name="nom_club" value="<?= htmlspecialchars($params['nom_club'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Adresse</label>
                <input type="text" name="adresse" value="<?= htmlspecialchars($params['adresse'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Email de contact</label>
                <input type="email" name="email_contact" value="<?= htmlspecialchars($params['email_contact'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Téléphone</label>
                <input type="text" name="telephone" value="<?= htmlspecialchars($params['telephone'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Description courte</label>
                <textarea name="description"><?= htmlspecialchars($params['description'] ?? '') ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary">💾 Enregistrer</button>
        </form>
    </div>
</div>
</body>
</html>