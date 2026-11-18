// ── Jeu « Un petit échange » (espace Jeux) ──────────────────────────────────
// Court vu de dessus : le joueur (en bas) se déplace de gauche à droite pour renvoyer la balle,
// l'ordinateur (en haut) la renvoie toujours. La balle accélère à chaque échange ;
// la partie s'arrête quand le joueur la rate. Score = nombre d'échanges réussis.
// Le serveur (api/jeux.php) donne un jeton au début de la partie et vérifie le score à la fin.
(function () {
    const zone = document.getElementById('jeu');
    const API = zone.dataset.api, JEU = zone.dataset.jeu;   // identifiant du jeu (voir JEUX dans src/jeux.php)

    // ── Réglages du jeu (unités du canvas : 400 × 600) ──
    const L = 400, H = 600;
    const COURT    = { gauche: 30, droite: 370, haut: 24, bas: 576, filet: 300 };
    const JOUEUR_Y = 540, CPU_Y = 60;
    const RAQUETTE = 32;          // demi-largeur de la zone de frappe
    const BALLE_R  = 6;
    const VITESSE_JOUEUR = 460;   // déplacement du joueur (unités / s)
    const VITESSE_DEPART = 290;   // vitesse de la balle au premier échange
    const VITESSE_MAX    = 860;
    const ACCELERATION   = 1.055; // ×5,5 % à chaque échange réussi
    const ANGLE_MAX      = 55 * Math.PI / 180;
    const ECHELLE_PERSO  = 1.25;  // taille des personnages sur le court (la raquette couvre la zone de frappe)

    // ── Éléments de la page ──
    const canvas  = document.getElementById('jeu-canvas');
    const ctx     = canvas.getContext('2d');
    const ecranAccueil = document.getElementById('jeu-accueil');
    const ecranFin     = document.getElementById('jeu-fin');
    const champPseudo  = document.getElementById('jeu-pseudo');
    const erreurPseudo = document.getElementById('jeu-pseudo-erreur');
    const affScore     = document.getElementById('jeu-score');
    const affRecord    = document.getElementById('jeu-record');
    const listeScores  = document.getElementById('jeu-classement');

    // ── État ──
    let etat = 'accueil';          // accueil | decompte | jeu | rate | fin
    let joueur, cpu, balle, traine, score, vitesse, jeton, pseudo;
    let decompte = 0, reprise = false, effets = [], derniereFrame = 0;
    const touches = { gauche: false, droite: false };
    let cibleDoigt = null;         // position visée au doigt (glisser sur le court)

    const stockage = {
        lire(cle) { try { return localStorage.getItem(cle); } catch { return null; } },
        ecrire(cle, valeur) { try { localStorage.setItem(cle, valeur); } catch {} },
    };
    champPseudo.value = stockage.lire('jeux_pseudo') || '';
    let record = parseInt(stockage.lire('jeu_' + JEU + '_record') || '0', 10);
    affRecord.textContent = record;

    // ── Choix du personnage (voir personnages.js) ──
    const Persos = window.PersonnagesTennis;
    let persoJoueur = Persos.trouver(stockage.lire('jeux_perso')), persoCpu = Persos.auHasard(persoJoueur.id);
    const grillePersos = document.getElementById('jeu-persos');
    const nomPerso = document.getElementById('jeu-perso-nom');

    function choisirPerso(perso) {
        persoJoueur = perso;
        stockage.ecrire('jeux_perso', perso.id);
        nomPerso.textContent = perso.nom;
        grillePersos.querySelectorAll('button').forEach(b => {
            const choisi = b.dataset.perso === perso.id;
            b.classList.toggle('choisi', choisi);
            b.setAttribute('aria-checked', choisi);
        });
    }

    Persos.liste.forEach(perso => {
        const bouton = document.createElement('button');
        bouton.type = 'button';
        bouton.dataset.perso = perso.id;
        bouton.setAttribute('role', 'radio');
        bouton.title = perso.nom;
        bouton.setAttribute('aria-label', perso.nom);
        const apercu = document.createElement('canvas');
        apercu.width = 56; apercu.height = 64;
        const c = apercu.getContext('2d');
        Persos.dessiner(c, 28, 44, perso, { echelle: 1.05 });
        const nom = document.createElement('span');
        nom.textContent = perso.court;
        bouton.append(apercu, nom);
        bouton.addEventListener('click', () => choisirPerso(perso));
        grillePersos.appendChild(bouton);
    });
    choisirPerso(persoJoueur);

    // ── Appels au serveur ──
    async function api(action, donnees = {}) {
        const corps = new URLSearchParams({ action, ...donnees });
        const reponse = await fetch(API, { method: 'POST', body: corps });
        return reponse.json();
    }

    async function chargerClassement() {
        try {
            const reponse = await fetch(API + '?action=classement&jeu=' + encodeURIComponent(JEU));
            afficherClassement((await reponse.json()).classement || []);
        } catch {
            listeScores.innerHTML = '<li class="jeu-vide">Classement indisponible pour le moment.</li>';
        }
    }

    function afficherClassement(lignes, pseudoMisEnAvant = '') {
        if (!lignes.length) {
            listeScores.innerHTML = '<li class="jeu-vide">Aucun score pour l\'instant : à vous de jouer !</li>';
            return;
        }
        const medailles = ['🥇', '🥈', '🥉'];
        listeScores.innerHTML = '';
        lignes.forEach((l, i) => {
            const li = document.createElement('li');
            if (pseudoMisEnAvant && l.pseudo.toLowerCase() === pseudoMisEnAvant.toLowerCase()) li.className = 'moi';
            li.innerHTML = '<span class="rang"></span><span class="pseudo"></span><span class="points"></span>';
            li.querySelector('.rang').textContent = medailles[i] || (i + 1);
            li.querySelector('.pseudo').textContent = l.pseudo;
            li.querySelector('.points').textContent = l.score;
            li.title = 'Le ' + l.date;
            listeScores.appendChild(li);
        });
    }

    // ── Déroulement d'une partie ──
    async function lancerPartie() {
        const valeur = champPseudo.value.trim().replace(/\s+/g, ' ');
        if (valeur.length < 2 || valeur.length > 16) {
            erreurPseudo.textContent = 'Choisissez un pseudo de 2 à 16 caractères.';
            champPseudo.focus();
            return;
        }
        erreurPseudo.textContent = '';
        pseudo = valeur;
        stockage.ecrire('jeux_pseudo', pseudo);

        jeton = null;
        try { jeton = (await api('debut', { jeu: JEU })).jeton || null; } catch {}

        persoCpu = Persos.auHasard(persoJoueur.id);      // nouvel adversaire à chaque partie
        joueur = { x: L / 2, frappe: 0, marche: 0 };
        cpu    = { x: L / 2, frappe: 0, marche: 0 };
        balle  = { x: L / 2, y: CPU_Y + 18, vx: 0, vy: 0 };
        traine = [];
        effets = [];
        score  = 0;
        reprise = false;
        vitesse = VITESSE_DEPART;
        affScore.textContent = '0';
        ecranAccueil.hidden = true;
        ecranFin.hidden = true;
        etat = 'decompte';
        decompte = 3;
        canvas.focus();
    }

    // Service de l'ordinateur vers le joueur, légèrement en biais
    function servir() {
        const angle = (Math.random() - 0.5) * 0.6;
        balle.vx = vitesse * Math.sin(angle);
        balle.vy = vitesse * Math.cos(angle);
        cpu.frappe = 1;
        etat = 'jeu';
    }

    async function finirPartie() {
        etat = 'fin';
        const nouveauRecord = score > record;
        if (nouveauRecord) { record = score; stockage.ecrire('jeu_' + JEU + '_record', record); affRecord.textContent = record; }

        document.getElementById('jeu-fin-score').textContent = score;
        document.getElementById('jeu-fin-titre').textContent =
            score === 0 ? 'Raté !' : nouveauRecord ? '🎉 Nouveau record !' : score >= 20 ? 'Bel échange !' : 'Balle out !';
        const statut = document.getElementById('jeu-fin-statut');
        statut.textContent = score > 0 ? 'Enregistrement du score…' : 'Renvoyez au moins une balle pour entrer au classement.';
        ecranFin.hidden = false;
        document.getElementById('jeu-rejouer').focus();

        if (score === 0) return;
        if (!jeton) { statut.textContent = 'Score non enregistré (connexion au serveur impossible).'; return; }
        try {
            const r = await api('score', { jeton, pseudo, score });
            if (r.erreur) { statut.textContent = r.erreur; return; }
            // r.rang = place du meilleur score de ce pseudo (pas forcément celui de cette partie)
            const place = r.rang ? (r.rang === 1 ? '1er' : r.rang + 'e') : null;
            statut.textContent = !place ? 'Score enregistré. Encore un effort pour entrer dans le top 15 !'
                : score >= r.record ? 'Score enregistré : vous êtes ' + place + ' du classement !'
                : 'Score enregistré. Avec votre record de ' + r.record + ', vous êtes ' + place + ' du classement.';
            afficherClassement(r.classement || [], pseudo);
        } catch {
            statut.textContent = 'Score non enregistré (connexion au serveur impossible).';
        }
    }

    // ── Physique ──
    // Abscisse de la balle quand elle atteindra la hauteur y, rebonds sur les côtés compris
    function predireX(y) {
        const t = (y - balle.y) / balle.vy;
        if (t <= 0) return balle.x;
        const min = COURT.gauche + BALLE_R, largeur = COURT.droite - BALLE_R - min;
        let x = (balle.x + balle.vx * t - min) % (2 * largeur);
        if (x < 0) x += 2 * largeur;
        return min + (x > largeur ? 2 * largeur - x : x);
    }

    function deplacer(objet, cible, vitesseMax, dt) {
        const ecart = cible - objet.x;
        const pas = Math.sign(ecart) * Math.min(Math.abs(ecart), vitesseMax * dt);
        objet.x = Math.max(COURT.gauche, Math.min(COURT.droite, objet.x + pas));
        objet.marche += Math.abs(pas) * 0.22;          // anime les jambes quand le personnage court
    }

    function ajouterEffet(texte, x, y, couleur = '#fff') {
        effets.push({ texte, x, y, couleur, vie: 1 });
    }

    function miseAJour(dt) {
        // Geste de frappe en cours (1 → 0 en un quart de seconde)
        if (joueur) [joueur, cpu].forEach(o => { o.frappe = Math.max(0, o.frappe - dt * 4); });

        // Joueur : clavier ou doigt
        if (etat === 'jeu' || etat === 'decompte' || etat === 'rate') {
            if (cibleDoigt !== null) deplacer(joueur, cibleDoigt, VITESSE_JOUEUR, dt);
            else if (touches.gauche !== touches.droite) deplacer(joueur, touches.gauche ? 0 : L, VITESSE_JOUEUR, dt);
        }

        if (etat === 'decompte') {
            decompte -= dt;
            if (decompte <= 0) {
                if (reprise) { reprise = false; etat = 'jeu'; } else servir();
            }
            return;
        }
        if (etat !== 'jeu' && etat !== 'rate') return;

        // Balle + rebonds sur les côtés du court
        balle.x += balle.vx * dt;
        balle.y += balle.vy * dt;
        if (balle.x < COURT.gauche + BALLE_R)  { balle.x = COURT.gauche + BALLE_R;  balle.vx = Math.abs(balle.vx); }
        if (balle.x > COURT.droite - BALLE_R)  { balle.x = COURT.droite - BALLE_R;  balle.vx = -Math.abs(balle.vx); }
        traine.push({ x: balle.x, y: balle.y });
        if (traine.length > 8) traine.shift();

        // L'ordinateur suit la balle quand elle monte, revient au centre sinon
        const cibleCpu = balle.vy < 0 ? predireX(CPU_Y) : L / 2;
        deplacer(cpu, cibleCpu, Math.max(380, vitesse * 1.1), dt);

        // Renvoi de l'ordinateur (il ne rate jamais) vers un point du camp du joueur,
        // de plus en plus éloigné du centre au fil des échanges
        if (balle.vy < 0 && balle.y - BALLE_R <= CPU_Y + 10) {
            cpu.x = balle.x;
            cpu.frappe = 1;
            const ecart = Math.min(1, 0.3 + score * 0.04) * (COURT.droite - COURT.gauche) / 2;
            const cible = L / 2 + (Math.random() * 2 - 1) * ecart;
            let angle = Math.atan2(cible - balle.x, JOUEUR_Y - balle.y);
            angle = Math.max(-ANGLE_MAX, Math.min(ANGLE_MAX, angle));
            balle.vx = vitesse * Math.sin(angle);
            balle.vy = vitesse * Math.cos(angle);
        }

        // Frappe du joueur : l'angle dépend de l'endroit où la balle touche la raquette
        if (etat === 'jeu' && balle.vy > 0 && balle.y + BALLE_R >= JOUEUR_Y - 10) {
            const decalage = (balle.x - joueur.x) / RAQUETTE;
            if (Math.abs(balle.x - joueur.x) <= RAQUETTE + BALLE_R) {
                score++;
                joueur.frappe = 1;
                affScore.textContent = score;
                vitesse = Math.min(VITESSE_MAX, vitesse * ACCELERATION);
                const angle = Math.max(-1, Math.min(1, decalage)) * (50 * Math.PI / 180);
                balle.vx = vitesse * Math.sin(angle);
                balle.vy = -vitesse * Math.cos(angle);
                balle.y = JOUEUR_Y - 10 - BALLE_R;
                ajouterEffet('+1', joueur.x, JOUEUR_Y - 58, '#F0C419');
                if (score % 5 === 0 && vitesse < VITESSE_MAX) ajouterEffet('Ça accélère !', L / 2, H / 2 + 40);
            } else {
                etat = 'rate';      // la balle file : fin de partie quand elle sort de l'écran
            }
        }
        if (etat === 'rate' && balle.y > H + 30) finirPartie();

        effets.forEach(e => { e.vie -= dt * 1.2; e.y -= dt * 30; });
        effets = effets.filter(e => e.vie > 0);
    }

    // ── Dessin ──
    function dessinerCourt() {
        ctx.fillStyle = '#17532E';
        ctx.fillRect(0, 0, L, H);
        ctx.fillStyle = '#2E7D4A';
        ctx.fillRect(COURT.gauche, COURT.haut, COURT.droite - COURT.gauche, COURT.bas - COURT.haut);

        ctx.strokeStyle = 'rgba(255,255,255,0.9)';
        ctx.lineWidth = 3;
        ctx.strokeRect(COURT.gauche, COURT.haut, COURT.droite - COURT.gauche, COURT.bas - COURT.haut);
        ctx.lineWidth = 2;
        const simpleG = COURT.gauche + 40, simpleD = COURT.droite - 40;
        ctx.beginPath();
        ctx.moveTo(simpleG, COURT.haut); ctx.lineTo(simpleG, COURT.bas);          // couloirs
        ctx.moveTo(simpleD, COURT.haut); ctx.lineTo(simpleD, COURT.bas);
        ctx.moveTo(simpleG, 170); ctx.lineTo(simpleD, 170);                       // lignes de service
        ctx.moveTo(simpleG, 430); ctx.lineTo(simpleD, 430);
        ctx.moveTo(L / 2, 170); ctx.lineTo(L / 2, 430);                           // ligne médiane
        ctx.stroke();

        // Filet avec son ombre et ses poteaux
        ctx.fillStyle = 'rgba(0,0,0,0.18)';
        ctx.fillRect(COURT.gauche - 8, COURT.filet + 2, COURT.droite - COURT.gauche + 16, 6);
        ctx.fillStyle = '#F4F4F4';
        ctx.fillRect(COURT.gauche - 8, COURT.filet - 2, COURT.droite - COURT.gauche + 16, 4);
        ctx.fillStyle = '#1B1B1B';
        ctx.fillRect(COURT.gauche - 12, COURT.filet - 5, 6, 10);
        ctx.fillRect(COURT.droite + 6, COURT.filet - 5, 6, 10);
    }

    // Personnage (voir personnages.js) avec son geste de frappe et sa course
    function dessinerJoueur(objet, y, perso) {
        Persos.dessiner(ctx, objet.x, y, perso, { echelle: ECHELLE_PERSO, frappe: objet.frappe, marche: objet.marche });
    }

    // Petite étiquette avec le nom de l'adversaire
    function etiquette(texte, x, y) {
        ctx.font = 'bold 11px Outfit, sans-serif';
        ctx.textAlign = 'center';
        ctx.lineWidth = 3; ctx.strokeStyle = 'rgba(0,0,0,0.55)';
        ctx.strokeText(texte, x, y);
        ctx.fillStyle = '#FFFFFF';
        ctx.fillText(texte, x, y);
    }

    function dessinerBalle() {
        traine.forEach((p, i) => {
            ctx.fillStyle = 'rgba(232,245,80,' + (i / traine.length) * 0.35 + ')';
            ctx.beginPath(); ctx.arc(p.x, p.y, BALLE_R * (0.4 + i / traine.length * 0.5), 0, Math.PI * 2); ctx.fill();
        });
        ctx.fillStyle = 'rgba(0,0,0,0.25)';
        ctx.beginPath(); ctx.arc(balle.x + 4, balle.y + 5, BALLE_R, 0, Math.PI * 2); ctx.fill();
        ctx.fillStyle = '#DDF238';
        ctx.beginPath(); ctx.arc(balle.x, balle.y, BALLE_R, 0, Math.PI * 2); ctx.fill();
        ctx.strokeStyle = 'rgba(255,255,255,0.85)'; ctx.lineWidth = 1.2;
        ctx.beginPath(); ctx.arc(balle.x - 5, balle.y, BALLE_R - 1, -0.9, 0.9); ctx.stroke();
    }

    function dessiner() {
        dessinerCourt();
        if (!joueur) {                     // écran d'accueil : décor seul
            dessinerJoueur({ x: L / 2 }, CPU_Y, persoCpu);
            dessinerJoueur({ x: L / 2 }, JOUEUR_Y, persoJoueur);
            return;
        }
        dessinerJoueur(cpu, CPU_Y, persoCpu);
        etiquette(persoCpu.court, cpu.x, CPU_Y + 37);
        dessinerJoueur(joueur, JOUEUR_Y, persoJoueur);
        if (etat !== 'decompte' || reprise) dessinerBalle();

        ctx.textAlign = 'center';
        effets.forEach(e => {
            ctx.globalAlpha = Math.max(0, e.vie);
            ctx.fillStyle = e.couleur;
            ctx.font = 'bold 20px Outfit, sans-serif';
            ctx.fillText(e.texte, e.x, e.y);
        });
        ctx.globalAlpha = 1;

        if (etat === 'decompte') {
            ctx.fillStyle = 'rgba(0,0,0,0.35)';
            ctx.fillRect(0, H / 2 - 62, L, 124);
            ctx.fillStyle = '#fff';
            ctx.font = 'bold 56px Outfit, sans-serif';
            ctx.fillText(decompte > 0.15 ? Math.ceil(decompte) : 'Go !', L / 2, H / 2 + 12);
            ctx.font = '600 15px Outfit, sans-serif';
            ctx.fillStyle = '#F0C419';
            ctx.fillText(persoJoueur.court + ' contre ' + persoCpu.court, L / 2, H / 2 + 44);
        }
    }

    function boucle(temps) {
        const dt = Math.min(0.033, (temps - derniereFrame) / 1000 || 0);
        derniereFrame = temps;
        miseAJour(dt);
        dessiner();
        requestAnimationFrame(boucle);
    }

    // Onglet masqué pendant une partie : compte à rebours au retour, puis la balle repart
    // de là où elle était, dans la même direction
    document.addEventListener('visibilitychange', () => {
        if (!document.hidden && etat === 'jeu') { etat = 'decompte'; decompte = 3; reprise = true; }
    });

    // ── Commandes ──
    const CLAVIER = { ArrowLeft: 'gauche', ArrowRight: 'droite', q: 'gauche', a: 'gauche', d: 'droite' };
    document.addEventListener('keydown', e => {
        const sens = CLAVIER[e.key];
        if (sens && etat !== 'accueil' && etat !== 'fin') { touches[sens] = true; cibleDoigt = null; e.preventDefault(); }
        if (e.key === 'Enter' && etat === 'fin' && document.activeElement !== champPseudo) lancerPartie();
    });
    document.addEventListener('keyup', e => {
        const sens = CLAVIER[e.key];
        if (sens) touches[sens] = false;
    });

    // Glisser le doigt (ou la souris) sur le court
    function positionSurCourt(e) {
        const rect = canvas.getBoundingClientRect();
        return (e.clientX - rect.left) * (L / rect.width);
    }
    canvas.addEventListener('pointerdown', e => { cibleDoigt = positionSurCourt(e); canvas.setPointerCapture(e.pointerId); });
    canvas.addEventListener('pointermove', e => { if (cibleDoigt !== null) cibleDoigt = positionSurCourt(e); });
    ['pointerup', 'pointercancel'].forEach(t => canvas.addEventListener(t, () => { cibleDoigt = null; }));

    // Boutons ← → (téléphone) : appui maintenu
    document.querySelectorAll('[data-jeu-sens]').forEach(bouton => {
        const sens = bouton.dataset.jeuSens;
        const appui = e => { e.preventDefault(); touches[sens] = true; cibleDoigt = null; };
        const relache = () => { touches[sens] = false; };
        bouton.addEventListener('pointerdown', appui);
        ['pointerup', 'pointerleave', 'pointercancel'].forEach(t => bouton.addEventListener(t, relache));
    });

    document.getElementById('jeu-form').addEventListener('submit', e => { e.preventDefault(); lancerPartie(); });
    document.getElementById('jeu-rejouer').addEventListener('click', lancerPartie);
    document.getElementById('jeu-changer').addEventListener('click', () => {
        ecranFin.hidden = true;
        ecranAccueil.hidden = false;
        etat = 'accueil';
        champPseudo.focus();
    });

    chargerClassement();
    requestAnimationFrame(boucle);
})();
