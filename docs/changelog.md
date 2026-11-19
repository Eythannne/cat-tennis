# Historique des versions

Les modifications du site, de la plus récente à la plus ancienne. Le détail de chaque changement est dans l'historique Git.

## 19 novembre 2026 — Mise à jour automatique des CSS et JS
- Tous les fichiers CSS et JavaScript sont chargés avec leur date de modification (`?v=…`) : après une mise en ligne, les visiteurs reçoivent la nouvelle version sans avoir à vider leur cache (fonctions `ressource()` et `ressource_admin()`).
- Page Pédagogie : la durée d'une animation se saisit au format heures:minutes et s'affiche « 2 h 30 ».

## 19 novembre 2026 — Page Pédagogie (entraîneurs)
- Nouvelle page **Pédagogie**, accessible par un lien discret « Espace pédagogique » en bas du site, avec 3 onglets :
  - **Animations** : fiches d'animations de club (catégorie, lieu, niveaux, âges, durée, déroulement, prix, main d'œuvre, participation des membres) ;
  - **Calendrier** : calendrier interne pour planifier les animations, avec un bouton « Publier sur le calendrier public » (les modifications suivent, le retrait est possible) ;
  - **Exercices** : bibliothèque d'exercices, programmation annuelle (PDF du directeur sportif + notes), critères de passage à la balle supérieure.
- Nouveau rôle **Peda** pour les comptes des entraîneurs (sans droits pour l'instant).
- Accès ouvert pendant la phase de test : modifications limitées par adresse IP, notées dans le Journal, page cachée des moteurs de recherche.
- Nouvelles tables `peda_animations`, `peda_calendrier`, `peda_exercices` (migration `2026-11-19_2-pedagogie`).

## 19 novembre 2026 — Espace Jeux
- Nouvelle page **Jeux**, qui regroupe les petits jeux du site (prévue pour en ajouter d'autres facilement).
- Premier jeu, **« Un petit échange »** : renvoyer la balle le plus longtemps possible, elle accélère à chaque échange. Jouable au clavier et sur téléphone.
- **8 personnages** en style dessin animé à choisir avant la partie (Federer, Nadal, Djokovic, Serena Williams, Alcaraz, Noah, Ben Shelton et le Joueur du CAT), avec un adversaire tiré au hasard.
- Tableau des 15 meilleurs joueurs de chaque jeu, avec un pseudo en invité.
- Balle de tennis flottante en bas à gauche de chaque page pour accéder aux jeux.
- Protections anti-triche (jeton de partie signé, durée vérifiée par le serveur) et filtre des pseudos.
- Page Jeux dans l'admin, réservée au fondateur : un onglet par jeu, scores, suppression, remise à zéro.
- Nouvelle table `jeux_scores` (migration `2026-11-19_1-jeux-scores`).

## 16 novembre 2026 — Petites améliorations
- Messages : bouton **« Marquer comme non lu »**.
- Images allégées : logo du club (306 Ko → 74 Ko) et image par défaut des articles (379 Ko → 66 Ko).

## 16 novembre 2026 — Rangement et documentation
- Documentation complète dans `docs/` : cahier des charges, dossier technique, guides d'installation, de mise en ligne et d'utilisation, rôles et droits, passation.
- `README.md` à la racine.
- Scripts SQL rangés : `sql/schema.sql` vérifié à jour, migrations datées dans `sql/migrations/`, mode d'emploi dans `sql/README.md`.
- Suppression d'une image inutilisée, `.gitignore` commenté.

## 14 novembre 2026 — Génération d'articles par IA
- Bouton **✨ Générer** sur la page Articles : l'IA Gemini rédige l'article à partir de notes brutes.
- Onglet Paramètres > Intelligence artificielle : clé vérifiée auprès de Google et jamais réaffichée, modèle, consigne. Modifiable par le fondateur uniquement.
- Modèles de secours automatiques si Google est surchargé.

## 2 novembre 2026 — Textes modifiables, nouveau Dashboard, calendriers
- Environ 50 **textes du site** modifiables dans Paramètres > Textes du site.
- Admin réorganisée : menu par thèmes, Paramètres en onglets.
- **Nouveau Dashboard** : bandeau d'accueil, chiffres cliquables, prochains événements, derniers messages, mini-calendrier du mois.
- **Jours fériés** affichés automatiquement sur les calendriers.
- Toutes les dates en français, semaine du lundi au dimanche.
- **Fiche d'inscription PDF par formule** de tarif, photos de groupe des entraîneurs et du bureau.

## 28-29 octobre 2026 — Rôles, journal, sécurité
- **3 rôles** : fondateur, administrateur, modérateur.
- **Journal** des actions importantes (fondateur uniquement), heure correcte en ligne.
- **Déconnexion automatique** de l'admin (inactivité ou sortie).
- Équipe administrative (bureau) gérée dans l'admin et affichée sur la page Le club.

## 27 octobre 2026 — Grande mise à niveau
- Corrections de sécurité urgentes et liens cassés.
- Site responsive, articles programmés, galerie avec visionneuse, pagination.
- Entraîneurs et tarifs gérés dans l'admin, anti-spam du formulaire de contact, pop-up du calendrier, page 404 personnalisée.
- Prochains événements sur l'accueil, mise en forme des articles, chiffres clés modifiables.
- Photos allégées automatiquement, aperçus pour le partage sur les réseaux sociaux, robots.txt et sitemap, comptes admin, aperçu d'article.
- Mentions légales et conformité RGPD.
- Script de mise à jour de la base en ligne (MariaDB).
- Une bannière différente pour chaque page.

## 20-26 octobre 2026 — Première version
- Première version du site et connexion à la base de données.
- Nouveau design vert et jaune, carte de la page Contact, liens vers Ten'up.
- Préparation de la base pour la mise en ligne.
