<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

interface Product {
    id: number;
    name: string;
    vendor: { id: number; name: string };
}

interface ExistingReview {
    rating: number;
    comment: string;
}

const props = defineProps<{
    product: Product;
    existingReview: ExistingReview | null;
}>();

const form = useForm({
    rating: props.existingReview?.rating ?? 0,
    comment: props.existingReview?.comment ?? '',
});

const hoverRating = ref(0);

function submit() {
    form.post(route('customer.reviews.store', props.product.id));
}
</script>

<template>
    <Head :title="existingReview ? 'Edit Review' : 'Write a Review'" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link
                    :href="route('customer.products.show', product.id)"
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
                <h2 class="text-xl font-bold text-gray-800 dark:text-gray-100">
                    {{ existingReview ? 'Edit Review' : 'Write a Review' }}
                </h2>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
                <div
                    class="rounded-2xl border border-white/60 bg-white/80 p-6 shadow-sm backdrop-blur-sm sm:p-8 dark:border-[#2e3039] dark:bg-[#1e2028]/90"
                    data-gsap="fade-up"
                >
                    <!-- Product Info -->
                    <div class="mb-6 border-b border-gray-100 pb-6 dark:border-[#2e3039]">
                        <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100">{{ product.name }}</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Sold by <span class="font-medium text-gray-700 dark:text-gray-300">{{ product.vendor.name }}</span>
                        </p>
                    </div>

                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Star Rating -->
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Rating</label>
                            <div class="flex items-center gap-1">
                                <button
                                    v-for="star in 5"
                                    :key="star"
                                    type="button"
                                    class="focus:outline-none"
                                    @mouseenter="hoverRating = star"
                                    @mouseleave="hoverRating = 0"
                                    @click="form.rating = star"
                                >
                                    <svg
                                        class="h-8 w-8 cursor-pointer transition-colors duration-150"
                                        :class="star <= (hoverRating || form.rating) ? 'text-amber-400' : 'text-gray-300 dark:text-gray-600'"
                                        :fill="star <= (hoverRating || form.rating) ? 'currentColor' : 'none'"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                        stroke-width="1.5"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"
                                        />
                                    </svg>
                                </button>
                            </div>
                            <p v-if="form.errors.rating" class="mt-1 text-xs text-red-500">{{ form.errors.rating }}</p>
                        </div>

                        <!-- Comment -->
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Comment</label>
                            <textarea
                                v-model="form.comment"
                                rows="5"
                                placeholder="Share your experience with this product..."
                                class="w-full rounded-xl border border-gray-300 bg-gray-50 px-4 py-3 text-sm text-gray-700 transition-colors focus:border-indigo-400 focus:ring-2 focus:ring-indigo-400/20 focus:outline-none dark:border-[#2e3039] dark:bg-[#1a1d23] dark:text-gray-200 dark:placeholder-gray-500 dark:focus:border-indigo-500 dark:focus:ring-indigo-500/20"
                            />
                            <p v-if="form.errors.comment" class="mt-1 text-xs text-red-500">{{ form.errors.comment }}</p>
                        </div>

                        <!-- Submit -->
                        <button
                            type="submit"
                            :disabled="form.processing || form.rating === 0"
                            class="w-full rounded-xl bg-indigo-600 px-6 py-3 text-sm font-bold text-white shadow-sm transition-all duration-200 hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none disabled:cursor-not-allowed disabled:opacity-50 dark:focus:ring-offset-[#1e2028]"
                        >
                            <span v-if="form.processing">Submitting...</span>
                            <span v-else>{{ existingReview ? 'Update Review' : 'Submit Review' }}</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
