<script setup>
import moment from "moment";
import draggable from 'vuedraggable'
import ActionMessage from "@/Components/ActionMessage.vue";
import {computed, ref} from "vue";
import {router} from "@inertiajs/vue3";
import ReviewModal from "@/Pages/Arrival/Partials/ReviewModal.vue";
import CreateUnloadingIssueModal from "@/Pages/Arrival/Partials/CreateUnloadingIssueModal.vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import Button from "primevue/button";

const props = defineProps({
    container: {
        type: Object,
        default: () => {
        }
    },
    cargoTypes: {
        type: Array,
        default: () => []
    },
    hblTypes: {
        type: Array,
        default: () => []
    },
    warehouses: {
        type: Array,
        default: () => []
    },
    packagesWithMhbl: {
        type: Array,
        default: () => []
    },
    packagesWithoutMhbl: {
        type: Array,
        default: () => []
    },
})

const searchQuery = ref('');
const containerArr = ref([]);
const mhblContainerArr = ref([]);
const warehouseArr = ref([]);
const warehouseMHBLArr = ref([]);

const groupedPackages = props.packagesWithoutMhbl
    .filter(p => p.pivot?.status !== 'draft-unload')
    .reduce((acc, p) => {
        const hbl_number = p.hbl.hbl_number;
        if (!acc[hbl_number]) {
            acc[hbl_number] = [];
        }
        acc[hbl_number].push(p);
        return acc;
    }, {});

const groupedMHBLPackages = props.packagesWithMhbl
    .filter(p => p.pivot?.status !== 'draft-unload')
    .reduce((acc, p) => {
        const mhblReference = p.hbl.mhbl.reference;
        if (!acc[mhblReference]) {
            acc[mhblReference] = [];
        }
        acc[mhblReference].push(p);
        return acc;
    }, {});

const groupedWarehousePackages = props.packagesWithoutMhbl
    .filter(p => p.pivot?.status === 'draft-unload');

const groupedWarehouseMHBLPackages = props.packagesWithMhbl
    .filter(p => p.pivot?.status === 'draft-unload').reduce((acc, p) => {
        const mhblReference = p.hbl.mhbl.reference;
        if (!acc[mhblReference]) {
            acc[mhblReference] = [];
        }
        acc[mhblReference].push(p);
        return acc;
    }, {});

containerArr.value = Object.keys(groupedPackages).map(hbl_number => {
    return {
        hbl_number: hbl_number,
        expanded: true,
        packages: groupedPackages[hbl_number]
    };
});

mhblContainerArr.value = Object.keys(groupedMHBLPackages).map(mhblReference => {
    console.log(groupedMHBLPackages[mhblReference][0]);
    return {
        mhblReference: mhblReference,
        expanded: true,
        packages: groupedMHBLPackages[mhblReference]
    };
});


warehouseArr.value = groupedWarehousePackages;
warehouseMHBLArr.value = Object.keys(groupedWarehouseMHBLPackages).map(mhblReference => {
    return {
        mhblReference: mhblReference,
        expanded: true,
        packages: groupedWarehouseMHBLPackages[mhblReference]
    };
});

const filteredPackages = computed(() => {
    if (!searchQuery.value) {
        return containerArr.value;
    }
    return containerArr.value.filter(packageData => {
        return packageData?.hbl_number.toLowerCase().includes(searchQuery.value.toLowerCase());
    });
})

const filteredMHBLPackages = computed(() => {
    if (!searchQuery.value) {
        return mhblContainerArr.value;
    }
    return mhblContainerArr.value.filter(packageData => {
        return packageData?.hbl_number.toLowerCase().includes(searchQuery.value.toLowerCase());
    });
})


const handleUnloadToWarehouse = (groupIndex, packageIndex) => {
    if (groupIndex !== -1 && packageIndex !== -1) {
        const packageToMove = containerArr.value[groupIndex].packages.splice(packageIndex, 1)[0];
        warehouseArr.value.push(packageToMove);
        // If the group is empty after removal, remove the group
        if (containerArr.value[groupIndex].packages.length === 0) {
            containerArr.value.splice(groupIndex, 1);
        }
        handleCreateDraftUnload([packageToMove]);
    }
}

const handleUnloadMHBLToWarehouse = (unloadMhblReference) => {
    const mhblToUnload = mhblContainerArr.value.find(mhbl => mhbl.mhblReference === unloadMhblReference);
    mhblToUnload.packages.forEach(pkg => {
        handleCreateDraftUnload([pkg]);
    });
    mhblContainerArr.value = mhblContainerArr.value.filter(mhbl => mhbl.mhblReference !== mhblToUnload.mhblReference);
    console.log(mhblContainerArr.value);
    warehouseMHBLArr.value.push(mhblToUnload);
}

const handleReloadMHBLToContainer = (loadMhblReference) => {
    const mhblToReload = warehouseMHBLArr.value.find(mhbl => mhbl.mhblReference === loadMhblReference);
    mhblToReload.packages.forEach(pkg => {
        handleRemoveDraftUnload([pkg]);
    });
    mhblContainerArr.value.push(mhblToReload);
    warehouseMHBLArr.value = warehouseMHBLArr.value.filter(mhbl => mhbl.mhblReference !== mhblToReload.mhblReference);
}
const handleReLoadToContainer = (index) => {
    if (index !== -1) {
        const packageToMove = warehouseArr.value.splice(index, 1)[0];
        const group = containerArr.value.find(g => g.hbl_number === packageToMove.hbl.hbl_number);
        if (group) {
            group.packages.push(packageToMove);
        } else {
            containerArr.value.push({
                hbl_number: packageToMove.hbl.hbl_number,
                expanded: true,
                packages: [packageToMove]
            });
        }
        handleRemoveDraftUnload([packageToMove]);
    }
}

