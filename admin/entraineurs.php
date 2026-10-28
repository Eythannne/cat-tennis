<?php
require_once 'auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);

    if ($action === 'create' || $action === 'edit') {
        $prenom      = trim($_POST['prenom']);
        $nom         = trim($_POST['nom']);
        $role        = trim($_POST['role']);
        $description = trim($_POST['description']);

        // Photo actuelle : lue en base (jamais depuis le formulaire)
        $photo = null;
        if ($action === 'edit') {
            $stmt = $pdo->prepare("SELECT photo FROM entraineurs WHERE id=?");
            $stmt->execute([$id]);
            $photo = $stmt->fetchColumn() ?: null;
            if ($photo && isset($_POST['supprimer_photo'])) {
                delete_upload(UPLOAD_ENTRAINEURS, $photo);
                $photo = null;
            }
        }
        $photo = save_cropped_image($_POST['image_cropped'] ?? '', UPLOAD_ENTRAINEURS, 'coach_', $photo);

        if ($action === 'create') {
            // Nouvel entraîneur placé en dernier
            $ordre = (int)$pdo->query("SELECT COALESCE(MAX(ordre), 0) + 1 FROM entraineurs")->fetchColumn();
            $stmt = $pdo->prepare("INSERT INTO entraineurs (prenom, nom, role, description, photo, ordre) VALUES (?,?,?,?,?,?)");
            $stmt->execute([$prenom, $nom, $role, $description, $photo, $ordre]);
            journaliser('entraineur', "Ajout de l'entraîneur « " . trim("$prenom $nom") . " »");
        } else {
            $stmt = $pdo->prepare("UPDATE entraineurs SET prenom=?, nom=?, role=?, description=?, photo=? WHERE id=?");
            $stmt->execute([$prenom, $nom, $role, $description, $photo, $id]);
            journaliser('entraineur', "Modification de l'entraîneur « " . trim("$prenom $nom") . " »");
        }
    }

    if ($action === 'move') {
        deplacer_ordre('entraineurs', $id, $_POST['sens'] ?? '');
    }

    if ($action === 'delete') {
        $stmt = $pdo->prepare("SELECT prenom, nom, photo FROM entraineurs WHERE id=?");
        $stmt->execute([$id]);
        $coach = $stmt->fetch();
        if (!empty($coach['photo'])) delete_upload(UPLOAD_ENTRAINEURS, $coach['photo']);
        $pdo->prepare("DELETE FROM entraineurs WHERE id=?")->execute([$id]);
        if ($coach) journaliser('entraineur', "Suppression de l'entraîneur « " . trim("{$coach['prenom']} {$coach['nom']}") . " »");
    }

    header('Location: entraineurs.php');
    exit;
}

$entraineurs = $pdo->query("SELECT * FROM entraineurs ORDER BY ordre, id")->fetchAll();
$edit = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM entraineurs WHERE id=?");
    $stmt->execute([(int)$_GET['edit']]);
    $edit = $stmt->fetch();
}

admin_header('Entraîneurs', 'entraineurs', CROP_HEAD . '<style>
    .coach-photo { width: 48px; height: 48px; border-radius: 50%; object-fit: cover; display: block; }
    .coach-initiales { width: 48px; height: 48px; border-radius: 50%; background: var(--vert); color: #fff; font-weight: 700; display: flex; align-items: center; justify-content: center; }
    .preview-img.rond { border-radius: 50%; }
</style>');
?>
    <h1>🎾 Entraîneurs</h1>

    <div class="card">
        <h2><?= $edit ? 'Modifier l\'entraîneur' : 'Ajouter un entraîneur' ?></h2>
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
                <label>Rôle / spécialité</label>
                <input type="text" name="role" value="<?= htmlspecialchars($edit['role'] ?? '') ?>" placeholder="ex : Enfants & Pôle compétition">
            </div>
            <div class="form-group">
                <label>Description courte</label>
                <textarea name="description" placeholder="ex : Cours collectifs enfants, stages vacances..."><?= htmlspecialchars($edit['description'] ?? '') ?></textarea>
            </div>
            <div class="form-group">
                <label>Photo (optionnel — sinon les initiales sont affichées)</label>
                <?php if (!empty($edit['photo'])): ?>
                    <div style="margin-bottom:10px;display:flex;align-items:center;gap:14px;flex-wrap:wrap">
                        <img src="../public/assets/uploads/entraineurs/<?= htmlspecialchars($edit['photo']) ?>" class="preview-img rond" style="max-height:80px" alt="">
                        <label class="form-check" style="font-weight:400"><input type="checkbox" name="supprimer_photo"> Supprimer la photo</label>
                    </div>
                <?php endif; ?>
                <?php crop_field(1, 400, 400); ?>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><?= $edit ? 'Mettre à jour' : 'Ajouter' ?></button>
                <?php if ($edit): ?><a href="entraineurs.php" class="link-cancel">Annuler</a><?php endif; ?>
            </div>
        </form>
    </div>

    <div class="card">
        <h2>Entraîneurs (<?= count($entraineurs) ?>) — dans l'ordre d'affichage sur la page « Le club »</h2>
        <?php if (empty($entraineurs)): ?>
            <p class="empty">Aucun entraîneur. La section n'apparaît pas sur le site tant qu'il n'y en a pas.</p>
        <?php else: ?>
        <div class="table-wrap">
        <table>
            <thead><tr><th></th><th>Nom</th><th>Rôle</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($entraineurs as $i => $e): ?>
            <tr>
                <td>
                    <?php if ($e['photo']): ?>
                        <img src="../public/assets/uploads/entraineurs/<?= htmlspecialchars($e['photo']) ?>" class="coach-photo" alt="">
                    <?php else: ?>
                        <div class="coach-initiales"><?= htmlspecialchars(initiales($e)) ?></div>
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars(trim($e['prenom'] . ' ' . $e['nom'])) ?></td>
                <td><?= htmlspecialchars($e['role'] ?? '') ?></td>
                <td>
                    <div class="actions">
                        <?php boutons_ordre($e['id'], $i, count($entraineurs)); ?>
                        <a href="entraineurs.php?edit=<?= $e['id'] ?>" class="btn btn-secondary btn-sm">✏️ Modifier</a>
                        <form method="POST" onsubmit="return confirm('Supprimer cet entraîneur ?')">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $e['id'] ?>">
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
<?php admin_footer(CROP_SCRIPTS); ?>
