<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const deleteCategory = (id: number) => {
    if (confirm('Are you sure you want to delete this category?')) {
        router.delete(route('vendor.categories.destroy', id));
    }
};

interface Category {
    id: number;
    name: string;
    parent_id: number | null;
    parent: { id: number; name: string } | null;
    children: { id: number; name: string }[];
    products_count: number;
}

defineProps<{
    categories: Category[];
}>();
</script>

<template>
    <Head title="My Categories" />

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
                    <h2 class="text-xl font-bold text-gray-800">My Categories</h2>
                </div>
                <Link
                    :href="route('vendor.categories.create')"
                    class="btn-sweep inline-flex items-center gap-1.5 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-indigo-700"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    Add Category
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Empty State -->
                <div v-if="categories.length === 0" class="animate-fade-in-up rounded-2xl border border-white/60 bg-white/80 px-6 py-16 text-center shadow-sm backdrop-blur-sm">
                    <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-gray-50">
                        <svg class="h-7 w-7 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                        </svg>
                    </div>
                    <p class="text-sm font-medium text-gray-500">No categories yet</p>
                    <p class="mt-1 text-xs text-gray-400">Create your first category to organize products</p>
                    <Link
                        :href="route('vendor.categories.create')"
                        class="mt-4 inline-flex items-center gap-1.5 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-indigo-700"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        Add Category
                    </Link>
                </div>

                <!-- Categories Grid -->
                <div v-else class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="category in categories"
                        :key="category.id"
                        class="glass-card animate-fade-in-up rounded-2xl border border-white/60 bg-white/80 p-5 shadow-sm backdrop-blur-sm"
                    >
                        <div class="flex items-start justify-between">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-violet-500 to-indigo-500 shadow-lg shadow-violet-200/50">
                                    <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-900">{{ category.name }}</p>
                                    <p v-if="category.parent" class="mt-0.5 text-xs text-gray-500">
                                        in {{ category.parent.name }}
                                    </p>
                                    <p v-else class="mt-0.5 text-xs text-gray-400">Top-level category</p>
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <Link
                                    :href="route('vendor.categories.edit', category.id)"
                                    class="rounded-lg bg-indigo-50 px-3 py-1.5 text-xs font-semibold text-indigo-600 transition-colors hover:bg-indigo-100"
                                >
                                    Edit
                                </Link>
                                <button
                                    @click="deleteCategory(category.id)"
                                    class="rounded-lg bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-600 transition-colors hover:bg-red-100"
                                >
                                    Delete
                                </button>
                            </div>
                        </div>

                        <div class="mt-4 flex items-center gap-3">
                            <span class="rounded-full bg-indigo-50 px-2.5 py-0.5 text-[11px] font-semibold text-indigo-600">
                                {{ category.products_count }} {{ category.products_count === 1 ? 'product' : 'products' }}
                            </span>
                            <span v-if="category.children.length > 0" class="rounded-full bg-violet-50 px-2.5 py-0.5 text-[11px] font-semibold text-violet-600">
                                {{ category.children.length }} {{ category.children.length === 1 ? 'subcategory' : 'subcategories' }}
                            </span>
                        </div>

                        <!-- Subcategories -->
                        <div v-if="category.children.length > 0" class="mt-3 flex flex-wrap gap-1.5">
                            <span
                                v-for="child in category.children"
                                :key="child.id"
                                class="rounded-full bg-gray-100 px-2 py-0.5 text-[10px] font-medium text-gray-500"
                            >
                                {{ child.name }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
