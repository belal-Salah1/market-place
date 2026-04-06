<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="relative flex min-h-screen flex-col items-center justify-center overflow-hidden bg-gradient-to-br from-indigo-100 via-purple-50 to-violet-100 px-4">
        <!-- Floating orbs -->
        <div class="pointer-events-none absolute inset-0">
            <div class="animate-float-slow absolute -top-16 left-1/4 h-64 w-64 rounded-full bg-indigo-200/30 blur-3xl" />
            <div class="animate-float absolute -bottom-20 right-1/4 h-80 w-80 rounded-full bg-violet-200/25 blur-3xl" />
            <div class="animate-float-slow absolute top-1/3 -right-16 h-48 w-48 rounded-full bg-purple-300/20 blur-3xl" />
        </div>

        <!-- Logo -->
        <div class="animate-fade-in-down relative z-10 mb-8">
            <Link href="/" class="group block text-indigo-600 transition-transform duration-300 hover:scale-105">
                <ApplicationLogo class="h-12 w-auto drop-shadow-sm" />
            </Link>
        </div>

        <!-- Card -->
        <div class="animate-fade-in-up delay-1 relative z-10 w-full max-w-md overflow-hidden rounded-3xl border border-white/60 bg-white/75 px-8 py-8 shadow-2xl shadow-indigo-200/30 backdrop-blur-2xl">
            <!-- Shimmer top edge -->
            <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-indigo-300/50 to-transparent" />
            <slot />
        </div>
    </div>
</template>