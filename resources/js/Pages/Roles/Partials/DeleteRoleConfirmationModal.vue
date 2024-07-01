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

const emit = defineEmits(['close', 'deleteRole']);

</script>

<template>
    <ConfirmationModal :show="show">
        <template #title>
            Delete Role?
        </template>

        <template #content>
            Would you like to delete this role?
        </template>

        <template #footer>
            <div class="space-x-2">
                <SecondaryButton @click="$emit('close')">
                    Nevermind
                </SecondaryButton>

                <DangerButton @click="$emit('deleteRole')">
                    Delete Role
                </DangerButton>
            </div>
        </template>
    </ConfirmationModal>
</template>
