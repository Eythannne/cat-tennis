<?php
require_once 'auth.php';

$stats = [
    'articles' => $pdo->query("SELECT COUNT(*) FROM articles")->fetchColumn(),
    'articles_publies' => $pdo->query("SELECT COUNT(*) FROM articles WHERE " . sql_article_visible())->fetchColumn(),
    'messages' => $pdo->query("SELECT COUNT(*) FROM messages_contact")->fetchColumn(),
    'messages_non_lus' => $pdo->query("SELECT COUNT(*) FROM messages_contact WHERE lu=0")->fetchColumn(),
    'evenements' => $pdo->query("SELECT COUNT(*) FROM evenements")->fetchColumn(),
    'galerie' => $pdo->query("SELECT COUNT(*) FROM galerie")->fetchColumn(),
];

$derniers_articles = $pdo->query("SELECT * FROM articles ORDER BY created_at DESC LIMIT 5")->fetchAll();
$derniers_messages = $pdo->query("SELECT * FROM messages_contact ORDER BY created_at DESC LIMIT 5")->fetchAll();

admin_header('Dashboard', 'index', '<style>
    .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 30px; }
    .stat-card { background: #fff; border-radius: 8px; padding: 20px; box-shadow: 0 1px 4px rgba(0,0,0,0.08); border-left: 4px solid var(--jaune); }
    .stat-card .number { font-size: 2rem; font-weight: 700; color: var(--vert); }
    .stat-card .label { font-size: 0.85rem; color: #888; margin-top: 4px; }
    .stat-card .sub { font-size: 0.8rem; color: var(--vert); margin-top: 2px; }
    .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    .grid-2 .card { margin-bottom: 0; }
    .card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; }
    .card-header h2 { margin-bottom: 0; }
    .card-header a { font-size: 0.82rem; color: var(--vert); text-decoration: none; }
    .list-item { display: flex; justify-content: space-between; align-items: center; gap: 10px; padding: 10px 0; border-bottom: 1px solid #f0f0f0; font-size: 0.88rem; }
    .list-item:last-child { border-bottom: none; }
    @media (max-width: 1000px) { .stats-grid { grid-template-columns: 1fr 1fr; } .grid-2 { grid-template-columns: 1fr; } }
</style>');
?>
    <h1>Dashboard</h1>
    <p class="subtitle">Bienvenue, <?= htmlspecialchars($_SESSION['admin_nom']) ?> — <?= date('d/m/Y') ?></p>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="number"><?= $stats['articles'] ?></div>
            <div class="label">Articles</div>
            <div class="sub"><?= $stats['articles_publies'] ?> en ligne</div>
        </div>
        <div class="stat-card">
            <div class="number"><?= $stats['messages'] ?></div>
            <div class="label">Messages reçus</div>
            <?php if ($stats['messages_non_lus'] > 0): ?>
            <div class="sub" style="color:var(--danger)"><?= $stats['messages_non_lus'] ?> non lus</div>
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
                    <?= article_badge($a) ?>
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
                    <span><?= htmlspecialchars($m['nom']) ?> — <?= htmlspecialchars(sujet_label($m['sujet'])) ?></span>
                    <?php if (!$m['lu']): ?>
                        <span class="badge badge-red">Non lu</span>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
<?php admin_footer(); ?>
