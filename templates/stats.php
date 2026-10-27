<?php
// Chiffres clés du club (accueil + page Le club).
// Les 3 premiers se modifient dans Admin → Paramètres, le nombre d'entraîneurs est calculé.
$nbEntraineurs = $pdo->query("SELECT COUNT(*) FROM entraineurs")->fetchColumn();
$chiffres = [];
foreach (CHIFFRES_DEFAUT as $n => [$valeurDefaut, $labelDefaut]) {
    // Jamais enregistré → valeur par défaut ; enregistré vide → chiffre masqué
    $valeur = param_brut("stat{$n}_valeur") ?? $valeurDefaut;
    if ($valeur !== '') $chiffres[] = [$valeur, param("stat{$n}_label", $labelDefaut)];
}
if ($nbEntraineurs > 0) $chiffres[] = [$nbEntraineurs, $nbEntraineurs > 1 ? 'Entraîneurs' : 'Entraîneur'];
?>
<div class="about-stats">
    <?php foreach ($chiffres as [$valeur, $label]): ?>
    <div class="stat-card">
        <span class="stat-number"><?= htmlspecialchars($valeur) ?></span>
        <span class="stat-label"><?= htmlspecialchars($label) ?></span>
    </div>
    <?php endforeach; ?>
</div>
