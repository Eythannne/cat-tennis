# Cahier des charges

> Ce document décrit les besoins auxquels répond le site. Il a été rédigé après la réalisation de la première version (octobre-novembre 2026), pour servir de référence à toute évolution.

## 1. Contexte

Le **Club Aulnaysien de Tennis (CAT)** est une association sportive d'Aulnay-sous-Bois (93), installée au Stade du Moulin Neuf, 4 avenue du Maréchal Juin.

Le club a besoin d'un site internet moderne pour présenter ses activités, informer ses adhérents et attirer de nouveaux membres. Le site doit pouvoir être mis à jour par des bénévoles **sans compétence technique**.

## 2. Objectifs

1. **Présenter le club** : histoire, terrains, équipe, entraîneurs, tarifs.
2. **Informer** : actualités, calendrier des événements (tournois, stages, animations, vacances).
3. **Faciliter l'inscription** : tarifs clairs et fiches d'inscription PDF téléchargeables.
4. **Être joignable** : formulaire de contact, coordonnées, carte, lien vers la réservation des courts (Ten'up).
5. **Rendre le club autonome** : tout le contenu se gère depuis un espace d'administration.

## 3. Utilisateurs

| Utilisateur | Besoins |
|---|---|
| **Visiteur** (futur adhérent, parent, joueur) | Trouver vite les infos : tarifs, inscription, horaires, contact, événements. Souvent sur téléphone. |
| **Adhérent** | Suivre les actualités et le calendrier du club. |
| **Modérateur** (entraîneur, bénévole) | Publier des actualités, des événements et des photos, répondre aux messages. |
| **Administrateur** (membre du bureau) | Tout gérer, y compris les informations du club et les comptes. |
| **Fondateur** (créateur et responsable technique du site) | Accès complet, gestion des réglages sensibles (clé de l'IA), journal des actions. |

## 4. Fonctionnalités attendues

### Site public

| Page | Fonctionnalités |
|---|---|
| Accueil | Bannière, présentation et chiffres clés, dernières actualités, prochains événements, accès à la réservation Ten'up, météo à Aulnay-sous-Bois |
| Le club | Histoire, terrains, équipe administrative (bureau), entraîneurs, tarifs avec fiche d'inscription PDF par formule |
| Actualités | Liste paginée des articles, archives par mois, page de chaque article, galerie photos par catégorie avec visionneuse plein écran |
| Calendrier | Vue mois et vue liste, couleurs par type d'événement, jours fériés automatiques, détail de chaque événement |
| Contact | Formulaire avec choix du sujet, protection anti-spam, coordonnées, carte interactive |
| Mentions légales | Éditeur, responsable de publication, hébergeur, RGPD, cookies, services tiers |
| Erreur 404 | Page personnalisée aux couleurs du club |
| Jeux | Espace qui regroupe des petits jeux (premier jeu : tennis), avec un tableau des 15 meilleurs scores par jeu, accessible par une balle flottante sur chaque page |

### Administration

| Rubrique | Fonctionnalités |
|---|---|
| Dashboard | Résumé de l'activité, chiffres, prochains événements, derniers messages et articles, mini-calendrier |
| Articles | Rédaction avec mise en forme simple, image recadrée, brouillon, publication immédiate ou programmée, aperçu, génération par IA à partir de notes |
| Événements | Création avec type (tournoi, cours/stage, animation, vacances, autre), dates et description |
| Galerie | Envoi de photos (réduites automatiquement), catégories, ordre |
| Entraîneurs / Bureau | Fiches avec photo, ordre d'affichage, photo de groupe |
| Tarifs | Formules, prix, avantages, fiche d'inscription PDF, saison affichée |
| Messages | Lecture des messages du formulaire de contact, compteur des non lus |
| Paramètres | Informations du club, chiffres clés, environ 50 textes du site, réglages de l'IA |
| Administrateurs | Comptes et rôles |
| Journal | Historique des actions importantes |
| Jeux | Scores de chaque jeu, suppression d'un score, remise à zéro (fondateur uniquement) |

## 5. Exigences non fonctionnelles

- **Responsive** : utilisable sur téléphone, tablette et ordinateur.
- **Simplicité** : l'administration doit être utilisable sans formation technique.
- **Sécurité** : mots de passe chiffrés, protection des formulaires, rôles et droits, déconnexion automatique, journal des actions.
- **RGPD** : données limitées au nécessaire, suppression automatique des messages après 3 ans, mentions légales, aucun cookie publicitaire.
- **Performance** : site léger, images réduites automatiquement, bibliothèques hébergées dans le projet.
- **Référencement** : descriptions des pages, plan du site (sitemap), fichier robots.txt, aperçus lors du partage sur les réseaux sociaux.
- **Langue** : site entièrement en français (dates comprises).

## 6. Contraintes

- **Budget : 0 €.** Hébergement gratuit (InfinityFree), services gratuits (OpenStreetMap, Open-Meteo, offre gratuite de Gemini).
- **Hébergement mutualisé gratuit** : PHP et MySQL/MariaDB uniquement, pas d'envoi d'emails par PHP, temps d'exécution limité.
- **Maintenance par des non-techniciens** : tout le contenu doit être modifiable depuis l'admin.
- **Reprise possible par une autre personne** : code lisible, commenté en français, documenté.

## 7. Hors périmètre de la première version

Prévu pour plus tard (voir [idee-a-ajouter.txt](idee-a-ajouter.txt)) :

- espace membre pour les adhérents ;
- notification par email des nouveaux messages, et réponse aux messages depuis l'admin ;
- site multilingue ;
- chatbot d'aide par mots-clés.
