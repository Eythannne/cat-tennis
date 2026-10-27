<?php
require_once 'auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);
    if ($action === 'delete') {
        $pdo->prepare("DELETE FROM messages_contact WHERE id=?")->execute([$id]);
    }
    header('Location: messages.php');
    exit;
}

$messages = $pdo->query("SELECT * FROM messages_contact ORDER BY created_at DESC")->fetchAll();
$detail = null;
if (isset($_GET['voir'])) {
    $stmt = $pdo->prepare("SELECT * FROM messages_contact WHERE id=?");
    $stmt->execute([(int)$_GET['voir']]);
    $detail = $stmt->fetch();
    if ($detail && !$detail['lu']) {
        $pdo->prepare("UPDATE messages_contact SET lu=1 WHERE id=?")->execute([$detail['id']]);
    }
}

admin_header('Messages', 'messages', '<style>
    .layout { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; align-items: start; }
    .msg-item { display: block; padding: 12px; border-radius: 6px; margin-bottom: 8px; border: 1px solid #eee; text-decoration: none; color: inherit; transition: background 0.2s; }
    .msg-item:hover { background: var(--vert-light); }
    .msg-item.unread { border-left: 3px solid var(--vert); background: #f6fbf7; }
    .msg-item.selected { border-color: var(--vert); }
    .msg-item .from { font-weight: 600; font-size: 0.9rem; }
    .msg-item .subject { font-size: 0.83rem; color: #666; margin-top: 2px; }
    .msg-item .date { font-size: 0.78rem; color: #aaa; margin-top: 2px; }
    .detail-header { margin-bottom: 16px; }
    .detail-header h3 { font-size: 1.1rem; margin-bottom: 4px; }
    .detail-header .meta { font-size: 0.85rem; color: #888; word-break: break-word; }
    .detail-body { background: #f9f9f9; border-radius: 6px; padding: 16px; font-size: 0.92rem; line-height: 1.6; white-space: pre-wrap; word-break: break-word; margin-bottom: 16px; }
    @media (max-width: 900px) { .layout { grid-template-columns: 1fr; } .layout .card-detail { order: -1; } }
</style>');
?>
    <h1>✉️ Messages de contact</h1>
    <div class="layout">
        <div class="card">
            <h2>Tous les messages (<?= count($messages) ?>)</h2>
            <?php if (empty($messages)): ?>
                <p class="empty">Aucun message reçu</p>
            <?php else: ?>
                <?php foreach ($messages as $m): ?>
                <a href="messages.php?voir=<?= $m['id'] ?>" class="msg-item <?= !$m['lu'] ? 'unread' : '' ?> <?= ($detail['id'] ?? 0) == $m['id'] ? 'selected' : '' ?>">
                    <div class="from"><?= htmlspecialchars($m['nom']) ?> <?= !$m['lu'] ? '<span class="badge badge-red">Nouveau</span>' : '' ?></div>
                    <div class="subject"><?= htmlspecialchars(sujet_label($m['sujet'])) ?></div>
                    <div class="date"><?= date('d/m/Y H:i', strtotime($m['created_at'])) ?></div>
                </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <?php if ($detail): ?>
        <div class="card card-detail">
            <div class="detail-header">
                <h3><?= htmlspecialchars(sujet_label($detail['sujet'])) ?></h3>
                <div class="meta">
                    De : <strong><?= htmlspecialchars($detail['nom']) ?></strong> — <?= htmlspecialchars($detail['email']) ?><br>
                    Le <?= date('d/m/Y à H:i', strtotime($detail['created_at'])) ?>
                </div>
            </div>
            <div class="detail-body"><?= htmlspecialchars($detail['message']) ?></div>
            <div class="actions">
                <a href="mailto:<?= htmlspecialchars($detail['email']) ?>" class="btn btn-secondary">↩️ Répondre</a>
                <form method="POST" onsubmit="return confirm('Supprimer ce message ?')">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= $detail['id'] ?>">
                    <button type="submit" class="btn btn-danger">🗑️ Supprimer</button>
                </form>
            </div>
        </div>
        <?php else: ?>
        <div class="card">
            <p class="empty">Sélectionne un message pour le lire</p>
        </div>
        <?php endif; ?>
    </div>
<?php admin_footer(); ?>
