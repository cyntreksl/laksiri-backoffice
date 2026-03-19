<template>
    <AppLayout title="Consignee & Volume Analysis">
        <template #header>Consignee & Volume Analysis</template>

        <div class="consignee-volume-page">
            <!-- Page Header -->
            <div class="page-header">
                <div class="header-content">
                    <div class="header-left">
                        <i class="ti ti-chart-bar text-4xl text-primary"></i>
                        <div>
                            <h1 class="text-3xl font-bold text-gray-800">Consignee & Volume Analysis</h1>
                            <p class="text-gray-600 mt-1">{{ reportDate }}</p>
                        </div>
                    </div>
                    <div class="header-right">
                        <Button
                            :disabled="loading || totalRecords === 0 || !filters.analysis_type"
                            icon="pi pi-file-pdf"
                            label="PDF"
                            severity="danger"
                            @click="exportData('pdf')"
                        />
                        <Button
                            :disabled="loading || totalRecords === 0 || !filters.analysis_type"
                            icon="pi pi-file-excel"
                            label="Excel"
                            severity="success"
                            @click="exportData('xlsx')"
                        />
                        <Button
                            :disabled="loading || totalRecords === 0 || !filters.analysis_type"
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
                                <p class="text-3xl font-bold text-blue-600">{{ stats?.total_agents || 0 }}</p>
                            </div>
                        </div>
                    </template>
                </Card>

                <Card class="summary-card">
                    <template #content>
                        <div class="card-content">
                            <i class="ti ti-users text-3xl text-green-500"></i>
                            <div>
                                <p class="text-sm text-gray-500 uppercase">Total Consignees</p>
                                <p class="text-3xl font-bold text-green-600">{{ stats?.total_consignees || 0 }}</p>
                            </div>
                        </div>
                    </template>
                </Card>

                <Card class="summary-card">
                    <template #content>
                        <div class="card-content">
                            <i class="ti ti-package text-3xl text-orange-500"></i>
                            <div>
                                <p class="text-sm text-gray-500 uppercase">Total Packages</p>
                                <p class="text-3xl font-bold text-orange-600">{{ stats?.total_packages_manifest || 0 }}</p>
                            </div>
                        </div>
                    </template>
                </Card>

                <Card class="summary-card">
                    <template #content>
                        <div class="card-content">
                            <i class="ti ti-cube text-3xl text-purple-500"></i>
                            <div>
                                <p class="text-sm text-gray-500 uppercase">Total CBM</p>
                                <p class="text-3xl font-bold text-purple-600">{{ stats?.total_cbm || 0 }}</p>
                            </div>
                        </div>
                    </template>
                </Card>
            </div>

            <!-- Filters Section -->
            <Panel :collapsed="false" class="mt-5" header="Filters" toggleable>
                <template #default>
                    <div class="filters-section">
                        <!-- Analysis Type Selection (Required) -->
                        <div class="analysis-type-section">
                            <h3 class="text-lg font-semibold text-gray-800 mb-3">Analysis Type (Required)</h3>
                            <div class="radio-group">
                                <div class="radio-item">
                                    <RadioButton
                                        v-model="filters.analysis_type"
                                        input-id="remaining"
                                        name="analysis_type"
                                        value="remaining"
                                    />
                                    <label class="radio-label" for="remaining">
                                        Total Remaining Consignee & Volume Of Cargo Analysis
                                    </label>
                                </div>
                                <div class="radio-item">
                                    <RadioButton
                                        v-model="filters.analysis_type"
                                        input-id="outgoing"
                                        name="analysis_type"
                                        value="outgoing"
                                    />
                                    <label class="radio-label" for="outgoing">
                                        Total Outgoing Consignee & Volume Of Cargo Analysis
                                    </label>
                                </div>
                            </div>
                        </div>

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
                                <InputText
                                    v-model="filters.search"
                                    class="w-full"
                                    input-id="search"
                                    @keyup.enter="applyFilters"
                                />
                                <label for="search">Search Agent</label>
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
                        :loading="loading"
                        :value="records"
                        currentPageReportTemplate="Showing {first} to {last} of {totalRecords} records"
                        dataKey="id"
                        row-hover
                        scrollHeight="600px"
                        scrollable
                        stripedRows
                    >
                        <template #empty>
                            <div class="empty-state">
                                <i class="ti ti-chart-bar text-6xl text-gray-300"></i>
                                <p class="text-gray-500 mt-4">
                                    {{ filters.analysis_type ? 'No consignee volume analysis data found' : 'Please select an analysis type to view data' }}
                                </p>
                            </div>
                        </template>

                        <template #loading>Loading consignee volume analysis data. Please wait.</template>

                        <Column field="agent_name" style="min-width: 300px">
                            <template #body="{ data }">
                                <span class="font-semibold">{{ data.agent_name }}</span>
                            </template>
                            <template #header>
                                <div class="text-left w-full">Agent Name</div>
                            </template>
                        </Column>

                        <Column field="no_of_consignees" style="min-width: 150px">
                            <template #body="{ data }">
                                <span class="text-center block font-semibold text-blue-600">{{ data.no_of_consignees }}</span>
                            </template>
                            <template #header>
                                <div class="text-center w-full">No of Consignees</div>
                            </template>
                        </Column>

                        <Column field="no_of_pkgs_manifest" style="min-width: 180px">
                            <template #body="{ data }">
                                <span class="text-center block font-semibold text-green-600">{{ data.no_of_pkgs_manifest }}</span>
                            </template>
                            <template #header>
                                <div class="text-center w-full">No of PKgs (Manifest)</div>
                            </template>
                        </Column>

                        <Column field="no_of_pkgs_actual" style="min-width: 180px">
                            <template #body="{ data }">
                                <span class="text-center block font-semibold text-orange-600">{{ data.no_of_pkgs_actual }}</span>
                            </template>
                            <template #header>
                                <div class="text-center w-full">No of PKgs (Actual)</div>
                            </template>
                        </Column>

                        <Column field="cbm" style="min-width: 120px">
                            <template #body="{ data }">
                                <span class="text-center block font-semibold text-purple-600">{{ data.cbm }}</span>
                            </template>
                            <template #header>
                                <div class="text-center w-full">CBM</div>
                            </template>
                        </Column>

                        <template #footer>
                            <div class="flex justify-between items-center">
                                <span>In total there are {{ totalRecords }} agent records.</span>
                                <div class="grand-total">
                                    <span class="font-bold">Grand Total: {{ stats?.total_consignees || 0 }} Consignees, {{ stats?.total_packages_manifest || 0 }} Packages, {{ stats?.total_cbm || 0 }} CBM</span>
                                </div>
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
import Calendar from 'primevue/calendar';
import Dropdown from 'primevue/dropdown';
import RadioButton from 'primevue/radiobutton';
import Card from 'primevue/card';
import Panel from 'primevue/panel';
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
const branches = ref(props.branches);
const stats = ref({
    total_agents: 0,
    total_consignees: 0,
    total_packages_manifest: 0,
    total_packages_actual: 0,
    total_cbm: 0,
});