const showReviewModal = ref(false);

const handlePackageChange = () => {
    containerArr.value = [...containerArr.value];
    warehouseArr.value = [...warehouseArr.value];
}

const draftTextEnabled = ref(false);

const handleCreateDraftUnload = (packages) => {
    router.post(route("arrival.unload-container.unload"), {
            container_id: route().params.container,
            packages,
            is_draft: true,
        },
        {
            onSuccess: () => {
                draftTextEnabled.value = true;
                setTimeout(() => draftTextEnabled.value = false, 3000);
            },
            onError: () => {
                console.error('Something went to wrong!');
            },
            preserveScroll: true,
            preserveState: true,
        });
}

const handleRemoveDraftUnload = (packages) => {
    router.post(route("arrival.unload-container.reload"), {
            container_id: route().params.container,
            package_id: packages[0].id,
        },
        {
            onSuccess: () => {
                draftTextEnabled.value = true;
                setTimeout(() => draftTextEnabled.value = false, 3000);
            },
            onError: () => {
                console.error('Something went to wrong!');
            },
            preserveScroll: true,
            preserveState: true,
        });
}

const showUnloadingIssueModal = ref(false);

const hblPackageId = ref(null);

const confirmShowCreateIssueModal = (index) => {
    hblPackageId.value = warehouseArr.value[index].id;
    showUnloadingIssueModal.value = true;
}

const confirmShowMHBLCreateIssueModal = (packageID) => {
    hblPackageId.value = packageID;
    showUnloadingIssueModal.value = true;
}

const reviewContainer = () => {
    showReviewModal.value = true
}
</script>

