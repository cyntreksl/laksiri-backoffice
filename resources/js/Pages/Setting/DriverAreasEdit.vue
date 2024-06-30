<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import UpdateDriverAreasForm from "@/Pages/Setting/DriverAreas/UpdateDriverAreasForm.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";

defineProps({
  driverareas: {
    type: Object,
    default: () => {},
  },
});
</script>

<template>
  <AppLayout title="Edit Driver Area">
    <template #header>Driver Area</template>

    <!-- Breadcrumb -->
    <Breadcrumb :driverareas="driverareas" />

    <div class="grid grid-cols-1 mt-4 gap-4">
      <UpdateDriverAreasForm :driverareas="driverareas" />
    </div>
  </AppLayout>
</template>
