<?php
// Espace Jeux : scores de chaque jeu, suppression, remise à zéro. Réservé au fondateur.
require_once 'auth.php';
require_once __DIR__ . '/../src/jeux.php';
exiger_fondateur();

// Jeu affiché (un onglet par jeu de la liste JEUX)
$jeu  = jeu_info($_GET['jeu'] ?? '') ? $_GET['jeu'] : array_key_first(JEUX);
$info = JEUX[$jeu];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'supprimer') {
        $stmt = $pdo->prepare("SELECT pseudo, score FROM jeux_scores WHERE id = ? AND jeu = ?");
        $stmt->execute([(int)($_POST['id'] ?? 0), $jeu]);
        if ($s = $stmt->fetch()) {
            $pdo->prepare("DELETE FROM jeux_scores WHERE id = ?")->execute([(int)$_POST['id']]);
            journaliser('jeu', "Jeu « {$info['titre']} » : suppression du score de « {$s['pseudo']} » ({$s['score']} {$info['unite']})");
        }
        header("Location: jeux.php?jeu=$jeu&ok=supprime");
        exit;
    }
    if ($action === 'remise_a_zero') {
        $stmt = $pdo->prepare("DELETE FROM jeux_scores WHERE jeu = ?");
        $stmt->execute([$jeu]);
        journaliser('jeu', "Jeu « {$info['titre']} » : remise à zéro du tableau des scores ({$stmt->rowCount()} scores supprimés)");
        header("Location: jeux.php?jeu=$jeu&ok=zero");
        exit;
    }
}
$message = ['supprime' => 'Score supprimé.', 'zero' => 'Tableau des scores remis à zéro.'][$_GET['ok'] ?? ''] ?? '';

