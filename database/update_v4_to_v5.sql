-- =========================================================
-- SIGA v5 - Actualização a partir da versão v4
-- =========================================================
--
-- A v5 acrescenta exclusivamente o backoffice de tabelas de apoio.
-- Não existem alterações ao modelo físico da base de dados.
-- Este script é mantido para que todas as versões do SIGA tenham
-- sempre uma migração explícita e rastreável.
--
USE `siga`;

START TRANSACTION;

-- Verificação da existência das tabelas utilizadas pelo backoffice.
SELECT COUNT(*) AS tabelas_de_apoio_existentes
FROM information_schema.tables
WHERE table_schema = DATABASE()
  AND table_name IN (
    'generos',
    'nacionalidades',
    'tipos_contacto',
    'tipos_evento',
    'tipos_parentesco'
  );

COMMIT;
