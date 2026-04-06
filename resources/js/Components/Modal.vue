<script setup>
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    maxWidth: {
        type: String,
        default: '2xl',
    },
    closeable: {
        type: Boolean,
        default: true,
    },
    vendor: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(['close', 'approve']);
const dialog = ref();
const showSlot = ref(props.show);

watch(
    () => props.show,
    () => {
        if (props.show) {
            document.body.style.overflow = 'hidden';
            showSlot.value = true;

            dialog.value?.showModal();
        } else {
            document.body.style.overflow = '';

            setTimeout(() => {
                dialog.value?.close();
                showSlot.value = false;
            }, 200);
        }
    },
);

const close = () => {
    if (props.closeable) {
        emit('close');
    }
};

const approve = () => {
    emit('approve', props.vendor);
    if (props.closeable) {
        emit('close');
    }
};

const reject = () =>{

}

const closeOnEscape = (e) => {
    if (e.key === 'Escape') {
        e.preventDefault();

        if (props.show) {
            close();
        }
    }
};

onMounted(() => document.addEventListener('keydown', closeOnEscape));

onUnmounted(() => {
    document.removeEventListener('keydown', closeOnEscape);

    document.body.style.overflow = '';
});

const maxWidthClass = computed(() => {
    return {
        sm: 'sm:max-w-sm',
        md: 'sm:max-w-md',
        lg: 'sm:max-w-lg',
        xl: 'sm:max-w-xl',
        '2xl': 'sm:max-w-2xl',
    }[props.maxWidth];
});
</script>

<template>
    <dialog
        class="z-50 m-0 min-h-full min-w-full overflow-y-auto bg-transparent backdrop:bg-transparent"
        ref="dialog"
    >
        <div
            class="fixed  z-50 flex items-center justify-center overflow-y-auto px-4 py-6 sm:px-0"
            scroll-region
        >
            <Transition
                enter-active-class="ease-out duration-300"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="ease-in duration-200"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div
                    v-show="show"
                    class="fixed inset-0 transform transition-all"
                    @click="close"
                >
                    <div
                        class="absolute  bg-gray-500 opacity-75"
                    />
                    <div
                        v-show="showSlot"
                        class="mb-6 transform overflow-hidden rounded-lg bg-white dark:bg-gray-800 shadow-xl transition-all sm:mx-auto sm:w-full"
                        :class="maxWidthClass"
                    >
                        <slot v-if="showSlot" :vendor="props.vendor" :close="close" :approve="approve">
                            <div class="p-6">
                                <h3 class="text-lg font-bold dark:text-white">Vendor details</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-300 mt-2">Name: {{ props.vendor?.name }}</p>
                                <p class="text-sm text-gray-600 dark:text-gray-300">Email: {{ props.vendor?.email }}</p>
                                <p class="text-sm text-gray-600 dark:text-gray-300">ID: {{ props.vendor?.id }}</p>
                                <div class="mt-4 flex gap-2">
                                    <button @click="approve" class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Approve</button>
                                    <button @click="reject" class="inline-flex items-center rounded-md border bg-red-600 px-4 py-2 text-sm text-white font-semibold">reject</button>
                                    <button @click="close" class="inline-flex items-center rounded-md border px-4 py-2 text-sm font-semibold">Cancel</button>
                                </div>
                            </div>
                        </slot>
                    </div>
                </div>
            </Transition>
        </div>
    </dialog>
</template>
