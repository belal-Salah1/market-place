<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    user: {
        type: Object,
        required: true,
    },
    pendingVendors: {
        type: Array,
        default: () => [],
    },
});

const stats = [
    { name: 'Total Users', value: '1,234', change: '+12%', icon: 'users' },
    { name: 'Total Revenue', value: '$45,678', change: '+8.3%', icon: 'currency-dollar' },
    { name: 'Active Vendors', value: '42', change: '+3', icon: 'storefront' },
    { name: 'System Health', value: 'Optimal', change: '100%', icon: 'shield-check' },
];
</script>

<template>
    <Head title="Admin Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="bg-gradient-to-r from-indigo-600 to-violet-600 bg-clip-text text-2xl font-bold text-transparent">Admin Control Center</h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Stats Grid -->
                <div class="mb-8 grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
                    <div
                        v-for="stat in stats"
                        :key="stat.name"
                        class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm transition-shadow duration-300 hover:shadow-md"
                    >
                        <div class="mb-4 flex items-center justify-between">
                            <div class="rounded-xl bg-indigo-50 p-3">
                                <span class="text-xs font-bold tracking-wider text-indigo-600 uppercase">{{ stat.name }}</span>
                            </div>
                            <span class="text-sm font-medium text-emerald-500">{{ stat.change }}</span>
                        </div>
                        <div class="text-3xl font-bold text-gray-900">{{ stat.value }}</div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
                    <div class="flex items-center justify-between border-b border-gray-50 p-6">
                        <h3 class="text-lg font-semibold text-gray-800">System Logs</h3>
                        <button class="text-sm font-medium text-indigo-600 hover:underline">View All</button>
                    </div>
                    <div class="divide-y divide-gray-50">
                        <div v-for="i in 5" :key="i" class="p-4 transition-colors hover:bg-gray-50">
                            <div class="flex items-center space-x-4">
                                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-indigo-100 text-indigo-600">
                                    <span class="text-sm font-bold">AL</span>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900">New vendor registration request</p>
                                    <p class="text-xs text-gray-500">2 minutes ago • ID: #45892</p>
                                </div>
                                <span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-medium text-amber-700">Pending</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
