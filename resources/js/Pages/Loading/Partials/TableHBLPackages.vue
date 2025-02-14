<script setup>
import {router, usePage} from "@inertiajs/vue3";
import {push} from "notivue";
import {onMounted, ref, watchEffect} from "vue";
import DeleteHBLConfirmationModal from "@/Pages/Loading/Partials/DeleteHBLConfirmationModal.vue";
import HBLDetailModal from "@/Pages/Common/HBLDetailModal.vue";
import MHBLDetailModal from "@/Pages/Common/MHBLDetailModal.vue";
import DeleteMHBLConfirmationModal from "@/Pages/Loading/Partials/DeleteMHBLConfirmModal.vue";

const props = defineProps({
    container: {
        type: Object,
        default: () => {
        },
    },
    containerHBLS: {
        type: Object,
        default: () => {
        },
    },
    containerMHBLS: {
        type: Array,
        default: () => [],
    }
});
const emit = defineEmits(['fetContainerData']);

const containerData = ref({});
const showConfirmDeleteHBLModal = ref(false);
const hblId = ref(null);

watchEffect(() => {
    containerData.value = props.container;
});

const confirmDeleteHBL = (id) => {
    hblId.value = id;
    showConfirmDeleteHBLModal.value = true;
};

const closeModal = () => {
    showConfirmDeleteHBLModal.value = false;
    hblId.value = null;
};

const handleRemoveHBLFromContainer = () => {
    router.put(route('loading.containers.unload.hbl', containerData.value.id), {
            hbl_id: hblId.value
        },
        {
            onSuccess: () => {
                containerData.value = {
                    ...containerData.value,
                    hbls: Object.values(containerData.value.hbls).filter(hbl => hbl.id !== hblId.value)
                };
                emit('fetContainerData');
                props.containerHBLS = props.containerHBLS.filter(item => item.id !== hblId.value);
                closeModal();
                if (containerData.value.hbls.length === 0) {
                    router.visit(route('loading.loaded-containers.index'));
                }
                push.success('Unloaded successfully!');
            },
            onError: () => {
                console.error('Something went to wrong!');
            },
            preserveScroll: true,
            preserveState: true,
        }
    )
}

const showConfirmViewHBLModal = ref(false);
const hblRecord = ref({});

const fetchHBL = async (id) => {
    try {
        const response = await fetch(`hbls/${id}`, {
            method: "GET",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": usePage().props.csrf,
            },
        });

        if (!response.ok) {
            throw new Error('Network response was not ok.');
        } else {
            const data = await response.json();
            hblRecord.value = data.hbl;
        }

    } catch (error) {
        console.log(error);
    }
}

const confirmViewHBL = async (id) => {
    await fetchHBL(id);
    showConfirmViewHBLModal.value = true;
};

const closeShowHBLModal = () => {
    showConfirmViewHBLModal.value = false;
};

const packageCounts = ref([]);

const fetchPackageCount = async () => {
    const mhbls = Object.values(props.containerMHBLS);

    mhbls.forEach(mhbl => {
        packageCounts.value[mhbl.id] = mhbl.hbls.reduce((total, hbl) => {
            return total + (hbl.packages_count || 0);
        }, 0);
    });
};

onMounted(() => {
    fetchPackageCount()
});

const mhblId = ref(null);
const showConfirmViewMHBLModal = ref(false);
const showConfirmDeleteMHBLModal = ref(false);
const confirmViewMHBL = async (id) => {
    mhblId.value = id;
    showConfirmViewMHBLModal.value = true;
};

const closeShowMHBLModal = () => {
    mhblId.value = null;
    showConfirmViewMHBLModal.value = false;
};

const confirmDeleteMHBL = (id) => {
    mhblId.value = id;
    showConfirmDeleteMHBLModal.value = true;
};

const closeConfirmDeleteMHBLModal = () => {
    showConfirmDeleteMHBLModal.value = false;
    mhblId.value = null;
};
const handleRemoveMHBLFromContainer = () => {
    router.put(route('loading.containers.unload.mhbl', containerData.value.id), {
            mhbl_id: mhblId.value
        },
        {
            onSuccess: () => {
                containerData.value = {
                    ...containerData.value,
                    hbls: Object.values(containerData.value.hbls).filter(hbl => {
                        if (hbl.mhbl && hbl.mhbl.id !== null) {
                            return hbl.mhbl.id !== mhblId.value;
                        }
                        return true;
                    })
                };
                emit('fetContainerData');
                closeConfirmDeleteMHBLModal();
                if (containerData.value.hbls.length === 0) {
                    router.visit(route('loading.loaded-containers.index'));
                }
                push.success('Unloaded successfully!');
            },
            onError: () => {
                console.error('Something went to wrong!');
            },
            preserveScroll: true,
            preserveState: true,
        }
    )
}
</script>

