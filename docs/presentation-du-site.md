# Site internet du Club Aulnaysien de Tennis (CAT) — dossier de présentation

> **À l'attention de Claude** : ce document décrit en détail le site internet que j'ai créé pour mon club de tennis. Je dois le **présenter oralement aux membres du club mercredi**. J'ai besoin de ton aide pour construire le **déroulé de ma présentation** : ordre des parties, ce que je montre en direct, ce que je dis, le timing et les réponses aux questions probables. Le public est composé de membres du club (bureau, entraîneurs, adhérents), **pas de spécialistes en informatique** : il faut parler simplement, en montrant surtout ce que le site apporte au club.

---

## 1. Le contexte

- **Le club** : Club Aulnaysien de Tennis (CAT), à Aulnay-sous-Bois (93). Adresse : Stade du Moulin Neuf, 4 avenue du Maréchal Juin, 93600 Aulnay-sous-Bois.
- **Le créateur** : moi, Ethan, membre du club. J'ai conçu et développé tout le site moi-même, du design à la base de données, avec l'aide d'une IA de programmation (Claude Code).
- **Le calendrier** : projet commencé le 20 octobre 2026, environ 3 semaines de travail.
- **L'objectif** :
  - donner au club un site moderne, clair et à jour, qui donne envie de s'inscrire ;
  - permettre au club de le **gérer lui-même**, sans savoir coder, grâce à un espace d'administration ;
  - centraliser les informations : actualités, calendrier, tarifs, fiches d'inscription, équipe, contact.
- **L'état actuel** : le site est **en ligne en version de test**, sur un hébergement gratuit (InfinityFree). Il fonctionne entièrement. Il reste à compléter quelques informations officielles avant de l'annoncer au public (voir partie 8).

---

## 2. Le site public (ce que voit tout le monde)

Le site est **responsive** : il s'adapte aux ordinateurs, aux tablettes et aux téléphones. Sur téléphone, le menu se replie dans un bouton.

Le menu contient : **Accueil · Le club · Actualités · Calendrier · Contact**, plus un menu **« Mon compte »**. Ce menu donne accès à l'administration et annonce un futur « Espace membre » (marqué « Bientôt »).

### Accueil
- Grande bannière d'accueil avec le nom du club et des boutons d'action.
- Présentation rapide du club avec des **chiffres clés** (nombre d'adhérents, année de création, nombre d'entraîneurs…). Le nombre d'entraîneurs se calcule tout seul.
- **Dernières actualités** : les articles les plus récents.
- **Agenda** : les prochains événements du club.
- Encart **réservation de courts**, avec un lien vers **Ten'up**, la plateforme de la Fédération française de tennis utilisée pour réserver.
- **Widget météo** à Aulnay-sous-Bois : la météo du jour et des prochains jours, pratique avant de venir jouer.

