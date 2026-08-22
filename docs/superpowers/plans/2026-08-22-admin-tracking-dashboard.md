# Admin Tracking Dashboard Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Ship deliverable 14 of `docs/meta-tracking.md` — an admin page showing funnel event counts, Conversions API health, and real browser-vs-server deduplication, with a recent-events table, payload drill-down, failed-event retry, and a date filter.

**Architecture:** Browser-side Pixel fires are recorded in a new `meta_browser_events` table via a public beacon endpoint the Pixel helper calls after `fbq` actually runs. `meta_events` is left untouched so its unique constraint, dedup guard, and job lifecycle keep working. A `MetaTrackingReportService` aggregates both tables; a `MetaTrackingController` renders one Inertia page behind `role:admin`.

**Tech Stack:** Laravel 12, Pest 4, Inertia v2, Vue 3 + TypeScript, Tailwind. Test DB is in-memory SQLite.

**Design doc:** `docs/superpowers/specs/2026-08-22-admin-tracking-dashboard-design.md`

**Read first:** `docs/meta-tracking.md` §5 (deduplication), §7 (queues/retries), §9 (dashboard shape).

---

## File Structure

**Create:**
- `app/Enums/MetaStandardEvent.php` — the allowlist of Meta standard event names, declared in funnel order. One source of truth for beacon validation and report row ordering.
- `app/Enums/MetaTrackingRange.php` — the date-filter options and their resolution to a start timestamp.
- `database/migrations/*_create_meta_browser_events_table.php`
- `app/Models/MetaBrowserEvent.php` — a browser fire. No lifecycle, no status.
- `app/Http/Requests/StoreMetaBrowserEventRequest.php`
- `app/Http/Controllers/MetaBrowserEventController.php` — the public beacon sink.
- `app/Services/Meta/MetaTrackingReportService.php` — all dashboard aggregation.
- `app/Http/Controllers/MetaTrackingController.php` — renders the page, retries an event.
- `resources/js/Pages/Admin/Tracking/Index.vue`
- `tests/Feature/MetaBrowserEventTest.php`
- `tests/Feature/MetaBrowserBeaconTest.php`
- `tests/Feature/MetaTrackingReportTest.php`
- `tests/Feature/MetaTrackingDashboardTest.php`

**Modify:**
- `app/Models/MetaEvent.php` — add `markPending()`.
- `resources/js/meta-pixel.js` — beacon each fire.
- `routes/web.php` — one public route, two admin routes.
- `resources/js/Pages/Admin/Dashboard.vue` — link to the new page.

**Note on test placement:** every new test file goes in `tests/Feature/`, including the service test. `tests/Pest.php` applies `RefreshDatabase` only to `->in('Feature')`, so a DB-touching test in `tests/Unit/` would run against no schema.

---

### Task 1: The `MetaStandardEvent` allowlist enum

Meta standard event names, declared in funnel order so the same enum drives both beacon validation and the ordering of dashboard rows.

**Files:**
- Create: `app/Enums/MetaStandardEvent.php`
- Test: `tests/Feature/MetaStandardEventTest.php`

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/MetaStandardEventTest.php`:

```php
<?php

use App\Enums\MetaStandardEvent;

it('lists the standard event names', function () {
    expect(MetaStandardEvent::names())->toContain('PageView', 'AddToCart', 'Purchase')
        ->and(MetaStandardEvent::names())->not->toContain('CalculatorUsed');
});

it('orders the funnel from PageView through to Purchase', function () {
    expect(MetaStandardEvent::position('PageView'))
        ->toBeLessThan(MetaStandardEvent::position('AddToCart'))
        ->and(MetaStandardEvent::position('AddToCart'))
        ->toBeLessThan(MetaStandardEvent::position('AddPaymentInfo'))
        ->and(MetaStandardEvent::position('AddPaymentInfo'))
        ->toBeLessThan(MetaStandardEvent::position('Purchase'));
});

it('sorts an unknown event name last rather than throwing', function () {
    expect(MetaStandardEvent::position('SomethingCustom'))
        ->toBeGreaterThan(MetaStandardEvent::position('Contact'));
});
```

- [ ] **Step 2: Run it to confirm it fails**

Run: `php artisan test --no-coverage tests/Feature/MetaStandardEventTest.php`
Expected: FAIL — `Class "App\Enums\MetaStandardEvent" not found`.

- [ ] **Step 3: Create the enum**

```bash
php artisan make:enum MetaStandardEvent --string --no-interaction
```

If `make:enum` is unavailable on this Laravel version, create the file by hand. Write `app/Enums/MetaStandardEvent.php`:

```php
<?php

namespace App\Enums;

/**
 * The Meta standard events we are willing to record. Declared in funnel order —
 * `position()` is what sorts the dashboard's Events column, so this list is both
 * the beacon allowlist and the report ordering.
 */
enum MetaStandardEvent: string
{
    case PAGE_VIEW = 'PageView';
    case VIEW_CONTENT = 'ViewContent';
    case SEARCH = 'Search';
    case ADD_TO_CART = 'AddToCart';
    case INITIATE_CHECKOUT = 'InitiateCheckout';
    case ADD_PAYMENT_INFO = 'AddPaymentInfo';
    case PURCHASE = 'Purchase';
    case COMPLETE_REGISTRATION = 'CompleteRegistration';
    case LEAD = 'Lead';
    case CONTACT = 'Contact';

