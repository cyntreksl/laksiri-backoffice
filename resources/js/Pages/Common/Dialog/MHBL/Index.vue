<script setup>
import {ref, watch} from "vue";
import { usePage } from '@inertiajs/vue3';
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';
import Tabs from 'primevue/tabs';
import TabList from 'primevue/tablist';
import Tab from 'primevue/tab';
import TabPanels from 'primevue/tabpanels';
import TabPanel from 'primevue/tabpanel';
import TabMHBLDetails from "@/Pages/Common/Dialog/MHBL/Tabs/TabMHBLDetails.vue";
import TabHBLs from "@/Pages/Common/Dialog/MHBL/Tabs/TabHBLs.vue";

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    mhblId: {
        type: Number,
        default: null,
    },
});

const emit = defineEmits(['close']);

const mhbl = ref({});
const isLoading = ref(false);

const fetchMHBL = async () => {
    isLoading.value = true;

    try {
        const response = await fetch(`/mhbls/${props.mhblId}`, {
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
            mhbl.value = data.mhbl;
        }

    } catch (error) {
        console.log(error);
    } finally {
        isLoading.value = false;
    }
}

// Watchers for prop changes
watch(() => props.mhblId, (newVal) => {
    if (newVal !== undefined) {
        fetchMHBL();
    }
});
</script>

<template>
    <Dialog
        :draggable="false"
        :style="{ width: '90rem' }"
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
                        <i class="ti ti-door" />
                        <span>MHBL Details</span>
                    </a>
                </Tab>
                <Tab value="1">
                    <a class="flex items-center gap-2 text-inherit">
                        <i class="ti ti-app-window" />
                        <span>HBLs</span>
                    </a>
                </Tab>
            </TabList>
            <TabPanels>
                <TabPanel value="0">
                    <TabMHBLDetails :is-loading="isLoading" :mhbl="mhbl" />
                </TabPanel>
                <TabPanel value="1">
                    <TabHBLs :mhbl="mhbl" />
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
