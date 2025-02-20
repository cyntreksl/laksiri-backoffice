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

const emit = defineEmits(['close', 'deleteAgent']);

</script>

<template>
    <ConfirmationModal :show="show">
        <template #title>
            Delete Agent?
        </template>

        <template #content>
            Would you like to delete this Agent?
        </template>

        <template #footer>
            <div class="space-x-2">
                <SecondaryButton @click="$emit('close')">
                    Nevermind
                </SecondaryButton>

                <DangerButton @click="$emit('deleteAgent')">
                    Delete Agent
                </DangerButton>
            </div>
        </template>
    </ConfirmationModal>
</template>
