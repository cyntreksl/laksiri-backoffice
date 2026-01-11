<script setup>
import { ref, reactive, onMounted, computed } from 'vue';
import axios from 'axios';
import { router } from '@inertiajs/vue3';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Calendar from 'primevue/calendar';
import Select from 'primevue/select';
import Tag from 'primevue/tag';
import Card from 'primevue/card';
import { push } from 'notivue';

const props = defineProps({
    detainTypes: {
        type: Array,
        default: () => [],
    },
    entityLevels: {
        type: Array,
        default: () => [],
    },
});

const loading = ref(false);
const records = ref([]);
const totalRecords = ref(0);
const lazyParams = reactive({
    page: 1,
    per_page: 25,
    sort_field: 'created_at',
    sort_order: 'desc',
});

const filters = reactive({
    date_from: null,
    date_to: null,
    status: null,
    detain_type: null,
    entity_level: null,
    search: '',
});

const statusOptions = [
    { value: null, label: 'All Status' },
    { value: 'detained', label: 'Detained' },
    { value: 'released', label: 'Released' },
];

const fetchData = async () => {
    loading.value = true;
    try {
        const params = {
            ...lazyParams,
            ...filters,
        };

        const response = await axios.get(route('report.detain-report.data'), { params });

        if (response.data.success) {
            records.value = response.data.data;
            totalRecords.value = response.data.total;
        }
    } catch (error) {
        console.error('Error fetching detain report:', error);
        push.error('Failed to load detain report data');
    } finally {
        loading.value = false;
    }
};

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
    filters.date_from = null;
    filters.date_to = null;
    filters.status = null;
    filters.detain_type = null;
    filters.entity_level = null;
    filters.search = '';
    lazyParams.page = 1;
    fetchData();
};

const exportData = () => {
    const params = new URLSearchParams({
        ...lazyParams,
        ...filters,
    });

    window.location.href = route('report.detain-report.export') + '?' + params.toString();
    push.success('Export started. Download will begin shortly.');
};

const getStatusSeverity = (status) => {
    return status === 'Detained' ? 'danger' : 'success';
};

const getDetainTypeSeverity = (type) => {
    const severities = {
        'RTF': 'danger',
        'DDC': 'warn',
        'SDDC': 'warn',
        'IAU': 'warn',
        'DC': 'info',
        'CO': 'secondary',
        'ICT': 'contrast'
    };
    return severities[type] || 'secondary';
};

