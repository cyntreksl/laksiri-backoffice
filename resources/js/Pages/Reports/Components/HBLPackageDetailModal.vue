<script setup>
import { ref, watch, computed } from 'vue';
import axios from 'axios';
import Dialog from 'primevue/dialog';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Calendar from 'primevue/calendar';
import Select from 'primevue/select';
import Tag from 'primevue/tag';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import FloatLabel from 'primevue/floatlabel';
import Card from 'primevue/card';
import Avatar from 'primevue/avatar';
import InfoDisplay from "@/Pages/Common/Components/InfoDisplay.vue";
import { push } from 'notivue';
import moment from 'moment';
import { debounce } from 'lodash';

const props = defineProps({
    show: {
        type: Boolean,
        required: true,
    },
    hblId: {
        type: Number,
        default: null,
    },
});

const emit = defineEmits(['close', 'update:show']);

const loading = ref(false);
const hblData = ref(null);
const packages = ref([]);
const totalPackages = ref(0);
const showFilters = ref(false);

const lazyParams = ref({
    page: 1,
    per_page: 10,
    sort_field: 'id',
    sort_order: 'asc',
});

const filters = ref({
    loaded_date_from: null,
    loaded_date_to: null,
    unloaded_date_from: null,
    unloaded_date_to: null,
    search: '',
});

const stats = ref({
    total_packages: 0,
    total_weight: '0.00',
    total_cbm: '0.00',
});

const fetchHBLData = async () => {
    if (!props.hblId) return;

    loading.value = true;
    try {
        const params = {
            ...lazyParams.value,
            ...filters.value,
            hbl_id: props.hblId,
        };

        // Format dates
        if (filters.value.loaded_date_from) {
            params.loaded_date_from = moment(filters.value.loaded_date_from).format('YYYY-MM-DD');
        }
        if (filters.value.loaded_date_to) {
            params.loaded_date_to = moment(filters.value.loaded_date_to).format('YYYY-MM-DD');
        }
        if (filters.value.unloaded_date_from) {
            params.unloaded_date_from = moment(filters.value.unloaded_date_from).format('YYYY-MM-DD');
        }
        if (filters.value.unloaded_date_to) {
            params.unloaded_date_to = moment(filters.value.unloaded_date_to).format('YYYY-MM-DD');
        }

        const response = await axios.get(route('report.hbl-package-report.hbl-details', props.hblId), { params });

        if (response.data.success) {
            hblData.value = response.data.hbl;
            packages.value = response.data.packages.data;
            totalPackages.value = response.data.packages.total;
            stats.value = response.data.stats;
        }
    } catch (error) {
        console.error('Error fetching HBL package details:', error);
        push.error('Failed to load package details');
    } finally {
        loading.value = false;
    }
};

const debouncedFetch = debounce(() => {
    lazyParams.value.page = 1;
    fetchHBLData();
}, 500);

watch(() => filters.value.search, () => {
    debouncedFetch();
});

watch(() => props.show, (newVal) => {
    if (newVal && props.hblId) {
        fetchHBLData();
    }
});

const onPage = (event) => {
    lazyParams.value.page = event.page + 1;
    lazyParams.value.per_page = event.rows;
    fetchHBLData();
};

const onSort = (event) => {
    lazyParams.value.sort_field = event.sortField || 'id';
    lazyParams.value.sort_order = event.sortOrder === 1 ? 'asc' : 'desc';
    fetchHBLData();
};

const applyFilters = () => {
    lazyParams.value.page = 1;
    fetchHBLData();
};

const resetFilters = () => {
    filters.value = {
        loaded_date_from: null,
        loaded_date_to: null,
        unloaded_date_from: null,
        unloaded_date_to: null,
        search: '',
    };
    lazyParams.value.page = 1;
    fetchHBLData();
};

const exportPackages = (format = 'xlsx') => {
    const params = {
        hbl_id: props.hblId,
        format: format,
    };

    // Add filters, formatting dates
    if (filters.value.loaded_date_from) {
        params.loaded_date_from = moment(filters.value.loaded_date_from).format('YYYY-MM-DD');
    }
    if (filters.value.loaded_date_to) {
        params.loaded_date_to = moment(filters.value.loaded_date_to).format('YYYY-MM-DD');
    }
    if (filters.value.unloaded_date_from) {
        params.unloaded_date_from = moment(filters.value.unloaded_date_from).format('YYYY-MM-DD');
    }
    if (filters.value.unloaded_date_to) {
        params.unloaded_date_to = moment(filters.value.unloaded_date_to).format('YYYY-MM-DD');
    }
    if (filters.value.search) {
        params.search = filters.value.search;
    }

    const queryString = new URLSearchParams(params).toString();
    window.location.href = route('report.hbl-package-report.export') + '?' + queryString;
    push.success(`Export started. ${format.toUpperCase()} download will begin shortly.`);
};

