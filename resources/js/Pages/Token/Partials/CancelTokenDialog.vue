<script setup>
import { computed, ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';
import Textarea from 'primevue/textarea';
import InputError from '@/Components/InputError.vue';
import { push } from 'notivue';
import axios from 'axios';
import Message from 'primevue/message';
import Tag from 'primevue/tag';

const props = defineProps({
    visible: {
        type: Boolean,
        default: false
    },
    token: {
        type: Object,
        default: () => null
    }
});

const emit = defineEmits(['update:visible', 'token-cancelled']);

const dialogVisible = computed({
    get: () => props.visible,
    set: (value) => emit('update:visible', value)
});

// Form handling with Inertia.js
const form = useForm({
    cancellation_reason: '',
    acknowledged_warnings: false
});

// Eligibility and warnings state
const eligibilityData = ref(null);
const loadingEligibility = ref(false);
const showConfirmation = ref(false);

// Local validation errors
const errors = ref({
    cancellation_reason: ''
});

// Reset form when dialog opens/closes
watch(dialogVisible, (newValue) => {
    if (newValue && props.token) {
        form.reset();
        errors.value = { cancellation_reason: '' };
        showConfirmation.value = false;
        checkEligibility();
    } else {
        eligibilityData.value = null;
    }
});

// Check eligibility when dialog opens
const checkEligibility = async () => {
    if (!props.token) return;

    loadingEligibility.value = true;
    try {
        const response = await axios.get(route('call-center.tokens.cancellation-eligibility', props.token.id));
        eligibilityData.value = response.data;
    } catch (error) {
        console.error('Error checking eligibility:', error);
        push.error('Failed to check token eligibility');
        dialogVisible.value = false;
    } finally {
        loadingEligibility.value = false;
    }
};

// Validate form fields
const validateForm = () => {
    errors.value = { cancellation_reason: '' };
    let isValid = true;

    // Validate cancellation reason
    if (!form.cancellation_reason.trim()) {
        errors.value.cancellation_reason = 'Cancellation reason is required';
        isValid = false;
    } else if (form.cancellation_reason.trim().length < 10) {
        errors.value.cancellation_reason = 'Cancellation reason must be at least 10 characters';
        isValid = false;
    } else if (form.cancellation_reason.trim().length > 500) {
        errors.value.cancellation_reason = 'Cancellation reason must not exceed 500 characters';
        isValid = false;
    }

    return isValid;
};

// Handle initial submission - show confirmation
const handleSubmit = () => {
    if (!validateForm()) {
        return;
    }

    // If there are warnings, require acknowledgment
    if (eligibilityData.value?.warnings?.length > 0 && !form.acknowledged_warnings) {
        showConfirmation.value = true;
        return;
    }

    // Proceed with cancellation
    confirmCancellation();
};

// Confirm and execute cancellation
const confirmCancellation = () => {
    if (!validateForm()) {
        return;
    }

    form.post(route('call-center.tokens.cancel', props.token.id), {
        onSuccess: (page) => {
            push.success('Token cancelled successfully');
            dialogVisible.value = false;
            emit('token-cancelled', props.token);
        },
        onError: (serverErrors) => {
            // Handle server-side validation errors
            if (serverErrors.cancellation_reason) {
                errors.value.cancellation_reason = serverErrors.cancellation_reason;
            }

            // Show general error notification
            const errorMessage = serverErrors.message || 'Failed to cancel token';
            push.error(errorMessage);
        }
    });
};

// Cancel dialog
const cancelDialog = () => {
    dialogVisible.value = false;
    showConfirmation.value = false;
};

// Go back from confirmation to edit
const goBackToEdit = () => {
    showConfirmation.value = false;
};

// Acknowledge warnings and show confirmation
const acknowledgeWarnings = () => {
    form.acknowledged_warnings = true;
    showConfirmation.value = true;
};
</script>

<template>
    <Dialog
        v-model:visible="dialogVisible"
        :closable="!form.processing"
        :style="{ width: '35rem' }"
        header="Cancel Token"
        modal
    >
        <!-- Loading state -->
        <div v-if="loadingEligibility" class="text-center py-8">
            <i class="pi pi-spin pi-spinner text-4xl text-blue-500"></i>
            <p class="mt-4 text-gray-600">Checking eligibility...</p>
        </div>

        <!-- Eligibility check failed or not eligible -->
        <div v-else-if="eligibilityData && !eligibilityData.eligible" class="py-4">
            <Message :closable="false" severity="error">
                <div class="font-semibold">Cannot Cancel Token</div>
                <div class="mt-2">{{ eligibilityData.reason }}</div>
            </Message>

            <div class="flex justify-end gap-2 mt-6">
                <Button
                    label="Close"
                    severity="secondary"
                    @click="cancelDialog"
                />
            </div>
        </div>

        <!-- Main form - not in confirmation mode -->
        <div v-else-if="!showConfirmation && eligibilityData">
            <div class="mb-6">
                <Message :closable="false" class="mb-4" severity="warn">
                    <div class="font-semibold">Warning: This action cannot be undone</div>
                </Message>

                <!-- Token Information -->
                <div class="bg-gray-50 dark:bg-gray-900 p-4 rounded-lg mb-4">
                    <h4 class="font-semibold text-gray-700 dark:text-gray-300 mb-3">Token Information</h4>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Token Number:</span>
                            <span class="font-semibold">{{ token.token }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">HBL Reference:</span>
                            <span class="font-semibold">{{ token.hbl?.reference || 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Status:</span>
                            <Tag :value="eligibilityData.token_status" severity="info" />
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Package Count:</span>
                            <span class="font-semibold">{{ token.package_count }}</span>
                        </div>
                    </div>
                </div>

                <!-- Warnings if packages are outside bond area -->
                <div v-if="eligibilityData.warnings && eligibilityData.warnings.length > 0" class="mb-4">
                    <Message :closable="false" severity="warn">
                        <div class="font-semibold mb-2">Package Location Warnings:</div>
                        <ul class="list-disc list-inside space-y-1">
                            <li v-for="(warning, index) in eligibilityData.warnings" :key="index" class="text-sm">
                                {{ warning }}
                            </li>
                        </ul>
                        <div class="mt-3 text-sm font-semibold">
                            Packages must be returned to the bond area.
                        </div>
                    </Message>
                </div>

                <!-- Cancellation Reason Input -->
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2" for="cancellation_reason">
                        Cancellation Reason <span class="text-red-500">*</span>
                    </label>
                    <Textarea
                        id="cancellation_reason"
                        v-model="form.cancellation_reason"
                        :class="{ 'p-invalid': errors.cancellation_reason || form.errors.cancellation_reason }"
                        :disabled="form.processing"
                        class="w-full"
                        placeholder="Please provide a detailed reason for cancelling this token (minimum 10 characters)"
                        rows="4"
                    />
                    <small class="text-gray-500 block mt-1">
                        {{ form.cancellation_reason.length }} / 500 characters
                    </small>
                    <InputError :message="errors.cancellation_reason || form.errors.cancellation_reason" class="mt-1" />
                </div>

                <!-- Consequences -->
                <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4 mb-4">
                    <h4 class="font-semibold text-yellow-800 dark:text-yellow-300 mb-2">Consequences of Cancellation:</h4>
                    <ul class="list-disc list-inside space-y-1 text-sm text-yellow-700 dark:text-yellow-400">
                        <li>Token will be permanently cancelled</li>
                        <li>Related invoice will be cancelled (if exists)</li>
                        <li>Related gate pass will be cancelled (if exists)</li>
                        <li>Token will be removed from all queues</li>
                        <li>This action cannot be reversed</li>
                    </ul>
                </div>
            </div>

            <div class="flex justify-end gap-2">
                <Button
                    :disabled="form.processing"
                    label="Cancel"
                    severity="secondary"
                    @click="cancelDialog"
                />
                <Button
                    v-if="eligibilityData.warnings && eligibilityData.warnings.length > 0"
                    :disabled="form.processing || !form.cancellation_reason.trim()"
                    label="Acknowledge & Continue"
                    severity="warning"
                    @click="acknowledgeWarnings"
                />
                <Button
                    v-else
                    :disabled="form.processing || !form.cancellation_reason.trim()"
                    label="Continue"
                    severity="danger"
                    @click="handleSubmit"
                />
            </div>
        </div>

        <!-- Confirmation step -->
        <div v-else-if="showConfirmation">
            <div class="mb-6">
                <Message :closable="false" class="mb-4" severity="error">
                    <div class="font-semibold text-lg">Final Confirmation Required</div>
                </Message>

                <div class="bg-red-50 dark:bg-red-900/20 border-2 border-red-300 dark:border-red-800 rounded-lg p-6 mb-4">
                    <p class="text-gray-800 dark:text-gray-200 mb-4">
                        You are about to permanently cancel token <strong>{{ token.token }}</strong>.
                    </p>

                    <div class="bg-white dark:bg-gray-800 p-4 rounded border border-gray-200 dark:border-gray-700 mb-4">
                        <p class="font-semibold text-gray-700 dark:text-gray-300 mb-2">Reason for cancellation:</p>
                        <p class="text-sm text-gray-600 dark:text-gray-400 italic">
                            "{{ form.cancellation_reason }}"
                        </p>
                    </div>

                    <div v-if="form.acknowledged_warnings" class="mb-4">
                        <Message :closable="false" severity="warn">
                            <div class="text-sm">
                                You have acknowledged that packages are outside the bond area and must be returned.
                            </div>
                        </Message>
                    </div>

                    <p class="text-red-600 dark:text-red-400 font-semibold">
                        This action is irreversible. Are you absolutely sure?
                    </p>
                </div>
            </div>

            <div class="flex justify-end gap-2">
                <Button
                    :disabled="form.processing"
                    label="Go Back"
                    severity="secondary"
                    @click="goBackToEdit"
                />
                <Button
                    :disabled="form.processing"
                    :loading="form.processing"
                    icon="pi pi-exclamation-triangle"
                    label="Confirm Cancellation"
                    severity="danger"
                    @click="confirmCancellation"
                />
            </div>
        </div>
    </Dialog>
</template>
