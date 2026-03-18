<template>
    <AppLayout title="Manifest Listing Report">
        <template #header>Manifest Listing Report</template>

        <div class="manifest-listing-page">
            <!-- Page Header -->
            <div class="page-header">
                <div class="header-content">
                    <div class="header-left">
                        <i class="ti ti-file-text text-4xl text-primary"></i>
                        <div>
                            <h1 class="text-3xl font-bold text-gray-800">Manifest Listing Report</h1>
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
                                <p class="text-sm text-gray-500 uppercase">Total Manifests</p>
                                <p class="text-3xl font-bold text-blue-600">{{ stats?.total_manifests || 0 }}</p>
                            </div>
                        </div>
                    </template>
                </Card>

                <Card class="summary-card">
                    <template #content>
                        <div class="card-content">
                            <i class="ti ti-package text-3xl text-green-500"></i>
                            <div>
                                <p class="text-sm text-gray-500 uppercase">Total Containers</p>
                                <p class="text-3xl font-bold text-green-600">{{ stats?.total_containers || 0 }}</p>
                            </div>
                        </div>
                    </template>
                </Card>

                <Card class="summary-card">
                    <template #content>
                        <div class="card-content">
                            <i class="ti ti-users text-3xl text-purple-500"></i>
                            <div>
                                <p class="text-sm text-gray-500 uppercase">Total Consignees</p>
                                <p class="text-3xl font-bold text-purple-600">{{ stats?.total_consignees || 0 }}</p>
                            </div>
                        </div>
                    </template>
                </Card>

                <Card class="summary-card">
                    <template #content>
                        <div class="card-content">
                            <i class="ti ti-box text-3xl text-orange-500"></i>
                            <div>
                                <p class="text-sm text-gray-500 uppercase">Total Packages</p>
                                <p class="text-3xl font-bold text-orange-600">{{ stats?.total_packages || 0 }}</p>
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
                                    v-model="filters.branch_id"
                                    :options="branches"
                                    class="w-full"
                                    input-id="branch"
                                    optionLabel="label"
                                    optionValue="value"
                                    showClear
                                />
                                <label for="branch">Branch (Agent Name)</label>
                            </FloatLabel>

                            <FloatLabel class="w-full" variant="in">
                                <Dropdown
                                    v-model="filters.created_user_id"
                                    :options="users"
                                    class="w-full"
                                    input-id="created-user"
                                    optionLabel="label"
                                    optionValue="value"
                                    showClear
                                />
                                <label for="created-user">Created User</label>
                            </FloatLabel>

                            <FloatLabel class="w-full" variant="in">
                                <InputText
                                    v-model="filters.manifest_number_from"
                                    class="w-full"
                                    input-id="manifest-from"
                                />
                                <label for="manifest-from">Manifest Number From</label>
                            </FloatLabel>

                            <FloatLabel class="w-full" variant="in">
                                <InputText
                                    v-model="filters.manifest_number_to"
                                    class="w-full"
                                    input-id="manifest-to"
                                />
                                <label for="manifest-to">Manifest Number To</label>
                            </FloatLabel>

                            <FloatLabel class="w-full" variant="in">
                                <InputText
                                    v-model="filters.search"
                                    class="w-full"
                                    input-id="search"
                                    @keyup.enter="applyFilters"
                                />
                                <label for="search">Search (Manifest/Vessel)</label>
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
                                <p class="text-gray-500 mt-4">No manifest records found</p>
                            </div>
                        </template>

                        <template #loading>Loading manifest data. Please wait.</template>

                        <Column field="manifest_number" frozen sortable style="min-width: 150px">
                            <template #body="{ data }">
                                <span class="font-mono font-semibold">{{ data.manifest_number }}</span>
                            </template>
                            <template #header>
                                <div class="text-left w-full">Manifest No</div>
                            </template>
                        </Column>

                        <Column field="date" sortable style="min-width: 120px">
                            <template #body="{ data }">
                                <span class="font-mono">{{ data.date }}</span>
                            </template>
                            <template #header>
                                <div class="text-center w-full">Date</div>
                            </template>
                        </Column>

                        <Column field="agent_name" style="min-width: 200px">
                            <template #body="{ data }">
                                <span>{{ data.agent_name }}</span>
                            </template>
                            <template #header>
                                <div class="text-left w-full">Agent Name</div>
                            </template>
                        </Column>

                        <Column field="vessel_name" sortable style="min-width: 250px">
                            <template #body="{ data }">
                                <span class="text-sm">{{ data.vessel_name }}</span>
                            </template>
                            <template #header>
                                <div class="text-left w-full">Vessel Name</div>
                            </template>
                        </Column>

                        <Column field="no_of_consignee" sortable style="min-width: 120px">
                            <template #body="{ data }">
                                <span class="text-center block font-semibold">{{ data.no_of_consignee }}</span>
                            </template>
                            <template #header>
                                <div class="text-center w-full">No of Consignee</div>
                            </template>
                        </Column>

                        <Column field="no_of_packages" sortable style="min-width: 120px">
                            <template #body="{ data }">
                                <span class="text-center block font-semibold">{{ data.no_of_packages }}</span>
                            </template>
                            <template #header>
                                <div class="text-center w-full">No of Packages</div>
                            </template>
                        </Column>

                        <Column field="user_id" style="min-width: 150px">
                            <template #body="{ data }">
                                <span>{{ data.user_id }}</span>
                            </template>
                            <template #header>
                                <div class="text-left w-full">User Id</div>
                            </template>
                        </Column>

                        <template #footer>
                            <div class="flex justify-between items-center">
                                <span>In total there are {{ totalRecords }} manifest records.</span>
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
    users: {
        type: Array,
        default: () => [],
    },
});

const loading = ref(false);
const records = ref([]);
const totalRecords = ref(0);
const branches = ref(props.branches);
const users = ref(props.users);
const stats = ref({
    total_manifests: 0,
    total_containers: 0,
    total_consignees: 0,
    total_packages: 0,
});

const lazyParams = reactive({
    page: 1,
    per_page: 25,
    sort_field: 'manifest_generated_at',
    sort_order: 'desc',
});

const filters = reactive({
    date_from: null,
    date_to: null,
    branch_id: null,
    created_user_id: null,
    manifest_number_from: '',
    manifest_number_to: '',
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

        const response = await axios.get(route('report.manifest-listing.data'), { params });

        if (response.data.success) {
            records.value = response.data.data;
            totalRecords.value = response.data.total;
            stats.value = response.data.stats || stats.value;
        }
    } catch (error) {
        console.error('Error fetching manifest listing data:', error);
        push.error('Failed to load manifest listing data');
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
    lazyParams.sort_field = event.sortField || 'manifest_generated_at';
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
    filters.created_user_id = null;
    filters.manifest_number_from = '';
    filters.manifest_number_to = '';
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
        const exportUrl = route('report.manifest-listing.export') + '?' + queryString;

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

const selectedAgent = computed(() => {
    if (filters.branch_id) {
        const branch = branches.value.find(b => b.value === filters.branch_id);
        return branch ? branch.label : 'All Agents';
    }
    return 'All Agents';
});

onMounted(() => {
    fetchData();
});
</script>

<style scoped>
.manifest-listing-page {
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
    .manifest-listing-page {
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
