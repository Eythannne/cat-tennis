<?php
// ── Page Pédagogie (entraîneurs) : listes, validation et enregistrement ──────
// Page publique/pedagogie.php, onglets Animations, Calendrier et Exercices.
// ⚠ Accès ouvert à tous pour l'instant (site en phase de test) : les modifications sont limitées
//    par adresse IP et notées dans le Journal. À terme, réservé au rôle « peda » (voir docs/idee-a-ajouter.txt).

const PEDA_ONGLETS = [
    'animations' => '🎉 Animations',
    'calendrier' => '📅 Calendrier',
    'exercices'  => '🎾 Exercices',
];
const PEDA_SECTIONS_EXERCICES = [
    'bibliotheque'  => '📚 Bibliothèque d\'exercices',
    'programmation' => '🗓️ Programmation annuelle',
    'criteres'      => '🏅 Critères de passage',
];

const PEDA_CATEGORIES = ['tennistique' => 'Tennistique', 'extrasportif' => 'Extrasportif', 'autre' => 'Autre'];
const PEDA_LIEUX = [
    'courts_couverts'   => 'Courts couverts',
    'courts_exterieurs' => 'Courts extérieurs',
    'club_house'        => 'Club-house',
    'exterieur'         => 'Hors du club',
    'autre'             => 'Autre lieu',
];
// Niveaux de jeu : libellé et couleur de la pastille
const PEDA_NIVEAUX = [
    'blanc'  => ['Blanc', '#FFFFFF'],
    'violet' => ['Violet', '#8E44AD'],
    'rouge'  => ['Rouge', '#E74C3C'],
    'orange' => ['Orange', '#F39C12'],
    'vert'   => ['Vert', '#27AE60'],
    'dur'    => ['Dur', '#DCE85A'],
    'pole'   => ['Pôle compétition', '#124A20'],
];
const PEDA_MOIS_COURTS = ['janv.', 'févr.', 'mars', 'avr.', 'mai', 'juin', 'juil.', 'août', 'sept.', 'oct.', 'nov.', 'déc.'];
const PEDA_AGES =['enfant' => 'Enfant', 'ado' => 'Ado', 'senior' => 'Senior', 'senior_plus' => 'Senior+'];
// Passage à la balle supérieure : niveau actuel => niveau suivant
const PEDA_PASSAGES = ['blanc' => 'violet', 'violet' => 'rouge', 'rouge' => 'orange', 'orange' => 'vert', 'vert' => 'dur', 'dur' => 'pole'];

const PEDA_ACTIONS_HEURE = 120;                                    // modifications par adresse IP et par heure
const PEDA_PDF_MAX       = 10 * 1024 * 1024;                       // 10 Mo
const PEDA_DOSSIER       = __DIR__ . '/../public/assets/uploads/pedagogie/';
const PEDA_AUTEUR        = 'Visiteur (page Pédagogie)';            // nom affiché dans le Journal

// ── Petits outils ──
function peda_texte($valeur, $max = 0) {
    $valeur = trim(str_replace("\r\n", "\n", (string)$valeur));
    return $max ? mb_substr($valeur, 0, $max) : $valeur;
}

// Liste de clés cochées (tableau POST) → "cle1,cle2" en gardant seulement les clés connues, dans l'ordre
function peda_cases($cochees, $permises) {
    $cochees = is_array($cochees) ? $cochees : [];
    return implode(',', array_values(array_filter(array_keys($permises), fn($cle) => in_array($cle, $cochees, true))));
}

function peda_liste($valeur) {
    return $valeur === '' || $valeur === null ? [] : explode(',', $valeur);
}

// Montant saisi (« 12 », « 12,50 », « 12.5 € ») → nombre, null si vide, false si invalide
function peda_montant($valeur) {
    $valeur = str_replace([',', '€', ' '], ['.', '', ''], trim((string)$valeur));
    if ($valeur === '') return null;
    return is_numeric($valeur) && $valeur >= 0 && $valeur < 1000000 ? round((float)$valeur, 2) : false;
}

