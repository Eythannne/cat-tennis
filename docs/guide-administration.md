# Guide d'utilisation de l'administration

> Pour les membres du club qui gèrent le site. Aucune connaissance technique n'est nécessaire.

## Se connecter

- Depuis le site : menu **« Mon compte » > « 🔐 Administration »**.
- Se connecter avec son **email** et son **mot de passe**, transmis par un administrateur.
- **Changer son mot de passe** dès la première connexion : bas du menu > **👤 Mon compte**.
- Pour des raisons de sécurité, la session se ferme seule après **30 minutes sans activité**, ou peu après avoir quitté l'administration.

Ce que chacun peut faire dépend de son rôle (fondateur, administrateur ou modérateur) : voir [roles-et-droits.md](roles-et-droits.md).

---

## 📊 Dashboard

La page d'accueil de l'administration résume l'activité :
- messages non lus, brouillons en attente, prochain événement ;
- boutons rapides **＋ Article** et **＋ Événement** ;
- derniers messages et articles ;
- mini-calendrier du mois (flèches ‹ › pour changer de mois).

---

## 📰 Publier un article

1. Menu **Articles**. Le formulaire « Nouvel article » est en haut de la page.
2. Renseigner le **titre**.
3. Écrire le texte dans **« Contenu généré »**. Deux possibilités :
   - **l'écrire soi-même**, avec la barre de mise en forme (gras, italique, intertitre, listes, lien) ;
   - **le faire rédiger par l'IA** : écrire quelques notes en vrac dans **« Infos / notes brutes »**, puis cliquer sur **✨ Générer**. Le texte apparaît en quelques secondes. **Le relire et le corriger** avant de publier.
4. Ajouter une **image** (facultatif) : choisir un fichier, puis le recadrer au bon format dans la fenêtre qui s'ouvre.
5. **Date de publication** (facultatif) : laisser vide pour publier tout de suite, ou choisir une date future pour que l'article apparaisse automatiquement ce jour-là.
6. Cliquer sur :
   - **🚀 Publier** : l'article est en ligne (ou programmé) ;
   - **💾 Enregistrer en brouillon** : l'article est gardé, mais invisible sur le site ;
   - **👁️ Aperçu** : voir le rendu final dans un nouvel onglet, sans rien enregistrer.

Dans la liste en dessous : **✏️ Modifier**, **⏸️ Dépublier** (repasse en brouillon), **🚀 Publier** ou **🗑️ Supprimer**.

> 💡 Les notes brutes ne sont jamais publiées ni enregistrées. N'y mettez pas d'informations personnelles (téléphone, adresse…).

---

## 📅 Ajouter un événement

1. Menu **Événements**.
2. Renseigner le **titre**, le **type** (Tournoi, Cours / Stage, Animation, Vacances scolaires, Autre), la **date de début** et, si besoin, la **date de fin** et une **description**.
3. Cliquer sur **Créer**.

L'événement apparaît aussitôt dans le **calendrier du site**, sur l'**accueil** (prochains événements) et dans le Dashboard. La couleur dépend du type. Les jours fériés s'affichent tout seuls ; les **vacances scolaires** sont à ajouter à la main, avec le type « Vacances scolaires ».

---

## 📷 Ajouter des photos à la galerie

1. Menu **Galerie**.
2. Choisir une photo, lui donner un **titre** et une **catégorie** (par exemple « Tournoi », « Cours »). Les catégories déjà utilisées sont proposées automatiquement.
3. Envoyer : la photo est **réduite automatiquement**, donc on peut envoyer directement une photo prise au téléphone (10 Mo maximum).

Les photos s'affichent dans l'onglet Galerie de la page Actualités, filtrables par catégorie. On peut modifier le titre ou la catégorie, changer l'ordre, ou supprimer une photo.

---

## 🎾 Entraîneurs et 🏛️ Bureau

Les deux pages fonctionnent de la même façon :
- **Ajouter une personne** : prénom, nom, fonction (par exemple « Président », « Entraîneur DE »), courte description et photo recadrée.
- **Changer l'ordre d'affichage** avec les flèches ↑ ↓.
- **Photo de groupe** (facultatif) : une photo de toute l'équipe avec une légende, affichée au-dessus des cartes sur la page Le club.

Si aucun membre du bureau n'est renseigné, la section « L'équipe administrative » est masquée sur le site.

---

## 💶 Tarifs et fiches d'inscription

1. Menu **Tarifs**.
2. **Saison** : l'année affichée au-dessus des tarifs (par exemple « 2026-2027 »).
3. Pour chaque **formule** :
   - nom, public visé, prix, période (« /an »…) ;
   - points importants, un par ligne ;
   - « mettre en avant » pour la faire ressortir sur le site ;
   - **fiche d'inscription PDF** (10 Mo maximum).
4. Changer l'ordre avec les flèches.

Sur le site, les visiteurs peuvent voir, télécharger ou imprimer la fiche PDF de chaque formule.

---

