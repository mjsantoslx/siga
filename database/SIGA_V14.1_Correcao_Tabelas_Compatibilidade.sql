-- ============================================================================
-- SIGA V14 / V14.1 - Correcção de compatibilidade com a aplicação
-- Cria tabelas que continuam a ser necessárias ao código existente.
-- Charset/collation: utf8mb4 / utf8mb4_uca1400_as_ci
-- ============================================================================

USE siga;

CREATE TABLE IF NOT EXISTS utilizadores (
    Id INT NOT NULL AUTO_INCREMENT,
    Nome VARCHAR(100) NOT NULL,
    Email VARCHAR(150) NOT NULL,
    Password VARCHAR(255) NOT NULL,
    Administrador TINYINT(1) NOT NULL DEFAULT 0,
    Activo TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (Id),
    UNIQUE KEY uk_utilizadores_nome (Nome)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_as_ci;

CREATE TABLE IF NOT EXISTS utilizadores_companhias (
    Id INT NOT NULL AUTO_INCREMENT,
    IdUtilizador INT NOT NULL,
    IdCompanhia INT NOT NULL,
    DataInicio DATETIME NOT NULL,
    DataFim DATETIME NULL,
    Activo TINYINT(1) NOT NULL DEFAULT 1,
    DataHora TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (Id),
    KEY idx_uc_utilizador_actual (IdUtilizador, Activo, DataFim),
    KEY idx_uc_companhia_actual (IdCompanhia, Activo, DataFim),
    CONSTRAINT fk_uc_utilizador FOREIGN KEY (IdUtilizador) REFERENCES utilizadores(Id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_uc_companhia FOREIGN KEY (IdCompanhia) REFERENCES companhias(Id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_as_ci;

CREATE TABLE IF NOT EXISTS utilizadores_associados (
    IdUtilizador INT NOT NULL,
    IdAssociado INT NOT NULL,
    DataHora TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    Activo TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (IdUtilizador),
    UNIQUE KEY uk_utilizadores_associados_associado (IdAssociado),
    CONSTRAINT fk_ua_utilizador FOREIGN KEY (IdUtilizador) REFERENCES utilizadores(Id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_ua_associado FOREIGN KEY (IdAssociado) REFERENCES associados(Id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_as_ci;

CREATE TABLE IF NOT EXISTS consentimentos (
    Id INT NOT NULL AUTO_INCREMENT,
    IdAssociado INT NOT NULL,
    DadosPessoais TINYINT(1) NOT NULL,
    DadosSaude TINYINT(1) NOT NULL,
    DadosVozImagem TINYINT(1) NOT NULL,
    PRIMARY KEY (Id),
    KEY ix_consentimentos_associado (IdAssociado),
    CONSTRAINT fk_consentimentos_associado FOREIGN KEY (IdAssociado) REFERENCES associados(Id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_as_ci;

CREATE TABLE IF NOT EXISTS fichas_saude (
    Id INT NOT NULL AUTO_INCREMENT,
    IdAssociado INT NOT NULL,
    NumUente CHAR(9) NOT NULL,
    Asma TINYINT(1) NOT NULL DEFAULT 0,
    Epilepsia TINYINT(1) NOT NULL DEFAULT 0,
    Diabetes TINYINT(1) NOT NULL DEFAULT 0,
    Alergias TINYINT(1) NOT NULL DEFAULT 0,
    DescAlergias TEXT NULL,
    MedicacaoRegular TEXT NULL,
    RestricoesAlimentares TEXT NULL,
    Outros TEXT NULL,
    PRIMARY KEY (Id),
    KEY ix_fichas_saude_associado (IdAssociado),
    CONSTRAINT fk_fichas_saude_associado FOREIGN KEY (IdAssociado) REFERENCES associados(Id) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_as_ci;

CREATE TABLE IF NOT EXISTS fichas_saude_historico (
    Id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    IdFichaSaude INT NOT NULL,
    IdAssociado INT NOT NULL,
    IdUtilizador INT NOT NULL,
    DataHora DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    Operacao VARCHAR(20) NOT NULL,
    DadosAnteriores LONGTEXT NULL,
    DadosNovos LONGTEXT NULL,
    PRIMARY KEY (Id),
    KEY idx_fsh_associado_data (IdAssociado, DataHora),
    KEY idx_fsh_utilizador_data (IdUtilizador, DataHora),
    CONSTRAINT fk_fsh_ficha FOREIGN KEY (IdFichaSaude) REFERENCES fichas_saude(Id) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_fsh_associado FOREIGN KEY (IdAssociado) REFERENCES associados(Id) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_fsh_utilizador FOREIGN KEY (IdUtilizador) REFERENCES utilizadores(Id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_as_ci;

INSERT INTO utilizadores (Nome, Email, Password, Administrador, Activo)
SELECT 'Administrador', '', '$2y$12$i1EcMpEYAHfIqe7QrDoETOb5bHodkksIY56MmyAQhSgScIB3nerY6', 1, 1
WHERE NOT EXISTS (SELECT 1 FROM utilizadores WHERE Nome = 'Administrador');
