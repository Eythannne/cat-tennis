<?php
require_once 'auth.php';

$message = '';
$erreur  = '';

function enregistrer_param($cle, $valeur) {
    global $pdo;
    $pdo->prepare("INSERT INTO parametres (cle, valeur) VALUES (?,?) ON DUPLICATE KEY UPDATE valeur=?")
        ->execute([$cle, $valeur, $valeur]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);

    if ($action === 'create' || $action === 'edit') {
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
            $pdo->prepare("INSERT INTO tarifs (nom, public_cible, prix, periode, avantages, mis_en_avant, ordre) VALUES (?,?,?,?,?,?,?)")
                ->execute([...$champs, $ordre]);
            journaliser('tarif', "Ajout de la formule $resume");
        } else {
            $pdo->prepare("UPDATE tarifs SET nom=?, public_cible=?, prix=?, periode=?, avantages=?, mis_en_avant=? WHERE id=?")
                ->execute([...$champs, $id]);
            journaliser('tarif', "Modification de la formule $resume");
        }
    }

    if ($action === 'move') {
        deplacer_ordre('tarifs', $id, $_POST['sens'] ?? '');
    }

    if ($action === 'delete') {
        $stmt = $pdo->prepare("SELECT nom, prix, periode FROM tarifs WHERE id=?");
        $stmt->execute([$id]);
        $formule = $stmt->fetch();
        $pdo->prepare("DELETE FROM tarifs WHERE id=?")->execute([$id]);
        if ($formule) journaliser('tarif', "Suppression de la formule « {$formule['nom']} » ({$formule['prix']} {$formule['periode']})");
    }

    if ($action === 'saison') {
        enregistrer_param('saison', trim($_POST['saison']));
        journaliser('tarif', 'Saison affichée : ' . trim($_POST['saison']));
    }

    // Fiche d'inscription PDF
    if ($action === 'fiche') {
        $f = $_FILES['fiche'] ?? null;
        if (!$f || $f['error'] !== UPLOAD_ERR_OK) {
            $erreur = 'Aucun fichier reçu (ou fichier trop lourd pour le serveur).';
        } elseif ($f['size'] > PDF_MAX_SIZE) {
            $erreur = 'Le PDF dépasse 10 Mo.';
        } elseif (file_get_contents($f['tmp_name'], false, null, 0, 5) !== '%PDF-') {
            // Vérifie le vrai contenu du fichier, pas seulement son nom
            $erreur = 'Le fichier n\'est pas un PDF valide.';
        } else {
            if (!is_dir(UPLOAD_DOCUMENTS)) mkdir(UPLOAD_DOCUMENTS, 0755, true);
            $filename = uniqid('fiche-inscription-') . '.pdf';
            if (move_uploaded_file($f['tmp_name'], UPLOAD_DOCUMENTS . $filename)) {
                delete_upload(UPLOAD_DOCUMENTS, param('fiche_inscription'));
                enregistrer_param('fiche_inscription', $filename);
                journaliser('tarif', 'Nouvelle fiche d\'inscription PDF envoyée (« ' . mb_substr($f['name'], 0, 80) . ' »)');
            } else {
                $erreur = 'Impossible d\'enregistrer le fichier.';
            }
        }
    }
    if ($action === 'fiche_supprimer') {
        delete_upload(UPLOAD_DOCUMENTS, param('fiche_inscription'));
        enregistrer_param('fiche_inscription', '');
        journaliser('tarif', 'Fiche d\'inscription PDF retirée du site');
    }

    if (!$erreur) {
        header('Location: tarifs.php');
        exit;
    }
}

$tarifs = $pdo->query("SELECT * FROM tarifs ORDER BY ordre, id")->fetchAll();
$fiche  = param('fiche_inscription');
$edit = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM tarifs WHERE id=?");
    $stmt->execute([(int)$_GET['edit']]);
    $edit = $stmt->fetch();
}

admin_header('Tarifs', 'tarifs', '<style>
    .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }
    .fiche-actuelle { display: flex; flex-wrap: wrap; align-items: center; gap: 10px; background: #f6faf7; border: 1px solid var(--vert-light); border-radius: 6px; padding: 10px 12px; margin-bottom: 14px; font-size: 0.88rem; }
    .fiche-actuelle a { color: var(--vert); font-weight: 600; }
    .avantages { font-size: 0.8rem; color: #888; }
    @media (max-width: 1000px) { .grid-2 { grid-template-columns: 1fr; } }
</style>');
?>
    <h1>💶 Tarifs & inscription</h1>
    <?php if ($erreur): ?><div class="alert alert-error">❌ <?= htmlspecialchars($erreur) ?></div><?php endif; ?>

    <div class="grid-2">
        <!-- Fiche d'inscription -->
        <div class="card">
            <h2>📄 Fiche d'inscription (PDF)</h2>
            <?php if ($fiche): ?>
                <div class="fiche-actuelle">
                    ✅ Fiche en ligne :
                    <a href="../public/assets/uploads/documents/<?= htmlspecialchars($fiche) ?>" target="_blank">Voir le PDF</a>
                    <form method="POST" onsubmit="return confirm('Retirer la fiche du site ?')" style="margin-left:auto">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="fiche_supprimer">
                        <button type="submit" class="btn btn-danger btn-sm">🗑️ Retirer</button>
                    </form>
                </div>
            <?php else: ?>
                <p class="form-hint" style="margin-bottom:14px">Aucune fiche : le bouton « Fiche d'inscription » n'apparaît pas sur le site.</p>
            <?php endif; ?>
            <form method="POST" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="fiche">
                <div class="form-group">
                    <label><?= $fiche ? 'Remplacer par une nouvelle fiche' : 'Envoyer la fiche' ?> (PDF, max 10 Mo)</label>
                    <input type="file" name="fiche" accept="application/pdf,.pdf" required>
                </div>
                <button type="submit" class="btn btn-primary">📤 Envoyer</button>
            </form>
        </div>

        <!-- Saison -->
        <div class="card">
            <h2>📅 Saison affichée</h2>
            <form method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="saison">
                <div class="form-group">
                    <label>Saison</label>
                    <input type="text" name="saison" value="<?= htmlspecialchars(param('saison')) ?>" placeholder="2026-2027">
                    <div class="form-hint">Titre de la section sur le site : « Tarifs saison <?= htmlspecialchars(param('saison', '…')) ?> ».</div>
                </div>
                <button type="submit" class="btn btn-primary">💾 Enregistrer</button>
            </form>
        </div>
    </div>

    <!-- Formulaire formule -->
    <div class="card">
        <h2><?= $edit ? 'Modifier la formule' : 'Ajouter une formule' ?></h2>
        <form method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="<?= $edit ? 'edit' : 'create' ?>">
            <?php if ($edit): ?><input type="hidden" name="id" value="<?= $edit['id'] ?>"><?php endif; ?>
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
                    Mettre en avant (carte verte, un peu plus grande)
                </label>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><?= $edit ? 'Mettre à jour' : 'Ajouter' ?></button>
                <?php if ($edit): ?><a href="tarifs.php" class="link-cancel">Annuler</a><?php endif; ?>
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
            <thead><tr><th>Formule</th><th>Prix</th><th>Points importants</th><th>Actions</th></tr></thead>
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
