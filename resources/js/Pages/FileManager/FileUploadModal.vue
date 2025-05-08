<script setup>
import { useForm } from "@inertiajs/vue3";
import { push } from "notivue";
import InputError from "@/Components/InputError.vue";
import 'filepond/dist/filepond.min.css';
import vueFilePond from 'vue-filepond';
import FilePondPluginImagePreview from 'filepond-plugin-image-preview';
import FilePondPluginFileValidateType from 'filepond-plugin-file-validate-type';
import 'filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css';
import Dialog from "primevue/dialog";
import Button from "primevue/button";


const FilePond = vueFilePond(FilePondPluginImagePreview, FilePondPluginFileValidateType);

const props = defineProps({
    visible: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["update:visible"]);

const form = useForm({
    files: []
});

const handleUploadFiles = () => {
    form.post(route('file-manager.upload'), {
        onSuccess: () => {
            emit('close');
            push.success('File Uploaded Successfully!');
            clearFileInput();
        },
        onError: () => {
            console.log('wrong')
        }
    })
}

const clearFileInput = () => {
    if (pond.value) {
        pond.value.removeFiles();
    }
};

const updateFiles = (files) => {
    form.files = files.map(file => file.file);
}
</script>

<template>
    <Dialog :style="{ width: '25rem' }" :visible="visible" header="File Chooser" modal @update:visible="(newValue) => $emit('update:visible', newValue)">

        <div>
            <FilePond
                ref="pond"
                accepted-file-types="image/jpeg, image/png, application/pdf"
                allow-multiple="true"
                label-idle="Drop files here or <span class='filepond--label-action'>Browse</span>"
                name="test"
                style="border: 2px dashed #e2e7ee; border-radius: 2px; padding: 2px;"
                v-on:updatefiles="updateFiles"
            />
            <InputError :message="form.errors.files"/>
        </div>

        <div class="flex justify-end gap-2 mt-5">
            <Button label="Cancel" severity="secondary" type="button" @click="emit('close')"></Button>
            <Button :class="{ 'opacity-25': form.processing }" :disabled="form.processing" label="Upload" type="button"
                    @click="handleUploadFiles"></Button>
        </div>

    </Dialog>
</template>
