<script setup>
import Breadcrumb from "@/Components/Breadcrumb.vue";
import {ref, watch, computed} from "vue";
import InputError from "@/Components/InputError.vue";
import {router, useForm, usePage} from "@inertiajs/vue3";
import {push} from "notivue";
import AppLayout from "@/Layouts/AppLayout.vue";
import Tab from "primevue/tab";
import TabPanel from "primevue/tabpanel";
import TabShipment from "@/Pages/Common/Dialog/HBL/Tabs/TabShipment.vue";
import Card from "primevue/card";
import TabHBLDetails from "@/Pages/Common/Dialog/HBL/Tabs/TabHBLDetails.vue";
import IftaLabel from "primevue/iftalabel";
import Skeleton from "primevue/skeleton";
import TabStatus from "@/Pages/Common/Dialog/HBL/Tabs/TabStatus.vue";
import Textarea from "primevue/textarea";
import TabDocuments from "@/Pages/Common/Dialog/HBL/Tabs/TabDocuments.vue";
import TabList from "primevue/tablist";
import Tabs from "primevue/tabs";
import TabPanels from "primevue/tabpanels";
import Button from "primevue/button";
import InputNumber from "primevue/inputnumber";
import TabHBLCharge from "@/Pages/Common/Dialog/HBL/Tabs/TabHBLCharge.vue";
import TabPayments from "@/Pages/Common/Dialog/HBL/Tabs/TabPayments.vue";
import Dialog from "primevue/dialog";
import PaymentSummaryCard from "@/Pages/CallCenter/Components/PaymentSummaryCard.vue";
import DemurrageConsentDialog from "@/Pages/Finance/HBL/Partials/DemurrageConsentDialog.vue";

const props = defineProps({
    customerQueue: {
        type: Object,
        default: () => {
        }
    },
    hblId: {
        type: Number,
        default: null
    },
    branch: {
        type: Object,
        default: null
    },
    currencyRate : {
        type: Number,
        default: 1.0
    }
})

const hbl = ref({});
const hblTotalSummary = ref({});
const hblCharges = ref({});
const isLoadingHbl = ref(false);
const paymentRecord = ref([]);
const isLoading = ref(false);
const isPaymentSummaryLoading = ref(false);
const currencyCode = ref(usePage().props.currentBranch.currency_symbol || "SAR");
const showPaymentDialog = ref(false);
const summaryTotalDue = ref(0);
const verificationInfo = ref(null);
const paymentStatus = ref(null);
const isCheckingPaymentStatus = ref(false);
const showDemurrageConsentDialog = ref(false);

// Check if HBL has container reached date
const hasContainerReachedDate = computed(() => {
    if (!hbl.value) return true;

    // Check HBL's containers directly (simpler approach)
    const containers = hbl.value.containers || [];
    if (containers.length === 0) return false;

    // Check if any container has a reached date
    return containers.some(container => container && container.reached_date);
});

const computedOutstanding = computed(() => {
    return (
        parseFloat(summaryTotalDue.value || 0) +
        parseFloat(form.additional_charges || 0) -
        parseFloat(form.discount || 0)
    );
});

// Calculate maximum discount based on demurrage charge and branch maximum discount percentage
const maxDiscountAmount = computed(() => {
    const demurrageCharge = parseFloat(hblCharges.value?.destination_demurrage_charge || 0);
    const maxDiscountPercentage = parseFloat(props.branch?.maximum_demurrage_discount || 0);

    if (demurrageCharge > 0 && maxDiscountPercentage > 0) {
        return (demurrageCharge * maxDiscountPercentage) / 100;
    }
    return 0;
});

// Check if discount should be disabled (when demurrage charge is 0)
const isDiscountDisabled = computed(() => {
    const demurrageCharge = parseFloat(hblCharges.value?.destination_demurrage_charge || 0);
    return demurrageCharge === 0;
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
        } else {
            hblTotalSummary.value = await response.json();
        }
    } catch (error) {
        console.error("Error:", error);
    } finally {
        // isLoading.value = false;
    }
};

