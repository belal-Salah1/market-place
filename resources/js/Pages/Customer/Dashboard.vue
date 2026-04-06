<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

interface OrderItem {
    id: number;
    quantity: number;
    price: number;
    product: { id: number; name: string };
}

interface Order {
    id: number;
    total_price: number;
    status: string;
    created_at: string;
    items: OrderItem[];
}

defineProps<{
    user: object;
    recentOrders: Order[];
    orderCount: number;
}>();

function formatDate(dateString: string): string {
    return new Date(dateString).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
}

function formatCurrency(amount: number): string {
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(amount);
}

function statusBadgeClasses(status: string): string {
    const base = 'rounded-full px-3 py-1 text-xs font-bold tracking-wider uppercase';
    const map: Record<string, string> = {
        pending: 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400',
        processing: 'bg-blue-50 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400',
        shipped: 'bg-violet-50 text-violet-700 dark:bg-violet-500/10 dark:text-violet-400',
        delivered: 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400',
        cancelled: 'bg-red-50 text-red-700 dark:bg-red-500/10 dark:text-red-400',
    };
    return `${base} ${map[status] ?? map.pending}`;
}
</script>

<template>
    <Head title="Customer Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-100" data-gsap="fade-up">Customer Dashboard</h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Stats -->
                <div class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div
                        class="glass-card rounded-2xl border border-white/60 bg-white/80 p-4 shadow-sm backdrop-blur-sm dark:border-[#2e3039] dark:bg-[#1e2028]/90"
                        data-gsap="fade-up"
                    >
                        <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-50 dark:bg-indigo-500/10">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-5 w-5 text-indigo-600"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="1.5"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                        </div>
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Orders</p>
                        <p class="mt-0.5 text-xl font-bold text-indigo-600">{{ orderCount }}</p>
                    </div>
                </div>

                <!-- Navigation Links -->
                <div class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <Link
                        :href="route('customer.products.index')"
                        class="glass-card group flex items-center justify-between rounded-2xl border border-white/60 bg-white/80 px-6 py-4 shadow-sm backdrop-blur-sm transition-all duration-200 hover:shadow-md dark:border-[#2e3039] dark:bg-[#1e2028]/90"
                        data-gsap="fade-up"
                        data-gsap-delay="0.3"
                    >
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-100 dark:bg-emerald-500/10">
                                <svg class="h-5 w-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"
                                    />
                                </svg>
                            </div>
                            <div>
                                <p
                                    class="font-semibold text-gray-900 transition-colors group-hover:text-emerald-600 dark:text-gray-100 dark:group-hover:text-emerald-400"
                                >
                                    Browse Products
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Discover and shop products</p>
                            </div>
                        </div>
                        <svg
                            class="h-5 w-5 text-gray-300 transition-transform duration-200 group-hover:translate-x-0.5 group-hover:text-emerald-400 dark:text-gray-500"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                            stroke-width="1.5"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </Link>
                    <Link
                        :href="route('customer.orders.index')"
                        class="glass-card group flex items-center justify-between rounded-2xl border border-white/60 bg-white/80 px-6 py-4 shadow-sm backdrop-blur-sm transition-all duration-200 hover:shadow-md dark:border-[#2e3039] dark:bg-[#1e2028]/90"
                        data-gsap="fade-up"
                        data-gsap-delay="0.38"
                    >
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-100 dark:bg-indigo-500/10">
                                <svg class="h-5 w-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                </svg>
                            </div>
                            <div>
                                <p
                                    class="font-semibold text-gray-900 transition-colors group-hover:text-indigo-600 dark:text-gray-100 dark:group-hover:text-indigo-400"
                                >
                                    My Orders
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">View your order history</p>
                            </div>
                        </div>
                        <svg
                            class="h-5 w-5 text-gray-300 transition-transform duration-200 group-hover:translate-x-0.5 group-hover:text-indigo-400 dark:text-gray-500"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                            stroke-width="1.5"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </Link>
                    <Link
                        :href="route('messages.index')"
                        class="glass-card group flex items-center justify-between rounded-2xl border border-white/60 bg-white/80 px-6 py-4 shadow-sm backdrop-blur-sm transition-all duration-200 hover:shadow-md dark:border-[#2e3039] dark:bg-[#1e2028]/90"
                        data-gsap="fade-up"
                        data-gsap-delay="0.45"
                    >
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-100 dark:bg-blue-500/10">
                                <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" /></svg>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-900 transition-colors group-hover:text-blue-600 dark:text-gray-100 dark:group-hover:text-blue-400">Messages</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Chat with vendors</p>
                            </div>
                        </div>
                        <svg class="h-5 w-5 text-gray-300 transition-transform duration-200 group-hover:translate-x-0.5 group-hover:text-blue-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                    </Link>
                </div>

                <!-- Recent Orders -->
                <div
                    class="overflow-hidden rounded-2xl border border-white/60 bg-white/80 shadow-sm backdrop-blur-sm dark:border-[#2e3039] dark:bg-[#1e2028]/90"
                    data-gsap="fade-up"
                    data-gsap-delay="0.45"
                >
                    <div class="flex items-center justify-between border-b border-gray-100/80 px-6 py-5 dark:border-[#2e3039]">
                        <div class="flex items-center space-x-3">
                            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-50 dark:bg-indigo-500/10">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-5 w-5 text-indigo-600"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="1.5"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100">Recent Orders</h3>
                        </div>
                        <Link
                            :href="route('customer.orders.index')"
                            class="text-sm font-semibold text-indigo-600 transition-colors hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300"
                        >
                            View All
                        </Link>
                    </div>

                    <!-- Orders List -->
                    <div v-if="recentOrders.length > 0" class="divide-y divide-gray-50 dark:divide-[#2e3039]">
                        <Link
                            v-for="order in recentOrders"
                            :key="order.id"
                            :href="route('customer.orders.show', order.id)"
                            class="glass-card flex items-center justify-between px-6 py-5 transition-all hover:bg-gray-50/50 dark:hover:bg-[#252830]/50"
                            data-gsap="fade-up"
                            data-gsap-delay="0.15"
                        >
                            <div class="flex items-center space-x-4">
                                <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-indigo-50 text-indigo-400 dark:bg-indigo-500/10">
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-7 w-7"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                        stroke-width="1.5"
                                    >
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-900 dark:text-gray-100">Order #{{ order.id }}</p>
                                    <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">
                                        {{ formatDate(order.created_at) }} &middot; {{ formatCurrency(order.total_price) }}
                                    </p>
                                    <p v-if="order.items.length > 0" class="mt-0.5 text-xs text-gray-400 dark:text-gray-500">
                                        {{ order.items.map((i) => i.product.name).join(', ') }}
                                    </p>
                                </div>
                            </div>
                            <span :class="statusBadgeClasses(order.status)">
                                {{ order.status }}
                            </span>
                        </Link>
                    </div>

                    <!-- Empty State -->
                    <div v-else class="px-6 py-16 text-center">
                        <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-gray-100 dark:bg-[#252830]">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-8 w-8 text-gray-400 dark:text-gray-500"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="1.5"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                        </div>
                        <p class="text-lg font-semibold text-gray-700 dark:text-gray-200">No orders yet</p>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Start shopping to see your orders here.</p>
                        <Link
                            :href="route('customer.products.index')"
                            class="btn-sweep mt-6 inline-flex items-center rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-indigo-700"
                        >
                            Browse Products
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
