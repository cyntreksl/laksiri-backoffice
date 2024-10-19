<script setup>
import {Menu, MenuButton, MenuItems, MenuItem} from '@headlessui/vue'
import {router} from "@inertiajs/vue3";
import {push} from "notivue";
import PDF_IMAGE from "../../../images/file-manager/pdf.png"
import DOC_IMAGE from "../../../images/file-manager/doc.png"
import XLS_IMAGE from "../../../images/file-manager/xls.png"
import ZIP_IMAGE from "../../../images/file-manager/zip.png"
import PPT_IMAGE from "../../../images/file-manager/ppt.png"
import TXT_IMAGE from "../../../images/file-manager/txt.png"
import MUSIC_IMAGE from "../../../images/file-manager/music.png"
import UNKNOWN_IMAGE from "../../../images/file-manager/php.png"
import {ref} from "vue";
import VideoPlayerModal from "@/Pages/FileManager/VideoPlayerModal.vue";

defineProps({
    file: {
        type: Object,
        default: () => {
        }
    }
})

const handleDeleteFile = (id) => {
    router.delete(route('file-manager.destroy', id), {
        onSuccess: () => {
            push.success('File Deleted Successfully!');
        }
    });
}

const isImage = (type) => {
    return type.startsWith('image/');
}

const isVideo = (type) => {
    return type.startsWith('video/');
}

const isPDF = (type) => {
    return type.startsWith('application/pdf');
}

const isZIP = (type) => {
    return type.startsWith('application/zip');
}