<template>
    <AppLayout title="Unloading Point">
        <template #header>Unloading Point</template>

        <main class="kanban-app w-full">
            <div
                class="flex items-center justify-between space-x-2 px-[var(--margin-x)] py-5 transition-all duration-[.25s]">
                <div class="flex items-center space-x-1">
                    <h3 class="text-lg font-medium text-slate-700 line-clamp-1 dark:text-navy-50">
                        Unloading Point
                    </h3>
                </div>
                <div class="flex space-x-5 items-center">
                    <ActionMessage :on="draftTextEnabled">
                        <div class="flex">
                            <svg class="size-5 mr-2 icon icon-tabler icons-tabler-outline icon-tabler-file-report"
                                 fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                 stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                                <path d="M17 17m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0"/>
                                <path d="M17 13v4h4"/>
                                <path d="M12 3v4a1 1 0 0 0 1 1h4"/>
                                <path d="M11.5 21h-6.5a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v2m0 3v4"/>
                            </svg>
                            Saved as draft.
                        </div>
                    </ActionMessage>
                    <Button :disabled="warehouseArr.length === 0 && warehouseMHBLArr.length === 0"
                            icon="pi pi-arrow-right" icon-pos="right" label="Proceed to Review"
                            size="small" @click.prevent="reviewContainer"/>
                </div>
            </div>

            <div class="px-[var(--margin-x)] mb-3">
                <label class="relative hidden w-full max-w-[16rem] sm:flex">
                    <input
                        v-model="searchQuery"
                        class="form-input peer h-8 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 pl-9 text-xs+ placeholder:text-slate-400/70 hover:z-10 hover:border-slate-400 dark:border-navy-450 dark:hover:border-navy-400 focus:ring-0 disabled:pointer-events-none disabled:select-none disabled:border-none disabled:bg-zinc-100"
                        placeholder="Search on HBL Packages" type="text"/>
                    <span
                        class="pointer-events-none absolute flex h-full w-9 items-center justify-center text-slate-400 peer-focus:text-primary dark:text-navy-300 dark:peer-focus:text-accent">
                    <svg class="size-4 transition-colors duration-200" fill="currentColor"
                         viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M3.316 13.781l.73-.171-.73.171zm0-5.457l.73.171-.73-.171zm15.473 0l.73-.171-.73.171zm0 5.457l.73.171-.73-.171zm-5.008 5.008l-.171-.73.171.73zm-5.457 0l-.171.73.171-.73zm0-15.473l-.171-.73.171.73zm5.457 0l.171-.73-.171.73zM20.47 21.53a.75.75 0 101.06-1.06l-1.06 1.06zM4.046 13.61a11.198 11.198 0 010-5.115l-1.46-.342a12.698 12.698 0 000 5.8l1.46-.343zm14.013-5.115a11.196 11.196 0 010 5.115l1.46.342a12.698 12.698 0 000-5.8l-1.46.343zm-4.45 9.564a11.196 11.196 0 01-5.114 0l-.342 1.46c1.907.448 3.892.448 5.8 0l-.343-1.46zM8.496 4.046a11.198 11.198 0 015.115 0l.342-1.46a12.698 12.698 0 00-5.8 0l.343 1.46zm0 14.013a5.97 5.97 0 01-4.45-4.45l-1.46.343a7.47 7.47 0 005.568 5.568l.342-1.46zm5.457 1.46a7.47 7.47 0 005.568-5.567l-1.46-.342a5.97 5.97 0 01-4.45 4.45l.342 1.46zM13.61 4.046a5.97 5.97 0 014.45 4.45l1.46-.343a7.47 7.47 0 00-5.568-5.567l-.342 1.46zm-5.457-1.46a7.47 7.47 0 00-5.567 5.567l1.46.342a5.97 5.97 0 014.45-4.45l-.343-1.46zm8.652 15.28l3.665 3.664 1.06-1.06-3.665-3.665-1.06 1.06z"/>
                    </svg>
                </span>
                </label>
            </div>

            <div class="flex h-[calc(100vh-8.5rem)] flex-grow flex-col">
                <div
                    class="kanban-scrollbar grid grid-cols-1 sm:grid-cols-2 space-x-4 overflow-x-auto overflow-y-auto px-[var(--margin-x)] transition-all duration-[.25s]">
                    <div class="relative flex max-h-full shrink-0 flex-col">
                        <div class="board-draggable-handler flex items-center justify-between px-0.5 pb-3">
                            <div class="flex items-center space-x-2">
                                <div
                                    class="flex size-8 items-center justify-center rounded-lg bg-warning/10 text-warning">
                                    <svg
                                        class="icon icon-tabler icons-tabler-outline icon-tabler-tir"
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
                                        <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                                        <path d="M5 17m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/>
                                        <path d="M17 17m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/>
                                        <path d="M7 18h8m4 0h2v-6a5 7 0 0 0 -5 -7h-1l1.5 7h4.5"/>
                                        <path d="M12 18v-13h3"/>
                                        <path d="M3 17l0 -5l9 0"/>
                                    </svg>
                                </div>
                                <h3 class="text-base text-slate-700 dark:text-navy-100">
                                    {{ container.cargo_type }} Container ({{ container?.reference }})
                                </h3>
                            </div>
                            <div>
                                <h3 class="text-base text-slate-700 dark:text-navy-100">
                                    {{ container.container_type }}
                                </h3>
                            </div>
                        </div>
                        <div>
                            <ul v-if="Object.keys(filteredPackages).length > 0"
                                class="space-y-1 font-inter font-medium">
                                <li v-for="(hbl, groupIndex) in filteredPackages" :key="hbl.id">
                                    <div
                                        v-if="Object.keys(hbl.packages).length > 0"
                                        class="flex cursor-pointer items-center rounded px-2 py-1 tracking-wide text-slate-800 outline-none transition-all hover:bg-slate-100 hover:text-slate-800 focus:bg-slate-100 focus:text-slate-800 dark:text-navy-100 dark:hover:bg-navy-600 dark:hover:text-navy-100 dark:focus:bg-navy-600 dark:focus:text-navy-100"
                                        tabindex="0"
                                    >
                                        <button
                                            class="btn mr-1 size-5 rounded-lg p-0 hover:bg-slate-300/20 focus:bg-slate-300/20 active:bg-slate-300/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25"
                                            @click="hbl.expanded = !hbl.expanded"
                                        >
                                            <svg
                                                :class="hbl.expanded && 'rotate-90'"
                                                class="size-7 transition-transform"
                                                fill="currentColor"
                                                viewBox="0 0 20 20"
                                                xmlns="http://www.w3.org/2000/svg"
                                            >
                                                <path
                                                    clip-rule="evenodd"
                                                    d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                                    fill-rule="evenodd"
                                                ></path>
                                            </svg>
                                        </button>
                                        <svg
                                            class="mr-3 size-9 text-primary"
                                            fill="currentColor"
                                            viewBox="0 0 20 20"
                                            xmlns="http://www.w3.org/2000/svg"
                                        >
                                            <path
                                                d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"
                                            ></path>
                                        </svg>
                                        <span>{{ hbl.hbl_number }}</span>
                                    </div>
                                    <ul v-show="hbl.expanded" class="pl-4">
                                        <draggable v-model="hbl.packages"
                                                   class="is-scrollbar-hidden relative space-y-2.5 overflow-y-auto p-0.5"
                                                   group="people"
                                                   item-key="id"
                                                   @change="handlePackageChange">
                                            <template #item="{element, index}">
                                                <div class="card cursor-pointer shadow-sm">
                                                    <div class="flex justify-between items-center">
                                                        <div class="space-y-3 rounded-lg px-2.5 pb-2 pt-1.5">
                                                            <div>
                                                                <div class="flex justify-between">
                                                                    <p class="font-medium tracking-wide text-lg text-slate-600 dark:text-navy-100">
                                                                    </p>
                                                                </div>
                                                            </div>
                                                            <div class="flex flex-wrap gap-1">
                                                                <div
                                                                    class="badge space-x-1 bg-slate-150 py-1 px-1.5 text-slate-800 dark:bg-navy-500 dark:text-navy-100">
                                                                    <svg class="size-3.5" fill="none"
                                                                         stroke="currentColor"
                                                                         viewBox="0 0 24 24"
                                                                         xmlns="http://www.w3.org/2000/svg">
                                                                        <path
                                                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                                                                            stroke-linecap="round"
                                                                            stroke-linejoin="round"
                                                                            stroke-width="2"/>
                                                                    </svg>
                                                                    <span>{{
                                                                            moment(element.created_at).format('YYYY-MM-DD')
                                                                        }}</span>
                                                                </div>

                                                                <div
                                                                    class="badge space-x-1 bg-warning/10 py-1 px-1.5 text-warning dark:bg-warning/15">
                                                                    <svg
                                                                        class="size-4 icon icon-tabler icons-tabler-outline icon-tabler-scale"
                                                                        fill="none"
                                                                        stroke="currentColor" stroke-linecap="round"
                                                                        stroke-linejoin="round" stroke-width="2"
                                                                        viewBox="0 0 24 24"
                                                                        width="24"
                                                                        xmlns="http://www.w3.org/2000/svg">
                                                                        <path d="M0 0h24v24H0z" fill="none"
                                                                              stroke="none"/>
                                                                        <path d="M7 20l10 0"/>
                                                                        <path d="M6 6l6 -1l6 1"/>
                                                                        <path d="M12 3l0 17"/>
                                                                        <path d="M9 12l-3 -6l-3 6a3 3 0 0 0 6 0"/>
                                                                        <path d="M21 12l-3 -6l-3 6a3 3 0 0 0 6 0"/>
                                                                    </svg>
                                                                    <span>Volume {{ element.volume }}</span>
                                                                </div>

                                                                <div
                                                                    class="badge space-x-1 bg-error/10 py-1 px-1.5 text-error dark:bg-error/15">
                                                                    <svg
                                                                        class="size-4 icon icon-tabler icons-tabler-outline icon-tabler-weight"
                                                                        fill="none" height="24" stroke="currentColor"
                                                                        stroke-linecap="round"
                                                                        stroke-linejoin="round" stroke-width="2"
                                                                        viewBox="0 0 24 24"
                                                                        width="24"
                                                                        xmlns="http://www.w3.org/2000/svg">
                                                                        <path d="M0 0h24v24H0z" fill="none"
                                                                              stroke="none"/>
                                                                        <path
                                                                            d="M12 6m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0"/>
                                                                        <path
                                                                            d="M6.835 9h10.33a1 1 0 0 1 .984 .821l1.637 9a1 1 0 0 1 -.984 1.179h-13.604a1 1 0 0 1 -.984 -1.179l1.637 -9a1 1 0 0 1 .984 -.821z"/>
                                                                    </svg>
                                                                    <span>Weight {{ element.weight }}</span>
                                                                </div>

                                                                <div
                                                                    class="badge space-x-1 bg-success/10 py-1 px-1.5 text-success dark:bg-success/15">
                                                                    <svg
                                                                        class="size-4 icon icon-tabler icons-tabler-outline icon-tabler-hash"
                                                                        fill="none" stroke="currentColor"
                                                                        stroke-linecap="round" stroke-linejoin="round"
                                                                        stroke-width="2"
                                                                        viewBox="0 0 24 24"
                                                                        xmlns="http://www.w3.org/2000/svg">
                                                                        <path d="M0 0h24v24H0z" fill="none"
                                                                              stroke="none"/>
                                                                        <path d="M5 9l14 0"/>
                                                                        <path d="M5 15l14 0"/>
                                                                        <path d="M11 4l-4 16"/>
                                                                        <path d="M17 4l-4 16"/>
                                                                    </svg>
                                                                    <span>Quantity {{ element.quantity }}</span>
                                                                </div>
                                                            </div>
                                                            <p class="mt-px font-medium text-slate-400 dark:text-navy-300">
                                                                {{ element.package_type }}
                                                            </p>
                                                        </div>
                                                        <div class="px-2.5">
                                                            <svg
                                                                class="icon icon-tabler icons-tabler-outline icon-tabler-corner-up-right-double hover:text-success"
                                                                fill="none" height="24" stroke="currentColor"
                                                                stroke-linecap="round"
                                                                stroke-linejoin="round" stroke-width="2"
                                                                viewBox="0 0 24 24"
                                                                width="24"
                                                                x-tooltip.placement.top.success="'Click to Unload'"
                                                                xmlns="http://www.w3.org/2000/svg"
                                                                @click.prevent="handleUnloadToWarehouse(groupIndex, index)">
                                                                <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                                                                <path d="M4 18v-6a3 3 0 0 1 3 -3h7"/>
                                                                <path d="M10 13l4 -4l-4 -4m5 8l4 -4l-4 -4"/>
                                                            </svg>
                                                        </div>
                                                    </div>
                                                </div>
                                            </template>
                                        </draggable>
                                    </ul>
                                </li>
                            </ul>

                            <div v-else
                                 class="cursor-pointer border-2 border-error/20 bg-error/10 rounded-lg border-dashed">
                                <div class="flex justify-center items-center space-x-3 px-2.5 pb-2 pt-1.5 h-24">
                                    <div class="text-center">
                                        <p
                                            class="font-medium text-lg tracking-wide text-slate-400 line-clamp-2 dark:text-navy-100">
                                            Sorry! Not Found HBL Packages.
                                        </p>

                                        <p class="mt-px text-xs text-slate-400 dark:text-navy-300">
                                            Please add HBL records first.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="board-draggable-handler flex items-center justify-between px-0.5 pb-3">
                            <div class="flex items-center space-x-2">
                                <div class="flex size-8 items-center justify-center rounded-lg bg-info/10 text-info">
                                    <i class="fa fa-boxes-packing text-base"></i>
                                </div>
                                <h3 class="text-base text-slate-700 dark:text-navy-100">
                                    MHBL Packages
                                </h3>
                            </div>
                        </div>

                        <div>
                            <ul v-if="Object.keys(filteredMHBLPackages).length > 0"
                                class="space-y-1 font-inter font-medium">
                                <li v-for="(pkg, groupIndex) in filteredMHBLPackages" :key="pkg.mhblReference">
                                    <div
                                        v-if="Object.keys(pkg.packages).length > 0"
                                        class="flex cursor-pointer items-center rounded px-2 py-1 tracking-wide text-slate-800 outline-none transition-all hover:bg-slate-100 hover:text-slate-800 focus:bg-slate-100 focus:text-slate-800 dark:text-navy-100 dark:hover:bg-navy-600 dark:hover:text-navy-100 dark:focus:bg-navy-600 dark:focus:text-navy-100"
                                        tabindex="0"
                                    >
                                        <button
                                            class="btn mr-1 size-5 rounded-lg p-0 hover:bg-slate-300/20 focus:bg-slate-300/20 active:bg-slate-300/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25"
                                            @click="pkg.expanded = !pkg.expanded"
                                        >
                                            <svg
                                                :class="pkg.expanded && 'rotate-90'"
                                                class="size-7 transition-transform"
                                                fill="currentColor"
                                                viewBox="0 0 20 20"
                                                xmlns="http://www.w3.org/2000/svg"
                                            >
                                                <path
                                                    clip-rule="evenodd"
                                                    d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                                    fill-rule="evenodd"
                                                ></path>
                                            </svg>
                                        </button>
                                        <svg
                                            class="mr-3 size-9 text-primary"
                                            fill="currentColor"
                                            viewBox="0 0 20 20"
                                            xmlns="http://www.w3.org/2000/svg"
                                        >
                                            <path
                                                d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"
                                            ></path>
                                        </svg>
                                        <span>{{ pkg.packages[0].hbl.mhbl.hbl_number || pkg.mhblReference }}</span>
                                        <div class="px-2.5">
                                            <svg
                                                class="icon icon-tabler icons-tabler-outline icon-tabler-corner-up-right-double hover:text-success"
                                                fill="none" height="24" stroke="currentColor"
                                                stroke-linecap="round"
                                                stroke-linejoin="round" stroke-width="2"
                                                viewBox="0 0 24 24"
                                                width="24"
                                                x-tooltip.placement.top.success="'Click to Unload'"
                                                xmlns="http://www.w3.org/2000/svg"
                                                @click.prevent="handleUnloadMHBLToWarehouse(pkg.mhblReference)">
                                                <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                                                <path d="M4 18v-6a3 3 0 0 1 3 -3h7"/>
                                                <path d="M10 13l4 -4l-4 -4m5 8l4 -4l-4 -4"/>
                                            </svg>
                                        </div>

                                    </div>
                                    <ul v-show="pkg.expanded" class="pl-4">
                                        <div :packages="pkg.packages"
                                             class="is-scrollbar-hidden relative space-y-2.5 overflow-y-auto p-0.5"
                                        >
                                            <div v-for="(element, index) in pkg.packages" :key="element.id">
                                                <div class="card cursor-pointer shadow-sm">
                                                    <div class="flex justify-between items-center">
                                                        <div class="space-y-3 rounded-lg px-2.5 pb-2 pt-1.5">
                                                            <div>
                                                                <div class="flex justify-between">
                                                                    <p class="font-medium tracking-wide text-lg text-slate-600 dark:text-navy-100">
                                                                        {{ element.hbl.hbl_number }}
                                                                    </p>
                                                                </div>
                                                            </div>
                                                            <div class="flex flex-wrap gap-1">
                                                                <div
                                                                    class="badge space-x-1 bg-slate-150 py-1 px-1.5 text-slate-800 dark:bg-navy-500 dark:text-navy-100">
                                                                    <svg class="size-3.5" fill="none"
                                                                         stroke="currentColor"
                                                                         viewBox="0 0 24 24"
                                                                         xmlns="http://www.w3.org/2000/svg">
                                                                        <path
                                                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                                                                            stroke-linecap="round"
                                                                            stroke-linejoin="round"
                                                                            stroke-width="2"/>
                                                                    </svg>
                                                                    <span>{{
                                                                            moment(element.created_at).format('YYYY-MM-DD')
                                                                        }}</span>
                                                                </div>

                                                                <div
                                                                    class="badge space-x-1 bg-warning/10 py-1 px-1.5 text-warning dark:bg-warning/15">
                                                                    <svg
                                                                        class="size-4 icon icon-tabler icons-tabler-outline icon-tabler-scale"
                                                                        fill="none"
                                                                        stroke="currentColor" stroke-linecap="round"
                                                                        stroke-linejoin="round" stroke-width="2"
                                                                        viewBox="0 0 24 24"
                                                                        width="24"
                                                                        xmlns="http://www.w3.org/2000/svg">
                                                                        <path d="M0 0h24v24H0z" fill="none"
                                                                              stroke="none"/>
                                                                        <path d="M7 20l10 0"/>
                                                                        <path d="M6 6l6 -1l6 1"/>
                                                                        <path d="M12 3l0 17"/>
                                                                        <path d="M9 12l-3 -6l-3 6a3 3 0 0 0 6 0"/>
                                                                        <path d="M21 12l-3 -6l-3 6a3 3 0 0 0 6 0"/>
                                                                    </svg>
                                                                    <span>Volume {{ element.volume }}</span>
                                                                </div>

                                                                <div
                                                                    class="badge space-x-1 bg-error/10 py-1 px-1.5 text-error dark:bg-error/15">
                                                                    <svg
                                                                        class="size-4 icon icon-tabler icons-tabler-outline icon-tabler-weight"
                                                                        fill="none" height="24" stroke="currentColor"
                                                                        stroke-linecap="round"
                                                                        stroke-linejoin="round" stroke-width="2"
                                                                        viewBox="0 0 24 24"
                                                                        width="24"
                                                                        xmlns="http://www.w3.org/2000/svg">
                                                                        <path d="M0 0h24v24H0z" fill="none"
                                                                              stroke="none"/>
                                                                        <path
                                                                            d="M12 6m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0"/>
                                                                        <path
                                                                            d="M6.835 9h10.33a1 1 0 0 1 .984 .821l1.637 9a1 1 0 0 1 -.984 1.179h-13.604a1 1 0 0 1 -.984 -1.179l1.637 -9a1 1 0 0 1 .984 -.821z"/>
                                                                    </svg>
                                                                    <span>Weight {{ element.weight }}</span>
                                                                </div>

                                                                <div
                                                                    class="badge space-x-1 bg-success/10 py-1 px-1.5 text-success dark:bg-success/15">
                                                                    <svg
                                                                        class="size-4 icon icon-tabler icons-tabler-outline icon-tabler-hash"
                                                                        fill="none" stroke="currentColor"
                                                                        stroke-linecap="round" stroke-linejoin="round"
                                                                        stroke-width="2"
                                                                        viewBox="0 0 24 24"
                                                                        xmlns="http://www.w3.org/2000/svg">
                                                                        <path d="M0 0h24v24H0z" fill="none"
                                                                              stroke="none"/>
                                                                        <path d="M5 9l14 0"/>
                                                                        <path d="M5 15l14 0"/>
                                                                        <path d="M11 4l-4 16"/>
                                                                        <path d="M17 4l-4 16"/>
                                                                    </svg>
                                                                    <span>Quantity {{ element.quantity }}</span>
                                                                </div>
                                                            </div>
                                                            <p class="mt-px font-medium text-slate-400 dark:text-navy-300">
                                                                {{ element.package_type }}
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </ul>
                                </li>
                            </ul>

                            <div v-else
                                 class="cursor-pointer border-2 border-error/20 bg-error/10 rounded-lg border-dashed">
                                <div class="flex justify-center items-center space-x-3 px-2.5 pb-2 pt-1.5 h-24">
                                    <div class="text-center">
                                        <p
                                            class="font-medium text-lg tracking-wide text-slate-400 line-clamp-2 dark:text-navy-100">
                                            Sorry! Not Found MHBL Packages.
                                        </p>

                                        <p class="mt-px text-xs text-slate-400 dark:text-navy-300">
                                            Please add HBL records first.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="board-draggable relative flex max-h-full shrink-0 flex-col">
                        <div class="board-draggable-handler flex items-center justify-between px-0.5 pb-3">
                            <div class="flex items-center space-x-2">
                                <div
                                    class="flex size-8 items-center justify-center rounded-lg bg-success/10 text-success">
                                    <svg
                                        class="icon icon-tabler icons-tabler-outline icon-tabler-building-warehouse"
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
                                        <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                                        <path d="M3 21v-13l9 -4l9 4v13"/>
                                        <path d="M13 13h4v8h-10v-6h6"/>
                                        <path d="M13 21v-9a1 1 0 0 0 -1 -1h-2a1 1 0 0 0 -1 1v3"/>
                                    </svg>
                                </div>
                                <h3 class="text-base text-slate-700 dark:text-navy-100">
                                    Warehouse
                                </h3>
                            </div>
                        </div>
                        <div class="is-scrollbar-hidden relative space-y-2.5 overflow-y-auto p-0.5">
                            <draggable
                                v-if="warehouseArr.length > 0"
                                v-model="warehouseArr"
                                class="is-scrollbar-hidden relative space-y-2.5 overflow-y-auto p-0.5"
                                group="people"
                                item-key="id"
                                @change="handlePackageChange"
                            >
                                <template #item="{element, index}">
                                    <div class="card cursor-pointer shadow-sm">
                                        <div class="flex justify-between items-center">
                                            <div class="space-y-3 rounded-lg px-2.5 pb-2 pt-1.5">
                                                <div>
                                                    <div class="flex items-center">
                                                        <svg v-show="element.unloading_issue.length > 0"
                                                             class="icon icon-tabler icons-tabler-outline icon-tabler-alert-triangle text-warning mr-2"
                                                             fill="none" height="24"
                                                             stroke="currentColor" stroke-linecap="round"
                                                             stroke-linejoin="round"
                                                             stroke-width="2" viewBox="0 0 24 24" width="24"
                                                             xmlns="http://www.w3.org/2000/svg">
                                                            <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                                                            <path d="M12 9v4"/>
                                                            <path
                                                                d="M10.363 3.591l-8.106 13.534a1.914 1.914 0 0 0 1.636 2.871h16.214a1.914 1.914 0 0 0 1.636 -2.87l-8.106 -13.536a1.914 1.914 0 0 0 -3.274 0z"/>
                                                            <path d="M12 16h.01"/>
                                                        </svg>

                                                        <p class="font-medium text-lg tracking-wide text-slate-600 dark:text-navy-100">
                                                            {{ element.hbl?.hbl_number }}
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="flex flex-wrap gap-1">
                                                    <div
                                                        class="badge space-x-1 bg-slate-150 py-1 px-1.5 text-slate-800 dark:bg-navy-500 dark:text-navy-100">
                                                        <svg class="size-3.5" fill="none" stroke="currentColor"
                                                             viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                            <path
                                                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                                                                stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"/>
                                                        </svg>
                                                        <span>{{
                                                                moment(element.created_at).format('YYYY-MM-DD')
                                                            }}</span>
                                                    </div>

                                                    <div
                                                        class="badge space-x-1 bg-warning/10 py-1 px-1.5 text-warning dark:bg-warning/15">
                                                        <svg
                                                            class="size-4 icon icon-tabler icons-tabler-outline icon-tabler-scale"
                                                            fill="none"
                                                            stroke="currentColor" stroke-linecap="round"
                                                            stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24"
                                                            width="24"
                                                            xmlns="http://www.w3.org/2000/svg">
                                                            <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                                                            <path d="M7 20l10 0"/>
                                                            <path d="M6 6l6 -1l6 1"/>
                                                            <path d="M12 3l0 17"/>
                                                            <path d="M9 12l-3 -6l-3 6a3 3 0 0 0 6 0"/>
                                                            <path d="M21 12l-3 -6l-3 6a3 3 0 0 0 6 0"/>
                                                        </svg>
                                                        <span>Volume {{ element.volume }}</span>
                                                    </div>

                                                    <div
                                                        class="badge space-x-1 bg-error/10 py-1 px-1.5 text-error dark:bg-error/15">
                                                        <svg
                                                            class="size-4 icon icon-tabler icons-tabler-outline icon-tabler-weight"
                                                            fill="none" height="24" stroke="currentColor"
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24"
                                                            width="24"
                                                            xmlns="http://www.w3.org/2000/svg">
                                                            <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                                                            <path d="M12 6m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0"/>
                                                            <path
                                                                d="M6.835 9h10.33a1 1 0 0 1 .984 .821l1.637 9a1 1 0 0 1 -.984 1.179h-13.604a1 1 0 0 1 -.984 -1.179l1.637 -9a1 1 0 0 1 .984 -.821z"/>
                                                        </svg>
                                                        <span>Weight {{ element.weight }}</span>
                                                    </div>

                                                    <div
                                                        class="badge space-x-1 bg-success/10 py-1 px-1.5 text-success dark:bg-success/15">
                                                        <svg
                                                            class="size-4 icon icon-tabler icons-tabler-outline icon-tabler-hash"
                                                            fill="none" stroke="currentColor"
                                                            stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            viewBox="0 0 24 24"
                                                            xmlns="http://www.w3.org/2000/svg">
                                                            <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                                                            <path d="M5 9l14 0"/>
                                                            <path d="M5 15l14 0"/>
                                                            <path d="M11 4l-4 16"/>
                                                            <path d="M17 4l-4 16"/>
                                                        </svg>
                                                        <span>Quantity {{ element.quantity }}</span>
                                                    </div>
                                                </div>
                                                <p class="mt-px font-medium text-slate-400 dark:text-navy-300">
                                                    {{ element.package_type }}
                                                </p>
                                            </div>
                                            <div class="flex items-center space-x-8 px-2.5">
                                                <Button :disabled="element.unloading_issue.length > 0"
                                                        icon="pi pi-exclamation-triangle" label="Create Unloading Issue"
                                                        severity="warn" size="small"
                                                        @click.prevent="confirmShowCreateIssueModal(index)"/>

                                                <Button v-tooltip.left="'Click to Re-Load'" aria-label="Filter"
                                                        icon="ti ti-corner-up-left-double text-2xl" rounded
                                                        severity="danger" text
                                                        @click.prevent="handleReLoadToContainer(index)"/>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </draggable>

                            <ul v-if="warehouseMHBLArr.length > 0" class="space-y-1 font-inter font-medium">
                                <li v-for="(mhbl, groupIndex) in warehouseMHBLArr" :key="mhbl.mhblReference">
                                    <div
                                        v-if="mhbl.packages.length > 0"
                                        class="flex cursor-pointer items-center rounded px-2 py-1 tracking-wide text-slate-800 outline-none transition-all hover:bg-slate-100 hover:text-slate-800 focus:bg-slate-100 focus:text-slate-800 dark:text-navy-100 dark:hover:bg-navy-600 dark:hover:text-navy-100 dark:focus:bg-navy-600 dark:focus:text-navy-100"
                                        tabindex="0"
                                    >
                                        <button
                                            class="btn mr-1 size-5 rounded-lg p-0 hover:bg-slate-300/20 focus:bg-slate-300/20 active:bg-slate-300/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25"
                                            @click="mhbl.expanded = !mhbl.expanded"
                                        >
                                            <svg
                                                :class="mhbl.expanded && 'rotate-90'"
                                                class="size-7 transition-transform"
                                                fill="currentColor"
                                                viewBox="0 0 20 20"
                                                xmlns="http://www.w3.org/2000/svg"
                                            >
                                                <path
                                                    clip-rule="evenodd"
                                                    d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                                    fill-rule="evenodd"
                                                ></path>
                                            </svg>
                                        </button>
                                        <svg
                                            class="mr-3 size-9 text-primary"
                                            fill="currentColor"
                                            viewBox="0 0 20 20"
                                            xmlns="http://www.w3.org/2000/svg"
                                        >
                                            <path
                                                d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"
                                            ></path>
                                        </svg>
                                        <span>{{ mhbl.packages[0].hbl.mhbl.hbl_number || mhbl.mhblReference }}</span>
                                        <div class="px-2.5">
                                            <svg
                                                class=" hover:text-error icon icon-tabler icons-tabler-outline icon-tabler-corner-up-left-double"
                                                fill="none" height="24" stroke="currentColor" stroke-linecap="round"
                                                stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24"
                                                width="24"
                                                x-tooltip.placement.top.error="'Click to Re-Load'"
                                                xmlns="http://www.w3.org/2000/svg"
                                                @click.prevent="handleReloadMHBLToContainer(mhbl.mhblReference)">
                                                <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                                                <path d="M19 18v-6a3 3 0 0 0 -3 -3h-7"/>
                                                <path d="M13 13l-4 -4l4 -4m-5 8l-4 -4l4 -4"/>
                                            </svg>
                                        </div>

                                    </div>
                                    <ul v-show="mhbl.expanded" class="pl-4">
                                        <div :packages="mhbl.packages"
                                             class="is-scrollbar-hidden relative space-y-2.5 overflow-y-auto p-0.5"
                                        >
                                            <div v-for="(element, index) in mhbl.packages" :key="element.id">
                                                <div class="card cursor-pointer shadow-sm">
                                                    <div class="flex justify-between items-center">
                                                        <div class="space-y-3 rounded-lg px-2.5 pb-2 pt-1.5">
                                                            <div>
                                                                <div class="flex justify-between">
                                                                    <p class="font-medium tracking-wide text-lg text-slate-600 dark:text-navy-100">
                                                                        {{ element.hbl.hbl_number }}
                                                                    </p>
                                                                </div>
                                                            </div>
                                                            <div class="flex flex-wrap gap-1">
                                                                <div
                                                                    class="badge space-x-1 bg-slate-150 py-1 px-1.5 text-slate-800 dark:bg-navy-500 dark:text-navy-100">
                                                                    <svg class="size-3.5" fill="none"
                                                                         stroke="currentColor"
                                                                         viewBox="0 0 24 24"
                                                                         xmlns="http://www.w3.org/2000/svg">
                                                                        <path
                                                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                                                                            stroke-linecap="round"
                                                                            stroke-linejoin="round"
                                                                            stroke-width="2"/>
                                                                    </svg>
                                                                    <span>{{
                                                                            moment(element.created_at).format('YYYY-MM-DD')
                                                                        }}</span>
                                                                </div>

                                                                <div
                                                                    class="badge space-x-1 bg-warning/10 py-1 px-1.5 text-warning dark:bg-warning/15">
                                                                    <svg
                                                                        class="size-4 icon icon-tabler icons-tabler-outline icon-tabler-scale"
                                                                        fill="none"
                                                                        stroke="currentColor" stroke-linecap="round"
                                                                        stroke-linejoin="round" stroke-width="2"
                                                                        viewBox="0 0 24 24"
                                                                        width="24"
                                                                        xmlns="http://www.w3.org/2000/svg">
                                                                        <path d="M0 0h24v24H0z" fill="none"
                                                                              stroke="none"/>
                                                                        <path d="M7 20l10 0"/>
                                                                        <path d="M6 6l6 -1l6 1"/>
                                                                        <path d="M12 3l0 17"/>
                                                                        <path d="M9 12l-3 -6l-3 6a3 3 0 0 0 6 0"/>
                                                                        <path d="M21 12l-3 -6l-3 6a3 3 0 0 0 6 0"/>
                                                                    </svg>
                                                                    <span>Volume {{ element.volume }}</span>
                                                                </div>

                                                                <div
                                                                    class="badge space-x-1 bg-error/10 py-1 px-1.5 text-error dark:bg-error/15">
                                                                    <svg
                                                                        class="size-4 icon icon-tabler icons-tabler-outline icon-tabler-weight"
                                                                        fill="none" height="24" stroke="currentColor"
                                                                        stroke-linecap="round"
                                                                        stroke-linejoin="round" stroke-width="2"
                                                                        viewBox="0 0 24 24"
                                                                        width="24"
                                                                        xmlns="http://www.w3.org/2000/svg">
                                                                        <path d="M0 0h24v24H0z" fill="none"
                                                                              stroke="none"/>
                                                                        <path
                                                                            d="M12 6m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0"/>
                                                                        <path
                                                                            d="M6.835 9h10.33a1 1 0 0 1 .984 .821l1.637 9a1 1 0 0 1 -.984 1.179h-13.604a1 1 0 0 1 -.984 -1.179l1.637 -9a1 1 0 0 1 .984 -.821z"/>
                                                                    </svg>
                                                                    <span>Weight {{ element.weight }}</span>
                                                                </div>

                                                                <div
                                                                    class="badge space-x-1 bg-success/10 py-1 px-1.5 text-success dark:bg-success/15">
                                                                    <svg
                                                                        class="size-4 icon icon-tabler icons-tabler-outline icon-tabler-hash"
                                                                        fill="none" stroke="currentColor"
                                                                        stroke-linecap="round" stroke-linejoin="round"
                                                                        stroke-width="2"
                                                                        viewBox="0 0 24 24"
                                                                        xmlns="http://www.w3.org/2000/svg">
                                                                        <path d="M0 0h24v24H0z" fill="none"
                                                                              stroke="none"/>
                                                                        <path d="M5 9l14 0"/>
                                                                        <path d="M5 15l14 0"/>
                                                                        <path d="M11 4l-4 16"/>
                                                                        <path d="M17 4l-4 16"/>
                                                                    </svg>
                                                                    <span>Quantity {{ element.quantity }}</span>
                                                                </div>
                                                            </div>
                                                            <p class="mt-px font-medium text-slate-400 dark:text-navy-300">
                                                                {{ element.package_type }}
                                                            </p>
                                                        </div>
                                                        <div class="flex items-center space-x-8 px-2.5">
                                                            <Button :disabled="element.unloading_issue.length > 0"
                                                                    icon="pi pi-exclamation-triangle"
                                                                    label="Create Unloading Issue"
                                                                    severity="warn" size="small"
                                                                    @click.prevent="confirmShowMHBLCreateIssueModal(element.id)"/>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </ul>
                                </li>
                            </ul>

                            <div v-if="warehouseArr.length === 0 && warehouseMHBLArr.length === 0"
                                 class="cursor-pointer border-2 rounded-lg border-dashed">
                                <div class="flex justify-center items-center space-x-3 px-2.5 pb-2 pt-1.5 h-24">
                                    <div class="text-center">
                                        <p
                                            class="font-medium text-lg tracking-wide text-slate-400 line-clamp-2 dark:text-navy-100">
                                            Warehouse
                                        </p>

                                        <p class="mt-px text-xs text-slate-400 dark:text-navy-300">
                                            Active to unloading process
                                        </p>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </main>

        <ReviewModal
            :visible="showReviewModal"
            :warehouse-array="warehouseArr"
            :warehouseMHBLs="warehouseMHBLArr"
            @close="showReviewModal = false"
            @update:visible="showReviewModal = $event"
        />

        <CreateUnloadingIssueModal :hbl-package-id="hblPackageId" :visible="showUnloadingIssueModal"
                                   @close="showUnloadingIssueModal = false"
                                   @update:visible="showUnloadingIssueModal = $event"/>
    </AppLayout>
</template>

<style scoped>

</style>
