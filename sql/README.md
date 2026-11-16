# Base de données

## Créer une base neuve (nouveau PC, nouvel hébergeur)

Importer **`schema.sql`** dans une base vide (encodage `utf8mb4_unicode_ci`) : il contient toutes les tables à jour et les paramètres de départ.
Créer ensuite le compte fondateur avec la ligne commentée à la fin du fichier.

## Mettre à jour une base existante

Le dossier **`migrations/`** contient l'historique des changements de structure, dans l'ordre d'exécution :

```
AAAA-MM-JJ_N-description[.local|.prod].sql
```

- **Date + numéro** : l'ordre dans lequel les exécuter.
- **`.local`** : pour le MySQL de Laragon (en local).
- **`.prod`** : pour la base en ligne (InfinityFree, MariaDB), qui accepte `IF NOT EXISTS`. Ces scripts peuvent être relancés sans risque.
- **Sans suffixe** : fonctionne sur les deux bases.

| Migration | Contenu |
|---|---|
| `2026-10-27_1-mise-a-niveau` | Encodage complet (emojis), publication programmée, horaires du secrétariat, type d'événement « vacances », entraîneurs, adresse, tarifs. Version `.prod` : ajoute aussi les colonnes qui manquaient à l'ancienne base en ligne |
| `2026-10-28_1-roles` | Rôles des comptes admin (administrateur / modérateur) |
| `2026-10-28_2-fondateur` | Rôle fondateur (à exécuter après les rôles) |
| `2026-10-28_3-journal` | Journal des actions importantes |
| `2026-10-28_4-bureau` | Équipe administrative (bureau) |
| `2026-11-02_1-pdf-tarifs` | Une fiche d'inscription PDF par formule de tarif |

Toutes ces migrations sont **déjà appliquées** sur la base locale et sur la base en ligne. Elles ne servent plus qu'à mettre à jour une ancienne copie de la base.

## Ajouter une migration

1. Créer le fichier dans `migrations/` en suivant le nom ci-dessus (une version `.local` et une version `.prod` si la syntaxe diffère).
2. L'exécuter en local, puis en ligne dans phpMyAdmin (onglet SQL), **avant** d'envoyer les fichiers du site.
3. Reporter le changement dans `schema.sql`.
4. Ajouter une ligne dans le tableau ci-dessus.
