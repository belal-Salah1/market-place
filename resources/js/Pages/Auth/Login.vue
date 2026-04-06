<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Log in" />

        <!-- Icon -->
        <div class="mb-6 flex justify-center" data-gsap="scale-in">
            <div class="flex h-16 w-16 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 to-violet-600 shadow-lg shadow-indigo-200 dark:shadow-indigo-500/20">
                <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
                    />
                </svg>
            </div>
        </div>

        <!-- Header -->
        <div class="mb-8 text-center">
            <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-gray-100" data-gsap="fade-up" data-gsap-delay="0.08">
                Welcome back
            </h1>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400" data-gsap="fade-up" data-gsap-delay="0.15">
                Sign in to your account to continue
            </p>
        </div>

        <!-- Status message -->
        <div
            v-if="status"
            class="mb-6 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-center text-sm font-medium text-green-700 dark:text-green-400 dark:bg-green-500/10 dark:border-green-500/20" data-gsap="fade-in"
        >
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-5">
            <!-- Email -->
            <div data-gsap="fade-up" data-gsap-delay="0.22">
                <InputLabel for="email" value="Email address" />
                <TextInput
                    id="email"
                    type="email"
                    class="mt-1.5 block w-full"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="you@example.com"
                />
                <InputError class="mt-1.5" :message="form.errors.email" />
            </div>

            <!-- Password -->
            <div data-gsap="fade-up" data-gsap-delay="0.3">
                <InputLabel for="password" value="Password" />
                <TextInput
                    id="password"
                    type="password"
                    class="mt-1.5 block w-full"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                    placeholder="Enter your password"
                />
                <InputError class="mt-1.5" :message="form.errors.password" />
            </div>

            <!-- Remember me & Forgot password -->
            <div class="flex items-center justify-between" data-gsap="fade-up" data-gsap-delay="0.38">
                <label class="flex items-center gap-2">
                    <Checkbox name="remember" v-model:checked="form.remember" />
                    <span class="text-sm text-gray-600 dark:text-gray-400">Remember me</span>
                </label>

                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="text-sm font-medium text-indigo-600 transition-colors duration-200 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300"
                >
                    Forgot password?
                </Link>
            </div>

            <!-- Submit -->
            <div data-gsap="fade-up" data-gsap-delay="0.45">
                <PrimaryButton
                    class="btn-sweep w-full justify-center py-2.5"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Sign in
                </PrimaryButton>
            </div>
        </form>

        <!-- Register link -->
        <div class="mt-8 border-t border-gray-100 dark:border-[#2e3039] pt-6 text-center" data-gsap="fade-up" data-gsap-delay="0.52">
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Don't have an account?
                <Link
                    :href="route('register')"
                    class="font-semibold text-indigo-600 transition-colors duration-200 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300"
                >
                    Create one now
                </Link>
            </p>
        </div>
    </GuestLayout>
</template>