<script setup>
import Tab from "@/Components/Tab.vue";
import InfoDisplay from "@/Pages/Common/Components/InfoDisplay.vue";
import AccordionPanel from "@/Components/AccordionPanel.vue";
import SimpleOverviewWidget from "@/Components/Widgets/SimpleOverviewWidget.vue";
import PostSkeleton from "@/Components/PostSkeleton.vue";

const props = defineProps({
    hbl: {
        type: Object,
        default: () => {
        },
    },
    pickup: {
        type: Object,
        default: () => {
        },
    },
    isLoading: {
        type: Boolean,
        required: true,
    }
});
</script>

<template>
    <Tab label="Details" name="tabHome">
        <PostSkeleton v-if="isLoading" />

        <AccordionPanel v-else :title="hbl ? 'HBL Basic Details' : 'Pickup Details'" show-panel>
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
                <div v-if="hbl" class="grid grid-cols-3 gap-x-4 gap-y-8">
                    <InfoDisplay :value="hbl?.reference" label="Job Ref"/>

                    <InfoDisplay :value="hbl?.hbl" label="HBL"/>

                    <InfoDisplay :value="hbl?.cr" label="CR"/>

                    <InfoDisplay :value="hbl?.hbl_name" label="Name"/>

                    <InfoDisplay :value="hbl?.contact_number" label="Contact"/>

                    <InfoDisplay :value="hbl?.address" label="Address"/>

                    <InfoDisplay :value="hbl?.nic" label="Passport Number"/>

                    <InfoDisplay :value="hbl?.iq_number" label="IQ Number"/>

                    <InfoDisplay :value="hbl?.location" label="Location"/>

                    <InfoDisplay :value="hbl?.warehouse" label="Warehouse Location"/>

                    <InfoDisplay :value="hbl?.hbl_type" label="Delivery Type"/>

                    <InfoDisplay :value="hbl?.cargo_type" label="Cargo Mode"/>
                </div>

                <div v-else class="grid grid-cols-3 gap-x-4 gap-y-8">
                    <InfoDisplay :value="pickup?.reference" label="Job Ref"/>

                    <InfoDisplay :value="pickup?.name" label="Name"/>

                    <InfoDisplay :value="pickup?.email" label="Email"/>

                    <InfoDisplay :value="pickup?.contact_number" label="Contact"/>

                    <InfoDisplay :value="pickup?.address" label="Address"/>

                    <InfoDisplay :value="pickup?.notes" label="Notes"/>

                    <InfoDisplay :value="pickup?.cargo_type" label="Cargo Mode"/>

                    <InfoDisplay :value="pickup?.pickup_date" label="Pickup Date"/>

                    <InfoDisplay :value="pickup?.pickup_time_start" label="Pickup Time Start"/>

                    <InfoDisplay :value="pickup?.pickup_time_end" label="Pickup Time End"/>

                    <InfoDisplay :value="pickup?.pickup_type" label="Pickup Type"/>
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

                    <InfoDisplay :value="hbl?.consignee_name" label="Name"/>

                    <InfoDisplay :value="hbl?.consignee_address" label="Address"/>

                    <InfoDisplay :value="hbl?.consignee_note" label="Note"/>

                    <InfoDisplay :value="hbl?.consignee_contact" label="Contact"/>

                    <InfoDisplay :value="hbl?.consignee_nic" label="Nic/PP No"/>

                </div>
            </div>
        </AccordionPanel>

        <PostSkeleton v-if="isLoading" />

        <AccordionPanel v-else show-panel title="Package Details">
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
                <SimpleOverviewWidget :count="hbl?.packages_count ?? 0" title="Packages">
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

                <SimpleOverviewWidget :count="hbl?.packages_sum_volume ?? 0" title="Volume">
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

                <SimpleOverviewWidget :count="hbl?.packages_sum_weight ?? 0" title="Weight">
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

            <div v-if="hbl?.packages.length > 0"
                 class="is-scrollbar-hidden min-w-full overflow-x-auto my-10">
                <table class="is-hoverable w-full text-left">
                    <thead>
                    <tr>
                        <th
                            class="whitespace-nowrap rounded-l-lg bg-slate-200 px-3 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                        >
                            Type
                        </th>
                        <th
                            class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                        >
                            Length (CM)
                        </th>
                        <th
                            class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                        >
                            Width
                        </th>
                        <th
                            class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                        >
                            Height
                        </th>
                        <th
                            class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                        >
                            Quantity
                        </th>
                        <th
                            class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                        >
                            Weight
                        </th>
                        <th
                            class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                        >
                            Volume (M.CU)
                        </th>
                        <th
                            class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                        >
                            Remarks
                        </th>
                        <th
                            class="whitespace-nowrap rounded-r-lg bg-slate-200 px-3 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                        >
                            Loaded
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="item in hbl.packages" :key="item.id"
                        class="border border-transparent border-b-slate-200 dark:border-b-navy-500">
                        <td class="whitespace-nowrap rounded-l-lg px-4 py-3 sm:px-5">
                            {{ item.package_type ?? '-' }}
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 sm:px-5">{{ item.length ?? 0 }}</td>
                        <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                            {{ item.width ?? 0 }}
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 sm:px-5">{{ item.height ?? 0 }}</td>
                        <td class="whitespace-nowrap px-4 py-3 sm:px-5">{{ item.quantity ?? 0 }}</td>
                        <td class="whitespace-nowrap px-4 py-3 sm:px-5">{{ item.weight ?? 0 }}</td>
                        <td class="whitespace-nowrap px-4 py-3 sm:px-5">{{ item.volume ?? 0 }}</td>
                        <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                            {{ item.remarks ?? '-' }}
                        </td>
                        <td class="whitespace-nowrap rounded-r-lg px-4 py-3 sm:px-5">
                            <svg v-if="item.is_loaded" class="size-6 text-success" fill="currentColor"
                                 viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path clip-rule="evenodd"
                                      d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12Zm13.36-1.814a.75.75 0 1 0-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 0 0-1.06 1.06l2.25 2.25a.75.75 0 0 0 1.14-.094l3.75-5.25Z"
                                      fill-rule="evenodd"/>
                            </svg>

                            <svg v-else class="size-6 text-error" fill="currentColor" viewBox="0 0 24 24"
                                 xmlns="http://www.w3.org/2000/svg">
                                <path clip-rule="evenodd"
                                      d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25Zm-1.72 6.97a.75.75 0 1 0-1.06 1.06L10.94 12l-1.72 1.72a.75.75 0 1 0 1.06 1.06L12 13.06l1.72 1.72a.75.75 0 1 0 1.06-1.06L13.06 12l1.72-1.72a.75.75 0 1 0-1.06-1.06L12 10.94l-1.72-1.72Z"
                                      fill-rule="evenodd"/>
                            </svg>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </AccordionPanel>

        <PostSkeleton v-if="isLoading" />

        <AccordionPanel v-else show-panel title="Payment Details">
            <template #header-image>
                <div
                    class="flex size-8 items-center justify-center rounded-lg p-1 text-primary dark:bg-accent-light/10 dark:text-accent-light">
                    <svg class="w-full h-full" fill="none" stroke="currentColor"
                         stroke-width="1.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z"
                            stroke-linecap="round"
                            stroke-linejoin="round"/>
                    </svg>
                </div>
            </template>
            <div class="px-4 py-4 sm:px-5">
                <div class="grid grid-cols-3 gap-x-4 gap-y-8">

                    <InfoDisplay :value="hbl?.grand_total.toFixed(2) ?? 0.00" label="Grand Total"/>

                    <InfoDisplay :value="hbl?.paid_amount.toFixed(2) ?? 0.00" label="Paid Amount"/>

                </div>
            </div>
        </AccordionPanel>
    </Tab>
</template>
