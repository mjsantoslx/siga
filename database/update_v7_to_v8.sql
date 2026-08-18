-- SIGA v8 - Actualização a partir da v7
USE `siga`;

START TRANSACTION;

-- Utilizadores não possuem número.
ALTER TABLE `utilizadores` DROP COLUMN `Numero`;

-- Apenas associados possuem N.º de Associado, com 5 caracteres.
ALTER TABLE `associados`
  MODIFY COLUMN `Numero` CHAR(5) CHARACTER SET latin1
  COLLATE latin1_general_ci NOT NULL;

UPDATE `associados`
SET `Numero` = LPAD(CAST(`Numero` AS UNSIGNED), 5, '0');

COMMIT;
