<script setup>
import DialogModal from "@/Components/DialogModal.vue";

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    selectedVideoUrl: {
        type: String,
        required: true,
    }
});

const emit = defineEmits(['close']);
</script>

<template>
    <DialogModal :maxWidth="'4xl'" :show="show" @close="$emit('close')">
        <template #content>
            <div class="flex items-center justify-center">
                <div class="relative w-full max-w-3xl bg-white p-4">
                    <video :src="selectedVideoUrl" autoplay class="w-full h-auto" controls></video>
                    <button class="absolute top-2 right-2 text-white bg-red-500 px-4 py-2" @click="$emit('close')">Close
                    </button>
                </div>
            </div>
        </template>
    </DialogModal>
</template>
