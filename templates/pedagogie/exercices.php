<?php
// Onglet Exercices de la page Pédagogie : bibliothèque d'exercices, programmation annuelle,
// critères de passage à la balle supérieure.
?>
<nav class="peda-sous-onglets" aria-label="Parties de l'onglet Exercices">
    <?php foreach (PEDA_SECTIONS_EXERCICES as $cle => $libelle): ?>
    <a href="pedagogie.php?onglet=exercices&amp;section=<?= $cle ?>" class="peda-puce <?= $section === $cle ? 'actif' : '' ?>"><?= $libelle ?></a>
    <?php endforeach; ?>
</nav>

<?php if ($section === 'bibliotheque'):
    $exercices = $pdo->query("SELECT * FROM peda_exercices ORDER BY titre")->fetchAll();
    $edit = isset($_GET['modifier']) ? peda_ligne('peda_exercices', $_GET['modifier']) : null;
    $x = $edit ?: [];
    $formulaireOuvert = $edit || ($erreurs && ($_POST['action'] ?? '') === 'exercice_enregistrer');
    $rubriques = ['description' => 'Description', 'utilite' => 'À quoi ça sert', 'cas_usage' => 'Dans quel cas l\'utiliser', 'points_attention' => 'Points d\'attention'];
?>
<div class="peda-entete">
    <p><strong><?= count($exercices) ?></strong> exercice<?= count($exercices) > 1 ? 's' : '' ?> dans la bibliothèque.</p>
</div>

<details class="peda-carte peda-formulaire" id="formulaire" <?= $formulaireOuvert ? 'open' : '' ?>>
    <summary><?= $edit ? '✏️ Modifier « ' . htmlspecialchars($edit['titre']) . ' »' : '＋ Nouvel exercice' ?></summary>
    <form method="POST" action="<?= peda_url() ?>#formulaire" class="peda-form">
        <input type="hidden" name="action" value="exercice_enregistrer">
        <?php if ($edit): ?><input type="hidden" name="id" value="<?= $edit['id'] ?>"><?php endif; ?>
        <div class="peda-champ peda-champ--large">
            <label for="x-titre">Titre de l'exercice *</label>
            <input type="text" id="x-titre" name="titre" maxlength="150" required value="<?= htmlspecialchars(peda_valeur('titre', $x)) ?>" placeholder="ex : Montée au filet en 3 temps">
        </div>
        <?php foreach ($rubriques as $champ => $libelle): ?>
        <div class="peda-champ peda-champ--large">
            <label for="x-<?= $champ ?>"><?= $libelle ?></label>
            <textarea id="x-<?= $champ ?>" name="<?= $champ ?>" rows="<?= $champ === 'description' ? 5 : 3 ?>"
                <?= $champ === 'points_attention' ? 'placeholder="ex : jeu de jambes du joueur, placement, respiration…"' : '' ?>><?= htmlspecialchars(peda_valeur($champ, $x)) ?></textarea>
        </div>
        <?php endforeach; ?>
        <div class="peda-actions">
            <button type="submit" class="btn-primary"><?= $edit ? '💾 Enregistrer les modifications' : '＋ Ajouter l\'exercice' ?></button>
            <?php if ($edit): ?><a href="<?= peda_url() ?>" class="peda-lien">Annuler</a><?php endif; ?>
        </div>
    </form>
</details>

<?php if ($exercices): ?>
<div class="peda-filtres">
    <input type="search" class="peda-recherche" data-cible="#liste-exercices .peda-exercice" placeholder="🔎 Rechercher un exercice…" aria-label="Rechercher un exercice">
</div>
<?php endif; ?>
<div class="peda-exercices" id="liste-exercices">
    <?php if (!$exercices): ?><p class="peda-vide">Aucun exercice pour l'instant. Ajoutez le premier avec « ＋ Nouvel exercice ».</p><?php endif; ?>
    <?php foreach ($exercices as $ex): ?>
    <details class="peda-carte peda-exercice">
        <summary><?= htmlspecialchars($ex['titre']) ?></summary>
        <div class="peda-exercice-corps">
            <?php foreach ($rubriques as $champ => $libelle): if (trim($ex[$champ] ?? '') === '') continue; ?>
            <div class="peda-rubrique peda-rubrique--<?= $champ ?>">
                <h4><?= $libelle ?></h4>
                <p><?= nl2br(htmlspecialchars($ex[$champ])) ?></p>
            </div>
            <?php endforeach; ?>
            <div class="peda-fiche-actions">
                <a href="<?= peda_url(['modifier' => $ex['id']]) ?>#formulaire" class="peda-bouton">✏️ Modifier</a>
                <form method="POST" action="<?= peda_url() ?>" data-confirmer="Supprimer l'exercice « <?= htmlspecialchars($ex['titre']) ?> » ?">
                    <input type="hidden" name="action" value="exercice_supprimer">
                    <input type="hidden" name="id" value="<?= $ex['id'] ?>">
                    <button type="submit" class="peda-bouton peda-bouton--danger">🗑️ Supprimer</button>
                </form>
            </div>
        </div>
    </details>
    <?php endforeach; ?>
