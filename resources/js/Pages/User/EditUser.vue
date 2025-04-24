<script setup>
import UpdatePasswordForm from "@/Pages/User/Partials/UpdatePasswordForm.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import UpdateBasicDetailsForm from "@/Pages/User/Partials/UpdateBasicDetailsForm.vue";
import UpdateBranchForm from "@/Pages/User/Partials/UpdateBranchForm.vue";
import AppLayout from "@/Layouts/AppLayout.vue";

defineProps({
    userRecord: {
        type: Object,
        default: () => {
        }
    },
    roles: {
        type: Object,
        default: () => {
        }
    },
    branches: {
        type: Object,
        default: () => {
        }
    },
})
</script>

<template>
    <AppLayout title="Edit User">
        <template #header>User Management</template>

        <!-- Breadcrumb -->
        <Breadcrumb :user="userRecord"/>

        <div class="grid grid-cols-1 mt-4 gap-4">
            <UpdateBasicDetailsForm :roles="roles" :user="userRecord"/>

            <UpdatePasswordForm :user="userRecord"/>

            <UpdateBranchForm :branches="branches" :user="userRecord"/>
        </div>
    </AppLayout>
</template>
