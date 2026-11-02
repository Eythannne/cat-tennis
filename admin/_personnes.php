<?php
// Page admin commune pour gérer une liste de personnes affichées sur la page « Le club »
// (entraîneurs, bureau...) : ajout, modification, photo recadrée, ordre, suppression.
// Utilisée par entraineurs.php et bureau.php avec une configuration différente.
//
// Clés de $c :
//   table, page (nom du fichier sans .php), titre, icone,
//   dossier_upload (chemin disque), dossier_url (sous-dossier de assets/uploads/), prefixe_photo,
//   journal (catégorie du journal), personne (ex : "l'entraîneur"), de_personne (ex : "de l'entraîneur"),
//   nouveau (ex : "Ajouter un entraîneur"),
//   label_role, exemple_role, exemple_description, suggestions_role (liste optionnelle),
//   exemple_legende (légende proposée pour la photo de groupe)

// Photo de groupe de la section (paramètres "groupe_<table>_photo" et "groupe_<table>_legende")
function cles_photo_groupe($table) {
    return ["groupe_{$table}_photo", "groupe_{$table}_legende"];
}

function ecrire_parametre($cle, $valeur) {
    global $pdo;
    $pdo->prepare("INSERT INTO parametres (cle, valeur) VALUES (?,?) ON DUPLICATE KEY UPDATE valeur=?")
        ->execute([$cle, $valeur, $valeur]);
}

// Reçoit la photo de groupe envoyée (champ "photo_groupe"), la redresse et l'allège.
// Renvoie [nom du fichier | null si aucun envoi, message d'erreur | ''].
function recevoir_photo_groupe($dossier) {
    $f = $_FILES['photo_groupe'] ?? null;
    if (!$f || $f['error'] === UPLOAD_ERR_NO_FILE) return [null, ''];
    if ($f['error'] !== UPLOAD_ERR_OK || $f['size'] > GALERIE_MAX_SIZE) return [null, 'La photo est trop lourde (10 Mo maximum).'];
    // Extension déduite du vrai contenu du fichier, pas de son nom
    $ext = image_extension(file_get_contents($f['tmp_name']), GALERIE_MAX_SIZE);
    if (!$ext) return [null, 'Format non reconnu : envoyez une photo JPG, PNG ou WEBP.'];
    if (!is_dir($dossier)) mkdir($dossier, 0755, true);
    $filename = uniqid('groupe_') . '.' . $ext;
    if (!move_uploaded_file($f['tmp_name'], $dossier . $filename)) return [null, 'Impossible d\'enregistrer la photo.'];
    optimiser_image($dossier . $filename, GALERIE_LARGEUR);
    return [$filename, ''];
}

