<script setup>
import {usePage} from "@inertiajs/vue3";
import {ref} from "vue";
import Card from "primevue/card";

const props = defineProps({
    hbl: {
        type: Object,
        default: () => ({}),
    },
    hblTotalSummary: {
        type: Object,
        default: () => ({}),
    },
    hblDestinationTotalSummary: {
        type: Object,
        default: () => ({}),
    },
});

const page = usePage();
const isPrepaid = ref(page.props.currentBranch.is_prepaid)
const currencyRate = ref(
    page.props.currentBranchCurrencyRate && page.props.currentBranchCurrencyRate.sl_rate
        ? page.props.currentBranchCurrencyRate.sl_rate
        : 1
)
const currencySymbol = ref(page.props.currentBranch.currency_symbol || '')

const formatCurrency = (amount) => {
    const symbol = isPrepaid.value ? 'LKR' : currencySymbol.value;
    const rate = isPrepaid.value ? 1 : (currencyRate.value);
    return `${symbol} ${new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(amount * rate)}`;
}
</script>

<template>
    <div v-if="Object.keys(hblTotalSummary).length > 0">

        <Card
            class="!bg-white !border !border-neutral-300 !shadow-md !rounded-md"
        >
            <template #content>
                <div class="flex items-center space-x-2">
                    <i class="ti ti-cash text-xl"></i>
                    <p class="text-xl uppercase font-normal">
                        Departure
                    </p>
                </div>

                <div v-if="hblTotalSummary.freight_charge" class="flex justify-between gap-x-6 p-2 hover:bg-gray-100 rounded">
                    <div class="flex min-w-0 gap-x-4">
                        <div class="min-w-0 flex-auto">
                            <p class="text-sm/6 font-semibold text-gray-900">Freight Charges</p>
                            <div class="text-gray-500 text-xs">
                    <span v-for="(charge, index) in hblTotalSummary.freight_charge_operations" :key="index">
                                    {{ charge }} <br>
                    </span>
                            </div>
                        </div>
                    </div>
                    <div class="hidden shrink-0 sm:flex sm:flex-col sm:items-end">
                        <p class="text-sm/6 text-gray-900">{{ currencySymbol }} {{ parseFloat(hblTotalSummary.freight_charge).toFixed(2) }}</p>
                    </div>
                </div>

                <div v-if="hblTotalSummary.destination_charges" class="flex justify-between gap-x-6 p-2 hover:bg-gray-100 rounded">
                    <div class="flex min-w-0 gap-x-4">
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
                        <div v-else class="mt-1 flex items-center gap-x-1.5">
                            <div class="flex-none rounded-full bg-red-500/20 p-1">
                                <div class="size-1.5 rounded-full bg-red-500" />
                            </div>
                            <p class="text-xs/5 text-gray-500">Unpaid</p>
                        </div>
                    </div>
                </div>

                <div v-if="hblTotalSummary.package_charges" class="flex justify-between gap-x-6 p-2 hover:bg-gray-100 rounded">
                    <div class="flex min-w-0 gap-x-4">
                        <div class="min-w-0 flex-auto">
                            <p class="text-sm/6 font-semibold text-gray-900">Package Charges</p>
                        </div>
                    </div>
                    <div class="hidden shrink-0 sm:flex sm:flex-col sm:items-end">
                        <p class="text-sm/6 text-gray-900">{{ currencySymbol }} {{ parseFloat(hblTotalSummary.package_charges).toFixed(2) }}</p>
                    </div>
                </div>

                <div v-if="hblTotalSummary.bill_charge" class="flex justify-between gap-x-6 p-2 hover:bg-gray-100 rounded">
                    <div class="flex min-w-0 gap-x-4">
                        <div class="min-w-0 flex-auto">
                            <p class="text-sm/6 font-semibold text-gray-900">Bill Charges</p>
                        </div>
                    </div>
                    <div class="hidden shrink-0 sm:flex sm:flex-col sm:items-end">
                        <p class="text-sm/6 text-gray-900">{{ currencySymbol }} {{ parseFloat(hblTotalSummary.bill_charge).toFixed(2) }}</p>
                    </div>
                </div>

                <div v-if="hblTotalSummary.additional_charge" class="flex justify-between gap-x-6 p-2 hover:bg-gray-100 rounded">
                    <div class="flex min-w-0 gap-x-4">
                        <div class="min-w-0 flex-auto">
                            <p class="text-sm/6 font-semibold text-gray-900">Additional Charges</p>
                        </div>
                    </div>
                    <div class="hidden shrink-0 sm:flex sm:flex-col sm:items-end">
                        <p class="text-sm/6 text-gray-900">{{ currencySymbol }} {{ parseFloat(hblTotalSummary.additional_charge).toFixed(2) }}</p>
                    </div>
                </div>

            </template>
        </Card>

        <Card
            class="!bg-white !border !border-neutral-300 !shadow-md !rounded-md mt-5"
        >
            <template #content>
                <div class="flex items-center space-x-2">
                    <i class="ti ti-cash text-xl"></i>
                    <p class="text-xl uppercase font-normal">
                        Destination
                    </p>
                </div>

                <div v-if="hblDestinationTotalSummary.handlingCharges" class="flex justify-between gap-x-6 p-2 hover:bg-gray-100 rounded">
                    <div class="flex min-w-0 gap-x-4">
                        <div class="min-w-0 flex-auto">
                            <p class="text-sm/6 font-semibold text-gray-900">Handling Charges</p>
                        </div>
                    </div>
                    <div class="hidden shrink-0 sm:flex sm:flex-col sm:items-end">
                        <p class="text-sm/6 text-gray-900">
                            {{formatCurrency(hblDestinationTotalSummary.handlingCharges)}}
                        </p>
                    </div>
                </div>

                <div v-if="hblDestinationTotalSummary.slpaCharge" class="flex justify-between gap-x-6 p-2 hover:bg-gray-100 rounded">
                    <div class="flex min-w-0 gap-x-4">
                        <div class="min-w-0 flex-auto">
                            <p class="text-sm/6 font-semibold text-gray-900">SLPA Charges</p>
                        </div>
                    </div>
                    <div class="hidden shrink-0 sm:flex sm:flex-col sm:items-end">
                        <p class="text-sm/6 text-gray-900">
                            {{formatCurrency(hblDestinationTotalSummary.slpaCharge)}}
                        </p>
                    </div>
                </div>

                <div v-if="hblDestinationTotalSummary.bondCharge" class="flex justify-between gap-x-6 p-2 hover:bg-gray-100 rounded">
                    <div class="flex min-w-0 gap-x-4">
                        <div class="min-w-0 flex-auto">
                            <p class="text-sm/6 font-semibold text-gray-900">Bond Charges</p>
                        </div>
                    </div>
                    <div class="hidden shrink-0 sm:flex sm:flex-col sm:items-end">
                        <p class="text-sm/6 text-gray-900">
                            {{formatCurrency(hblDestinationTotalSummary.bondCharge)}}
                        </p>
                    </div>
                </div>

                <div v-if="hblDestinationTotalSummary.demurrageCharge" class="flex justify-between gap-x-6 p-2 hover:bg-gray-100 rounded">
                    <div class="flex min-w-0 gap-x-4">
                        <div class="min-w-0 flex-auto">
                            <p class="text-sm/6 font-semibold text-gray-900">Demurrage Charges</p>
                        </div>
                    </div>
                    <div class="hidden shrink-0 sm:flex sm:flex-col sm:items-end">
                        <p class="text-sm/6 text-gray-900">
                            {{formatCurrency(hblDestinationTotalSummary.demurrageCharge)}}
                        </p>
                    </div>
                </div>

                <div v-if="hblDestinationTotalSummary.dOCharge" class="flex justify-between gap-x-6 p-2 hover:bg-gray-100 rounded">
                    <div class="flex min-w-0 gap-x-4">
                        <div class="min-w-0 flex-auto">
                            <p class="text-sm/6 font-semibold text-gray-900">DO Charges</p>
                        </div>
                    </div>
                    <div class="hidden shrink-0 sm:flex sm:flex-col sm:items-end">
                        <p class="text-sm/6 text-gray-900">
                            {{formatCurrency(hblDestinationTotalSummary.dOCharge)}}
                        </p>
                    </div>
                </div>

                <div v-if="hblDestinationTotalSummary.totalAmount" class="flex justify-between gap-x-6 p-2 hover:bg-gray-100 rounded">
                    <div class="flex min-w-0 gap-x-4">
                        <div class="min-w-0 flex-auto">
                            <p class="text-sm/6 font-semibold text-gray-900">Total</p>
                        </div>
                    </div>
                    <div class="hidden shrink-0 sm:flex sm:flex-col sm:items-end">
                        <p class="text-sm/6 text-gray-900">
                            {{formatCurrency(hblDestinationTotalSummary.totalAmount)}}
                        </p>
                    </div>
                </div>
            </template>
        </Card>

        <ul class="divide-y divide-gray-100" role="list">
            <li v-if="hblTotalSummary.vat" class="flex justify-between gap-x-6 p-2 hover:bg-gray-100 rounded">
                <div class="flex min-w-0 gap-x-4">
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
