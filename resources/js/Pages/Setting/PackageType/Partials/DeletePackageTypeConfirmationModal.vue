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

const emit = defineEmits(['close', 'deletePackageType']);

</script>

<template>
    <ConfirmationModal :show="show">
        <template #title>
            Delete Package Type?
        </template>

        <template #content>
            Would you like to delete this package type?
        </template>

        <template #footer>
            <div class="space-x-2">
                <SecondaryButton @click="$emit('close')">
                    Nevermind
                </SecondaryButton>

                <DangerButton @click="$emit('deletePackageType')">
                    Delete Package Type
                </DangerButton>
            </div>
        </template>
    </ConfirmationModal>
</template>
