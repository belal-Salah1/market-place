<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    user: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <GuestLayout>
        <Head title="Pending Approval" />

        <div class="text-center">
            <!-- Icon -->
            <div class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-full bg-yellow-100">
                <svg
                    class="h-10 w-10 text-yellow-600"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                    />
                </svg>
            </div>

            <!-- Title -->
            <h2 class="mb-2 text-2xl font-bold text-gray-900">
                Registration Pending
            </h2>

            <!-- Message -->
            <div class="mb-6 space-y-3 text-gray-600">
                <p class="text-base">
                    Thank you for registering as a vendor,
                    <span class="font-semibold text-gray-900">{{ user.name }}</span>!
                </p>
                <p class="text-sm">
                    Your account is currently pending approval from our admin team.
                    You'll receive an email notification once your account has been reviewed and approved.
                </p>
            </div>

            <!-- Info Box -->
            <div class="mb-6 rounded-lg bg-blue-50 p-4 text-left">
                <div class="flex">
                    <div class="shrink-0">
                        <svg
                            class="h-5 w-5 text-blue-400"
                            fill="currentColor"
                            viewBox="0 0 20 20"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                clip-rule="evenodd"
                            />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-800">
                            What happens next?
                        </h3>
                        <div class="mt-2 text-sm text-blue-700">
                            <ul class="list-inside list-disc space-y-1">
                                <li>Our team will review your vendor registration</li>
                                <li>This typically takes 1-2 business days</li>
                                <li>You'll receive an email at <span class="font-semibold">{{ user.email }}</span></li>
                                <li>Once approved, you can log in and start selling</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex flex-col gap-3 sm:flex-row sm:justify-center">
                <Link :href="route('home')">
                    <PrimaryButton type="button" class="w-full sm:w-auto">
                        Back to Home
                    </PrimaryButton>
                </Link>

                <form method="POST" :action="route('logout')" class="w-full sm:w-auto">
                    <SecondaryButton
                        type="submit"
                        class="w-full"
                    >
                        Sign Out
                    </SecondaryButton>
                </form>
            </div>

            <!-- Support Contact -->
            <div class="mt-6 border-t border-gray-200 pt-6">
                <p class="text-xs text-gray-500">
                    Have questions?
                    <a
                        href="mailto:support@example.com"
                        class="font-medium text-indigo-600 hover:text-indigo-500"
                    >
                        Contact our support team
                    </a>
                </p>
            </div>
        </div>
    </GuestLayout>
</template>
