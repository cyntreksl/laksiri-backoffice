<script setup>
import ConfirmationModal from "@/Components/ConfirmationModal.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import DangerButton from "@/Components/DangerButton.vue";

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    countOfSelectedData: {
        type: Number,
        required: true,
    }
});

const emit = defineEmits(['close', 'deleteExceptions']);

</script>

<template>
    <ConfirmationModal :show="show">
        <template #title>
            {{countOfSelectedData}} Delete Pickup Exceptions?
        </template>

        <template #content>
            Would you like to delete this pickup exception record(s)?
        </template>

        <template #footer>
            <div class="space-x-2">
                <SecondaryButton @click="$emit('close')">
                    Nevermind
                </SecondaryButton>

                <DangerButton @click="$emit('deleteExceptions')">
                    Delete Pickup Exceptions
                </DangerButton>
            </div>
        </template>
    </ConfirmationModal>
</template>
