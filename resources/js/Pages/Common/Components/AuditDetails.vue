<script setup>
import SecondaryButton from "@/Components/SecondaryButton.vue";
import DialogModal from "@/Components/DialogModal.vue";
import { ref } from "vue";
import InfoDisplay from "@/Pages/Common/Components/InfoDisplay.vue";

const props = defineProps({
    oldProperties: {
        type: Object,
        default: () => {
        }
    },
    properties: {
        type: Object,
        default: () => {
        }
    },
});

const confirmingShowAuditDetails = ref(false);

const closeModal = () => {
    confirmingShowAuditDetails.value = false;
};
</script>

<template>
    <div class="mx-5 mt-4">
        <svg
            class="icon icon-tabler icons-tabler-outline icon-tabler-eyeglass text-info cursor-pointer"
            fill="none"
            height="24"
            stroke="currentColor"
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            viewBox="0 0 24 24"
            width="24"
            xmlns="http://www.w3.org/2000/svg"
            @click="confirmingShowAuditDetails = !confirmingShowAuditDetails"
        >
            <path d="M0 0h24v24H0z" fill="none" stroke="none" />
            <path d="M8 4h-2l-3 10" />
            <path d="M16 4h2l3 10" />
            <path d="M10 16l4 0" />
            <path d="M21 16.5a3.5 3.5 0 0 1 -7 0v-2.5h7v2.5" />
            <path d="M10 16.5a3.5 3.5 0 0 1 -7 0v-2.5h7v2.5" />
        </svg>
    </div>

    <DialogModal :maxWidth="'5xl'" :show="confirmingShowAuditDetails" @close="closeModal">
        <template #title>
            <div class="text-lg font-bold">Audit Details</div>
        </template>

        <template #content>
            <div class="p-4">
                <p v-for="(newValue, key) in properties" :key="key" class="mb-2">
            <span class="font-semibold text-slate-500 dark:text-navy-100">
                {{ key.replace(/_/g, ' ').toUpperCase() }}
            </span>
                    <span v-if="oldProperties[key] !== newValue" class="text-slate-500">
                "{{ oldProperties[key] || 'N/A' }}" changed to
                <span class="text-green-500">"{{ newValue || 'N/A' }}"</span>.
            </span>
                    <span v-else class="text-slate-700 dark:text-navy-100">
                No change ({{ newValue || 'N/A' }}).
            </span>
                </p>
            </div>
        </template>



        <template #footer>
            <SecondaryButton @click="closeModal">
                Cancel
            </SecondaryButton>
        </template>
    </DialogModal>
</template>
