<?php
require_once 'auth.php';

$message = '';
$erreur  = '';

function enregistrer_param($cle, $valeur) {
    global $pdo;
    $pdo->prepare("INSERT INTO parametres (cle, valeur) VALUES (?,?) ON DUPLICATE KEY UPDATE valeur=?")
        ->execute([$cle, $valeur, $valeur]);
}

// Fiche d'inscription PDF envoyée avec une formule (champ facultatif "pdf").
// Renvoie [nom du fichier enregistré | null si aucun envoi, message d'erreur | ''].
function recevoir_pdf_formule() {
    $f = $_FILES['pdf'] ?? null;
    if (!$f || $f['error'] === UPLOAD_ERR_NO_FILE) return [null, ''];
    if ($f['error'] !== UPLOAD_ERR_OK)  return [null, 'Le PDF n\'a pas pu être reçu (fichier trop lourd pour le serveur ?).'];
    if ($f['size'] > PDF_MAX_SIZE)       return [null, 'Le PDF dépasse 10 Mo.'];
    // Vérifie le vrai contenu du fichier, pas seulement son nom
    if (file_get_contents($f['tmp_name'], false, null, 0, 5) !== '%PDF-') return [null, 'Le fichier envoyé n\'est pas un PDF valide.'];
    if (!is_dir(UPLOAD_DOCUMENTS)) mkdir(UPLOAD_DOCUMENTS, 0755, true);
    $filename = uniqid('tarif-') . '.pdf';
    if (!move_uploaded_file($f['tmp_name'], UPLOAD_DOCUMENTS . $filename)) return [null, 'Impossible d\'enregistrer le PDF.'];
    return [$filename, ''];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);

    if ($action === 'create' || $action === 'edit') {
        // Le PDF est vérifié en premier : s'il est refusé, rien n'est enregistré
        [$nouveauPdf, $erreur] = recevoir_pdf_formule();
        // Formule supprimée entre-temps : on ne garde pas le PDF qui vient d'être envoyé
        if (!$erreur && $action === 'edit') {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM tarifs WHERE id=?");
            $stmt->execute([$id]);
            if (!$stmt->fetchColumn()) {
                if ($nouveauPdf) delete_upload(UPLOAD_DOCUMENTS, $nouveauPdf);
                $erreur = 'Cette formule n\'existe plus.';
            }
        }
    }

    if (($action === 'create' || $action === 'edit') && !$erreur) {
        $champs = [
            trim($_POST['nom']),
            trim($_POST['public_cible']),
            trim($_POST['prix']),
            trim($_POST['periode']),
            trim($_POST['avantages']),
            isset($_POST['mis_en_avant']) ? 1 : 0,
        ];
        $resume = "« {$champs[0]} » ({$champs[2]} {$champs[3]})";
        if ($action === 'create') {
            $ordre = (int)$pdo->query("SELECT COALESCE(MAX(ordre), 0) + 1 FROM tarifs")->fetchColumn();
            $pdo->prepare("INSERT INTO tarifs (nom, public_cible, prix, periode, avantages, mis_en_avant, pdf, ordre) VALUES (?,?,?,?,?,?,?,?)")
                ->execute([...$champs, $nouveauPdf, $ordre]);
            journaliser('tarif', "Ajout de la formule $resume" . ($nouveauPdf ? ' avec sa fiche PDF' : ''));
        } else {
            // PDF actuel : lu en base. Remplacé par le nouveau, ou retiré si la case est cochée.
            $stmt = $pdo->prepare("SELECT pdf FROM tarifs WHERE id=?");
            $stmt->execute([$id]);
            $pdf = $stmt->fetchColumn() ?: null;
            $notePdf = '';
            if ($pdf && ($nouveauPdf || isset($_POST['supprimer_pdf']))) {
                delete_upload(UPLOAD_DOCUMENTS, $pdf);
                $notePdf = $nouveauPdf ? ', fiche PDF remplacée' : ', fiche PDF retirée';
                $pdf = null;
            } elseif ($nouveauPdf) {
                $notePdf = ', fiche PDF ajoutée';
            }
            $pdf = $nouveauPdf ?? $pdf;
            $pdo->prepare("UPDATE tarifs SET nom=?, public_cible=?, prix=?, periode=?, avantages=?, mis_en_avant=?, pdf=? WHERE id=?")
                ->execute([...$champs, $pdf, $id]);
            journaliser('tarif', "Modification de la formule $resume$notePdf");
        }
    }

    if ($action === 'move') {
        deplacer_ordre('tarifs', $id, $_POST['sens'] ?? '');
    }

    if ($action === 'delete') {
        $stmt = $pdo->prepare("SELECT nom, prix, periode, pdf FROM tarifs WHERE id=?");
        $stmt->execute([$id]);
        $formule = $stmt->fetch();
        if (!empty($formule['pdf'])) delete_upload(UPLOAD_DOCUMENTS, $formule['pdf']);
        $pdo->prepare("DELETE FROM tarifs WHERE id=?")->execute([$id]);
        if ($formule) journaliser('tarif', "Suppression de la formule « {$formule['nom']} » ({$formule['prix']} {$formule['periode']})");
    }

    if ($action === 'saison') {
        enregistrer_param('saison', trim($_POST['saison']));
        journaliser('tarif', 'Saison affichée : ' . trim($_POST['saison']));
    }

    if (!$erreur) {
        header('Location: tarifs.php');
        exit;
    }
}

