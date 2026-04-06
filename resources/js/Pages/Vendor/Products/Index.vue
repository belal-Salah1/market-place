<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

interface Category {
    id: number;
    name: string;
}

interface Product {
    id: number;
    name: string;
    description: string;
    price: number;
    stock: number;
    image: string | null;
    category: Category | null;
    created_at: string;
}

defineProps<{
    products: Product[];
}>();
</script>

<template>
    <Head title="My Products" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <Link
                        :href="route('vendor.dashboard')"
                        class="group flex h-8 w-8 items-center justify-center rounded-lg bg-white/60 text-gray-400 backdrop-blur-sm transition-all duration-200 hover:bg-indigo-50 hover:text-indigo-600"
                    >
                        <svg class="h-4 w-4 transition-transform duration-200 group-hover:-translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </Link>
                    <h2 class="text-xl font-bold text-gray-800">My Products</h2>
                </div>
                <Link
                    :href="route('vendor.products.create')"
                    class="btn-sweep inline-flex items-center gap-1.5 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-indigo-700"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    Add Product
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Empty State -->
                <div v-if="products.length === 0" class="animate-fade-in-up rounded-2xl border border-white/60 bg-white/80 px-6 py-16 text-center shadow-sm backdrop-blur-sm">
                    <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-gray-50">
                        <svg class="h-7 w-7 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </div>
                    <p class="text-sm font-medium text-gray-500">No products yet</p>
                    <p class="mt-1 text-xs text-gray-400">Create your first product to get started</p>
                    <Link
                        :href="route('vendor.products.create')"
                        class="mt-4 inline-flex items-center gap-1.5 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-indigo-700"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        Add Product
                    </Link>
                </div>

                <!-- Products Table -->
                <div v-else class="animate-fade-in-up overflow-hidden rounded-2xl border border-white/60 bg-white/80 shadow-sm backdrop-blur-sm">
                    <div class="flex items-center justify-between border-b border-gray-100/80 px-6 py-5">
                        <div class="flex items-center gap-2.5">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-100">
                                <svg class="h-4 w-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                            </div>
                            <h3 class="text-sm font-bold text-gray-800">All Products</h3>
                        </div>
                        <span class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-600">
                            {{ products.length }} total
                        </span>
                    </div>

                    <div class="divide-y divide-gray-50">
                        <div
                            v-for="product in products"
                            :key="product.id"
                            class="flex items-center justify-between px-6 py-4 transition-colors hover:bg-gray-50/50"
                        >
                            <div class="flex items-center gap-4 min-w-0">
                                <div v-if="product.image" class="h-12 w-12 flex-shrink-0 overflow-hidden rounded-xl">
                                    <img :src="`/storage/${product.image}`" :alt="product.name" class="h-full w-full object-cover" />
                                </div>
                                <div v-else class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-xl bg-gray-100">
                                    <svg class="h-5 w-5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <div class="min-w-0">
                                    <p class="truncate font-semibold text-gray-900">{{ product.name }}</p>
                                    <p class="mt-0.5 truncate text-xs text-gray-500">{{ product.description || 'No description' }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-4 flex-shrink-0 ml-4">
                                <span v-if="product.category" class="hidden rounded-full bg-violet-50 px-2.5 py-0.5 text-[11px] font-medium text-violet-600 sm:inline-block">
                                    {{ product.category.name }}
                                </span>
                                <div class="text-right">
                                    <p class="text-sm font-bold text-gray-900">${{ Number(product.price).toFixed(2) }}</p>
                                    <p class="text-[11px] text-gray-400">{{ product.stock }} in stock</p>
                                </div>
                                <Link
                                    :href="route('vendor.products.edit', product.id)"
                                    class="rounded-lg bg-indigo-50 px-3 py-1.5 text-xs font-semibold text-indigo-600 transition-colors hover:bg-indigo-100"
                                >
                                    Edit
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
