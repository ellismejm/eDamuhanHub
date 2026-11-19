# eDamuhanHub

eDamuhanHub is a web-based information and database management system for rice weeds in the Philippines. It provides a searchable weed species catalog and administrative tools for maintaining its reference data, users, images, imports, and database operations.

This README is also available as [README.docx](README.docx), which can be opened and edited in Microsoft Word.

## What the system provides

- Browse and search weed species and view their details, classification, family, local names, and images.
- View dashboard summaries of species, classifications, families, images, life cycles, and (for administrators) active users.
- Sign in with active user accounts and use role-based access for standard users and administrators.
- Let administrators manage species, user accounts, reference data, image records, and dataset imports.
- Keep an audit trail of important actions and provide database backup tools for MySQL deployments.
- Let users update their profile and password.

## Requirements

- PHP 8.3 or later (the project currently uses Laravel 13).
- Composer 2.
- Node.js and npm for compiling frontend assets.
- A database supported by the project: SQLite for a single-computer setup, or MySQL/MariaDB for a separate database server.
- The matching PHP PDO driver: `pdo_sqlite` or `pdo_mysql`.

## Set up on another computer

### 1. Get the project files

Copy or clone the project to the application computer. If using a ZIP, include the application source, `composer.json`, `composer.lock`, `package.json`, `package-lock.json`, and the `database/migrations` directory. Do not copy the existing computer's `.env`, `vendor`, or `node_modules` directories; each computer should have its own environment settings and installed dependencies.

Open a terminal in the project directory for the commands below. On Windows PowerShell, use `Copy-Item .env.example .env` in place of `cp .env.example .env`.

### 2. Install dependencies and configure the application

```sh
composer install
npm install
cp .env.example .env
php artisan key:generate
```

Edit `.env` and set at least:

```dotenv
APP_NAME=eDamuhanHub
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000
```

Keep `.env` private and out of version control. `APP_DEBUG=true` is for local development only; turn it off for a shared or production server.

### 3. Choose and configure the database

#### Option A: SQLite on the application computer

The supplied `.env.example` already selects SQLite. Leave `DB_CONNECTION=sqlite` and the MySQL settings commented out. Laravel will use `database/database.sqlite` by default; the first migration creates the database file if it does not exist.

#### Option B: MySQL/MariaDB on a different computer

On the database computer, install and start MySQL or MariaDB. Create a database and a dedicated application account. Run the following as a database administrator, replacing the example application-server IP and password with your own values:

```sql
CREATE DATABASE edamuhanhub
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

CREATE USER 'edamuhan_app'@'10.0.0.25'
    IDENTIFIED BY 'replace-with-a-strong-password';

GRANT ALL PRIVILEGES ON edamuhanhub.*
    TO 'edamuhan_app'@'10.0.0.25';
```

The host portion of the database account must match the application computer's address as seen by the database server. Use the narrowest host restriction practical; do not use a remote `root` account.

On the application computer, edit its `.env` and set the database server's reachable private IP address or hostname. Do not use `127.0.0.1` or `localhost` unless the database is on the same computer as the application.

```dotenv
DB_CONNECTION=mysql
DB_HOST=10.0.0.10
DB_PORT=3306
DB_DATABASE=edamuhanhub
DB_USERNAME=edamuhan_app
DB_PASSWORD=replace-with-a-strong-password
```

Replace `10.0.0.10` with the database computer's private address and use the exact database name, user, and password created above. Confirm that the MySQL server listens on its network interface and that its firewall allows port 3306 only from the application computer. For connections across untrusted networks, use a VPN or properly configured TLS; do not expose the database port to the public internet.

### 4. Create the tables and build the frontend

Run these commands from the application computer after saving `.env`:

```sh
php artisan config:clear
php artisan migrate --seed
php artisan storage:link
npm run build
```

The migrations create the application's tables. The seed command adds the Administrator and Standard User roles; it does not populate the weed catalog. If moving an existing catalog, transfer/import its data separately and also copy any associated uploaded images from `storage/app/public`.

### 5. Create the first administrator and sign in

Start the local development server:

```sh
php artisan serve
```

Open `http://localhost:8000/setup` and create the first administrator account. The one-time setup page closes after the first administrator is created. Sign in at `/login`.

For use from another computer, configure a proper web server and use the application computer's network name/address. Do not treat Laravel's development server as a production web server.

## Database notes

- Application configuration is read from `.env`; do not commit database credentials.
- Use `php artisan migrate:status` to see which migrations have run.
- Use `php artisan migrate` after receiving a project update that adds migrations.
- Back up the database before restoring a backup or running destructive maintenance.
- The in-app database backup feature is intended for MySQL connections. Keep a separate copy of uploaded files as well as the database.
- Do not run `php artisan migrate:fresh` on a database containing data; it deletes existing tables and records.

## How the project requirements are addressed

The implemented scope translates the need for a maintained, searchable rice-weed reference system into connected application features:

1. **Organized weed information:** The database schema separates species from classifications, families, habitats, life cycles, local names, and images. Users can browse and search the catalog and open individual species records.
2. **Useful day-to-day access:** The dashboard summarizes catalog and reference data, while the species pages provide the detailed records needed for lookup.
3. **Controlled maintenance:** Authentication, active accounts, and Administrator/Standard User roles separate public-facing information access from administrative changes. Administrators can maintain species, reference values, users, images, and imports.
4. **Accountability and continuity:** Audit logs record important actions, and the administration area includes database backup controls for MySQL deployments. Profile settings support account maintenance.
5. **Verifiable delivery:** Feature tests exercise key workflows such as sign-in, role restrictions, species browsing, dashboard data, administration, and the developer-information page. Database migrations make the schema repeatable on a new computer.

The development approach is to turn each need into a data model and migration, expose it through a route/controller, present it in the Blade interface, and verify important behavior with feature tests. The setup steps above complete that workflow on a new machine by installing dependencies, configuring its own database connection, applying the migrations, and creating the initial administrator.

## Meet the developers

The public **Our Developers** page is available at `/developers` and linked from the sign-in page. It currently has three editable placeholders. Replace `Meynard Ellis Molina`, `John Sebastian Mangilin`, and `Princess Ellieza Hipolito` in `resources/views/developers.blade.php` with the actual team names.

## Tests

Run the automated test suite with:

```sh
php artisan test
```
