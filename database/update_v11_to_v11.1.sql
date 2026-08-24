-- SIGA v11.1 - Actualização a partir da v11.0
USE `siga`;
START TRANSACTION;

ALTER TABLE `seccoes`
  DROP COLUMN `Activo`,
  ADD COLUMN `NominativoMasculino` varchar(50) NOT NULL AFTER `Designacao`,
  ADD COLUMN `NominativoFeminino` varchar(50) NOT NULL AFTER `NominativoMasculino`;

UPDATE `seccoes`
SET
 `NominativoMasculino` = CASE `Designacao`
  WHEN 'Colónia' THEN 'Castor'
  WHEN 'Alcateia' THEN 'Lobito'
  WHEN 'Tribo Júnior' THEN 'Escoteiro Júnior'
  WHEN 'Tribo Sénior' THEN 'Escoteiro Sénior'
  WHEN 'Clã' THEN 'Caminheiro'
  WHEN 'Chefia' THEN 'Escoteiro Chefe'
  ELSE `NominativoMasculino` END,
 `NominativoFeminino` = CASE `Designacao`
  WHEN 'Colónia' THEN 'Castora'
  WHEN 'Alcateia' THEN 'Lobita'
  WHEN 'Tribo Júnior' THEN 'Escoteira Júnior'
  WHEN 'Tribo Sénior' THEN 'Escoteira Sénior'
  WHEN 'Clã' THEN 'Caminheira'
  WHEN 'Chefia' THEN 'Escoteira Chefe'
  ELSE `NominativoFeminino` END;

COMMIT;
