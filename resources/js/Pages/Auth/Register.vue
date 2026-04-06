<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    isVendor: false, //default customer
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Register" />

        <!-- Icon -->
        <div class="mb-6 flex justify-center" data-gsap="scale-in">
            <div class="flex h-16 w-16 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 to-violet-600 shadow-lg shadow-indigo-200 dark:shadow-indigo-500/20">
                <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"
                    />
                </svg>
            </div>
        </div>

        <!-- Header -->
        <div class="mb-8 text-center">
            <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-gray-100" data-gsap="fade-up" data-gsap-delay="0.08">
                Create your account
            </h1>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400" data-gsap="fade-up" data-gsap-delay="0.15">
                Join our marketplace and get started today
            </p>
        </div>

        <form @submit.prevent="submit" class="space-y-5">
            <!-- Name -->
            <div data-gsap="fade-up" data-gsap-delay="0.22">
                <InputLabel for="name" value="Full name" />
                <TextInput
                    id="name"
                    type="text"
                    class="mt-1.5 block w-full"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                    placeholder="John Doe"
                />
                <InputError class="mt-1.5" :message="form.errors.name" />
            </div>

            <!-- Email -->
            <div data-gsap="fade-up" data-gsap-delay="0.3">
                <InputLabel for="email" value="Email address" />
                <TextInput
                    id="email"
                    type="email"
                    class="mt-1.5 block w-full"
                    v-model="form.email"
                    required
                    autocomplete="username"
                    placeholder="you@example.com"
                />
                <InputError class="mt-1.5" :message="form.errors.email" />
            </div>

            <!-- Password -->
            <div data-gsap="fade-up" data-gsap-delay="0.38">
                <InputLabel for="password" value="Password" />
                <TextInput
                    id="password"
                    type="password"
                    class="mt-1.5 block w-full"
                    v-model="form.password"
                    required
                    autocomplete="new-password"
                    placeholder="Create a strong password"
                />
                <InputError class="mt-1.5" :message="form.errors.password" />
            </div>

            <!-- Confirm Password -->
            <div data-gsap="fade-up" data-gsap-delay="0.45">
                <InputLabel for="password_confirmation" value="Confirm password" />
                <TextInput
                    id="password_confirmation"
                    type="password"
                    class="mt-1.5 block w-full"
                    v-model="form.password_confirmation"
                    required
                    autocomplete="new-password"
                    placeholder="Confirm your password"
                />
                <InputError class="mt-1.5" :message="form.errors.password_confirmation" />
            </div>

            <!-- Vendor toggle card -->
            <div
                class="cursor-pointer rounded-xl border-2 p-4 transition-all duration-200" data-gsap="fade-up" data-gsap-delay="0.52"
                :class="form.isVendor
                    ? 'border-indigo-500 bg-indigo-50/60 ring-2 ring-indigo-500/20 dark:bg-indigo-500/10 dark:border-indigo-500/40'
                    : 'border-gray-200 bg-gray-50 hover:border-gray-300 dark:border-[#2e3039] dark:bg-[#1e2028]/60 dark:hover:border-gray-600'"
                @click="form.isVendor = !form.isVendor"
            >
                <div class="flex items-center gap-3">
                    <!-- Shop Icon -->
                    <div
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg transition-colors duration-200"
                        :class="form.isVendor ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-500 dark:bg-gray-700 dark:text-gray-400'"
                    >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"
                            />
                        </svg>
                    </div>

                    <!-- Text -->
                    <div class="flex-1">
                        <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                            I want to sell products
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Register as a vendor to list and sell your products
                        </p>
                    </div>

                    <!-- Toggle indicator -->
                    <div
                        class="relative h-6 w-11 shrink-0 rounded-full transition-colors duration-200"
                        :class="form.isVendor ? 'bg-indigo-600' : 'bg-gray-300 dark:bg-gray-600'"
                    >
                        <div
                            class="absolute top-0.5 h-5 w-5 rounded-full bg-white shadow transition-transform duration-200"
                            :class="form.isVendor ? 'translate-x-5' : 'translate-x-0.5'"
                        ></div>
                    </div>
                </div>
            </div>

            <!-- Submit -->
            <div data-gsap="fade-up" data-gsap-delay="0.52">
                <PrimaryButton
                    class="btn-sweep w-full justify-center py-2.5"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Create account
                </PrimaryButton>
            </div>
        </form>

        <!-- Login link -->
        <div class="mt-8 border-t border-gray-100 dark:border-[#2e3039] pt-6 text-center" data-gsap="fade-in" data-gsap-delay="0.52">
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Already have an account?
                <Link
                    :href="route('login')"
                    class="font-semibold text-indigo-600 transition-colors duration-200 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300"
                >
                    Sign in
                </Link>
            </p>
        </div>
    </GuestLayout>
</template>