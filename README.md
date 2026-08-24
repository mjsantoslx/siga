# SIGA — Sistema de Gestão de Associados

**Versão: 5.0**

Versão inicial funcional da aplicação SIGA baseada no schema `siga(1).sql`.

## Modelo de segurança

- `utilizadores` e `associados` são entidades independentes.
- `utilizadores_associados` liga opcionalmente um utilizador a um associado.
- Um utilizador não muda de associado.
- `utilizadores_companhias` define o âmbito de um utilizador que não depende necessariamente de um associado.
- `associados_companhias` define a pertença dos associados às companhias e mantém histórico.
- Um utilizador administrador tem âmbito global.
- Uma companhia com `ambito_global=1` (actualmente "Chefia Nacional") confere âmbito global.
- Os restantes utilizadores apenas acedem aos associados das suas companhias actuais.
- Fichas de saúde seguem o mesmo âmbito de acesso.
- Alterações à ficha de saúde são registadas em `fichas_saude_historico`.

## Instalação

1. Criar a base de dados `siga`.
2. Importar `database/siga_v2.sql`.
3. Editar `config/config.php`.
4. Executar `composer install`.
5. Configurar o DocumentRoot do Apache para `public/`, preferencialmente.
6. Criar um utilizador com um hash produzido por:
   `php tools/password_hash.php 'A_TUA_PASSWORD'`

## Nota

A versão visual é deliberadamente funcional e simples. O embelezamento/UI será tratado numa fase posterior.

## Identificadores

As colunas `Id` das entidades com chave primária própria são `AUTO_INCREMENT`. Tabelas de relação com chave primária composta, como `associados_moradas` e `utilizadores_associados`, não têm um `Id` próprio por desenho.


## Login

A autenticação é feita pelo campo `utilizadores.Nome`, e não pelo email. O campo `Email` pode permanecer vazio.

O campo `Nome` é único e é utilizado como identificador de login.

## Apache / siga.local

Se o VirtualHost tiver o `DocumentRoot` apontado para a raiz do projecto, o `.htaccess` da raiz encaminha as rotas para `public/index.php`.

Exemplo:

