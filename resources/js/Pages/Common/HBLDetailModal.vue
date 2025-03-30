<script setup>
import {onMounted, ref, watch} from "vue";
import TabHBLDetails from "@/Pages/Common/Partials/TabHBLDetails.vue";
import TabStatus from "@/Pages/Common/Partials/TabStatus.vue";
import TabDocuments from "@/Pages/Common/Partials/TabDocuments.vue";
import { usePage } from '@inertiajs/vue3';
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';
import Tabs from 'primevue/tabs';
import TabList from 'primevue/tablist';
import Tab from 'primevue/tab';
import TabPanels from 'primevue/tabpanels';
import TabPanel from 'primevue/tabpanel';
import TabHBLPayments from "@/Pages/Common/Partials/TabHBLPayments.vue";
import TabShipment from "@/Pages/Common/Partials/TabShipment.vue";

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

const emit = defineEmits(['close']);

const hbl = ref({});
const pickup = ref({});
const hblTotalSummary = ref({});
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

const getHBLTotalSummary = async () => {
    try {
        const response = await fetch(`/hbls/get-total-summary/${props.hblId}`, {
            method: "GET",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document
                    .querySelector('meta[name="csrf-token"]')
                    .getAttribute("content"),
            },
        });

        if (!response.ok) {
            throw new Error("Network response was not ok.");
        }else{
            hblTotalSummary.value = await response.json();
        }
    } catch (error) {
        console.error("Error:", error);
    }
};

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
        getHBLTotalSummary();
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
</script>

<template>
    <Dialog
        :style="{ width: '80rem' }"
        :visible="show"
        modal
        @update:visible="(newValue) => $emit('update:show', newValue)"
        closable
        maximizable
        dismissable-mask
        close-on-escape
        header="Overview"
        :draggable="false"
    >
        <Tabs value="0">
            <TabList>
                <Tab value="0">
                    <a class="flex items-center gap-2 text-inherit">
                        <i class="pi pi-info-circle" />
                        <span>Details</span>
                    </a>
                </Tab>
                <Tab value="1">
                    <a class="flex items-center gap-2 text-inherit">
                        <i class="pi pi-dollar" />
                        <span>Payments</span>
                    </a>
                </Tab>
                <Tab value="2">
                    <a class="flex items-center gap-2 text-inherit">
                        <i class="pi pi-truck" />
                        <span>Shipment</span>
                    </a>
                </Tab>
                <Tab value="3">
                    <a class="flex items-center gap-2 text-inherit">
                        <i class="pi pi-chart-bar" />
                        <span>Status & Audit</span>
                    </a>
                </Tab>
                <Tab value="4">
                    <a class="flex items-center gap-2 text-inherit">
                        <i class="pi pi-file" />
                        <span>Documents</span>
                    </a>
                </Tab>
            </TabList>
            <TabPanels>
                <TabPanel value="0">
                    <TabHBLDetails :hbl="hbl" :is-loading="isLoading" :pickup="pickup" />
                </TabPanel>
                <TabPanel value="1">
                    <TabHBLPayments :hbl="hbl" :hbl-total-summary="hblTotalSummary" />
                </TabPanel>
                <TabPanel value="2">
                    <TabShipment v-if="hbl" :hbl="hbl" :pickup="pickup" />
                </TabPanel>
                <TabPanel value="3">
                    <TabStatus v-if="hbl" :hbl="hbl" :pickup="pickup" />
                </TabPanel>
                <TabPanel value="4">
                    <TabDocuments v-if="hbl" :hbl-id="hbl.id"/>
                </TabPanel>
            </TabPanels>
        </Tabs>

        <template #footer>
            <div class="mt-3">
                <Button @click="$emit('close')" severity="secondary" label="Cancel"/>
            </div>
        </template>
    </Dialog>
</template>
