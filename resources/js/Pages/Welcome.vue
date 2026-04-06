<script setup>
import { Head, Link } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    laravelVersion: {
        type: String,
        required: true,
    },
    phpVersion: {
        type: String,
        required: true,
    },
});
</script>

<template>
    <Head title="Crystal Market" />

    <div class="relative min-h-screen overflow-hidden bg-gradient-to-br from-indigo-100 via-purple-50 to-violet-100">
        <!-- Floating decorative orbs -->
        <div
            class="animate-float pointer-events-none absolute -left-24 top-20 h-72 w-72 rounded-full bg-indigo-300/30 blur-3xl"
        ></div>
        <div
            class="animate-float-slow pointer-events-none absolute -right-16 top-60 h-96 w-96 rounded-full bg-violet-300/25 blur-3xl"
        ></div>
        <div
            class="animate-float pointer-events-none absolute bottom-32 left-1/3 h-64 w-64 rounded-full bg-purple-300/20 blur-3xl"
        ></div>
        <div
            class="animate-float-slow pointer-events-none absolute -bottom-10 right-1/4 h-80 w-80 rounded-full bg-indigo-200/30 blur-3xl"
        ></div>

        <!-- Glassmorphic Navigation -->
        <nav
            class="animate-fade-in-down delay-0 sticky top-0 z-50 border-b border-white/30 bg-white/60 px-6 py-4 backdrop-blur-xl lg:px-12"
        >
            <div class="mx-auto flex max-w-7xl items-center justify-between">
                <div class="flex items-center gap-3">
                    <ApplicationLogo class="h-9 w-9 fill-indigo-600" />
                    <span
                        class="bg-gradient-to-r from-indigo-600 to-violet-600 bg-clip-text text-xl font-bold tracking-tight text-transparent"
                    >
                        Crystal Market
                    </span>
                </div>

                <div v-if="canLogin" class="flex items-center gap-3">
                    <Link
                        :href="route('login')"
                        class="rounded-full px-5 py-2 text-sm font-semibold text-indigo-600 transition-all hover:bg-indigo-50/80"
                    >
                        Log in
                    </Link>
                    <Link
                        v-if="canRegister"
                        :href="route('register')"
                        class="btn-sweep rounded-full bg-indigo-600 px-5 py-2 text-sm font-semibold text-white shadow-md shadow-indigo-200 transition-all hover:bg-indigo-700 hover:shadow-lg hover:shadow-indigo-300"
                    >
                        Register
                    </Link>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <section class="relative px-6 pb-24 pt-20 lg:px-12 lg:pt-32">
            <div class="mx-auto max-w-4xl text-center">
                <h1 class="animate-fade-in-up delay-0 text-5xl font-extrabold tracking-tight sm:text-6xl lg:text-7xl">
                    <span
                        class="bg-gradient-to-r from-indigo-600 via-purple-600 to-violet-600 bg-clip-text text-transparent"
                    >
                        Discover, Shop, Sell
                    </span>
                </h1>

                <p
                    class="animate-fade-in-up delay-1 mx-auto mt-6 max-w-2xl text-lg leading-relaxed text-gray-600 sm:text-xl"
                >
                    A premium marketplace connecting trusted vendors with discerning
                    customers. Find unique products, enjoy secure transactions, and
                    build your business -- all in one place.
                </p>

                <div class="animate-fade-in-up delay-2 mt-10 flex flex-col items-center justify-center gap-4 sm:flex-row">
                    <Link
                        v-if="canRegister"
                        :href="route('register')"
                        class="btn-sweep inline-flex items-center gap-2 rounded-full bg-indigo-600 px-8 py-3.5 text-base font-semibold text-white shadow-lg shadow-indigo-200 transition-all hover:bg-indigo-700 hover:shadow-xl hover:shadow-indigo-300"
                    >
                        Get Started
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                        </svg>
                    </Link>
                    <Link
                        :href="route('dashboard')"
                        class="inline-flex items-center gap-2 rounded-full border border-indigo-200/60 bg-white/70 px-8 py-3.5 text-base font-semibold text-indigo-600 backdrop-blur-sm transition-all hover:border-indigo-300 hover:bg-white hover:shadow-md"
                    >
                        Explore
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                        </svg>
                    </Link>
                </div>
            </div>
        </section>

        <!-- Features Grid -->
        <section class="relative px-6 pb-24 lg:px-12">
            <div class="mx-auto max-w-7xl">
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <!-- Fast Delivery -->
                    <div
                        class="glass-card animate-fade-in-up delay-3 rounded-2xl bg-white/70 p-8 backdrop-blur-sm"
                    >
                        <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-100 to-purple-100">
                            <svg class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
                            </svg>
                        </div>
                        <h3 class="mb-2 text-lg font-semibold text-gray-900">Fast Delivery</h3>
                        <p class="text-sm leading-relaxed text-gray-500">
                            Lightning-fast shipping right to your doorstep with real-time tracking on every order.
                        </p>
                    </div>

                    <!-- Secure Payments -->
                    <div
                        class="glass-card animate-fade-in-up delay-4 rounded-2xl bg-white/70 p-8 backdrop-blur-sm"
                    >
                        <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-100 to-purple-100">
                            <svg class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                            </svg>
                        </div>
                        <h3 class="mb-2 text-lg font-semibold text-gray-900">Secure Payments</h3>
                        <p class="text-sm leading-relaxed text-gray-500">
                            End-to-end encrypted transactions with trusted payment providers you already know.
                        </p>
                    </div>

                    <!-- Trusted Vendors -->
                    <div
                        class="glass-card animate-fade-in-up delay-5 rounded-2xl bg-white/70 p-8 backdrop-blur-sm"
                    >
                        <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-100 to-purple-100">
                            <svg class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a3.001 3.001 0 0 0 3.75-.615A2.993 2.993 0 0 0 9.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 0 0 2.25 1.016c.896 0 1.7-.393 2.25-1.015a3.001 3.001 0 0 0 3.75.614m-16.5 0a3.004 3.004 0 0 1-.621-4.72l1.189-1.19A1.5 1.5 0 0 1 5.378 3h13.243a1.5 1.5 0 0 1 1.06.44l1.19 1.189a3 3 0 0 1-.621 4.72M6.75 18h3.75a.75.75 0 0 0 .75-.75V13.5a.75.75 0 0 0-.75-.75H6.75a.75.75 0 0 0-.75.75v3.75c0 .414.336.75.75.75Z" />
                            </svg>
                        </div>
                        <h3 class="mb-2 text-lg font-semibold text-gray-900">Trusted Vendors</h3>
                        <p class="text-sm leading-relaxed text-gray-500">
                            Every vendor is vetted and approved, ensuring quality products and reliable service.
                        </p>
                    </div>

                    <!-- 24/7 Support -->
                    <div
                        class="glass-card animate-fade-in-up delay-6 rounded-2xl bg-white/70 p-8 backdrop-blur-sm"
                    >
                        <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-100 to-purple-100">
                            <svg class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 0 1-.825-.242m9.345-8.334a2.126 2.126 0 0 0-.476-.095 48.64 48.64 0 0 0-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0 0 11.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155" />
                            </svg>
                        </div>
                        <h3 class="mb-2 text-lg font-semibold text-gray-900">24/7 Support</h3>
                        <p class="text-sm leading-relaxed text-gray-500">
                            Our dedicated team is always available to help you with anything you need.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Stats Row -->
        <section class="animate-fade-in delay-7 px-6 pb-24 lg:px-12">
            <div class="mx-auto max-w-3xl text-center">
                <div
                    class="inline-flex flex-wrap items-center justify-center gap-x-8 gap-y-4 rounded-2xl border border-white/40 bg-white/50 px-10 py-6 backdrop-blur-sm"
                >
                    <div class="flex flex-col items-center">
                        <span class="text-3xl font-extrabold text-indigo-600">10K+</span>
                        <span class="mt-1 text-sm font-medium text-gray-500">Products</span>
                    </div>
                    <div class="hidden h-8 w-px bg-indigo-200/60 sm:block"></div>
                    <div class="flex flex-col items-center">
                        <span class="text-3xl font-extrabold text-purple-600">5K+</span>
                        <span class="mt-1 text-sm font-medium text-gray-500">Vendors</span>
                    </div>
                    <div class="hidden h-8 w-px bg-indigo-200/60 sm:block"></div>
                    <div class="flex flex-col items-center">
                        <span class="text-3xl font-extrabold text-violet-600">50K+</span>
                        <span class="mt-1 text-sm font-medium text-gray-500">Customers</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="border-t border-white/30 bg-white/40 px-6 py-8 backdrop-blur-xl lg:px-12">
            <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-4 sm:flex-row">
                <div class="flex items-center gap-2.5">
                    <ApplicationLogo class="h-7 w-7 fill-indigo-600" />
                    <span
                        class="bg-gradient-to-r from-indigo-600 to-violet-600 bg-clip-text text-sm font-bold text-transparent"
                    >
                        Crystal Market
                    </span>
                </div>
                <p class="text-sm text-gray-400">
                    Powered by Laravel v{{ laravelVersion }} &middot; PHP v{{ phpVersion }}
                </p>
            </div>
        </footer>
    </div>
</template>