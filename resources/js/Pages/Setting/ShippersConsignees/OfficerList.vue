<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import CreateOfficerModal from "@/Pages/Setting/ShippersConsignees/Partials/CreateOfficer.vue";



import { ref } from "vue";

const showCreateShipperModal = ref(false);

const openCreateShipperModal = () => {
    showCreateShipperModal.value = true;
};

const closeCreateShipperModal = () => {
    showCreateShipperModal.value = false;
};

</script>

<template>
    <AppLayout title="Package Types">
        <template #header>Package Types</template>

        <Breadcrumb/>

        <div class="flex items-center justify-between p-2 my-5">
            <h2
                class="text-base font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100"
            >
                <PrimaryButton @click="openCreateShipperModal">
                    Create New Shipper
                </PrimaryButton>    <PrimaryButton> Create New Consignee</PrimaryButton>
            </h2>
        </div>

        <div class="min-w-full overflow-auto">
            <table class="is-hoverable w-full text-left">
                <thead>
                <tr>
                    <th
                        class="whitespace-nowrap rounded-tl-lg bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                    >
                        #
                    </th>
                    <th
                        class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                    >
                        Name
                    </th>
                    <th
                        class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                    >
                        Email
                    </th>
                    <th
                        class="whitespace-nowrap rounded-tr-lg bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                    >
                        Office Type
                    </th>
                </tr>
                </thead>
            </table>
        </div>
        <div v-if="showCreateShipperModal" class="modal-overlay">
            <CreateOfficerModal @close="closeCreateShipperModal" />
        </div>
    </AppLayout>
</template>
