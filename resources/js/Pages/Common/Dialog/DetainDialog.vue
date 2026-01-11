<script setup>
import { ref, watch } from 'vue';
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';
import Dropdown from 'primevue/dropdown';
import Textarea from 'primevue/textarea';
import FloatLabel from 'primevue/floatlabel';

const props = defineProps({
    visible: {
        type: Boolean,
        required: true
    },
    mode: {
        type: String,
        required: true,
        validator: (value) => ['detain', 'lift'].includes(value)
    },
    entityType: {
        type: String,
        required: true,
        validator: (value) => ['package', 'hbl', 'shipment'].includes(value)
    },
    entityName: {
        type: String,
        default: ''
    }
});

const emit = defineEmits(['update:visible', 'confirm']);

const detainTypeOptions = [
    { label: 'RTF', value: 'RTF' },
    { label: 'DDC ', value: 'DDC' },
    { label: 'SDDC', value: 'SDDC' },
    { label: 'IAU', value: 'IAU' },
    { label: 'DC', value: 'DC' },
    { label: 'CO', value: 'CO' },
    { label: 'ICT', value: 'ICT' }
];

const detainType = ref(null);
const detainReason = ref('');
const liftReason = ref('');
const remarks = ref('');
const loading = ref(false);

watch(() => props.visible, (newVal) => {
    if (!newVal) {
        resetForm();
    }
});

const resetForm = () => {
    detainType.value = null;
    detainReason.value = '';
    liftReason.value = '';
    remarks.value = '';
    loading.value = false;
};

const handleConfirm = () => {
    if (props.mode === 'detain') {
        if (!detainType.value || !detainReason.value) {
            return;
        }
        emit('confirm', {
            detain_type: detainType.value,
            detain_reason: detainReason.value,
            remarks: remarks.value
        });
    } else {
        if (!liftReason.value) {
            return;
        }
        emit('confirm', {
            lift_reason: liftReason.value,
            remarks: remarks.value
        });
    }
};

const handleCancel = () => {
    emit('update:visible', false);
};

const isFormValid = () => {
    if (props.mode === 'detain') {
        return detainType.value && detainReason.value.trim();
    } else {
        return liftReason.value.trim();
    }
};
</script>

<template>
    <Dialog
        :header="mode === 'detain' ? `Detain ${entityType.charAt(0).toUpperCase() + entityType.slice(1)}` : `Lift Detain from ${entityType.charAt(0).toUpperCase() + entityType.slice(1)}`"
        :style="{ width: '600px' }"
        :visible="visible"
        modal
        @update:visible="$emit('update:visible', $event)"
    >
        <div class="space-y-4">
            <!-- Entity Info -->
            <div v-if="entityName" class="p-3 bg-blue-50 rounded-lg border border-blue-200">
                <p class="text-sm text-blue-700">
                    <span class="font-semibold">{{ entityType.charAt(0).toUpperCase() + entityType.slice(1) }}:</span>
                    {{ entityName }}
                </p>
            </div>

            <!-- Detain Type (only for detain mode) -->
            <FloatLabel v-if="mode === 'detain'" variant="on">
                <Dropdown
                    id="detain-type"
                    v-model="detainType"
                    :options="detainTypeOptions"
                    class="w-full"
                    optionLabel="label"
                    optionValue="value"
                    placeholder="Select Detain Type"
                />
            </FloatLabel>

            <!-- Detain Reason (only for detain mode) -->
            <FloatLabel v-if="mode === 'detain'" variant="on">
                <Textarea
                    id="detain-reason"
                    v-model="detainReason"
                    class="w-full"
                    maxlength="500"
                    placeholder="Enter reason for detaining..."
                    rows="3"
                />
            </FloatLabel>

            <!-- Lift Reason (only for lift mode) -->
            <FloatLabel v-if="mode === 'lift'" variant="on">
                <Textarea
                    id="lift-reason"
                    v-model="liftReason"
                    class="w-full"
                    maxlength="500"
                    placeholder="Enter reason for lifting detain..."
                    rows="3"
                />
            </FloatLabel>

            <!-- Remarks (optional for both modes) -->
            <FloatLabel variant="on">
                <Textarea
                    id="remarks"
                    v-model="remarks"
                    class="w-full"
                    maxlength="1000"
                    placeholder="Additional remarks (optional)..."
                    rows="3"
                />
            </FloatLabel>

            <!-- Character count -->
            <div class="text-xs text-gray-500 text-right">
                <span v-if="mode === 'detain'">
                    Reason: {{ detainReason.length }}/500
                </span>
                <span v-else>
                    Reason: {{ liftReason.length }}/500
                </span>
                <span class="ml-3">Remarks: {{ remarks.length }}/1000</span>
            </div>
        </div>

        <template #footer>
            <div class="flex justify-end gap-2">
                <Button
                    :disabled="loading"
                    label="Cancel"
                    outlined
                    severity="secondary"
                    @click="handleCancel"
                />
                <Button
                    :disabled="!isFormValid() || loading"
                    :label="mode === 'detain' ? 'Detain' : 'Lift Detain'"
                    :loading="loading"
                    :severity="mode === 'detain' ? 'warn' : 'success'"
                    @click="handleConfirm"
                />
            </div>
        </template>
    </Dialog>
</template>
