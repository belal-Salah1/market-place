<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const vendorStats = [
    {
        name: 'Total Sales',
        value: '$12,450',
        color: 'text-emerald-600',
        bg: 'bg-emerald-50',
        icon: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
    },
    {
        name: 'Active Products',
        value: '24',
        color: 'text-blue-600',
        bg: 'bg-blue-50',
        icon: 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
    },
    { name: 'Pending Orders', value: '8', color: 'text-amber-600', bg: 'bg-amber-50', icon: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z' },
    {
        name: 'Store Rating',
        value: '4.9/5',
        color: 'text-indigo-600',
        bg: 'bg-indigo-50',
        icon: 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z',
    },
];
</script>

<template>
    <Head title="Vendor Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="animate-fade-in-up text-2xl font-bold text-gray-800">Vendor Dashboard</h2>
                <div class="flex items-center gap-3">
                    <Link
                        :href="route('vendor.categories.create')"
                        class="inline-flex items-center rounded-xl border border-gray-200 bg-white px-5 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition-all duration-200 hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="mr-1.5 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        Add Category
                    </Link>
                    <Link
                        :href="route('vendor.products.create')"
                        class="btn-sweep inline-flex items-center rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-indigo-700"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="mr-1.5 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
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
                        v-for="(stat, index) in vendorStats"
                        :key="stat.name"
                        :class="[
                            'glass-card rounded-2xl border border-white/60 bg-white/80 backdrop-blur-sm shadow-sm p-4',
                            'animate-fade-in-up',
                            index === 0 ? 'delay-0' : index === 1 ? 'delay-1' : index === 2 ? 'delay-2' : 'delay-3',
                        ]"
                    >
                        <div :class="['mb-3 flex h-10 w-10 items-center justify-center rounded-xl', stat.bg]">
                            <svg xmlns="http://www.w3.org/2000/svg" :class="['h-5 w-5', stat.color]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" :d="stat.icon" />
                            </svg>
                        </div>
                        <p class="text-xs font-medium text-gray-500">{{ stat.name }}</p>
                        <p :class="['mt-0.5 text-xl font-bold', stat.color]">{{ stat.value }}</p>
                    </div>
                </div>

                <!-- Navigation Links -->
                <div class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <Link
                        :href="route('vendor.products.index')"
                        class="glass-card group flex items-center justify-between rounded-2xl border border-white/60 bg-white/80 px-6 py-4 shadow-sm backdrop-blur-sm transition-all duration-200 hover:shadow-md animate-fade-in-up delay-4"
                    >
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-100">
                                <svg class="h-5 w-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-900 group-hover:text-indigo-600 transition-colors">My Products</p>
                                <p class="text-xs text-gray-500">View all your products</p>
                            </div>
                        </div>
                        <svg class="h-5 w-5 text-gray-300 transition-transform duration-200 group-hover:translate-x-0.5 group-hover:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </Link>
                    <Link
                        :href="route('vendor.categories.index')"
                        class="glass-card group flex items-center justify-between rounded-2xl border border-white/60 bg-white/80 px-6 py-4 shadow-sm backdrop-blur-sm transition-all duration-200 hover:shadow-md animate-fade-in-up delay-5"
                    >
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-violet-100">
                                <svg class="h-5 w-5 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                </svg>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-900 group-hover:text-violet-600 transition-colors">My Categories</p>
                                <p class="text-xs text-gray-500">View all your categories</p>
                            </div>
                        </div>
                        <svg class="h-5 w-5 text-gray-300 transition-transform duration-200 group-hover:translate-x-0.5 group-hover:text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </Link>
                </div>

                <!-- Sales Chart Mockup / Quick Actions -->
                <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
                    <div class="animate-fade-in-up delay-4 rounded-2xl border border-white/60 bg-white/80 backdrop-blur-sm shadow-sm p-6 lg:col-span-2">
                        <div class="mb-6 flex items-center justify-between">
                            <h3 class="text-lg font-bold text-gray-800">Sales Overview</h3>
                            <select
                                class="rounded-xl border-gray-200 bg-gray-50 px-3 py-2 text-sm font-medium text-gray-600 focus:border-indigo-300 focus:ring-indigo-200"
                            >
                                <option>Last 7 days</option>
                                <option>Last 30 days</option>
                            </select>
                        </div>
                        <div class="flex h-64 items-end justify-between gap-3 rounded-xl bg-gray-50/80 px-6 py-4">
                            <div
                                v-for="h in [40, 70, 45, 90, 65, 80, 50]"
                                :key="h"
                                :style="{ height: h + '%' }"
                                class="flex-1 cursor-pointer rounded-lg bg-gradient-to-t from-indigo-600 to-violet-400 opacity-80 transition-all duration-200 hover:opacity-100 hover:shadow-md"
                            ></div>
                        </div>
                        <div class="mt-3 flex justify-between px-6 text-xs font-medium text-gray-400">
                            <span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span><span>Sun</span>
                        </div>
                    </div>

                    <div class="animate-slide-in-right delay-3 rounded-2xl border border-white/60 bg-white/80 backdrop-blur-sm shadow-sm p-6">
                        <h3 class="mb-6 text-lg font-bold text-gray-800">Quick Actions</h3>
                        <div class="space-y-3">
                            <button
                                class="group w-full rounded-xl border border-gray-100/80 bg-white/60 p-4 text-left transition-all hover:border-indigo-200 hover:bg-indigo-50 hover:shadow-sm"
                            >
                                <div class="flex items-center space-x-3">
                                    <div
                                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 transition-colors group-hover:bg-emerald-100"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                            />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-700 group-hover:text-indigo-700">Withdraw Earnings</p>
                                        <p class="text-xs text-gray-500">Available: $8,420</p>
                                    </div>
                                </div>
                            </button>
                            <button
                                class="group w-full rounded-xl border border-gray-100/80 bg-white/60 p-4 text-left transition-all hover:border-indigo-200 hover:bg-indigo-50 hover:shadow-sm"
                            >
                                <div class="flex items-center space-x-3">
                                    <div
                                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-violet-50 text-violet-600 transition-colors group-hover:bg-violet-100"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"
                                            />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-700 group-hover:text-indigo-700">Manage Coupons</p>
                                        <p class="text-xs text-gray-500">3 active campaigns</p>
                                    </div>
                                </div>
                            </button>
                            <button
                                class="group w-full rounded-xl border border-gray-100/80 bg-white/60 p-4 text-left transition-all hover:border-indigo-200 hover:bg-indigo-50 hover:shadow-sm"
                            >
                                <div class="flex items-center space-x-3">
                                    <div
                                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600 transition-colors group-hover:bg-blue-100"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"
                                            />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-700 group-hover:text-indigo-700">Customer Messages</p>
                                        <p class="text-xs text-gray-500">2 unread notifications</p>
                                    </div>
                                </div>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>