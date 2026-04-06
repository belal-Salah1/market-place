<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

interface TopProduct {
    product_id: number;
    revenue: number;
    units_sold: number;
    product: { id: number; name: string };
}

interface RecentSale {
    id: number;
    quantity: number;
    price: number;
    product: { name: string };
    order: { id: number; customer: { name: string } };
}

defineProps<{
    totalEarnings: number;
    monthlyEarnings: number;
    topProducts: TopProduct[];
    recentSales: RecentSale[];
}>();
</script>

<template>
    <Head title="Earnings" />

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
                <h2 class="text-xl font-bold text-gray-800 dark:text-gray-100">Earnings</h2>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Earnings Cards -->
                <div class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div
                        class="glass-card rounded-2xl border border-white/60 bg-white/80 p-6 shadow-sm backdrop-blur-sm dark:border-[#2e3039] dark:bg-[#1e2028]/90"
                        data-gsap="fade-up"
                    >
                        <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 dark:bg-emerald-500/10">
                            <svg class="h-5 w-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Earnings</p>
                        <p class="mt-0.5 text-2xl font-bold text-emerald-600">${{ Number(totalEarnings).toFixed(2) }}</p>
                    </div>

                    <div
                        class="glass-card rounded-2xl border border-white/60 bg-white/80 p-6 shadow-sm backdrop-blur-sm dark:border-[#2e3039] dark:bg-[#1e2028]/90"
                        data-gsap="fade-up"
                        data-gsap-delay="0.08"
                    >
                        <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 dark:bg-blue-500/10">
                            <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">This Month</p>
                        <p class="mt-0.5 text-2xl font-bold text-blue-600">${{ Number(monthlyEarnings).toFixed(2) }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
                    <!-- Top Products -->
                    <div
                        class="rounded-2xl border border-white/60 bg-white/80 p-6 shadow-sm backdrop-blur-sm dark:border-[#2e3039] dark:bg-[#1e2028]/90"
                        data-gsap="fade-up"
                        data-gsap-delay="0.15"
                    >
                        <div class="mb-6 flex items-center gap-2.5">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-100 dark:bg-amber-500/10">
                                <svg class="h-4 w-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                                </svg>
                            </div>
                            <h3 class="text-sm font-bold text-gray-800 dark:text-gray-100">Top Products</h3>
                        </div>

                        <div v-if="topProducts.length" class="space-y-3">
                            <div
                                v-for="(item, index) in topProducts"
                                :key="item.product_id"
                                class="flex items-center justify-between rounded-xl border border-gray-100/80 bg-white/60 px-4 py-3 dark:border-[#2e3039] dark:bg-[#1a1d23]/60"
                            >
                                <div class="flex items-center gap-3 min-w-0">
                                    <span class="flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-lg bg-gray-100 text-xs font-bold text-gray-500 dark:bg-[#252830] dark:text-gray-400">
                                        {{ index + 1 }}
                                    </span>
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-semibold text-gray-800 dark:text-gray-100">{{ item.product.name }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ item.units_sold }} units sold</p>
                                    </div>
                                </div>
                                <span class="flex-shrink-0 text-sm font-bold text-emerald-600">${{ Number(item.revenue).toFixed(2) }}</span>
                            </div>
                        </div>

                        <div v-else class="flex flex-col items-center justify-center rounded-xl bg-gray-50/80 py-10 dark:bg-[#1a1d23]/80">
                            <svg class="mb-3 h-10 w-10 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">No product data yet</p>
                        </div>
                    </div>

                    <!-- Recent Sales -->
                    <div
                        class="rounded-2xl border border-white/60 bg-white/80 p-6 shadow-sm backdrop-blur-sm dark:border-[#2e3039] dark:bg-[#1e2028]/90"
                        data-gsap="fade-up"
                        data-gsap-delay="0.22"
                    >
                        <div class="mb-6 flex items-center gap-2.5">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-100 dark:bg-indigo-500/10">
                                <svg class="h-4 w-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                </svg>
                            </div>
                            <h3 class="text-sm font-bold text-gray-800 dark:text-gray-100">Recent Sales</h3>
                        </div>

                        <div v-if="recentSales.length" class="space-y-3">
                            <div
                                v-for="sale in recentSales"
                                :key="sale.id"
                                class="flex items-center justify-between rounded-xl border border-gray-100/80 bg-white/60 px-4 py-3 dark:border-[#2e3039] dark:bg-[#1a1d23]/60"
                            >
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-gray-800 dark:text-gray-100">{{ sale.product.name }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ sale.order.customer.name }} &middot; Qty: {{ sale.quantity }} &middot; Order #{{ sale.order.id }}
                                    </p>
                                </div>
                                <span class="flex-shrink-0 text-sm font-bold text-gray-900 dark:text-gray-100">${{ Number(sale.price * sale.quantity).toFixed(2) }}</span>
                            </div>
                        </div>

                        <div v-else class="flex flex-col items-center justify-center rounded-xl bg-gray-50/80 py-10 dark:bg-[#1a1d23]/80">
                            <svg class="mb-3 h-10 w-10 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">No sales yet</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