### Le club
- **Notre histoire** : présentation du club (texte modifiable depuis l'admin).
- **Les terrains** : présentation des courts.
- **L'équipe administrative (le bureau)** : cartes avec photo, nom et fonction de chaque membre, et une photo de groupe possible.
- **Les entraîneurs** : même présentation, avec photo de groupe possible.
- **Les tarifs** : une carte par formule (adulte, enfant…). Chaque formule peut avoir sa **fiche d'inscription en PDF**, que l'on peut voir dans une fenêtre, télécharger ou imprimer directement depuis le site.

### Actualités
- La liste des articles du club, avec image, date (en français) et extrait.
- Des **archives par mois** pour retrouver les anciens articles.
- Un onglet **Galerie photos** : les photos du club classées par catégorie (filtrables). Un clic ouvre la photo en plein écran, avec navigation (précédente / suivante) et miniatures.
- Chaque article a sa propre page, avec une bannière et une mise en forme soignée (intertitres, gras, listes, liens).

### Calendrier
- Un vrai calendrier interactif, en vue **mois** ou en vue **liste** (la vue liste est utilisée automatiquement sur téléphone).
- Les événements sont colorés par type : **Tournoi, Cours / Stage, Animation, Vacances scolaires, Autre**, avec une légende.
- Les **jours fériés** français sont affichés automatiquement (calculés chaque année, y compris Pâques, l'Ascension et la Pentecôte).- Un clic sur un événement ouvre une fenêtre avec les détails (date, horaires, description).
- La semaine commence le lundi, et tout est en français.

### Contact
- **Formulaire de contact** avec choix du sujet : Inscription au club, Cours & entraînements, Tournois & compétitions, Réservation de courts, Autre.
- **Protection anti-spam** invisible pour les visiteurs (piège à robots, détection des envois trop rapides, limite du nombre de messages).
- **Carte interactive** (OpenStreetMap) pour situer le club.
- Coordonnées du club (adresse, email, téléphone, horaires du secrétariat).

### Autres éléments
- **Mentions légales** complètes : éditeur, hébergeur, RGPD, cookies, services tiers.
- **Page d'erreur 404** personnalisée, aux couleurs du club.
- **Référencement Google** : plan du site automatique (sitemap) et descriptions des pages, pour que le site soit bien trouvé sur Google.

---

## 3. L'espace d'administration (le « back-office »)

C'est la partie la plus importante pour le club : **tout le contenu du site se modifie depuis l'administration, sans toucher au code**. On y accède par « Mon compte » > « Administration », avec un email et un mot de passe.

Le menu est organisé par thèmes : **Contenu**, **Le club**, **Échanges** et **Administration**.

### Dashboard (page d'accueil de l'admin)
- Un message d'accueil personnalisé (« Bonjour / Bonsoir + prénom ») avec la date, et une phrase de résumé qui change selon la situation : messages non lus, brouillons en attente, prochain événement dans X jours…
- Des boutons rapides : **+ Article**, **+ Événement**, **Voir le site**.
- Des chiffres cliquables : articles en ligne, événements à venir, photos, messages reçus.
- **Prochainement au club** : les prochains événements.
- **Derniers messages** reçus par le formulaire de contact (les non lus ressortent en jaune).
- **Derniers articles**, avec leur statut.
- Un **mini-calendrier du mois**, avec les événements, les vacances scolaires et les jours fériés.
- **Dernières actions** (visible par le fondateur uniquement) : ce qui a été fait récemment dans l'admin.

### Contenu
- **Articles** :
  - écrire, modifier et supprimer des articles, avec une image (recadrage intégré au bon format) ;
  - barre de mise en forme simple : gras, italique, intertitres, listes, liens ;
  - **brouillon** ou **publication immédiate** ;
  - **publication programmée** : on choisit une date future, et l'article apparaît tout seul ce jour-là ;
  - **aperçu** de l'article tel qu'il apparaîtra, avant de publier ;
  - **✨ Génération par intelligence artificielle** (voir partie 5).
- **Événements** : ajouter les tournois, stages, animations et vacances avec dates, horaires, type et description. Ils apparaissent automatiquement dans le calendrier du site et sur l'accueil.
- **Galerie** : envoyer des photos (même directement depuis un téléphone : elles sont réduites automatiquement pour ne pas alourdir le site), avec catégories et ordre d'affichage.

### Le club
- **Entraîneurs** et **Bureau** : fiches avec photo recadrée, nom et rôle, ordre d'affichage modifiable, et photo de groupe avec légende.
- **Tarifs** : formules, prix, description, et **fiche d'inscription PDF** pour chaque formule.

### Échanges
- **Messages** : tous les messages envoyés par le formulaire de contact, avec un compteur des non lus dans le menu. On peut les lire, les marquer comme lus et les supprimer.

### Administration
- **Paramètres**, en 4 onglets :
  1. **Informations du club** : nom, adresse, email, téléphone, horaires, présentation, informations légales (responsable de publication, numéro RNA, SIRET).
  2. **Chiffres clés** affichés sur l'accueil et sur la page Le club.
  3. **Textes du site** : environ 50 textes du site public (titres, sous-titres, boutons, paragraphes) modifiables sans code. Chaque texte peut revenir à sa version d'origine en un clic.
  4. **Intelligence artificielle** : réglages de la génération d'articles (fondateur uniquement).
- **Administrateurs** : gestion des comptes qui ont accès à l'admin.
- **Journal** : historique des actions importantes (fondateur uniquement).
- **Mon compte** : chacun peut changer son mot de passe.

---

## 4. Les rôles et les droits

Il existe **3 rôles** pour les comptes de l'administration.

| Fonctionnalité | 👑 Fondateur | 🛡️ Administrateur | 🧑‍💼 Modérateur |
|---|:---:|:---:|:---:|
| **Contenu** | | | |
| Articles : créer, modifier, publier, programmer, supprimer | ✅ | ✅ | ✅ |
| ✨ Générer un article avec l'IA | ✅ | ✅ | ✅ |
| Événements | ✅ | ✅ | ✅ |
| Galerie | ✅ | ✅ | ✅ |
| **Le club** | | | |
| Entraîneurs, Bureau, Tarifs (fiches PDF comprises) | ✅ | ✅ | ✅ |
| **Échanges** | | | |
| Messages : lire, marquer comme lu, supprimer | ✅ | ✅ | ✅ |
| **Paramètres** | | | |
| Infos du club, chiffres clés, textes du site | ✅ Modifier | ✅ Modifier | 👁️ Lecture seule |
| Intelligence artificielle (clé, modèle, consigne) | ✅ Modifier | 👁️ Lecture seule | 👁️ Lecture seule |
| Voir la clé API de l'IA en entier | ❌ | ❌ | ❌ |
| **Comptes** (page Administrateurs) | | | |
| Voir la page Administrateurs | ✅ | ✅ | ❌ Masquée et refusée |
| Créer un compte administrateur ou modérateur | ✅ | ✅ | ❌ |
| Modifier ou supprimer un administrateur ou un modérateur | ✅ | ✅ | ❌ |
| Changer le rôle d'un autre compte | ✅ | ✅ | ❌ |
| Modifier le compte fondateur | ✅ Le sien uniquement | ❌ | ❌ |
| Supprimer le compte fondateur | ❌ | ❌ | ❌ |
| **Journal** des actions | ✅ | ❌ Masqué et refusé | ❌ Masqué et refusé |
| Bloc « Dernières actions » du Dashboard | ✅ | ❌ | ❌ |
| **Mon compte** | | | |
| Changer son propre mot de passe | ✅ | ✅ | ✅ |
| Changer son propre nom ou email | ✅ | ✅ | ❌ Doit le demander à un administrateur |
| Changer son propre rôle | ❌ | ❌ | ❌ |
| Supprimer son propre compte | ❌ | ❌ | ❌ |

**À retenir**
- **Fondateur** (moi) : un seul compte, protégé. Ce rôle ne se donne que directement dans la base de données : personne ne peut créer un second fondateur, ni modifier ou supprimer le compte fondateur depuis l'admin. Une tentative est refusée et notée dans le journal.
- **Administrateur** : gère tout le site, y compris les comptes (sauf le fondateur) et les paramètres (sauf l'IA). Rôle adapté au bureau (président, secrétaire…).
- **Modérateur** : gère tout le **contenu** (articles, événements, photos, équipe, tarifs, messages), mais ne peut changer ni les réglages ni les comptes. Rôle adapté aux entraîneurs ou aux bénévoles qui publient les actualités.
- Un changement de rôle ou une suppression de compte s'applique **immédiatement**.

---

## 5. La génération d'articles par intelligence artificielle

Fonctionnalité phare, à montrer en direct si possible.

- Sur la page Articles, on écrit simplement des **notes en vrac** dans un champ « Infos / notes brutes ». Par exemple : « Fin du tournoi Open du CAT, animation Olympiade, il commence à faire froid, couvrez-vous bien ».
- On clique sur **✨ Générer** : l'IA **Gemini de Google** rédige en quelques secondes un **article complet et bien structuré** (introduction, intertitres, conclusion), dans le ton du club.
- Le texte généré reste **entièrement modifiable** : on le relit, on corrige, puis on publie. Les notes brutes, elles, ne sont jamais publiées.
- **Garde-fous** :
  - l'IA a pour consigne de **ne rien inventer** (ni date, ni prix, ni nom, ni score) qui ne figure pas dans les notes ;
  - le nombre de générations est limité, pour rester dans le quota gratuit ;
  - si les serveurs de Google sont surchargés, un autre modèle est essayé automatiquement.
- La consigne donnée à l'IA (ton, style, longueur) est modifiable par le fondateur dans les Paramètres.
- **Coût** : gratuit, grâce à l'offre gratuite de Google.
- **Intérêt pour le club** : publier une actualité prend 2 minutes au lieu de 20. Même quelqu'un qui n'aime pas écrire peut faire vivre le site.

---

## 6. Sécurité et données personnelles

- **Mots de passe chiffrés** : personne, même pas moi, ne peut lire le mot de passe d'un compte.
- **Déconnexion automatique** de l'admin après 30 minutes d'inactivité, ou peu après avoir quitté l'admin (onglet fermé). Utile si quelqu'un se connecte sur un ordinateur partagé du club.
- **Protection contre les attaques courantes** : jetons de sécurité sur tous les formulaires, limitation des tentatives de connexion, vérification des fichiers envoyés (images et PDF).
- **Anti-spam** sur le formulaire de contact.
- **Journal des actions** : on sait qui a fait quoi et quand (connexions, création et suppression de comptes, publications, modifications importantes). Il est conservé 12 mois.
- **RGPD** :
  - les messages de contact sont **supprimés automatiquement après 3 ans** ;
  - les mentions légales détaillent les données collectées et les services tiers (carte, météo, polices d'écriture) ;
  - aucun cookie publicitaire.
- **Clé de l'IA protégée** : elle n'est jamais affichée en entier dans l'admin, et seul le fondateur peut la changer.

---

## 7. Côté technique (en quelques mots, si on me pose la question)

- Site développé **sur mesure** (pas de WordPress ni de Wix) en **PHP** et **MySQL**, avec HTML, CSS et JavaScript.
- **Hébergement gratuit** (InfinityFree) pour la phase de test : **0 € de coût** aujourd'hui.
- Le code est sauvegardé et versionné sur **GitHub** : chaque modification est historisée et peut être annulée.
- Le site est léger et rapide (photos réduites automatiquement, pas de publicité).
- Le **nom de domaine** (par exemple une adresse en « .fr ») et un **hébergement plus solide** sont des options pour la version officielle. Petit coût annuel, à décider avec le bureau.

---

## 8. Avant l'ouverture officielle (ce qu'il reste à faire)

- **Informations légales obligatoires** pour une association : le nom du **responsable de la publication** (en général le président ou la présidente) et le **numéro RNA** de l'association. Il me faut ces informations de la part du bureau.
- **Contenu réel** : remplacer les articles de test, compléter les fiches d'inscription PDF manquantes, ajouter les photos du club, vérifier les tarifs, les horaires et les textes.
- **Créer les comptes** des personnes qui géreront le site, et choisir leur rôle (administrateur ou modérateur).
- Décider d'un éventuel **nom de domaine** propre au club.

---

## 9. Les évolutions possibles (version 2)

- **Espace membre** (déjà annoncé « Bientôt » dans le menu) : compte adhérent, informations personnelles, etc.
- **Notification par email** quand un message de contact arrive.
- **Répondre aux messages directement depuis l'admin**, avec un historique de la conversation.
- **Site multilingue** : anglais, espagnol, allemand, ukrainien, arabe… (beaucoup de langues sont parlées dans le 93).
- Toute idée des membres du club est la bienvenue : le site est fait sur mesure, donc il peut évoluer selon les besoins.

---

## 10. Points forts à mettre en avant pendant la présentation

1. **Le club est autonome** : tout se modifie depuis l'admin, sans compétence technique (textes, tarifs, équipe, photos, événements).
2. **Gratuit** aujourd'hui, et fait sur mesure pour le CAT.
3. **Moderne et adapté au téléphone**, là où la plupart des gens consultent les sites.
4. **L'IA qui rédige les articles** à partir de quelques notes : effet « waouh » garanti en démonstration.
5. **Tout au même endroit** : calendrier, inscriptions en PDF, réservation Ten'up, contact, galerie.
6. **Sécurisé**, avec des rôles adaptés à chacun (bureau, entraîneurs, bénévoles).

## 11. Idées de démonstration en direct

- Montrer le site public sur grand écran, **puis sur un téléphone**.
- Dans l'admin : **créer un article avec ✨ Générer** à partir de notes dictées par la salle, puis le publier et le montrer sur le site.
- **Ajouter un événement** et le voir apparaître immédiatement dans le calendrier.
- **Modifier un texte du site** (par exemple un titre de l'accueil) dans Paramètres > Textes du site, et montrer le résultat.
- Montrer la **fiche d'inscription PDF** depuis la page Tarifs.
- Envoyer un **message par le formulaire de contact** et le montrer arriver dans l'admin.

## 12. Questions probables du public (et éléments de réponse)

- **« Combien ça coûte ? »** Rien pour l'instant : hébergement gratuit, IA gratuite. Seuls un nom de domaine et un hébergement plus solide auraient un petit coût annuel, si le club le souhaite.
- **« Qui pourra modifier le site ? »** Les personnes à qui on crée un compte, avec le rôle adapté (voir partie 4). Pas besoin de savoir coder.
- **« Et si tu n'es plus disponible ? »** Le contenu se gère entièrement depuis l'admin. Le code est sauvegardé sur GitHub et documenté, donc une autre personne pourrait reprendre le projet.
- **« L'IA peut-elle écrire n'importe quoi ? »** Elle a pour consigne de ne rien inventer, et rien n'est publié sans relecture : le texte généré se modifie avant publication.
- **« Nos données sont-elles protégées ? »** Mots de passe chiffrés, messages supprimés après 3 ans, mentions légales et RGPD en place, aucun cookie publicitaire.
- **« Peut-on réserver un court sur le site ? »** La réservation se fait sur Ten'up (la plateforme de la FFT), et le site y renvoie directement.
- **« Quand le site sera-t-il officiel ? »** Dès que le bureau m'aura transmis les informations légales et le contenu réel (voir partie 8).
