<script setup>
import Tab from "@/Components/Tab.vue";
import InfoDisplay from "@/Pages/Common/Components/InfoDisplay.vue";
import AccordionPanel from "@/Components/AccordionPanel.vue";
import SimpleOverviewWidget from "@/Components/Widgets/SimpleOverviewWidget.vue";
import PostSkeleton from "@/Components/PostSkeleton.vue";
import {computed, ref, watch} from "vue";
import HBLDetailModal from "@/Pages/Common/HBLDetailModal.vue";

const props = defineProps({
    mhbl: {
        type: Object,
        default: () => ({}),
    },
    isLoading: {
        type: Boolean,
        required: true,
    }
});

// Computed property to calculate total volume
const totalVolume = computed(() => {
    if (!props.mhbl?.hbls) return "0.00";

    // Calculate the sum of all package volumes
    const total = props.mhbl.hbls.reduce((totalHBL, hbl) => {
        if (hbl.packages) {
            return totalHBL + hbl.packages.reduce((totalPkg, pkg) => totalPkg + (pkg.volume || 0), 0);
        }
        return totalHBL;
    }, 0);

    return total.toFixed(2);
});

// Computed property to calculate total weight
const totalWeight = computed(() => {
    if (!props.mhbl?.hbls) return "0.00";

    // Calculate the sum of all package volumes
    const total = props.mhbl.hbls.reduce((totalHBL, hbl) => {
        if (hbl.packages) {
            return totalHBL + hbl.packages.reduce((totalPkg, pkg) => totalPkg + (pkg.weight || 0), 0);
        }
        return totalHBL;
    }, 0);

    return total.toFixed(2);
});

const getPackageMeasures = (packages) => {
    const totalVolume = packages.reduce((total, pkg) =>
            total + (pkg.volume ?? 0),
        0);

    const totalWeight = packages.reduce((total, pkg) =>
            total + (pkg.weight ?? 0),
        0);

    return [
        totalVolume,
        totalWeight
    ];
};

const hblId = ref(null);
const showConfirmViewHBLModal = ref(false);
const confirmViewHBL = async (id) => {
    hblId.value = id;
    showConfirmViewHBLModal.value = true;
};

const closeHBLModal = () => {
    showConfirmViewHBLModal.value = false;
    hblId.value = null;
};

</script>

