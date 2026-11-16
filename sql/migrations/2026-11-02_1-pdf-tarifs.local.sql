-- Migration du 02/11/2026 — une fiche d'inscription PDF par formule de tarif
-- Base LOCALE (MySQL de Laragon). Pour la base en ligne : 2026-11-02_1-pdf-tarifs.prod.sql

ALTER TABLE tarifs ADD COLUMN pdf VARCHAR(255) DEFAULT NULL AFTER mis_en_avant;
