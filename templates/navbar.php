<nav class="navbar <?= $navScrolled ?? '' ?>" id="navbar">
    <div class="nav-container">
        <a href="/cat-tennis/index.php" class="nav-logo">CAT</a>
        <ul class="nav-links">
            <li><a href="/cat-tennis/index.php" <?= ($activePage ?? '') === 'accueil' ? 'class="active"' : '' ?>>Accueil</a></li>
            <li><a href="/cat-tennis/club.php" <?= ($activePage ?? '') === 'club' ? 'class="active"' : '' ?>>Le club</a></li>
            <li><a href="/cat-tennis/actualites.php" <?= ($activePage ?? '') === 'actualites' ? 'class="active"' : '' ?>>Actualités</a></li>
            <li><a href="/cat-tennis/calendrier.php" <?= ($activePage ?? '') === 'calendrier' ? 'class="active"' : '' ?>>Calendrier</a></li>
            <li><a href="/cat-tennis/contact.php" <?= ($activePage ?? '') === 'contact' ? 'class="active"' : '' ?>>Contact</a></li>
        </ul>
    </div>
</nav>