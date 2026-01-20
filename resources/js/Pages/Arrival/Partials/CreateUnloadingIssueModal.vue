<script setup>
import {ref} from "vue";
import {router, useForm} from "@inertiajs/vue3";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import {push} from "notivue";
import 'filepond/dist/filepond.min.css';
import vueFilePond from "vue-filepond";
import FilePondPluginImagePreview from "filepond-plugin-image-preview";
import FilePondPluginFileValidateType from "filepond-plugin-file-validate-type";
import 'filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css';
import Dialog from "primevue/dialog";
import Button from "primevue/button";
import IftaLabel from "primevue/iftalabel";
import InputText from "primevue/inputtext";
import Select from "primevue/select";
import Textarea from "primevue/textarea";
import Checkbox from 'primevue/checkbox';

const FilePond = vueFilePond(FilePondPluginImagePreview, FilePondPluginFileValidateType);

const props = defineProps({
    visible: {
        type: Boolean,
        default: false,
    },
    hblPackageId: {
        type: Number,
        required: true,
    }
});

const emit = defineEmits(["update:visible"]);

const types = ref([
    'Crashed', 'Broken', 'Opened'
])

const form = useForm({
    hbl_package_id: null,
    issue: '',
    is_damaged: false,
    type: null,
    files: [],
    note: '',
});

const handleCreateUnloadingIssue = () => {
    form.hbl_package_id = props.hblPackageId;
    form.post(route('arrival.unloading-points.create.unloading-issue'), {
        onSuccess: () => {
            push.success('Unloading Issue Created!');
            emit('close');
            router.visit(route("arrival.unloading-points.index", {
                'container': route().params.container,
            }));
        },
        onError: () => {
            push.error('Something went to wrong!');
        }
    })
}

const updateFiles = (files) => {
    form.files = files.map(file => file.file);
}
</script>

<template>
    <Dialog :style="{ width: '50rem' }" :visible="visible" header="Create Unloading Issue" modal
            @update:visible="(newValue) => $emit('update:visible', newValue)">
        <div class="grid grid-cols-2 gap-5">
            <div class="col-span-2">
                <IftaLabel>
                    <InputText v-model="form.issue" class="w-full" placeholder="Type Issue" variant="filled"/>
                    <label>Issue</label>
                </IftaLabel>
                <InputError :message="form.errors.issue"/>
            </div>

            <div class="col-span-2">
                <IftaLabel>
                    <Select v-model="form.type" :options="types" class="w-full" placeholder="Select a Type" variant="filled" />
                    <label>Issue Type</label>
                </IftaLabel>
                <InputError :message="form.errors.type"/>
            </div>

            <div class="col-span-2">
                <IftaLabel>
                                    <Textarea id="description" v-model="form.note" class="w-full" cols="30"
                                              placeholder="Type note here..." rows="5" style="resize: none"
                                              variant="filled"/>
                    <label for="description">Note</label>
                </IftaLabel>
                <InputError :message="form.errors.note"/>
            </div>

            <div class="col-span-2">
                <div class="flex items-center space-x-2">
                    <Checkbox v-model="form.is_damaged" binary input-id="damage"/>
                    <label class="cursor-pointer" for="damage">Damage</label>
                </div>
                <InputError :message="form.errors.is_damaged"/>
            </div>

            <div class="col-span-2">
                <InputLabel value="Upload Images"/>
                <FilePond
                    ref="pond"
                    accepted-file-types="image/jpeg, image/png, application/pdf"
                    allow-multiple="true"
                    label-idle="Drop images here or <span class='filepond--label-action'>Browse</span>"
                    name="test"
                    style="border: 2px dashed #e2e7ee; border-radius: 2px; padding: 2px;"
                    v-on:updatefiles="updateFiles"
                />
                <InputError :message="form.errors.files"/>
            </div>
        </div>

        <div class="flex justify-end gap-2 mt-5">
            <Button label="Cancel" severity="secondary" type="button" @click="emit('close')"></Button>
            <Button :class="{ 'opacity-25': form.processing }" :disabled="form.processing" label="Create Issue"
                    type="button"
                    @click="handleCreateUnloadingIssue"></Button>
        </div>
    </Dialog>
</template>
