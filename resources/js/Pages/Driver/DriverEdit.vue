<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import UpdateDriverBasicDetailsForm from "@/Pages/Driver/Partials/UpdateDriverBasicDetailsForm.vue";
import UpdateDriverPasswordForm from "@/Pages/Driver/Partials/UpdateDriverPasswordForm.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";

defineProps({
  user: {
    type: Object,
    default: () => {},
  },
  zones: {
    type: Object,
    default: () => {},
  },
});
</script>

<template>
  <AppLayout title="Edit Driver">
    <template #header>Edit Driver</template>

    <!-- Breadcrumb -->
    <Breadcrumb />

    <div class="grid grid-cols-1 mt-4 gap-4">
      <UpdateDriverBasicDetailsForm :user="user" :zones="zones" />
      <UpdateDriverPasswordForm :user="user" />
    </div>
  </AppLayout>
</template>
