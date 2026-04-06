<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

interface Vendor {
    id: number;
    name: string;
    email: string;
    is_approved: boolean;
    products_count: number;
    created_at: string;
}

defineProps<{
    vendors: Vendor[];
}>();
</script>

<template>
    <Head title="All Vendors" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link
                    :href="route('admin.dashboard')"
                    class="group flex h-8 w-8 items-center justify-center rounded-lg bg-white/60 text-gray-400 backdrop-blur-sm transition-all duration-200 hover:bg-indigo-50 hover:text-indigo-600"
                >
                    <svg class="h-4 w-4 transition-transform duration-200 group-hover:-translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </Link>
                <h2 class="text-xl font-bold text-gray-800">All Vendors</h2>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div v-if="vendors.length === 0" class="animate-fade-in-up rounded-2xl border border-white/60 bg-white/80 px-6 py-16 text-center shadow-sm backdrop-blur-sm">
                    <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-gray-50">
                        <svg class="h-7 w-7 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <p class="text-sm font-medium text-gray-400">No vendors found</p>
                </div>

                <div v-else class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <Link
                        v-for="vendor in vendors"
                        :key="vendor.id"
                        :href="route('admin.vendors.show', vendor.id)"
                        class="glass-card animate-fade-in-up group rounded-2xl border border-white/60 bg-white/80 p-5 shadow-sm backdrop-blur-sm transition-all duration-200 hover:shadow-md"
                    >
                        <div class="flex items-center gap-4">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 to-violet-500 text-white shadow-sm">
                                <span class="text-sm font-bold">{{ vendor.name.substring(0, 2).toUpperCase() }}</span>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate font-semibold text-gray-900 group-hover:text-indigo-600 transition-colors">{{ vendor.name }}</p>
                                <p class="mt-0.5 truncate text-xs text-gray-500">{{ vendor.email }}</p>
                            </div>
                            <svg class="h-5 w-5 text-gray-300 transition-transform duration-200 group-hover:translate-x-0.5 group-hover:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>

                        <div class="mt-4 flex items-center gap-3">
                            <span
                                :class="[
                                    'rounded-full px-2.5 py-0.5 text-[11px] font-bold uppercase tracking-wider',
                                    vendor.is_approved ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700',
                                ]"
                            >
                                {{ vendor.is_approved ? 'Approved' : 'Pending' }}
                            </span>
                            <span class="rounded-full bg-gray-100 px-2.5 py-0.5 text-[11px] font-medium text-gray-500">
                                {{ vendor.products_count }} {{ vendor.products_count === 1 ? 'product' : 'products' }}
                            </span>
                        </div>
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
