<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import UpdateWarehousezoneForm from "@/Pages/Setting/Wareahousezones/Partials/UpdateWarehousezoneForm.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";

defineProps({
  warehousezone: {
    type: Object,
    default: () => {},
  },
  branches: {
    type: Object,
    default: () => {},
  },
});
</script>

<template>
  <AppLayout title="Edit Warehouse Zone">
    <template #header>Warehouse Zones</template>

    <!-- Breadcrumb -->
    <Breadcrumb :warehousezone="warehousezone" />

    <div class="grid grid-cols-1 mt-4 gap-4">
      <UpdateWarehousezoneForm :warehousezone="warehousezone" />
    </div>
  </AppLayout>
</template>
