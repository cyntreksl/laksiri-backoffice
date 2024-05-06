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

const emit = defineEmits(['close', 'deleteDriver']);

</script>

<template>
    <ConfirmationModal :show="show">
        <template #title>
            Delete Driver?
        </template>

        <template #content>
            Would you like to delete this driver?
        </template>

        <template #footer>
            <div class="space-x-2">
                <SecondaryButton @click="$emit('close')">
                    Nevermind
                </SecondaryButton>

                <DangerButton @click="$emit('deleteDriver')">
                    Delete Driver
                </DangerButton>
            </div>
        </template>
    </ConfirmationModal>
</template>
