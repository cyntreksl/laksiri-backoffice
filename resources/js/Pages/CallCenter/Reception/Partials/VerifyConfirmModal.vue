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

const emit = defineEmits(['close', 'verifyCustomer']);

</script>

<template>
    <ConfirmationModal :show="show">
        <template #title>
            Verify?
        </template>

        <template #content>
            Are you sure to verify this customer?
        </template>

        <template #footer>
            <div class="space-x-2">
                <SecondaryButton @click="$emit('close')">
                    Nevermind
                </SecondaryButton>

                <DangerButton @click="$emit('verifyCustomer')">
                    Verify
                </DangerButton>
            </div>
        </template>
    </ConfirmationModal>
</template>
