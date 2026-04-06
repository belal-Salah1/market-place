<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
import { useGsap } from '@/composables/useGsap';
import { useDarkMode } from '@/composables/useDarkMode';

useGsap();
const { isDark, toggle: toggleDark } = useDarkMode();
</script>

<template>
    <div class="relative flex min-h-screen flex-col items-center justify-center overflow-hidden bg-gradient-to-br from-indigo-100 via-purple-50 to-violet-100 dark:from-gray-950 dark:via-slate-900 dark:to-gray-950 px-4">
        <!-- Floating orbs -->
        <div class="pointer-events-none absolute inset-0">
            <div data-gsap-float="slow" class="absolute -top-16 left-1/4 h-64 w-64 rounded-full bg-indigo-200/30 dark:bg-indigo-800/20 blur-3xl" />
            <div data-gsap-float class="absolute -bottom-20 right-1/4 h-80 w-80 rounded-full bg-violet-200/25 dark:bg-violet-800/15 blur-3xl" />
            <div data-gsap-float="slow" class="absolute top-1/3 -right-16 h-48 w-48 rounded-full bg-purple-300/20 dark:bg-purple-800/15 blur-3xl" />
        </div>

        <!-- Logo -->
        <div data-gsap="fade-down" class="relative z-10 mb-8">
            <Link href="/" class="group block text-indigo-600 transition-transform duration-300 hover:scale-105">
                <ApplicationLogo class="h-12 w-auto drop-shadow-sm" />
            </Link>
        </div>

        <button @click="toggleDark" class="relative z-10 mb-4 flex h-8 w-8 items-center justify-center rounded-full border border-white/60 bg-white/60 text-gray-500 transition-all duration-300 hover:bg-white dark:border-white/10 dark:bg-white/5 dark:text-gray-400 dark:hover:bg-white/10 dark:hover:text-yellow-400" title="Toggle dark mode">
            <svg v-if="isDark" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" /></svg>
            <svg v-else class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 006.002-2.082z" /></svg>
        </button>

        <!-- Card -->
        <div data-gsap="fade-up" data-gsap-delay="0.1" class="relative z-10 w-full max-w-md overflow-hidden rounded-3xl border border-white/60 bg-white/75 dark:border-white/10 dark:bg-gray-900/80 dark:shadow-black/30 px-8 py-8 shadow-2xl shadow-indigo-200/30 backdrop-blur-2xl">
            <!-- Shimmer top edge -->
            <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-indigo-300/50 dark:via-indigo-500/30 to-transparent" />
            <slot />
        </div>
    </div>
</template>