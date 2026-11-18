// ── Personnages des jeux (style dessin animé / emoji) ───────────────────────
// Caricatures humoristiques, sans lien officiel avec les joueurs cités.
// Chaque personnage est décrit par quelques réglages ; PersonnagesTennis.dessiner() les dessine tous.
// Réutilisable par les futurs jeux de l'espace Jeux.
window.PersonnagesTennis = (function () {
    // ➕ Ajouter un personnage : une entrée ici.
    //   coiffure : court | mi-long | boucles | chignon | dreads
    //   expression : sourire | grand-sourire | concentre | cri
    //   tenue bicolore (facultatif) : maillot2 = manches et bande en biais, short2 = ceinture et bandes
    const LISTE = [
        { id: 'cat',      nom: 'Joueur du CAT',   court: 'CAT',      peau: '#F1C9A5', cheveux: '#7A4B22', coiffure: 'court',
          casquette: '#1B6B2F', maillot: '#F0C419', short: '#1B6B2F', expression: 'sourire' },
        { id: 'federer',  nom: 'Roger Federer',   court: 'Federer',  peau: '#F3CFB1', cheveux: '#5A3B24', coiffure: 'mi-long',
          bandeau: '#FFFFFF', maillot: '#FFFFFF', short: '#FFFFFF', expression: 'sourire' },
        { id: 'nadal',    nom: 'Rafael Nadal',    court: 'Nadal',    peau: '#E2A77C', cheveux: '#3B2618', coiffure: 'mi-long',
          bandana: '#FF8A00', maillot: '#A3E635', short: '#1F2937', debardeur: true, gaucher: true, expression: 'cri' },
        { id: 'djokovic', nom: 'Novak Djokovic',  court: 'Djokovic', peau: '#EBC1A0', cheveux: '#2B1D14', coiffure: 'court',
          maillot: '#C1272D', short: '#1E3A8A', expression: 'concentre' },
        { id: 'serena',   nom: 'Serena Williams', court: 'Serena',   peau: '#7A4B30', cheveux: '#1A1A1A', coiffure: 'chignon',
          maillot: '#E11D74', short: '#E11D74', expression: 'grand-sourire' },
        { id: 'alcaraz',  nom: 'Carlos Alcaraz',  court: 'Alcaraz',  peau: '#EDBE98', cheveux: '#4A2C1A', coiffure: 'boucles',
          maillot: '#38BDF8', short: '#0F172A', expression: 'grand-sourire' },
        { id: 'noah',     nom: 'Yannick Noah',    court: 'Noah',     peau: '#6B4026', cheveux: '#1A1A1A', coiffure: 'dreads',
          maillot: '#FFFFFF', short: '#0055A4', expression: 'grand-sourire' },
        { id: 'shelton',  nom: 'Ben Shelton',     court: 'Shelton',  peau: '#B27A52', cheveux: '#1F1510', coiffure: 'boucles',
          maillot: '#2563EB', maillot2: '#F472B6', short: '#F472B6', short2: '#2563EB', gaucher: true, expression: 'grand-sourire' },
    ];

    const CONTOUR = 'rgba(40,30,25,0.55)';

    function ellipse(ctx, x, y, rx, ry, couleur, contour) {
        ctx.beginPath(); ctx.ellipse(x, y, rx, ry, 0, 0, Math.PI * 2);
        ctx.fillStyle = couleur; ctx.fill();
        if (contour) { ctx.strokeStyle = CONTOUR; ctx.lineWidth = 1.2; ctx.stroke(); }
    }

    function rectArrondi(ctx, x, y, l, h, r, couleur) {
        ctx.beginPath(); ctx.roundRect(x, y, l, h, r);
        ctx.fillStyle = couleur; ctx.fill();
        ctx.strokeStyle = CONTOUR; ctx.lineWidth = 1.2; ctx.stroke();
    }

    // Raquette tenue à la main (mx, my), inclinée selon l'angle (radians)
    function raquette(ctx, mx, my, sens, angle) {
        ctx.save();
        ctx.translate(mx, my);
        ctx.rotate(angle * sens);
        ctx.strokeStyle = '#2B2B2B'; ctx.lineWidth = 2.6; ctx.lineCap = 'round';
        ctx.beginPath(); ctx.moveTo(0, 0); ctx.lineTo(sens * 5, -9); ctx.stroke();      // manche
        ctx.translate(sens * 8, -16);
        ctx.rotate(0.35 * sens);
        ctx.beginPath(); ctx.ellipse(0, 0, 6.5, 8.5, 0, 0, Math.PI * 2);
        ctx.fillStyle = 'rgba(255,255,255,0.35)'; ctx.fill();
        ctx.strokeStyle = '#D93A2B'; ctx.lineWidth = 2.4; ctx.stroke();
        ctx.strokeStyle = 'rgba(255,255,255,0.7)'; ctx.lineWidth = 0.7;               // cordage
        for (let i = -1; i <= 1; i++) {
            ctx.beginPath(); ctx.moveTo(i * 2.6, -7); ctx.lineTo(i * 2.6, 7); ctx.stroke();
            ctx.beginPath(); ctx.moveTo(-5.5, i * 3); ctx.lineTo(5.5, i * 3); ctx.stroke();
        }
        ctx.restore();
    }

    function cheveuxDerriere(ctx, p, x, ty, r) {
        ctx.fillStyle = p.cheveux;
        if (p.coiffure === 'mi-long') {
            ctx.beginPath(); ctx.ellipse(x, ty + r * 0.15, r * 1.12, r * 1.05, 0, 0, Math.PI * 2); ctx.fill();
        } else if (p.coiffure === 'dreads') {
            ctx.strokeStyle = p.cheveux; ctx.lineWidth = 3.2; ctx.lineCap = 'round';
            for (let i = 0; i < 4; i++) {
                const dx = r * (0.65 + i * 0.12);
                [-1, 1].forEach(c => { ctx.beginPath(); ctx.moveTo(x + c * dx * 0.8, ty - r * 0.3); ctx.lineTo(x + c * (dx + 2), ty + r * (0.9 + i * 0.12)); ctx.stroke(); });
            }
        } else if (p.coiffure === 'chignon') {
            ellipse(ctx, x, ty - r * 1.05, r * 0.5, r * 0.42, p.cheveux);
        }
    }

    function cheveuxDessus(ctx, p, x, ty, r) {
        ctx.fillStyle = p.cheveux;
        if (p.coiffure === 'boucles') {
            for (let i = -3; i <= 3; i++) ellipse(ctx, x + i * r * 0.3, ty - r * 0.72 + Math.abs(i) * r * 0.1, r * 0.3, r * 0.3, p.cheveux);
            return;
        }
        // Calotte sur le haut de la tête + petite frange
        ctx.beginPath(); ctx.arc(x, ty, r * 1.02, Math.PI * 1.05, Math.PI * 1.95); ctx.closePath(); ctx.fill();
        ctx.beginPath(); ctx.ellipse(x - r * 0.25, ty - r * 0.55, r * 0.55, r * 0.28, -0.3, 0, Math.PI * 2); ctx.fill();
        if (p.coiffure === 'mi-long') {                       // mèches sur les côtés
            ctx.beginPath(); ctx.ellipse(x - r * 0.92, ty + r * 0.1, r * 0.22, r * 0.55, 0.15, 0, Math.PI * 2); ctx.fill();
            ctx.beginPath(); ctx.ellipse(x + r * 0.92, ty + r * 0.1, r * 0.22, r * 0.55, -0.15, 0, Math.PI * 2); ctx.fill();
        }
    }

    function accessoires(ctx, p, x, ty, r) {
        if (p.bandeau || p.bandana) {
            const couleur = p.bandeau || p.bandana;
            ctx.fillStyle = couleur; ctx.strokeStyle = CONTOUR; ctx.lineWidth = 1;
            ctx.beginPath(); ctx.roundRect(x - r * 1.02, ty - r * 0.58, r * 2.04, r * 0.32, 2); ctx.fill(); ctx.stroke();
            if (p.bandana) {                                  // nœud et pans flottants
                ctx.beginPath(); ctx.moveTo(x + r * 0.95, ty - r * 0.45); ctx.lineTo(x + r * 1.55, ty - r * 0.15);
                ctx.lineTo(x + r * 1.35, ty + r * 0.05); ctx.closePath(); ctx.fill(); ctx.stroke();
            }
        }
        if (p.casquette) {
            ctx.fillStyle = p.casquette;
            ctx.beginPath(); ctx.arc(x, ty - r * 0.05, r * 1.05, Math.PI, Math.PI * 2); ctx.closePath(); ctx.fill();
            ctx.strokeStyle = CONTOUR; ctx.lineWidth = 1; ctx.stroke();
            ctx.beginPath(); ctx.ellipse(x, ty - r * 0.12, r * 1.15, r * 0.24, 0, 0, Math.PI * 2); ctx.fill(); ctx.stroke();   // visière
            ellipse(ctx, x, ty - r * 0.55, r * 0.22, r * 0.14, '#F0C419');   // petit écusson
        }
    }

    function visage(ctx, p, x, ty, r) {
        const yeuxY = ty + r * 0.08, ecart = r * 0.38;
        // Joues
        ctx.fillStyle = 'rgba(255,110,110,0.28)';
        [-1, 1].forEach(c => { ctx.beginPath(); ctx.ellipse(x + c * r * 0.58, ty + r * 0.38, r * 0.17, r * 0.11, 0, 0, Math.PI * 2); ctx.fill(); });
        // Yeux (ronds noirs avec reflet), fermés en criant
        [-1, 1].forEach(c => {
            if (p.expression === 'cri') {
                ctx.strokeStyle = '#2A1E18'; ctx.lineWidth = 1.6; ctx.lineCap = 'round';
                ctx.beginPath(); ctx.moveTo(x + c * ecart - r * 0.14, yeuxY - r * 0.06); ctx.lineTo(x + c * ecart + r * 0.14, yeuxY + r * 0.04); ctx.stroke();
                return;
            }
            ellipse(ctx, x + c * ecart, yeuxY, r * 0.13, r * 0.17, '#2A1E18');
            ellipse(ctx, x + c * ecart + r * 0.04, yeuxY - r * 0.06, r * 0.045, r * 0.05, '#FFFFFF');
        });
        // Sourcils concentrés
        if (p.expression === 'concentre' || p.expression === 'cri') {
            ctx.strokeStyle = '#2A1E18'; ctx.lineWidth = 1.5;
            [-1, 1].forEach(c => { ctx.beginPath(); ctx.moveTo(x + c * (ecart + r * 0.18), yeuxY - r * 0.3); ctx.lineTo(x + c * (ecart - r * 0.14), yeuxY - r * 0.2); ctx.stroke(); });
        }
        // Bouche
        const by = ty + r * 0.5;
        ctx.strokeStyle = '#2A1E18'; ctx.lineWidth = 1.5; ctx.lineCap = 'round';
        if (p.expression === 'cri') {                         // « Vamos ! »
            ellipse(ctx, x, by + r * 0.02, r * 0.2, r * 0.17, '#7A1F1F');
            ellipse(ctx, x, by + r * 0.1, r * 0.11, r * 0.06, '#E07A7A');
        } else if (p.expression === 'grand-sourire') {
            ctx.beginPath(); ctx.moveTo(x - r * 0.3, by - r * 0.06); ctx.quadraticCurveTo(x, by + r * 0.42, x + r * 0.3, by - r * 0.06); ctx.closePath();
            ctx.fillStyle = '#7A1F1F'; ctx.fill();
            ctx.fillStyle = '#FFFFFF'; ctx.fillRect(x - r * 0.24, by - r * 0.05, r * 0.48, r * 0.09);
        } else if (p.expression === 'concentre') {
            ctx.beginPath(); ctx.moveTo(x - r * 0.15, by + r * 0.04); ctx.lineTo(x + r * 0.15, by + r * 0.02); ctx.stroke();
        } else {
            ctx.beginPath(); ctx.arc(x, by - r * 0.08, r * 0.24, 0.2 * Math.PI, 0.8 * Math.PI); ctx.stroke();
        }
    }

    // Dessine un personnage centré en (x, y) — y = hauteur de la raquette / du corps.
    // options : echelle (1 par défaut), frappe (0 à 1 : geste de frappe en cours), marche (phase de course)
    function dessiner(ctx, x, y, p, options = {}) {
        const e = options.echelle || 1, frappe = options.frappe || 0, marche = options.marche || 0;
        const sens = p.gaucher ? -1 : 1;
        ctx.save();
        ctx.translate(x, y);
        ctx.scale(e, e);
        const r = 12, ty = -22;                                // rayon et centre de la tête

        // Ombre
        ctx.fillStyle = 'rgba(0,0,0,0.22)';
        ctx.beginPath(); ctx.ellipse(2, 16, 15, 5, 0, 0, Math.PI * 2); ctx.fill();
        // Jambes (elles bougent pendant la course)
        const pas = Math.sin(marche) * 2.5;
        rectArrondi(ctx, -7, 4 + pas, 5, 11, 2, p.peau);
        rectArrondi(ctx, 2, 4 - pas, 5, 11, 2, p.peau);
        ellipse(ctx, -4.5, 15 + pas, 4, 2.2, '#FFFFFF', true);  // chaussures
        ellipse(ctx, 4.5, 15 - pas, 4, 2.2, '#FFFFFF', true);
        // Short (ou jupe), avec ceinture et bandes sur les côtés si tenue bicolore (short2)
        rectArrondi(ctx, -9, 1, 18, 7, 3, p.short);
        if (p.short2) {
            ctx.save();
            ctx.beginPath(); ctx.roundRect(-9, 1, 18, 7, 3); ctx.clip();
            ctx.fillStyle = p.short2;
            ctx.fillRect(-9, 1, 18, 2);
            ctx.fillRect(-9, 1, 2.5, 7);
            ctx.fillRect(6.5, 1, 2.5, 7);
            ctx.restore();
        }
        // Maillot, avec une bande en biais si tenue bicolore (maillot2)
        rectArrondi(ctx, -10, -11, 20, 14, 5, p.maillot);
        if (p.maillot2) {
            ctx.save();
            ctx.beginPath(); ctx.roundRect(-10, -11, 20, 14, 5); ctx.clip();
            ctx.fillStyle = p.maillot2;
            ctx.beginPath(); ctx.moveTo(-10, -2); ctx.lineTo(10, -9); ctx.lineTo(10, -4.5); ctx.lineTo(-10, 2.5); ctx.closePath(); ctx.fill();
            ctx.restore();
            ctx.beginPath(); ctx.roundRect(-10, -11, 20, 14, 5);
            ctx.strokeStyle = CONTOUR; ctx.lineWidth = 1.2; ctx.stroke();
        }

        // Bras de chaque côté du torse : avant-bras couleur peau + manche courte (sauf débardeur)
        const manche = p.maillot2 || p.maillot;
        function bras(cx, longueur) {
            rectArrondi(ctx, cx - 2.5, -9, 5, longueur, 2.5, p.peau);
            if (!p.debardeur) rectArrondi(ctx, cx - 3, -9.5, 6, 5.5, 2.5, manche);
        }
        bras(-sens * 12, 11);                                  // bras libre
        ellipse(ctx, -sens * 12, 2, 2.6, 2.6, p.peau, true);
        // Bras qui tient la raquette (geste de frappe : la raquette balaie vers l'avant)
        bras(sens * 12, 9);
        const angle = -0.25 + Math.sin(frappe * Math.PI) * 1.4;
        raquette(ctx, sens * 12, -1, sens, angle);
        ellipse(ctx, sens * 12, -1, 2.6, 2.6, p.peau, true);   // main

        // Tête
        cheveuxDerriere(ctx, p, 0, ty, r);
        ellipse(ctx, 0, ty, r, r * 0.98, p.peau, true);
        cheveuxDessus(ctx, p, 0, ty, r);
        accessoires(ctx, p, 0, ty, r);
        visage(ctx, p, 0, ty, r);
        ctx.restore();
    }

    function trouver(id) {
        return LISTE.find(p => p.id === id) || LISTE[0];
    }

    // Personnage au hasard, différent de celui donné
    function auHasard(sauf) {
        const autres = LISTE.filter(p => p.id !== sauf);
        return autres[Math.floor(Math.random() * autres.length)];
    }

    return { liste: LISTE, dessiner, trouver, auHasard };
})();
