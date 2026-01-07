<script setup>
import {computed, ref} from "vue";
import {router, useForm} from "@inertiajs/vue3";
import {push} from "notivue";
import Dialog from "primevue/dialog";
import Button from "primevue/button";
import Card from "primevue/card";
import Checkbox from "primevue/checkbox";
import InputText from "primevue/inputtext";
import IconField from "primevue/iconfield";
import InputIcon from "primevue/inputicon";
import Badge from "primevue/badge";
import Divider from "primevue/divider";
import axios from "axios";

const props = defineProps({
    visible: {
        type: Boolean,
        default: false,
    },
    container: {
        type: Object,
        default: () => {
        },
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
    },
    packagesWithoutMhbl: {
        type: Array,
        default: () => [],
    },
    packagesWithMhbl: {
        type: Array,
        default: () => [],
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

const searchQuery = ref('');

const countPackages = (packageHBlId) => {
    return props.warehouseArray.filter(item => item.hbl_id === packageHBlId).length;
}

const getPackagesForHBL = (hblId) => {
    return props.warehouseArray.filter(item => item.hbl_id === hblId);
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

const filteredHBLs = computed(() => {
    if (!searchQuery.value.trim()) {
        return uniqueContainerArray.value;
    }
    const query = searchQuery.value.toLowerCase();
    return uniqueContainerArray.value.filter(item => {
        return item.hbl.hbl_number?.toLowerCase().includes(query) ||
               item.hbl.consignee_name?.toLowerCase().includes(query) ||
               item.hbl.consignee_contact?.toLowerCase().includes(query);
    });
});

const filteredMHBLs = computed(() => {
    if (!searchQuery.value.trim()) {
        return props.warehouseMHBLs;
    }
    const query = searchQuery.value.toLowerCase();
    return props.warehouseMHBLs.filter(mhbl => {
        const mhblNumber = mhbl.packages[0]?.hbl?.mhbl?.hbl_number || mhbl.mhblReference || '';
        return mhblNumber.toLowerCase().includes(query);
    });
});

// Get all unique HBLs from container (original packages loaded in container)
const allContainerHBLs = computed(() => {
    const seen = new Set();
    const hbls = [];
    
    // Get unique HBLs from packages without MHBL
    if (props.packagesWithoutMhbl && props.packagesWithoutMhbl.length > 0) {
        props.packagesWithoutMhbl.forEach(pkg => {
            const hblId = pkg.hbl?.id;
            if (hblId && !seen.has(hblId)) {
                seen.add(hblId);
                hbls.push({
                    id: hblId,
                    hbl_number: pkg.hbl.hbl_number,
                    type: 'hbl'
                });
            }
        });
    }
    
    return hbls;
});

// Get all unique MHBLs from container (original packages loaded in container)
const allContainerMHBLs = computed(() => {
    const seen = new Set();
    const mhbls = [];
    
    // Get unique MHBLs from packages with MHBL
    if (props.packagesWithMhbl && props.packagesWithMhbl.length > 0) {
        props.packagesWithMhbl.forEach(pkg => {
            const mhblReference = pkg.hbl?.mhbl?.reference || pkg.hbl?.mhbl?.hbl_number;
            if (mhblReference && !seen.has(mhblReference)) {
                seen.add(mhblReference);
                mhbls.push({
                    reference: mhblReference,
                    hbl_number: pkg.hbl?.mhbl?.hbl_number || mhblReference,
                    type: 'mhbl'
                });
            }
        });
    }
    
    return mhbls;
});

// Calculate unloaded vs remaining HBLs and MHBLs
const unloadProgress = computed(() => {
    const totalHBLs = allContainerHBLs.value.length;
    const totalMHBLs = allContainerMHBLs.value.length;
    const total = totalHBLs + totalMHBLs;
    
    const unloadedHBLs = uniqueContainerArray.value.length;
    const unloadedMHBLs = props.warehouseMHBLs.length;
    const unloaded = unloadedHBLs + unloadedMHBLs;
    
    const remaining = total - unloaded;
    
    return {
        total,
        unloaded,
        remaining,
        totalHBLs,
        totalMHBLs,
        unloadedHBLs,
        unloadedMHBLs,
        hasRemaining: remaining > 0
    };
});

// Get remaining HBLs (not unloaded)
const remainingHBLs = computed(() => {
    const unloadedHBLIds = new Set(uniqueContainerArray.value.map(item => item.hbl.id));
    return allContainerHBLs.value.filter(hbl => !unloadedHBLIds.has(hbl.id));
});

// Get remaining MHBLs (not unloaded)
const remainingMHBLs = computed(() => {
    const unloadedMHBLReferences = new Set(props.warehouseMHBLs.map(mhbl => mhbl.mhblReference));
    return allContainerMHBLs.value.filter(mhbl => !unloadedMHBLReferences.has(mhbl.reference));
});

// Summary statistics
const summaryStats = computed(() => {
    const totalHBLPackages = props.warehouseArray.length;
    const totalMHBLPackages = props.warehouseMHBLs.reduce((sum, mhbl) => sum + mhbl.packages.length, 0);
    const totalPackages = totalHBLPackages + totalMHBLPackages;
    const totalHBLs = uniqueContainerArray.value.length;
    const totalMHBLs = props.warehouseMHBLs.length;
    
    const totalWeight = [...props.warehouseArray, ...props.warehouseMHBLs.flatMap(m => m.packages)]
        .reduce((sum, pkg) => sum + (parseFloat(pkg.weight) || 0), 0);
    
    const totalVolume = [...props.warehouseArray, ...props.warehouseMHBLs.flatMap(m => m.packages)]
        .reduce((sum, pkg) => sum + (parseFloat(pkg.volume) || 0), 0);
    
    return {
        totalPackages,
        totalHBLs,
        totalMHBLs,
        totalWeight: totalWeight.toFixed(2),
        totalVolume: totalVolume.toFixed(2)
    };
});

const form = useForm({
    container_id: route().params.container,
    packages: [],
    remaining_hbl_ids: [],
    remaining_mhbl_references: [],
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

    // Add remaining HBL IDs and MHBL references to mark as shortlanded
    form.remaining_hbl_ids = remainingHBLs.value.map(hbl => hbl.id);
    form.remaining_mhbl_references = remainingMHBLs.value.map(mhbl => mhbl.reference);

    form.post(route("arrival.unload-container.unload"), {
        onSuccess: () => {
            if (unloadProgress.value.hasRemaining) {
                const hblCount = remainingHBLs.value.length;
                const mhblCount = remainingMHBLs.value.length;
                let message = `Unloading completed! `;
                if (hblCount > 0 && mhblCount > 0) {
                    message += `${hblCount} HBL(s) and ${mhblCount} MHBL(s) marked as shortlanded.`;
                } else if (hblCount > 0) {
                    message += `${hblCount} HBL(s) marked as shortlanded.`;
                } else if (mhblCount > 0) {
                    message += `${mhblCount} MHBL(s) marked as shortlanded.`;
                }
                push.warning(message);
            } else {
                push.success('Unloading successfully!');
            }
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
    <Dialog :style="{ width: '75rem', maxHeight: '90vh' }" :visible="visible" header="Unloaded Summary" modal
            class="unloading-review-modal"
            @update:visible="(newValue) => $emit('update:visible', newValue)">

        <!-- Progress Indicator -->
        <div class="mb-4 p-4 bg-slate-50 dark:bg-navy-800 rounded-lg border border-slate-200 dark:border-navy-700">
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center w-10 h-10 bg-info/10 text-info rounded-lg">
                        <i class="ti ti-box text-lg"></i>
                    </div>
                    <div>
                        <div class="text-sm font-medium text-slate-600 dark:text-navy-300">Unloading Progress</div>
                        <div class="text-lg font-bold text-slate-800 dark:text-navy-100">
                            Unloaded {{ unloadProgress.unloaded }}/{{ unloadProgress.total }} (HBL: {{ unloadProgress.unloadedHBLs }}/{{ unloadProgress.totalHBLs }}, MHBL: {{ unloadProgress.unloadedMHBLs }}/{{ unloadProgress.totalMHBLs }})
                        </div>
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-sm text-slate-500 dark:text-navy-400">Remaining</div>
                    <div class="text-lg font-bold" :class="unloadProgress.hasRemaining ? 'text-warning' : 'text-success'">
                        {{ unloadProgress.remaining }}
                    </div>
                </div>
            </div>
            <div class="w-full bg-slate-200 dark:bg-navy-700 rounded-full h-2 mt-3">
                <div 
                    class="h-2 rounded-full transition-all duration-300"
                    :class="unloadProgress.hasRemaining ? 'bg-warning' : 'bg-success'"
                    :style="{ width: `${unloadProgress.total > 0 ? (unloadProgress.unloaded / unloadProgress.total) * 100 : 0}%` }"
                ></div>
            </div>
        </div>

        <!-- Warning for Remaining HBLs/MHBLs -->
        <div v-if="unloadProgress.hasRemaining" class="mb-4 p-4 bg-warning/10 border border-warning/30 rounded-lg">
            <div class="flex items-start gap-3">
                <i class="ti ti-alert-triangle text-warning text-xl mt-0.5"></i>
                <div class="flex-1">
                    <div class="font-semibold text-warning mb-1">Shortlanded HBLs/MHBLs Detected</div>
                    <div class="text-sm text-slate-700 dark:text-navy-200">
                        There are <strong>{{ unloadProgress.remaining }} item(s)</strong> remaining in the container ({{ remainingHBLs.length }} HBL(s), {{ remainingMHBLs.length }} MHBL(s)) that will be automatically marked as <strong>shortlanded</strong> when you finish the unload process.
                    </div>
                    <div v-if="remainingHBLs.length > 0" class="mt-2 text-xs text-slate-600 dark:text-navy-300">
                        <strong>Remaining HBLs:</strong> {{ remainingHBLs.map(h => h.hbl_number).join(', ') }}
                    </div>
                    <div v-if="remainingMHBLs.length > 0" class="mt-1 text-xs text-slate-600 dark:text-navy-300">
                        <strong>Remaining MHBLs:</strong> {{ remainingMHBLs.map(m => m.hbl_number).join(', ') }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Search Bar -->
        <div class="mb-4">
            <IconField class="w-full">
                <InputIcon>
                    <i class="pi pi-search" />
                </InputIcon>
                <InputText
                    v-model="searchQuery"
                    class="w-full"
                    placeholder="Search by HBL number, consignee name, or contact..."
                />
            </IconField>
        </div>

        <Divider />

        <!-- HBL Packages Section -->
        <div v-if="filteredHBLs.length > 0" class="mb-5">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-lg font-semibold text-slate-700 dark:text-navy-100 flex items-center gap-2">
                    <i class="ti ti-box text-info"></i>
                    Unloaded HBL Packages ({{ filteredHBLs.length }})
                </h3>
            </div>
            <div class="border border-slate-200 dark:border-navy-600 rounded-lg overflow-hidden">
                <div class="max-h-[400px] overflow-y-auto kanban-scrollbar">
                    <table class="w-full">
                        <thead class="bg-slate-50 dark:bg-navy-800 sticky top-0">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-navy-300 uppercase">HBL Number</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-navy-300 uppercase">Consignee</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-slate-600 dark:text-navy-300 uppercase">Packages</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-slate-600 dark:text-navy-300 uppercase">Weight</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-slate-600 dark:text-navy-300 uppercase">Volume</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-slate-600 dark:text-navy-300 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-navy-700">
                            <tr 
                                v-for="(packageData, index) in filteredHBLs"
                                :key="index"
                                class="hover:bg-slate-50 dark:hover:bg-navy-800/50 transition-colors"
                            >
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <i class="ti ti-box text-info"></i>
                                        <span class="font-semibold text-slate-800 dark:text-navy-100">{{ packageData.hbl.hbl_number }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-sm text-slate-600 dark:text-navy-300">{{ packageData.hbl.consignee_name || '-' }}</span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <Badge :value="countPackages(packageData.hbl_id)" severity="success" />
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="text-sm text-slate-600 dark:text-navy-300">
                                        {{ getPackagesForHBL(packageData.hbl_id).reduce((sum, p) => sum + (parseFloat(p.weight) || 0), 0).toFixed(2) }} kg
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="text-sm text-slate-600 dark:text-navy-300">
                                        {{ getPackagesForHBL(packageData.hbl_id).reduce((sum, p) => sum + (parseFloat(p.volume) || 0), 0).toFixed(2) }} m³
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <Button
                                        v-tooltip.top="'Mark HBL Status'"
                                        icon="pi pi-flag"
                                        severity="secondary"
                                        size="small"
                                        text
                                        rounded
                                        @click="openReportModal(packageData.hbl)"
                                    />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- MHBL Packages Section -->
        <div v-if="filteredMHBLs.length > 0" class="mb-5">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-lg font-semibold text-slate-700 dark:text-navy-100 flex items-center gap-2">
                    <i class="ti ti-boxes text-warning"></i>
                    Unloaded MHBL Packages ({{ filteredMHBLs.length }})
                </h3>
            </div>
            <div class="border border-slate-200 dark:border-navy-600 rounded-lg overflow-hidden">
                <div class="max-h-[300px] overflow-y-auto kanban-scrollbar">
                    <table class="w-full">
                        <thead class="bg-slate-50 dark:bg-navy-800 sticky top-0">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-navy-300 uppercase">MHBL Number</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-slate-600 dark:text-navy-300 uppercase">Packages</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-slate-600 dark:text-navy-300 uppercase">Weight</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-slate-600 dark:text-navy-300 uppercase">Volume</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-navy-700">
                            <tr 
                                v-for="(mhbl, index) in filteredMHBLs"
                                :key="index"
                                class="hover:bg-slate-50 dark:hover:bg-navy-800/50 transition-colors"
                            >
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <i class="ti ti-boxes text-warning"></i>
                                        <span class="font-semibold text-slate-800 dark:text-navy-100">{{ mhbl.packages[0]?.hbl?.mhbl?.hbl_number || mhbl.mhblReference }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <Badge :value="mhbl.packages.length" severity="warning" />
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="text-sm text-slate-600 dark:text-navy-300">
                                        {{ mhbl.packages.reduce((sum, p) => sum + (parseFloat(p.weight) || 0), 0).toFixed(2) }} kg
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="text-sm text-slate-600 dark:text-navy-300">
                                        {{ mhbl.packages.reduce((sum, p) => sum + (parseFloat(p.volume) || 0), 0).toFixed(2) }} m³
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-if="filteredHBLs.length === 0 && filteredMHBLs.length === 0" class="text-center py-8">
            <i class="pi pi-search text-4xl text-slate-400 mb-3"></i>
            <p class="text-slate-500 dark:text-navy-400">No packages found matching your search</p>
        </div>

        <Divider />

        <!-- Action Buttons -->
        <div class="flex justify-between items-center mt-4">
            <div class="text-sm text-slate-500 dark:text-navy-400">
                Review all packages before finishing the unload process
            </div>
            <div class="flex gap-2">
                <Button
                    label="Cancel"
                    severity="secondary"
                    outlined
                    @click="emit('close')"
                />
                <Button
                    :disabled="form.processing || (filteredHBLs.length === 0 && filteredMHBLs.length === 0)"
                    :loading="form.processing"
                    icon="pi pi-check"
                    label="Finish Unload"
                    @click="handleFinishUnloading"
                />
            </div>
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
