<script setup>
import { ref, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link } from '@inertiajs/vue3';

const showingNavigationDropdown = ref(false);
const showFlash = ref(false);
const page = usePage();

onMounted(() => {
    if (page.props.flash?.success || page.props.flash?.error) {
        showFlash.value = true;
        setTimeout(() => { showFlash.value = false; }, 4000);
    }
});
</script>

<template>
    <div>
        <div class="relative min-h-screen overflow-hidden bg-gradient-to-br from-indigo-100 via-purple-50 to-violet-100">
            <!-- Floating orbs -->
            <div class="pointer-events-none absolute inset-0 overflow-hidden">
                <div class="animate-float-slow absolute -top-20 -right-20 h-72 w-72 rounded-full bg-indigo-200/30 blur-3xl" />
                <div class="animate-float absolute -bottom-32 -left-32 h-96 w-96 rounded-full bg-violet-200/25 blur-3xl" />
                <div class="animate-float-slow absolute top-1/2 right-1/4 h-48 w-48 rounded-full bg-purple-200/20 blur-3xl" />
            </div>

            <!-- Navigation -->
            <nav class="animate-fade-in-down relative z-50 border-b border-white/60 bg-white/70 backdrop-blur-2xl">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="flex h-16 items-center justify-between">
                        <div class="flex items-center gap-8">
                            <Link :href="route('dashboard')" class="flex shrink-0 items-center text-indigo-600 transition-transform duration-300 hover:scale-105">
                                <ApplicationLogo class="block h-8 w-auto" />
                            </Link>

                            <div class="hidden items-center gap-1 sm:flex">
                                <NavLink :href="route('dashboard')" :active="route().current('dashboard')">
                                    Dashboard
                                </NavLink>
                            </div>
                        </div>

                        <div class="hidden sm:flex sm:items-center sm:gap-3">
                            <div class="relative">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <button
                                            type="button"
                                            class="group flex items-center gap-2 rounded-full border border-white/80 bg-white/60 px-3 py-1.5 text-sm font-medium text-gray-700 shadow-sm backdrop-blur-sm transition-all duration-300 hover:border-indigo-200 hover:bg-white hover:shadow-md"
                                        >
                                            <span class="flex h-7 w-7 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 to-violet-500 text-xs font-bold text-white shadow-sm transition-transform duration-300 group-hover:scale-110">
                                                {{ $page.props.auth.user.name.charAt(0).toUpperCase() }}
                                            </span>
                                            {{ $page.props.auth.user.name }}
                                            <svg class="h-4 w-4 text-gray-400 transition-transform duration-300 group-hover:rotate-180" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
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
                                class="inline-flex items-center justify-center rounded-xl p-2 text-gray-400 transition-all duration-200 hover:bg-white/60 hover:text-gray-600"
                            >
                                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                    <path :class="{ hidden: showingNavigationDropdown, 'inline-flex': !showingNavigationDropdown }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                    <path :class="{ hidden: !showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
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
                        <div class="space-y-1 px-3 pb-3 pt-2">
                            <ResponsiveNavLink :href="route('dashboard')" :active="route().current('dashboard')">Dashboard</ResponsiveNavLink>
                        </div>
                        <div class="border-t border-gray-100 px-3 pb-3 pt-4">
                            <div class="mb-3 flex items-center gap-3 px-4">
                                <span class="flex h-9 w-9 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 to-violet-500 text-sm font-bold text-white">
                                    {{ $page.props.auth.user.name.charAt(0).toUpperCase() }}
                                </span>
                                <div>
                                    <div class="text-sm font-semibold text-gray-800">{{ $page.props.auth.user.name }}</div>
                                    <div class="text-xs text-gray-500">{{ $page.props.auth.user.email }}</div>
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
            <header v-if="$slots.header" class="animate-fade-in relative z-10 border-b border-white/40 bg-white/50 backdrop-blur-sm">
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
                <div v-if="showFlash && ($page.props.flash?.success || $page.props.flash?.error)" class="relative z-20 mx-auto max-w-7xl px-4 pt-4 sm:px-6 lg:px-8">
                    <div
                        v-if="$page.props.flash?.success"
                        class="flex items-center gap-3 rounded-xl border border-emerald-200/60 bg-emerald-50/80 px-4 py-3 shadow-sm backdrop-blur-sm"
                    >
                        <svg class="h-5 w-5 shrink-0 text-emerald-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <p class="text-sm font-medium text-emerald-800">{{ $page.props.flash.success }}</p>
                        <button @click="showFlash = false" class="ml-auto text-emerald-400 transition-colors hover:text-emerald-600">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                    <div
                        v-if="$page.props.flash?.error"
                        class="flex items-center gap-3 rounded-xl border border-red-200/60 bg-red-50/80 px-4 py-3 shadow-sm backdrop-blur-sm"
                    >
                        <svg class="h-5 w-5 shrink-0 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                        <p class="text-sm font-medium text-red-800">{{ $page.props.flash.error }}</p>
                        <button @click="showFlash = false" class="ml-auto text-red-400 transition-colors hover:text-red-600">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
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