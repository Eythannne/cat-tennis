<?php
require_once 'auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = (int)$_POST['id'];
    if ($action === 'lu') {
        $pdo->prepare("UPDATE messages_contact SET lu=1 WHERE id=?")->execute([$id]);
    }
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
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages — Admin CAT</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Segoe UI',sans-serif; background:#f4f6f9; color:#333; display:flex; min-height:100vh; }
        .sidebar { width:240px; background:#1A5276; color:#fff; padding:20px 0; flex-shrink:0; display:flex; flex-direction:column; }
        .sidebar-logo { padding:0 20px 30px; font-size:1.5rem; font-weight:700; letter-spacing:2px; }
        .sidebar nav a { display:block; padding:12px 20px; color:#cce4f7; text-decoration:none; font-size:0.95rem; transition:background 0.2s; }
        .sidebar nav a:hover, .sidebar nav a.active { background:rgba(255,255,255,0.15); color:#fff; }
        .sidebar-footer { padding:20px; margin-top:auto; }
        .sidebar-footer a { color:#cce4f7; text-decoration:none; font-size:0.85rem; }
        .main { flex:1; padding:30px; }
        h1 { font-size:1.6rem; margin-bottom:24px; color:#1A5276; }
        .layout { display:grid; grid-template-columns:1fr 1fr; gap:20px; }
        .card { background:#fff; border-radius:8px; padding:20px; box-shadow:0 1px 4px rgba(0,0,0,0.08); }
        .card h2 { font-size:1rem; color:#555; margin-bottom:16px; }
        .msg-item { padding:12px; border-radius:6px; margin-bottom:8px; cursor:pointer; border:1px solid #eee; transition:background 0.2s; }
        .msg-item:hover { background:#f0f7ff; }
        .msg-item.unread { border-left:3px solid #1A5276; background:#f7fbff; }
        .msg-item .from { font-weight:600; font-size:0.9rem; }
        .msg-item .subject { font-size:0.83rem; color:#666; margin-top:2px; }
        .msg-item .date { font-size:0.78rem; color:#aaa; margin-top:2px; }
        .detail-header { margin-bottom:16px; }
        .detail-header h3 { font-size:1.1rem; margin-bottom:4px; }
        .detail-header .meta { font-size:0.85rem; color:#888; }
        .detail-body { background:#f9f9f9; border-radius:6px; padding:16px; font-size:0.92rem; line-height:1.6; white-space:pre-wrap; margin-bottom:16px; }
        .actions { display:flex; gap:8px; }
        .btn { padding:8px 16px; border:none; border-radius:6px; cursor:pointer; font-size:0.85rem; font-weight:600; }
        .btn-danger { background:#e74c3c; color:#fff; }
        .btn-secondary { background:#eee; color:#333; text-decoration:none; display:inline-block; }
        .empty { color:#bbb; text-align:center; padding:30px 0; font-size:0.9rem; }
        .badge { display:inline-block; padding:2px 8px; border-radius:20px; font-size:0.75rem; font-weight:600; }
        .badge-red { background:#fde; color:#c0392b; }
    </style>
</head>
<body>
<div class="sidebar">
    <div class="sidebar-logo">CAT</div>
    <nav>
        <a href="index.php">📊 Dashboard</a>
        <a href="articles.php">📰 Articles</a>
        <a href="messages.php" class="active">✉️ Messages</a>
        <a href="evenements.php">📅 Événements</a>
        <a href="galerie.php">📷 Galerie</a>
        <a href="parametres.php">⚙️ Paramètres</a>
    </nav>
    <div class="sidebar-footer"><a href="logout.php">🚪 Déconnexion</a></div>
</div>
<div class="main">
    <h1>✉️ Messages de contact</h1>
    <div class="layout">
        <div class="card">
            <h2>Tous les messages (<?= count($messages) ?>)</h2>
            <?php if (empty($messages)): ?>
                <p class="empty">Aucun message reçu</p>
            <?php else: ?>
                <?php foreach ($messages as $m): ?>
                <a href="messages.php?voir=<?= $m['id'] ?>" style="text-decoration:none;color:inherit;">
                    <div class="msg-item <?= !$m['lu'] ? 'unread' : '' ?>">
                        <div class="from"><?= htmlspecialchars($m['nom']) ?> <?= !$m['lu'] ? '<span class="badge badge-red">Nouveau</span>' : '' ?></div>
                        <div class="subject"><?= htmlspecialchars($m['sujet'] ?? '(sans sujet)') ?></div>
                        <div class="date"><?= date('d/m/Y H:i', strtotime($m['created_at'])) ?></div>
                    </div>
                </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <div class="card">
            <?php if ($detail): ?>
                <div class="detail-header">
                    <h3><?= htmlspecialchars($detail['sujet'] ?? '(sans sujet)') ?></h3>
                    <div class="meta">
                        De : <strong><?= htmlspecialchars($detail['nom']) ?></strong> — <?= htmlspecialchars($detail['email']) ?><br>
                        Le <?= date('d/m/Y à H:i', strtotime($detail['created_at'])) ?>
                    </div>
                </div>
                <div class="detail-body"><?= htmlspecialchars($detail['message']) ?></div>
                <div class="actions">
                    <a href="mailto:<?= htmlspecialchars($detail['email']) ?>" class="btn btn-secondary">↩️ Répondre</a>
                    <form method="POST">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= $detail['id'] ?>">
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Supprimer ce message ?')">🗑️ Supprimer</button>
                    </form>
                </div>
            <?php else: ?>
                <p class="empty">Sélectionne un message pour le lire</p>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>