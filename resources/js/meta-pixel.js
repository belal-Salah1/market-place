import { router } from '@inertiajs/vue3';

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
 * Whether this page carries the Pixel at all. The base snippet is an inline script
 * that defines the stub during parsing, before this deferred module runs, so an
 * undefined `fbq` here means the snippet was never rendered — admin and vendor
 * traffic is held out of reporting, and no pixel id is configured. Nothing will
 * ever load, so there is no point waiting for it.
 */
function pixelPresent() {
    return typeof window.fbq === 'function';
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

    // Polling would burn four seconds on every admin and vendor page load.
    if (!pixelPresent() || attempt >= 20) {
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
        window.axios?.post(route('meta.browser-event'), { event_name: eventName, event_id: eventId }).catch(() => {});
    });
}

/**
 * The Pixel base snippet fires PageView for the initial document, and Inertia
 * fires `navigate` for that same first page — so skip that one and only
 * re-track the client-side visits that follow.
 */
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

/**
 * The server flashes a tracking payload after actions worth reporting and mints
 * the `event_id` itself, so the Pixel and the Conversions API send the same id
 * and Meta merges the pair instead of counting two events.
 *
 * The initial page carries a flashed event whenever the action ended in a full
 * page load rather than an Inertia visit (Google sign-up, for one), and `success`
 * never fires for it — so fire that one directly.
 */
export function trackFlashedEvents(initialPage) {
    const fired = new Set();

    const fire = (meta) => {
        if (!meta || fired.has(meta.event_id)) {
            return;
        }

        fired.add(meta.event_id);

        window.fbq?.('track', meta.name, meta.params, { eventID: meta.event_id });
        beacon(meta.name, meta.event_id);
    };

    fire(initialPage?.props?.metaEvent);

    router.on('success', (event) => fire(event.detail.page.props.metaEvent));
}