const docTypes = ['application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];

const isDocument = (type) => docTypes.includes(type);

const excelTypes = ['application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'text/csv'];

const isExcel = (type) => excelTypes.includes(type);

const musicTypes = [
    'audio/mpeg',          // MP3
    'audio/wav',           // WAV
    'audio/ogg',           // OGG
    'audio/aac',           // AAC
    'audio/flac',          // FLAC
    'audio/mp4',           // MP4 audio (M4A)
    'audio/x-ms-wma',      // WMA
    'audio/webm',          // WebM Audio
    'audio/x-aiff',        // AIFF
    'audio/midi',          // MIDI
    'audio/x-matroska'     // Matroska Audio
];

const isMusic = (type) => musicTypes.includes(type);

const textTypes = ['text/plain'];

const isText = (type) => textTypes.includes(type);

// PowerPoint types
const pptTypes = [
    'application/vnd.ms-powerpoint',
    'application/vnd.openxmlformats-officedocument.presentationml.presentation'
];

const isPPT = (type) => pptTypes.includes(type);

const isModalVisible = ref(false);
const selectedVideoUrl = ref(null);

// Function to play video in modal
const playVideoInModal = (url) => {
    selectedVideoUrl.value = url;
    isModalVisible.value = true;
}

// Function to close the modal
const closeModal = () => {
    isModalVisible.value = false;
    selectedVideoUrl.value = null;
}
</script>

<template>
    <div class="card swiper-slide w-56 p-3 pt-4 ">
        <div class="flex items-center justify-between">

            <template v-if="isImage(file.type)">
                <!-- Image thumbnail -->
                <img :alt="file.name" :src="file.url" class="size-14">
            </template>

            <template v-else-if="isVideo(file.type)">
                <!-- Video thumbnail -->
                <video :src="file.url" class="size-36" @click="playVideoInModal(file.url)"/>
            </template>

            <template v-else-if="isPDF(file.type)">
                <!-- PDF thumbnail -->
                <img :alt="file.name" :src="PDF_IMAGE" class="size-14">
            </template>

            <template v-else-if="isDocument(file.type)">
                <!-- DOC thumbnail -->
                <img :alt="file.name" :src="DOC_IMAGE" class="size-14">
            </template>

            <template v-else-if="isExcel(file.type)">
                <!-- XLSX thumbnail -->
                <img :alt="file.name" :src="XLS_IMAGE" class="size-14">
            </template>

            <template v-else-if="isZIP(file.type)">
                <!-- ZIP thumbnail -->
                <img :alt="file.name" :src="ZIP_IMAGE" class="size-14">
            </template>

            <template v-else-if="isPPT(file.type)">
                <!-- PPT thumbnail -->
                <img :alt="file.name" :src="PPT_IMAGE" class="size-14">
            </template>

            <template v-else-if="isMusic(file.type)">
                <!-- Music thumbnail -->
                <img :alt="file.name" :src="MUSIC_IMAGE" class="size-14">
            </template>

            <template v-else-if="isText(file.type)">
                <!-- TEXT thumbnail -->
                <img :alt="file.name" :src="TXT_IMAGE" class="size-14">
            </template>

            <template v-else>
                <!-- Default for other types -->
                <img :alt="file.name" :src="UNKNOWN_IMAGE" class="size-14">
            </template>

            <Menu as="div" class="relative inline-block text-left">
                <div>
                    <MenuButton
                        class="btn size-8 rounded-full p-0 hover:bg-slate-300/20 focus:bg-slate-300/20 active:bg-slate-300/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25">
                        <svg
                            viewBox="0 0 24 24"
                            class="size-5"
                            fill="none"
                            xmlns="http://www.w3.org/2000/svg"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M5 12h.01M12 12h.01M19 12h.01M6 12a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0z"
                            ></path>
                        </svg>
                    </MenuButton>
                </div>

                <transition enter-active-class="transition ease-out duration-100"
                            enter-from-class="transform opacity-0 scale-95"
                            enter-to-class="transform opacity-100 scale-100"
                            leave-active-class="transition ease-in duration-75"
                            leave-from-class="transform opacity-100 scale-100"
                            leave-to-class="transform opacity-0 scale-95">
                    <MenuItems
                        class="absolute right-0 z-50 mt-2 w-56 origin-top-right rounded-md bg-white shadow-lg ring-1 ring-gray-300 focus:outline-none">
                        <div class="py-1">
                            <MenuItem v-slot="{ active }" class="flex items-center">
                                <a :class="[active ? 'bg-gray-100 text-gray-900' : 'text-gray-700', 'cursor-pointer block px-4 py-2 text-sm']"
                                   :href="route('file-manager.downloads.single', file.id)">
                                    <svg class="icon icon-tabler icons-tabler-outline icon-tabler-download mr-2" fill="none" height="18" stroke="currentColor"
                                         stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24"
                                         width="18"
                                         xmlns="http://www.w3.org/2000/svg">
                                        <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                                        <path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2"/>
                                        <path d="M7 11l5 5l5 -5"/>
                                        <path d="M12 4l0 12"/>
                                    </svg>
                                    Download
                                </a>
                            </MenuItem>
                            <MenuItem v-slot="{ active }" class="flex items-center">
                                <button :class="[active ? 'bg-red-100 text-red-600' : 'text-red-700', 'block w-full px-4 py-2 text-left text-sm']"
                                        @click.prevent="handleDeleteFile(file.id)">
                                    <svg class="icon icon-tabler icons-tabler-outline icon-tabler-trash-x mr-2" fill="none" height="18"
                                         stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                         viewBox="0 0 24 24" width="18"
                                         xmlns="http://www.w3.org/2000/svg">
                                        <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                                        <path d="M4 7h16"/>
                                        <path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12"/>
                                        <path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3"/>
                                        <path d="M10 12l4 4m0 -4l-4 4"/>
                                    </svg>
                                    Delete
                                </button>
                            </MenuItem>
                        </div>
                    </MenuItems>
                </transition>
            </Menu>
        </div>
        <div class="pt-5 text-base font-medium tracking-wide text-primary dark:text-accent-light">
            {{ file.name }}
        </div>
        <div class="mt-1.5 flex items-center justify-between">
            <p class="text-salte-400 text-xs+ dark:text-navy-300">
                {{ isDocument(file.type) ? 'DOC' : isExcel(file.type) ? 'XLSX' : file.type }}
            </p>
            <p class="font-medium text-slate-600 dark:text-navy-100">
                {{ file.size }}
            </p>
        </div>
    </div>

    <VideoPlayerModal :selected-video-url="selectedVideoUrl" :show="isModalVisible" @close="closeModal" />
</template>
