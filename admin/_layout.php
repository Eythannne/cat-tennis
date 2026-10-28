<?php
// Gabarit commun du back-office : <head>, sidebar et fermeture de page.

// ── Rôles ────────────────────────────────────────────────────────────────────
// Fondateur : compte unique, accès complet, ni modifiable ni supprimable par les administrateurs.
//             Désigné en base de données (sql/migration-*-fondateur.sql), jamais depuis l'admin.
// Administrateur : accès complet (sauf toucher au compte fondateur).
// Modérateur : tout le contenu, Paramètres en lecture seule, pas d'accès aux Administrateurs.
const ROLES = [
    'fondateur'      => 'Fondateur',
    'administrateur' => 'Administrateur',
    'moderateur'     => 'Modérateur',
];
// Rôles proposés dans le formulaire des comptes (le fondateur n'est jamais attribuable)
const ROLES_ATTRIBUABLES = ['administrateur', 'moderateur'];
const PAGES_LECTURE_SEULE   = ['parametres'];  // visibles par les modérateurs, sans modification
const PAGES_ADMINISTRATEURS = ['admins'];      // masquées et interdites aux modérateurs
const PAGES_FONDATEUR       = ['journal'];     // masquées et interdites à tous sauf au fondateur

function est_fondateur() {
    return ($_SESSION['admin_role'] ?? '') === 'fondateur';
}

// Fondateur ou administrateur : accès complet au back-office
function est_administrateur() {
    return in_array($_SESSION['admin_role'] ?? '', ['fondateur', 'administrateur'], true);
}

// Badge coloré d'un rôle
function badge_role($role) {
    $classes = ['fondateur' => 'badge-fondateur', 'administrateur' => 'badge-yellow', 'moderateur' => 'badge-green'];
    return '<span class="badge ' . ($classes[$role] ?? 'badge-gray') . '">'
         . ($role === 'fondateur' ? '★ ' : '') . (ROLES[$role] ?? htmlspecialchars($role)) . '</span>';
}

// À appeler en haut d'une page de PAGES_FONDATEUR : tout autre compte est refusé.
function exiger_fondateur() {
    if (!est_fondateur()) acces_refuse();
}

// À appeler en haut d'une page de PAGES_ADMINISTRATEURS : un modérateur est refusé,
// même s'il tape l'adresse à la main.
function exiger_administrateur() {
    if (!est_administrateur()) acces_refuse();
}

function acces_refuse() {
    http_response_code(403);
    admin_header('Accès refusé', '');
    echo '<h1>🔒 Accès refusé</h1>
          <div class="card"><p>Vous n\'avez pas accès à cette page.</p>
          <p style="margin-top:16px"><a href="index.php" class="btn btn-primary">← Retour au dashboard</a></p></div>';
    admin_footer();
    exit;
}

// À appeler en haut des pages de PAGES_LECTURE_SEULE.
// Modérateur : la page s'affiche (renvoie true = lecture seule) mais tout envoi de formulaire
// est refusé, même s'il contourne l'affichage.
function lecture_seule_pour_moderateur() {
    if (est_administrateur()) return false;
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        http_response_code(403);
        admin_header('Lecture seule', '');
        echo '<h1>👁️ Lecture seule</h1>
              <div class="card"><p>Votre compte est <strong>modérateur</strong> : vous pouvez consulter cette page,
              mais seuls les <strong>administrateurs</strong> peuvent la modifier.</p>
              <p style="margin-top:16px"><a href="index.php" class="btn btn-primary">← Retour au dashboard</a></p></div>';
        admin_footer();
        exit;
    }
    return true;
}

// Bandeau affiché en haut d'une page en lecture seule
function bandeau_lecture_seule() {
    echo '<div class="alert alert-info">👁️ <strong>Lecture seule</strong> — votre compte est modérateur :
          vous pouvez consulter cette page, mais seuls les administrateurs peuvent la modifier.</div>';
}

