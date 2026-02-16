<script setup>
import { ref, reactive, onMounted, computed, watch } from 'vue';
import axios from 'axios';
import { usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Calendar from 'primevue/calendar';
import Select from 'primevue/select';
import Tag from 'primevue/tag';
import Card from 'primevue/card';
import ContextMenu from 'primevue/contextmenu';
import Panel from 'primevue/panel';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import FloatLabel from 'primevue/floatlabel';
import { push } from 'notivue';
import HBLPackageDetailModal from "@/Pages/Reports/Components/HBLPackageDetailModal.vue";
import moment from "moment";
import { debounce } from "lodash";

const props = defineProps({
    cargoTypes: {
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
    total_packages: 0,
    total_weight: '0.00',
    total_cbm: '0.00',
    total_hbls: 0,
});

// Modal state
const showPackageDetailModal = ref(false);
const selectedHBLForPackages = ref(null);

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
    search: '',
});

const cm = ref();
const selectedHBL = ref(null);
const dt = ref();

const fetchData = async () => {
    loading.value = true;
    try {
        const params = {
            ...lazyParams,
            ...filters,
            group_by_hbl: true, // Group packages by HBL
        };

        // Format date filters
        const dateFields = [
            'loaded_date_from', 'loaded_date_to',
            'unloaded_date_from', 'unloaded_date_to',
            'appointment_date_from', 'appointment_date_to',
            'token_issued_date_from', 'token_issued_date_to',
            'gate_pass_date_from', 'gate_pass_date_to',
            'cashier_invoice_date_from', 'cashier_invoice_date_to',
            'document_verified_date_from', 'document_verified_date_to'
        ];

        dateFields.forEach(field => {
            if (filters[field]) {
                params[field] = moment(filters[field]).format('YYYY-MM-DD');
            }
        });

        const response = await axios.get(route('report.hbl-package-report.data'), { params });

        if (response.data.success) {
            records.value = response.data.data;
            totalRecords.value = response.data.total;
            stats.value = response.data.stats || stats.value;
        } else {
            push.error(response.data.message || 'Failed to load HBL package report data');
        }
    } catch (error) {
        console.error('Error fetching HBL package report:', error);
        if (error.response?.data?.message) {
            push.error(error.response.data.message);
        } else {
            push.error('Failed to load HBL package report data. Please try again.');
        }
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
    window.location.href = route('report.hbl-package-report.export') + '?' + queryString;
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

const formatNumber = (value) => {
    if (!value) return '0.00';
    return parseFloat(value.toString().replace(/,/g, '')).toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
};

const getCargoTypeSeverity = (type) => {
    const severities = {
        'Sea Cargo': 'success',
        'Air Cargo': 'info',
    };
    return severities[type] || 'secondary';
};

const resolveCargoType = (cargoType) => {
    const icons = {
        'Sea Cargo': 'ti ti-sailboat',
        'Air Cargo': 'ti ti-plane-tilt',
    };
    return {
        icon: icons[cargoType] || 'ti ti-package',
        color: getCargoTypeSeverity(cargoType),
    };
};

const menuModel = ref([
    {
        label: "View Packages",
        icon: "pi pi-fw pi-box",
        command: () => viewPackages(selectedHBL),
    },
]);

const onRowContextMenu = (event) => {
    cm.value.show(event.originalEvent);
};

const viewPackages = (hbl) => {
    selectedHBLForPackages.value = hbl.value.hbl_id;
    showPackageDetailModal.value = true;
};

const onRowClick = (event) => {
    selectedHBLForPackages.value = event.data.hbl_id;
    showPackageDetailModal.value = true;
};

const closePackageModal = () => {
    showPackageDetailModal.value = false;
    selectedHBLForPackages.value = null;
};

const exportCSVFilename = computed(() => {
    const timestamp = moment().format('YYYY_MM_DD_HH_mm_ss');
    return `hbl-package-report-${timestamp}`;
});

onMounted(() => {
    fetchData();
});
</script>

<template>
    <AppLayout title="HBL Package Report">
        <template #header>HBL Package Report</template>

        <div class="hbl-package-report-page">
            <!-- Page Header -->
            <div class="page-header">
                <div class="header-content">
                    <div class="header-left">
                        <i class="ti ti-package text-4xl text-primary"></i>
                        <div>
                            <h1 class="text-3xl font-bold text-gray-800">HBL Package Report</h1>
                            <p class="text-gray-600 mt-1">Package-level details linked to each HBL with operational milestones</p>
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
                            <i class="ti ti-weight text-3xl text-green-500"></i>
                            <div>
                                <p class="text-sm text-gray-500 uppercase">Total Weight (KG)</p>
                                <p class="text-3xl font-bold text-green-600">{{ formatNumber(stats.total_weight) }}</p>
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
                                <p class="text-3xl font-bold text-teal-600">{{ formatNumber(stats.total_cbm) }}</p>
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
                                <label for="branch">Agent</label>
                            </FloatLabel>

                            <!-- Customer Search -->
                            <FloatLabel class="w-full" variant="in">
                                <Select
                                    v-model="filters.customer_search"
                                    :options="customers"
                                    class="w-full"
                                    filter
                                    input-id="customer"
                                    optionLabel="label"
                                    optionValue="value"
                                    showClear
                                />
                                <label for="customer">Customer</label>
                            </FloatLabel>

                            <!-- Appointment Date Range -->
                            <FloatLabel class="w-full" variant="in">
                                <Calendar
                                    v-model="filters.appointment_date_from"
                                    class="w-full"
                                    dateFormat="yy-mm-dd"
                                    input-id="appointment-date-from"
                                    showIcon
                                />
                                <label for="appointment-date-from">Appointment Date From</label>
                            </FloatLabel>

                            <FloatLabel class="w-full" variant="in">
                                <Calendar
                                    v-model="filters.appointment_date_to"
                                    class="w-full"
                                    dateFormat="yy-mm-dd"
                                    input-id="appointment-date-to"
                                    showIcon
                                />
                                <label for="appointment-date-to">Appointment Date To</label>
                            </FloatLabel>

                            <!-- Token Issued Date Range -->
                            <FloatLabel class="w-full" variant="in">
                                <Calendar
                                    v-model="filters.token_issued_date_from"
                                    class="w-full"
                                    dateFormat="yy-mm-dd"
                                    input-id="token-issued-from"
                                    showIcon
                                />
                                <label for="token-issued-from">Token Issued From</label>
                            </FloatLabel>

                            <FloatLabel class="w-full" variant="in">
                                <Calendar
                                    v-model="filters.token_issued_date_to"
                                    class="w-full"
                                    dateFormat="yy-mm-dd"
                                    input-id="token-issued-to"
                                    showIcon
                                />
                                <label for="token-issued-to">Token Issued To</label>
                            </FloatLabel>

                            <!-- Document Verified Date Range -->
                            <FloatLabel class="w-full" variant="in">
                                <Calendar
                                    v-model="filters.document_verified_date_from"
                                    class="w-full"
                                    dateFormat="yy-mm-dd"
                                    input-id="doc-verified-from"
                                    showIcon
                                />
                                <label for="doc-verified-from">Document Verified From</label>
                            </FloatLabel>

                            <FloatLabel class="w-full" variant="in">
                                <Calendar
                                    v-model="filters.document_verified_date_to"
                                    class="w-full"
                                    dateFormat="yy-mm-dd"
                                    input-id="doc-verified-to"
                                    showIcon
                                />
                                <label for="doc-verified-to">Document Verified To</label>
                            </FloatLabel>

                            <!-- Cashier Invoice Date Range -->
                            <FloatLabel class="w-full" variant="in">
                                <Calendar
                                    v-model="filters.cashier_invoice_date_from"
                                    class="w-full"
                                    dateFormat="yy-mm-dd"
                                    input-id="cashier-invoice-from"
                                    showIcon
                                />
                                <label for="cashier-invoice-from">Cashier Invoice From</label>
                            </FloatLabel>

                            <FloatLabel class="w-full" variant="in">
                                <Calendar
                                    v-model="filters.cashier_invoice_date_to"
                                    class="w-full"
                                    dateFormat="yy-mm-dd"
                                    input-id="cashier-invoice-to"
                                    showIcon
                                />
                                <label for="cashier-invoice-to">Cashier Invoice To</label>
                            </FloatLabel>

                            <!-- Gate Pass Date Range -->
                            <FloatLabel class="w-full" variant="in">
                                <Calendar
                                    v-model="filters.gate_pass_date_from"
                                    class="w-full"
                                    dateFormat="yy-mm-dd"
                                    input-id="gate-pass-from"
                                    showIcon
                                />
                                <label for="gate-pass-from">Gate Pass Date From</label>
                            </FloatLabel>

                            <FloatLabel class="w-full" variant="in">
                                <Calendar
                                    v-model="filters.gate_pass_date_to"
                                    class="w-full"
                                    dateFormat="yy-mm-dd"
                                    input-id="gate-pass-to"
                                    showIcon
                                />
                                <label for="gate-pass-to">Gate Pass Date To</label>
                            </FloatLabel>

                            <!-- Shipment/Container -->
                            <FloatLabel class="w-full" variant="in">
                                <Select
                                    v-model="filters.container_id"
                                    :options="containers"
                                    class="w-full"
                                    filter
                                    input-id="container"
                                    optionLabel="reference"
                                    optionValue="id"
                                    showClear
                                />
                                <label for="container">Shipment/Container</label>
                            </FloatLabel>

                            <!-- Cargo Type -->
                            <FloatLabel class="w-full" variant="in">
                                <Select
                                    v-model="filters.cargo_type"
                                    :options="cargoTypes"
                                    class="w-full"
                                    input-id="cargo-type"
                                />
                                <label for="cargo-type">Cargo Type</label>
                            </FloatLabel>

                            <!-- General Search -->
                            <FloatLabel class="w-full" variant="in">
                                <InputText
                                    v-model="filters.search"
                                    class="w-full"
                                    input-id="search"
                                    @keyup.enter="applyFilters"
                                />
                                <label for="search">Search</label>
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
                    <ContextMenu ref="cm" :model="menuModel" @hide="selectedHBL = null" />
                    <DataTable
                        ref="dt"
                        v-model:contextMenuSelection="selectedHBL"
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
                        context-menu
                        currentPageReportTemplate="Showing {first} to {last} of {totalRecords} HBLs"
                        dataKey="hbl_id"
                        paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                        removable-sort
                        row-hover
                        sortMode="single"
                        stripedRows
                        tableStyle="min-width: 50rem"
                        @page="onPage"
                        @rowContextmenu="onRowContextMenu"
                        @sort="onSort"
                        @row-click="onRowClick"
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
                                        placeholder="Keyword Search"
                                        size="small"
                                    />
                                </IconField>
                                <span class="text-sm text-gray-600">Click on any row to view package details</span>
                            </div>
                        </template>

                        <template #empty>
                            <div class="empty-state">
                                <i class="ti ti-database-off text-6xl text-gray-300"></i>
                                <p class="text-gray-500 mt-4">No HBL records found</p>
                            </div>
                        </template>

                        <template #loading> Loading HBL package report data. Please wait.</template>

                        <Column field="hbl_number" header="HBL" sortable style="min-width: 150px">
                            <template #body="slotProps">
                                <span class="font-medium text-primary cursor-pointer hover:underline">{{ slotProps.data.hbl_number }}</span>
                            </template>
                        </Column>

                        <Column field="cargo_type" header="Cargo Type" sortable>
                            <template #body="slotProps">
                                <Tag :icon="resolveCargoType(slotProps.data.cargo_type).icon"
                                     :severity="resolveCargoType(slotProps.data.cargo_type).color"
                                     :value="slotProps.data.cargo_type"
                                     class="text-sm"></Tag>
                            </template>
                        </Column>

                        <Column field="customer_name" header="Customer">
                            <template #body="slotProps">
                                <div>{{ slotProps.data.customer_name }}</div>
                                <div class="text-gray-500 text-sm">{{ slotProps.data.customer_contact }}</div>
                            </template>
                        </Column>

                        <Column field="branch_name" header="Branch" sortable style="min-width: 150px">
                            <template #body="{ data }">
                                <span class="text-sm">{{ data.branch_name }}</span>
                            </template>
                        </Column>

                        <Column field="total_packages" header="Packages" sortable style="min-width: 100px">
                            <template #body="{ data }">
                                <span class="font-semibold text-blue-600">{{ data.total_packages }}</span>
                            </template>
                        </Column>

                        <Column field="total_weight" header="Weight (KG)" sortable style="min-width: 120px">
                            <template #body="{ data }">
                                <span class="font-semibold">{{ formatNumber(data.total_weight) }}</span>
                            </template>
                        </Column>

                        <Column field="total_cbm" header="CBM" sortable style="min-width: 100px">
                            <template #body="{ data }">
                                <span class="font-semibold">{{ formatNumber(data.total_cbm) }}</span>
                            </template>
                        </Column>

                        <Column field="container_reference" header="Container" style="min-width: 150px">
                            <template #body="{ data }">
                                <span class="text-sm">{{ data.container_reference || 'N/A' }}</span>
                            </template>
                        </Column>

                        <template #footer> In total there are {{ totalRecords }} HBL records. </template>
                    </DataTable>
                </template>
            </Card>
        </div>
    </AppLayout>

    <HBLPackageDetailModal
        :hbl-id="selectedHBLForPackages"
        :show="showPackageDetailModal"
        @close="closePackageModal"
        @update:show="showPackageDetailModal = $event"
    />
</template>

<style scoped>
.hbl-package-report-page {
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
    .hbl-package-report-page {
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
