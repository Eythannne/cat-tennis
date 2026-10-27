// Navbar sticky — change d'apparence au scroll
const navbar = document.getElementById('navbar');

window.addEventListener('scroll', () => {
    // .nav-fixe : page sans bandeau sombre en haut, la navbar doit rester opaque
    if (window.scrollY > 50 || navbar.classList.contains('nav-fixe')) {
        navbar.classList.add('scrolled');
    } else {
        navbar.classList.remove('scrolled');
    }
});

// Menu burger (mobile)
const navToggle = document.getElementById('nav-toggle');
if (navToggle) {
    navToggle.addEventListener('click', () => {
        const isOpen = navbar.classList.toggle('nav-open');
        navToggle.setAttribute('aria-expanded', isOpen);
        navToggle.setAttribute('aria-label', isOpen ? 'Fermer le menu' : 'Ouvrir le menu');
    });
}

// Dropdown "Mon compte"
const dropdownBtn  = document.querySelector('.nav-dropdown-btn');
const dropdownMenu = document.querySelector('.nav-dropdown-menu');
if (dropdownBtn && dropdownMenu) {
    dropdownBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        const isOpen = dropdownMenu.classList.toggle('open');
        dropdownBtn.setAttribute('aria-expanded', isOpen);
    });
    // Ferme si clic ailleurs
    document.addEventListener('click', () => {
        dropdownMenu.classList.remove('open');
        dropdownBtn.setAttribute('aria-expanded', 'false');
    });
    // Ferme si Échap
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            dropdownMenu.classList.remove('open');
            dropdownBtn.setAttribute('aria-expanded', 'false');
        }
    });
}

// Onglets actualités
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
        btn.classList.add('active');
        document.getElementById('tab-' + btn.dataset.tab).classList.add('active');
    });
});
