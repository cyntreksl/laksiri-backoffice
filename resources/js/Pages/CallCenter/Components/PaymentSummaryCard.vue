<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import Skeleton from 'primevue/skeleton';
import Card from 'primevue/card';
import { usePage } from '@inertiajs/vue3';

const props = defineProps({
    hblId: { required: true }
});

const hbl = ref({});
const hblCharges = ref({});
const isLoading = ref(true);
const isChargesLoading = ref(false);
const isPrepaid = computed(() => hblCharges.value?.is_branch_prepaid ?? false);
const baseCurrency = computed(() => hblCharges.value?.base_currency_code || 'LKR');
const baseRate = computed(() => hblCharges.value?.base_currency_rate_in_lkr || 1);

function formatCurrency(amount, symbol = 'LKR') {
    if (amount === null || amount === undefined || isNaN(amount)) return `${symbol} 0.00`;
    return `${symbol} ${parseFloat(amount).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
}

function convertCurrency(amount, isBaseCurrency = false) {
    if (amount === null || amount === undefined || isNaN(amount)) return 0;
    if (isBaseCurrency) {
        return amount;
    } else {
        const rateRaw = baseRate.value || 1;
        const rate = isPrepaid.value ? (1 / rateRaw) : rateRaw;
        return amount * rate;
    }
}

const fetchHBL = async () => {
    isLoading.value = true;
    try {
        const response = await fetch(`/hbls/${props.hblId}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': usePage().props.csrf
            },
        });
        if (!response.ok) throw new Error('Network response was not ok.');
        const data = await response.json();
        hbl.value = data.hbl || {};
    } catch (e) {
        console.error('Error fetching HBL:', e);
        hbl.value = {};
    } finally {
        isLoading.value = false;
    }
};

