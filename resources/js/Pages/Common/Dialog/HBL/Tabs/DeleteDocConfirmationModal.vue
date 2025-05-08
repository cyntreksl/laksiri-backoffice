<script setup>
import ConfirmationModal from "@/Components/ConfirmationModal.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import DangerButton from "@/Components/DangerButton.vue";

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    docName: {
        type: String,
        required: true,
    }
});

const emit = defineEmits(['close', 'deleteDoc']);

</script>

<template>
    <ConfirmationModal :show="show">
        <template #title>
            Delete {{docName}} ?
        </template>

        <template #content>
            Would you like to delete this document?
        </template>

        <template #footer>
            <div class="space-x-2">
                <SecondaryButton @click="$emit('close')">
                    Nevermind
                </SecondaryButton>

                <DangerButton @click="$emit('deleteDoc')">
                    Delete Document
                </DangerButton>
            </div>
        </template>
    </ConfirmationModal>
</template>
