<?php
// Corps d'une page article : utilisé par public/article.php et par l'aperçu de l'admin.
// Attend $article (titre, contenu, published_at) et $imageSrc (adresse de l'image).
?>
<section class="page-hero">
    <div class="container">
        <h1><?= htmlspecialchars($article['titre']) ?></h1>
        <p>Publié le <?= date('d F Y', strtotime($article['published_at'] ?? 'now')) ?></p>
    </div>
</section>

<section class="section">
    <div class="container article-container">
        <a href="<?= BASE_URL ?>/actualites.php" class="btn-back">← Retour aux actualités</a>

        <div class="article-img-wrap">
            <img src="<?= htmlspecialchars($imageSrc) ?>" alt="<?= htmlspecialchars($article['titre']) ?>">
        </div>

        <div class="article-content">
            <?= format_article($article['contenu']) ?>
        </div>

        <div class="article-footer">
            <a href="<?= BASE_URL ?>/actualites.php" class="btn-back">← Retour aux actualités</a>
        </div>
    </div>
</section>
