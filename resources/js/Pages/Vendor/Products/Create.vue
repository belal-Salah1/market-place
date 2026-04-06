<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import InputError from '@/Components/InputError.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const form = useForm({
    name: '',
    description: '',
    price: '',
    stock: '',
    image: null as File | null,
});

const imagePreview = ref<string | null>(null);
const isDragging = ref(false);

const charCount = computed(() => form.description.length);

const handleImageUpload = (e: Event) => {
    const input = e.target as HTMLInputElement;
    if (input.files?.[0]) {
        setImage(input.files[0]);
    }
};

const setImage = (file: File) => {
    if (file.type.startsWith('image/')) {
        form.image = file;
        const reader = new FileReader();
        reader.onload = (e: ProgressEvent<FileReader>) => {
            imagePreview.value = e.target?.result as string;
        };
        reader.readAsDataURL(file);
    }
};

const removeImage = () => {
    form.image = null;
    imagePreview.value = null;
};

const onDragOver = (e: DragEvent) => {
    e.preventDefault();
    isDragging.value = true;
};

const onDragLeave = () => {
    isDragging.value = false;
};

const onDrop = (e: DragEvent) => {
    e.preventDefault();
    isDragging.value = false;
    const file = e.dataTransfer?.files[0];
    if (file) setImage(file);
};

const submit = () => {
    form.post(route('vendor.products.store'), {
        onSuccess: () => form.reset(),
    });
};
</script>