const fetchHBLCharges = async () => {
    isChargesLoading.value = true;
    try {
        const response = await fetch(`/hbl-charge/${props.hblId}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': usePage().props.csrf,
            },
        });
        if (!response.ok) throw new Error('Network response was not ok.');
        const data = await response.json();
        hblCharges.value = data || {};
    } catch (e) {
        console.error('Error fetching HBL charges:', e);
        hblCharges.value = {};
    } finally {
        isChargesLoading.value = false;
    }
};

onMounted(() => {
    if (props.hblId) {
        fetchHBL();
        fetchHBLCharges();
    }
});

watch(() => props.hblId, (newVal) => {
    if (newVal) {
        fetchHBL();
        fetchHBLCharges();
    }
});

// --- Computed breakdowns ---
const departureCharges = computed(() => {
    if (!hblCharges.value) return 0;
    const freight = Number(hblCharges.value.freight_charge) || 0;
    const packageCharge = Number(hblCharges.value.package_charge) || 0;
    const billCharge = Number(hblCharges.value.bill_charge) || 0;
    return convertCurrency(freight + packageCharge + billCharge, isPrepaid.value);
});

const destinationICharges = computed(() => {
    if (!hblCharges.value) return 0;
    const handling = Number(hblCharges.value.destination_handling_charge) || 0;
    const slpa = Number(hblCharges.value.destination_slpa_charge) || 0;
    const bond = Number(hblCharges.value.destination_bond_charge) || 0;
    const tax1 = Number(hblCharges.value.destination_1_tax) || 0;
    return convertCurrency(handling + slpa + bond + tax1, false);
});

const agentTotal = computed(() => {
    if (!hblCharges.value) return 0;
    const base = (Number(hblCharges.value.freight_charge) || 0) +
        (Number(hblCharges.value.package_charge) || 0) +
        (Number(hblCharges.value.bill_charge) || 0);
    const add = Number(hblCharges.value.additional_charges) || 0;
    const disc = Number(hblCharges.value.discount) || 0;

    if (isPrepaid.value) {
        const dest1 = (Number(hblCharges.value.destination_1_total) || 0) +
            (Number(hblCharges.value.destination_1_tax) || 0);
        return base + add - disc + convertCurrency(dest1, false);
    } else {
        return convertCurrency(base + add - disc, false);
    }
});

const agentPaidAmount = computed(() => {
    if (!hbl.value) return 0;
    return convertCurrency(Number(hbl.value.paid_amount) || 0, isPrepaid.value);
});

const agentDue = computed(() => agentTotal.value - agentPaidAmount.value);
const agentDueLKR = computed(() => isPrepaid.value ? agentDue.value * baseRate.value : agentDue.value);

const destinationIICharges = computed(() => {
    if (!hblCharges.value) return 0;
    const demurrage = Number(hblCharges.value.destination_demurrage_charge) || 0;
    const doCharge = Number(hblCharges.value.destination_do_charge) || 0;
    const tax2 = Number(hblCharges.value.destination_2_tax) || 0;
    return convertCurrency(demurrage + doCharge + tax2, isPrepaid.value);
});

const slPortalTotal = computed(() => {
    if (!hblCharges.value) return 0;
    if (isPrepaid.value) {
        const sum = (Number(hblCharges.value.destination_2_total) || 0) +
            (Number(hblCharges.value.destination_2_tax) || 0);
        return convertCurrency(sum, false);
    } else {
        const sum = (Number(hblCharges.value.destination_1_total) || 0) +
            (Number(hblCharges.value.destination_2_total) || 0) +
            (Number(hblCharges.value.destination_1_tax) || 0) +
            (Number(hblCharges.value.destination_2_tax) || 0);
        return convertCurrency(sum, false);
    }
});

const totalDue = computed(() => agentDueLKR.value + slPortalTotal.value);
</script>

<template>
    <Skeleton v-if="isLoading || isChargesLoading" height="350px" width="100%"></Skeleton>
    <Card v-else class="shadow-xl border-0 overflow-hidden rounded-2xl">
        <template #content>
            <div class="space-y-6">
                <div class="bg-gradient-to-r from-blue-100/80 to-indigo-100/80 -m-6 mb-6 p-6 border-b border-blue-200 rounded-t-2xl">
                    <div class="flex items-center gap-3">
                        <div class="p-3">
                            <i class="pi pi-wallet text-blue-700 text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold text-gray-900 tracking-tight">Payment Summary</h3>
                            <p class="text-base text-gray-600">Transaction overview</p>
                        </div>
                    </div>
                </div>
                <div class="space-y-6">
                    <!-- Agent Section -->
                    <div>
                        <div class="flex items-center mb-4 gap-2">
                            <i class="pi pi-building text-blue-600 text-xl"></i>
                            <span class="font-semibold text-blue-900 text-lg">Agent Charges</span>
                        </div>
                        <div class="divide-y divide-gray-100">
                            <div class="flex justify-between items-center py-2">
                                <span class="text-gray-700">Departure Charges</span>
                                <span class="font-semibold">{{ formatCurrency(departureCharges, baseCurrency) }}</span>
                            </div>
                            <div class="flex justify-between items-center py-2">
                                <span class="text-gray-700">Destination I Charges</span>
                                <span class="font-semibold">{{ formatCurrency(destinationICharges, baseCurrency) }}</span>
                            </div>
                            <div class="flex justify-between items-center py-2">
                                <span class="text-gray-700">Agent Total</span>
                                <span class="text-blue-700 font-bold text-lg">{{ formatCurrency(agentTotal, baseCurrency) }}</span>
                            </div>
                            <div class="flex justify-between items-center py-2">
                                <span class="text-gray-700">Agent Paid Amount</span>
                                <span class="text-green-700 font-bold">{{ formatCurrency(agentPaidAmount, baseCurrency) }}</span>
                            </div>
                            <div class="flex justify-between items-center py-2">
                                <span class="text-gray-700">Agent Due</span>
                                <span class="text-red-700 font-bold">{{ formatCurrency(agentDue, baseCurrency) }}</span>
                            </div>
                            <div class="flex justify-between items-center py-2">
                                <span class="text-gray-700">Agent Due in LKR</span>
                                <span class="text-red-700 font-bold">{{ formatCurrency(agentDueLKR, 'LKR') }}</span>
                            </div>
                        </div>
                    </div>
                    <!-- SL Portal Section -->
                    <div>
                        <div class="flex items-center mb-4 gap-2">
                            <i class="pi pi-globe text-green-600 text-xl"></i>
                            <span class="font-semibold text-green-900 text-lg">SL Portal Charges</span>
                        </div>
                        <div class="divide-y divide-gray-100">
                            <div class="flex justify-between items-center py-2">
                                <span class="text-gray-700">Destination II Charges</span>
                                <span class="font-bold">{{ formatCurrency(destinationIICharges, 'LKR') }}</span>
                            </div>
                            <div class="flex justify-between items-center py-2">
                                <span class="text-gray-700">SL Portal Total</span>
                                <span class="text-green-700 font-bold text-lg">{{ formatCurrency(slPortalTotal, 'LKR') }}</span>
                            </div>
                        </div>
                    </div>
                    <!-- Total Due Section -->
                    <div class="bg-gradient-to-r from-purple-100 to-purple-200 rounded-xl shadow p-5 border border-purple-200 flex items-center justify-between mt-2">
                        <div class="flex items-center gap-3">
                            <i class="pi pi-calculator text-purple-700 text-2xl"></i>
                            <span class="font-semibold text-lg text-gray-900">Total Due</span>
                        </div>
                        <span class="text-purple-700 font-extrabold text-2xl tracking-wide">{{ formatCurrency(totalDue, 'LKR') }}</span>
                    </div>
                </div>
            </div>
        </template>
    </Card>
</template>
