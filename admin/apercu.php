<?php
// Aperçu d'un article depuis le formulaire de l'admin : rien n'est enregistré.
// Le formulaire est envoyé ici (bouton "Aperçu") avec son jeton CSRF, vérifié par auth.php.
require_once 'auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: articles.php');
    exit;
}

$article = [
    'titre'        => trim($_POST['titre'] ?? '') ?: '(Sans titre)',
    'contenu'      => $_POST['contenu'] ?? '',
    'published_at' => !empty($_POST['published_at']) ? $_POST['published_at'] : date('Y-m-d H:i:s'),
];

// Image : celle tout juste recadrée (pas encore envoyée), sinon celle déjà enregistrée
if (!empty($_POST['image_cropped']) && preg_match('/^data:image\/(jpeg|png|webp);base64,[A-Za-z0-9+\/=]+$/', $_POST['image_cropped'])) {
    $imageSrc = $_POST['image_cropped'];
} else {
    $stmt = $pdo->prepare("SELECT image FROM articles WHERE id = ?");
    $stmt->execute([(int)($_POST['id'] ?? 0)]);
    $image = $stmt->fetchColumn();
    $imageSrc = BASE_URL . '/assets/' . ($image ? 'uploads/articles/' . $image : 'images/placeholder.png');
}

$pageTitle = 'Aperçu — ' . $article['titre'];
$extraHead = '<meta name="robots" content="noindex, nofollow">
    <style>
        .apercu-bandeau { position: fixed; bottom: 0; left: 0; right: 0; z-index: 5000; background: var(--jaune); color: var(--dark);
                          text-align: center; font-weight: 700; padding: 10px 16px; box-shadow: 0 -4px 16px rgba(0,0,0,0.2); }
        .apercu-bandeau button { margin-left: 12px; background: var(--vert-dark); color: #fff; border: none; border-radius: 6px; padding: 6px 14px; cursor: pointer; font-family: inherit; font-weight: 600; }
        body { padding-bottom: 50px; }
    </style>';
?>
<!DOCTYPE html>
<html lang="fr">
<?php include '../templates/head.php'; ?>
<body>
<?php
$activePage = 'actualites';
$navScrolled = 'scrolled';
include '../templates/navbar.php';
include '../templates/article-contenu.php';
include '../templates/footer.php';
?>
<div class="apercu-bandeau">
    👁️ Aperçu — cet article n'est pas encore enregistré
    <button type="button" onclick="window.close()">Fermer l'aperçu</button>
</div>
<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
</body>
</html>
