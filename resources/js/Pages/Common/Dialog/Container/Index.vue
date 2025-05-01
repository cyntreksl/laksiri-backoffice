<script setup>
import Dialog from "primevue/dialog";
import Tab from "primevue/tab";
import Tabs from "primevue/tabs";
import TabPanel from "primevue/tabpanel";
import TabList from "primevue/tablist";
import Button from "primevue/button";
import TabPanels from "primevue/tabpanels";
import TabHBLUnderShipment from "@/Pages/Common/Dialog/Container/Tabs/TabHBLUnderShipment.vue";
import TabHandlingProcedure from "@/Pages/Common/Dialog/Container/Tabs/TabHandlingProcedure.vue";
import TabMHBLUnderShipment from "@/Pages/Common/Dialog/Container/Tabs/TabMHBLUnderShipment.vue";
import TabShipmentDetails from "@/Pages/Common/Dialog/Container/Tabs/TabShipmentDetails.vue";

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    container: {
        type: Object,
        default: () => {
        },
    },
    containerStatus: {
        type: Array,
        default: () => [],
    },
    seaContainerOptions: {
        type: Array,
        required: true,
    },
    airContainerOptions: {
        type: Array,
        required: true,
    },
});

const emit = defineEmits(['close']);
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
        @update:visible="(newValue) => $emit('update:show', newValue)"
    >
        <Tabs value="0">
            <TabList>
                <Tab value="0">
                    <a class="flex items-center gap-2 text-inherit">
                        <i class="ti ti-app-window text-xl"/>
                        <span>HBLs</span>
                    </a>
                </Tab>
                <Tab value="1">
                    <a class="flex items-center gap-2 text-inherit">
                        <i class="ti ti-door text-xl"/>
                        <span>MHBLs</span>
                    </a>
                </Tab>
                <Tab value="2">
                    <a class="flex items-center gap-2 text-inherit">
                        <i class="ti ti-info-square-rounded text-xl"/>
                        <span>Shipment Details</span>
                    </a>
                </Tab>
                <Tab value="3">
                    <a class="flex items-center gap-2 text-inherit">
                        <i class="ti ti-file-stack text-xl"/>
                        <span>Shipment Documents</span>
                    </a>
                </Tab>
                <Tab value="4">
                    <a class="flex items-center gap-2 text-inherit">
                        <i class="ti ti-checklist text-xl"/>
                        <span>Handling Procedure</span>
                    </a>
                </Tab>
            </TabList>
            <TabPanels>
                <TabPanel value="0">
                    <TabHBLUnderShipment :container="container"/>
                </TabPanel>
                <TabPanel value="1">
                    <TabMHBLUnderShipment :container="container"/>
                </TabPanel>
                <TabPanel value="2">
                    <TabShipmentDetails :air-container-options="airContainerOptions" :container="container" :container-status="containerStatus" :sea-container-options="seaContainerOptions" />
                </TabPanel>
                <TabPanel value="3">
                    a
                </TabPanel>
                <TabPanel value="4">
                    <TabHandlingProcedure :container="container"/>
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
