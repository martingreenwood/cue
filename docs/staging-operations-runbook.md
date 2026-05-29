# Production-Like Staging Operations

This runbook defines the Phase 4 staging exercise for Cue's first ticketing
vertical slice. It validates scheduled catalogue and price work, Horizon
visibility, failure recovery and public resilience before production launch.

## Launch Boundary

Cue renders public programme content from PostgreSQL and serves guide-price
information from local synchronised records. It does not make live seat
availability claims before a visitor enters embedded Spektrix booking.

There is no public response cache to warm in this release. Cache preparation
means compiling Laravel and Filament metadata on deploy, keeping Valkey
available for locks, queues and sessions, and verifying public pages from local
data while the provider is unavailable.

## Staging Services

Staging should use the same service shape expected for production:

| Concern | Staging requirement |
| --- | --- |
| Application | `APP_ENV=staging`, debug disabled, HTTPS URL configured |
| Database | PostgreSQL with UTC connection timezone |
| Queues, cache, sessions | Shared Redis-compatible Valkey service |
| Queue monitoring | Horizon running continuously |
| Scheduler | One scheduler runner executing Laravel schedules every minute |
| Media | Persistent public storage for managed event images |

Required staging environment values:

```dotenv
APP_ENV=staging
APP_DEBUG=false
DB_CONNECTION=pgsql
DB_TIMEZONE=UTC
CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
REDIS_QUEUE_RETRY_AFTER=90

TICKETING_CATALOGUE_SYNC_ENABLED=true
TICKETING_CATALOGUE_SYNC_CRON="0 * * * *"
TICKETING_CATALOGUE_ACTIVE_RUN_STALE_AFTER_MINUTES=15
TICKETING_PRICE_SYNC_ENABLED=false
TICKETING_PRICE_SYNC_CRON="*/15 * * * *"
TICKETING_PRICE_STALE_AFTER_MINUTES=60

# Server-side catalogue API requests may remain on system.spektrix.com.
SPEKTRIX_API_BASE_URL=https://system.spektrix.com/clientname/api/v3
# Customer-facing iframe and integrate.js must use the confirmed custom domain together.
SPEKTRIX_CUSTOMER_FACING_BASE_URL=https://tickets.myvenue.com/clientname
SPEKTRIX_CUSTOM_DOMAIN_CONFIRMED=false
```

Keep scheduled price refresh disabled during the first catalogue soak. A
full price refresh of the current public dataset may make hundreds of provider
requests per run. Enable the 15-minute cadence only after the approved provider
client, observed run duration and acceptable API load have been confirmed.

## Spektrix Custom Domain Cutover

Spektrix requires a custom domain for the customer-facing booking journey so its
booking cookies remain first-party. Mixed use of `system.spektrix.com` and a
custom domain across customer-facing booking surfaces can cause empty baskets or
CORS errors, especially under browser tracking prevention.

Cue separates these concerns:

| Configuration | Purpose | Domain rule |
| --- | --- | --- |
| `SPEKTRIX_API_BASE_URL` | Server-side catalogue and price synchronisation | May continue to use `system.spektrix.com`. |
| `SPEKTRIX_CUSTOMER_FACING_BASE_URL` | Public booking iframe, `integrate.js`, customer login API call, login-status and basket-summary components | Must use the Spektrix-confirmed custom domain plus client name for launch. |
| `SPEKTRIX_CUSTOM_DOMAIN_CONFIRMED` | Activation and operational confirmation marker shown in Filament | Set `true` only after Spektrix confirms setup; until then Cue will not activate the new custom-domain base URL. |

The Filament dashboard exposes the customer-facing booking-domain state. A
production or staging launch is not ready while it reports a system domain,
missing/malformed custom URL or pending Spektrix confirmation.
Cue also refuses to activate an unconfirmed custom host placed in the legacy
`SPEKTRIX_IFRAME_BASE_URL` fallback, or a confirmed custom URL without HTTPS and
a Spektrix client-name path.

### Website Admin Destinations

The `apitesting` Spektrix Website Admin configuration has been prepared with the
following Cue-facing destinations. Registration, login, logout and passwordless
login operate through Cue-owned pages making direct browser-to-provider API
calls. Magic-link login is Cue's advertised forgotten-password recovery route.
The Website Admin password-reset destination remains a provider-isolated fallback
surface because Spektrix may direct provider-issued reset emails there. Basket
also renders a provider-isolated Spektrix iframe surface. The remaining routes
must still be completed before this configuration is launch-ready:

