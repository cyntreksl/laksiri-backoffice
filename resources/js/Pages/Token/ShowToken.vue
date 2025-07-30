<script setup>
import {usePage} from "@inertiajs/vue3";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import {computed, ref} from "vue";
import moment from "moment";
import AppLayout from "@/Layouts/AppLayout.vue";
import Card from 'primevue/card';
import Skeleton from 'primevue/skeleton';
import Avatar from "primevue/avatar";
import Button from "primevue/button";
import InfoDisplay from "@/Pages/Common/Components/InfoDisplay.vue";
import HBLDetailModal from "@/Pages/Common/Dialog/HBL/Index.vue";

const props = defineProps({
    verificationDocuments: {
        type: Array,
        default: () => []
    },
    customerQueue: {
        type: Object,
        default: () => {}
    },
    hblId: {
        type: Number,
        default: null
    },
    token: {
        type: Object,
        default: () => {}
    },
    queueLogs: {
        type: Object,
        default: () => {}
    },
})

const hbl = ref({});
const hblTotalSummary = ref({});
const isLoadingHbl = ref(false);
const paymentRecord = ref([]);
const isLoading = ref(false);
const currencyCode = ref(usePage().props.currentBranch.currency_symbol || "SAR");
const showConfirmViewHBLModal = ref(false);

const tokenTimeline = computed(() => {
    // Create a mapping between queue types and timeline steps
    const queueTypeToStep = {
        'TOKEN_ISSUED': 1,
        'RECEPTION_VERIFICATION_QUEUE': 1,
        'DOCUMENT_VERIFICATION_QUEUE': 2,
        'CASHIER_QUEUE': 3,
        'EXAMINATION_QUEUE': 5
    };

    // Initialize all steps as pending
    const timeline = [
        {
            id: 1,
            label: 'Reception Queue',
            icon: 'pi pi-users',
            description: 'Customer arrival and document verification',
            status: 'pending'
        },
        {
            id: 2,
            label: 'Document Verification Queue',
            icon: 'pi pi-file-check',
            description: 'Document verification and approval',
            status: 'pending'
        },
        {
            id: 3,
            label: 'Cashier Queue',
            icon: 'pi pi-wallet',
            description: 'Payment processing and collection',
            status: 'pending'
        },
        {
            id: 4,
            label: 'Waiting for Package Receive',
            icon: 'pi pi-box',
            description: 'Package collection from bond area',
            status: 'pending'
        },
        {
            id: 5,
            label: 'Examination Queue',
            icon: 'pi pi-search',
            description: 'Gate pass creation and examination',
            status: 'pending'
        },
        {
            id: 6,
            label: 'Done',
            icon: 'pi pi-check-circle',
            description: 'Gate pass released - Process complete',
            status: 'pending'
        }
    ];

    // Process queue logs to update timeline statuses
    props.queueLogs?.forEach(log => {
        const stepId = queueTypeToStep[log.queue_type];
        if (stepId) {
            // Mark step as completed if left_at is not null
            if (log.left_at !== null) {
                timeline[stepId - 1].status = 'completed';
            } else {
                // If left_at is null, it's either current or next
                timeline[stepId - 1].status = 'current';
            }
        }
    });

    // Determine the "next" step (first pending step after the last completed)
    const lastCompletedIndex = timeline.findIndex(step => step.status !== 'completed');
    if (lastCompletedIndex > 0 && lastCompletedIndex < timeline.length) {
        timeline[lastCompletedIndex].status = 'next';
    }

    // Special case for "Done" step - mark as completed if examination is completed
    const examinationCompleted = props.queueLogs?.some(
        log => log.queue_type === 'EXAMINATION_QUEUE' && log.left_at !== null
    );
    if (examinationCompleted) {
        timeline[5].status = 'completed';
    }

    return timeline;
});

const fetchHBL = async () => {
    isLoadingHbl.value = true;

    try {
        const response = await fetch(`/hbls/${props.hblId}`, {
            method: "GET",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": usePage().props.csrf
            },
        });

        if (!response.ok) {
            throw new Error('Network response was not ok.');
        } else {
            const data = await response.json();
            hbl.value = data.hbl;
        }

    } catch (error) {
        console.log(error);
    } finally {
        isLoadingHbl.value = false;
    }
}

