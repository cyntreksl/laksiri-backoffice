<template>
    <AppLayout title="Letter Registration Records">
        <template #header>Letter Registration Records</template>

        <div class="letter-registration-page">
            <!-- Page Header -->
            <div class="page-header">
                <div class="header-content">
                    <div class="header-left">
                        <i class="ti ti-file-text text-4xl text-primary"></i>
                        <div>
                            <h1 class="text-3xl font-bold text-gray-800">Letter Registration Records</h1>
                            <p class="text-gray-600 mt-1">{{ reportDate }}</p>
                        </div>
                    </div>
                    <div class="header-right">
                        <Button
                            :disabled="loading || totalRecords === 0 || !filters.container_id"
                            icon="pi pi-file-pdf"
                            label="PDF"
                            severity="danger"
                            @click="exportData('pdf')"
                        />
                        <Button
                            :disabled="loading || totalRecords === 0 || !filters.container_id"
                            icon="pi pi-file-excel"
                            label="Excel"
                            severity="success"
                            @click="exportData('xlsx')"
                        />
                        <Button
                            :disabled="loading || totalRecords === 0 || !filters.container_id"
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
                            <i class="ti ti-file-text text-3xl text-blue-500"></i>
                            <div>
                                <p class="text-sm text-gray-500 uppercase">Total HBLs</p>
                                <p class="text-3xl font-bold text-blue-600">{{ stats?.total_hbls || 0 }}</p>
                            </div>
                        </div>
                    </template>
                </Card>

                <Card class="summary-card">
                    <template #content>
                        <div class="card-content">
                            <i class="ti ti-package text-3xl text-green-500"></i>
                            <div>
                                <p class="text-sm text-gray-500 uppercase">Total Packages</p>
                                <p class="text-3xl font-bold text-green-600">{{ stats?.total_packages || 0 }}</p>
                            </div>
                        </div>
                    </template>
                </Card>

                <Card class="summary-card">
                    <template #content>
                        <div class="card-content">
                            <i class="ti ti-weight text-3xl text-orange-500"></i>
                            <div>
                                <p class="text-sm text-gray-500 uppercase">Total Weight</p>
                                <p class="text-3xl font-bold text-orange-600">{{ (stats?.total_weight || 0).toFixed(2) }}</p>
                            </div>
                        </div>
                    </template>
                </Card>

                <Card class="summary-card">
                    <template #content>
                        <div class="card-content">
                            <i class="ti ti-building text-3xl text-purple-500"></i>
                            <div>
                                <p class="text-sm text-gray-500 uppercase">Total Agents</p>
                                <p class="text-3xl font-bold text-purple-600">{{ stats?.total_agents || 0 }}</p>
                            </div>
                        </div>
                    </template>
                </Card>
            </div>

            <!-- Filters Section -->
            <Panel :collapsed="false" class="mt-5" header="Filters" toggleable>
                <template #default>
                    <div class="filters-section">
                        <div class="filters-grid">
                            <FloatLabel class="w-full" variant="in">
                                <InputText
                                    v-model="filters.manifest_number"
                                    class="w-full"
                                    input-id="manifest-number"
                                />
                                <label for="manifest-number">Manifest No.</label>
                            </FloatLabel>

                            <FloatLabel class="w-full" variant="in">
                                <Dropdown
                                    v-model="filters.branch_id"
                                    :options="branches"
                                    class="w-full"
                                    input-id="branch"
                                    optionLabel="label"
                                    optionValue="value"
                                    showClear
                                />
                                <label for="branch">Branch</label>
                            </FloatLabel>

                            <FloatLabel class="w-full" variant="in">
                                <Dropdown
                                    v-model="filters.container_id"
                                    :options="containers"
                                    class="w-full"
                                    input-id="container"
                                    optionLabel="label"
                                    optionValue="value"
                                    showClear
                                />
                                <label for="container">Container</label>
                            </FloatLabel>

                            <FloatLabel class="w-full" variant="in">
                                <InputText
                                    v-model="filters.search"
                                    class="w-full"
                                    input-id="search"
                                    @keyup.enter="applyFilters"
                                />
                                <label for="search">Search (HBL/Consignee)</label>
                            </FloatLabel>
                        </div>

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
                        :lazy="true"
                        :loading="loading"
                        :paginator="true"
                        :rows="lazyParams.per_page"
                        :rowsPerPageOptions="[10, 25, 50, 100]"
                        :totalRecords="totalRecords"
                        :value="records"
                        currentPageReportTemplate="Showing {first} to {last} of {totalRecords} records"
                        dataKey="id"
                        paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                        row-hover
                        scrollHeight="600px"
                        scrollable
                        stripedRows
                        @page="onPage"
                        @sort="onSort"
                    >
                        <template #empty>
                            <div class="empty-state">
                                <i class="ti ti-file-text text-6xl text-gray-300"></i>
                                <p class="text-red-400 mt-4">
                                    {{ filters.container_id ? 'No letter registration records found for selected container' : 'Please select a container to view letter registration records' }}
                                </p>
                            </div>
                        </template>

                        <template #loading>Loading letter registration records. Please wait.</template>

                        <Column field="serial_no" style="min-width: 80px">
                            <template #body="{ index }">
                                <span class="text-center block font-semibold">{{ (lazyParams.page - 1) * lazyParams.per_page + index + 1 }}</span>
                            </template>
                            <template #header>
                                <div class="text-center w-full">Serial No</div>
                            </template>
                        </Column>

                        <Column field="hbl_no" frozen sortable style="min-width: 150px">
                            <template #body="{ data }">
                                <span class="font-mono font-semibold">{{ data.hbl_no }}</span>
                            </template>
                            <template #header>
                                <div class="text-center w-full">HBL No</div>
                            </template>
                        </Column>

                        <Column field="consignee_name_address" sortable style="min-width: 300px">
                            <template #body="{ data }">
                                <span>{{ data.consignee_name_address }}</span>
                            </template>
                            <template #header>
                                <div class="text-left w-full">Consignee's Name & Address</div>
                            </template>
                        </Column>

                        <Column field="remarks" style="min-width: 150px">
                            <template #body="{ data }">
                                <span>{{ data.remarks }}</span>
                            </template>
                            <template #header>
                                <div class="text-center w-full">Remarks</div>
                            </template>
                        </Column>

                        <Column field="packages" style="min-width: 100px">
                            <template #body="{ data }">
                                <span class="text-center block font-semibold text-blue-600">{{ data.packages }}</span>
                            </template>
                            <template #header>
                                <div class="text-center w-full">PKgs</div>
                            </template>
                        </Column>

                        <Column field="cb" style="min-width: 100px">
                            <template #body="{ data }">
                                <span class="text-center block font-semibold text-green-600">{{ data.cb }}</span>
                            </template>
                            <template #header>
                                <div class="text-center w-full">CB</div>
                            </template>
                        </Column>

                        <template #footer>
                            <div class="flex justify-between items-center">
                                <span>In total there are {{ totalRecords }} letter registration records.</span>
                            </div>
                        </template>
                    </DataTable>
                </template>
            </Card>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, reactive, onMounted, computed, watch } from 'vue';
