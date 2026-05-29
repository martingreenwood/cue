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
- Limit authenticated customer behaviour in this release slice to direct
  provider-managed sign-in status and browser-to-Spektrix login; do not add
  Cue-owned customer persistence, custom basket operations or order handling.

### Spektrix Payments API (Custom Checkout)

Spektrix provides a pre-release Payments API and `<spektrix-payments>` web component that
enables a fully custom checkout experience without relying on a payment iframe. The
component is Adyen-powered and Spektrix-managed, so card data and PCI scope remain
entirely outside Cue. This changes the viable scope of the `/basket` and `/checkout`
pages materially.

Checkout flows available:

- **Direct Checkout** (`POST /basket/initiate-direct-payment`): for unauthenticated
  customers, identified by email only after payment is confirmed.
- **Customer Checkout** (`POST /basket/initiate-customer-payment`): for customers who
  are logged in or have been added to the basket.

The initiation endpoint returns a `paymentToken`. Cue passes this to the component:

```html
<spektrix-payments
    custom-domain="{tickets.yourdomain.com}"
    system-name="{clientName}"
    payment-token="{paymentToken}"
    billing-address-id="{addressId}"   <!-- required for Customer Checkout -->
    store-card="true"                  <!-- required for Customer Checkout -->
></spektrix-payments>
```

The component fires `onPaymentCompleted` (with `event.detail.orderId`), `onPaymentRefused`,
`onPaymentNotFound` (basket timed out) and `onError` for orchestrating the post-payment
journey entirely from Cue.

Basket API calls supporting a custom basket page follow the same browser-to-Spektrix
pattern already used for account pages:

- `GET /api/v3/basket` — full basket state including tickets, totals, applied offers,
  delivery options and a `Hash` value that changes on any mutation.
- `PATCH /api/v3/basket` with `{ "promoCode": "MYCODE" }` — applies a promo code inline;
  returns the updated basket with `TotalDiscount` and recalculated totals.
- `DELETE /api/v3/basket/tickets?ticketIds[]=…` — removes specific tickets.
- `PATCH /api/v3/basket/tickets?ticketIds[]=…` — updates ticket type on selected tickets.

Seat selection is unaffected: it continues through `ChooseSeats.aspx` (the booking
handoff from the event detail page), after which Spektrix returns the customer to
Cue's `/basket`.

**Hard gates before any implementation work begins:**

1. The Payments API is **pre-release and not generally available**. Spektrix early
   access must be confirmed in writing before any implementation starts.
2. The Cue hosting domain must be **whitelisted by Spektrix** for the payments
   component. This is a separate step from custom domain confirmation and requires
   contacting Spektrix support with both production and development domain values.
3. The `SPEKTRIX_CUSTOM_DOMAIN_CONFIRMED=true` condition (already a Phase 4 hard
   requirement) must be met before basket or checkout components activate.

Reference material:

