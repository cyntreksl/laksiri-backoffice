<script setup>
import { ref, computed } from 'vue';
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';
import Checkbox from 'primevue/checkbox';
import Textarea from 'primevue/textarea';
import Message from 'primevue/message';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';

const props = defineProps({
    visible: {
        type: Boolean,
        required: true,
    },
    hblsWithMissingDates: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(['update:visible', 'confirm', 'cancel']);

const consentGiven = ref(false);
const consentNote = ref('');

const canProceed = computed(() => consentGiven.value);

const handleConfirm = () => {
    if (!canProceed.value) return;

    emit('confirm', {
        consentGiven: consentGiven.value,
        consentNote: consentNote.value,
    });

    // Reset form
    consentGiven.value = false;
    consentNote.value = '';
};

const handleCancel = () => {
    emit('cancel');
    emit('update:visible', false);

    // Reset form
    consentGiven.value = false;
    consentNote.value = '';
};
</script>

<template>
    <Dialog
        :closable="true"
        :draggable="false"
        :modal="true"
        :visible="visible"
        header="Container Arrived at Primary Warehouse Date Missing - Consent Required"
        style="width: 50rem"
        @update:visible="$emit('update:visible', $event)"
    >
        <div class="space-y-4">
            <Message :closable="false" class="mt-1" severity="warn">
                <div class="font-semibold mb-2">
                    <i class="pi pi-exclamation-triangle mr-2"></i>
                    Warning: Missing Container Arrived at Primary Warehouse Date
                </div>
                <p class="text-sm">
                    The following HBL(s) do not have a container arrived at primary warehouse date set. This date is critical for accurate demurrage charge calculation.
                </p>
            </Message>

            <div class="border rounded-lg p-4 bg-gray-50">
                <h4 class="font-semibold mb-3 text-sm">Affected HBLs:</h4>
                <DataTable
                    :paginator="hblsWithMissingDates.length > 5"
                    :rows="5"
                    :value="hblsWithMissingDates"
                    class="text-sm"
                >
                    <Column field="hbl_number" header="HBL Number" />
                    <Column field="hbl_name" header="HBL Name" />
                </DataTable>
            </div>

            <Message :closable="false" severity="info">
                <div class="text-sm">
                    <strong>Impact:</strong>
                    <ul class="list-disc ml-5 mt-2 space-y-1">
                        <li>Demurrage charges may be calculated incorrectly or set to zero</li>
                        <li>This could result in financial discrepancies</li>
                        <li>The container arrived at primary warehouse date should be set before approval</li>
                    </ul>
                </div>
            </Message>

            <div class="border-t pt-4">
                <div class="flex items-start gap-3 mb-3">
                    <Checkbox
                        v-model="consentGiven"
                        :binary="true"
                        input-id="consent-checkbox"
                    />
                    <label class="text-sm cursor-pointer" for="consent-checkbox">
                        <strong>I acknowledge and consent to proceed</strong> with the approval despite the missing container arrived at primary warehouse date(s).
                        I understand that demurrage calculations may be affected and take full responsibility for this decision.
                    </label>
                </div>

                <div class="mt-3">
                    <label class="block text-sm font-medium mb-2" for="consent-note">
                        Additional Notes (Optional):
                    </label>
                    <Textarea
                        id="consent-note"
                        v-model="consentNote"
                        class="w-full"
                        placeholder="Enter any additional notes or reasons for proceeding without the arrived at primary warehouse date..."
                        rows="3"
                    />
                </div>
            </div>
        </div>

        <template #footer>
            <div class="flex justify-end gap-2">
                <Button
                    label="Cancel"
                    outlined
                    severity="secondary"
                    @click="handleCancel"
                />
                <Button
                    :disabled="!canProceed"
                    label="Proceed with Approval"
                    severity="warn"
                    @click="handleConfirm"
                />
            </div>
        </template>
    </Dialog>
</template>
