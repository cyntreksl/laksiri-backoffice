<script setup>
import {computed, ref} from "vue";
import {router, useForm} from "@inertiajs/vue3";
import {push} from "notivue";
import Dialog from "primevue/dialog";
import Button from "primevue/button";
import Card from "primevue/card";
import Checkbox from "primevue/checkbox";
import axios from "axios";

const props = defineProps({
    visible: {
        type: Boolean,
        default: false,
    },
    warehouseArray: {
        type: Object,
        default: () => {
        },
    },
    warehouseMHBLs: {
        type: Object,
        default: () => {
        },
    }
});

const emit = defineEmits(["update:visible"]);

// Report modal state
const isReportModalVisible = ref(false);
const selectedHBL = ref(null);
const reportForm = ref({
    is_short_load: false,
    is_unmanifest: false,
    is_overland: false
});
const isSubmittingReport = ref(false);

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
    packages: [],
});

const handleFinishUnloading = () => {
    props.warehouseMHBLs.forEach(mhbl => {
        mhbl.packages.forEach(pkg => {
            form.packages.push(pkg);
        });
    });

    props.warehouseArray.forEach(pkg => {
        form.packages.push(pkg);
    });

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

const openReportModal = (hbl) => {
    selectedHBL.value = hbl;
    reportForm.value = {
        is_short_load: hbl.is_short_load || false,
        is_unmanifest: hbl.is_unmanifest || false,
        is_overland: hbl.is_overland || false
    };
    isReportModalVisible.value = true;
}

const closeReportModal = () => {
    isReportModalVisible.value = false;
    selectedHBL.value = null;
    reportForm.value = {
        is_short_load: false,
        is_unmanifest: false,
        is_overland: false
    };
}

const submitReport = async () => {
    if (!selectedHBL.value) return;

    isSubmittingReport.value = true;

    try {
        await axios.post(route('hbls.update-status', selectedHBL.value.id), reportForm.value);
        push.success('HBL status updated successfully!');
        closeReportModal();
    } catch (error) {
        console.error('Error updating HBL status:', error);
        push.error('Failed to update HBL status');
    } finally {
        isSubmittingReport.value = false;
    }
}
</script>

<template>
    <Dialog :style="{ width: '50rem' }" :visible="visible" header="Unloaded Summery" modal
            @update:visible="(newValue) => $emit('update:visible', newValue)">

        <div class="grid grid-cols-3 gap-4 mb-4">
            <Card
                v-for="(packageData, index) in uniqueContainerArray"
                :key="index"
                class="!shadow-md !border rounded-2xl bg-white"
            >
                <template #content>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <div class="flex items-center justify-center w-12 h-12 bg-info/10 text-info rounded-full">
                                <i class="ti ti-box text-2xl"></i>
                            </div>
                            <div>
                                <div class="text-lg font-semibold text-gray-800">
                                    {{ packageData.hbl.hbl_number }}
                                </div>
                                <div class="text-sm text-success flex items-center gap-1 mt-1">
                                    <i class="ti ti-packages"></i>
                                    {{ countPackages(packageData.hbl_id) }} Packages
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center">
                            <Button
                                v-tooltip="'Mark HBL Status'"
                                icon="ti ti-report"
                                severity="secondary"
                                size="small"
                                text
                                @click="openReportModal(packageData.hbl)"
                            />
                        </div>
                    </div>
                </template>
            </Card>
        </div>

        <div v-if="warehouseMHBLs.length > 0" class="grid grid-cols-3 gap-4">
            <Card v-for="(mhbl, index) in warehouseMHBLs" class="!shadow-md !border rounded-2xl bg-white">
                <template #content>
                    <div class="flex items-center space-x-4">
                        <div class="flex items-center justify-center w-12 h-12 bg-warning/10 text-warning rounded-full">
                            <i class="ti ti-box text-2xl"></i>
                        </div>
                        <div>
                            <div class="text-lg font-semibold text-gray-800">
                                {{ mhbl.packages[0].hbl.mhbl.hbl_number || mhbl.mhblReference }}
                            </div>
                            <div class="text-sm text-success flex items-center gap-1 mt-1">
                                <i class="ti ti-packages"></i>
                                {{ mhbl.packages.length }} Packages
                            </div>
                        </div>
                    </div>
                </template>
            </Card>
        </div>

        <div class="flex justify-end gap-2 mt-5">
            <Button label="Cancel" severity="secondary" type="button" @click="emit('close')"></Button>
            <Button :class="{ 'opacity-25': form.processing }" :disabled="form.processing" label="Finish Unload"
                    type="button"
                    @click="handleFinishUnloading"></Button>
        </div>
    </Dialog>

    <!-- Report Modal -->
    <Dialog v-model:visible="isReportModalVisible" :style="{ width: '30rem' }" header="Mark HBL Status" modal>
        <div v-if="selectedHBL" class="space-y-4">
            <div class="mb-4 p-3 bg-blue-50 rounded-lg">
                <p class="font-semibold text-blue-800">{{ selectedHBL.hbl_number }}</p>
                <p class="text-sm text-blue-600">Select the applicable status options for this HBL</p>
            </div>

            <div class="space-y-3">
                <div class="flex items-center">
                    <Checkbox
                        v-model="reportForm.is_short_load"
                        binary
                        inputId="shortLoad"
                    />
                    <label class="ml-2 text-sm font-medium text-gray-700" for="shortLoad">
                        Short Load
                    </label>
                </div>

                <div class="flex items-center">
                    <Checkbox
                        v-model="reportForm.is_unmanifest"
                        binary
                        inputId="unmanifest"
                    />
                    <label class="ml-2 text-sm font-medium text-gray-700" for="unmanifest">
                        Unmanifest
                    </label>
                </div>

                <div class="flex items-center">
                    <Checkbox
                        v-model="reportForm.is_overland"
                        binary
                        inputId="overland"
                    />
                    <label class="ml-2 text-sm font-medium text-gray-700" for="overland">
                        Overland
                    </label>
                </div>
            </div>
        </div>

        <template #footer>
            <div class="flex justify-end gap-2">
                <Button
                    :disabled="isSubmittingReport"
                    label="Cancel"
                    severity="secondary"
                    @click="closeReportModal"
                />
                <Button
                    :loading="isSubmittingReport"
                    label="Update Status"
                    @click="submitReport"
                />
            </div>
        </template>
    </Dialog>
</template>
