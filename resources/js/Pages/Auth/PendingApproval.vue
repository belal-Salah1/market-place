<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Inertia } from '@inertiajs/inertia';

const logout = () => {
    Inertia.post(route('logout'));
};

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
            <!-- Icon (amber themed) -->
            <div class="mx-auto mb-6 flex justify-center" data-gsap="scale-in">
                <div class="flex h-16 w-16 items-center justify-center rounded-full bg-gradient-to-br from-amber-400 to-amber-600 shadow-lg shadow-amber-200 dark:shadow-amber-500/20">
                    <svg
                        class="h-8 w-8 text-white"
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
            </div>

            <!-- Title -->
            <h1 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-gray-100" data-gsap="fade-up" data-gsap-delay="0.08">
                Registration Pending
            </h1>

            <!-- Message -->
            <div class="mb-6 space-y-2" data-gsap="fade-up" data-gsap-delay="0.15">
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Thank you for registering as a vendor,
                    <span class="font-semibold text-gray-900 dark:text-gray-100">{{ user.name }}</span>!
                </p>
                <p class="text-sm leading-relaxed text-gray-500 dark:text-gray-400">
                    Your account is currently pending approval from our admin team.
                    You'll receive an email notification once your account has been reviewed.
                </p>
            </div>

            <!-- Info Box -->
            <div class="mb-6 rounded-xl border border-indigo-100 bg-indigo-50/60 dark:border-indigo-500/20 dark:bg-indigo-500/10 p-4 text-left" data-gsap="fade-up" data-gsap-delay="0.22">
                <div class="flex gap-3">
                    <div class="shrink-0">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-100 dark:bg-indigo-500/20">
                            <svg
                                class="h-4 w-4 text-indigo-600 dark:text-indigo-400"
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
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                            What happens next?
                        </h3>
                        <ul class="mt-2 space-y-1.5 text-sm text-gray-600 dark:text-gray-400">
                            <li class="flex items-start gap-2" data-gsap="fade-up" data-gsap-delay="0.3">
                                <span class="mt-1 block h-1.5 w-1.5 shrink-0 rounded-full bg-indigo-400"></span>
                                Our team will review your vendor registration
                            </li>
                            <li class="flex items-start gap-2" data-gsap="fade-up" data-gsap-delay="0.38">
                                <span class="mt-1 block h-1.5 w-1.5 shrink-0 rounded-full bg-indigo-400"></span>
                                This typically takes 1-2 business days
                            </li>
                            <li class="flex items-start gap-2" data-gsap="fade-up" data-gsap-delay="0.45">
                                <span class="mt-1 block h-1.5 w-1.5 shrink-0 rounded-full bg-indigo-400"></span>
                                You'll receive an email at <span class="font-semibold text-gray-900 dark:text-gray-100">{{ user.email }}</span>
                            </li>
                            <li class="flex items-start gap-2" data-gsap="fade-up" data-gsap-delay="0.52">
                                <span class="mt-1 block h-1.5 w-1.5 shrink-0 rounded-full bg-indigo-400"></span>
                                Once approved, you can log in and start selling
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex flex-col gap-3 sm:flex-row sm:justify-center" data-gsap="fade-up" data-gsap-delay="0.52">
                <Link :href="route('home')">
                    <PrimaryButton type="button" class="btn-sweep w-full justify-center sm:w-auto">
                        Back to Home
                    </PrimaryButton>
                </Link>

                <SecondaryButton
                    @click="logout"
                    class="w-full sm:w-auto"
                >
                    Sign Out
                </SecondaryButton>
            </div>

            <!-- Support Contact -->
            <div class="mt-6 border-t border-gray-100 dark:border-[#2e3039] pt-6" data-gsap="fade-in" data-gsap-delay="0.52">
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Have questions?
                    <a
                        href="mailto:support@example.com"
                        class="font-medium text-indigo-600 transition-colors duration-200 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300"
                    >
                        Contact our support team
                    </a>
                </p>
            </div>
        </div>
    </GuestLayout>
</template>