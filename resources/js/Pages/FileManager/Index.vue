<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import {ref} from "vue";
import FileUploadModal from "@/Pages/FileManager/FileUploadModal.vue";
import FileCard from "@/Pages/FileManager/FileCard.vue";
import uploadCloud from "../../../images/illustrations/upload-cloud.svg"

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
                >
                    <svg class="icon icon-tabler icons-tabler-outline icon-tabler-folder-plus mr-2" fill="none" height="24" stroke="currentColor" stroke-linecap="round"
                         stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" width="24"
                         xmlns="http://www.w3.org/2000/svg">
                        <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                        <path d="M12 19h-7a2 2 0 0 1 -2 -2v-11a2 2 0 0 1 2 -2h4l3 3h7a2 2 0 0 1 2 2v3.5"/>
                        <path d="M16 19h6"/>
                        <path d="M19 16v6"/>
                    </svg>
                    <span>Create New Folder</span>
                </button>

                <button
                    class="btn space-x-2 rounded-full bg-slate-150 font-medium text-slate-800 hover:bg-slate-200 focus:bg-slate-200 active:bg-slate-200/80 dark:bg-navy-500 dark:text-navy-50 dark:hover:bg-navy-450 dark:focus:bg-navy-450 dark:active:bg-navy-450/90"
                    @click.prevent="confirmFileUpload"
                >
                    <svg class="icon icon-tabler icons-tabler-outline icon-tabler-cloud-upload mr-2" fill="none" height="24" stroke="currentColor" stroke-linecap="round"
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
            <div class="flex">
                <button
                    class="btn size-8 rounded-full p-0 hover:bg-slate-300/20 active:bg-slate-300/25 dark:hover:bg-navy-300/20 dark:active:bg-navy-300/25">
                    <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                         xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round"
                              stroke-width="1.5"/>
                    </svg>
                </button>
                <a class="btn size-8 rounded-full p-0 text-slate-500 hover:bg-slate-300/20 focus:bg-slate-300/20 active:bg-slate-300/25 dark:text-navy-200 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25"
                   href="#">
                    <svg class="size-5" fill="none" stroke="currentColor" stroke-width="1.5"
                         viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round"
                              stroke-width="1.5"/>
                    </svg>
                </a>
            </div>
        </div>

        <div class="flex flex-col">
            <div
                v-if="Object.keys(files).length > 0"
                class="px-[var(--margin-x)] pt-4 transition-all duration-[.25s]">
                <div class="swiper-wrapper space-x-6">
                    <FileCard v-for="file in files" :key="file.id" :file="file"/>
                </div>
            </div>

            <div v-else class="flex justify-center py-10">
                <div class="text-center">
                    <img :src="uploadCloud" alt="upload-cloud">
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
                <div class="card mt-3">
                    <div class="is-scrollbar-hidden min-w-full overflow-x-auto">
                        <table class="is-hoverable w-full text-left">
                            <thead>
                            <tr>
                                <th
                                    class="whitespace-nowrap rounded-tl-lg bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5">
                                    Name
                                </th>
                                <th
                                    class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5">
                                    Last edit
                                </th>
                                <th
                                    class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5">
                                    Size
                                </th>
                                <th
                                    class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5">
                                    Members
                                </th>

                                <th
                                    class="whitespace-nowrap rounded-tr-lg bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5">
                                </th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr class="border-y border-transparent border-b-slate-200 dark:border-b-navy-500">
                                <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                    <div class="flex items-center space-x-4">
                                        <svg class="size-8 text-yellow-500" fill="currentColor"
                                             viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"/>
                                        </svg>
                                        <span class="font-medium text-slate-700 dark:text-navy-100">Designs</span>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                    2 day ago
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-slate-700 dark:text-navy-100 sm:px-5">
                                    14.3GB
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                    <div class="flex -space-x-2">
                                        <div class="avatar size-7 hover:z-10">
                                            <img alt="avatar"
                                                 class="rounded-full ring ring-white dark:ring-navy-700"
                                                 src="{{ asset('images/100x100.png') }}"/>
                                        </div>

                                        <div class="avatar size-7 hover:z-10">
                                            <div
                                                class="is-initial rounded-full bg-info text-xs+ uppercase text-white ring ring-white dark:ring-navy-700">
                                                jd
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                    <button
                                        class="btn size-8 rounded-full p-0 hover:bg-slate-300/20 focus:bg-slate-300/20 active:bg-slate-300/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25">
                                        <svg class="size-5" fill="none" stroke="currentColor"
                                             stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M5 12h.01M12 12h.01M19 12h.01M6 12a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0z"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"/>
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                            <tr class="border-y border-transparent">
                                <td class="whitespace-nowrap rounded-bl-lg px-4 py-3 sm:px-5">
                                    <div class="flex items-center space-x-4">
                                        <svg class="size-8 text-yellow-500" fill="currentColor"
                                             viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"/>
                                        </svg>
                                        <span class="font-medium text-slate-700 dark:text-navy-100">Documents
                                            </span>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                    a day ago
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-slate-700 dark:text-navy-100 sm:px-5">
                                    602 MB
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                    <div class="flex -space-x-2">
                                        <div class="avatar size-7 hover:z-10">
                                            <div
                                                class="is-initial rounded-full bg-info text-xs+ uppercase text-white ring ring-white dark:ring-navy-700">
                                                gh
                                            </div>
                                        </div>
                                        <div class="avatar size-7 hover:z-10">
                                            <img alt="avatar"
                                                 class="rounded-full ring ring-white dark:ring-navy-700"
                                                 src="{{ asset('images/100x100.png') }}"/>
                                        </div>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap rounded-br-lg px-4 py-3 sm:px-5">
                                    <button
                                        class="btn size-8 rounded-full p-0 hover:bg-slate-300/20 focus:bg-slate-300/20 active:bg-slate-300/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25">
                                        <svg class="size-5" fill="none" stroke="currentColor"
                                             stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M5 12h.01M12 12h.01M19 12h.01M6 12a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0z"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"/>
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <FileUploadModal :show="isShowFileUploadModal" @close="closeFileUploadModal"/>

    </AppLayout>
</template>
