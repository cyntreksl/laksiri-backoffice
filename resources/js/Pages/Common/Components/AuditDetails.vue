<script setup>
import SecondaryButton from "@/Components/SecondaryButton.vue";
import DialogModal from "@/Components/DialogModal.vue";
import { ref, computed } from "vue";
import InfoDisplay from "@/Pages/Common/Components/InfoDisplay.vue";

const props = defineProps({
    oldProperties: {
        type: Object,
        default: () => {
        }
    },
    properties: {
        type: Object,
        default: () => {
        }
    },
});

const confirmingShowAuditDetails = ref(false);

const closeModal = () => {
    confirmingShowAuditDetails.value = false;
};

// Field name mappings for better readability
const fieldLabels = {
    'hbl_number': 'HBL Number',
    'sender_name': 'Sender Name',
    'sender_phone': 'Sender Phone',
    'sender_address': 'Sender Address',
    'receiver_name': 'Receiver Name',
    'receiver_phone': 'Receiver Phone',
    'receiver_address': 'Receiver Address',
    'receiver_nic': 'Receiver NIC',
    'branch_id': 'Branch',
    'pickup_id': 'Pickup',
    'container_id': 'Container',
    'status': 'Status',
    'total_weight': 'Total Weight',
    'total_cbm': 'Total CBM',
    'total_amount': 'Total Amount',
    'paid_amount': 'Paid Amount',
    'balance_amount': 'Balance Amount',
    'payment_status': 'Payment Status',
    'created_at': 'Created Date',
    'updated_at': 'Updated Date',
    'is_active': 'Active Status',
    'notes': 'Notes',
    'special_instructions': 'Special Instructions',
    'delivery_date': 'Delivery Date',
    'pickup_date': 'Pickup Date',
    'package_type': 'Package Type',
    'package_count': 'Package Count',
    'description': 'Description',
    'value': 'Value',
    'weight': 'Weight',
    'cbm': 'CBM',
    'length': 'Length',
    'width': 'Width',
    'height': 'Height',
    'is_fragile': 'Fragile Item',
    'is_liquid': 'Liquid Item',
    'customs_value': 'Customs Value',
    'insurance_value': 'Insurance Value'
};

// Format field names to be more readable
const formatFieldName = (key) => {
    return fieldLabels[key] || key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
};

// System status mappings from HBL model
const systemStatusMappings = {
    1.3: 'HBL Preparation by Driver',
    2.1: 'HBL Preparation by Warehouse',
    2.2: 'Cash Received by Accountant',
    2.3: 'Manifest Preparation',
    2.4: 'D2D Document Preparation',
    2.5: 'Palletize Cargo',
    3.0: 'HBL Created',
    3.1: 'HBL Converted from Job',
    4.0: 'Cash Collected',
    4.1: 'Partial Loaded',
    4.2: 'Fully Loaded',
    4.3: 'Partial Unloaded',
    4.4: 'Fully Unloaded',
    4.5: 'Finance Approved',
    6.0: 'Token Issued',
    6.1: 'Reception Queue',
    6.2: 'Document Verification Queue',
    6.3: 'Cashier Queue',
    6.4: 'Waiting for Package Receive from Bon Area Queue',
    6.6: 'Examination Queue',
    6.7: 'Gate Pass Issued',
    6.8: 'Gate Pass Mark as Released'
};

