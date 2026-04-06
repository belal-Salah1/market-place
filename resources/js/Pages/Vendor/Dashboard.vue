<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

interface OrderItem {
    id: number;
    quantity: number;
    price: number;
    product: { id: number; name: string };
}

interface RecentOrder {
    id: number;
    total_price: number;
    status: string;
    created_at: string;
    customer: { id: number; name: string };
    items: OrderItem[];
}

const props = defineProps<{
    user: object;
    stats: {
        totalSales: number;
        productCount: number;
        pendingOrders: number;
        avgRating: number | null;
    };
    recentOrders: RecentOrder[];
}>();

const vendorStats = [
    {
        name: 'Total Sales',
        value: `$${props.stats.totalSales}`,
        color: 'text-emerald-600',
        bg: 'bg-emerald-50 dark:bg-emerald-500/10',
        icon: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
    },
    {
        name: 'Active Products',
        value: String(props.stats.productCount),
        color: 'text-blue-600',
        bg: 'bg-blue-50 dark:bg-blue-500/10',
        icon: 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
    },
    {
        name: 'Pending Orders',
        value: String(props.stats.pendingOrders),
        color: 'text-amber-600',
        bg: 'bg-amber-50 dark:bg-amber-500/10',
        icon: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
    },
    {
        name: 'Store Rating',
        value: props.stats.avgRating ? `${props.stats.avgRating}/5` : 'N/A',
        color: 'text-indigo-600',
        bg: 'bg-indigo-50 dark:bg-indigo-500/10',
        icon: 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z',
    },
];

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
    <Head title="Vendor Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-100" data-gsap="fade-up">Vendor Dashboard</h2>
                <div class="flex items-center gap-3">
                    <Link
                        :href="route('vendor.categories.create')"
                        class="inline-flex items-center rounded-xl border border-gray-200 bg-white px-5 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition-all duration-200 hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700 dark:border-[#2e3039] dark:bg-[#1e2028]/90 dark:text-gray-200 dark:hover:bg-indigo-500/10 dark:hover:text-indigo-400"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="mr-1.5 h-4 w-4"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        Add Category
                    </Link>
                    <Link
                        :href="route('vendor.products.create')"
                        class="btn-sweep inline-flex items-center rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-indigo-700"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="mr-1.5 h-4 w-4"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        Add New Product
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Vendor Stats -->
                <div class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div
                        v-for="stat in vendorStats"
                        :key="stat.name"
                        class="glass-card rounded-2xl border border-white/60 bg-white/80 p-4 shadow-sm backdrop-blur-sm dark:border-[#2e3039] dark:bg-[#1e2028]/90"
                        data-gsap="fade-up"
                    >
                        <div :class="['mb-3 flex h-10 w-10 items-center justify-center rounded-xl', stat.bg]">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                :class="['h-5 w-5', stat.color]"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="1.5"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" :d="stat.icon" />
                            </svg>
                        </div>
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ stat.name }}</p>
                        <p :class="['mt-0.5 text-xl font-bold', stat.color]">{{ stat.value }}</p>
                    </div>
                </div>

                <!-- Navigation Links -->
                <div class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <Link
                        :href="route('vendor.products.index')"
                        class="glass-card group flex items-center justify-between rounded-2xl border border-white/60 bg-white/80 px-6 py-4 shadow-sm backdrop-blur-sm transition-all duration-200 hover:shadow-md dark:border-[#2e3039] dark:bg-[#1e2028]/90"
                        data-gsap="fade-up"
                        data-gsap-delay="0.3"
                    >
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-100 dark:bg-indigo-500/10">
                                <svg class="h-5 w-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"
                                    />
                                </svg>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-900 transition-colors group-hover:text-indigo-600 dark:text-gray-100 dark:group-hover:text-indigo-400">
                                    My Products
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">View all your products</p>
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
                        :href="route('vendor.categories.index')"
                        class="glass-card group flex items-center justify-between rounded-2xl border border-white/60 bg-white/80 px-6 py-4 shadow-sm backdrop-blur-sm transition-all duration-200 hover:shadow-md dark:border-[#2e3039] dark:bg-[#1e2028]/90"
                        data-gsap="fade-up"
                        data-gsap-delay="0.38"
                    >
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-violet-100 dark:bg-violet-500/10">
                                <svg class="h-5 w-5 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"
                                    />
                                </svg>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-900 transition-colors group-hover:text-violet-600 dark:text-gray-100 dark:group-hover:text-violet-400">
                                    My Categories
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">View all your categories</p>
                            </div>
                        </div>
                        <svg
                            class="h-5 w-5 text-gray-300 transition-transform duration-200 group-hover:translate-x-0.5 group-hover:text-violet-400 dark:text-gray-500"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                            stroke-width="1.5"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </Link>
                    <Link
                        :href="route('vendor.orders.index')"
                        class="glass-card group flex items-center justify-between rounded-2xl border border-white/60 bg-white/80 px-6 py-4 shadow-sm backdrop-blur-sm transition-all duration-200 hover:shadow-md dark:border-[#2e3039] dark:bg-[#1e2028]/90"
                        data-gsap="fade-up"
                        data-gsap-delay="0.46"
                    >
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-100 dark:bg-emerald-500/10">
                                <svg class="h-5 w-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"
                                    />
                                </svg>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-900 transition-colors group-hover:text-emerald-600 dark:text-gray-100 dark:group-hover:text-emerald-400">
                                    My Orders
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">View all your orders</p>
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
                        :href="route('vendor.reviews.index')"
                        class="glass-card group flex items-center justify-between rounded-2xl border border-white/60 bg-white/80 px-6 py-4 shadow-sm backdrop-blur-sm transition-all duration-200 hover:shadow-md dark:border-[#2e3039] dark:bg-[#1e2028]/90"
                        data-gsap="fade-up"
                        data-gsap-delay="0.45"
                    >
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-100 dark:bg-amber-500/10">
                                <svg class="h-5 w-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" /></svg>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-900 transition-colors group-hover:text-amber-600 dark:text-gray-100 dark:group-hover:text-amber-400">Reviews</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">View customer reviews</p>
                            </div>
                        </div>
                        <svg class="h-5 w-5 text-gray-300 transition-transform duration-200 group-hover:translate-x-0.5 group-hover:text-amber-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                    </Link>
                </div>

                <!-- Recent Orders / Quick Actions -->
                <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
                    <!-- Recent Orders -->
                    <div
                        class="rounded-2xl border border-white/60 bg-white/80 p-6 shadow-sm backdrop-blur-sm lg:col-span-2 dark:border-[#2e3039] dark:bg-[#1e2028]/90"
                        data-gsap="fade-up"
                        data-gsap-delay="0.3"
                    >
                        <div class="mb-6 flex items-center justify-between">
                            <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100">Recent Orders</h3>
                            <Link
                                :href="route('vendor.orders.index')"
                                class="text-sm font-medium text-indigo-600 transition-colors hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300"
                            >
                                View all
                            </Link>
                        </div>

                        <!-- Orders list -->
                        <div v-if="recentOrders.length" class="space-y-3">
                            <Link
                                v-for="order in recentOrders"
                                :key="order.id"
                                :href="route('vendor.orders.show', order.id)"
                                class="group block rounded-xl border border-gray-100/80 bg-white/60 p-4 transition-all hover:border-indigo-200 hover:shadow-sm dark:border-[#2e3039] dark:bg-[#1a1d23]/60 dark:hover:border-indigo-500/30"
                            >
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-gray-100 text-sm font-bold text-gray-600 dark:bg-gray-700/50 dark:text-gray-300">
                                            #{{ order.id }}
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-gray-800 group-hover:text-indigo-600 dark:text-gray-100 dark:group-hover:text-indigo-400">
                                                {{ order.customer.name }}
                                            </p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ formatDate(order.created_at) }} &middot; {{ order.items.length }} item{{ order.items.length !== 1 ? 's' : '' }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <span
                                            :class="[
                                                'rounded-full px-2.5 py-0.5 text-xs font-semibold capitalize',
                                                statusColors[order.status]?.badge ?? 'bg-gray-50 text-gray-600 dark:bg-gray-500/10 dark:text-gray-400',
                                            ]"
                                        >
                                            {{ order.status }}
                                        </span>
                                        <span class="text-sm font-bold text-gray-800 dark:text-gray-100">${{ order.total_price }}</span>
                                    </div>
                                </div>
                            </Link>
                        </div>

                        <!-- Empty state -->
                        <div v-else class="flex flex-col items-center justify-center rounded-xl bg-gray-50/80 py-12 dark:bg-[#1a1d23]/80">
                            <svg class="mb-3 h-12 w-12 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
                                />
                            </svg>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">No orders yet</p>
                            <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Orders will appear here once customers purchase your products.</p>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div
                        class="rounded-2xl border border-white/60 bg-white/80 p-6 shadow-sm backdrop-blur-sm dark:border-[#2e3039] dark:bg-[#1e2028]/90"
                        data-gsap="slide-right"
                        data-gsap-delay="0.22"
                    >
                        <h3 class="mb-6 text-lg font-bold text-gray-800 dark:text-gray-100">Quick Actions</h3>
                        <div class="space-y-3">
                            <Link
                                :href="route('vendor.earnings.index')"
                                class="group block w-full rounded-xl border border-gray-100/80 bg-white/60 p-4 text-left transition-all hover:border-indigo-200 hover:bg-indigo-50 hover:shadow-sm dark:border-[#2e3039] dark:bg-[#1a1d23]/60 dark:hover:border-indigo-500/30 dark:hover:bg-indigo-500/10"
                            >
                                <div class="flex items-center space-x-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 transition-colors group-hover:bg-emerald-100 dark:bg-emerald-500/10 dark:group-hover:bg-emerald-500/20">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-700 group-hover:text-indigo-700 dark:text-gray-200 dark:group-hover:text-indigo-400">View Earnings</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">View earnings overview</p>
                                    </div>
                                </div>
                            </Link>
                            <Link
                                :href="route('vendor.coupons.index')"
                                class="group block w-full rounded-xl border border-gray-100/80 bg-white/60 p-4 text-left transition-all hover:border-indigo-200 hover:bg-indigo-50 hover:shadow-sm dark:border-[#2e3039] dark:bg-[#1a1d23]/60 dark:hover:border-indigo-500/30 dark:hover:bg-indigo-500/10"
                            >
                                <div class="flex items-center space-x-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-violet-50 text-violet-600 transition-colors group-hover:bg-violet-100 dark:bg-violet-500/10 dark:group-hover:bg-violet-500/20">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" /></svg>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-700 group-hover:text-indigo-700 dark:text-gray-200 dark:group-hover:text-indigo-400">Manage Coupons</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Create and manage coupons</p>
                                    </div>
                                </div>
                            </Link>
                            <Link
                                :href="route('messages.index')"
                                class="group block w-full rounded-xl border border-gray-100/80 bg-white/60 p-4 text-left transition-all hover:border-indigo-200 hover:bg-indigo-50 hover:shadow-sm dark:border-[#2e3039] dark:bg-[#1a1d23]/60 dark:hover:border-indigo-500/30 dark:hover:bg-indigo-500/10"
                            >
                                <div class="flex items-center space-x-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600 transition-colors group-hover:bg-blue-100 dark:bg-blue-500/10 dark:group-hover:bg-blue-500/20">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" /></svg>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-700 group-hover:text-indigo-700 dark:text-gray-200 dark:group-hover:text-indigo-400">Customer Messages</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">View and reply to messages</p>
                                    </div>
                                </div>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
