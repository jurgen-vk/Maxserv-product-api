# MaxServ B.V. Assignment

## Table of Contents

- [Overview](#overview)
- [Getting Started](#getting-started)
- [Commands](#commands)
- [Project Structure](#project-structure)
- [Packages](#packages)

---

## Overview

This project is a foundational implementation for the MaxServ B.V. assignment: a small product catalog API/app built
without a PHP framework, on top of a custom, hand-rolled `core` package (DI container, routing, Twig rendering,
database/migrations, caching, messaging, etc.). This is the foundational side of the application, where the symfony
components and other global functionalities are wired up. The `app` package is where the actual assignment (
product/brand/category management, imports from an external API,
filtering/searching/pagination, real-time import status via Mercure) is implemented on top of that foundation. The use
of PHP frameworks is not permitted, PHP
packages/libraries are.

---

## Getting Started

### Prerequisites

- **Docker** - runs the app, database, Mercure hub, and reverse proxy.
- **Git**
- **mkcert** - generates locally-trusted HTTPS certificates. See the scenario-specific install steps below.
- **Node.js/npm** - required. `docker-compose.override.yml` (step 4) bind-mounts your project folder in dev mode over
  the container's `/var/www/html`, which shadows the JS assets the Docker image builds internally, so `npm run dev`
  needs to actually be running for the frontend to load at all locally.
- **Composer** - required, for the same reason: the bind mount also shadows the image's own `vendor/`, so dependencies
  need to be installed on your host too.

_**Note:** I would recommend placing / running the application inside of wsl / linux. Placing / running the application in windows adds a very large delay when viewing the website in the web browser, because of the translation that needs to be done between windows and the docker environment in linux._

### 1. Clone the repository

Clone the repository:

```
git clone https://github.com/jurgen-vk/Maxserv-product-api.git
```

Move into the project directory:

```
cd Maxserv-product-api
```

### 2. Install mkcert and generate local HTTPS certificates

Pick the scenario that matches your setup.

<details>
<summary><strong>Scenario 1: Project in WSL/Linux, Browser in WSL/Linux</strong></summary>

Run these commands inside your WSL/Linux terminal at the project root.

Install mkcert:

```
sudo apt update && sudo apt install -y mkcert libnss3-tools
```

Add mkcert's local CA to your system/browser trust store:

```
mkcert -install
```

Create the certs directory:

```
mkdir -p certs
```

Generate the certificate:

```
mkcert -cert-file certs/localhost.pem -key-file certs/localhost-key.pem localhost 127.0.0.1 ::1
```

*Note: You may need to completely restart your browser for it to pick up the new certificates.*

</details>

<details>
<summary><strong>Scenario 2: Project in WSL/Linux, Browser in Windows</strong></summary>

Run these commands inside your WSL/Linux terminal at the project root.

Install mkcert on Windows, from within WSL:

```
powershell.exe -Command "winget install FiloSottile.mkcert"
```

*Note: You may need to restart your WSL/Linux terminal after installation for the next commands.*

Add mkcert's local CA to Windows' trust store:

```
mkcert.exe -install
```

Create the certs directory:

```
mkdir -p certs
```

Generate the certificate:

```
mkcert.exe -cert-file certs/localhost.pem -key-file certs/localhost-key.pem localhost 127.0.0.1 ::1
```

*Note: You may need to completely restart your browser for it to pick up the new certificates.*

</details>

<details>
<summary><strong>Scenario 3: Project in Windows, Browser in Windows</strong></summary>

Run these commands inside a Windows PowerShell terminal at the project root.

Install mkcert:

```
winget install FiloSottile.mkcert
```

*Note: Restart your PowerShell terminal window before running the next lines.*

Add mkcert's local CA to your system/browser trust store:

```
mkcert -install
```

Create the certs directory:

```
New-Item -ItemType Directory -Force -Path certs
```

Generate the certificate:

```
mkcert -cert-file certs\localhost.pem -key-file certs\localhost-key.pem localhost 127.0.0.1 ::1
```

*Note: You may need to completely restart your browser for it to pick up the new certificates.*

</details>

### 3. Create your `.env` file

Copy the example environment file:

```
cp .env.example .env
```

Fill in:

- `MYSQL_ROOT_PASSWORD` / `MYSQL_DATABASE` / `MYSQL_USER` / `MYSQL_PASSWORD` - any local values.
- `MERCURE_PUBLISHER_JWT_KEY` and `MERCURE_SUBSCRIBER_JWT_KEY` - each a random secret string. You can generate one, for
  example, with [this random string generator](https://pinetools.com/random-string-generator), or a terminal command. I
  recommend making a random string of at least 32 characters, using a-z, A-Z, and 0-9.
- `ACME_EMAIL` can stay blank locally - it's only used by the production `Caddyfile`, not the local one (see next step).

`MERCURE_PUBLISHER_JWT` also needs to be generated, using [jwt.io](https://www.jwt.io/):

1. Click the **JWT Encoder** tab (the site opens on the Decoder tab by default).
2. Under **Header** → **Algorithm & Token Type**, leave the default as-is, it's already
   `{ "alg": "HS256", "typ": "JWT" }`.
3. Under **Payload** → **Data**, replace the default JSON with:
   ```json
   {
     "mercure": {
       "publish": ["*"]
     }
   }
   ```
4. Under **Sign JWT** → **Secret**, replace the example text (`a-string-secret-at-least-256-bits-long`) with the
   _**same**_
   random string you used for `MERCURE_PUBLISHER_JWT_KEY` above. Leave the `Base64URL Encoded` toggle off, since this is
   a plain-text secret.
5. The **Encoded JWT** box further down updates automatically, and that full string is `MERCURE_PUBLISHER_JWT`.

*Note: jwt.io signs the token entirely in your browser (no network request), so this is safe to use even for a real
secret, but treat it as local-dev-only regardless.*

### 4. Create your `docker-compose.override.yml`

Copy the example override file:

```
cp docker-compose.override.yml.example docker-compose.override.yml
```

This swaps in the mkcert-based `Caddyfile.dev` (instead of production's ACME-based `Caddyfile`), mounts `./certs`, and
bind-mounts your source into the containers so code changes don't need a rebuild.

### 5. Install dependencies on your host

Install PHP dependencies:

```
composer install
```

Install JS dependencies:

```
npm install
```

This step is required, not just for IDE support: `docker-compose.override.yml` bind-mounts your project folder over the
containers' `/var/www/html`, which shadows whatever the Docker image built internally at that path, including `vendor/`
and the compiled JS assets. Without this step, the app containers won't be able to find `vendor/autoload.php`, and the
frontend won't have anything to serve.

### 6. Build and start everything

Build the images and start every service:

```
docker-compose up -d --build
```

Runs database migrations via the `migrate` service, then starts `app`, `app-proxy`, `mercure`, `messenger-imports`,
`db`, and `phpmyadmin`.

### 7. Start the frontend dev server

Start the Vite dev server:

```
npm run dev
```

Starts Vite on `https://localhost:5173`, matching `VITE_SERVER_URL` in `.env`. Keep this running, in dev mode, the app
loads its CSS/JS directly from this dev server rather than from a pre-built `public/assets/` folder, so without it the
site will load with no styling or interactivity.

### 8. Verify

| Service    | URL                                                        |
|------------|------------------------------------------------------------|
| App        | `https://localhost` (trusted, thanks to `mkcert -install`) |
| phpMyAdmin | `http://localhost:8081`                                    |

---

## Commands

Most commands run **inside the `app` container**. The root `composer.json` provides a shortcut for that:

```
composer -- console <command> [arguments]
```

which forwards to `docker compose exec --user www-data app php bin/console <command>`. The `--` is required whenever
`[arguments]` includes a flag (e.g. `composer -- console migrate:fresh --force`), otherwise Composer swallows the flag
as its own option instead of forwarding it to the command.

### Composer scripts (run on your host)

| Command                         | What it does                                               |
|---------------------------------|------------------------------------------------------------|
| `composer -- console <command>` | Runs any `bin/console` command inside the `app` container. |
| `composer restart-imports`      | Restarts the `messenger-imports` worker container.         |

### npm scripts (run on your host)

| Command         | What it does                                                              |
|-----------------|---------------------------------------------------------------------------|
| `npm run dev`   | Starts the Vite dev server for `packages/app` (`https://localhost:5173`). |
| `npm run build` | Builds production assets for `packages/app` into `public/assets/`.        |

### Console commands (`composer -- console <name>`)

| Command                                    | What it does                                                                                                                                                                                                                                                                                                                                                                                                                                                    |
|--------------------------------------------|-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `cache:clear`                              | Clear every cache (container, templates, routes).                                                                                                                                                                                                                                                                                                                                                                                                               |
| `cache:warmup`                             | Warm up every cache.                                                                                                                                                                                                                                                                                                                                                                                                                                            |
| `container:cache`                          | Build and cache the DI container.                                                                                                                                                                                                                                                                                                                                                                                                                               |
| `container:clear`                          | Clear the cached DI container.                                                                                                                                                                                                                                                                                                                                                                                                                                  |
| `route:cache`                              | Warm up the route cache.                                                                                                                                                                                                                                                                                                                                                                                                                                        |
| `route:clear`                              | Clear the route cache.                                                                                                                                                                                                                                                                                                                                                                                                                                          |
| `template:cache`                           | Warm up the Twig template cache.                                                                                                                                                                                                                                                                                                                                                                                                                                |
| `template:clear`                           | Clear the Twig template cache.                                                                                                                                                                                                                                                                                                                                                                                                                                  |
| `make:template <path> [options]`           | Scaffold a new template (`index.html.twig`, `index.css`, `index.js`) at `<path>` (e.g. `pages.products.show._nav-menu`). By default generates all three files; pass one or more of `--twig`/`-t`, `--css`/`-c`, `--js`/`-j` to generate only those. Other options: `--class=<name>` (override the wrapping element's class), `--name=<name>` (override the base filename, default `index`), `--package=<name>` (which package to generate into, default `app`). |
| `migrate:init`                             | Run migrations, but only against a genuinely uninitialized database, a no-op otherwise (this is what the `migrate` Docker service runs on every `up`).                                                                                                                                                                                                                                                                                                          |
| `migrate`                                  | Run pending migrations.                                                                                                                                                                                                                                                                                                                                                                                                                                         |
| `migrate:install`                          | Create the migrations tracking table, without running any migrations.                                                                                                                                                                                                                                                                                                                                                                                           |
| `migrate:reset [--force]`                  | Drop every table without re-running migrations. `--force` skips the confirmation prompt.                                                                                                                                                                                                                                                                                                                                                                        |
| `migrate:fresh [--force]`                  | Drop every table and re-run all migrations from scratch. `--force` skips the confirmation prompt.                                                                                                                                                                                                                                                                                                                                                               |
| `make:migration <name> [--package=<name>]` | Scaffold a new, timestamped migration file, e.g. `create_products_table`. `--package` picks which package it belongs to (default `app`).                                                                                                                                                                                                                                                                                                                        |
| `import:clear-stuck`                       | Mark imports stuck in `pending`, `started`, or `running` as failed (e.g. after a worker crash/restart).                                                                                                                                                                                                                                                                                                                                                         |

---

## Project Structure

### Root

| Path                                              | Description                                                                                                                                                                                                                                               |
|---------------------------------------------------|-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `bin/console`                                     | CLI entry point, boots the app and runs Symfony Console commands against it.                                                                                                                                                                              |
| `public/`                                         | Web entry point (`index.php`, `.htaccess`), kept intentionally minimal.                                                                                                                                                                                   |
| `packages/`                                       | The two workspace packages, `core` and `app` (see below).                                                                                                                                                                                                 |
| `Dockerfile`                                      | Multi-stage build: a Composer stage for PHP deps, a Node stage that builds frontend assets with Vite, and the final `php:8.5-apache` runtime image.                                                                                                       |
| `docker-compose.yml`                              | The service definitions: `app`, `db`, `phpmyadmin`, `migrate`, `app-proxy`, `messenger-imports`, `mercure`.                                                                                                                                               |
| `docker-compose.override.yml.example`             | Template for local dev overrides (bind-mounts source into containers, swaps in the mkcert-based Caddyfile). Copy to `docker-compose.override.yml`, which is gitignored.                                                                                   |
| `Caddyfile` / `Caddyfile.dev`                     | Reverse proxy config for production (ACME-issued certs) vs. local dev (mkcert certs).                                                                                                                                                                     |
| `docker-entrypoint.sh`                            | Container startup script: syncs the `www-data` UID/GID to match your host user in dev (so bind-mounted files aren't owned by root), and repairs `vendor/maxserv/*` symlinks that break when the project is bind-mounted from a native Windows filesystem. |
| `composer.json` / `composer.lock`                 | Root Composer manifest, registers `packages/*` as path repositories so `core`/`app` are installed as (symlinked) local packages.                                                                                                                          |
| `package.json` / `package-lock.json`              | Root npm workspace manifest (`packages/*` as npm workspaces).                                                                                                                                                                                             |
| `.env.example`                                    | Template for local environment variables (DB credentials, Mercure keys, etc.).                                                                                                                                                                            |
| `.gitignore` / `.gitattributes` / `.dockerignore` | VCS ignore rules, line-ending normalization, and Docker build-context ignore rules.                                                                                                                                                                       |
| `setup.md`                                        | The full local setup walkthrough referenced above.                                                                                                                                                                                                        |

### Core

The framework-less foundation: DI container, routing, Twig rendering, database/migrations, caching, and messaging, all built on Symfony components rather than a framework. Boot logic itself (building the container) lives directly in `src/Bootstrap.php`, invoked by both real entry points (`public/index.php` for HTTP, `bin/console` for CLI).

| Path                       | Description                                                                                                  |
|----------------------------|--------------------------------------------------------------------------------------------------------------|
| `src/Container/`           | Builds and caches the dependency-injection container, plus its `container:cache`/`container:clear` commands. |
| `src/Routing/`             | Attribute-based route loading and matching, plus route caching and its `route:cache`/`route:clear` commands. |
| `src/Twig/`                | Template rendering, HTML/JSON error pages, Twig extensions, and the `template:*`/`make:template` commands.   |
| `src/Database/`            | The database connection and migration runner, plus all `migrate:*` and `make:migration` commands.            |
| `src/Cache/`               | The shared interface every cache layer implements, plus the aggregate `cache:clear`/`cache:warmup` commands. |
| `src/DependencyInjection/` | Compiler passes and environment-variable processing used while building the container.                       |
| `src/Messenger/`           | Integration code for Symfony Messenger, the async message bus.                                               |
| `src/Mercure/`             | A wrapper around the Mercure hub for pushing real-time updates to the browser.                               |
| `src/Notification/`        | In-app notification events and the subscriber that broadcasts them.                                          |
| `src/Http/`                | HTTP-related utilities used across the app.                                                                  |
| `src/FileSystem/`          | Filesystem-related utilities used across the app.                                                            |
| `src/Runtime/`             | Environment/runtime configuration applied during boot.                                                       |
| `src/Event/`               | The base event class other packages' events extend.                                                          |
| `src/Time/`                | Time/date-related utilities used across the app.                                                             |
| `config/`                  | This package's own DI service definitions.                                                                   |
| `templates/`               | Shared base templates (error pages, layouts) other packages build on.                                        |
| `assets/`                  | Shared frontend code and icons.                                                                              |

### App

The actual assignment: a product catalog with brands/categories, media, filtering/search/pagination, and an import pipeline that pulls products from an external API in the background.

| Path                                   | Description                                                                                                 |
|----------------------------------------|-------------------------------------------------------------------------------------------------------------|
| `src/Controller/`                      | HTTP controllers: read the request, delegate to the domain, and return a response.                          |
| `src/Entity/`                          | Plain domain objects representing the app's core concepts.                                                  |
| `src/Repository/`                      | Database query logic, one repository per entity.                                                            |
| `src/Filter/`                          | Translates a listing endpoint's request query parameters into SQL `WHERE`/search/sort clauses.              |
| `src/Hydrator/`                        | Maps raw external data (database rows, API payloads) into entity objects.                                   |
| `src/Importer/`                        | Fetches data from an external source and persists it into the database.                                     |
| `src/Extractor/`                       | Extracts and normalizes raw data out of a payload before it's hydrated.                                     |
| `src/Service/`                         | Self-contained, reusable business logic that doesn't belong on an entity, repository, or controller.        |
| `src/Utility/`                         | Small, stateless helper functions with no business logic of their own.                                      |
| `src/Message/` / `src/MessageHandler/` | Message/handler pairs dispatched onto the async message bus for background processing.                      |
| `src/Event/`                           | Domain events dispatched at points of interest, namespaced per feature.                                     |
| `src/EventSubscriber/`                 | Listeners that react to dispatched domain events, namespaced per feature.                                   |
| `src/Command/`                         | Custom Symfony Console commands for this package.                                                           |
| `src/Dto/`                             | Small, immutable value objects that carry data between layers without behavior.                             |
| `src/Enum/`                            | PHP enums representing a fixed set of domain values.                                                        |
| `config/`                              | This package's own DI service definitions.                                                                  |
| `migrations/`                          | SQL migration files for this package's database schema.                                                     |
| `templates/`                           | The reusable UI component library, plus this package's pages, layouts, and error templates.                 |
| `assets/`                              | This package's own frontend code and static files (styles, scripts, icons, fonts, and other static assets). |

---

## Packages

### PHP (Composer)

**Core**

| Package                                             | Description                                                        |
|-----------------------------------------------------|--------------------------------------------------------------------|
| `symfony/console`                                   | The CLI framework behind `bin/console`.                            |
| `symfony/dependency-injection`                      | The DI container.                                                  |
| `symfony/routing`                                   | Route matching/generation.                                         |
| `symfony/http-foundation`                           | Request/response abstraction.                                      |
| `symfony/config`                                    | Loading/validating configuration.                                  |
| `symfony/yaml`                                      | YAML parsing (used for `services.yaml`).                           |
| `symfony/event-dispatcher`                          | The event bus events/subscribers run on.                           |
| `symfony/messenger` + `symfony/doctrine-messenger`  | The async message bus and its Doctrine DBAL transport.             |
| `symfony/mercure`                                   | Publishing real-time updates over Mercure.                         |
| `symfony/property-access` / `symfony/property-info` | Reading/writing object properties and their type info dynamically. |
| `symfony/serializer`                                | (De)serializing data structures.                                   |
| `symfony/twig-bridge`                               | Symfony's Twig integration extensions.                             |
| `symfony/finder`                                    | Locating files/directories (e.g. templates, migrations).           |
| `symfony/filesystem`                                | Filesystem operations.                                             |
| `doctrine/dbal`                                     | The database abstraction layer used by `Connection`/`Migrator`.    |
| `twig/twig`                                         | The template engine.                                               |
| `userfrosting/vite-php-twig`                        | Resolves Vite-built asset paths/tags from Twig.                    |

**App**

| Package             | Description                                                               |
|---------------------|---------------------------------------------------------------------------|
| `guzzlehttp/guzzle` | The HTTP client used by `ProductImporter` to fetch from the external API. |
| `maxserv/core`      | This project's own `core` package.                                        |

`packages/app` also uses `symfony/http-foundation`, `symfony/mercure`, and `symfony/messenger` directly.

### JavaScript (npm)

**Root**

| Package                                  | Description                                                    |
|------------------------------------------|----------------------------------------------------------------|
| `ci`                                     | Detects whether code is currently running in a CI environment. |
| `@types/jquery` / `navigation-api-types` | TypeScript type definitions for editor support.                |

**App**

| Package                            | Description                                                                                        |
|------------------------------------|----------------------------------------------------------------------------------------------------|
| `jquery`                           | DOM/event handling used throughout the component JS.                                               |
| `tom-select`                       | The library behind the custom select/dropdown component.                                           |
| `maxserv-core`                     | This project's own `core` package's frontend assets.                                               |
| `vite` + `vite-plugin-static-copy` | The dev server/bundler and its static-asset-copying plugin.                                        |
| `postcss` + `postcss-custom-media` | CSS processing and the `--under-lg`-style custom media breakpoints used across component CSS.      |
| `picomatch` / `tinyglobby`         | Glob matching, used by the project's custom Vite plugins (e.g. auto-discovering component CSS/JS). |

**Core**

| Package  | Description                                   |
|----------|-----------------------------------------------|
| `jquery` | Used by `core`'s own shared frontend helpers. |
