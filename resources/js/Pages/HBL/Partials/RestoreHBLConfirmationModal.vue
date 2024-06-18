<script setup>
import ConfirmationModal from "@/Components/ConfirmationModal.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['close', 'restoreHbl']);

</script>

<template>
    <ConfirmationModal :show="show">
        <template #title>
            Restore HBL?
        </template>

        <template #content>
            Would you like to restore this hbl record?
        </template>

        <template #footer>
            <div class="flex space-x-2 items-center">
                <SecondaryButton @click="$emit('close')">
                    Nevermind
                </SecondaryButton>

                <PrimaryButton @click="$emit('restoreHbl')">
                    <svg
                        class="size-5 icon icon-tabler icons-tabler-outline icon-tabler-trash-off mr-2"
                        fill="none"
                        height="24"
                        stroke="currentColor"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        viewBox="0 0 24 24"
                        width="24"
                        xmlns="http://www.w3.org/2000/svg"
                    >
                        <path d="M0 0h24v24H0z" fill="none" stroke="none" />
                        <path d="M3 3l18 18" />
                        <path d="M4 7h3m4 0h9" />
                        <path d="M10 11l0 6" />
                        <path d="M14 14l0 3" />
                        <path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l.077 -.923" />
                        <path d="M18.384 14.373l.616 -7.373" />
                        <path d="M9 5v-1a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" />
                    </svg>
                    Restore HBL
                </PrimaryButton>
            </div>
        </template>
    </ConfirmationModal>
</template>
