<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

interface CartItem {
    id: number;
    quantity: number;
    product: { id: number; name: string; price: number };
}

interface PaymentMethod {
    value: string;
    label: string;
}

defineProps<{
    items: CartItem[];
    total: number;
    paymentMethods: PaymentMethod[];
}>();

const form = useForm({
    payment_method: 'cash',
});

function formatPrice(price: number): string {
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(price);
}

function submit() {
    form.post(route('customer.orders.store'));
}
</script>

<template>
    <Head title="Checkout" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link
                    :href="route('customer.cart.index')"
                    class="group flex h-8 w-8 items-center justify-center rounded-lg bg-white/60 text-gray-400 backdrop-blur-sm transition-all duration-200 hover:bg-indigo-50 hover:text-indigo-600 dark:bg-white/5 dark:text-gray-500 dark:hover:bg-indigo-500/10 dark:hover:text-indigo-400"
                >
                    <svg
                        class="h-4 w-4 transition-transform duration-200 group-hover:-translate-x-0.5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </Link>
                <h2 class="text-xl font-bold text-gray-800 dark:text-gray-100">Checkout</h2>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
                    <!-- Order review -->
                    <div class="lg:col-span-2" data-gsap="fade-up">
                        <div
                            class="overflow-hidden rounded-2xl border border-white/60 bg-white/80 shadow-sm backdrop-blur-sm dark:border-[#2e3039] dark:bg-[#1e2028]/90"
                        >
                            <div class="border-b border-gray-100/80 px-6 py-5 dark:border-[#2e3039]">
                                <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100">Order Review</h3>
                            </div>
                            <div class="divide-y divide-gray-50 dark:divide-[#2e3039]">
                                <div v-for="item in items" :key="item.id" class="flex items-center justify-between px-6 py-4">
                                    <div>
                                        <p class="font-semibold text-gray-900 dark:text-gray-100">{{ item.product.name }}</p>
                                        <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">
                                            {{ item.quantity }} &times; {{ formatPrice(item.product.price) }}
                                        </p>
                                    </div>
                                    <p class="font-bold text-gray-900 dark:text-gray-100">
                                        {{ formatPrice(item.product.price * item.quantity) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment -->
                    <div data-gsap="fade-up" data-gsap-delay="0.15">
                        <div
                            class="sticky top-8 rounded-2xl border border-white/60 bg-white/80 p-6 shadow-sm backdrop-blur-sm dark:border-[#2e3039] dark:bg-[#1e2028]/90"
                        >
                            <div class="mb-6 flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-100 dark:bg-indigo-500/10">
                                    <svg
                                        class="h-5 w-5 text-indigo-600 dark:text-indigo-400"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                        stroke-width="1.5"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z"
                                        />
                                    </svg>
                                </div>
                                <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100">Payment</h3>
                            </div>

                            <form class="space-y-5" @submit.prevent="submit">
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Payment Method</label>
                                    <select
                                        v-model="form.payment_method"
                                        class="w-full rounded-xl border border-gray-300 bg-gray-50 px-4 py-2.5 text-sm text-gray-700 transition-colors focus:border-indigo-400 focus:ring-2 focus:ring-indigo-400/20 focus:outline-none dark:border-[#2e3039] dark:bg-[#1a1d23] dark:text-gray-200"
                                    >
                                        <option v-for="method in paymentMethods" :key="method.value" :value="method.value">
                                            {{ method.label }}
                                        </option>
                                    </select>
                                    <p v-if="form.errors.payment_method" class="mt-1 text-xs text-red-500">{{ form.errors.payment_method }}</p>
                                </div>

                                <div class="rounded-xl border border-gray-100 bg-gray-50 p-4 dark:border-[#2e3039] dark:bg-[#1a1d23]">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-gray-500 dark:text-gray-400">Total</span>
                                        <span class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ formatPrice(total) }}</span>
                                    </div>
                                </div>

                                <p v-if="form.errors.items" class="text-xs text-red-500">{{ form.errors.items }}</p>

                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="w-full rounded-xl bg-indigo-600 px-6 py-3 text-sm font-bold text-white shadow-sm transition-all duration-200 hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none disabled:cursor-not-allowed disabled:opacity-50 dark:focus:ring-offset-[#1e2028]"
                                >
                                    {{ form.processing ? 'Placing Order...' : 'Place Order' }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
