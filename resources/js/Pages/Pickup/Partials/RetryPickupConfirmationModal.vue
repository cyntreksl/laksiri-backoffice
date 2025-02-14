<script setup>
import ConfirmationModal from "@/Components/ConfirmationModal.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['close', 'retryPickup']);

</script>

<template>
    <ConfirmationModal :show="show">
        <template #title>
            Retry Pickup?
        </template>

        <template #content>
            Are you sure you want to Retry Job?
        </template>

        <template #footer>
            <div class="space-x-2">
                <SecondaryButton @click="$emit('close')">
                    Cancel
                </SecondaryButton>

                <PrimaryButton @click="$emit('retryPickup')">
                    Retry
                </PrimaryButton>
            </div>
        </template>
    </ConfirmationModal>
</template>
