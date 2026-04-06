<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

interface Category {
    id: number;
    name: string;
    products_count: number;
}

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

const props = defineProps<{
    products: Product[];
    categories: Category[];
    filters: { category: string | null; search: string | null };
}>();

const search = ref(props.filters.search ?? '');
const activeCategory = ref(props.filters.category ?? null);

let searchTimeout: ReturnType<typeof setTimeout> | null = null;

watch(search, (value) => {
    if (searchTimeout) clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(
            route('customer.products.index'),
            { category: activeCategory.value, search: value || undefined },
            { preserveState: true, preserveScroll: true },
        );
    }, 400);
});

function filterByCategory(categoryId: number | null) {
    activeCategory.value = categoryId as string | null;
    router.get(
        route('customer.products.index'),
        { category: categoryId, search: search.value || undefined },
        { preserveState: true, preserveScroll: true },
    );
}

function formatPrice(price: number): string {
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(price);
}
</script>

<template>
    <Head title="Browse Products" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link
                    :href="route('customer.dashboard')"
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
                <h2 class="text-xl font-bold text-gray-800 dark:text-gray-100">Browse Products</h2>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8" data-gsap="fade-up">
                <!-- Search -->
                <div class="mb-6">
                    <div class="relative">
                        <svg
                            class="absolute left-3.5 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400 dark:text-gray-500"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                            stroke-width="1.5"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Search products..."
                            class="w-full rounded-xl border border-gray-300 bg-gray-50 py-3 pl-11 pr-4 text-sm text-gray-700 placeholder-gray-400 transition-colors focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-400/20 dark:border-[#2e3039] dark:bg-[#1a1d23] dark:text-gray-200 dark:placeholder-gray-500 dark:focus:border-indigo-500 dark:focus:ring-indigo-500/20"
                        />
                    </div>
                </div>

                <!-- Category Filter Pills -->
                <div class="mb-8 flex flex-wrap gap-2">
                    <button
                        :class="[
                            'rounded-full px-4 py-1.5 text-sm font-medium transition-all duration-200',
                            !activeCategory
                                ? 'bg-indigo-600 text-white shadow-sm'
                                : 'bg-white/80 text-gray-600 hover:bg-indigo-50 hover:text-indigo-600 dark:bg-[#1e2028]/90 dark:text-gray-400 dark:hover:bg-indigo-500/10 dark:hover:text-indigo-400',
                        ]"
                        @click="filterByCategory(null)"
                    >
                        All
                    </button>
                    <button
                        v-for="category in categories"
                        :key="category.id"
                        :class="[
                            'rounded-full px-4 py-1.5 text-sm font-medium transition-all duration-200',
                            String(activeCategory) === String(category.id)
                                ? 'bg-indigo-600 text-white shadow-sm'
                                : 'bg-white/80 text-gray-600 hover:bg-indigo-50 hover:text-indigo-600 dark:bg-[#1e2028]/90 dark:text-gray-400 dark:hover:bg-indigo-500/10 dark:hover:text-indigo-400',
                        ]"
                        @click="filterByCategory(category.id)"
                    >
                        {{ category.name }}
                        <span class="ml-1 text-xs opacity-60">({{ category.products_count }})</span>
                    </button>
                </div>

                <!-- Empty State -->
                <div
                    v-if="products.length === 0"
                    class="rounded-2xl border border-white/60 bg-white/80 px-6 py-16 text-center shadow-sm backdrop-blur-sm dark:border-[#2e3039] dark:bg-[#1e2028]/90"
                >
                    <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-gray-50 dark:bg-[#1a1d23]">
                        <svg class="h-7 w-7 text-gray-300 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"
                            />
                        </svg>
                    </div>
                    <p class="text-sm font-medium text-gray-400 dark:text-gray-500">No products found</p>
                    <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Try adjusting your search or filter criteria</p>
                </div>

                <!-- Product Grid -->
                <div v-else class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    <Link
                        v-for="product in products"
                        :key="product.id"
                        :href="route('customer.products.show', product.id)"
                        class="group rounded-2xl border border-white/60 bg-white/80 shadow-sm backdrop-blur-sm transition-all duration-200 hover:shadow-md dark:border-[#2e3039] dark:bg-[#1e2028]/90"
                        data-gsap="fade-up"
                    >
                        <!-- Image -->
                        <div class="relative overflow-hidden rounded-t-2xl bg-gray-100 dark:bg-[#1a1d23]">
                            <img
                                v-if="product.image"
                                :src="product.image"
                                :alt="product.name"
                                class="h-48 w-full object-cover transition-transform duration-300 group-hover:scale-105"
                            />
                            <div v-else class="flex h-48 items-center justify-center">
                                <svg class="h-12 w-12 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                                    />
                                </svg>
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="p-5">
                            <div class="mb-2 flex items-start justify-between gap-2">
                                <h3 class="font-semibold text-gray-900 transition-colors group-hover:text-indigo-600 dark:text-gray-100 dark:group-hover:text-indigo-400">
                                    {{ product.name }}
                                </h3>
                                <span class="shrink-0 text-lg font-bold text-indigo-600 dark:text-indigo-400">
                                    {{ formatPrice(product.price) }}
                                </span>
                            </div>

                            <p v-if="product.vendor" class="mb-3 text-xs text-gray-500 dark:text-gray-400">
                                by {{ product.vendor.name }}
                            </p>

                            <div class="flex items-center gap-2">
                                <span
                                    v-if="product.category"
                                    class="rounded-full bg-violet-50 px-2.5 py-0.5 text-[11px] font-medium text-violet-600 dark:bg-violet-500/10 dark:text-violet-400"
                                >
                                    {{ product.category.name }}
                                </span>
                                <span
                                    :class="[
                                        'rounded-full px-2.5 py-0.5 text-[11px] font-medium',
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
                        </div>
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
