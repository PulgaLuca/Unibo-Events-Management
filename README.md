# Unibo Events Management - Project Overview

## Purpose
This project is an event and team management application created for the "Web Development" course. It provides features for creating and searching events, managing teams and memberships, assigning skills to users and events, and administering users and skills for the students of the University of Bologna (but its generalization is so clean that could be used for each organization ;-) ).

### Architecture & Design
- Layered architecture: 
    - `Presentation` (controllers, views),
    - `Application` (services), 
    - `Domain` (entities),
    - `Infrastructure` (HTTP, database, persistence).
- Domain-Driven elements: rich `Domain/Entities` and `Repositories` to encapsulate business rules.
- Design patterns used: Repository Pattern, Service Layer, Dependency Injection (container at `config/container.php`), Router abstraction, and small reusable Traits for entity attributes.
- Persistence: a MySQL-backed `Persistence/Mysql` implementation accessed via a PDO wrapper (`Infrastructure/Database/PdoConnection.php`). Migrations and seeders are managed with Phinx (`config/phinx.php`).

### Libraries & Components
- PHP 8.1+ and Composer for dependency management.
- Twig for server-side templates (views in `resources/views`).
- Phinx for database migrations and seeds (`database/migrations`, `database/seeds`).
- `vlucas/phpdotenv` for environment variable loading.
- Built-in PHP server support for local development (`php -S ... -t public`).
- Frontend: static assets under `public/assets` (CSS, JS, images) used by the Twig templates.

### Where to look in the codebase
- Controllers: `src/Presentation/Controllers` — HTTP entry points and request handling.
- Services: `src/Application/Services` — application workflows and use-cases.
- Domain: `src/Domain/Entities` and `src/Domain/Repositories` — core business models and persistence interfaces.
- Infrastructure: `src/Infrastructure` — container factory, database connection, router and persistence implementations.
- Migrations & Seeds: `database/migrations`, `database/seeds`.

### Quick notes
- Configuration: `config/container.php` and `config/routes.php` wire services and routes.
- Database: create the DB and use Phinx to run migrations and seeds as described above.
- Contributions: follow existing patterns (services, repositories, controllers) and add tests where appropriate.

---

## Setup & Migrations

### Prerequisites
- PHP 8.1+
- Composer
- MySQL (or MariaDB)

### 1) Install dependencies
```bash
composer install
```

### 2) Configure environment
- Copy the example env file and edit database credentials:
```bash
copy .env.example .env   # Windows
# or
cp .env.example .env     # macOS/Linux
```
- Edit `.env` values

### 3) Ensure database exists
Create the database named in `DB_NAME` (default `unibo_matchskills_db`).

### 4) Run migrations (Phinx)
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

### 5) Run seeders (Phinx)
Phinx is configured via `config/phinx.php` and stores seed files under `database/seeds`.

- Windows (Composer vendor binaries):
```bash
php vendor\bin\phinx seed:run -c config\phinx.php
```

- Cross-platform alternative:
```bash
php vendor/bin/phinx seed:run -c config/phinx.php
```

### 5.1) Load DB Model and Data
It is also possible load the database model with inserted data from:\
**Unibo-Events-Management\docs\db\unibo_matchskills_db.sql**


### 6) Start the development server
Serve the app from the `public` directory:
```bash
php -S localhost:8000 -t public
```
Visit http://localhost:8000 to see the home page rendered via Twig.

### Notes
- Environment variables are loaded in `public/index.php` via `vlucas/phpdotenv`.
- DI service definitions live in `config/container.php`.
