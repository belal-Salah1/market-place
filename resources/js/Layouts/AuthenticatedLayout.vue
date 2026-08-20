<script setup>
import { ref, computed, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link } from '@inertiajs/vue3';
import { useGsap } from '@/composables/useGsap';
import { useDarkMode } from '@/composables/useDarkMode';

useGsap();
const { isDark, toggle: toggleDark } = useDarkMode();

const showingNavigationDropdown = ref(false);
const showFlash = ref(false);
const page = usePage();

const cartCount = computed(() => page.props.cartCount ?? 0);
const isCustomer = computed(() => page.props.auth?.user?.role?.name === 'customer');

onMounted(() => {
    if (page.props.flash?.success || page.props.flash?.error) {
        showFlash.value = true;
        setTimeout(() => {
            showFlash.value = false;
        }, 4000);
    }
});
</script>

<template>
    <div>
        <div
            class="relative min-h-screen overflow-hidden bg-gradient-to-br from-indigo-100 via-purple-50 to-violet-100 dark:from-gray-950 dark:via-slate-900 dark:to-gray-950"
        >
            <!-- Floating orbs -->
            <div class="pointer-events-none absolute inset-0 overflow-hidden">
                <div
                    data-gsap-float="slow"
                    class="absolute -top-20 -right-20 h-72 w-72 rounded-full bg-indigo-200/30 blur-3xl dark:bg-indigo-800/20"
                />
                <div data-gsap-float class="absolute -bottom-32 -left-32 h-96 w-96 rounded-full bg-violet-200/25 blur-3xl dark:bg-violet-800/15" />
                <div
                    data-gsap-float="slow"
                    class="absolute top-1/2 right-1/4 h-48 w-48 rounded-full bg-purple-200/20 blur-3xl dark:bg-purple-800/15"
                />
            </div>

            <!-- Navigation -->
            <nav
                data-gsap="fade-down"
                class="relative z-50 border-b border-white/60 bg-white/70 backdrop-blur-2xl dark:border-white/10 dark:bg-gray-900/80"
            >
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="flex h-16 items-center justify-between">
                        <div class="flex items-center gap-8">
                            <Link
                                :href="route('dashboard')"
                                class="flex shrink-0 items-center text-indigo-600 transition-transform duration-300 hover:scale-105 dark:text-indigo-400"
                            >
                                <ApplicationLogo class="block h-8 w-auto" />
                            </Link>

                            <div class="hidden items-center gap-1 sm:flex">
                                <NavLink :href="route('dashboard')" :active="route().current('dashboard')"> Dashboard </NavLink>
                                <NavLink v-if="isCustomer" :href="route('customer.cart.index')" :active="route().current('customer.cart.*')">
                                    Cart
                                    <span
                                        v-if="cartCount > 0"
                                        class="ml-1.5 inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-indigo-600 px-1.5 text-xs font-bold text-white"
                                    >
                                        {{ cartCount }}
                                    </span>
                                </NavLink>
                            </div>
                        </div>

                        <div class="hidden sm:flex sm:items-center sm:gap-3">
                            <button
                                @click="toggleDark"
                                class="flex h-8 w-8 items-center justify-center rounded-full border border-white/80 bg-white/60 text-gray-500 transition-all duration-300 hover:bg-white hover:text-gray-700 dark:border-white/10 dark:bg-white/5 dark:text-gray-400 dark:hover:bg-white/10 dark:hover:text-yellow-400"
                                title="Toggle dark mode"
                            >
                                <svg v-if="isDark" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z"
                                    />
                                </svg>
                                <svg v-else class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 006.002-2.082z"
                                    />
                                </svg>
                            </button>
                            <div class="relative">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <button
                                            type="button"
                                            class="group flex items-center gap-2 rounded-full border border-white/80 bg-white/60 px-3 py-1.5 text-sm font-medium text-gray-700 shadow-sm backdrop-blur-sm transition-all duration-300 hover:border-indigo-200 hover:bg-white hover:shadow-md dark:border-white/10 dark:bg-white/5 dark:text-gray-200 dark:hover:border-indigo-400/30 dark:hover:bg-white/10"
                                        >
                                            <span
                                                class="flex h-7 w-7 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 to-violet-500 text-xs font-bold text-white shadow-sm transition-transform duration-300 group-hover:scale-110"
                                            >
                                                {{ $page.props.auth.user.name.charAt(0).toUpperCase() }}
                                            </span>
                                            {{ $page.props.auth.user.name }}
                                            <svg
                                                class="h-4 w-4 text-gray-400 transition-transform duration-300 group-hover:rotate-180 dark:text-gray-500"
                                                viewBox="0 0 20 20"
                                                fill="currentColor"
                                            >
                                                <path
                                                    fill-rule="evenodd"
                                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                    clip-rule="evenodd"
                                                />
                                            </svg>
                                        </button>
                                    </template>
                                    <template #content>
                                        <DropdownLink :href="route('profile.edit')">Profile</DropdownLink>
                                        <DropdownLink :href="route('logout')" method="post" as="button">Log Out</DropdownLink>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>

                        <!-- Hamburger -->
                        <div class="-me-2 flex items-center sm:hidden">
                            <button
                                @click="showingNavigationDropdown = !showingNavigationDropdown"
                                class="inline-flex items-center justify-center rounded-xl p-2 text-gray-400 transition-all duration-200 hover:bg-white/60 hover:text-gray-600 dark:hover:bg-white/10 dark:hover:text-gray-200"
                            >
                                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                    <path
                                        :class="{ hidden: showingNavigationDropdown, 'inline-flex': !showingNavigationDropdown }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                    <path
                                        :class="{ hidden: !showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Mobile menu -->
                <Transition
                    enter-active-class="transition duration-200 ease-out"
                    enter-from-class="opacity-0 -translate-y-2"
                    enter-to-class="opacity-100 translate-y-0"
                    leave-active-class="transition duration-150 ease-in"
                    leave-from-class="opacity-100 translate-y-0"
                    leave-to-class="opacity-0 -translate-y-2"
                >
                    <div v-show="showingNavigationDropdown" class="sm:hidden">
                        <div class="space-y-1 px-3 pt-2 pb-3">
                            <ResponsiveNavLink :href="route('dashboard')" :active="route().current('dashboard')">Dashboard</ResponsiveNavLink>
                            <ResponsiveNavLink v-if="isCustomer" :href="route('customer.cart.index')" :active="route().current('customer.cart.*')">
                                Cart<span v-if="cartCount > 0"> ({{ cartCount }})</span>
                            </ResponsiveNavLink>
                        </div>
                        <div class="border-t border-gray-100 px-3 pt-4 pb-3 dark:border-gray-700">
                            <div class="mb-3 flex items-center gap-3 px-4">
                                <span
                                    class="flex h-9 w-9 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 to-violet-500 text-sm font-bold text-white"
                                >
                                    {{ $page.props.auth.user.name.charAt(0).toUpperCase() }}
                                </span>
                                <div>
                                    <div class="text-sm font-semibold text-gray-800 dark:text-gray-100">{{ $page.props.auth.user.name }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $page.props.auth.user.email }}</div>
                                </div>
                            </div>
                            <div class="space-y-1">
                                <ResponsiveNavLink :href="route('profile.edit')">Profile</ResponsiveNavLink>
                                <ResponsiveNavLink :href="route('logout')" method="post" as="button">Log Out</ResponsiveNavLink>
                            </div>
                        </div>
                    </div>
                </Transition>
            </nav>

            <!-- Header -->
            <header
                v-if="$slots.header"
                data-gsap="fade-in"
                data-gsap-delay="0.1"
                class="relative z-10 border-b border-white/40 bg-white/50 backdrop-blur-sm dark:border-white/10 dark:bg-gray-900/50"
            >
                <div class="mx-auto max-w-7xl px-4 py-5 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Flash Messages -->
            <Transition
                enter-active-class="transition duration-300 ease-out"
                enter-from-class="opacity-0 -translate-y-3"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition duration-200 ease-in"
                leave-from-class="opacity-100 translate-y-0"
                leave-to-class="opacity-0 -translate-y-3"
            >
                <div
                    v-if="showFlash && ($page.props.flash?.success || $page.props.flash?.error)"
                    class="relative z-20 mx-auto max-w-7xl px-4 pt-4 sm:px-6 lg:px-8"
                >
                    <div
                        v-if="$page.props.flash?.success"
                        class="flex items-center gap-3 rounded-xl border border-emerald-200/60 bg-emerald-50/80 px-4 py-3 shadow-sm backdrop-blur-sm dark:border-emerald-700/60 dark:bg-emerald-950/80"
                    >
                        <svg class="h-5 w-5 shrink-0 text-emerald-500" fill="currentColor" viewBox="0 0 20 20">
                            <path
                                fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd"
                            />
                        </svg>
                        <p class="text-sm font-medium text-emerald-800 dark:text-emerald-200">{{ $page.props.flash.success }}</p>
                        <button @click="showFlash = false" class="ml-auto text-emerald-400 transition-colors hover:text-emerald-600">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <div
                        v-if="$page.props.flash?.error"
                        class="flex items-center gap-3 rounded-xl border border-red-200/60 bg-red-50/80 px-4 py-3 shadow-sm backdrop-blur-sm dark:border-red-700/60 dark:bg-red-950/80"
                    >
                        <svg class="h-5 w-5 shrink-0 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                            <path
                                fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                clip-rule="evenodd"
                            />
                        </svg>
                        <p class="text-sm font-medium text-red-800 dark:text-red-200">{{ $page.props.flash.error }}</p>
                        <button @click="showFlash = false" class="ml-auto text-red-400 transition-colors hover:text-red-600">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </Transition>

            <!-- Content -->
            <main class="relative z-10">
                <slot />
            </main>
        </div>
    </div>
</template>
