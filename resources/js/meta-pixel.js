import { router } from '@inertiajs/vue3';

/**
 * The Pixel base snippet fires PageView on the initial document load only.
 * Inertia visits swap pages without a reload, so re-track them here.
 */
export function trackInertiaPageViews() {
    router.on('navigate', () => window.fbq?.('track', 'PageView'));
}