    /**
     * @return array<int, string>
     */
    public static function names(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Sort key for the Events column. An event name we do not recognise sorts to
     * the bottom instead of blowing up the dashboard.
     */
    public static function position(string $eventName): int
    {
        $index = array_search($eventName, self::names(), true);

        return $index === false ? PHP_INT_MAX : $index;
    }
}
```

- [ ] **Step 4: Run it to confirm it passes**

Run: `php artisan test --no-coverage tests/Feature/MetaStandardEventTest.php`
Expected: PASS, 3 tests.

- [ ] **Step 5: Commit**

```bash
vendor/bin/pint --dirty --format agent
git add app/Enums/MetaStandardEvent.php tests/Feature/MetaStandardEventTest.php
git commit -m "feat: add MetaStandardEvent allowlist in funnel order"
```

---

### Task 2: The `meta_browser_events` table and model

**Files:**
- Create: `database/migrations/*_create_meta_browser_events_table.php`
- Create: `app/Models/MetaBrowserEvent.php`
- Test: `tests/Feature/MetaBrowserEventTest.php`

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/MetaBrowserEventTest.php`:

```php
<?php

use App\Models\MetaBrowserEvent;

it('records the same event_id twice so a double fire stays visible', function () {
    MetaBrowserEvent::create(['event_name' => 'AddToCart', 'event_id' => 'atc_1']);
    MetaBrowserEvent::create(['event_name' => 'AddToCart', 'event_id' => 'atc_1']);

    expect(MetaBrowserEvent::count())->toBe(2);
});

it('records a fire that carries no event_id', function () {
    $event = MetaBrowserEvent::create(['event_name' => 'PageView']);

    expect($event->event_name)->toBe('PageView')
        ->and($event->event_id)->toBeNull();
});
```

The first test is the point of the table: `event_id` is deliberately **not** unique, because "185 fires across 183 distinct ids" is a double-fire bug we want the dashboard to show. A unique constraint would hide it.

- [ ] **Step 2: Run it to confirm it fails**

Run: `php artisan test --no-coverage tests/Feature/MetaBrowserEventTest.php`
Expected: FAIL — `Class "App\Models\MetaBrowserEvent" not found`.

- [ ] **Step 3: Create the migration and model**

```bash
php artisan make:model MetaBrowserEvent -m --no-interaction
```

Write the migration's `up()` (the generated file is `database/migrations/<timestamp>_create_meta_browser_events_table.php`):

```php
public function up(): void
{
    Schema::create('meta_browser_events', function (Blueprint $table) {
        $table->id();
        $table->string('event_name');
        // Null for the base snippet's PageView, which fires with no eventID.
        $table->string('event_id')->nullable();
        $table->timestamps();

        // No unique on event_id: a repeated id is a double fire we want to see.
        $table->index(['event_name', 'created_at']);
        $table->index('event_id');
    });
}

public function down(): void
{
    Schema::dropIfExists('meta_browser_events');
}
```

Write `app/Models/MetaBrowserEvent.php`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One Pixel fire that actually reached `fbevents.js` in a browser.
 *
 * Deliberately separate from MetaEvent: a browser fire is a completed fact with no
 * status, no attempts and nothing to retry, and keeping it out of `meta_events`
 * leaves that table's unique `event_id` — and the dedup guard in MetaEventService
 * that depends on it — working exactly as before.
 */
class MetaBrowserEvent extends Model
{
    protected $fillable = [
        'event_name',
        'event_id',
    ];
}
```

- [ ] **Step 4: Run it to confirm it passes**

Run: `php artisan test --no-coverage tests/Feature/MetaBrowserEventTest.php`
Expected: PASS, 2 tests.

- [ ] **Step 5: Migrate the development database**

Run: `php artisan migrate`
Expected: `meta_browser_events` created. (Tests use in-memory SQLite and migrate themselves; this step is for the `market` dev DB.)

- [ ] **Step 6: Commit**

```bash
vendor/bin/pint --dirty --format agent
git add database/migrations app/Models/MetaBrowserEvent.php tests/Feature/MetaBrowserEventTest.php
git commit -m "feat: add meta_browser_events table for browser-side pixel fires"
```

---

### Task 3: The public beacon endpoint

**Files:**
- Create: `app/Http/Requests/StoreMetaBrowserEventRequest.php`
- Create: `app/Http/Controllers/MetaBrowserEventController.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/MetaBrowserBeaconTest.php`

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/MetaBrowserBeaconTest.php`:

```php
<?php

use App\Models\MetaBrowserEvent;

beforeEach(function () {
    config()->set('services.meta.pixel_id', '1562473862273045');
});

it('records a fire from a guest, since guests get the pixel too', function () {
    $this->postJson(route('meta.browser-event'), ['event_name' => 'PageView'])
        ->assertNoContent();

    $event = MetaBrowserEvent::sole();

    expect($event->event_name)->toBe('PageView')
        ->and($event->event_id)->toBeNull();
});

it('records the event_id so the fire can be matched against a CAPI send', function () {
    $this->postJson(route('meta.browser-event'), [
        'event_name' => 'Purchase',
        'event_id' => 'order_9843',
    ])->assertNoContent();

    expect(MetaBrowserEvent::sole()->event_id)->toBe('order_9843');
});

it('rejects an event name outside the standard allowlist', function () {
    $this->postJson(route('meta.browser-event'), ['event_name' => 'NotAnEvent'])
        ->assertInvalid('event_name');

    expect(MetaBrowserEvent::count())->toBe(0);
});

it('requires an event name', function () {
    $this->postJson(route('meta.browser-event'), [])
        ->assertInvalid('event_name');
});

it('ignores numeric fields the browser tries to supply', function () {
    $this->postJson(route('meta.browser-event'), [
        'event_name' => 'Purchase',
        'event_id' => 'order_1',
        'value' => 999999,
        'currency' => 'USD',
    ])->assertNoContent();

    expect(MetaBrowserEvent::sole()->getAttributes())
        ->not->toHaveKey('value')
        ->not->toHaveKey('currency');
});

it('records nothing when the pixel is not configured', function () {
    config()->set('services.meta.pixel_id', null);

    $this->postJson(route('meta.browser-event'), ['event_name' => 'PageView'])
        ->assertNoContent();

    expect(MetaBrowserEvent::count())->toBe(0);
});

it('throttles a flood of beacons', function () {
    foreach (range(1, 60) as $ignored) {
        $this->postJson(route('meta.browser-event'), ['event_name' => 'PageView'])
            ->assertNoContent();
    }

    $this->postJson(route('meta.browser-event'), ['event_name' => 'PageView'])
        ->assertStatus(429);
});
```

- [ ] **Step 2: Run it to confirm it fails**

Run: `php artisan test --no-coverage tests/Feature/MetaBrowserBeaconTest.php`
Expected: FAIL — `Route [meta.browser-event] not defined`.

- [ ] **Step 3: Write the form request**

```bash
php artisan make:request StoreMetaBrowserEventRequest --no-interaction
```

Write `app/Http/Requests/StoreMetaBrowserEventRequest.php`:

```php
<?php

namespace App\Http\Requests;

use App\Enums\MetaStandardEvent;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * This endpoint is public and unauthenticated, so the body is hostile input. The
 * allowlist means a caller can only create rows for events we already expect, and
 * nothing numeric is accepted — a browser-supplied `value` would be trivially
 * forgeable, so the dashboard never reads one.
 */
class StoreMetaBrowserEventRequest extends FormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'event_name' => ['required', 'string', Rule::in(MetaStandardEvent::names())],
            'event_id' => ['nullable', 'string', 'max:191'],
        ];
    }
}
```

- [ ] **Step 4: Write the controller**

```bash
php artisan make:controller MetaBrowserEventController --no-interaction
```

Write `app/Http/Controllers/MetaBrowserEventController.php`:

```php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMetaBrowserEventRequest;
use App\Models\MetaBrowserEvent;
use Illuminate\Http\Response;

class MetaBrowserEventController extends Controller
{
    /**
     * The Pixel calls this the moment `fbq` has actually run, so the dashboard can
     * compare what the browser really sent against what the Conversions API sent.
     * Flashing a payload only proves we asked for a fire; this proves one happened.
     */
    public function store(StoreMetaBrowserEventRequest $request): Response
    {
        // Mirrors the guard in MetaEventService::dualSend — no pixel, no tracking.
        if (filled(config('services.meta.pixel_id'))) {
            MetaBrowserEvent::create($request->safe()->only(['event_name', 'event_id']));
        }

        return response()->noContent();
    }
}
```

- [ ] **Step 5: Register the route**

In `routes/web.php`, add the import alongside the other controller imports:

```php
use App\Http\Controllers\MetaBrowserEventController;
```

Then add the route near the top, outside every auth group (guests must reach it). Put it directly after the `Route::middleware('guest')->group(...)` block:

```php
// Telemetry sink for the browser Pixel. Public by necessity — guests are the top of
// the funnel — so it is throttled and accepts only an allowlisted event name plus id.
Route::post('/meta/browser-event', [MetaBrowserEventController::class, 'store'])
    ->middleware('throttle:60,1')
    ->name('meta.browser-event');
```

CSRF stays on. Laravel sets the `XSRF-TOKEN` cookie on the response that delivered the page and `window.axios` returns it automatically, so no exemption is needed. An expired session costs one telemetry row, which is a better trade than an unauthenticated CSRF-exempt write endpoint.

- [ ] **Step 6: Run it to confirm it passes**

Run: `php artisan test --no-coverage tests/Feature/MetaBrowserBeaconTest.php`
Expected: PASS, 7 tests.

- [ ] **Step 7: Commit**

```bash
vendor/bin/pint --dirty --format agent
git add app/Http/Requests/StoreMetaBrowserEventRequest.php app/Http/Controllers/MetaBrowserEventController.php routes/web.php tests/Feature/MetaBrowserBeaconTest.php
git commit -m "feat: add throttled beacon endpoint for browser pixel fires"
```

---

### Task 4: Beacon every real Pixel fire

There is no JS test runner in this project, so this task is verified by reading the network tab in Task 9. Keep the change small and obvious.

**Files:**
- Modify: `resources/js/meta-pixel.js`

- [ ] **Step 1: Add the load check and the beacon**

The base snippet defines a **stub** `fbq` that queues calls, so `window.fbq` exists even when an ad blocker has blocked `fbevents.js`. `callMethod` is only wired up by the real library, which makes it the honest test of whether a fire reached Meta.

Add to the top of `resources/js/meta-pixel.js`, after the existing `import`:

```js
/**
 * The base snippet's stub `fbq` queues calls, so `window.fbq` is defined even when
 * `fbevents.js` was blocked. Only the real library sets `callMethod`, so this is the
 * honest test of whether a fire actually reached Meta — a blocked fire records no
 * browser row, which is exactly the gap the tracking dashboard should show.
 */
function pixelLoaded() {
    return typeof window.fbq?.callMethod === 'function';
}

/**
 * `fbevents.js` is injected async, so the first fire can beat it. Wait for the real
 * library for about four seconds, then give up — at that point it is blocked or
 * failed, and recording nothing is the correct answer.
 */
function whenPixelLoaded(callback, attempt = 0) {
    if (pixelLoaded()) {
        callback();

        return;
    }

    if (attempt >= 20) {
        return;
    }

    setTimeout(() => whenPixelLoaded(callback, attempt + 1), 200);
}

/**
 * Tell the server the Pixel fired, so the dashboard can compare browser counts to
 * Conversions API counts. Deliberately `axios` rather than Inertia's router: a
 * beacon must not perform a visit, which would re-render the page and re-trigger
 * the `success` handler below. `resources/js/bootstrap.js` already configures axios
 * to send the CSRF header from the XSRF cookie, so no token is read by hand.
 * Failures are dropped — a missing telemetry row must never reach a customer.
 */
function beacon(eventName, eventId = null) {
    whenPixelLoaded(() => {
        window.axios
            ?.post(route('meta.browser-event'), { event_name: eventName, event_id: eventId })
            .catch(() => {});
    });
}
```

`route()` needs no import here. The `@routes` directive in `resources/views/app.blade.php`
inlines Ziggy's UMD bundle (`vendor/tightenco/ziggy/src/BladeRouteGenerator.php:33`),
which puts `route` on the global scope — this is the same reason
`resources/js/Pages/Customer/Products/Show.vue` calls it from `<script setup>` without
importing it.

- [ ] **Step 2: Beacon PageView**

Replace the whole `trackInertiaPageViews` function with:

```js
export function trackInertiaPageViews() {
    let isInitialVisit = true;

    // The base snippet already fired PageView for the initial document; report it
    // here because the snippet itself has no way to reach the server.
    beacon('PageView');

    router.on('navigate', () => {
        if (isInitialVisit) {
            isInitialVisit = false;

            return;
        }

        window.fbq?.('track', 'PageView');
        beacon('PageView');
    });
}
```

- [ ] **Step 3: Beacon flashed events**

In `trackFlashedEvents`, extend the `fire` callback to beacon after the `fbq` call:

```js
    const fire = (meta) => {
        if (!meta || fired.has(meta.event_id)) {
            return;
        }

        fired.add(meta.event_id);

        window.fbq?.('track', meta.name, meta.params, { eventID: meta.event_id });
        beacon(meta.name, meta.event_id);
    };
```

- [ ] **Step 4: Lint and build**

Run: `npx eslint resources/js/meta-pixel.js --fix && npx prettier --write resources/js/meta-pixel.js`
Expected: no errors.

Run: `npm run build`
Expected: build succeeds.

- [ ] **Step 5: Confirm nothing server-side regressed**

Run: `php artisan test --no-coverage --filter=Meta`
Expected: all existing Meta tests still pass (32 before this plan, plus the new ones).

- [ ] **Step 6: Commit**

```bash
git add resources/js/meta-pixel.js
git commit -m "feat: beacon real pixel fires to the server"
```

---

### Task 5: The date range enum

**Files:**
- Create: `app/Enums/MetaTrackingRange.php`
- Test: `tests/Feature/MetaTrackingRangeTest.php`

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/MetaTrackingRangeTest.php`:

```php
<?php

use App\Enums\MetaTrackingRange;
use Illuminate\Support\Carbon;

it('resolves each range to a start time', function () {
    Carbon::setTestNow('2026-08-22 14:00:00');

    expect(MetaTrackingRange::TODAY->since()->toDateTimeString())->toBe('2026-08-22 00:00:00')
        ->and(MetaTrackingRange::WEEK->since()->toDateTimeString())->toBe('2026-08-15 14:00:00')
        ->and(MetaTrackingRange::MONTH->since()->toDateTimeString())->toBe('2026-07-23 14:00:00')
        ->and(MetaTrackingRange::ALL->since())->toBeNull();
});

it('lists its values for the dashboard tabs', function () {
    expect(MetaTrackingRange::values())->toBe(['today', '7d', '30d', 'all']);
});
```

- [ ] **Step 2: Run it to confirm it fails**

Run: `php artisan test --no-coverage tests/Feature/MetaTrackingRangeTest.php`
Expected: FAIL — `Class "App\Enums\MetaTrackingRange" not found`.

- [ ] **Step 3: Write the enum**

Write `app/Enums/MetaTrackingRange.php`:

```php
<?php

namespace App\Enums;

use Illuminate\Support\Carbon;

/**
 * The tracking dashboard's date filter. `ALL` resolves to null, which every report
 * query reads as "do not constrain".
 *
 * Named `since()` rather than `from()` because every backed enum already inherits a
 * static `BackedEnum::from()`, and PHP forbids a static and an instance method
 * sharing a name.
 */
enum MetaTrackingRange: string
{
    case TODAY = 'today';
    case WEEK = '7d';
    case MONTH = '30d';
    case ALL = 'all';

