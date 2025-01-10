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

const emit = defineEmits(['close', 'removePriceRule']);

</script>

<template>
    <ConfirmationModal :show="show">
        <template #title>
            Remove Price Rule?
        </template>

        <template #content>
            Would you like to remove this Price Rule?
        </template>

        <template #footer>
            <div class="space-x-2">
                <SecondaryButton @click="$emit('close')">
                    Nevermind
                </SecondaryButton>

                <DangerButton @click="$emit('removePriceRule')">
                    Remove Package
                </DangerButton>
            </div>
        </template>
    </ConfirmationModal>
</template>