const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const formatNumber = (value) => {
    if (!value) return '0.00';
    return parseFloat(value.toString().replace(/,/g, '')).toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
};

const getCargoTypeSeverity = (type) => {
    const severities = {
        'Sea Cargo': 'success',
        'Air Cargo': 'info',
    };
    return severities[type] || 'secondary';
};

const resolveCargoType = (cargoType) => {
    const icons = {
        'Sea Cargo': 'ti ti-sailboat',
        'Air Cargo': 'ti ti-plane-tilt',
    };
    return {
        icon: icons[cargoType] || 'ti ti-package',
        color: getCargoTypeSeverity(cargoType),
    };
};

const closeModal = () => {
    emit('update:show', false);
    emit('close');
};
</script>

<template>
    <Dialog
        :style="{ width: '95vw', maxWidth: '1400px' }"
        :visible="show"
        modal
        @update:visible="closeModal"
    >
        <template #header>
            <div class="flex items-center gap-3 w-full">
                <i class="ti ti-package text-2xl text-primary"></i>
                <div class="flex-1">
                    <h3 class="text-xl font-bold">HBL Package Details</h3>
                    <p v-if="hblData" class="text-sm text-gray-600">{{ hblData.hbl_number }}</p>
                </div>
            </div>
        </template>

        <div v-if="loading && !hblData" class="flex items-center justify-center py-20">
            <div class="text-center">
                <i class="pi pi-spin pi-spinner text-4xl text-primary"></i>
                <p class="mt-4 text-gray-600">Loading package details...</p>
            </div>
        </div>

        <div v-else-if="hblData" class="space-y-4">
            <!-- HBL Summary -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                <!-- Shipper Card -->
                <Card class="border">
                    <template #title>
                        <div class="flex items-center gap-2">
                            <i class="ti ti-user-pentagon text-lg"></i>
                            <span class="text-base">Shipper</span>
                        </div>
                    </template>
                    <template #content>
                        <div class="flex items-start gap-3">
                            <Avatar
                                :label="hblData.hbl_name?.charAt(0)"
                                class="!bg-emerald-200 flex-shrink-0"
                                size="large"
                            />
                            <div class="flex flex-col min-w-0 flex-grow">
                                <p class="font-medium text-gray-900 truncate">{{ hblData.hbl_name }}</p>
                                <p class="text-gray-500 text-sm truncate">{{ hblData.contact_number }}</p>
                                <p class="text-gray-500 text-xs break-all">{{ hblData.email }}</p>
                            </div>
                        </div>
                    </template>
                </Card>

                <!-- Stats Cards -->
                <Card class="border bg-blue-50">
                    <template #content>
                        <div class="flex items-center gap-3">
                            <i class="ti ti-package text-3xl text-blue-500"></i>
                            <div>
                                <p class="text-sm text-gray-500 uppercase">Total Packages</p>
                                <p class="text-2xl font-bold text-gray-800">{{ stats.total_packages }}</p>
                            </div>
                        </div>
                    </template>
                </Card>

                <Card class="border bg-green-50">
                    <template #content>
                        <div class="flex items-center gap-3">
                            <i class="ti ti-weight text-3xl text-green-500"></i>
                            <div>
                                <p class="text-sm text-gray-500 uppercase">Total Weight</p>
                                <p class="text-2xl font-bold text-green-600">{{ formatNumber(stats.total_weight) }} KG</p>
                            </div>
                        </div>
                    </template>
                </Card>
            </div>

            <!-- Filters & Export -->
            <div class="flex items-center justify-between gap-3 p-4 bg-gray-50 rounded-lg">
                <Button
                    :icon="showFilters ? 'pi pi-filter-slash' : 'pi pi-filter'"
                    :label="showFilters ? 'Hide Filters' : 'Show Filters'"
                    severity="secondary"
                    size="small"
                    @click="showFilters = !showFilters"
                />

                <div class="flex gap-2">
                    <Button
                        :disabled="loading || totalPackages === 0"
                        icon="pi pi-file-pdf"
                        label="PDF"
                        severity="danger"
                        size="small"
                        @click="exportPackages('pdf')"
                    />
                    <Button
                        :disabled="loading || totalPackages === 0"
                        icon="pi pi-file-excel"
                        label="Excel"
                        severity="success"
                        size="small"
                        @click="exportPackages('xlsx')"
                    />
                    <Button
                        :disabled="loading || totalPackages === 0"
                        icon="pi pi-file"
                        label="CSV"
                        severity="secondary"
                        size="small"
                        @click="exportPackages('csv')"
                    />
                </div>
            </div>

            <!-- Filter Panel -->
            <div v-if="showFilters" class="p-4 bg-white border rounded-lg">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
                    <FloatLabel variant="in">
                        <Calendar
                            v-model="filters.loaded_date_from"
                            class="w-full"
                            dateFormat="yy-mm-dd"
                            input-id="loaded-from"
                            showIcon
                        />
                        <label for="loaded-from">Loaded From</label>
                    </FloatLabel>

                    <FloatLabel variant="in">
                        <Calendar
                            v-model="filters.loaded_date_to"
                            class="w-full"
                            dateFormat="yy-mm-dd"
                            input-id="loaded-to"
                            showIcon
                        />
                        <label for="loaded-to">Loaded To</label>
                    </FloatLabel>

                    <FloatLabel variant="in">
                        <Calendar
                            v-model="filters.unloaded_date_from"
                            class="w-full"
                            dateFormat="yy-mm-dd"
                            input-id="unloaded-from"
                            showIcon
                        />
                        <label for="unloaded-from">Unloaded From</label>
                    </FloatLabel>

                    <FloatLabel variant="in">
                        <Calendar
                            v-model="filters.unloaded_date_to"
                            class="w-full"
                            dateFormat="yy-mm-dd"
                            input-id="unloaded-to"
                            showIcon
                        />
                        <label for="unloaded-to">Unloaded To</label>
                    </FloatLabel>
                </div>

                <div class="flex gap-2 mt-3">
                    <Button
                        :loading="loading"
                        icon="pi pi-check"
                        label="Apply"
                        size="small"
                        @click="applyFilters"
                    />
                    <Button
                        :disabled="loading"
                        icon="pi pi-refresh"
                        label="Reset"
                        severity="secondary"
                        size="small"
                        @click="resetFilters"
                    />
                </div>
            </div>

            <!-- Packages Table -->
            <DataTable
                :lazy="true"
                :loading="loading"
                :paginator="true"
                :rows="lazyParams.per_page"
                :rowsPerPageOptions="[10, 25, 50]"
                :sortField="lazyParams.sort_field"
                :sortOrder="lazyParams.sort_order === 'asc' ? 1 : -1"
                :totalRecords="totalPackages"
                :value="packages"
                currentPageReportTemplate="Showing {first} to {last} of {totalRecords} packages"
                dataKey="id"
                paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                removable-sort
                row-hover
                sortMode="single"
                stripedRows
                @page="onPage"
                @sort="onSort"
            >
                <template #header>
                    <div class="flex justify-between items-center">
                        <IconField>
                            <InputIcon>
                                <i class="pi pi-search" />
                            </InputIcon>
                            <InputText
                                v-model="filters.search"
                                placeholder="Search packages..."
                                size="small"
                            />
                        </IconField>
                    </div>
                </template>

                <template #empty>
                    <div class="text-center py-8">
                        <i class="ti ti-package-off text-4xl text-gray-300"></i>
                        <p class="text-gray-500 mt-2">No packages found</p>
                    </div>
                </template>

                <Column field="package_number" header="Package #" sortable style="min-width: 120px">
                    <template #body="{ data }">
                        <span class="font-semibold text-primary">{{ data.package_number }}</span>
                    </template>
                </Column>

                <Column field="description" header="Description" style="min-width: 200px">
                    <template #body="{ data }">
                        <span class="text-sm">{{ data.description || 'N/A' }}</span>
                    </template>
                </Column>

                <Column field="weight" header="Weight (KG)" sortable style="min-width: 120px">
                    <template #body="{ data }">
                        <span class="font-semibold">{{ formatNumber(data.weight) }}</span>
                    </template>
                </Column>

                <Column field="cbm" header="CBM" sortable style="min-width: 100px">
                    <template #body="{ data }">
                        <span class="font-semibold">{{ formatNumber(data.cbm) }}</span>
                    </template>
                </Column>

                <Column field="loaded_date" header="Loaded Date" sortable style="min-width: 180px">
                    <template #body="{ data }">
                        <span class="text-sm">{{ formatDate(data.loaded_date) }}</span>
                    </template>
                </Column>

                <Column field="unloaded_date" header="Unloaded Date" sortable style="min-width: 180px">
                    <template #body="{ data }">
                        <span class="text-sm">{{ formatDate(data.unloaded_date) }}</span>
                    </template>
                </Column>

                <Column field="container_reference" header="Container" style="min-width: 150px">
                    <template #body="{ data }">
                        <span class="text-sm">{{ data.container_reference || 'N/A' }}</span>
                    </template>
                </Column>

                <template #footer>
                    In total there are {{ totalPackages }} packages.
                </template>
            </DataTable>
        </div>
    </Dialog>
</template>

<style scoped>
:deep(.p-dialog-header) {
    padding: 1.25rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

:deep(.p-dialog-content) {
    padding: 1.5rem;
}
</style>