    public function since(): ?Carbon
    {
        return match ($this) {
            self::TODAY => Carbon::today(),
            self::WEEK => Carbon::now()->subDays(7),
            self::MONTH => Carbon::now()->subDays(30),
            self::ALL => null,
        };
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
```

- [ ] **Step 4: Run it to confirm it passes**

Run: `php artisan test --no-coverage tests/Feature/MetaTrackingRangeTest.php`
Expected: PASS, 2 tests.

- [ ] **Step 5: Commit**

```bash
vendor/bin/pint --dirty --format agent
git add app/Enums/MetaTrackingRange.php tests/Feature/MetaTrackingRangeTest.php
git commit -m "feat: add MetaTrackingRange for the dashboard date filter"
```

---

### Task 6: The report service

**Files:**
- Create: `app/Services/Meta/MetaTrackingReportService.php`
- Test: `tests/Feature/MetaTrackingReportTest.php`

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/MetaTrackingReportTest.php`:

```php
<?php

use App\Enums\MetaEventStatus;
use App\Models\MetaBrowserEvent;
use App\Models\MetaEvent;
use App\Services\Meta\MetaTrackingReportService;
use Illuminate\Support\Carbon;

function serverEvent(string $name, string $eventId, MetaEventStatus $status, ?Carbon $at = null): MetaEvent
{
    $event = MetaEvent::create([
        'event_name' => $name,
        'event_id' => $eventId,
        'status' => $status,
        'payload' => ['event_name' => $name, 'event_id' => $eventId],
    ]);

    if ($at) {
        $event->forceFill(['created_at' => $at])->save();
    }

    return $event;
}

function browserFire(string $name, ?string $eventId = null, ?Carbon $at = null): MetaBrowserEvent
{
    $fire = MetaBrowserEvent::create(['event_name' => $name, 'event_id' => $eventId]);

    if ($at) {
        $fire->forceFill(['created_at' => $at])->save();
    }

    return $fire;
}

beforeEach(function () {
    $this->reports = app(MetaTrackingReportService::class);
});

it('reports the funnel in funnel order with browser and server counts', function () {
    browserFire('PageView');
    browserFire('PageView');
    browserFire('AddToCart', 'atc_1');
    serverEvent('AddToCart', 'atc_1', MetaEventStatus::SENT);
    serverEvent('Purchase', 'order_1', MetaEventStatus::SENT);

    expect($this->reports->funnel(null))->toBe([
        ['event_name' => 'PageView', 'browser' => 2, 'server' => 0],
        ['event_name' => 'AddToCart', 'browser' => 1, 'server' => 1],
        ['event_name' => 'Purchase', 'browser' => 0, 'server' => 1],
    ]);
});

it('omits an event with no data rather than showing a permanent zero row', function () {
    browserFire('PageView');

    expect(collect($this->reports->funnel(null))->pluck('event_name')->all())
        ->toBe(['PageView']);
});

it('counts CAPI events by status', function () {
    serverEvent('Purchase', 'order_1', MetaEventStatus::SENT);
    serverEvent('Purchase', 'order_2', MetaEventStatus::SENT);
    serverEvent('AddToCart', 'atc_1', MetaEventStatus::FAILED);

    expect($this->reports->capiHealth(null))
        ->toBe(['pending' => 0, 'sent' => 2, 'failed' => 1]);
});

it('counts a matched pair as one deduplicated event', function () {
    browserFire('Purchase', 'order_1');
    serverEvent('Purchase', 'order_1', MetaEventStatus::SENT);

    expect($this->reports->deduplication(null))->toBe([
        'browser' => 1,
        'server' => 1,
        'matched' => 1,
        'deduplicated' => 1,
    ]);
});

it('shows an unmatched server event as a dedup gap', function () {
    browserFire('AddToCart', 'atc_1');
    serverEvent('AddToCart', 'atc_1', MetaEventStatus::SENT);
    // Ad-blocked in the browser, so CAPI is the only sender.
    serverEvent('Purchase', 'order_1', MetaEventStatus::SENT);

    expect($this->reports->deduplication(null))->toBe([
        'browser' => 1,
        'server' => 2,
        'matched' => 1,
        'deduplicated' => 2,
    ]);
});

it('excludes pending and failed events from the server dedup figure', function () {
    serverEvent('Purchase', 'order_1', MetaEventStatus::PENDING);
    serverEvent('Purchase', 'order_2', MetaEventStatus::FAILED);

    expect($this->reports->deduplication(null)['server'])->toBe(0);
});

it('ignores a browser fire with no event_id in the dedup figures', function () {
    browserFire('PageView');

    expect($this->reports->deduplication(null))->toBe([
        'browser' => 0,
        'server' => 0,
        'matched' => 0,
        'deduplicated' => 0,
    ]);
});

it('counts a repeated browser event_id once, so a double fire is visible in the funnel only', function () {
    browserFire('Purchase', 'order_1');
    browserFire('Purchase', 'order_1');

    expect($this->reports->deduplication(null)['browser'])->toBe(1)
        ->and($this->reports->funnel(null)[0]['browser'])->toBe(2);
});

it('scopes every figure to the requested range', function () {
    Carbon::setTestNow('2026-08-22 12:00:00');

    $old = Carbon::parse('2026-08-01 12:00:00');
    browserFire('Purchase', 'order_old', $old);
    serverEvent('Purchase', 'order_old', MetaEventStatus::SENT, $old);

    browserFire('Purchase', 'order_new');
    serverEvent('Purchase', 'order_new', MetaEventStatus::SENT);

    $from = Carbon::now()->subDays(7);

    expect($this->reports->funnel($from))->toBe([
        ['event_name' => 'Purchase', 'browser' => 1, 'server' => 1],
    ])
        ->and($this->reports->capiHealth($from)['sent'])->toBe(1)
        ->and($this->reports->deduplication($from)['deduplicated'])->toBe(1);
});

it('paginates recent events newest first', function () {
    serverEvent('AddToCart', 'atc_1', MetaEventStatus::SENT);
    serverEvent('Purchase', 'order_1', MetaEventStatus::FAILED);

    $page = $this->reports->recentEvents(null, perPage: 1);

    expect($page->total())->toBe(2)
        ->and($page->items()[0]->event_id)->toBe('order_1');
});
```

- [ ] **Step 2: Run it to confirm it fails**

Run: `php artisan test --no-coverage tests/Feature/MetaTrackingReportTest.php`
Expected: FAIL — `Class "App\Services\Meta\MetaTrackingReportService" not found`.

- [ ] **Step 3: Write the service**

Write `app/Services/Meta/MetaTrackingReportService.php`:

```php
<?php

namespace App\Services\Meta;

use App\Enums\MetaEventStatus;
use App\Enums\MetaStandardEvent;
use App\Models\MetaBrowserEvent;
use App\Models\MetaEvent;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Aggregation behind the admin tracking dashboard. Every method takes a nullable
 * `$from`; null means all time.
 */
class MetaTrackingReportService
{
    /**
     * Browser fires against Conversions API rows, per event, in funnel order.
     *
     * Rows come from the data actually present, so an event we have not shipped yet
     * is simply absent instead of a permanent zero row.
     *
     * @return array<int, array{event_name: string, browser: int, server: int}>
     */
    public function funnel(?Carbon $from): array
    {
        $browser = $this->countsByEventName(MetaBrowserEvent::query(), $from);
        $server = $this->countsByEventName(MetaEvent::query(), $from);

        return $browser->keys()
            ->merge($server->keys())
            ->unique()
            ->sortBy(fn (string $name) => MetaStandardEvent::position($name))
            ->values()
            ->map(fn (string $name) => [
                'event_name' => $name,
                'browser' => $browser->get($name, 0),
                'server' => $server->get($name, 0),
            ])
            ->all();
    }

    /**
     * Every status is present in the result, including the ones sitting at zero —
     * "Failed 0" is information the dashboard needs to state.
     *
     * @return array<string, int>
     */
    public function capiHealth(?Carbon $from): array
    {
        $counts = MetaEvent::query()
            ->when($from, fn (Builder $query) => $query->where('created_at', '>=', $from))
            ->groupBy('status')
            ->selectRaw('status, count(*) as total')
            ->get()
            ->mapWithKeys(fn (MetaEvent $row) => [$row->status->value => (int) $row->total]);

        return collect(MetaEventStatus::cases())
            ->mapWithKeys(fn (MetaEventStatus $status) => [
                $status->value => $counts->get($status->value, 0),
            ])
            ->all();
    }

    /**
     * What Meta ends up counting once it merges our pairs.
     *
     * Only `sent` rows can deduplicate: a pending or failed event never reached
     * Meta, so it has nothing to merge with. `matched` is the health signal — well
     * below either side means Meta is counting two events where we intended one.
     *
     * @return array{browser: int, server: int, matched: int, deduplicated: int}
     */
    public function deduplication(?Carbon $from): array
    {
        $browserIds = MetaBrowserEvent::query()
            ->when($from, fn (Builder $query) => $query->where('created_at', '>=', $from))
            ->whereNotNull('event_id')
            ->distinct()
            ->pluck('event_id');

        $serverIds = MetaEvent::query()
            ->when($from, fn (Builder $query) => $query->where('created_at', '>=', $from))
            ->where('status', MetaEventStatus::SENT)
            ->distinct()
            ->pluck('event_id');

        return [
            'browser' => $browserIds->count(),
            'server' => $serverIds->count(),
            'matched' => $browserIds->intersect($serverIds)->count(),
            'deduplicated' => $browserIds->merge($serverIds)->unique()->count(),
        ];
    }

    /**
     * The debugging view: what we sent, whether it landed, and why it did not.
     */
    public function recentEvents(?Carbon $from, int $perPage = 15): LengthAwarePaginator
    {
        return MetaEvent::query()
            ->when($from, fn (Builder $query) => $query->where('created_at', '>=', $from))
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Grouped counts keyed by event name. `get()` then map rather than `pluck()`,
     * because `pluck` rebuilds the select and loses the aggregate column.
     *
     * @return Collection<string, int>
     */
    private function countsByEventName(Builder $query, ?Carbon $from): Collection
    {
        return $query
            ->when($from, fn (Builder $q) => $q->where('created_at', '>=', $from))
            ->groupBy('event_name')
            ->selectRaw('event_name, count(*) as total')
            ->get()
            ->mapWithKeys(fn ($row) => [$row->event_name => (int) $row->total]);
    }
}
```

Note on `deduplication()`: the id sets are intersected in PHP rather than SQL. That keeps one implementation working identically on SQLite and MySQL, and at dashboard scale the id list is small. If `meta_events` ever grows past a few hundred thousand rows in a window, move this to a SQL join.

- [ ] **Step 4: Run it to confirm it passes**

Run: `php artisan test --no-coverage tests/Feature/MetaTrackingReportTest.php`
Expected: PASS, 10 tests.

- [ ] **Step 5: Commit**

```bash
vendor/bin/pint --dirty --format agent
git add app/Services/Meta/MetaTrackingReportService.php tests/Feature/MetaTrackingReportTest.php
git commit -m "feat: add MetaTrackingReportService for funnel, CAPI health and dedup"
```

---

### Task 7: The dashboard controller and retry action

**Files:**
- Modify: `app/Models/MetaEvent.php`
- Create: `app/Http/Controllers/MetaTrackingController.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/MetaTrackingDashboardTest.php`

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/MetaTrackingDashboardTest.php`:

```php
<?php

use App\Enums\MetaEventStatus;
use App\Jobs\SendMetaCapiEvent;
use App\Models\MetaBrowserEvent;
use App\Models\MetaEvent;
use App\Models\User;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    config()->set('services.meta.pixel_id', '1562473862273045');
    config()->set('services.meta.capi_token', 'test-token');
});

function trackedEvent(string $name, string $eventId, MetaEventStatus $status): MetaEvent
{
    return MetaEvent::create([
        'event_name' => $name,
        'event_id' => $eventId,
        'status' => $status,
        'payload' => ['event_name' => $name, 'event_id' => $eventId],
    ]);
}

it('is closed to a customer', function () {
    $this->actingAs(User::factory()->customer()->create())
        ->get(route('admin.tracking.index'))
        ->assertForbidden();
});

it('is closed to a vendor', function () {
    $this->actingAs(User::factory()->vendor()->approved()->create())
        ->get(route('admin.tracking.index'))
        ->assertForbidden();
});

it('shows an admin the funnel, CAPI health and dedup figures', function () {
    MetaBrowserEvent::create(['event_name' => 'PageView']);
    MetaBrowserEvent::create(['event_name' => 'Purchase', 'event_id' => 'order_1']);
    trackedEvent('Purchase', 'order_1', MetaEventStatus::SENT);
    trackedEvent('AddToCart', 'atc_1', MetaEventStatus::FAILED);

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('admin.tracking.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/Tracking/Index')
            ->where('range', '7d')
            ->where('capi.sent', 1)
            ->where('capi.failed', 1)
            ->where('capi.pending', 0)
            ->where('dedup.browser', 1)
            ->where('dedup.server', 1)
            ->where('dedup.matched', 1)
            ->where('dedup.deduplicated', 1)
            ->where('pixelConfigured', true)
            ->where('capiConfigured', true)
            ->has('funnel', 3)
            ->has('events.data', 2)
        );
});

