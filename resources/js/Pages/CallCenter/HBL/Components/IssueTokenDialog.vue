<script setup>
import {ref, computed, watch} from "vue";
import {useForm, usePage} from "@inertiajs/vue3";
import {push} from "notivue";
import {useConfirm} from "primevue/useconfirm";
import Dialog from "primevue/dialog";
import Card from "primevue/card";
import Button from "primevue/button";
import Checkbox from "primevue/checkbox";
import Textarea from "primevue/textarea";
import IftaLabel from "primevue/iftalabel";
import InputError from "@/Components/InputError.vue";
import Divider from "primevue/divider";
import Avatar from "primevue/avatar";
import Tag from "primevue/tag";
import TokenSuccessModal from "./TokenSuccessModal.vue";
import PaymentSummaryCard from "@/Pages/CallCenter/Components/PaymentSummaryCard.vue";
import DemurrageConsentDialog from "@/Pages/Finance/HBL/Partials/DemurrageConsentDialog.vue";

const props = defineProps({
    visible: {
        type: Boolean,
        default: false
    },
    hbl: {
        type: Object,
        default: () => ({})
    }
});

const emit = defineEmits(['update:visible', 'token-issued']);

const confirm = useConfirm();
const isProcessing = ref(false);
const showSuccessModal = ref(false);
const tokenData = ref({});
const currencyCode = ref(usePage().props.currentBranch.currency_symbol || "SAR");
const showDemurrageConsentDialog = ref(false);
const pendingTokenIssuance = ref(null);

const verificationDocuments = computed(() => {
    if (!props.hbl || !props.hbl.hbl_type) return [];

    let docs = ['Passport', 'NIC', 'HBL Receipt'];
    if (props.hbl.hbl_type === 'UPB') {
        return docs.filter(doc => doc !== 'NIC');
    } else {
        return docs.filter(doc => doc !== 'Passport');
    }
});

const form = useForm({
    hbl_id: null,
    is_checked: {},
    note: '',
    demurrage_consent_given: false,
    demurrage_consent_note: ''
});

const paymentStatus = computed(() => {
    if (!props.hbl?.grand_total || props.hbl?.paid_amount === undefined) return 'Pending';

    const total = parseFloat(props.hbl.grand_total);
    const paid = parseFloat(props.hbl.paid_amount);

    if (paid >= total) return 'Paid';
    if (paid > 0) return 'Partial';
    return 'Unpaid';
});

// Check if ALL required documents are verified
const areAllDocumentsVerified = computed(() => {
    if (verificationDocuments.value.length === 0) return false;

    return verificationDocuments.value.every(doc => form.is_checked[doc] === true);
});

// Check if any documents are verified (but not necessarily all)
const isAnyDocumentVerified = computed(() => {
    return Object.keys(form.is_checked).length > 0 && Object.values(form.is_checked).some(checked => checked);
});

const tokenTimeline = computed(() => {


    return [
        {
            id: 1,
            label: 'Reception Queue',
            icon: 'pi pi-users',
            description: 'Customer arrival and document verification',
            status: 'completed', // Reception is always considered done when issuing token
            skipped: false
        },
        {
            id: 2,
            label: 'Document Verification Queue',
            icon: 'pi pi-file-check',
            description: 'Document verification and approval',
            status: 'next', // Always next after reception
            skipped: false
        },
        {
            id: 3,
            label: 'Cashier Queue',
            icon: 'pi pi-wallet',
            description: 'Payment processing and collection',
            status: 'pending',
            skipped: false
        },
        {
            id: 4,
            label: 'Waiting for Package Receive',
            icon: 'pi pi-box',
            description: 'Package collection from bond area',
            status: 'pending',
            skipped: false
        },
        {
            id: 5,
            label: 'Examination Queue',
            icon: 'pi pi-search',
            description: 'Gate pass creation and examination',
            status: 'pending',
            skipped: false
        },
        {
            id: 6,
            label: 'Done',
            icon: 'pi pi-check-circle',
            description: 'Gate pass released - Process complete',
            status: 'pending',
            skipped: false
        }
    ];
});

