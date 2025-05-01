<script setup>
import DialogModal from "@/Components/DialogModal.vue";
import Tabs from "@/Components/Tabs.vue";
import TabDocuments from "@/Pages/Loading/Partials/TabDocuments.vue";

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    container: {
        type: Object,
        default: () => {
        },
    },
    containerStatus: {
        type: Array,
        default: () => [],
    },
    seaContainerOptions: {
        type: Array,
        required: true,
    },
    airContainerOptions: {
        type: Array,
        required: true,
    },
});
const emit = defineEmits(['close']);
</script>

<template>
    <DialogModal :closeable="true" :maxWidth="'full'" :show="show" @close="close">
        <template #title>
            <div class="flex justify-between items-center">
                <div></div>
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
            <Tabs>
                <template #icon="{tab}">
                    <svg v-if="tab.name === 'tabDocuments'" class="w-6 h-6" fill="none" stroke="currentColor"
                         stroke-width="1.5"
                         viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"
                            stroke-linecap="round"
                            stroke-linejoin="round"/>
                    </svg>
                </template>

                <TabDocuments :container-id="container?.id"/>
            </Tabs>
        </template>
    </DialogModal>
</template>
