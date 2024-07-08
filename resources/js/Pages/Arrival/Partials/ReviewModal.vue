<script setup>
import DialogModal from "@/Components/DialogModal.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import {computed} from "vue";
import TextInput from "@/Components/TextInput.vue";
import {router, useForm} from "@inertiajs/vue3";
import {push} from "notivue";

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    warehouseArray: {
        type: Object,
        default: () => {
        },
    },
});

const emit = defineEmits(['close']);

const countPackages = (packageHBlId) => {
    return props.warehouseArray.filter(item => item.hbl_id === packageHBlId).length;
}

const uniqueContainerArray = computed(() => {
    const seen = new Set();
    return props.warehouseArray.filter(item => {
        const hblId = item.hbl.id;
        if (!seen.has(hblId)) {
            seen.add(hblId);
            return true;
        }
        return false;
    });
});

const form = useForm({
    container_id: route().params.container,
    packages: computed(() => {
        return props.warehouseArray;
    }),
});

const handleFinishUnloading = () => {
    form.post(route("arrival.unload-container.unload"), {
        onSuccess: () => {
            push.success('Unloading successfully!');
            emit('close');
            router.visit(route("arrival.unloading-points.index", {
                'container': route().params.container,
            }));
        },
        onError: () => {
            push.error('Something went to wrong!');
        },
        preserveScroll: true,
        preserveState: true,
    });
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

            <div class="is-scrollbar-hidden min-w-full overflow-x-auto">
                <table class="is-hoverable w-full text-left">
                    <thead>
                    <tr>
                        <th
                            class="whitespace-nowrap rounded-l-lg bg-slate-200 px-3 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                        >
                            #
                        </th>
                        <th
                            class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                        >
                            HBL
                        </th>
                        <th
                            class="whitespace-nowrap rounded-r-lg bg-slate-200 px-3 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                        >
                            Unloaded Packages
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="(packageData, index) in uniqueContainerArray"
                        class="border border-transparent border-b-slate-200 dark:border-b-navy-500">
                        <td class="whitespace-nowrap rounded-l-lg px-4 py-3 sm:px-5">{{ index + 1 }}</td>
                        <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                            {{ packageData.hbl.reference }}
                        </td>
                        <td class="whitespace-nowrap rounded-r-lg px-4 py-3 sm:px-5">
                            {{ countPackages(packageData.hbl_id) }}
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </template>

        <template #footer>
            <div class="flex space-x-2">
                <SecondaryButton @click="$emit('close')">
                    Cancel
                </SecondaryButton>
                <PrimaryButton @click.prevent="handleFinishUnloading">
                    Finish Unloading
                </PrimaryButton>
            </div>
        </template>
    </DialogModal>
</template>
