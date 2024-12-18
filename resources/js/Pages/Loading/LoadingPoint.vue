<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import moment from "moment";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import {computed, reactive, ref, watch} from "vue";
import draggable from 'vuedraggable'
import ReviewModal from "@/Pages/Loading/Partials/ReviewModal.vue";
import {router, usePage} from "@inertiajs/vue3";
import ActionMessage from "@/Components/ActionMessage.vue";
import RadioButton from "@/Components/RadioButton.vue";
import {forEach} from "vuedraggable/dist/vuedraggable.common.js";

const props = defineProps({
    container: {
        type: Object,
        default: () => {
        }
    },
    loadedHBLs: {
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
    }
})

const searchQuery = ref('');
const unloadedHBLs = ref([]);
const hblPackagesArr = ref([]);
const unloadedMHBLs = ref([]);
const loadedMHBLs = ref([]);
const mhblPackagesArr = ref([]);

const params = route().params;

const filters = reactive({
    cargoMode: '',
    hblType: '',
    warehouse: '',
});

watch(
    () => params.cargoType,
    (newCargoType) => {
        filters.cargoMode = newCargoType;
    },
    {immediate: true}
);

watch(
    () => params.hblType,
    (newHblType) => {
        if (newHblType) {
            filters.hblType = newHblType;
        }
    },
    {immediate: true}
);

watch(
    () => params.warehouse,
    (newWarehouse) => {
        if (newWarehouse) {
            filters.warehouse = newWarehouse;
        }
    },
    {immediate: true}
);

const getUnloadedHBLs = async () => {
    const response = await fetch(`/hbls/get-unloaded-hbl/list?cargoType=${filters.cargoMode}&hblType=${filters.hblType}&warehouse=${filters.warehouse}`, {
        method: "GET",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": usePage().props.csrf,
        },
    });

    if (!response.ok) {
        const errorData = await response.json();
        if (errorData.errors && errorData.errors.reference) {
            throw new Error(errorData.errors.reference[0]);
        } else {
            throw new Error('Network response was not ok.');
        }
    } else {
        const data = await response.json();
        unloadedHBLs.value = data.data;
    }
}

const getUnloadedMHBLs = async () => {
    const response = await fetch(`/mhbls/get-unloaded-mhbl/list?cargoType=${filters.cargoMode}&hblType=${filters.hblType}&warehouse=${filters.warehouse}`, {
        method: "GET",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": usePage().props.csrf,
        },
    });

    if (!response.ok) {
        const errorData = await response.json();
        if (errorData.errors && errorData.errors.reference) {
            throw new Error(errorData.errors.reference[0]);
        } else {
            throw new Error('Network response was not ok.');
        }
    } else {
        const data = await response.json();
        unloadedMHBLs.value = data.data;
    }
}

getUnloadedMHBLs();

const getLoadedMHBLs = async () => {
    const response = await fetch(`/mhbls/get-container-loaded-mhbl/list?container=${props.container.id}`, {
        method: "GET",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": usePage().props.csrf,
        },
    });
    if (!response.ok) {
        const errorData = await response.json();
        if (errorData.errors && errorData.errors.reference) {
            throw new Error(errorData.errors.reference[0]);
        } else {
            throw new Error('Network response was not ok.');
        }
    } else {
        const data = await response.json();
        loadedMHBLs.value = data.data;
    }
}

getLoadedMHBLs();
watch(() => filters.cargoMode, getUnloadedMHBLs);
watch(() => filters.hblType, getUnloadedMHBLs);
watch(() => filters.warehouse, getUnloadedMHBLs);

getUnloadedMHBLs();

watch(() => filters.cargoMode, getUnloadedHBLs);
watch(() => filters.hblType, getUnloadedHBLs);
watch(() => filters.warehouse, getUnloadedHBLs);

getUnloadedHBLs();

const filteredPackages = computed(() => {
    if (!searchQuery.value) {
        return unloadedHBLs.value;
    }
    return unloadedHBLs.value.filter(hbl => {
        return hbl?.hbl.toLowerCase().includes(searchQuery.value.toLowerCase());
    });
})

const filteredMHBLs = computed(() => {
    if (!searchQuery.value) {
        return unloadedMHBLs.value;
    }
    return unloadedMHBLs.value.filter(mhbl => {
        return mhbl?.reference.toLowerCase().includes(searchQuery.value.toLowerCase());
    });
})

const filteredLoadedMHBLs = computed(() => {
    return loadedMHBLs.value;
})

const containerArr = ref(props.loadedHBLs.flatMap(hbl => hbl.packages));

