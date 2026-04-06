<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

interface OrderItem {
    id: number;
    quantity: number;
    price: number;
    product: { id: number; name: string; image: string | null };
}

interface Order {
    id: number;
    total_price: number;
    status: string;
    created_at: string;
    customer: { id: number; name: string };
    items: OrderItem[];
    payment: { method: string; status: string } | null;
}

defineProps<{
    orders: Order[];
}>();

const statusColors: Record<string, { badge: string }> = {
    pending: { badge: 'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400' },
    processing: { badge: 'bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400' },
    shipped: { badge: 'bg-violet-50 text-violet-600 dark:bg-violet-500/10 dark:text-violet-400' },
    delivered: { badge: 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400' },
    cancelled: { badge: 'bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400' },
};

function formatDate(dateStr: string): string {
    return new Date(dateStr).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
}
</script>

<template>
    <Head title="My Orders" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link
                    :href="route('vendor.dashboard')"
                    class="flex h-9 w-9 items-center justify-center rounded-xl border border-gray-200 bg-white text-gray-500 transition-all hover:border-indigo-200 hover:text-indigo-600 dark:border-[#2e3039] dark:bg-[#1e2028]/90 dark:text-gray-400 dark:hover:border-indigo-500/30 dark:hover:text-indigo-400"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                </Link>
                <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-100" data-gsap="fade-up">Orders</h2>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Orders list -->
                <div v-if="orders.length" class="space-y-4">
                    <Link
                        v-for="order in orders"
                        :key="order.id"
                        :href="route('vendor.orders.show', order.id)"
                        class="group block rounded-2xl border border-white/60 bg-white/80 p-5 shadow-sm backdrop-blur-sm transition-all duration-200 hover:shadow-md dark:border-[#2e3039] dark:bg-[#1e2028]/90"
                        data-gsap="fade-up"
                    >
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <div class="flex items-center gap-4">
                                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-gray-100 text-sm font-bold text-gray-600 dark:bg-gray-700/50 dark:text-gray-300">
                                    #{{ order.id }}
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-800 group-hover:text-indigo-600 dark:text-gray-100 dark:group-hover:text-indigo-400">
                                        {{ order.customer.name }}
                                    </p>
                                    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                                        {{ formatDate(order.created_at) }}
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center gap-4 sm:gap-6">
                                <div class="text-center">
                                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Items</p>
                                    <p class="text-sm font-bold text-gray-800 dark:text-gray-100">{{ order.items.length }}</p>
                                </div>

                                <div v-if="order.payment" class="text-center">
                                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Payment</p>
                                    <p class="text-sm font-semibold capitalize text-gray-800 dark:text-gray-100">{{ order.payment.method }}</p>
                                </div>

                                <span
                                    :class="[
                                        'rounded-full px-2.5 py-0.5 text-xs font-semibold capitalize',
                                        statusColors[order.status]?.badge ?? 'bg-gray-50 text-gray-600 dark:bg-gray-500/10 dark:text-gray-400',
                                    ]"
                                >
                                    {{ order.status }}
                                </span>

                                <span class="min-w-[5rem] text-right text-base font-bold text-gray-800 dark:text-gray-100">
                                    ${{ order.total_price }}
                                </span>

                                <svg
                                    class="h-5 w-5 text-gray-300 transition-transform duration-200 group-hover:translate-x-0.5 group-hover:text-indigo-400 dark:text-gray-500"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                    stroke-width="1.5"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                </svg>
                            </div>
                        </div>
                    </Link>
                </div>

                <!-- Empty state -->
                <div
                    v-else
                    class="flex flex-col items-center justify-center rounded-2xl border border-white/60 bg-white/80 py-16 shadow-sm backdrop-blur-sm dark:border-[#2e3039] dark:bg-[#1e2028]/90"
                    data-gsap="fade-up"
                >
                    <svg class="mb-4 h-16 w-16 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
                        />
                    </svg>
                    <p class="text-base font-semibold text-gray-500 dark:text-gray-400">No orders yet</p>
                    <p class="mt-1 text-sm text-gray-400 dark:text-gray-500">Orders will appear here once customers purchase your products.</p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
