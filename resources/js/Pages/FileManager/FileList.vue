<script setup>
import PDF_IMAGE from "../../../images/file-manager/pdf.png"
import DOC_IMAGE from "../../../images/file-manager/doc.png"
import XLS_IMAGE from "../../../images/file-manager/xls.png"
import ZIP_IMAGE from "../../../images/file-manager/zip.png"
import PPT_IMAGE from "../../../images/file-manager/ppt.png"
import TXT_IMAGE from "../../../images/file-manager/txt.png"
import MUSIC_IMAGE from "../../../images/file-manager/music.png"
import UNKNOWN_IMAGE from "../../../images/file-manager/php.png"
import {router} from "@inertiajs/vue3";
import {push} from "notivue";
import {ref} from "vue";
import VideoPlayerModal from "@/Pages/FileManager/VideoPlayerModal.vue";
import DataView from 'primevue/dataview';
import Button from 'primevue/button';
import {useConfirm} from "primevue/useconfirm";
import Image from 'primevue/image';

const props = defineProps({
    files: {
        type: Object,
        default: () => {
        }
    }
})

const confirm = useConfirm();

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
    <DataView :rows="5" :value="files" class="my-5" paginator>
        <template #list="slotProps">
            <div class="flex flex-col">
                <div v-for="(item, index) in slotProps.items" :key="index">
                    <div :class="{ 'border-t border-surface-200 dark:border-surface-700': index !== 0 }" class="flex flex-col sm:flex-row sm:items-center p-6 gap-4">
                        <div class="md:w-40 relative">
                            <template v-if="isImage(item.type)">
                                <!-- Image thumbnail -->
                                <Image :alt="item.name" :src="item.url" class="block xl:block mx-auto border-round w-full" preview />
                            </template>

                            <template v-else-if="isVideo(item.type)">
                                <!-- Video thumbnail -->
                                <video :src="item.url" class="size-8" @click="playVideoInModal(item.url)"/>
                            </template>

                            <template v-else-if="isPDF(item.type)">
                                <!-- PDF thumbnail -->
                                <img :alt="item.name" :src="PDF_IMAGE" class="size-8">
                            </template>

                            <template v-else-if="isDocument(item.type)">
                                <!-- DOC thumbnail -->
                                <img :alt="item.name" :src="DOC_IMAGE" class="size-8">
                            </template>

                            <template v-else-if="isExcel(item.type)">
                                <!-- XLSX thumbnail -->
                                <img :alt="item.name" :src="XLS_IMAGE" class="size-8">
                            </template>

                            <template v-else-if="isZIP(item.type)">
                                <!-- ZIP thumbnail -->
                                <img :alt="item.name" :src="ZIP_IMAGE" class="size-8">
                            </template>

                            <template v-else-if="isPPT(item.type)">
                                <!-- PPT thumbnail -->
                                <img :alt="item.name" :src="PPT_IMAGE" class="size-8">
                            </template>

                            <template v-else-if="isMusic(item.type)">
                                <!-- Music thumbnail -->
                                <img :alt="item.name" :src="MUSIC_IMAGE" class="size-8">
                            </template>

                            <template v-else-if="isText(item.type)">
                                <!-- TEXT thumbnail -->
                                <img :alt="item.name" :src="TXT_IMAGE" class="size-8">
                            </template>

                            <template v-else>
                                <!-- Default for other types -->
                                <img :alt="item.name" :src="UNKNOWN_IMAGE" class="size-8">
                            </template>
<!--                            <div class="absolute bg-black/70 rounded-border" style="left: 4px; top: 4px">-->
<!--                                <Tag :value="item.inventoryStatus" :severity="getSeverity(item)"></Tag>-->
<!--                            </div>-->
                        </div>
                        <div class="flex flex-col md:flex-row justify-between md:items-center flex-1 gap-6">
                            <div class="flex flex-row md:flex-col justify-between items-start gap-2">
                                <div>
                                    <span class="font-medium text-gray-400 text-sm">{{ item.size }}</span>
                                    <div class="text-lg font-medium mt-2" @click="isVideo(item.type) && playVideoInModal(item.url)">{{ item.name }}</div>
                                </div>
                            </div>
                            <div class="flex flex-col md:items-end gap-8">
                                <span class="text-sm font-semibold">{{
                                        isDocument(item.type) ? 'DOC' : isExcel(item.type) ? 'XLSX' : item.type
                                    }}</span>
                                <div class="flex flex-row-reverse md:flex-row gap-2">
                                    <a :href="route('file-manager.downloads.single', item.id)">
                                        <Button class="flex-auto md:flex-initial whitespace-nowrap" icon="pi pi-download" label="Download" size="small"></Button>
                                    </a>

                                    <Button icon="pi pi-trash" severity="danger" size="small" @click.prevent="handleDeleteFile(item.id)"></Button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </DataView>

    <VideoPlayerModal :selected-video-url="selectedVideoUrl" :show="isModalVisible" @close="closeModal"/>
</template>
