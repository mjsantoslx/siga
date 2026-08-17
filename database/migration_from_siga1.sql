SET FOREIGN_KEY_CHECKS=0;

ALTER TABLE companhias
  ADD COLUMN ambito_global TINYINT(1) NOT NULL DEFAULT 0 AFTER Designacao,
  ADD COLUMN Activo TINYINT(1) NOT NULL DEFAULT 1 AFTER ambito_global;

ALTER TABLE utilizadores
  ADD COLUMN Administrador TINYINT(1) NOT NULL DEFAULT 0 AFTER Password,
  ADD COLUMN Activo TINYINT(1) NOT NULL DEFAULT 1 AFTER Administrador;

ALTER TABLE utilizadores
  ADD UNIQUE KEY uk_utilizadores_nome (Nome);

ALTER TABLE utilizadores
  MODIFY COLUMN Id INT(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE associados_companhias
  ADD COLUMN Id INT NOT NULL AUTO_INCREMENT UNIQUE FIRST,
  ADD COLUMN DataInicio DATETIME NULL AFTER IdCompanhia,
  ADD COLUMN DataFim DATETIME NULL AFTER DataInicio,
  MODIFY COLUMN DataHora TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;

UPDATE associados_companhias
SET DataInicio = DataHora
WHERE DataInicio IS NULL;

ALTER TABLE associados_companhias
  MODIFY COLUMN DataInicio DATETIME NOT NULL,
  DROP PRIMARY KEY,
  ADD PRIMARY KEY (Id);

CREATE INDEX idx_ac_associado_actual
  ON associados_companhias (IdAssociado, Activo, DataFim);

CREATE INDEX idx_ac_companhia_actual
  ON associados_companhias (IdCompanhia, Activo, DataFim);

CREATE TABLE utilizadores_associados (
  IdUtilizador INT NOT NULL,
  IdAssociado INT NOT NULL,
  DataHora TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  Activo TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (IdUtilizador),
  UNIQUE KEY uk_utilizadores_associados_associado (IdAssociado),
  CONSTRAINT fk_ua_utilizador FOREIGN KEY (IdUtilizador) REFERENCES utilizadores(Id) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_ua_associado FOREIGN KEY (IdAssociado) REFERENCES associados(Id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

CREATE TABLE utilizadores_companhias (
  Id INT NOT NULL AUTO_INCREMENT,
  IdUtilizador INT NOT NULL,
  IdCompanhia INT NOT NULL,
  DataInicio DATETIME NOT NULL,
  DataFim DATETIME NULL,
  Activo TINYINT(1) NOT NULL DEFAULT 1,
  DataHora TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (Id),
  INDEX idx_uc_utilizador_actual (IdUtilizador, Activo, DataFim),
  INDEX idx_uc_companhia_actual (IdCompanhia, Activo, DataFim),
  CONSTRAINT fk_uc_utilizador FOREIGN KEY (IdUtilizador) REFERENCES utilizadores(Id) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_uc_companhia FOREIGN KEY (IdCompanhia) REFERENCES companhias(Id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

CREATE TABLE fichas_saude_historico (
  Id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  IdFichaSaude INT NOT NULL,
  IdAssociado INT NOT NULL,
  IdUtilizador INT NOT NULL,
  DataHora DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  Operacao VARCHAR(20) NOT NULL,
  DadosAnteriores LONGTEXT NULL,
  DadosNovos LONGTEXT NULL,
  PRIMARY KEY (Id),
  INDEX idx_fsh_associado_data (IdAssociado, DataHora),
  INDEX idx_fsh_utilizador_data (IdUtilizador, DataHora),
  CONSTRAINT fk_fsh_ficha FOREIGN KEY (IdFichaSaude) REFERENCES fichas_saude(Id) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_fsh_associado FOREIGN KEY (IdAssociado) REFERENCES associados(Id) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_fsh_utilizador FOREIGN KEY (IdUtilizador) REFERENCES utilizadores(Id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

INSERT INTO companhias (Designacao, ambito_global)
SELECT 'Chefia Nacional', 1
WHERE NOT EXISTS (SELECT 1 FROM companhias WHERE Designacao='Chefia Nacional');

SET FOREIGN_KEY_CHECKS=1;

ALTER TABLE utilizadores_companhias
  ADD INDEX uk_uc_utilizador_companhia_actual
  (IdUtilizador, IdCompanhia, Activo, DataFim);
