<?php
// Espace Jeux : une carte par jeu de la liste JEUX (src/jeux.php), avec son record et son nombre de joueurs.
require_once '../src/init.php';
require_once '../src/jeux.php';
$pageTitle = "Jeux — CAT";
$pageDesc = "Les petits jeux du Club Aulnaysien de Tennis : jouez avec un pseudo et grimpez au classement.";

// Styles propres à cette page, ajoutés dans le <head> (voir templates/head.php)
ob_start();
?>
<style>
    .section-jeux { padding: 50px 0 80px; }
    .jeux-grille { display: grid; grid-template-columns: repeat(auto-fit, minmax(270px, 320px)); justify-content: center; gap: 24px; max-width: 1040px; margin: 0 auto; }
    .jeu-carte { display: flex; flex-direction: column; background: var(--white); border: 1px solid var(--border); border-radius: 16px; overflow: hidden; box-shadow: var(--shadow-card); text-decoration: none; color: var(--text); transition: transform 0.2s ease, box-shadow 0.2s ease; }
    a.jeu-carte:hover { transform: translateY(-4px); box-shadow: 0 12px 30px rgba(18,74,32,0.16); }
    .jeu-carte-visuel { position: relative; height: 150px; display: grid; place-items: center; font-size: 4rem;
        background: linear-gradient(135deg, var(--vert) 0%, var(--vert-dark) 100%); }
    /* Lignes de court en décor */
    .jeu-carte-visuel::before { content: ''; position: absolute; inset: 18px 30px; border: 2px solid rgba(255,255,255,0.35); border-radius: 2px; }
    .jeu-carte-visuel::after { content: ''; position: absolute; left: 30px; right: 30px; top: 50%; border-top: 2px solid rgba(255,255,255,0.35); }
    .jeu-carte-visuel span { position: relative; z-index: 1; filter: drop-shadow(0 4px 8px rgba(0,0,0,0.3)); transition: transform 0.3s ease; }
    a.jeu-carte:hover .jeu-carte-visuel span { transform: scale(1.12) rotate(-12deg); }
    .jeu-carte-corps { flex: 1; display: flex; flex-direction: column; gap: 10px; padding: 20px 22px 22px; }
    .jeu-carte h2 { font-size: 1.25rem; color: var(--dark); }
    .jeu-carte p { font-size: 0.92rem; color: var(--muted); line-height: 1.5; }
    .jeu-carte-stats { display: flex; flex-wrap: wrap; gap: 8px; margin-top: auto; padding-top: 6px; font-size: 0.82rem; }
    .jeu-carte-stats span { background: var(--vert-light); color: var(--vert-dark); padding: 4px 10px; border-radius: 20px; font-weight: 600; }
    .jeu-carte-bouton { display: inline-block; align-self: flex-start; margin-top: 6px; padding: 10px 22px; border-radius: 10px; background: var(--jaune); color: var(--dark); font-weight: 700; }
    .jeu-carte--bientot { border: 2px dashed var(--border); box-shadow: none; background: transparent; }
    .jeu-carte--bientot .jeu-carte-visuel { background: repeating-linear-gradient(45deg, #E9EFEA, #E9EFEA 12px, #F1F5F2 12px, #F1F5F2 24px); }
    .jeu-carte--bientot .jeu-carte-visuel::before, .jeu-carte--bientot .jeu-carte-visuel::after { display: none; }
    .jeu-carte--bientot a { color: var(--vert); font-weight: 600; }
    .jeux-note { max-width: 980px; margin: 30px auto 0; text-align: center; font-size: 0.85rem; color: var(--muted); }
</style>
<?php $extraHead = ob_get_clean(); ?>
<!DOCTYPE html>
<html lang="fr">
<?php include '../templates/head.php'; ?>
<body>

<?php
$activePage = 'jeux';
$navScrolled = 'scrolled';
include '../templates/navbar.php';
?>

<section class="page-hero page-hero--jeux">
    <div class="container">
        <h1><?= t('jeux_titre') ?></h1>
        <p><?= t('jeux_sous_titre') ?></p>
    </div>
</section>

<section class="section-jeux">
    <div class="container">
        <div class="jeux-grille">
            <?php foreach (JEUX as $id => $jeu): $resume = jeu_resume($id); ?>
            <a href="<?= BASE_URL ?>/<?= $jeu['page'] ?>" class="jeu-carte">
                <div class="jeu-carte-visuel"><span aria-hidden="true"><?= $jeu['icone'] ?></span></div>
                <div class="jeu-carte-corps">
                    <h2><?= htmlspecialchars($jeu['titre']) ?></h2>
                    <p><?= htmlspecialchars($jeu['description']) ?></p>
                    <div class="jeu-carte-stats">
                        <?php if ($resume['premier']): ?>
                            <span>🏆 Record : <?= $resume['premier']['score'] ?> <?= $jeu['unite'] ?> par <?= htmlspecialchars($resume['premier']['pseudo']) ?></span>
                            <span>👥 <?= $resume['joueurs'] ?> joueur<?= $resume['joueurs'] > 1 ? 's' : '' ?></span>
                        <?php else: ?>
                            <span>🏆 Aucun score : soyez le premier !</span>
                        <?php endif; ?>
                    </div>
                    <span class="jeu-carte-bouton">Jouer →</span>
                </div>
            </a>
            <?php endforeach; ?>

            <div class="jeu-carte jeu-carte--bientot">
                <div class="jeu-carte-visuel"><span aria-hidden="true">🎲</span></div>
                <div class="jeu-carte-corps">
                    <h2>D'autres jeux arrivent…</h2>
                    <p>De nouveaux jeux viendront s'ajouter ici. Une idée de jeu ? <a href="<?= BASE_URL ?>/contact.php">Proposez-la au club</a> !</p>
                </div>
            </div>
        </div>
        <p class="jeux-note">Un simple pseudo suffit pour jouer et apparaître au classement de chaque jeu.</p>
    </div>
</section>

<?php include '../templates/footer.php'; ?>

<script src="<?= ressource('js/main.js') ?>"></script>
</body>
</html>
