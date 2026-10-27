<?php
require_once 'auth.php';

// Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $maintenant = date('Y-m-d H:i:s');

    if ($action === 'create' || $action === 'edit') {
        $titre = trim($_POST['titre']);
        $contenu = trim($_POST['contenu']);

        // Bouton cliqué : "brouillon" ou "publier".
        // Date de publication : celle saisie (future = programmée), sinon maintenant.
        $dateSaisie = !empty($_POST['published_at']) ? date('Y-m-d H:i:s', strtotime($_POST['published_at'])) : null;
        if (($_POST['statut'] ?? '') === 'publier') {
            $publie = 1;
            $published_at = $dateSaisie ?? $maintenant;
        } else {
            $publie = 0;
            $published_at = $dateSaisie;
        }

        // Image actuelle : lue en base (jamais depuis le formulaire)
        $image = null;
        if ($action === 'edit') {
            $stmt = $pdo->prepare("SELECT image FROM articles WHERE id=?");
            $stmt->execute([(int)$_POST['id']]);
            $image = $stmt->fetchColumn() ?: null;
        }

        // Image recadrée envoyée par le cropper (remplace l'ancienne)
        $image = save_cropped_image($_POST['image_cropped'] ?? '', UPLOAD_ARTICLES, 'article_', $image);

        if ($action === 'create') {
            $stmt = $pdo->prepare("INSERT INTO articles (titre, contenu, publie, published_at, image) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$titre, $contenu, $publie, $published_at, $image]);
        } else {
            $id = (int)$_POST['id'];
            $stmt = $pdo->prepare("UPDATE articles SET titre=?, contenu=?, publie=?, published_at=?, image=? WHERE id=?");
            $stmt->execute([$titre, $contenu, $publie, $published_at, $image, $id]);
        }
    }

    // Boutons rapides de la liste
    if ($action === 'publier') {
        $pdo->prepare("UPDATE articles SET publie=1, published_at=? WHERE id=?")->execute([$maintenant, (int)$_POST['id']]);
    }
    if ($action === 'depublier') {
        $pdo->prepare("UPDATE articles SET publie=0 WHERE id=?")->execute([(int)$_POST['id']]);
    }

    if ($action === 'delete') {
        $id = (int)$_POST['id'];
        // Supprime l'image associée
        $art = $pdo->prepare("SELECT image FROM articles WHERE id=?");
        $art->execute([$id]);
        $row = $art->fetch();
        if (!empty($row['image'])) delete_upload(UPLOAD_ARTICLES, $row['image']);
        $pdo->prepare("DELETE FROM articles WHERE id=?")->execute([$id]);
    }

    header('Location: articles.php');
    exit;
}

// Récupération
$articles = $pdo->query("SELECT * FROM articles ORDER BY created_at DESC")->fetchAll();

// Article à éditer ?
$edit = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM articles WHERE id=?");
    $stmt->execute([(int)$_GET['edit']]);
    $edit = $stmt->fetch();
}

admin_header('Articles', 'articles', CROP_HEAD . '<style>
    .form-group textarea[name=contenu] { height: 180px; }
</style>');
?>
    <h1>📰 Gestion des articles</h1>

    <!-- Formulaire -->
    <div class="card">
        <h2><?= $edit ? 'Modifier l\'article' : 'Nouvel article' ?> <?= $edit ? article_badge($edit) : '' ?></h2>
        <form method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="<?= $edit ? 'edit' : 'create' ?>">
            <?php if ($edit): ?>
                <input type="hidden" name="id" value="<?= $edit['id'] ?>">
            <?php endif; ?>
            <div class="form-group">
                <label>Titre</label>
                <input type="text" name="titre" value="<?= htmlspecialchars($edit['titre'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label>Contenu</label>
                <textarea name="contenu"><?= htmlspecialchars($edit['contenu'] ?? '') ?></textarea>
            </div>
            <div class="form-group">
                <label>Image (jpg, png, webp — max 5 Mo)</label>
                <?php if (!empty($edit['image'])): ?>
                    <div style="margin-bottom:10px;">
                        <img src="../public/assets/uploads/articles/<?= htmlspecialchars($edit['image']) ?>" class="preview-img" alt="">
                        <div class="form-hint">Image actuelle — choisir un fichier pour la remplacer</div>
                    </div>
                <?php endif; ?>
                <?php crop_field(16 / 9, 800, 450); ?>
            </div>
            <div class="form-group">
                <label>Date de publication (optionnel)</label>
                <input type="datetime-local" name="published_at" style="max-width:260px"
                       value="<?= !empty($edit['published_at']) ? date('Y-m-d\TH:i', strtotime($edit['published_at'])) : '' ?>">
                <div class="form-hint">Vide = publié tout de suite. Une date future programme la publication : l'article apparaîtra automatiquement à cette date.</div>
            </div>
            <div class="form-actions">
                <button type="submit" name="statut" value="publier" class="btn btn-primary">
                    🚀 <?= ($edit['publie'] ?? 0) ? 'Mettre à jour' : 'Publier' ?>
                </button>
                <button type="submit" name="statut" value="brouillon" class="btn btn-secondary">
                    💾 <?= ($edit['publie'] ?? 0) ? 'Repasser en brouillon' : 'Enregistrer en brouillon' ?>
                </button>
                <?php if ($edit): ?>
                    <a href="articles.php" class="link-cancel">Annuler</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Liste -->
    <div class="card">
        <h2>Tous les articles (<?= count($articles) ?>)</h2>
        <?php if (empty($articles)): ?>
            <p class="empty">Aucun article pour l'instant.</p>
        <?php else: ?>
        <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Titre</th>
                    <th>Créé le</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($articles as $a): ?>
                <tr>
                    <td><?= htmlspecialchars($a['titre']) ?></td>
                    <td><?= date('d/m/Y', strtotime($a['created_at'])) ?></td>
                    <td><?= article_badge($a) ?></td>
                    <td>
                        <div class="actions">
                            <a href="articles.php?edit=<?= $a['id'] ?>" class="btn btn-secondary btn-sm">✏️ Modifier</a>
                            <form method="POST">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= $a['id'] ?>">
                                <?php if ($a['publie']): ?>
                                    <button type="submit" name="action" value="depublier" class="btn btn-secondary btn-sm">⏸️ Dépublier</button>
                                <?php else: ?>
                                    <button type="submit" name="action" value="publier" class="btn btn-accent btn-sm">🚀 Publier</button>
                                <?php endif; ?>
                            </form>
                            <form method="POST" onsubmit="return confirm('Supprimer cet article ?')">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $a['id'] ?>">
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