const updateChecked = (doc, isChecked) => {
    form.is_checked = { ...form.is_checked, [doc]: isChecked };
};

// Check if HBL has container reached date
const hasContainerReachedDate = computed(() => {
    if (!props.hbl) return true;

    // Check if HBL has packages with containers that have reached dates
    const packages = props.hbl.packages || [];
    if (packages.length === 0) return true;

    // Check if any package has a container with a reached date
    const hasReachedDate = packages.some(pkg => {
        const container = pkg.container;
        return container && container.reached_date;
    });

    return hasReachedDate;
});

const handleIssueToken = () => {
    // Check if container reached date is missing
    if (!hasContainerReachedDate.value) {
        // Show demurrage consent dialog
        showDemurrageConsentDialog.value = true;
        return;
    }

    // Proceed with normal token issuance
    proceedWithTokenIssuance();
};

const handleDemurrageConsent = (consentData) => {
    // Store consent data in form
    form.demurrage_consent_given = consentData.consentGiven;
    form.demurrage_consent_note = consentData.consentNote;

    // Close consent dialog
    showDemurrageConsentDialog.value = false;

    // Proceed with token issuance
    proceedWithTokenIssuance();
};

const handleDemurrageConsentCancel = () => {
    showDemurrageConsentDialog.value = false;
    form.demurrage_consent_given = false;
    form.demurrage_consent_note = '';
};

const proceedWithTokenIssuance = () => {
    const message = areAllDocumentsVerified.value
        ? 'Are you sure you want to issue token for this HBL? All required documents have been verified.'
        : 'Are you sure you want to issue token for this HBL?';

    confirm.require({
        message: message,
        header: 'Issue Token',
        icon: 'pi pi-info-circle',
        rejectLabel: 'Cancel',
        rejectProps: {
            label: 'Cancel',
            severity: 'secondary',
            outlined: true
        },
        acceptProps: {
            label: 'Issue Token',
            severity: 'success'
        },
        accept: () => {
            isProcessing.value = true;

            // Set default note if empty
            if (!form.note) {
                form.note = areAllDocumentsVerified.value
                    ? 'Reception verification completed - All documents verified'
                    : 'Token issued - Reception verification pending';
            }

            // Ensure is_checked is always an object, even if empty
            const requestData = {
                is_checked: Object.keys(form.is_checked).length > 0 ? form.is_checked : {},
                note: form.note || '',
                demurrage_consent_given: form.demurrage_consent_given,
                demurrage_consent_note: form.demurrage_consent_note
            };

            // Use fetch for direct JSON response handling
            fetch(route('call-center.hbls.create-token-with-verification', props.hbl.id), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': usePage().props.csrf,
                    'X-Inertia': 'true',
                    'Accept': 'application/json',
                },
                body: JSON.stringify(requestData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Store token data for success modal
                    tokenData.value = data;

                    // Show appropriate success message based on queue placement
                    let queueMessage = '';

                    if (data.token.all_documents_verified) {
                        queueMessage = 'Token issued and placed in Document Verification Queue - All documents verified!';
                    } else {
                        queueMessage = 'Token issued and placed in Document Verification Queue - Please complete document verification.';
                    }

                    push.success(queueMessage);

                    // Reset the form and timeline
                    resetForm();

                    // Close main dialog
                    closeDialog();

                    // Show success modal
                    showSuccessModal.value = true;
                } else {
                    throw new Error(data.message || 'Failed to create token');
                }
            })
            .catch(error => {
                console.error("Error issuing token:", error);
                push.error('Failed to issue token. Please try again.');
            })
            .finally(() => {
                isProcessing.value = false;
            });
        }
    });
};

const resetForm = () => {
    // Reset form data
    form.reset();
    form.clearErrors();

    // Clear all checked documents
    form.is_checked = {};

    // Clear note
    form.note = '';

    // Clear consent data
    form.demurrage_consent_given = false;
    form.demurrage_consent_note = '';
};

