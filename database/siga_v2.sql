/*
 Navicat MySQL Dump SQL

 Source Server         : Siga
 Source Server Type    : MariaDB
 Source Server Version : 110702 (11.7.2-MariaDB)
 Source Host           : localhost:3306
 Source Schema         : siga

 Target Server Type    : MariaDB
 Target Server Version : 110702 (11.7.2-MariaDB)
 File Encoding         : 65001

 Date: 16/08/2026 03:55:03
*/

SET NAMES latin1;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for companhias
-- ----------------------------
DROP TABLE IF EXISTS `companhias`;
CREATE TABLE `companhias`  (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `Designacao` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `ambito_global` tinyint(1) NOT NULL DEFAULT 0,
  `Activo` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`Id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1001 CHARACTER SET = latin1 COLLATE = latin1_general_ci ROW_FORMAT = Dynamic;


-- ----------------------------
-- Table structure for generos
-- ----------------------------
DROP TABLE IF EXISTS `generos`;
CREATE TABLE `generos`  (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `Designacao` varchar(150) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  PRIMARY KEY (`Id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1001 CHARACTER SET = latin1 COLLATE = latin1_general_ci ROW_FORMAT = Dynamic;


-- ----------------------------
-- Table structure for moradas
-- ----------------------------
DROP TABLE IF EXISTS `moradas`;
CREATE TABLE `moradas`  (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `Morada` varchar(150) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `Localidade` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `IdConcelho` smallint(6) NULL DEFAULT NULL,
  `IdDistrito` smallint(6) NULL DEFAULT NULL,
  `CodPostal` char(8) CHARACTER SET latin1 COLLATE latin1_general_ci NULL DEFAULT NULL,
  PRIMARY KEY (`Id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1001 CHARACTER SET = latin1 COLLATE = latin1_general_ci ROW_FORMAT = Dynamic;


-- ----------------------------
-- Table structure for nacionalidades
-- ----------------------------
DROP TABLE IF EXISTS `nacionalidades`;
CREATE TABLE `nacionalidades`  (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `Nacionalidade` varchar(20) CHARACTER SET latin1 COLLATE latin1_general_ci NULL DEFAULT NULL,
  PRIMARY KEY (`Id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1001 CHARACTER SET = latin1 COLLATE = latin1_general_ci ROW_FORMAT = Dynamic;


-- ----------------------------
-- Table structure for tipos_contacto
-- ----------------------------
DROP TABLE IF EXISTS `tipos_contacto`;
CREATE TABLE `tipos_contacto`  (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `Designacao` varchar(25) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  PRIMARY KEY (`Id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1001 CHARACTER SET = latin1 COLLATE = latin1_general_ci ROW_FORMAT = Dynamic;


-- ----------------------------
-- Table structure for tipos_evento
-- ----------------------------
DROP TABLE IF EXISTS `tipos_evento`;
CREATE TABLE `tipos_evento`  (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `Designacao` varchar(25) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  PRIMARY KEY (`Id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = latin1 COLLATE = latin1_general_ci ROW_FORMAT = Dynamic;


-- ----------------------------
-- Table structure for tipos_parentesco
-- ----------------------------
DROP TABLE IF EXISTS `tipos_parentesco`;
CREATE TABLE `tipos_parentesco`  (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `TipoParentesco` varchar(20) CHARACTER SET latin1 COLLATE latin1_general_ci NULL DEFAULT NULL,
  PRIMARY KEY (`Id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1001 CHARACTER SET = latin1 COLLATE = latin1_general_ci ROW_FORMAT = Dynamic;


-- ----------------------------
-- Table structure for utilizadores
-- ----------------------------
DROP TABLE IF EXISTS `utilizadores`;
CREATE TABLE `utilizadores`  (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `Nome` varchar(100) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `Email` varchar(150) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `Password` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `Administrador` tinyint(1) NOT NULL DEFAULT 0,
  `Activo` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`Id`),
  UNIQUE KEY `uk_utilizadores_nome` (`Nome`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = latin1 COLLATE = latin1_general_ci ROW_FORMAT = Dynamic;



-- ----------------------------
-- Table structure for associados
-- ----------------------------
DROP TABLE IF EXISTS `associados`;
CREATE TABLE `associados`  (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `Nome` varchar(100) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `DNasc` date NOT NULL,
  `IdGenero` int(6) NOT NULL,
  `CartaoCidadao` char(8) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `NIF` char(11) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `IdNacionalidade` int(6) NOT NULL,
  `Naturalidade` varchar(100) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `Profissao` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci NULL DEFAULT NULL,
  `Habilitacoes` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci NULL DEFAULT NULL,
  `DataRegisto` date NOT NULL,
  `Activo` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`Id`) USING BTREE,
  INDEX `fk_associados_generos_idgenero`(`IdGenero` ASC) USING BTREE,
  INDEX `fk_associados_nacionalidade_idnacionalidade`(`IdNacionalidade` ASC) USING BTREE,
  CONSTRAINT `fk_associados_nacionalidades_idnacionalidade` FOREIGN KEY (`IdNacionalidade`) REFERENCES `nacionalidades` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_associados_generos` FOREIGN KEY (`IdGenero`) REFERENCES `generos` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 1001 CHARACTER SET = latin1 COLLATE = latin1_general_ci ROW_FORMAT = Dynamic;


-- ----------------------------
-- Table structure for companhias_moradas
-- ----------------------------
DROP TABLE IF EXISTS `companhias_moradas`;
CREATE TABLE `companhias_moradas`  (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `IdCompanhia` int(6) NOT NULL,
  `IdMorada` int(6) NOT NULL,
  PRIMARY KEY (`Id`) USING BTREE,
  INDEX `fk_companhias_moradas_companhias`(`IdCompanhia` ASC) USING BTREE,
  INDEX `fk_companhias_moradas_moradas`(`IdMorada` ASC) USING BTREE,
  CONSTRAINT `fk_companhias_moradas_companhias` FOREIGN KEY (`IdCompanhia`) REFERENCES `companhias` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_companhias_moradas_moradas` FOREIGN KEY (`IdMorada`) REFERENCES `moradas` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 1001 CHARACTER SET = latin1 COLLATE = latin1_general_ci ROW_FORMAT = Dynamic;


-- ----------------------------
-- Table structure for utilizadores_companhias
-- ----------------------------
DROP TABLE IF EXISTS `utilizadores_companhias`;
CREATE TABLE `utilizadores_companhias` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `IdUtilizador` int(11) NOT NULL,
  `IdCompanhia` int(11) NOT NULL,
  `DataInicio` datetime NOT NULL,
  `DataFim` datetime NULL DEFAULT NULL,
  `Activo` tinyint(1) NOT NULL DEFAULT 1,
  `DataHora` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`Id`),
  INDEX `idx_uc_utilizador_actual` (`IdUtilizador`,`Activo`,`DataFim`),
  INDEX `idx_uc_companhia_actual` (`IdCompanhia`,`Activo`,`DataFim`),
  CONSTRAINT `fk_uc_utilizador` FOREIGN KEY (`IdUtilizador`) REFERENCES `utilizadores` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_uc_companhia` FOREIGN KEY (`IdCompanhia`) REFERENCES `companhias` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB CHARACTER SET=latin1 COLLATE=latin1_general_ci;


-- ----------------------------
-- Table structure for associados_companhias
-- ----------------------------
DROP TABLE IF EXISTS `associados_companhias`;
CREATE TABLE `associados_companhias` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `IdAssociado` int(11) NOT NULL,
  `IdCompanhia` int(11) NOT NULL,
  `DataInicio` datetime NOT NULL,
  `DataFim` datetime NULL DEFAULT NULL,
  `Activo` tinyint(1) NOT NULL DEFAULT 1,
  `DataHora` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`Id`),
  INDEX `idx_ac_associado_actual` (`IdAssociado`,`Activo`,`DataFim`),
  INDEX `idx_ac_companhia_actual` (`IdCompanhia`,`Activo`,`DataFim`),
  CONSTRAINT `fk_associados_companhias_associados` FOREIGN KEY (`IdAssociado`) REFERENCES `associados` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_associados_companhias_companhias` FOREIGN KEY (`IdCompanhia`) REFERENCES `companhias` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB CHARACTER SET=latin1 COLLATE=latin1_general_ci;


-- ----------------------------
-- Table structure for associados_moradas
-- ----------------------------
DROP TABLE IF EXISTS `associados_moradas`;
CREATE TABLE `associados_moradas`  (
  `IdAssociado` int(11) NOT NULL,
  `IdMorada` int(11) NOT NULL,
  `Activo` tinyint(1) NULL DEFAULT 1,
  `DataHora` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`IdAssociado`, `IdMorada`) USING BTREE,
  INDEX `fk_associados_moradas_moradas_idmorada`(`IdMorada` ASC) USING BTREE,
  CONSTRAINT `fk_associados_moradas_associados_idassociado` FOREIGN KEY (`IdAssociado`) REFERENCES `associados` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_associados_moradas_moradas_idmorada` FOREIGN KEY (`IdMorada`) REFERENCES `moradas` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB CHARACTER SET = latin1 COLLATE = latin1_general_ci ROW_FORMAT = Dynamic;


-- ----------------------------
-- Table structure for consentimentos
-- ----------------------------
DROP TABLE IF EXISTS `consentimentos`;
CREATE TABLE `consentimentos`  (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `IdAssociado` int(11) NOT NULL,
  `DadosPessoais` tinyint(1) NOT NULL,
  `DadosSaude` tinyint(1) NOT NULL,
  `DadosVozImagem` tinyint(1) NOT NULL,
  PRIMARY KEY (`Id`) USING BTREE,
  INDEX `fk_consentimentos_associados_id_associado`(`IdAssociado` ASC) USING BTREE,
  CONSTRAINT `fk_consentimentos_associados_id_associado` FOREIGN KEY (`IdAssociado`) REFERENCES `associados` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 1001 CHARACTER SET = latin1 COLLATE = latin1_general_ci ROW_FORMAT = Dynamic;


-- ----------------------------
-- Table structure for contactos
-- ----------------------------
DROP TABLE IF EXISTS `contactos`;
CREATE TABLE `contactos`  (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `IdTipoContacto` int(11) NOT NULL,
  `IdAssociado` int(11) NOT NULL,
  `Valor` varchar(100) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  PRIMARY KEY (`Id`) USING BTREE,
  INDEX `fk_contactos_tipos_contacto`(`IdTipoContacto` ASC) USING BTREE,
  INDEX `fk_contactos_associados`(`IdAssociado` ASC) USING BTREE,
  CONSTRAINT `fk_contactos_tipos_contacto` FOREIGN KEY (`IdTipoContacto`) REFERENCES `tipos_contacto` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_contactos_associados` FOREIGN KEY (`IdAssociado`) REFERENCES `associados` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 1001 CHARACTER SET = latin1 COLLATE = latin1_general_ci ROW_FORMAT = Dynamic;


-- ----------------------------
-- Table structure for eventos_associados
-- ----------------------------
DROP TABLE IF EXISTS `eventos_associados`;
CREATE TABLE `eventos_associados`  (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `IdAssociado` int(6) NOT NULL,
  `Descricao` text CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `DataEvento` date NOT NULL,
  `IdTipoEvento` int(11) NULL DEFAULT NULL,
  PRIMARY KEY (`Id`) USING BTREE,
  INDEX `fk_eventos_associados_associados`(`IdAssociado` ASC) USING BTREE,
  INDEX `fk_eventos_associados_tipos_evento`(`IdTipoEvento` ASC) USING BTREE,
  CONSTRAINT `fk_eventos_associados_associados` FOREIGN KEY (`IdAssociado`) REFERENCES `associados` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_eventos_associados_tipos_evento` FOREIGN KEY (`IdTipoEvento`) REFERENCES `tipos_evento` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 1001 CHARACTER SET = latin1 COLLATE = latin1_general_ci ROW_FORMAT = Dynamic;


-- ----------------------------
-- Table structure for fichas_saude
-- ----------------------------
DROP TABLE IF EXISTS `fichas_saude`;
CREATE TABLE `fichas_saude`  (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `IdAssociado` int(4) NOT NULL,
  `NumUente` char(9) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `Asma` tinyint(1) NOT NULL DEFAULT 0,
  `Epilepsia` tinyint(1) NOT NULL DEFAULT 0,
  `Diabetes` tinyint(1) NOT NULL DEFAULT 0,
  `Alergias` tinyint(1) NOT NULL DEFAULT 0,
  `DescAlergias` text CHARACTER SET latin1 COLLATE latin1_general_ci NULL DEFAULT NULL,
  `MedicacaoRegular` text CHARACTER SET latin1 COLLATE latin1_general_ci NULL DEFAULT NULL,
  `RestricoesAlimentares` text CHARACTER SET latin1 COLLATE latin1_general_ci NULL DEFAULT NULL,
  `Outros` text CHARACTER SET latin1 COLLATE latin1_general_ci NULL DEFAULT NULL,
  PRIMARY KEY (`Id`) USING BTREE,
  INDEX `fichas_saude_associados_Id_fk`(`IdAssociado` ASC) USING BTREE,
  CONSTRAINT `fichas_saude_associados_Id_fk` FOREIGN KEY (`IdAssociado`) REFERENCES `associados` (`Id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 1001 CHARACTER SET = latin1 COLLATE = latin1_general_ci ROW_FORMAT = Dynamic;


-- ----------------------------
-- Table structure for parentescos
-- ----------------------------
DROP TABLE IF EXISTS `parentescos`;
CREATE TABLE `parentescos`  (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `IdTipoParentesco` int(11) NOT NULL,
  `IdAssociado` int(11) NOT NULL,
  `Nome` varchar(100) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `Telefone` varchar(15) CHARACTER SET latin1 COLLATE latin1_general_ci NULL DEFAULT NULL,
  `email` varchar(100) CHARACTER SET latin1 COLLATE latin1_general_ci NULL DEFAULT NULL,
  `Profissao` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci NULL DEFAULT NULL,
  PRIMARY KEY (`Id`) USING BTREE,
  INDEX `fk_parentescos_tipos_parentesco_id`(`IdTipoParentesco` ASC) USING BTREE,
  INDEX `fk_parentescos_associados`(`IdAssociado` ASC) USING BTREE,
  CONSTRAINT `fk_parentescos_tipos_parentesco_id` FOREIGN KEY (`IdTipoParentesco`) REFERENCES `tipos_parentesco` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_parentescos_associados` FOREIGN KEY (`IdAssociado`) REFERENCES `associados` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 1001 CHARACTER SET = latin1 COLLATE = latin1_general_ci ROW_FORMAT = Dynamic;


-- ----------------------------
-- Table structure for utilizadores_associados
-- ----------------------------
DROP TABLE IF EXISTS `utilizadores_associados`;
CREATE TABLE `utilizadores_associados` (
  `IdUtilizador` int(11) NOT NULL,
  `IdAssociado` int(11) NOT NULL,
  `DataHora` timestamp NOT NULL DEFAULT current_timestamp(),
  `Activo` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`IdUtilizador`),
  UNIQUE KEY `uk_utilizadores_associados_associado` (`IdAssociado`),
  CONSTRAINT `fk_ua_utilizador` FOREIGN KEY (`IdUtilizador`) REFERENCES `utilizadores` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_ua_associado` FOREIGN KEY (`IdAssociado`) REFERENCES `associados` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB CHARACTER SET=latin1 COLLATE=latin1_general_ci;


-- ----------------------------
-- Table structure for fichas_saude_historico
-- ----------------------------
DROP TABLE IF EXISTS `fichas_saude_historico`;
CREATE TABLE `fichas_saude_historico` (
  `Id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `IdFichaSaude` int(11) NOT NULL,
  `IdAssociado` int(11) NOT NULL,
  `IdUtilizador` int(11) NOT NULL,
  `DataHora` datetime NOT NULL DEFAULT current_timestamp(),
  `Operacao` varchar(20) NOT NULL,
  `DadosAnteriores` longtext NULL,
  `DadosNovos` longtext NULL,
  PRIMARY KEY (`Id`),
  INDEX `idx_fsh_associado_data` (`IdAssociado`,`DataHora`),
  INDEX `idx_fsh_utilizador_data` (`IdUtilizador`,`DataHora`),
  CONSTRAINT `fk_fsh_ficha` FOREIGN KEY (`IdFichaSaude`) REFERENCES `fichas_saude` (`Id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_fsh_associado` FOREIGN KEY (`IdAssociado`) REFERENCES `associados` (`Id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_fsh_utilizador` FOREIGN KEY (`IdUtilizador`) REFERENCES `utilizadores` (`Id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB CHARACTER SET=latin1 COLLATE=latin1_general_ci;


SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------
-- Regra de negócio: Chefia Nacional tem âmbito global.
-- Ajustar/confirmar a designação conforme os dados reais.
-- ---------------------------------------------------------
INSERT INTO `companhias` (`Designacao`, `ambito_global`)
SELECT 'Chefia Nacional', 1
WHERE NOT EXISTS (
    SELECT 1 FROM `companhias` WHERE `Designacao` = 'Chefia Nacional'
);


-- =========================================================
-- Dados iniciais protegidos
-- =========================================================
INSERT INTO `companhias` (`Designacao`, `ambito_global`, `Activo`)
SELECT 'Chefia Nacional', 1, 1
WHERE NOT EXISTS (SELECT 1 FROM `companhias` WHERE `ambito_global` = 1);

INSERT INTO `utilizadores` (`Nome`, `Email`, `Password`, `Administrador`, `Activo`)
SELECT 'Administrador', '', '$2y$12$i1EcMpEYAHfIqe7QrDoETOb5bHodkksIY56MmyAQhSgScIB3nerY6', 1, 1
WHERE NOT EXISTS (SELECT 1 FROM `utilizadores` WHERE `Nome` = 'Administrador');
