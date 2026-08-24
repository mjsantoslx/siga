-- SIGA v11.9 - Actualização a partir da v11.8
-- Modelo de histórico de moradas para associados e companhias.
--
-- A migração preserva as relações existentes:
--   - cada relação existente passa a ser a morada actual;
--   - DataInicio é definida como a data da migração;
--   - DataFim fica NULL;
--   - Activo fica 1.
--
-- As tabelas antigas são renomeadas e reconstruídas para obter uma PK própria
-- e os campos necessários ao histórico.

USE `siga`;

START TRANSACTION;

RENAME TABLE
    `associados_moradas` TO `associados_moradas_v118`,
    `companhias_moradas` TO `companhias_moradas_v118`;

CREATE TABLE `associados_moradas` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `IdAssociado` int(11) NOT NULL,
  `IdMorada` int(11) NOT NULL,
  `DataInicio` datetime NOT NULL,
  `DataFim` datetime NULL DEFAULT NULL,
  `Activo` tinyint(1) NOT NULL DEFAULT 1,
  `DataHora` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`Id`),
  KEY `idx_associados_moradas_associado` (`IdAssociado`),
  KEY `idx_associados_moradas_morada` (`IdMorada`),
  KEY `idx_associados_moradas_actual` (`IdAssociado`,`Activo`,`DataFim`),
  CONSTRAINT `fk_associados_moradas_associado` FOREIGN KEY (`IdAssociado`) REFERENCES `associados` (`Id`),
  CONSTRAINT `fk_associados_moradas_morada` FOREIGN KEY (`IdMorada`) REFERENCES `moradas` (`Id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

INSERT INTO `associados_moradas`
    (`IdAssociado`, `IdMorada`, `DataInicio`, `DataFim`, `Activo`)
SELECT `IdAssociado`, `IdMorada`, NOW(), NULL, 1
FROM `associados_moradas_v118`;

CREATE TABLE `companhias_moradas` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `IdCompanhia` int(11) NOT NULL,
  `IdMorada` int(11) NOT NULL,
  `DataInicio` datetime NOT NULL,
  `DataFim` datetime NULL DEFAULT NULL,
  `Activo` tinyint(1) NOT NULL DEFAULT 1,
  `DataHora` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`Id`),
  KEY `idx_companhias_moradas_companhia` (`IdCompanhia`),
  KEY `idx_companhias_moradas_morada` (`IdMorada`),
  KEY `idx_companhias_moradas_actual` (`IdCompanhia`,`Activo`,`DataFim`),
  CONSTRAINT `fk_companhias_moradas_companhia` FOREIGN KEY (`IdCompanhia`) REFERENCES `companhias` (`Id`),
  CONSTRAINT `fk_companhias_moradas_morada` FOREIGN KEY (`IdMorada`) REFERENCES `moradas` (`Id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

INSERT INTO `companhias_moradas`
    (`IdCompanhia`, `IdMorada`, `DataInicio`, `DataFim`, `Activo`)
SELECT `IdCompanhia`, `IdMorada`, NOW(), NULL, 1
FROM `companhias_moradas_v118`;

DROP TABLE `associados_moradas_v118`;
DROP TABLE `companhias_moradas_v118`;

COMMIT;
