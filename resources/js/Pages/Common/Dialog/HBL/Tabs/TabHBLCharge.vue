<script setup>
import {ref, watch} from "vue";
import Card from "primevue/card";
import {usePage} from "@inertiajs/vue3";

const props = defineProps({
    hbl: {
        type: Object,
        default: () => ({}),
    }
});

watch(() => props.hbl, (newVal) => {
    if (newVal !== undefined) {
        getHBLChargeDetails(props.hbl)
    }
});

const hblCharges = ref({});
const currencySymbol = ref('LKR');
const isPrepaid = ref(false);
const currencyRate = ref(1);

watch(hblCharges, (newVal) => {
    currencySymbol.value = newVal.base_currency_code;
    isPrepaid.value = newVal.is_branch_prepaid;
    currencyRate.value = newVal.base_currency_rate_in_lkr;
});

const formatCurrency = (amount) => {
    const symbol = isPrepaid.value ? (currencySymbol.value || 'LKR') : 'LKR';
    const rateRaw = isPrepaid.value ? (currencyRate.value || 1) : (currencyRate.value || 1);
    const rate = isPrepaid.value ? (1 / rateRaw) : rateRaw;

    const converted = amount * rate;
    if (isNaN(converted)) return `${symbol} 0.00`;

    return `${symbol} ${new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(converted)}`;
};


const getHBLChargeDetails = async (hbl) => {
    console.log("Fetching HBL charge details for:", hbl.id);
    if (!hbl || !hbl.id) {
        console.error("Invalid HBL object or ID.");
        return;
    }
    try {
        const response = await fetch(`/hbl-charge/${hbl.id}`, {
            method: "GET",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": usePage().props.csrf,
            },
        });

        if (!response.ok) {
            throw new Error("Network response was not ok.");
        } else {
            hblCharges.value = await response.json();
        }
    } catch (error) {
        console.error("Error:", error);
    }
};

</script>

