<script setup>
import DialogModal from "@/Components/DialogModal.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import {onMounted, ref, watch} from "vue";
import {usePage} from "@inertiajs/vue3";

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    hblId: {
        type: Number,
        default: false,
    }
});

const image = ref(null);
const isLoading = ref(false);
const emit = defineEmits(['close']);

// Fetch images based on provided IDs
const fetchImages = async () => {
    if (!props.hblId) return;
    isLoading.value = true;
    try {
        const response = await fetch(`/get-unloading-issues-image/${props.hblId}`, {
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
        image.value =  data.image;
    } catch (error) {
        console.error("Error fetching images:", error);
    } finally {
        isLoading.value = false;
    }
};

// Watch for changes in `imageIds` prop
watch(() => props.hblId, () => {
    fetchImages();
});


// Download an image
const downloadImage = (url) => {
    const link = document.createElement("a");
    link.href = url;
    link.setAttribute("download", `image-${new Date().getTime()}`);
    document.body.appendChild(link);
    link.click();
    link.remove();
};
</script>

<template>
    <DialogModal :maxWidth="'7xl'" :show="show" @close="$emit('close')" :closeable="true">
        <!-- Title -->
        <template #title>
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-semibold text-gray-800">Image Viewer </h3>
                <button
                    class="text-gray-500 hover:text-red-500 focus:outline-none"
                    @click="$emit('close')"
                >
                    <svg class="size-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"
                         xmlns="http://www.w3.org/2000/svg">
                        <path d="M6 18L18 6M6 6L18 18" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            </div>
        </template>

        <!-- Content -->
        <template #content>
            <div v-if="isLoading" class="flex justify-center items-center h-48">
                <svg class="animate-spin h-8 w-8 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none"
                     viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                          d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 0012 20c4.411 0 8-3.589 8-8H4c0 2.206 1.794 4.109 4 4.709z"></path>
                </svg>
            </div>

            <div v-if="image" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 p-4">
                <div class="relative group">
                    <img
                        :src="image"
                        alt="Uploaded Image"
                        class="w-full h-48 object-cover rounded-lg shadow-md"
                    />
                    <div class="absolute bottom-2 right-2">
                        <button
                            @click="downloadImage(image)"
                            class="bg-blue-500 text-white px-3 py-1 rounded-md text-sm hover:bg-blue-600 transition duration-300"
                        >
                            Download
                        </button>
                    </div>
                </div>
            </div>

            <div v-else class="text-center text-gray-500 py-8">
                No images available.
            </div>
        </template>

        <!-- Footer -->
        <template #footer>
            <SecondaryButton @click="$emit('close')">
                Close
            </SecondaryButton>
        </template>
    </DialogModal>
</template>
