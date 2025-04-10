<script setup>
import {router} from "@inertiajs/vue3";
import {push} from "notivue";
import PDF_IMAGE from "../../../../images/file-manager/pdf.png"
import DOC_IMAGE from "../../../../images/file-manager/doc.png"
import XLS_IMAGE from "../../../../images/file-manager/xls.png"
import ZIP_IMAGE from "../../../../images/file-manager/zip.png"
import PPT_IMAGE from "../../../../images/file-manager/ppt.png"
import TXT_IMAGE from "../../../../images/file-manager/txt.png"
import MUSIC_IMAGE from "../../../../images/file-manager/music.png"
import UNKNOWN_IMAGE from "../../../../images/file-manager/php.png"
import {ref} from "vue";
import VideoPlayerModal from "@/Pages/FileManager/VideoPlayerModal.vue";
import Card from 'primevue/card';
import Button from 'primevue/button';
import Image from 'primevue/image';
import { useConfirm } from "primevue/useconfirm";

const props = defineProps({
    file: {
        type: Object,
        default: () => {
        }
    }
});

const emit = defineEmits(['refreshFiles']);

const confirm = useConfirm();

const handleDeleteFile = (id) => {
    confirm.require({
        message: 'Are you sure you want to proceed?',
        header: 'Delete Attachment?',
        icon: 'pi pi-info-circle',
        rejectProps: {
            label: 'Cancel',
            severity: 'secondary',
            outlined: true
        },
        acceptProps: {
            label: 'Delete',
            severity: 'danger',
        },
        accept: () => {
            router.delete(route('arrival.unloading-issues.destroy-image', id), {
                onSuccess: () => {
                    emit('refreshFiles');
                    push.success('File Deleted Successfully!');
                }
            });
        },
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
    <Card class="border" style="overflow: hidden">
        <template #header>
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
                <img :alt="file.name" :src="PDF_IMAGE" class="size-14 p-2">
            </template>

            <template v-else-if="isDocument(file.type)">
                <!-- DOC thumbnail -->
                <img :alt="file.name" :src="DOC_IMAGE" class="size-14 p-2">
            </template>

            <template v-else-if="isExcel(file.type)">
                <!-- XLSX thumbnail -->
                <img :alt="file.name" :src="XLS_IMAGE" class="size-14 p-2">
            </template>

            <template v-else-if="isZIP(file.type)">
                <!-- ZIP thumbnail -->
                <img :alt="file.name" :src="ZIP_IMAGE" class="size-14 p-2">
            </template>

            <template v-else-if="isPPT(file.type)">
                <!-- PPT thumbnail -->
                <img :alt="file.name" :src="PPT_IMAGE" class="size-14 p-2">
            </template>

            <template v-else-if="isMusic(file.type)">
                <!-- Music thumbnail -->
                <img :alt="file.name" :src="MUSIC_IMAGE" class="size-14 p-2">
            </template>

            <template v-else-if="isText(file.type)">
                <!-- TEXT thumbnail -->
                <img :alt="file.name" :src="TXT_IMAGE" class="size-14 p-2">
            </template>

            <template v-else>
                <!-- Default for other types -->
                <img :alt="file.name" :src="UNKNOWN_IMAGE" class="size-14 p-2">
            </template>
        </template>
        <template #title>{{ file.name }}</template>
        <template #subtitle>
            <p>{{ isDocument(file.type) ? 'DOC' : isExcel(file.type) ? 'XLSX' : file.type }}</p>
            <small>{{ file.size }}</small>
        </template>
        <template #footer>
            <div class="flex gap-4 mt-1">
                <Button :href="route('arrival.unloading-issues.downloads.single', file.id)" as="a" class="w-full" label="Download" outlined severity="secondary" size="small" />

                <Button class="w-full" label="Delete" severity="danger" size="small" @click.prevent="handleDeleteFile(file.id)" />
            </div>
        </template>
    </Card>

    <VideoPlayerModal :selected-video-url="selectedVideoUrl" :show="isModalVisible" @close="closeModal" />
</template>
