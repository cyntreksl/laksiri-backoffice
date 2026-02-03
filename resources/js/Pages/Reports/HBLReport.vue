<script setup>
import { ref, reactive, onMounted, computed } from 'vue';
import axios from 'axios';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
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
    cargoTypes: {
        type: Array,
        default: () => [],
    },
    hblTypes: {
        type: Array,
        default: () => [],
    },
    branches: {
        type: Array,
        default: () => [],
    },
    containers: {
        type: Array,
        default: () => [],
    },
    customers: {
        type: Array,
        default: () => [],
    },
});

const loading = ref(false);
const records = ref([]);
const totalRecords = ref(0);
const stats = ref({
    total_hbls: 0,
    total_amount: '0.00',
    total_paid: '0.00',
    total_packages: 0,
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
    branch_id: null,
    customer_search: '',
    appointment_date_from: null,
    appointment_date_to: null,
    token_issued_date_from: null,
    token_issued_date_to: null,
    gate_pass_date_from: null,
    gate_pass_date_to: null,
    cashier_invoice_date_from: null,
    cashier_invoice_date_to: null,
    document_verified_date_from: null,
    document_verified_date_to: null,
    container_id: null,
    cargo_type: null,
    hbl_type: null,
    search: '',
});

const fetchData = async () => {
    loading.value = true;
    try {
        const params = {
            ...lazyParams,
            ...filters,
        };

        const response = await axios.get(route('report.hbl-report.data'), { params });

        if (response.data.success) {
            records.value = response.data.data;
            totalRecords.value = response.data.total;
            stats.value = response.data.stats || stats.value;
        }
    } catch (error) {
        console.error('Error fetching HBL report:', error);
        push.error('Failed to load HBL report data');
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
    Object.keys(filters).forEach(key => {
        filters[key] = null;
    });
    filters.customer_search = '';
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
    window.location.href = route('report.hbl-report.export') + '?' + queryString;
    push.success(`Export started. ${format.toUpperCase()} download will begin shortly.`);
};

const getCargoTypeSeverity = (type) => {
    const severities = {
        'General': 'info',
        'Dangerous': 'danger',
        'Perishable': 'warn',
    };
    return severities[type] || 'secondary';
};

const getHBLTypeSeverity = (type) => {
    const severities = {
        'Normal': 'primary',
        'Door to Door': 'success',
        'Third Party': 'secondary',
    };
    return severities[type] || 'contrast';
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

const formatCurrency = (amount) => {
    if (!amount) return '0.00';
    return parseFloat(amount.toString().replace(/,/g, '')).toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
};

onMounted(() => {
    fetchData();
});
</script>

<template>
    <AppLayout title="HBL Report">
        <template #header>HBL Report</template>

        <div class="hbl-report-page">
            <!-- Page Header -->
            <div class="page-header">
                <div class="header-content">
                    <div class="header-left">
                        <i class="ti ti-file-invoice text-4xl text-primary"></i>
                        <div>
                            <h1 class="text-3xl font-bold text-gray-800">HBL Report</h1>
                            <p class="text-gray-600 mt-1">Comprehensive HBL records with operational and financial milestones</p>
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
                            <i class="ti ti-file-invoice text-3xl text-blue-500"></i>
                            <div>
                                <p class="text-sm text-gray-500 uppercase">Total HBLs</p>
                                <p class="text-3xl font-bold text-gray-800">{{ stats.total_hbls }}</p>
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
                                <p class="text-3xl font-bold text-purple-600">{{ stats.total_packages }}</p>
                            </div>
                        </div>
                    </template>
                </Card>

                <Card class="summary-card">
                    <template #content>
                        <div class="card-content">
                            <i class="ti ti-currency-dollar text-3xl text-green-500"></i>
                            <div>
                                <p class="text-sm text-gray-500 uppercase">Total Amount</p>
                                <p class="text-3xl font-bold text-green-600">{{ formatCurrency(stats.total_amount) }}</p>
                            </div>
                        </div>
                    </template>
                </Card>

                <Card class="summary-card">
                    <template #content>
                        <div class="card-content">
                            <i class="ti ti-cash text-3xl text-teal-500"></i>
                            <div>
                                <p class="text-sm text-gray-500 uppercase">Total Paid</p>
                                <p class="text-3xl font-bold text-teal-600">{{ formatCurrency(stats.total_paid) }}</p>
                            </div>
                        </div>
                    </template>
                </Card>
            </div>

            <!-- Filters Section -->
            <Card class="filters-card">
                <template #content>
                    <div class="filters-section">
                        <h3 class="text-lg font-semibold text-gray-700 mb-4">Filters</h3>

                        <div class="filters-grid">
                            <!-- Loaded Date Range -->
                            <div class="filter-item">
                                <label class="filter-label">Loaded Date From</label>
                                <Calendar
                                    v-model="filters.loaded_date_from"
                                    dateFormat="yy-mm-dd"
                                    placeholder="Select date"
                                    showIcon
                                />
                            </div>

                            <div class="filter-item">
                                <label class="filter-label">Loaded Date To</label>
                                <Calendar
                                    v-model="filters.loaded_date_to"
                                    dateFormat="yy-mm-dd"
                                    placeholder="Select date"
                                    showIcon
                                />
                            </div>

                            <!-- Unloaded Date Range -->
                            <div class="filter-item">
                                <label class="filter-label">Unloaded Date From</label>
                                <Calendar
                                    v-model="filters.unloaded_date_from"
                                    dateFormat="yy-mm-dd"
                                    placeholder="Select date"
                                    showIcon
                                />
                            </div>

                            <div class="filter-item">
                                <label class="filter-label">Unloaded Date To</label>
                                <Calendar
                                    v-model="filters.unloaded_date_to"
                                    dateFormat="yy-mm-dd"
                                    placeholder="Select date"
                                    showIcon
                                />
                            </div>

                            <!-- Branch -->
                            <div class="filter-item">
                                <label class="filter-label">Branch/Agent</label>
                                <Select
                                    v-model="filters.branch_id"
                                    :options="branches"
                                    optionLabel="label"
                                    optionValue="value"
                                    placeholder="Select branch"
                                    filter
                                    showClear
                                />
                            </div>

                            <!-- Customer Search -->
                            <div class="filter-item">
                                <label class="filter-label">Customer</label>
                                <Select
                                    v-model="filters.customer_search"
                                    :options="customers"
                                    filter
                                    optionLabel="label"
                                    optionValue="value"
                                    placeholder="Select customer"
                                    showClear
                                />
                            </div>

                            <!-- Appointment Date Range -->
                            <div class="filter-item">
                                <label class="filter-label">Appointment Date From</label>
                                <Calendar
                                    v-model="filters.appointment_date_from"
                                    dateFormat="yy-mm-dd"
                                    placeholder="Select date"
                                    showIcon
                                />
                            </div>

                            <div class="filter-item">
                                <label class="filter-label">Appointment Date To</label>
                                <Calendar
                                    v-model="filters.appointment_date_to"
                                    dateFormat="yy-mm-dd"
                                    placeholder="Select date"
                                    showIcon
                                />
                            </div>

                            <!-- Token Issued Date Range -->
                            <div class="filter-item">
                                <label class="filter-label">Token Issued From</label>
                                <Calendar
                                    v-model="filters.token_issued_date_from"
                                    dateFormat="yy-mm-dd"
                                    placeholder="Select date"
                                    showIcon
                                />
                            </div>

                            <div class="filter-item">
                                <label class="filter-label">Token Issued To</label>
                                <Calendar
                                    v-model="filters.token_issued_date_to"
                                    dateFormat="yy-mm-dd"
                                    placeholder="Select date"
                                    showIcon
                                />
                            </div>

                            <!-- Document Verified Date Range -->
                            <div class="filter-item">
                                <label class="filter-label">Document Verified From</label>
                                <Calendar
                                    v-model="filters.document_verified_date_from"
                                    dateFormat="yy-mm-dd"
                                    placeholder="Select date"
                                    showIcon
                                />
                            </div>

                            <div class="filter-item">
                                <label class="filter-label">Document Verified To</label>
                                <Calendar
                                    v-model="filters.document_verified_date_to"
                                    dateFormat="yy-mm-dd"
                                    placeholder="Select date"
                                    showIcon
                                />
                            </div>

                            <!-- Cashier Invoice Date Range -->
                            <div class="filter-item">
                                <label class="filter-label">Cashier Invoice From</label>
                                <Calendar
                                    v-model="filters.cashier_invoice_date_from"
                                    dateFormat="yy-mm-dd"
                                    placeholder="Select date"
                                    showIcon
                                />
                            </div>

                            <div class="filter-item">
                                <label class="filter-label">Cashier Invoice To</label>
                                <Calendar
                                    v-model="filters.cashier_invoice_date_to"
                                    dateFormat="yy-mm-dd"
                                    placeholder="Select date"
                                    showIcon
                                />
                            </div>

                            <!-- Gate Pass Date Range -->
                            <div class="filter-item">
                                <label class="filter-label">Gate Pass Date From</label>
                                <Calendar
                                    v-model="filters.gate_pass_date_from"
                                    dateFormat="yy-mm-dd"
                                    placeholder="Select date"
                                    showIcon
                                />
                            </div>

                            <div class="filter-item">
                                <label class="filter-label">Gate Pass Date To</label>
                                <Calendar
                                    v-model="filters.gate_pass_date_to"
                                    dateFormat="yy-mm-dd"
                                    placeholder="Select date"
                                    showIcon
                                />
                            </div>

                            <!-- Shipment/Container -->
                            <div class="filter-item">
                                <label class="filter-label">Shipment/Container</label>
                                <Select
                                    v-model="filters.container_id"
                                    :options="containers"
                                    filter
                                    optionLabel="reference"
                                    optionValue="id"
                                    placeholder="Select container"
                                    showClear
                                />
                            </div>

                            <!-- Cargo Type -->
                            <div class="filter-item">
                                <label class="filter-label">Cargo Type</label>
                                <Select
                                    v-model="filters.cargo_type"
                                    :options="cargoTypes"
                                    placeholder="Select cargo type"
                                />
                            </div>

                            <!-- HBL Type -->
                            <div class="filter-item">
                                <label class="filter-label">HBL Type</label>
                                <Select
                                    v-model="filters.hbl_type"
                                    :options="hblTypes"
                                    placeholder="Select HBL type"
                                />
                            </div>

                            <!-- General Search -->
                            <div class="filter-item">
                                <label class="filter-label">Search</label>
                                <InputText
                                    v-model="filters.search"
                                    placeholder="HBL number, reference, name..."
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
                        class="hbl-table"
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
                                <p class="text-gray-500 mt-4">No HBL records found</p>
                            </div>
                        </template>

                        <Column field="reference" frozen header="HBL Reference" sortable style="min-width: 150px">
                            <template #body="{ data }">
                                <span class="font-mono font-semibold text-primary">{{ data.reference }}</span>
                            </template>
                        </Column>

                        <Column field="hbl_number" header="HBL Number" sortable style="min-width: 150px">
                            <template #body="{ data }">
                                <span class="font-mono font-semibold">{{ data.hbl_number || 'N/A' }}</span>
                            </template>
                        </Column>

                        <Column field="hbl_name" header="Customer Name" sortable style="min-width: 180px">
                            <template #body="{ data }">
                                <span class="font-medium">{{ data.hbl_name }}</span>
                            </template>
                        </Column>

                        <Column field="contact_number" header="Contact" style="min-width: 130px">
                            <template #body="{ data }">
                                <span class="font-mono text-sm">{{ data.contact_number || 'N/A' }}</span>
                            </template>
                        </Column>

                        <Column field="branch.name" header="Branch" style="min-width: 150px">
                            <template #body="{ data }">
                                <span class="text-sm">{{ data.branch?.name || 'N/A' }}</span>
                            </template>
                        </Column>

                        <Column field="container_reference" header="Container" style="min-width: 150px">
                            <template #body="{ data }">
                                <span class="font-mono text-sm">{{ data.container_reference || 'N/A' }}</span>
                            </template>
                        </Column>

                        <Column field="cargo_type" header="Cargo Type" sortable style="min-width: 130px">
                            <template #body="{ data }">
                                <Tag
                                    :severity="getCargoTypeSeverity(data.cargo_type)"
                                    :value="data.cargo_type"
                                    class="text-xs"
                                />
                            </template>
                        </Column>

                        <Column field="hbl_type" header="HBL Type" sortable style="min-width: 140px">
                            <template #body="{ data }">
                                <Tag
                                    :severity="getHBLTypeSeverity(data.hbl_type)"
                                    :value="data.hbl_type"
                                    class="text-xs"
                                />
                            </template>
                        </Column>

                        <Column field="total_packages" header="Packages" style="min-width: 100px">
                            <template #body="{ data }">
                                <span class="font-semibold">{{ data.total_packages }}</span>
                            </template>
                        </Column>

                        <Column field="loaded_date" header="Loaded Date" style="min-width: 180px">
                            <template #body="{ data }">
                                <span class="text-sm">{{ formatDate(data.loaded_date) }}</span>
                            </template>
                        </Column>

                        <Column field="unloaded_date" header="Unloaded Date" style="min-width: 180px">
                            <template #body="{ data }">
                                <span class="text-sm">{{ formatDate(data.unloaded_date) }}</span>
                            </template>
                        </Column>

                        <Column field="appointment_date" header="Appointment" style="min-width: 150px">
                            <template #body="{ data }">
                                <span class="text-sm">{{ data.appointment_date || 'N/A' }}</span>
                            </template>
                        </Column>

                        <Column field="token_number" header="Token #" style="min-width: 120px">
                            <template #body="{ data }">
                                <span class="font-mono text-sm">{{ data.token_number || 'N/A' }}</span>
                            </template>
                        </Column>

                        <Column field="token_issued_date" header="Token Issued" style="min-width: 180px">
                            <template #body="{ data }">
                                <span class="text-sm">{{ formatDate(data.token_issued_date) }}</span>
                            </template>
                        </Column>

                        <Column field="document_verified_date" header="Doc Verified" style="min-width: 180px">
                            <template #body="{ data }">
                                <span class="text-sm">{{ formatDate(data.document_verified_date) }}</span>
                            </template>
                        </Column>

                        <Column field="cashier_invoice_date" header="Cashier Invoice" style="min-width: 180px">
                            <template #body="{ data }">
                                <span class="text-sm">{{ formatDate(data.cashier_invoice_date) }}</span>
                            </template>
                        </Column>

                        <Column field="gate_pass_date" header="Gate Pass" style="min-width: 180px">
                            <template #body="{ data }">
                                <span class="text-sm">{{ formatDate(data.gate_pass_date) }}</span>
                            </template>
                        </Column>

                        <Column field="grand_total" header="Grand Total" sortable style="min-width: 130px">
                            <template #body="{ data }">
                                <span class="font-semibold text-green-600">{{ formatCurrency(data.grand_total) }}</span>
                            </template>
                        </Column>

                        <Column field="paid_amount" header="Paid" style="min-width: 130px">
                            <template #body="{ data }">
                                <span class="font-semibold text-blue-600">{{ formatCurrency(data.paid_amount) }}</span>
                            </template>
                        </Column>

                        <Column field="balance" header="Balance" style="min-width: 130px">
                            <template #body="{ data }">
                                <span class="font-semibold text-red-600">{{ formatCurrency(data.balance) }}</span>
                            </template>
                        </Column>

                        <Column field="created_at" header="Created Date" sortable style="min-width: 180px">
                            <template #body="{ data }">
                                <span class="text-sm">{{ formatDate(data.created_at) }}</span>
                            </template>
                        </Column>

                        <Column field="created_by.name" header="Created By" style="min-width: 150px">
                            <template #body="{ data }">
                                <div v-if="data.created_by" class="flex items-center gap-2">
                                    <i class="pi pi-user text-gray-400"></i>
                                    <span class="text-sm">{{ data.created_by.name }}</span>
                                </div>
                                <span v-else class="text-gray-400">N/A</span>
                            </template>
                        </Column>
                    </DataTable>
                </template>
            </Card>
        </div>
    </AppLayout>
</template>

<style scoped>
.hbl-report-page {
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

.filters-section h3 {
    margin-bottom: 1rem;
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

.hbl-table {
    font-size: 0.875rem;
}

.hbl-table :deep(.p-datatable-header) {
    background: #f9fafb;
    border-bottom: 2px solid #e5e7eb;
}

.hbl-table :deep(.p-datatable-thead > tr > th) {
    background: #f9fafb;
    color: #374151;
    font-weight: 600;
    padding: 1rem;
}

.hbl-table :deep(.p-datatable-tbody > tr > td) {
    padding: 0.875rem 1rem;
}

.hbl-table :deep(.p-datatable-tbody > tr:hover) {
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
    .hbl-report-page {
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
