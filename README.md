# Site du Club Aulnaysien de Tennis (CAT)

Site internet du **Club Aulnaysien de Tennis** (Aulnay-sous-Bois, 93), avec un espace d'administration complet qui permet au club de tout gérer lui-même, sans connaissance technique.

- **Site public** : accueil, présentation du club (terrains, bureau, entraîneurs, tarifs et fiches d'inscription PDF), actualités et galerie photos, calendrier des événements, formulaire de contact avec carte.
- **Administration** : articles (brouillon, publication programmée, génération par IA), événements, galerie, équipe, tarifs, messages, textes du site, comptes avec 3 rôles (fondateur, administrateur, modérateur), journal des actions.

## Technologies

| Domaine | Choix |
|---|---|
| Serveur | PHP 8 (sans framework), sessions PHP |
| Base de données | MySQL 8 en local (Laragon), MariaDB en ligne (InfinityFree) |
| Interface | HTML, CSS, JavaScript sans framework |
| Bibliothèques | FullCalendar (calendrier), Leaflet + OpenStreetMap (carte), Cropper.js (recadrage d'images) — toutes hébergées dans le projet |
| Services externes | Open-Meteo (météo), API Gemini de Google (génération d'articles), Ten'up (lien de réservation de la FFT) |

## Organisation du projet

```
cat-tennis/
├── public/          Site public (racine web) : pages, CSS/JS/images, API du calendrier
├── admin/           Espace d'administration (une page par rubrique)
│   └── ajax/        Appels en arrière-plan (génération d'article par IA)
├── src/             Code partagé : connexion, fonctions, textes du site, IA
│   └── config/      Connexion à la base (database.php, hors Git)
├── templates/       Morceaux de pages communs (en-tête, menu, pied de page…)
├── sql/             Schéma de la base et migrations (voir sql/README.md)
└── docs/            Documentation complète (voir docs/README.md)
```

## Démarrage rapide (en local avec Laragon)

1. Cloner le dépôt dans `C:\laragon\www\cat-tennis`. Le nom du dossier compte, car les adresses du site en dépendent.
2. Copier `src/config/database.example.php` en `src/config/database.php`.
3. Créer une base `cat_tennis` (`utf8mb4_unicode_ci`) et y importer `sql/schema.sql`.
4. Créer le compte fondateur (voir la fin de `sql/schema.sql`).
5. Ouvrir `http://localhost/cat-tennis/public/`. L'administration est sur `http://localhost/cat-tennis/admin/`.

Le détail de chaque étape est dans [docs/guide-installation.md](docs/guide-installation.md).

## Documentation

Toute la documentation est dans le dossier [`docs/`](docs/README.md) : cahier des charges, dossier technique, guides d'installation, de mise en ligne et d'utilisation de l'admin, rôles et droits, passation, historique des versions.
