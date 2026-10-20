<?php
require_once 'auth.php';

$stats = [
    'articles' => $pdo->query("SELECT COUNT(*) FROM articles")->fetchColumn(),
    'articles_publies' => $pdo->query("SELECT COUNT(*) FROM articles WHERE publie=1")->fetchColumn(),
    'messages' => $pdo->query("SELECT COUNT(*) FROM messages_contact")->fetchColumn(),
    'messages_non_lus' => $pdo->query("SELECT COUNT(*) FROM messages_contact WHERE lu=0")->fetchColumn(),
    'evenements' => $pdo->query("SELECT COUNT(*) FROM evenements")->fetchColumn(),
    'galerie' => $pdo->query("SELECT COUNT(*) FROM galerie")->fetchColumn(),
];

$derniers_articles = $pdo->query("SELECT * FROM articles ORDER BY created_at DESC LIMIT 5")->fetchAll();
$derniers_messages = $pdo->query("SELECT * FROM messages_contact ORDER BY created_at DESC LIMIT 5")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — Admin CAT</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #f4f6f9; color: #333; display: flex; min-height: 100vh; }
        .sidebar { width: 240px; background: #1A5276; color: #fff; padding: 20px 0; flex-shrink: 0; display: flex; flex-direction: column; }
        .sidebar-logo { padding: 0 20px 30px; font-size: 1.5rem; font-weight: 700; letter-spacing: 2px; }
        .sidebar nav a { display: block; padding: 12px 20px; color: #cce4f7; text-decoration: none; font-size: 0.95rem; transition: background 0.2s; }
        .sidebar nav a:hover, .sidebar nav a.active { background: rgba(255,255,255,0.15); color: #fff; }
        .sidebar-footer { padding: 20px; margin-top: auto; }
        .sidebar-footer a { color: #cce4f7; text-decoration: none; font-size: 0.85rem; }
        .main { flex: 1; padding: 30px; }
        h1 { font-size: 1.6rem; margin-bottom: 8px; color: #1A5276; }
        .subtitle { color: #888; margin-bottom: 28px; font-size: 0.9rem; }
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 30px; }
        .stat-card { background: #fff; border-radius: 8px; padding: 20px; box-shadow: 0 1px 4px rgba(0,0,0,0.08); }
        .stat-card .number { font-size: 2rem; font-weight: 700; color: #1A5276; }
        .stat-card .label { font-size: 0.85rem; color: #888; margin-top: 4px; }
        .stat-card .sub { font-size: 0.8rem; color: #27ae60; margin-top: 2px; }
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .card { background: #fff; border-radius: 8px; padding: 20px; box-shadow: 0 1px 4px rgba(0,0,0,0.08); }
        .card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; }
        .card-header h2 { font-size: 1rem; color: #555; }
        .card-header a { font-size: 0.82rem; color: #1A5276; text-decoration: none; }
        .list-item { display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid #f0f0f0; font-size: 0.88rem; }
        .list-item:last-child { border-bottom: none; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
        .badge-green { background: #d5f5e3; color: #1e8449; }
        .badge-gray { background: #eee; color: #777; }
        .badge-red { background: #fde; color: #c0392b; }
        .empty { color: #bbb; font-size: 0.88rem; text-align: center; padding: 20px 0; }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-logo">CAT</div>
    <nav>
        <a href="index.php" class="active">📊 Dashboard</a>
        <a href="articles.php">📰 Articles</a>
        <a href="messages.php">✉️ Messages <?= $stats['messages_non_lus'] > 0 ? "({$stats['messages_non_lus']})" : '' ?></a>
        <a href="evenements.php">📅 Événements</a>
        <a href="galerie.php">📷 Galerie</a>
        <a href="parametres.php">⚙️ Paramètres</a>
    </nav>
    <div class="sidebar-footer">
        <a href="logout.php">🚪 Déconnexion</a>
    </div>
</div>

<div class="main">
    <h1>Dashboard</h1>
    <p class="subtitle">Bienvenue, <?= htmlspecialchars($_SESSION['admin_nom']) ?> — <?= date('d/m/Y') ?></p>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="number"><?= $stats['articles'] ?></div>
            <div class="label">Articles</div>
            <div class="sub"><?= $stats['articles_publies'] ?> publiés</div>
        </div>
        <div class="stat-card">
            <div class="number"><?= $stats['messages'] ?></div>
            <div class="label">Messages reçus</div>
            <?php if ($stats['messages_non_lus'] > 0): ?>
            <div class="sub" style="color:#e74c3c"><?= $stats['messages_non_lus'] ?> non lus</div>
            <?php endif; ?>
        </div>
        <div class="stat-card">
            <div class="number"><?= $stats['evenements'] ?></div>
            <div class="label">Événements</div>
        </div>
        <div class="stat-card">
            <div class="number"><?= $stats['galerie'] ?></div>
            <div class="label">Photos galerie</div>
        </div>
    </div>

    <div class="grid-2">
        <div class="card">
            <div class="card-header">
                <h2>📰 Derniers articles</h2>
                <a href="articles.php">Voir tout →</a>
            </div>
            <?php if (empty($derniers_articles)): ?>
                <p class="empty">Aucun article</p>
            <?php else: ?>
                <?php foreach ($derniers_articles as $a): ?>
                <div class="list-item">
                    <span><?= htmlspecialchars($a['titre']) ?></span>
                    <span class="badge <?= $a['publie'] ? 'badge-green' : 'badge-gray' ?>">
                        <?= $a['publie'] ? 'Publié' : 'Brouillon' ?>
                    </span>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="card">
            <div class="card-header">
                <h2>✉️ Derniers messages</h2>
                <a href="messages.php">Voir tout →</a>
            </div>
            <?php if (empty($derniers_messages)): ?>
                <p class="empty">Aucun message</p>
            <?php else: ?>
                <?php foreach ($derniers_messages as $m): ?>
                <div class="list-item">
                    <span><?= htmlspecialchars($m['nom']) ?> — <?= htmlspecialchars($m['sujet'] ?? '(sans sujet)') ?></span>
                    <?php if (!$m['lu']): ?>
                        <span class="badge badge-red">Non lu</span>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>