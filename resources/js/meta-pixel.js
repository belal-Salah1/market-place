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
