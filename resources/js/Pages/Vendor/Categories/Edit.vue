<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import InputError from '@/Components/InputError.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    category: {
        type: Object as () => { id: number; name: string; parent_id: number | null },
        required: true,
    },
    parentCategories: {
        type: Array as () => Array<{ id: number; name: string }>,
        default: () => [],
    },
});

const form = useForm({
    name: props.category.name,
    parent_id: props.category.parent_id ?? '',
});

const selectedParentName = computed(() => {
    if (!form.parent_id) return null;
    const found = props.parentCategories.find((c) => c.id === Number(form.parent_id));
    return found ? found.name : null;
});

const submit = () => {
    form.put(route('vendor.categories.update', props.category.id));
};
</script>

<template>
    <Head title="Edit Category" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link
                    :href="route('vendor.categories.index')"
                    class="group flex h-8 w-8 items-center justify-center rounded-lg bg-white/60 text-gray-400 backdrop-blur-sm transition-all duration-200 hover:bg-indigo-50 hover:text-indigo-600 dark:bg-white/5 dark:text-gray-500 dark:hover:bg-indigo-500/10 dark:hover:text-indigo-400"
                >
                    <svg class="h-4 w-4 transition-transform duration-200 group-hover:-translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </Link>
                <h2 class="text-xl font-bold text-gray-800 dark:text-gray-100">Edit Category</h2>
            </div>
        </template>

        <div class="py-10">
            <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
                <form @submit.prevent="submit">
                    <div class="glass-card rounded-2xl border border-white/60 bg-white/80 p-6 shadow-sm backdrop-blur-sm sm:p-8 dark:bg-[#1e2028]/90 dark:border-[#2e3039]" data-gsap="fade-up" data-gsap-delay="0.08">
                        <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-indigo-300/40 to-transparent" />

                        <div class="mb-6 flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-violet-500 shadow-lg shadow-indigo-200/50" data-gsap="scale-in">
                                <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-gray-800 dark:text-gray-100">Category Details</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Update this category</p>
                            </div>
                        </div>

                        <div class="space-y-5">
                            <div data-gsap="fade-up" data-gsap-delay="0.15">
                                <label for="name" class="mb-1.5 block text-xs font-semibold text-gray-600 dark:text-gray-300">
                                    Category Name <span class="text-red-400">*</span>
                                </label>
                                <input
                                    id="name"
                                    type="text"
                                    v-model="form.name"
                                    placeholder="e.g. Electronics, Fashion, Home & Garden"
                                    class="block w-full rounded-xl border border-gray-300 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 transition-all duration-200 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 dark:border-[#2e3039] dark:bg-[#1a1d23] dark:text-gray-200 dark:placeholder-gray-600 dark:focus:bg-[#1a1d23]"
                                />
                                <InputError :message="form.errors.name" class="mt-1.5" />
                            </div>

                            <div data-gsap="fade-up" data-gsap-delay="0.22">
                                <label for="parent_id" class="mb-1.5 block text-xs font-semibold text-gray-600 dark:text-gray-300">
                                    Parent Category
                                </label>
                                <select
                                    id="parent_id"
                                    v-model="form.parent_id"
                                    class="block w-full rounded-xl border border-gray-300 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 transition-all duration-200 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 dark:border-[#2e3039] dark:bg-[#1a1d23] dark:text-gray-200 dark:focus:bg-[#1a1d23]"
                                >
                                    <option value="">None — Top Level Category</option>
                                    <option
                                        v-for="cat in parentCategories"
                                        :key="cat.id"
                                        :value="cat.id"
                                    >
                                        {{ cat.name }}
                                    </option>
                                </select>
                                <InputError :message="form.errors.parent_id" class="mt-1.5" />
                            </div>

                            <div class="rounded-xl border border-dashed border-gray-200 bg-gray-50/50 p-4 dark:border-[#2e3039] dark:bg-[#1a1d23]/50" data-gsap="fade-up" data-gsap-delay="0.3">
                                <p class="mb-2 text-[10px] font-bold uppercase tracking-widest text-gray-400 dark:text-gray-500">Preview</p>
                                <div class="flex items-center gap-2 text-sm">
                                    <span v-if="selectedParentName" class="rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs font-medium text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                                        {{ selectedParentName }}
                                    </span>
                                    <svg v-if="selectedParentName" class="h-3.5 w-3.5 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                    <span
                                        class="rounded-full px-2.5 py-0.5 text-xs font-semibold transition-colors duration-200"
                                        :class="form.name ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-400' : 'bg-gray-100 text-gray-400 dark:bg-[#252830] dark:text-gray-500'"
                                    >
                                        {{ form.name || 'Category Name' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-8 flex items-center justify-between border-t border-gray-100/80 dark:border-[#2e3039] pt-6" data-gsap="fade-up" data-gsap-delay="0.45">
                            <Link
                                :href="route('vendor.categories.index')"
                                class="text-sm font-medium text-gray-400 transition-colors duration-200 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300"
                            >
                                Cancel
                            </Link>

                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="btn-sweep inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-6 py-2.5 text-sm font-semibold text-white shadow-md shadow-indigo-200/50 dark:shadow-indigo-900/30 transition-all duration-200 hover:bg-indigo-700 hover:shadow-lg hover:shadow-indigo-200/50 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                <span v-if="form.processing" class="flex items-center gap-2">
                                    <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                                    </svg>
                                    Saving...
                                </span>
                                <span v-else class="flex items-center gap-2">
                                    Save Changes
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                </span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
