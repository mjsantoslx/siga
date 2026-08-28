# Validação do pacote SIGA V15.0 consolidado

## Validações executadas

- Validação sintáctica (`php -l`) de todos os ficheiros PHP do pacote.
- Remoção de tags HTML/JavaScript que tinham sido indevidamente inseridas em ficheiros de modelo e controlador.
- Correcção dos campos de data dos formulários de associados e eventos.
- Confirmação de que a máscara de datas está no ficheiro `public/assets/js/date-mask.js`.
- Verificação estática das referências SQL mais comuns no código contra as tabelas criadas no script SQL.
- Confirmação de que existe apenas um ficheiro SQL no pacote.
- Confirmação de que não existem scripts de migração.

## Limitação

A execução integral do script contra o servidor MariaDB do ambiente WAMP não pode ser reproduzida neste ambiente. A validação SQL é estrutural/estática; o primeiro teste de instalação deverá executar o script no MariaDB alvo.
