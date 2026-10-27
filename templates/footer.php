<footer class="footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-col">
                <a href="<?= BASE_URL ?>/index.php" class="nav-logo">
                    <img src="<?= BASE_URL ?>/favicon.png" alt="CAT" class="nav-logo-img">
                </a>
                <p><?= htmlspecialchars(param('nom_club', 'Club Aulnaysien de Tennis')) ?><br><?= htmlspecialchars(param('adresse', 'Aulnay-sous-Bois, 93600')) ?></p>
            </div>
            <div class="footer-col">
                <h4>Navigation</h4>
                <ul>
                    <li><a href="<?= BASE_URL ?>/index.php">Accueil</a></li>
                    <li><a href="<?= BASE_URL ?>/club.php">Le club</a></li>
                    <li><a href="<?= BASE_URL ?>/actualites.php">Actualités</a></li>
                    <li><a href="<?= BASE_URL ?>/calendrier.php">Calendrier</a></li>
                    <li><a href="<?= BASE_URL ?>/contact.php">Contact</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Contact</h4>
                <p>
                    <?= htmlspecialchars(param('email_contact', 'contact@cat-tennis.fr')) ?>
                    <?php if (param('telephone')): ?><br><?= htmlspecialchars(param('telephone')) ?><?php endif; ?>
                </p>
            </div>
            <div class="footer-col">
                <h4>Liens utiles</h4>
                <ul>
                    <li><a href="https://tenup.fft.fr/" target="_blank" rel="noopener">Ten'up</a></li>
                    <li><a href="<?= BASE_URL ?>/mentions-legales.php">Mentions légales</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>© <?= date('Y') ?> <?= htmlspecialchars(param('nom_club', 'Club Aulnaysien de Tennis')) ?> — Tous droits réservés</p>
        </div>
    </div>
</footer>