// Format values to be more readable
const formatValue = (value, key, allProperties = null) => {
    if (value === null || value === undefined || value === '') {
        return 'Not Set';
    }

    // Handle system_status specifically
    if (key === 'system_status' && typeof value === 'number') {
        return systemStatusMappings[value] || `Unknown Status (${value})`;
    }

    // Boolean values
    if (typeof value === 'boolean') {
        return value ? 'Yes' : 'No';
    }

    // Handle foreign key relationships - look for related data
    if (key && key.endsWith('_id') && !isNaN(value) && allProperties) {
        const relationshipKey = key.replace('_id', '');
        
        // Common relationship mappings
        const relationshipMappings = {
            'branch': 'branch_name',
            'pickup': 'pickup_number',
            'container': 'container_number',
            'user': 'user_name',
            'driver': 'driver_name',
            'customer': 'customer_name',
            'sender': 'sender_name',
            'receiver': 'receiver_name',
            'created_by': 'created_by_name',
            'updated_by': 'updated_by_name',
            'assigned_to': 'assigned_to_name'
        };

        // Try to find the relationship name in the properties
        const nameField = relationshipMappings[relationshipKey];
        if (nameField && allProperties[nameField]) {
            return `${allProperties[nameField]} (ID: ${value})`;
        }

        // Try alternative naming patterns
        const alternativeFields = [
            `${relationshipKey}_name`,
            `${relationshipKey}_title`,
            `${relationshipKey}_number`,
            `${relationshipKey}_code`
        ];

        for (const field of alternativeFields) {
            if (allProperties[field]) {
                return `${allProperties[field]} (ID: ${value})`;
            }
        }

        // If no relationship name found, still show a more user-friendly format
        return `${relationshipKey.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())} ID: ${value}`;
    }

    // Status values - make them more readable
    if (key && key.includes('status') && typeof value === 'string') {
        return value.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
    }

    // Numeric values with specific formatting
    if (key && (key.includes('amount') || key.includes('value')) && !isNaN(value)) {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'LKR',
            minimumFractionDigits: 2
        }).format(value);
    }

    if (key && (key.includes('weight')) && !isNaN(value)) {
        return `${value} kg`;
    }

    if (key && (key.includes('cbm')) && !isNaN(value)) {
        return `${value} m³`;
    }

    // Date formatting
    if (key && (key.includes('date') || key.includes('_at')) && value) {
        try {
            const date = new Date(value);
            return date.toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        } catch (e) {
            return value;
        }
    }

    // Handle enum-like values (make them more readable)
    if (typeof value === 'string' && value.includes('_')) {
        return value.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
    }

    return value;
};

// Compute changes for better organization
const changes = computed(() => {
    if (!props.properties) return [];

    const changesList = [];

    Object.keys(props.properties).forEach(key => {
        const newValue = props.properties[key];
        const oldValue = props.oldProperties?.[key];

        changesList.push({
            field: key,
            fieldName: formatFieldName(key),
            oldValue: formatValue(oldValue, key, props.oldProperties),
            newValue: formatValue(newValue, key, props.properties),
            hasChanged: oldValue !== newValue,
            isNew: oldValue === null || oldValue === undefined,
            isRemoved: newValue === null || newValue === undefined
        });
    });

    // Sort changes: modified first, then new, then unchanged
    return changesList.sort((a, b) => {
        if (a.hasChanged && !b.hasChanged) return -1;
        if (!a.hasChanged && b.hasChanged) return 1;
        if (a.isNew && !b.isNew) return -1;
        if (!a.isNew && b.isNew) return 1;
        return a.fieldName.localeCompare(b.fieldName);
    });
});
</script>