const getEntityLevelColor = (level) => {
    const colors = {
        'shipment': 'primary',
        'hbl': 'info',
        'package': 'secondary',
    };
    return colors[level] || 'contrast';
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

const summaryStats = computed(() => {
    return {
        total: totalRecords.value,
        detained: records.value.filter(r => r.status === 'Detained').length,
        released: records.value.filter(r => r.status === 'Released').length,
    };
});

onMounted(() => {
    fetchData();
});
</script>

<template>
    <div class="detain-report-page">
        <!-- Page Header -->
        <div class="page-header">
            <div class="header-content">
                <div class="header-left">
                    <i class="ti ti-report text-4xl text-primary"></i>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-800">Detain Report</h1>
                        <p class="text-gray-600 mt-1">Track and view detained shipments, HBLs, and packages</p>
                    </div>
                </div>
                <div class="header-right">
                    <Button
                        :disabled="loading || totalRecords === 0"
                        icon="pi pi-download"
                        label="Export"
                        severity="success"
                        @click="exportData"
                    />
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="summary-cards">
            <Card class="summary-card">
                <template #content>
                    <div class="card-content">
                        <i class="ti ti-list-details text-3xl text-blue-500"></i>
                        <div>
                            <p class="text-sm text-gray-500 uppercase">Total Records</p>
                            <p class="text-3xl font-bold text-gray-800">{{ summaryStats.total }}</p>
                        </div>
                    </div>
                </template>
            </Card>

            <Card class="summary-card">
                <template #content>
                    <div class="card-content">
                        <i class="pi pi-lock text-3xl text-red-500"></i>
                        <div>
                            <p class="text-sm text-gray-500 uppercase">Currently Detained</p>
                            <p class="text-3xl font-bold text-red-600">{{ summaryStats.detained }}</p>
                        </div>
                    </div>
                </template>
            </Card>

            <Card class="summary-card">
                <template #content>
                    <div class="card-content">
                        <i class="pi pi-lock-open text-3xl text-green-500"></i>
                        <div>
                            <p class="text-sm text-gray-500 uppercase">Released</p>
                            <p class="text-3xl font-bold text-green-600">{{ summaryStats.released }}</p>
                        </div>
                    </div>
                </template>
            </Card>
        </div>

        <!-- Filters Section -->
        <Card class="filters-card">
            <template #content>
                <div class="filters-grid">
                    <!-- Date Range -->
                    <div class="filter-item">
                        <label class="filter-label">Date From</label>
                        <Calendar
                            v-model="filters.date_from"
                            dateFormat="yy-mm-dd"
                            placeholder="Select start date"
                            showIcon
                        />
                    </div>

                    <div class="filter-item">
                        <label class="filter-label">Date To</label>
                        <Calendar
                            v-model="filters.date_to"
                            dateFormat="yy-mm-dd"
                            placeholder="Select end date"
                            showIcon
                        />
                    </div>

                    <!-- Status -->
                    <div class="filter-item">
                        <label class="filter-label">Status</label>
                        <Select
                            v-model="filters.status"
                            :options="statusOptions"
                            optionLabel="label"
                            optionValue="value"
                            placeholder="Select status"
                        />
                    </div>

                    <!-- Detain Type -->
                    <div class="filter-item">
                        <label class="filter-label">Detain Type</label>
                        <Select
                            v-model="filters.detain_type"
                            :options="detainTypes"
                            optionLabel="label"
                            optionValue="value"
                            placeholder="Select detain type"
                        />
                    </div>

                    <!-- Entity Level -->
                    <div class="filter-item">
                        <label class="filter-label">Entity Level</label>
                        <Select
                            v-model="filters.entity_level"
                            :options="entityLevels"
                            optionLabel="label"
                            optionValue="value"
                            placeholder="Select entity level"
                        />
                    </div>

                    <!-- Search -->
                    <div class="filter-item">
                        <label class="filter-label">Search</label>
                        <InputText
                            v-model="filters.search"
                            placeholder="Search reason or remarks..."
                            @keyup.enter="applyFilters"
                        />
                    </div>
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
            </template>
        </Card>

        <!-- Data Table -->
        <Card class="table-card">
            <template #content>
                <DataTable
                    :lazy="true"
                    :loading="loading"
                    :paginator="true"
                    :rows="lazyParams.per_page"
                    :rowsPerPageOptions="[10, 25, 50, 100]"
                    :sortField="lazyParams.sort_field"
                    :sortOrder="lazyParams.sort_order === 'asc' ? 1 : -1"
                    :totalRecords="totalRecords"
                    :value="records"
                    class="detain-table"
                    currentPageReportTemplate="Showing {first} to {last} of {totalRecords} records"
                    paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                    sortMode="single"
                    stripedRows
                    @page="onPage"
                    @sort="onSort"
                >
                    <template #empty>
                        <div class="empty-state">
                            <i class="ti ti-database-off text-6xl text-gray-300"></i>
                            <p class="text-gray-500 mt-4">No detain records found</p>
                        </div>
                    </template>

                    <Column field="id" header="ID" sortable style="min-width: 80px">
                        <template #body="{ data }">
                            <span class="font-mono text-sm">#{{ data.id }}</span>
                        </template>
                    </Column>

                    <Column field="shipment_reference" header="Shipment Ref" sortable style="min-width: 150px">
                        <template #body="{ data }">
                            <span class="font-mono font-semibold">{{ data.shipment_reference || 'N/A' }}</span>
                        </template>
                    </Column>

                    <Column field="hbl_reference" header="HBL Ref" sortable style="min-width: 150px">
                        <template #body="{ data }">
                            <span class="font-mono">{{ data.hbl_reference || 'N/A' }}</span>
                        </template>
                    </Column>

                    <Column field="package_number" header="Package #" sortable style="min-width: 120px">
                        <template #body="{ data }">
                            <span class="font-mono">{{ data.package_number || 'N/A' }}</span>
                        </template>
                    </Column>

                    <Column field="entity_level" header="Level" sortable style="min-width: 120px">
                        <template #body="{ data }">
                            <Tag
                                :severity="getEntityLevelColor(data.entity_level)"
                                :value="data.entity_level"
                                class="text-xs uppercase"
                            />
                        </template>
                    </Column>

                    <Column field="detain_type" header="Detain Type" sortable style="min-width: 120px">
                        <template #body="{ data }">
                            <Tag
                                :severity="getDetainTypeSeverity(data.detain_type)"
                                :value="data.detain_type"
                                class="font-semibold"
                            />
                        </template>
                    </Column>

                    <Column field="detain_reason" header="Detain Reason" style="min-width: 250px">
                        <template #body="{ data }">
                            <span class="text-sm">{{ data.detain_reason || 'N/A' }}</span>
                        </template>
                    </Column>

                    <Column field="detained_date" header="Detained Date" sortable style="min-width: 180px">
                        <template #body="{ data }">
                            <span class="text-sm">{{ formatDate(data.detained_date) }}</span>
                        </template>
                    </Column>

                    <Column field="detention_duration_human" header="Duration" sortable style="min-width: 120px">
                        <template #body="{ data }">
                            <span class="font-mono text-sm font-semibold">
                                {{ data.detention_duration_human || 'N/A' }}
                            </span>
                        </template>
                    </Column>

                    <Column field="status" header="Status" sortable style="min-width: 120px">
                        <template #body="{ data }">
                            <Tag
                                :severity="getStatusSeverity(data.status)"
                                :value="data.status"
                                class="font-semibold"
                            >
                                <template #icon>
                                    <i :class="data.status === 'Detained' ? 'pi pi-lock' : 'pi pi-lock-open'" class="mr-1"></i>
                                </template>
                            </Tag>
                        </template>
                    </Column>

                    <Column field="released_date" header="Released Date" sortable style="min-width: 180px">
                        <template #body="{ data }">
                            <span class="text-sm">{{ formatDate(data.released_date) }}</span>
                        </template>
                    </Column>

                    <Column field="detained_by.name" header="Detained By" style="min-width: 150px">
                        <template #body="{ data }">
                            <div v-if="data.detained_by" class="flex items-center gap-2">
                                <i class="pi pi-user text-gray-400"></i>
                                <span class="text-sm">{{ data.detained_by.name }}</span>
                            </div>
                            <span v-else class="text-gray-400">N/A</span>
                        </template>
                    </Column>

                    <Column field="remarks" header="Remarks" style="min-width: 200px">
                        <template #body="{ data }">
                            <span class="text-sm italic text-gray-600">{{ data.remarks || 'N/A' }}</span>
                        </template>
                    </Column>
                </DataTable>
            </template>
        </Card>
    </div>
</template>

<style scoped>
.detain-report-page {
    padding: 1.5rem;
    max-width: 100%;
}

/* Page Header */
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

/* Summary Cards */
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

/* Filters */
.filters-card {
    margin-bottom: 2rem;
    border: 1px solid #e5e7eb;
}

.filters-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 1rem;
}

.filter-item {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.filter-label {
    font-size: 0.875rem;
    font-weight: 600;
    color: #374151;
}

.filter-actions {
    display: flex;
    gap: 0.75rem;
    padding-top: 1rem;
    border-top: 1px solid #e5e7eb;
}

/* Table */
.table-card {
    border: 1px solid #e5e7eb;
}

.detain-table {
    font-size: 0.875rem;
}

.detain-table :deep(.p-datatable-header) {
    background: #f9fafb;
    border-bottom: 2px solid #e5e7eb;
}

.detain-table :deep(.p-datatable-thead > tr > th) {
    background: #f9fafb;
    color: #374151;
    font-weight: 600;
    padding: 1rem;
}

.detain-table :deep(.p-datatable-tbody > tr > td) {
    padding: 0.875rem 1rem;
}

.detain-table :deep(.p-datatable-tbody > tr:hover) {
    background: #f9fafb;
}

.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 4rem 2rem;
}

/* Responsive */
@media (max-width: 768px) {
    .detain-report-page {
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
