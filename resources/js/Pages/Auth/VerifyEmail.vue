<script setup>
import { computed } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    status: {
        type: String,
    },
});

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(
    () => props.status === 'verification-link-sent',
);
</script>

<template>
    <GuestLayout>
        <Head title="Email Verification" />

        <!-- Icon -->
        <div class="mb-6 flex justify-center" data-gsap="scale-in">
            <div class="flex h-16 w-16 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 to-violet-600 shadow-lg shadow-indigo-200 dark:shadow-indigo-500/20">
                <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 19v-8.93a2 2 0 01.89-1.664l7-4.666a2 2 0 012.22 0l7 4.666A2 2 0 0121 10.07V19M3 19a2 2 0 002 2h14a2 2 0 002-2M3 19l6.75-4.5M21 19l-6.75-4.5M3 10l6.75 4.5M21 10l-6.75 4.5m0 0l-1.14.76a2 2 0 01-2.22 0l-1.14-.76"
                    />
                </svg>
            </div>
        </div>

        <!-- Header -->
        <div class="mb-6 text-center">
            <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-gray-100" data-gsap="fade-up" data-gsap-delay="0.08">
                Check your email
            </h1>
            <p class="mt-2 text-sm leading-relaxed text-gray-500 dark:text-gray-400" data-gsap="fade-up" data-gsap-delay="0.15">
                Thanks for signing up! Before getting started, please verify your email address by clicking the link we just sent you.
            </p>
        </div>

        <!-- Success message -->
        <div
            v-if="verificationLinkSent"
            class="mb-6 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-center text-sm font-medium text-green-700 dark:text-green-400 dark:bg-green-500/10 dark:border-green-500/20" data-gsap="fade-in"
        >
            A new verification link has been sent to the email address you provided during registration.
        </div>

        <form @submit.prevent="submit">
            <div class="flex flex-col items-center gap-4" data-gsap="fade-up" data-gsap-delay="0.22">
                <PrimaryButton
                    class="btn-sweep w-full justify-center py-2.5"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Resend verification email
                </PrimaryButton>

                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="text-sm font-medium text-gray-500 transition-colors duration-200 hover:text-indigo-600 dark:text-gray-400 dark:hover:text-indigo-400"
                >
                    Sign out
                </Link>
            </div>
        </form>
    </GuestLayout>
</template>