<template>
    <div class="mx-5 mt-4">
        <svg
            class="icon icon-tabler icons-tabler-outline icon-tabler-eyeglass text-info cursor-pointer"
            fill="none"
            height="24"
            stroke="currentColor"
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            viewBox="0 0 24 24"
            width="24"
            xmlns="http://www.w3.org/2000/svg"
            @click="confirmingShowAuditDetails = !confirmingShowAuditDetails"
        >
            <path d="M0 0h24v24H0z" fill="none" stroke="none" />
            <path d="M8 4h-2l-3 10" />
            <path d="M16 4h2l3 10" />
            <path d="M10 16l4 0" />
            <path d="M21 16.5a3.5 3.5 0 0 1 -7 0v-2.5h7v2.5" />
            <path d="M10 16.5a3.5 3.5 0 0 1 -7 0v-2.5h7v2.5" />
        </svg>
    </div>

    <DialogModal :maxWidth="'6xl'" :show="confirmingShowAuditDetails" @close="closeModal">
        <template #title>
            <div class="flex items-center space-x-3">
                <div class="flex size-10 items-center justify-center rounded-lg bg-primary/10 text-primary">
                    <svg class="icon icon-tabler icons-tabler-outline icon-tabler-history" fill="none" height="20" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" width="20" xmlns="http://www.w3.org/2000/svg">
                        <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                        <path d="M12 8l0 4l2 2"/>
                        <path d="M3.05 11a9 9 0 1 1 .5 4m-.5 5v-5h5"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-slate-800 dark:text-navy-100">Change History</h3>
                    <p class="text-sm text-slate-500 dark:text-navy-300">Review what was modified in this record</p>
                </div>
            </div>
        </template>

        <template #content>
            <div class="p-6">
                <div v-if="changes.length === 0" class="text-center py-8">
                    <div class="flex justify-center mb-4">
                        <div class="flex size-16 items-center justify-center rounded-full bg-slate-100 dark:bg-navy-600">
                            <svg class="icon icon-tabler icons-tabler-outline icon-tabler-file-search text-slate-400" fill="none" height="32" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" viewBox="0 0 24 24" width="32" xmlns="http://www.w3.org/2000/svg">
                                <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                                <path d="M14 3v4a1 1 0 0 0 1 1h4"/>
                                <path d="M12 21h-5a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v4.5"/>
                                <path d="M16.5 17.5m-2.5 0a2.5 2.5 0 1 0 5 0a2.5 2.5 0 1 0 -5 0"/>
                                <path d="M18.5 19.5l2.5 2.5"/>
                            </svg>
                        </div>
                    </div>
                    <h4 class="text-lg font-semibold text-slate-600 dark:text-navy-200 mb-2">No Changes Found</h4>
                    <p class="text-slate-500 dark:text-navy-300">There are no recorded changes for this item.</p>
                </div>

                <div v-else class="space-y-4">
                    <!-- Summary Stats -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                        <div class="bg-blue-50 dark:bg-navy-800 rounded-lg p-4 text-center">
                            <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                                {{ changes.filter(c => c.hasChanged).length }}
                            </div>
                            <div class="text-sm text-blue-600 dark:text-blue-400 font-medium">Modified Fields</div>
                        </div>
                        <div class="bg-green-50 dark:bg-navy-800 rounded-lg p-4 text-center">
                            <div class="text-2xl font-bold text-green-600 dark:text-green-400">
                                {{ changes.filter(c => c.isNew).length }}
                            </div>
                            <div class="text-sm text-green-600 dark:text-green-400 font-medium">New Fields</div>
                        </div>
                        <div class="bg-slate-50 dark:bg-navy-800 rounded-lg p-4 text-center">
                            <div class="text-2xl font-bold text-slate-600 dark:text-slate-400">
                                {{ changes.filter(c => !c.hasChanged).length }}
                            </div>
                            <div class="text-sm text-slate-600 dark:text-slate-400 font-medium">Unchanged Fields</div>
                        </div>
                    </div>

                    <!-- Changes List -->
                    <div class="space-y-3">
                        <div v-for="change in changes" :key="change.field"
                             class="border border-slate-200 dark:border-navy-600 rounded-lg p-4 transition-all hover:shadow-sm">

                            <!-- Field Header -->
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center space-x-3">
                                    <!-- Change Type Icon -->
                                    <div v-if="change.hasChanged" class="flex size-8 items-center justify-center rounded-full bg-amber-100 dark:bg-amber-900/30">
                                        <svg class="icon icon-tabler icons-tabler-outline icon-tabler-edit text-amber-600 dark:text-amber-400" fill="none" height="16" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" width="16" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                                            <path d="M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1"/>
                                            <path d="M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3l8.385 -8.415z"/>
                                            <path d="M16 5l3 3"/>
                                        </svg>
                                    </div>
                                    <div v-else-if="change.isNew" class="flex size-8 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/30">
                                        <svg class="icon icon-tabler icons-tabler-outline icon-tabler-plus text-green-600 dark:text-green-400" fill="none" height="16" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" width="16" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                                            <path d="M12 5l0 14"/>
                                            <path d="M5 12l14 0"/>
                                        </svg>
                                    </div>
                                    <div v-else class="flex size-8 items-center justify-center rounded-full bg-slate-100 dark:bg-navy-700">
                                        <svg class="icon icon-tabler icons-tabler-outline icon-tabler-minus text-slate-500 dark:text-slate-400" fill="none" height="16" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" width="16" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                                            <path d="M5 12l14 0"/>
                                        </svg>
                                    </div>

                                    <!-- Field Name -->
                                    <div>
                                        <h4 class="font-semibold text-slate-800 dark:text-navy-100">{{ change.fieldName }}</h4>
                                        <p class="text-xs text-slate-500 dark:text-navy-300">{{ change.field }}</p>
                                    </div>
                                </div>

                                <!-- Change Badge -->
                                <div v-if="change.hasChanged" class="px-2 py-1 bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 text-xs font-medium rounded-full">
                                    Modified
                                </div>
                                <div v-else-if="change.isNew" class="px-2 py-1 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 text-xs font-medium rounded-full">
                                    New
                                </div>
                                <div v-else class="px-2 py-1 bg-slate-100 dark:bg-navy-700 text-slate-600 dark:text-slate-300 text-xs font-medium rounded-full">
                                    Unchanged
                                </div>
                            </div>

                            <!-- Value Changes -->
                            <div v-if="change.hasChanged" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Old Value -->
                                <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-3">
                                    <div class="flex items-center space-x-2 mb-2">
                                        <svg class="icon icon-tabler icons-tabler-outline icon-tabler-arrow-left text-red-600 dark:text-red-400" fill="none" height="16" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" width="16" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                                            <path d="M5 12l14 0"/>
                                            <path d="M5 12l6 6"/>
                                            <path d="M5 12l6 -6"/>
                                        </svg>
                                        <span class="text-sm font-medium text-red-700 dark:text-red-300">Previous Value</span>
                                    </div>
                                    <div class="text-sm text-red-800 dark:text-red-200 font-mono bg-white dark:bg-navy-900 rounded px-2 py-1 border">
                                        {{ change.oldValue }}
                                    </div>
                                </div>

                                <!-- New Value -->
                                <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-3">
                                    <div class="flex items-center space-x-2 mb-2">
                                        <svg class="icon icon-tabler icons-tabler-outline icon-tabler-arrow-right text-green-600 dark:text-green-400" fill="none" height="16" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" width="16" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                                            <path d="M5 12l14 0"/>
                                            <path d="M13 18l6 -6"/>
                                            <path d="M13 6l6 6"/>
                                        </svg>
                                        <span class="text-sm font-medium text-green-700 dark:text-green-300">New Value</span>
                                    </div>
                                    <div class="text-sm text-green-800 dark:text-green-200 font-mono bg-white dark:bg-navy-900 rounded px-2 py-1 border">
                                        {{ change.newValue }}
                                    </div>
                                </div>
                            </div>

                            <!-- Single Value (for unchanged or new fields) -->
                            <div v-else class="bg-slate-50 dark:bg-navy-800 border border-slate-200 dark:border-navy-600 rounded-lg p-3">
                                <div class="flex items-center space-x-2 mb-2">
                                    <svg class="icon icon-tabler icons-tabler-outline icon-tabler-info-circle text-slate-600 dark:text-slate-400" fill="none" height="16" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" width="16" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                                        <path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0"/>
                                        <path d="M12 9h.01"/>
                                        <path d="M11 12h1v4h1"/>
                                    </svg>
                                    <span class="text-sm font-medium text-slate-700 dark:text-slate-300">
                                        {{ change.isNew ? 'Initial Value' : 'Current Value' }}
                                    </span>
                                </div>
                                <div class="text-sm text-slate-800 dark:text-slate-200 font-mono bg-white dark:bg-navy-900 rounded px-2 py-1 border">
                                    {{ change.newValue }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <template #footer>
            <SecondaryButton class="mr-3" @click="closeModal">
                Close
            </SecondaryButton>
        </template>
    </DialogModal>
</template>