function admin_header($titre, $page, $head = '') {
    global $pdo;
    $nonLus = $pdo->query("SELECT COUNT(*) FROM messages_contact WHERE lu=0")->fetchColumn();
    $liens = [
        'index'      => '📊 Dashboard',
        'articles'   => '📰 Articles',
        'messages'   => '✉️ Messages',
        'evenements' => '📅 Événements',
        'galerie'    => '📷 Galerie',
        'entraineurs'=> '🎾 Entraîneurs',
        'tarifs'     => '💶 Tarifs',
        'parametres' => '⚙️ Paramètres',
        'admins'     => '👥 Administrateurs',
        'journal'    => '📜 Journal',
        'compte'     => '👤 Mon compte',
    ];
    if (!est_administrateur()) {
        $liens = array_diff_key($liens, array_flip(PAGES_ADMINISTRATEURS));
    }
    if (!est_fondateur()) {
        $liens = array_diff_key($liens, array_flip(PAGES_FONDATEUR));
    }
    ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($titre) ?> — Admin CAT</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="icon" type="image/png" href="../public/favicon.png">
    <link rel="stylesheet" href="admin.css">
    <?= $head ?>
</head>
<body>
<div class="sidebar">
    <a href="index.php" class="sidebar-logo"><img src="../public/favicon.png" alt="">CAT</a>
    <div class="sidebar-user">
        <?= htmlspecialchars($_SESSION['admin_nom'] ?? '') ?>
        <span class="sidebar-role"><?= ROLES[$_SESSION['admin_role'] ?? ''] ?? '' ?></span>
    </div>
    <nav>
        <?php foreach ($liens as $fichier => $label): ?>
        <a href="<?= $fichier ?>.php" <?= $page === $fichier ? 'class="active"' : '' ?>>
            <?= $label ?><?php if ($fichier === 'messages' && $nonLus > 0): ?> <span class="sidebar-badge"><?= $nonLus ?></span><?php endif; ?>
            <?php if (!est_administrateur() && in_array($fichier, PAGES_LECTURE_SEULE, true)): ?><span class="sidebar-lecture" title="Lecture seule">👁️</span><?php endif; ?>
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

// ── Ordre d'affichage (boutons ↑ ↓ / ← →) ───────────────────────────────────
// Échange l'élément $id avec son voisin puis renumérote 1..n.
// $table vient toujours du code (jamais de l'utilisateur).
function deplacer_ordre($table, $id, $sens, $tri = 'ordre, id') {
    global $pdo;
    $ids = array_map('intval', $pdo->query("SELECT id FROM $table ORDER BY $tri")->fetchAll(PDO::FETCH_COLUMN));
    $pos = array_search((int)$id, $ids, true);
    $cible = $pos + ($sens === 'avant' ? -1 : 1);
    if ($pos === false || !isset($ids[$cible])) return;
    [$ids[$pos], $ids[$cible]] = [$ids[$cible], $ids[$pos]];
    $stmt = $pdo->prepare("UPDATE $table SET ordre=? WHERE id=?");
    foreach ($ids as $i => $autreId) $stmt->execute([$i + 1, $autreId]);
}

// Les deux petits formulaires ↑ ↓ d'une ligne ($i = position, $total = nombre d'éléments)
function boutons_ordre($id, $i, $total, $fleches = ['avant' => '↑', 'apres' => '↓']) {
    foreach ($fleches as $sens => $fleche): ?>
    <form method="POST">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="move">
        <input type="hidden" name="sens" value="<?= $sens ?>">
        <input type="hidden" name="id" value="<?= $id ?>">
        <button type="submit" class="btn btn-secondary btn-sm" title="Déplacer"
            <?= ($sens === 'avant' && $i === 0) || ($sens === 'apres' && $i === $total - 1) ? 'disabled' : '' ?>><?= $fleche ?></button>
    </form>
    <?php endforeach;
}

// ── Recadrage d'image (voir crop.js) ─────────────────────────────────────────
// À mettre dans le <head> de la page
const CROP_HEAD = '<link rel="stylesheet" href="../public/assets/css/cropper.min.css">';
// À passer à admin_footer()
const CROP_SCRIPTS = '<script src="../public/assets/js/cropper.min.js"></script><script src="crop.js"></script>';

// Champ "choisir une image" + aperçu, à placer dans le formulaire
function crop_field($ratio, $width, $height) {
    ?>
    <input type="hidden" name="image_cropped" id="image_cropped">
    <input type="file" id="image_picker" accept="image/jpeg,image/png,image/webp" style="font-size:0.9rem;max-width:100%"
           data-ratio="<?= $ratio ?>" data-width="<?= $width ?>" data-height="<?= $height ?>">
    <div id="crop_preview_wrap" hidden>
        <img id="crop_preview_thumb" class="preview-img" style="max-height:80px" alt="">
        <span style="font-size:0.8rem;color:var(--vert);">✅ Image recadrée prête</span>
        <button type="button" id="crop_redo" class="link-btn">Recadrer à nouveau</button>
    </div>
    <?php
}

// Fenêtre de recadrage, à placer une fois en bas de la page (hors du formulaire)
function crop_modal() {
    ?>
<div class="crop-overlay" id="crop_overlay">
    <div class="crop-modal">
        <h3>✂️ Recadrer l'image</h3>
        <p class="crop-hint">Déplacez et zoomez pour choisir la zone à afficher (la forme du cadre est fixe).</p>
        <div class="crop-container">
            <img id="crop_img" src="" alt="Crop">
        </div>
        <div class="crop-actions">
            <button type="button" class="btn btn-secondary" id="crop_cancel">Annuler</button>
            <button type="button" class="btn btn-primary" id="crop_confirm">✅ Valider le recadrage</button>
        </div>
    </div>
</div>
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