| Spektrix setting | Configured Cue destination | Current implementation status |
| --- | --- | --- |
| Checkout Link | `/checkout` | Required next tranche |
| MyAccount Link | `/account` | Implemented Cue-owned account area with separate profile, address, order, payment, security and contact-preference pages |
| Custom Basket Link | `/basket` | Implemented provider-isolated iframe page |
| Password Reset Page Link | `/account/password-reset` | Implemented unadvertised secure set-password fallback iframe page |
| Redeem Gift Page Link | `/redeem` | Required next tranche |
| Basket "Book more tickets" Link | `/events` | Implemented public listing |
| Account Login Link | `/login` | Implemented provider API-driven login and magic-link recovery page; customer registration uses `/account/register` |
| Membership Renewal Link | `/renew` | Required next tranche |
| Website Javascript Options blank page | `/blank` | Required next tranche |

Do not approve Express Checkout or account/basket acceptance testing until each
required destination renders the intended provider-isolated surface and shares
the active customer-facing domain boundary.

Custom domain preparation:

1. Choose a customer-facing subdomain, conventionally `tickets.myvenue.com`.
2. Add a non-proxied `CNAME` from that subdomain to `customers.spektrix.com`.
3. If the apex domain already publishes CAA records, add Spektrix-supported
   authorities required for certificate issuance.
4. Send the Spektrix client name, custom domain and required deadline to Spektrix
   support.
5. Keep Cue using the existing development/test customer-facing base URL until
   Spektrix confirms the custom-domain setup and redirects are complete.

Atomic cutover and verification:

1. Set `SPEKTRIX_CUSTOMER_FACING_BASE_URL=https://tickets.myvenue.com/clientname`
   while leaving `SPEKTRIX_CUSTOM_DOMAIN_CONFIRMED=false`; Filament should show
   that confirmation is pending and Cue will not activate the new domain yet.
2. After Spektrix support confirms setup, set
   `SPEKTRIX_CUSTOM_DOMAIN_CONFIRMED=true` and clear/rebuild cached configuration
   as part of the atomic cutover deployment.
3. Confirm an event booking page loads both `ChooseSeats.aspx` and
   `/website/scripts/integrate.js` from the same custom-domain/client-name root.
4. Confirm public pages initialise `spektrix-login-status` and
   `spektrix-basket-summary` with the same client name and custom-domain host,
   and that basket count follows a seat selected in the booking frame.
5. Confirm the Cue `/login` form sends credentials directly to
   `/api/v3/customer/authenticate` on that same custom domain, then updates the
   login-status component after returning to public pages.
6. Create a test customer through `/account/register`, confirming the API
   request targets the same custom domain and the customer can subsequently log in.
7. Confirm logout sends the active browser session to
   `/api/v3/customer/deauthenticate`, then the login-status component returns to
   its logged-out state.
8. Open `/account` while logged in and confirm Cue loads the current customer
   record from `/api/v3/customer`, including profile, addresses, orders, print-at-home
   documents and stored-card metadata supplied by Spektrix. If using a disposable
   test customer, update profile details and change the password, confirming the
   browser sends `PATCH /api/v3/customer` and
   `POST /api/v3/customer/change-password` to the same active customer-facing
   domain. Visit `/account/contact-preferences`, update selected preferences and
   confirm the browser sends `PUT /api/v3/customer/agreed-statements`.
9. Request and complete a magic-link login through `/login`, confirming the
   provider session is reflected in login status. The requested magic-link
   callback URL must include Spektrix's literal `{token}` placeholder, which Cue
   maps to `/login/magic-link?token={token}`.
10. Separately verify the Website Admin `/account/password-reset` fallback with a
   provider-issued reset email if Spektrix requires password reset support.
   Confirm the email link origin and embedded iframe origin use the same confirmed
   customer-facing domain; mismatched origins report an expired or invalid link.
11. Test a complete add-to-basket journey in Safari and a second modern browser,
   including navigation between performance selection and booking steps.
12. Confirm the Filament Booking custom domain dashboard card reports success.

Do not introduce a partial cutover. Any later customer-facing Spektrix surfaces,
including customer authentication API calls, account iframes, donation web components or
client-side basket API calls, must use the same confirmed custom domain. Multiple
venue domains on one Spektrix system will have separate customer sessions and
baskets, which must be included in product acceptance if Cue later supports them.

## Scheduled Work

| Work | Schedule | Queue behaviour | Operational purpose |
| --- | --- | --- | --- |
| Horizon metrics | Every 5 minutes | Runs on one server | Retains 24 hours of five-minute queue metrics. |
| Catalogue sync | Hourly at minute `0` when enabled | Queues one unique catalogue job, refuses a current active run and marks an active run older than 15 minutes failed before replacing it | Refreshes local events, performances and missing/changed managed images. |
| Price sync | Every 15 minutes when explicitly enabled | Queues a batch for future, on-sale, non-cancelled performances and refuses a second active batch | Refreshes guide-price snapshots and dynamic-price freshness. |

Current timeout chain is suitable for staging:

| Operation | Job timeout | Horizon timeout | Redis `retry_after` |
| --- | ---: | ---: | ---: |
| Catalogue import | 50 seconds | 60 seconds | 90 seconds |
| Performance price row | 30 seconds | 60 seconds | 90 seconds |
| Image download | 30 seconds | 60 seconds | 90 seconds |

