<?php
require_once '../src/init.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id) {
    header('Location: actualites.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM articles WHERE id = ? AND " . sql_article_visible());
$stmt->execute([$id]);
$article = $stmt->fetch();

if (!$article) {
    header('Location: actualites.php');
    exit;
}

$pageTitle = $article['titre'] . " — CAT";
$pageDesc = mb_substr($article['contenu'], 0, 150);
?>
<!DOCTYPE html>
<html lang="fr">
<?php include '../templates/head.php'; ?>
<body>

<?php
$activePage = 'actualites';
$navScrolled = 'scrolled';
include '../templates/navbar.php';
?>

<section class="page-hero">
    <div class="container">
        <h1><?= htmlspecialchars($article['titre']) ?></h1>
        <p>Publié le <?= date('d F Y', strtotime($article['published_at'])) ?></p>
    </div>
</section>

<section class="section">
    <div class="container article-container">
        <a href="actualites.php" class="btn-back">← Retour aux actualités</a>

        <div class="article-img-wrap">
            <img src="<?= !empty($article['image']) ? 'assets/uploads/articles/'.htmlspecialchars($article['image']) : 'assets/images/placeholder.png' ?>"
                 alt="<?= htmlspecialchars($article['titre']) ?>">
        </div>

        <div class="article-content">
            <?= nl2br(htmlspecialchars($article['contenu'])) ?>
        </div>

        <div class="article-footer">
            <a href="actualites.php" class="btn-back">← Retour aux actualités</a>
        </div>
    </div>
</section>

<?php include '../templates/footer.php'; ?>

<script src="assets/js/main.js"></script>
</body>
</html>