</div>

<?php elseif ($section === 'programmation'):
    $pdf   = param('peda_programmation_pdf');
    $notes = $_SERVER['REQUEST_METHOD'] === 'POST' ? ($_POST['notes'] ?? '') : param('peda_programmation_notes');
    $urlPdf = $pdf ? BASE_URL . '/assets/uploads/pedagogie/' . rawurlencode($pdf) : '';
?>
<div class="peda-entete">
    <p>Document du directeur sportif : la répartition des thèmes travaillés au fil de l'année.</p>
</div>
<?php if ($pdf): ?>
<div class="peda-carte">
    <div class="peda-fiche-actions peda-pdf-actions">
        <a href="<?= $urlPdf ?>" target="_blank" rel="noopener" class="peda-bouton">📄 Ouvrir en plein écran</a>
        <a href="<?= $urlPdf ?>" download class="peda-bouton">⬇️ Télécharger</a>
        <form method="POST" action="<?= peda_url() ?>" data-confirmer="Supprimer le document de programmation annuelle ?">
            <input type="hidden" name="action" value="programmation_supprimer_pdf">
            <button type="submit" class="peda-bouton peda-bouton--danger">🗑️ Supprimer le document</button>
        </form>
    </div>
    <object class="peda-pdf" data="<?= $urlPdf ?>" type="application/pdf">
        <p class="peda-vide">L'aperçu n'est pas disponible sur cet appareil : <a href="<?= $urlPdf ?>" target="_blank" rel="noopener">ouvrir le PDF</a>.</p>
    </object>
</div>
<?php else: ?>
<p class="peda-vide peda-carte">Aucun document de programmation pour l'instant.</p>
<?php endif; ?>

<form method="POST" action="<?= peda_url() ?>" enctype="multipart/form-data" class="peda-carte peda-form">
    <input type="hidden" name="action" value="programmation_enregistrer">
    <div class="peda-champ peda-champ--large">
        <label for="prog-pdf"><?= $pdf ? 'Remplacer le document (PDF, 10 Mo maximum)' : 'Document de programmation (PDF, 10 Mo maximum)' ?></label>
        <input type="file" id="prog-pdf" name="pdf" accept="application/pdf,.pdf">
    </div>
    <div class="peda-champ peda-champ--large">
        <label for="prog-notes">Notes / répartition des thèmes</label>
        <textarea id="prog-notes" name="notes" rows="6" placeholder="ex : Septembre-octobre : coup droit et revers. Novembre-décembre : service et retour…"><?= htmlspecialchars($notes) ?></textarea>
    </div>
    <div class="peda-actions"><button type="submit" class="btn-primary">💾 Enregistrer</button></div>
</form>

<?php else: /* critères de passage */ ?>
<div class="peda-entete">
    <p>Critères qu'un enfant doit remplir pour passer à la balle supérieure. Une ligne par critère.</p>
</div>
<form method="POST" action="<?= peda_url() ?>" class="peda-form">
    <input type="hidden" name="action" value="criteres_enregistrer">
    <div class="peda-criteres">
        <?php foreach (PEDA_PASSAGES as $de => $vers):
            $valeur = $_SERVER['REQUEST_METHOD'] === 'POST' ? ($_POST['criteres'][$de] ?? '') : param("peda_criteres_$de"); ?>
        <div class="peda-carte peda-critere">
            <h3>
                <span class="peda-etiquette"><span class="peda-pastille" style="background:<?= PEDA_NIVEAUX[$de][1] ?>"></span><?= PEDA_NIVEAUX[$de][0] ?></span>
                <span aria-hidden="true">→</span>
                <span class="peda-etiquette"><span class="peda-pastille" style="background:<?= PEDA_NIVEAUX[$vers][1] ?>"></span><?= PEDA_NIVEAUX[$vers][0] ?></span>
            </h3>
            <label class="sr-only" for="crit-<?= $de ?>">Critères pour passer de <?= PEDA_NIVEAUX[$de][0] ?> à <?= PEDA_NIVEAUX[$vers][0] ?></label>
            <textarea id="crit-<?= $de ?>" name="criteres[<?= $de ?>]" rows="5" placeholder="ex : Réaliser 10 échanges en coup droit…"><?= htmlspecialchars($valeur) ?></textarea>
        </div>
        <?php endforeach; ?>
    </div>
    <div class="peda-actions"><button type="submit" class="btn-primary">💾 Enregistrer les critères</button></div>
</form>
<?php endif; ?>
