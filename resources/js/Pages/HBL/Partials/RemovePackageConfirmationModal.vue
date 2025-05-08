<script setup>
import ConfirmationModal from "@/Components/ConfirmationModal.vue";
import Button from "primevue/button";

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
                <Button label="Cancel" severity="danger" variant="outlined"  @click="$emit('close')" />

                <Button label="Remove Package"  @click="$emit('removePackage')" />
            </div>
        </template>
    </ConfirmationModal>
</template>