$tarifs = $pdo->query("SELECT * FROM tarifs ORDER BY ordre, id")->fetchAll();
$edit = null;
$idEdit = (int)($_GET['edit'] ?? ($erreur && ($_POST['action'] ?? '') === 'edit' ? $_POST['id'] : 0));
if ($idEdit) {
    $stmt = $pdo->prepare("SELECT * FROM tarifs WHERE id=?");
    $stmt->execute([$idEdit]);
    $edit = $stmt->fetch();
}
// Après une erreur (PDF refusé), on réaffiche ce qui avait été saisi
if ($erreur) {
    $edit = array_merge($edit ?: [], array_intersect_key($_POST, array_flip(['nom', 'public_cible', 'prix', 'periode', 'avantages'])),
                        ['mis_en_avant' => isset($_POST['mis_en_avant']) ? 1 : 0]);
}
$modeEdition = !empty($edit['id']);   // sans id : formulaire d'ajout (éventuellement pré-rempli après une erreur)

admin_header('Tarifs', 'tarifs', '<style>
    .fiche-actuelle { display: flex; flex-wrap: wrap; align-items: center; gap: 10px 16px; background: #f6faf7; border: 1px solid var(--vert-light); border-radius: 6px; padding: 10px 12px; margin-bottom: 10px; font-size: 0.88rem; }
    .fiche-actuelle a { color: var(--vert); font-weight: 600; }
    .avantages { font-size: 0.8rem; color: #888; }
    .lien-pdf { color: var(--vert); font-weight: 600; text-decoration: none; white-space: nowrap; }
</style>');
?>
    <h1>💶 Tarifs & inscription</h1>
    <?php if ($erreur): ?><div class="alert alert-error">❌ <?= htmlspecialchars($erreur) ?> Rien n'a été enregistré.</div><?php endif; ?>

    <!-- Saison -->
    <div class="card">
        <h2>📅 Saison affichée</h2>
        <form method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="saison">
            <div class="form-group">
                <label>Saison</label>
                <input type="text" name="saison" value="<?= htmlspecialchars(param('saison')) ?>" placeholder="2026-2027" style="max-width:260px">
                <div class="form-hint">Titre de la section sur le site : « Tarifs saison <?= htmlspecialchars(param('saison', '…')) ?> ».</div>
            </div>
            <button type="submit" class="btn btn-primary">💾 Enregistrer</button>
        </form>
    </div>

    <!-- Formulaire formule -->
    <div class="card">
        <h2><?= $modeEdition ? 'Modifier la formule' : 'Ajouter une formule' ?></h2>
        <form method="POST" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="<?= $modeEdition ? 'edit' : 'create' ?>">
            <?php if ($modeEdition): ?><input type="hidden" name="id" value="<?= $edit['id'] ?>"><?php endif; ?>
            <div class="form-row">
                <div class="form-group">
                    <label>Nom de la formule</label>
                    <input type="text" name="nom" value="<?= htmlspecialchars($edit['nom'] ?? '') ?>" placeholder="ex : Adulte" required>
                </div>
                <div class="form-group">
                    <label>Pour qui ?</label>
                    <input type="text" name="public_cible" value="<?= htmlspecialchars($edit['public_cible'] ?? '') ?>" placeholder="ex : 18 ans et plus">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Prix</label>
                    <input type="text" name="prix" value="<?= htmlspecialchars($edit['prix'] ?? '') ?>" placeholder="ex : 250 €" required>
                </div>
                <div class="form-group">
                    <label>Période</label>
                    <input type="text" name="periode" value="<?= htmlspecialchars($edit['periode'] ?? '/an') ?>" placeholder="ex : /an, /trimestre">
                </div>
            </div>
            <div class="form-group">
                <label>Points importants (un par ligne)</label>
                <textarea name="avantages" style="height:110px" placeholder="Cotisation FFT incluse&#10;Accès aux cours collectifs"><?= htmlspecialchars($edit['avantages'] ?? '') ?></textarea>
            </div>
            <div class="form-group">
                <label class="form-check">
                    <input type="checkbox" name="mis_en_avant" <?= ($edit['mis_en_avant'] ?? 0) ? 'checked' : '' ?>>
                    Mettre en avant (carte jaune « Populaire »)
                </label>
            </div>
            <div class="form-group">
                <label>Fiche d'inscription de cette formule (PDF, optionnel, max 10 Mo)</label>
                <?php if (!empty($edit['pdf'])): ?>
                    <div class="fiche-actuelle">
                        ✅ Fiche actuelle : <a href="../public/assets/uploads/documents/<?= htmlspecialchars($edit['pdf']) ?>" target="_blank">Voir le PDF</a>
                        <label class="form-check" style="font-weight:400"><input type="checkbox" name="supprimer_pdf"> Retirer ce PDF</label>
                    </div>
                <?php endif; ?>
                <input type="file" name="pdf" accept="application/pdf,.pdf">
                <div class="form-hint">
                    <?= !empty($edit['pdf']) ? 'Choisir un fichier remplace la fiche actuelle.' : 'Sans PDF, la carte de cette formule n\'affiche pas de bouton « Voir la fiche d\'inscription ».' ?>
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><?= $modeEdition ? 'Mettre à jour' : 'Ajouter' ?></button>
                <?php if ($modeEdition): ?><a href="tarifs.php" class="link-cancel">Annuler</a><?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Liste -->
    <div class="card">
        <h2>Formules (<?= count($tarifs) ?>) — dans l'ordre d'affichage</h2>
        <?php if (empty($tarifs)): ?>
            <p class="empty">Aucune formule. La section tarifs n'apparaît pas sur le site.</p>
        <?php else: ?>
        <div class="table-wrap">
        <table>
            <thead><tr><th>Formule</th><th>Prix</th><th>Points importants</th><th>Fiche PDF</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($tarifs as $i => $t): ?>
            <tr>
                <td>
                    <strong><?= htmlspecialchars($t['nom']) ?></strong>
                    <?php if ($t['mis_en_avant']): ?><span class="badge badge-yellow">★ Mise en avant</span><?php endif; ?>
                    <div class="avantages"><?= htmlspecialchars($t['public_cible'] ?? '') ?></div>
                </td>
                <td style="white-space:nowrap"><?= htmlspecialchars($t['prix'] . ' ' . $t['periode']) ?></td>
                <td class="avantages"><?= nl2br(htmlspecialchars($t['avantages'] ?? '')) ?></td>
                <td>
                    <?php if (!empty($t['pdf'])): ?>
                        <a href="../public/assets/uploads/documents/<?= htmlspecialchars($t['pdf']) ?>" target="_blank" class="lien-pdf">📄 Voir</a>
                    <?php else: ?>
                        <span class="avantages">—</span>
                    <?php endif; ?>
                </td>
                <td>
                    <div class="actions">
                        <?php boutons_ordre($t['id'], $i, count($tarifs)); ?>
                        <a href="tarifs.php?edit=<?= $t['id'] ?>" class="btn btn-secondary btn-sm">✏️ Modifier</a>
                        <form method="POST" onsubmit="return confirm('Supprimer cette formule ?')">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $t['id'] ?>">
                            <button type="submit" class="btn btn-danger btn-sm">🗑️</button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
        <?php endif; ?>
    </div>
<?php admin_footer(); ?>
