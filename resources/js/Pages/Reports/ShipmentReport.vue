<script setup>
import { ref, reactive, onMounted, computed, watch } from 'vue';
import axios from 'axios';
import AppLayout from '@/Layouts/AppLayout.vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Calendar from 'primevue/calendar';
import Select from 'primevue/select';
import Tag from 'primevue/tag';
import Card from 'primevue/card';
import Panel from 'primevue/panel';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import FloatLabel from 'primevue/floatlabel';
import { push } from 'notivue';
import moment from "moment";
import { debounce } from "lodash";

const props = defineProps({
    cargoTypes: {
        type: Array,
        default: () => [],
    },
    containerStatuses: {
        type: Array,
        default: () => [],
    },
    branches: {
        type: Array,
        default: () => [],
    },
});

const loading = ref(false);
const records = ref([]);
const totalRecords = ref(0);
const stats = ref({
    total_shipments: 0,
    total_packages: 0,
    total_weight: '0.00',
    total_cbm: '0.00',
});

const lazyParams = reactive({
    page: 1,
    per_page: 25,
    sort_field: 'created_at',
    sort_order: 'desc',
});

const filters = reactive({
    loaded_date_from: null,
    loaded_date_to: null,
    unloaded_date_from: null,
    unloaded_date_to: null,
    reached_date_from: null,
    reached_date_to: null,
    arrival_date_from: null,
    arrival_date_to: null,
    release_date_from: null,
    release_date_to: null,
    branch_id: null,
    cargo_type: null,
    status: null,
    search: '',
});

const dt = ref();

const fetchData = async () => {
    loading.value = true;
    try {
        const params = {
            ...lazyParams,
            ...filters,
        };

        // Format date filters
        if (filters.loaded_date_from) {
            params.loaded_date_from = moment(filters.loaded_date_from).format('YYYY-MM-DD');
        }
        if (filters.loaded_date_to) {
            params.loaded_date_to = moment(filters.loaded_date_to).format('YYYY-MM-DD');
        }
        if (filters.unloaded_date_from) {
            params.unloaded_date_from = moment(filters.unloaded_date_from).format('YYYY-MM-DD');
        }
        if (filters.unloaded_date_to) {
            params.unloaded_date_to = moment(filters.unloaded_date_to).format('YYYY-MM-DD');
        }
        if (filters.reached_date_from) {
            params.reached_date_from = moment(filters.reached_date_from).format('YYYY-MM-DD');
        }
        if (filters.reached_date_to) {
            params.reached_date_to = moment(filters.reached_date_to).format('YYYY-MM-DD');
        }
        if (filters.arrival_date_from) {
            params.arrival_date_from = moment(filters.arrival_date_from).format('YYYY-MM-DD');
        }
        if (filters.arrival_date_to) {
            params.arrival_date_to = moment(filters.arrival_date_to).format('YYYY-MM-DD');
        }
        if (filters.release_date_from) {
            params.release_date_from = moment(filters.release_date_from).format('YYYY-MM-DD');
        }
        if (filters.release_date_to) {
            params.release_date_to = moment(filters.release_date_to).format('YYYY-MM-DD');
        }

        const response = await axios.get(route('report.shipment-report.data'), { params });

        if (response.data.success) {
            records.value = response.data.data;
            totalRecords.value = response.data.total;
            stats.value = response.data.stats || stats.value;
        }
    } catch (error) {
        console.error('Error fetching Shipment report:', error);
        push.error('Failed to load shipment report data');
    } finally {
        loading.value = false;
    }
};

// Debounced search function
const debouncedFetchData = debounce(() => {
    lazyParams.page = 1;
    fetchData();
}, 1000);

// Watch for search changes
watch(() => filters.search, (newValue) => {
    if (newValue !== null) {
        debouncedFetchData();
    }
});

const onPage = (event) => {
    lazyParams.page = event.page + 1;
    lazyParams.per_page = event.rows;
    fetchData();
};

