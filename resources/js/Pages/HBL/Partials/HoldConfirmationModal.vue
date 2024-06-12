<script setup>
import ConfirmationModal from "@/Components/ConfirmationModal.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";

defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    hblData: {
        type: Object,
        default: () => {
        }
    }
});

const emit = defineEmits(['close', 'toggleHold']);

</script>

<template>
    <ConfirmationModal :show="show">
        <template #title>
            {{hblData[14]?.data ? 'Release' : 'Hold'}} {{ hblData[1]?.data }} ?
        </template>

        <template #content>
            Would you like to {{hblData[14]?.data ? 'release' : 'hold'}}?
        </template>

        <template #footer>
            <div class="flex space-x-2">
                <SecondaryButton @click="$emit('close')">
                    Nevermind
                </SecondaryButton>

                <PrimaryButton v-if="hblData[14]?.data" type="button" @click="$emit('toggleHold')">
                    <svg
                        class="icon icon-tabler icons-tabler-outline icon-tabler-player-play mr-2"
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
                        <path d="M7 4v16l13 -8z" />
                    </svg>
                    Release
                </PrimaryButton>

                <PrimaryButton v-else type="button" @click="$emit('toggleHold')">
                    <svg
                        class="icon icon-tabler icons-tabler-outline icon-tabler-player-pause mr-2"
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
                        <path d="M6 5m0 1a1 1 0 0 1 1 -1h2a1 1 0 0 1 1 1v12a1 1 0 0 1 -1 1h-2a1 1 0 0 1 -1 -1z" />
                        <path d="M14 5m0 1a1 1 0 0 1 1 -1h2a1 1 0 0 1 1 1v12a1 1 0 0 1 -1 1h-2a1 1 0 0 1 -1 -1z" />
                    </svg>
                    Hold
                </PrimaryButton>
            </div>
        </template>
    </ConfirmationModal>
</template>
