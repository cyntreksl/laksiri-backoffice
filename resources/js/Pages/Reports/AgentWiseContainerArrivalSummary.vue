<script setup>
import { ref, reactive, onMounted, computed, watch } from 'vue';
import axios from 'axios';
import AppLayout from '@/Layouts/AppLayout.vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import ColumnGroup from 'primevue/columngroup';
import Row from 'primevue/row';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Calendar from 'primevue/calendar';
import Select from 'primevue/select';
import Card from 'primevue/card';
import Panel from 'primevue/panel';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import FloatLabel from 'primevue/floatlabel';
import { push } from 'notivue';
import moment from "moment";
import { debounce } from "lodash";

const props = defineProps({
    branches: {
        type: Array,
        default: () => [],
    },
});

const loading = ref(false);
const records = ref([]);
const totalRecords = ref(0);
const stats = ref({
    total_agents: 0,
    total_containers: 0,
    total_packages: 0,
    total_consignees: 0,
    grand_total_cbm: '0.00',
});

const lazyParams = reactive({
    page: 1,
    per_page: 25,
    sort_field: 'agent_name',
    sort_order: 'asc',
});

const filters = reactive({
    date_from: null,
    date_to: null,
    branch_id: null,
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

        if (filters.date_from) {
            params.date_from = moment(filters.date_from).format('YYYY-MM-DD');
        }
        if (filters.date_to) {
            params.date_to = moment(filters.date_to).format('YYYY-MM-DD');
        }

        const response = await axios.get(route('report.agent-wise-container-arrival-summary.data'), { params });

        if (response.data.success) {
            records.value = response.data.data;
            totalRecords.value = response.data.total;
            stats.value = response.data.stats || stats.value;
        }
    } catch (error) {
        console.error('Error fetching agent wise container arrival summary:', error);
        push.error('Failed to load report data');
    } finally {
        loading.value = false;
    }
};

const debouncedFetchData = debounce(() => {
    lazyParams.page = 1;
    fetchData();
}, 1000);

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
    lazyParams.sort_field = event.sortField || 'agent_name';
    lazyParams.sort_order = event.sortOrder === 1 ? 'asc' : 'desc';
    fetchData();
};

const applyFilters = () => {
    lazyParams.page = 1;
    fetchData();
};

const resetFilters = () => {
    filters.date_from = null;
    filters.date_to = null;
    filters.branch_id = null;
    filters.search = '';
    lazyParams.page = 1;
    fetchData();
};

const exportData = (format = 'xlsx') => {
    const params = {};

    Object.keys(lazyParams).forEach(key => {
        if (lazyParams[key] !== null && lazyParams[key] !== undefined) {
            params[key] = lazyParams[key];
        }
    });

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
    window.location.href = route('report.agent-wise-container-arrival-summary.export') + '?' + queryString;
    push.success(`Export started. ${format.toUpperCase()} download will begin shortly.`);
};

const formatNumber = (value) => {
    if (!value) return '0.00';
    return parseFloat(value.toString().replace(/,/g, '')).toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
};

const formatInteger = (value) => {
    if (!value) return '0';
    return parseInt(value).toLocaleString('en-US');
};

const dateRangeText = computed(() => {
    if (filters.date_from && filters.date_to) {
        return `${moment(filters.date_from).format('DD/MM/YYYY')} To ${moment(filters.date_to).format('DD/MM/YYYY')}`;
    }
    return 'All Time';
});

const exportCSVFilename = computed(() => {
    const timestamp = moment().format('YYYY_MM_DD_HH_mm_ss');
    return `agent-wise-container-arrival-summary-${timestamp}`;
});

onMounted(() => {
    // Set default date range to current month
    const now = new Date();
    filters.date_from = new Date(now.getFullYear(), now.getMonth(), 1);
    filters.date_to = new Date(now.getFullYear(), now.getMonth() + 1, 0);
    fetchData();
});
</script>

