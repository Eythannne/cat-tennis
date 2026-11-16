# Historique des versions

Les modifications du site, de la plus récente à la plus ancienne. Le détail de chaque changement est dans l'historique Git.

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
