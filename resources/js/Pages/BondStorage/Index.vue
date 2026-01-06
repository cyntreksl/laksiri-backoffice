<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Button from 'primevue/button';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Dialog from 'primevue/dialog';
import InputNumber from 'primevue/inputnumber';
import InputText from 'primevue/inputtext';
import Checkbox from 'primevue/checkbox';
import { push } from 'notivue';
import axios from 'axios';
import moment from 'moment';

const props = defineProps({
    containers: {
        type: Array,
        default: () => []
    },
    settings: {
        type: Object,
        default: () => ({})
    }
});

const selectedContainer = ref(null);
const showGenerateDialog = ref(false);
const showSettingsDialog = ref(false);
const loading = ref(false);
const packagesData = ref(null);
const manualHbls = ref([]);
const searchHblNumber = ref('');

// Settings
const bondStorageSeaSerial = ref(props.settings.bond_storage_sea_serial);
const bondStorageAirSerial = ref(props.settings.bond_storage_air_serial);
const autoBondGenerationEnabled = ref(props.settings.auto_bond_generation_enabled);

const selectShipment = async (container) => {
    selectedContainer.value = container;
    loading.value = true;

    try {
        const response = await axios.post(route('bond-storage.get-packages'), {
            container_id: container.id
        });

        packagesData.value = response.data;
        showGenerateDialog.value = true;
    } catch (error) {
        push.error('Failed to load shipment packages');
        console.error(error);
    } finally {
        loading.value = false;
    }
};

const addManualHbl = async () => {
    if (!searchHblNumber.value) {
        push.warning('Please enter HBL number');
        return;
    }

    loading.value = true;

    try {
        const response = await axios.post(route('bond-storage.search-hbl'), {
            hbl_number: searchHblNumber.value
        });

        if (response.data.success) {
            manualHbls.value.push({
                hbl_number: response.data.data.hbl_number,
                hbl_id: response.data.data.id,
                packages: response.data.data.packages,
                is_manual: true
            });

            searchHblNumber.value = '';
            push.success('HBL added successfully');
        }
    } catch (error) {
        push.error(error.response?.data?.message || 'HBL not found');
    } finally {
        loading.value = false;
    }
};

const removeManualHbl = (index) => {
    manualHbls.value.splice(index, 1);
};

const generateBondNumbers = async () => {
    if (!packagesData.value) return;

    loading.value = true;

    try {
        const allPackages = packagesData.value.hbl_groups.flatMap(group =>
            group.packages.map(pkg => ({
                id: pkg.id,
                hbl_id: group.hbl_id
            }))
        );

        const response = await axios.post(route('bond-storage.generate'), {
            container_id: selectedContainer.value.id,
            packages: allPackages,
            manual_hbls: manualHbls.value
        });

        if (response.data.success) {
            push.success(`Generated ${response.data.data.total_generated} bond storage numbers`);
            showGenerateDialog.value = false;
            packagesData.value = null;
            manualHbls.value = [];
            router.reload();
        }
    } catch (error) {
        push.error('Failed to generate bond storage numbers');
        console.error(error);
    } finally {
        loading.value = false;
    }
};

const updateSettings = async () => {
    loading.value = true;

    try {
        await axios.post(route('bond-storage.update-settings'), {
            bond_storage_sea_serial: bondStorageSeaSerial.value,
            bond_storage_air_serial: bondStorageAirSerial.value,
            auto_bond_generation_enabled: autoBondGenerationEnabled.value
        });

        push.success('Settings updated successfully');
        showSettingsDialog.value = false;
    } catch (error) {
        push.error('Failed to update settings');
        console.error(error);
    } finally {
        loading.value = false;
    }
};

const totalPackages = computed(() => {
    if (!packagesData.value) return 0;

    const existingCount = packagesData.value.hbl_groups.reduce((sum, group) =>
        sum + group.packages.length, 0
    );

    const manualCount = manualHbls.value.reduce((sum, hbl) =>
        sum + hbl.packages.length, 0
    );

    return existingCount + manualCount;
});

const totalHbls = computed(() => {
    if (!packagesData.value) return 0;
    return packagesData.value.hbl_groups.length + manualHbls.value.length;
});

const formatDate = (date) => {
    return moment(date).format('YYYY-MM-DD HH:mm');
};
</script>

