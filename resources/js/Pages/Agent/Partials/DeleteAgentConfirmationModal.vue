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

const emit = defineEmits(['close', 'deleteAgent']);  // Changed to camelCase

</script>

<template>
    <ConfirmationModal :show="show">
        <template #title>
            Delete Third Party Agent ?
        </template>

        <template #content>
            Would you like to delete this Third Party Agent?
        </template>

        <template #footer>
            <div class="space-x-2">
                <SecondaryButton @click="$emit('close')">
                    Close
                </SecondaryButton>

                <DangerButton @click="$emit('deleteAgent')">
                    Delete Agent
                </DangerButton>
            </div>
        </template>
    </ConfirmationModal>
</template>
