# EasyAPI

API REST construída em PHP para cadastro e autenticação de usuários. A aplicação utiliza Slim 4 para as rotas, Eloquent para acesso ao banco de dados e JWT para autenticação.

## Recursos

- Cadastro de usuários com validação básica de nome, e-mail e senha.
- Armazenamento da senha como hash.
- Autenticação por e-mail e senha.
- Emissão de token JWT com validade de uma hora.
- Rota protegida para verificar se a API está funcionando.
- Respostas JSON com formato padronizado.
- Especificação OpenAPI 3.0 em [`openapi.json`](./openapi.json).

## Requisitos

- PHP 8.3 ou superior.
- Composer.
- MySQL ou outro banco compatível com o driver Eloquent configurado.
- Extensão PHP PDO e o driver PDO correspondente ao banco (por exemplo, `pdo_mysql` para MySQL).

## Instalação

Clone ou baixe o projeto e, na pasta raiz, instale as dependências:

```bash
composer install
```

Crie o arquivo local de ambiente a partir do exemplo:

```bash
cp .env.example .env
```

Edite o `.env` para informar as credenciais do banco e uma chave JWT secreta e exclusiva para o ambiente:

```dotenv
APP_ENV=development
APP_NAME='EasyAPI'

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=easyapi
DB_USERNAME=root
DB_PASSWORD=sua-senha
DB_CHARSET=utf8mb4
DB_COLLATION=utf8mb4_unicode_ci

JWT_SECRET=troque-por-uma-chave-longa-e-aleatoria
```

Crie o banco de dados indicado em `DB_DATABASE` e execute o SQL do arquivo [`database.sql`](./database.sql) nesse banco. Para MySQL, por exemplo:

```bash
mysql -u root -p -e "CREATE DATABASE easyapi CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p easyapi < database.sql
```

Se o nome ou o usuário do banco forem diferentes, ajuste os comandos e o `.env`. O script cria a tabela `usuarios` e inclui um usuário de exemplo para desenvolvimento:

- E-mail: `exemplo@usuario.com`
- Senha: `senha123`

Não utilize a chave ou as credenciais de exemplo em produção.

## Executar localmente

Na raiz do projeto, inicie o servidor embutido do PHP:

```bash
php -S 127.0.0.1:8080 -t public public/index.php
```

A API ficará disponível em `http://127.0.0.1:8080`.

## Documentação OpenAPI / Swagger

A interface Swagger está disponível na rota [`/docs`](http://127.0.0.1:8080/docs) quando o servidor local estiver em execução. Ela carrega automaticamente a especificação servida em `/openapi.json`.

Abra `http://127.0.0.1:8080/docs` no navegador após iniciar o servidor. A interface Swagger usa os arquivos do Swagger UI via CDN, portanto o navegador precisa de acesso à internet para carregá-los. A especificação OpenAPI também está disponível diretamente em [`/openapi.json`](http://127.0.0.1:8080/openapi.json).

O documento OpenAPI lista somente rotas registradas pela aplicação. Operações que possam existir em classes internas, mas não estejam ligadas a uma rota, não fazem parte da API HTTP documentada.

## Rotas

| Método | Caminho | Autenticação | Descrição |
|---|---|---|---|
| `GET` | `/docs` | Não | Abre a interface interativa da documentação Swagger. |
| `GET` | `/openapi.json` | Não | Retorna a especificação OpenAPI em JSON. |
| `POST` | `/login` | Não | Valida as credenciais e retorna um JWT. |
| `POST` | `/novo-usuario` | Não | Cadastra um usuário. |
| `GET` | `/` | Bearer JWT | Verifica se a API está em funcionamento. |

As requisições com corpo devem enviar `Content-Type: application/json`.

### Cadastrar usuário

```bash
curl -X POST http://127.0.0.1:8080/novo-usuario \
  -H 'Content-Type: application/json' \
  -d '{
    "nome": "Maria da Silva",
    "email": "maria@example.com",
    "senha": "UmaSenhaSegura123"
  }'
```

Os campos `nome`, `email` e `senha` são obrigatórios. O e-mail deve ter formato válido e não pode estar cadastrado. Em caso de sucesso, a API responde com HTTP `200`.

### Login

```bash
curl -X POST http://127.0.0.1:8080/login \
  -H 'Content-Type: application/json' \
  -d '{
    "email": "exemplo@usuario.com",
    "senha": "senha123"
  }'
```

Resposta de sucesso (o token foi abreviado):

```json
{
  "status": "success",
  "status_code": 200,
  "message": "Login realizado com sucesso.",
  "data": {
    "token": "<jwt>"
  }
}
```

Credenciais inválidas retornam HTTP `401`. O token expira após uma hora.

### Verificar a API com o token

Substitua `<jwt>` pelo token obtido no login:

```bash
curl http://127.0.0.1:8080/ \
  -H 'Authorization: Bearer <jwt>'
```

Sem o cabeçalho `Authorization`, ou com token inválido ou expirado, a API responde com HTTP `401`.

## Formato das respostas

As respostas JSON seguem o envelope:

```json
{
  "status": "success",
  "status_code": 200,
  "message": "Mensagem do resultado.",
  "data": null
}
```

O conteúdo de `data` depende da operação. Respostas de erro podem incluir também `error_id`. Os códigos de resposta mais relevantes são:

| Código | Significado |
|---|---|
| `200` | Requisição concluída; inclui sucesso e o caso em que o cadastro foi realizado. |
| `400` | JSON inválido, campo obrigatório ausente ou e-mail inválido. |
| `401` | Credenciais inválidas ou autenticação Bearer ausente/inválida. |
| `404` | Rota não encontrada. |
| `405` | Método HTTP não permitido para a rota. |
| `409` | E-mail já cadastrado. |
| `500` | Erro interno do servidor. |

## Estrutura do projeto

```text
config/                  Configuração do banco e tratamento de erros
public/index.php         Inicialização da aplicação Slim
src/Controllers/         Controladores HTTP
src/Exceptions/          Exceções da aplicação
src/Helpers/             Respostas JSON e suporte a JWT
src/Middlewares/         Middleware de autenticação
src/Models/              Modelos Eloquent
src/Routes/web.php       Rotas HTTP registradas
src/Services/            Regras de negócio
database.sql             Estrutura e usuário de exemplo do banco
openapi.json             Especificação OpenAPI 3.0
```

## Observações

- Configure o `.env` antes de iniciar a aplicação; ele não deve ser versionado.
- `JWT_SECRET` deve permanecer privado e ser diferente em cada ambiente.
- O arquivo SQL e as credenciais de exemplo destinam-se somente ao desenvolvimento.
- As exceções gerais são convertidas em respostas JSON pelo tratamento de erros configurado pela aplicação.
