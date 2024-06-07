<script setup>
import DialogModal from "@/Components/DialogModal.vue";
import InputError from "@/Components/InputError.vue";
import {useForm} from "@inertiajs/vue3";
import TextInput from "@/Components/TextInput.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['close']);

const form = useForm({
    reference: null,
})
</script>

<template>
    <DialogModal :closeable="true" :maxWidth="'4xl'" :show="show" @close="close">
        <template #title>
            <div class="flex justify-between items-center">
                <div>HBL Add to Shipment</div>
                <button
                    class="text-gray-500 jus text-right hover:text-red-500 focus:outline-none"
                    @click="$emit('close')"
                >
                    <svg class="size-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"
                         xmlns="http://www.w3.org/2000/svg">
                        <path d="M6 18 18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            </div>

        </template>
        <template #content>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 items-center">
                <div class="sm:col-span-2">
                    <TextInput v-model="form.reference" class="w-full" placeholder="Enter HBL Reference"/>
                    <InputError :message="form.errors.reference"/>
                </div>

                <div>
                    <PrimaryButton :disabled="!form.reference" class="w-full">Confirm</PrimaryButton>
                </div>
            </div>
        </template>
    </DialogModal>
</template>
