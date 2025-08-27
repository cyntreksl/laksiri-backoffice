<script setup>
import {onMounted, ref, watch} from "vue";
import { usePage } from '@inertiajs/vue3';
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';
import Tabs from 'primevue/tabs';
import TabList from 'primevue/tablist';
import Tab from 'primevue/tab';
import TabPanels from 'primevue/tabpanels';
import TabPanel from 'primevue/tabpanel';
import TabDocuments from "@/Pages/Common/Dialog/HBL/Tabs/TabDocuments.vue";
import TabStatus from "@/Pages/Common/Dialog/HBL/Tabs/TabStatus.vue";
import TabShipment from "@/Pages/Common/Dialog/HBL/Tabs/TabShipment.vue";
import TabHBLDetails from "@/Pages/Common/Dialog/HBL/Tabs/TabHBLDetails.vue";
import TabHBLCharge from "@/Pages/Common/Dialog/HBL/Tabs/TabHBLCharge.vue";
import TabPayments from "@/Pages/Common/Dialog/HBL/Tabs/TabPayments.vue";
import TabRemarks from "@/Pages/Common/Dialog/HBL/Tabs/TabRemarks.vue";

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

    if (props.pickupId === null || props.pickupId === undefined) {
        pickup.value = {};
        isLoading.value = false;
        return;
    }

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
        :draggable="false"
        :style="{ width: '80rem' }"
        :visible="show"
        closable
        close-on-escape
        dismissable-mask
        header="Overview"
        maximizable
        modal
        @afterHide="emit('close')"
        @update:visible="(newValue) => $emit('update:show', newValue)"
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
                        <span>Charges</span>
                    </a>
                </Tab>
                <Tab value="2">
                    <a class="flex items-center gap-2 text-inherit">
                        <i class="pi pi-wallet" />
                        <span>Payments</span>
                    </a>
                </Tab>
                <Tab value="3">
                    <a class="flex items-center gap-2 text-inherit">
                        <i class="pi pi-truck" />
                        <span>Shipment</span>
                    </a>
                </Tab>
                <Tab value="4">
                    <a class="flex items-center gap-2 text-inherit">
                        <i class="pi pi-chart-bar" />
                        <span>Status & Audit</span>
                    </a>
                </Tab>
                <Tab value="5">
                    <a class="flex items-center gap-2 text-inherit">
                        <i class="pi pi-file" />
                        <span>Documents</span>
                    </a>
                </Tab>
                <Tab value="6">
                    <a class="flex items-center gap-2 text-inherit">
                        <i class="pi pi-comments" />
                        <span>Remarks</span>
                    </a>
                </Tab>
            </TabList>
            <TabPanels>
                <TabPanel value="0">
                    <TabHBLDetails :hbl="hbl" :is-loading="isLoading" :pickup="pickup" />
                </TabPanel>
                <TabPanel value="1">
                    <TabHBLCharge :hbl="hbl"></TabHBLCharge>
                </TabPanel>
                <TabPanel value="2">
                    <TabPayments :hbl="hbl"></TabPayments>
                </TabPanel>
                <TabPanel value="3">
                    <TabShipment v-if="hbl" :hbl="hbl" :pickup="pickup" />
                </TabPanel>
                <TabPanel value="4">
                    <TabStatus v-if="hbl" :hbl="hbl" :pickup="pickup" />
                </TabPanel>
                <TabPanel value="5">
                    <TabDocuments v-if="hbl" :hbl-id="hbl.id"/>
                </TabPanel>
                <TabPanel value="6">
                    <TabRemarks v-if="hbl" :hbl-id="hbl.id"/>
                </TabPanel>
            </TabPanels>
        </Tabs>

        <template #footer>
            <div class="mt-3">
                <Button label="Cancel" severity="secondary" @click="$emit('close')"/>
            </div>
        </template>
    </Dialog>
</template>