<template>
    <Tab label="Details" name="tabHome">
        <PostSkeleton v-if="isLoading" />

        <AccordionPanel v-else :title="mhbl ? 'MHBL Basic Details' : 'Pickup Details'" show-panel>
            <template #header-image>
                <div
                    class="flex size-8 items-center justify-center rounded-lg p-1 text-primary dark:bg-accent-light/10 dark:text-accent-light">
                    <svg
                        class="icon icon-tabler icons-tabler-outline icon-tabler-file-info w-full h-full"
                        fill="none" stroke="currentColor"
                        stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                        <path d="M14 3v4a1 1 0 0 0 1 1h4"/>
                        <path
                            d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z"/>
                        <path d="M11 14h1v4h1"/>
                        <path d="M12 11h.01"/>
                    </svg>
                </div>
            </template>
            <div class="px-4 py-4 sm:px-5">
                <div v-if="mhbl && Object.keys(mhbl).length > 0" class="grid grid-cols-3 gap-x-4 gap-y-8">
                    <InfoDisplay :value="mhbl.reference" label="Job Ref"/>

                    <InfoDisplay :value="mhbl?.hbl_number" label="MHBL Number"/>

                    <InfoDisplay :value="1245" label="CR Number"/>

                    <InfoDisplay :value="mhbl?.shipper.name" label="Name"/>

                    <InfoDisplay :value="mhbl?.shipper.mobile_number" label="Contact"/>

                    <InfoDisplay :value="mhbl?.shipper.address" label="Address"/>

                    <InfoDisplay :value="mhbl?.shipper.pp_or_nic_no" label="Passport Number"/>

                    <InfoDisplay :value="mhbl?.shipper.iq_number" label="IQ Number"/>

                    <InfoDisplay :value="mhbl?.shipper.location" label="Location"/>

                    <InfoDisplay :value="mhbl?.warehouse.name" label="Warehouse Location"/>

                    <InfoDisplay value="Gift" label="Delivery Type"/>

                    <InfoDisplay :value="mhbl?.cargo_type" label="Cargo Mode"/>
                </div>
            </div>
        </AccordionPanel>

        <PostSkeleton v-if="isLoading" />

        <AccordionPanel v-else show-panel title="Consignee Details">
            <template #header-image>
                <div
                    class="flex size-8 items-center justify-center rounded-lg p-1 text-primary dark:bg-accent-light/10 dark:text-accent-light">
                    <svg
                        class="icon icon-tabler icons-tabler-outline icon-tabler-user-pentagon h-full w-full"
                        fill="none" stroke="currentColor"
                        stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                        <path
                            d="M13.163 2.168l8.021 5.828c.694 .504 .984 1.397 .719 2.212l-3.064 9.43a1.978 1.978 0 0 1 -1.881 1.367h-9.916a1.978 1.978 0 0 1 -1.881 -1.367l-3.064 -9.43a1.978 1.978 0 0 1 .719 -2.212l8.021 -5.828a1.978 1.978 0 0 1 2.326 0z"/>
                        <path d="M12 13a3 3 0 1 0 0 -6a3 3 0 0 0 0 6z"/>
                        <path d="M6 20.703v-.703a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v.707"/>
                    </svg>
                </div>
            </template>
            <div class="px-4 py-4 sm:px-5">
                <div class="grid grid-cols-3 gap-x-4 gap-y-8">

                    <InfoDisplay :value="mhbl?.consignee.name" label="Name"/>

                    <InfoDisplay :value="mhbl?.consignee.address" label="Address"/>

                    <InfoDisplay value="" label="Note"/>

                    <InfoDisplay :value="mhbl?.consignee.mobile_number" label="Contact"/>

                    <InfoDisplay :value="mhbl?.consignee.pp_or_nic_no" label="Nic/PP No"/>

                </div>
            </div>
        </AccordionPanel>

        <PostSkeleton v-if="isLoading" />

        <AccordionPanel v-else show-panel title="HBLs Details">
            <template #header-image>
                <div
                    class="flex size-8 items-center justify-center rounded-lg p-1 text-primary dark:bg-accent-light/10 dark:text-accent-light">
                    <svg class="icon icon-tabler icons-tabler-outline icon-tabler-package w-full h-full"
                         fill="none" stroke="currentColor"
                         stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                         viewBox="0 0 24 24"
                         xmlns="http://www.w3.org/2000/svg">
                        <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                        <path d="M12 3l8 4.5l0 9l-8 4.5l-8 -4.5l0 -9l8 -4.5"/>
                        <path d="M12 12l8 -4.5"/>
                        <path d="M12 12l0 9"/>
                        <path d="M12 12l-8 -4.5"/>
                        <path d="M16 5.25l-8 4.5"/>
                    </svg>
                </div>
            </template>
            <div class="flex gap-3">
                <SimpleOverviewWidget :count="mhbl?.hbls.length ?? 0" title="HBLs">
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

                <SimpleOverviewWidget :count="totalVolume ?? '0.00'" title="Volume">
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

                <SimpleOverviewWidget :count="totalWeight ?? '0.00'" title="Weight">
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
            </div>
            <div v-if="mhbl && mhbl.hbls && mhbl.hbls.length > 0"
                 class="is-scrollbar-hidden min-w-full overflow-x-auto my-10">
                <table class="is-hoverable w-full text-left">
                    <thead>
                        <tr>
                            <th
                                class="whitespace-nowrap rounded-l-lg bg-slate-200 px-3 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                            >
                                HBL Number
                            </th>
                            <th
                                class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                            >
                                HBL Name
                            </th>
                            <th
                                class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                            >
                                Packages Count
                            </th>
                            <th
                                class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                            >
                                Grand Volume
                            </th>
                            <th
                                class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                            >
                                Grand Weight
                            </th>
                            <th
                                class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                            >
                                Cargo Type
                            </th>
                            <th
                                class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                            >
                                HBL Type
                            </th>
                            <th
                                class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                            >
                                Warehouse
                            </th>
                            <th
                                class="whitespace-nowrap rounded-r-lg bg-slate-200 px-3 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                            >
                                #
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="hbl in mhbl.hbls" :key="hbl.id"
                            class="border border-transparent border-b-slate-200 dark:border-b-navy-500">
                            <td class="whitespace-nowrap rounded-l-lg px-4 py-3 sm:px-5">
                                {{ hbl.hbl_number ?? '-' }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 sm:px-5">{{ hbl.hbl_name ?? '-' }}</td>
                            <td class="whitespace-nowrap px-4 py-3 sm:px-5">{{ hbl.packages.length ?? 0 }}</td>
                            <td class="whitespace-nowrap px-4 py-3 sm:px-5">{{ getPackageMeasures(hbl.packages)[0] ?? 0 }}</td>
                            <td class="whitespace-nowrap px-4 py-3 sm:px-5">{{ getPackageMeasures(hbl.packages)[1] ?? 0 }}</td>
                            <td class="whitespace-nowrap px-4 py-3 sm:px-5">{{ hbl.cargo_type ?? '-' }}</td>
                            <td class="whitespace-nowrap px-4 py-3 sm:px-5">{{ hbl.hbl_type ?? '-' }}</td>
                            <td class="whitespace-nowrap px-4 py-3 sm:px-5">{{ hbl.warehouse ?? '-' }}</td>
                            <td class="whitespace-nowrap rounded-r-lg px-4 py-3 sm:px-5">
                              <button
                                  class="btn size-8 p-0 rounded-full text-success hover:bg-success/20 focus:bg-success/20 active:bg-success/25"
                                  x-tooltip.placement.bottom.error="'Show HBL'"
                                  @click.prevent="confirmViewHBL(hbl.id)">
                                <svg  class="size-5 icon icon-tabler icons-tabler-outline icon-tabler-eye"  fill="none"  height="24"  stroke="currentColor"  stroke-linecap="round"  stroke-linejoin="round"  stroke-width="2"  viewBox="0 0 24 24"  width="24"  xmlns="http://www.w3.org/2000/svg"><path d="M0 0h24v24H0z" fill="none" stroke="none"/><path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" /><path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6" /></svg>
                              </button>

                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </AccordionPanel>


    </Tab>
    <HBLDetailModal
        :hbl-id="hblId"
        :show="showConfirmViewHBLModal"
        @close="closeHBLModal"
        @update:show="showConfirmViewHBLModal = $event"
    />
</template>
