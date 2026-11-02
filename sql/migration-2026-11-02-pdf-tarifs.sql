-- Migration du 02/11/2026 — une fiche d'inscription PDF par formule de tarif
-- Base LOCALE (MySQL de Laragon). Pour la base en ligne : sql/migration-prod-2026-11-02-pdf-tarifs.sql

ALTER TABLE tarifs ADD COLUMN pdf VARCHAR(255) DEFAULT NULL AFTER mis_en_avant;
