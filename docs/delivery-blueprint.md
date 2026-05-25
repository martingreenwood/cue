# Cue Delivery Blueprint

## Purpose

This document guides Cue from an early Laravel foundation to a production-ready
theatre website platform. It records the first product boundary, architectural
decisions, delivery milestones and release gates so implementation remains focused
and reviewable.

This is a living delivery document. It should change when a product decision,
external integration constraint or production requirement changes. It should not
become a catalogue of speculative future features.

## Product Definition

Cue is a fast, accessible and editorially capable website platform for theatres and
live event organisations. It connects to ticketing providers, initially Spektrix,
while owning the public website experience and locally stored catalogue content.

Cue initially solves:

- reliable catalogue synchronisation from Spektrix;
- local management of events, performances, display pricing and event imagery;
- a comprehensive Filament administration surface for inspecting synced data and
  defining editorial content ownership;
- server-rendered public listings and event pages;
- clear booking handoff into Spektrix purchase flows;
- admin visibility into imports, failures and editorial overrides.

Cue does not initially solve:

- multi-tenant hosting;
- a generic plugin marketplace;
- a visual page builder;
- customer accounts or complete checkout replacement;
- multiple ticketing providers in production;
- distributed services or event-driven infrastructure beyond Laravel queues.

The architecture must allow those ideas to be reconsidered later without paying
their complexity cost now.

## Verified Baseline

Baseline recorded on 2026-05-25:

| Area | Current State |
| --- | --- |
| Framework | Laravel 13.11.2 on PHP 8.4.21 via Herd |
| Admin UI | Filament 5.6.5 installed; starter admin panel exists |
| Reactive UI | Livewire 4.3.0 installed |
| Frontend build | Tailwind CSS 4.3.0 and Vite 8.0.14 |
| Queues | Horizon 5.47.0 installed; local queue connection is Redis/Valkey |
| Cache/session | Redis/Valkey locally |
| Local persistence | PostgreSQL |
| Testing | Pest 4.7.0 |
| Code style | Pint 1.29.1 |
| Static analysis | Larastan 3.9.6 with `phpstan.neon.dist` |
| Existing application | Laravel starter homepage and starter Filament panel only |

Local foundations already completed:

- `composer run dev` starts Horizon, the scheduler, Pail and Vite;
- `horizon:snapshot` is scheduled every five minutes for metrics;
- Valkey responds to Laravel's Redis-compatible configuration;
- PostgreSQL is the local relational database baseline before domain migrations;
- the initial fast Pest harness remains isolated on in-memory SQLite, with PostgreSQL integration coverage added when database-specific behaviour is introduced;
- static analysis and test commands are available.

## Integration Findings

### Lessons From WPSPX

The legacy WPSPX plugin is a behavioural reference, not an implementation base.
It established the important theatre concepts:

| WPSPX / Spektrix Concept | Cue Domain Language |
| --- | --- |
| Spektrix event / WordPress show | Event |
| Spektrix instance | Performance |
| Instance price list | Performance pricing snapshot |
| Instance status | Availability snapshot or live availability |
| Remote event image | Managed event media |
| Spektrix iframe URL | Booking handoff |

WPSPX retrieved API resources, stored JSON cache files, mapped Spektrix events to
WordPress content, and rendered public pages alongside purchase iframe handoff.
Cue should keep the useful user journey while replacing global configuration,
file-based caching and WordPress-coupled content storage.

WPSPX displayed a performance's lowest raw price as its "From" price. That is not
a reliable public pricing rule: the Spektrix demo client includes a performance
whose default Full Price tickets range from GBP 20 to GBP 40, while Student and
Over 60 prices fall to GBP 15. Cue must not advertise a concession or otherwise
restricted price as the headline price unless the display policy explicitly allows
it.

### Spektrix API Boundary

Spektrix API v3 supports Web/Public, Agent and System Owner modes. Public event and
instance catalogue data is available in Web/Public mode without authentication.
System Owner or Agent calls require signed authentication and are appropriate only
where restricted functionality is required.

Spektrix also recommends cached server-side retrieval for data that changes
infrequently, including events and instances. This aligns with Cue's core rule:
public page rendering must use normalised local data rather than depend on live API
availability.

Initial integration boundary:

- Sync public events and performances server-side from API v3 Web/Public mode.
- Sync current public performance price lists into local, timestamped price records.
- Persist remote identifiers and original payload fragments needed for diagnosis.
- Treat pricing as freshness-sensitive catalogue data: public rendering reads local
  records, while regular refreshes capture price changes including dynamic pricing.
