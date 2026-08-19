-- SIGA v11: novas secções e histórico de evolução
USE `siga`;
START TRANSACTION;
CREATE TABLE IF NOT EXISTS `seccoes` (
 `Id` int(11) NOT NULL AUTO_INCREMENT,
 `Designacao` varchar(50) NOT NULL,
 `Activo` tinyint(1) NOT NULL DEFAULT 1,
 PRIMARY KEY (`Id`),
 UNIQUE KEY `uk_seccoes_designacao` (`Designacao`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT IGNORE INTO `seccoes` (`Designacao`) VALUES
('Colónia'),('Alcateia'),('Tribo Júnior'),('Tribo Sénior'),('Clã'),('Chefia');
CREATE TABLE IF NOT EXISTS `associados_seccoes` (
 `Id` int(11) NOT NULL AUTO_INCREMENT,
 `IdAssociado` int(11) NOT NULL,
 `IdSeccao` int(11) NOT NULL,
 `DataInicio` datetime NOT NULL,
 `DataFim` datetime DEFAULT NULL,
 `Activo` tinyint(1) NOT NULL DEFAULT 1,
 `DataHora` timestamp NOT NULL DEFAULT current_timestamp(),
 PRIMARY KEY (`Id`),
 KEY `idx_as_associado_actual` (`IdAssociado`,`Activo`,`DataFim`),
 CONSTRAINT `fk_as_associado` FOREIGN KEY (`IdAssociado`) REFERENCES `associados` (`Id`) ON DELETE CASCADE,
 CONSTRAINT `fk_as_seccao` FOREIGN KEY (`IdSeccao`) REFERENCES `seccoes` (`Id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
COMMIT;
