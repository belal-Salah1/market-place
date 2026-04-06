<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

interface Product {
    id: number;
    name: string;
    description: string;
    price: number;
    stock: number;
    image: string | null;
    category: { id: number; name: string } | null;
    vendor: { id: number; name: string } | null;
}

interface PaymentMethod {
    value: string;
    label: string;
}

const props = defineProps<{
    product: Product;
    paymentMethods: PaymentMethod[];
}>();

const form = useForm({
    items: [{ product_id: props.product.id, quantity: 1 }],
    payment_method: 'cash',
});

const total = computed(() => props.product.price * form.items[0].quantity);

function formatPrice(price: number): string {
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(price);
}

function submit() {
    form.post(route('customer.orders.store'));
}
</script>

<template>
    <Head :title="product.name" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link
                    :href="route('customer.products.index')"
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
                <h2 class="text-xl font-bold text-gray-800 dark:text-gray-100">Product Details</h2>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 gap-8 lg:grid-cols-3" data-gsap="fade-up">
                    <!-- Product Details (Left) -->
                    <div class="lg:col-span-2">
                        <div class="overflow-hidden rounded-2xl border border-white/60 bg-white/80 shadow-sm backdrop-blur-sm dark:border-[#2e3039] dark:bg-[#1e2028]/90">
                            <!-- Image -->
                            <div class="relative overflow-hidden bg-gray-100 dark:bg-[#1a1d23]">
                                <img
                                    v-if="product.image"
                                    :src="product.image"
                                    :alt="product.name"
                                    class="h-72 w-full object-cover sm:h-96"
                                />
                                <div v-else class="flex h-72 items-center justify-center sm:h-96">
                                    <svg class="h-16 w-16 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                                        />
                                    </svg>
                                </div>
                            </div>

                            <!-- Info -->
                            <div class="p-6 sm:p-8">
                                <div class="mb-4 flex flex-wrap items-center gap-2">
                                    <span
                                        v-if="product.category"
                                        class="rounded-full bg-violet-50 px-3 py-1 text-xs font-medium text-violet-600 dark:bg-violet-500/10 dark:text-violet-400"
                                    >
                                        {{ product.category.name }}
                                    </span>
                                    <span
                                        :class="[
                                            'rounded-full px-3 py-1 text-xs font-medium',
                                            product.stock > 10
                                                ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400'
                                                : product.stock > 0
                                                  ? 'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400'
                                                  : 'bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400',
                                        ]"
                                    >
                                        {{ product.stock > 0 ? product.stock + ' in stock' : 'Out of stock' }}
                                    </span>
                                </div>

                                <h1 class="mb-2 text-2xl font-bold text-gray-900 dark:text-gray-100">{{ product.name }}</h1>

                                <div v-if="product.vendor" class="mb-4 flex items-center gap-3">
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        Sold by <span class="font-medium text-gray-700 dark:text-gray-300">{{ product.vendor.name }}</span>
                                    </p>
                                    <Link
                                        :href="route('messages.show', product.vendor.id)"
                                        class="inline-flex items-center gap-1.5 rounded-lg bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-600 transition-colors hover:bg-blue-100 dark:bg-blue-500/10 dark:text-blue-400 dark:hover:bg-blue-500/20"
                                    >
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" /></svg>
                                        Message Vendor
                                    </Link>
                                </div>

                                <p class="mb-6 text-3xl font-bold text-indigo-600 dark:text-indigo-400">{{ formatPrice(product.price) }}</p>

                                <div class="border-t border-gray-100 pt-6 dark:border-[#2e3039]">
                                    <h3 class="mb-2 text-sm font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Description</h3>
                                    <p class="leading-relaxed text-gray-600 dark:text-gray-400">{{ product.description }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Order Form (Right) -->
                    <div data-gsap="fade-up" data-gsap-delay="0.15">
                        <div class="sticky top-8 rounded-2xl border border-white/60 bg-white/80 p-6 shadow-sm backdrop-blur-sm dark:border-[#2e3039] dark:bg-[#1e2028]/90">
                            <div class="mb-6 flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-100 dark:bg-indigo-500/10">
                                    <svg class="h-5 w-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z" />
                                    </svg>
                                </div>
                                <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100">Place Order</h3>
                            </div>

                            <form @submit.prevent="submit" class="space-y-5">
                                <!-- Quantity -->
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Quantity</label>
                                    <input
                                        v-model.number="form.items[0].quantity"
                                        type="number"
                                        min="1"
                                        :max="product.stock"
                                        class="w-full rounded-xl border border-gray-300 bg-gray-50 px-4 py-2.5 text-sm text-gray-700 transition-colors focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-400/20 dark:border-[#2e3039] dark:bg-[#1a1d23] dark:text-gray-200 dark:focus:border-indigo-500 dark:focus:ring-indigo-500/20"
                                    />
                                    <p v-if="form.errors.items" class="mt-1 text-xs text-red-500">{{ form.errors.items }}</p>
                                </div>

                                <!-- Payment Method -->
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Payment Method</label>
                                    <select
                                        v-model="form.payment_method"
                                        class="w-full rounded-xl border border-gray-300 bg-gray-50 px-4 py-2.5 text-sm text-gray-700 transition-colors focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-400/20 dark:border-[#2e3039] dark:bg-[#1a1d23] dark:text-gray-200 dark:focus:border-indigo-500 dark:focus:ring-indigo-500/20"
                                    >
                                        <option v-for="method in paymentMethods" :key="method.value" :value="method.value">
                                            {{ method.label }}
                                        </option>
                                    </select>
                                    <p v-if="form.errors.payment_method" class="mt-1 text-xs text-red-500">{{ form.errors.payment_method }}</p>
                                </div>

                                <!-- Total -->
                                <div class="rounded-xl border border-gray-100 bg-gray-50 p-4 dark:border-[#2e3039] dark:bg-[#1a1d23]">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-gray-500 dark:text-gray-400">Total</span>
                                        <span class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ formatPrice(total) }}</span>
                                    </div>
                                    <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">
                                        {{ form.items[0].quantity }} x {{ formatPrice(product.price) }}
                                    </p>
                                </div>

                                <!-- Submit -->
                                <button
                                    type="submit"
                                    :disabled="form.processing || product.stock === 0"
                                    class="w-full rounded-xl bg-indigo-600 px-6 py-3 text-sm font-bold text-white shadow-sm transition-all duration-200 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 dark:focus:ring-offset-[#1e2028]"
                                >
                                    <span v-if="form.processing">Placing Order...</span>
                                    <span v-else-if="product.stock === 0">Out of Stock</span>
                                    <span v-else>Place Order</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
