<?php
// Journal des actions importantes : réservé au fondateur, en lecture seule.
require_once 'auth.php';
exiger_fondateur();

$parPage   = 50;
$categorie = isset(JOURNAL_CATEGORIES[$_GET['categorie'] ?? '']) ? $_GET['categorie'] : '';
$compte    = trim($_GET['compte'] ?? '');

// Filtres (catégorie, compte) — toujours en requête préparée
$where  = [];
$valeurs = [];
if ($categorie !== '') { $where[] = 'categorie = ?'; $valeurs[] = $categorie; }
if ($compte !== '')    { $where[] = 'admin_nom = ?'; $valeurs[] = $compte; }
$sqlWhere = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$stmt = $pdo->prepare("SELECT COUNT(*) FROM journal $sqlWhere");
$stmt->execute($valeurs);
$total   = (int)$stmt->fetchColumn();
$nbPages = max(1, (int)ceil($total / $parPage));
$page    = min(max(1, (int)($_GET['page'] ?? 1)), $nbPages);

$stmt = $pdo->prepare("SELECT * FROM journal $sqlWhere ORDER BY created_at DESC, id DESC
                       LIMIT $parPage OFFSET " . (($page - 1) * $parPage));
$stmt->execute($valeurs);
$entrees = $stmt->fetchAll();

$comptes = $pdo->query("SELECT DISTINCT admin_nom FROM journal WHERE admin_nom <> '' ORDER BY admin_nom")->fetchAll(PDO::FETCH_COLUMN);

// Garde les filtres actifs dans les liens de pagination
function lien_journal($page) {
    global $categorie, $compte;
    return 'journal.php?' . http_build_query(array_filter(['categorie' => $categorie, 'compte' => $compte, 'page' => $page > 1 ? $page : null]));
}

admin_header('Journal', 'journal', '<style>
    .filtres { display: flex; flex-wrap: wrap; gap: 10px; align-items: flex-end; }
    .filtres .form-group { margin-bottom: 0; min-width: 180px; }
    .journal-date { white-space: nowrap; color: #666; font-size: 0.82rem; }
    .journal-cat { white-space: nowrap; font-size: 0.82rem; }
    .journal-ip { font-family: monospace; font-size: 0.78rem; color: #999; }
    .journal-echec td { background: #fff6f6; }
    .pagination-admin { display: flex; justify-content: space-between; align-items: center; margin-top: 16px; font-size: 0.88rem; }
</style>');
?>
    <h1>📜 Journal</h1>
    <p class="subtitle">Actions importantes réalisées dans l'admin — visible uniquement par le fondateur.
       Les entrées de plus de <?= JOURNAL_CONSERVATION_MOIS ?> mois sont supprimées automatiquement.</p>

    <div class="card">
        <form method="GET" class="filtres">
            <div class="form-group">
                <label>Type d'action</label>
                <select name="categorie">
                    <option value="">Toutes</option>
                    <?php foreach (JOURNAL_CATEGORIES as $cle => [$icone, $libelle]): ?>
                    <option value="<?= $cle ?>" <?= $categorie === $cle ? 'selected' : '' ?>><?= $icone ?> <?= $libelle ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Compte</label>
                <select name="compte">
                    <option value="">Tous</option>
                    <?php foreach ($comptes as $nom): ?>
                    <option value="<?= htmlspecialchars($nom) ?>" <?= $compte === $nom ? 'selected' : '' ?>><?= htmlspecialchars($nom) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Filtrer</button>
            <?php if ($categorie || $compte): ?><a href="journal.php" class="link-cancel">Réinitialiser</a><?php endif; ?>
        </form>
    </div>

    <div class="card">
        <h2><?= $total ?> action<?= $total > 1 ? 's' : '' ?><?= $categorie || $compte ? ' (filtrées)' : '' ?></h2>
        <?php if (empty($entrees)): ?>
            <p class="empty">Aucune action enregistrée<?= $categorie || $compte ? ' pour ces filtres' : ' pour le moment' ?>.</p>
        <?php else: ?>
        <div class="table-wrap">
        <table>
            <thead><tr><th>Date</th><th>Compte</th><th>Type</th><th>Action</th><th>Adresse IP</th></tr></thead>
            <tbody>
            <?php foreach ($entrees as $e): [$icone, $libelle] = JOURNAL_CATEGORIES[$e['categorie']] ?? ['•', $e['categorie']]; ?>
            <tr class="<?= $e['admin_id'] === null ? 'journal-echec' : '' ?>">
                <td class="journal-date"><?= date('d/m/Y H:i', strtotime($e['created_at'])) ?></td>
                <td><?= htmlspecialchars($e['admin_nom'] ?: '—') ?></td>
                <td class="journal-cat"><?= $icone ?> <?= $libelle ?></td>
                <td><?= htmlspecialchars($e['description']) ?></td>
                <td class="journal-ip"><?= htmlspecialchars($e['ip']) ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
        <?php if ($nbPages > 1): ?>
        <div class="pagination-admin">
            <?= $page > 1 ? '<a href="' . htmlspecialchars(lien_journal($page - 1)) . '" class="btn btn-secondary btn-sm">← Plus récentes</a>' : '<span></span>' ?>
            <span>Page <?= $page ?> / <?= $nbPages ?></span>
            <?= $page < $nbPages ? '<a href="' . htmlspecialchars(lien_journal($page + 1)) . '" class="btn btn-secondary btn-sm">Plus anciennes →</a>' : '<span></span>' ?>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>
<?php admin_footer(); ?>
