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
