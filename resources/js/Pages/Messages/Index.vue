<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

interface Conversation {
    user: { id: number; name: string; email: string };
    lastMessage: { content: string; created_at: string; sender_id: number };
    unreadCount: number;
}

defineProps<{
    conversations: Conversation[];
}>();

function getInitials(name: string): string {
    return name
        .split(' ')
        .map((w) => w[0])
        .join('')
        .toUpperCase()
        .slice(0, 2);
}

function truncate(text: string, length: number = 60): string {
    return text.length > length ? text.slice(0, length) + '...' : text;
}

function timeAgo(dateString: string): string {
    const now = new Date();
    const date = new Date(dateString);
    const seconds = Math.floor((now.getTime() - date.getTime()) / 1000);

    if (seconds < 60) return 'just now';
    if (seconds < 3600) return Math.floor(seconds / 60) + 'm ago';
    if (seconds < 86400) return Math.floor(seconds / 3600) + 'h ago';
    if (seconds < 604800) return Math.floor(seconds / 86400) + 'd ago';

    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
}
</script>

<template>
    <Head title="Messages" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link
                    :href="route('dashboard')"
                    class="group flex h-8 w-8 items-center justify-center rounded-lg bg-white/60 text-gray-400 backdrop-blur-sm transition-all duration-200 hover:bg-indigo-50 hover:text-indigo-600 dark:bg-white/5 dark:text-gray-500 dark:hover:bg-indigo-500/10 dark:hover:text-indigo-400"
                >
                    <svg
                        class="h-4 w-4 transition-transform duration-200 group-hover:-translate-x-0.5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </Link>
                <h2 class="text-xl font-bold text-gray-800 dark:text-gray-100">Messages</h2>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8" data-gsap="fade-up">
                <!-- Empty State -->
                <div
                    v-if="conversations.length === 0"
                    class="rounded-2xl border border-white/60 bg-white/80 px-6 py-16 text-center shadow-sm backdrop-blur-sm dark:border-[#2e3039] dark:bg-[#1e2028]/90"
                >
                    <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-gray-50 dark:bg-[#1a1d23]">
                        <svg
                            class="h-7 w-7 text-gray-300 dark:text-gray-500"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                            stroke-width="1.5"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"
                            />
                        </svg>
                    </div>
                    <p class="text-sm font-medium text-gray-400 dark:text-gray-500">No messages yet</p>
                    <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Start a conversation to see it here</p>
                </div>

                <!-- Conversations List -->
                <div v-else class="space-y-2">
                    <Link
                        v-for="conversation in conversations"
                        :key="conversation.user.id"
                        :href="route('messages.show', conversation.user.id)"
                        class="group flex items-center gap-4 rounded-2xl border border-white/60 bg-white/80 p-4 shadow-sm backdrop-blur-sm transition-all duration-200 hover:shadow-md dark:border-[#2e3039] dark:bg-[#1e2028]/90"
                        data-gsap="fade-up"
                    >
                        <!-- Avatar -->
                        <div
                            class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-sm font-bold text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400"
                        >
                            {{ getInitials(conversation.user.name) }}
                        </div>

                        <!-- Content -->
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center justify-between">
                                <p
                                    class="truncate font-semibold text-gray-800 transition-colors group-hover:text-indigo-600 dark:text-gray-100 dark:group-hover:text-indigo-400"
                                >
                                    {{ conversation.user.name }}
                                </p>
                                <span class="shrink-0 text-xs text-gray-400 dark:text-gray-500">
                                    {{ timeAgo(conversation.lastMessage.created_at) }}
                                </span>
                            </div>
                            <div class="mt-0.5 flex items-center justify-between">
                                <p class="truncate text-sm text-gray-500 dark:text-gray-400">
                                    {{ truncate(conversation.lastMessage.content) }}
                                </p>
                                <span
                                    v-if="conversation.unreadCount > 0"
                                    class="ml-2 flex h-5 min-w-[1.25rem] shrink-0 items-center justify-center rounded-full bg-indigo-600 px-1.5 text-[11px] font-bold text-white"
                                >
                                    {{ conversation.unreadCount }}
                                </span>
                            </div>
                        </div>

                        <!-- Arrow -->
                        <svg
                            class="h-5 w-5 shrink-0 text-gray-300 transition-transform duration-200 group-hover:translate-x-0.5 group-hover:text-indigo-400 dark:text-gray-500"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                            stroke-width="1.5"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
