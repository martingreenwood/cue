# Page Builder Proposal: Marketing, Fund, and Membership Pages

## Goal

Introduce a reusable, editor-friendly page builder so non-developers can manage:

- marketing landing pages;
- donation fund pages;
- membership pages;

while preserving Cue's current architecture: provider data synced locally, public pages rendered server-side, and Filament used for admin/editor tooling.

## Current Baseline (as of 2026-06-01)

- Public routes already exist for journey pages including `/donate` and `/memberships`.
- `DonationFund` and `Membership` content is already synced and stored locally.
- Filament already manages copy via a `Content Strings` page.
- Current donate/membership templates are hard-coded Blade layouts.

This means we should layer a page builder on top of existing data sources rather than replacing the sync pipeline.

## Documentation Research Summary

### Filament v5

From Filament v5.1.1 docs:

- `Builder` is designed for web page content with multiple block types in arbitrary order.
- `Repeater` is better for repeating a single schema, while `Builder` is better for mixed content blocks.
- Builder data is intended to be stored as JSON, with model cast support.

### Laravel 13

From Laravel 13 docs:

- JSON content should be cast on the model (`array` or `AsCollection`).
- Slug-based page routing can be implemented via route key binding (`{page:slug}` or `getRouteKeyName()`).

### Optional plugin path: Filament Fabricator

Filament Fabricator adds a pre-built page builder skeleton (page model/resource/layout/block registration). It can speed up delivery but adds package and upgrade surface area.

## Recommendation

Use a **native Filament Builder implementation first** (no new package initially).

Why:

- fits current architecture and conventions;
- avoids dependency risk while requirements are still evolving;
- lets us design blocks around real Cue journeys (donate, memberships, marketing CTA) instead of generic CMS assumptions;
- can still migrate to Fabricator later if we outgrow the native build.

## Proposed Architecture

## 1) New CMS Page model

Create a dedicated CMS page entity (e.g. `App\Domains\CMS\Models\Page`) with fields such as:

- `title` (string)
- `slug` (string, unique)
- `page_type` (enum/string: `marketing`, `fund`, `membership`)
- `status` (draft/published)
- `meta_title` (nullable)
- `meta_description` (nullable)
- `hero_image_path` (nullable, optional)
- `content_blocks` (JSON)
- `settings` (JSON, optional per-page flags)
- `published_at` (nullable datetime)

`content_blocks` is where Filament `Builder` stores block definitions.

## 2) Block registry (code-owned)

Define a small, explicit block set in code first:

- `hero`
- `rich_text`
- `cta`
- `feature_grid`
- `quote`
- `faq`
- `fund_selector` (donation-specific dynamic block)
- `membership_selector` (membership-specific dynamic block)

Important: `fund_selector` and `membership_selector` blocks should render from synced models (`DonationFund`, `Membership`) and not allow editors to alter provider-owned pricing/details in page JSON.

## 3) Filament editor surface

Add a Filament `Resource` for pages with:

- table filters by `page_type` and `status`;
- form with base metadata + `Builder::make('content_blocks')`;
- block-level validation and sensible defaults;
- preview link to public route;
- publish/unpublish action flow.

## 4) Rendering layer

Create:

- `PageRenderer` service (maps blocks to Blade partials);
- Blade components/partials under `resources/views/pages/blocks/*`;
- a generic public controller route for marketing pages by slug.

For `/donate` and `/memberships`, keep existing routes but render through the page renderer when a published page is configured for each journey; otherwise fall back to current templates.

## 5) Safety and governance

- Block allowlist by page type (e.g. only fund pages can use `fund_selector`).
- Strict server-side validation of block payload structure.
- Keep provider IDs out of editable copy blocks unless explicitly needed.
- Maintain an audit trail (`updated_by`, publish timestamps) if editorial governance requires it.

## Rollout Plan

## Phase 1: Foundation

- Add `pages` table + model + casts.
- Build Filament Page Resource with minimal blocks (`hero`, `rich_text`, `cta`).
- Add public `/pages/{slug}` rendering for marketing pages.

## Phase 2: Journey integration

- Add `fund_selector` and `membership_selector` block types.
- Wire `/donate` and `/memberships` to resolve a configured published page.
- Keep fallback to existing templates until parity is proven.

## Phase 3: Editorial maturity

- Add preview/draft workflow polish.
- Add version snapshotting (optional) before publish.
- Expand blocks only when an actual content need appears.

## Testing Strategy

Use Pest feature tests to cover:

- public rendering of published vs draft pages;
- slug uniqueness and route resolution;
- block validation and type restrictions;
- donate/membership dynamic blocks pulling current synced records;
- fallback behavior when no published page is configured.

Also test Filament resource interactions for create/edit/publish flows.

## Risks and Mitigations

- Scope creep in block library: start with a constrained block set and add only validated needs.
- Editor confusion across page types: enforce block allowlists and clear helper text.
- Performance: cache rendered block trees per page + invalidate on publish/update.
- Upgrade burden (if plugin chosen too early): start native and revisit after usage patterns stabilize.

## When to Consider Filament Fabricator

Re-evaluate Fabricator if we need:

- many page templates/layout families;
- deeper block discovery/registry tooling out of the box;
- faster multi-site style expansion where custom implementation overhead grows.

At current project stage, native Builder is the lower-risk path.

## Suggested Next Build Ticket

"Implement Page Builder Phase 1 (native Filament Builder): pages table, model/resource, marketing page render route, and Pest coverage."

That gives us immediate editorial value and a stable base for fund/membership integration in Phase 2.
