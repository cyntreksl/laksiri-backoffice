<script setup>
import Tab from "@/Components/Tab.vue";
import SimpleOverviewWidget from "@/Components/Widgets/SimpleOverviewWidget.vue";
import PrimaryOutlineButton from "@/Components/PrimaryOutlineButton.vue";
import {ref} from "vue";
import AddHBLModal from "@/Pages/Loading/Partials/AddHBLModal.vue";
import TableHBLPackages from "@/Pages/Loading/Partials/TableHBLPackages.vue";

const props = defineProps({
    container: {
        type: Object,
        default: () => {
        },
    }
});

const showConfirmAddHBLModal = ref(false);

const confirmViewLoadedShipment = () => {
    showConfirmAddHBLModal.value = true;
};

const closeModal = () => {
    showConfirmAddHBLModal.value = false;
}
</script>

<template>
    <Tab label="HBLs Under Shipment" name="tabHome">
        <div
            class="mt-3 flex flex-col items-center justify-between space-y-2 text-center sm:flex-row sm:space-y-0 sm:text-left">
            <div>
                <h3 class="text-xl font-semibold text-slate-700 dark:text-navy-100">
                    HBLs Under Loading
                </h3>
                <p class="mt-1 hidden sm:block">{{ container.reference }}</p>
            </div>
            <div class="flex items-center space-x-2">
                <PrimaryOutlineButton :disabled="container.status !== 'DRAFT'" @click="confirmViewLoadedShipment">
                    <svg class="size-5 mr-2" fill="none" stroke="currentColor"
                         stroke-width="1.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" stroke-linecap="round"
                              stroke-linejoin="round"/>
                    </svg>

                    Add HBL To Shipment
                </PrimaryOutlineButton>

                <a :href="route('loading.hbls.batch-downloads', container.id)">
                    <PrimaryOutlineButton :disabled="container.status !== 'LOADED'">
                        <svg class="size-5 mr-2" fill="none" stroke="currentColor"
                             stroke-width="1.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z"
                                stroke-linecap="round"
                                stroke-linejoin="round"/>
                        </svg>

                        Print All HBL
                    </PrimaryOutlineButton>
                </a>
            </div>
        </div>

        <div class="flex gap-3 my-3">
            <SimpleOverviewWidget :count="container?.hbl_count || 0" title="HBL">
                <svg class="icon icon-tabler icons-tabler-outline icon-tabler-app-window text-info"
                     fill="none" height="24" stroke="currentColor"
                     stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24"
                     width="24"
                     xmlns="http://www.w3.org/2000/svg">
                    <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                    <path
                        d="M3 5m0 2a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v10a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2z"/>
                    <path d="M6 8h.01"/>
                    <path d="M9 8h.01"/>
                </svg>
            </SimpleOverviewWidget>

            <SimpleOverviewWidget :count="container?.hbl_packages_count || 0" title="Package">
                <svg class="icon icon-tabler icons-tabler-outline icon-tabler-package text-info"
                     fill="none"
                     height="24" stroke="currentColor" stroke-linecap="round"
                     stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" width="24"
                     xmlns="http://www.w3.org/2000/svg">
                    <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                    <path d="M12 3l8 4.5l0 9l-8 4.5l-8 -4.5l0 -9l8 -4.5"/>
                    <path d="M12 12l8 -4.5"/>
                    <path d="M12 12l0 9"/>
                    <path d="M12 12l-8 -4.5"/>
                    <path d="M16 5.25l-8 4.5"/>
                </svg>
            </SimpleOverviewWidget>

            <SimpleOverviewWidget :count="container?.hbl_packages_sum_weight || 0.00" title="Weight">
                <svg class="icon icon-tabler icons-tabler-outline icon-tabler-weight text-info"
                     fill="none" height="24" stroke="currentColor" stroke-linecap="round"
                     stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" width="24"
                     xmlns="http://www.w3.org/2000/svg">
                    <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                    <path d="M12 6m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0"/>
                    <path
                        d="M6.835 9h10.33a1 1 0 0 1 .984 .821l1.637 9a1 1 0 0 1 -.984 1.179h-13.604a1 1 0 0 1 -.984 -1.179l1.637 -9a1 1 0 0 1 .984 -.821z"/>
                </svg>
            </SimpleOverviewWidget>

            <SimpleOverviewWidget :count="container?.hbl_packages_sum_volume || 0.00" title="Volume">
                <svg class="icon icon-tabler icons-tabler-outline icon-tabler-scale text-info"
                     fill="none"
                     height="24" stroke="currentColor" stroke-linecap="round"
                     stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" width="24"
                     xmlns="http://www.w3.org/2000/svg">
                    <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                    <path d="M7 20l10 0"/>
                    <path d="M6 6l6 -1l6 1"/>
                    <path d="M12 3l0 17"/>
                    <path d="M9 12l-3 -6l-3 6a3 3 0 0 0 6 0"/>
                    <path d="M21 12l-3 -6l-3 6a3 3 0 0 0 6 0"/>
                </svg>
            </SimpleOverviewWidget>
        </div>

        <TableHBLPackages :container="container"/>
    </Tab>
    <AddHBLModal :container="container" :show="showConfirmAddHBLModal" @close="closeModal"/>
</template>

<style scoped>

</style>
