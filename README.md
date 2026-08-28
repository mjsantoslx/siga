# SIGA V15.0

Pacote consolidado para instalação de raiz.

## Conteúdo
- Código da aplicação.
- `config/config.php` como ficheiro de configuração canónico.
- `database/SIGA_V15.0_Criacao_BD.sql` como único script SQL da V15.0.
- Dados iniciais definidos no script de criação.

## Regras funcionais consolidadas
- Datas apresentadas e introduzidas em formato europeu `dd/mm/aaaa`.
- A base de dados armazena datas no formato `YYYY-MM-DD`.
- Datas de inscrição e eventos sugerem a data atual, permitem datas anteriores e não permitem datas futuras.
- A criação de um associado gera o evento de `Admissão`.
- Eventos são históricos e não são eliminados.
- Associados podem ser inativados e reativados sem perder histórico.
- Moradas são entidades independentes, podem ser partilhadas e suportam correção ou substituição.
- Géneros: Masculino, Feminino e Outro; para Outro, o nominativo é introduzido manualmente.
- Número de utente: 9 algarismos, tratado como texto.
- Para Cartão de Cidadão e Bilhete de Identidade, números com menos de 8 algarismos são completados à esquerda com zeros, no servidor e no formulário.
- A instalação de raiz inclui dados iniciais das tabelas de referência.

## Dependências PHP
Na raiz do projeto, quando aplicável:
`composer install`
