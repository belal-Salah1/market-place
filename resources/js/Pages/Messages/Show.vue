<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, nextTick, onMounted, ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

interface Message {
    id: number;
    content: string;
    sender_id: number;
    created_at: string;
    product: { id: number; name: string } | null;
}

interface OtherUser {
    id: number;
    name: string;
    email: string;
}

const props = defineProps<{
    otherUser: OtherUser;
    messages: Message[];
}>();

const page = usePage();
const authUserId = computed(() => (page.props.auth as { user: { id: number } }).user.id);

const messagesContainer = ref<HTMLElement | null>(null);

const form = useForm({
    receiver_id: props.otherUser.id,
    content: '',
});

function formatTime(dateString: string): string {
    return new Date(dateString).toLocaleTimeString('en-US', {
        hour: 'numeric',
        minute: '2-digit',
    });
}

function formatDate(dateString: string): string {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
}

function getInitials(name: string): string {
    return name
        .split(' ')
        .map((w) => w[0])
        .join('')
        .toUpperCase()
        .slice(0, 2);
}

function isMine(message: Message): boolean {
    return message.sender_id === authUserId.value;
}

function shouldShowDate(index: number): boolean {
    if (index === 0) return true;
    const current = new Date(props.messages[index].created_at).toDateString();
    const previous = new Date(props.messages[index - 1].created_at).toDateString();
    return current !== previous;
}

function scrollToBottom() {
    nextTick(() => {
        if (messagesContainer.value) {
            messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
        }
    });
}

function send() {
    form.post(route('messages.store'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset('content');
            scrollToBottom();
        },
    });
}

onMounted(() => {
    scrollToBottom();
});
</script>

<template>
    <Head :title="`Chat with ${otherUser.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link
                    :href="route('messages.index')"
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
                <div class="flex items-center gap-3">
                    <div
                        class="flex h-9 w-9 items-center justify-center rounded-full bg-indigo-100 text-xs font-bold text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400"
                    >
                        {{ getInitials(otherUser.name) }}
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-800 dark:text-gray-100">{{ otherUser.name }}</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ otherUser.email }}</p>
                    </div>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <div
                    class="flex flex-col overflow-hidden rounded-2xl border border-white/60 bg-white/80 shadow-sm backdrop-blur-sm dark:border-[#2e3039] dark:bg-[#1e2028]/90"
                    data-gsap="fade-up"
                >
                    <!-- Messages Container -->
                    <div ref="messagesContainer" class="flex-1 space-y-1 overflow-y-auto p-6" style="max-height: 28rem; min-height: 20rem">
                        <!-- Empty state -->
                        <div v-if="messages.length === 0" class="flex h-full items-center justify-center">
                            <div class="text-center">
                                <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-2xl bg-gray-50 dark:bg-[#1a1d23]">
                                    <svg
                                        class="h-6 w-6 text-gray-300 dark:text-gray-500"
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
                                <p class="text-sm text-gray-400 dark:text-gray-500">No messages yet. Start the conversation!</p>
                            </div>
                        </div>

                        <template v-for="(message, index) in messages" :key="message.id">
                            <!-- Date separator -->
                            <div v-if="shouldShowDate(index)" class="flex items-center gap-3 py-3">
                                <div class="h-px flex-1 bg-gray-200 dark:bg-[#2e3039]" />
                                <span class="text-[11px] font-medium text-gray-400 dark:text-gray-500">
                                    {{ formatDate(message.created_at) }}
                                </span>
                                <div class="h-px flex-1 bg-gray-200 dark:bg-[#2e3039]" />
                            </div>

                            <!-- Message bubble -->
                            <div class="flex" :class="isMine(message) ? 'justify-end' : 'justify-start'">
                                <div
                                    class="max-w-[75%] rounded-2xl px-4 py-2.5"
                                    :class="
                                        isMine(message)
                                            ? 'rounded-br-md bg-indigo-600 text-white'
                                            : 'rounded-bl-md bg-gray-100 text-gray-800 dark:bg-[#2e3039] dark:text-gray-200'
                                    "
                                >
                                    <!-- Product reference -->
                                    <p
                                        v-if="message.product"
                                        class="mb-1 text-[11px] font-medium"
                                        :class="isMine(message) ? 'text-indigo-200' : 'text-gray-400 dark:text-gray-500'"
                                    >
                                        Re: {{ message.product.name }}
                                    </p>

                                    <p class="text-sm leading-relaxed">{{ message.content }}</p>

                                    <p class="mt-1 text-[11px]" :class="isMine(message) ? 'text-indigo-300' : 'text-gray-400 dark:text-gray-500'">
                                        {{ formatTime(message.created_at) }}
                                    </p>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Message Input -->
                    <div class="border-t border-gray-100 p-4 dark:border-[#2e3039]">
                        <form @submit.prevent="send" class="flex items-end gap-3">
                            <div class="flex-1">
                                <textarea
                                    v-model="form.content"
                                    rows="2"
                                    placeholder="Type a message..."
                                    class="w-full resize-none rounded-xl border border-gray-300 bg-gray-50 px-4 py-2.5 text-sm text-gray-700 transition-colors focus:border-indigo-400 focus:ring-2 focus:ring-indigo-400/20 focus:outline-none dark:border-[#2e3039] dark:bg-[#1a1d23] dark:text-gray-200 dark:placeholder-gray-500 dark:focus:border-indigo-500 dark:focus:ring-indigo-500/20"
                                    @keydown.enter.exact.prevent="send"
                                />
                                <p v-if="form.errors.content" class="mt-1 text-xs text-red-500">{{ form.errors.content }}</p>
                            </div>
                            <button
                                type="submit"
                                :disabled="form.processing || !form.content.trim()"
                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-600 text-white shadow-sm transition-all duration-200 hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none disabled:cursor-not-allowed disabled:opacity-50 dark:focus:ring-offset-[#1e2028]"
                            >
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"
                                    />
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