function peda_prix_affiche($montant) {
    return $montant === null ? '' : number_format((float)$montant, (float)$montant == (int)$montant ? 0 : 2, ',', ' ') . ' €';
}

// Durée saisie au format heures:minutes (« 02:30 ») → « 2 h 30 », « 45 min », « 3 h »
function peda_duree_affiche($duree) {
    if (!preg_match('/^(\d{2}):(\d{2})$/', $duree, $m)) return $duree;
    [$h, $min] = [(int)$m[1], (int)$m[2]];
    if (!$h) return "$min min";
    return $min ? sprintf('%d h %02d', $h, $min) : "$h h";
}

// Valeur pour le champ « heures:minutes » du formulaire. Comprend aussi les anciennes durées écrites
// en texte (« 2h », « 1 h 30 », « 45 min ») ; renvoie '' si la durée n'est pas reconnue.
function peda_duree_heure($duree) {
    $duree = trim((string)$duree);
    if (preg_match('/^\d{2}:\d{2}$/', $duree)) return $duree;
    if (preg_match('/^(\d{1,2})\s*h(?:eures?)?\s*(\d{1,2})?\s*(?:min)?$/i', $duree, $m)) return sprintf('%02d:%02d', $m[1], $m[2] ?? 0);
    if (preg_match('/^(\d{1,2})\s*min(?:utes?)?$/i', $duree, $m)) return sprintf('00:%02d', $m[1]);
    return '';
}

function peda_date($valeur) {
    $ts = strtotime((string)$valeur);
    return $valeur !== '' && $ts ? date('Y-m-d H:i:s', $ts) : null;
}

function peda_journal($description) {
    journaliser('pedagogie', $description, PEDA_AUTEUR);
}

// ── Calendrier public : publier / mettre à jour / retirer une animation planifiée ──
function peda_publier($planif) {
    global $pdo;
    $type = EVENT_TYPES['evenement'];
    $valeurs = [$planif['titre'], $planif['date_debut'], $planif['date_fin'], 'evenement', $type['couleur'], $planif['notes']];
    if ($planif['evenement_id'] && peda_evenement_existe($planif['evenement_id'])) {
        $pdo->prepare("UPDATE evenements SET titre=?, date_debut=?, date_fin=?, type=?, couleur=?, description=? WHERE id=?")
            ->execute([...$valeurs, $planif['evenement_id']]);
        return (int)$planif['evenement_id'];
    }
    $pdo->prepare("INSERT INTO evenements (titre, date_debut, date_fin, type, couleur, description) VALUES (?,?,?,?,?,?)")->execute($valeurs);
    $id = (int)$pdo->lastInsertId();
    $pdo->prepare("UPDATE peda_calendrier SET evenement_id=? WHERE id=?")->execute([$id, $planif['id']]);
    return $id;
}

function peda_retirer($planif) {
    global $pdo;
    if ($planif['evenement_id']) $pdo->prepare("DELETE FROM evenements WHERE id=?")->execute([$planif['evenement_id']]);
    $pdo->prepare("UPDATE peda_calendrier SET evenement_id=NULL WHERE id=?")->execute([$planif['id']]);
}

function peda_evenement_existe($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM evenements WHERE id=?");
    $stmt->execute([$id]);
    return (bool)$stmt->fetchColumn();
}

function peda_ligne($table, $id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM $table WHERE id=?");
    $stmt->execute([(int)$id]);
    return $stmt->fetch() ?: null;
}

// ── Paramètres (programmation annuelle, critères de passage) ──
function peda_param_ecrire($cle, $valeur) {
    global $pdo;
    $pdo->prepare("INSERT INTO parametres (cle, valeur) VALUES (?,?) ON DUPLICATE KEY UPDATE valeur=?")->execute([$cle, $valeur, $valeur]);
}

