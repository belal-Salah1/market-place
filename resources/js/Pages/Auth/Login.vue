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
        <div class="mb-6 flex justify-center animate-scale-in">
            <div class="flex h-16 w-16 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 to-violet-600 shadow-lg shadow-indigo-200">
                <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
                    />
                </svg>
            </div>
        </div>

        <!-- Header -->
        <div class="mb-8 text-center">
            <h1 class="text-2xl font-bold tracking-tight text-gray-900 animate-fade-in-up delay-1">
                Welcome back
            </h1>
            <p class="mt-2 text-sm text-gray-500 animate-fade-in-up delay-2">
                Sign in to your account to continue
            </p>
        </div>

        <!-- Status message -->
        <div
            v-if="status"
            class="mb-6 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-center text-sm font-medium text-green-700 animate-fade-in"
        >
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-5">
            <!-- Email -->
            <div class="animate-fade-in-up delay-3">
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
            <div class="animate-fade-in-up delay-4">
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
            <div class="flex items-center justify-between animate-fade-in-up delay-5">
                <label class="flex items-center gap-2">
                    <Checkbox name="remember" v-model:checked="form.remember" />
                    <span class="text-sm text-gray-600">Remember me</span>
                </label>

                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="text-sm font-medium text-indigo-600 transition-colors duration-200 hover:text-indigo-800"
                >
                    Forgot password?
                </Link>
            </div>

            <!-- Submit -->
            <div class="animate-fade-in-up delay-6">
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
        <div class="mt-8 border-t border-gray-100 pt-6 text-center animate-fade-in-up delay-7">
            <p class="text-sm text-gray-500">
                Don't have an account?
                <Link
                    :href="route('register')"
                    class="font-semibold text-indigo-600 transition-colors duration-200 hover:text-indigo-800"
                >
                    Create one now
                </Link>
            </p>
        </div>
    </GuestLayout>
</template>