- [Spektrix API v3 Overview](https://integrate.spektrix.com/docs/API3)
- [Spektrix API Authentication](https://integrate.spektrix.com/docs/authentication)
- [Filtering Events and Instances](https://integrate.spektrix.com/docs/apieventfiltering)
- [Spektrix Custom Domains](https://integrate.spektrix.com/docs/customdomains)
- [Spektrix Customer Information](https://integrate.spektrix.com/docs/customer)
- [Spektrix Login Status Web Component](https://integrate.spektrix.com/docs/web-components/login-status/spektrix-login-status)
- [Spektrix Basket Summary Web Component](https://integrate.spektrix.com/docs/web-components/basket-summary/spektrix-basket-summary)
- [Spektrix Payments Component](https://integrate.spektrix.com/docs/spektrix-payments/payments-component)
- [Spektrix Payments API: Custom Checkouts](https://integrate.spektrix.com/docs/spektrix-payments/payments-custom-checkouts)
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
- Cue-owned access terms assigned per performance, because accessible provisions
  such as captioning or audio description may apply to selected performances only;
- provider payload metadata needed for safe resync or diagnostics.

### Public Filter Terms

Minimum responsibilities:

- editor-managed reusable terms grouped as `What`, `Offers` or `Access`;
- stable slugs and display order for later server-rendered filter URLs and UI;
- `What` and `Offers` assignment at event level, representing programme
  classification and event-level promotions;
- `Access` assignment at performance level, allowing an event to surface
  accessible dates without claiming every performance has the same provision;
- no dependency on Spektrix naming or real-time availability.

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
inspection in Phase 2. Price snapshot identity was corrected after realistic
full-catalogue data showed that Spektrix reuses price identifiers between
performances: stored rows are now unique by performance, provider and provider
price identifier.

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

Status: in progress. The first public tranche is implemented: a published event
listing and detail experience renders from local Cue records only, applies
editorial overrides with provider-source fallbacks, exposes guide-price freshness
messaging and honours active editorial slug redirects. Selecting an available
performance now renders a provider-isolated Spektrix booking iframe inside the
event page while catalogue content continues to render wholly from local records.
The listing now provides server-rendered search and date-window filtering without
querying Spektrix on the public request path. It also renders editor-managed
`What`, `Offers` and `Access` controls from local assignments: multiple terms
within a group broaden results, while selections across groups narrow them
together. Cue now uses an explicit public
availability-language policy: programme dates and synchronised guide prices may
be displayed locally, while current ticket availability and final prices are
confirmed only during secure Spektrix booking.
Venues can adjust this Cue-owned language from a dedicated Filament Content
Strings editor under Settings; defaults remain in the CMS domain so public pages
work before any settings have been saved.

Deliverables:

- public event listing route and server-rendered Blade view;
- event detail page with performance selection;
- public rendering using editorial overrides with provider-source fallback;
- clearly labelled locally sourced performance prices with freshness-aware fallback;
- responsive and accessible event presentation;
- embedded booking handoff using persisted provider data;
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

Availability decision for launch: Cue will not introduce a short-lived availability
snapshot in the launch vertical slice. Public pages may show locally synchronised
programme dates, performance access provisions and guide prices, but they must not
claim that seats are currently available. Visitors confirm current availability and
final pricing inside the embedded Spektrix booking journey. This avoids an extra
freshness-sensitive data path whose value is limited while booking is already
embedded on the detail page.

Spektrix exposes instance status endpoints that can support a future availability
adapter if a venue requires sold-out indicators, low-availability messaging or
availability-driven discovery before a visitor enters booking. Any such addition
must be provider-isolated, short-lived, timestamped and presented as indicative
until booking confirms inventory.

Operational definition: the production-like staging procedure is specified in
[`docs/staging-operations-runbook.md`](staging-operations-runbook.md). Cue now
supports a disabled-by-default hourly catalogue schedule, prevents overlapping
active catalogue runs from producing duplicate operational records, replaces
abandoned catalogue runs after a configurable operational timeout, provides a
bounded `staging` Horizon supervisor and retains 24 hours of five-minute Horizon
metrics. Because launch pages read local records directly and there is no public
response-cache layer, Phase 4 uses framework/Filament deploy optimisation and page
smoke checks rather than introducing content cache warming.

Customer-facing Spektrix domain readiness is also a Phase 4 launch concern.
Spektrix requires its customer-facing booking surfaces to use a confirmed custom
domain so basket cookies remain first-party. Cue keeps server-side catalogue API
requests independent, but the embedded booking iframe and `integrate.js` now share
an explicit customer-facing base URL. Filament reports whether that booking domain
is absent, still using the Spektrix system domain, malformed, awaiting support
confirmation or ready for launch. DNS, Spektrix-support confirmation and atomic
cutover checks are defined in the staging operations runbook.

Cue now also surfaces Spektrix login status and basket item count in a compact
public utility bar using the official Web Components. The logged-out account
action links to a Cue-owned `/login` page which supports password authentication
and presents passwordless magic-link login as the forgotten-password recovery
route against Spektrix Web User endpoints. Magic link requests supply
`/login/magic-link?token={token}` so Spektrix can replace its required
placeholder in the emailed callback URL. Cue-owned
`/account/register` now creates core customer accounts directly through the
provider Web User endpoint with first name, last name, email and password;
marketing preferences and richer customer profile fields remain deferred until
statement/tag requirements are modelled. Logged-in
visitors may log out through `customer/deauthenticate`. The
configured `/account/password-reset` destination remains only as an
unadvertised Website Admin fallback and embeds Spektrix
`Secure/SetPassword.aspx`, because the emailed reset journey retains secure token
state on the customer-facing provider domain rather than exposing it to Cue.
Cue does not handle reset credentials. The basket action links to a Cue-hosted
`/basket` page embedding the provider surface. Components, API requests and the embedded
basket derive their active domain from the same provider configuration used by
booking, so an unconfirmed custom domain cannot create a second customer-session
boundary. Cue-owned status and basket labels remain editable through Content Strings.
A Cue-owned account area now provides separate pages for profile, addresses,
orders, payments, security and contact preferences. Each page hydrates the
signed-in customer in the browser from `GET /api/v3/customer`, which Spektrix
documents as returning the current customer's profile, addresses, orders,
print-at-home documents, subscriptions, stored cards and credit balance. Profile
updates and password changes are submitted browser-to-provider via
`PATCH /api/v3/customer` and `POST /api/v3/customer/change-password`. Contact
preferences are loaded from Spektrix statement data and changed through
current-customer agreed-statement endpoints.

Account contact preferences should present the provider's available data
protection statements at `/account/contact-preferences`. The page should load the
available contact-preference catalogue with `GET /api/v3/statements`, then load
the signed-in customer's current choices with
`GET /api/v3/customer/agreed-statements`. Render each `DataProtectionStatement`
with `id`, `text` and `agreed`, using the customer's agreed list as the current
state while keeping the provider statement text authoritative. Selecting a new
preference should call `POST /api/v3/customer/agreed-statements` with a
collection of `{ id }` records for the statements being added. Clearing an
individual preference should call
`DELETE /api/v3/customer/agreed-statements/{statementId}`. A deliberate "save all"
implementation may use `PUT /api/v3/customer/agreed-statements` to replace the
whole preference set when the submitted UI state is known to be complete; avoid it
for per-toggle changes. Bulk `DELETE /api/v3/customer/agreed-statements` should
remain reserved for a future multi-select removal workflow. Cue must not cache
contact preferences locally beyond the active browser session, because Spektrix is
the customer-consent system of record.

Account address management should now be delivered as the next account-area slice
at `/account/addresses`. Spektrix exposes current-customer address operations in
Web User mode, so Cue should keep the same browser-to-provider boundary used by
profile and contact-preference forms and must not persist address data locally.
The page should load the canonical address collection with
`GET /api/v3/customer/addresses`, render each returned `CustomerAddress` with
`id`, `isBilling`, `isDelivery`, `country`, `administrativeDivision`, `name`,
`line1` through `line5`, `town` and `postcode`, and preserve graceful empty,
loading and signed-out states. Add-address submission should use
`POST /api/v3/customer/addresses` with a `NewCustomerAddress` payload containing
`isDelivery`, `isBilling`, `country`, `administrativeDivision`, `name`, address
lines, `postcode` and `town`, then refresh the list from Spektrix using the
created `CustomerAddress` response or a follow-up list request. Editing an
existing address should target `PATCH /api/v3/customer/addresses/{addressId}`
with an `AddressPatch` payload for mutable address fields. Cue should reserve
`PUT /api/v3/customer/addresses` for a deliberate future "replace all addresses"
workflow rather than normal form saves, because that endpoint replaces the entire
current collection. Deletion should call
`DELETE /api/v3/customer/addresses/{addressId}` for a single address after
confirmation; bulk `DELETE /api/v3/customer/addresses` can remain out of scope
until the UI introduces multi-select address management.

Address form validation should mirror the provider contract without overfitting
to UK-only assumptions: keep `name`, `line1`, `town` and `country` prominent,
show `postcode` as required only when the selected country metadata indicates it,
and keep `administrativeDivision` optional unless the selected country requires
it. Country choices should use Spektrix `GET /api/v3/countries` rather than
hard-coded lists, because the returned `Country` records include `isoCode`,
`name`, `postcodeRequired`, `displayPriority` and any `administrativeDivisions`.
`GET /api/v3/countries/{isoCode}` can refresh or validate a selected country if a
form is reopened from stale browser state. The address form should also offer a
progressive postcode lookup: call
`GET /api/v3/postcode-lookup?postcode={postcode}` to list matching lookup results
with descriptions and lookup ids, then call
`GET /api/v3/postcode-lookup/{postcodeLookupId}` to retrieve the full selected
address and populate the editable fields before submission to the
customer-address endpoint. Billing and delivery flags should be explicit
checkboxes, with copy that makes it clear Spektrix remains the source of truth
for how those addresses are used in checkout.

Account order history should be the next account read-only slice after addresses.
`/account/orders` should list the signed-in customer's order history using
`GET /api/v3/customer/orders`, rendering provider totals, transaction dates,
delivery summaries and order identifiers without storing them locally. Order
detail expansion should fetch `GET /api/v3/orders/{id}` for the selected order and
render the returned `Order` details, including tickets, payments, refunds,
charges, deliveries, memberships, gift vouchers, donations, merchandise and any
attached `printAtHomeDocuments` where present. Cue must treat order details as
customer-session data, not public catalogue data, and should avoid exposing raw
custom `Attribute_` fields unless a venue-specific display rule is agreed. The
page should also surface available e-tickets from
`GET /api/v3/customer/print-at-home-documents`, matching them to orders where the
provider payload allows it and otherwise presenting them in a separate "E-tickets"
section with clear event/item labels. Opening or downloading a specific e-ticket
should call `GET /api/v3/print-at-home-documents/{id}` for that current-user
document rather than constructing document URLs locally.

Account payment methods should be delivered as a provider-backed management page
at `/account/payments`. The page should load stored cards with
`GET /api/v3/customer/stored-cards?includePending={includePending}`, defaulting
`includePending` to `false` for normal account display unless the UI deliberately
offers a pending-card state. Render only provider-safe card metadata from the
returned `StoredCard` records: `id`, `maskedNumber`, `cardHolderName`,
`expiryDate`, `type`, `isDefault`, `isPending` and any billing `address` summary.
Card removal should call `DELETE /api/v3/customer/stored-cards/{cardId}` after a
confirmation step, then refresh the stored-card list from the response or by
reloading the collection. Cue must never collect, display or persist full card
numbers, CVV values or payment credentials; adding new cards remains a
Spektrix-owned checkout/account-card journey unless a separate secure provider
flow is agreed.

Runtime activation also rejects unconfirmed custom hosts supplied through the
legacy iframe fallback and refuses malformed custom-domain URLs, leaving the
Filament readiness warning visible while retaining a configured system-domain
development fallback where present.

The Spektrix `apitesting` Website Admin configuration has now been prepared for
the Cue support journey with return destinations for checkout, account, basket,
password reset, gift redemption, login, membership renewal, programme browsing
and the blank iframe utility page. Cue now provides the full configured route
set: `/login`, `/account`, `/account/register`, `/account/password-reset`,
`/basket`, `/events`, `/checkout`, `/redeem`, `/renew` and `/blank`.
Checkout, basket, password reset, gift redemption and membership renewal use
provider-isolated Spektrix iframe destinations from the same active
customer-facing booking domain as `integrate.js`; `/blank` is intentionally an
empty utility page for Spektrix website JavaScript options.

Deliverables:

- refine admin diagnostics and alerts discovered through operational use;
- document and enforce the launch availability-language policy with secure-booking
  confirmation as the availability authority;
- validate Spektrix custom-domain readiness for customer-facing iframe/script
  integrations and prevent unreviewed system-domain launch configuration;
- expose customer login status and basket count through provider-isolated
  Web Components on the active customer-facing session domain;
- deliver `/account/contact-preferences` with browser-to-Spektrix statement
  catalogue, current agreed-statement state and add/remove preference mutations;
- deliver `/account/addresses` with browser-to-Spektrix list, add, edit and
  single-delete address management using current-customer address endpoints,
  country metadata and UK postcode lookup where available;
- deliver `/account/orders` with browser-to-Spektrix order history, order-detail
  expansion and customer e-ticket display;
- deliver `/account/payments` with browser-to-Spektrix stored-card listing and
  card removal, without handling raw card credentials in Cue;
- cache warming and scheduled sync policy;
- diagnostics, logging and failure recovery documentation.

Exit criteria:

- sync failure and stale catalogue scenarios are demonstrably recoverable;
- operations users know how to diagnose and rerun imports;
- public pages make no unqualified current-availability claims before Spektrix
  booking confirmation;
- customer-facing Spektrix iframe and integration script requests use one confirmed
  custom-domain/client-name root before launch;
- public login-status and basket-summary components resolve through that same
  active customer-domain/client-name boundary;
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
29. Correct price snapshot identity to `performance_id`, `provider` and
    `external_id`, preventing shared Spektrix price IDs from moving price rows
    between performances during a full refresh.

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

Image state: 20 events expose provider imagery and have successfully downloaded
local managed source images through the queued image ingestion job.

Pricing integrity: a full corrective refresh after the snapshot-identity migration
synced 724 currently eligible performances and 5,316 price entries. All 724
performances displaying a headline price now retain corresponding local price rows.

Booking handoff: 0 of 729 performances have a `web_id`. The `apitesting` demo
client does not populate `webInstanceId`. Spektrix docs confirm `ChooseSeats.aspx`
accepts either `WebInstanceId` (our `web_id`) or `EventInstanceId` (the integer
prefix of our `external_id`, e.g. `112659AKSS...` → `112659`). Cue will use
`web_id` when set, falling back to `(int) $external_id`. The iframe URL uses a
different base path to the API URL; `SPEKTRIX_IFRAME_BASE_URL` has been added as
the client-root URL (for example, `https://system.spektrix.com/apitesting`) so
the adapter can append Spektrix's `/website/ChooseSeats.aspx` path. Embedded
booking URLs append `resize=true` as their final query parameter and load the
Spektrix integration resize script only once a performance has been selected.
Live iframe verification identified and corrected a PostgreSQL session-timezone
issue: Spektrix supplies demo performance `EventInstanceId=138342` as
`startUtc=2026-05-29T18:00:00` and local `start=2026-05-29T19:00:00`. Cue now
sets PostgreSQL connections to UTC for timestamp persistence and renders public
performance times in configurable `TICKETING_DISPLAY_TIMEZONE`
(`Europe/London` for the current UK client).

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

Phase 3 completed so far:

30. Add public `events.index` and `events.show` routes with a lean controller and
    a domain catalogue service that exposes only published editorial events.
31. Add readonly presentation data for editorial/provider fallback, local image
    paths, price display and stale-price messaging.
32. Render responsive Blade listing and detail views with metadata, canonical URLs,
    local image usage, empty states and visible indicative-price language.
33. Honour active editorial slug redirects on the public event route.
34. Cover published/draft/scheduled visibility, fallback content, redirects and
    API-independent local pricing rendering with feature tests.
35. Add generic booking handoff data at the ticketing-provider boundary and
    implement the Spektrix `ChooseSeats.aspx` adapter without live catalogue calls.
36. Render a tested embedded Spektrix booking journey for selected saleable
    performances, preferring `WebInstanceId` and safely falling back to numeric
    `EventInstanceId`.
37. Add validated, URL-driven public event-list search and filters against local
    published catalogue records, respecting editorial overrides and fallback data.
38. Validate the public listing, event detail and embedded handoff layout at desktop,
    tablet and mobile sizes; address Cue-owned findings with named poster links,
    larger navigation targets, optimised listing image loading and progressive
    disclosure for long performance runs. Spektrix iframe internals remain
    provider-owned and outside Cue remediation.
39. Adopt public availability-freshness language: listing and detail pages expose
    guide prices rather than implied live prices; performance calls to action
    invite visitors to check availability in secure booking; stored sale state is
    not phrased as current seat availability.
40. Add CMS-owned public copy settings with a thin Filament Content Strings editor
    under Settings so venue teams can tune availability and booking-handoff
    language without changing public templates or provider iframe content.
41. Define editor-managed public filter terms: assign `What` and `Offers` terms
    to events and `Access` terms to individual performances through Filament,
    ready for a later public filtering implementation.
42. Implement server-rendered public `What`, `Offers` and `Access` filtering:
    validate term slugs by group, filter local catalogue records without API
    requests and show selected editorial terms in accessible responsive controls.
43. Add server-rendered performance filtering on event detail pages: filter
    upcoming dates by exact date, quick date window and performance-specific
    access provision, while keeping booking confirmation in Spektrix.
44. Decide Phase 4 launch availability policy: do not implement a short-lived
    availability snapshot for launch; retain secure Spektrix booking as the
    authoritative confirmation of live inventory and final price.
45. Define Phase 4 staging operations: add configurable hourly catalogue
    scheduling, prevent overlapping or abandoned catalogue run records, add
    bounded Horizon staging capacity and day-long metrics retention, and document
    cache, scheduled-sync and failure-recovery checks.
46. Add Spektrix custom-domain readiness operations: separate customer-facing
    booking configuration from server-side API configuration, use one base URL for
    iframe and `integrate.js` only after confirmation, surface readiness in
    Filament and document the DNS/support/atomic-cutover checks required before launch.
47. Add a public customer-session utility bar with provider-isolated Spektrix
    login-status and basket-summary Web Components, sharing the active booking
    domain boundary and editor-managed Content Strings labels.
48. Harden customer-domain activation after review: reject unconfirmed custom
    hosts supplied through the legacy iframe fallback and refuse malformed custom
    URLs before either booking or customer-session components can use them.
49. Correct mobile customer utility-bar flow by overriding block display on only
    Spektrix-controlled visible state containers while preserving hidden states.
50. Link logged-out account and basket utility actions to Cue-hosted `/login` and
    `/basket` pages; login uses Spektrix Web User API authentication directly in
    the browser while basket uses a provider-isolated embedded journey.
51. Extend the public account entry journey with browser-to-Spektrix logout,
    password reset completion and passwordless magic-link authentication,
    retaining one active customer-facing domain and keeping credentials outside Cue.
52. Add the first Cue-owned My Account area at `/account`, hydrating the
    current customer through Spektrix `GET /customer` and surfacing profile,
    address, order, payment, security and contact-preference pages without
    storing customer data in Cue.
53. Add browser-to-Spektrix account profile and password forms using the current
    customer `PATCH /customer` and `POST /customer/change-password` endpoints.
54. Add browser-to-Spektrix contact preference management using
    `GET /statements`, `GET /customer/agreed-statements`,
    `POST /customer/agreed-statements`, `PUT /customer/agreed-statements` for
    deliberate replace-all saves and `DELETE /customer/agreed-statements/{statementId}`.
55. Seed and review representative public filter data: add an idempotent
    Filament-visible vocabulary for `What`, `Offers` and `Access`, then assign
    realistic event-level programme/offer terms and performance-level access
    provisions to existing local catalogue records for review.

Next coding tranche:

1. Implement `/account/contact-preferences` against Spektrix `GET /statements`,
   `GET /customer/agreed-statements`, `POST /customer/agreed-statements` and
   `DELETE /customer/agreed-statements/{statementId}`, reserving
   `PUT /customer/agreed-statements` for a complete save-all flow and bulk
   `DELETE /customer/agreed-statements` for future multi-select removals.
2. Add browser-facing tests for contact-preference rendering, add, remove,
   replace-all save behaviour if implemented, provider errors and signed-out
   recovery using mocked provider responses.
3. Implement `/account/addresses` against Spektrix
   `GET /customer/addresses`, `POST /customer/addresses`,
   `PATCH /customer/addresses/{addressId}` and
   `DELETE /customer/addresses/{addressId}`, with no local customer-address
   persistence and with list refresh after successful mutations. Use
   `GET /countries`, `GET /countries/{isoCode}`,
   `GET /postcode-lookup?postcode={postcode}` and
   `GET /postcode-lookup/{postcodeLookupId}` for country-aware validation and
   postcode lookup.
4. Add browser-facing tests for address rendering, add, edit, validation,
   single-delete confirmation, Spektrix error handling and signed-out recovery
   using mocked provider responses.
5. Implement `/account/orders` against Spektrix `GET /customer/orders`,
   `GET /orders/{id}`, `GET /customer/print-at-home-documents` and
   `GET /print-at-home-documents/{id}`, with list, detail and e-ticket states
   rendered from live customer-session responses only.
6. Add browser-facing tests for order history, order-detail expansion, e-ticket
   display, empty states, provider errors and signed-out recovery using mocked
   provider responses.
7. Implement `/account/payments` against Spektrix
   `GET /customer/stored-cards?includePending={includePending}` and
   `DELETE /customer/stored-cards/{cardId}`, rendering masked stored-card
   metadata only and refreshing after removals.
8. Add browser-facing tests for stored-card display, pending-card inclusion,
   delete confirmation, provider errors and signed-out recovery using mocked
   provider responses.
9. Execute the production-like staging soak and failure drill in
   `docs/staging-operations-runbook.md`.
10. Contact Spektrix to confirm Payments API early access and submit Cue hosting
    domain(s) for whitelisting. Both must be confirmed in writing before the
    custom basket and checkout implementation begins. While awaiting confirmation,
    `/basket` and `/checkout` remain the current iframe journeys; no code changes
    are needed until access is granted.

## Operational Decisions

Accepted now:

| Decision | Reason |
| --- | --- |
| Blade, Livewire and Tailwind for public UI | Server rendering and progressive enhancement suit performance, SEO and accessibility goals. |
| Spektrix embedded booking handoff via ChooseSeats.aspx | Cue embeds the Spektrix iframe after performance selection using `WebInstanceId` (`web_id`) where set, falling back to `EventInstanceId` (integer prefix of `external_id`). Payment processing remains in Spektrix. |
| Zero-price display label | Operator-configurable via `TICKETING_ZERO_PRICE_DISPLAY` (`free` or `monetary`). Defaults to `free`. Phase 3 templates read this config rather than hard-coding the label. |
| Timezone ownership | PostgreSQL application connections persist provider timestamps in UTC via `DB_TIMEZONE=UTC`; public ticketing pages render them in `TICKETING_DISPLAY_TIMEZONE`, currently `Europe/London`. |
| Filament for internal tools only | Keeps public architecture independent from admin framework internals. |
| Valkey through Laravel Redis support for queues/cache/session | Supports Horizon and production-style local operations. |
| Horizon from the first sync job | External integrations require retries, failure visibility and throughput monitoring. |
| Local catalogue persistence | Protects page availability and performance from provider downtime and latency. |
| Spektrix implemented as an adapter | Prevents external provider naming from shaping Cue's domain. |
| Local price-list snapshots with regular refresh | Makes event pages fast while acknowledging dynamic pricing and leaving final transaction pricing to Spektrix. |
| No raw-minimum "cheapest" headline | Avoids misrepresenting concession or restricted prices as generally available ticket prices. |
| Public availability freshness language | Cue labels locally synchronised values as guide prices and does not claim live availability; current ticket availability and final pricing are confirmed during secure Spektrix booking. Cue-owned wording is editable in Filament through Settings > Content Strings, while provider iframe content remains external. |
| No public availability snapshot at launch | The embedded Spektrix booking journey already confirms live seats and final pricing. Avoiding a second freshness-sensitive public data path keeps launch resilient and truthful; provider-isolated snapshots can be added only when a concrete availability-led UX requirement exists. |
| Spektrix customer-facing custom domain required for launch | Booking iframes, `integrate.js` and customer-session components activate a valid HTTPS custom-domain/client path only when `SPEKTRIX_CUSTOM_DOMAIN_CONFIRMED=true` after Spektrix confirmation, including when legacy fallback configuration is present. This protects first-party booking sessions and avoids premature or partial-domain basket/CORS failures. Server-side synchronisation may continue to use `SPEKTRIX_API_BASE_URL`. |
| Customer-session utility bar scope | Public pages may show Spektrix-managed login state and basket count using the official Web Components on the active booking domain. Cue-owned account entry supports customer registration, password login, logout and magic-link recovery through direct browser-to-Spektrix Web User calls on that domain. The provider password-reset iframe is retained only as an unadvertised Website Admin fallback. Credentials are transmitted browser-to-provider only; Cue does not persist customer, basket or order data. |
| Account contact preference scope | `/account/contact-preferences` should use `GET /statements` for available provider statements, `GET /customer/agreed-statements` for current customer consent state, `POST /customer/agreed-statements` for newly selected statements and `DELETE /customer/agreed-statements/{statementId}` for individual removals. `PUT /customer/agreed-statements` is acceptable only for a deliberate complete save-all flow; bulk `DELETE /customer/agreed-statements` remains out of scope until multi-select removal exists. Cue does not persist consent state locally. |
| Account address management scope | `/account/addresses` should use Spektrix current-customer address endpoints directly in the browser: `GET /customer/addresses` for rendering, `POST /customer/addresses` for adding, `PATCH /customer/addresses/{addressId}` for normal edits and `DELETE /customer/addresses/{addressId}` for single-address removal. Country metadata comes from `GET /countries` and postcode lookup uses `GET /postcode-lookup?postcode={postcode}` followed by `GET /postcode-lookup/{postcodeLookupId}`. Cue avoids local address persistence and does not use collection-wide `PUT` or bulk `DELETE` until a deliberate replace-all or multi-select workflow exists. |
| Account order history scope | `/account/orders` should use `GET /customer/orders` for the signed-in customer's history, `GET /orders/{id}` for selected order details, `GET /customer/print-at-home-documents` for e-ticket listing and `GET /print-at-home-documents/{id}` for a specific current-user e-ticket. Cue renders order and ticketing documents as live customer-session data only, without local order persistence or venue-specific custom attribute display unless explicitly modelled. |
| Account payment methods scope | `/account/payments` should use `GET /customer/stored-cards?includePending={includePending}` for masked stored-card display and `DELETE /customer/stored-cards/{cardId}` for removals. Cue never handles raw card credentials; adding cards stays in a Spektrix-owned secure journey unless a separate provider flow is agreed. |
| Spektrix Website Admin support configuration | The `apitesting` client is configured with Cue return URLs and Express Checkout support settings. Cue now implements the configured support route set: `/login`, `/account`, `/account/register`, `/account/password-reset`, `/basket`, `/events`, `/checkout`, `/redeem`, `/renew` and `/blank`. Magic link is the advertised recovery route, while password-reset completion must load from the same confirmed customer-facing Spektrix origin as any provider-issued reset link. Password reset, gift redemption and membership renewal remain provider-isolated iframe journeys; `/blank` is intentionally empty for Spektrix website JavaScript options. `/basket` and `/checkout` are targeted for migration to the Payments API custom checkout approach (see below). |
| Basket and checkout page scope | `/basket` will be a Cue-owned page that reads basket state from `GET /api/v3/basket`, renders tickets and totals natively, allows inline promo code application via `PATCH /api/v3/basket` and ticket removal via `DELETE /api/v3/basket/tickets`, then links to `/checkout`. `/checkout` will render the `<spektrix-payments>` web component after calling `POST /basket/initiate-direct-payment` (unauthenticated) or `POST /basket/initiate-customer-payment` (logged in) to obtain a payment token. On `onPaymentCompleted`, Cue redirects to a confirmation page using the returned `orderId`. Seat selection continues through `ChooseSeats.aspx`; the payment component styling inherits Adyen BEM classes and can be tuned through the existing Spektrix CSS file or Cue's own stylesheets. Implementation is gated on Spektrix Payments API early access confirmation and domain whitelisting — until those are confirmed, `/basket` and `/checkout` remain the current provider iframe journeys. |
| Public filter taxonomy ownership | Editors define reusable `What`, `Offers` and `Access` terms in Filament. `What` and `Offers` attach to events; `Access` attaches to performances so accessibility claims remain date-specific. |
| Admin content modelling before public pages | Lets editors and developers validate usable content, source ownership and pricing presentation against realistic imported data. |

Deferred decisions:

| Decision | Trigger For Resolving |
| --- | --- |
| Production PostgreSQL hosting target | Before staging infrastructure is provisioned. |
| Authenticated Spektrix mode and secret management | When a feature requires restricted API calls. |
| Live availability snapshot implementation | Post-launch only if a venue requires public sold-out/low-availability messaging or availability-driven discovery before secure booking; use provider-isolated, timestamped, short-lived snapshots. |
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
3. What are the initial accessibility and page performance acceptance targets?
4. Which staging and production hosting target will operate Horizon and persistent services?
5. Should headline prices represent only default/full-price tickets, or may
   explicitly labelled concession prices appear in event cards and detail pages?
6. What maximum age is acceptable for a displayed price on dynamically priced
   performances before the UI suppresses it or marks it as indicative?

Resolved during Phase 2 review:

- Zero-price display: operator sets `TICKETING_ZERO_PRICE_DISPLAY=free` or `monetary`.
- Booking handoff identifier: `web_id` (WebInstanceId) preferred, `(int) external_id` fallback.
- Initial public filter taxonomy: `What` and `Offers` are event terms; `Access`
  is assigned to individual performances to keep accessible-show claims precise.
- Launch availability policy: Cue presents synchronised guide information and
  defers current seat availability and final price confirmation to embedded
  Spektrix booking; no short-lived public availability snapshot is required for
  the launch vertical slice.

## Working Rule

Each phase should end with working software, automated verification and updated
decisions. The immediate success measure is not the size of the architecture; it is
a resilient, locally rendered event journey powered by repeatable Spektrix sync.
