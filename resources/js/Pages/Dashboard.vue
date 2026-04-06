<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="animate-fade-in-up text-2xl font-bold text-gray-800">Dashboard</h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Welcome Card -->
                <div class="animate-fade-in-up delay-0 glass-card rounded-2xl border border-white/60 bg-white/80 backdrop-blur-sm shadow-sm p-8">
                    <div class="flex items-center space-x-5">
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-500 to-violet-500 shadow-lg shadow-indigo-200">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-7 w-7 text-white"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                                />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900">Welcome back, {{ $page.props.auth.user.name }}</h3>
                            <p class="mt-1 text-sm text-gray-500">
                                You're logged in and ready to go. Use the navigation above to explore the marketplace.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Decorative Gradient Card -->
                <div class="animate-fade-in-up delay-1 mt-8 rounded-2xl bg-gradient-to-r from-indigo-600 via-violet-600 to-purple-600 p-8 text-white shadow-lg animate-gradient">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-2xl font-bold">Explore the Marketplace</h3>
                            <p class="mt-2 max-w-lg text-sm text-indigo-100">
                                Discover thousands of products from verified vendors. Browse categories, compare prices, and find exactly what you need.
                            </p>
                        </div>
                        <div class="hidden sm:block">
                            <div class="flex h-20 w-20 items-center justify-center rounded-2xl bg-white/15 backdrop-blur-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Stats Row -->
                <div class="mt-8 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="animate-fade-in-up delay-2 glass-card rounded-2xl border border-white/60 bg-white/80 backdrop-blur-sm shadow-sm p-4">
                        <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-50">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                        </div>
                        <p class="text-xs font-medium text-gray-500">Products</p>
                        <p class="mt-0.5 text-xl font-bold text-gray-900">2,540</p>
                    </div>

                    <div class="animate-fade-in-up delay-3 glass-card rounded-2xl border border-white/60 bg-white/80 backdrop-blur-sm shadow-sm p-4">
                        <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <p class="text-xs font-medium text-gray-500">Vendors</p>
                        <p class="mt-0.5 text-xl font-bold text-gray-900">184</p>
                    </div>

                    <div class="animate-fade-in-up delay-4 glass-card rounded-2xl border border-white/60 bg-white/80 backdrop-blur-sm shadow-sm p-4">
                        <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                            </svg>
                        </div>
                        <p class="text-xs font-medium text-gray-500">Reviews</p>
                        <p class="mt-0.5 text-xl font-bold text-gray-900">8,920</p>
                    </div>

                    <div class="animate-fade-in-up delay-5 glass-card rounded-2xl border border-white/60 bg-white/80 backdrop-blur-sm shadow-sm p-4">
                        <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-violet-50">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-violet-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                            </svg>
                        </div>
                        <p class="text-xs font-medium text-gray-500">Categories</p>
                        <p class="mt-0.5 text-xl font-bold text-gray-900">36</p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>