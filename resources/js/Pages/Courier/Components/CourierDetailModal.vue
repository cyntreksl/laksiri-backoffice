<script setup>
import { ref, watch, onMounted } from "vue";
import { usePage } from '@inertiajs/vue3';
import Dialog from "primevue/dialog";
import Button from "primevue/button";
import Tabs from "primevue/tabs";
import TabList from "primevue/tablist";
import Tab from "primevue/tab";
import TabPanels from "primevue/tabpanels";
import TabPanel from "primevue/tabpanel";
import CourierDetailsTab from "./CourierDetailsTab.vue";
import CourierPaymentsTab from "./CourierPaymentsTab.vue";

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    courierId: {
        type: Number,
        default: null,
    },
});

const emit = defineEmits(['close', 'update:show']);

const courier = ref({});
const isLoading = ref(false);

const fetchCourier = async () => {
    if (!props.courierId) return;

    isLoading.value = true;

    try {
        const response = await fetch(`/couriers/${props.courierId}`, {
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
            courier.value = data;
        }

    } catch (error) {
        console.log('Error fetching courier:', error);
    } finally {
        isLoading.value = false;
    }
};

watch(() => props.courierId, (newVal) => {
    if (newVal !== undefined && newVal !== null) {
        fetchCourier();
    }
});

watch(() => props.show, (newVal) => {
    if (newVal && props.courierId) {
        fetchCourier();
    }
});

onMounted(() => {
    if (props.courierId !== null && props.show) {
        fetchCourier();
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
        header="Courier Overview"
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
                <Tab v-if="courier && Object.keys(courier).length > 0" value="1">
                    <a class="flex items-center gap-2 text-inherit">
                        <i class="pi pi-dollar" />
                        <span>Payments</span>
                    </a>
                </Tab>
            </TabList>
            <TabPanels>
                <TabPanel value="0">
                    <CourierDetailsTab :courier="courier" :is-loading="isLoading" />
                </TabPanel>
                <TabPanel value="1">
                    <CourierPaymentsTab :courier="courier" :is-loading="isLoading" />
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


