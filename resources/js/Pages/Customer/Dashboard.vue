<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

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
            <h2 class="animate-fade-in-up text-2xl font-bold text-gray-800">Welcome back, {{ $page.props.auth.user.name }}!</h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
                    <!-- Orders Section -->
                    <div class="space-y-6 lg:col-span-2 animate-fade-in-up delay-4">
                        <div class="overflow-hidden rounded-2xl border border-white/60 bg-white/80 backdrop-blur-sm shadow-sm">
                            <div class="flex items-center justify-between border-b border-gray-100/80 px-6 py-5">
                                <div class="flex items-center space-x-3">
                                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-50">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                        </svg>
                                    </div>
                                    <h3 class="text-lg font-bold text-gray-800">My Recent Orders</h3>
                                </div>
                                <Link href="#" class="text-sm font-semibold text-indigo-600 transition-colors hover:text-indigo-800">
                                    View History
                                </Link>
                            </div>
                            <div class="divide-y divide-gray-50">
                                <div
                                    v-for="(item, index) in recentItems"
                                    :key="item.name"
                                    :class="[
                                        'glass-card flex items-center justify-between px-6 py-5 transition-all',
                                        'animate-fade-in-up',
                                        index === 0 ? 'delay-0' : index === 1 ? 'delay-1' : 'delay-2',
                                    ]"
                                >
                                    <div class="flex items-center space-x-4">
                                        <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-indigo-50 text-indigo-400">
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
                                            <p class="font-semibold text-gray-900">{{ item.name }}</p>
                                            <p class="mt-0.5 text-sm text-gray-500">{{ item.date }} &middot; {{ item.price }}</p>
                                        </div>
                                    </div>
                                    <span
                                        :class="[
                                            'rounded-full px-3 py-1 text-xs font-bold tracking-wider uppercase',
                                            item.status === 'Delivered'
                                                ? 'bg-emerald-50 text-emerald-700'
                                                : item.status === 'In Transit'
                                                  ? 'bg-blue-50 text-blue-700'
                                                  : 'bg-amber-50 text-amber-700',
                                        ]"
                                    >
                                        {{ item.status }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar Info -->
                    <div class="space-y-6 animate-slide-in-right delay-3">
                        <!-- Rewards Card -->
                        <div class="rounded-2xl bg-gradient-to-br from-indigo-600 to-violet-700 p-6 text-white shadow-lg">
                            <div class="mb-3 flex items-center space-x-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/15 backdrop-blur-sm">
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-5 w-5"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                        stroke-width="2"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"
                                        />
                                    </svg>
                                </div>
                                <h3 class="text-lg font-bold">Member Rewards</h3>
                            </div>
                            <p class="mb-1 text-3xl font-bold">450</p>
                            <p class="mb-6 text-sm text-indigo-100">Points available for your next purchase</p>
                            <button class="btn-sweep w-full rounded-xl bg-white py-3 font-bold text-indigo-600 transition-colors hover:bg-indigo-50">
                                Redeem Now
                            </button>
                        </div>

                        <!-- Account Settings -->
                        <div class="rounded-2xl border border-white/60 bg-white/80 backdrop-blur-sm shadow-sm p-6">
                            <h3 class="mb-4 text-lg font-bold text-gray-800">Account Settings</h3>
                            <nav class="space-y-1">
                                <Link
                                    href="/profile"
                                    class="flex items-center rounded-xl p-3 text-gray-600 transition-colors hover:bg-indigo-50 hover:text-indigo-700"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="mr-3 h-5 w-5 text-gray-400"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                        stroke-width="1.5"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                                        />
                                    </svg>
                                    <span class="text-sm font-medium">Profile Information</span>
                                </Link>
                                <Link
                                    href="#"
                                    class="flex items-center rounded-xl p-3 text-gray-600 transition-colors hover:bg-indigo-50 hover:text-indigo-700"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="mr-3 h-5 w-5 text-gray-400"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                        stroke-width="1.5"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
                                        />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <span class="text-sm font-medium">Shipping Addresses</span>
                                </Link>
                                <Link
                                    href="#"
                                    class="flex items-center rounded-xl p-3 text-gray-600 transition-colors hover:bg-indigo-50 hover:text-indigo-700"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="mr-3 h-5 w-5 text-gray-400"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                        stroke-width="1.5"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"
                                        />
                                    </svg>
                                    <span class="text-sm font-medium">Payment Methods</span>
                                </Link>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>