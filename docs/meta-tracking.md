# Meta Tracking Integration — Project Guidelines

The current focus of this project: build a **complete Meta (Facebook/Instagram) tracking
system** on top of the marketplace — not just "drop a Pixel in the layout".

> Stack note: the original spec mentioned Angular for the frontend. This project is
> **Laravel 12 + Inertia v2 + Vue 3**, so all browser-side work lives in Vue/Inertia
> (see the Vue + Inertia rules in the global guidelines: no hand-rolled `fetch`/`axios`,
> no manual cookie/DOM access, use `router.*` / `useForm`).

## 1. Architecture

```
                    USER
                     │
                     ↓
                  Website
                     │
          ┌──────────┴──────────┐
          ↓                     ↓
    Meta Pixel (browser)   Laravel (server)
          │                     │
          ↓                     ↓
        Meta                  CAPI
          │                     │
          └──────────┬──────────┘
                     ↓
              Deduplication (event_id)
                     ↓
              Meta Events Manager
                     ↓
               Ads Optimization
```

- **Pixel** = browser-side tracking (JS).
- **Conversions API (CAPI)** = server-side tracking from Laravel.
- A professional setup runs **both**, deduplicated by `event_id`.

Setup path: Meta Business → Events Manager → Connect Data → Web → Meta Pixel → get Pixel ID.

## 2. Events

Prefer **standard events** over custom ones whenever one fits:

`PageView`, `ViewContent`, `Search`, `AddToCart`, `InitiateCheckout`,
`AddPaymentInfo`, `Purchase`, `Lead`, `CompleteRegistration`, `Contact`.

Custom events only when no standard event applies:
`fbq('trackCustom', 'CalculatorUsed', { calculator_type: 'mortgage' })`.

### Always send parameters, not bare events

```js
fbq('track', 'Purchase', {
    value: 1500,
    currency: 'EGP',
    content_ids: ['SKU-123'],
    content_type: 'product',
    num_items: 2,
}, { eventID: 'order_1001' });
```

`value`, `currency`, `content_ids`, `content_type`, `num_items` — Meta needs the metadata
to understand the conversion.

### E-commerce funnel we must be able to report on

```
PageView → ViewContent → AddToCart → InitiateCheckout → AddPaymentInfo → Purchase
```

## 3. Attribution capture (UTM + fbclid)

On every landing request, capture and persist:

```
utm_source, utm_medium, utm_campaign, utm_content, utm_term, fbclid
```

Store them on the session, then attach to the user and to the order:

```
User
├── first_touch { source, campaign }
├── fbclid
└── order_id
```

Also collect the browser identifiers `_fbp` (Pixel browser id) and `_fbc` (click id,
derived from `fbclid`) — CAPI uses them for event matching.

## 4. Why CAPI is required

Browser-only tracking loses events to ad blockers, tracking prevention, disabled JS and
network failures. The **backend is the source of truth for a purchase**: if the order is
PAID in Laravel, Meta must hear about it.

```
Payment gateway → webhook → Laravel → Order = PAID → dispatch job → CAPI Purchase
```

**Fire the CAPI purchase after payment confirmation (webhook/backend), never when the
user merely clicks "Pay".**

## 5. Deduplication

Browser Purchase + Server Purchase = 2 purchases in reporting unless they share an
`event_id`. Use a deterministic id:

```
event_id = order_{order_id}      // e.g. order_9843
```

Same `event_name` + same `event_id` on both sides → Meta merges them.

## 6. User data matching (CAPI)

Send matching signals with server events: `email`, `phone`, `external_id`,
`client_ip_address`, `client_user_agent`, `fbp`, `fbc`.

PII identifiers (email, phone) must be **normalized then SHA-256 hashed** before sending:

```
email → trim + lowercase → SHA-256 → Meta
```

Never send raw email/phone.

## 7. Queues, retries, and an events table

Never block the checkout response on an HTTP call to Meta:

```
Payment successful → create order → dispatch MetaPurchaseJob → respond to user
                                          ↓
                                    queue worker → Meta API (retry on failure)
```

If the Meta API is down, the order must not fail. Track every event in a `meta_events`
table so nothing is lost silently:

| column | purpose |
| --- | --- |
| `id` | pk |
| `event_name` | Purchase, AddToCart, … |
| `event_id` | dedup key (e.g. `order_9843`) |
| `order_id` | nullable relation |
| `payload` | full payload sent |
| `status` | pending / sent / failed |
| `attempts` | retry count |
| `last_error` | last failure message |
| `sent_at`, `created_at` | timing |

## 8. Verification is part of the work

"The code runs" is not done. Verify in **Events Manager**:

- Events received for every funnel step
- **Event Match Quality**
- Diagnostics warnings
- Deduplication (browser count ≈ server count ≈ deduplicated count)
- **Test Events** during development — use it instead of waiting on a real campaign

## 9. Deliverable scope

A mini e-commerce flow on this marketplace plus a full tracking system:

1. Product page → 2. Add to cart → 3. Checkout → 4. Payment (fake or real)
5. Meta Pixel → 6. UTM tracking → 7. fbclid tracking → 8. Purchase event
9. Meta CAPI → 10. `event_id` deduplication → 11. Queue → 12. Retry mechanism
13. `meta_events` table → 14. Admin tracking dashboard

Admin dashboard shape:

```
Events                     CAPI                  Deduplication
──────────────────         ────────────────      ────────────────
PageView     12,430        Sent        180       Browser      183
ViewContent   4,230        Failed        3       Server       183
AddToCart     1,230        Pending       0       Deduplicated 183
Checkout        540
Purchase        183
```

## 10. Later phase — Meta Marketing API

Separate from Pixel/CAPI: pull campaign / ad set / ad performance server-side
(spend, impressions, clicks, CTR, CPC, CPM, conversions) and compute ROAS
(`revenue / spend`) against our own order data.
