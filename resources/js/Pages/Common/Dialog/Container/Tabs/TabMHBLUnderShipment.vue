<script setup>
import SimpleOverviewWidget from "@/Components/Widgets/SimpleOverviewWidget.vue";
import {ref, watch} from "vue";
import {usePage} from "@inertiajs/vue3";
import Button from "primevue/button";
import MHBLPackages from "@/Pages/Common/Dialog/Container/Tables/MHBLPackages.vue";
import AddMHBLModal from "@/Pages/Common/Dialog/Container/Dialog/AddMHBLModal.vue";
import AddHBLModal from "@/Pages/Common/Dialog/Container/Dialog/AddHBLModal.vue";

const props = defineProps({
    container: {
        type: Object,
        default: () => {
        },
    }
});

const showConfirmAddMHBLModal = ref(false);

const confirmAddMHBLModal = () => {
    showConfirmAddMHBLModal.value = true;
};

const closeModal = () => {
    showConfirmAddMHBLModal.value = false;
}
const filteredHBLS = ref([]);
const containerData = ref({});
const hblsCount = ref(0)
const mhblsCount = ref(0);
const filteredHBLSPackagesCount = ref(0);
const filteredHBLSPackagesWeight = ref(0);
const filteredHBLSPackagesVolume = ref(0);

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
    }catch (error) {
        console.error("Error:", error);
    }
};
fetchLoadedContainer()
const hbls = () => {
    const hbls = containerData.value.hbls;
    filteredHBLS.value = Object.values(hbls).filter(hbl => hbl.mhbl !== null);
    hblsCount.value = filteredHBLS.value.length;

    const filteredHblIds = filteredHBLS.value.map(hbl => hbl.id);

    const filteredHblPackages = props.container.hbl_packages.filter(pkg =>
        filteredHblIds.includes(pkg.hbl_id)
    );

    filteredHBLSPackagesCount.value = filteredHblPackages.length;

    filteredHBLSPackagesWeight.value = filteredHblPackages.reduce((sum, pkg) => {
        return sum + (pkg.weight || 0);  // Ensure pkg.weight exists
    }, 0);

    filteredHBLSPackagesVolume.value = filteredHblPackages.reduce((sum, pkg) => {
        return sum + (pkg.volume || 0);  // Ensure pkg.weight exists
    }, 0);
    //counting mhbls
    const mhblSet = new Set(filteredHBLS.value.map(hbl => hbl.mhbl));
    mhblsCount.value = mhblSet.size;
}

watch(() => containerData.value, () => {
    hbls();
});
// hbls();
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
            <h3 class="text-2xl font-semibold text-slate-700 dark:text-navy-100">
                {{ container.reference }}
            </h3>
        </div>
        <div class="flex items-center space-x-2">
            <Button :disabled="usePage().props.user?.roles[0] !== 'admin'" icon="pi pi-plus"
                    label="Add MHBL To Shipment" size="small" @click.prevent="confirmAddMHBLModal" />

            <a :href="route('loading.loaded-containers.doorToDoor.export', container.id)">
                <Button v-if="filteredHBLSPackagesCount > 0" icon="pi pi-print"
                        label="Print Manifest" severity="info" size="small"/>
            </a>
        </div>
    </div>

    <div class="flex gap-3 my-3">
        <SimpleOverviewWidget :count="hblsCount || 0" class="bg-slate-100" title="HBLs">
            <i class="ti ti-app-window text-emerald-500 text-3xl"></i>
        </SimpleOverviewWidget>

        <SimpleOverviewWidget :count="mhblsCount || 0" class="bg-slate-100" title="MHBLs">
            <i class="ti ti-door text-emerald-500 text-3xl"></i>
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

    <MHBLPackages :container="container" :containerHBLS="filteredHBLS"/>

    <AddMHBLModal :container="container"
                  :visible="showConfirmAddMHBLModal"
                  @close="closeModal"
                  @update:visible="showConfirmAddMHBLModal = $event"/>
</template>
