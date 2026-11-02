<?php
require_once 'auth.php';

$stats = [
    'articles' => $pdo->query("SELECT COUNT(*) FROM articles")->fetchColumn(),
    'articles_publies' => $pdo->query("SELECT COUNT(*) FROM articles WHERE " . sql_article_visible())->fetchColumn(),
    'messages' => $pdo->query("SELECT COUNT(*) FROM messages_contact")->fetchColumn(),
    'messages_non_lus' => $pdo->query("SELECT COUNT(*) FROM messages_contact WHERE lu=0")->fetchColumn(),
    'evenements_a_venir' => $pdo->query("SELECT COUNT(*) FROM evenements WHERE COALESCE(date_fin, date_debut) >= '" . date('Y-m-d') . "'")->fetchColumn(),
    'galerie' => $pdo->query("SELECT COUNT(*) FROM galerie")->fetchColumn(),
];

// ── « À compléter » : ce qui manque encore sur le site (lien vers la bonne page) ──
$aFaire = [];
if ($stats['messages_non_lus'] > 0) {
    $aFaire[] = ['✉️', "{$stats['messages_non_lus']} message(s) non lu(s)", 'messages.php'];
}
if (est_administrateur() && (param('responsable_publication') === '' || param('rna') === '')) {
    $aFaire[] = ['⚖️', 'Informations légales incomplètes (responsable de publication, numéro RNA)', 'parametres.php?onglet=infos'];
}
$sansPdf = $pdo->query("SELECT COUNT(*) FROM tarifs WHERE pdf IS NULL OR pdf = ''")->fetchColumn();
if ($sansPdf > 0) $aFaire[] = ['📄', "$sansPdf formule(s) de tarif sans fiche d'inscription PDF", 'tarifs.php'];
$prixADefinir = $pdo->query("SELECT COUNT(*) FROM tarifs WHERE prix LIKE '%définir%'")->fetchColumn();
if ($prixADefinir > 0) $aFaire[] = ['💶', "$prixADefinir formule(s) avec un prix « À définir »", 'tarifs.php'];
if (!$pdo->query("SELECT COUNT(*) FROM bureau")->fetchColumn()) {
    $aFaire[] = ['🏛️', "Aucun membre du bureau : la section « L'équipe administrative » est masquée", 'bureau.php'];
}
$brouillons = $pdo->query("SELECT COUNT(*) FROM articles WHERE publie = 0")->fetchColumn();
if ($brouillons > 0) $aFaire[] = ['📝', "$brouillons article(s) en brouillon", 'articles.php'];

// ── Raccourcis : les pages du menu (selon le rôle), avec une courte description ──
const DESCRIPTIONS_PAGES = [
    'articles'    => 'Écrire, publier, programmer',
    'evenements'  => 'Tournois, stages, vacances',
    'galerie'     => 'Photos et catégories',
    'entraineurs' => 'Fiches et photo de groupe',
    'bureau'      => 'Équipe administrative',
    'tarifs'      => 'Formules et fiches PDF',
    'messages'    => 'Formulaire de contact',
    'parametres'  => 'Infos, chiffres clés, textes',
    'admins'      => 'Comptes et rôles',
    'journal'     => 'Historique des actions',
];

$derniers_articles = $pdo->query("SELECT * FROM articles ORDER BY created_at DESC LIMIT 5")->fetchAll();
$derniers_messages = $pdo->query("SELECT * FROM messages_contact ORDER BY created_at DESC LIMIT 5")->fetchAll();

