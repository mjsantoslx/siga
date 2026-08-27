-- SIGA V14 -> V14.1
USE siga;
ALTER TABLE associados ADD COLUMN NominativoOutro VARCHAR(100) NULL AFTER NumeroCartaoUtente;
ALTER TABLE associados DROP CONSTRAINT chk_associados_genero;
ALTER TABLE associados ADD CONSTRAINT chk_associados_genero CHECK (Genero IN ('M','F','O'));
ALTER TABLE associados ADD CONSTRAINT chk_associados_nominativo_outro CHECK ((Genero='O' AND NominativoOutro IS NOT NULL AND CHAR_LENGTH(TRIM(NominativoOutro))>0) OR (Genero IN ('M','F') AND NominativoOutro IS NULL));