const handleLoad = (index, pkg_id) => {
    if (index !== -1) {
        const packageToLoad = hblPackagesArr.value.find(
            (pkg) => pkg.id === pkg_id
        );
        containerArr.value = [...containerArr.value, packageToLoad];
        const objectIndex = hblPackagesArr.value.findIndex(pkg => pkg.id === pkg_id);
        hblPackagesArr.value.splice(objectIndex, 1);

        const hblIndex = unloadedHBLs.value.findIndex(hbl => hbl.packages.some(p => p.id === packageToLoad.id));
        if (hblIndex !== -1) {
            const packageIndex = unloadedHBLs.value[hblIndex].packages.findIndex(p => p.id === packageToLoad.id);
            unloadedHBLs.value[hblIndex].packages.splice(packageIndex, 1);

            if (unloadedHBLs.value[hblIndex].packages.length === 0) {
                unloadedHBLs.value.splice(hblIndex, 1);
            }
        }
    }
}

const handleLoadMHBL = (mhbl_id, mhbl_hbls) => {
    const loadedPackages = unloadedMHBLs.value
        .filter(mhbl => mhbl.id === mhbl_id)[0].hbls
        .flatMap(hbl => hbl.packages);
    loadedMHBLs.value.push((unloadedMHBLs.value.filter(mhbl => mhbl.id === mhbl_id))[0]);
    unloadedMHBLs.value = unloadedMHBLs.value.filter(mhbl => mhbl.id !== mhbl_id);

    if(loadedPackages.length > 0){
        handleCreateDraftLoadedContainer(loadedPackages);
    }
    // loadedPackages.forEach(pkg => {
    //     handleCreateDraftLoadedContainer([pkg]);
    // });
}

const handleUnloadMHBL = (mhbl_id) => {
    const unLoadedPackages = loadedMHBLs.value
        .filter(mhbl => mhbl.id === mhbl_id)[0].hbls
        .flatMap(hbl => hbl.packages);

    unloadedMHBLs.value.push((loadedMHBLs.value.filter(mhbl => mhbl.id === mhbl_id))[0]);
    loadedMHBLs.value = loadedMHBLs.value.filter(mhbl => mhbl.id !== mhbl_id);

    if(unLoadedPackages.length > 0){
        unLoadedPackages.forEach(pkg => {
            handleRemoveDraftLoadedContainer([pkg]);
        });
    }
}

const handleUnload = (index) => {
    if (index !== -1) {
        const packageToUnload = containerArr.value[index];
        hblPackagesArr.value = [...hblPackagesArr.value, packageToUnload];
        containerArr.value = containerArr.value.filter((_, i) => i !== index);

        const hbl = findHblByPackageId(packageToUnload.id);
        const hblIndex = unloadedHBLs.value.findIndex(h => h.id === hbl.id);
        if (hblIndex !== -1) {
            unloadedHBLs.value[hblIndex].packages.push(packageToUnload);
        } else {
            unloadedHBLs.value.push({
                ...hbl,
                packages: [packageToUnload],
                expanded: false
            });
        }
    }
}

// find HBL by packageId
const findHblByPackageId = (packageId) => {
    // First, check if the package ID exists in any of the unloaded HBLs
    const packageExists = unloadedHBLs.value.some(hbl => hbl.packages.some(p => p.id === packageId));

    // If the package ID exists, find and return the corresponding HBL
    if (packageExists) {
        return unloadedHBLs.value.find(hbl => hbl.packages.some(p => p.id === packageId));
    } else {
        return props.loadedHBLs.find(hbl => hbl.packages.some(p => p.id === packageId));
    }
}

const showReviewModal = ref(false);

const handlePackageChange = () => {
    containerArr.value = [...containerArr.value];
    hblPackagesArr.value = [...hblPackagesArr.value];
}

const draftTextEnabled = ref(false);

