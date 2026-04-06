<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

interface OrderItem {
    id: number;
    quantity: number;
    price: number;
    product: { id: number; name: string; image: string | null; price: number };
}

interface Payment {
    amount: number;
    method: string;
    status: string;
}

interface Order {
    id: number;
    total_price: number;
    status: string;
    created_at: string;
    items: OrderItem[];
    payment: Payment | null;
}

defineProps<{
    order: Order;
}>();

function formatPrice(price: number): string {
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(price);
}

function formatDate(dateString: string): string {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

function statusClasses(status: string): string {
    const map: Record<string, string> = {
        pending: 'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400',
        processing: 'bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400',
        shipped: 'bg-violet-50 text-violet-600 dark:bg-violet-500/10 dark:text-violet-400',
        delivered: 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400',
        cancelled: 'bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400',
    };
    return map[status] ?? 'bg-gray-50 text-gray-600 dark:bg-gray-500/10 dark:text-gray-400';
}

function paymentStatusClasses(status: string): string {
    const map: Record<string, string> = {
        completed: 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400',
        pending: 'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400',
        failed: 'bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400',
    };
    return map[status] ?? 'bg-gray-50 text-gray-600 dark:bg-gray-500/10 dark:text-gray-400';
}

function formatPaymentMethod(method: string): string {
    return method
        .replace(/_/g, ' ')
        .replace(/\b\w/g, (c) => c.toUpperCase());
}
</script>

<template>
    <Head :title="'Order #' + order.id" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link
                    :href="route('customer.orders.index')"
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
                <h2 class="text-xl font-bold text-gray-800 dark:text-gray-100">Order Details</h2>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8" data-gsap="fade-up">
                <!-- Receipt Card -->
                <div class="overflow-hidden rounded-2xl border border-white/60 bg-white/80 shadow-sm backdrop-blur-sm dark:border-[#2e3039] dark:bg-[#1e2028]/90">
                    <!-- Header -->
                    <div class="border-b border-gray-100/80 px-6 py-6 dark:border-[#2e3039]">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Order #{{ order.id }}</h1>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ formatDate(order.created_at) }}</p>
                            </div>
                            <span
                                :class="[
                                    'inline-flex w-fit rounded-full px-3 py-1 text-xs font-bold uppercase tracking-wider',
                                    statusClasses(order.status),
                                ]"
                            >
                                {{ order.status }}
                            </span>
                        </div>
                    </div>

                    <!-- Items -->
                    <div class="border-b border-gray-100/80 dark:border-[#2e3039]">
                        <div class="px-6 py-4">
                            <h3 class="mb-3 text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Items</h3>
                        </div>
                        <div class="divide-y divide-gray-50 dark:divide-[#2e3039]">
                            <div
                                v-for="item in order.items"
                                :key="item.id"
                                class="flex items-center gap-4 px-6 py-4"
                                data-gsap="fade-up"
                                data-gsap-delay="0.1"
                            >
                                <!-- Product Image -->
                                <div class="flex h-14 w-14 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-gray-100 dark:bg-[#1a1d23]">
                                    <img
                                        v-if="item.product.image"
                                        :src="item.product.image"
                                        :alt="item.product.name"
                                        class="h-full w-full object-cover"
                                    />
                                    <svg v-else class="h-6 w-6 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"
                                        />
                                    </svg>
                                </div>

                                <!-- Product Info -->
                                <div class="min-w-0 flex-1">
                                    <p class="truncate font-medium text-gray-900 dark:text-gray-100">{{ item.product.name }}</p>
                                    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                                        {{ item.quantity }} x {{ formatPrice(item.price) }}
                                    </p>
                                </div>

                                <!-- Line Total -->
                                <p class="shrink-0 font-semibold text-gray-900 dark:text-gray-100">
                                    {{ formatPrice(item.quantity * item.price) }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Total -->
                    <div class="border-b border-gray-100/80 px-6 py-5 dark:border-[#2e3039]">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Subtotal</span>
                            <span class="font-semibold text-gray-700 dark:text-gray-300">{{ formatPrice(order.total_price) }}</span>
                        </div>
                        <div class="mt-3 flex items-center justify-between border-t border-dashed border-gray-200 pt-3 dark:border-[#2e3039]">
                            <span class="text-base font-bold text-gray-900 dark:text-gray-100">Total</span>
                            <span class="text-xl font-bold text-indigo-600 dark:text-indigo-400">{{ formatPrice(order.total_price) }}</span>
                        </div>
                    </div>

                    <!-- Payment Info -->
                    <div v-if="order.payment" class="px-6 py-5">
                        <h3 class="mb-3 text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Payment</h3>
                        <div class="flex flex-wrap items-center gap-3">
                            <div class="flex items-center gap-2">
                                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-gray-100 dark:bg-[#1a1d23]">
                                    <svg class="h-4 w-4 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"
                                        />
                                    </svg>
                                </div>
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ formatPaymentMethod(order.payment.method) }}
                                </span>
                            </div>
                            <span
                                :class="[
                                    'rounded-full px-2.5 py-0.5 text-[11px] font-bold uppercase tracking-wider',
                                    paymentStatusClasses(order.payment.status),
                                ]"
                            >
                                {{ order.payment.status }}
                            </span>
                            <span class="text-sm text-gray-500 dark:text-gray-400">
                                {{ formatPrice(order.payment.amount) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Back Link -->
                <div class="mt-6 text-center">
                    <Link
                        :href="route('customer.orders.index')"
                        class="inline-flex items-center gap-2 text-sm font-medium text-gray-500 transition-colors hover:text-indigo-600 dark:text-gray-400 dark:hover:text-indigo-400"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Back to My Orders
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
