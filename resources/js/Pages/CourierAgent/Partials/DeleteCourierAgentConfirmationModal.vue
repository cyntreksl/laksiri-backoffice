<script setup>
import ConfirmationModal from "@/Components/ConfirmationModal.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import DangerButton from "@/Components/DangerButton.vue";

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['close', 'deleteCourierAgent']);  // Changed to camelCase

</script>

<template>
    <ConfirmationModal :show="show">
        <template #title>
            Delete Agent?
        </template>

        <template #content>
            Would you like to delete this Courier Agent?
        </template>

        <template #footer>
            <div class="space-x-2">
                <SecondaryButton @click="$emit('close')">
                    Close
                </SecondaryButton>

                <DangerButton @click="$emit('deleteCourierAgent')">
                    Delete Agent
                </DangerButton>
            </div>
        </template>
    </ConfirmationModal>
</template>
