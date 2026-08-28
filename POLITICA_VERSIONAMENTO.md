# Política de versionamento do SIGA

## V15.0
A V15.0 é uma versão-base. O pacote inclui:
- código da aplicação;
- configuração canónica em `config/config.php`;
- um único script de criação de raiz da base de dados:
  `database/SIGA_V15.0_Criacao_BD.sql`.

Não inclui scripts de migração.

## A partir da V15.1
Cada versão deverá incluir:
1. um script de criação de raiz da versão;
2. um script de migração da versão anterior para a nova versão.

Exemplo V15.1:
- `SIGA_V15.1_Criacao_BD.sql`
- `SIGA_V15.0_para_V15.1_Migracao.sql`