<template>
    <Head title="Create Product" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <Link
                        :href="route('vendor.dashboard')"
                        class="group flex h-8 w-8 items-center justify-center rounded-lg bg-gray-100 text-gray-400 transition-all duration-200 hover:bg-indigo-50 hover:text-indigo-600"
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
                    <div>
                        <h2 class="text-xl font-bold text-gray-800">Create Product</h2>
                        <p class="text-xs text-gray-400">Fill in the details to list a new product</p>
                    </div>
                </div>
                <span class="hidden items-center gap-1.5 rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-600 ring-1 ring-amber-200/50 sm:inline-flex">
                    <span class="h-1.5 w-1.5 rounded-full bg-amber-400"></span>
                    Draft
                </span>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <form @submit.prevent="submit">
                    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                        <!-- Left Column: Form Fields (2/3) -->
                        <div class="space-y-6 lg:col-span-2">
                            <!-- Product Info Card -->
                            <div class="glass-card animate-fade-in-up delay-0 rounded-2xl border border-white/60 bg-white/80 p-6 shadow-sm backdrop-blur-sm">
                                <div class="mb-5 flex items-center gap-2.5">
                                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-100">
                                        <svg class="h-4 w-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                            />
                                        </svg>
                                    </div>
                                    <h3 class="text-sm font-bold text-gray-800">Product Information</h3>
                                </div>

                                <div class="space-y-5">
                                    <!-- Name -->
                                    <div>
                                        <label for="name" class="mb-1.5 block text-xs font-semibold tracking-wide text-gray-500 uppercase">Product Name</label>
                                        <input
                                            id="name"
                                            type="text"
                                            v-model="form.name"
                                            placeholder="e.g. Wireless Headphones Pro"
                                            class="block w-full rounded-xl border-gray-200 bg-gray-50/80 px-4 py-3 text-sm text-gray-900 placeholder-gray-400 transition-all duration-200 focus:border-indigo-400 focus:bg-white focus:ring-2 focus:ring-indigo-100"
                                        />
                                        <InputError :message="form.errors.name" class="mt-1.5" />
                                    </div>

                                    <!-- Description -->
                                    <div>
                                        <div class="mb-1.5 flex items-center justify-between">
                                            <label for="description" class="text-xs font-semibold tracking-wide text-gray-500 uppercase">Description</label>
                                            <span class="rounded-full bg-gray-100 px-2 py-0.5 text-[10px] font-medium text-gray-400 tabular-nums">{{ charCount }} chars</span>
                                        </div>
                                        <textarea
                                            id="description"
                                            v-model="form.description"
                                            rows="5"
                                            placeholder="Describe your product -- features, materials, dimensions..."
                                            class="block w-full resize-none rounded-xl border-gray-200 bg-gray-50/80 px-4 py-3 text-sm leading-relaxed text-gray-900 placeholder-gray-400 transition-all duration-200 focus:border-indigo-400 focus:bg-white focus:ring-2 focus:ring-indigo-100"
                                        ></textarea>
                                        <InputError :message="form.errors.description" class="mt-1.5" />
                                    </div>
                                </div>
                            </div>

                            <!-- Pricing & Stock Card -->
                            <div class="glass-card animate-fade-in-up delay-2 rounded-2xl border border-white/60 bg-white/80 p-6 shadow-sm backdrop-blur-sm">
                                <div class="mb-5 flex items-center gap-2.5">
                                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-100">
                                        <svg class="h-4 w-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                            />
                                        </svg>
                                    </div>
                                    <h3 class="text-sm font-bold text-gray-800">Pricing & Inventory</h3>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <!-- Price -->
                                    <div class="rounded-xl border border-white/60 bg-gradient-to-br from-emerald-50/50 to-white p-5 shadow-sm">
                                        <label for="price" class="mb-3 block text-[10px] font-bold tracking-widest text-gray-400 uppercase">Price</label>
                                        <div class="flex items-baseline gap-1.5">
                                            <span class="text-lg font-bold text-emerald-400">$</span>
                                            <input
                                                id="price"
                                                type="number"
                                                step="0.01"
                                                min="0"
                                                v-model="form.price"
                                                placeholder="0.00"
                                                class="w-full border-0 bg-transparent p-0 text-2xl font-bold text-gray-900 tabular-nums placeholder-gray-300 focus:ring-0"
                                            />
                                        </div>
                                        <InputError :message="form.errors.price" class="mt-2" />
                                    </div>

                                    <!-- Stock -->
                                    <div class="rounded-xl border border-white/60 bg-gradient-to-br from-violet-50/50 to-white p-5 shadow-sm">
                                        <label for="stock" class="mb-3 block text-[10px] font-bold tracking-widest text-gray-400 uppercase">Stock</label>
                                        <div class="flex items-baseline gap-1.5">
                                            <svg class="h-5 w-5 text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"
                                                />
                                            </svg>
                                            <input
                                                id="stock"
                                                type="number"
                                                min="0"
                                                v-model="form.stock"
                                                placeholder="0"
                                                class="w-full border-0 bg-transparent p-0 text-2xl font-bold text-gray-900 tabular-nums placeholder-gray-300 focus:ring-0"
                                            />
                                        </div>
                                        <InputError :message="form.errors.stock" class="mt-2" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column: Image + Actions (1/3) -->
                        <div class="space-y-6">
                            <!-- Image Upload Card -->
                            <div class="glass-card animate-fade-in-up delay-1 rounded-2xl border border-white/60 bg-white/80 p-5 shadow-sm backdrop-blur-sm">
                                <div class="mb-4 flex items-center gap-2.5">
                                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-violet-100">
                                        <svg class="h-4 w-4 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                                            />
                                        </svg>
                                    </div>
                                    <h3 class="text-sm font-bold text-gray-800">Product Image</h3>
                                </div>

                                <!-- Preview State -->
                                <div v-if="imagePreview" class="group relative overflow-hidden rounded-xl">
                                    <img
                                        :src="imagePreview"
                                        alt="Product preview"
                                        class="aspect-square w-full object-cover transition-transform duration-300 group-hover:scale-105"
                                    />
                                    <div
                                        class="absolute inset-0 flex items-center justify-center bg-black/0 transition-all duration-300 group-hover:bg-black/40"
                                    >
                                        <button
                                            type="button"
                                            @click="removeImage"
                                            class="flex items-center gap-1.5 rounded-full bg-white px-4 py-2 text-xs font-semibold text-red-600 opacity-0 shadow-lg transition-all duration-300 group-hover:opacity-100 hover:bg-red-50"
                                        >
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                                />
                                            </svg>
                                            Remove
                                        </button>
                                    </div>
                                    <div
                                        class="absolute top-2.5 right-2.5 rounded-full bg-emerald-500 px-2.5 py-0.5 text-[10px] font-bold text-white shadow-sm"
                                    >
                                        Ready
                                    </div>
                                </div>

                                <!-- Upload Zone -->
                                <label
                                    v-else
                                    for="image"
                                    class="group relative flex cursor-pointer flex-col items-center justify-center overflow-hidden rounded-xl border-2 border-dashed py-12 transition-all duration-200"
                                    :class="
                                        isDragging
                                            ? 'border-indigo-400 bg-indigo-50 shadow-inner'
                                            : 'border-gray-200 bg-gray-50/50 hover:border-indigo-300 hover:bg-indigo-50/30'
                                    "
                                    @dragover="onDragOver"
                                    @dragleave="onDragLeave"
                                    @drop="onDrop"
                                >
                                    <!-- Background decoration -->
                                    <div
                                        class="absolute -top-4 -right-4 h-24 w-24 rounded-full bg-indigo-100/40 transition-transform duration-300 group-hover:scale-125"
                                    />
                                    <div
                                        class="absolute -bottom-3 -left-3 h-16 w-16 rounded-full bg-violet-100/40 transition-transform duration-300 group-hover:scale-125"
                                    />

                                    <div class="relative flex flex-col items-center">
                                        <div
                                            class="mb-3 flex h-14 w-14 items-center justify-center rounded-xl transition-all duration-200"
                                            :class="
                                                isDragging
                                                    ? 'scale-110 bg-indigo-200 text-indigo-700'
                                                    : 'bg-white text-gray-400 shadow-sm group-hover:text-indigo-500 group-hover:shadow-md'
                                            "
                                        >
                                            <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="1.5"
                                                    d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"
                                                />
                                            </svg>
                                        </div>
                                        <p class="text-sm font-semibold text-gray-600"><span class="text-indigo-600">Upload</span> or drag & drop</p>
                                        <p class="mt-1 text-[11px] text-gray-400">PNG, JPG, GIF &middot; Max 2MB</p>
                                    </div>
                                </label>

                                <input
                                    type="file"
                                    id="image"
                                    class="hidden"
                                    accept="image/jpeg,image/png,image/jpg,image/gif"
                                    @change="handleImageUpload"
                                />
                                <InputError :message="form.errors.image" class="mt-2" />
                            </div>

                            <!-- Actions Card -->
                            <div class="glass-card animate-fade-in-up delay-3 rounded-2xl border border-white/60 bg-white/80 p-5 shadow-sm backdrop-blur-sm">
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
                                        Publish Product
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
                                    :href="route('vendor.dashboard')"
                                    class="mt-3 flex w-full items-center justify-center rounded-xl border border-gray-200 bg-white/80 px-4 py-2.5 text-sm font-medium text-gray-600 transition-all duration-200 hover:bg-gray-50 hover:text-gray-800"
                                >
                                    Cancel
                                </Link>
                            </div>

                            <!-- Tips Card -->
                            <div class="animate-fade-in-up delay-5 rounded-xl border border-indigo-100/60 bg-indigo-50/50 px-5 py-4 backdrop-blur-sm">
                                <div class="flex items-center gap-2">
                                    <svg class="h-4 w-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <p class="text-xs font-semibold text-indigo-900/70">Quick tips</p>
                                </div>
                                <ul class="mt-2.5 space-y-1.5 text-xs text-indigo-700/60">
                                    <li class="flex items-start gap-2">
                                        <span class="mt-1.5 h-1 w-1 flex-shrink-0 rounded-full bg-indigo-400" />
                                        Use a clear, well-lit product photo
                                    </li>
                                    <li class="flex items-start gap-2">
                                        <span class="mt-1.5 h-1 w-1 flex-shrink-0 rounded-full bg-indigo-400" />
                                        Write a detailed description with specs
                                    </li>
                                    <li class="flex items-start gap-2">
                                        <span class="mt-1.5 h-1 w-1 flex-shrink-0 rounded-full bg-indigo-400" />
                                        Set competitive pricing for better sales
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>