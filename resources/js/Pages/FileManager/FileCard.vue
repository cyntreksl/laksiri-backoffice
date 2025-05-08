<script setup>
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
import Card from 'primevue/card';
import Image from 'primevue/image';

defineProps({
    file: {
        type: Object,
        default: () => {
        }
    }
})

const isImage = (type) => {
    return typeof type === 'string' && type.startsWith('image/');
}

const isVideo = (type) => {
    return typeof type === 'string' && type.startsWith('video/');
}

const isPDF = (type) => {
    return typeof type === 'string' && type.startsWith('application/pdf');
}

const isZIP = (type) => {
    return typeof type === 'string' && type.startsWith('application/zip');
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
    <Card>
        <template #title>
            <div class="flex items-center justify-between">
                <template v-if="isImage(file.type)">
                    <!-- Image thumbnail -->
                    <Image :alt="file.name" :src="file.url" class="size-14" preview />
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
            </div>
        </template>
        <template #content>
            <div class="font-medium truncate max-w-full">
                {{ file.name }}
            </div>
            <div class="mt-1.5 flex items-center justify-between">
                <p class="text-gray-400 text-xs+ dark:text-navy-300">
                    {{ isDocument(file.type) ? 'DOC' : isExcel(file.type) ? 'XLSX' : file.type }}
                </p>
                <p class="font-medium text-slate-600 dark:text-navy-100">
                    {{ file.size }}
                </p>
            </div>
        </template>
    </Card>

    <VideoPlayerModal :selected-video-url="selectedVideoUrl" :show="isModalVisible" @close="closeModal" />
</template>
