<script setup>
import {ref, watch, computed} from "vue";
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

const formatCurrency = (amount, isBaseCurrency = false) => {
    if (isBaseCurrency) {
        // For freight, package, bill charges - always use base currency without conversion
        const symbol = currencySymbol.value || 'LKR';
        const converted = amount;
        if (isNaN(converted)) return `${symbol} 0.00`;

        return `${symbol} ${new Intl.NumberFormat('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(converted)}`;
    } else {
        // For destination charges - apply conversion based on prepaid status
        const symbol = isPrepaid.value ? (currencySymbol.value || 'LKR') : 'LKR';
        const rateRaw = isPrepaid.value ? (currencyRate.value || 1) : (currencyRate.value || 1);
        const rate = isPrepaid.value ? (1 / rateRaw) : rateRaw;

        const converted = amount * rate;
        if (isNaN(converted)) return `${symbol} 0.00`;

        return `${symbol} ${new Intl.NumberFormat('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(converted)}`;
    }
};

// Computed properties for charge breakdowns
const agentTotal = computed(() => {
    if (!hblCharges.value) return 0;

    // Base currency charges (no conversion needed)
    const baseCharges = (hblCharges.value.freight_charge || 0) +
                       (hblCharges.value.package_charge || 0) +
                       (hblCharges.value.bill_charge || 0);

    // Additional charges and discount (in base currency)
    const additionalCharges = hblCharges.value.additional_charges || 0;
    const discount = hblCharges.value.discount || 0;

    if (isPrepaid.value) {
        // Prepaid: base charges + additional charges - discount + destination1 + destination1_tax (with conversion)
        const rateRaw = currencyRate.value || 1;
        const rate = 1 / rateRaw;
        const destinationCharges = ((hblCharges.value.destination_1_total || 0) +
                                   (hblCharges.value.destination_1_tax || 0)) * rate;
        return baseCharges + additionalCharges - discount + destinationCharges;
    } else {
        // Non-prepaid: base charges + additional charges - discount
        return baseCharges + additionalCharges - discount;
    }
});

const slPortalCharge = computed(() => {
    if (!hblCharges.value) return 0;

    const rateRaw = currencyRate.value || 1;
    const rate = isPrepaid.value ? (1 / rateRaw) : rateRaw;

    if (isPrepaid.value) {
        // Prepaid: destination2 + destination2_tax (with conversion)
        return ((hblCharges.value.destination_2_total || 0) +
                (hblCharges.value.destination_2_tax || 0)) * rate;
    } else {
        // Non-prepaid: destination1 + destination2 + destination1_tax + destination2_tax (with conversion)
        return ((hblCharges.value.destination_1_total || 0) +
                (hblCharges.value.destination_2_total || 0) +
                (hblCharges.value.destination_1_tax || 0) +
                (hblCharges.value.destination_2_tax || 0)) * rate;
    }
});

