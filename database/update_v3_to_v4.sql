-- =========================================================
-- SIGA v4 - Actualização a partir da versão v3
-- =========================================================

USE `siga`;

START TRANSACTION;

-- Índice auxiliar para acelerar a determinação das ligações
-- actuais entre utilizadores e companhias.
ALTER TABLE `utilizadores_companhias`
  ADD INDEX `uk_uc_utilizador_companhia_actual`
  (`IdUtilizador`, `IdCompanhia`, `Activo`, `DataFim`);

COMMIT;
