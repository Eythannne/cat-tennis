<nav class="navbar <?= $navScrolled ?? '' ?>" id="navbar">
    <div class="nav-container">
        <a href="/cat-tennis/index.php" class="nav-logo">CAT</a>
        <ul class="nav-links">
            <li><a href="/cat-tennis/index.php" <?= ($activePage ?? '') === 'accueil' ? 'class="active"' : '' ?>>Accueil</a></li>
            <li><a href="/cat-tennis/club.php" <?= ($activePage ?? '') === 'club' ? 'class="active"' : '' ?>>Le club</a></li>
            <li><a href="/cat-tennis/actualites.php" <?= ($activePage ?? '') === 'actualites' ? 'class="active"' : '' ?>>Actualités</a></li>
            <li><a href="/cat-tennis/calendrier.php" <?= ($activePage ?? '') === 'calendrier' ? 'class="active"' : '' ?>>Calendrier</a></li>
            <li><a href="/cat-tennis/contact.php" <?= ($activePage ?? '') === 'contact' ? 'class="active"' : '' ?>>Contact</a></li>
            <li class="nav-dropdown">
                <button class="nav-dropdown-btn" aria-expanded="false" aria-haspopup="true">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
                    Mon compte
                    <svg class="nav-chevron" xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                </button>
                <ul class="nav-dropdown-menu" role="menu">
                    <li role="menuitem"><a href="/cat-tennis/admin/login.php">🔐 Administration</a></li>
                    <li role="menuitem" class="nav-dropdown-disabled"><span>👤 Espace membre <span class="nav-badge">Bientôt</span></span></li>
                </ul>
            </li>
        </ul>
    </div>
</nav>