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
    };

    fire(initialPage?.props?.metaEvent);

    router.on('success', (event) => fire(event.detail.page.props.metaEvent));
}