const filters = reactive({
    analysis_type: 'remaining', // Default to 'remaining' analysis
    date_from: null,
    date_to: null,
    branch_id: null,
    search: '',
});

const dt = ref();

const fetchData = async () => {
    // Only fetch data if analysis type is selected
    if (!filters.analysis_type) {
        records.value = [];
        totalRecords.value = 0;
        stats.value = {
            total_agents: 0,
            total_consignees: 0,
            total_packages_manifest: 0,
            total_packages_actual: 0,
            total_cbm: 0,
        };
        return;
    }

    loading.value = true;
    try {
        const params = {
            ...filters,
        };

        if (filters.date_from) {
            params.date_from = moment(filters.date_from).format('YYYY-MM-DD');
        }
        if (filters.date_to) {
            params.date_to = moment(filters.date_to).format('YYYY-MM-DD');
        }

        const response = await axios.get(route('report.consignee-volume-analysis.data'), { params });

        if (response.data.success) {
            records.value = response.data.data;
            totalRecords.value = response.data.total;
            stats.value = response.data.stats || stats.value;
        }
    } catch (error) {
        console.error('Error fetching consignee volume analysis data:', error);
        push.error('Failed to load consignee volume analysis data');
    } finally {
        loading.value = false;
    }
};

const debouncedFetchData = debounce(() => {
    fetchData();
}, 1000);

watch(() => filters.analysis_type, (newValue) => {
    if (newValue !== null) {
        fetchData();
    } else {
        // Clear data when no analysis type is selected
        records.value = [];
        totalRecords.value = 0;
        stats.value = {
            total_agents: 0,
            total_consignees: 0,
            total_packages_manifest: 0,
            total_packages_actual: 0,
            total_cbm: 0,
        };
    }
});

watch(() => filters.search, (newValue) => {
    if (newValue !== null && filters.analysis_type) {
        debouncedFetchData();
    }
});

const applyFilters = () => {
    fetchData();
};

const resetFilters = () => {
    filters.analysis_type = 'remaining'; // Reset to default 'remaining'
    filters.date_from = null;
    filters.date_to = null;
    filters.branch_id = null;
    filters.search = '';
    fetchData();
};

const exportData = (format = 'xlsx') => {
    // Check if analysis type is selected before exporting
    if (!filters.analysis_type) {
        push.warning('Please select an analysis type before exporting');
        return;
    }

    try {
        const params = {};

        Object.keys(filters).forEach(key => {
            const value = filters[key];
            if (value !== null && value !== undefined && value !== '') {
                if (value instanceof Date) {
                    params[key] = moment(value).format('YYYY-MM-DD');
                } else {
                    params[key] = value;
                }
            }
        });

        params.format = format;

        console.log('Export params:', params); // Debug log

        const queryString = new URLSearchParams(params).toString();
        const exportUrl = route('report.consignee-volume-analysis.export') + '?' + queryString;

        console.log('Export URL:', exportUrl); // Debug log

        window.location.href = exportUrl;
        push.success(`Export started. ${format.toUpperCase()} download will begin shortly.`);
    } catch (error) {
        console.error('Export error:', error);
        push.error('Export failed. Please try again.');
    }
};

const reportDate = computed(() => {
    let dateStr = '';
    if (filters.date_from && filters.date_to) {
        dateStr = moment(filters.date_from).format('DD/MM/YYYY') + ' - ' + moment(filters.date_to).format('DD/MM/YYYY');
    } else {
        dateStr = 'All Records';
    }

    if (filters.analysis_type === 'remaining') {
        return dateStr + ' (Remaining Consignees)';
    } else if (filters.analysis_type === 'outgoing') {
        return dateStr + ' (Outgoing Consignees)';
    }

    return dateStr;
});

onMounted(() => {
    // Load data on mount with default 'remaining' analysis type
    fetchData();
});
</script>

<style scoped>
.consignee-volume-page {
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

.analysis-type-section {
    margin-bottom: 2rem;
    padding: 1rem;
    background-color: #f8fafc;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
}

.radio-group {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.radio-item {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
}

.radio-label {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
    font-weight: 500;
    color: #374151;
    cursor: pointer;
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

.grand-total {
    font-size: 0.875rem;
    color: #6b7280;
}

@media (max-width: 768px) {
    .consignee-volume-page {
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

    .radio-group {
        gap: 1.5rem;
    }
}
</style>
