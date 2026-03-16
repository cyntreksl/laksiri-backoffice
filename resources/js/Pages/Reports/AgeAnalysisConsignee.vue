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
const containers = ref([]);
const loadingContainers = ref(false);
const stats = ref({
    total_hbls: 0,
    total_packages: 0
});

const debugInfo = ref(null);

const lazyParams = reactive({
    page: 1,
    per_page: 25,
    sort_field: 'destuffing_date',
    sort_order: 'desc',
});

const filters = reactive({
    date_from: new Date(new Date().setDate(new Date().getDate() - 30)),
    date_to: new Date(),
    agent_from: null,
    agent_to: null,
    container_id: null,
    search: '',
});

const fetchContainers = async () => {
    if (!filters.agent_from && !filters.agent_to) {
        containers.value = [];
        return;
    }

    loadingContainers.value = true;
    try {
        const params = {};
        if (filters.agent_from) params.agent_from = filters.agent_from;
        if (filters.agent_to) params.agent_to = filters.agent_to;

        const response = await axios.get(route('report.age-analysis-consignee.containers'), { params });
        containers.value = response.data;
    } catch (error) {
        console.error('Error fetching containers:', error);
        containers.value = [];
    } finally {
        loadingContainers.value = false;
    }
};

// Watch for agent changes to update container list
watch([() => filters.agent_from, () => filters.agent_to], () => {
    filters.container_id = null; // Reset container selection
    fetchContainers();
});

const canFetchData = computed(() => {
    return filters.agent_from && filters.agent_to;
});

const dt = ref();

const fetchData = async () => {
    if (!canFetchData.value) {
        records.value = [];
        totalRecords.value = 0;
        stats.value = {
            total_hbls: 0,
            total_packages: 0,
        };
        return;
    }

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

        const response = await axios.get(route('report.age-analysis-consignee.data'), { params });

        if (response.data.success) {
            records.value = response.data.data;
            totalRecords.value = response.data.total;
            stats.value = response.data.stats || stats.value;
            debugInfo.value = response.data.debug || null;
        }
    } catch (error) {
        console.error('Error fetching age analysis data:', error);
        push.error('Failed to load age analysis data');
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
    lazyParams.sort_field = event.sortField || 'destuffing_date';
    lazyParams.sort_order = event.sortOrder === 1 ? 'asc' : 'desc';
    fetchData();
};

const applyFilters = () => {
    lazyParams.page = 1;
    fetchData();
};

const resetFilters = () => {
    filters.date_from = new Date(new Date().setDate(new Date().getDate() - 30));
    filters.date_to = new Date();
    filters.agent_from = null;
    filters.agent_to = null;
    filters.container_id = null;
    filters.search = '';
    containers.value = [];
    lazyParams.page = 1;
    fetchData();
};

const exportData = (format = 'xlsx') => {
    if (!canFetchData.value) {
        push.error('Please select both Agent From and Agent To before exporting.');
        return;
    }

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
                params[key] = moment(value).format('YYYY-MM-DD');
            } else {
                params[key] = value;
            }
        }
    });

    params.format = format;

    const queryString = new URLSearchParams(params).toString();
    window.location.href = route('report.age-analysis-consignee.export') + '?' + queryString;
    push.success(`Export started. ${format.toUpperCase()} download will begin shortly.`);
};

const reportDate = computed(() => {
    if (filters.date_from && filters.date_to) {
        return moment(filters.date_from).format('DD/MM/YYYY') + ' - ' + moment(filters.date_to).format('DD/MM/YYYY');
    }
    return 'All Records';
});

const selectedAgentFrom = computed(() => {
    if (filters.agent_from) {
        const branch = branches.value.find(b => b.value === filters.agent_from);
        return branch ? branch.label : 'All';
    }
    return 'All';
});

const selectedAgentTo = computed(() => {
    if (filters.agent_to) {
        const branch = branches.value.find(b => b.value === filters.agent_to);
        return branch ? branch.label : 'All';
    }
    return 'All';
});

onMounted(() => {
    // Don't fetch data on mount - wait for user to select agents
});
</script>

