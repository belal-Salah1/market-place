<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

interface Category {
    id: number;
    name: string;
    parent_id: number | null;
}

interface Product {
    id: number;
    name: string;
    description: string;
    price: number;
    stock: number;
    image: string | null;
    category: Category | null;
}

interface Vendor {
    id: number;
    name: string;
    email: string;
    is_approved: boolean;
    created_at: string;
}

defineProps<{
    vendor: Vendor;
    products: Product[];
    categories: Category[];
}>();
</script>

<template>
    <Head :title="`Vendor: ${vendor.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link
                    :href="route('admin.vendors.index')"
                    class="group flex h-8 w-8 items-center justify-center rounded-lg bg-white/60 text-gray-400 backdrop-blur-sm transition-all duration-200 hover:bg-indigo-50 hover:text-indigo-600"
                >
                    <svg class="h-4 w-4 transition-transform duration-200 group-hover:-translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </Link>
                <h2 class="text-xl font-bold text-gray-800">{{ vendor.name }}</h2>
                <span
                    :class="[
                        'rounded-full px-2.5 py-0.5 text-[11px] font-bold uppercase tracking-wider',
                        vendor.is_approved ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700',
                    ]"
                >
                    {{ vendor.is_approved ? 'Approved' : 'Pending' }}
                </span>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Vendor Info -->
                <div class="animate-fade-in-up mb-6 rounded-2xl border border-white/60 bg-white/80 p-6 shadow-sm backdrop-blur-sm">
                    <div class="flex items-center gap-4">
                        <div class="flex h-14 w-14 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 to-violet-500 text-white shadow-lg shadow-indigo-200/50">
                            <span class="text-lg font-bold">{{ vendor.name.substring(0, 2).toUpperCase() }}</span>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">{{ vendor.name }}</h3>
                            <p class="text-sm text-gray-500">{{ vendor.email }}</p>
                        </div>
                    </div>
                    <div class="mt-4 flex gap-4">
                        <div class="rounded-xl bg-indigo-50/60 px-4 py-2.5">
                            <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400">Products</p>
                            <p class="text-lg font-bold text-indigo-600">{{ products.length }}</p>
                        </div>
                        <div class="rounded-xl bg-violet-50/60 px-4 py-2.5">
                            <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400">Categories</p>
                            <p class="text-lg font-bold text-violet-600">{{ categories.length }}</p>
                        </div>
                    </div>
                </div>

                <!-- Categories -->
                <div class="animate-fade-in-up delay-1 mb-6 rounded-2xl border border-white/60 bg-white/80 p-6 shadow-sm backdrop-blur-sm">
                    <div class="mb-4 flex items-center gap-2.5">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-violet-100">
                            <svg class="h-4 w-4 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                            </svg>
                        </div>
                        <h3 class="text-sm font-bold text-gray-800">Categories</h3>
                    </div>

                    <div v-if="categories.length === 0" class="py-8 text-center">
                        <p class="text-sm text-gray-400">No categories yet</p>
                    </div>

                    <div v-else class="flex flex-wrap gap-2">
                        <span
                            v-for="category in categories"
                            :key="category.id"
                            class="rounded-full bg-violet-50 px-3 py-1.5 text-xs font-semibold text-violet-700"
                        >
                            {{ category.name }}
                        </span>
                    </div>
                </div>

                <!-- Products -->
                <div class="animate-fade-in-up delay-2 rounded-2xl border border-white/60 bg-white/80 shadow-sm backdrop-blur-sm">
                    <div class="flex items-center justify-between border-b border-gray-100/80 px-6 py-5">
                        <div class="flex items-center gap-2.5">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-100">
                                <svg class="h-4 w-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                            </div>
                            <h3 class="text-sm font-bold text-gray-800">Products</h3>
                        </div>
                        <span class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-600">
                            {{ products.length }} total
                        </span>
                    </div>

                    <div v-if="products.length === 0" class="px-6 py-12 text-center">
                        <p class="text-sm text-gray-400">No products yet</p>
                    </div>

                    <div v-else class="divide-y divide-gray-50">
                        <div
                            v-for="product in products"
                            :key="product.id"
                            class="flex items-center justify-between px-6 py-4"
                        >
                            <div class="flex items-center gap-4 min-w-0">
                                <div v-if="product.image" class="h-12 w-12 flex-shrink-0 overflow-hidden rounded-xl">
                                    <img :src="`/storage/${product.image}`" :alt="product.name" class="h-full w-full object-cover" />
                                </div>
                                <div v-else class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-xl bg-gray-100">
                                    <svg class="h-5 w-5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
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
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
