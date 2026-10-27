<?php
// Gabarit commun du back-office : <head>, sidebar et fermeture de page.

function admin_header($titre, $page, $head = '') {
    global $pdo;
    $nonLus = $pdo->query("SELECT COUNT(*) FROM messages_contact WHERE lu=0")->fetchColumn();
    $liens = [
        'index'      => '📊 Dashboard',
        'articles'   => '📰 Articles',
        'messages'   => '✉️ Messages',
        'evenements' => '📅 Événements',
        'galerie'    => '📷 Galerie',
        'parametres' => '⚙️ Paramètres',
    ];
    ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($titre) ?> — Admin CAT</title>
    <link rel="icon" type="image/png" href="../public/favicon.png">
    <link rel="stylesheet" href="admin.css">
    <?= $head ?>
</head>
<body>
<div class="sidebar">
    <a href="index.php" class="sidebar-logo"><img src="../public/favicon.png" alt="">CAT</a>
    <nav>
        <?php foreach ($liens as $fichier => $label): ?>
        <a href="<?= $fichier ?>.php" <?= $page === $fichier ? 'class="active"' : '' ?>>
            <?= $label ?><?php if ($fichier === 'messages' && $nonLus > 0): ?> <span class="sidebar-badge"><?= $nonLus ?></span><?php endif; ?>
        </a>
        <?php endforeach; ?>
    </nav>
    <div class="sidebar-footer">
        <a href="<?= BASE_URL ?>/index.php" target="_blank">🌐 Voir le site</a>
        <a href="logout.php">🚪 Déconnexion</a>
    </div>
</div>
<div class="main">
<?php
}

// Badge de statut d'un article : brouillon, programmé ou publié
function article_badge($a) {
    if (!$a['publie'])                                  return '<span class="badge badge-gray">Brouillon</span>';
    if ($a['published_at'] > date('Y-m-d H:i:s'))       return '<span class="badge badge-yellow">Programmé ' . date('d/m H:i', strtotime($a['published_at'])) . '</span>';
    return '<span class="badge badge-green">Publié</span>';
}

// Libellé lisible du sujet d'un message de contact
function sujet_label($sujet) {
    return SUJETS_CONTACT[$sujet] ?? ($sujet ?: '(sans sujet)');
}

function admin_footer($scripts = '') {
    ?>
</div>
<?= $scripts ?>
</body>
</html>
<?php
}
