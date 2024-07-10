<script setup>
import DialogModal from "@/Components/DialogModal.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import {computed} from "vue";
import {useForm} from "@inertiajs/vue3";

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['close']);

const form = useForm({
    container_id: route().params.container,
    packages: computed(() => {
        return props.warehouseArray;
    }),
});

const handleCreateUnloadingIssue = () => {

}
</script>

<template>
    <DialogModal :closeable="true" :maxWidth="'2xl'" :show="show" @close="$emit('close')">
        <template #title>
            <div class="flex justify-between items-center">
                <div>Unloaded Summery</div>
                <button
                    class="text-gray-500 jus text-right hover:text-red-500 focus:outline-none"
                    @click="$emit('close')"
                >
                    <svg class="size-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"
                         xmlns="http://www.w3.org/2000/svg">
                        <path d="M6 18 18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            </div>

        </template>
        <template #content>


        </template>

        <template #footer>
            <div class="flex space-x-2">
                <SecondaryButton @click="$emit('close')">
                    Cancel
                </SecondaryButton>
                <PrimaryButton @click.prevent="handleCreateUnloadingIssue">
                    Create Issue
                </PrimaryButton>
            </div>
        </template>
    </DialogModal>
</template>