const onSort = (event) => {
    lazyParams.sort_field = event.sortField || 'created_at';
    lazyParams.sort_order = event.sortOrder === 1 ? 'asc' : 'desc';
    fetchData();
};

const applyFilters = () => {
    lazyParams.page = 1;
    fetchData();
};

const resetFilters = () => {
    Object.keys(filters).forEach(key => {
        filters[key] = null;
    });
    filters.search = '';
    lazyParams.page = 1;
    fetchData();
};

const exportData = (format = 'xlsx') => {
    const params = {};

    // Add lazy params
    Object.keys(lazyParams).forEach(key => {
        if (lazyParams[key] !== null && lazyParams[key] !== undefined) {
            params[key] = lazyParams[key];
        }
    });

    // Add filters, formatting dates
    Object.keys(filters).forEach(key => {
        const value = filters[key];
        if (value !== null && value !== undefined && value !== '') {
            if (value instanceof Date) {
                params[key] = value.toISOString().split('T')[0];
            } else {
                params[key] = value;
            }
        }
    });

    params.format = format;

    const queryString = new URLSearchParams(params).toString();
    window.location.href = route('report.shipment-report.export') + '?' + queryString;
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

const formatNumber = (num) => {
    if (!num) return '0';
    return parseFloat(num).toLocaleString('en-US');
};

const getCargoTypeSeverity = (type) => {
    const severities = {
        'General': 'info',
        'Dangerous': 'danger',
        'Perishable': 'warn',
        'Sea Cargo': 'success',
        'Air Cargo': 'info',
    };
    return severities[type] || 'secondary';
};

const getStatusSeverity = (status) => {
    const severities = {
        'Booked': 'secondary',
        'Loading': 'warn',
        'Loaded': 'info',
        'Shipped': 'primary',
        'Arrived': 'success',
        'Cleared': 'success',
        'Delivered': 'success',
    };
    return severities[status] || 'contrast';
};

const exportCSVFilename = computed(() => {
    const timestamp = moment().format('YYYY_MM_DD_HH_mm_ss');
    return `shipment-report-${timestamp}`;
});

onMounted(() => {
    fetchData();
});
</script>

<template>
    <AppLayout title="Shipment Report">
        <template #header>Shipment Report</template>

        <div class="shipment-report-page">
            <!-- Page Header -->
            <div class="page-header">
                <div class="header-content">
                    <div class="header-left">
                        <i class="ti ti-truck text-4xl text-primary"></i>
                        <div>
                            <h1 class="text-3xl font-bold text-gray-800">Shipment Report</h1>
                            <p class="text-gray-600 mt-1">Container-level movement and status tracking</p>
                        </div>
                    </div>
                    <div class="header-right">
                        <Button
                            :disabled="loading || totalRecords === 0"
                            icon="pi pi-file-pdf"
                            label="PDF"
                            severity="danger"
                            @click="exportData('pdf')"
                        />
                        <Button
                            :disabled="loading || totalRecords === 0"
                            icon="pi pi-file-excel"
                            label="Excel"
                            severity="success"
                            @click="exportData('xlsx')"
                        />
                        <Button
                            :disabled="loading || totalRecords === 0"
                            icon="pi pi-file"
                            label="CSV"
                            severity="secondary"
                            @click="exportData('csv')"
                        />
                    </div>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="summary-cards">
                <Card class="summary-card">
                    <template #content>
                        <div class="card-content">
                            <i class="ti ti-container text-3xl text-blue-500"></i>
                            <div>
                                <p class="text-sm text-gray-500 uppercase">Total Shipments</p>
                                <p class="text-3xl font-bold text-gray-800">{{ stats.total_shipments }}</p>
                            </div>
                        </div>
                    </template>
                </Card>

                <Card class="summary-card">
                    <template #content>
                        <div class="card-content">
                            <i class="ti ti-package text-3xl text-purple-500"></i>
                            <div>
                                <p class="text-sm text-gray-500 uppercase">Total Packages</p>
                                <p class="text-3xl font-bold text-purple-600">{{ formatNumber(stats.total_packages) }}</p>
                            </div>
                        </div>
                    </template>
                </Card>

                <Card class="summary-card">
                    <template #content>
                        <div class="card-content">
                            <i class="ti ti-weight text-3xl text-green-500"></i>
                            <div>
                                <p class="text-sm text-gray-500 uppercase">Total Weight (KG)</p>
                                <p class="text-3xl font-bold text-green-600">{{ stats.total_weight }}</p>
                            </div>
                        </div>
                    </template>
                </Card>

                <Card class="summary-card">
                    <template #content>
                        <div class="card-content">
                            <i class="ti ti-box text-3xl text-teal-500"></i>
                            <div>
                                <p class="text-sm text-gray-500 uppercase">Total CBM</p>
                                <p class="text-3xl font-bold text-teal-600">{{ stats.total_cbm }}</p>
                            </div>
                        </div>
                    </template>
                </Card>
            </div>

            <!-- Filters Section -->
            <Panel :collapsed="true" class="mt-5" header="Advance Filters" toggleable>
                <template #default>
                    <div class="filters-section">
                        <div class="filters-grid">
                            <!-- Loaded Date Range -->
                            <FloatLabel class="w-full" variant="in">
                                <Calendar
                                    v-model="filters.loaded_date_from"
                                    class="w-full"
                                    dateFormat="yy-mm-dd"
                                    input-id="loaded-date-from"
                                    showIcon
                                />
                                <label for="loaded-date-from">Loaded Date From</label>
                            </FloatLabel>

                            <FloatLabel class="w-full" variant="in">
                                <Calendar
                                    v-model="filters.loaded_date_to"
                                    class="w-full"
                                    dateFormat="yy-mm-dd"
                                    input-id="loaded-date-to"
                                    showIcon
                                />
                                <label for="loaded-date-to">Loaded Date To</label>
                            </FloatLabel>

                            <!-- Unloaded Date Range -->
                            <FloatLabel class="w-full" variant="in">
                                <Calendar
                                    v-model="filters.unloaded_date_from"
                                    class="w-full"
                                    dateFormat="yy-mm-dd"
                                    input-id="unloaded-date-from"
                                    showIcon
                                />
                                <label for="unloaded-date-from">Unloaded Date From</label>
                            </FloatLabel>

                            <FloatLabel class="w-full" variant="in">
                                <Calendar
                                    v-model="filters.unloaded_date_to"
                                    class="w-full"
                                    dateFormat="yy-mm-dd"
                                    input-id="unloaded-date-to"
                                    showIcon
                                />
                                <label for="unloaded-date-to">Unloaded Date To</label>
                            </FloatLabel>

                            <!-- Reached Date Range -->
                            <FloatLabel class="w-full" variant="in">
                                <Calendar
                                    v-model="filters.reached_date_from"
                                    class="w-full"
                                    dateFormat="yy-mm-dd"
                                    input-id="reached-date-from"
                                    showIcon
                                />
                                <label for="reached-date-from">Reached Date From</label>
                            </FloatLabel>

                            <FloatLabel class="w-full" variant="in">
                                <Calendar
                                    v-model="filters.reached_date_to"
                                    class="w-full"
                                    dateFormat="yy-mm-dd"
                                    input-id="reached-date-to"
                                    showIcon
                                />
                                <label for="reached-date-to">Reached Date To</label>
                            </FloatLabel>

                            <!-- Arrival Date Range -->
                            <FloatLabel class="w-full" variant="in">
                                <Calendar
                                    v-model="filters.arrival_date_from"
                                    class="w-full"
                                    dateFormat="yy-mm-dd"
                                    input-id="arrival-date-from"
                                    showIcon
                                />
                                <label for="arrival-date-from">Arrival Date From</label>
                            </FloatLabel>

                            <FloatLabel class="w-full" variant="in">
                                <Calendar
                                    v-model="filters.arrival_date_to"
                                    class="w-full"
                                    dateFormat="yy-mm-dd"
                                    input-id="arrival-date-to"
                                    showIcon
                                />
                                <label for="arrival-date-to">Arrival Date To</label>
                            </FloatLabel>

                            <!-- Release Date Range -->
                            <FloatLabel class="w-full" variant="in">
                                <Calendar
                                    v-model="filters.release_date_from"
                                    class="w-full"
                                    dateFormat="yy-mm-dd"
                                    input-id="release-date-from"
                                    showIcon
                                />
                                <label for="release-date-from">Release Date From</label>
                            </FloatLabel>

                            <FloatLabel class="w-full" variant="in">
                                <Calendar
                                    v-model="filters.release_date_to"
                                    class="w-full"
                                    dateFormat="yy-mm-dd"
                                    input-id="release-date-to"
                                    showIcon
                                />
                                <label for="release-date-to">Release Date To</label>
                            </FloatLabel>

                            <!-- Branch -->
                            <FloatLabel class="w-full" variant="in">
                                <Select
                                    v-model="filters.branch_id"
                                    :options="branches"
                                    class="w-full"
                                    filter
                                    input-id="branch"
                                    optionLabel="label"
                                    optionValue="value"
                                    showClear
                                />
                                <label for="branch">Branch/Agent</label>
                            </FloatLabel>

                            <!-- Cargo Type -->
                            <FloatLabel class="w-full" variant="in">
                                <Select
                                    v-model="filters.cargo_type"
                                    :options="cargoTypes"
                                    class="w-full"
                                    input-id="cargo-type"
                                    showClear
                                />
                                <label for="cargo-type">Cargo Type</label>
                            </FloatLabel>

                            <!-- Container Status -->
                            <FloatLabel class="w-full" variant="in">
                                <Select
                                    v-model="filters.status"
                                    :options="containerStatuses"
                                    class="w-full"
                                    input-id="status"
                                    showClear
                                />
                                <label for="status">Container Status</label>
                            </FloatLabel>

                            <!-- General Search -->
                            <FloatLabel class="w-full" variant="in">
                                <InputText
                                    v-model="filters.search"
                                    class="w-full"
                                    input-id="search"
                                    @keyup.enter="applyFilters"
                                />
                                <label for="search">Search (Reference, Container, BL, AWB)</label>
                            </FloatLabel>
                        </div>

                        <!-- Filter Actions -->
                        <div class="filter-actions">
                            <Button
                                :loading="loading"
                                icon="pi pi-filter"
                                label="Apply Filters"
                                @click="applyFilters"
                            />
                            <Button
                                :disabled="loading"
                                icon="pi pi-refresh"
                                label="Reset"
                                severity="secondary"
                                @click="resetFilters"
                            />
                        </div>
                    </div>
                </template>
            </Panel>

            <!-- Data Table -->
            <Card class="my-5">
                <template #content>
                    <DataTable
                        ref="dt"
                        :export-filename="exportCSVFilename"
                        :lazy="true"
                        :loading="loading"
                        :paginator="true"
                        :rows="lazyParams.per_page"
                        :rowsPerPageOptions="[10, 25, 50, 100]"
                        :sortField="lazyParams.sort_field"
                        :sortOrder="lazyParams.sort_order === 'asc' ? 1 : -1"
                        :totalRecords="totalRecords"
                        :value="records"
                        currentPageReportTemplate="Showing {first} to {last} of {totalRecords} records"
                        dataKey="id"
                        paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                        removable-sort
                        row-hover
                        sortMode="single"
                        stripedRows
                        tableStyle="min-width: 50rem"
                        @page="onPage"
                        @sort="onSort"
                    >
                        <template #header>
                            <div class="flex flex-col sm:flex-row justify-between gap-4">
                                <!-- Search Field -->
                                <IconField class="w-full sm:w-auto">
                                    <InputIcon>
                                        <i class="pi pi-search" />
                                    </InputIcon>
                                    <InputText
                                        v-model="filters.search"
                                        class="w-full"
                                        placeholder="Keyword Search"
                                        size="small"
                                    />
                                </IconField>
                            </div>
                        </template>

                        <template #empty>
                            <div class="empty-state">
                                <i class="ti ti-database-off text-6xl text-gray-300"></i>
                                <p class="text-gray-500 mt-4">No shipment records found</p>
                            </div>
                        </template>

                        <template #loading> Loading shipment report data. Please wait.</template>

                        <Column field="reference" header="Reference" sortable style="min-width: 150px;">
                            <template #body="{ data }">
                                <span class="font-semibold text-primary">{{ data.reference }}</span>
                            </template>
                        </Column>

                        <Column field="cargo_type" header="Cargo Type" sortable style="min-width: 120px;">
                            <template #body="{ data }">
                                <Tag :severity="getCargoTypeSeverity(data.cargo_type)" :value="data.cargo_type" />
                            </template>
                        </Column>

                        <Column field="status" header="Status" sortable style="min-width: 120px;">
                            <template #body="{ data }">
                                <Tag :severity="getStatusSeverity(data.status)" :value="data.status" />
                            </template>
                        </Column>

                        <Column field="container_number" header="Container No." style="min-width: 150px;">
                            <template #body="{ data }">
                                {{ data.container_number || '-' }}
                            </template>
                        </Column>

                        <Column field="bl_number" header="BL/AWB Number" style="min-width: 150px;">
                            <template #body="{ data }">
                                {{ data.bl_number || data.awb_number || '-' }}
                            </template>
                        </Column>

                        <Column field="branch_name" header="Branch" sortable style="min-width: 150px;">
                            <template #body="{ data }">
                                {{ data.branch_name }}
                            </template>
                        </Column>

                        <Column field="warehouse_name" header="Warehouse" style="min-width: 150px;">
                            <template #body="{ data }">
                                {{ data.warehouse_name }}
                            </template>
                        </Column>

                        <Column field="total_packages" header="Packages" style="min-width: 100px; text-align: center;">
                            <template #body="{ data }">
                                <span class="font-semibold">{{ data.total_packages }}</span>
                            </template>
                        </Column>

                        <Column field="loaded_date" header="Loaded Date" sortable style="min-width: 180px;">
                            <template #body="{ data }">
                                {{ formatDate(data.loaded_date) }}
                            </template>
                        </Column>

                        <Column field="unloaded_date" header="Unloaded Date" sortable style="min-width: 180px;">
                            <template #body="{ data }">
                                {{ formatDate(data.unloaded_date) }}
                            </template>
                        </Column>

                        <Column field="reached_date" header="Reached Date" sortable style="min-width: 150px;">
                            <template #body="{ data }">
                                {{ data.reached_date ? moment(data.reached_date).format('MMM DD, YYYY') : 'N/A' }}
                            </template>
                        </Column>

                        <Column field="arrival_date" header="Arrival Date" sortable style="min-width: 180px;">
                            <template #body="{ data }">
                                {{ formatDate(data.arrival_date) }}
                            </template>
                        </Column>

                        <Column field="created_at" header="Created Date" sortable style="min-width: 180px;">
                            <template #body="{ data }">
                                {{ formatDate(data.created_at) }}
                            </template>
                        </Column>
                    </DataTable>
                </template>
            </Card>
        </div>
    </AppLayout>
</template>

<style scoped>
.shipment-report-page {
    padding: 1.5rem;
}

.page-header {
    background: white;
    border-radius: 8px;
    padding: 1.5rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    margin-bottom: 1.5rem;
}

.header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
}

.header-left {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.header-right {
    display: flex;
    gap: 0.5rem;
}

.summary-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.summary-card {
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.card-content {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.filters-section {
    padding: 1rem 0;
}

.filters-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
    margin-bottom: 1rem;
}

.filter-actions {
    display: flex;
    gap: 0.5rem;
    justify-content: flex-end;
}

.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 3rem;
}

@media (max-width: 768px) {
    .header-content {
        flex-direction: column;
        align-items: flex-start;
    }

    .header-right {
        width: 100%;
        justify-content: flex-start;
    }

    .filters-grid {
        grid-template-columns: 1fr;
    }
}
</style>
