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
                <h2 class="bg-gradient-to-r from-indigo-600 to-violet-600 bg-clip-text text-2xl font-bold text-transparent">Meta Tracking</h2>
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
                    <div
                        class="rounded-2xl border border-white/60 bg-white/80 p-6 shadow-sm backdrop-blur-sm dark:border-[#2e3039] dark:bg-[#1e2028]/90"
                    >
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
                                    <td class="py-2 text-right text-gray-600 tabular-nums dark:text-gray-400">
                                        {{ row.browser.toLocaleString() }}
                                    </td>
                                    <td class="py-2 text-right text-gray-600 tabular-nums dark:text-gray-400">
                                        {{ row.server.toLocaleString() }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- CAPI -->
                    <div
                        class="rounded-2xl border border-white/60 bg-white/80 p-6 shadow-sm backdrop-blur-sm dark:border-[#2e3039] dark:bg-[#1e2028]/90"
                    >
                        <h3 class="mb-4 text-sm font-bold tracking-wider text-gray-500 uppercase dark:text-gray-400">Conversions API</h3>
                        <dl class="space-y-3 text-sm">
                            <div class="flex items-center justify-between">
                                <dt class="text-gray-600 dark:text-gray-400">Sent</dt>
                                <dd class="font-bold text-emerald-600 tabular-nums">{{ props.capi.sent.toLocaleString() }}</dd>
                            </div>
                            <div class="flex items-center justify-between">
                                <dt class="text-gray-600 dark:text-gray-400">Pending</dt>
                                <dd class="font-bold text-amber-600 tabular-nums">{{ props.capi.pending.toLocaleString() }}</dd>
                            </div>
                            <div class="flex items-center justify-between">
                                <dt class="text-gray-600 dark:text-gray-400">Failed</dt>
                                <dd class="font-bold text-rose-600 tabular-nums">{{ props.capi.failed.toLocaleString() }}</dd>
                            </div>
                        </dl>
                    </div>

                    <!-- Deduplication -->
                    <div
                        class="rounded-2xl border border-white/60 bg-white/80 p-6 shadow-sm backdrop-blur-sm dark:border-[#2e3039] dark:bg-[#1e2028]/90"
                    >
                        <h3 class="mb-4 text-sm font-bold tracking-wider text-gray-500 uppercase dark:text-gray-400">Deduplication</h3>
                        <dl class="space-y-3 text-sm">
                            <div class="flex items-center justify-between">
                                <dt class="text-gray-600 dark:text-gray-400">Browser</dt>
                                <dd class="font-bold text-gray-900 tabular-nums dark:text-gray-100">{{ props.dedup.browser.toLocaleString() }}</dd>
                            </div>
                            <div class="flex items-center justify-between">
                                <dt class="text-gray-600 dark:text-gray-400">Server</dt>
                                <dd class="font-bold text-gray-900 tabular-nums dark:text-gray-100">{{ props.dedup.server.toLocaleString() }}</dd>
                            </div>
                            <div class="flex items-center justify-between">
                                <dt class="text-gray-600 dark:text-gray-400">Matched</dt>
                                <dd class="font-bold text-gray-900 tabular-nums dark:text-gray-100">{{ props.dedup.matched.toLocaleString() }}</dd>
                            </div>
                            <div class="flex items-center justify-between border-t border-gray-100 pt-3 dark:border-[#2e3039]">
                                <dt class="font-semibold text-gray-700 dark:text-gray-300">Deduplicated</dt>
                                <dd class="font-bold text-indigo-600 tabular-nums">{{ props.dedup.deduplicated.toLocaleString() }}</dd>
                            </div>
                        </dl>
                        <p class="mt-4 text-xs leading-relaxed text-gray-400">
                            Matched counts event ids Meta saw from both sides. Well below either figure means Meta is counting two events where we
                            intended one. Browser PageView carries no event id and is excluded here.
                        </p>
                    </div>
                </div>

                <!-- Recent events -->
                <div
                    class="overflow-hidden rounded-2xl border border-white/60 bg-white/80 shadow-sm backdrop-blur-sm dark:border-[#2e3039] dark:bg-[#1e2028]/90"
                >
                    <h3 class="border-b border-gray-100/80 px-6 py-5 text-lg font-bold text-gray-800 dark:border-[#2e3039] dark:text-gray-100">
                        Recent server events
                    </h3>

                    <p v-if="props.events.data.length === 0" class="px-6 py-12 text-center text-sm text-gray-400">No server events in this period.</p>

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
                                <tr
                                    class="cursor-pointer hover:bg-gray-50/60 dark:hover:bg-[#1a1d23]"
                                    @click="expanded = expanded === event.id ? null : event.id"
                                >
                                    <td class="px-6 py-3 font-medium text-gray-800 dark:text-gray-200">{{ event.event_name }}</td>
                                    <td class="px-6 py-3 font-mono text-xs text-gray-500 dark:text-gray-400">{{ event.event_id }}</td>
                                    <td class="px-6 py-3">
                                        <span :class="['rounded-full px-2.5 py-0.5 text-xs font-bold uppercase', statusStyles[event.status]]">
                                            {{ event.status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-3 text-right text-gray-600 tabular-nums dark:text-gray-400">{{ event.attempts }}</td>
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

                    <div
                        v-if="props.events.links.length > 3"
                        class="flex flex-wrap gap-1 border-t border-gray-100/80 px-6 py-4 dark:border-[#2e3039]"
                    >
                        <!-- Keyed by index, not label: a long paginator emits several "..." labels. -->
                        <template v-for="(link, index) in props.events.links" :key="index">
                            <span v-if="!link.url" class="px-3 py-1 text-sm text-gray-300" v-html="link.label" />
                            <Link
                                v-else
                                :href="link.url"
                                preserve-scroll
                                :class="[
                                    'rounded-lg px-3 py-1 text-sm',
                                    link.active ? 'bg-indigo-600 text-white' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-300',
                                ]"
                            >
                                <span v-html="link.label" />
                            </Link>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
