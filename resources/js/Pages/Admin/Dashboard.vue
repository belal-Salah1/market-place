<script setup lang="ts">
import { Inertia } from '@inertiajs/inertia';
import { Head, Link } from '@inertiajs/vue3';
import { onMounted, computed, ref } from 'vue';
import Modal from '@/Components/Modal.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useDateFormat } from '../../composables/useDateFormat';

const props = defineProps({
    user: {
        type: Object,
        required: true,
    },
    pendingVendors: {
        type: Array,
        default: () => [],
    },
});
const format = useDateFormat();
const isModalOpen = ref(false);
const selectedVendor = ref(null);

const isApproved = computed(() => props.pendingVendors.every((vendor) => vendor.is_approved));

const openModal = (vendor) => {
    selectedVendor.value = vendor;
    isModalOpen.value = true;
    console.log('Opening modal for vendor', vendor);
};

const closeModal = () => {
    isModalOpen.value = false;
    selectedVendor.value = null;
};

const approve = (vendor) => {
    Inertia.post(
        route('admin.vendors.approve', vendor.id),
        {},
        {
            onSuccess: () => {
                closeModal();
            },
            onError: (errors) => {
                console.error('Approval failed:', errors);
            },
        },
    );
    isModalOpen.value = false;
    selectedVendor.value = null;
};
onMounted(() => {
    console.log('Pending Vendors:', props.pendingVendors);
});

const stats = [
    {
        name: 'Total Users',
        value: '1,234',
        change: '+12%',
        icon: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z',
    },
    { name: 'Total Orders', value: '856', change: '+8%', icon: 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z' },
    {
        name: 'Revenue',
        value: '$45.2K',
        change: '+23%',
        icon: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
    },
    {
        name: 'Active Vendors',
        value: '48',
        change: '+5%',
        icon: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
    },
];
</script>

<template>
    <Head title="Admin Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="animate-fade-in-up bg-gradient-to-r from-indigo-600 to-violet-600 bg-clip-text text-2xl font-bold text-transparent">Admin Control Center</h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Stats Grid -->
                <div class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div
                        v-for="(stat, index) in stats"
                        :key="stat.name"
                        :class="[
                            'glass-card rounded-2xl border border-white/60 bg-white/80 backdrop-blur-sm shadow-sm p-4',
                            'animate-fade-in-up',
                            index === 0 ? 'delay-0' : index === 1 ? 'delay-1' : index === 2 ? 'delay-2' : 'delay-3',
                        ]"
                    >
                        <div class="mb-3 flex items-center justify-between">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-50">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" :d="stat.icon" />
                                </svg>
                            </div>
                            <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-[11px] font-semibold text-emerald-600">{{ stat.change }}</span>
                        </div>
                        <p class="text-xs font-medium text-gray-500">{{ stat.name }}</p>
                        <p class="mt-0.5 text-xl font-bold text-gray-900">{{ stat.value }}</p>
                    </div>
                </div>

                <!-- View All Vendors Link -->
                <div class="mb-8 animate-fade-in-up delay-4">
                    <Link
                        :href="route('admin.vendors.index')"
                        class="glass-card group flex items-center justify-between rounded-2xl border border-white/60 bg-white/80 px-6 py-4 shadow-sm backdrop-blur-sm transition-all duration-200 hover:shadow-md"
                    >
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-violet-500 shadow-lg shadow-indigo-200/50">
                                <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-900 group-hover:text-indigo-600 transition-colors">View All Vendors</p>
                                <p class="text-xs text-gray-500">See every vendor with their products and categories</p>
                            </div>
                        </div>
                        <svg class="h-5 w-5 text-gray-300 transition-transform duration-200 group-hover:translate-x-0.5 group-hover:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </Link>
                </div>

                <!-- Vendor Approval List -->
                <div class="animate-fade-in-up delay-4 overflow-hidden rounded-2xl border border-white/60 bg-white/80 backdrop-blur-sm shadow-sm">
                    <div class="flex items-center justify-between border-b border-gray-100/80 px-6 py-5">
                        <div class="flex items-center space-x-3">
                            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-50">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-5 w-5 text-indigo-600"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="1.5"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
                                    />
                                </svg>
                            </div>
                            <h3 class="text-lg font-bold text-gray-800">Vendor Requests</h3>
                        </div>
                        <span class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-600">
                            {{ props.pendingVendors.length }} pending
                        </span>
                    </div>

                    <div v-if="props.pendingVendors.length === 0" class="px-6 py-12 text-center">
                        <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-gray-50">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-7 w-7 text-gray-300"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="1.5"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <p class="text-sm font-medium text-gray-400">No pending vendor requests</p>
                    </div>

                    <div v-else class="divide-y divide-gray-50">
                        <div
                            v-for="vendor in props.pendingVendors"
                            :key="vendor.id"
                            class="glass-card flex cursor-pointer items-center justify-between px-6 py-4 transition-all"
                            @click="openModal(vendor)"
                        >
                            <div class="flex items-center space-x-4">
                                <div
                                    class="flex h-11 w-11 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 to-violet-500 text-white shadow-sm"
                                >
                                    <span class="text-sm font-bold">{{ vendor.name.substring(0, 2).toUpperCase() }}</span>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-900">{{ vendor.name }}</p>
                                    <p class="mt-0.5 text-xs text-gray-500">
                                        {{ format.format(vendor.updated_at, { dateStyle: 'short', timeStyle: 'short' }) }} &middot; ID: #{{
                                            vendor.id
                                        }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-3">
                                <span
                                    :class="[
                                        'rounded-full px-3 py-1 text-xs font-bold tracking-wider uppercase',
                                        isApproved ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700',
                                    ]"
                                >
                                    {{ isApproved ? 'Approved' : 'Pending' }}
                                </span>
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-5 w-5 text-gray-300"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="1.5"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <Modal :show="isModalOpen" :vendor="selectedVendor" @close="closeModal" @approve="approve" maxWidth="lg" :closeable="true" />
    </AuthenticatedLayout>
</template>