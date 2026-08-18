-- =========================================================
-- SIGA v8 - Actualização a partir da versão v7
-- =========================================================
--
-- O N.º de Associado passa de INT para CHAR(5).
-- Exemplos: 00001, 00002, 00010, 00100.
-- =========================================================

USE `siga`;

START TRANSACTION;

ALTER TABLE `associados`
  MODIFY COLUMN `Numero` CHAR(5) NOT NULL;

UPDATE `associados`
SET `Numero` = LPAD(CAST(`Numero` AS UNSIGNED), 5, '0');

COMMIT;