it('accepts a range and falls back to 7d on nonsense', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('admin.tracking.index', ['range' => 'all']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('range', 'all'));

    $this->actingAs($admin)
        ->get(route('admin.tracking.index', ['range' => 'nonsense']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('range', '7d'));
});

it('requeues a failed event', function () {
    Queue::fake();

    $event = trackedEvent('Purchase', 'order_1', MetaEventStatus::FAILED);
    $event->update(['last_error' => 'Invalid token']);

    $this->actingAs(User::factory()->admin()->create())
        ->post(route('admin.tracking.retry', $event))
        ->assertRedirect();

    expect($event->fresh()->status)->toBe(MetaEventStatus::PENDING)
        ->and($event->fresh()->last_error)->toBeNull();

    Queue::assertPushed(SendMetaCapiEvent::class, fn ($job) => $job->event->is($event));
});

it('refuses to requeue an event that already reached Meta', function () {
    Queue::fake();

    $event = trackedEvent('Purchase', 'order_1', MetaEventStatus::SENT);

    $this->actingAs(User::factory()->admin()->create())
        ->post(route('admin.tracking.retry', $event))
        ->assertRedirect();

    expect($event->fresh()->status)->toBe(MetaEventStatus::SENT);

    Queue::assertNothingPushed();
});

