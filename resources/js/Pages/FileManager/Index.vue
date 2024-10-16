<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import {ref} from "vue";
import FileUploadModal from "@/Pages/FileManager/FileUploadModal.vue";
import FileCard from "@/Pages/FileManager/FileCard.vue";
import uploadCloud from "../../../images/illustrations/upload-cloud.svg"
import FileList from "@/Pages/FileManager/FileList.vue";

const props = defineProps({
    files: {
        type: Object,
        default: () => {
        }
    }
})

const isShowPopper = ref(false);
const isShowFileUploadModal = ref(false);

const confirmFileUpload = () => {
    isShowFileUploadModal.value = true;
};

const closeFileUploadModal = () => {
    isShowFileUploadModal.value = false;
};
</script>

<template>
    <AppLayout title="Laksiri - File Manager">

        <div
            class="flex items-center justify-between space-x-2 px-[var(--margin-x)] pb-4 transition-all duration-[.25s]">
            <div class="flex space-x-2">
                <button
                    class="btn rounded-full bg-primary/10 font-medium text-primary hover:bg-primary/20 focus:bg-primary/20 active:bg-primary/25 dark:bg-accent-light/10 dark:text-accent-light dark:hover:bg-accent-light/20 dark:focus:bg-accent-light/20 dark:active:bg-accent-light/25"
                    @click.prevent="confirmFileUpload"
                >
                    <svg class="icon icon-tabler icons-tabler-outline icon-tabler-cloud-upload mr-2" fill="none"
                         height="24" stroke="currentColor" stroke-linecap="round"
                         stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" width="24"
                         xmlns="http://www.w3.org/2000/svg">
                        <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                        <path d="M7 18a4.6 4.4 0 0 1 0 -9a5 4.5 0 0 1 11 2h1a3.5 3.5 0 0 1 0 7h-1"/>
                        <path d="M9 15l3 -3l3 3"/>
                        <path d="M12 12l0 9"/>
                    </svg>
                    <span>Upload File</span>
                </button>
            </div>
        </div>

        <div
            class="mt-4 grid grid-cols-12 gap-4 px-[var(--margin-x)] transition-all duration-[.25s] sm:mt-5 sm:gap-5 lg:gap-6">
            <div class="col-span-12">
                <div class="flex items-center justify-between">
                    <h2 class="text-base font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100">
                        Recent Files
                    </h2>
                </div>

                <div
                    v-if="Object.keys(files).length > 0"
                    class="pt-4 transition-all duration-[.25s]">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-4">
                        <FileCard v-for="file in files.slice(0, 12)" :key="file.id" :file="file"/>
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
            class="mt-4 grid grid-cols-12 gap-4 px-[var(--margin-x)] transition-all duration-[.25s] sm:mt-5 sm:gap-5 lg:gap-6">
            <div class="col-span-12">
                <div class="flex items-center justify-between">
                    <h2 class="text-base font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100">
                        All Files
                    </h2>
                </div>
                <div class="card my-3">
                    <div class="is-scrollbar-hidden min-w-full overflow-x-auto">
                        <FileList :files="files"/>
                    </div>
                </div>
            </div>
        </div>

        <FileUploadModal :show="isShowFileUploadModal" @close="closeFileUploadModal"/>

    </AppLayout>
</template>
