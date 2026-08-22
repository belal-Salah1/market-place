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
    tracking: {
        type: Object,
        required: true,
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

// Real figures, unlike the placeholders that used to sit here. `failed` is all-time
// on purpose — a conversion Meta never received does not stop mattering after a week.
const trackingStats = computed(() => [
    { name: 'Purchases tracked', value: props.tracking.purchases, hint: 'last 7 days' },
    { name: 'Deduplicated', value: props.tracking.deduplicated, hint: 'last 7 days' },
    { name: 'Browser/server matched', value: props.tracking.matched, hint: 'last 7 days' },
    { name: 'CAPI failed', value: props.tracking.failed, hint: 'all time', alert: true },
]);
</script>

<template>
    <Head title="Admin Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="bg-gradient-to-r from-indigo-600 to-violet-600 bg-clip-text text-2xl font-bold text-transparent" data-gsap="fade-up">
                Admin Control Center
            </h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Tracking Stats Grid -->
                <Link :href="route('admin.tracking.index')" class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4" data-gsap="fade-up">
                    <div
                        v-for="stat in trackingStats"
                        :key="stat.name"
                        class="glass-card rounded-2xl border border-white/60 bg-white/80 p-4 shadow-sm backdrop-blur-sm dark:border-[#2e3039] dark:bg-[#1e2028]/90"
                    >
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ stat.name }}</p>
                        <p
                            class="mt-0.5 text-xl font-bold"
                            :class="stat.alert && stat.value > 0 ? 'text-rose-600' : 'text-gray-900 dark:text-gray-100'"
                        >
                            {{ stat.value }}
                        </p>
                        <p class="mt-1 text-[11px] font-medium text-gray-400 dark:text-gray-500">{{ stat.hint }}</p>
                    </div>
                </Link>

                <!-- View All Vendors Link -->
                <div class="mb-8" data-gsap="fade-up" data-gsap-delay="0.3">
                    <Link
                        :href="route('admin.vendors.index')"
                        class="glass-card group flex items-center justify-between rounded-2xl border border-white/60 bg-white/80 px-6 py-4 shadow-sm backdrop-blur-sm transition-all duration-200 hover:shadow-md dark:border-[#2e3039] dark:bg-[#1e2028]/90"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-violet-500 shadow-lg shadow-indigo-200/50"
                            >
                                <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
                                    />
                                </svg>
                            </div>
                            <div>
                                <p
                                    class="font-semibold text-gray-900 transition-colors group-hover:text-indigo-600 dark:text-gray-100 dark:group-hover:text-indigo-400"
                                >
                                    View All Vendors
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">See every vendor with their products and categories</p>
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
                </div>

                <!-- Vendor Approval List -->
                <div
                    class="overflow-hidden rounded-2xl border border-white/60 bg-white/80 shadow-sm backdrop-blur-sm dark:border-[#2e3039] dark:bg-[#1e2028]/90"
                    data-gsap="fade-up"
                    data-gsap-delay="0.3"
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
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
                                    />
                                </svg>
                            </div>
                            <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100">Vendor Requests</h3>
                        </div>
                        <span
                            class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400"
                        >
                            {{ props.pendingVendors.length }} pending
                        </span>
                    </div>

                    <div v-if="props.pendingVendors.length === 0" class="px-6 py-12 text-center">
                        <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-gray-50 dark:bg-[#1a1d23]">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-7 w-7 text-gray-300 dark:text-gray-500"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="1.5"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <p class="text-sm font-medium text-gray-400 dark:text-gray-500">No pending vendor requests</p>
                    </div>

                    <div v-else class="divide-y divide-gray-50 dark:divide-[#2e3039]">
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
                                    <p class="font-semibold text-gray-900 dark:text-gray-100">{{ vendor.name }}</p>
                                    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
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
                                        isApproved
                                            ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400'
                                            : 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400',
                                    ]"
                                >
                                    {{ isApproved ? 'Approved' : 'Pending' }}
                                </span>
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-5 w-5 text-gray-300 dark:text-gray-500"
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
