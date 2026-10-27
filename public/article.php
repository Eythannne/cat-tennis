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
$pageDesc = texte_brut($article['contenu'], 155);
$pageType = 'article';
if (!empty($article['image'])) $pageImage = 'assets/uploads/articles/' . $article['image'];
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

<?php
$imageSrc = BASE_URL . '/assets/' . (!empty($article['image']) ? 'uploads/articles/' . $article['image'] : 'images/placeholder.png');
include '../templates/article-contenu.php';
?>

<?php include '../templates/footer.php'; ?>

<script src="assets/js/main.js"></script>
</body>
</html>
