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
    items: OrderItem[];
    payment: { method: string; status: string } | null;
}

defineProps<{
    orders: Order[];
}>();

function formatPrice(price: number): string {
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(price);
}

function formatDate(dateString: string): string {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
}

function statusClasses(status: string): string {
    const map: Record<string, string> = {
        pending: 'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400',
        processing: 'bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400',
        shipped: 'bg-violet-50 text-violet-600 dark:bg-violet-500/10 dark:text-violet-400',
        delivered: 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400',
        cancelled: 'bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400',
    };
    return map[status] ?? 'bg-gray-50 text-gray-600 dark:bg-gray-500/10 dark:text-gray-400';
}

function formatPaymentMethod(method: string): string {
    return method
        .replace(/_/g, ' ')
        .replace(/\b\w/g, (c) => c.toUpperCase());
}
</script>

<template>
    <Head title="My Orders" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link
                    :href="route('customer.dashboard')"
                    class="group flex h-8 w-8 items-center justify-center rounded-lg bg-white/60 text-gray-400 backdrop-blur-sm transition-all duration-200 hover:bg-indigo-50 hover:text-indigo-600 dark:bg-white/5 dark:text-gray-500 dark:hover:bg-indigo-500/10 dark:hover:text-indigo-400"
                >
                    <svg
                        class="h-4 w-4 transition-transform duration-200 group-hover:-translate-x-0.5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </Link>
                <h2 class="text-xl font-bold text-gray-800 dark:text-gray-100">My Orders</h2>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8" data-gsap="fade-up">
                <!-- Empty State -->
                <div
                    v-if="orders.length === 0"
                    class="rounded-2xl border border-white/60 bg-white/80 px-6 py-16 text-center shadow-sm backdrop-blur-sm dark:border-[#2e3039] dark:bg-[#1e2028]/90"
                >
                    <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-gray-50 dark:bg-[#1a1d23]">
                        <svg class="h-7 w-7 text-gray-300 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                    </div>
                    <p class="text-sm font-medium text-gray-400 dark:text-gray-500">No orders yet</p>
                    <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Browse products and place your first order</p>
                    <Link
                        :href="route('customer.products.index')"
                        class="mt-4 inline-flex items-center rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-indigo-700"
                    >
                        Browse Products
                    </Link>
                </div>

                <!-- Orders List -->
                <div v-else class="space-y-4">
                    <Link
                        v-for="order in orders"
                        :key="order.id"
                        :href="route('customer.orders.show', order.id)"
                        class="group block rounded-2xl border border-white/60 bg-white/80 p-5 shadow-sm backdrop-blur-sm transition-all duration-200 hover:shadow-md dark:border-[#2e3039] dark:bg-[#1e2028]/90"
                        data-gsap="fade-up"
                    >
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <!-- Left -->
                            <div class="flex items-center gap-4">
                                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-100 dark:bg-indigo-500/10">
                                    <svg class="h-6 w-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-900 transition-colors group-hover:text-indigo-600 dark:text-gray-100 dark:group-hover:text-indigo-400">
                                        Order #{{ order.id }}
                                    </p>
                                    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                                        {{ formatDate(order.created_at) }}
                                        <span class="mx-1">&middot;</span>
                                        {{ order.items.length }} {{ order.items.length === 1 ? 'item' : 'items' }}
                                    </p>
                                </div>
                            </div>

                            <!-- Right -->
                            <div class="flex items-center gap-3 sm:gap-4">
                                <span
                                    v-if="order.payment"
                                    class="rounded-full bg-gray-100 px-2.5 py-0.5 text-[11px] font-medium text-gray-500 dark:bg-[#1a1d23] dark:text-gray-400"
                                >
                                    {{ formatPaymentMethod(order.payment.method) }}
                                </span>
                                <span
                                    :class="[
                                        'rounded-full px-2.5 py-0.5 text-[11px] font-bold uppercase tracking-wider',
                                        statusClasses(order.status),
                                    ]"
                                >
                                    {{ order.status }}
                                </span>
                                <span class="text-lg font-bold text-gray-900 dark:text-gray-100">
                                    {{ formatPrice(order.total_price) }}
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
            </div>
        </div>
    </AuthenticatedLayout>
</template>