const getHBLCharges = async () => {
    try {
        const response = await fetch(`/hbl-charge/${props.hblId}`, {
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

if (props.hblId !== null) {
    fetchHBL();
    getHBLTotalSummary();
    getHBLCharges();
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

const getVerificationInfo = async () => {
    if (!props.hblId) return;

    try {
        const response = await fetch(`/call-center/cashier/verification-info/${props.hblId}`, {
            method: "GET",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": usePage().props.csrf,
            },
        });

        if (response.ok) {
            const data = await response.json();
            // Only set verificationInfo if verified is true
            if (data && data.verified === true) {
                verificationInfo.value = data;
            } else {
                verificationInfo.value = null;
            }
        }
    } catch (error) {
        console.error("Error fetching verification info:", error);
        verificationInfo.value = null;
    }
};

getVerificationInfo();

const getPaymentStatus = async () => {
    if (!props.hblId) return;

    isCheckingPaymentStatus.value = true;
    try {
        const response = await fetch(`/call-center/cashier/payment-status/${props.hblId}`, {
            method: "GET",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": usePage().props.csrf,
            },
        });

        if (response.ok) {
            paymentStatus.value = await response.json();
        }
    } catch (error) {
        console.error("Error fetching payment status:", error);
        paymentStatus.value = null;
    } finally {
        isCheckingPaymentStatus.value = false;
    }
};

getPaymentStatus();

// Computed to check if payment should be disabled
// Use the frontend's computedOutstanding (which includes all charges from PaymentSummaryCard)
// rather than backend's simple HBL calculation
const isPaymentDisabled = computed(() => {
    // Don't determine payment status until PaymentSummaryCard data is loaded
    // This prevents the card from showing/hiding during initial load
    if (isPaymentSummaryLoading.value) {
        return false; // Show loading state, not "Already Paid"
    }

    // Check if there's a verified payment record from backend
    // This indicates payment was already completed and verified
    if (paymentStatus.value?.is_paid) {
        return true;
    }

    // If there's verification info, payment is complete
    if (verificationInfo.value) {
        return true;
    }

    return false;
});

// Computed to check if data is still loading
// Only check PaymentSummaryCard loading since that's what determines the payment status
const isDataLoading = computed(() => {
    return isPaymentSummaryLoading.value;
});

// Computed to check if any action should be disabled
const isAnyActionDisabled = computed(() => {
    return isLoading.value ||
           isLoadingHbl.value ||
           isPaymentSummaryLoading.value ||
           isCheckingPaymentStatus.value ||
           isPaymentDisabled.value;
});

const form = useForm({
    paid_amount: 0,
    customer_queue: props.customerQueue,
    note: '',
    discount: 0,
    additional_charges: 0,
    demurrage_consent_given: false,
    demurrage_consent_note: '',
});

const cashTendered = ref(null);

const balanceAmount = computed(() => {
    if (!cashTendered.value) return 0;
    return parseFloat(cashTendered.value) - parseFloat(form.paid_amount || 0);
});

// Watch cash tendered to update amount if needed, though they are separate
watch(cashTendered, (val) => {
    // Optional: if want to auto-fill amount? No, better keep manual.
});

const handleVerify = () => {
    // Check if payment is already completed
    if (isPaymentDisabled.value) {
        push.error('This HBL has already been fully paid and verified.');
        return;
    }

    // Check if container reached date is missing
    if (!hasContainerReachedDate.value) {
        showDemurrageConsentDialog.value = true;
        return;
    }

    // Proceed with verification
    proceedWithVerification();
};

const handleDemurrageConsent = (consentData) => {
    // Store consent data in form
    form.demurrage_consent_given = consentData.consentGiven;
    form.demurrage_consent_note = consentData.consentNote;

    // Close consent dialog
    showDemurrageConsentDialog.value = false;

    // Proceed with the action that was interrupted
    if (showPaymentDialog.value) {
        proceedWithPayment();
    } else {
        proceedWithVerification();
    }
};

const handleDemurrageConsentCancel = () => {
    showDemurrageConsentDialog.value = false;
    form.demurrage_consent_given = false;
    form.demurrage_consent_note = '';
};

const proceedWithVerification = () => {
    // Check if payment is already completed
    if (isPaymentDisabled.value) {
        push.error('This HBL has already been fully paid and verified.');
        return;
    }

    // For zero payment/verification, we set paid_amount to 0
    form.paid_amount = 0;

    // Submit verification and redirect to queue list
    form.post(route("call-center.cashier.store"), {
        onSuccess: () => {
            router.visit(route("call-center.cashier.queue.list"));
            form.reset();
            push.success('Verified Successfully!');
        },
        onError: (errors) => {
            // Inertia passes validation errors as an object
            console.error('Verification error:', errors);

            // Check if there's a message in the errors
            if (errors.message) {
                push.error(errors.message);
            } else if (typeof errors === 'string') {
                push.error(errors);
            } else if (errors && typeof errors === 'object') {
                // Get the first error message
                const firstError = Object.values(errors)[0];
                if (firstError) {
                    push.error(Array.isArray(firstError) ? firstError[0] : firstError);
                } else {
                    push.error('Something went wrong!');
                }
            } else {
                push.error('Something went wrong!');
            }
        },
        preserveScroll: true,
        preserveState: true,
        data: {
            ...form,
            additional_charges: form.additional_charges,
            discount: form.discount,
        }
    });
};

const downloadInvoice = () => {
    if (props.hblId) {
        window.location.href = route("hbls.getCashierReceipt", {hbl: props.hblId});
    }
};

const streamInvoice = () => {
    if (props.hblId) {
        window.open(route("hbls.streamCashierReceipt", {hbl: props.hblId}), '_blank');
    }
};

const downloadReceipt = () => {
    if (props.hblId) {
        window.location.href = route("hbls.downloadPOSReceipt", {hbl: props.hblId});
    }
};

const streamReceipt = () => {
    if (props.hblId) {
        window.open(route("hbls.streamPOSReceipt", {hbl: props.hblId}), '_blank');
    }
};

const printInvoice = () => {
    if (props.hblId) {
        window.open(route("hbls.getCashierReceipt", {hbl: props.hblId}), '_blank');
    }
};

const handleUpdatePayment = () => {
    // Check if payment is already completed
    if (isPaymentDisabled.value) {
        push.error('This HBL has already been fully paid.');
        return;
    }

    // Check if container reached date is missing
    if (!hasContainerReachedDate.value) {
        showDemurrageConsentDialog.value = true;
        return;
    }

    // Proceed with payment
    proceedWithPayment();
}

const proceedWithPayment = () => {
    // Check if payment is already completed
    if (isPaymentDisabled.value) {
        push.error('This HBL has already been fully paid.');
        return;
    }

    // If it's a verification (zero payment), skip validations
    const isVerification = computedOutstanding.value <= 0;

    if (!isVerification) {
        const outstandingAmount = parseFloat(computedOutstanding.value);
        const paidAmount = parseFloat(form.paid_amount);

        // Round to 2 decimal places for comparison
        const roundedOutstanding = Math.round(outstandingAmount * 100) / 100;
        const roundedPaid = Math.round(paidAmount * 100) / 100;

        // Allow a small tolerance of 0.05 for rounding differences
        // This prevents issues with cent values like 0.01, 0.02, etc.
        const tolerance = 0.05;

        if (roundedPaid < (roundedOutstanding - tolerance)) {
            push.error('Please pay full amount');
            return;
        }
    }

    form.post(route("call-center.cashier.store"), {
        onSuccess: () => {
            // Open receipt in new tab after successful payment
            if (props.hblId && form.paid_amount > 0) {
                window.open(route("hbls.streamPOSReceipt", {hbl: props.hblId}), '_blank');
            }

            // Show success message
            push.success('Payment Update Successfully!');

            // Reset form
            form.reset();

            // Close the payment modal
            showPaymentDialog.value = false;

            // Force a full page reload to refresh all data and prevent duplicate payments
            // Use router.visit to do a fresh Inertia visit
            router.visit(route("call-center.cashier.create", {customer_queue: props.customerQueue.id}), {
                preserveState: false,
                preserveScroll: false,
                replace: true,
            });
        },
        onError: (errors) => {
            // Inertia passes validation errors as an object
            // For exceptions, the message might be in different formats
            console.error('Payment error:', errors);

            // Check if there's a message in the errors
            if (errors.message) {
                push.error(errors.message);
            } else if (typeof errors === 'string') {
                push.error(errors);
            } else if (errors && typeof errors === 'object') {
                // Get the first error message
                const firstError = Object.values(errors)[0];
                if (firstError) {
                    push.error(Array.isArray(firstError) ? firstError[0] : firstError);
                } else {
                    push.error('Something went wrong!');
                }
            } else {
                push.error('Something went wrong!');
            }
        },
        preserveScroll: true,
        preserveState: true,
        data: {
            ...form,
            additional_charges: form.additional_charges,
            discount: form.discount,
        }
    });
}

// Watch for the dialog open to set the default amount
watch(showPaymentDialog, (val) => {
    if (val) {
        // Check if payment is already completed before opening dialog
        if (isPaymentDisabled.value) {
            showPaymentDialog.value = false;
            push.error('This HBL has already been fully paid.');
            return;
        }

        form.paid_amount = parseFloat(computedOutstanding.value.toFixed(2));
        cashTendered.value = null; // Reset cash tendered
    }
});

// Always sync amount with outstanding if dialog is closed? No only initialization.
watch(computedOutstanding, (val) => {
    if (showPaymentDialog.value && val > 0) {
       form.paid_amount = parseFloat(val.toFixed(2));
    }
});
</script>

<template>
    <AppLayout title="Settle Payments">
        <template #header>Settle Payments</template>

        <Breadcrumb/>

        <div class="grid grid-cols-12 gap-5 mt-5">
            <div class="col-span-8">
                <Card>
                    <template #content>
                        <Tabs value="1">
                            <TabList>
                                <Tab value="0">
                                    <a class="flex items-center gap-2 text-inherit">
                                        <i class="pi pi-info-circle"/>
                                        <span>Details</span>
                                    </a>
                                </Tab>
                                <Tab v-if="Object.keys(hbl).length !== 0" value="1">
                                    <a class="flex items-center gap-2 text-inherit">
                                        <i class="pi pi-dollar"/>
                                        <span>Charges</span>
                                    </a>
                                </Tab>
                                <Tab value="2">
                                    <a class="flex items-center gap-2 text-inherit">
                                        <i class="pi pi-wallet" />
                                        <span>Payments</span>
                                    </a>
                                </Tab>
                                <Tab v-if="Object.keys(hbl).length !== 0" value="3">
                                    <a class="flex items-center gap-2 text-inherit">
                                        <i class="pi pi-truck"/>
                                        <span>Shipment</span>
                                    </a>
                                </Tab>
                                <Tab value="4">
                                    <a class="flex items-center gap-2 text-inherit">
                                        <i class="pi pi-chart-bar"/>
                                        <span>Status & Audit</span>
                                    </a>
                                </Tab>
                                <Tab value="5">
                                    <a class="flex items-center gap-2 text-inherit">
                                        <i class="pi pi-file"/>
                                        <span>Documents</span>
                                    </a>
                                </Tab>
                            </TabList>
                            <TabPanels>
                                <TabPanel value="0">
                                    <TabHBLDetails :compact="true" :hbl="hbl" :is-loading="isLoading" />
                                </TabPanel>
                                <TabPanel value="1">
                                    <TabHBLCharge :hbl="hbl"></TabHBLCharge>
                                </TabPanel>
                                <TabPanel value="2">
                                    <TabPayments :hbl="hbl"></TabPayments>
                                </TabPanel>
                                <TabPanel value="3">
                                    <TabShipment v-if="hbl" :hbl="hbl"/>
                                </TabPanel>
                                <TabPanel value="4">
                                    <TabStatus v-if="hbl" :hbl="hbl"/>
                                </TabPanel>
                                <TabPanel value="5">
                                    <TabDocuments v-if="hbl" :hbl-id="hbl.id"/>
                                </TabPanel>
                            </TabPanels>
                        </Tabs>
                    </template>
                </Card>
            </div>

            <div class="col-span-4">
                <!-- Verification Status Card -->
                <Card v-if="verificationInfo" class="mb-4 bg-green-50 border-green-200">
                    <template #content>
                        <div class="flex items-center gap-3">
                            <i class="pi pi-check-circle text-green-600 text-3xl"></i>
                            <div>
                                <h3 class="text-lg font-bold text-green-800">Verified</h3>
                                <p class="text-sm text-green-700">By {{ verificationInfo.verified_by_name }}</p>
                                <p class="text-xs text-green-600">{{ verificationInfo.verified_at }}</p>
                            </div>
                        </div>
                    </template>
                </Card>

                <!-- Payment Completed Status Card - Only show after data is fully loaded -->
                <Card v-if="!isDataLoading && isPaymentDisabled" class="mb-4 bg-blue-50 border-blue-200">
                    <template #content>
                        <div class="space-y-3">
                            <div class="flex items-center gap-3">
                                <i class="pi pi-check-circle text-blue-600 text-3xl"></i>
                                <div>
                                    <h3 class="text-lg font-bold text-blue-800">Payment Completed</h3>
                                    <p class="text-sm text-blue-700">This HBL has been fully paid</p>
                                </div>
                            </div>
                            <div v-if="paymentStatus?.latest_payment" class="border-t border-blue-200 pt-3 space-y-1">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Invoice:</span>
                                    <span class="font-semibold text-gray-800">{{ paymentStatus.latest_payment.invoice_number }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Receipt:</span>
                                    <span class="font-semibold text-gray-800">{{ paymentStatus.latest_payment.receipt_number }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Paid By:</span>
                                    <span class="font-semibold text-gray-800">{{ paymentStatus.latest_payment.verified_by }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Date:</span>
                                    <span class="font-semibold text-gray-800">{{ paymentStatus.latest_payment.created_at }}</span>
                                </div>
                            </div>
                            <div v-else class="border-t border-blue-200 pt-3">
                                <p class="text-sm text-gray-600 italic">Payment was completed outside the cashier system</p>
                            </div>
                        </div>
                    </template>
                </Card>

                <div class="flex flex-col gap-3 mb-4">
                    <!-- Loading Skeleton for buttons while data loads -->
                    <Skeleton v-if="isDataLoading" height="60px" width="100%"></Skeleton>

                    <!-- Verify Button for Zero Outstanding, disabled if already verified or paid -->
                    <Button v-else-if="computedOutstanding <= 0 && !verificationInfo && !isPaymentDisabled"
                            class="p-button-lg p-button-success w-full"
                            icon="pi pi-check-circle"
                            label="Verify & Next"
                            raised
                            size="large"
                            style="min-width: 180px; font-size: 1.25rem;"
                            :loading="form.processing"
                            :disabled="isAnyActionDisabled || form.processing"
                            @click="handleVerify"/>

                    <!-- Pay Now Button for Outstanding > 0, disabled if already paid -->
                    <Button v-else-if="computedOutstanding > 0 && !isPaymentDisabled"
                            class="p-button-lg p-button-primary w-full"
                            icon="pi pi-credit-card"
                            label="Pay Now"
                            raised
                            size="large"
                            style="min-width: 180px; font-size: 1.25rem;"
                            :disabled="isAnyActionDisabled"
                            @click="showPaymentDialog = true"/>

                    <!-- Already Paid Message -->
                    <Button v-else-if="isPaymentDisabled"
                            class="p-button-lg p-button-success w-full"
                            disabled
                            icon="pi pi-check-circle"
                            label="Already Paid"
                            raised
                            size="large"
                            style="min-width: 180px; font-size: 1.25rem;"/>

                    <!-- Print Invoice Button (Always visible if there's any payment history or simply always for re-printing) -->
                    <Button v-if="!isDataLoading"
                            class="p-button-lg p-button-secondary p-button-outlined w-full"
                            icon="pi pi-print"
                            label="Print Invoice"
                            size="large"
                            style="min-width: 180px;"
                            :disabled="isLoading || isLoadingHbl || isPaymentSummaryLoading"
                            @click="printInvoice"/>
                </div>

                <!-- PaymentSummaryCard - always render if hblId exists, it handles its own loading state -->
                <PaymentSummaryCard v-if="props.hblId" :hbl-id="props.hblId" @update:total-due="summaryTotalDue = $event" @update:loading="isPaymentSummaryLoading = $event" />
            </div>
        </div>

        <Dialog v-model:visible="showPaymentDialog" :style="{ width: '500px' }" header="Pay Now" modal>
            <!-- Payment Already Completed Warning -->
            <div v-if="isPaymentDisabled" class="mb-4 p-4 rounded-lg bg-red-50 border border-red-200">
                <div class="flex items-center gap-2">
                    <i class="pi pi-exclamation-triangle text-red-600 text-xl"></i>
                    <div>
                        <p class="font-semibold text-red-800">Payment Already Completed</p>
                        <p class="text-sm text-red-600">This HBL has already been fully paid. No additional payment is required.</p>
                    </div>
                </div>
            </div>

            <div v-else class="grid grid-cols-1 gap-5 mt-3">
                <!-- Outstanding Amount Widget (now inside dialog) -->
                <div v-if="computedOutstanding" class="mb-2 p-4 rounded-xl shadow bg-gradient-to-r from-red-100 to-orange-100 border border-red-200 flex flex-col items-center">
                    <div class="flex items-center gap-2 mb-2">
                        <i class="pi pi-exclamation-circle text-red-600 text-2xl"></i>
                        <span class="font-semibold text-lg text-red-800">Outstanding</span>
                    </div>
                    <div class="text-3xl font-bold text-red-700">
                        LKR {{ parseFloat(computedOutstanding).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}
                    </div>
                </div>
                <div v-show="computedOutstanding > 0">
                    <IftaLabel>
                        <InputNumber v-model="form.paid_amount" :max="computedOutstanding"
                                     :maxFractionDigits="2" :minFractionDigits="2" class="w-full" inputId="paid-amount"
                                     min="0" step="any"
                                     variant="filled"/>
                        <label for="paid-amount">Amount to Pay (LKR)</label>
                    </IftaLabel>
                    <InputError :message="form.errors.paid_amount"/>
                </div>

                <!-- Cash Given Input -->
                <div v-if="computedOutstanding > 0">
                    <IftaLabel>
                        <InputNumber
                            v-model="cashTendered"
                            :maxFractionDigits="2"
                            :minFractionDigits="2"
                            class="w-full"
                            inputId="cash-tendered"
                            min="0"
                            step="any"
                            variant="filled"
                        />
                        <label for="cash-tendered">Cash Given</label>
                    </IftaLabel>
                </div>

                <!-- Additional Charges Field -->
                <div>
                    <IftaLabel>
                        <InputNumber v-model="form.additional_charges" :maxFractionDigits="2" :minFractionDigits="2"
                                     class="w-full" inputId="additional-charges" min="0" step="any"
                                     variant="filled"/>
                        <label for="additional-charges">Additional Charges</label>
                    </IftaLabel>
                    <InputError :message="form.errors.additional_charges"/>
                </div>

                <!-- Discount Field -->
                <div>
                    <IftaLabel>
                        <InputNumber v-model="form.discount" :disabled="isDiscountDisabled" :max="maxDiscountAmount" :maxFractionDigits="2" :minFractionDigits="2"
                                     class="w-full" inputId="discount" min="0" step="any"
                                     variant="filled"/>
                        <label for="discount">
                            <span v-if="isDiscountDisabled">Discount (Not Available)</span>
                            <span v-else>Discount (Max: LKR {{ maxDiscountAmount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }})</span>
                        </label>
                    </IftaLabel>
                    <InputError :message="form.errors.discount"/>
                    <small v-if="isDiscountDisabled" class="text-red-500 mt-1 block">
                        <i class="pi pi-exclamation-triangle mr-1"></i>
                        Discount not available - No demurrage charges found
                    </small>
                    <small v-else-if="maxDiscountAmount > 0" class="text-gray-500 mt-1 block">
                        Based on {{ (props.branch?.maximum_demurrage_discount || 0) }}% of demurrage charge (LKR {{ parseFloat(hblCharges?.destination_demurrage_charge || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }})
                    </small>
                </div>

                <div>
                    <IftaLabel>
                        <Textarea id="description" v-model="form.note" class="w-full" cols="30"
                                  placeholder="Type note here..." rows="5" style="resize: none"
                                  variant="filled"/>
                        <label for="description">Note</label>
                    </IftaLabel>
                    <InputError :message="form.errors.note"/>
                </div>
            </div>
            <template #footer>
                <div class="flex justify-between items-center w-full">
                    <!-- Balance/Change Display -->
                    <div v-if="cashTendered && cashTendered > 0 && !isPaymentDisabled" class="flex items-center gap-2">
                        <span class="text-sm text-gray-600">Balance:</span>
                        <span class="text-lg font-bold" :class="balanceAmount < 0 ? 'text-red-600' : 'text-green-600'">
                            LKR {{ balanceAmount.toFixed(2) }}
                        </span>
                    </div>
                    <div class="flex gap-2 ml-auto">
                        <Button class="p-button-text" icon="pi pi-times" label="Cancel" @click="showPaymentDialog = false" />
                        <Button
                          v-if="!isPaymentDisabled"
                          :class="{ 'opacity-25': form.processing }"
                          :disabled="form.processing || isPaymentDisabled"
                          icon="pi pi-wallet"
                          icon-class="animate-pulse"
                          label="Pay Now"
                          @click="handleUpdatePayment"
                        />
                    </div>
                </div>
            </template>
        </Dialog>

        <!-- Demurrage Consent Dialog -->
        <DemurrageConsentDialog
            :hbls-with-missing-dates="[{
                hbl_number: hbl?.hbl_number || hbl?.hbl,
                hbl_name: hbl?.hbl_name
            }]"
            :visible="showDemurrageConsentDialog"
            @cancel="handleDemurrageConsentCancel"
            @confirm="handleDemurrageConsent"
            @update:visible="showDemurrageConsentDialog = $event"
        />
    </AppLayout>
</template>
