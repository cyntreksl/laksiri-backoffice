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
const agents = ref(props.branches);
const stats = ref({
    total_hbls: 0,
    total_short_packages: 0,
});

const lazyParams = reactive({
    page: 1,
    per_page: 25,
    sort_field: 'hbl.created_at',
    sort_order: 'desc',
});

const filters = reactive({
    date_from: new Date(new Date().setDate(new Date().getDate() - 30)),
    date_to: new Date(),
    agent_id: null,
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

        const response = await axios.get(route('report.short-land.data'), { params });

        if (response.data.success) {
            records.value = response.data.data;
            totalRecords.value = response.data.total;
            stats.value = response.data.stats || stats.value;
        }
    } catch (error) {
        console.error('Error fetching short land data:', error);
        push.error('Failed to load short land data');
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
    lazyParams.sort_field = event.sortField || 'hbl.created_at';
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
    filters.agent_id = null;
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
                params[key] = moment(value).format('YYYY-MM-DD');
            } else {
                params[key] = value;
            }
        }
    });

    params.format = format;

    const queryString = new URLSearchParams(params).toString();
    window.location.href = route('report.short-land.export') + '?' + queryString;
    push.success(`Export started. ${format.toUpperCase()} download will begin shortly.`);
};

const reportDate = computed(() => {
    if (filters.date_from && filters.date_to) {
        return moment(filters.date_from).format('DD/MM/YYYY') + ' - ' + moment(filters.date_to).format('DD/MM/YYYY');
    }
    return 'All Records';
});

const selectedAgent = computed(() => {
    if (filters.agent_id) {
        const agent = agents.value.find(a => a.value === filters.agent_id);
        return agent ? agent.label : 'All Agents';
    }
    return 'All Agents';
});

onMounted(() => {
    fetchData();
});
</script>

<template>
    <AppLayout title="Short Land Report">
        <template #header>Short Land Report</template>

        <div class="short-land-page">
            <!-- Page Header -->
            <div class="page-header">
                <div class="header-content">
                    <div class="header-left">
                        <i class="ti ti-package-off text-4xl text-red-500"></i>
                        <div>
                            <h1 class="text-3xl font-bold text-gray-800">Short Land Report</h1>
                            <p class="text-gray-600 mt-1">{{ selectedAgent }} for {{ reportDate }}</p>
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
                            <i class="ti ti-package-off text-3xl text-red-500"></i>
                            <div>
                                <p class="text-sm text-gray-500 uppercase">Short Packages</p>
                                <p class="text-3xl font-bold text-red-600">{{ stats.total_short_packages }}</p>
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
                                <Dropdown
                                    v-model="filters.agent_id"
                                    :options="agents"
                                    class="w-full"
                                    input-id="agent"
                                    optionLabel="label"
                                    optionValue="value"
                                    showClear
                                />
                                <label for="agent">Agent</label>
                            </FloatLabel>

                            <FloatLabel class="w-full" variant="in">
                                <InputText
                                    v-model="filters.search"
                                    class="w-full"
                                    input-id="search"
                                    @keyup.enter="applyFilters"
                                />
                                <label for="search">Search (HBL/Ref/Consignee)</label>
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
                                <i class="ti ti-package-off text-6xl text-gray-300"></i>
                                <p class="text-gray-500 mt-4">No short land records found</p>
                            </div>
                        </template>

                        <template #loading> Loading short land data. Please wait.</template>

                        <Column field="hbl_number" frozen sortable style="min-width: 120px">
                            <template #body="{ data }">
                                <span class="font-mono font-semibold">{{ data.hbl_number }}</span>
                            </template>
                            <template #header>
                                <div class="text-left w-full">HBL No.</div>
                            </template>
                        </Column>

                        <Column field="reference" sortable style="min-width: 120px">
                            <template #body="{ data }">
                                <span class="font-mono">{{ data.reference }}</span>
                            </template>
                            <template #header>
                                <div class="text-left w-full">Ref. No.</div>
                            </template>
                        </Column>

                        <Column field="consignee_name" sortable style="min-width: 250px">
                            <template #body="{ data }">
                                <span>{{ data.consignee_name }}</span>
                            </template>
                            <template #header>
                                <div class="text-left w-full">Consignee Name</div>
                            </template>
                        </Column>

                        <Column field="address" style="min-width: 250px">
                            <template #body="{ data }">
                                <span class="text-sm">{{ data.address }}</span>
                            </template>
                            <template #header>
                                <div class="text-left w-full">Address</div>
                            </template>
                        </Column>

                        <Column field="main_qty" sortable style="min-width: 100px">
                            <template #body="{ data }">
                                <span class="text-center block font-semibold">{{ data.main_qty }}</span>
                            </template>
                            <template #header>
                                <div class="text-center w-full">Mani. Qty</div>
                            </template>
                        </Column>

                        <Column field="typ_pkg" style="min-width: 100px">
                            <template #body="{ data }">
                                <span class="text-center block text-sm">{{ data.typ_pkg }}</span>
                            </template>
                            <template #header>
                                <div class="text-center w-full">Typ Pkg</div>
                            </template>
                        </Column>

                        <Column field="arrival_date" sortable style="min-width: 120px">
                            <template #body="{ data }">
                                <span class="font-mono">{{ data.arrival_date ? moment(data.arrival_date).format('DD/MM/YYYY') : '-' }}</span>
                            </template>
                            <template #header>
                                <div class="text-center w-full">Arrival Date</div>
                            </template>
                        </Column>

                        <Column field="destuff_date" sortable style="min-width: 120px">
                            <template #body="{ data }">
                                <span class="font-mono">{{ data.destuff_date ? moment(data.destuff_date).format('DD/MM/YYYY') : '-' }}</span>
                            </template>
                            <template #header>
                                <div class="text-center w-full">Destuf. Date</div>
                            </template>
                        </Column>

                        <Column field="short_qty" sortable style="min-width: 100px">
                            <template #body="{ data }">
                                <span class="text-center block font-bold text-red-600">{{ data.short_qty }}</span>
                            </template>
                            <template #header>
                                <div class="text-center w-full">Short Qty</div>
                            </template>
                        </Column>

                        <Column field="reci_qty" sortable style="min-width: 100px">
                            <template #body="{ data }">
                                <span class="text-center block font-semibold text-green-600">{{ data.reci_qty }}</span>
                            </template>
                            <template #header>
                                <div class="text-center w-full">Reci. Qty</div>
                            </template>
                        </Column>

                        <template #footer>
                            <div class="flex justify-between items-center">
                                <span>In total there are {{ totalRecords }} short land HBLs.</span>
                            </div>
                        </template>
                    </DataTable>
                </template>
            </Card>
        </div>
    </AppLayout>
</template>

<style scoped>
.short-land-page {
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
    .short-land-page {
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
