<?php
// Cartes de personnes (entraîneurs, bureau) de la page « Le club ».
// Attend $personnes (lignes de la table) et $dossierPhotos (sous-dossier de assets/uploads/).
/** @var array  $personnes */
/** @var string $dossierPhotos */
?>
<div class="coaches-grid">
    <?php foreach ($personnes as $p): ?>
    <div class="coach-card">
        <div class="coach-avatar">
            <?php if ($p['photo']): ?>
                <img src="assets/uploads/<?= $dossierPhotos ?>/<?= htmlspecialchars($p['photo']) ?>" alt="<?= htmlspecialchars($p['prenom']) ?>" loading="lazy">
            <?php else: ?>
                <?= htmlspecialchars(initiales($p)) ?>
            <?php endif; ?>
        </div>
        <h3><?= htmlspecialchars(trim($p['prenom'] . ' ' . $p['nom'])) ?></h3>
        <?php if ($p['role']): ?><span class="coach-role"><?= htmlspecialchars($p['role']) ?></span><?php endif; ?>
        <?php if ($p['description']): ?><p><?= nl2br(htmlspecialchars($p['description'])) ?></p><?php endif; ?>
    </div>
    <?php endforeach; ?>
</div>
