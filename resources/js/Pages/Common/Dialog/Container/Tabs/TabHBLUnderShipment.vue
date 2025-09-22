<script setup>
import SimpleOverviewWidget from "@/Components/Widgets/SimpleOverviewWidget.vue";
import {ref, watch} from "vue";
import HBLPackages from "@/Pages/Common/Dialog/Container/Tables/HBLPackages.vue";
import AddHBLModal from "@/Pages/Common/Dialog/Container/Dialog/AddHBLModal.vue";
import {usePage} from "@inertiajs/vue3";
import Button from "primevue/button";
import Divider from "primevue/divider";

const props = defineProps({
    container: {
        type: Object,
        default: () => {
        },
    }
});

const showConfirmAddHBLModal = ref(false);

const confirmAddHBLModal = () => {
    showConfirmAddHBLModal.value = true;
};

const closeModal = () => {
    showConfirmAddHBLModal.value = false;
    fetchLoadedContainer();
}

const filteredHBLS = ref([]);
const containerData = ref({});
const hblsCount = ref(0)
const filteredHBLSPackagesCount = ref(0);
const filteredHBLSPackagesWeight = ref(0);
const filteredHBLSPackagesVolume = ref(0);
const containerStatusFlags = ref({
    has_short_load: false,
    has_unmanifest: false,
    has_overland: false
});

const fetchLoadedContainer = async () => {
    try {
        const response = await fetch(`/loaded-containers/get-container/${props.container.id}`, {
            method: "GET",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": usePage().props.csrf,
            },
        });
        if (!response.ok) {
            throw new Error(`Failed to fetch container ${props.container.id}`);
        }
        containerData.value = (await response.json())[0];
    } catch (error) {
        console.error("Error:", error);
    }
};

fetchLoadedContainer();

const hbls = () => {
    const hbls = containerData.value.hbls;
    filteredHBLS.value = Object.values(hbls).filter(hbl => hbl.mhbl === null);

    const filteredHblIds = filteredHBLS.value.map(hbl => hbl.id);

    const filteredHblPackages = props.container.hbl_packages.filter(pkg =>
        filteredHblIds.includes(pkg.hbl_id)
    );

    filteredHBLSPackagesCount.value = filteredHblPackages.length;

    filteredHBLSPackagesWeight.value = filteredHblPackages.reduce((sum, pkg) => {
        return sum + (pkg.actual_weight || 0);
    }, 0);

    filteredHBLSPackagesVolume.value = filteredHblPackages.reduce((sum, pkg) => {
        return sum + (pkg.volume || 0);
    }, 0);

    // Calculate container status flags based on HBLs
    updateContainerStatusFlags();
}

const updateContainerStatusFlags = () => {
    const allHBLs = containerData.value.hbls ? Object.values(containerData.value.hbls) : [];
    
    containerStatusFlags.value = {
        has_short_load: allHBLs.some(hbl => hbl.is_short_load),
        has_unmanifest: allHBLs.some(hbl => hbl.is_unmanifest),
        has_overland: allHBLs.some(hbl => hbl.is_overland)
    };
}

watch(() => containerData.value, () => {
    hbls();
    mhbls();
});

const filteredMHBLS = ref([]);
const filteredMHBLsLHBL = ref([]);

const mhbls = () => {
    const hbls = Object.values(containerData.value.hbls);
    filteredMHBLsLHBL.value = Object.values(hbls).filter(hbl => hbl.mhbl !== null);

    const filteredMHblsHBLIds = filteredMHBLsLHBL.value.map(hbl => hbl.id);
    const filteredMHblsHBLPackages = props.container.hbl_packages.filter(pkg =>
        filteredMHblsHBLIds.includes(pkg.hbl_id)
    );

    //Get packages Count
    filteredHBLSPackagesCount.value = filteredHBLSPackagesCount.value + filteredMHblsHBLPackages.length;

    filteredHBLSPackagesWeight.value = filteredHBLSPackagesWeight.value + filteredMHblsHBLPackages.reduce((sum, pkg) => {
        return sum + (pkg.actual_weight || 0);
    }, 0);

    filteredHBLSPackagesVolume.value = filteredHBLSPackagesVolume.value + filteredMHblsHBLPackages.reduce((sum, pkg) => {
        return sum + (pkg.volume || 0);
    }, 0);

    const mhblMap = {};

    hbls.forEach(hbl => {
        if (hbl.mhbl !== null) {
            const mhblId = hbl.mhbl.id;
            if (!mhblMap[mhblId]) {
                mhblMap[mhblId] = {
                    ...hbl.mhbl,
                    hbls: []
                };
            }
            mhblMap[mhblId].hbls.push(hbl);
        }
    });
    filteredMHBLS.value = mhblMap;

}

