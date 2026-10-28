<?php
require_once 'auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'create' || $action === 'edit') {
        $titre = trim($_POST['titre']);
        $date_debut = $_POST['date_debut'];
        $date_fin = $_POST['date_fin'] ?: null;
        if ($date_fin && $date_fin < $date_debut) $date_fin = null;
        // La couleur découle du type (voir EVENT_TYPES dans src/init.php)
        $type = isset(EVENT_TYPES[$_POST['type'] ?? '']) ? $_POST['type'] : 'autre';
        $couleur = EVENT_TYPES[$type]['couleur'];
        $description = trim($_POST['description']);
        $quand = date('d/m/Y', strtotime($date_debut));
        if ($action === 'create') {
            $stmt = $pdo->prepare("INSERT INTO evenements (titre, date_debut, date_fin, type, couleur, description) VALUES (?,?,?,?,?,?)");
            $stmt->execute([$titre, $date_debut, $date_fin, $type, $couleur, $description]);
            journaliser('evenement', "Création de l'événement « $titre » (" . EVENT_TYPES[$type]['label'] . ", $quand)");
        } else {
            $id = (int)$_POST['id'];
            $stmt = $pdo->prepare("UPDATE evenements SET titre=?, date_debut=?, date_fin=?, type=?, couleur=?, description=? WHERE id=?");
            $stmt->execute([$titre, $date_debut, $date_fin, $type, $couleur, $description, $id]);
            journaliser('evenement', "Modification de l'événement « $titre » (" . EVENT_TYPES[$type]['label'] . ", $quand)");
        }
    }
    if ($action === 'delete') {
        $stmt = $pdo->prepare("SELECT titre, date_debut FROM evenements WHERE id=?");
        $stmt->execute([(int)$_POST['id']]);
        $ev = $stmt->fetch();
        $pdo->prepare("DELETE FROM evenements WHERE id=?")->execute([(int)$_POST['id']]);
        if ($ev) journaliser('evenement', "Suppression de l'événement « {$ev['titre']} » (" . date('d/m/Y', strtotime($ev['date_debut'])) . ')');
    }
    header('Location: evenements.php');
    exit;
}

$evenements = $pdo->query("SELECT * FROM evenements ORDER BY date_debut DESC")->fetchAll();
$edit = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM evenements WHERE id=?");
    $stmt->execute([(int)$_GET['edit']]);
    $edit = $stmt->fetch();
}

admin_header('Événements', 'evenements');
?>
    <h1>📅 Gestion des événements</h1>
    <div class="card">
        <h2><?= $edit ? 'Modifier l\'événement' : 'Nouvel événement' ?></h2>
        <form method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="<?= $edit ? 'edit' : 'create' ?>">
            <?php if ($edit): ?><input type="hidden" name="id" value="<?= $edit['id'] ?>"><?php endif; ?>
            <div class="form-row">
                <div class="form-group">
                    <label>Titre</label>
                    <input type="text" name="titre" value="<?= htmlspecialchars($edit['titre'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label>Type</label>
                    <select name="type">
                        <?php foreach (EVENT_TYPES as $t => $infos): ?>
                        <option value="<?= $t ?>" <?= ($edit['type'] ?? '') === $t ? 'selected' : '' ?>><?= $infos['label'] ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-hint">La couleur dans le calendrier dépend du type.</div>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Date début</label>
                    <input type="datetime-local" name="date_debut" value="<?= $edit ? date('Y-m-d\TH:i', strtotime($edit['date_debut'])) : '' ?>" required>
                </div>
                <div class="form-group">
                    <label>Date fin (optionnel)</label>
                    <input type="datetime-local" name="date_fin" value="<?= ($edit && $edit['date_fin']) ? date('Y-m-d\TH:i', strtotime($edit['date_fin'])) : '' ?>">
                </div>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description"><?= htmlspecialchars($edit['description'] ?? '') ?></textarea>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><?= $edit ? 'Mettre à jour' : 'Créer' ?></button>
                <?php if ($edit): ?><a href="evenements.php" class="link-cancel">Annuler</a><?php endif; ?>
            </div>
        </form>
    </div>
    <div class="card">
        <h2>Tous les événements (<?= count($evenements) ?>)</h2>
        <?php if (empty($evenements)): ?>
            <p class="empty">Aucun événement.</p>
        <?php else: ?>
        <div class="table-wrap">
        <table>
            <thead><tr><th>Titre</th><th>Date début</th><th>Type</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($evenements as $e): $type = EVENT_TYPES[$e['type']] ?? EVENT_TYPES['autre']; ?>
            <tr>
                <td><span class="dot" style="background:<?= $type['couleur'] ?>"></span><?= htmlspecialchars($e['titre']) ?></td>
                <td><?= date('d/m/Y H:i', strtotime($e['date_debut'])) ?></td>
                <td><?= $type['label'] ?></td>
                <td>
                    <div class="actions">
                        <a href="evenements.php?edit=<?= $e['id'] ?>" class="btn btn-secondary btn-sm">✏️ Modifier</a>
                        <form method="POST" onsubmit="return confirm('Supprimer ?')">
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
<?php admin_footer(); ?>