<template>
    <AppLayout title="Bond Storage Number Generation">
        <template #header>Bond Storage Number Generation</template>

        <div class="px-[var(--margin-x)] py-5">
            <div class="flex justify-between items-center mb-5">
                <h3 class="text-lg font-medium text-slate-700 dark:text-navy-50">
                    Manual Bond Storage Number Generation
                </h3>
                <Button
                    icon="pi pi-cog"
                    label="Settings"
                    severity="secondary"
                    @click="showSettingsDialog = true"
                />
            </div>

            <DataTable
                :loading="loading"
                :rows="10"
                :value="containers"
                class="p-datatable-sm"
                paginator
                stripedRows
            >
                <Column field="reference" header="Reference" sortable />
                <Column field="cargo_type" header="Cargo Type" sortable />
                <Column field="container_type" header="Container Type" sortable />
                <Column field="status" header="Status" sortable />
                <Column field="estimated_time_of_arrival" header="ETA" sortable>
                    <template #body="{ data }">
                        {{ data.estimated_time_of_arrival ? formatDate(data.estimated_time_of_arrival) : 'N/A' }}
                    </template>
                </Column>
                <Column header="Actions">
                    <template #body="{ data }">
                        <Button
                            icon="pi pi-play"
                            label="Generate"
                            severity="success"
                            size="small"
                            @click="selectShipment(data)"
                        />
                    </template>
                </Column>
            </DataTable>
        </div>

        <!-- Generate Dialog -->
        <Dialog
            v-model:visible="showGenerateDialog"
            :style="{ width: '80vw' }"
            header="Generate Bond Storage Numbers"
            modal
        >
            <div v-if="packagesData" class="space-y-4">
                <div class="bg-blue-50 p-4 rounded-lg">
                    <h4 class="font-semibold mb-2">Shipment Details</h4>
                    <div class="grid grid-cols-2 gap-2 text-sm">
                        <div><strong>Reference:</strong> {{ packagesData.container.reference }}</div>
                        <div><strong>Cargo Type:</strong> {{ packagesData.container.cargo_type }}</div>
                        <div><strong>Container Type:</strong> {{ packagesData.container.container_type }}</div>
                        <div><strong>Total HBLs:</strong> {{ totalHbls }}</div>
                        <div><strong>Total Packages:</strong> {{ totalPackages }}</div>
                    </div>
                </div>

                <!-- Manual HBL Addition -->
                <div class="border rounded-lg p-4">
                    <h4 class="font-semibold mb-3">Add Manual HBL</h4>
                    <div class="flex gap-2">
                        <InputText
                            v-model="searchHblNumber"
                            class="flex-1"
                            placeholder="Enter HBL Number"
                            @keyup.enter="addManualHbl"
                        />
                        <Button
                            :loading="loading"
                            icon="pi pi-plus"
                            label="Add HBL"
                            @click="addManualHbl"
                        />
                    </div>
                </div>

                <!-- Manual HBLs List -->
                <div v-if="manualHbls.length > 0" class="border rounded-lg p-4">
                    <h4 class="font-semibold mb-3">Manual HBLs</h4>
                    <div v-for="(hbl, index) in manualHbls" :key="index" class="mb-3 p-3 bg-yellow-50 rounded">
                        <div class="flex justify-between items-center mb-2">
                            <span class="font-medium">{{ hbl.hbl_number }}</span>
                            <Button
                                icon="pi pi-times"
                                severity="danger"
                                text
                                @click="removeManualHbl(index)"
                            />
                        </div>
                        <div class="text-sm text-gray-600">
                            {{ hbl.packages.length }} package(s)
                        </div>
                    </div>
                </div>

                <!-- Existing HBLs -->
                <div class="border rounded-lg p-4 max-h-96 overflow-y-auto">
                    <h4 class="font-semibold mb-3">Packages by HBL</h4>
                    <div v-for="group in packagesData.hbl_groups" :key="group.hbl_id" class="mb-4">
                        <div class="bg-slate-100 p-3 rounded-lg">
                            <div class="flex justify-between items-center mb-2">
                                <div>
                                    <span class="font-medium">{{ group.hbl_number }}</span>
                                    <span v-if="group.is_overland" class="ml-2 badge bg-warning/10 text-warning text-xs">Overland</span>
                                    <span v-if="group.is_unmanifest" class="ml-2 badge bg-error/10 text-error text-xs">Unmanifest</span>
                                </div>
                                <span class="text-sm text-gray-600">{{ group.packages.length }} package(s)</span>
                            </div>
                            <div class="grid grid-cols-1 gap-2 mt-2">
                                <div
                                    v-for="pkg in group.packages"
                                    :key="pkg.id"
                                    class="bg-white p-2 rounded text-sm"
                                >
                                    <div class="flex justify-between">
                                        <span>{{ pkg.package_type }}</span>
                                        <span class="text-gray-600">
                                            Qty: {{ pkg.quantity }} | Weight: {{ pkg.weight }} | Volume: {{ pkg.volume }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-4">
                    <Button
                        label="Cancel"
                        severity="secondary"
                        @click="showGenerateDialog = false"
                    />
                    <Button
                        :loading="loading"
                        icon="pi pi-check"
                        label="Generate Bond Storage Numbers"
                        severity="success"
                        @click="generateBondNumbers"
                    />
                </div>
            </div>
        </Dialog>

        <!-- Settings Dialog -->
        <Dialog
            v-model:visible="showSettingsDialog"
            :style="{ width: '500px' }"
            header="Bond Storage Number Settings"
            modal
        >
            <div class="space-y-4">
                <div>
                    <label class="block mb-2 font-medium">SEA Serial Number (Starting)</label>
                    <InputNumber
                        v-model="bondStorageSeaSerial"
                        :min="1"
                        class="w-full"
                    />
                    <small class="text-gray-500">Current serial number for SEA cargo</small>
                </div>

                <div>
                    <label class="block mb-2 font-medium">AIR Serial Number (Starting)</label>
                    <InputNumber
                        v-model="bondStorageAirSerial"
                        :min="1"
                        class="w-full"
                    />
                    <small class="text-gray-500">Current serial number for AIR cargo</small>
                </div>

                <div class="flex items-center gap-2">
                    <Checkbox
                        v-model="autoBondGenerationEnabled"
                        :binary="true"
                        inputId="auto-generation"
                    />
                    <label for="auto-generation">Enable automatic bond generation during unloading</label>
                </div>

                <div class="flex justify-end gap-2 pt-4">
                    <Button
                        label="Cancel"
                        severity="secondary"
                        @click="showSettingsDialog = false"
                    />
                    <Button
                        :loading="loading"
                        icon="pi pi-save"
                        label="Save Settings"
                        @click="updateSettings"
                    />
                </div>
            </div>
        </Dialog>
    </AppLayout>
</template>
