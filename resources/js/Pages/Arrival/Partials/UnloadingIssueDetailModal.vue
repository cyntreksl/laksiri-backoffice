<script setup>
import {ref, watch} from "vue";
import {usePage} from "@inertiajs/vue3";
import Dialog from "primevue/dialog";
import Card from "primevue/card";
import Image from "primevue/image";

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    issue: {
        type: Object,
        default: null,
    }
});

const images = ref([]);
const isLoading = ref(false);
const emit = defineEmits(['close', 'update:show']);

// Fetch images for the issue
const fetchImages = async () => {
    if (!props.issue?.id) return;
    isLoading.value = true;
    try {
        const response = await fetch(`/get-unloading-issues-image/${props.issue.id}`, {
            method: "GET",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": usePage().props.csrf,
            },
        });
        if (!response.ok) {
            throw new Error(`Failed to fetch images`);
        }
        const data = await response.json();
        images.value = data;
    } catch (error) {
        console.error("Error fetching images:", error);
        images.value = [];
    } finally {
        isLoading.value = false;
    }
};

// Watch for changes in issue prop
watch(() => props.issue, (newVal) => {
    if (newVal) {
        images.value = [];
        fetchImages();
    }
}, { immediate: true });

</script>

<template>
    <Dialog
        :style="{ width: '60rem' }"
        :visible="show"
        header="Unloading Issue Details"
        modal
        @update:visible="(newValue) => $emit('update:show', newValue)"
    >
        <div v-if="issue">
            <!-- Issue Information -->
            <Card class="mb-4">
                <template #content>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-semibold text-gray-600">HBL Number</label>
                            <p class="text-lg">{{ issue.hbl }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-gray-600">Issue Type</label>
                            <p class="text-lg">{{ issue.type }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-gray-600">HBL Name</label>
                            <p class="text-lg">{{ issue.hbl_name }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-gray-600">Consignee Name</label>
                            <p class="text-lg">{{ issue.consignee_name }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-gray-600">Origin Branch</label>
                            <p class="text-lg">{{ issue.branch }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-gray-600">Created Date</label>
                            <p class="text-lg">{{ issue.created_at }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-gray-600">Weight</label>
                            <p class="text-lg">{{ issue.weight ? issue.weight.toFixed(2) : '-' }} kg</p>
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-gray-600">Volume</label>
                            <p class="text-lg">{{ issue.volume ? issue.volume.toFixed(3) : '-' }} m³</p>
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-gray-600">Quantity</label>
                            <p class="text-lg">{{ issue.quantity }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-gray-600">Damaged</label>
                            <p class="text-lg">{{ issue.is_damaged }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-gray-600">Fixed</label>
                            <p class="text-lg">
                                <i :class="issue.is_fixed ? 'pi pi-check text-green-500' : 'pi pi-times text-red-500'"></i>
                            </p>
                        </div>
                    </div>

                    <!-- Remarks Section -->
                    <div v-if="issue.remarks && issue.remarks !== '-'" class="mt-4 pt-4 border-t">
                        <label class="text-sm font-semibold text-gray-600">Remarks</label>
                        <p class="text-base mt-2 whitespace-pre-wrap">{{ issue.remarks }}</p>
                    </div>

                    <!-- Note Section -->
                    <div v-if="issue.note && issue.note !== '-'" class="mt-4 pt-4 border-t">
                        <label class="text-sm font-semibold text-gray-600">Note</label>
                        <p class="text-base mt-2 whitespace-pre-wrap">{{ issue.note }}</p>
                    </div>
                </template>
            </Card>

            <!-- Photos Section -->
            <Card>
                <template #title>
                    <div class="flex items-center gap-2">
                        <i class="pi pi-images"></i>
                        <span>Attached Photos</span>
                        <span v-if="issue.photos_count > 0" class="text-sm text-gray-500">({{ issue.photos_count }})</span>
                    </div>
                </template>
                <template #content>
                    <div v-if="isLoading" class="text-center py-8">
                        <i class="pi pi-spin pi-spinner text-primary" style="font-size: 2rem"></i>
                        <p class="mt-2 text-gray-500">Loading photos...</p>
                    </div>

                    <div v-else-if="images.length > 0" class="grid grid-cols-3 gap-4">
                        <div v-for="image in images" :key="image.id" class="relative">
                            <Image
                                :alt="image.name"
                                :src="image.url"
                                class="w-full h-32 object-cover rounded"
                                preview
                            />
                            <p class="text-xs text-gray-600 mt-1 truncate">{{ image.name }}</p>
                        </div>
                    </div>

                    <div v-else class="text-center text-gray-500 py-8">
                        <i class="pi pi-image text-4xl mb-2"></i>
                        <p>No photos attached</p>
                    </div>
                </template>
            </Card>
        </div>
    </Dialog>
</template>
