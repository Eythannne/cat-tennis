<?php
require_once 'auth.php';

$upload_dir = '../public/assets/uploads/galerie/';
$miniatures_dir = $upload_dir . 'miniatures/';
if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'upload') {
        $titre = trim($_POST['titre']);
        $categorie = trim($_POST['categorie']);
        $f = $_FILES['fichier'] ?? null;
        // Extension déduite du vrai contenu du fichier, pas de son nom
        $ext = ($f && $f['error'] === UPLOAD_ERR_OK) ? image_extension(file_get_contents($f['tmp_name']), GALERIE_MAX_SIZE) : null;

        if (!$f || in_array($f['error'], [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE]) || ($f['size'] ?? 0) > GALERIE_MAX_SIZE) {
            $erreur = 'La photo est trop lourde (10 Mo maximum).';
        } elseif (!$ext) {
            $erreur = 'Format non reconnu : envoyez une photo JPG, PNG ou WEBP.';
        } else {
            $filename = uniqid() . '.' . $ext;
            if (move_uploaded_file($f['tmp_name'], $upload_dir . $filename)) {
                // Photo allégée pour le plein écran + vignette pour la grille
                optimiser_image($upload_dir . $filename, GALERIE_LARGEUR);
                optimiser_image($upload_dir . $filename, MINIATURE_LARGEUR, $miniatures_dir . $filename, 78);
                // Nouvelle photo placée en dernier
                $ordre = (int)$pdo->query("SELECT COALESCE(MAX(ordre), 0) + 1 FROM galerie")->fetchColumn();
                $stmt = $pdo->prepare("INSERT INTO galerie (titre, fichier, categorie, ordre) VALUES (?,?,?,?)");
                $stmt->execute([$titre, $filename, $categorie, $ordre]);
                journaliser('galerie', "Ajout de la photo « $titre »" . ($categorie !== '' ? " (catégorie $categorie)" : ''));
            } else {
                $erreur = 'Impossible d\'enregistrer la photo.';
            }
        }
    }
    if ($action === 'update') {
        $stmt = $pdo->prepare("UPDATE galerie SET titre=?, categorie=? WHERE id=?");
        $stmt->execute([trim($_POST['titre']), trim($_POST['categorie']), (int)$_POST['id']]);
    }
    if ($action === 'move') {
        deplacer_ordre('galerie', $_POST['id'] ?? 0, $_POST['sens'] ?? '', 'ordre ASC, created_at DESC');
    }
    if ($action === 'delete') {
        $id = (int)$_POST['id'];
        $row = $pdo->prepare("SELECT titre, fichier FROM galerie WHERE id=?");
        $row->execute([$id]);
        if ($photo = $row->fetch()) {
            delete_upload($upload_dir, $photo['fichier']);
            delete_upload($miniatures_dir, $photo['fichier']);
        }
        $pdo->prepare("DELETE FROM galerie WHERE id=?")->execute([$id]);
        if ($photo) journaliser('galerie', "Suppression de la photo « {$photo['titre']} »");
    }
    if (!$erreur) {
        header('Location: galerie.php');
        exit;
    }
}

$photos = $pdo->query("SELECT * FROM galerie ORDER BY ordre ASC, created_at DESC")->fetchAll();
$categories = $pdo->query("SELECT DISTINCT categorie FROM galerie WHERE categorie <> '' ORDER BY categorie")->fetchAll(PDO::FETCH_COLUMN);

admin_header('Galerie', 'galerie', '<style>
    .galerie-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 16px; }
    .galerie-item { border-radius: 8px; overflow: hidden; border: 1px solid #eee; background: #fff; }
    .galerie-item img { width: 100%; height: 140px; object-fit: cover; display: block; }
    .galerie-item .info { padding: 8px 10px; }
    .galerie-item .titre { font-size: 0.85rem; color: #333; font-weight: 600; }
    .galerie-item .cat { font-size: 0.75rem; color: #888; }
    .galerie-item .tools { display: flex; gap: 4px; margin-top: 8px; }
    .galerie-item .tools form { display: inline; }
    .galerie-item details { margin-top: 8px; font-size: 0.82rem; }
    .galerie-item details summary { cursor: pointer; color: var(--vert); }
    .galerie-item details .form-group { margin: 6px 0; }
    .galerie-item details input { padding: 6px 8px !important; font-size: 0.82rem !important; }
</style>');
?>
    <h1>📷 Galerie photos</h1>
    <?php if ($erreur): ?><div class="alert alert-error">❌ <?= htmlspecialchars($erreur) ?></div><?php endif; ?>
    <datalist id="categories">
        <?php foreach ($categories as $c): ?><option value="<?= htmlspecialchars($c) ?>"><?php endforeach; ?>
    </datalist>
    <div class="card">
        <h2>Ajouter une photo</h2>
        <form method="POST" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="upload">
            <div class="form-row">
                <div class="form-group">
                    <label>Titre</label>
                    <input type="text" name="titre" required>
                </div>
                <div class="form-group">
                    <label>Catégorie</label>
                    <input type="text" name="categorie" list="categories" placeholder="ex: Tournoi, Cours...">
                    <div class="form-hint">Les visiteurs pourront filtrer la galerie par catégorie.</div>
                </div>
            </div>
            <div class="form-group">
                <label>Photo (jpg, png, webp — max 10 Mo)</label>
                <div class="form-hint" style="margin:-2px 0 6px">Les photos sont automatiquement allégées et redressées à l'envoi : pas besoin de les retoucher.</div>
                <input type="file" name="fichier" accept=".jpg,.jpeg,.png,.webp" required>
            </div>
            <button type="submit" class="btn btn-primary">📤 Uploader</button>
        </form>
    </div>
    <div class="card">
        <h2>Photos (<?= count($photos) ?>) — dans l'ordre d'affichage sur le site</h2>
        <?php if (empty($photos)): ?>
            <p class="empty">Aucune photo.</p>
        <?php else: ?>
        <div class="galerie-grid">
            <?php foreach ($photos as $i => $p): ?>
            <div class="galerie-item">
                <img src="../public/assets/uploads/galerie/<?= htmlspecialchars(miniature_galerie($p['fichier'])) ?>" alt="<?= htmlspecialchars($p['titre']) ?>">
                <div class="info">
                    <div class="titre"><?= htmlspecialchars($p['titre']) ?></div>
                    <div class="cat"><?= $p['categorie'] !== '' && $p['categorie'] !== null ? htmlspecialchars($p['categorie']) : 'Sans catégorie' ?></div>
                    <div class="tools">
                        <?php boutons_ordre($p['id'], $i, count($photos), ['avant' => '←', 'apres' => '→']); ?>
                        <form method="POST" onsubmit="return confirm('Supprimer ?')" style="margin-left:auto">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $p['id'] ?>">
                            <button type="submit" class="btn btn-danger btn-sm">🗑️</button>
                        </form>
                    </div>
                    <details>
                        <summary>✏️ Modifier</summary>
                        <form method="POST">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="id" value="<?= $p['id'] ?>">
                            <div class="form-group"><input type="text" name="titre" value="<?= htmlspecialchars($p['titre']) ?>" required></div>
                            <div class="form-group"><input type="text" name="categorie" list="categories" value="<?= htmlspecialchars($p['categorie'] ?? '') ?>" placeholder="Catégorie"></div>
                            <button type="submit" class="btn btn-primary btn-sm">Enregistrer</button>
                        </form>
                    </details>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
<?php admin_footer(); ?>