<template>
    <div class="is-scrollbar-hidden min-w-full overflow-x-auto my-10">
        <table class="is-hoverable w-full text-left">
            <thead>
            <tr>
                <th
                    class="whitespace-nowrap rounded-l-lg bg-slate-200 px-3 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                >
                    HBL
                </th>
                <th
                    class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                >
                    Packages
                </th>
                <th
                    class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                >
                    Name
                </th>
                <th
                    class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                >
                    PP No
                </th>
                <th
                    class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                >
                    Address
                </th>
                <th
                    class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                >
                    Contact
                </th>
                <th
                    class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                >
                    Consignee Name
                </th>
                <th
                    class="whitespace-nowrap bg-slate-200 px-3 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                >
                    Consignee Address
                </th>
                <th
                    class="whitespace-nowrap rounded-r-lg bg-slate-200 px-3 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                >
                    Action
                </th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="hbl in containerHBLS"
                class="border border-transparent border-b-slate-200 dark:border-b-navy-500">
                <td class="whitespace-nowrap rounded-l-lg px-4 py-3 sm:px-5">
                    {{ hbl.hbl_number || '-' }}
                </td>
                <td class="whitespace-nowrap px-4 py-3 sm:px-5">{{ hbl.packages_count || '-' }}</td>
                <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                    {{ hbl.hbl_name || '-' }}
                </td>
                <td class="whitespace-nowrap px-4 py-3 sm:px-5">-</td>
                <td class="whitespace-nowrap px-4 py-3 sm:px-5">{{ hbl.address || '-' }}</td>
                <td class="whitespace-nowrap px-4 py-3 sm:px-5">{{ hbl.contact_number || '-' }}</td>
                <td class="whitespace-nowrap px-4 py-3 sm:px-5">{{ hbl.consignee_name || '-' }}</td>
                <td class="whitespace-nowrap px-4 py-3 sm:px-5">{{ hbl.consignee_address || '-' }}</td>
                <td class="whitespace-nowrap rounded-r-lg px-4 py-3 sm:px-5">
                    <button
                        class="btn size-8 p-0 rounded-full text-success hover:bg-success/20 focus:bg-success/20 active:bg-success/25"
                        x-tooltip.placement.bottom.error="'Show HBL'"
                        @click.prevent="confirmViewHBL(hbl.id)">
                        <svg  class="size-5 icon icon-tabler icons-tabler-outline icon-tabler-eye"  fill="none"  height="24"  stroke="currentColor"  stroke-linecap="round"  stroke-linejoin="round"  stroke-width="2"  viewBox="0 0 24 24"  width="24"  xmlns="http://www.w3.org/2000/svg"><path d="M0 0h24v24H0z" fill="none" stroke="none"/><path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" /><path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6" /></svg>
                    </button>

                    <button
                        class="btn size-8 p-0 rounded-full text-error hover:bg-error/20 focus:bg-error/20 active:bg-error/25"
                        x-tooltip.placement.bottom.error="'Remove From Shipment'"
                        @click.prevent="confirmDeleteHBL(hbl.id)">
                        <svg class="size-5" fill="none" stroke="currentColor"
                             stroke-width="1.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"
                                stroke-linecap="round"
                                stroke-linejoin="round"/>
                        </svg>
                    </button>
                </td>
            </tr>
            <tr v-for="mhbl in containerMHBLS"
                class="border border-transparent border-b-slate-200 dark:border-b-navy-500">
                <td class="whitespace-nowrap rounded-l-lg px-4 py-3 sm:px-5">
                    {{ mhbl.hbl_number || '-' }}
                </td>
                <td class="whitespace-nowrap px-4 py-3 sm:px-5">{{ packageCounts[mhbl.id] || 0 }}</td>
                <td class="whitespace-nowrap px-4 py-3 sm:px-5">{{ mhbl.shipper.name || '-' }}</td>
                <td class="whitespace-nowrap px-4 py-3 sm:px-5">{{ mhbl.shipper.pp_or_nic_no || '-' }}</td>
                <td class="whitespace-nowrap px-4 py-3 sm:px-5">{{ mhbl.shipper.address || '-' }}</td>
                <td class="whitespace-nowrap px-4 py-3 sm:px-5">{{ mhbl.shipper.mobile_number || '-' }}</td>
                <td class="whitespace-nowrap px-4 py-3 sm:px-5">{{ mhbl.consignee.name || '-' }}</td>
                <td class="whitespace-nowrap px-4 py-3 sm:px-5">{{ mhbl.consignee.address || '-' }}</td>
                <td class="whitespace-nowrap rounded-r-lg px-4 py-3 sm:px-5">
                    <button
                        class="btn size-8 p-0 rounded-full text-success hover:bg-success/20 focus:bg-success/20 active:bg-success/25"
                        x-tooltip.placement.bottom.error="'Show HBL'"
                        @click.prevent="confirmViewMHBL(mhbl.id)">
                        <svg  class="size-5 icon icon-tabler icons-tabler-outline icon-tabler-eye"  fill="none"  height="24"  stroke="currentColor"  stroke-linecap="round"  stroke-linejoin="round"  stroke-width="2"  viewBox="0 0 24 24"  width="24"  xmlns="http://www.w3.org/2000/svg"><path d="M0 0h24v24H0z" fill="none" stroke="none"/><path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" /><path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6" /></svg>
                    </button>

                    <button
                        class="btn size-8 p-0 rounded-full text-error hover:bg-error/20 focus:bg-error/20 active:bg-error/25"
                        x-tooltip.placement.bottom.error="'Remove From Shipment'"
                        @click.prevent="confirmDeleteMHBL(mhbl.id)">
                        <svg class="size-5" fill="none" stroke="currentColor"
                             stroke-width="1.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"
                                stroke-linecap="round"
                                stroke-linejoin="round"/>
                        </svg>
                    </button>
                </td>
            </tr>
            </tbody>
        </table>
    </div>

    <DeleteHBLConfirmationModal :show="showConfirmDeleteHBLModal" @close="closeModal" @unload-hbl="handleRemoveHBLFromContainer"/>

    <DeleteMHBLConfirmationModal :show="showConfirmDeleteMHBLModal" @close="closeConfirmDeleteMHBLModal" @unload-mhbl="handleRemoveMHBLFromContainer"/>

    <HBLDetailModal
        :hbl-id="hblRecord?.id"
        :show="showConfirmViewHBLModal"
        @close="closeShowHBLModal"
    />

    <MHBLDetailModal
        :mhbl-id="mhblId"
        :show="showConfirmViewMHBLModal"
        @close="closeShowMHBLModal"
    />
</template>
