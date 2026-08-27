# Validação do pacote SIGA V14.1

Verificações efectuadas antes do empacotamento:

- Todos os ficheiros PHP foram validados com `php -l`.
- O autoloader interno foi testado para as classes Core, Controllers e Models da aplicação.
- As referências SQL estáticas às tabelas usadas pelo código foram comparadas com as tabelas criadas pelo script de criação da BD; não foram encontradas referências a tabelas inexistentes.
- O pacote contém um único script SQL de criação integral da base de dados.
- Não contém scripts de migração ou actualização incremental.

A validação de execução contra MariaDB/MySQL requer um servidor de base de dados, que não está disponível no ambiente de empacotamento.
