<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

const vendorStats = [
    { name: 'Total Sales', value: '$12,450', color: 'text-emerald-600' },
    { name: 'Active Products', value: '24', color: 'text-blue-600' },
    { name: 'Pending Orders', value: '8', color: 'text-amber-600' },
    { name: 'Store Rating', value: '4.9/5', color: 'text-indigo-600' },
];
</script>

<template>
    <Head title="Vendor Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-bold text-gray-800">Vendor Dashboard</h2>
                <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-indigo-700">
                    + Add New Product
                </button>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Vendor Stats -->
                <div class="mb-8 grid grid-cols-1 gap-6 md:grid-cols-4">
                    <div
                        v-for="stat in vendorStats"
                        :key="stat.name"
                        class="flex flex-col items-center rounded-2xl border border-gray-100 bg-white p-6 shadow-sm"
                    >
                        <span class="mb-2 text-xs font-bold tracking-widest text-gray-500 uppercase">{{ stat.name }}</span>
                        <span :class="['text-3xl font-black', stat.color]">{{ stat.value }}</span>
                    </div>
                </div>

                <!-- Sales Chart Mockup / Recent Orders -->
                <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
                    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm lg:col-span-2">
                        <div class="mb-6 flex items-center justify-between">
                            <h3 class="text-lg font-bold text-gray-800">Sales Overview</h3>
                            <select class="rounded-lg border-gray-200 text-sm">
                                <option>Last 7 days</option>
                                <option>Last 30 days</option>
                            </select>
                        </div>
                        <div class="flex h-64 items-end justify-between rounded-xl bg-gray-50 px-8 py-4">
                            <!-- Simple CSS Bar Chart Mockup -->
                            <div
                                v-for="h in [40, 70, 45, 90, 65, 80, 50]"
                                :key="h"
                                :style="{ height: h + '%' }"
                                class="w-8 cursor-pointer rounded-t-lg bg-indigo-500 opacity-80 transition-opacity hover:opacity-100"
                            ></div>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                        <h3 class="mb-6 text-lg font-bold text-gray-800">Quick Actions</h3>
                        <div class="space-y-4">
                            <button
                                class="group w-full rounded-xl border border-transparent bg-gray-50 p-4 text-left transition-colors hover:border-indigo-100 hover:bg-indigo-50"
                            >
                                <p class="font-semibold text-gray-700 group-hover:text-indigo-700">Withdraw Earnings</p>
                                <p class="text-xs text-gray-500">Available: $8,420</p>
                            </button>
                            <button
                                class="group w-full rounded-xl border border-transparent bg-gray-50 p-4 text-left transition-colors hover:border-indigo-100 hover:bg-indigo-50"
                            >
                                <p class="font-semibold text-gray-700 group-hover:text-indigo-700">Manage Coupons</p>
                                <p class="text-xs text-gray-500">3 active campaigns</p>
                            </button>
                            <button
                                class="group w-full rounded-xl border border-transparent bg-gray-50 p-4 text-left transition-colors hover:border-indigo-100 hover:bg-indigo-50"
                            >
                                <p class="font-semibold text-gray-700 group-hover:text-indigo-700">Customer Messages</p>
                                <p class="text-xs text-gray-500">2 unread notifications</p>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
