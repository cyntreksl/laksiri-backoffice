<script setup>
import ConfirmationModal from "@/Components/ConfirmationModal.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import SoftPrimaryButton from "@/Components/SoftPrimaryButton.vue";

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['close', 'printToken']);

</script>

<template>
    <ConfirmationModal :show="show">
        <template #title>
            Issue & Print Token?
        </template>

        <template #content>
            Would you like to print token for this customer?
        </template>

        <template #footer>
            <div class="space-x-2">
                <SecondaryButton @click="$emit('close')">
                    Nevermind
                </SecondaryButton>

                <SoftPrimaryButton @click="$emit('printToken')">
                    Print & Issue Token
                </SoftPrimaryButton>
            </div>
        </template>
    </ConfirmationModal>
</template>
