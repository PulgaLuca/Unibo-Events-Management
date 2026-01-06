# Unibo Events Management — Setup & Migrations

## Prerequisites
- PHP 8.1+
- Composer
- MySQL (or MariaDB)

## 1) Install dependencies
```bash
composer install
```

## 2) Configure environment
- Copy the example env file and edit database credentials:
```bash
copy .env.example .env   # Windows
# or
cp .env.example .env     # macOS/Linux
```
- Edit `.env` values

## 3) Ensure database exists
Create the database named in `DB_NAME` (default `unibo_matchskills_db`).

## 4) Run migrations (Phinx)
Phinx is configured via `config/phinx.php` and stores migration files under `database/migrations`.

- Windows (Composer vendor binaries):
```bash
vendor\bin\phinx status -c config\phinx.php
vendor\bin\phinx migrate -c config\phinx.php
```

- Cross-platform alternative:
```bash
php vendor/bin/phinx status -c config/phinx.php
php vendor/bin/phinx migrate -c config/phinx.php
```

## 5) Run seeders (Phinx)
Phinx is configured via `config/phinx.php` and stores seed files under `database/seeds`.

- Windows (Composer vendor binaries):
```bash
php vendor\bin\phinx seed:run -c config\phinx.php
```

- Cross-platform alternative:
```bash
php vendor/bin/phinx seed:run -c config/phinx.php
```


## 6) Start the development server
Serve the app from the `public` directory:
```bash
php -S localhost:8000 -t public
```
Visit http://localhost:8000 to see the home page rendered via Twig.

## Notes
- Environment variables are loaded in `public/index.php` via `vlucas/phpdotenv`.
- DI service definitions live in `config/container.php`.