- Treat availability as a separate real-time concern, with optional short-lived
  cached snapshots; a stored price does not claim that a seat at that price remains
  available.
- Hand off bookings to supported Spektrix web purchase journeys initially.
- Do not implement authenticated customer, basket or order operations in the first release slice.

Reference material:

- [Spektrix API v3 Overview](https://integrate.spektrix.com/docs/API3)
- [Spektrix API Authentication](https://integrate.spektrix.com/docs/authentication)
- [Filtering Events and Instances](https://integrate.spektrix.com/docs/apieventfiltering)
- [Spektrix API v3 Demo Endpoint Index](https://system.spektrix.com/apitesting/api/v3/Help)

## Architecture

### Design Rules

- Use generic domain language throughout Cue.
- Keep Spektrix-specific request and response details inside infrastructure code.
- Make locally persisted data the source for public event rendering.
- Keep Filament resource and page classes limited to admin presentation and action dispatch.
- Build and validate the admin content model before committing to public presentation.
- Put orchestration in actions and external access behind contracts.
- Use queued jobs for all external synchronisation work.
- Make imports idempotent, retry-safe and observable.
- Prefer one clear implementation for the first provider over premature abstraction.

### Proposed Module Layout

```text
app/
├── Domains/
│   ├── Events/
│   │   ├── Actions/
│   │   ├── Data/
│   │   ├── Jobs/
│   │   └── Models/
│   └── Ticketing/
│       ├── Contracts/
│       └── Data/
├── Infrastructure/
│   └── Ticketing/
│       └── Spektrix/
├── Filament/
│   ├── Pages/
│   ├── Resources/
│   └── Widgets/
└── Http/
    └── Controllers/
```

Only create folders as their first implemented classes require them. The layout is
a direction, not a requirement to generate empty architecture.

### Core Boundaries

`TicketingProvider`

- Fetches provider event, performance and pricing catalogue data as Cue DTOs.
- Does not persist Eloquent models.
- Does not know about public views or Filament.

`SpektrixTicketingProvider`

- Builds and executes Spektrix API v3 requests.
- Maps provider payloads to generic DTOs.
- Handles transport errors, timeouts and provider diagnostics.

`SyncCatalogueAction`

- Performs idempotent persistence of event and performance data.
- Records sync outcomes and dispatches any follow-up work.
- Operates on generic ticketing DTOs rather than raw Spektrix payloads.

`SyncPerformancePricesAction`

- Fetches current price lists only for relevant future/on-sale performances.
- Stores price entries and display aggregates idempotently with a captured timestamp.
- Computes display values from an explicit eligibility policy, never an unqualified
  raw minimum.

Queued sync jobs

- Initiates catalogue or pricing sync through the relevant action.
- Has explicit timeout, retries, backoff and failed handling.
- Is tagged for Horizon operations visibility.

Public event controllers

- Query published local records and return Blade views.
- Never call Spektrix.

Filament operations tooling

- Presents events, performances, prices, sync health and provider diagnostics.
- Allows controlled editorial overrides without mutating provider-owned source data.
- Dispatches sync jobs and displays recorded outcomes.
- Does not contain integration logic.

## Initial Data Model

The first implementation should introduce only records necessary to render and
operate the event catalogue.

### Events

Minimum responsibilities:

- generic identity and route slug;
- provider identifier and provider type;
- title and descriptions;
- sale and publication state;
- remote image metadata pending media download;
- first and last known performance timestamps;
- separately owned editorial override fields validated through the admin content model.

### Performances

Minimum responsibilities:

- belonging to an event;
- provider identifier;
- start datetime and web sale window where available;
- sale/cancellation status;
- booking handoff identifier;
- provider payload metadata needed for safe resync or diagnostics.

### Performance Prices

Minimum responsibilities:

- belonging to a performance and retaining the provider price identifier;
- ticket type and price band remote identifiers and public names;
- amount stored as currency minor units with an explicit currency;
- provider flags needed for display policy, including band-default status and
  dynamic-pricing eligibility where supplied;
- captured/synchronised timestamp and source payload for diagnosis;
- derived per-performance display values, such as `standard_from_price`, only
  after the rule selecting eligible ticket types is agreed.

Price definitions and seat availability must remain distinct. Price entries are
locally synced catalogue data. A future availability snapshot may say whether
inventory is currently purchasable, but Spektrix remains authoritative at booking
time for both availability and the transaction's final price.

### Sync Runs

Minimum responsibilities:

- sync type and provider;
- queued, running, successful or failed status;
- start/end timestamps and duration;
- imported/updated/failed counts;
- failure message and diagnostic context without secrets.

### Media

Do not build a full media library first. Begin with remote event image fields and
then add queued download/optimisation once event sync and the admin presentation of
source imagery are proven.

### Editorial Ownership

The administration phase must establish which data is owned by the ticketing
provider and which is owned by Cue editors:

| Data | Initial Ownership |
| --- | --- |
| Provider identifiers, on-sale state, instance dates and current price data | Synced and read-only in Filament |
| Synced event title, descriptions and remote images | Visible provider source data |
| Published title, summary, body content and hero image selection | Editorial overrides with source fallback |
| SEO title, meta description, canonical behaviour and redirect history | Editorial |
| Publication and visibility state | Editorial, independent of ticketing sale state |

A ticketing resync may update provider-owned data but must not overwrite editorial
overrides. Public rendering is deferred until this ownership model is reviewable
through Filament against realistic imported data.

## Delivery Roadmap

### Phase 0: Foundation

Goal: establish a dependable development and operational baseline.

Status: largely complete.

Deliverables:

- Laravel, Filament, Livewire, Tailwind and Horizon installed;
- Valkey-backed queues/cache/sessions locally;
- scheduler-driven Horizon metrics;
- Pint, Pest and Larastan verification;
- architecture and delivery blueprint.

Exit criteria:

- `composer run dev` starts supporting processes successfully.
- `composer analyse` and `php artisan test --compact` pass.
- The first implementation slice is agreed and scoped.

### Phase 1: Catalogue Sync Foundation

Goal: store Spektrix public events and performances locally through a robust queued process.

Status: implemented and verified against the public `apitesting` client; operational
admin surfaces remain in a later phase.

Deliverables:

- provider configuration for a single Spektrix client;
- generic ticketing contract and event/performance DTOs;
- Spektrix public catalogue adapter;
- event, performance and sync run migrations/models/factories;
- sync action and Horizon-observable queued job;
- manual Artisan sync entry point or dispatch command;
- provider mapping, persistence and queue tests.

Quality requirements:

- no API call from public rendering;
- explicit HTTP timeouts and retry behaviour;
- idempotent repeated imports;
- unique provider identifiers enforced in persistence;
- failure status captured without exposing credentials;
- queue timeout below Horizon timeout below queue `retry_after`.

Exit criteria:

- a recorded fixture or mocked Spektrix payload imports successfully;
- repeat sync updates records without duplication;
- failures are visible through a sync run record and Horizon;
- automated tests and static analysis pass.

### Phase 1B: Pricing Foundation

Goal: store and display meaningful performance pricing without implying unavailable
inventory or misleading audiences with ineligible minimum prices.

Status: implemented and verified against one public `apitesting` performance.
The recurring schedule is wired with overlap protection and remains disabled by
default until an environment deliberately enables it. Public stale-price
presentation is consumed in Phase 3, while pricing freshness is exposed for
inspection in Phase 2.

Deliverables:

- generic price DTOs and provider contract capability;
- Spektrix `instances/{id}/price-list` mapping and fixture coverage;
- performance price persistence in minor currency units with sync timestamps;
- queued, idempotent price refresh for relevant future/on-sale performances;
- display price policy supporting an initial standard/default "from" price;
- dynamic-pricing refresh cadence and stale-price behaviour recorded in configuration;
- tests proving concession prices are not automatically presented as standard prices.

Quality requirements:

- public pages never request price lists directly from Spektrix;
- amounts are stored without floating-point arithmetic;
- price data has an observable freshness timestamp;
- displayed language distinguishes current display pricing from live availability;
- booking flows treat Spektrix as final price authority.

Exit criteria:

- demo price lists import idempotently and retain bands/ticket types correctly;
- a dynamically eligible performance can be refreshed without catalogue resync;
- headline prices follow the agreed display policy;
- stale or failed price sync behaviour is visible and tested.

### Phase 2: Administration And Editorial Model

Goal: provide a comprehensive Filament workspace for understanding synced provider
content, operating syncs and defining Cue-owned editorial presentation before any
public event UI is committed.

Status: complete. All deliverables implemented and verified against the public
`apitesting` Spektrix client. Realistic imported data has been reviewed in the
admin; Phase 3 is cleared to begin.

Deliverables:

- Filament operations dashboard showing catalogue and pricing sync status, failures,
  stale data and manual refresh actions;
- event management views showing provider source fields separately from editorial
  overrides;
- related performance and pricing inspection, including default headline price,
  concessions, dynamic-pricing state and freshness;
- read-only technical diagnostics for provider identifiers and captured payloads
  where useful for development and support;
- editorial override storage for published title, summaries/body content,
  publication/visibility and SEO metadata;
- queued image downloads and optimisation;
- local storage strategy and image variants;
- editorial image selection/override tooling;
- redirect management for editorial slug changes;
- cache invalidation on publishable changes.

Exit criteria:

- an editor can inspect an imported event with its performances, prices and sync
  freshness without reading the database or raw API;
- synced provider fields and editable Cue fields are unmistakably separated;
- editor changes survive a catalogue/pricing resync;
- editors can alter allowed fields without corrupting provider sync;
- image work is retry-safe and operationally visible;
- public-page content requirements can be agreed from representative admin-managed
  data rather than guessed from provider responses.

### Phase 3: Public Event Experience

Goal: render an accessible, fast theatre website journey from the editorially
managed and locally synced event catalogue.

Precondition: the administration and editorial model has been reviewed against
representative event, performance and pricing data.

Deliverables:

- public event listing route and server-rendered Blade view;
- event detail page with performance selection;
- public rendering using editorial overrides with provider-source fallback;
- clearly labelled locally sourced performance prices with freshness-aware fallback;
- responsive and accessible event presentation;
- booking handoff link/button using persisted provider data;
- basic metadata, canonical URLs and empty/sold-out states;
- feature tests for published visibility and page rendering.

Exit criteria:

- pages render with the ticketing API unavailable;
- public pages avoid remote image dependencies for synced media;
- accessible keyboard and screen-reader paths are checked;
- key pages meet agreed performance targets;
- booking handoff has been tested against an approved Spektrix client/test setup.

### Phase 4: Availability And Operational Hardening

Goal: provide the operational confidence required for launch.

Deliverables:

- refine admin diagnostics and alerts discovered through operational use;
- availability approach validated and implemented where product needs it;
- cache warming and scheduled sync policy;
- diagnostics, logging and failure recovery documentation.

Exit criteria:

- sync failure and stale catalogue scenarios are demonstrably recoverable;
- operations users know how to diagnose and rerun imports;
- real-time calls are limited to justified transactional or availability use cases.

### Phase 5: Production Readiness And Launch

Goal: deploy a secure, observable and maintainable production vertical slice.

Deliverables:

- production database, Redis-compatible service and Horizon worker configuration;
- environment and secrets management;
- backups, error tracking, logging and alerting;
- CI quality gates for tests, formatting and static analysis;
- accessibility, SEO, performance and security review;
- deployment and rollback runbook;
- content migration and launch checklist.

Exit criteria:

- production-like staging has passed the full event-to-booking journey;
- all required monitoring and restore procedures are tested;
- launch owners approve the operational runbook and acceptance checklist.

## Completed Foundations And Next Tranche

Phase 1 completed:

1. Add `config/ticketing.php` and environment keys for one Spektrix public client URL.
2. Create a generic ticketing provider contract and readonly event/performance DTOs.
3. Implement a Spektrix adapter for public event and instance retrieval.
4. Add event, performance and sync run storage with appropriate unique indexes.
5. Add an idempotent queued catalogue sync job and action.
6. Test API payload mapping, repeated sync and failure recording.

Live development verification imported 42 events and 729 performances from the
public `apitesting` client; a second import retained the same record counts.

Phase 1B completed:

7. Add generic price DTOs and extend the ticketing provider contract.
8. Implement Spektrix `instances/{id}/price-list` mapping with fixture coverage.
9. Persist performance prices in minor currency units with sync timestamps.
10. Add a queued, idempotent price refresh job with overlap protection.
11. Implement `StandardPriceDisplayPolicy` to select band-default eligible prices.
12. Record dynamic-pricing configuration and stale-price behaviour in `config/ticketing.php`.

Development verification imported 12 price rows for performance `#1` from
the public `apitesting` client. The source included GBP 15 concession prices; Cue
correctly selected the dynamically eligible default Full Price value of GBP 20
as `display_from_price_minor`.

Phase 2 completed so far:

13. Add `EventEditorial` and `EventRedirect` models with migrations and factories.
14. Implement editable `EventResource` with source and editorial fields clearly separated.
15. Add read-only `PerformanceResource` and `PricesRelationManager` for price inspection.
16. Add `SyncRunResource` with controlled catalogue and pricing dispatch actions.
17. Add `RedirectsRelationManager` for editorial slug change management.
18. Add `CatalogueHealthWidget` and `PricingSyncHealthWidget` to the operations dashboard.
19. Add `local_image_path` to `events` and `DownloadEventImageJob` for queued image ingestion.
20. Process downloaded images with PHP GD (max 1400px wide, 85% JPEG) and store on the public disk.
21. Dispatch image download jobs from `SyncCatalogueAction` after the transaction, only for new or changed image URLs.
22. Show downloaded source image in `EventInfolist` (`ImageEntry`) and `EventsTable` (`ImageColumn`).
23. Add `CreateSlugRedirectAction` to auto-create 301 redirects on editorial slug changes.
24. Wire `EditEvent` lifecycle hooks to dispatch the action and notify on redirect creation.
25. Add `event_path_prefix` to `config/ticketing.php`; redirect paths are configurable.
26. Add publication helper text to `is_published` and `published_at` form fields.
27. Fix static analysis: add `@property` datetime annotations to `SyncRun`; add
    `$event` type assertion in `EditEvent` lifecycle hooks.
28. Review realistic imported data in Filament admin against Phase 2 exit criteria.

Phase 2 first-tranche verification covers editorial edits without mutating synced
event data, read-only performance/price inspection, event redirect management and
manual sync dispatch from Filament.

Phase 2 dashboard verification: `CatalogueHealthWidget` shows event/performance
counts and last catalogue sync state; `PricingSyncHealthWidget` shows priced
performance ratio, stale pricing count against the configured freshness threshold,
and last price sync state. Both widgets poll every 30 seconds and are backed by
11 automated tests.

Phase 2 image ingestion verification: `DownloadEventImageJob` downloads, resizes
and stores Spektrix source images locally. HTTP failures and undecodable payloads
are logged and skipped without failing the job. Dispatch is idempotent — jobs are
only queued for events with a new or changed `image_url`. 7 automated tests cover
download, JPEG output, silent skip on failure, and dispatch conditions.

Phase 2 editorial workflow verification: `CreateSlugRedirectAction` handles three
cases — slug changed, slug set for the first time (differs from provider), slug
cleared back to null. `EditEvent` captures the pre-save slug in `beforeSave()` and
dispatches the action in `afterSave()`, showing an info notification when a redirect
is created. The action uses `updateOrCreate` on `source_path` to prevent duplicate
redirects and respects the configurable `event_path_prefix`. 9 automated tests cover
all redirect scenarios and the custom prefix.

Phase 2 realistic data review findings (against `apitesting` Spektrix client):

Catalogue state: 42 events, 729 performances, all with future dates (2026-05-25
through 2028-05-19), no duplicate slugs. All 728 future on-sale performances have
a `display_from_price_minor` set; display prices range GBP 0.00 to GBP 100.00. 9
distinct ticket type names observed (Full Price, Over 60, Adult, Student, Agent,
AS Agent, VIP Ticket, Free Web Ticket, Complimentary). 14 dynamically priced
performances present.

Image state: 20 events have a remote `image_url` from Spektrix; `local_image_path`
remains null for all because no Horizon worker has been running locally since the
migration was added. Running `composer run dev` and re-syncing will populate these.

Booking handoff: 0 of 729 performances have a `web_id`. The `apitesting` demo
client does not populate `webInstanceId`. Spektrix docs confirm `ChooseSeats.aspx`
accepts either `WebInstanceId` (our `web_id`) or `EventInstanceId` (the integer
prefix of our `external_id`, e.g. `112659AKSS...` → `112659`). Cue will use
`web_id` when set, falling back to `(int) $external_id`. The iframe URL uses a
different base path to the API URL; `SPEKTRIX_IFRAME_BASE_URL` has been added to
config to support custom client domains.

Event title quality: several test events use `->` in titles (e.g. "Aldwych Theatre
-> Release Rules 02"). Slug generation handles this correctly (stripped to hyphens).
These are artefacts of the demo client and not representative of real theatre data.

Phase 2 exit criteria assessment:
- Editor can inspect events, performances, prices and sync freshness without DB access. Pass.
- Provider and editorial fields are unmistakably separated. Pass.
- Editorial changes survive a resync. Pass (verified by test).
- Image work is retry-safe and operationally visible. Pass (job skips on failure, tagged in Horizon).
- Static analysis passes at configured level. Pass (0 errors after review fixes).
- Public-page content requirements can be agreed from admin data. Pass — see open
  questions below regarding `web_id` coverage and zero-price display.

Next coding tranche:

1. Begin Phase 3: public event listing and detail pages.

## Operational Decisions

Accepted now:

| Decision | Reason |
| --- | --- |
|| Blade, Livewire and Tailwind for public UI | Server rendering and progressive enhancement suit performance, SEO and accessibility goals. |
|| Spektrix booking handoff via ChooseSeats.aspx | Cue hands off to the Spektrix iframe using `WebInstanceId` (web_id) where set, falling back to `EventInstanceId` (integer prefix of external_id). Payment processing remains in Spektrix. |
|| Zero-price display label | Operator-configurable via `TICKETING_ZERO_PRICE_DISPLAY` (`free` or `monetary`). Defaults to `free`. Phase 3 templates read this config rather than hard-coding the label. |
| Filament for internal tools only | Keeps public architecture independent from admin framework internals. |
| Valkey through Laravel Redis support for queues/cache/session | Supports Horizon and production-style local operations. |
| Horizon from the first sync job | External integrations require retries, failure visibility and throughput monitoring. |
| Local catalogue persistence | Protects page availability and performance from provider downtime and latency. |
| Spektrix implemented as an adapter | Prevents external provider naming from shaping Cue's domain. |
| Local price-list snapshots with regular refresh | Makes event pages fast while acknowledging dynamic pricing and leaving final transaction pricing to Spektrix. |
| No raw-minimum "cheapest" headline | Avoids misrepresenting concession or restricted prices as generally available ticket prices. |
| Admin content modelling before public pages | Lets editors and developers validate usable content, source ownership and pricing presentation against realistic imported data. |

Deferred decisions:

| Decision | Trigger For Resolving |
| --- | --- |
| Production PostgreSQL hosting target | Before staging infrastructure is provisioned. |
| Authenticated Spektrix mode and secret management | When a feature requires restricted API calls. |
| Availability freshness strategy | Before public messaging claims tickets or price bands are currently available. |
| Final display-price eligibility policy | Before public pages advertise "from" pricing. |
| Pricing refresh SLA for dynamically priced performances | Before priced pages are enabled outside development. |
| Media processing implementation details | During the admin editorial phase, once provider image shapes and editorial needs are inspected. |
| CMS breadth beyond event overrides | After the event-focused admin and public vertical slice is usable. |

## Production Gates

### Functional

- Public event catalogue and detail pages meet approved requirements.
- Booking handoff works for supported event/performance states.
- Editorial overrides behave predictably across resyncs.

### Reliability

- Sync jobs are idempotent and retry-safe.
- Failed imports are observable and recoverable.
- Provider outage does not remove existing published catalogue pages.
- Backups and restoration are tested.

### Security And Privacy

- Provider credentials, if introduced, remain server-side and outside logs.
- Admin access is authorised and audited as necessary.
- Customer or transactional data is not stored until its handling has been designed and reviewed.

### Quality

- Pest tests pass in CI.
- Pint formatting is enforced.
- Larastan passes at the agreed analysis level.
- Accessibility, SEO and performance acceptance checks pass.

### Operations

- Horizon and scheduled sync processes run in production.
- Monitoring covers failed jobs, stale syncs and application exceptions.
- Deployment, rollback and incident recovery procedures are documented.

## Open Product Questions

These questions should be resolved as their answers become necessary. The admin
content-modelling phase exists to answer the editorial and presentation questions
before public templates are built:

1. Which first theatre or Spektrix test client supplies realistic fixture data?
2. Which editorial override, media, SEO, publication and redirect fields must be
   manageable in Filament at launch?
3. Does launch require live seat/availability messaging or only booking handoff?
4. What event taxonomy and filtering is essential for the first public listing?
5. What are the initial accessibility and page performance acceptance targets?
6. Which staging and production hosting target will operate Horizon and persistent services?
7. Should headline prices represent only default/full-price tickets, or may
   explicitly labelled concession prices appear in event cards and detail pages?
8. What maximum age is acceptable for a displayed price on dynamically priced
   performances before the UI suppresses it or marks it as indicative?

Resolved during Phase 2 review:

- Zero-price display: operator sets `TICKETING_ZERO_PRICE_DISPLAY=free` or `monetary`.
- Booking handoff identifier: `web_id` (WebInstanceId) preferred, `(int) external_id` fallback.

## Working Rule

Each phase should end with working software, automated verification and updated
decisions. The immediate success measure is not the size of the architecture; it is
a resilient, locally rendered event journey powered by repeatable Spektrix sync.
