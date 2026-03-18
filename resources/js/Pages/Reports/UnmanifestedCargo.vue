<template>
    <AppLayout title="Unmanifested Cargo Report">
        <template #header>Unmanifested Cargo Report</template>

        <div class="unmanifested-cargo-page">
            <!-- Page Header -->
            <div class="page-header">
                <div class="header-content">
                    <div class="header-left">
                        <i class="ti ti-package text-4xl text-primary"></i>
                        <div>
                            <h1 class="text-3xl font-bold text-gray-800">Unmanifested Cargo Report</h1>
                            <p class="text-gray-600 mt-1">{{ reportDate }}</p>
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
                            <i class="ti ti-stack text-3xl text-orange-500"></i>
                            <div>
                                <p class="text-sm text-gray-500 uppercase">Total Quantity</p>
                                <p class="text-3xl font-bold text-orange-600">{{ stats?.total_quantity || 0 }}</p>
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
                                <InputText
                                    v-model="filters.manifest_number_from"
                                    class="w-full"
                                    input-id="manifest-from"
                                />
                                <label for="manifest-from">Manifest No From</label>
                            </FloatLabel>

                            <FloatLabel class="w-full" variant="in">
                                <InputText
                                    v-model="filters.manifest_number_to"
                                    class="w-full"
                                    input-id="manifest-to"
                                />
                                <label for="manifest-to">Manifest No To</label>
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
                                <label for="branch">Agent</label>
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
                                <i class="ti ti-package text-6xl text-gray-300"></i>
                                <p class="text-gray-500 mt-4">No unmanifested cargo records found</p>
                            </div>
                        </template>

                        <template #loading>Loading unmanifested cargo data. Please wait.</template>

                        <Column field="agent_name" sortable style="min-width: 200px">
                            <template #body="{ data }">
                                <span>{{ data.agent_name }}</span>
                            </template>
                            <template #header>
                                <div class="text-left w-full">Agent Name</div>
                            </template>
                        </Column>

                        <Column field="destuff_date" sortable style="min-width: 120px">
                            <template #body="{ data }">
                                <span class="font-mono">{{ data.destuff_date }}</span>
                            </template>
                            <template #header>
                                <div class="text-center w-full">Destuff Date</div>
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

                        <Column field="type_of_package" style="min-width: 150px">
                            <template #body="{ data }">
                                <span>{{ data.type_of_package }}</span>
                            </template>
                            <template #header>
                                <div class="text-center w-full">Type of Package</div>
                            </template>
                        </Column>

                        <Column field="quantity" style="min-width: 100px">
                            <template #body="{ data }">
                                <span class="text-center block font-semibold text-blue-600">{{ data.quantity }}</span>
                            </template>
                            <template #header>
                                <div class="text-center w-full">Qty</div>
                            </template>
                        </Column>

                        <template #footer>
                            <div class="flex justify-between items-center">
                                <span>In total there are {{ totalRecords }} unmanifested cargo records.</span>
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
    total_hbls: 0,
    total_packages: 0,
    total_quantity: 0,
    total_agents: 0,
});

const lazyParams = reactive({
    page: 1,
    per_page: 25,
    sort_field: 'hbl_no',
    sort_order: 'asc',
});

const filters = reactive({
    date_from: null,
    date_to: null,
    manifest_number_from: '',
    manifest_number_to: '',
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

        const response = await axios.get(route('report.unmanifested-cargo.data'), { params });

        if (response.data.success) {
            records.value = response.data.data;
            totalRecords.value = response.data.total;
            stats.value = response.data.stats || stats.value;
        }
    } catch (error) {
        console.error('Error fetching unmanifested cargo data:', error);
        push.error('Failed to load unmanifested cargo data');
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
    lazyParams.sort_field = event.sortField || 'hbl_no';
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
    filters.manifest_number_from = '';
    filters.manifest_number_to = '';
    filters.branch_id = null;
    filters.search = '';
    lazyParams.page = 1;
    fetchData();
};

const exportData = (format = 'xlsx') => {
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
        const exportUrl = route('report.unmanifested-cargo.export') + '?' + queryString;

        console.log('Export URL:', exportUrl); // Debug log

        window.location.href = exportUrl;
        push.success(`Export started. ${format.toUpperCase()} download will begin shortly.`);
    } catch (error) {
        console.error('Export error:', error);
        push.error('Export failed. Please try again.');
    }
};

const reportDate = computed(() => {
    if (filters.date_from && filters.date_to) {
        return moment(filters.date_from).format('DD/MM/YYYY') + ' - ' + moment(filters.date_to).format('DD/MM/YYYY');
    }
    return 'All Records';
});

onMounted(() => {
    fetchData();
});
</script>

<style scoped>
.unmanifested-cargo-page {
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
    .unmanifested-cargo-page {
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