<script setup>
import {computed} from "vue";
import {router, useForm} from "@inertiajs/vue3";
import {push} from "notivue";
import Dialog from "primevue/dialog";
import Button from "primevue/button";
import Card from "primevue/card";
import Tag from 'primevue/tag';
import IftaLabel from "primevue/iftalabel";
import InputText from "primevue/inputtext";

const props = defineProps({
    visible: {
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

const emit = defineEmits(["update:visible"]);

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

const handleDownLoadTallySheet = () => {
    window.location.href = route("loading.containers.tally-sheet-downloads", form.container_id);
}

const handleDownLoadTallySheetExcel = () => {
    window.location.href = route("loading.containers.tally-sheet-excel-downloads", form.container_id);
}

const getMHBLPackageCount = (hbls) => {
    return hbls.reduce((total, hbl) => {
        return total + (hbl.packages ? hbl.packages.length : 0);
    }, 0);
}

const handleCreateLoadedContainer = (printTallySheet) => {
    form.post(route("loading.loaded-containers.store"), {
        onSuccess: () => {
            push.success('Container loaded successfully!');
            emit('close');
            if(printTallySheet){
                window.location.href = route("loading.containers.tally-sheet-downloads", form.container_id);
            }
            router.visit(route("loading.loaded-containers.index"));
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
    <Dialog :style="{ width: '50rem' }" :visible="visible" header="Shipping Tally Sheet" modal
            @update:visible="(newValue) => $emit('update:visible', newValue)">

        <div class="grid grid-cols-2 my-3">
            <div>
                <div v-if="route().params.cargoType === 'Sea Cargo'" class="flex items-center space-x-2 text-primary">
                    <i class="ti ti-ship text-2xl"></i>
                    <span>Sea Cargo</span>
                </div>

                <div v-if="route().params.cargoType === 'Air Cargo'" class="flex items-center text-warning space-x-2">
                    <i class="ti ti-plane text-2xl"></i>
                    <span>Air Cargo</span>
                </div>
            </div>

            <IftaLabel>
                <InputText v-model="form.note" class="w-full" placeholder="Type Somethings..." variant="filled" />
                <label for="description">Note</label>
            </IftaLabel>
        </div>

        <div class="grid grid-cols-3 gap-4 mb-4">
            <Card
                v-for="(packageData, index) in uniqueContainerArray"
                :key="index"
                class="!shadow-md !border rounded-2xl bg-white"
            >
                <template #content>
                    <div class="flex items-center space-x-4">
                        <div class="flex items-center justify-center w-12 h-12 bg-info/10 text-info rounded-full">
                            <i class="ti ti-box text-2xl"></i>
                        </div>
                        <div>
                            <div class="text-lg font-semibold text-gray-800">
                                {{ findHblByPackageId(packageData.id).hbl_number }}
                            </div>
                            <div class="text-sm text-success flex items-center gap-1 mt-1">
                                <i class="ti ti-packages"></i>
                                {{ countPackages(packageData.hbl_id) }} / {{ loadedHBLsPackages[packageData.hbl_id]?.length }} Packages
                            </div>
                            <Tag v-if="countPackages(packageData.hbl_id) < loadedHBLsPackages[packageData.hbl_id]?.length" class="mt-1" severity="warn" value="Short Loaded"></Tag>
                        </div>
                    </div>
                </template>
            </Card>
        </div>

        <div v-if="!isDestinationLoading && Object.keys(loadedMHBLs).length > 0" class="grid grid-cols-3 gap-4 mb-4">
            <Card
                v-for="(mhbl, index) in loadedMHBLs"
                :key="index"
                class="!shadow-md !border rounded-2xl bg-white"
            >
                <template #content>
                    <div class="flex items-center space-x-4">
                        <div class="flex items-center justify-center w-12 h-12 bg-warning/10 text-warning rounded-full">
                            <i class="ti ti-box text-2xl"></i>
                        </div>
                        <div>
                            <div class="text-lg font-semibold text-gray-800">
                                {{ mhbl.hbl_number || mhbl.reference }}
                            </div>
                            <div class="text-sm text-success flex items-center gap-1 mt-1">
                                <i class="ti ti-packages"></i>
                                {{ getMHBLPackageCount(mhbl.hbls) }} Packages
                            </div>
                        </div>
                    </div>
                </template>
            </Card>
        </div>

        <div class="flex justify-end space-x-2 mt-5">
            <Button label="Cancel" severity="secondary" size="small" type="button" @click="emit('close')"></Button>

            <Button icon="pi pi-download" label="Download Tally Sheet"
                    outlined
                    severity="info"
                    size="small"
                    type="button"
                    @click.prevent="handleDownLoadTallySheet"></Button>

            <Button icon="pi pi-file-excel" label="Download Tally Sheet (Excel)"
                    outlined
                    severity="success"
                    size="small"
                    type="button"
                    @click.prevent="handleDownLoadTallySheetExcel"></Button>

            <Button label="Finish Loading & Download Tally Sheet" outlined
                    severity="help"
                    size="small"
                    type="button"
                    @click.prevent="handleCreateLoadedContainer(true)"></Button>

            <Button icon="pi pi-check" label="Finish Loading"
                    size="small"
                    type="button"
                    @click.prevent="handleCreateLoadedContainer(false)"></Button>
        </div>
    </Dialog>
</template>
