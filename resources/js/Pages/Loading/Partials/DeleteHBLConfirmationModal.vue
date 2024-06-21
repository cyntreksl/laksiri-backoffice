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

const emit = defineEmits(['close', 'unloadHbl']);

</script>

<template>
    <ConfirmationModal :show="show">
        <template #title>
            Unload HBL?
        </template>

        <template #content>
            Would you like to unload this hbl record from this container?
        </template>

        <template #footer>
            <div class="space-x-2">
                <SecondaryButton @click="$emit('close')">
                    Nevermind
                </SecondaryButton>

                <DangerButton @click="$emit('unloadHbl')">
                    Unload HBL
                </DangerButton>
            </div>
        </template>
    </ConfirmationModal>
</template>
