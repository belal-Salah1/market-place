<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

interface Coupon {
    id: number;
    code: string;
    type: string;
    value: number;
    min_order_amount: number;
    usage_limit: number | null;
    times_used: number;
    is_active: boolean;
    expires_at: string | null;
}

defineProps<{
    coupons: Coupon[];
}>();

function formatValue(coupon: Coupon): string {
    return coupon.type === 'percentage' ? `${coupon.value}%` : `$${Number(coupon.value).toFixed(2)}`;
}

function formatDate(dateStr: string | null): string {
    if (!dateStr) return 'No expiry';
    return new Date(dateStr).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
}

function toggleStatus(couponId: number) {
    router.patch(route('vendor.coupons.toggle', couponId));
}

function deleteCoupon(couponId: number) {
    if (confirm('Are you sure you want to delete this coupon?')) {
        router.delete(route('vendor.coupons.destroy', couponId));
    }
}
</script>

<template>
    <Head title="My Coupons" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <Link
                        :href="route('vendor.dashboard')"
                        class="group flex h-8 w-8 items-center justify-center rounded-lg bg-white/60 text-gray-400 backdrop-blur-sm transition-all duration-200 hover:bg-indigo-50 hover:text-indigo-600 dark:bg-white/5 dark:text-gray-500 dark:hover:bg-indigo-500/10 dark:hover:text-indigo-400"
                    >
                        <svg class="h-4 w-4 transition-transform duration-200 group-hover:-translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </Link>
                    <h2 class="text-xl font-bold text-gray-800 dark:text-gray-100">My Coupons</h2>
                </div>
                <Link
                    :href="route('vendor.coupons.create')"
                    class="btn-sweep inline-flex items-center gap-1.5 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-indigo-700"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    Create Coupon
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Empty State -->
                <div
                    v-if="coupons.length === 0"
                    class="rounded-2xl border border-white/60 bg-white/80 px-6 py-16 text-center shadow-sm backdrop-blur-sm dark:border-[#2e3039] dark:bg-[#1e2028]/90"
                    data-gsap="fade-up"
                >
                    <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-gray-50 dark:bg-[#252830]">
                        <svg class="h-7 w-7 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                        </svg>
                    </div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">No coupons yet</p>
                    <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Create your first coupon to offer discounts to customers</p>
                    <Link
                        :href="route('vendor.coupons.create')"
                        class="mt-4 inline-flex items-center gap-1.5 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-indigo-700"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        Create Coupon
                    </Link>
                </div>

                <!-- Coupons Table -->
                <div
                    v-else
                    class="overflow-hidden rounded-2xl border border-white/60 bg-white/80 shadow-sm backdrop-blur-sm dark:border-[#2e3039] dark:bg-[#1e2028]/90"
                    data-gsap="fade-up"
                >
                    <div class="flex items-center justify-between border-b border-gray-100/80 px-6 py-5 dark:border-[#2e3039]">
                        <div class="flex items-center gap-2.5">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-violet-100 dark:bg-violet-500/10">
                                <svg class="h-4 w-4 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                </svg>
                            </div>
                            <h3 class="text-sm font-bold text-gray-800 dark:text-gray-100">All Coupons</h3>
                        </div>
                        <span class="rounded-full bg-violet-50 px-3 py-1 text-xs font-semibold text-violet-600 dark:bg-violet-500/10 dark:text-violet-400">
                            {{ coupons.length }} total
                        </span>
                    </div>

                    <div class="divide-y divide-gray-50 dark:divide-[#2e3039]">
                        <div
                            v-for="coupon in coupons"
                            :key="coupon.id"
                            class="flex flex-col gap-4 px-6 py-4 transition-colors hover:bg-gray-50/50 sm:flex-row sm:items-center sm:justify-between dark:hover:bg-white/5"
                        >
                            <div class="flex items-center gap-4 min-w-0">
                                <!-- Code Badge -->
                                <span class="inline-flex items-center rounded-lg bg-gray-100 px-3 py-1.5 font-mono text-sm font-bold text-gray-800 dark:bg-[#252830] dark:text-gray-200">
                                    {{ coupon.code }}
                                </span>
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2">
                                        <!-- Type Badge -->
                                        <span
                                            :class="[
                                                'rounded-full px-2.5 py-0.5 text-xs font-semibold capitalize',
                                                coupon.type === 'percentage'
                                                    ? 'bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400'
                                                    : 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400',
                                            ]"
                                        >
                                            {{ coupon.type }}
                                        </span>
                                        <!-- Value -->
                                        <span class="text-sm font-bold text-gray-900 dark:text-gray-100">
                                            {{ formatValue(coupon) }}
                                        </span>
                                    </div>
                                    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                                        Min order: ${{ Number(coupon.min_order_amount).toFixed(2) }}
                                        &middot;
                                        Usage: {{ coupon.times_used }}{{ coupon.usage_limit ? `/${coupon.usage_limit}` : ' (unlimited)' }}
                                        &middot;
                                        {{ formatDate(coupon.expires_at) }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 flex-shrink-0">
                                <!-- Status Toggle -->
                                <button
                                    @click="toggleStatus(coupon.id)"
                                    :class="[
                                        'rounded-lg px-3 py-1.5 text-xs font-semibold transition-colors',
                                        coupon.is_active
                                            ? 'bg-emerald-50 text-emerald-600 hover:bg-emerald-100 dark:bg-emerald-500/10 dark:text-emerald-400 dark:hover:bg-emerald-500/20'
                                            : 'bg-gray-100 text-gray-500 hover:bg-gray-200 dark:bg-gray-500/10 dark:text-gray-400 dark:hover:bg-gray-500/20',
                                    ]"
                                >
                                    {{ coupon.is_active ? 'Active' : 'Inactive' }}
                                </button>
                                <!-- Delete -->
                                <button
                                    @click="deleteCoupon(coupon.id)"
                                    class="rounded-lg bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-600 transition-colors hover:bg-red-100 dark:bg-red-500/10 dark:text-red-400 dark:hover:bg-red-500/20"
                                >
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
