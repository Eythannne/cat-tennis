# Rôles et droits

L'administration distingue **4 rôles**. Chaque compte en a un seul.

| Rôle | Pour qui | En résumé |
|---|---|---|
| 👑 **Fondateur** | Le créateur et responsable technique du site (un seul compte) | Accès complet, réglages de l'IA, journal des actions, scores des jeux |
| 🛡️ **Administrateur** | Membres du bureau (président·e, secrétaire…) | Tout le site, y compris les comptes et les paramètres (sauf l'IA) |
| 🧑‍💼 **Modérateur** | Entraîneurs, bénévoles qui publient | Tout le contenu, mais ni les réglages ni les comptes |
| 🧑‍🏫 **Peda** | Entraîneurs (page Pédagogie) | Aucun droit pour l'instant : uniquement « Mon compte » (voir plus bas) |

## Tableau complet

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
| **Jeux** : voir les scores, en supprimer, remettre à zéro | ✅ | ❌ Masqué et refusé | ❌ Masqué et refusé |
| Bloc « Dernières actions » du Dashboard | ✅ | ❌ | ❌ |
| **Mon compte** | | | |
| Changer son propre mot de passe | ✅ | ✅ | ✅ |
| Changer son propre nom ou email | ✅ | ✅ | ❌ Doit le demander à un administrateur |
| Changer son propre rôle | ❌ | ❌ | ❌ |
| Supprimer son propre compte | ❌ | ❌ | ❌ |

## Rôle Peda

Le rôle **Peda** est prévu pour les entraîneurs. Pour l'instant, il n'a **aucun droit** dans l'administration : un compte Peda peut se connecter, mais il est renvoyé vers « Mon compte », et toutes les autres pages lui sont refusées.

La **page Pédagogie** (lien « Espace pédagogique » en bas du site) est pour l'instant **ouverte à tous**, pendant la phase de test du site. À terme, elle sera réservée aux comptes Peda (voir `docs/idee-a-ajouter.txt`).

## À retenir

- **Un seul fondateur.** Ce rôle ne se donne que directement dans la base de données : personne ne peut créer un second fondateur depuis l'admin. Une tentative de modifier ou de supprimer le compte fondateur par quelqu'un d'autre est refusée et notée dans le journal.
- **Un administrateur peut gérer les autres administrateurs**, y compris les supprimer ou les repasser modérateurs. Seul le compte fondateur est protégé.
- **Les droits sont vérifiés par le serveur**, pas seulement masqués à l'écran : une page interdite tapée à la main ou un formulaire envoyé en contournant l'affichage est refusé.
- **Un changement de rôle ou une suppression de compte s'applique immédiatement**, dès la page suivante.
- **Pour tous les rôles** : déconnexion automatique après 30 minutes d'inactivité, ou 15 secondes après avoir quitté l'administration.

> Côté code, voir la partie « Rôles et droits » du [dossier technique](dossier-technique.md).