<template>
    <div v-if="hblCharges">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <Card class="!bg-white !border !border-neutral-300 !shadow-md !rounded-md">
                <template #content>
                    <div class="flex items-center space-x-2">
                        <i class="ti ti-cash text-xl"></i>
                        <p class="text-xl uppercase font-normal">
                            Departure
                        </p>
                    </div>

                    <div
                        class="flex justify-between gap-x-6 p-2 hover:bg-gray-100 rounded">
                        <div class="flex min-w-0 gap-x-4">
                            <div class="min-w-0 flex-auto">
                                <p class="text-sm/6 font-semibold text-gray-900">Freight Charges</p>
                                <div class="text-gray-500 text-xs">

                                </div>
                            </div>
                        </div>
                        <div class="hidden shrink-0 sm:flex sm:flex-col sm:items-end">
                            <p class="text-sm/6 text-gray-900">{{ currencySymbol }}
                                {{ parseFloat(hblCharges.freight_charge).toFixed(2) }}</p>
                        </div>
                    </div>

                    <div
                        class="flex justify-between gap-x-6 p-2 hover:bg-gray-100 rounded">
                        <div class="flex min-w-0 gap-x-4">
                            <div class="min-w-0 flex-auto">
                                <p class="text-sm/6 font-semibold text-gray-900">Package Charges</p>
                            </div>
                        </div>
                        <div class="hidden shrink-0 sm:flex sm:flex-col sm:items-end">
                            <p class="text-sm/6 text-gray-900">{{ currencySymbol }}
                                {{ parseFloat(hblCharges.package_charge).toFixed(2) }}</p>
                        </div>
                    </div>

                    <div
                        class="flex justify-between gap-x-6 p-2 hover:bg-gray-100 rounded">
                        <div class="flex min-w-0 gap-x-4">
                            <div class="min-w-0 flex-auto">
                                <p class="text-sm/6 font-semibold text-gray-900">Bill Charges</p>
                            </div>
                        </div>
                        <div class="hidden shrink-0 sm:flex sm:flex-col sm:items-end">
                            <p class="text-sm/6 text-gray-900">{{ currencySymbol }}
                                {{ parseFloat(hblCharges.bill_charge).toFixed(2) }}</p>
                        </div>
                    </div>
                </template>
            </Card>

            <div>
                <Card class="!bg-white !border !border-neutral-300 !shadow-md !rounded-md">
                    <template #content>
                        <div class="flex items-center space-x-2">
                            <i class="ti ti-cash text-xl"></i>
                            <p class="text-xl uppercase font-normal">
                                Destination - I
                            </p>
                        </div>

                        <div
                            class="flex justify-between gap-x-6 p-2 hover:bg-gray-100 rounded">
                            <div class="flex min-w-0 gap-x-4">
                                <div class="min-w-0 flex-auto">
                                    <p class="text-sm/6 font-semibold text-gray-900">Handling Charges</p>
                                </div>
                            </div>
                            <div class="hidden shrink-0 sm:flex sm:flex-col sm:items-end">
                                <p class="text-sm/6 text-gray-900">
                                    {{ formatCurrency(hblCharges.destination_handling_charge) }}
                                </p>
                            </div>
                        </div>

                        <div
                            class="flex justify-between gap-x-6 p-2 hover:bg-gray-100 rounded">
                            <div class="flex min-w-0 gap-x-4">
                                <div class="min-w-0 flex-auto">
                                    <p class="text-sm/6 font-semibold text-gray-900">SLPA Charges</p>
                                </div>
                            </div>
                            <div class="hidden shrink-0 sm:flex sm:flex-col sm:items-end">
                                <p class="text-sm/6 text-gray-900">
                                    {{ formatCurrency(hblCharges.destination_slpa_charge) }}
                                </p>
                            </div>
                        </div>

                        <div
                            class="flex justify-between gap-x-6 p-2 hover:bg-gray-100 rounded">
                            <div class="flex min-w-0 gap-x-4">
                                <div class="min-w-0 flex-auto">
                                    <p class="text-sm/6 font-semibold text-gray-900">Bond Charges</p>
                                </div>
                            </div>
                            <div class="hidden shrink-0 sm:flex sm:flex-col sm:items-end">
                                <p class="text-sm/6 text-gray-900">
                                    {{ formatCurrency(hblCharges.destination_bond_charge) }}
                                </p>
                            </div>
                        </div>

                        <div
                            class="flex justify-between gap-x-6 p-2 hover:bg-gray-100 rounded">
                            <div class="flex min-w-0 gap-x-4">
                                <div class="min-w-0 flex-auto">
                                    <p class="text-sm/6 font-semibold text-gray-900">Total</p>
                                </div>
                            </div>
                            <div class="hidden shrink-0 sm:flex sm:flex-col sm:items-end">
                                <p class="text-sm/6 text-gray-900 font-semibold">
                                    {{ formatCurrency(hblCharges.destination_1_total) }}
                                </p>
                            </div>
                        </div>
                    </template>
                </Card>

                <Card class="!bg-white !border !border-neutral-300 !shadow-md !rounded-md mt-5">
                    <template #content>
                        <div class="flex items-center space-x-2">
                            <i class="ti ti-cash text-xl"></i>
                            <p class="text-xl uppercase font-normal">
                                Destination - II
                            </p>
                        </div>

                        <div class="flex justify-between gap-x-6 p-2 hover:bg-gray-100 rounded">
                            <div class="flex min-w-0 gap-x-4">
                                <div class="min-w-0 flex-auto">
                                    <p class="text-sm/6 font-semibold text-gray-900">Demurrage Charges</p>
                                </div>
                            </div>
                            <div class="hidden shrink-0 sm:flex sm:flex-col sm:items-end">
                                <p class="text-sm/6 text-gray-900">
                                    LKR {{
                                        hblCharges.destination_demurrage_charge ? parseFloat(hblCharges.destination_demurrage_charge).toFixed(2) : '0.00'
                                    }}
                                </p>
                            </div>
                        </div>

                        <div class="flex justify-between gap-x-6 p-2 hover:bg-gray-100 rounded">
                            <div class="flex min-w-0 gap-x-4">
                                <div class="min-w-0 flex-auto">
                                    <p class="text-sm/6 font-semibold text-gray-900">DO Charges</p>
                                </div>
                            </div>
                            <div class="hidden shrink-0 sm:flex sm:flex-col sm:items-end">
                                <p class="text-sm/6 text-gray-900">
                                    LKR {{
                                        hblCharges.destination_do_charge ? parseFloat(hblCharges.destination_do_charge).toFixed(2) : '0.00'
                                    }}
                                </p>
                            </div>
                        </div>

                    </template>
                </Card>
            </div>

            <Card class="!bg-white !border !border-neutral-300 !shadow-md !rounded-md">
                <template #content>
                    <div class="flex items-center space-x-2">
                        <i class="ti ti-cash text-xl"></i>
                        <p class="text-xl uppercase font-normal">
                            Total Charges
                        </p>
                    </div>

                    <div class="flex justify-between gap-x-6 p-2 hover:bg-gray-100 rounded">
                        <div class="flex min-w-0 gap-x-4">
                            <div class="min-w-0 flex-auto">
                                <p class="text-sm/6 font-semibold text-gray-900">Agent Charge</p>
                            </div>
                        </div>
                        <div class="hidden shrink-0 sm:flex sm:flex-col sm:items-end">
                            <p class="text-sm/6 text-gray-900">
                                LKR {{
                                    hblCharges.departure_grand_total ? parseFloat(hblCharges.departure_grand_total).toFixed(2) : '0.00'
                                }}
                            </p>
                        </div>
                    </div>

                    <div class="flex justify-between gap-x-6 p-2 hover:bg-gray-100 rounded">
                        <div class="flex min-w-0 gap-x-4">
                            <div class="min-w-0 flex-auto">
                                <p class="text-sm/6 font-semibold text-gray-900">Destination I Charges</p>
                            </div>
                        </div>
                        <div class="hidden shrink-0 sm:flex sm:flex-col sm:items-end">
                            <p class="text-sm/6 text-gray-900">
                                LKR {{
                                    hblCharges.destination_1_total ? parseFloat(hblCharges.destination_1_total).toFixed(2) : '0.00'
                                }}
                            </p>
                        </div>
                    </div>

                    <div class="flex justify-between gap-x-6 p-2 hover:bg-gray-100 rounded">
                        <div class="flex min-w-0 gap-x-4">
                            <div class="min-w-0 flex-auto">
                                <p class="text-sm/6 font-semibold text-gray-900">Destination II Charges</p>
                            </div>
                        </div>
                        <div class="hidden shrink-0 sm:flex sm:flex-col sm:items-end">
                            <p class="text-sm/6 text-gray-900">
                                LKR {{
                                    hblCharges.destination_2_total ? parseFloat(hblCharges.destination_2_total).toFixed(2) : '0.00'
                                }}
                            </p>
                        </div>
                    </div>

                    <div class="flex justify-between gap-x-6 p-2 hover:bg-gray-100 rounded">
                        <div class="flex min-w-0 gap-x-4">
                            <div class="min-w-0 flex-auto">
                                <p class="text-sm/6 font-semibold text-gray-900">TAX I</p>
                            </div>
                        </div>
                        <div class="hidden shrink-0 sm:flex sm:flex-col sm:items-end">
                            <p class="text-sm/6 text-gray-900">
                                LKR {{
                                    hblCharges.destination_1_tax ? parseFloat(hblCharges.destination_1_tax).toFixed(2) : '0.00'
                                }}
                            </p>
                        </div>
                    </div>

                    <div class="flex justify-between gap-x-6 p-2 hover:bg-gray-100 rounded">
                        <div class="flex min-w-0 gap-x-4">
                            <div class="min-w-0 flex-auto">
                                <p class="text-sm/6 font-semibold text-gray-900">TAX II</p>
                            </div>
                        </div>
                        <div class="hidden shrink-0 sm:flex sm:flex-col sm:items-end">
                            <p class="text-sm/6 text-gray-900">
                                LKR {{
                                    hblCharges.destination_2_tax ? parseFloat(hblCharges.destination_2_tax).toFixed(2) : '0.00'
                                }}
                            </p>
                        </div>
                    </div>

                    <div class="flex justify-between gap-x-6 p-2 hover:bg-gray-100 rounded">
                        <div class="flex min-w-0 gap-x-4">
                            <div class="min-w-0 flex-auto">
                                <p class="text-sm/6 font-semibold text-gray-900">Grand Total</p>
                            </div>
                        </div>
                        <div class="hidden shrink-0 sm:flex sm:flex-col sm:items-end">
                            <p class="text-sm/6 text-gray-900">
                                LKR {{
                                    hblCharges.destination_2_tax ? parseFloat(hblCharges.destination_2_tax).toFixed(2) : '0.00'
                                }}
                            </p>
                        </div>
                    </div>

                </template>
            </Card>
        </div>

        <!--        <ul class="divide-y divide-gray-100" role="list">-->
        <!--            <li v-if="hblTotalSummary.vat" class="flex justify-between gap-x-6 p-2 hover:bg-gray-100 rounded">-->
        <!--                <div class="flex min-w-0 gap-x-4">-->
        <!--                    <div class="min-w-0 flex-auto">-->
        <!--                        <p class="text-sm/6 font-semibold text-gray-900">VAT</p>-->
        <!--                    </div>-->
        <!--                </div>-->
        <!--                <div class="hidden shrink-0 sm:flex sm:flex-col sm:items-end">-->
        <!--                    <p class="text-sm/6 text-gray-900">{{ currencySymbol }}-->
        <!--                        {{ parseFloat(hblTotalSummary.vat).toFixed(2) }}</p>-->
        <!--                </div>-->
        <!--            </li>-->

        <!--            <li v-if="hblTotalSummary.discount" class="flex justify-between gap-x-6 p-2 hover:bg-gray-100 rounded">-->
        <!--                <div class="flex min-w-0 gap-x-4">-->
        <!--                    <div class="min-w-0 flex-auto">-->
        <!--                        <p class="text-sm/6 font-semibold text-gray-900">Discount</p>-->
        <!--                    </div>-->
        <!--                </div>-->
        <!--                <div class="hidden shrink-0 sm:flex sm:flex-col sm:items-end">-->
        <!--                    <p class="text-sm/6 text-gray-900">{{ currencySymbol }}-->
        <!--                        {{ parseFloat(hblTotalSummary.discount).toFixed(2) }}</p>-->
        <!--                </div>-->
        <!--            </li>-->

        <!--            <li class="flex justify-between gap-x-6 p-2 hover:bg-gray-100 rounded">-->
        <!--                <div class="flex min-w-0 gap-x-4">-->
        <!--                    <div class="min-w-0 flex-auto">-->
        <!--                        <p class="text-sm/6 font-semibold text-gray-900">Grand Total</p>-->
        <!--                    </div>-->
        <!--                </div>-->
        <!--                <div class="hidden shrink-0 sm:flex sm:flex-col sm:items-end">-->
        <!--                    <p class="text-lg text-gray-900">{{ currencySymbol }} {{ (hbl?.grand_total ?? 0).toFixed(2) }}</p>-->
        <!--                </div>-->
        <!--            </li>-->

        <!--            <li class="flex justify-between gap-x-6 p-2 hover:bg-gray-100 rounded">-->
        <!--                <div class="flex min-w-0 gap-x-4">-->
        <!--                    <div class="min-w-0 flex-auto">-->
        <!--                        <p class="text-sm/6 font-semibold text-gray-900">Paid Amount</p>-->
        <!--                    </div>-->
        <!--                </div>-->
        <!--                <div class="hidden shrink-0 sm:flex sm:flex-col sm:items-end">-->
        <!--                    <p class="text-lg text-gray-900">{{ currencySymbol }} {{ (hbl?.paid_amount ?? 0).toFixed(2) }}</p>-->
        <!--                </div>-->
        <!--            </li>-->
        <!--        </ul>-->
    </div>
</template>
