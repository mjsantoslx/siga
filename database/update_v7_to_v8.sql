-- =========================================================
-- SIGA v8 - Actualização a partir da versão v7
-- =========================================================
--
-- O N.º de Associado passa a CHAR(5), mantendo os valores
-- existentes e preservando os zeros à esquerda.
-- =========================================================

USE `siga`;

START TRANSACTION;

ALTER TABLE `associados`
  MODIFY COLUMN `Numero` CHAR(5) CHARACTER SET latin1
  COLLATE latin1_general_ci NOT NULL;

UPDATE `associados`
SET `Numero` = LPAD(CAST(`Numero` AS UNSIGNED), 5, '0');

COMMIT;
