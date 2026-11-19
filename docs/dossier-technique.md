# Dossier technique

> Pour un développeur qui reprend le projet. Le code est écrit et commenté en français ; ce document en donne la vue d'ensemble.

## 1. Architecture générale

Site **PHP 8 sans framework**, base **MySQL/MariaDB**, interface en HTML/CSS/JavaScript sans framework. Chaque page est un fichier PHP qui fait ses requêtes puis affiche son HTML.

```
Navigateur ──► .htaccess (racine) ──► public/   (site public, racine web)
                                 └──► admin/    (administration)
        public/ et admin/ ──► src/init.php ──► src/config/database.php (connexion PDO)
                                          └──► src/textes.php (textes modifiables)
```

### Routage (`.htaccess`)

- **Racine** : `admin/` et `public/` sont servis tels quels, toute autre adresse est redirigée vers `public/`. En ligne, l'adresse `/club.php` affiche donc `public/club.php`.
- **`public/.htaccess`** :
  - `robots.txt` → `robots.php` ;
  - `sitemap.xml` → `sitemap.php` (générés en PHP) ;
  - toute adresse inexistante → `404.php`.
- **`BASE_URL`** (défini dans `database.php`) vaut `/cat-tennis/public` en local et `''` en ligne. Il est détecté automatiquement selon le nom d'hôte.

## 2. Organisation des fichiers

