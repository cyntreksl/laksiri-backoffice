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
                    <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M15.91 11.672a.375.375 0 0 1 0 .656l-5.603 3.113a.375.375 0 0 1-.557-.328V8.887c0-.286.307-.466.557-.327l5.603 3.112Z" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    Release
                </PrimaryButton>

                <PrimaryButton v-else type="button" @click="$emit('toggleHold')">
                    <svg class="w-6 h-6 mr-2 " fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M14.25 9v6m-4.5 0V9M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    Hold
                </PrimaryButton>
            </div>
        </template>
    </ConfirmationModal>
</template>
