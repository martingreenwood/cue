# Cue

Cue is a Laravel platform for theatre and live event websites.

It gives venues a fast public website, an editorial admin area, and a reliable
bridge into ticketing systems. The first production integration is Spektrix, but
Cue keeps provider-specific details behind a ticketing contract so the core
application can speak in venue-friendly language: events, performances, pricing,
filters, content, donations, memberships and booking journeys.

Cue is not a WordPress plugin rewrite. It takes the useful behaviour of older
Spektrix website integrations and rebuilds it as a maintainable Laravel
application with locally stored catalogue data, server-rendered public pages,
observable sync jobs and Filament-powered operations.

## What Cue Is For

Cue is designed for arts organisations that need their website to stay close to
their ticketing system without making every page render depend on a live external
API call.

It is currently focused on:

- synchronising public event, performance and pricing data from Spektrix;
- storing a normalised local catalogue for fast public browsing;
- letting editors enhance synced events with website-owned presentation content;
- exposing operational health checks for catalogue, pricing, journey and booking
  domain readiness;
- handing customers off into provider-managed booking, account and payment
  journeys without Cue storing customer accounts or card details;
- providing a Laravel foundation that can grow beyond Spektrix later.

## Current Features

### Public Website

- Server-rendered homepage support through Filament Fabricator pages.
- Public event listing at `/events` with URL-driven search and filters.
- Public event detail pages with performance listings and booking actions.
- Search suggestion endpoint for upcoming public events.
- Editor-managed filters for `What`, `Offers` and performance-specific `Access`.
- Locally rendered guide pricing with freshness-aware copy.
- Event slug redirects so changed public URLs can be preserved.
- Public customer utility bar for provider login and basket status.
- Cue-hosted wrapper pages for login, account, basket, checkout, donations, gift
  vouchers and memberships.

### Admin And Editorial Tools

- Filament admin panel at `/admin`.
- Event, performance, pricing, redirect and sync-run inspection.
- Editable event presentation fields separated from synced provider data.
- Filter vocabulary management for programme and access discovery.
- Donation fund and membership management for customer journey pages.
- Content Strings page for public-facing labels, notices and booking language.
- Filament Fabricator page builder blocks for general content pages, including
  hero, text, media, accordions, file downloads and related content.

### Ticketing And Sync

- Spektrix public API catalogue sync for events and performances.
- Spektrix performance price sync using a standard-price display policy.
- Donation fund and membership sync for customer journey surfaces.
- Scheduled sync commands for catalogue, pricing and customer journeys.
- Horizon-backed queue processing for observable sync jobs.
- Sync run records for import status, failure visibility and operational review.
- Custom-domain readiness checks for Spektrix customer-facing journeys.
- Provider isolation through `TicketingProvider` contracts and infrastructure
  adapters.

### Operations And Quality

- Laravel Horizon dashboard metrics and scheduled snapshots.
- PostgreSQL as the local and production-aligned relational database.
- Valkey or Redis for queues, cache and sessions.
- Pest feature and unit test coverage.
- Pint formatting and Larastan static analysis.
- Delivery and staging notes in `docs/`.

## Tech Stack

| Area | Tooling |
| --- | --- |
| Runtime | PHP 8.4 via Laravel Herd |
| Framework | Laravel 13 |
| Admin UI | Filament 5 |
| Page building | Filament Fabricator |
| Reactive UI | Livewire 4 |
| Frontend | Tailwind CSS 4 and Vite 8 |
| Queue monitoring | Laravel Horizon 5 |
| Database | PostgreSQL |
| Queue / cache / session | Valkey or Redis |
| Tests | Pest 4 |
| Formatting | Laravel Pint |
| Static analysis | Larastan |

## Installation

These steps assume local development on macOS with Laravel Herd, PostgreSQL,
Valkey or Redis, Composer and Node.js already installed.

### 1. Clone The Repository

```bash
git clone <repository-url> cue
cd cue
```

### 2. Create The Database

Create a PostgreSQL database named `cue` before running migrations:

```bash
createdb cue
```

If your local PostgreSQL user or password differs from the defaults, update the
database variables in `.env` after copying the example file.

### 3. Install PHP And Frontend Dependencies

The project includes a Composer setup script that installs dependencies, creates
`.env`, generates the app key, runs migrations, installs npm packages and builds
frontend assets:

```bash
composer run setup
```

If you prefer to run the setup manually:

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm install
npm run build
```

### 4. Configure Local Services

Make sure PostgreSQL and Valkey or Redis are running. The default `.env.example`
uses:

```dotenv
DB_CONNECTION=pgsql
DB_DATABASE=cue
QUEUE_CONNECTION=redis
CACHE_STORE=redis
SESSION_DRIVER=redis
REDIS_HOST=127.0.0.1
```

For Laravel Herd, the site is available at:

```text
https://cue.test
```

### 5. Configure Ticketing

For local catalogue development, `.env.example` points at the public Spektrix
`apitesting` API:

```dotenv
TICKETING_PROVIDER=spektrix
SPEKTRIX_API_BASE_URL=https://system.spektrix.com/apitesting/api/v3
```

Customer-facing booking journeys require a confirmed Spektrix customer-facing
domain:

```dotenv
SPEKTRIX_CUSTOMER_FACING_BASE_URL=
SPEKTRIX_CUSTOM_DOMAIN_CONFIRMED=false
```

Keep sync schedules disabled until the provider configuration is ready:

```dotenv
TICKETING_CATALOGUE_SYNC_ENABLED=false
TICKETING_PRICE_SYNC_ENABLED=false
TICKETING_JOURNEY_SYNC_ENABLED=false
```

### 6. Seed Local Data

Run the database seeder to create the default admin user and filter vocabulary:

```bash
php artisan db:seed
```

The seeded local admin user is:

```text
Email: test@example.com
Password: password
```

### 7. Run The Development Processes

Laravel Herd serves the site, so do not start a separate PHP web server. Use the
Composer dev command for the supporting workers:

```bash
composer run dev
```

This starts:

- Horizon queue worker;
- Laravel scheduler;
- Pail log tailing;
- Vite development server.

## Common Commands

### Sync Ticketing Data

Queue a full catalogue sync:

```bash
php artisan ticketing:sync-catalogue
```

Queue a performance price sync:

```bash
php artisan ticketing:sync-prices
```

Refresh one local performance price list:

```bash
php artisan ticketing:sync-prices --performance=1
```

Sync donation funds and memberships:

```bash
php artisan ticketing:sync-journeys
```

### Quality Checks

Format changed PHP files:

```bash
vendor/bin/pint --dirty --format agent
```

Run static analysis:

```bash
composer analyse
```

Run tests:

```bash
php artisan test --compact
```

Build frontend assets:

```bash
npm run build
```

## Application Map

| Area | Path |
| --- | --- |
| Public routes | `routes/web.php` |
| Scheduled commands | `routes/console.php` |
| Event domain | `app/Domains/Events` |
| CMS domain | `app/Domains/CMS` |
| Ticketing contracts and data | `app/Domains/Ticketing` |
| Spektrix adapter | `app/Infrastructure/Ticketing/Spektrix` |
| Filament admin resources | `app/Filament/Resources` |
| Filament admin pages | `app/Filament/Pages` |
| Public event views | `resources/views/events` |
| Ticketing journey views | `resources/views/ticketing` |
| Page builder blocks | `app/Filament/Fabricator/PageBlocks` |
| Tests | `tests` |

## Architecture Principles

- Public pages render from normalised local data, not live ticketing API calls.
- Provider-specific behaviour stays inside infrastructure adapters.
- Cue-owned editorial content is stored separately from synced provider fields.
- Synchronisation runs through retry-safe, observable queued jobs.
- Filament is the admin and operations surface, not the public rendering engine.
- Pricing copy is careful: guide prices are local snapshots, while final price and
  availability are confirmed inside the provider booking journey.
- Customer accounts, baskets and checkout remain provider-managed in the current
  slice.

## Documentation

- `docs/delivery-blueprint.md` records the product boundary, architectural
  decisions and delivery milestones.
- `docs/staging-operations-runbook.md` covers the production-like operational
  staging exercise.
- `docs/page-builder-proposal.md` records page builder direction and decisions.

## Current Status

Cue is in vertical-slice development. The Spektrix-backed catalogue, pricing,
editorial admin, public event pages, content strings, filter vocabulary, customer
journey wrappers and operational widgets are implemented. The project is suitable
for local development and staging exercises, but production use still depends on
venue-specific provider configuration, custom domain confirmation, operational
soak testing and launch readiness review.