// PDF envoyé : [nom du fichier enregistré, null] ou [null, message d'erreur]
function peda_recevoir_pdf($fichier) {
    if (($fichier['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) return [null, 'L\'envoi du fichier a échoué.'];
    if ($fichier['size'] > PEDA_PDF_MAX) return [null, 'Fichier trop lourd (10 Mo maximum).'];
    if (file_get_contents($fichier['tmp_name'], false, null, 0, 5) !== '%PDF-') return [null, 'Le fichier envoyé n\'est pas un PDF valide.'];
    if (!is_dir(PEDA_DOSSIER)) mkdir(PEDA_DOSSIER, 0755, true);
    $nom = 'programmation_' . bin2hex(random_bytes(6)) . '.pdf';
    if (!move_uploaded_file($fichier['tmp_name'], PEDA_DOSSIER . $nom)) return [null, 'Impossible d\'enregistrer le fichier.'];
    return [$nom, null];
}

// ── Traitement des formulaires ───────────────────────────────────────────────
// Renvoie [code de succès pour la redirection, ou null] ; en cas d'erreur, remplit $erreurs
// (les valeurs saisies restent dans $_POST pour réafficher le formulaire).
function peda_traiter(&$erreurs) {
    global $pdo;
    $action = $_POST['action'] ?? '';

    if (limite_atteinte('pedagogie', PEDA_ACTIONS_HEURE, 3600)) {
        $erreurs[] = 'Trop de modifications en une heure depuis cette connexion : réessayez plus tard.';
        return null;
    }
    limite_enregistrer('pedagogie', 3600);

    // ── Animations ──
    if ($action === 'animation_enregistrer') {
        $id  = (int)($_POST['id'] ?? 0);
        $nom = peda_texte($_POST['nom'] ?? '', 150);
        $categorie = isset(PEDA_CATEGORIES[$_POST['categorie'] ?? '']) ? $_POST['categorie'] : 'tennistique';
        $lieu = isset(PEDA_LIEUX[$_POST['lieu'] ?? '']) ? $_POST['lieu'] : '';
        $prix = peda_montant($_POST['prix'] ?? '');
        $mainOeuvre    = !empty($_POST['main_oeuvre']);
        $participation = !empty($_POST['participation']);
        $nbPersonnes   = $mainOeuvre ? filter_var($_POST['main_oeuvre_nb'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 500]]) : null;
        $prixMembre    = $participation ? peda_montant($_POST['participation_prix'] ?? '') : null;

        if ($nom === '') $erreurs[] = 'Le nom de l\'animation est obligatoire.';
        if ($categorie === 'autre' && peda_texte($_POST['categorie_autre'] ?? '') === '') $erreurs[] = 'Précisez la catégorie « Autre ».';
        $duree = trim($_POST['duree'] ?? '');
        if ($duree !== '' && (!preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $duree) || $duree === '00:00')) {
            $erreurs[] = 'La durée doit être au format heures:minutes (ex : 02:30).';
        }
        if ($prix === false) $erreurs[] = 'Le prix doit être un montant (ex : 50 ou 12,50).';
        if ($mainOeuvre && !$nbPersonnes) $erreurs[] = 'Indiquez le nombre de personnes nécessaires (main d\'œuvre).';
        if ($participation && ($prixMembre === null || $prixMembre === false)) $erreurs[] = 'Indiquez le montant de la participation demandée aux membres.';
        if ($erreurs) return null;

        $valeurs = [
            $nom, $categorie, $categorie === 'autre' ? peda_texte($_POST['categorie_autre'], 100) : '',
            $lieu, peda_texte($_POST['lieu_precision'] ?? '', 150),
            peda_cases($_POST['niveaux'] ?? [], PEDA_NIVEAUX), peda_cases($_POST['ages'] ?? [], PEDA_AGES),
            $duree, peda_texte($_POST['deroulement'] ?? '', 20000), $prix,
            (int)$mainOeuvre, $mainOeuvre ? $nbPersonnes : null, (int)$participation, $participation ? $prixMembre : null,
        ];
        $colonnes = 'nom=?, categorie=?, categorie_autre=?, lieu=?, lieu_precision=?, niveaux=?, ages=?, duree=?, deroulement=?, prix=?, main_oeuvre=?, main_oeuvre_nb=?, participation=?, participation_prix=?';
        if ($id && peda_ligne('peda_animations', $id)) {
            $pdo->prepare("UPDATE peda_animations SET $colonnes WHERE id=?")->execute([...$valeurs, $id]);
            peda_journal("Modification de l'animation « $nom »");
            return 'animation_modifiee';
        }
        $pdo->prepare("INSERT INTO peda_animations SET $colonnes")->execute($valeurs);
        peda_journal("Création de l'animation « $nom »");
        return 'animation_creee';
    }
    if ($action === 'animation_supprimer' && ($a = peda_ligne('peda_animations', $_POST['id'] ?? 0))) {
        // Les dates déjà planifiées sont gardées (avec leur titre), simplement détachées de la fiche
        $pdo->prepare("UPDATE peda_calendrier SET animation_id=NULL WHERE animation_id=?")->execute([$a['id']]);
        $pdo->prepare("DELETE FROM peda_animations WHERE id=?")->execute([$a['id']]);
        peda_journal("Suppression de l'animation « {$a['nom']} »");
        return 'animation_supprimee';
    }

    // ── Calendrier interne ──
    if ($action === 'planif_enregistrer') {
        $id = (int)($_POST['id'] ?? 0);
        $animation = !empty($_POST['animation_id']) ? peda_ligne('peda_animations', $_POST['animation_id']) : null;
        $titre = peda_texte($_POST['titre'] ?? '', 150) ?: ($animation['nom'] ?? '');
        $debut = peda_date($_POST['date_debut'] ?? '');
        $fin   = peda_date($_POST['date_fin'] ?? '');
        if ($titre === '') $erreurs[] = 'Choisissez une animation de la bibliothèque ou saisissez un titre.';
        if (!$debut) $erreurs[] = 'La date de début est obligatoire.';
        if ($debut && $fin && $fin < $debut) $erreurs[] = 'La date de fin doit être après la date de début.';
        if ($erreurs) return null;

        $notes = peda_texte($_POST['notes'] ?? '', 5000);
        $valeurs = [$animation['id'] ?? null, $titre, $debut, $fin, $notes];
        if ($id && ($ancienne = peda_ligne('peda_calendrier', $id))) {
            $pdo->prepare("UPDATE peda_calendrier SET animation_id=?, titre=?, date_debut=?, date_fin=?, notes=? WHERE id=?")->execute([...$valeurs, $id]);
            // Déjà publiée : le calendrier public suit la modification (sauf si l'événement public
            // a été supprimé entre-temps depuis l'admin : on ne le recrée pas sans qu'on le demande)
            if ($ancienne['evenement_id'] && peda_evenement_existe($ancienne['evenement_id'])) {
                peda_publier(peda_ligne('peda_calendrier', $id));
            }
            peda_journal("Modification de la date planifiée « $titre » (" . date('d/m/Y', strtotime($debut)) . ')');
            return 'planif_modifiee';
        }
        $pdo->prepare("INSERT INTO peda_calendrier (animation_id, titre, date_debut, date_fin, notes) VALUES (?,?,?,?,?)")->execute($valeurs);
        peda_journal("Planification de « $titre » le " . date('d/m/Y', strtotime($debut)));
        return 'planif_creee';
    }
    if (in_array($action, ['planif_publier', 'planif_retirer', 'planif_supprimer'], true)
        && ($p = peda_ligne('peda_calendrier', $_POST['id'] ?? 0))) {
        $quand = date('d/m/Y', strtotime($p['date_debut']));
        if ($action === 'planif_publier') {
            peda_publier($p);
            peda_journal("Publication sur le calendrier public : « {$p['titre']} » ($quand)");
            return 'planif_publiee';
        }
        if ($action === 'planif_retirer') {
            peda_retirer($p);
            peda_journal("Retrait du calendrier public : « {$p['titre']} » ($quand)");
            return 'planif_retiree';
        }
        peda_retirer($p);                                  // supprimée aussi du calendrier public
        $pdo->prepare("DELETE FROM peda_calendrier WHERE id=?")->execute([$p['id']]);
        peda_journal("Suppression de la date planifiée « {$p['titre']} » ($quand)");
        return 'planif_supprimee';
    }

    // ── Exercices ──
    if ($action === 'exercice_enregistrer') {
        $id = (int)($_POST['id'] ?? 0);
        $titre = peda_texte($_POST['titre'] ?? '', 150);
        if ($titre === '') { $erreurs[] = 'Le titre de l\'exercice est obligatoire.'; return null; }
        $valeurs = [$titre, peda_texte($_POST['description'] ?? '', 20000), peda_texte($_POST['utilite'] ?? '', 10000),
                    peda_texte($_POST['cas_usage'] ?? '', 10000), peda_texte($_POST['points_attention'] ?? '', 10000)];
        if ($id && peda_ligne('peda_exercices', $id)) {
            $pdo->prepare("UPDATE peda_exercices SET titre=?, description=?, utilite=?, cas_usage=?, points_attention=? WHERE id=?")->execute([...$valeurs, $id]);
            peda_journal("Modification de l'exercice « $titre »");
            return 'exercice_modifie';
        }
        $pdo->prepare("INSERT INTO peda_exercices (titre, description, utilite, cas_usage, points_attention) VALUES (?,?,?,?,?)")->execute($valeurs);
        peda_journal("Création de l'exercice « $titre »");
        return 'exercice_cree';
    }
    if ($action === 'exercice_supprimer' && ($e = peda_ligne('peda_exercices', $_POST['id'] ?? 0))) {
        $pdo->prepare("DELETE FROM peda_exercices WHERE id=?")->execute([$e['id']]);
        peda_journal("Suppression de l'exercice « {$e['titre']} »");
        return 'exercice_supprime';
    }

    // ── Programmation annuelle (PDF du directeur sportif + notes) ──
    if ($action === 'programmation_enregistrer') {
        if (($_FILES['pdf']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            [$nom, $erreur] = peda_recevoir_pdf($_FILES['pdf']);
            if ($erreur) { $erreurs[] = $erreur; return null; }
            $ancien = param_brut('peda_programmation_pdf');
            if ($ancien) @unlink(PEDA_DOSSIER . basename($ancien));
            peda_param_ecrire('peda_programmation_pdf', $nom);
            peda_journal('Nouveau document de programmation annuelle');
        }
        peda_param_ecrire('peda_programmation_notes', peda_texte($_POST['notes'] ?? '', 20000));
        return 'programmation_enregistree';
    }
    if ($action === 'programmation_supprimer_pdf') {
        $ancien = param_brut('peda_programmation_pdf');
        if ($ancien) @unlink(PEDA_DOSSIER . basename($ancien));
        peda_param_ecrire('peda_programmation_pdf', '');
        peda_journal('Suppression du document de programmation annuelle');
        return 'programmation_pdf_supprime';
    }

    // ── Critères de passage à la balle supérieure ──
    if ($action === 'criteres_enregistrer') {
        foreach (array_keys(PEDA_PASSAGES) as $niveau) {
            peda_param_ecrire("peda_criteres_$niveau", peda_texte($_POST['criteres'][$niveau] ?? '', 10000));
        }
        peda_journal('Modification des critères de passage');
        return 'criteres_enregistres';
    }

    $erreurs[] = 'Action inconnue ou élément introuvable.';
    return null;
}

const PEDA_MESSAGES = [
    'animation_creee'            => 'Animation créée.',
    'animation_modifiee'         => 'Animation modifiée.',
    'animation_supprimee'        => 'Animation supprimée.',
    'planif_creee'               => 'Animation ajoutée au calendrier interne.',
    'planif_modifiee'            => 'Date planifiée modifiée.',
    'planif_publiee'             => 'Publiée sur le calendrier public : elle est maintenant visible par tous les visiteurs.',
    'planif_retiree'             => 'Retirée du calendrier public (elle reste dans le calendrier interne).',
    'planif_supprimee'           => 'Date planifiée supprimée.',
    'exercice_cree'              => 'Exercice ajouté à la bibliothèque.',
    'exercice_modifie'           => 'Exercice modifié.',
    'exercice_supprime'          => 'Exercice supprimé.',
    'programmation_enregistree'  => 'Programmation annuelle enregistrée.',
    'programmation_pdf_supprime' => 'Document de programmation supprimé.',
    'criteres_enregistres'       => 'Critères de passage enregistrés.',
];
