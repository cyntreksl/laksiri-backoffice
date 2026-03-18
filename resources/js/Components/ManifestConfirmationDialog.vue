<template>
    <Dialog
        v-model:visible="visible"
        :closable="false"
        :style="{ width: '450px' }"
        header="Generate Manifest Confirmation"
        modal
    >
        <div class="flex items-center gap-4 mb-4">
            <i class="pi pi-exclamation-triangle text-orange-500 text-3xl"></i>
            <div>
                <p class="text-lg font-medium mb-2">Are you sure you want to generate the manifest?</p>
                <p class="text-sm text-gray-600">
                    Once the manifest is generated, the container cannot be edited or loaded.
                </p>
            </div>
        </div>

        <template #footer>
            <div class="flex justify-end gap-2">
                <Button
                    :disabled="loading"
                    label="No"
                    outlined
                    severity="secondary"
                    @click="handleCancel"
                />
                <Button
                    :loading="loading"
                    label="Yes"
                    severity="danger"
                    @click="handleConfirm"
                />
            </div>
        </template>
    </Dialog>
</template>

<script setup>
import { ref, watch } from 'vue';
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';

const props = defineProps({
    show: {
        type: Boolean,
        default: false
    },
    containerId: {
        type: [String, Number],
        required: true
    }
});

const emit = defineEmits(['update:show', 'confirmed', 'cancelled']);

const visible = ref(false);
const loading = ref(false);

// Watch for show prop changes
watch(() => props.show, (newValue) => {
    visible.value = newValue;
});

// Watch for visible changes to emit update
watch(visible, (newValue) => {
    emit('update:show', newValue);
});

const handleConfirm = () => {
    loading.value = true;
    emit('confirmed', props.containerId);
};

const handleCancel = () => {
    visible.value = false;
    emit('cancelled');
};

// Method to reset loading state (called from parent)
const resetLoading = () => {
    loading.value = false;
};

defineExpose({
    resetLoading
});
</script>
