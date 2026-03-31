# MeuCampeonato - Backend

API REST para gerenciamento de campeonatos, times, inscrições e partidas.

## Pré-requisitos

- Docker + Docker Compose
- Git

> ⚠️ Certifique-se de que as extensões do PHP abaixo estão habilitadas no seu php.ini (necessário para rodar o Laravel):
> - zip
> - pdo
> - pdo_sqlite

## Subindo a aplicação (Docker)

### 1) Clonar o projeto

```bash
git clone https://github.com/bielmorais30/MeuCampeonato.git
cd MeuCampeonato
```

### 2) Subir os containers

```bash
docker compose up -d --build
```

Isso vai subir:
- `app` (PHP/Laravel)
- `webserver` (Nginx)
- `db` (MySQL 8)

### 3) Instalar dependências do PHP

```bash
docker compose exec app composer install
```

### 4) Configurar ambiente

Crie o `.env` a partir do exemplo:

```bash
docker compose exec app cp .env.example .env
```

As variáveis de banco já estão preparadas para Docker no `.env.example`:

```env
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=championship_db
DB_USERNAME=laravel
DB_PASSWORD=root
```

### 5) Gerar chave da aplicação

```bash
docker compose exec app php artisan key:generate
```

### 6) Rodar migrations

```bash
docker compose exec app php artisan migrate
```

### 7) (Opcional) Rodar seeders

```bash
docker compose exec app php artisan db:seed
```

### 8) Acessar a API

- URL base: `http://localhost:8000`
- Exemplo: `http://localhost:8000/api/teams`

## Comandos úteis


- Rodar testes:

```bash
docker compose exec app php artisan test
```

- Parar containers:

```bash
docker compose down
```

## Alternativa sem Docker (local)

Se quiser rodar localmente com PHP 8.2+ e MySQL:

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```

API disponível em: `http://127.0.0.1:8000`.
