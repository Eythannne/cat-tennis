<?php
require_once 'auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'create' || $action === 'edit') {
        $titre = trim($_POST['titre']);
        $date_debut = $_POST['date_debut'];
        $date_fin = $_POST['date_fin'] ?: null;
        $type = $_POST['type'];
        $couleur = $_POST['couleur'];
        $description = trim($_POST['description']);
        if ($action === 'create') {
            $stmt = $pdo->prepare("INSERT INTO evenements (titre, date_debut, date_fin, type, couleur, description) VALUES (?,?,?,?,?,?)");
            $stmt->execute([$titre, $date_debut, $date_fin, $type, $couleur, $description]);
        } else {
            $id = (int)$_POST['id'];
            $stmt = $pdo->prepare("UPDATE evenements SET titre=?, date_debut=?, date_fin=?, type=?, couleur=?, description=? WHERE id=?");
            $stmt->execute([$titre, $date_debut, $date_fin, $type, $couleur, $description, $id]);
        }
    }
    if ($action === 'delete') {
        $pdo->prepare("DELETE FROM evenements WHERE id=?")->execute([(int)$_POST['id']]);
    }
    header('Location: evenements.php');
    exit;
}

$evenements = $pdo->query("SELECT * FROM evenements ORDER BY date_debut DESC")->fetchAll();
$edit = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM evenements WHERE id=?");
    $stmt->execute([(int)$_GET['edit']]);
    $edit = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Événements — Admin CAT</title>
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
        .form-group input, .form-group select, .form-group textarea { width:100%; padding:9px 12px; border:1px solid #ddd; border-radius:6px; font-size:0.9rem; font-family:inherit; }
        .form-group textarea { height:80px; resize:vertical; }
        .btn { padding:10px 20px; border:none; border-radius:6px; cursor:pointer; font-size:0.9rem; font-weight:600; }
        .btn-primary { background:#1A5276; color:#fff; }
        .btn-danger { background:#e74c3c; color:#fff; font-size:0.8rem; padding:6px 12px; }
        .btn-edit { background:#f0f0f0; color:#333; font-size:0.8rem; padding:6px 12px; text-decoration:none; border-radius:6px; }
        table { width:100%; border-collapse:collapse; }
        th, td { padding:11px 14px; text-align:left; border-bottom:1px solid #eee; font-size:0.88rem; }
        th { font-weight:600; color:#555; background:#fafafa; }
        .dot { display:inline-block; width:10px; height:10px; border-radius:50%; margin-right:6px; }
        .actions { display:flex; gap:6px; }
    </style>
</head>
<body>
<div class="sidebar">
    <div class="sidebar-logo">CAT</div>
    <nav>
        <a href="index.php">📊 Dashboard</a>
        <a href="articles.php">📰 Articles</a>
        <a href="messages.php">✉️ Messages</a>
        <a href="evenements.php" class="active">📅 Événements</a>
        <a href="galerie.php">📷 Galerie</a>
        <a href="parametres.php">⚙️ Paramètres</a>
    </nav>
    <div class="sidebar-footer"><a href="logout.php">🚪 Déconnexion</a></div>
</div>
<div class="main">
    <h1>📅 Gestion des événements</h1>
    <div class="card">
        <h2><?= $edit ? 'Modifier l\'événement' : 'Nouvel événement' ?></h2>
        <form method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="<?= $edit ? 'edit' : 'create' ?>">
            <?php if ($edit): ?><input type="hidden" name="id" value="<?= $edit['id'] ?>"><?php endif; ?>
            <div class="form-group">
                <label>Titre</label>
                <input type="text" name="titre" value="<?= htmlspecialchars($edit['titre'] ?? '') ?>" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Date début</label>
                    <input type="datetime-local" name="date_debut" value="<?= $edit ? date('Y-m-d\TH:i', strtotime($edit['date_debut'])) : '' ?>" required>
                </div>
                <div class="form-group">
                    <label>Date fin (optionnel)</label>
                    <input type="datetime-local" name="date_fin" value="<?= ($edit && $edit['date_fin']) ? date('Y-m-d\TH:i', strtotime($edit['date_fin'])) : '' ?>">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Type</label>
                    <select name="type">
                        <?php foreach (['tournoi','cours','evenement','autre'] as $t): ?>
                        <option value="<?= $t ?>" <?= ($edit['type'] ?? '') === $t ? 'selected' : '' ?>><?= ucfirst($t) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Couleur</label>
                    <input type="color" name="couleur" value="<?= htmlspecialchars($edit['couleur'] ?? '#1A5276') ?>">
                </div>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description"><?= htmlspecialchars($edit['description'] ?? '') ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary"><?= $edit ? 'Mettre à jour' : 'Créer' ?></button>
            <?php if ($edit): ?><a href="evenements.php" style="margin-left:10px;color:#777;font-size:0.9rem;">Annuler</a><?php endif; ?>
        </form>
    </div>
    <div class="card">
        <h2>Tous les événements (<?= count($evenements) ?>)</h2>
        <?php if (empty($evenements)): ?>
            <p style="color:#999">Aucun événement.</p>
        <?php else: ?>
        <table>
            <thead><tr><th>Titre</th><th>Date début</th><th>Type</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($evenements as $e): ?>
            <tr>
                <td><span class="dot" style="background:<?= htmlspecialchars($e['couleur']) ?>"></span><?= htmlspecialchars($e['titre']) ?></td>
                <td><?= date('d/m/Y H:i', strtotime($e['date_debut'])) ?></td>
                <td><?= ucfirst($e['type']) ?></td>
                <td class="actions">
                    <a href="evenements.php?edit=<?= $e['id'] ?>" class="btn-edit">✏️ Modifier</a>
                    <form method="POST" onsubmit="return confirm('Supprimer ?')">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= $e['id'] ?>">
                        <button type="submit" class="btn btn-danger">🗑️</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>
</body>
</html>