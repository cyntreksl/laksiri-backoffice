<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
// import UpdatePasswordForm from "@/Pages/User/Partials/UpdatePasswordForm.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
// import UpdateBasicDetailsForm from "@/Pages/User/Partials/UpdateBasicDetailsForm.vue";
// import UpdateBranchForm from "@/Pages/User/Partials/UpdateBranchForm.vue";

defineProps({
    user: {
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
        <Breadcrumb :user="user"/>

        <div class="grid grid-cols-1 mt-4 gap-4">
            <UpdateBasicDetailsForm :user="user" :roles="roles"/>

            <UpdatePasswordForm :user="user"/>

            <UpdateBranchForm :user="user" :branches="branches"/>
        </div>
    </AppLayout>
</template>