it('does not let a customer requeue an event', function () {
    Queue::fake();

    $event = trackedEvent('Purchase', 'order_1', MetaEventStatus::FAILED);

    $this->actingAs(User::factory()->customer()->create())
        ->post(route('admin.tracking.retry', $event))
        ->assertForbidden();

    Queue::assertNothingPushed();
});
```

- [ ] **Step 2: Run it to confirm it fails**

Run: `php artisan test --no-coverage tests/Feature/MetaTrackingDashboardTest.php`
Expected: FAIL — `Route [admin.tracking.index] not defined`.

- [ ] **Step 3: Add `markPending()` to the model**

In `app/Models/MetaEvent.php`, add after `markFailed()`:

```php
    /**
     * Put a failed event back in the queue. `attempts` deliberately keeps climbing
     * rather than resetting — the cumulative number is the useful diagnostic.
     */
    public function markPending(): void
    {
        $this->update([
            'status' => MetaEventStatus::PENDING,
            'last_error' => null,
        ]);
    }
```

- [ ] **Step 4: Write the controller**

```bash
php artisan make:controller MetaTrackingController --no-interaction
```

Write `app/Http/Controllers/MetaTrackingController.php`:

```php
<?php

namespace App\Http\Controllers;

use App\Enums\MetaEventStatus;
use App\Enums\MetaTrackingRange;
use App\Jobs\SendMetaCapiEvent;
use App\Models\MetaEvent;
use App\Services\Meta\ConversionsApiClient;
use App\Services\Meta\MetaTrackingReportService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class MetaTrackingController extends Controller
{
    public function __construct(
        private readonly MetaTrackingReportService $reports,
        private readonly ConversionsApiClient $capi,
    ) {}

    public function index(Request $request): Response
    {
        // An unrecognised range falls back rather than 422ing a dashboard.
        $range = MetaTrackingRange::tryFrom((string) $request->query('range'))
            ?? MetaTrackingRange::WEEK;

        $from = $range->since();

        return Inertia::render('Admin/Tracking/Index', [
            'range' => $range->value,
            'ranges' => MetaTrackingRange::values(),
            'funnel' => $this->reports->funnel($from),
            'capi' => $this->reports->capiHealth($from),
            'dedup' => $this->reports->deduplication($from),
            'events' => $this->reports->recentEvents($from),
            'pixelConfigured' => filled(config('services.meta.pixel_id')),
            'capiConfigured' => $this->capi->isConfigured(),
        ]);
    }

    /**
     * Send one failed event back to the queue. Without this `markFailed` is
     * terminal, so a Meta outage past the job's five attempts leaves a real
     * conversion unreported with no way to recover it.
     */
    public function retry(MetaEvent $event): RedirectResponse
    {
        if ($event->status !== MetaEventStatus::FAILED) {
            return back()->with('error', 'Only a failed event can be retried.');
        }

        $event->markPending();

        SendMetaCapiEvent::dispatch($event);

        return back()->with('success', "Requeued {$event->event_name} ({$event->event_id}).");
    }
}
```

- [ ] **Step 5: Register the routes**

Add the import in `routes/web.php`:

```php
use App\Http\Controllers\MetaTrackingController;
```

Add after the existing admin vendor routes (inside the `['auth', 'verified']` group, each route carrying `role:admin` to match the file's existing style):

```php
    // Meta tracking dashboard
    Route::get('/admin/tracking', [MetaTrackingController::class, 'index'])
        ->middleware('role:admin')
        ->name('admin.tracking.index');

    Route::post('/admin/tracking/events/{event}/retry', [MetaTrackingController::class, 'retry'])
        ->middleware('role:admin')
        ->name('admin.tracking.retry');
