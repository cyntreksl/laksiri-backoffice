<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import EditHBLForm from "@/Pages/HBL/Partials/EditHBLForm.vue";

defineProps({
    hbl: {
        type: Object,
        default: () => {
        }
    },
    hblTypes: {
        type: Object,
        default: () => {
        }
    },
    cargoTypes: {
        type: Object,
        default: () => {
        }
    },
    warehouses: {
        type: Object,
        default: () => {
        }
    },
    priceRules: {
        type: Object,
        default: () => {
        }
    }
})
</script>

<template>
    <AppLayout title="Edit HBL">
        <template #header>HBL Management</template>

        <Breadcrumb :hbl="hbl"/>

        <div class="grid grid-cols-1 mt-4 gap-4">
            <EditHBLForm :cargo-types="cargoTypes" :hbl="hbl" :hbl-types="hblTypes" :price-rules="priceRules" :warehouses="warehouses" />
        </div>
    </AppLayout>
</template>


