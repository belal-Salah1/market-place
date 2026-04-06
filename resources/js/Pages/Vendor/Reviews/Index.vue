<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

interface Review {
    id: number;
    rating: number;
    comment: string;
    created_at: string;
    product: { id: number; name: string };
    customer: { id: number; name: string };
}

defineProps<{
    reviews: Review[];
    avgRating: number | null;
}>();

function formatDate(dateStr: string): string {
    return new Date(dateStr).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
}
</script>

<template>
    <Head title="Reviews" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link
                    :href="route('vendor.dashboard')"
                    class="group flex h-8 w-8 items-center justify-center rounded-lg bg-white/60 text-gray-400 backdrop-blur-sm transition-all duration-200 hover:bg-indigo-50 hover:text-indigo-600 dark:bg-white/5 dark:text-gray-500 dark:hover:bg-indigo-500/10 dark:hover:text-indigo-400"
                >
                    <svg class="h-4 w-4 transition-transform duration-200 group-hover:-translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </Link>
                <h2 class="text-xl font-bold text-gray-800 dark:text-gray-100">Customer Reviews</h2>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Average Rating Card -->
                <div
                    class="mb-8 rounded-2xl border border-white/60 bg-white/80 p-6 shadow-sm backdrop-blur-sm dark:border-[#2e3039] dark:bg-[#1e2028]/90"
                    data-gsap="fade-up"
                >
                    <div class="flex items-center gap-4">
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-50 dark:bg-amber-500/10">
                            <svg class="h-7 w-7 text-amber-500" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Average Rating</p>
                            <div class="flex items-center gap-2">
                                <p class="text-2xl font-bold text-gray-800 dark:text-gray-100">
                                    {{ avgRating ? avgRating.toFixed(1) : 'N/A' }}
                                </p>
                                <!-- Stars -->
                                <div v-if="avgRating" class="flex items-center gap-0.5">
                                    <template v-for="star in 5" :key="star">
                                        <svg
                                            class="h-5 w-5"
                                            :class="star <= Math.round(avgRating!) ? 'text-amber-400' : 'text-gray-200 dark:text-gray-600'"
                                            fill="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                                        </svg>
                                    </template>
                                </div>
                                <span class="text-sm text-gray-500 dark:text-gray-400">
                                    ({{ reviews.length }} review{{ reviews.length !== 1 ? 's' : '' }})
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div
                    v-if="reviews.length === 0"
                    class="rounded-2xl border border-white/60 bg-white/80 px-6 py-16 text-center shadow-sm backdrop-blur-sm dark:border-[#2e3039] dark:bg-[#1e2028]/90"
                    data-gsap="fade-up"
                >
                    <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-gray-50 dark:bg-[#252830]">
                        <svg class="h-7 w-7 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                        </svg>
                    </div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">No reviews yet</p>
                    <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Reviews will appear here once customers leave feedback on your products.</p>
                </div>

                <!-- Reviews List -->
                <div v-else class="space-y-4">
                    <div
                        v-for="review in reviews"
                        :key="review.id"
                        class="rounded-2xl border border-white/60 bg-white/80 p-5 shadow-sm backdrop-blur-sm transition-colors hover:bg-white dark:border-[#2e3039] dark:bg-[#1e2028]/90 dark:hover:bg-[#1e2028]"
                        data-gsap="fade-up"
                    >
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0">
                                <div class="flex items-center gap-3">
                                    <!-- Customer avatar placeholder -->
                                    <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-full bg-indigo-100 text-sm font-bold text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                                        {{ review.customer.name.charAt(0).toUpperCase() }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-gray-800 dark:text-gray-100">{{ review.customer.name }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            on <span class="font-medium text-gray-700 dark:text-gray-300">{{ review.product.name }}</span>
                                        </p>
                                    </div>
                                </div>

                                <!-- Star Rating -->
                                <div class="mt-3 flex items-center gap-1">
                                    <template v-for="star in 5" :key="star">
                                        <svg
                                            class="h-4 w-4"
                                            :class="star <= review.rating ? 'text-amber-400' : 'text-gray-200 dark:text-gray-600'"
                                            fill="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                                        </svg>
                                    </template>
                                </div>

                                <!-- Comment -->
                                <p v-if="review.comment" class="mt-2.5 text-sm leading-relaxed text-gray-600 dark:text-gray-300">
                                    {{ review.comment }}
                                </p>
                            </div>

                            <!-- Date -->
                            <span class="flex-shrink-0 text-xs text-gray-400 dark:text-gray-500">
                                {{ formatDate(review.created_at) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
