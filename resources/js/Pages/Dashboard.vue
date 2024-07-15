<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import DashboardCard from "@/Components/Widgets/DashboardCard.vue";

defineProps({
    assignedJobs: {
        type: Number,
        default: 0,
    },
    pickedJobs: {
        type: Number,
        default: 0,
    },
    pendingJobs: {
        type: Number,
        default: 0,
    },
    totalHBLs: {
        type: Number,
        default: 0,
    },
    loadedShipments: {
        type: Number,
        default: 0,
    },
    totalContainers: {
        type: Number,
        default: 0,
    },
    warehouses: {
        type: Number,
        default: 0,
    },
    cashSettlements: {
        type: Number,
        default: 0,
    },
})

</script>

<template>
    <AppLayout title="Home">
        <template #header>Dashboard</template>

        <div class="grid grid-cols-2 gap-4 sm:grid-cols-4 sm:gap-5 lg:grid-cols-6 lg:gap-6">
            <DashboardCard :count="assignedJobs" icon="briefcase" icon-color="secondary" title="Assigned Job"/>
            <DashboardCard :count="pickedJobs" icon="person-biking" icon-color="success" title="Picked"/>
            <DashboardCard :count="pendingJobs" icon="hourglass-half" icon-color="warning" title="Pending Job"/>
            <DashboardCard :count="totalHBLs" icon="box" icon-color="info" title="Total HBL"/>
            <DashboardCard :count="loadedShipments" icon="truck-ramp-box" icon-color="error" title="Loaded Shipment"/>
            <DashboardCard :count="totalContainers" icon="truck-moving" icon-color="primary" title="Total Containers"/>
            <DashboardCard :count="warehouses" icon="warehouse" icon-color="dark" title="Warehouse"/>
            <DashboardCard :count="cashSettlements" icon="cash-register" icon-color="warning" title="Cash Settlements"/>
        </div>
    </AppLayout>
</template>


