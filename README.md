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
performances and synchronised performance price lists into local storage through
Horizon-observable sync jobs. Pricing uses default ticket prices for headline
"from" amounts rather than presenting concessions as standard ticket prices. The
Filament administration and editorial gate is implemented: it separates editable
presentation content from synced provider data and exposes performances, pricing,
redirects and sync operations for inspection. Phase 3 is now underway with
server-rendered published event listing and detail pages sourced from local records,
including URL-driven catalogue search plus editor-managed `What`, `Offers` and
performance-specific `Access` filtering, and a provider-isolated embedded Spektrix
booking handoff. Cue's public availability and booking-support language is
managed through a dedicated Filament Content Strings editor with safe domain
defaults for fresh installations. Filament also defines public filter terms,
assigning `What` and `Offers` to events and performance-specific `Access`
provisions. Event detail pages provide date and access filters for long
performance runs. For launch, Cue deliberately does not claim live seat
availability before booking; availability and final pricing are confirmed within
the embedded Spektrix journey. Phase 4 now also surfaces Spektrix customer-facing
custom-domain readiness in Filament, keeping iframe and integration-script
configuration aligned for first-party booking sessions. Public pages include a
provider-isolated customer utility bar that reads Spektrix login status and basket
count through Web Components on that same active customer-facing domain; its
logged-out action opens a Cue form that authenticates directly against the Spektrix
Web User API in the browser, with logout, password reset and passwordless
magic-link login also routed directly to Spektrix; its basket action opens a
Cue-hosted embedded Spektrix journey page. Cue-owned status labels are editable
in Content Strings. Custom hosts are only
activated after confirmation and URL-readiness checks, including when legacy
iframe fallback configuration is present.

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
The Phase 4 operational staging exercise is documented in
[docs/staging-operations-runbook.md](docs/staging-operations-runbook.md).

The first implemented tranche is the catalogue sync foundation:

1. Generic events, performances and sync runs are stored locally.
2. A Spektrix adapter sits behind a generic ticketing provider contract.
3. Public event and instance data synchronises through queued jobs.

Next:

1. Seed and review a representative venue filter vocabulary in Filament.
2. Execute the Phase 4 production-like staging soak and failure drill.

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