watch(
    [
        () => filteredHBLS.value,
        () => filteredMHBLsLHBL.value
    ],
    ([filteredHBLS, filteredMHBLsLHBL]) => {
        const flattenedMHBLs = filteredMHBLsLHBL.flat();
        const uniqueMHBLs = new Set(flattenedMHBLs.map(item => item.mhbl?.id));
        const uniqueMHBLCount = uniqueMHBLs.size;

        hblsCount.value = filteredHBLS.length + uniqueMHBLCount;
    }
);
</script>

<template>
    <div
        class="mt-3 flex flex-col items-center justify-between space-y-2 text-center sm:flex-row sm:space-y-0 sm:text-left">
        <div>
            <div class="flex items-center space-x-2 text-xs text-slate-400">
                <div class="flex items-center">
                    <i class="ti ti-plane-departure text-xl mr-2"></i>
                    {{ container.branch.name }}
                </div>
                <div>
                    <i class="ti ti-arrow-narrow-right text-xl"></i>
                </div>
                <div class="flex items-center">
                    <i class="ti ti-plane-arrival text-xl mr-2"></i>
                    {{ container.warehouse.name }}
                </div>
            </div>
            <div class="flex items-center gap-3">
                <h3 class="text-2xl font-semibold text-slate-700 dark:text-navy-100">
                    {{ container.reference }}
                </h3>
                <div class="flex gap-2">
                    <i v-if="containerStatusFlags.has_short_load" v-tooltip="'Container has Short Load HBLs'" class="ti ti-truck-loading text-xl text-orange-500"></i>
                    <i v-if="containerStatusFlags.has_unmanifest" v-tooltip="'Container has Unmanifest HBLs'" class="ti ti-file-x text-xl text-purple-500"></i>
                    <i v-if="containerStatusFlags.has_overland" v-tooltip="'Container has Overland HBLs'" class="ti ti-road text-xl text-blue-500"></i>
                </div>
            </div>
        </div>
        <div class="flex items-center space-x-2">
            <template v-if="container.status !== 'IN TRANSIT'">
                <Button v-if="usePage().props.user?.roles[0] === 'admin'" icon="pi pi-plus"
                        label="Add HBL To Shipment" size="small" @click.prevent="confirmAddHBLModal"/>
            </template>

            <a :href="route('loading.hbls.batch-downloads', container.id)">
                <Button icon="pi pi-print" label="Print All HBL" severity="info" size="small"/>
            </a>
        </div>
    </div>

    <Divider />

    <div class="flex gap-3 my-3">
        <SimpleOverviewWidget :count="hblsCount || 0" class="bg-slate-100" title="HBLs">
            <i class="ti ti-app-window text-emerald-500 text-3xl"></i>
        </SimpleOverviewWidget>

        <SimpleOverviewWidget :count="filteredHBLSPackagesCount || 0" class="bg-slate-100" title="Packages">
            <i class="ti ti-packages text-emerald-500 text-3xl"></i>
        </SimpleOverviewWidget>

        <SimpleOverviewWidget :count="filteredHBLSPackagesWeight.toFixed(2)" class="bg-slate-100" title="Weight">
            <i class="ti ti-weight text-emerald-500 text-3xl"></i>
        </SimpleOverviewWidget>

        <SimpleOverviewWidget :count="filteredHBLSPackagesVolume.toFixed(3)" class="bg-slate-100" title="Volume">
            <i class="ti ti-scale text-emerald-500 text-3xl"></i>
        </SimpleOverviewWidget>
    </div>

    <HBLPackages :container="container" :containerHBLS="filteredHBLS" :containerMHBLS="filteredMHBLS"
                 :filteredMHBLsLHBL="filteredMHBLsLHBL" @fetContainerData="fetchLoadedContainer"/>

    <AddHBLModal :container="container" :visible="showConfirmAddHBLModal"
                 @close="closeModal"
                 @update:visible="showConfirmAddHBLModal = $event"/>
</template>