## Deployment Preparation

Run these after environment values are configured and database migrations are
applied:

```bash
php artisan optimize
php artisan filament:optimize
php artisan schedule:list
php artisan horizon:status
```

Expected schedule output includes `horizon:snapshot`, `ticketing:sync-catalogue`
and `ticketing:sync-prices`. The configuration flags determine whether each
ticketing task dispatches work when its cron expression becomes due.

## Initial Staging Exercise

1. Start Horizon and the scheduler through the staging process manager.
2. Run `php artisan ticketing:sync-catalogue` once to populate or refresh the local catalogue.
3. Confirm in Filament that the sync run succeeds, event/performance counts are plausible and managed images are present for representative events.
4. Confirm public event listing and detail pages render from local data and the embedded booking handoff loads only after selecting a performance.
5. Confirm the Filament Booking custom domain health card reports the expected
   readiness state; do not mark launch ready until the confirmed custom domain is
   in use.
6. Confirm the public user bar reports Spektrix login status and basket count
   using the same confirmed customer-facing domain as booking.
7. Run `php artisan ticketing:sync-prices` once while watching Horizon throughput and provider response failures.
8. Inspect priced performance coverage, stale-price counts and at least one dynamic-priced performance in Filament.
9. Leave the hourly catalogue schedule enabled for at least 24 hours and verify Horizon metrics and Sync Runs remain healthy.
10. Enable scheduled price refresh only after the one-off run demonstrates acceptable load and duration for the selected provider environment.

## Observation Checklist

During the soak, record:

- last successful catalogue sync time, duration and imported event/performance counts;
- last successful price sync time, queued performance count, failed performance count and price count;
- Horizon queue wait time, job throughput and any failed job tags;
- stale price count against `TICKETING_PRICE_STALE_AFTER_MINUTES`;
- missing local images or image download warnings;
- successful loading of representative published listing, detail, filter and booking-handoff pages.

The expected public failure mode is graceful: if Spektrix catalogue requests fail,
existing published pages remain available from PostgreSQL, guide pricing retains
its freshness wording, and current availability continues to be confirmed in
secure booking only.

## Failure Recovery

| Failure | Diagnosis | Recovery | Proof of recovery |
| --- | --- | --- | --- |
| Catalogue job fails | Check Filament Sync Runs and Horizon job tagged `sync:catalogue` for provider/transport error. | Correct credentials/network/provider issue, then run `php artisan ticketing:sync-catalogue`. Do not remove published local records. | New catalogue run succeeds; public pages remained available during outage. |
| Catalogue command is run while a sync is active | Command reports the existing active run ID. | Wait for the active run or diagnose it in Horizon; do not enqueue another import. | No duplicate queued Sync Run is created. |
| Catalogue run remains active after its worker is lost | Sync Run is queued/running for longer than `TICKETING_CATALOGUE_ACTIVE_RUN_STALE_AFTER_MINUTES`. | Diagnose the stopped worker/provider first, then run `php artisan ticketing:sync-catalogue`; Cue marks the abandoned run failed, ignores any late delivery of its old queued job and queues its replacement. | Old run records the timeout failure and the replacement run succeeds. |
| Price batch contains failed performances | Check Sync Run counts and Horizon jobs tagged `sync:performance-prices`. | Correct upstream/transient issue, then run `php artisan ticketing:sync-prices --performance=<local-id>` for a controlled retry, or rerun the whole price batch after widespread failure. | Affected performance receives a recent `prices_synced_at`; stale count falls. |
| Managed event image is missing | Check Horizon/media job logs and provider image URL. | Correct storage/network/source issue, then run `php artisan ticketing:sync-catalogue`; events without a local image are requeued for download. | Local image is stored and rendered publicly. |
| Horizon workers stop | Run `php artisan horizon:status` and inspect process manager logs. | Restart the managed Horizon process; use `php artisan horizon:terminate` during deployment so managed workers restart with current code. | Queued work drains and fresh metrics resume. |
| Scheduler mutex remains after an interrupted process | Scheduled imports do not dispatch despite no active Sync Run. | Verify no import is running, then run `php artisan schedule:clear-cache`. | Next scheduled tick queues exactly one run. |

## Phase 4 Staging Exit Criteria

- Catalogue syncing completes hourly for a 24-hour soak without overlapping runs.
- Horizon records metrics throughout the soak and failures remain diagnosable by tag and Sync Run.
- A controlled provider-failure drill proves public catalogue pages remain available and catalogue refresh recovers through an idempotent rerun.
- One-off price sync completes with measured throughput; scheduled price cadence is explicitly approved or remains disabled.
- Cache/deployment compilation succeeds; no application response-cache layer is introduced without a measured requirement and an invalidation policy.
- Public pages continue to avoid unqualified live availability claims.