```

The `{event}` parameter name must stay `event` so it binds to the `MetaEvent $event` argument.

- [ ] **Step 6: Run it to confirm it passes**

Run: `php artisan test --no-coverage tests/Feature/MetaTrackingDashboardTest.php`
Expected: PASS, 7 tests.

`assertForbidden()` is correct here: `app/Http/Middleware/UserRole.php` calls `abort(403)` on a role mismatch, it does not redirect.

- [ ] **Step 7: Commit**

```bash
vendor/bin/pint --dirty --format agent
git add app/Models/MetaEvent.php app/Http/Controllers/MetaTrackingController.php routes/web.php tests/Feature/MetaTrackingDashboardTest.php
git commit -m "feat: add admin tracking dashboard controller and failed-event retry"
```

---

### Task 8: The dashboard page

**Files:**
- Create: `resources/js/Pages/Admin/Tracking/Index.vue`
- Modify: `resources/js/Pages/Admin/Dashboard.vue`

- [ ] **Step 1: Write the page**

Create `resources/js/Pages/Admin/Tracking/Index.vue`:

```vue
<script setup lang="ts">
import { Head, Link, router, usePoll } from '@inertiajs/vue3';
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

interface FunnelRow {
    event_name: string;
    browser: number;
    server: number;
}

interface TrackedEvent {
    id: number;
    event_name: string;
    event_id: string;
    status: 'pending' | 'sent' | 'failed';
    attempts: number;
    last_error: string | null;
    sent_at: string | null;
    created_at: string;
    payload: Record<string, unknown>;
}

const props = defineProps<{
    range: string;
    ranges: string[];
    funnel: FunnelRow[];
    capi: { pending: number; sent: number; failed: number };
    dedup: { browser: number; server: number; matched: number; deduplicated: number };
    events: { data: TrackedEvent[]; links: { url: string | null; label: string; active: boolean }[] };
    pixelConfigured: boolean;
    capiConfigured: boolean;
}>();

const only = ['funnel', 'capi', 'dedup', 'events'];

// Left open on a second monitor this keeps the numbers from going stale; the button
// covers a deliberate check while verifying in Events Manager.
usePoll(1_800_000, { only });

const expanded = ref<number | null>(null);
const refreshing = ref(false);

const rangeLabels: Record<string, string> = {
    today: 'Today',
    '7d': 'Last 7 days',
    '30d': 'Last 30 days',
    all: 'All time',
};

const statusStyles: Record<string, string> = {
    sent: 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400',
    pending: 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400',
    failed: 'bg-rose-50 text-rose-700 dark:bg-rose-500/10 dark:text-rose-400',
};

function refresh() {
    refreshing.value = true;
    router.reload({ only, onFinish: () => (refreshing.value = false) });
}

function selectRange(range: string) {
    router.get(route('admin.tracking.index'), { range }, { preserveState: true, preserveScroll: true });
}

function retry(event: TrackedEvent) {
    router.post(route('admin.tracking.retry', event.id), {}, { preserveScroll: true });
}
</script>