// Chiffres du jeu
$stmt = $pdo->prepare("SELECT COUNT(*) parties, COUNT(DISTINCT LOWER(pseudo)) joueurs, COALESCE(MAX(score), 0) meilleur,
                              COALESCE(SUM(created_at >= CURDATE()), 0) aujourdhui FROM jeux_scores WHERE jeu = ?");
$stmt->execute([$jeu]);
$stats = $stmt->fetch();
$classement = jeu_classement($jeu);

// Tous les scores du jeu, du plus récent au plus ancien
$parPage = 50;
$total   = (int)$stats['parties'];
$nbPages = max(1, (int)ceil($total / $parPage));
$page    = min(max(1, (int)($_GET['page'] ?? 1)), $nbPages);
$stmt = $pdo->prepare("SELECT * FROM jeux_scores WHERE jeu = ? ORDER BY created_at DESC, id DESC LIMIT $parPage OFFSET " . (($page - 1) * $parPage));
$stmt->execute([$jeu]);
$scores = $stmt->fetchAll();

admin_header('Jeux', 'jeux', '<style>
    .onglets { display: flex; flex-wrap: wrap; gap: 4px; margin-bottom: 20px; border-bottom: 2px solid #e3e8e4; }
    .onglets a { padding: 10px 16px; text-decoration: none; color: #666; font-weight: 600; font-size: 0.92rem; border-bottom: 3px solid transparent; margin-bottom: -2px; border-radius: 6px 6px 0 0; }
    .onglets a:hover { color: var(--vert); background: #fff; }
    .onglets a.actif { color: var(--vert); border-bottom-color: var(--jaune); background: #fff; }
    .jeu-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 24px; }
    .jeu-stat { background: #fff; border-radius: 8px; padding: 16px; box-shadow: 0 1px 4px rgba(0,0,0,0.08); }
    .jeu-stat b { display: block; font-size: 1.5rem; color: var(--vert); }
    .jeu-stat span { font-size: 0.8rem; color: #777; }
    .colonnes-jeu { display: grid; grid-template-columns: 1fr 2fr; gap: 20px; align-items: start; }
    .top li { display: flex; gap: 10px; padding: 6px 0; border-bottom: 1px solid #eee; font-size: 0.9rem; }
    .top li:last-child { border-bottom: none; }
    .top .rang { width: 26px; color: #888; font-weight: 700; }
    .top .pseudo { flex: 1; }
    .top .points { font-weight: 700; color: var(--vert); }
    .pagination-admin { display: flex; justify-content: space-between; align-items: center; margin-top: 16px; font-size: 0.88rem; }
    .zone-danger { border: 1px solid #f3c9c4; }
    @media (max-width: 1000px) { .jeu-stats { grid-template-columns: 1fr 1fr; } .colonnes-jeu { grid-template-columns: 1fr; } }
</style>');
?>
    <h1>🕹️ Jeux</h1>
    <p class="subtitle">Scores des jeux de l'<a href="<?= BASE_URL ?>/jeux.php" target="_blank">espace Jeux ↗</a>. Visible uniquement par le fondateur.
        Pour ajouter un jeu, voir la liste <code>JEUX</code> dans <code>src/jeux.php</code>.</p>

    <nav class="onglets">
        <?php foreach (JEUX as $id => $j): ?>
        <a href="jeux.php?jeu=<?= $id ?>" class="<?= $id === $jeu ? 'actif' : '' ?>"><?= $j['icone'] ?> <?= htmlspecialchars($j['titre']) ?></a>
        <?php endforeach; ?>
    </nav>

    <?php if ($message): ?><div class="alert alert-success">✅ <?= $message ?></div><?php endif; ?>

    <div class="jeu-stats">
        <div class="jeu-stat"><b><?= $stats['parties'] ?></b><span>scores enregistrés</span></div>
        <div class="jeu-stat"><b><?= $stats['joueurs'] ?></b><span>joueurs différents</span></div>
        <div class="jeu-stat"><b><?= $stats['meilleur'] ?></b><span>meilleur score (<?= $info['unite'] ?>)</span></div>
        <div class="jeu-stat"><b><?= (int)$stats['aujourdhui'] ?></b><span>scores aujourd'hui</span></div>
    </div>

    <div class="colonnes-jeu">
        <div class="card">
            <h2>🏆 Top <?= JEU_TOP ?> affiché sur le site</h2>
            <?php if (!$classement): ?>
                <p class="empty">Aucun score pour l'instant</p>
            <?php else: ?>
                <ol class="top" style="list-style:none">
                    <?php foreach ($classement as $i => $l): ?>
                    <li><span class="rang"><?= ['🥇', '🥈', '🥉'][$i] ?? $i + 1 ?></span><span class="pseudo"><?= htmlspecialchars($l['pseudo']) ?></span><span class="points"><?= $l['score'] ?></span></li>
                    <?php endforeach; ?>
                </ol>
            <?php endif; ?>
        </div>

        <div class="card">
            <h2>Tous les scores (<?= $total ?>)</h2>
            <?php if (!$scores): ?>
                <p class="empty">Aucun score pour l'instant</p>
            <?php else: ?>
            <div class="table-wrap">
            <table>
                <thead><tr><th>Date</th><th>Pseudo</th><th>Score</th><th>Durée</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($scores as $s): ?>
                    <tr>
                        <td style="white-space:nowrap"><?= date('d/m/Y H:i', strtotime($s['created_at'])) ?></td>
                        <td><?= htmlspecialchars($s['pseudo']) ?></td>
                        <td><strong><?= $s['score'] ?></strong></td>
                        <td><?= floor($s['duree'] / 60) ?> min <?= sprintf('%02d', $s['duree'] % 60) ?> s</td>
                        <td>
                            <form method="POST" action="jeux.php?jeu=<?= $jeu ?>" onsubmit="return confirm('Supprimer ce score ?')">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="supprimer">
                                <input type="hidden" name="id" value="<?= $s['id'] ?>">
                                <button type="submit" class="btn btn-danger btn-sm" title="Supprimer (pseudo insultant, score suspect…)">🗑️</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            </div>
            <?php if ($nbPages > 1): ?>
            <div class="pagination-admin">
                <span><?php if ($page > 1): ?><a href="jeux.php?jeu=<?= $jeu ?>&page=<?= $page - 1 ?>">← Plus récents</a><?php endif; ?></span>
                <span>Page <?= $page ?> / <?= $nbPages ?></span>
                <span><?php if ($page < $nbPages): ?><a href="jeux.php?jeu=<?= $jeu ?>&page=<?= $page + 1 ?>">Plus anciens →</a><?php endif; ?></span>
            </div>
            <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($total > 0): ?>
    <div class="card zone-danger">
        <h2>Remettre le tableau de ce jeu à zéro</h2>
        <p class="form-hint" style="margin:-8px 0 14px">Supprime tous les scores de « <?= htmlspecialchars($info['titre']) ?> » (par exemple au début d'une nouvelle saison). Action définitive.</p>
        <form method="POST" action="jeux.php?jeu=<?= $jeu ?>" onsubmit="return confirm('Supprimer définitivement les <?= $total ?> scores de ce jeu ?')">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="remise_a_zero">
            <button type="submit" class="btn btn-danger">🗑️ Tout supprimer</button>
        </form>
    </div>
    <?php endif; ?>
<?php admin_footer(); ?>
