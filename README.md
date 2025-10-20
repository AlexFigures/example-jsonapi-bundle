# JSON:API Symfony Demo

This repository contains a minimal Symfony 7.3 application demonstrating how to integrate the [AlexFigures/symfony-jsonapi-bundle](https://github.com/AlexFigures/jsonapi-symfony) with two Doctrine entity managers that point to different databases (PostgreSQL and MySQL).

The example exposes three JSON:API resources:

- `authors` and `articles` stored in PostgreSQL (`src/PgEntity`).
- `comments` stored in MySQL (`src/MysqlEntity`).

Integration tests exercise CRUD workflows, relationships, sparse fieldsets, includes, and JSON:API content negotiation.

## Requirements

- Docker and Docker Compose v2
- Make (optional)
- PHP 8.2+ if running Symfony commands locally instead of inside Docker

## Project Structure

```
├── config/
│   ├── packages/doctrine.yaml       # Two Doctrine connections/entity managers
│   ├── packages/jsonapi.yaml        # JsonApiBundle configuration
│   └── routes.yaml                  # Automatic JSON:API routes
├── docker-compose.yml               # PHP-FPM, PostgreSQL, MySQL, and Nginx services
├── src/PgEntity                     # PostgreSQL backed entities (Author, Article)
├── src/MysqlEntity                  # MySQL backed entity (Comment)
└── tests/Integration                # JSON:API integration tests
```

## Quick Start

1. **Start the Docker environment**

   ```bash
   docker compose up -d
   ```

2. **Install dependencies** (inside the PHP container):

   ```bash
   docker compose exec php composer install
   ```

3. **Prepare the databases**

   Run the schema commands for both entity managers. The sample project uses Doctrine SchemaTool for convenience.

   ```bash
   docker compose exec php php bin/console doctrine:database:create --connection=pgsql
   docker compose exec php php bin/console doctrine:database:create --connection=mysql
   docker compose exec php php bin/console doctrine:schema:update --force --em=pgsql
   docker compose exec php php bin/console doctrine:schema:update --force --em=mysql
   ```

4. **Run the Symfony built-in server (optional)**

   You can serve the app either via the bundled Nginx container (exposed on <http://localhost:8080>) or by using Symfony CLI inside the PHP container:

   ```bash
   docker compose exec php symfony server:start --no-tls --port=8000
   ```

## JSON:API Endpoints

The bundle auto-generates routes under `/api`. Examples:

- `POST /api/authors` – create an author
- `POST /api/articles` – create an article with an `author` relationship
- `POST /api/comments` – create a comment stored in MySQL
- `GET /api/articles/{id}?include=author&fields[articles]=title,createdAt` – fetch with include and sparse fieldset
- `GET /api/articles/{id}/relationships/author` – relationship linkage

Swagger UI is available at `/_jsonapi/docs` when the server is running.

## Running Tests

Integration tests exercise the JSON:API flows end-to-end. Ensure the databases are running and schemas are created (see steps above), then execute:

```bash
docker compose exec php ./vendor/bin/phpunit
```

The tests use Doctrine SchemaTool to reset both entity managers before the first test run.

## Environment Variables

The key environment variables live in `.env` and `phpunit.xml.dist`:

- `DATABASE_URL` – PostgreSQL connection (points at the `pgsql` Docker service)
- `MYSQL_URL` – MySQL connection (points at the `mysql` Docker service)

Override these in `.env.local` or `.env.test.local` when running outside of Docker.

## Troubleshooting

- **Database connection errors** – confirm `docker compose ps` shows both `pgsql` and `mysql` containers healthy and that the schemas have been created.
- **Unsupported Media Type (415)** – the JSON:API bundle enforces the `application/vnd.api+json` content type. Set both the `Content-Type` and `Accept` headers when calling the API.
- **Doctrine metadata not found** – verify entities are placed in `src/PgEntity` or `src/MysqlEntity` and that the namespaces match the mappings defined in `config/packages/doctrine.yaml`.

Happy hacking!
