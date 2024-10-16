<script setup>
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import DialogModal from "@/Components/DialogModal.vue";
import {router, useForm} from "@inertiajs/vue3";
import {push} from "notivue";
import {ref} from "vue";
import InputError from "@/Components/InputError.vue";

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['close']);

const fileInput = ref(null);

const form = useForm({
    files: []
});

const handleUploadFiles = () => {
    if (fileInput.value.files.length) {
        form.files = [...fileInput.value.files];
    }

    form.post(route('file-manager.upload'), {
        onSuccess: () => {
            console.log('done');
        },
        onError: () => {
            console.log('wrong')
        }
    })
}

const clearFileInput = () => {
    if (fileInput.value) {
        fileInput.value = null;
    }
};
</script>

<template>
    <DialogModal :maxWidth="'xl'" :show="show">
        <template #title>
            <div class="flex">
                <svg class="icon icon-tabler icons-tabler-outline icon-tabler-cloud-upload mr-2" fill="none" height="24" stroke="currentColor" stroke-linecap="round"
                     stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" width="24"
                     xmlns="http://www.w3.org/2000/svg">
                    <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                    <path d="M7 18a4.6 4.4 0 0 1 0 -9a5 4.5 0 0 1 11 2h1a3.5 3.5 0 0 1 0 7h-1"/>
                    <path d="M9 15l3 -3l3 3"/>
                    <path d="M12 12l0 9"/>
                </svg>

                File Chooser
            </div>
        </template>

        <template #content>
            <div class="mt-4">
                <input ref="fileInput" multiple type="file" v-on:change="form.files"/>
                <InputError :message="form.errors.files"/>
            </div>
        </template>

        <template #footer>
            <SecondaryButton @click="$emit('close')">
                Cancel
            </SecondaryButton>
            <PrimaryButton
                :class="{ 'opacity-25': form.processing }"
                :disabled="form.processing"
                class="ms-3"
                @click="handleUploadFiles"
            >
                Save
            </PrimaryButton>
        </template>
    </DialogModal>
</template>
