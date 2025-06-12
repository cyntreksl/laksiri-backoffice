<script setup>
import InfoDisplay from "@/Pages/Common/Components/InfoDisplay.vue";
import PostSkeleton from "@/Components/PostSkeleton.vue";
import Card from 'primevue/card';

const props = defineProps({
    courier: {
        type: Object,
        default: () => ({}),
    },
    isLoading: {
        type: Boolean,
        required: true,
    },
});

// Helper function to format currency
const formatCurrency = (amount) => {
    if (!amount && amount !== 0) return 'N/A';
    return `$${Number(amount).toFixed(2)}`;
};

// Helper function to calculate discount amount
const getDiscountAmount = () => {
    if (!courier.value?.discount_amount) return 0;
    return Number(courier.value.discount_amount);
};

// Helper function to calculate tax amount
const getTaxAmount = () => {
    if (!courier.value?.tax_amount) return 0;
    return Number(courier.value.tax_amount);
};

// Helper function to get base amount
const getBaseAmount = () => {
    if (!courier.value?.amount) return 0;
    return Number(courier.value.amount);
};
</script>

<template>
    <div class="grid grid-cols-12 gap-6">
        <!-- Payment Summary -->
        <div class="col-span-12 lg:col-span-8">
            <PostSkeleton v-if="isLoading" />

            <Card v-else class="border">
                <template #title>
                    <div class="flex items-center space-x-2">
                        <i class="ti ti-receipt text-green-600"></i>
                        <span>Payment Summary</span>
                    </div>
                </template>
                <template #content>
                    <div class="space-y-6">
                        <!-- Base Amount -->
                        <div class="flex justify-between items-center py-3 border-b border-gray-200">
                            <span class="text-lg font-medium text-gray-700">Base Amount</span>
                            <span class="text-lg font-semibold text-gray-900">{{ formatCurrency(courier?.amount) }}</span>
                        </div>

                        <!-- Discount Section -->
                        <div class="bg-red-50 p-4 rounded-lg border border-red-200">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-lg font-medium text-red-700">Discount</span>
                                <span class="text-lg font-semibold text-red-700">-{{ formatCurrency(courier?.discount_amount) }}</span>
                            </div>
                            <div class="text-sm text-red-600 space-y-1">
                                <div class="flex justify-between">
                                    <span>Method:</span>
                                    <span class="font-medium">{{ courier?.discount_method || 'N/A' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Value:</span>
                                    <span class="font-medium">
                                        {{ courier?.discount_value || 'N/A' }}
                                        {{ courier?.discount_method === 'Percentage' ? '%' : '' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Tax Section -->
                        <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-lg font-medium text-blue-700">Tax</span>
                                <span class="text-lg font-semibold text-blue-700">+{{ formatCurrency(courier?.tax_amount) }}</span>
                            </div>
                            <div class="text-sm text-blue-600 space-y-1">
                                <div class="flex justify-between">
                                    <span>Method:</span>
                                    <span class="font-medium">{{ courier?.tax_method || 'N/A' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Value:</span>
                                    <span class="font-medium">
                                        {{ courier?.tax_value || 'N/A' }}
                                        {{ courier?.tax_method === 'Percentage' ? '%' : '' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Grand Total -->
                        <div class="bg-green-50 p-4 rounded-lg border border-green-200">
                            <div class="flex justify-between items-center">
                                <span class="text-xl font-bold text-green-700">Grand Total</span>
                                <span class="text-2xl font-bold text-green-700">{{ formatCurrency(courier?.grand_total) }}</span>
                            </div>
                        </div>
                    </div>
                </template>
            </Card>
        </div>

        <!-- Payment Details -->
        <div class="col-span-12 lg:col-span-4">
            <PostSkeleton v-if="isLoading" />

            <Card v-else class="border h-full">
                <template #title>
                    <div class="flex items-center space-x-2">
                        <i class="ti ti-info-circle text-blue-600"></i>
                        <span>Payment Details</span>
                    </div>
                </template>
                <template #content>
                    <div class="space-y-4">
                        <div class="text-center p-4 bg-gray-50 rounded-lg">
                            <i class="ti ti-credit-card text-3xl text-gray-400 mb-2"></i>
                            <p class="text-gray-600 text-sm">Payment Status</p>
                            <p class="font-semibold text-lg">
                                {{ courier?.payment_status || 'Pending' }}
                            </p>
                        </div>

                        <div class="grid grid-cols-1 gap-3">
                            <InfoDisplay
                                :value="courier?.created_at ? new Date(courier.created_at).toLocaleDateString() : 'N/A'"
                                label="Created Date"
                            />
                            <InfoDisplay
                                :value="courier?.updated_at ? new Date(courier.updated_at).toLocaleDateString() : 'N/A'"
                                label="Last Updated"
                            />
                        </div>
                    </div>
                </template>
            </Card>
        </div>

        <!-- Calculation Breakdown -->
        <div class="col-span-12">
            <Card class="!bg-slate-50 !border !border-slate-200 !shadow-none">
                <template #title>
                    <div class="flex items-center space-x-2">
                        <i class="ti ti-calculator text-slate-600"></i>
                        <span>Calculation Breakdown</span>
                    </div>
                </template>
                <template #content>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 text-center">
                        <div class="p-4 bg-white rounded-lg border">
                            <i class="ti ti-receipt-2 text-2xl text-gray-600 mb-2"></i>
                            <p class="text-sm text-gray-500">Original Amount</p>
                            <p class="text-lg font-semibold">{{ formatCurrency(courier?.amount) }}</p>
                        </div>

                        <div class="p-4 bg-white rounded-lg border">
                            <i class="ti ti-discount-2 text-2xl text-red-600 mb-2"></i>
                            <p class="text-sm text-gray-500">Total Discount</p>
                            <p class="text-lg font-semibold text-red-600">-{{ formatCurrency(courier?.discount_amount) }}</p>
                        </div>

                        <div class="p-4 bg-white rounded-lg border">
                            <i class="ti ti-tax text-2xl text-blue-600 mb-2"></i>
                            <p class="text-sm text-gray-500">Total Tax</p>
                            <p class="text-lg font-semibold text-blue-600">+{{ formatCurrency(courier?.tax_amount) }}</p>
                        </div>

                        <div class="p-4 bg-green-100 rounded-lg border border-green-300">
                            <i class="ti ti-wallet text-2xl text-green-600 mb-2"></i>
                            <p class="text-sm text-green-600">Final Amount</p>
                            <p class="text-xl font-bold text-green-700">{{ formatCurrency(courier?.grand_total) }}</p>
                        </div>
                    </div>
                </template>
            </Card>
        </div>

        <!-- Empty State -->
        <div v-if="!courier?.amount && !courier?.grand_total" class="col-span-12">
            <Card class="border">
                <template #content>
                    <div class="text-center py-8">
                        <i class="ti ti-receipt-off text-4xl text-gray-400 mb-4"></i>
                        <p class="text-gray-500">No payment information available</p>
                    </div>
                </template>
            </Card>
        </div>
    </div>
</template>
