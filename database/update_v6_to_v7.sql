-- =========================================================
-- SIGA v7 - Actualização a partir da versão v6
-- =========================================================
-- Introduz o N.º de Associado, sequencial e único, começando em 1.
-- =========================================================

USE `siga`;
START TRANSACTION;

ALTER TABLE `associados`
  ADD COLUMN `Numero` int(11) NULL AFTER `Id`;

SET @numero := 0;
UPDATE `associados`
SET `Numero` = (@numero := @numero + 1)
ORDER BY `Id`;

ALTER TABLE `associados`
  MODIFY COLUMN `Numero` int(11) NOT NULL,
  ADD UNIQUE INDEX `uk_associados_numero` (`Numero`);

COMMIT;
