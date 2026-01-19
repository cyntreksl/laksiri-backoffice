<script setup>
import {onMounted, ref, watch} from "vue";
import NotFound from '@/../images/illustrations/empty-girl-box.svg';
import LongVehicle from '@/../images/illustrations/long-vehicle.png';
import CargoPlane from '@/../images/illustrations/cargo-plane.png';
import InfoDisplay from "@/Pages/Common/Components/InfoDisplay.vue";
import {usePage} from '@inertiajs/vue3';
import Card from "primevue/card";
import Chip from 'primevue/chip';
import Button from 'primevue/button';
import LoadedShipmentDetailDialog from "@/Pages/Common/Dialog/Container/Index.vue";
import axios from "axios";

const props = defineProps({
    hbl: {
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
        default: () => [],
    },
    airContainerOptions: {
        type: Array,
        default: () => [],
    },
})

const container = ref({});
const isLoadingContainer = ref(false);
const showShipmentDetailModal = ref(false);
const fullContainerData = ref(null);
const isLoadingFullContainer = ref(false);

const fetchContainer = async () => {
    isLoadingContainer.value = true;

    try {
        const response = await fetch(`/get-container/${props.hbl.id}`, {
            method: "GET",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": usePage().props.csrf,
            },
        });

        if (!response.ok) {
            throw new Error('Network response was not ok.');
        } else {
            container.value = await response.json();
        }

    } catch (error) {
        console.log(error);
    } finally {
        isLoadingContainer.value = false;
    }
}

const viewShipmentDetails = async () => {
    if (!container.value?.id) return;

    isLoadingFullContainer.value = true;

    try {
        const response = await axios.get(`/loaded-containers/get-container/${container.value.id}`);
        fullContainerData.value = response.data[0];
        showShipmentDetailModal.value = true;
    } catch (error) {
        console.error('Error fetching full container details:', error);
    } finally {
        isLoadingFullContainer.value = false;
    }
}

watch(() => props.hbl, (newVal) => {
    if (newVal !== undefined) {
        fetchContainer();
    }
});

onMounted(() => {
    if (props.hbl !== null && props.hbl.id !== undefined) {
        fetchContainer();
    }
});

const resolveCargoType = (cargoType) => {
    switch (cargoType) {
        case 'Sea Cargo':
            return {
                icon: "ti ti-sailboat",
            };
        case 'Air Cargo':
            return {
                icon: "ti ti-plane-tilt",
            };
        default:
            return null;
    }
};
</script>

<template>
    <div v-if="Object.values(container).length !== 0">

        <Card class="!border-2 !border-emerald-200 !shadow-none">
            <template #content>
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-3xl uppercase font-normal">
                            {{ container?.reference }}
                        </p>
                        <div class="flex space-x-2">
                            <Chip :icon="resolveCargoType(container?.cargo_type).icon" :label="container?.cargo_type" class="!bg-slate-200"/>
                            <Chip :label="hbl?.hbl_type" class="!bg-cyan-200"/>
                            <Chip :label="container?.container_type" class="!bg-amber-200" icon="pi pi-arrows-h"/>
                        </div>
                    </div>
                    <div class="flex flex-col items-end gap-3">
                        <Button
                            :loading="isLoadingFullContainer"
                            icon="pi pi-external-link"
                            label="View Shipment Details"
                            outlined
                            severity="info"
                            @click="viewShipmentDetails"
                        />
                        <img v-if="container?.cargo_type === 'Sea Cargo'" :src="LongVehicle" alt="image"
                             class="w-48"/>

                        <img v-if="container?.cargo_type === 'Air Cargo'" :src="CargoPlane" alt="image"
                             class="w-48"/>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-4 mt-10 gap-10">
                    <InfoDisplay :label="container.awb_number ? 'AWB NO' : 'BL NO'" :value="container.awb_number || container.bl_number"/>

                    <InfoDisplay :value="container.container_number" label="Shipment NO"/>

                    <InfoDisplay v-if="container.seal_number" :value="container.seal_number" label="Seal NO"/>

                    <InfoDisplay :value="container?.estimated_time_of_departure" label="ETD"/>

                    <InfoDisplay :value="container?.estimated_time_of_arrival" label="ETA"/>

                    <InfoDisplay v-if="container.vessel_name" :value="container?.vessel_name" label="Vessel Name"/>

                    <InfoDisplay v-if="container.voyage_number" :value="container?.voyage_number" label="Voyage Number"/>

                    <InfoDisplay v-if="container.shipping_line" :value="container?.shipping_line" label="Shipping Line"/>

                    <InfoDisplay v-if="container.port_of_loading" :value="container?.port_of_loading" label="Port of Loading"/>

                    <InfoDisplay v-if="container.port_of_discharge" :value="container?.port_of_discharge" label="Port of Discharge"/>

                    <InfoDisplay v-if="container.flight_number" :value="container?.flight_number" label="Flight Number"/>

                    <InfoDisplay v-if="container.airline_name" :value="container?.airline_name" label="Airline Name"/>

                    <InfoDisplay v-if="container.airport_of_departure" :value="container?.airport_of_departure" label="Airport of Departure"/>

                    <InfoDisplay v-if="container.airport_of_arrival" :value="container?.airport_of_arrival" label="Airport of Arrival"/>

                    <InfoDisplay v-if="container.cargo_class" :value="container?.cargo_class" label="Cargo Class"/>

                    <InfoDisplay v-if="container.loading_started_at" :value="container?.loading_started_at" label="Loading Started At"/>

                    <InfoDisplay v-if="container.loading_ended_at" :value="container?.loading_ended_at" label="Loading Ended At"/>

                    <InfoDisplay :value="container?.note" label="Note"/>

                    <InfoDisplay :value="container?.loading_started_at" label="Created Date"/>

                    <InfoDisplay :value="container?.reached_date" label="Reached Date"/>

                    <InfoDisplay :value="container?.is_reached ? 'Yes' : 'No'" label="Has Reached Destination?"/>
                </div>
            </template>
        </Card>
    </div>

    <div v-if="Object.values(container).length === 0" class="w-full flex justify-center">
        <img :src="NotFound" alt="image"
             class="w-1/4 mt-10"/>
    </div>

    <LoadedShipmentDetailDialog
        v-if="fullContainerData"
        :air-container-options="airContainerOptions"
        :container="fullContainerData"
        :container-status="containerStatus"
        :sea-container-options="seaContainerOptions"
        :show="showShipmentDetailModal"
        @update:show="showShipmentDetailModal = $event"
    />
</template>

<style>
.p-tag-icon {
    font-size: 15px;
}
</style>
