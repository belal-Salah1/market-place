# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

A multi-role marketplace application (admin, vendor, customer) built with Laravel 12, Inertia.js v2, Vue 3, and Tailwind CSS. Users have role-based dashboards and access controls. Vendors create products, customers place orders and leave reviews, admins approve vendors and view reports.

## Common Commands

### Development
```bash
composer run dev          # Starts server, queue, logs, and Vite concurrently
```

### Testing
```bash
php artisan test --compact                        # Run all tests
php artisan test --compact --filter=testName       # Run specific test
php artisan test --compact tests/Unit/OrderReportTest.php  # Run specific file
```
Tests use Pest 4 with PHPUnit 12. Test DB: in-memory SQLite (set in `phpunit.xml`) so `RefreshDatabase` can never wipe the development `market` database. Unit tests are in `tests/Unit/`, feature tests in `tests/Feature/`.

Because SQLite and MySQL do not guarantee the same row order, assert unordered query results with `toEqualCanonicalizing`, not `toBe`.

### Linting & Formatting
```bash
vendor/bin/pint --dirty --format agent   # PHP formatting (Laravel preset)
npx eslint . --fix                        # JS/TS/Vue linting
npx prettier --write resources/           # Frontend formatting
```

## Architecture

### Backend (Laravel 12)
- **Roles & Auth**: Role-based system using `RoleStatus` enum and `role` middleware. Three roles: admin, vendor, customer. Vendor approval flow via `VendorApproveStatus` middleware.
- **Middleware** (registered in `bootstrap/app.php`): `role` (role gating), `redirect.dashboard` (role-based redirect), `vendor.approval` (blocks unapproved vendors).
- **Services** (`app/Services/`): Report services encapsulate query logic — `OrderReportsService`, `ProductReportsServices`, `CategoryReportService`, `CustomersReportService`, `PaymentReportService`, `ReviewsReportService`, `RoleAssignmentService`.
- **Report Controllers**: Dedicated controllers per report domain (orders, products, categories, customers, payments, reviews) delegate to service classes.
- **Enums** (`app/Enums/`): `RoleStatus`, `OrderStatus`, `PaymentMethodStatus`, `PaymentStatus`.

### Frontend (Vue 3 + Inertia v2)
- Pages in `resources/js/Pages/` organized by role: `Admin/`, `Vendor/`, `Customer/`, plus shared `Auth/`, `Profile/`.
- Uses Wayfinder for type-safe route generation — import from `@/actions/` or `@/routes/`.
- TypeScript with Vite bundling (`vite.config.ts`).
- ESLint enforces type-imports and import ordering.

### Database
MySQL with models: User, Role, Category (hierarchical via parent_id), Product, Order, OrderItem, Payment, Review. Products belong to vendors, orders belong to customers, commissions track vendor earnings.

## Key Conventions
- Use `php artisan make:*` commands with `--no-interaction` to create files.
- Form Request classes for validation (not inline in controllers).
- Prefer Eloquent over `DB::` facade; use eager loading to prevent N+1.
- Run `vendor/bin/pint --dirty --format agent` before finalizing PHP changes.
- Every change should be covered by tests (Pest).

## Current Focus: Meta Tracking

Active work is a full Meta (Facebook/Instagram) tracking system: Pixel (browser) + Conversions API (server) with `event_id` deduplication, UTM/fbclid attribution, queued + retried CAPI jobs backed by a `meta_events` table, and an admin tracking dashboard.

**Read `docs/meta-tracking.md` before touching anything tracking-related.** Non-negotiables from it:
- Fire CAPI `Purchase` from the backend after payment confirmation (webhook), never on the "Pay" click.
- Every dual-sent event carries a deterministic `event_id` (e.g. `order_9843`) on both Pixel and CAPI.
- CAPI calls go through a queued job with retries; a Meta outage must never fail an order.
- Hash PII (email, phone) with SHA-256 after normalizing; never send raw.
- Prefer Meta standard events over custom ones, and always send `value`/`currency`/`content_ids`.
- Verify in Events Manager (Test Events, Event Match Quality, deduplication) — passing tests alone is not "done".
