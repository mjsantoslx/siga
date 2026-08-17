# SIGA MVC v2

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
