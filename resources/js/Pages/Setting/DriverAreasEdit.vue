<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import UpdateDriverAreasForm from "@/Pages/Setting/DriverAreas/UpdateDriverAreasForm.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";

defineProps({
    driverAreas: {
        type: Object,
        default: () => {
        },
    },
    zones: {
        type: Object,
        default: () => {
        },
    },
    zoneIds: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <AppLayout title="Edit Driver Area">
        <template #header>Driver Area</template>

        <!-- Breadcrumb -->
        <Breadcrumb :driverAreas="driverAreas"/>

        <div class="grid grid-cols-1 mt-4 gap-4">
            <UpdateDriverAreasForm :driverAreas="driverAreas" :zone-ids="zoneIds" :zones="zones"/>
        </div>
    </AppLayout>
</template>