const grandTotal = computed(() => {
    return agentTotal.value + slPortalCharge.value;
});

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
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Agent Total Section -->
            <Card class="!bg-white !border !border-neutral-300 !shadow-md !rounded-md">
                <template #content>
                    <div class="flex items-center space-x-2 mb-4">
                        <i class="ti ti-building text-xl text-blue-600"></i>
                        <p class="text-xl font-semibold text-gray-900">
                            Agent Total
                        </p>
                    </div>

                    <!-- Departure Charges -->
                    <div class="mb-4">
                        <h4 class="text-sm font-semibold text-gray-700 mb-2 flex items-center">
                            <i class="ti ti-plane-departure text-sm mr-2"></i>
                            Departure Charges
                        </h4>
                        <div class="space-y-2 ml-4">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Freight Charge</span>
                                <span class="font-medium">{{ formatCurrency(hblCharges.freight_charge || 0, true) }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Package Charge</span>
                                <span class="font-medium">{{ formatCurrency(hblCharges.package_charge || 0, true) }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Bill Charge</span>
                                <span class="font-medium">{{ formatCurrency(hblCharges.bill_charge || 0, true) }}</span>
                            </div>
                            <div v-if="hblCharges.additional_charges > 0" class="flex justify-between text-sm">
                                <span class="text-gray-600">Additional Charges</span>
                                <span class="font-medium text-green-600">{{ formatCurrency(hblCharges.additional_charges || 0, true) }}</span>
                            </div>
                            <div v-if="hblCharges.discount > 0" class="flex justify-between text-sm">
                                <span class="text-gray-600">Discount</span>
                                <span class="font-medium text-red-600">-{{ formatCurrency(hblCharges.discount || 0, true) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Destination I Charges (only for prepaid) -->
                    <div v-if="isPrepaid" class="mb-4">
                        <h4 class="text-sm font-semibold text-gray-700 mb-2 flex items-center">
                            <i class="ti ti-anchor text-sm mr-2"></i>
                            Destination I Charges
                        </h4>
                        <div class="space-y-2 ml-4">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Handling Charge</span>
                                <span class="font-medium">{{ formatCurrency(hblCharges.destination_handling_charge || 0, false) }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">SLPA Charge</span>
                                <span class="font-medium">{{ formatCurrency(hblCharges.destination_slpa_charge || 0, false) }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Bond Charge</span>
                                <span class="font-medium">{{ formatCurrency(hblCharges.destination_bond_charge || 0, false) }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Tax I</span>
                                <span class="font-medium">{{ formatCurrency(hblCharges.destination_1_tax || 0, false) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Agent Total Summary -->
                    <div class="border-t pt-3 mt-4">
                        <div class="flex justify-between items-center">
                            <span class="text-lg font-semibold text-gray-900">Agent Total</span>
                            <span class="text-lg font-bold text-blue-600">{{ formatCurrency(agentTotal) }}</span>
                        </div>
                    </div>
                </template>
            </Card>

            <!-- SL Portal Charges Section -->
            <Card class="!bg-white !border !border-neutral-300 !shadow-md !rounded-md">
                <template #content>
                    <div class="flex items-center space-x-2 mb-4">
                        <i class="ti ti-world text-xl text-green-600"></i>
                        <p class="text-xl font-semibold text-gray-900">
                            SL Portal Charges
                        </p>
                    </div>

                    <!-- Destination I Charges (only for non-prepaid) -->
                    <div v-if="!isPrepaid" class="mb-4">
                        <h4 class="text-sm font-semibold text-gray-700 mb-2 flex items-center">
                            <i class="ti ti-anchor text-sm mr-2"></i>
                            Destination I Charges
                        </h4>
                        <div class="space-y-2 ml-4">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Handling Charge</span>
                                <span class="font-medium">{{ formatCurrency(hblCharges.destination_handling_charge || 0, false) }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">SLPA Charge</span>
                                <span class="font-medium">{{ formatCurrency(hblCharges.destination_slpa_charge || 0, false) }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Bond Charge</span>
                                <span class="font-medium">{{ formatCurrency(hblCharges.destination_bond_charge || 0, false) }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Tax I</span>
                                <span class="font-medium">{{ formatCurrency(hblCharges.destination_1_tax || 0, false) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Destination II Charges -->
                    <div class="mb-4">
                        <h4 class="text-sm font-semibold text-gray-700 mb-2 flex items-center">
                            <i class="ti ti-truck text-sm mr-2"></i>
                            Destination II Charges
                        </h4>
                        <div class="space-y-2 ml-4">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Demurrage Charge</span>
                                <span class="font-medium">{{ formatCurrency(hblCharges.destination_demurrage_charge || 0, false) }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">DO Charge</span>
                                <span class="font-medium">{{ formatCurrency(hblCharges.destination_do_charge || 0, false) }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Tax II</span>
                                <span class="font-medium">{{ formatCurrency(hblCharges.destination_2_tax || 0, false) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- SL Portal Total Summary -->
                    <div class="border-t pt-3 mt-4">
                        <div class="flex justify-between items-center">
                            <span class="text-lg font-semibold text-gray-900">SL Portal Total</span>
                            <span class="text-lg font-bold text-green-600">{{ formatCurrency(slPortalCharge) }}</span>
                        </div>
                    </div>
                </template>
            </Card>
        </div>

        <!-- Grand Total Section -->
        <Card class="!bg-white !border !border-neutral-300 !shadow-md !rounded-md mt-6">
            <template #content>
                <div class="flex justify-between items-center">
                    <div class="flex items-center space-x-2">
                        <i class="ti ti-calculator text-xl text-purple-600"></i>
                        <span class="text-xl font-semibold text-gray-900">Grand Total</span>
                    </div>
                    <span class="text-2xl font-bold text-purple-600">{{ formatCurrency(grandTotal) }}</span>
                </div>

                <!-- Breakdown Summary -->
                <div class="mt-4 pt-4 border-t border-gray-200">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Agent Total:</span>
                            <span class="font-medium">{{ formatCurrency(agentTotal) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">SL Portal Total:</span>
                            <span class="font-medium">{{ formatCurrency(slPortalCharge) }}</span>
                        </div>
                    </div>
                </div>
            </template>
        </Card>
    </div>
</template>
