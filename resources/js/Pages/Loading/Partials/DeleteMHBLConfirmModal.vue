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

const emit = defineEmits(['close', 'unloadMhbl']);

</script>

<template>
    <ConfirmationModal :show="show">
        <template #title>
            Unload MHBL?
        </template>

        <template #content>
            Would you like to unload this mhbl record from this container?
        </template>

        <template #footer>
            <div class="space-x-2">
                <SecondaryButton @click="$emit('close')">
                    Nevermind
                </SecondaryButton>

                <DangerButton @click="$emit('unloadMhbl')">
                    Unload HBL
                </DangerButton>
            </div>
        </template>
    </ConfirmationModal>
</template>
