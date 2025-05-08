<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import {ref} from "vue";
import FileUploadModal from "@/Pages/FileManager/FileUploadModal.vue";
import FileCard from "@/Pages/FileManager/FileCard.vue";
import uploadCloud from "../../../images/illustrations/upload-cloud.svg"
import FileList from "@/Pages/FileManager/FileList.vue";
import Button from 'primevue/button';
import ContextMenu from 'primevue/contextmenu';
import {router} from "@inertiajs/vue3";
import {push} from "notivue";
import {useConfirm} from "primevue/useconfirm";

const props = defineProps({
    files: {
        type: Object,
        default: () => {
        }
    }
})

const menu = ref();
const confirm = useConfirm();
const selectedFile = ref();
const items = ref([
    { label: 'Download', icon: 'pi pi-download', url: () => route('file-manager.downloads.single', selectedFile.value.id) },
    { label: 'Delete', icon: 'pi pi-trash', command: () => handleDeleteFile(selectedFile.value.id) }
]);

const onCardRightClick = (event, file) => {
    selectedFile.value = file;
    menu.value.show(event);
};

const isShowFileUploadModal = ref(false);

const confirmFileUpload = () => {
    isShowFileUploadModal.value = true;
};

const closeFileUploadModal = () => {
    isShowFileUploadModal.value = false;
};

const handleDeleteFile = (id) => {
    confirm.require({
        message: 'Would you like to delete this file?',
        header: 'Delete File?',
        icon: 'pi pi-info-circle',
        rejectLabel: 'Cancel',
        rejectProps: {
            label: 'Cancel',
            severity: 'secondary',
            outlined: true
        },
        acceptProps: {
            label: 'Delete',
            severity: 'danger'
        },
        accept: () => {
            router.delete(route('file-manager.destroy', id), {
                onSuccess: () => {
                    push.success('File Deleted Successfully!');
                }
            });
        },
        reject: () => {
        }
    });
}
</script>

<template>
    <AppLayout title="Laksiri - File Manager">

        <div
            class="flex items-center justify-between space-x-2 px-[var(--margin-x)] pb-4 transition-all duration-[.25s]">
            <div class="flex space-x-2">
                <Button icon="pi pi-upload" label="Upload" severity="help" type="button" @click.prevent="confirmFileUpload" />
            </div>
        </div>

        <div
            class="mt-4 grid grid-cols-12 gap-4 px-[var(--margin-x)] transition-all duration-[.25s] sm:mt-5 sm:gap-5 lg:gap-6">
            <div class="col-span-12">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100">
                        Recent Files
                    </h2>
                </div>

                <div
                    v-if="Object.keys(files).length > 0"
                    class="pt-4 transition-all duration-[.25s]">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-4">
                        <div
                            v-for="file in files.slice(0, 12)"
                            :key="file.id"
                            aria-haspopup="true"
                            @contextmenu.prevent="onCardRightClick($event, file)"
                        >
                            <FileCard :file="file" />
                        </div>
                        <ContextMenu ref="menu" :model="items" @hide="selectedFile = null" />
                    </div>
                </div>

                <div v-else class="flex justify-center py-10">
                    <div class="text-center">
                        <img :src="uploadCloud" alt="upload-cloud">
                    </div>
                </div>
            </div>
        </div>

        <div
            v-if="Object.keys(files).length > 0"
            class="mt-4 px-[var(--margin-x)] transition-all duration-[.25s] sm:mt-5 sm:gap-5 lg:gap-6">
            <div>
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100">
                        All Files
                    </h2>
                </div>
                <FileList :files="files"/>
            </div>
        </div>

        <FileUploadModal :visible="isShowFileUploadModal"
                         @close="closeFileUploadModal"
                         @update:visible="isShowFileUploadModal = $event" />

    </AppLayout>
</template>