<template>
    <Head title="Meta Tracking" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-4">
                <h2 class="bg-gradient-to-r from-indigo-600 to-violet-600 bg-clip-text text-2xl font-bold text-transparent">
                    Meta Tracking
                </h2>
                <button
                    type="button"
                    class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-500 disabled:opacity-50"
                    :disabled="refreshing"
                    @click="refresh"
                >
                    {{ refreshing ? 'Refreshing…' : 'Refresh' }}
                </button>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
                <!-- Configuration warnings come first: every zero below is meaningless
                     if the pixel id or CAPI token is missing. -->
                <div
                    v-if="!props.pixelConfigured || !props.capiConfigured"
                    class="rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-800 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-300"
                >
                    <p v-if="!props.pixelConfigured">META_PIXEL_ID is not set — nothing is being tracked at all.</p>
                    <p v-if="!props.capiConfigured">META_CAPI_ACCESS_TOKEN is not set — server events are never queued.</p>
                </div>

                <!-- Range filter -->
                <div class="flex flex-wrap gap-2">
                    <button
                        v-for="option in props.ranges"
                        :key="option"
                        type="button"
                        :class="[
                            'rounded-xl px-4 py-2 text-sm font-semibold transition',
                            option === props.range
                                ? 'bg-indigo-600 text-white'
                                : 'bg-white/80 text-gray-600 hover:bg-white dark:bg-[#1e2028]/90 dark:text-gray-300',
                        ]"
                        @click="selectRange(option)"
                    >
                        {{ rangeLabels[option] ?? option }}
                    </button>
                </div>

                <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
                    <!-- Events -->
                    <div class="rounded-2xl border border-white/60 bg-white/80 p-6 shadow-sm backdrop-blur-sm dark:border-[#2e3039] dark:bg-[#1e2028]/90">
                        <h3 class="mb-4 text-sm font-bold tracking-wider text-gray-500 uppercase dark:text-gray-400">Events</h3>
                        <p v-if="props.funnel.length === 0" class="text-sm text-gray-400">No events in this period.</p>
                        <table v-else class="w-full text-sm">
                            <thead>
                                <tr class="text-xs text-gray-400">
                                    <th class="pb-2 text-left font-medium">Event</th>
                                    <th class="pb-2 text-right font-medium">Browser</th>
                                    <th class="pb-2 text-right font-medium">Server</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50 dark:divide-[#2e3039]">
                                <tr v-for="row in props.funnel" :key="row.event_name">
                                    <td class="py-2 font-medium text-gray-800 dark:text-gray-200">{{ row.event_name }}</td>
                                    <td class="py-2 text-right tabular-nums text-gray-600 dark:text-gray-400">
                                        {{ row.browser.toLocaleString() }}
                                    </td>
                                    <td class="py-2 text-right tabular-nums text-gray-600 dark:text-gray-400">
                                        {{ row.server.toLocaleString() }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- CAPI -->
                    <div class="rounded-2xl border border-white/60 bg-white/80 p-6 shadow-sm backdrop-blur-sm dark:border-[#2e3039] dark:bg-[#1e2028]/90">
                        <h3 class="mb-4 text-sm font-bold tracking-wider text-gray-500 uppercase dark:text-gray-400">Conversions API</h3>
                        <dl class="space-y-3 text-sm">
                            <div class="flex items-center justify-between">
                                <dt class="text-gray-600 dark:text-gray-400">Sent</dt>
                                <dd class="font-bold tabular-nums text-emerald-600">{{ props.capi.sent.toLocaleString() }}</dd>
                            </div>
                            <div class="flex items-center justify-between">
                                <dt class="text-gray-600 dark:text-gray-400">Pending</dt>
                                <dd class="font-bold tabular-nums text-amber-600">{{ props.capi.pending.toLocaleString() }}</dd>
                            </div>
                            <div class="flex items-center justify-between">
                                <dt class="text-gray-600 dark:text-gray-400">Failed</dt>
                                <dd class="font-bold tabular-nums text-rose-600">{{ props.capi.failed.toLocaleString() }}</dd>
                            </div>
                        </dl>
                    </div>

                    <!-- Deduplication -->
                    <div class="rounded-2xl border border-white/60 bg-white/80 p-6 shadow-sm backdrop-blur-sm dark:border-[#2e3039] dark:bg-[#1e2028]/90">
                        <h3 class="mb-4 text-sm font-bold tracking-wider text-gray-500 uppercase dark:text-gray-400">Deduplication</h3>
                        <dl class="space-y-3 text-sm">
                            <div class="flex items-center justify-between">
                                <dt class="text-gray-600 dark:text-gray-400">Browser</dt>
                                <dd class="font-bold tabular-nums text-gray-900 dark:text-gray-100">{{ props.dedup.browser.toLocaleString() }}</dd>
                            </div>
                            <div class="flex items-center justify-between">
                                <dt class="text-gray-600 dark:text-gray-400">Server</dt>
                                <dd class="font-bold tabular-nums text-gray-900 dark:text-gray-100">{{ props.dedup.server.toLocaleString() }}</dd>
                            </div>
                            <div class="flex items-center justify-between">
                                <dt class="text-gray-600 dark:text-gray-400">Matched</dt>
                                <dd class="font-bold tabular-nums text-gray-900 dark:text-gray-100">{{ props.dedup.matched.toLocaleString() }}</dd>
                            </div>
                            <div class="flex items-center justify-between border-t border-gray-100 pt-3 dark:border-[#2e3039]">
                                <dt class="font-semibold text-gray-700 dark:text-gray-300">Deduplicated</dt>
                                <dd class="font-bold tabular-nums text-indigo-600">{{ props.dedup.deduplicated.toLocaleString() }}</dd>
                            </div>
                        </dl>
                        <p class="mt-4 text-xs leading-relaxed text-gray-400">
                            Matched counts event ids Meta saw from both sides. Well below either figure means Meta is counting two events
                            where we intended one. Browser PageView carries no event id and is excluded here.
                        </p>
                    </div>
                </div>

                <!-- Recent events -->
                <div class="overflow-hidden rounded-2xl border border-white/60 bg-white/80 shadow-sm backdrop-blur-sm dark:border-[#2e3039] dark:bg-[#1e2028]/90">
                    <h3 class="border-b border-gray-100/80 px-6 py-5 text-lg font-bold text-gray-800 dark:border-[#2e3039] dark:text-gray-100">
                        Recent server events
                    </h3>

                    <p v-if="props.events.data.length === 0" class="px-6 py-12 text-center text-sm text-gray-400">
                        No server events in this period.
                    </p>

                    <table v-else class="w-full text-sm">
                        <thead class="bg-gray-50/60 text-xs text-gray-500 dark:bg-[#1a1d23] dark:text-gray-400">
                            <tr>
                                <th class="px-6 py-3 text-left font-medium">Event</th>
                                <th class="px-6 py-3 text-left font-medium">Event ID</th>
                                <th class="px-6 py-3 text-left font-medium">Status</th>
                                <th class="px-6 py-3 text-right font-medium">Attempts</th>
                                <th class="px-6 py-3 text-right font-medium"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 dark:divide-[#2e3039]">
                            <template v-for="event in props.events.data" :key="event.id">
                                <tr class="cursor-pointer hover:bg-gray-50/60 dark:hover:bg-[#1a1d23]" @click="expanded = expanded === event.id ? null : event.id">
                                    <td class="px-6 py-3 font-medium text-gray-800 dark:text-gray-200">{{ event.event_name }}</td>
                                    <td class="px-6 py-3 font-mono text-xs text-gray-500 dark:text-gray-400">{{ event.event_id }}</td>
                                    <td class="px-6 py-3">
                                        <span :class="['rounded-full px-2.5 py-0.5 text-xs font-bold uppercase', statusStyles[event.status]]">
                                            {{ event.status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-3 text-right tabular-nums text-gray-600 dark:text-gray-400">{{ event.attempts }}</td>
                                    <td class="px-6 py-3 text-right">
                                        <button
                                            v-if="event.status === 'failed'"
                                            type="button"
                                            class="rounded-lg bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-600 transition hover:bg-indigo-100 dark:bg-indigo-500/10 dark:text-indigo-400"
                                            @click.stop="retry(event)"
                                        >
                                            Retry
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="expanded === event.id" class="bg-gray-50/60 dark:bg-[#1a1d23]">
                                    <td colspan="5" class="px-6 py-4">
                                        <p v-if="event.last_error" class="mb-3 text-xs font-medium text-rose-600">{{ event.last_error }}</p>
                                        <pre class="max-h-80 overflow-auto rounded-xl bg-gray-900 p-4 text-xs leading-relaxed text-gray-100">{{
                                            JSON.stringify(event.payload, null, 2)
                                        }}</pre>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>

                    <div v-if="props.events.links.length > 3" class="flex flex-wrap gap-1 border-t border-gray-100/80 px-6 py-4 dark:border-[#2e3039]">
                        <template v-for="link in props.events.links" :key="link.label">
                            <span v-if="!link.url" class="px-3 py-1 text-sm text-gray-300" v-html="link.label" />
                            <Link
                                v-else
                                :href="link.url"
                                preserve-scroll
                                :class="[
                                    'rounded-lg px-3 py-1 text-sm',
                                    link.active ? 'bg-indigo-600 text-white' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-300',
                                ]"
                                v-html="link.label"
                            />
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
```

- [ ] **Step 2: Link it from the admin dashboard**

In `resources/js/Pages/Admin/Dashboard.vue`, directly after the closing `</div>` of the existing "View All Vendors Link" block, add a matching card:

```vue
                <div class="mb-8">
                    <Link
                        :href="route('admin.tracking.index')"
                        class="glass-card group flex items-center justify-between rounded-2xl border border-white/60 bg-white/80 px-6 py-4 shadow-sm backdrop-blur-sm transition-all duration-200 hover:shadow-md dark:border-[#2e3039] dark:bg-[#1e2028]/90"
                    >
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-sky-500 to-indigo-500 shadow-lg shadow-sky-200/50">
                                <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                                </svg>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-900 transition-colors group-hover:text-indigo-600 dark:text-gray-100 dark:group-hover:text-indigo-400">
                                    Meta Tracking
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Funnel events, Conversions API health and deduplication</p>
                            </div>
                        </div>
                        <svg class="h-5 w-5 text-gray-300 transition-transform duration-200 group-hover:translate-x-0.5 group-hover:text-indigo-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </Link>
                </div>
```

`Link` is already imported in that file, so no import change is needed.

- [ ] **Step 3: Lint, format, and build**

Run: `npx eslint resources/js/Pages/Admin/Tracking/Index.vue resources/js/Pages/Admin/Dashboard.vue --fix`
Expected: no errors. ESLint enforces type-imports and import ordering — if it complains about the `@inertiajs/vue3` import, follow what it says.

Run: `npx prettier --write resources/js/Pages/Admin/Tracking/Index.vue`

Run: `npm run build`
Expected: build succeeds with no TypeScript errors.

- [ ] **Step 4: Commit**

```bash
git add resources/js/Pages/Admin/Tracking/Index.vue resources/js/Pages/Admin/Dashboard.vue
git commit -m "feat: add the admin Meta tracking dashboard page"
```

---

### Task 9: Full verification

Passing tests are not "done" for tracking work — `docs/meta-tracking.md` §8 requires seeing it in Events Manager.

- [ ] **Step 1: Whole suite green**

Run: `php artisan test --no-coverage`
Expected: every test passes. The 32 pre-existing Meta tests must be unaffected.

- [ ] **Step 2: Formatters clean**

Run: `vendor/bin/pint --dirty --format agent`
Run: `npx eslint . --fix`
Expected: no outstanding issues.

- [ ] **Step 3: Seed some real events and read the page**

```bash
composer run dev
```

Then, in a browser profile with **no ad blocker** (an extension that blocks `fbevents.js` makes `pixelLoaded()` false, so no browser rows are written and the Browser column stays at zero — that is correct behaviour, not a bug):

1. Sign in as a customer, browse a product, add it to the cart, go to checkout, pick a payment method, place the order.
2. In DevTools → Network, confirm a `POST /meta/browser-event` returns **204** for each fire — `PageView`, `AddToCart`, `AddPaymentInfo`, `Purchase`.
3. Sign in as an admin and open `/admin/tracking`.

Confirm on the page:
- The Events column lists `PageView`, `AddToCart`, `AddPaymentInfo`, `Purchase` in that order.
- CAPI shows the queued events as `sent` (the queue worker is running under `composer run dev`).
- Deduplication shows `matched` equal to the number of dual-sent events, and `browser`, `server`, `deduplicated` agreeing.
- Clicking a row reveals the JSON payload; the `user_data.em` value is a hash, never a raw address.
- Switching the range tabs changes the numbers.

- [ ] **Step 4: Verify a retry actually works**

```bash
php artisan tinker --execute="\App\Models\MetaEvent::latest('id')->first()->markFailed('forced failure for retry test');"
```

Reload `/admin/tracking`, click **Retry** on that row, and confirm it moves to `pending` and then `sent`.

- [ ] **Step 5: Verify in Meta Events Manager**

With `META_CAPI_TEST_EVENT_CODE` set, open Events Manager → Test Events and confirm for the `Purchase`:
- Both a browser and a server event arrive.
- They are **deduplicated**, not counted twice — the shared `order_{id}` is what does this.
- Event Match Quality is reported, with no diagnostics warning about raw PII.

Cross-check the dashboard's `deduplicated` figure against what Events Manager reports. A mismatch means our dedup definition is wrong and needs fixing before this is done.

- [ ] **Step 6: Commit any fixes and finish the branch**

```bash
git add -A
git commit -m "fix: <whatever verification turned up>"
```

Then use superpowers:finishing-a-development-branch.

---

## Out of scope

Left undone deliberately, per the design doc:

- `ViewContent` and `InitiateCheckout` — the dashboard omits their rows until they ship.
- UTM and fbclid attribution (deliverables 6 and 7).
- Meta Marketing API / ROAS (`docs/meta-tracking.md` §10).