```apache
<VirtualHost *:80>
    ServerName siga.local
    DocumentRoot "C:/Apache24/htdocs/siga"
    <Directory "C:/Apache24/htdocs/siga">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

É necessário que `mod_rewrite` esteja activo.

Alternativamente, pode-se apontar o `DocumentRoot` directamente para a pasta `public`; nesse caso o `public/.htaccess` trata as rotas.


## Gestão de utilizadores

A gestão de utilizadores está disponível apenas para Administradores.

- Criar utilizadores regulares ou Administradores.
- O email é opcional.
- O nome do utilizador é único e é o identificador de login.
- Um utilizador pode existir sem estar ligado a um associado.
- A ligação utilizador-associado é opcional e só pode ser definida uma vez pela aplicação.
- Um utilizador pode ter várias companhias.
- A entrada/saída de companhias fica em histórico através de `utilizadores_companhias`.
- A gestão de companhias do utilizador permite adicionar e terminar ligações sem apagar o histórico.
- Um administrador não pode desactivar a própria conta.

## Scripts de base de dados — v4

O pacote inclui os dois scripts necessários para a base de dados:

- `database/siga_v4.sql` — criação completa da base de dados SIGA v4 de raiz.
- `database/update_v3_to_v4.sql` — actualização de uma instalação SIGA v3 para v4.

O script de actualização não elimina dados existentes.

## Backoffice de tabelas de apoio — v5

Disponível apenas para Administradores em **Tabelas de apoio**.

Tabelas geridas:
- Géneros (`generos`)
- Nacionalidades (`nacionalidades`)
- Tipos de contacto (`tipos_contacto`)
- Tipos de evento (`tipos_evento`)
- Tipos de parentesco (`tipos_parentesco`)

Inclui criação, edição e eliminação, com proteção CSRF e tratamento de erros de Foreign Key. A aplicação não permite que utilizadores regulares acedam ao backoffice.

Scripts:
- `database/siga_v5.sql` — criação de raiz.
- `database/update_v4_to_v5.sql` — atualização da v4 para v5. A v5 não altera o esquema da BD.


## Política de palavras-passe

A aplicação não impõe requisitos de complexidade à palavra-passe.

A única regra é:
- mínimo de **5 caracteres**;
- podem ser utilizados caracteres alfanuméricos e símbolos;
- não é exigida combinação específica de maiúsculas, minúsculas, números ou símbolos.

As palavras-passe continuam a ser armazenadas com `password_hash()` e nunca em texto simples.


## Numeração dos associados

Cada associado possui um **N.º de Associado** público, sequencial e único, começando em **1**. O número é atribuído automaticamente pela aplicação e é independente do `Id` técnico da tabela.

## N.º de Associado

Cada associado possui um **N.º de Associado** público com exactamente **5 caracteres**.

Exemplos: `00001`, `00002`, `00010`, `00100`.

O identificador é armazenado como `CHAR(5)`, preservando os zeros à esquerda. É único, atribuído automaticamente pela aplicação, independente do `Id` técnico e não é editável pelo utilizador. Os números atribuídos não são reutilizados.

O N.º de Associado é armazenado em `CHAR(5)` e começa em `00001`.


## Numeração

- **Associados:** N.º de Associado `CHAR(5)`, sequencial, começando em `00001`.
- **Utilizadores:** não possuem qualquer número; o login é feito pelo campo `Nome`.

## Encoding

A versão 9.0 normaliza a aplicação e a base de dados para UTF-8 / `utf8mb4`.


## Alterações da versão 10.0

- Na criação de associados, **Portuguesa** é apresentada em primeiro lugar na lista de nacionalidades; as restantes seguem alfabeticamente.
- As listagens principais passam a permitir ordenação clicando no cabeçalho das colunas.

- Correcção: a listagem de associados inclui explicitamente o campo `Numero`.


**Versão:** 11.4

## Revisão 10.1

Correcção da listagem de associados: o campo `Numero` passa a ser
seleccionado explicitamente pelo Model, permitindo a sua apresentação
na listagem sem o erro `Undefined array key "Numero"`.

## Versão 11.0

- Companhia do associado é opcional.
- Chefia Nacional pode coexistir com outra companhia.
- Secções: Colónia, Alcateia, Tribo Júnior, Tribo Sénior, Clã e Chefia.
- Histórico de evolução de secção em `associados_seccoes`.

## Versão 11.1

- A tabela `seccoes` deixou de ter a flag `Activo`.
- Foram adicionados `NominativoMasculino` e `NominativoFeminino`.
- Colónia: Castor / Castora
- Alcateia: Lobito / Lobita
- Tribo Júnior: Escoteiro Júnior / Escoteira Júnior
- Tribo Sénior: Escoteiro Sénior / Escoteira Sénior
- Clã: Caminheiro / Caminheira
- Chefia: Escoteiro Chefe / Escoteira Chefe

## Versão 11.2

- Correcção da obtenção e apresentação da secção actual.
- O detalhe apresenta secção e nominativo.
- A listagem apresenta secção/nominativo.
- É possível criar associados sem companhia.

## Versão 11.3

- Data de nascimento introduzida no formato europeu `dd/mm/aaaa`.
- Conversão interna para `YYYY-MM-DD` antes de gravar na MariaDB/MySQL.
- Validação de datas reais.

## Versão 11.4

- Correcção do formulário de edição de associados.
- Data de nascimento carregada e apresentada em `dd/mm/aaaa`.
- Secção actual carregada e seleccionada no formulário de edição.
- Alteração de secção passa a manter o histórico em `associados_seccoes`.
- Validação da secção durante a actualização.
- Não existem alterações ao schema da base de dados.
