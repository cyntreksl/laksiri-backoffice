<script setup>
import {ref, watch} from "vue";
import {usePage} from "@inertiajs/vue3";
import FileCard from "@/Pages/Arrival/Partials/FileCard.vue";
import Dialog from "primevue/dialog";

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    unloadingIssueID: {
        type: Number,
        default: false,
    }
});

const images = ref([]);
const isLoading = ref(false);
const emit = defineEmits(['close']);

// Fetch images based on provided IDs
const fetchImages = async () => {
    if (!props.unloadingIssueID) return;
    isLoading.value = true;
    try {
        const response = await fetch(`/get-unloading-issues-image/${props.unloadingIssueID}`, {
            method: "GET",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": usePage().props.csrf,
            },
        });
        if (!response.ok) {
            throw new Error(`Failed to fetch image with ID ${id}`);
        }
        const data = await response.json();
        images.value =  data;
    } catch (error) {
        console.error("Error fetching images:", error);
    } finally {
        isLoading.value = false;
    }
};

// Watch for changes in `imageIds` prop
watch(() => props.unloadingIssueID, () => {
    images.value = [];
    fetchImages();
});
</script>

<template>
    <Dialog :visible="show" header="Attachments" modal @update:visible="(newValue) => $emit('update:show', newValue)">

        <div v-if="isLoading">
            <i class="pi pi-spin pi-spinner flex justify-center items-center text-primary" style="font-size: 2rem"></i>

            <div class="text-center text-gray-500 py-8">
                Loading Attachments.
            </div>
        </div>

        <div v-else>
            <div
                v-if="Object.keys(images).length > 0"
                class="pt-4 transition-all duration-[.25s]">
                <div class="grid grid-cols-4 gap-4">
                    <FileCard v-for="file in images.slice(0, 12)" :key="file.id" :file="file" @refresh-files="fetchImages"/>
                </div>
            </div>

            <div v-else class="text-center text-gray-500 py-8">
                No attachments available.
            </div>
        </div>
    </Dialog>
</template>
