# Repository Guidelines

## Project Structure & Module Organization

This PHP 8.2+ / Symfony 7.4 demo integrates `alexfigures/symfony-jsonapi-bundle` with two Doctrine entity managers. PostgreSQL resources (`Author`, `Article`) live in `src/PgEntity`; MySQL resources (`Comment`) live in `src/MysqlEntity`. Keep entities in the directory and namespace mapped to their database. Comments reference articles through `articleId` across databases.

`config/packages/` contains framework, Doctrine, and JSON:API configuration; `config/routes.yaml` imports generated API routes. HTTP tests live in `tests/Acceptance/`; production fault tests live in `tests/Torture/`. `public/index.php` is the HTTP entry point, and `docker/` holds the PHP image and database initialization. There is no frontend build pipeline.

## Build, Test, and Development Commands

- `docker compose up -d`: start PHP development server, PostgreSQL, and MySQL; access the application at `http://localhost:8080`.
- `docker compose exec php composer install`: install locked dependencies and run Symfony's cache/assets scripts.
- `docker compose exec php php bin/console doctrine:database:create --connection=pgsql`: create the PostgreSQL database; repeat with `--connection=mysql`.
- `docker compose exec php php bin/console doctrine:schema:update --force --em=pgsql`: prepare the demo schema; repeat with `--em=mysql`.
- `docker compose exec php php bin/console cache:clear`: refresh Symfony's cache after configuration changes.
- `docker compose exec php ./vendor/bin/phpunit tests/Acceptance`: run integration tests explicitly.

## Coding Style & Naming Conventions

Follow `.editorconfig`: UTF-8, LF endings, four spaces by default, and a final newline. Preserve existing YAML indentation. Use `declare(strict_types=1)`, typed properties and return values, PascalCase class names, and camelCase methods. Follow PSR-4 mappings (`App\` → `src/`, `App\Tests\` → `tests/`). Define entity mappings, validation, and resource metadata with PHP attributes. No formatter or static-analysis tool is currently configured.

## Testing Guidelines

Use PHPUnit with Symfony `WebTestCase`; name files `*Test.php` and methods `test...`. Cover changed CRUD behavior, relationships, fieldsets, and content negotiation. Send `application/vnd.api+json` headers. No coverage threshold is configured. Create both test databases using the database commands with `--env=test`; verify connection URLs for your execution environment. Tests drop and recreate mapped schemas, so use disposable test databases.

## Commit & Pull Request Guidelines

The short Git history uses imperative subjects such as `Add Symfony JSON:API demo application`; follow that style. PRs should describe changed behavior, identify affected resources or configuration, link relevant issues, and report test commands and results. Update `README.md` when setup or API usage changes. Keep local credentials in `.env.local` or `.env.test.local` rather than committed files.