## ✉️ Messages

- Les messages envoyés par le formulaire de contact arrivent ici. Le nombre de **non lus** s'affiche dans le menu.
- Cliquer sur un message pour le lire : il passe en « lu ». **✉️ Marquer comme non lu** le fait redevenir « Nouveau », pour le traiter plus tard.
- **↩️ Répondre** ouvre le logiciel de messagerie de l'ordinateur, avec l'adresse de la personne déjà remplie.
- Les messages sont **supprimés automatiquement après 3 ans** (RGPD). On peut aussi les supprimer à la main.

---

## ⚙️ Paramètres

| Onglet | Contenu |
|---|---|
| 🏛️ Informations du club | Nom, adresse, email, téléphone, horaires du secrétariat, présentation du club, **informations légales** (responsable de publication et numéro RNA, obligatoires pour une association) |
| 🔢 Chiffres clés | Les 3 chiffres affichés sur l'accueil et la page Le club (adhérents, courts, année de création…) |
| ✏️ Textes du site | Environ 50 textes des pages publiques (titres, sous-titres, boutons, paragraphes), classés par page. **↺ Texte d'origine** remet le texte par défaut. Les textes modifiés sont marqués « modifié ». |
| ✨ Intelligence artificielle | Clé API Gemini, modèle et consigne donnée à l'IA (modifiable par le fondateur uniquement) |

---

## 👥 Administrateurs (administrateurs et fondateur)

- **Créer un compte** : nom, email, mot de passe provisoire (8 caractères minimum) et rôle (administrateur ou modérateur). Transmettre le mot de passe à la personne, qui le changera dans « Mon compte ».
- **Modifier un compte** : nom, email, rôle, ou nouveau mot de passe (si la personne l'a oublié).
- **Supprimer un compte** : l'accès est coupé immédiatement.

---

## 🧑‍🏫 Page Pédagogie (entraîneurs)

Lien **« Espace pédagogique »** tout en bas du site (dans la ligne du copyright). Pour l'instant, la page est accessible sans compte.

**🎉 Animations** : la bibliothèque des animations de club.
- **＋ Nouvelle animation** : nom, catégorie (tennistique, extrasportif ou autre, à préciser), lieu (et une précision), niveaux concernés, âges, durée, prix (coût pour le club), déroulement.
- Cocher **« Besoin de main d'œuvre »** pour indiquer combien de personnes il faut, et **« Demander une participation »** pour indiquer le prix demandé aux membres.
- Sur chaque fiche : **Modifier**, **Planifier** (ouvre le calendrier avec l'animation déjà choisie) et 🗑️ supprimer. Une recherche et un filtre par catégorie aident à retrouver une animation.

**📅 Calendrier** : le calendrier interne des entraîneurs, invisible sur le site.
- **＋ Planifier une animation** : choisir une animation de la bibliothèque (ou saisir un titre libre), les dates et une description.
- **🌐 Publier sur le calendrier public** : l'animation apparaît dans le calendrier que voient tous les visiteurs. Une fois publiée, toute modification est aussi appliquée au calendrier public. **↩️ Retirer du calendrier public** l'enlève du site, mais elle reste dans le calendrier interne.
- Cliquer sur une animation du calendrier ouvre sa fiche de modification. Violet = interne, vert = publiée.

**🎾 Exercices**, en trois parties :
- **Bibliothèque d'exercices** : une fiche par exercice (description, à quoi ça sert, dans quel cas l'utiliser, points d'attention), avec une recherche.
- **Programmation annuelle** : le document PDF du directeur sportif, affiché dans la page, avec des notes sur la répartition des thèmes. On peut le remplacer ou le supprimer.
- **Critères de passage** : pour chaque étape (Blanc → Violet → Rouge → Orange → Vert → Dur → Pôle compétition), les critères qu'un enfant doit remplir. Une ligne par critère, puis **Enregistrer les critères**.

---

## 🕹️ Jeux (fondateur uniquement)

Sur le site, une balle de tennis en bas à gauche de chaque page mène à l'**espace Jeux**, qui regroupe les petits jeux du site (pour l'instant « Un petit échange »). Les visiteurs y entrent un pseudo, et leurs meilleurs scores apparaissent dans le top 15 de chaque jeu.

Dans l'admin, la page **Jeux** a un onglet par jeu, qui permet de :
- voir les chiffres (scores enregistrés, joueurs, meilleur score) et tous les scores ;
- **supprimer un score**, par exemple un pseudo insultant ou un score suspect ;
- **remettre le tableau du jeu à zéro**, par exemple au début d'une nouvelle saison. Cette action est définitive.

Les pseudos grossiers sont refusés automatiquement, et les scores impossibles sont rejetés par le serveur.

---

## 📜 Journal (fondateur uniquement)

L'historique des actions importantes : connexions (et tentatives échouées), comptes créés ou supprimés, articles publiés, paramètres modifiés… On peut filtrer par catégorie et par compte. Les entrées sont conservées 12 mois.