import axios from 'axios';
import AppLayout from '@/Layouts/AppLayout.vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Dropdown from 'primevue/dropdown';
import Card from 'primevue/card';
import Panel from 'primevue/panel';
import FloatLabel from 'primevue/floatlabel';
import { push } from 'notivue';
import { debounce } from "lodash";

const props = defineProps({
    branches: {
        type: Array,
        default: () => [],
    },
    containers: {
        type: Array,
        default: () => [],
    },
});

const loading = ref(false);
const records = ref([]);
const totalRecords = ref(0);
const branches = ref(props.branches);
const containers = ref(props.containers);
const stats = ref({
    total_hbls: 0,
    total_packages: 0,
    total_weight: 0,
    total_agents: 0,
});

const lazyParams = reactive({
    page: 1,
    per_page: 25,
    sort_field: 'hbl_no',
    sort_order: 'asc',
});

const filters = reactive({
    manifest_number: '',
    branch_id: null,
    container_id: null,
    search: '',
});

const dt = ref();

const fetchData = async () => {
    // Only fetch data if a container is selected
    if (!filters.container_id) {
        records.value = [];
        totalRecords.value = 0;
        stats.value = {
            total_hbls: 0,
            total_packages: 0,
            total_weight: 0,
            total_agents: 0,
        };
        return;
    }

    loading.value = true;
    try {
        const params = {
            ...lazyParams,
            ...filters,
        };

        const response = await axios.get(route('report.letter-registration-records.data'), { params });

        if (response.data.success) {
            records.value = response.data.data;
            totalRecords.value = response.data.total;
            stats.value = response.data.stats || stats.value;
        }
    } catch (error) {
        console.error('Error fetching letter registration records:', error);
        push.error('Failed to load letter registration records');
    } finally {
        loading.value = false;
    }
};

