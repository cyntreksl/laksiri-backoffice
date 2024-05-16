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

const emit = defineEmits(['close', 'removePackage']);

</script>

<template>
    <ConfirmationModal :show="show">
        <template #title>
            Remove Package?
        </template>

        <template #content>
            Would you like to remove this hbl package record?
        </template>

        <template #footer>
            <div class="space-x-2">
                <SecondaryButton @click="$emit('close')">
                    Nevermind
                </SecondaryButton>

                <DangerButton @click="$emit('removePackage')">
                    Remove Package
                </DangerButton>
            </div>
        </template>
    </ConfirmationModal>
</template>
