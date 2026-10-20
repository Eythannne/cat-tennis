<?php
require_once 'auth.php';

// Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create' || $action === 'edit') {
        $titre = trim($_POST['titre']);
        $contenu = trim($_POST['contenu']);
        $publie = isset($_POST['publie']) ? 1 : 0;

        if ($action === 'create') {
            $stmt = $pdo->prepare("INSERT INTO articles (titre, contenu, publie) VALUES (?, ?, ?)");
            $stmt->execute([$titre, $contenu, $publie]);
        } else {
            $id = (int)$_POST['id'];
            $stmt = $pdo->prepare("UPDATE articles SET titre=?, contenu=?, publie=? WHERE id=?");
            $stmt->execute([$titre, $contenu, $publie, $id]);
        }
    }

    if ($action === 'delete') {
        $id = (int)$_POST['id'];
        $pdo->prepare("DELETE FROM articles WHERE id=?")->execute([$id]);
    }

    header('Location: articles.php');
    exit;
}

// Récupération
$articles = $pdo->query("SELECT * FROM articles ORDER BY created_at DESC")->fetchAll();

// Article à éditer ?
$edit = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM articles WHERE id=?");
    $stmt->execute([(int)$_GET['edit']]);
    $edit = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Articles — Admin CAT</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #f4f6f9; color: #333; display: flex; min-height: 100vh; }

        /* Sidebar */
        .sidebar { width: 240px; background: #1A5276; color: #fff; padding: 20px 0; flex-shrink: 0; }
        .sidebar-logo { padding: 0 20px 30px; font-size: 1.5rem; font-weight: 700; letter-spacing: 2px; }
        .sidebar nav a { display: block; padding: 12px 20px; color: #cce4f7; text-decoration: none; font-size: 0.95rem; transition: background 0.2s; }
        .sidebar nav a:hover, .sidebar nav a.active { background: rgba(255,255,255,0.15); color: #fff; }
        .sidebar-footer { padding: 20px; margin-top: auto; }
        .sidebar-footer a { color: #cce4f7; text-decoration: none; font-size: 0.85rem; }

        /* Main */
        .main { flex: 1; padding: 30px; }
        h1 { font-size: 1.6rem; margin-bottom: 24px; color: #1A5276; }

        /* Formulaire */
        .card { background: #fff; border-radius: 8px; padding: 24px; margin-bottom: 30px; box-shadow: 0 1px 4px rgba(0,0,0,0.08); }
        .card h2 { font-size: 1.1rem; margin-bottom: 16px; color: #555; }
        .form-group { margin-bottom: 14px; }
        .form-group label { display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 5px; color: #555; }
        .form-group input[type="text"],
        .form-group textarea { width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 0.95rem; font-family: inherit; }
        .form-group textarea { height: 140px; resize: vertical; }
        .form-check { display: flex; align-items: center; gap: 8px; font-size: 0.9rem; }
        .btn { padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer; font-size: 0.9rem; font-weight: 600; }
        .btn-primary { background: #1A5276; color: #fff; }
        .btn-danger { background: #e74c3c; color: #fff; font-size: 0.8rem; padding: 6px 12px; }
        .btn-edit { background: #f0f0f0; color: #333; font-size: 0.8rem; padding: 6px 12px; text-decoration: none; border-radius: 6px; }

        /* Table */
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px 14px; text-align: left; border-bottom: 1px solid #eee; font-size: 0.9rem; }
        th { font-weight: 600; color: #555; background: #fafafa; }
        .badge { display: inline-block; padding: 3px 9px; border-radius: 20px; font-size: 0.78rem; font-weight: 600; }
        .badge-green { background: #d5f5e3; color: #1e8449; }
        .badge-gray { background: #eee; color: #777; }
        .actions { display: flex; gap: 6px; }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-logo">CAT</div>
    <nav>
        <a href="index.php">📊 Dashboard</a>
        <a href="articles.php" class="active">📰 Articles</a>
        <a href="messages.php">✉️ Messages</a>
        <a href="evenements.php">📅 Événements</a>
        <a href="galerie.php">📷 Galerie</a>
        <a href="parametres.php">⚙️ Paramètres</a>
    </nav>
    <div class="sidebar-footer">
        <a href="logout.php">🚪 Déconnexion</a>
    </div>
</div>

<div class="main">
    <h1>📰 Gestion des articles</h1>

    <!-- Formulaire -->
    <div class="card">
        <h2><?= $edit ? 'Modifier l\'article' : 'Nouvel article' ?></h2>
        <form method="POST">
            <input type="hidden" name="action" value="<?= $edit ? 'edit' : 'create' ?>">
            <?php if ($edit): ?>
                <input type="hidden" name="id" value="<?= $edit['id'] ?>">
            <?php endif; ?>
            <div class="form-group">
                <label>Titre</label>
                <input type="text" name="titre" value="<?= htmlspecialchars($edit['titre'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label>Contenu</label>
                <textarea name="contenu"><?= htmlspecialchars($edit['contenu'] ?? '') ?></textarea>
            </div>
            <div class="form-group">
                <label class="form-check">
                    <input type="checkbox" name="publie" <?= ($edit['publie'] ?? 0) ? 'checked' : '' ?>>
                    Publier l'article
                </label>
            </div>
            <button type="submit" class="btn btn-primary"><?= $edit ? 'Mettre à jour' : 'Créer l\'article' ?></button>
            <?php if ($edit): ?>
                <a href="articles.php" style="margin-left:10px;color:#777;font-size:0.9rem;">Annuler</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Liste -->
    <div class="card">
        <h2>Tous les articles (<?= count($articles) ?>)</h2>
        <?php if (empty($articles)): ?>
            <p style="color:#999">Aucun article pour l'instant.</p>
        <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Titre</th>
                    <th>Date</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($articles as $a): ?>
                <tr>
                    <td><?= $a['id'] ?></td>
                    <td><?= htmlspecialchars($a['titre']) ?></td>
                    <td><?= date('d/m/Y', strtotime($a['created_at'])) ?></td>
                    <td>
                        <span class="badge <?= $a['publie'] ? 'badge-green' : 'badge-gray' ?>">
                            <?= $a['publie'] ? 'Publié' : 'Brouillon' ?>
                        </span>
                    </td>
                    <td class="actions">
                        <a href="articles.php?edit=<?= $a['id'] ?>" class="btn-edit">✏️ Modifier</a>
                        <form method="POST" onsubmit="return confirm('Supprimer cet article ?')">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $a['id'] ?>">
                            <button type="submit" class="btn btn-danger">🗑️ Supprimer</button>
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