const closeDialog = () => {
    emit('update:visible', false);
    resetForm();
};

const handleSuccessModalClose = () => {
    showSuccessModal.value = false;

    // Now emit event to parent to refresh table data
    emit('token-issued', tokenData.value);

    // Clear token data
    tokenData.value = {};
};

// Watch for dialog visibility changes
watch(() => props.visible, (newVal) => {
    if (newVal && props.hbl?.id) {
        form.hbl_id = props.hbl.id;
    }
});
</script>

<template>
    <Dialog
        :visible="visible"
        :style="{ width: '90vw', maxWidth: '1200px' }"
        header="Issue Token"
        modal
        class="p-fluid"
        @update:visible="emit('update:visible', $event)"
    >
               <div class="grid grid-cols-12 gap-5">
            <!-- Left Panel - HBL Details & Timeline -->
            <div class="col-span-8">
                <!-- HBL Summary Card -->
                <Card class="mb-5">
                    <template #title>
                        <div class="flex items-center gap-3">
                            <Avatar icon="pi pi-file-o" class="bg-blue-100 text-blue-600" size="large" />
                            <div>
                                <h3 class="text-xl font-semibold text-gray-900">HBL Details</h3>
                                <p class="text-sm text-gray-600">{{ props.hbl?.reference || 'N/A' }}</p>
                            </div>
                        </div>
                    </template>
                    <template #content>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-3">
                                <div class="flex justify-between">
                                    <span class="font-medium text-gray-600">HBL Number:</span>
                                    <span class="font-semibold">{{ props.hbl?.hbl_number || 'N/A' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="font-medium text-gray-600">Customer Name:</span>
                                    <span class="font-semibold">{{ props.hbl?.hbl_name || 'N/A' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="font-medium text-gray-600">Consignee:</span>
                                    <span class="font-semibold">{{ props.hbl?.consignee_name || 'N/A' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="font-medium text-gray-600">Contact:</span>
                                    <span class="font-semibold">{{ props.hbl?.contact_number || 'N/A' }}</span>
                                </div>
                            </div>
                            <div class="space-y-3">
                                <div class="flex justify-between">
                                    <span class="font-medium text-gray-600">Cargo Type:</span>
                                    <Tag :value="props.hbl?.cargo_type || 'N/A'" severity="info" />
                                </div>
                                <div class="flex justify-between">
                                    <span class="font-medium text-gray-600">HBL Type:</span>
                                    <Tag :value="props.hbl?.hbl_type || 'N/A'" severity="warning" />
                                </div>
                                <div class="flex justify-between">
                                    <span class="font-medium text-gray-600">Package Count:</span>
                                    <span class="font-semibold">{{ props.hbl?.packages?.length || 0 }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="font-medium text-gray-600">Status:</span>
                                    <Tag :value="props.hbl?.status || 'N/A'" severity="success" />
                                </div>
                            </div>
                        </div>
                    </template>
                </Card>

                <!-- Token Timeline -->
                <Card>
                    <template #title>
                        <div class="flex items-center gap-3">
                            <Avatar icon="pi pi-clock" class="bg-purple-100 text-purple-600" size="large" />
                            <div>
                                <h3 class="text-xl font-semibold text-gray-900">Token Timeline</h3>
                                <p class="text-sm text-gray-600">Process workflow stages</p>
                            </div>
                        </div>
                    </template>
                    <template #content>
                        <div class="space-y-4">
                            <div
                                v-for="(step, index) in tokenTimeline"
                                :key="step.id"
                                class="flex items-start gap-4 p-4 rounded-lg border transition-all duration-300"
                                :class="{
                                    'border-green-200 bg-green-50': step.status === 'completed',
                                    'border-blue-200 bg-blue-50': step.status === 'current',
                                    'border-orange-200 bg-orange-50': step.status === 'next',
                                    'border-purple-200 bg-purple-50': step.status === 'skipped',
                                    'border-gray-200 bg-gray-50': step.status === 'pending'
                                }"
                            >
                                <div class="flex-shrink-0">
                                    <div
                                        class="w-10 h-10 rounded-full flex items-center justify-center transition-all duration-300"
                                        :class="{
                                            'bg-green-500 text-white': step.status === 'completed',
                                            'bg-blue-500 text-white': step.status === 'current',
                                            'bg-orange-500 text-white': step.status === 'next',
                                            'bg-purple-500 text-white': step.status === 'skipped',
                                            'bg-gray-200 text-gray-600': step.status === 'pending'
                                        }"
                                    >
                                        <i
                                            :class="step.status === 'completed' ? 'pi pi-check' : step.status === 'skipped' ? 'pi pi-forward' : step.icon"
                                            class="text-sm"
                                        ></i>
                                    </div>
                                </div>
                                <div class="flex-grow">
                                    <div class="flex items-center justify-between">
                                        <h4
                                            class="font-semibold transition-all duration-300"
                                            :class="{
                                                'text-green-800': step.status === 'completed',
                                                'text-blue-800': step.status === 'current',
                                                'text-orange-800': step.status === 'next',
                                                'text-purple-800': step.status === 'skipped',
                                                'text-gray-600': step.status === 'pending'
                                            }"
                                        >
                                            {{ step.label }}
                                            <span v-if="step.skipped" class="text-xs text-purple-600 ml-2">(Skipped)</span>
                                        </h4>
                                        <div class="flex items-center gap-2">
                                            <span
                                                class="text-xs px-2 py-1 rounded-full font-medium transition-all duration-300"
                                                :class="{
                                                    'bg-green-100 text-green-700': step.status === 'completed',
                                                    'bg-blue-100 text-blue-700': step.status === 'current',
                                                    'bg-orange-100 text-orange-700': step.status === 'next',
                                                    'bg-purple-100 text-purple-700': step.status === 'skipped',
                                                    'bg-gray-100 text-gray-600': step.status === 'pending'
                                                }"
                                            >
                                                {{ step.status === 'completed' ? 'Completed' : step.status === 'current' ? 'Current' : step.status === 'next' ? 'Next' : step.status === 'skipped' ? 'Skipped' : 'Pending' }}
                                            </span>
                                            <span class="text-sm font-medium text-gray-500">Step {{ step.id }}</span>
                                        </div>
                                    </div>
                                    <p
                                        class="text-sm mt-1 transition-all duration-300"
                                        :class="{
                                            'text-green-700': step.status === 'completed',
                                            'text-blue-700': step.status === 'current',
                                            'text-orange-700': step.status === 'next',
                                            'text-gray-600': step.status === 'pending'
                                        }"
                                    >
                                        {{ step.description }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </template>
                </Card>
            </div>

            <!-- Right Panel - Payment Summary & Reception Verification -->
            <div class="col-span-4">
                <!-- Payment Summary -->
                <PaymentSummaryCard v-if="props.hbl?.id" :hbl-id="props.hbl.id" />

                <!-- Reception Verification -->
                <Card>
                    <template #title>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <Avatar
                                    icon="pi pi-check-square"
                                    :class="areAllDocumentsVerified ? 'bg-green-100 text-green-600' : 'bg-orange-100 text-orange-600'"
                                    size="large"
                                />
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">Reception Verification</h3>
                                    <p class="text-sm text-gray-600">Verify customer brought required documents</p>
                                </div>
                            </div>
                            <div v-if="areAllDocumentsVerified" class="flex items-center gap-2">
                                <i class="pi pi-check-circle text-green-600"></i>
                                <span class="text-sm font-medium text-green-600">All Verified</span>
                            </div>
                            <div v-else-if="isAnyDocumentVerified" class="flex items-center gap-2">
                                <i class="pi pi-clock text-orange-600"></i>
                                <span class="text-sm font-medium text-orange-600">Partial</span>
                            </div>
                            <div v-else class="flex items-center gap-2">
                                <i class="pi pi-times-circle text-red-600"></i>
                                <span class="text-sm font-medium text-red-600">Pending</span>
                            </div>
                        </div>
                    </template>
                    <template #content>
                        <div class="space-y-4">
                            <!-- Verification Progress -->
                            <div class="bg-gray-50 p-3 rounded-lg">
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-sm font-medium text-gray-600">Verification Progress</span>
                                    <span class="text-sm font-medium"
                                          :class="areAllDocumentsVerified ? 'text-green-600' : 'text-orange-600'">
                                        {{ Object.values(form.is_checked).filter(Boolean).length }} / {{ verificationDocuments.length }}
                                    </span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="h-2 rounded-full transition-all duration-300"
                                         :class="areAllDocumentsVerified ? 'bg-green-500' : 'bg-orange-500'"
                                         :style="{ width: `${(Object.values(form.is_checked).filter(Boolean).length / verificationDocuments.length) * 100}%` }">
                                    </div>
                                </div>
                            </div>

                            <!-- Document Checklist -->
                            <div class="space-y-3">
                                <div v-for="(doc, index) in verificationDocuments" :key="index"
                                     class="flex items-center gap-3 p-3 border rounded-lg transition-all duration-200"
                                     :class="form.is_checked[doc] ? 'border-green-200 bg-green-50' : 'border-gray-200 bg-white'">
                                    <Checkbox
                                        :checked="form.is_checked[doc] || false"
                                        :input-id="`${doc}-${index}`"
                                        :value="doc"
                                        @change="(event) => updateChecked(doc, event.target.checked)"
                                    />
                                    <label :for="`${doc}-${index}`" class="cursor-pointer font-medium flex-grow"
                                           :class="form.is_checked[doc] ? 'text-green-700' : 'text-gray-700'">
                                        {{ doc }}
                                    </label>
                                    <i v-if="form.is_checked[doc]" class="pi pi-check text-green-600"></i>
                                </div>
                            </div>

                            <Divider />

                            <!-- Note Section -->
                            <div>
                                <IftaLabel>
                                    <Textarea
                                        id="reception-note"
                                        v-model="form.note"
                                        class="w-full"
                                        rows="4"
                                        placeholder="Add reception verification notes..."
                                        style="resize: none"
                                    />
                                    <label for="reception-note">Reception Notes</label>
                                </IftaLabel>
                                <InputError :message="form.errors.note" />
                            </div>
                        </div>
                    </template>
                </Card>
            </div>
        </div>

                <template #footer>
            <div class="flex justify-between items-center pt-5">
                <div v-if="!areAllDocumentsVerified" class="flex items-center gap-2 text-blue-600 mr-2">
                    <i class="pi pi-info-circle"></i>
                    <span class="text-sm">Document verification checklist helps track customer requirements</span>
                </div>
                <div v-else class="flex items-center gap-2 text-green-600 mr-2">
                    <i class="pi pi-check-circle"></i>
                    <span class="text-sm">All documents Checked - Customer is ready for next queue</span>
                </div>

                <div class="flex gap-3">
                    <Button
                        label="Cancel"
                        severity="secondary"
                        outlined
                        @click="closeDialog"
                        :disabled="isProcessing"
                    />
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
                    <Button
                        label="Issue Token"
                        icon="pi pi-tag"
                        :loading="isProcessing"
                        :disabled="isProcessing"
                        @click="handleIssueToken"
                    />
                </div>
            </div>
        </template>
    </Dialog>

    <!-- Token Success Modal -->
    <TokenSuccessModal
        :visible="showSuccessModal"
        :token-data="tokenData"
        @update:visible="showSuccessModal = $event"
        @close="handleSuccessModalClose"
    />

    <!-- Demurrage Consent Dialog -->
    <DemurrageConsentDialog
        :hbls-with-missing-dates="[{
            hbl_number: props.hbl?.hbl_number || props.hbl?.hbl,
            hbl_name: props.hbl?.hbl_name
        }]"
        :visible="showDemurrageConsentDialog"
        @cancel="handleDemurrageConsentCancel"
        @confirm="handleDemurrageConsent"
        @update:visible="showDemurrageConsentDialog = $event"
    />
</template>
