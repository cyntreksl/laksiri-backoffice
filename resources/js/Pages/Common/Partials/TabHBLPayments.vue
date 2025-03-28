<script setup>
import InfoDisplay from "@/Pages/Common/Components/InfoDisplay.vue";
import AccordionPanel from "@/Components/AccordionPanel.vue";
import SimpleOverviewWidget from "@/Components/Widgets/SimpleOverviewWidget.vue";
import PostSkeleton from "@/Components/PostSkeleton.vue";
import {watch} from "vue";
import Card from 'primevue/card';
import Avatar from 'primevue/avatar';

const props = defineProps({
    hbl: {
        type: Object,
        default: () => ({}),
    },
    hblTotalSummary: {
        type: Object,
        default: () => ({}),
    },
});
</script>

<template>
    <AccordionPanel show-panel title="Payment Details">
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
        <div v-if="Object.keys(hblTotalSummary).length > 0"
             class="is-scrollbar-hidden min-w-full overflow-x-auto my-5">
            <table class="is-hoverable w-full text-left">
                <thead>
                <tr>
                    <th
                        class="whitespace-nowrap rounded-l-lg bg-slate-200 px-3 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                    >
                        Description
                    </th>
                    <th
                        class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                    >

                    </th>
                    <th
                        class="whitespace-nowrap rounded-r-lg bg-slate-200 px-3 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                    >
                        Amount
                    </th>
                </tr>
                </thead>
                <tbody>
                <tr v-if="hblTotalSummary.freight_charge" >
                    <td class="whitespace-nowrap px-4 py-3 sm:px-5">Freight Charge</td>
                    <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                <span v-for="(charge, index) in hblTotalSummary.freight_charge_operations"
                                      :key="index">
                                    {{ charge }} <br>
                                </span>
                    </td>
                    <td class="whitespace-nowrap px-4 py-3 sm:px-5">{{parseFloat(hblTotalSummary.freight_charge).toFixed(2)}}</td>
                </tr>
                <tr v-if="hblTotalSummary.destination_charges" >
                    <td class="whitespace-nowrap px-4 py-3 sm:px-5">Destination Charge</td>
                    <td class="whitespace-nowrap px-4 py-3 sm:px-5"></td>
                    <td class="whitespace-nowrap px-4 py-3 sm:px-5">{{parseFloat(hblTotalSummary.destination_charges).toFixed(2)}}</td>
                </tr>
                <tr v-if="hblTotalSummary.package_charges" >
                    <td class="whitespace-nowrap px-4 py-3 sm:px-5">Package Charge</td>
                    <td class="whitespace-nowrap px-4 py-3 sm:px-5"></td>
                    <td class="whitespace-nowrap px-4 py-3 sm:px-5">{{parseFloat(hblTotalSummary.package_charges).toFixed(2)}}</td>
                </tr>
                <tr v-if="hblTotalSummary.bill_charge" >
                    <td class="whitespace-nowrap px-4 py-3 sm:px-5">Bill Charge</td>
                    <td class="whitespace-nowrap px-4 py-3 sm:px-5"></td>
                    <td class="whitespace-nowrap px-4 py-3 sm:px-5">{{parseFloat(hblTotalSummary.bill_charge).toFixed(2)}}</td>
                </tr>
                <tr v-if="hblTotalSummary.additional_charge" >
                    <td class="whitespace-nowrap px-4 py-3 sm:px-5">Additional Charge</td>
                    <td class="whitespace-nowrap px-4 py-3 sm:px-5"></td>
                    <td class="whitespace-nowrap px-4 py-3 sm:px-5">{{parseFloat(hblTotalSummary.additional_charge).toFixed(2)}}</td>
                </tr>
                <tr v-if="hblTotalSummary.vat" >
                    <td class="whitespace-nowrap px-4 py-3 sm:px-5">Vat</td>
                    <td class="whitespace-nowrap px-4 py-3 sm:px-5"></td>
                    <td class="whitespace-nowrap px-4 py-3 sm:px-5">{{parseFloat(hblTotalSummary.vat).toFixed(2)}}</td>
                </tr>
                <tr v-if="hblTotalSummary.discount" >
                    <td class="whitespace-nowrap px-4 py-3 sm:px-5">Discount</td>
                    <td class="whitespace-nowrap px-4 py-3 sm:px-5"></td>
                    <td class="whitespace-nowrap px-4 py-3 sm:px-5">-{{parseFloat(hblTotalSummary.discount).toFixed(2)}}</td>
                </tr>
                </tbody>
            </table>
        </div>
        <div class="px-4 py-4 sm:px-5">
            <div class="grid grid-cols-3 gap-x-4 gap-y-8">

                <InfoDisplay :value="(hbl?.grand_total ?? 0).toFixed(2)" label="Grand Total"/>

                <InfoDisplay :value="(hbl?.paid_amount ?? 0).toFixed(2)" label="Paid Amount"/>

            </div>
        </div>
    </AccordionPanel>
</template>