const getHBLTotalSummary = async () => {
    try {
        const response = await fetch(`/hbls/get-total-summary/${props.hblId}`, {
            method: "GET",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": usePage().props.csrf,
            },
        });

        if (!response.ok) {
            throw new Error("Network response was not ok.");
        }else{
            hblTotalSummary.value = await response.json();
        }
    } catch (error) {
        console.error("Error:", error);
    }
};

if (props.hblId !== null) {
    fetchHBL();
    getHBLTotalSummary();
}

const getHBLPayments = async () => {
    isLoading.value = true;
    try {
        const response = await fetch(`/call-center/get-hbl-pricing/${props.customerQueue?.token_id}`, {
            method: "GET",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": usePage().props.csrf,
            },
        });

        if (!response.ok) {
            throw new Error("Network response was not ok.");
        }

        paymentRecord.value = await response.json();
    } catch (error) {
        console.error("Error:", error);
    } finally {
        isLoading.value = false;
    }
};

getHBLPayments();

const closeModal = () => {
    showConfirmViewHBLModal.value = false;
};
</script>

<template>
    <AppLayout title="Reception Verification">
        <template #header>Reception Verification</template>

        <Breadcrumb />

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 mt-5">
            <div class="lg:col-span-9">
                <!-- HBL Summary Card -->
                <Card class="mb-5">
                    <template #title>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <Avatar class="bg-blue-100 text-blue-600" icon="ti ti-app-window" size="large" />
                                <div>
                                    <h3 class="text-xl font-semibold text-gray-900">HBL Details</h3>
                                    <p class="text-sm text-gray-600">{{ hbl?.reference || 'N/A' }}</p>
                                </div>
                            </div>
                            <div class="space-x-2">
                                <Button
                                    :href="`/hbls/download/receipt/${hbl?.id}`"
                                    as="a"
                                    icon="pi pi-download"
                                    label="Download Invoice"
                                    severity="info"
                                />
                                <Button
                                    :href="`/hbls/stream/receipt/${hbl?.id}`"
                                    as="a"
                                    icon="pi pi-file-pdf"
                                    label="Stream Invoice"
                                    rel="noopener"
                                    severity="warn"
                                    target="_blank"
                                />
                                <Button icon="pi pi-eye" label="Show HBL Details" @click.prevent="showConfirmViewHBLModal = !showConfirmViewHBLModal"></Button>
                            </div>
                        </div>
                    </template>
                    <template #content>
                        <div class="grid grid-cols-2 gap-4 py-5">
                            <div class="space-y-3">
                                <InfoDisplay :value="hbl?.hbl_number" label="HBL Number"/>
                                <InfoDisplay :value="hbl?.hbl_name" label="Customer Name"/>
                                <InfoDisplay :value="hbl?.consignee_name" label="Consignee Name"/>
                                <InfoDisplay :value="hbl?.contact_number" label="Contact"/>
                            </div>
                            <div class="space-y-3">
                                <InfoDisplay :value="hbl?.cargo_type" label="Cargo Type"/>
                                <InfoDisplay :value="hbl?.hbl_type" label="HBL Type"/>
                                <InfoDisplay :value="hbl?.packages?.length || 0" label="Package Count"/>
                                <InfoDisplay :value="hbl?.status ? hbl.status.toUpperCase() : 'N/A'" label="Status"/>
                            </div>
                        </div>
                    </template>
                </Card>

                <!-- Token Timeline -->
                <Card>
                    <template #title>
                        <div class="flex items-center gap-3">
                            <Avatar class="bg-purple-100 text-purple-600" icon="pi pi-clock" size="large" />
                            <div>
                                <h3 class="text-xl font-semibold text-gray-900">Token Timeline</h3>
                                <p class="text-sm text-gray-600">Process workflow stages</p>
                            </div>
                        </div>
                    </template>
                    <template #content>
                        <div class="space-y-4 my-5">
                            <div
                                v-for="(step, index) in tokenTimeline"
                                :key="step.id"
                                :class="{
                                    'border-green-200 bg-green-50': step.status === 'completed',
                                    'border-blue-200 bg-blue-50': step.status === 'current',
                                    'border-orange-200 bg-orange-50': step.status === 'next',
                                    'border-gray-200 bg-gray-50': step.status === 'pending'
                                }"
                                class="flex items-start gap-4 p-4 rounded-lg border transition-all duration-300"
                            >
                                <div class="flex-shrink-0">
                                    <div
                                        :class="{
                                            'bg-green-500 text-white': step.status === 'completed',
                                            'bg-blue-500 text-white': step.status === 'current',
                                            'bg-orange-500 text-white': step.status === 'next',
                                            'bg-gray-200 text-gray-600': step.status === 'pending'
                                        }"
                                        class="w-10 h-10 rounded-full flex items-center justify-center transition-all duration-300"
                                    >
                                        <i
                                            :class="step.status === 'completed' ? 'pi pi-check' : step.icon"
                                            class="text-sm"
                                        ></i>
                                    </div>
                                </div>
                                <div class="flex-grow">
                                    <div class="flex items-center justify-between">
                                        <h4
                                            :class="{
                                                'text-green-800': step.status === 'completed',
                                                'text-blue-800': step.status === 'current',
                                                'text-orange-800': step.status === 'next',
                                                'text-gray-600': step.status === 'pending'
                                            }"
                                            class="font-semibold transition-all duration-300"
                                        >
                                            {{ step.label }}
                                        </h4>
                                        <div class="flex items-center gap-2">
                                            <span
                                                :class="{
                                                    'bg-green-100 text-green-700': step.status === 'completed',
                                                    'bg-blue-100 text-blue-700': step.status === 'current',
                                                    'bg-orange-100 text-orange-700': step.status === 'next',
                                                    'bg-gray-100 text-gray-600': step.status === 'pending'
                                                }"
                                                class="text-xs px-2 py-1 rounded-full font-medium transition-all duration-300"
                                            >
                                                {{ step.status === 'completed' ? 'Completed' : step.status === 'current' ? 'Current' : step.status === 'next' ? 'Next' : 'Pending' }}
                                            </span>
                                            <span class="text-sm font-medium text-gray-500">Step {{ step.id }}</span>
                                        </div>
                                    </div>
                                    <p
                                        :class="{
                                            'text-green-700': step.status === 'completed',
                                            'text-blue-700': step.status === 'current',
                                            'text-orange-700': step.status === 'next',
                                            'text-gray-600': step.status === 'pending'
                                        }"
                                        class="text-sm mt-1 transition-all duration-300"
                                    >
                                        {{ step.description }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </template>
                </Card>
            </div>

            <div class="lg:col-span-3">
                <Card class="mb-5">
                    <template #content>
                        <div class="flex justify-center items-center text-[185px] font-medium">
                            {{token.token}}
                        </div>
                    </template>
                </Card>

                <Skeleton v-if="isLoading" height="350px" width="100%"></Skeleton>

                <Card v-else class="shadow-lg border-0 overflow-hidden">
                    <template #content>
                        <div v-if="Object.keys(paymentRecord).length > 0" class="space-y-6">
                            <!-- Header -->
                            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 -m-6 mb-6 p-6 border-b border-blue-100">
                                <div class="flex items-center gap-3">
                                    <div class="p-2 bg-blue-100 rounded-lg">
                                        <i class="pi pi-wallet text-blue-600 text-lg"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-900">Payment Summary</h3>
                                        <p class="text-sm text-gray-600">Transaction overview</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Amount Details -->
                            <div class="space-y-4">
                                <!-- Total Amount -->
                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl border border-gray-100">
                                    <div class="flex items-center gap-3">
                                        <div class="p-2 bg-blue-100 rounded-lg">
                                            <i class="pi pi-calculator text-blue-600"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-600">Total Amount</p>
                                            <p class="text-xs text-gray-500">Full invoice total</p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-xl font-bold text-gray-900">{{ currencyCode }} {{ parseFloat(paymentRecord.grand_total).toFixed(2) }}</p>
                                    </div>
                                </div>

                                <!-- Paid Amount -->
                                <div class="flex items-center justify-between p-4 bg-green-50 rounded-xl border border-green-100">
                                    <div class="flex items-center gap-3">
                                        <div class="p-2 bg-green-100 rounded-lg">
                                            <i class="pi pi-check-circle text-green-600"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-600">Paid Amount</p>
                                            <p class="text-xs text-gray-500">Amount received</p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-xl font-bold text-green-700">{{ currencyCode }} {{ parseFloat(hbl.paid_amount).toFixed(2) }}</p>
                                    </div>
                                </div>

                                <!-- Outstanding -->
                                <div :class="(paymentRecord.grand_total - hbl.paid_amount) > 0 ? 'bg-orange-50 border-orange-100' : 'bg-green-50 border-green-100'"
                                     class="flex items-center justify-between p-4 rounded-xl border">
                                    <div class="flex items-center gap-3">
                                        <div :class="(paymentRecord.grand_total - hbl.paid_amount) > 0 ? 'bg-orange-100' : 'bg-green-100'"
                                             class="p-2 rounded-lg">
                                            <i :class="(paymentRecord.grand_total - hbl.paid_amount) > 0 ? 'pi pi-exclamation-triangle text-orange-600' : 'pi pi-check text-green-600'"
                                               class="text-lg"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-600">Outstanding</p>
                                            <p class="text-xs text-gray-500">Remaining balance</p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p :class="(paymentRecord.grand_total - hbl.paid_amount) > 0 ? 'text-orange-700' : 'text-green-700'"
                                           class="text-xl font-bold">
                                            {{ currencyCode }} {{ (paymentRecord.grand_total - hbl.paid_amount).toFixed(2) }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Status and Date -->
                            <div class="border-t border-gray-100 pt-4 space-y-4">
                                <!-- Status -->
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="p-1.5 bg-blue-100 rounded-lg">
                                            <i class="pi pi-info-circle text-blue-600 text-sm"></i>
                                        </div>
                                        <span class="text-sm font-medium text-gray-600">Payment Status</span>
                                    </div>
                                    <div>
                                        <span :class="{
                                                  'bg-green-100 text-green-800': paymentRecord.status === 'Paid' || paymentRecord.status === 'Completed',
                                                  'bg-orange-100 text-orange-800': paymentRecord.status === 'Partial' || paymentRecord.status === 'Pending',
                                                  'bg-red-100 text-red-800': paymentRecord.status === 'Unpaid' || paymentRecord.status === 'Failed',
                                                  'bg-gray-100 text-gray-800': !['Paid', 'Completed', 'Partial', 'Pending', 'Unpaid', 'Failed'].includes(paymentRecord.status)
                                              }"
                                              class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium">
                                            <i :class="{
                                                   'text-green-500': paymentRecord.status === 'Paid' || paymentRecord.status === 'Completed',
                                                   'text-orange-500': paymentRecord.status === 'Partial' || paymentRecord.status === 'Pending',
                                                   'text-red-500': paymentRecord.status === 'Unpaid' || paymentRecord.status === 'Failed',
                                                   'text-gray-500': !['Paid', 'Completed', 'Partial', 'Pending', 'Unpaid', 'Failed'].includes(paymentRecord.status)
                                               }"
                                               class="pi pi-circle-fill text-xs mr-1.5"></i>
                                            {{ paymentRecord.status }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Last Updated -->
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="p-1.5 bg-gray-100 rounded-lg">
                                            <i class="pi pi-clock text-gray-600 text-sm"></i>
                                        </div>
                                        <span class="text-sm font-medium text-gray-600">Last Updated</span>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-semibold text-gray-800">{{ moment(paymentRecord.updated_at).format('MMM DD, YYYY') }}</p>
                                        <p class="text-xs text-gray-500">{{ moment(paymentRecord.updated_at).format('h:mm A') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Empty State -->
                        <div v-else class="text-center py-12">
                            <div class="flex flex-col items-center gap-4">
                                <div class="p-4 bg-gray-100 rounded-full">
                                    <i class="pi pi-wallet text-gray-400 text-3xl"></i>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-700 mb-2">No Payment Records</h3>
                                    <p class="text-sm text-gray-500">Payment information will appear here once available.</p>
                                </div>
                            </div>
                        </div>
                    </template>
                </Card>
            </div>
        </div>
    </AppLayout>

    <HBLDetailModal
        :hbl-id="hbl?.id"
        :show="showConfirmViewHBLModal"
        @close="closeModal"
        @update:show="showConfirmViewHBLModal = $event"
    />
</template>
