<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

interface CartItem {
    id: number;
    quantity: number;
    product: {
        id: number;
        name: string;
        price: number;
        stock: number;
        image: string | null;
        category: { id: number; name: string } | null;
    };
}

const props = defineProps<{
    items: CartItem[];
    total: number;
}>();

const isEmpty = computed(() => props.items.length === 0);

function formatPrice(price: number): string {
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(price);
}

function updateQuantity(item: CartItem, quantity: number) {
    if (quantity < 1) return;

    router.patch(route('customer.cart.update', item.id), { quantity }, { preserveScroll: true });
}

function remove(item: CartItem) {
    router.delete(route('customer.cart.destroy', item.id), { preserveScroll: true });
}
</script>

<template>
    <Head title="My Cart" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-100" data-gsap="fade-up">My Cart</h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div
                    v-if="isEmpty"
                    class="rounded-2xl border border-white/60 bg-white/80 px-6 py-16 text-center shadow-sm backdrop-blur-sm dark:border-[#2e3039] dark:bg-[#1e2028]/90"
                    data-gsap="fade-up"
                >
                    <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-gray-100 dark:bg-[#252830]">
                        <svg
                            class="h-8 w-8 text-gray-400 dark:text-gray-500"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                            stroke-width="1.5"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"
                            />
                        </svg>
                    </div>
                    <p class="text-lg font-semibold text-gray-700 dark:text-gray-200">Your cart is empty</p>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Add a few products and they'll show up here.</p>
                    <Link
                        :href="route('customer.products.index')"
                        class="btn-sweep mt-6 inline-flex items-center rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-indigo-700"
                    >
                        Browse Products
                    </Link>
                </div>

                <div v-else class="grid grid-cols-1 gap-8 lg:grid-cols-3">
                    <!-- Items -->
                    <div class="space-y-4 lg:col-span-2" data-gsap="fade-up">
                        <div
                            v-for="item in items"
                            :key="item.id"
                            class="flex items-center gap-4 rounded-2xl border border-white/60 bg-white/80 p-4 shadow-sm backdrop-blur-sm dark:border-[#2e3039] dark:bg-[#1e2028]/90"
                        >
                            <img
                                v-if="item.product.image"
                                :src="item.product.image"
                                :alt="item.product.name"
                                class="h-20 w-20 shrink-0 rounded-xl object-cover"
                            />
                            <div v-else class="flex h-20 w-20 shrink-0 items-center justify-center rounded-xl bg-gray-100 dark:bg-[#1a1d23]">
                                <svg
                                    class="h-8 w-8 text-gray-300 dark:text-gray-600"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                    stroke-width="1"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                                    />
                                </svg>
                            </div>

                            <div class="min-w-0 flex-1">
                                <Link
                                    :href="route('customer.products.show', item.product.id)"
                                    class="font-semibold text-gray-900 transition-colors hover:text-indigo-600 dark:text-gray-100 dark:hover:text-indigo-400"
                                >
                                    {{ item.product.name }}
                                </Link>
                                <p v-if="item.product.category" class="mt-0.5 text-xs text-gray-400 dark:text-gray-500">
                                    {{ item.product.category.name }}
                                </p>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ formatPrice(item.product.price) }} each</p>
                            </div>

                            <div class="flex items-center gap-3">
                                <input
                                    :value="item.quantity"
                                    type="number"
                                    min="1"
                                    :max="item.product.stock"
                                    class="w-20 rounded-xl border border-gray-300 bg-gray-50 px-3 py-2 text-sm text-gray-700 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-400/20 focus:outline-none dark:border-[#2e3039] dark:bg-[#1a1d23] dark:text-gray-200"
                                    @change="updateQuantity(item, Number(($event.target as HTMLInputElement).value))"
                                />
                                <p class="w-24 text-right font-bold text-gray-900 dark:text-gray-100">
                                    {{ formatPrice(item.product.price * item.quantity) }}
                                </p>
                                <button
                                    type="button"
                                    class="flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 transition-colors hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-500/10 dark:hover:text-red-400"
                                    title="Remove"
                                    @click="remove(item)"
                                >
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Summary -->
                    <div data-gsap="fade-up" data-gsap-delay="0.15">
                        <div
                            class="sticky top-8 rounded-2xl border border-white/60 bg-white/80 p-6 shadow-sm backdrop-blur-sm dark:border-[#2e3039] dark:bg-[#1e2028]/90"
                        >
                            <h3 class="mb-6 text-lg font-bold text-gray-800 dark:text-gray-100">Summary</h3>

                            <div class="rounded-xl border border-gray-100 bg-gray-50 p-4 dark:border-[#2e3039] dark:bg-[#1a1d23]">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-500 dark:text-gray-400">Total</span>
                                    <span class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ formatPrice(total) }}</span>
                                </div>
                            </div>

                            <Link
                                :href="route('customer.checkout.index')"
                                class="btn-sweep mt-5 block w-full rounded-xl bg-indigo-600 px-6 py-3 text-center text-sm font-bold text-white shadow-sm transition-colors hover:bg-indigo-700"
                            >
                                Proceed to Checkout
                            </Link>
                            <Link
                                :href="route('customer.products.index')"
                                class="mt-3 block text-center text-sm font-semibold text-indigo-600 transition-colors hover:text-indigo-800 dark:text-indigo-400"
                            >
                                Continue shopping
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
