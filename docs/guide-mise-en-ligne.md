# Guide de mise en ligne

> Pour envoyer une nouvelle version du site sur l'hébergeur (InfinityFree), sans rien casser ni perdre.

## 1. Avant de commencer

- Toutes les modifications sont testées en local et commitées sur GitHub.
- Vérifier s'il y a une **nouvelle migration** dans `sql/migrations/` depuis la dernière mise en ligne.

## 2. Mettre à jour la base de données (seulement s'il y a une migration)

À faire **avant** d'envoyer les fichiers, sinon le nouveau code chercherait des colonnes qui n'existent pas encore.

1. Se connecter à InfinityFree, ouvrir **phpMyAdmin**.
2. Cliquer sur la base du site (`if0_…_cat_tennis`), puis sur l'onglet **SQL**.
3. Coller le contenu de la migration **`.prod.sql`** (ou sans suffixe), puis cliquer sur **Go**.

Les scripts `.prod` peuvent être relancés sans risque. Une erreur « Duplicate column » signifie que la migration était déjà faite.

## 3. Préparer le zip

Sélectionner dans le dossier du projet :

| ✅ À mettre | ❌ À ne pas mettre |
|---|---|
| `admin/`, `public/`, `src/`, `templates/`, `.htaccess` | `.git/` (historique, inutile en ligne) |
| | `.git.zip` (ancienne sauvegarde contenant d'anciens identifiants) |
| | `public/assets/uploads/` (images de test : elles écraseraient celles du site en ligne) |

`sql/`, `docs/`, `README.md` et `.gitignore` sont inutiles en ligne, mais sans danger : le `.htaccess` les rend inaccessibles depuis le web.

`src/config/database.php` détecte tout seul s'il tourne en local ou en ligne, et il est déjà sur l'hébergeur : on peut l'inclure ou non.

Clic droit sur la sélection, puis « Compresser dans un fichier ZIP ». Si `public` est sélectionné en entier, ouvrir le zip et supprimer `public/assets/uploads` **à l'intérieur du zip**.

## 4. Envoyer les fichiers

1. InfinityFree → **Gestionnaire de fichiers** → dossier **`htdocs`**.
2. Envoyer le zip, puis l'**extraire par-dessus** le contenu existant.

⚠ Ne jamais supprimer `htdocs` avant : il contient les photos, PDF et fichiers envoyés depuis l'admin en ligne, ainsi que la configuration de la base.

## 5. Vérifier après la mise en ligne

- Ouvrir le site : les nouveaux CSS et JavaScript sont chargés automatiquement (leur adresse contient leur date de modification), il n'est plus nécessaire de faire Ctrl + F5.
- Parcourir les pages modifiées, sur ordinateur et sur téléphone.
- Se connecter à l'admin et tester la fonctionnalité ajoutée.
- En cas de page blanche ou d'erreur : vérifier qu'une migration n'a pas été oubliée (étape 2).

## 6. Spécificités d'InfinityFree

| Point | Conséquence |
|---|---|
| Base **MariaDB** (et non MySQL) | Les scripts `.prod` utilisent `IF NOT EXISTS`, propre à MariaDB |
| Serveur hors de France | Le fuseau horaire est forcé à Paris dans `src/init.php` (PHP et base) |
| **Envoi d'emails par PHP bloqué** | Aucune fonctionnalité n'envoie d'email ; il faudra un service externe (SMTP, Brevo…) le jour où ce sera nécessaire |
| Temps d'exécution limité | La génération par IA s'arrête au bout de 45 secondes au maximum |
| Hébergement gratuit | Suffisant pour la phase de test ; un hébergement payant et un nom de domaine sont envisageables pour la version officielle |
