# Passation

> Si quelqu'un d'autre reprend le site : la liste de ce qu'il faut lui transmettre et vérifier.
>
> ⚠ **Ne jamais écrire de mot de passe, d'identifiant ni de clé dans ce fichier, dans le code ou sur GitHub.** Les transmettre en main propre ou par un canal privé, puis les faire changer.

## 1. Accès à transmettre

| Accès | À quoi il sert | Après la transmission |
|---|---|---|
| **Hébergeur** (compte InfinityFree) | Gestionnaire de fichiers, phpMyAdmin, nom de domaine | Changer le mot de passe du compte |
| **Base de données en ligne** (hôte, nom, utilisateur, mot de passe) | Connexion du site ; ces identifiants sont dans `src/config/database.php` sur l'hébergeur | Changer le mot de passe de la base, puis mettre à jour `database.php` sur l'hébergeur |
| **Dépôt GitHub** | Code source et historique | Ajouter le repreneur comme collaborateur, ou transférer le dépôt |
| **Compte fondateur** de l'admin | Accès complet, réglages de l'IA, journal | Le repreneur crée son propre compte ; le rôle fondateur est transféré dans la base (voir ci-dessous) |
| **Compte Google** de la clé Gemini | Génération d'articles par IA | Le repreneur crée sa propre clé sur aistudio.google.com/apikey et la colle dans Paramètres > IA |
| **Nom de domaine** (s'il y en a un) | Adresse du site | Transférer ou changer le titulaire |

## 2. Transférer le rôle fondateur

Le rôle fondateur ne se change que dans la base (phpMyAdmin > onglet SQL) :

```sql
-- L'ancien fondateur devient administrateur (ou supprimer son compte depuis l'admin ensuite)
UPDATE admins SET role = 'administrateur' WHERE email = 'ancien@email.fr';
-- Le repreneur devient fondateur (son compte doit déjà exister)
UPDATE admins SET role = 'fondateur' WHERE email = 'nouveau@email.fr';
```

Il ne doit y avoir **qu'un seul** compte fondateur.

## 3. Documents à lire en premier

1. [README.md](../README.md) : vue d'ensemble du projet.
2. [Guide d'installation](guide-installation.md) : faire tourner le site en local.
3. [Dossier technique](dossier-technique.md) : architecture, base de données, sécurité.
4. [Guide de mise en ligne](guide-mise-en-ligne.md) : publier une nouvelle version.
5. [Idées et évolutions](idee-a-ajouter.txt) : ce qui est fait et ce qui reste à faire.

## 4. Vérifications après la reprise

- [ ] Le repreneur se connecte avec son propre compte, et les anciens comptes inutiles sont supprimés.
- [ ] Tous les mots de passe transmis ont été changés.
- [ ] Il sait installer le site en local et le mettre en ligne.
- [ ] La génération par IA fonctionne avec sa propre clé.
- [ ] Les informations légales (Paramètres > Informations du club) sont à jour, en particulier le responsable de la publication.
