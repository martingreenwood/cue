# Cue

Cue is a Laravel platform by Neurospicy Studio for theatre and live event websites.
It begins with a Spektrix integration, but its core domain is deliberately provider
agnostic: events, performances, venues, editorial content and booking journeys.

Cue is not a WordPress plugin rewrite. It replaces the useful integration behaviour
of WPSPX with a maintainable Laravel application that stores catalogue content
locally, renders fast public pages, and provides operational tooling for editors and
developers.

## Current Status

Cue is in vertical-slice development. It now imports public Spektrix events,
performances and current performance price lists into local storage through
Horizon-observable sync jobs. Pricing uses default ticket prices for headline
"from" amounts rather than presenting concessions as standard ticket prices. The
current focus is a comprehensive Filament administration and editorial surface
before public event pages are designed. The first admin tranche now separates
editorial event content from synced provider data and exposes performances, pricing,
redirects and sync operations for inspection.

Verified local stack:

| Component | Version / Choice |
| --- | --- |
| PHP | 8.4.21 via Laravel Herd |
| Laravel | 13.11.2 |
| Filament | 5.6.5 |
| Livewire | 4.3.0 |
| Tailwind CSS | 4.3.0 |
| Horizon | 5.47.0 |
| Database | PostgreSQL for local development and production alignment |
| Queue / cache / session | Valkey through Laravel's Redis driver |
| Tests | Pest 4.7.0 |
| Formatting | Pint 1.29.1 |
| Static analysis | Larastan 3.9.6 |

## Start Here

The product boundary, target architecture, milestone plan and production readiness
gates are documented in [docs/delivery-blueprint.md](docs/delivery-blueprint.md).

The first implemented tranche is the catalogue sync foundation:

1. Generic events, performances and sync runs are stored locally.
2. A Spektrix adapter sits behind a generic ticketing provider contract.
3. Public event and instance data synchronises through queued jobs.

Next:

1. Add dashboard widgets for sync health, failures and stale pricing.
2. Complete queued managed-image ingestion and optimisation.
3. Refine publication and redirect workflow after reviewing imported data in Filament.
4. Design public event pages from the reviewed admin-managed content model.

## Local Development

Laravel Herd serves the application locally. The supporting development processes
are run through Composer:

```bash
composer run dev
```

This starts Horizon, Laravel's scheduler, Pail and Vite. Valkey must be running for
Horizon, queues, cache and sessions. PostgreSQL must provide the configured `cue`
application database before running migrations.

Quality checks:

```bash
vendor/bin/pint --dirty --format agent
composer analyse
php artisan test --compact
```

The initial automated test harness uses an isolated in-memory SQLite database for
fast feedback. Add PostgreSQL-backed integration coverage when a feature depends on
PostgreSQL behaviour, such as specialised indexing or search.

For local catalogue data, `.env` may target Spektrix's public `apitesting` client.
Non-development environments must supply their intended Spektrix client URL
explicitly; there is no application-level demo fallback.

## Architecture Principles

- Public pages render from normalised local application data, not live API requests.
- External ticketing products are isolated behind provider adapters.
- Filament supplies admin and operational tooling only.
- Synchronisation work runs through observable, retry-safe queued jobs.
- Controllers and models remain thin; domain actions perform orchestration.
- The initial release ships a robust vertical slice rather than speculative platform features.
