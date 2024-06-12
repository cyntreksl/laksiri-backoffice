<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import UpdateDriverBasicDetailsForm from "@/Pages/Driver/Partials/UpdateDriverBasicDetailsForm.vue";
// import Breadcrumb from "@/Components/Breadcrumb.vue";
// import UpdateBasicDetailsForm from "@/Pages/User/Partials/UpdateBasicDetailsForm.vue";
// import UpdateBranchForm from "@/Pages/User/Partials/UpdateBranchForm.vue";

defineProps({
  user: {
    type: Object,
    default: () => {},
  },
  roles: {
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
  <AppLayout title="Edit Driver">
    <template #header>Edit Driver</template>

    <!-- Breadcrumb -->
    <!-- <Breadcrumb /> -->

    <div class="grid grid-cols-1 mt-4 gap-4">
      <!-- <UpdateDriverBasicDetailsForm
        :user="user"
        :roles="roles"
        :branches="branches"
      /> -->
    </div>
  </AppLayout>
</template>
