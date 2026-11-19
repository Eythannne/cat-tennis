// ── Page Pédagogie : champs conditionnels, recherche, confirmations, calendrier interne ──
(function () {
    // Champs affichés seulement si une case est cochée (data-affiche="id") ou si un bouton radio
    // précis est choisi (data-affiche="id" data-si="valeur")
    document.querySelectorAll('[data-affiche]').forEach(champ => {
        const cible = document.getElementById(champ.dataset.affiche);
        if (!cible) return;
        const groupe = champ.type === 'radio' ? document.querySelectorAll(`input[name="${champ.name}"]`) : [champ];
        const actualiser = () => {
            const visible = champ.type === 'radio'
                ? [...groupe].some(r => r.checked && r.value === champ.dataset.si)
                : champ.checked;
            cible.hidden = !visible;
            if (visible) cible.querySelector('input, textarea, select')?.focus?.({ preventScroll: true });
        };
        groupe.forEach(el => el.addEventListener('change', actualiser));
    });

    // Recherche dans une liste (sans tenir compte des accents ni des majuscules) + filtre par catégorie
    const sansAccents = t => t.normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase();
    document.querySelectorAll('.peda-recherche').forEach(champ => {
        const filtres = document.querySelector('[data-filtre-categorie]');
        let categorie = '';
        const filtrer = () => {
            const mots = sansAccents(champ.value.trim());
            document.querySelectorAll(champ.dataset.cible).forEach(el => {
                const okTexte = !mots || sansAccents(el.textContent).includes(mots);
                const okCategorie = !categorie || el.dataset.categorie === categorie;
                el.hidden = !(okTexte && okCategorie);
            });
        };
        champ.addEventListener('input', filtrer);
        filtres?.querySelectorAll('button').forEach(bouton => bouton.addEventListener('click', () => {
            categorie = bouton.dataset.categorie;
            filtres.querySelectorAll('button').forEach(b => b.classList.toggle('actif', b === bouton));
            filtrer();
        }));
    });

    // Confirmation avant les actions importantes (suppression, publication)
    document.querySelectorAll('form[data-confirmer]').forEach(form => {
        form.addEventListener('submit', e => { if (!confirm(form.dataset.confirmer)) e.preventDefault(); });
    });

    // Calendrier interne : même présentation que le calendrier public ;
    // un clic sur une animation ouvre son formulaire de modification
    const zone = document.getElementById('calendar');
    if (zone && window.FullCalendar) {
        const petitEcran = window.innerWidth < 700;
        const calendrier = new FullCalendar.Calendar(zone, {
            locale: 'fr',
            firstDay: 1,
            allDayText: 'Toute la journée',
            headerToolbar: petitEcran
                ? { left: 'prev,next', center: 'title', right: 'today' }
                : { left: 'prev,next today', center: 'title', right: 'dayGridMonth,listMonth' },
            initialView: petitEcran ? 'listMonth' : 'dayGridMonth',
            buttonText: { today: "Aujourd'hui", month: 'Mois', list: 'Liste' },
            noEventsText: 'Aucune animation planifiée ce mois-ci',
            events: zone.dataset.source,
            eventClick: info => {
                info.jsEvent.preventDefault();
                window.location = zone.dataset.lien + '&modifier=' + encodeURIComponent(info.event.id) + '#formulaire';
            },
            height: 'auto',
        });
        calendrier.render();
    }
})();