<template>
    <AppLayout title="Container Arrival Summary">
        <template #header>Container Arrival Summary</template>

        <div class="agent-wise-report-page">
            <!-- Page Header -->
            <div class="page-header">
                <div class="header-content">
                    <div class="header-left">
                        <i class="ti ti-building-warehouse text-4xl text-primary"></i>
                        <div>
                            <h1 class="text-3xl font-bold text-gray-800">Container Arrival Summary</h1>
                            <p class="text-gray-600 mt-1">{{ dateRangeText }}</p>
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
                            <i class="ti ti-building text-3xl text-blue-500"></i>
                            <div>
                                <p class="text-sm text-gray-500 uppercase">Total Agents</p>
                                <p class="text-3xl font-bold text-gray-800">{{ stats.total_agents }}</p>
                            </div>
                        </div>
                    </template>
                </Card>

                <Card class="summary-card">
                    <template #content>
                        <div class="card-content">
                            <i class="ti ti-container text-3xl text-purple-500"></i>
                            <div>
                                <p class="text-sm text-gray-500 uppercase">Total Containers</p>
                                <p class="text-3xl font-bold text-purple-600">{{ formatInteger(stats.total_containers) }}</p>
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
                                <p class="text-3xl font-bold text-green-600">{{ formatInteger(stats.total_packages) }}</p>
                            </div>
                        </div>
                    </template>
                </Card>

                <Card class="summary-card">
                    <template #content>
                        <div class="card-content">
                            <i class="ti ti-users text-3xl text-teal-500"></i>
                            <div>
                                <p class="text-sm text-gray-500 uppercase">Total Consignees</p>
                                <p class="text-3xl font-bold text-teal-600">{{ formatInteger(stats.total_consignees) }}</p>
                            </div>
                        </div>
                    </template>
                </Card>

                <Card class="summary-card">
                    <template #content>
                        <div class="card-content">
                            <i class="ti ti-box text-3xl text-orange-500"></i>
                            <div>
                                <p class="text-sm text-gray-500 uppercase">Total CBM</p>
                                <p class="text-3xl font-bold text-orange-600">{{ formatNumber(stats.grand_total_cbm) }}</p>
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
                                <Calendar
                                    v-model="filters.date_from"
                                    class="w-full"
                                    dateFormat="yy-mm-dd"
                                    input-id="date-from"
                                    showIcon
                                />
                                <label for="date-from">Date From</label>
                            </FloatLabel>

                            <FloatLabel class="w-full" variant="in">
                                <Calendar
                                    v-model="filters.date_to"
                                    class="w-full"
                                    dateFormat="yy-mm-dd"
                                    input-id="date-to"
                                    showIcon
                                />
                                <label for="date-to">Date To</label>
                            </FloatLabel>

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
                                <label for="branch">Agent</label>
                            </FloatLabel>

                            <FloatLabel class="w-full" variant="in">
                                <InputText
                                    v-model="filters.search"
                                    class="w-full"
                                    input-id="search"
                                    @keyup.enter="applyFilters"
                                />
                                <label for="search">Search Agent Name</label>
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
                        currentPageReportTemplate="Showing {first} to {last} of {totalRecords} agents"
                        dataKey="agent_name"
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
                                <IconField class="w-full sm:w-auto">
                                    <InputIcon>
                                        <i class="pi pi-search" />
                                    </InputIcon>
                                    <InputText
                                        v-model="filters.search"
                                        class="w-full"
                                        placeholder="Search Agent"
                                        size="small"
                                    />
                                </IconField>
                            </div>
                        </template>

                        <ColumnGroup type="header">
                            <Row>
                                <Column :rowspan="2" header="Agent Name" sortField="agent_name" sortable style="min-width: 200px" />
                                <Column :rowspan="2" header="CBM" sortField="total_cbm" sortable style="min-width: 120px" />
                                <Column :colspan="4" header="No. Of Package" headerStyle="text-align: center" />
                                <Column :rowspan="2" header="No. Of Consig." sortField="total_consignees" sortable style="min-width: 120px" />
                                <Column :colspan="3" header="Containers" headerStyle="text-align: center" />
                            </Row>
                            <Row>
                                <Column header="Wooden Boxes" sortField="wooden_boxes" sortable style="min-width: 120px" />
                                <Column header="Steel Trunk" sortField="steel_trunk" sortable style="min-width: 120px" />
                                <Column header="Other" sortField="other_packages" sortable style="min-width: 100px" />
                                <Column header="Total" sortField="total_packages" sortable style="min-width: 100px" />
                                <Column header="40ft" sortField="containers_40ft" sortable style="min-width: 80px" />
                                <Column header="20ft" sortField="containers_20ft" sortable style="min-width: 80px" />
                                <Column header="45ft" sortField="containers_45ft" sortable style="min-width: 80px" />
                            </Row>
                        </ColumnGroup>

                        <template #empty>
                            <div class="empty-state">
                                <i class="ti ti-database-off text-6xl text-gray-300"></i>
                                <p class="text-gray-500 mt-4">No agent records found</p>
                            </div>
                        </template>

                        <template #loading> Loading agent wise container arrival summary. Please wait.</template>

                        <Column field="agent_name" style="min-width: 200px">
                            <template #body="{ data }">
                                <div class="font-semibold text-gray-800">{{ data.agent_name }}</div>
                            </template>
                        </Column>

                        <Column field="total_cbm" style="min-width: 120px">
                            <template #body="{ data }">
                                <span class="font-mono">{{ formatNumber(data.total_cbm) }}</span>
                            </template>
                        </Column>

                        <Column field="wooden_boxes" style="min-width: 120px">
                            <template #body="{ data }">
                                <span class="font-mono">{{ formatInteger(data.wooden_boxes) }}</span>
                            </template>
                        </Column>

                        <Column field="steel_trunk" style="min-width: 120px">
                            <template #body="{ data }">
                                <span class="font-mono">{{ formatInteger(data.steel_trunk) }}</span>
                            </template>
                        </Column>

                        <Column field="other_packages" style="min-width: 100px">
                            <template #body="{ data }">
                                <span class="font-mono">{{ formatInteger(data.other_packages) }}</span>
                            </template>
                        </Column>

                        <Column field="total_packages" style="min-width: 100px">
                            <template #body="{ data }">
                                <span class="font-mono font-semibold text-blue-600">{{ formatInteger(data.total_packages) }}</span>
                            </template>
                        </Column>

                        <Column field="total_consignees" style="min-width: 120px">
                            <template #body="{ data }">
                                <span class="font-mono font-semibold text-purple-600">{{ formatInteger(data.total_consignees) }}</span>
                            </template>
                        </Column>

                        <Column field="containers_40ft" style="min-width: 80px">
                            <template #body="{ data }">
                                <span class="font-mono">{{ formatInteger(data.containers_40ft) }}</span>
                            </template>
                        </Column>

                        <Column field="containers_20ft" style="min-width: 80px">
                            <template #body="{ data }">
                                <span class="font-mono">{{ formatInteger(data.containers_20ft) }}</span>
                            </template>
                        </Column>

                        <Column field="containers_45ft" style="min-width: 80px">
                            <template #body="{ data }">
                                <span class="font-mono">{{ formatInteger(data.containers_45ft) }}</span>
                            </template>
                        </Column>

                        <ColumnGroup type="footer">
                            <Row>
                                <Column footerStyle="text-align: right; font-weight: bold" />
                                <Column :footer="formatNumber(stats.grand_total_cbm)" footerStyle="text-align: left; font-weight: bold" />
                                <Column :footer="formatInteger(stats.total_wooden_boxes)" footerStyle="text-align: left; font-weight: bold" />
                                <Column :footer="formatInteger(stats.total_steel_trunk)" footerStyle="text-align: left; font-weight: bold" />
                                <Column :footer="formatInteger(stats.total_other_packages)" footerStyle="text-align: left; font-weight: bold" />
                                <Column :footer="formatInteger(stats.total_packages_consignees)" footerStyle="text-align: left; font-weight: bold" />
                                <Column :footer="formatInteger(stats.total_consignees)" footerStyle="text-align: left; font-weight: bold; color: #9333ea" />
                                <Column :footer="formatInteger(stats.total_containers_40ft)" footerStyle="text-align: left; font-weight: bold" />
                                <Column :footer="formatInteger(stats.total_containers_20ft)" footerStyle="text-align: left; font-weight: bold" />
                                <Column :footer="formatInteger(stats.total_containers_45ft)" footerStyle="text-align: left; font-weight: bold" />
                            </Row>
                        </ColumnGroup>

                        <template #footer> In total there are {{ totalRecords }} agent records. </template>
                    </DataTable>
                </template>
            </Card>
        </div>
    </AppLayout>
</template>

<style scoped>
.agent-wise-report-page {
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
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
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

:deep(.p-datatable .p-datatable-thead > tr > th) {
    background: #f9fafb;
    color: #374151;
    font-weight: 600;
    padding: 0.75rem;
    border: 1px solid #e5e7eb;
}

:deep(.p-datatable .p-datatable-tbody > tr > td) {
    padding: 0.75rem;
    border: 1px solid #e5e7eb;
}

:deep(.p-datatable .p-datatable-tfoot > tr > td) {
    background: #f9fafb;
    font-weight: 600;
    padding: 0.75rem;
    border: 1px solid #e5e7eb;
}

@media (max-width: 768px) {
    .agent-wise-report-page {
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
