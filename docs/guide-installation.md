# Guide d'installation en local

> Pour installer le site sur un ordinateur Windows et travailler dessus, de zéro jusqu'au site qui tourne.

## 1. Prérequis

- **[Laragon](https://laragon.org/)** (édition Full) : il fournit Apache, PHP 8 et MySQL 8 d'un coup.
- **[Git](https://git-scm.com/)** (souvent inclus dans Laragon).
- Un éditeur de code, par exemple VS Code.

Lancer Laragon, puis cliquer sur **« Démarrer tout »** : Apache et MySQL doivent passer au vert.

## 2. Récupérer le code

Ouvrir le terminal de Laragon (bouton « Terminal ») :

```
cd C:\laragon\www
git clone https://github.com/Eythannne/cat-tennis.git
```

⚠ Le dossier doit s'appeler exactement **`cat-tennis`** : les adresses du site en local (`/cat-tennis/public`) en dépendent.

## 3. Configurer la connexion à la base

Copier `src/config/database.example.php` et nommer la copie **`database.php`**, dans le même dossier.

Les valeurs locales du modèle sont déjà celles de Laragon (`localhost`, base `cat_tennis`, utilisateur `root`, sans mot de passe). Les identifiants de la base en ligne ne servent pas en local.

> `database.php` est exclu de Git : il ne doit jamais être envoyé sur GitHub.

## 4. Créer la base de données

1. Ouvrir **phpMyAdmin** ou **HeidiSQL** (Laragon : bouton « Base de données »).
2. Créer une base nommée **`cat_tennis`**, interclassement **`utf8mb4_unicode_ci`**.
3. Sélectionner cette base, puis importer le fichier **`sql/schema.sql`**.

La base contient alors les 10 tables et les paramètres de départ.

## 5. Créer le compte fondateur

La base est vide : il faut créer le premier compte à la main.

1. Calculer le « hash » (la version chiffrée) du mot de passe dans le terminal de Laragon :
   ```
   php -r "echo password_hash('MonMotDePasse', PASSWORD_DEFAULT);"
   ```
2. Copier le résultat (il commence par `$2y$`) et exécuter dans l'onglet SQL de phpMyAdmin :
   ```sql
   INSERT INTO admins (nom, role, email, mot_de_passe)
   VALUES ('Prénom Nom', 'fondateur', 'adresse@email.fr', 'COLLER_LE_HASH_ICI');
   ```

## 6. Ouvrir le site

- Site public : **http://localhost/cat-tennis/public/**
- Administration : **http://localhost/cat-tennis/admin/**, avec l'email et le mot de passe du compte fondateur.

## 7. Ce qui n'est pas dans Git

| Élément | Comment le récupérer |
|---|---|
| Contenu de la base (articles, événements, comptes, paramètres) | Exporter la base depuis phpMyAdmin sur l'ordinateur d'origine, puis l'importer. ⚠ Ne jamais mettre cet export sur GitHub : il contient les comptes et la clé de l'IA. |
| Images et PDF envoyés (`public/assets/uploads/`) | Copier le dossier à la main |
| Clé API Gemini | La recoller dans Paramètres > ✨ Intelligence artificielle (compte fondateur) |

## 8. Travailler sur plusieurs ordinateurs

- En arrivant : `git pull` pour récupérer les dernières modifications.
- Avant de partir : `git add`, `git commit`, puis `git push`.
- Si une modification de la base a été faite entre-temps, exécuter la migration correspondante de `sql/migrations/` (voir [sql/README.md](../sql/README.md)).

## 9. Problèmes fréquents

| Problème | Solution |
|---|---|
| « Le site est momentanément indisponible » | MySQL n'est pas démarré, ou `database.php` est absent ou incorrect |
| Page introuvable (404) sur tout le site | Le dossier ne s'appelle pas `cat-tennis`, ou Apache n'est pas démarré |
| Le CSS ne s'affiche pas après une modification | Vérifier que le fichier est chargé avec `ressource()` (voir le dossier technique) ; sinon, recharger sans le cache : **Ctrl + F5** |
| Impossible de se connecter à l'admin | Vérifier le compte dans la table `admins` ; après 5 échecs, attendre 15 minutes |
