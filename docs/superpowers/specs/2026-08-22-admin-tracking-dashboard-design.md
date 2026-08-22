# Admin Tracking Dashboard — Design

Deliverable 14 of `docs/meta-tracking.md` §9. Gives an admin one page that answers
three questions: which funnel events are firing, is the Conversions API healthy, and
is Meta actually deduplicating our browser/server pairs.

## Problem

`meta_events` records every server-side (CAPI) event, so CAPI health is already
queryable. Nothing records browser-side fires. That leaves two of the spec's three
columns with no data source:

- **Events** — `PageView` is fired only by the base snippet, so it has no server row.
- **Deduplication** — comparing browser count to server count requires knowing what
  the browser actually did. Flashing a payload is not evidence `fbq` ran; an ad
  blocker that 503s `fbevents.js` produces a flash with no fire.

Measuring the browser side is therefore a prerequisite, not an optional extra.

## Approach

Record browser fires in a **separate `meta_browser_events` table**, written by a
public beacon endpoint the Pixel helper calls immediately after `fbq` returns.

Rejected: adding a `source` column to `meta_events`. It would force `event_id` from
globally unique to unique-per-source, and the existing pre-queue guard
(`MetaEvent::where('event_id', $eventId)->exists()` in `MetaEventService`) would then
match browser rows and silently suppress real CAPI sends. Browser rows would also
carry four columns that mean nothing for them (`status`, `attempts`, `last_error`,
`sent_at`). The two records look alike but behave nothing alike: a browser fire is a
completed fact, a CAPI event has a retry lifecycle.

## Components

### 1. `meta_browser_events` table + model

```
id
event_name   string            index
event_id     string nullable   index
created_at / updated_at
```

Index `['event_name', 'created_at']` for the range-scoped funnel query.

**Deliberately not unique on `event_id`.** The same id firing twice in the browser is
a real bug we want visible: "185 fires across 183 distinct ids" is a double-fire
signal that a unique constraint would hide.

`event_id` is nullable because the base snippet fires `PageView` with no `eventID`.

### 2. Beacon endpoint

`POST /meta/browser-event` → `MetaBrowserEventController::store`

- **Public** (guests get the Pixel, so guest fires must be recordable) and outside
  the admin/auth groups.
- `throttle:60,1` — it is an unauthenticated write endpoint.
- Validated by `StoreMetaBrowserEventRequest`: `event_name` required and `Rule::in`
  an allowlist of Meta standard event names; `event_id` nullable string max 191.
  A hostile client can only insert rows we already expect to see.
- Nothing numeric is accepted from the browser. No `value`, no `currency`, no
  counts — those would be trivially forgeable and are never displayed.
- Returns `204 No Content`. No-ops (still 204) when `services.meta.pixel_id` is
  blank, mirroring the existing `dualSend` guard.
- **CSRF stays enabled.** Laravel sets the `XSRF-TOKEN` cookie on the response that
  delivered the page, and `window.axios` sends it back automatically, so the beacon
  passes verification without any exemption. On an expired session it 419s and we
  lose one telemetry row — an acceptable trade for not opening an unauthenticated,
  CSRF-exempt write endpoint.

### 3. Pixel helper change (`resources/js/meta-pixel.js`)

After each successful `fbq` call — the base-snippet `PageView` excepted, which the
helper does not own — post `{ event_name, event_id }` to the beacon.

This uses `window.axios` rather than Inertia's `router.post`, a deliberate exception
to the project's "use Inertia's router" rule. A beacon must not change page state;
`router.post` performs an Inertia visit, which re-renders the page and re-triggers
the `success` handler this helper listens on. `window.axios` is already configured in
`resources/js/bootstrap.js` to send the CSRF header from the XSRF cookie, so no token
is read by hand. Failures are swallowed — a missed beacon row must never surface to a
customer mid-checkout.

To count `PageView`, `trackInertiaPageViews` beacons its own client-side fires, and
the initial document `PageView` is beaconed once at helper setup.

### 4. `MetaTrackingReportService`

