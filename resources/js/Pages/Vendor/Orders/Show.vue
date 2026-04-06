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
    customer: { id: number; name: string; email: string };
    items: OrderItem[];
    payment: { amount: number; method: string; status: string } | null;
}

defineProps<{
    order: Order;
}>();

const statusColors: Record<string, { badge: string }> = {
    pending: { badge: 'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400' },
    processing: { badge: 'bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400' },
    shipped: { badge: 'bg-violet-50 text-violet-600 dark:bg-violet-500/10 dark:text-violet-400' },
    delivered: { badge: 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400' },
    cancelled: { badge: 'bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400' },
};

const paymentStatusColors: Record<string, string> = {
    paid: 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400',
    pending: 'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400',
    failed: 'bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400',
    refunded: 'bg-violet-50 text-violet-600 dark:bg-violet-500/10 dark:text-violet-400',
};

function formatDate(dateStr: string): string {
    return new Date(dateStr).toLocaleDateString('en-US', {
        month: 'long',
        day: 'numeric',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}
</script>

<template>
    <Head :title="`Order #${order.id}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Link
                        :href="route('vendor.orders.index')"
                        class="flex h-9 w-9 items-center justify-center rounded-xl border border-gray-200 bg-white text-gray-500 transition-all hover:border-indigo-200 hover:text-indigo-600 dark:border-[#2e3039] dark:bg-[#1e2028]/90 dark:text-gray-400 dark:hover:border-indigo-500/30 dark:hover:text-indigo-400"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                        </svg>
                    </Link>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-100" data-gsap="fade-up">Order #{{ order.id }}</h2>
                        <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">{{ formatDate(order.created_at) }}</p>
                    </div>
                </div>
                <span
                    :class="[
                        'rounded-full px-3 py-1 text-sm font-semibold capitalize',
                        statusColors[order.status]?.badge ?? 'bg-gray-50 text-gray-600 dark:bg-gray-500/10 dark:text-gray-400',
                    ]"
                >
                    {{ order.status }}
                </span>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
                    <!-- Main content -->
                    <div class="space-y-8 lg:col-span-2">
                        <!-- Order Items -->
                        <div
                            class="rounded-2xl border border-white/60 bg-white/80 p-6 shadow-sm backdrop-blur-sm dark:border-[#2e3039] dark:bg-[#1e2028]/90"
                            data-gsap="fade-up"
                        >
                            <h3 class="mb-5 text-lg font-bold text-gray-800 dark:text-gray-100">Order Items</h3>
                            <div class="space-y-3">
                                <div
                                    v-for="item in order.items"
                                    :key="item.id"
                                    class="flex items-center justify-between rounded-xl border border-gray-100/80 bg-white/60 p-4 dark:border-[#2e3039] dark:bg-[#1a1d23]/60"
                                >
                                    <div class="flex items-center gap-4">
                                        <div
                                            v-if="item.product.image"
                                            class="h-12 w-12 flex-shrink-0 overflow-hidden rounded-lg"
                                        >
                                            <img :src="item.product.image" :alt="item.product.name" class="h-full w-full object-cover" />
                                        </div>
                                        <div
                                            v-else
                                            class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-700/50"
                                        >
                                            <svg class="h-5 w-5 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-gray-800 dark:text-gray-100">{{ item.product.name }}</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                                Qty: {{ item.quantity }} &times; ${{ item.price }}
                                            </p>
                                        </div>
                                    </div>
                                    <p class="text-sm font-bold text-gray-800 dark:text-gray-100">
                                        ${{ (item.quantity * item.price).toFixed(2) }}
                                    </p>
                                </div>
                            </div>

                            <!-- Total -->
                            <div class="mt-5 flex items-center justify-between border-t border-gray-100 pt-5 dark:border-[#2e3039]">
                                <p class="text-sm font-semibold text-gray-500 dark:text-gray-400">Order Total</p>
                                <p class="text-xl font-bold text-gray-800 dark:text-gray-100">${{ order.total_price }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-6">
                        <!-- Customer Info -->
                        <div
                            class="rounded-2xl border border-white/60 bg-white/80 p-6 shadow-sm backdrop-blur-sm dark:border-[#2e3039] dark:bg-[#1e2028]/90"
                            data-gsap="fade-up"
                            data-gsap-delay="0.1"
                        >
                            <h3 class="mb-4 text-lg font-bold text-gray-800 dark:text-gray-100">Customer</h3>
                            <div class="space-y-3">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-50 dark:bg-indigo-500/10">
                                        <svg class="h-5 w-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-gray-800 dark:text-gray-100">{{ order.customer.name }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ order.customer.email }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Info -->
                        <div
                            class="rounded-2xl border border-white/60 bg-white/80 p-6 shadow-sm backdrop-blur-sm dark:border-[#2e3039] dark:bg-[#1e2028]/90"
                            data-gsap="fade-up"
                            data-gsap-delay="0.2"
                        >
                            <h3 class="mb-4 text-lg font-bold text-gray-800 dark:text-gray-100">Payment</h3>
                            <div v-if="order.payment" class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Method</p>
                                    <p class="text-sm font-semibold capitalize text-gray-800 dark:text-gray-100">{{ order.payment.method }}</p>
                                </div>
                                <div class="flex items-center justify-between">
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Amount</p>
                                    <p class="text-sm font-semibold text-gray-800 dark:text-gray-100">${{ order.payment.amount }}</p>
                                </div>
                                <div class="flex items-center justify-between">
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Status</p>
                                    <span
                                        :class="[
                                            'rounded-full px-2.5 py-0.5 text-xs font-semibold capitalize',
                                            paymentStatusColors[order.payment.status] ?? 'bg-gray-50 text-gray-600 dark:bg-gray-500/10 dark:text-gray-400',
                                        ]"
                                    >
                                        {{ order.payment.status }}
                                    </span>
                                </div>
                            </div>
                            <div v-else class="text-center">
                                <p class="text-sm text-gray-500 dark:text-gray-400">No payment information available</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
