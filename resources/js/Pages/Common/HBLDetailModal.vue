<script setup>
import DialogModal from "@/Components/DialogModal.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import Tabs from "@/Components/Tabs.vue";
import {onMounted, ref, watch} from "vue";
import TabHBLDetails from "@/Pages/Common/Partials/TabHBLDetails.vue";
import TabStatus from "@/Pages/Common/Partials/TabStatus.vue";
import TabDocuments from "@/Pages/Common/Partials/TabDocuments.vue";
import { usePage } from '@inertiajs/vue3';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    hblId: {
        type: Number,
        default: null,
    },
    pickupId: {
        type: Number,
        default: null,
    }
});

const hbl = ref({});
const pickup = ref({});
const isLoading = ref(false);

const fetchHBL = async () => {
    isLoading.value = true;

    try {
        const response = await fetch(`/hbls/${props.hblId}`, {
            method: "GET",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": usePage().props.csrf,
            },
        });

        if (!response.ok) {
            throw new Error('Network response was not ok.');
        } else {
            const data = await response.json();
            hbl.value = data.hbl;
        }

    } catch (error) {
        console.log(error);
    } finally {
        isLoading.value = false;
    }
}

const fetchPickup = async () => {
    isLoading.value = true;

    try {
        const response = await fetch(`/pickups/${props.pickupId}`, {
            method: "GET",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": usePage().props.csrf,
            },
        });

        if (!response.ok) {
            throw new Error('Network response was not ok.');
        } else {
            const data = await response.json();
            pickup.value = data;
        }

    } catch (error) {
        console.log(error);
    } finally {
        isLoading.value = false;
    }
}

// Watchers for prop changes
watch(() => props.hblId, (newVal) => {
    if (newVal !== undefined) {
        fetchHBL();
    }
});

watch(() => props.pickupId, (newVal) => {
    if (newVal !== null && newVal !== undefined) {
        fetchPickup();
    }
});

// Run after component is mounted
onMounted(() => {
    if (props.hblId !== null) {
        fetchHBL();
    }

    if (props.pickupId !== null) {
        fetchPickup();
    }
});

const emit = defineEmits(['close']);
</script>

<template>
    <DialogModal :maxWidth="'7xl'" :show="show" @close="close" :closeable="true">
        <template #title>
            <div class="flex justify-between items-center">
                <div></div>
                <button
                    class="text-gray-500 jus text-right hover:text-red-500 focus:outline-none"
                    @click="$emit('close')"
                >
                    <svg class="size-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"
                         xmlns="http://www.w3.org/2000/svg">
                        <path d="M6 18 18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            </div>

        </template>
        <template #content>
            <Tabs>
                <template #icon="{tab}">
                    <svg v-if="tab.name === 'tabHome'" class="w-6 h-6" fill="none" stroke="currentColor"
                         stroke-width="1.5"
                         viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z"
                            stroke-linecap="round"
                            stroke-linejoin="round"/>
                    </svg>

                    <svg v-if="tab.name === 'tabStatus'" class="w-6 h-6" fill="none" stroke="currentColor"
                         stroke-width="1.5"
                         viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M3.75 3v11.25A2.25 2.25 0 0 0 6 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0 1 18 16.5h-2.25m-7.5 0h7.5m-7.5 0-1 3m8.5-3 1 3m0 0 .5 1.5m-.5-1.5h-9.5m0 0-.5 1.5m.75-9 3-3 2.148 2.148A12.061 12.061 0 0 1 16.5 7.605"
                            stroke-linecap="round"
                            stroke-linejoin="round"/>
                    </svg>

                    <svg v-if="tab.name === 'tabDocuments'" class="w-6 h-6" fill="none" stroke="currentColor"
                         stroke-width="1.5"
                         viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"
                            stroke-linecap="round"
                            stroke-linejoin="round"/>
                    </svg>
                </template>

                <TabHBLDetails :hbl="hbl" :is-loading="isLoading" :pickup="pickup"/>

                <TabStatus v-if="hbl" :hbl="hbl" :pickup="pickup" />


                <TabDocuments v-if="hbl" :hbl-id="hbl.id"/>
            </Tabs>
        </template>

        <template #footer>
            <SecondaryButton @click="$emit('close')">
                Cancel
            </SecondaryButton>
        </template>
    </DialogModal>
</template>