All queries Eloquent, all scoped to a resolved `?Carbon $from` (null = all time).

- `funnel($from)` — per event name: browser fires, and server rows split by status.
  Rows are derived from the data present, not a hard-coded event list, so
  `ViewContent`/`InitiateCheckout` are absent rather than permanently zero until
  those events ship.
- `capiHealth($from)` — `meta_events` counts by `MetaEventStatus`.
- `deduplication($from)` — four numbers, defined precisely:
  - **Browser** — distinct non-null `event_id` in `meta_browser_events`.
  - **Server** — distinct `event_id` in `meta_events` where status is `sent`.
    Pending and failed rows never reached Meta, so they cannot deduplicate.
  - **Matched** — distinct `event_id` present in *both*. This is the health signal:
    matched well below either side means Meta is counting two events, not one.
  - **Deduplicated** — distinct `event_id` across the union. What Meta actually
    counts after merging.
- `recentEvents($from, $perPage)` — paginated `meta_events`, newest first, with
  `event_name`, `event_id`, `status`, `attempts`, `last_error`, `sent_at`, `payload`.

Browser `PageView` has a null `event_id` and so contributes to the funnel column but
is excluded from every dedup figure, with a note on the panel saying so. Its funnel
`server` cell still reads `0` rather than blank — "we send no server PageView" and "the
queue is broken" must not look different, so the zero stays honest.

### 5. `MetaTrackingController`

Behind `['auth', 'verified', 'role:admin']`.

- `GET /admin/tracking` → `admin.tracking.index`, renders `Admin/Tracking/Index`.
  `range` query param validated against `today|7d|30d|all`, default `7d`.
- `POST /admin/tracking/events/{event}/retry` → `admin.tracking.retry`.
  Requeues one event: guard that status is `failed` (422 otherwise), reset to
  `pending` via a new `MetaEvent::markPending()`, redispatch `SendMetaCapiEvent`,
  redirect back with a flash. This closes the gap where `markFailed` is terminal and
  an outage past five attempts leaves a row stuck forever. `attempts` keeps
  accumulating across retries rather than resetting — the cumulative number is the
  more useful diagnostic.

### 6. `Admin/Tracking/Index.vue`

Follows the existing `Admin/Dashboard.vue` glass-card styling and
`AuthenticatedLayout`.

- Three panels — Events funnel, CAPI health, Deduplication — in the spec's layout.
- Range tabs driving `router.get` with `preserveState`.
- Refresh button → `router.reload({ only: [...] })`.
- `usePoll(1_800_000, { only: [...] })` — a 30-minute background refresh so a page
  left open does not go stale, with the button covering deliberate verification.
- Recent-events table; a row expands to show the JSON payload actually sent to Meta.
  Failed rows get a Retry button.
- Entry point linked from `Admin/Dashboard.vue`.

The Pixel is not rendered for admins, so an admin using this page never pollutes the
numbers they are reading.

## Testing

Pest feature tests, in-memory SQLite. Unordered results asserted with
`toEqualCanonicalizing` per `CLAUDE.md`.

`tests/Feature/MetaBrowserBeaconTest.php`
- records a fire with and without an `event_id`
- rejects an event name outside the allowlist
- ignores browser-supplied `value`/`currency` fields
- no-ops when `pixel_id` is unconfigured
- is reachable by a guest
- returns 204

`tests/Feature/MetaTrackingDashboardTest.php`
- non-admin gets 403; admin gets 200
- funnel counts split browser vs server correctly
- dedup math: a matched pair counts 1 deduplicated, 1 matched; a server-only event
  raises Server and Deduplicated but not Matched
- `pending`/`failed` server rows are excluded from the Server dedup figure
- range filter excludes rows outside the window
- retry requeues a failed event and dispatches the job
- retry on a `sent` event is rejected

## Out of scope

- `ViewContent` and `InitiateCheckout` — the two missing funnel events. The dashboard
  omits their rows until they ship.
- UTM/fbclid attribution (deliverables 6 and 7).
- Meta Marketing API / ROAS (spec §10).