function page_personnes(array $c) {
    global $pdo;
    $table = $c['table'];   // vient toujours du code, jamais de l'utilisateur
    [$cleGroupePhoto, $cleGroupeLegende] = cles_photo_groupe($table);
    $erreur = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';
        $id = (int)($_POST['id'] ?? 0);

        // ── Photo de groupe ──
        if ($action === 'groupe') {
            [$nouvelle, $erreur] = recevoir_photo_groupe($c['dossier_upload']);
            if (!$erreur) {
                $ancienne = param($cleGroupePhoto);
                if ($nouvelle) {
                    if ($ancienne) delete_upload($c['dossier_upload'], $ancienne);
                    ecrire_parametre($cleGroupePhoto, $nouvelle);
                }
                $legende = trim($_POST['legende'] ?? '');
                $legendeChangee = $legende !== param($cleGroupeLegende);
                ecrire_parametre($cleGroupeLegende, $legende);
                if ($nouvelle || $legendeChangee) {
                    journaliser($c['journal'], ($nouvelle ? ($ancienne ? 'Remplacement' : 'Ajout') . ' de la photo de groupe' : 'Modification de la légende de la photo de groupe')
                        . " ({$c['titre']})");
                }
            }
        }
        if ($action === 'groupe_supprimer') {
            delete_upload($c['dossier_upload'], param($cleGroupePhoto));
            ecrire_parametre($cleGroupePhoto, '');
            journaliser($c['journal'], "Photo de groupe retirée ({$c['titre']})");
        }

        if ($action === 'create' || $action === 'edit') {
            $prenom      = trim($_POST['prenom']);
            $nom         = trim($_POST['nom']);
            $role        = trim($_POST['role']);
            $description = trim($_POST['description']);

            // Photo actuelle : lue en base (jamais depuis le formulaire)
            $photo = null;
            if ($action === 'edit') {
                $stmt = $pdo->prepare("SELECT photo FROM $table WHERE id=?");
                $stmt->execute([$id]);
                $photo = $stmt->fetchColumn() ?: null;
                if ($photo && isset($_POST['supprimer_photo'])) {
                    delete_upload($c['dossier_upload'], $photo);
                    $photo = null;
                }
            }
            $photo = save_cropped_image($_POST['image_cropped'] ?? '', $c['dossier_upload'], $c['prefixe_photo'], $photo);

            $nomComplet = trim("$prenom $nom");
            if ($action === 'create') {
                // Nouvelle personne placée en dernier
                $ordre = (int)$pdo->query("SELECT COALESCE(MAX(ordre), 0) + 1 FROM $table")->fetchColumn();
                $stmt = $pdo->prepare("INSERT INTO $table (prenom, nom, role, description, photo, ordre) VALUES (?,?,?,?,?,?)");
                $stmt->execute([$prenom, $nom, $role, $description, $photo, $ordre]);
                journaliser($c['journal'], "Ajout {$c['de_personne']} « $nomComplet »" . ($role !== '' ? " ($role)" : ''));
            } else {
                $stmt = $pdo->prepare("UPDATE $table SET prenom=?, nom=?, role=?, description=?, photo=? WHERE id=?");
                $stmt->execute([$prenom, $nom, $role, $description, $photo, $id]);
                journaliser($c['journal'], "Modification {$c['de_personne']} « $nomComplet »" . ($role !== '' ? " ($role)" : ''));
            }
        }

        if ($action === 'move') {
            deplacer_ordre($table, $id, $_POST['sens'] ?? '');
        }

        if ($action === 'delete') {
            $stmt = $pdo->prepare("SELECT prenom, nom, photo FROM $table WHERE id=?");
            $stmt->execute([$id]);
            $personne = $stmt->fetch();
            if (!empty($personne['photo'])) delete_upload($c['dossier_upload'], $personne['photo']);
            $pdo->prepare("DELETE FROM $table WHERE id=?")->execute([$id]);
            if ($personne) journaliser($c['journal'], "Suppression {$c['de_personne']} « " . trim("{$personne['prenom']} {$personne['nom']}") . " »");
        }

        if (!$erreur) {
            header("Location: {$c['page']}.php");
            exit;
        }
    }

    $personnes = $pdo->query("SELECT * FROM $table ORDER BY ordre, id")->fetchAll();
    $photoGroupe   = param($cleGroupePhoto);
    $legendeGroupe = param_brut($cleGroupeLegende) ?? '';
    $edit = null;
    if (isset($_GET['edit'])) {
        $stmt = $pdo->prepare("SELECT * FROM $table WHERE id=?");
        $stmt->execute([(int)$_GET['edit']]);
        $edit = $stmt->fetch();
    }
    $urlPhotos = '../public/assets/uploads/' . $c['dossier_url'] . '/';

    admin_header($c['titre'], $c['page'], CROP_HEAD . '<style>
        .coach-photo { width: 48px; height: 48px; border-radius: 50%; object-fit: cover; display: block; }
        .coach-initiales { width: 48px; height: 48px; border-radius: 50%; background: var(--vert); color: #fff; font-weight: 700; display: flex; align-items: center; justify-content: center; }
        .preview-img.rond { border-radius: 50%; }
        .groupe-apercu { display: flex; flex-wrap: wrap; gap: 16px; align-items: flex-start; margin-bottom: 14px; }
        .groupe-apercu img { max-width: 320px; width: 100%; border-radius: 8px; border: 1px solid #ddd; display: block; }
    </style>');
    ?>
    <h1><?= $c['icone'] ?> <?= $c['titre'] ?></h1>
    <?php if ($erreur): ?><div class="alert alert-error">❌ <?= htmlspecialchars($erreur) ?> Rien n'a été enregistré.</div><?php endif; ?>

    <!-- Photo de groupe de la section (affichée au-dessus des cartes sur « Le club ») -->
    <div class="card">
        <h2>📸 Photo de groupe</h2>
        <?php if ($photoGroupe): ?>
            <div class="groupe-apercu">
                <img src="<?= $urlPhotos . htmlspecialchars($photoGroupe) ?>" alt="Photo de groupe actuelle">
                <form method="POST" onsubmit="return confirm('Retirer la photo de groupe du site ?')">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="groupe_supprimer">
                    <button type="submit" class="btn btn-danger btn-sm">🗑️ Retirer la photo</button>
                </form>
            </div>
        <?php else: ?>
            <p class="form-hint" style="margin-bottom:14px">Aucune photo de groupe. Elle s'affiche sur la page « Le club », au-dessus des cartes individuelles.</p>
        <?php endif; ?>
        <form method="POST" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="groupe">
            <div class="form-row">
                <div class="form-group">
                    <label><?= $photoGroupe ? 'Remplacer la photo' : 'Choisir la photo' ?> (jpg, png, webp — max 10 Mo)</label>
                    <input type="file" name="photo_groupe" accept="image/jpeg,image/png,image/webp">
                    <div class="form-hint">Une photo en paysage rend mieux. Elle est automatiquement allégée et redressée.</div>
                </div>
                <div class="form-group">
                    <label>Légende (optionnel)</label>
                    <input type="text" name="legende" value="<?= htmlspecialchars($legendeGroupe) ?>" placeholder="<?= htmlspecialchars($c['exemple_legende'] ?? 'ex : Saison 2026-2027') ?>">
                </div>
            </div>
            <button type="submit" class="btn btn-primary">💾 Enregistrer</button>
        </form>
    </div>

    <div class="card">
        <h2><?= $edit ? 'Modifier ' . $c['personne'] : $c['nouveau'] ?></h2>
        <form method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="<?= $edit ? 'edit' : 'create' ?>">
            <?php if ($edit): ?><input type="hidden" name="id" value="<?= $edit['id'] ?>"><?php endif; ?>
            <div class="form-row">
                <div class="form-group">
                    <label>Prénom</label>
                    <input type="text" name="prenom" value="<?= htmlspecialchars($edit['prenom'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label>Nom</label>
                    <input type="text" name="nom" value="<?= htmlspecialchars($edit['nom'] ?? '') ?>" required>
                </div>
            </div>
            <div class="form-group">
                <label><?= $c['label_role'] ?></label>
                <input type="text" name="role" value="<?= htmlspecialchars($edit['role'] ?? '') ?>" placeholder="<?= htmlspecialchars($c['exemple_role']) ?>"
                       <?= !empty($c['suggestions_role']) ? 'list="suggestions-role"' : '' ?>>
                <?php if (!empty($c['suggestions_role'])): ?>
                <datalist id="suggestions-role">
                    <?php foreach ($c['suggestions_role'] as $s): ?><option value="<?= htmlspecialchars($s) ?>"><?php endforeach; ?>
                </datalist>
                <?php endif; ?>
            </div>
            <div class="form-group">
                <label>Description courte (optionnel)</label>
                <textarea name="description" placeholder="<?= htmlspecialchars($c['exemple_description']) ?>"><?= htmlspecialchars($edit['description'] ?? '') ?></textarea>
            </div>
            <div class="form-group">
                <label>Photo (optionnel — sinon les initiales sont affichées)</label>
                <?php if (!empty($edit['photo'])): ?>
                    <div style="margin-bottom:10px;display:flex;align-items:center;gap:14px;flex-wrap:wrap">
                        <img src="<?= $urlPhotos . htmlspecialchars($edit['photo']) ?>" class="preview-img rond" style="max-height:80px" alt="">
                        <label class="form-check" style="font-weight:400"><input type="checkbox" name="supprimer_photo"> Supprimer la photo</label>
                    </div>
                <?php endif; ?>
                <?php crop_field(1, 400, 400); ?>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><?= $edit ? 'Mettre à jour' : 'Ajouter' ?></button>
                <?php if ($edit): ?><a href="<?= $c['page'] ?>.php" class="link-cancel">Annuler</a><?php endif; ?>
            </div>
        </form>
    </div>

    <div class="card">
        <h2><?= $c['titre'] ?> (<?= count($personnes) ?>) — dans l'ordre d'affichage sur la page « Le club »</h2>
        <?php if (empty($personnes)): ?>
            <p class="empty">Personne pour l'instant. La section n'apparaît pas sur le site tant que la liste est vide.</p>
        <?php else: ?>
        <div class="table-wrap">
        <table>
            <thead><tr><th></th><th>Nom</th><th><?= $c['label_role'] ?></th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($personnes as $i => $p): ?>
            <tr>
                <td>
                    <?php if ($p['photo']): ?>
                        <img src="<?= $urlPhotos . htmlspecialchars($p['photo']) ?>" class="coach-photo" alt="">
                    <?php else: ?>
                        <div class="coach-initiales"><?= htmlspecialchars(initiales($p)) ?></div>
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars(trim($p['prenom'] . ' ' . $p['nom'])) ?></td>
                <td><?= htmlspecialchars($p['role'] ?? '') ?></td>
                <td>
                    <div class="actions">
                        <?php boutons_ordre($p['id'], $i, count($personnes)); ?>
                        <a href="<?= $c['page'] ?>.php?edit=<?= $p['id'] ?>" class="btn btn-secondary btn-sm">✏️ Modifier</a>
                        <form method="POST" onsubmit="return confirm('Supprimer <?= htmlspecialchars(addslashes($c['personne'])) ?> ?')">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $p['id'] ?>">
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

    <?php crop_modal(); ?>
    <?php admin_footer(CROP_SCRIPTS);
}
