<script setup>
import {ref, computed} from "vue";
import {usePage} from "@inertiajs/vue3";
import Dialog from "primevue/dialog";
import Card from "primevue/card";
import Button from "primevue/button";
import Avatar from "primevue/avatar";
import Tag from "primevue/tag";
import Divider from "primevue/divider";
import moment from "moment";

const props = defineProps({
    visible: {
        type: Boolean,
        default: false
    },
    tokenData: {
        type: Object,
        default: () => ({})
    }
});

const emit = defineEmits(['update:visible', 'close']);

const currencyCode = ref(usePage().props.currentBranch.currency_symbol || "SAR");

const closeModal = () => {
    emit('update:visible', false);
    emit('close');
};

const downloadToken = () => {
    if (props.tokenData?.pdf_url) {
        window.open(props.tokenData.pdf_url, '_blank');
    }
};
</script>

<template>
    <Dialog
        :visible="visible"
        :style="{ width: '600px' }"
        header="Token Issued Successfully"
        modal
        class="p-fluid"
        :closable="false"
        @update:visible="emit('update:visible', $event)"
    >
        <div class="space-y-6">
            <!-- Success Header -->
            <div class="text-center">
                <Avatar icon="pi pi-check-circle" class="bg-green-100 text-green-600" size="xlarge" />
                <h2 class="text-2xl font-bold text-green-800 mt-4">Token Created Successfully!</h2>
                <p class="text-gray-600 mt-2">Your token has been generated and is ready for download</p>
            </div>

            <Divider />

            <!-- Token Information -->
            <Card>
                <template #title>
                    <div class="flex items-center gap-3">
                        <Avatar icon="pi pi-tag" class="bg-blue-100 text-blue-600" size="large" />
                        <div>
                            <h3 class="text-xl font-semibold text-gray-900">Token Information</h3>
                        </div>
                    </div>
                </template>
                <template #content>
                    <div class="grid grid-cols-1 gap-4">
                        <!-- Token Number -->
                        <div class="flex justify-between items-center p-4 bg-blue-50 rounded-lg border border-blue-200">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-blue-100 rounded-lg">
                                    <i class="pi pi-hashtag text-blue-600"></i>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-800">Token Number</p>
                                    <p class="text-sm text-gray-600">Queue reference number</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="inline-flex items-center justify-center w-12 h-12 text-xl font-bold text-white bg-blue-500 rounded-full">
                                    {{ tokenData?.token?.token_number || 'N/A' }}
                                </span>
                            </div>
                        </div>

                        <!-- HBL Details -->
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="font-medium text-gray-600">HBL Number:</span>
                                <span class="font-semibold">{{ tokenData?.hbl?.hbl_number || 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="font-medium text-gray-600">Customer Name:</span>
                                <span class="font-semibold">{{ tokenData?.hbl?.hbl_name || 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="font-medium text-gray-600">Consignee:</span>
                                <span class="font-semibold">{{ tokenData?.hbl?.consignee_name || 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="font-medium text-gray-600">Reference:</span>
                                <span class="font-semibold">{{ tokenData?.hbl?.reference || 'N/A' }}</span>
                            </div>
                        </div>

                        <!-- Timestamp -->
                        <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center gap-2">
                                <i class="pi pi-clock text-gray-600"></i>
                                <span class="font-medium text-gray-600">Created At:</span>
                            </div>
                            <span class="font-semibold text-gray-800">{{ moment().format('MMM DD, YYYY h:mm A') }}</span>
                        </div>
                    </div>
                </template>
            </Card>

            <!-- Instructions -->
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <div class="flex items-start gap-3">
                    <i class="pi pi-info-circle text-yellow-600 mt-1"></i>
                    <div>
                        <h4 class="font-semibold text-yellow-800">Next Steps:</h4>
                        <ul class="text-sm text-yellow-700 mt-2 space-y-1">
                            <li>• Download and print the token</li>
                            <li>• Provide the token to the customer</li>
                            <li>• Customer can use this token to track their queue status</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <template #footer>
            <div class="flex justify-end gap-3">
                <Button
                    label="Download Token"
                    icon="pi pi-download"
                    severity="success"
                    @click="downloadToken"
                    :disabled="!tokenData?.pdf_url"
                />
                <Button
                    label="Close"
                    icon="pi pi-times"
                    severity="secondary"
                    outlined
                    @click="closeModal"
                />
            </div>
        </template>
    </Dialog>
</template>
