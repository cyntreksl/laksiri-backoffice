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

const emit = defineEmits(['close', 'deleteCourier']);

</script>

<template>
    <ConfirmationModal :show="show">
        <template #title>
            Delete Coourier?
        </template>

        <template #content>
            Would you like to delete this courier record?
        </template>

        <template #footer>
            <div class="space-x-2">
                <SecondaryButton @click="$emit('close')">
                    Close
                </SecondaryButton>

                <DangerButton @click="$emit('deleteCourier')">
                    Delete HBL
                </DangerButton>
            </div>
        </template>
    </ConfirmationModal>
</template>
