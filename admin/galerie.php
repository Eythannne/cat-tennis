<?php
require_once 'auth.php';

$upload_dir = '../public/assets/uploads/galerie/';
if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'upload') {
        $titre = trim($_POST['titre']);
        $categorie = trim($_POST['categorie']);
        if (isset($_FILES['fichier']) && $_FILES['fichier']['error'] === UPLOAD_ERR_OK
            && $_FILES['fichier']['size'] <= IMAGE_MAX_SIZE) {
            // Extension déduite du vrai contenu du fichier, pas de son nom
            $ext = image_extension(file_get_contents($_FILES['fichier']['tmp_name']));
            if ($ext) {
                $filename = uniqid() . '.' . $ext;
                if (move_uploaded_file($_FILES['fichier']['tmp_name'], $upload_dir . $filename)) {
                    $stmt = $pdo->prepare("INSERT INTO galerie (titre, fichier, categorie) VALUES (?,?,?)");
                    $stmt->execute([$titre, $filename, $categorie]);
                }
            }
        }
    }
    if ($action === 'delete') {
        $id = (int)$_POST['id'];
        $row = $pdo->prepare("SELECT fichier FROM galerie WHERE id=?");
        $row->execute([$id]);
        $f = $row->fetchColumn();
        if ($f && file_exists($upload_dir . basename($f))) unlink($upload_dir . basename($f));
        $pdo->prepare("DELETE FROM galerie WHERE id=?")->execute([$id]);
    }
    header('Location: galerie.php');
    exit;
}

$photos = $pdo->query("SELECT * FROM galerie ORDER BY created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galerie — Admin CAT</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Segoe UI',sans-serif; background:#f4f6f9; color:#333; display:flex; min-height:100vh; }
        .sidebar { width:240px; background:#1A5276; color:#fff; padding:20px 0; flex-shrink:0; display:flex; flex-direction:column; }
        .sidebar-logo { padding:0 20px 30px; font-size:1.5rem; font-weight:700; letter-spacing:2px; }
        .sidebar nav a { display:block; padding:12px 20px; color:#cce4f7; text-decoration:none; font-size:0.95rem; }
        .sidebar nav a:hover, .sidebar nav a.active { background:rgba(255,255,255,0.15); color:#fff; }
        .sidebar-footer { padding:20px; margin-top:auto; }
        .sidebar-footer a { color:#cce4f7; text-decoration:none; font-size:0.85rem; }
        .main { flex:1; padding:30px; }
        h1 { font-size:1.6rem; margin-bottom:24px; color:#1A5276; }
        .card { background:#fff; border-radius:8px; padding:24px; margin-bottom:24px; box-shadow:0 1px 4px rgba(0,0,0,0.08); }
        .card h2 { font-size:1rem; color:#555; margin-bottom:16px; }
        .form-row { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
        .form-group { margin-bottom:14px; }
        .form-group label { display:block; font-size:0.85rem; font-weight:600; margin-bottom:5px; color:#555; }
        .form-group input { width:100%; padding:9px 12px; border:1px solid #ddd; border-radius:6px; font-size:0.9rem; }
        .btn { padding:10px 20px; border:none; border-radius:6px; cursor:pointer; font-size:0.9rem; font-weight:600; }
        .btn-primary { background:#1A5276; color:#fff; }
        .btn-danger { background:#e74c3c; color:#fff; font-size:0.78rem; padding:5px 10px; }
        .galerie-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:16px; }
        .galerie-item { border-radius:8px; overflow:hidden; border:1px solid #eee; }
        .galerie-item img { width:100%; height:140px; object-fit:cover; display:block; }
        .galerie-item .info { padding:8px 10px; display:flex; justify-content:space-between; align-items:center; }
        .galerie-item .info span { font-size:0.82rem; color:#555; }
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
        <a href="galerie.php" class="active">📷 Galerie</a>
        <a href="parametres.php">⚙️ Paramètres</a>
    </nav>
    <div class="sidebar-footer"><a href="logout.php">🚪 Déconnexion</a></div>
</div>
<div class="main">
    <h1>📷 Galerie photos</h1>
    <div class="card">
        <h2>Ajouter une photo</h2>
        <form method="POST" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="upload">
            <div class="form-row">
                <div class="form-group">
                    <label>Titre</label>
                    <input type="text" name="titre" required>
                </div>
                <div class="form-group">
                    <label>Catégorie</label>
                    <input type="text" name="categorie" placeholder="ex: Tournoi, Cours...">
                </div>
            </div>
            <div class="form-group">
                <label>Photo (jpg, png, webp — max 5 Mo)</label>
                <input type="file" name="fichier" accept=".jpg,.jpeg,.png,.webp" required>
            </div>
            <button type="submit" class="btn btn-primary">📤 Uploader</button>
        </form>
    </div>
    <div class="card">
        <h2>Photos (<?= count($photos) ?>)</h2>
        <?php if (empty($photos)): ?>
            <p style="color:#999">Aucune photo.</p>
        <?php else: ?>
        <div class="galerie-grid">
            <?php foreach ($photos as $p): ?>
            <div class="galerie-item">
                <img src="../public/assets/uploads/galerie/<?= htmlspecialchars($p['fichier']) ?>" alt="<?= htmlspecialchars($p['titre']) ?>">
                <div class="info">
                    <span><?= htmlspecialchars($p['titre']) ?></span>
                    <form method="POST" onsubmit="return confirm('Supprimer ?')">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= $p['id'] ?>">
                        <button type="submit" class="btn btn-danger">🗑️</button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>