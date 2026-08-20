import { router } from '@inertiajs/vue3';

/**
 * The Pixel base snippet fires PageView for the initial document, and Inertia
 * fires `navigate` for that same first page — so skip that one and only
 * re-track the client-side visits that follow.
 */
export function trackInertiaPageViews() {
    let isInitialVisit = true;

    router.on('navigate', () => {
        if (isInitialVisit) {
            isInitialVisit = false;

            return;
        }

        window.fbq?.('track', 'PageView');
    });
}

/**
 * The server flashes a tracking payload after actions worth reporting and mints
 * the `event_id` itself, so the Pixel and the Conversions API send the same id
 * and Meta merges the pair instead of counting two events.
 */
export function trackFlashedEvents() {
    const fired = new Set();

    router.on('success', (event) => {
        const meta = event.detail.page.props.metaEvent;

        if (!meta || fired.has(meta.event_id)) {
            return;
        }

        fired.add(meta.event_id);

        window.fbq?.('track', meta.name, meta.params, { eventID: meta.event_id });
    });
}
