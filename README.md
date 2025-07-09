# Corta Fila Back-End

Este repositório contém o backend em PHP da aplicação **Corta Fila**.

## Configuração do Banco de Dados

1. Instale um servidor MySQL e crie o banco de dados `Corta_Fila_2` ou o nome que preferir.
2. Altere as credenciais utilizadas em `db.php`. O projeto sugere armazenar as informações em variáveis de ambiente:

   ```bash
   export DB_HOST=localhost
   export DB_NAME=Corta_Fila_2
   export DB_USER=root
   export DB_PASS=senha
   ```

3. Ajuste `db.php` para ler essas variáveis ou edite o arquivo com os valores desejados.

## Endpoints

### POST `/user/register.php`
Registra um novo usuário.

Exemplo de requisição:

```bash
curl -X POST http://localhost:8000/user/register.php \
     -H 'Content-Type: application/json' \
     -d '{"name":"João","phone":"+551199999999","password":"123456"}'
```

### POST `/user/login.php`
Realiza o login de um usuário.

```bash
curl -X POST http://localhost:8000/user/login.php \
     -H 'Content-Type: application/json' \
     -d '{"phone":"+551199999999","password":"123456"}'
```

### POST `/barber/register/register.php`
Cadastro de barbeiro (envio de foto via `multipart/form-data`).

```bash
curl -X POST http://localhost:8000/barber/register/register.php \
     -F 'name=Barbeiro' \
     -F 'email=barbeiro@example.com' \
     -F 'bio=Melhor barbeiro' \
     -F 'user_id=1' \
     -F 'photo=@/caminho/para/foto.jpg'
```

## Executando o servidor

1. Tenha o PHP instalado (versão 8 ou superior) com extensão PDO para MySQL.
2. Navegue até a raiz do projeto e execute:

   ```bash
   php -S localhost:8000
   ```

3. Os endpoints acima ficarão acessíveis em `http://localhost:8000`.

