# SIGA — Política de Versionamento e Base de Dados

## V15.0
A V15.0 é a nova versão-base.

O pacote inclui:
- código da aplicação;
- `config/config.php`;
- `database/SIGA_V15.0_Criacao_BD.sql`.

A V15.0 **não inclui script de migração**, porque é a versão-base definida para a nova linha de evolução.

## V15.1 e seguintes
Cada versão posterior deverá incluir:
1. um script de criação integral da base de dados na versão dessa release;
2. um script de migração da versão imediatamente anterior para a nova versão.

Exemplo para V15.1:
- `SIGA_V15.1_Criacao_BD.sql`
- `SIGA_V15.0_para_V15.1_Migracao.sql`

A mesma regra aplica-se sucessivamente às versões posteriores.
