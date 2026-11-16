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
| `public/assets/` | `css/style.css` (site), `js/main.js` (menu, interactions), images, polices et bibliothèques (FullCalendar, Leaflet, Cropper) |
| `public/assets/uploads/` | Fichiers envoyés depuis l'admin (`articles/`, `entraineurs/`, `bureau/`, `documents/`, galerie). **Hors Git.** |
| `templates/` | Morceaux communs : `head.php` (balises meta, partage), `navbar.php`, `footer.php`, `stats.php` (chiffres clés), `cartes-personnes.php` (bureau / entraîneurs), `article-contenu.php` (article et aperçu) |
| `src/init.php` | Point d'entrée commun : connexion, fuseau horaire, constantes, fonctions utilitaires |
| `src/textes.php` | Textes du site modifiables dans l'admin, avec leurs valeurs par défaut |
| `src/ia.php` | Génération d'articles avec l'API Gemini |
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

10 tables, toutes en `utf8mb4_unicode_ci` (accents et emojis).

| Table | Contenu | Colonnes notables |
|---|---|---|
| `admins` | Comptes de l'administration | `role` (`fondateur`, `administrateur`, `moderateur`), `email` unique, `mot_de_passe` (hash) |
| `articles` | Actualités | `contenu` (syntaxe simplifiée, voir §6), `image`, `publie`, `published_at` (date de publication, future = programmée) |
| `evenements` | Calendrier | `date_debut`, `date_fin`, `type` (`tournoi`, `cours`, `evenement`, `vacances`, `autre`) — la couleur dépend du type |
| `galerie` | Photos | `fichier`, `categorie`, `ordre` |
| `entraineurs`, `bureau` | Fiches de l'équipe | `prenom`, `nom`, `role` (fonction), `photo`, `ordre` |
| `tarifs` | Formules | `prix` (texte libre), `periode`, `avantages` (une ligne par avantage), `mis_en_avant`, `pdf` (fiche d'inscription) |
| `messages_contact` | Formulaire de contact | `sujet` (clé de `SUJETS_CONTACT`), `lu` — supprimés automatiquement après 3 ans |
| `parametres` | Réglages clé / valeur | `cle` unique, `valeur` (voir ci-dessous) |
| `journal` | Actions importantes | `admin_nom` (conservé même si le compte est supprimé), `categorie`, `ip` — conservées 12 mois |

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
  - `exiger_fondateur()` en haut de `journal.php` ;
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

## 7. Sécurité

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

## 8. Services externes

| Service | Utilisation | Où |
|---|---|---|
| **Gemini** (Google) | Rédaction d'articles à partir de notes. Modèle par défaut `gemini-flash-lite-latest`, modèles de secours si surcharge | `src/ia.php`, `admin/ajax/generer-article.php` |
| **Open-Meteo** | Météo d'Aulnay-sous-Bois sur l'accueil (appel depuis le navigateur, sans clé) | `public/index.php` |
| **OpenStreetMap** | Fond de carte de la page Contact (Leaflet) | `public/contact.php` |
| **Ten'up** (FFT) | Simple lien vers la réservation des courts (texte modifiable) | `lien_reservation` dans `src/textes.php` |

Les bibliothèques JavaScript et la police d'écriture sont **hébergées dans le projet** (aucun appel à un CDN ni à Google Fonts).

## 9. Ajouter ou modifier quelque chose

- **Un texte modifiable** : ajouter une entrée dans `TEXTES_SITE` (`src/textes.php`), puis l'afficher avec `<?= t('ma_cle') ?>`. Il apparaît automatiquement dans Paramètres > Textes du site.
- **Une page admin** : créer `admin/ma-page.php` commençant par `require_once 'auth.php';`, puis l'ajouter à `MENU_ADMIN`. L'ajouter aussi à `PAGES_ADMINISTRATEURS` ou `PAGES_FONDATEUR` si elle doit être restreinte, et appeler `exiger_*()` en haut du fichier.
- **Une page publique** : créer `public/ma-page.php` sur le modèle d'une page existante (`require_once '../src/init.php'`, puis `head.php`, `navbar.php`, `footer.php`), et l'ajouter au menu (`templates/navbar.php`) et au sitemap.
- **Un changement de structure de la base** : suivre la procédure de [sql/README.md](../sql/README.md).
- **Une action à tracer** : appeler `journaliser('categorie', 'description')`. La catégorie doit exister dans `JOURNAL_CATEGORIES`.

## 10. Conventions

- Code, variables, commentaires et messages en **français**.
- Une constante en haut de fichier pour chaque réglage (délais, limites, tailles).
- Pas de dépendance à installer (pas de Composer, pas de npm) : le projet fonctionne tel quel sur un hébergement PHP basique.