const debouncedFetchData = debounce(() => {
    lazyParams.page = 1;
    fetchData();
}, 1000);

watch(() => filters.container_id, (newValue) => {
    if (newValue !== null) {
        lazyParams.page = 1;
        fetchData();
    } else {
        // Clear data when no container is selected
        records.value = [];
        totalRecords.value = 0;
        stats.value = {
            total_hbls: 0,
            total_packages: 0,
            total_weight: 0,
            total_agents: 0,
        };
    }
});

watch(() => filters.search, (newValue) => {
    if (newValue !== null && filters.container_id) {
        debouncedFetchData();
    }
});

const onPage = (event) => {
    lazyParams.page = event.page + 1;
    lazyParams.per_page = event.rows;
    fetchData();
};

const onSort = (event) => {
    lazyParams.sort_field = event.sortField || 'hbl_no';
    lazyParams.sort_order = event.sortOrder === 1 ? 'asc' : 'desc';
    fetchData();
};

const applyFilters = () => {
    lazyParams.page = 1;
    fetchData();
};

const resetFilters = () => {
    filters.manifest_number = '';
    filters.branch_id = null;
    filters.container_id = null;
    filters.search = '';
    lazyParams.page = 1;
    fetchData();
};

const exportData = (format = 'xlsx') => {
    // Check if container is selected before exporting
    if (!filters.container_id) {
        push.warning('Please select a container before exporting');
        return;
    }

    try {
        const params = {};

        Object.keys(lazyParams).forEach(key => {
            if (lazyParams[key] !== null && lazyParams[key] !== undefined) {
                params[key] = lazyParams[key];
            }
        });

        Object.keys(filters).forEach(key => {
            const value = filters[key];
            if (value !== null && value !== undefined && value !== '') {
                params[key] = value;
            }
        });

        params.format = format;

        console.log('Export params:', params); // Debug log

        const queryString = new URLSearchParams(params).toString();
        const exportUrl = route('report.letter-registration-records.export') + '?' + queryString;

        console.log('Export URL:', exportUrl); // Debug log

        window.location.href = exportUrl;
        push.success(`Export started. ${format.toUpperCase()} download will begin shortly.`);
    } catch (error) {
        console.error('Export error:', error);
        push.error('Export failed. Please try again.');
    }
};

const reportDate = computed(() => {
    if (filters.container_id) {
        const selectedContainer = containers.value.find(c => c.value === filters.container_id);
        if (selectedContainer) {
            return `Container: ${selectedContainer.label}`;
        }
    }
    return 'Please select a container';
});

onMounted(() => {
    // Don't fetch data on mount - wait for container selection
    // fetchData();
});
</script>

<style scoped>
.letter-registration-page {
    padding: 1.5rem;
    max-width: 100%;
}

.page-header {
    margin-bottom: 2rem;
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
    gap: 0.75rem;
}

.summary-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.summary-card {
    border: 1px solid #e5e7eb;
    transition: all 0.2s ease;
}

.summary-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
}

.summary-card .card-content {
    display: flex;
    align-items: center;
    gap: 1.5rem;
}

.filters-section {
    padding: 1rem 0;
}

.filters-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 1rem;
}

.filter-actions {
    display: flex;
    gap: 0.75rem;
    padding-top: 1rem;
    border-top: 1px solid #e5e7eb;
}

.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 4rem 2rem;
}

@media (max-width: 768px) {
    .letter-registration-page {
        padding: 1rem;
    }

    .header-content {
        flex-direction: column;
        align-items: flex-start;
    }

    .filters-grid {
        grid-template-columns: 1fr;
    }

    .filter-actions {
        flex-direction: column;
    }

    .filter-actions button {
        width: 100%;
    }
}
</style>