<template>
    <AppLayout title="Age Analysis of Consignees">
        <template #header>Age Analysis of Consignees</template>

        <div class="age-analysis-page">
            <!-- Page Header -->
            <div class="page-header">
                <div class="header-content">
                    <div class="header-left">
                        <i class="ti ti-clock text-4xl text-blue-500"></i>
                        <div>
                            <h1 class="text-3xl font-bold text-gray-800">Age Analysis of Consignees</h1>
                            <p class="text-gray-600 mt-1">{{ selectedAgentFrom }} to {{ selectedAgentTo }} for {{ reportDate }}</p>
                        </div>
                    </div>
                    <div class="header-right">
                        <Button
                            :disabled="loading || totalRecords === 0 || !canFetchData"
                            icon="pi pi-file-pdf"
                            label="PDF"
                            severity="danger"
                            @click="exportData('pdf')"
                        />
                        <Button
                            :disabled="loading || totalRecords === 0 || !canFetchData"
                            icon="pi pi-file-excel"
                            label="Excel"
                            severity="success"
                            @click="exportData('xlsx')"
                        />
                        <Button
                            :disabled="loading || totalRecords === 0 || !canFetchData"
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
                                <p class="text-3xl font-bold text-blue-600">{{ stats.total_hbls }}</p>
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
                                <p class="text-3xl font-bold text-green-600">{{ stats.total_packages }}</p>
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
                                <label for="date-from">From Date</label>
                            </FloatLabel>

                            <FloatLabel class="w-full" variant="in">
                                <Calendar
                                    v-model="filters.date_to"
                                    class="w-full"
                                    dateFormat="yy-mm-dd"
                                    input-id="date-to"
                                    showIcon
                                />
                                <label for="date-to">To Date</label>
                            </FloatLabel>

                            <FloatLabel class="w-full" variant="in">
                                <Dropdown
                                    v-model="filters.agent_from"
                                    :options="branches"
                                    class="w-full"
                                    input-id="agent-from"
                                    optionLabel="label"
                                    optionValue="value"
                                    placeholder="Select Agent From"
                                    showClear
                                />
                                <label for="agent-from">Agent From *</label>
                            </FloatLabel>

                            <FloatLabel class="w-full" variant="in">
                                <Dropdown
                                    v-model="filters.agent_to"
                                    :options="branches"
                                    class="w-full"
                                    input-id="agent-to"
                                    optionLabel="label"
                                    optionValue="value"
                                    placeholder="Select Agent To"
                                    showClear
                                />
                                <label for="agent-to">Agent To *</label>
                            </FloatLabel>

                            <FloatLabel class="w-full" variant="in">
                                <Dropdown
                                    v-model="filters.container_id"
                                    :disabled="!canFetchData"
                                    :loading="loadingContainers"
                                    :options="containers"
                                    class="w-full"
                                    input-id="container"
                                    optionLabel="label"
                                    optionValue="value"
                                    placeholder="Select Container (Optional)"
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
                                <label for="search">Search (HBL/Consignee/Manifest)</label>
                            </FloatLabel>
                        </div>

                        <div class="filter-actions">
                            <Button
                                :disabled="!canFetchData"
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

                        <div v-if="!canFetchData" class="mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                            <div class="flex items-center">
                                <i class="ti ti-info-circle text-yellow-600 mr-2"></i>
                                <p class="text-yellow-800 font-medium">Please select both "Agent From" and "Agent To" to view the report data.</p>
                            </div>
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
                        dataKey="hbl_number"
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
                                <i class="ti ti-clock text-6xl text-gray-300"></i>
                                <p v-if="!canFetchData" class="text-gray-500 mt-4">Please select Agent From and Agent To to view data</p>
                                <p v-else class="text-gray-500 mt-4">No age analysis records found</p>
                                <div v-if="debugInfo" class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg text-sm">
                                    <p class="font-semibold text-blue-800 mb-2">Debug Information:</p>
                                    <p class="text-blue-700">Container ID: {{ debugInfo.container_id }}</p>
                                    <p class="text-blue-700">Container Info: {{ JSON.stringify(debugInfo.container_info) }}</p>
                                    <p class="text-blue-700">HBL Count for Container: {{ debugInfo.hbl_count_for_container }}</p>
                                </div>
                            </div>
                        </template>

                        <template #loading> Loading age analysis data. Please wait.</template>

                        <Column field="hbl_number" frozen sortable style="min-width: 120px">
                            <template #body="{ data }">
                                <span class="font-mono font-semibold">{{ data.hbl_number }}</span>
                            </template>
                            <template #header>
                                <div class="text-left w-full">HBL No</div>
                            </template>
                        </Column>

                        <Column field="consignee_name" sortable style="min-width: 300px">
                            <template #body="{ data }">
                                <div>
                                    <div class="font-semibold">{{ data.consignee_name }}</div>
                                    <div class="text-sm text-gray-600">{{ data.address }}</div>
                                </div>
                            </template>
                            <template #header>
                                <div class="text-left w-full">Consignee Name & Address</div>
                            </template>
                        </Column>

                        <Column field="type_of_package" style="min-width: 150px">
                            <template #body="{ data }">
                                <span class="text-sm">{{ data.type_of_package || '-' }}</span>
                            </template>
                            <template #header>
                                <div class="text-center w-full">Type of Package</div>
                            </template>
                        </Column>

                        <Column field="qty_manifest" sortable style="min-width: 100px">
                            <template #body="{ data }">
                                <span class="text-center block font-semibold">{{ data.qty_manifest }}</span>
                            </template>
                            <template #header>
                                <div class="text-center w-full">Qty (Mani)</div>
                            </template>
                        </Column>

                        <Column field="qty_actual" sortable style="min-width: 100px">
                            <template #body="{ data }">
                                <span class="text-center block font-semibold">{{ data.qty_actual }}</span>
                            </template>
                            <template #header>
                                <div class="text-center w-full">Qty (Actual)</div>
                            </template>
                        </Column>

                        <Column field="destuffing_date" sortable style="min-width: 120px">
                            <template #body="{ data }">
                                <span class="font-mono">{{ data.destuffing_date || '-' }}</span>
                            </template>
                            <template #header>
                                <div class="text-center w-full">Destuffing Date</div>
                            </template>
                        </Column>

                        <Column field="no_of_days" sortable style="min-width: 100px">
                            <template #body="{ data }">
                                <span :class="data.no_of_days > 30 ? 'text-red-600' : data.no_of_days > 15 ? 'text-orange-600' : 'text-green-600'" class="text-center block font-bold">
                                    {{ data.no_of_days }}
                                </span>
                            </template>
                            <template #header>
                                <div class="text-center w-full">No of Days</div>
                            </template>
                        </Column>

                        <template #footer>
                            <div class="flex justify-between items-center">
                                <span>In total there are {{ totalRecords }} consignee records.</span>
                            </div>
                        </template>
                    </DataTable>
                </template>
            </Card>
        </div>
    </AppLayout>
</template>

<style scoped>
.age-analysis-page {
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
    .age-analysis-page {
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
