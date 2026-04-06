<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const form = useForm({
    code: '',
    type: 'percentage',
    value: '',
    min_order_amount: '',
    usage_limit: '',
    expires_at: '',
});

const submit = () => {
    form.post(route('vendor.coupons.store'), {
        onSuccess: () => form.reset(),
    });
};
</script>

<template>
    <Head title="Create Coupon" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <Link
                        :href="route('vendor.coupons.index')"
                        class="group flex h-8 w-8 items-center justify-center rounded-lg bg-white/60 text-gray-400 backdrop-blur-sm transition-all duration-200 hover:bg-indigo-50 hover:text-indigo-600 dark:bg-white/5 dark:text-gray-500 dark:hover:bg-indigo-500/10 dark:hover:text-indigo-400"
                    >
                        <svg class="h-4 w-4 transition-transform duration-200 group-hover:-translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </Link>
                    <div>
                        <h2 class="text-xl font-bold text-gray-800 dark:text-gray-100">Create Coupon</h2>
                        <p class="text-xs text-gray-400 dark:text-gray-500">Set up a new discount coupon for your customers</p>
                    </div>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <form @submit.prevent="submit">
                    <!-- Coupon Details Card -->
                    <div
                        class="glass-card rounded-2xl border border-white/60 bg-white/80 p-6 shadow-sm backdrop-blur-sm dark:border-[#2e3039] dark:bg-[#1e2028]/90"
                        data-gsap="fade-up"
                    >
                        <div class="mb-5 flex items-center gap-2.5">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-violet-100 dark:bg-violet-500/10">
                                <svg class="h-4 w-4 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                </svg>
                            </div>
                            <h3 class="text-sm font-bold text-gray-800 dark:text-gray-100">Coupon Details</h3>
                        </div>

                        <div class="space-y-5">
                            <!-- Code -->
                            <div>
                                <label for="code" class="mb-1.5 block text-xs font-semibold tracking-wide text-gray-500 uppercase dark:text-gray-300">
                                    Coupon Code <span class="text-red-400">*</span>
                                </label>
                                <input
                                    id="code"
                                    type="text"
                                    v-model="form.code"
                                    placeholder="e.g. SUMMER20"
                                    class="block w-full rounded-xl border border-gray-300 bg-gray-50/80 px-4 py-3 font-mono text-sm text-gray-900 uppercase placeholder-gray-400 transition-all duration-200 focus:border-indigo-400 focus:bg-white focus:ring-2 focus:ring-indigo-100 dark:border-[#2e3039] dark:bg-[#1a1d23] dark:text-gray-200 dark:placeholder-gray-600"
                                />
                                <InputError :message="form.errors.code" class="mt-1.5" />
                            </div>

                            <!-- Type & Value -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="type" class="mb-1.5 block text-xs font-semibold tracking-wide text-gray-500 uppercase dark:text-gray-300">
                                        Discount Type <span class="text-red-400">*</span>
                                    </label>
                                    <select
                                        id="type"
                                        v-model="form.type"
                                        class="block w-full rounded-xl border border-gray-300 bg-gray-50/80 px-4 py-3 text-sm text-gray-900 transition-all duration-200 focus:border-indigo-400 focus:bg-white focus:ring-2 focus:ring-indigo-100 dark:border-[#2e3039] dark:bg-[#1a1d23] dark:text-gray-200"
                                    >
                                        <option value="percentage">Percentage</option>
                                        <option value="fixed">Fixed Amount</option>
                                    </select>
                                    <InputError :message="form.errors.type" class="mt-1.5" />
                                </div>
                                <div>
                                    <label for="value" class="mb-1.5 block text-xs font-semibold tracking-wide text-gray-500 uppercase dark:text-gray-300">
                                        Value <span class="text-red-400">*</span>
                                    </label>
                                    <div class="relative">
                                        <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-sm font-semibold text-gray-400">
                                            {{ form.type === 'percentage' ? '%' : '$' }}
                                        </span>
                                        <input
                                            id="value"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            v-model="form.value"
                                            placeholder="0"
                                            class="block w-full rounded-xl border border-gray-300 bg-gray-50/80 py-3 pr-4 pl-10 text-sm text-gray-900 placeholder-gray-400 transition-all duration-200 focus:border-indigo-400 focus:bg-white focus:ring-2 focus:ring-indigo-100 dark:border-[#2e3039] dark:bg-[#1a1d23] dark:text-gray-200 dark:placeholder-gray-600"
                                        />
                                    </div>
                                    <InputError :message="form.errors.value" class="mt-1.5" />
                                </div>
                            </div>

                            <!-- Min Order Amount -->
                            <div>
                                <label for="min_order_amount" class="mb-1.5 block text-xs font-semibold tracking-wide text-gray-500 uppercase dark:text-gray-300">
                                    Minimum Order Amount <span class="text-red-400">*</span>
                                </label>
                                <div class="relative">
                                    <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-sm font-semibold text-gray-400">$</span>
                                    <input
                                        id="min_order_amount"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        v-model="form.min_order_amount"
                                        placeholder="0.00"
                                        class="block w-full rounded-xl border border-gray-300 bg-gray-50/80 py-3 pr-4 pl-10 text-sm text-gray-900 placeholder-gray-400 transition-all duration-200 focus:border-indigo-400 focus:bg-white focus:ring-2 focus:ring-indigo-100 dark:border-[#2e3039] dark:bg-[#1a1d23] dark:text-gray-200 dark:placeholder-gray-600"
                                    />
                                </div>
                                <InputError :message="form.errors.min_order_amount" class="mt-1.5" />
                            </div>

                            <!-- Usage Limit & Expires At -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="usage_limit" class="mb-1.5 block text-xs font-semibold tracking-wide text-gray-500 uppercase dark:text-gray-300">
                                        Usage Limit <span class="text-xs font-normal normal-case text-gray-400">(optional)</span>
                                    </label>
                                    <input
                                        id="usage_limit"
                                        type="number"
                                        min="1"
                                        v-model="form.usage_limit"
                                        placeholder="Unlimited"
                                        class="block w-full rounded-xl border border-gray-300 bg-gray-50/80 px-4 py-3 text-sm text-gray-900 placeholder-gray-400 transition-all duration-200 focus:border-indigo-400 focus:bg-white focus:ring-2 focus:ring-indigo-100 dark:border-[#2e3039] dark:bg-[#1a1d23] dark:text-gray-200 dark:placeholder-gray-600"
                                    />
                                    <InputError :message="form.errors.usage_limit" class="mt-1.5" />
                                </div>
                                <div>
                                    <label for="expires_at" class="mb-1.5 block text-xs font-semibold tracking-wide text-gray-500 uppercase dark:text-gray-300">
                                        Expiry Date <span class="text-xs font-normal normal-case text-gray-400">(optional)</span>
                                    </label>
                                    <input
                                        id="expires_at"
                                        type="datetime-local"
                                        v-model="form.expires_at"
                                        class="block w-full rounded-xl border border-gray-300 bg-gray-50/80 px-4 py-3 text-sm text-gray-900 placeholder-gray-400 transition-all duration-200 focus:border-indigo-400 focus:bg-white focus:ring-2 focus:ring-indigo-100 dark:border-[#2e3039] dark:bg-[#1a1d23] dark:text-gray-200 dark:placeholder-gray-600"
                                    />
                                    <InputError :message="form.errors.expires_at" class="mt-1.5" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div
                        class="mt-6 glass-card rounded-2xl border border-white/60 bg-white/80 p-5 shadow-sm backdrop-blur-sm dark:border-[#2e3039] dark:bg-[#1e2028]/90"
                        data-gsap="fade-up"
                        data-gsap-delay="0.15"
                    >
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="btn-sweep group relative w-full overflow-hidden rounded-xl bg-indigo-600 px-4 py-3.5 text-sm font-semibold text-white shadow-md shadow-indigo-200 transition-all duration-200 hover:bg-indigo-700 hover:shadow-lg hover:shadow-indigo-200 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            <span v-if="form.processing" class="flex items-center justify-center gap-2">
                                <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                                </svg>
                                Creating...
                            </span>
                            <span v-else class="flex items-center justify-center gap-2">
                                Create Coupon
                                <svg
                                    class="h-4 w-4 transition-transform duration-200 group-hover:translate-x-0.5"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14m-7-7l7 7-7 7" />
                                </svg>
                            </span>
                        </button>

                        <Link
                            :href="route('vendor.coupons.index')"
                            class="mt-3 flex w-full items-center justify-center rounded-xl border border-gray-200 bg-white/80 px-4 py-2.5 text-sm font-medium text-gray-600 transition-all duration-200 hover:bg-gray-50 hover:text-gray-800 dark:border-[#2e3039] dark:bg-[#1a1d23] dark:text-gray-300 dark:hover:bg-[#252830] dark:hover:text-gray-100"
                        >
                            Cancel
                        </Link>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
