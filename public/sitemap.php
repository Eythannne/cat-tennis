<?php
// Servi à l'adresse /sitemap.xml (voir .htaccess) : liste des pages pour Google.
require_once '../src/init.php';
header('Content-Type: application/xml; charset=utf-8');

$pages = [
    ['index.php',            'weekly',  '1.0'],
    ['club.php',             'monthly', '0.8'],
    ['actualites.php',       'weekly',  '0.8'],
    ['calendrier.php',       'weekly',  '0.7'],
    ['contact.php',          'yearly',  '0.6'],
    ['mentions-legales.php', 'yearly',  '0.2'],
];
$articles = $pdo->query(
    "SELECT id, COALESCE(updated_at, published_at) AS modif FROM articles WHERE " . sql_article_visible() . " ORDER BY published_at DESC"
)->fetchAll();

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php foreach ($pages as [$page, $frequence, $priorite]): ?>
    <url>
        <loc><?= htmlspecialchars(url_site($page)) ?></loc>
        <changefreq><?= $frequence ?></changefreq>
        <priority><?= $priorite ?></priority>
    </url>
<?php endforeach; ?>
<?php foreach ($articles as $a): ?>
    <url>
        <loc><?= htmlspecialchars(url_site('article.php?id=' . $a['id'])) ?></loc>
        <lastmod><?= date('Y-m-d', strtotime($a['modif'])) ?></lastmod>
        <priority>0.6</priority>
    </url>
<?php endforeach; ?>
</urlset>
