<?php
require_once 'auth.php';

$aujourdhui = date('Y-m-d');
$compter = fn($sql) => (int)$pdo->query($sql)->fetchColumn();

$nonLus     = $compter("SELECT COUNT(*) FROM messages_contact WHERE lu = 0");
$brouillons = $compter("SELECT COUNT(*) FROM articles WHERE publie = 0");

$stats = [
    ['📰', $compter("SELECT COUNT(*) FROM articles WHERE " . sql_article_visible()), 'articles en ligne', 'articles.php'],
    ['📅', $compter("SELECT COUNT(*) FROM evenements WHERE COALESCE(date_fin, date_debut) >= '$aujourdhui'"), 'événements à venir', 'evenements.php'],
    ['📷', $compter("SELECT COUNT(*) FROM galerie"), 'photos dans la galerie', 'galerie.php'],
    ['✉️', $compter("SELECT COUNT(*) FROM messages_contact"), 'messages reçus', 'messages.php'],
];

// ── Mini-calendrier du mois (?mois=AAAA-MM pour naviguer) ──
$mois = preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $_GET['mois'] ?? '') ? $_GET['mois'] : date('Y-m');
$premier  = strtotime("$mois-01");
$nbJours  = (int)date('t', $premier);
$dernier  = date('Y-m-d', strtotime("$mois-$nbJours"));
$stmt = $pdo->prepare("SELECT * FROM evenements WHERE DATE(date_debut) <= ? AND DATE(COALESCE(date_fin, date_debut)) >= ? ORDER BY date_debut");
$stmt->execute([$dernier, "$mois-01"]);
$evenementsMois = $stmt->fetchAll();
// Pour chaque jour : vacances (fond coloré) et autres événements (pastilles)
$joursCalendrier = [];
foreach ($evenementsMois as $e) {
    $d   = max(strtotime(date('Y-m-d', strtotime($e['date_debut']))), $premier);
    $fin = min(strtotime(date('Y-m-d', strtotime($e['date_fin'] ?: $e['date_debut']))), strtotime($dernier));
    for (; $d <= $fin; $d = strtotime('+1 day', $d)) {
        $joursCalendrier[(int)date('j', $d)][] = $e;
    }
}
// Jours fériés du mois, mêlés aux événements dans la liste sous la grille (triée par date)
$feriesMois = array_filter(jours_feries((int)date('Y', $premier)), fn($d) => str_starts_with($d, $mois), ARRAY_FILTER_USE_KEY);
$lignesMois = [];
foreach ($evenementsMois as $e) {
    $type = EVENT_TYPES[$e['type']] ?? EVENT_TYPES['autre'];
    $lignesMois[] = ['date' => $e['date_debut'], 'html' => '<em style="background:' . $type['couleur'] . '"></em><a href="evenements.php?edit=' . $e['id'] . '">' . htmlspecialchars($e['titre']) . '</a><small>' . quand_evenement($e) . '</small>'];
}
foreach ($feriesMois as $date => $nom) {
    $lignesMois[] = ['date' => $date, 'html' => '<em style="background:' . FERIE_COULEUR . '"></em><span class="ferie-nom">' . $nom . '</span><small>Férié · ' . jour_mois(strtotime($date)) . '</small>'];
}
usort($lignesMois, fn($a, $b) => strcmp($a['date'], $b['date']));

$moisPrecedent =date('Y-m', strtotime('-1 month', $premier));
$moisSuivant   = date('Y-m', strtotime('+1 month', $premier));

$evenements = $pdo->query("SELECT * FROM evenements WHERE COALESCE(date_fin, date_debut) >= '$aujourdhui' ORDER BY date_debut LIMIT 4")->fetchAll();
$messages   = $pdo->query("SELECT * FROM messages_contact ORDER BY created_at DESC LIMIT 4")->fetchAll();
$articles   = $pdo->query("SELECT * FROM articles ORDER BY created_at DESC LIMIT 4")->fetchAll();
$activite   = est_fondateur() ? $pdo->query("SELECT * FROM journal ORDER BY created_at DESC LIMIT 5")->fetchAll() : [];

