<script setup>
import {usePage} from "@inertiajs/vue3";
import {ref} from "vue";

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

const currencySymbol = ref(usePage().props.currentBranch.currency_symbol || '');
</script>

<template>
    <div v-if="Object.keys(hblTotalSummary).length > 0">

        <ul class="divide-y divide-gray-100" role="list">
            <li v-if="hblTotalSummary.freight_charge" class="flex justify-between gap-x-6 p-2 hover:bg-gray-100 rounded">
                <div class="flex min-w-0 gap-x-4">
                    <!--                    <img class="size-12 flex-none rounded-full bg-gray-50" :src="person.imageUrl" alt="" />-->
                    <div class="min-w-0 flex-auto">
                        <p class="text-sm/6 font-semibold text-gray-900">Freight Charges</p>
                        <div class="text-gray-500">
                    <span v-for="(charge, index) in hblTotalSummary.freight_charge_operations" :key="index">
                                    {{ charge }} <br>
                    </span>
                        </div>
                    </div>
                </div>
                <div class="hidden shrink-0 sm:flex sm:flex-col sm:items-end">
                    <p class="text-sm/6 text-gray-900">{{ currencySymbol }} {{ parseFloat(hblTotalSummary.freight_charge).toFixed(2) }}</p>
                </div>
            </li>

            <li v-if="hblTotalSummary.destination_charges" class="flex justify-between gap-x-6 p-2 hover:bg-gray-100 rounded">
                <div class="flex min-w-0 gap-x-4">
<!--                    <img class="size-12 flex-none rounded-full bg-gray-50" :src="person.imageUrl" alt="" />-->
                    <div class="min-w-0 flex-auto">
                        <p class="text-sm/6 font-semibold text-gray-900">Destination Charges</p>
                    </div>
                </div>
                <div class="hidden shrink-0 sm:flex sm:flex-col sm:items-end">
                    <p class="text-sm/6 text-gray-900">{{ currencySymbol }} {{ parseFloat(hblTotalSummary.destination_charges).toFixed(2) }}</p>
                    <div v-if="hbl.is_destination_charges_paid" class="mt-1 flex items-center gap-x-1.5">
                        <div class="flex-none rounded-full bg-emerald-500/20 p-1">
                            <div class="size-1.5 rounded-full bg-emerald-500" />
                        </div>
                        <p class="text-xs/5 text-gray-500">Paid</p>
                    </div>
                </div>
            </li>

            <li v-if="hblTotalSummary.package_charges" class="flex justify-between gap-x-6 p-2 hover:bg-gray-100 rounded">
                <div class="flex min-w-0 gap-x-4">
                    <!--                    <img class="size-12 flex-none rounded-full bg-gray-50" :src="person.imageUrl" alt="" />-->
                    <div class="min-w-0 flex-auto">
                        <p class="text-sm/6 font-semibold text-gray-900">Package Charges</p>
                    </div>
                </div>
                <div class="hidden shrink-0 sm:flex sm:flex-col sm:items-end">
                    <p class="text-sm/6 text-gray-900">{{ currencySymbol }} {{ parseFloat(hblTotalSummary.package_charges).toFixed(2) }}</p>
                </div>
            </li>

            <li v-if="hblTotalSummary.bill_charge" class="flex justify-between gap-x-6 p-2 hover:bg-gray-100 rounded">
                <div class="flex min-w-0 gap-x-4">
                    <!--                    <img class="size-12 flex-none rounded-full bg-gray-50" :src="person.imageUrl" alt="" />-->
                    <div class="min-w-0 flex-auto">
                        <p class="text-sm/6 font-semibold text-gray-900">Bill Charges</p>
                    </div>
                </div>
                <div class="hidden shrink-0 sm:flex sm:flex-col sm:items-end">
                    <p class="text-sm/6 text-gray-900">{{ currencySymbol }} {{ parseFloat(hblTotalSummary.bill_charge).toFixed(2) }}</p>
                </div>
            </li>

            <li v-if="hblTotalSummary.additional_charge" class="flex justify-between gap-x-6 p-2 hover:bg-gray-100 rounded">
                <div class="flex min-w-0 gap-x-4">
                    <!--                    <img class="size-12 flex-none rounded-full bg-gray-50" :src="person.imageUrl" alt="" />-->
                    <div class="min-w-0 flex-auto">
                        <p class="text-sm/6 font-semibold text-gray-900">Additional Charges</p>
                    </div>
                </div>
                <div class="hidden shrink-0 sm:flex sm:flex-col sm:items-end">
                    <p class="text-sm/6 text-gray-900">{{ currencySymbol }} {{ parseFloat(hblTotalSummary.additional_charge).toFixed(2) }}</p>
                </div>
            </li>

            <li v-if="hblTotalSummary.vat" class="flex justify-between gap-x-6 p-2 hover:bg-gray-100 rounded">
                <div class="flex min-w-0 gap-x-4">
                    <!--                    <img class="size-12 flex-none rounded-full bg-gray-50" :src="person.imageUrl" alt="" />-->
                    <div class="min-w-0 flex-auto">
                        <p class="text-sm/6 font-semibold text-gray-900">VAT</p>
                    </div>
                </div>
                <div class="hidden shrink-0 sm:flex sm:flex-col sm:items-end">
                    <p class="text-sm/6 text-gray-900">{{ currencySymbol }} {{ parseFloat(hblTotalSummary.vat).toFixed(2) }}</p>
                </div>
            </li>

            <li v-if="hblTotalSummary.discount" class="flex justify-between gap-x-6 p-2 hover:bg-gray-100 rounded">
                <div class="flex min-w-0 gap-x-4">
                    <!--                    <img class="size-12 flex-none rounded-full bg-gray-50" :src="person.imageUrl" alt="" />-->
                    <div class="min-w-0 flex-auto">
                        <p class="text-sm/6 font-semibold text-gray-900">Discount</p>
                    </div>
                </div>
                <div class="hidden shrink-0 sm:flex sm:flex-col sm:items-end">
                    <p class="text-sm/6 text-gray-900">{{ currencySymbol }} {{ parseFloat(hblTotalSummary.discount).toFixed(2) }}</p>
                </div>
            </li>

            <li class="flex justify-between gap-x-6 p-2 hover:bg-gray-100 rounded">
                <div class="flex min-w-0 gap-x-4">
                    <!--                    <img class="size-12 flex-none rounded-full bg-gray-50" :src="person.imageUrl" alt="" />-->
                    <div class="min-w-0 flex-auto">
                        <p class="text-sm/6 font-semibold text-gray-900">Grand Total</p>
                    </div>
                </div>
                <div class="hidden shrink-0 sm:flex sm:flex-col sm:items-end">
                    <p class="text-lg text-gray-900">{{ currencySymbol }} {{ (hbl?.grand_total ?? 0).toFixed(2) }}</p>
                </div>
            </li>

            <li class="flex justify-between gap-x-6 p-2 hover:bg-gray-100 rounded">
                <div class="flex min-w-0 gap-x-4">
                    <!--                    <img class="size-12 flex-none rounded-full bg-gray-50" :src="person.imageUrl" alt="" />-->
                    <div class="min-w-0 flex-auto">
                        <p class="text-sm/6 font-semibold text-gray-900">Paid Amount</p>
                    </div>
                </div>
                <div class="hidden shrink-0 sm:flex sm:flex-col sm:items-end">
                    <p class="text-lg text-gray-900">{{ currencySymbol }} {{ (hbl?.paid_amount ?? 0).toFixed(2) }}</p>
                </div>
            </li>
        </ul>
    </div>
</template>