| Dossier / fichier | Rôle |
|---|---|
| `public/*.php` | Pages publiques : `index`, `club`, `actualites`, `article`, `calendrier`, `contact`, `mentions-legales`, `404`, plus `robots` et `sitemap` |
| `public/api/evenements.php` | Flux JSON des événements et jours fériés pour FullCalendar |
| `public/jeux.php` | Espace Jeux : une carte par jeu de la liste `JEUX` |
| `public/jeu-echange.php`, `public/assets/js/jeu-echange.js` | Jeu « Un petit échange » : page et jeu (canvas) |
| `public/assets/js/personnages.js` | Personnages des jeux en style dessin animé (dessinés en canvas, réutilisables) |
| `public/api/jeux.php` | API des scores, commune à tous les jeux |
| `public/pedagogie.php`, `templates/pedagogie/*.php`, `public/assets/css/pedagogie.css`, `public/assets/js/pedagogie.js`, `public/api/pedagogie.php` | Page Pédagogie : page, un fichier par onglet, style, script, flux du calendrier interne |
| `public/assets/` | `css/style.css` (site), `js/main.js` (menu, interactions), images, polices et bibliothèques (FullCalendar, Leaflet, Cropper) |
| `public/assets/uploads/` | Fichiers envoyés depuis l'admin (`articles/`, `entraineurs/`, `bureau/`, `documents/`, galerie). **Hors Git.** |
| `templates/` | Morceaux communs : `head.php` (balises meta, partage), `navbar.php`, `footer.php`, `stats.php` (chiffres clés), `cartes-personnes.php` (bureau / entraîneurs), `article-contenu.php` (article et aperçu) |
| `src/init.php` | Point d'entrée commun : connexion, fuseau horaire, constantes, fonctions utilitaires |
| `src/textes.php` | Textes du site modifiables dans l'admin, avec leurs valeurs par défaut |
| `src/ia.php` | Génération d'articles avec l'API Gemini |
| `src/jeux.php` | Espace Jeux côté serveur : liste des jeux (`JEUX`), jetons de partie, pseudos, classements |
| `src/pedagogie.php` | Page Pédagogie côté serveur : listes (niveaux, âges, catégories, lieux), validation, publication sur le calendrier public |
| `src/config/database.php` | Identifiants de la base. **Hors Git** (modèle : `database.example.php`) |
| `admin/session.php` | Session sécurisée, CSRF, traitement des images et des fichiers envoyés |
| `admin/auth.php` | À inclure en haut de chaque page admin : connexion obligatoire, déconnexion automatique, rôle, purges RGPD |
| `admin/_layout.php` | Gabarit de l'admin (menu par rôle, en-tête, pied de page) et fonctions de droits |
| `admin/_personnes.php` | Code commun aux pages Entraîneurs et Bureau |
| `admin/*.php` | Une page par rubrique (`articles`, `evenements`, `galerie`, `tarifs`, `messages`, `parametres`, `admins`, `journal`, `compte`…) |
| `admin/ajax/` | Appels en arrière-plan (`generer-article.php`), réponses JSON |
| `admin/*.js` | `editeur.js` (barre de mise en forme et bouton ✨ Générer), `crop.js` (recadrage d'images) |
| `sql/` | `schema.sql` (base neuve) et `migrations/` (historique) — voir `sql/README.md` |

## 3. Base de données

14 tables, toutes en `utf8mb4_unicode_ci` (accents et emojis).

| Table | Contenu | Colonnes notables |
|---|---|---|
| `admins` | Comptes de l'administration | `role` (`fondateur`, `administrateur`, `moderateur`, `peda`), `email` unique, `mot_de_passe` (hash) |
| `articles` | Actualités | `contenu` (syntaxe simplifiée, voir §6), `image`, `publie`, `published_at` (date de publication, future = programmée) |
| `evenements` | Calendrier | `date_debut`, `date_fin`, `type` (`tournoi`, `cours`, `evenement`, `vacances`, `autre`) — la couleur dépend du type |
| `galerie` | Photos | `fichier`, `categorie`, `ordre` |
| `entraineurs`, `bureau` | Fiches de l'équipe | `prenom`, `nom`, `role` (fonction), `photo`, `ordre` |
| `tarifs` | Formules | `prix` (texte libre), `periode`, `avantages` (une ligne par avantage), `mis_en_avant`, `pdf` (fiche d'inscription) |
| `messages_contact` | Formulaire de contact | `sujet` (clé de `SUJETS_CONTACT`), `lu` — supprimés automatiquement après 3 ans |
| `parametres` | Réglages clé / valeur | `cle` unique, `valeur` (voir ci-dessous) |
| `journal` | Actions importantes | `admin_nom` (conservé même si le compte est supprimé), `categorie`, `ip` — conservées 12 mois |
| `peda_animations` | Fiches d'animations (page Pédagogie) | `categorie`, `niveaux` et `ages` (clés séparées par des virgules, voir `PEDA_NIVEAUX` / `PEDA_AGES`), `prix`, `main_oeuvre_nb`, `participation_prix` |
| `peda_calendrier` | Calendrier interne des animations | `animation_id` (fiche d'origine, peut être NULL), `evenement_id` (événement public si publiée) |
| `peda_exercices` | Bibliothèque d'exercices | `description`, `utilite`, `cas_usage`, `points_attention` |
| `jeux_scores` | Scores de tous les jeux | `jeu` (identifiant du jeu), `pseudo`, `score`, `duree` (mesurée par le serveur), `jeton` unique (un score par partie), `membre_id` (prévu pour le futur espace membre) |

### Clés de la table `parametres`

| Clés | Usage |
|---|---|
| `nom_club`, `adresse`, `email_contact`, `telephone`, `horaires`, `description`, `presentation_club` | Informations du club (Paramètres > Informations du club) |
| `responsable_publication`, `rna`, `siret` | Mentions légales |
| `stat1_valeur` … `stat3_label` | Chiffres clés (défauts : `CHIFFRES_DEFAUT`) |
| `texte_<clé>` | Textes du site modifiés (vide = texte par défaut de `src/textes.php`) |
| `saison` | Saison affichée au-dessus des tarifs |
| `groupe_entraineurs_photo`, `groupe_bureau_photo` (+ `_legende`) | Photos de groupe |
| `gemini_api_key`, `gemini_modele`, `prompt_ia_articles` | Réglages de l'IA (vides = valeurs par défaut de `src/ia.php`) |
| `peda_programmation_pdf`, `peda_programmation_notes`, `peda_criteres_<niveau>` | Page Pédagogie : document de programmation annuelle (dans `uploads/pedagogie/`), notes, critères de passage |
| `jeux_secret` | Clé qui signe les jetons des jeux, créée automatiquement au premier usage (propre à chaque installation) |

Les paramètres se lisent avec `param('cle', 'défaut')`. Une valeur vide renvoie le défaut.

## 4. Fonctions importantes

| Fonction | Fichier | Rôle |
|---|---|---|
| `param()`, `param_brut()` | `src/init.php` | Lire un paramètre (chargés une seule fois par page) |
| `texte()`, `t()` | `src/textes.php` | Texte du site (modifié ou par défaut). `t()` l'échappe pour le HTML |
| `journaliser()` | `src/init.php` | Ajouter une ligne au journal (ne bloque jamais la page) |
| `limite_atteinte()`, `limite_enregistrer()` | `src/init.php` | Limiter des actions par adresse IP (connexion, contact, IA) |
| `format_article()`, `texte_brut()` | `src/init.php` | Convertir la syntaxe des articles en HTML / en texte seul |
| `sql_article_visible()` | `src/init.php` | Condition SQL « publié et date de publication passée » |
| `ressource()`, `ressource_admin()` | `src/init.php` | Adresse d'un fichier CSS ou JS avec sa date de modification (`?v=…`), pour que les navigateurs rechargent la nouvelle version après une mise à jour |
| `jours_feries()`, `quand_evenement()`, `date_fr()` | `src/init.php` | Jours fériés (calculés, Pâques comprise), dates en français |
| `est_fondateur()`, `est_administrateur()`, `exiger_*()`, `lecture_seule_pour_moderateur()` | `admin/_layout.php` | Contrôle des droits |
| `csrf_field()`, `csrf_check()` | `admin/session.php` | Jeton anti-falsification des formulaires |
| `save_cropped_image()`, `optimiser_image()`, `delete_upload()` | `admin/session.php` | Images envoyées : vérification, réduction, suppression |
| `gemini_generer_article()`, `gemini_verifier()` | `src/ia.php` | Génération d'articles et vérification de la clé |

## 5. Rôles et droits

Trois rôles, stockés dans `admins.role`. Le tableau complet est dans [roles-et-droits.md](roles-et-droits.md).

- **Menu** : `MENU_ADMIN` dans `admin/_layout.php`, filtré selon les listes `PAGES_ADMINISTRATEURS`, `PAGES_FONDATEUR` et `PAGES_LECTURE_SEULE`.
- **Contrôle côté serveur** (jamais seulement à l'affichage) :
  - `exiger_administrateur()` en haut de `admins.php` ;
  - `exiger_fondateur()` en haut de `journal.php` et de `jeux.php` ;
  - `lecture_seule_pour_moderateur()` en haut de `parametres.php` (refuse tout envoi de formulaire d'un modérateur) ;
  - l'onglet IA de `parametres.php` refuse tout envoi qui ne vient pas du fondateur.
- **Rôle relu à chaque page** (`auth.php`) : un changement de rôle ou une suppression de compte s'applique immédiatement.
- **Fondateur** : ce rôle ne s'attribue que dans la base (`ROLES_ATTRIBUABLES` l'exclut). Son compte n'est modifiable que par lui-même.

## 6. Contenu des articles

Les articles sont stockés en **texte simple**, converti en HTML à l'affichage par `format_article()` (le texte est toujours échappé avant conversion) :

```
## Intertitre         **gras**         *italique*
- liste à puces       1. liste numérotée
[texte du lien](https://…)            ligne vide = nouveau paragraphe
```

La barre d'outils (`admin/editeur.js`) insère cette syntaxe, et la génération par IA la produit (`IA_CONSIGNE_FORMAT` dans `src/ia.php`).

## 7. Espace Jeux

La page `jeux.php` affiche une carte par jeu de la liste **`JEUX`** (`src/jeux.php`). Chaque jeu tourne entièrement dans le navigateur ; le premier, « Un petit échange », est dessiné dans un `<canvas>` de 400 × 600 (`public/assets/js/jeu-echange.js`). Le serveur ne sert qu'à valider et stocker les scores, avec une API commune (`api/jeux.php`) et une seule table (`jeux_scores`, colonne `jeu`) :

1. **Début de partie** : action `debut` avec l'identifiant du jeu. Le serveur renvoie un **jeton signé** (HMAC avec `jeux_secret`) contenant un identifiant unique, le jeu et l'heure de début. Aucun cookie n'est utilisé.
2. **Fin de partie** : le navigateur envoie le jeton, le pseudo et le score (action `score`). Le serveur vérifie :
   - la signature du jeton ;
   - la durée réelle de la partie : un score marqué plus vite que `ms_par_point` (réglage du jeu dans `JEUX`, 500 ms pour le tennis) est impossible, donc refusé ;
   - le pseudo : 2 à 16 caractères et filtre des mots grossiers, y compris déguisés (« c0n », « s.a.l.o.p.e ») ;
   - l'unicité : contrainte `UNIQUE` sur le jeton, donc un seul score par partie.
3. **Classement** : action `classement` avec l'identifiant du jeu, le meilleur score de chaque pseudo (top `JEU_TOP` = 15).

Les réglages du jeu sont des constantes en haut des fichiers :
- **gameplay du tennis** (`jeu-echange.js`) : `VITESSE_DEPART`, `VITESSE_MAX`, `ACCELERATION`, `RAQUETTE`, `VITESSE_JOUEUR`. Si on augmente `VITESSE_MAX`, il faut vérifier que `ms_par_point` (dans `JEUX`) reste en dessous de la durée minimale réelle d'un échange ;
- **limites communes** (`src/jeux.php`) : `JEU_PARTIES_HEURE`, `JEU_SCORES_HEURE`, `JEU_MOTS_INTERDITS`.

La balle flottante qui mène à l'espace Jeux est dans `templates/footer.php` (style `.balle-jeu` dans `style.css`). Elle est masquée sur les pages des jeux.

### Personnages

`public/assets/js/personnages.js` décrit les personnages (liste `LISTE`) : couleur de peau, coiffure (`court`, `mi-long`, `boucles`, `chignon`, `dreads`), bandeau, bandana ou casquette, maillot, short, débardeur, gaucher, expression (`sourire`, `grand-sourire`, `concentre`, `cri`). La fonction `PersonnagesTennis.dessiner(ctx, x, y, perso, options)` les dessine tous, avec le geste de frappe (`frappe`) et la course (`marche`).

- **Ajouter un personnage** : une entrée dans `LISTE`. Il apparaît tout seul dans l'écran de choix.
- Le choix du joueur est mémorisé dans le navigateur (`jeux_perso`) ; l'adversaire est tiré au hasard à chaque partie.
- Les personnages s'inspirent de joueurs réels. Ce sont des caricatures humoristiques, sans logo de marque, avec une mention « sans lien officiel » sur la page du jeu.

### Ajouter un jeu

1. Ajouter une entrée dans **`JEUX`** (`src/jeux.php`) : identifiant (lettres minuscules, sans point), titre, icône, description, page, unité du score, `ms_par_point` et `score_max` (anti-triche).
2. Créer sa page **`public/jeu-<identifiant>.php`** sur le modèle de `jeu-echange.php` (même structure, `data-jeu` = identifiant), et son script dans `public/assets/js/`. Les personnages de `personnages.js` peuvent être réutilisés.
3. Dans le script, appeler l'API `api/jeux.php` comme `jeu-echange.js` : `debut` au lancement, `score` à la fin, `classement` pour le tableau.
4. Ajouter la page au plan du site (`public/sitemap.php`).

Le jeu apparaît alors tout seul sur la page Jeux, dans l'API des scores et dans l'admin (un onglet par jeu). Aucune migration n'est nécessaire.

### Page Pédagogie

La page `public/pedagogie.php` charge un fichier par onglet (`templates/pedagogie/animations.php`, `calendrier.php`, `exercices.php`). Tous les formulaires sont traités par `peda_traiter()` (`src/pedagogie.php`), puis la page est rechargée (?ok=…).

- **Publication** : `peda_publier()` crée un événement de type « Animation » dans `evenements` et garde son identifiant dans `peda_calendrier.evenement_id`. Modifier une date publiée met l'événement public à jour ; retirer ou supprimer la date le supprime. Si l'événement public a été supprimé depuis l'admin, il n'est pas recréé sans action.
- **Accès** : ⚠ ouvert à tous pendant la phase de test, sans compte. En contrepartie : 120 modifications maximum par heure et par IP, chaque action notée dans le Journal (« Visiteur (page Pédagogie) », catégorie `pedagogie`), page en `noindex` et interdite dans `robots.txt`. À terme : réservée au rôle `peda` (voir `docs/idee-a-ajouter.txt`), il faudra alors exiger une session admin et un jeton CSRF sur les formulaires.
- **Rôle Peda** : `PAGES_PEDA` (`admin/_layout.php`) et un contrôle dans `admin/auth.php` limitent pour l'instant ces comptes à « Mon compte ».

## 8. Sécurité

| Mesure | Détail |
|---|---|
| Mots de passe | `password_hash()` / `password_verify()`, 8 caractères minimum |
| Session | Cookie `HttpOnly`, `SameSite=Lax`, `Secure` en HTTPS ; identifiant régénéré à la connexion |
| CSRF | Jeton aléatoire par session, vérifié sur tout envoi de formulaire de l'admin (`csrf_check()` dans `auth.php`) |
| Requêtes SQL | Requêtes préparées (PDO, `ATTR_EMULATE_PREPARES` désactivé) |
| Affichage | `htmlspecialchars()` sur toute donnée saisie |
| Connexion | 5 échecs maximum par IP en 15 minutes, pause d'une seconde après un échec, chaque échec noté au journal |
| Déconnexion automatique | 30 minutes d'inactivité, ou 15 secondes après avoir quitté l'admin (signal `sendBeacon` vers `quitter.php`) |
| Formulaire de contact | Champ piège invisible, délai minimum de 3 s, 5 messages par heure et par IP, 2 liens maximum |
| Fichiers envoyés | Type réel vérifié (images JPEG/PNG/WebP, PDF), taille limitée (5 Mo images, 10 Mo PDF et galerie), nom de fichier généré par le serveur |
| IA | Clé jamais réaffichée, modifiable par le fondateur uniquement, 20 générations par heure et par IP |
| RGPD | Messages supprimés après 3 ans, journal après 12 mois (purges dans `auth.php`) |

## 9. Services externes

| Service | Utilisation | Où |
|---|---|---|
| **Gemini** (Google) | Rédaction d'articles à partir de notes. Modèle par défaut `gemini-flash-lite-latest`, modèles de secours si surcharge | `src/ia.php`, `admin/ajax/generer-article.php` |
| **Open-Meteo** | Météo d'Aulnay-sous-Bois sur l'accueil (appel depuis le navigateur, sans clé) | `public/index.php` |
| **OpenStreetMap** | Fond de carte de la page Contact (Leaflet) | `public/contact.php` |
| **Ten'up** (FFT) | Simple lien vers la réservation des courts (texte modifiable) | `lien_reservation` dans `src/textes.php` |

Les bibliothèques JavaScript et la police d'écriture sont **hébergées dans le projet** (aucun appel à un CDN ni à Google Fonts).

## 10. Ajouter ou modifier quelque chose

- **Un texte modifiable** : ajouter une entrée dans `TEXTES_SITE` (`src/textes.php`), puis l'afficher avec `<?= t('ma_cle') ?>`. Il apparaît automatiquement dans Paramètres > Textes du site.
- **Une page admin** : créer `admin/ma-page.php` commençant par `require_once 'auth.php';`, puis l'ajouter à `MENU_ADMIN`. L'ajouter aussi à `PAGES_ADMINISTRATEURS` ou `PAGES_FONDATEUR` si elle doit être restreinte, et appeler `exiger_*()` en haut du fichier.
- **Un fichier CSS ou JS** : toujours le charger avec `ressource('css/mon-fichier.css')` (fichiers de `public/assets/`) ou `ressource_admin('mon-fichier.js')` (dossier `admin/`), jamais avec une adresse écrite à la main : sinon les visiteurs risquent de garder l'ancienne version en cache.
- **Une page publique** : créer `public/ma-page.php` sur le modèle d'une page existante (`require_once '../src/init.php'`, puis `head.php`, `navbar.php`, `footer.php`), et l'ajouter au menu (`templates/navbar.php`) et au sitemap.
- **Un changement de structure de la base** : suivre la procédure de [sql/README.md](../sql/README.md).
- **Une action à tracer** : appeler `journaliser('categorie', 'description')`. La catégorie doit exister dans `JOURNAL_CATEGORIES`.

## 11. Conventions

- Code, variables, commentaires et messages en **français**.
- Une constante en haut de fichier pour chaque réglage (délais, limites, tailles).
- Pas de dépendance à installer (pas de Composer, pas de npm) : le projet fonctionne tel quel sur un hébergement PHP basique.