const JOURS_FR    = ['dimanche', 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'];
const MOIS_COURTS = ['janv', 'févr', 'mars', 'avr', 'mai', 'juin', 'juil', 'août', 'sept', 'oct', 'nov', 'déc'];

// « il y a 5 min », « il y a 3 h », « hier », « 12 oct. »
function il_y_a($date) {
    $ecart = time() - strtotime($date);
    if ($ecart < 60)    return "à l'instant";
    if ($ecart < 3600)  return 'il y a ' . floor($ecart / 60) . ' min';
    if ($ecart < 86400 && date('Y-m-d', strtotime($date)) === date('Y-m-d')) return 'il y a ' . floor($ecart / 3600) . ' h';
    if (date('Y-m-d', strtotime($date)) === date('Y-m-d', strtotime('-1 day'))) return 'hier';
    return jour_mois(strtotime($date));
}

// Phrase de résumé sous le bonjour
$resume = [];
if ($nonLus)     $resume[] = "<strong>$nonLus message" . ($nonLus > 1 ? 's' : '') . " non lu" . ($nonLus > 1 ? 's' : '') . "</strong>";
if ($brouillons) $resume[] = "<strong>$brouillons brouillon" . ($brouillons > 1 ? 's' : '') . "</strong> en attente";
if ($evenements) {
    $prochain = $evenements[0];
    $jours = (int)((strtotime(date('Y-m-d', strtotime($prochain['date_debut']))) - strtotime($aujourdhui)) / 86400);
    $quand = $jours <= 0 ? "aujourd'hui" : ($jours === 1 ? 'demain' : "dans $jours jours");
    $resume[] = 'prochain événement <strong>' . $quand . '</strong>';
}
$phrase = $resume ? 'Au programme : ' . implode(', ', $resume) . '.' : 'Tout est à jour, rien ne vous attend. 🎾';

$prenom = explode(' ', trim($_SESSION['admin_nom']))[0];

admin_header('Dashboard', 'index', '<style>
    /* Contraste renforcé, sur cette page uniquement */
    body { background: #e9eeea; }
    .main .card { border: 1px solid #d9e0db; box-shadow: 0 2px 6px rgba(0,0,0,0.07); }
    .main .card h2 { color: #1f2a22; }
    .main .empty { color: #6b746e; }

    .accueil { background: linear-gradient(120deg, var(--vert-dark), var(--vert)); color: #fff; border-radius: 12px; padding: 26px 28px; margin-bottom: 20px; display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 18px; }
    .accueil h1 { color: #fff; margin: 0 0 4px; }
    .accueil .date { opacity: 0.85; font-size: 0.85rem; text-transform: capitalize; }
    .accueil p { margin-top: 10px; font-size: 0.95rem; opacity: 0.95; }
    .accueil strong { color: var(--jaune); }
    .accueil-actions { display: flex; flex-wrap: wrap; gap: 8px; }
    .accueil-actions .btn-ghost { background: rgba(255,255,255,0.12); color: #fff; }
    .accueil-actions .btn-ghost:hover { background: rgba(255,255,255,0.22); }

    .chiffres { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 20px; }
    .chiffre { display: flex; align-items: center; gap: 12px; background: #fff; border-radius: 10px; padding: 14px 16px; border: 1px solid #d9e0db; box-shadow: 0 2px 6px rgba(0,0,0,0.06); text-decoration: none; color: #1f2a22; transition: transform 0.15s; }
    .chiffre:hover { transform: translateY(-2px); }
    .chiffre .icone { font-size: 1.3rem; width: 42px; height: 42px; border-radius: 10px; background: var(--vert-light); display: grid; place-items: center; flex-shrink: 0; }
    .chiffre b { display: block; font-size: 1.35rem; color: var(--vert); line-height: 1.1; }
    .chiffre span { font-size: 0.78rem; color: #5c6660; }

    .colonnes { display: grid; grid-template-columns: 1.6fr 1fr; gap: 0 20px; align-items: start; }
    .colonnes .card { margin-bottom: 20px; }
    .titre-carte { display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 14px; }
    .titre-carte h2 { margin: 0; }
    .titre-carte a { font-size: 0.8rem; color: var(--vert); text-decoration: none; }

    .evt { display: flex; gap: 14px; align-items: center; padding: 10px 0; border-bottom: 1px solid #e3e8e4; text-decoration: none; color: #1f2a22; }
    .evt:last-child { border-bottom: none; }
    .evt-date { width: 50px; flex-shrink: 0; text-align: center; border-radius: 8px; padding: 6px 0; color: #fff; }
    .evt-date b { display: block; font-size: 1.25rem; line-height: 1; }
    .evt-date small { font-size: 0.68rem; text-transform: uppercase; letter-spacing: 0.5px; }
    .evt-info strong { display: block; font-size: 0.92rem; }
    .evt-info span { font-size: 0.78rem; color: #5c6660; }

    .msg { display: flex; gap: 12px; padding: 10px 0; border-bottom: 1px solid #e3e8e4; text-decoration: none; color: #1f2a22; }
    .msg:last-child { border-bottom: none; }
    .msg-avatar { width: 36px; height: 36px; border-radius: 50%; background: #eef1ee; color: var(--vert); font-weight: 700; font-size: 0.8rem; display: grid; place-items: center; flex-shrink: 0; }
    .msg.non-lu .msg-avatar { background: var(--jaune); color: #111; }
    .msg-corps { min-width: 0; flex: 1; }
    .msg-ligne { display: flex; justify-content: space-between; gap: 8px; font-size: 0.88rem; }
    .msg.non-lu .msg-ligne strong::after { content: ""; display: inline-block; width: 7px; height: 7px; border-radius: 50%; background: var(--danger); margin-left: 6px; vertical-align: middle; }
    .msg-ligne time { font-size: 0.75rem; color: #6b746e; white-space: nowrap; }
    .msg-extrait { font-size: 0.8rem; color: #5c6660; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

    .calendrier-tete { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; }
    .calendrier-tete h2 { margin: 0; }
    .calendrier-tete a { width: 30px; height: 30px; border-radius: 50%; display: grid; place-items: center; text-decoration: none; color: var(--vert-dark); font-size: 1.3rem; background: #eef2ef; }
    .calendrier-tete a:hover { background: var(--vert-light); }
    .calendrier { display: grid; grid-template-columns: repeat(7, 1fr); gap: 3px; text-align: center; }
    .calendrier b { font-size: 0.72rem; color: #4d5750; padding-bottom: 4px; }
    .calendrier a { position: relative; height: 40px; display: grid; place-items: center; border-radius: 8px; font-size: 0.85rem; font-weight: 600; color: #1f2a22; text-decoration: none; }
    .calendrier a:hover { background: #eef2ef; }
    .calendrier a.passe { color: #a3aba5; font-weight: 400; }
    .calendrier a.vacances { background: #EFE3F5; }
    .calendrier a.ferie { color: #A93226; box-shadow: inset 0 0 0 2px #F1B5AE; }
    .calendrier a.auj { background: var(--vert); color: #fff; }
    .calendrier-liste .ferie-nom { color: #A93226; font-weight: 600; }
    .calendrier i { position: absolute; bottom: 3px; left: 0; right: 0; display: flex; justify-content: center; gap: 2px; }
    .calendrier em, .calendrier-liste em { display: inline-block; width: 6px; height: 6px; border-radius: 50%; }
    .calendrier a.auj em { outline: 1px solid #fff; }
    .calendrier-liste { list-style: none; margin-top: 14px; border-top: 1px solid #e3e8e4; padding-top: 8px; }
    .calendrier-liste li { display: flex; align-items: center; gap: 8px; padding: 5px 0; font-size: 0.84rem; }
    .calendrier-liste em { width: 8px; height: 8px; flex-shrink: 0; }
    .calendrier-liste a { color: #1f2a22; text-decoration: none; font-weight: 600; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .calendrier-liste a:hover { color: var(--vert); }
    .calendrier-liste small { margin-left: auto; color: #5c6660; white-space: nowrap; font-size: 0.76rem; }
    .calendrier-vide { margin-top: 12px; text-align: center; font-size: 0.84rem; color: #5c6660; }

    .ligne { display: flex; justify-content: space-between; align-items: center; gap: 10px; padding: 8px 0; border-bottom: 1px solid #e3e8e4; font-size: 0.86rem; color: #1f2a22; text-decoration: none; }
    .ligne:last-child { border-bottom: none; }
    .ligne span:first-child { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    a.ligne:hover { color: var(--vert); }
    .action-journal { font-size: 0.82rem; padding: 7px 0; border-bottom: 1px solid #e3e8e4; }
    .action-journal:last-child { border-bottom: none; }
    .action-journal small { display: block; color: #6b746e; font-size: 0.74rem; }

    @media (max-width: 1100px) { .chiffres { grid-template-columns: 1fr 1fr; } .colonnes { grid-template-columns: 1fr; } }
    @media (max-width: 600px) { .accueil { padding: 20px; } .chiffre { padding: 12px; } .chiffre .icone { width: 36px; height: 36px; font-size: 1.1rem; } }
</style>');
?>
    <section class="accueil">
        <div>
            <h1><?= date('G') < 18 ? 'Bonjour' : 'Bonsoir' ?> <?= htmlspecialchars($prenom) ?> 👋</h1>
            <div class="date"><?= JOURS_FR[date('w')] ?> <?= date_fr(time()) ?></div>
            <p><?= $phrase ?></p>
        </div>
        <div class="accueil-actions">
            <a href="articles.php" class="btn btn-accent">＋ Article</a>
            <a href="evenements.php" class="btn btn-ghost">＋ Événement</a>
            <a href="<?= BASE_URL ?>/index.php" target="_blank" class="btn btn-ghost">Voir le site ↗</a>
        </div>
    </section>

    <div class="chiffres">
        <?php foreach ($stats as [$icone, $nombre, $label, $lien]): ?>
        <a href="<?= $lien ?>" class="chiffre">
            <span class="icone"><?= $icone ?></span>
            <div><b><?= $nombre ?></b><span><?= $label ?></span></div>
        </a>
        <?php endforeach; ?>
    </div>

    <div class="colonnes">
        <div>
            <div class="card">
                <div class="titre-carte">
                    <h2>📅 Prochainement au club</h2>
                    <a href="evenements.php">Calendrier →</a>
                </div>
                <?php if (!$evenements): ?>
                    <p class="empty">Aucun événement à venir — <a href="evenements.php">en ajouter un</a></p>
                <?php endif; ?>
                <?php foreach ($evenements as $e):
                    $ts = strtotime($e['date_debut']);
                    $type = EVENT_TYPES[$e['type']] ?? EVENT_TYPES['autre']; ?>
                <a href="evenements.php?edit=<?= $e['id'] ?>" class="evt">
                    <div class="evt-date" style="background:<?= $type['couleur'] ?>">
                        <b><?= date('j', $ts) ?></b>
                        <small><?= MOIS_COURTS[date('n', $ts) - 1] ?></small>
                    </div>
                    <div class="evt-info">
                        <strong><?= htmlspecialchars($e['titre']) ?></strong>
                        <span><?= $type['label'] ?> · <?= quand_evenement($e) ?></span>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>

            <div class="card">
                <div class="titre-carte">
                    <h2>✉️ Derniers messages</h2>
                    <a href="messages.php">Tous les messages →</a>
                </div>
                <?php if (!$messages): ?>
                    <p class="empty">Aucun message pour l'instant</p>
                <?php endif; ?>
                <?php foreach ($messages as $m): ?>
                <a href="messages.php?voir=<?= $m['id'] ?>" class="msg <?= $m['lu'] ? '' : 'non-lu' ?>">
                    <span class="msg-avatar"><?= htmlspecialchars(mb_strtoupper(mb_substr($m['nom'], 0, 1))) ?></span>
                    <div class="msg-corps">
                        <div class="msg-ligne">
                            <strong><?= htmlspecialchars($m['nom']) ?></strong>
                            <time><?= il_y_a($m['created_at']) ?></time>
                        </div>
                        <div class="msg-extrait"><?= htmlspecialchars(sujet_label($m['sujet'])) ?> — <?= htmlspecialchars(mb_substr($m['message'], 0, 120)) ?></div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>

            <div class="card">
                <div class="titre-carte">
                    <h2>📰 Articles</h2>
                    <a href="articles.php">Gérer →</a>
                </div>
                <?php if (!$articles): ?>
                    <p class="empty">Aucun article</p>
                <?php endif; ?>
                <?php foreach ($articles as $a): ?>
                <a href="articles.php?edit=<?= $a['id'] ?>" class="ligne">
                    <span><?= htmlspecialchars($a['titre']) ?></span>
                    <?= article_badge($a) ?>
                </a>
                <?php endforeach; ?>
            </div>
        </div>

        <div>
            <div class="card" id="calendrier">
                <div class="calendrier-tete">
                    <a href="?mois=<?= $moisPrecedent ?>#calendrier" aria-label="Mois précédent">‹</a>
                    <h2>📆 <?= MOIS_FR[date('m', $premier)] ?> <?= date('Y', $premier) ?></h2>
                    <a href="?mois=<?= $moisSuivant ?>#calendrier" aria-label="Mois suivant">›</a>
                </div>
                <div class="calendrier">
                    <?php foreach (['L', 'M', 'M', 'J', 'V', 'S', 'D'] as $j): ?><b><?= $j ?></b><?php endforeach; ?>
                    <?php for ($i = 1; $i < (int)date('N', $premier); $i++): ?><span></span><?php endfor; ?>
                    <?php for ($j = 1; $j <= $nbJours; $j++):
                        $date = sprintf('%s-%02d', $mois, $j);
                        $evts = $joursCalendrier[$j] ?? [];
                        $vacances = array_filter($evts, fn($e) => $e['type'] === 'vacances');
                        $autres   = array_filter($evts, fn($e) => $e['type'] !== 'vacances');
                        $ferie    = $feriesMois[$date] ?? null;
                        $classes  = trim(($date === $aujourdhui ? 'auj ' : '') . ($date < $aujourdhui ? 'passe ' : '') . ($vacances ? 'vacances ' : '') . ($ferie ? 'ferie' : ''));
                        $titre    = implode("\n", array_merge($ferie ? ["Férié : $ferie"] : [], array_map(fn($e) => $e['titre'], $evts))); ?>
                    <a href="evenements.php" class="<?= $classes ?>" <?= $titre !== '' ? 'title="' . htmlspecialchars($titre) . '"' : '' ?>>
                        <?= $j ?>
                        <?php if ($autres): ?>
                        <i><?php foreach (array_slice($autres, 0, 3) as $e): ?><em style="background:<?= (EVENT_TYPES[$e['type']] ?? EVENT_TYPES['autre'])['couleur'] ?>"></em><?php endforeach; ?></i>
                        <?php endif; ?>
                    </a>
                    <?php endfor; ?>
                </div>
                <?php if ($lignesMois): ?>
                <ul class="calendrier-liste">
                    <?php foreach ($lignesMois as $ligne): ?>
                    <li><?= $ligne['html'] ?></li>
                    <?php endforeach; ?>
                </ul>
                <?php else: ?>
                <p class="calendrier-vide">Aucun événement ce mois-ci</p>
                <?php endif; ?>
            </div>

            <?php if ($activite): ?>
            <div class="card">
                <div class="titre-carte">
                    <h2>📜 Dernières actions</h2>
                    <a href="journal.php">Journal →</a>
                </div>
                <?php foreach ($activite as $j): ?>
                <div class="action-journal">
                    <?= htmlspecialchars($j['description']) ?>
                    <small><?= htmlspecialchars($j['admin_nom']) ?> · <?= il_y_a($j['created_at']) ?></small>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
<?php admin_footer(); ?>
