// Barre d'outils de mise en forme pour le contenu des articles.
// Insère la syntaxe comprise par format_article() (src/init.php).
(function() {
    const zone = document.getElementById('contenu');
    if (!zone) return;

    // Entoure la sélection (ex : **gras**)
    function entourer(avant, apres, exemple) {
        const { selectionStart: d, selectionEnd: f, value } = zone;
        const texte = value.slice(d, f) || exemple;
        zone.setRangeText(avant + texte + apres, d, f, 'end');
        if (d === f) zone.setSelectionRange(d + avant.length, d + avant.length + texte.length);
        zone.focus();
    }

    // Ajoute un préfixe au début de chaque ligne sélectionnée (ex : "- " pour une liste)
    function prefixer(prefixe) {
        const { selectionStart: d, selectionEnd: f, value } = zone;
        const debutLigne = value.lastIndexOf('\n', d - 1) + 1;
        const bloc = value.slice(debutLigne, f) || '';
        let n = 0;
        const nouveau = bloc.split('\n').map(l => {
            n++;
            return (typeof prefixe === 'function' ? prefixe(n) : prefixe) + l;
        }).join('\n');
        zone.setRangeText(nouveau, debutLigne, f, 'end');
        zone.focus();
    }

    const actions = {
        gras:     () => entourer('**', '**', 'texte en gras'),
        italique: () => entourer('*', '*', 'texte en italique'),
        titre:    () => prefixer('## '),
        liste:    () => prefixer('- '),
        numeros:  () => prefixer(n => n + '. '),
        lien:     () => {
            const url = prompt('Adresse du lien (commence par https://) :', 'https://');
            if (url && /^(https?:\/\/|mailto:)/.test(url)) entourer('[', '](' + url + ')', 'texte du lien');
        },
    };

    document.querySelectorAll('[data-format]').forEach(btn => {
        btn.addEventListener('click', () => actions[btn.dataset.format]());
    });

    // Raccourcis clavier Ctrl+B / Ctrl+I
    zone.addEventListener('keydown', e => {
        if (!(e.ctrlKey || e.metaKey)) return;
        if (e.key === 'b') { e.preventDefault(); actions.gras(); }
        if (e.key === 'i') { e.preventDefault(); actions.italique(); }
    });
})();

// ✨ Générer : l'IA (Gemini) rédige le contenu à partir du titre et des notes brutes
// (admin/ajax/generer-article.php). Le texte obtenu reste modifiable avant publication.
(function() {
    const bouton = document.getElementById('generer-ia');
    if (!bouton) return;
    const form   = bouton.closest('form');
    const zone   = document.getElementById('contenu');
    const notes  = document.getElementById('notes');
    const statut = document.getElementById('ia-statut');

    function afficher(message, type) {
        statut.textContent = message;
        statut.className = 'ia-statut ia-' + type;
        statut.hidden = false;
    }

    bouton.addEventListener('click', async () => {
        const titre = form.elements.titre.value.trim();
        if (!titre || !notes.value.trim()) {
            afficher('⚠️ Renseignez le titre et quelques notes avant de générer.', 'erreur');
            (titre ? notes : form.elements.titre).focus();
            return;
        }
        if (zone.value.trim() && !confirm('Remplacer le contenu actuel par un nouveau texte généré ?')) return;

        const donnees = new FormData();
        donnees.append('csrf_token', form.elements.csrf_token.value);
        donnees.append('titre', titre);
        donnees.append('notes', notes.value);

        const libelle = bouton.textContent;
        bouton.disabled = true;
        bouton.textContent = '⏳ Génération…';
        afficher('Rédaction en cours, cela peut prendre une vingtaine de secondes…', 'info');
        try {
            const reponse = await fetch('ajax/generer-article.php', { method: 'POST', body: donnees, credentials: 'same-origin' });
            let resultat;
            try { resultat = await reponse.json(); }
            catch { resultat = { erreur: 'Réponse inattendue du serveur. Rechargez la page et réessayez.' }; }

            if (resultat.texte) {
                zone.value = resultat.texte;
                zone.scrollTop = 0;
                zone.focus();
                zone.setSelectionRange(0, 0);
                afficher('✅ Article généré : relisez-le et modifiez-le si besoin avant de publier.', 'succes');
            } else {
                afficher('⚠️ ' + (resultat.erreur || 'La génération a échoué.'), 'erreur');
            }
        } catch {
            afficher('⚠️ Connexion impossible. Vérifiez votre connexion internet et réessayez.', 'erreur');
        } finally {
            bouton.disabled = false;
            bouton.textContent = libelle;
        }
    });
})();
