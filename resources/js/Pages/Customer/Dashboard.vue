<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const recentItems = [
    { name: 'Wireless Headphones', price: '$99.00', status: 'Delivered', date: 'Oct 24, 2023' },
    { name: 'Mechanical Keyboard', price: '$150.00', status: 'In Transit', date: 'Oct 26, 2023' },
    { name: 'Gaming Mouse', price: '$59.00', status: 'Processing', date: 'Oct 27, 2023' },
];
</script>

<template>
    <Head title="My Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-2xl font-bold text-gray-800">Welcome back, {{ $page.props.auth.user.name }}!</h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
                    <!-- Orders Section -->
                    <div class="space-y-6 lg:col-span-2">
                        <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
                            <div class="flex items-center justify-between border-b border-gray-50 p-6">
                                <h3 class="text-lg font-bold text-gray-800">My Recent Orders</h3>
                                <Link href="#" class="text-sm font-semibold text-indigo-600">View History</Link>
                            </div>
                            <div class="divide-y divide-gray-50">
                                <div
                                    v-for="item in recentItems"
                                    :key="item.name"
                                    class="flex items-center justify-between p-6 transition-colors hover:bg-gray-50"
                                >
                                    <div class="flex items-center space-x-4">
                                        <div class="flex h-16 w-16 items-center justify-center rounded-xl bg-gray-100 text-gray-400">
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                class="h-8 w-8"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"
                                                />
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="font-bold text-gray-900">{{ item.name }}</p>
                                            <p class="text-sm text-gray-500">{{ item.date }} • {{ item.price }}</p>
                                        </div>
                                    </div>
                                    <span
                                        :class="[
                                            'rounded-full px-3 py-1 text-xs font-bold tracking-wider uppercase',
                                            item.status === 'Delivered'
                                                ? 'bg-emerald-100 text-emerald-700'
                                                : item.status === 'In Transit'
                                                  ? 'bg-blue-100 text-blue-700'
                                                  : 'bg-amber-100 text-amber-700',
                                        ]"
                                    >
                                        {{ item.status }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar Info -->
                    <div class="space-y-6">
                        <div class="rounded-2xl bg-gradient-to-br from-indigo-600 to-violet-700 p-6 text-white shadow-lg">
                            <h3 class="mb-2 text-lg font-bold">Member Rewards</h3>
                            <p class="mb-6 text-sm text-indigo-100">You have 450 points available for your next purchase.</p>
                            <button class="w-full rounded-xl bg-white py-3 font-bold text-indigo-600 transition-colors hover:bg-indigo-50">
                                Redeem Now
                            </button>
                        </div>

                        <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                            <h3 class="mb-4 text-lg font-bold text-gray-800">Account Settings</h3>
                            <nav class="space-y-2">
                                <Link href="/profile" class="block flex items-center rounded-lg p-3 text-gray-600 hover:bg-gray-50">
                                    <span class="mr-3">👤</span> Profile Information
                                </Link>
                                <Link href="#" class="block flex items-center rounded-lg p-3 text-gray-600 hover:bg-gray-50">
                                    <span class="mr-3">📍</span> Shipping Addresses
                                </Link>
                                <Link href="#" class="block flex items-center rounded-lg p-3 text-gray-600 hover:bg-gray-50">
                                    <span class="mr-3">💳</span> Payment Methods
                                </Link>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