const handleCreateDraftLoadedContainer = (packages) => {
    router.post(route("loading.loaded-containers.store"), {
            container_id: route().params.container,
            cargo_type: route().params.cargoType,
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

const handleRemoveDraftLoadedContainer = (packages) => {
    router.post(route("loading.loaded-containers.remove"), {
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

// Watch for changes in the container array
watch(containerArr, (newValue, oldValue) => {
    const added = newValue.filter(item => !oldValue.includes(item));
    const removed = oldValue.filter(item => !newValue.includes(item));

    if (added.length > 0) {
        handleCreateDraftLoadedContainer(added);
    }
    if (removed.length > 0) {
        handleRemoveDraftLoadedContainer(removed);
    }
});

watch(unloadedHBLs, (newVal) => {
    newVal.forEach(hbl => {
        if (!hbl.hasOwnProperty('expanded')) {
            hbl.expanded = true;
        }
    });
    hblPackagesArr.value = newVal.flatMap(hbl => hbl.packages);
});

watch(unloadedMHBLs, (newVal) => {
    newVal.forEach(mhbl => {
        if (!mhbl.hasOwnProperty('expanded')) {
            mhbl.expanded = true;
        }
    });
    mhblPackagesArr.value = newVal.flatMap(mhbl =>
        mhbl.hbls.flatMap(hbl => hbl.packages)
    );
});
</script>

<template>
    <AppLayout title="Loading Points">
        <template #header>Loading Points</template>

        <main class="kanban-app w-full">
            <div
                class="flex items-center justify-between space-x-2 px-[var(--margin-x)] py-5 transition-all duration-[.25s]">
                <div class="flex items-center space-x-1">
                    <h3 class="text-lg font-medium text-slate-700 line-clamp-1 dark:text-navy-50">
                        Loading Point
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
                    <PrimaryButton :disabled="containerArr.length === 0" @click.prevent="showReviewModal = true">
                        Proceed to Review
                    </PrimaryButton>
                </div>
            </div>

            <div class="px-[var(--margin-x)]">
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

            <div class="flex flex-wrap space-x-10 items-center p-4 my-4 rounded bg-white border border-indigo-400 mx-[var(--margin-x)]">
                <div class="flex space-x-4 bg-green-100 p-5 rounded-lg">
                    <label
                        v-for="cargoType in cargoTypes"
                        :key="cargoType"
                        class="flex space-x-2 items-center"
                    >
                        <RadioButton
                            v-model="filters.cargoMode"
                            :label="cargoType"
                            name="cargoType"
                            :value="cargoType"
                        />
                        <svg
                            v-if="cargoType === 'Air Cargo'"
                            class="icon icon-tabler icons-tabler-outline icon-tabler-plane"
                            fill="none"
                            height="15"
                            stroke="currentColor"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            viewBox="0 0 24 24"
                            width="15"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                            <path
                                d="M16 10h4a2 2 0 0 1 0 4h-4l-4 7h-3l2 -7h-4l-2 2h-3l2 -4l-2 -4h3l2 2h4l-2 -7h3z"/>
                        </svg>
                        <svg
                            v-else
                            class="icon icon-tabler icons-tabler-outline icon-tabler-ship mr-2"
                            fill="none"
                            height="15"
                            stroke="currentColor"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            viewBox="0 0 24 24"
                            width="15"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                            <path
                                d="M2 20a2.4 2.4 0 0 0 2 1a2.4 2.4 0 0 0 2 -1a2.4 2.4 0 0 1 2 -1a2.4 2.4 0 0 1 2 1a2.4 2.4 0 0 0 2 1a2.4 2.4 0 0 0 2 -1a2.4 2.4 0 0 1 2 -1a2.4 2.4 0 0 1 2 1a2.4 2.4 0 0 0 2 1a2.4 2.4 0 0 0 2 -1"/>
                            <path d="M4 18l-1 -5h18l-2 4"/>
                            <path d="M5 13v-6h8l4 6"/>
                            <path d="M7 7v-4h-1"/>
                        </svg>
                    </label>
                </div>
                <div class="flex space-x-4 bg-blue-100 p-5 rounded-lg">
                    <label
                        v-for="hblType in hblTypes"
                        :key="hblType"
                        class="flex space-x-2 items-center"
                    >
                        <RadioButton
                            v-model="filters.hblType"
                            :label="hblType"
                            :value="hblType"
                            name="hblType"
                        />
                    </label>

                    <div v-if="filters.hblType" class="flex size-8 items-center justify-center rounded-lg bg-error/10 text-error hover:bg-error/40 hover:cursor-pointer" @click.prevent="filters.hblType = ''">
                        <i class="fa fa-times-circle text-base"></i>
                    </div>
                </div>
                <div class="flex space-x-4 bg-amber-100 p-5 rounded-lg">
                    <label
                        v-for="warehouse in warehouses"
                        :key="warehouse"
                        class="flex space-x-2 items-center"
                    >
                        <RadioButton
                            v-model="filters.warehouse"
                            :label="warehouse"
                            :value="warehouse"
                            name="warehouse"
                        />
                    </label>

                    <div v-if="filters.warehouse" class="flex size-8 items-center justify-center rounded-lg bg-error/10 text-error hover:bg-error/40 hover:cursor-pointer" @click.prevent="filters.warehouse = ''">
                        <i class="fa fa-times-circle text-base"></i>
                    </div>
                </div>
            </div>

            <div class="flex h-[calc(100vh-8.5rem)] flex-grow flex-col">
                <div
                    class="kanban-scrollbar grid grid-cols-1 sm:grid-cols-2 space-x-4 overflow-x-auto overflow-y-auto px-[var(--margin-x)] transition-all duration-[.25s]">
                    <div class="relative flex max-h-full shrink-0 flex-col">
                        <div class="board-draggable-handler flex items-center justify-between px-0.5 pb-3">
                            <div class="flex items-center space-x-2">
                                <div class="flex size-8 items-center justify-center rounded-lg bg-info/10 text-info">
                                    <i class="fa fa-boxes-packing text-base"></i>
                                </div>
                                <h3 class="text-base text-slate-700 dark:text-navy-100">
                                    HBL Packages
                                </h3>
                            </div>
                        </div>
                        <div>
                            <ul v-if="Object.keys(filteredPackages).length > 0" class="space-y-1 font-inter font-medium">
                                <li v-for="hbl in filteredPackages" :key="hbl.id">
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
                                        <span>{{ hbl?.hbl_number || hbl.hbl }}</span>
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
                                                                        {{ findHblByPackageId(element.id)?.hbl_number || findHblByPackageId(element.id).hbl }}
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
                                                                    <span>Volume {{ element.volume.toFixed(3) }}</span>
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
                                                                    <span>Weight {{ element.weight.toFixed(2) }}</span>
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
                                                        <div class="px-2.5">
                                                            <svg
                                                                class="icon icon-tabler icons-tabler-outline icon-tabler-corner-up-right-double hover:text-success"
                                                                fill="none" height="24" stroke="currentColor" stroke-linecap="round"
                                                                stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24"
                                                                width="24"
                                                                x-tooltip.placement.top.success="'Click to Load'"
                                                                xmlns="http://www.w3.org/2000/svg"
                                                                @click.prevent="handleLoad(index, element.id)">
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
                            <ul v-if="Object.keys(filteredMHBLs).length > 0" class="space-y-1 font-inter font-medium">
                                <li v-for="mhbl in filteredMHBLs" :key="mhbl.id">
                                    <div
                                        v-if="Object.keys(mhbl.hbls).length > 0"
                                        class="flex cursor-pointer items-center rounded px-2 py-1 tracking-wide text-slate-800 outline-none transition-all hover:bg-slate-100 hover:text-slate-800 focus:bg-slate-100 focus:text-slate-800 dark:text-navy-100 dark:hover:bg-navy-600 dark:hover:text-navy-100 dark:focus:bg-navy-600 dark:focus:text-navy-100"
                                        tabindex="0"
                                    >
                                        <button
                                            class="btn mr-1 size-5 rounded-lg p-0 hover:bg-slate-300/20 focus:bg-slate-300/20 active:bg-slate-300/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25"
                                            @click="mhbl.expanded = !mhbl.expanded"
                                        >
                                            <svg
                                                :class="'rotate-90'"
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
                                        <span>{{ mhbl.reference }}</span>
                                        <div class="px-2.5">
                                            <svg
                                                class="icon icon-tabler icons-tabler-outline icon-tabler-corner-up-right-double hover:text-success"
                                                fill="none" height="24" stroke="currentColor" stroke-linecap="round"
                                                stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24"
                                                width="24"
                                                x-tooltip.placement.top.success="'Click to Load'"
                                                xmlns="http://www.w3.org/2000/svg"
                                                @click.prevent="handleLoadMHBL(mhbl.id,mhbl.hbls)">
                                                <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                                                <path d="M4 18v-6a3 3 0 0 1 3 -3h7"/>
                                                <path d="M10 13l4 -4l-4 -4m5 8l4 -4l-4 -4"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <ul v-show="mhbl.expanded" class="pl-4">
                                        <div v-for="hbl in mhbl.hbls" :key="hbl.id">
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
                                                                            {{hbl.hbl_number}}
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
                                                                        <span>Volume {{ element.volume.toFixed(3) }}</span>
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
                                                                        <span>Weight {{ element.weight.toFixed(2) }}</span>
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

                                                        </div>
                                                    </div>
                                                </template>
                                            </draggable>
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
                                    class="flex size-8 items-center justify-center rounded-lg bg-warning/10 text-warning">
                                    <i class="fa fa-boxes-alt text-base"></i>
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
                        <div class="is-scrollbar-hidden relative space-y-2.5 overflow-y-auto p-0.5">
                            <p>111</p>
                            <draggable
                                v-if="containerArr.length > 0"
                                v-model="containerArr" @change="handlePackageChange"
                                class="is-scrollbar-hidden relative space-y-2.5 overflow-y-auto p-0.5"
                                group="people"
                                item-key="id"
                            >
                                <template #item="{element, index}">
                                    <div class="card cursor-pointer shadow-sm">
                                        <div class="flex justify-between items-center">
                                            <div class="space-y-3 rounded-lg px-2.5 pb-2 pt-1.5">
                                                <div>
                                                    <div class="flex justify-between">
                                                        <p class="font-medium text-lg tracking-wide text-slate-600 dark:text-navy-100">
                                                            {{ findHblByPackageId(element.id)?.hbl_number || findHblByPackageId(element.id)?.hbl }}
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
                                                        <span>Volume {{ element.volume.toFixed(3) }}</span>
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
                                                        <span>Weight {{ element.weight.toFixed(2) }}</span>
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
                                            <div class="px-2.5">
                                                <svg
                                                    class=" hover:text-error icon icon-tabler icons-tabler-outline icon-tabler-corner-up-left-double"
                                                    fill="none" height="24" stroke="currentColor" stroke-linecap="round"
                                                    stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24"
                                                    width="24"
                                                    x-tooltip.placement.top.error="'Click to Unload'"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    @click.prevent="handleUnload(index)">
                                                    <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                                                    <path d="M19 18v-6a3 3 0 0 0 -3 -3h-7"/>
                                                    <path d="M13 13l-4 -4l4 -4m-5 8l-4 -4l4 -4"/>
                                                </svg>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </draggable>
                            <div>
                                <ul v-if="Object.keys(loadedMHBLs).length > 0" class="space-y-1 font-inter font-medium">
                                    <li v-for="mhbl in loadedMHBLs" :key="mhbl.id">
                                        <div
                                            v-if="mhbl.hbls.length > 0"
                                            class="flex cursor-pointer items-center rounded px-2 py-1 tracking-wide text-slate-800 outline-none transition-all hover:bg-slate-100 hover:text-slate-800 focus:bg-slate-100 focus:text-slate-800 dark:text-navy-100 dark:hover:bg-navy-600 dark:hover:text-navy-100 dark:focus:bg-navy-600 dark:focus:text-navy-100"
                                            tabindex="0"
                                        >
                                            <button
                                                class="btn mr-1 size-5 rounded-lg p-0 hover:bg-slate-300/20 focus:bg-slate-300/20 active:bg-slate-300/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25"
                                                @click="mhbl.expanded = !mhbl.expanded"
                                            >
                                                <svg
                                                    :class="'rotate-90'"
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
                                            <span>{{ mhbl.reference }}</span>
                                            <div class="px-2.5">
                                                <svg
                                                    class=" hover:text-error icon icon-tabler icons-tabler-outline icon-tabler-corner-up-left-double"
                                                    fill="none" height="24" stroke="currentColor" stroke-linecap="round"
                                                    stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24"
                                                    width="24"
                                                    x-tooltip.placement.top.error="'Click to Unload'"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    @click.prevent="handleUnloadMHBL(mhbl.id)">
                                                    <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                                                    <path d="M19 18v-6a3 3 0 0 0 -3 -3h-7"/>
                                                    <path d="M13 13l-4 -4l4 -4m-5 8l-4 -4l4 -4"/>
                                                </svg>
                                            </div>
                                        </div>
                                        <ul v-show="mhbl.expanded" class="pl-4">
                                            <div v-for="hbl in mhbl.hbls" :key="hbl.id">
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
                                                                                {{hbl.hbl_number}}
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
                                                                            <span>Volume {{ element.volume.toFixed(3) }}</span>
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
                                                                            <span>Weight {{ element.weight.toFixed(2) }}</span>
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

                                                            </div>
                                                        </div>
                                                    </template>
                                                </draggable>
                                            </div>

                                        </ul>
                                    </li>
                                </ul>
                            </div>
                            <div v-if="containerArr.length === 0"
                                 class="cursor-pointer border-2 rounded-lg border-dashed">
                                <div class="flex justify-center items-center space-x-3 px-2.5 pb-2 pt-1.5 h-24">
                                    <div class="text-center">
                                        <p
                                            class="font-medium text-lg tracking-wide text-slate-400 line-clamp-2 dark:text-navy-100">
                                            {{ container.container_type }} Empty Container
                                        </p>

                                        <p class="mt-px text-xs text-slate-400 dark:text-navy-300">
                                            Active to loading process
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
        <ReviewModal :container-array="containerArr" :find-hbl-by-package-id="findHblByPackageId"
                     :show="showReviewModal" @close="showReviewModal = false"/>
    </AppLayout>
</template>
