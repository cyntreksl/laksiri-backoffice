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
    containerArray: {
        type: Object,
        default: () => {
        },
    },
    findHblByPackageId: {
        type: Function,
        required: true
    },
    containerPackages: {
        type: Object,
        default: () => {
        },
    },
    loadedMHBLs: {
        type: Object,
        default: () => {
        },
    },
    isDestinationLoading: {
        type: Boolean,
        default: false,
        required: false,
    },
    loadedHBLsPackages: {
        type: Object,
        default: () => {
        },
    },
});

const emit = defineEmits(['close']);

const countPackages = (packageHBlId) => {
    return props.containerArray.filter(item => item.hbl_id === packageHBlId).length;
}

const uniqueContainerArray = computed(() => {
    const seen = new Set();
    return props.containerArray.filter(item => {
        const hblId = props.findHblByPackageId(item.id).id;
        if (!seen.has(hblId)) {
            seen.add(hblId);
            return true;
        }
        return false;
    });
});

const form = useForm({
    note: '',
    container_id: route().params.container,
    cargo_type: route().params.cargoType,
    packages: props.isDestinationLoading ? computed(() => {return props.containerArray;}) :computed(() => {return props.containerPackages;}),
    isDestinationLoading: props.isDestinationLoading,
});

const getMHBLPackageCount = (hbls) => {
    return hbls.reduce((total, hbl) => {
        return total + (hbl.packages ? hbl.packages.length : 0);
    }, 0);
}

const handleCreateLoadedContainer = () => {
    form.post(route("loading.loaded-containers.store"), {
        onSuccess: () => {
            push.success('Container loaded successfully!');
            emit('close');
            router.visit(route("loading.loading-points.index", {
                'container': route().params.container,
                'cargoType': route().params.cargoType,
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
    <DialogModal :closeable="true" :maxWidth="'4xl'" :show="show" @close="$emit('close')">
        <template #title>
            <div class="flex justify-between items-center">
                <div>Shipping Summery</div>
                <button
                    class="text-gray-500 jus text-right hover:text-red-500 focus:outline-none"
                    @click="$emit('close')"
                >
                    <svg class="size-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M6 18 18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>
            </div>

        </template>
        <template #content>

            <div class="py-5 mx-2 flex items-center justify-between">
                <div v-if="route().params.cargoType === 'Sea Cargo'" class="flex items-center space-x-2">
                    <svg class="icon icon-tabler icons-tabler-outline icon-tabler-ship text-primary"  fill="none"  height="24"  stroke="currentColor"  stroke-linecap="round"  stroke-linejoin="round"  stroke-width="2"  viewBox="0 0 24 24"  width="24"  xmlns="http://www.w3.org/2000/svg"><path d="M0 0h24v24H0z" fill="none" stroke="none"/><path d="M2 20a2.4 2.4 0 0 0 2 1a2.4 2.4 0 0 0 2 -1a2.4 2.4 0 0 1 2 -1a2.4 2.4 0 0 1 2 1a2.4 2.4 0 0 0 2 1a2.4 2.4 0 0 0 2 -1a2.4 2.4 0 0 1 2 -1a2.4 2.4 0 0 1 2 1a2.4 2.4 0 0 0 2 1a2.4 2.4 0 0 0 2 -1" /><path d="M4 18l-1 -5h18l-2 4" /><path d="M5 13v-6h8l4 6" /><path d="M7 7v-4h-1" /></svg>
                    <p>Sea Cargo</p>
                </div>

                <div v-if="route().params.cargoType === 'Air Cargo'" class="flex items-center space-x-2">
                    <svg class="icon icon-tabler icons-tabler-outline icon-tabler-plane text-warning"  fill="none"  height="24"  stroke="currentColor"  stroke-linecap="round"  stroke-linejoin="round"  stroke-width="2"  viewBox="0 0 24 24"  width="24"  xmlns="http://www.w3.org/2000/svg"><path d="M0 0h24v24H0z" fill="none" stroke="none"/><path d="M16 10h4a2 2 0 0 1 0 4h-4l-4 7h-3l2 -7h-4l-2 2h-3l2 -4l-2 -4h3l2 2h4l-2 -7h3z" /></svg>
                    <p>Air Cargo</p>
                </div>

                <TextInput v-model="form.note" placeholder="Notes" />
            </div>

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
                            class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                        >
                            Loaded Packages
                        </th>
                        <th
                            class="whitespace-nowrap rounded-r-lg bg-slate-200 px-3 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                        >
                            Total Packages
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="(packageData, index) in uniqueContainerArray" class="border border-transparent border-b-slate-200 dark:border-b-navy-500">
                        <td class="whitespace-nowrap rounded-l-lg px-4 py-3 sm:px-5">{{ index + 1 }}</td>
                        <td class="whitespace-nowrap px-4 py-3 sm:px-5">{{ findHblByPackageId(packageData.id).hbl_number }}</td>
                        <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                            {{ countPackages(packageData.hbl_id)}}
                        </td>
                        <td class="whitespace-nowrap rounded-r-lg px-4 py-3 sm:px-5">
                            {{ loadedHBLsPackages[packageData.hbl_id].length }}
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="!isDestinationLoading" class="is-scrollbar-hidden min-w-full overflow-x-auto mt-2">
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
                            MHBL
                        </th>
                        <th
                            class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                        >
                            Loaded Packages
                        </th>
                        <th
                            class="whitespace-nowrap rounded-r-lg bg-slate-200 px-3 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                        >
                            Total Packages
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="(mhbl, index) in loadedMHBLs" class="border border-transparent border-b-slate-200 dark:border-b-navy-500">
                        <td class="whitespace-nowrap rounded-l-lg px-4 py-3 sm:px-5">{{ index + 1 }}</td>
                        <td class="whitespace-nowrap px-4 py-3 sm:px-5">{{ mhbl.hbl_number || mhbl.reference }}</td>
                        <td class="whitespace-nowrap px-4 py-3 sm:px-5">{{ getMHBLPackageCount(mhbl.hbls) }}</td>
                        <td class="whitespace-nowrap px-4 py-3 sm:px-5">{{ getMHBLPackageCount(mhbl.hbls) }}</td>

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
                <PrimaryButton @click.prevent="handleCreateLoadedContainer">
                    Finish Loading
                </PrimaryButton>
            </div>
        </template>
    </DialogModal>
</template>