admin_header('Dashboard', 'index', '<style>
    .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 24px; }
    .stat-card { background: #fff; border-radius: 8px; padding: 18px 20px; box-shadow: 0 1px 4px rgba(0,0,0,0.08); border-left: 4px solid var(--jaune); text-decoration: none; color: inherit; transition: transform 0.15s; }
    .stat-card:hover { transform: translateY(-2px); }
    .stat-card .number { font-size: 1.9rem; font-weight: 700; color: var(--vert); }
    .stat-card .label { font-size: 0.85rem; color: #888; margin-top: 2px; }
    .stat-card .sub { font-size: 0.8rem; color: var(--vert); margin-top: 2px; }
    .a-faire { border-left: 4px solid var(--jaune); }
    .a-faire ul { list-style: none; }
    .a-faire li a { display: flex; gap: 10px; align-items: center; padding: 9px 4px; border-bottom: 1px solid #f0f0f0; color: #333; text-decoration: none; font-size: 0.9rem; }
    .a-faire li:last-child a { border-bottom: none; }
    .a-faire li a:hover { color: var(--vert); }
    .a-faire li a::after { content: "→"; margin-left: auto; color: var(--vert); }
    .raccourcis-groupe { margin-bottom: 18px; }
    .raccourcis-groupe h3 { font-size: 0.72rem; text-transform: uppercase; letter-spacing: 1.2px; color: #999; margin-bottom: 8px; }
    .raccourcis { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 10px; }
    .raccourci { display: block; padding: 14px; border: 1px solid #e3e8e4; border-radius: 8px; text-decoration: none; color: #333; background: #fafcfa; transition: border-color 0.15s, background 0.15s; }
    .raccourci:hover { border-color: var(--vert); background: var(--vert-light); }
    .raccourci strong { display: block; font-size: 0.95rem; margin-bottom: 2px; }
    .raccourci span { font-size: 0.8rem; color: #888; }
    .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    .grid-2 .card { margin-bottom: 0; }
    .card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; }
    .card-header h2 { margin-bottom: 0; }
    .card-header a { font-size: 0.82rem; color: var(--vert); text-decoration: none; }
    .list-item { display: flex; justify-content: space-between; align-items: center; gap: 10px; padding: 9px 0; border-bottom: 1px solid #f0f0f0; font-size: 0.88rem; }
    .list-item:last-child { border-bottom: none; }
    @media (max-width: 1000px) { .stats-grid { grid-template-columns: 1fr 1fr; } .grid-2 { grid-template-columns: 1fr; } }
</style>');
?>
    <h1>Dashboard</h1>
    <p class="subtitle">Bienvenue, <?= htmlspecialchars($_SESSION['admin_nom']) ?> — <?= date('d/m/Y') ?></p>

    <div class="stats-grid">
        <a href="articles.php" class="stat-card">
            <div class="number"><?= $stats['articles'] ?></div>
            <div class="label">Articles</div>
            <div class="sub"><?= $stats['articles_publies'] ?> en ligne</div>
        </a>
        <a href="messages.php" class="stat-card">
            <div class="number"><?= $stats['messages'] ?></div>
            <div class="label">Messages reçus</div>
            <?php if ($stats['messages_non_lus'] > 0): ?>
            <div class="sub" style="color:var(--danger)"><?= $stats['messages_non_lus'] ?> non lus</div>
            <?php endif; ?>
        </a>
        <a href="evenements.php" class="stat-card">
            <div class="number"><?= $stats['evenements_a_venir'] ?></div>
            <div class="label">Événements à venir</div>
        </a>
        <a href="galerie.php" class="stat-card">
            <div class="number"><?= $stats['galerie'] ?></div>
            <div class="label">Photos galerie</div>
        </a>
    </div>

    <?php if ($aFaire): ?>
    <div class="card a-faire">
        <h2>📌 À compléter</h2>
        <ul>
            <?php foreach ($aFaire as [$icone, $texte, $lien]): ?>
            <li><a href="<?= $lien ?>"><span><?= $icone ?></span> <?= htmlspecialchars($texte) ?></a></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <div class="card">
        <h2>🧭 Accès rapide</h2>
        <?php foreach (MENU_ADMIN as [$groupe, $liens]):
            if (!est_administrateur()) $liens = array_diff_key($liens, array_flip(PAGES_ADMINISTRATEURS));
            if (!est_fondateur())      $liens = array_diff_key($liens, array_flip(PAGES_FONDATEUR));
            unset($liens['index']);
            if (!$liens) continue; ?>
        <div class="raccourcis-groupe">
            <h3><?= $groupe ?></h3>
            <div class="raccourcis">
                <?php foreach ($liens as $fichier => $label): ?>
                <a href="<?= $fichier ?>.php" class="raccourci">
                    <strong><?= $label ?></strong>
                    <span><?= DESCRIPTIONS_PAGES[$fichier] ?? '' ?></span>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>
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
