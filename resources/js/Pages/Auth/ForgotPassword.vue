<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <GuestLayout>
        <Head title="Forgot Password" />

        <!-- Icon -->
        <div class="mb-6 flex justify-center animate-scale-in">
            <div class="flex h-16 w-16 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 to-violet-600 shadow-lg shadow-indigo-200">
                <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                    />
                </svg>
            </div>
        </div>

        <!-- Header -->
        <div class="mb-6 text-center">
            <h1 class="text-2xl font-bold tracking-tight text-gray-900 animate-fade-in-up delay-1">
                Reset your password
            </h1>
            <p class="mt-2 text-sm leading-relaxed text-gray-500 animate-fade-in-up delay-2">
                No worries! Enter your email address and we'll send you a link to reset your password.
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

            <!-- Submit -->
            <div class="animate-fade-in-up delay-4">
                <PrimaryButton
                    class="btn-sweep w-full justify-center py-2.5"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Send reset link
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>