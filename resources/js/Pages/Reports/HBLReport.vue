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
import { useConfirm } from "primevue/useconfirm";
import { push } from 'notivue';
import HBLDetailModal from "@/Pages/Common/Dialog/HBL/Index.vue";
import moment from "moment";
import { debounce } from "lodash";

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

const cm = ref();
const selectedHBL = ref(null);
const selectedHBLID = ref(null);
const showConfirmViewHBLModal = ref(false);
const confirm = useConfirm();
const dt = ref();

const fetchData = async () => {
    loading.value = true;
    try {
        const params = {
            ...lazyParams,
            ...filters,
        };

        // Format date filters
        if (filters.loaded_date_from) {
            params.loaded_date_from = moment(filters.loaded_date_from).format('YYYY-MM-DD');
        }
        if (filters.loaded_date_to) {
            params.loaded_date_to = moment(filters.loaded_date_to).format('YYYY-MM-DD');
        }
        if (filters.unloaded_date_from) {
            params.unloaded_date_from = moment(filters.unloaded_date_from).format('YYYY-MM-DD');
        }
        if (filters.unloaded_date_to) {
            params.unloaded_date_to = moment(filters.unloaded_date_to).format('YYYY-MM-DD');
        }
        if (filters.appointment_date_from) {
            params.appointment_date_from = moment(filters.appointment_date_from).format('YYYY-MM-DD');
        }
        if (filters.appointment_date_to) {
            params.appointment_date_to = moment(filters.appointment_date_to).format('YYYY-MM-DD');
        }
        if (filters.token_issued_date_from) {
            params.token_issued_date_from = moment(filters.token_issued_date_from).format('YYYY-MM-DD');
        }
        if (filters.token_issued_date_to) {
            params.token_issued_date_to = moment(filters.token_issued_date_to).format('YYYY-MM-DD');
        }
        if (filters.gate_pass_date_from) {
            params.gate_pass_date_from = moment(filters.gate_pass_date_from).format('YYYY-MM-DD');
        }
        if (filters.gate_pass_date_to) {
            params.gate_pass_date_to = moment(filters.gate_pass_date_to).format('YYYY-MM-DD');
        }
        if (filters.cashier_invoice_date_from) {
            params.cashier_invoice_date_from = moment(filters.cashier_invoice_date_from).format('YYYY-MM-DD');
        }
        if (filters.cashier_invoice_date_to) {
            params.cashier_invoice_date_to = moment(filters.cashier_invoice_date_to).format('YYYY-MM-DD');
        }
        if (filters.document_verified_date_from) {
            params.document_verified_date_from = moment(filters.document_verified_date_from).format('YYYY-MM-DD');
        }
        if (filters.document_verified_date_to) {
            params.document_verified_date_to = moment(filters.document_verified_date_to).format('YYYY-MM-DD');
        }

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

// Debounced search function
const debouncedFetchData = debounce(() => {
    lazyParams.page = 1;
    fetchData();
}, 1000);

// Watch for search changes
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

const getCargoTypeSeverity = (type) => {
    const severities = {
        'General': 'info',
        'Dangerous': 'danger',
        'Perishable': 'warn',
        'Sea Cargo': 'success',
        'Air Cargo': 'info',
    };
    return severities[type] || 'secondary';
};

const getHBLTypeSeverity = (type) => {
    const severities = {
        'Normal': 'primary',
        'UPB': 'secondary',
        'Door to Door': 'info',
        'Gift': 'warn',
        'Third Party': 'secondary',
    };
    return severities[type] || 'contrast';
};

const resolveHBLType = (hbl) => {
    return getHBLTypeSeverity(hbl.hbl_type);
};

const resolveCargoType = (hbl) => {
    const icons = {
        'Sea Cargo': 'ti ti-sailboat',
        'Air Cargo': 'ti ti-plane-tilt',
    };
    return {
        icon: icons[hbl.cargo_type] || 'ti ti-package',
        color: getCargoTypeSeverity(hbl.cargo_type),
    };
};

const resolveWarehouse = (warehouse) => {
    if (!warehouse || typeof warehouse !== 'string') return 'secondary';

    switch (warehouse.toUpperCase()) {
        case 'COLOMBO':
            return 'info';
        case 'NINTAVUR':
            return 'danger';
        default:
            return 'secondary';
    }
};

const menuModel = ref([
    {
        label: "View",
        icon: "pi pi-fw pi-search",
        command: () => confirmViewHBL(selectedHBL),
        disabled: !usePage().props.user.permissions.includes("hbls.show"),
    },
]);

const onRowContextMenu = (event) => {
    cm.value.show(event.originalEvent);
};

const confirmViewHBL = (hbl) => {
    selectedHBLID.value = hbl.value.id;
    showConfirmViewHBLModal.value = true;
};

const closeModal = () => {
    showConfirmViewHBLModal.value = false;
    selectedHBLID.value = null;
};

const exportCSVFilename = computed(() => {
    const timestamp = moment().format('YYYY_MM_DD_HH_mm_ss');
    return `hbl-report-${timestamp}`;
});

const exportCSV = () => {
    dt.value.exportCSV();
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
            <Panel :collapsed="true" class="mt-5" header="Advance Filters" toggleable>
                <template #default>
                    <div class="filters-section">
                        <div class="filters-grid">
                            <!-- Loaded Date Range -->
                            <FloatLabel class="w-full" variant="in">
                                <Calendar
                                    v-model="filters.loaded_date_from"
                                    dateFormat="yy-mm-dd"
                                    class="w-full"
                                    showIcon
                                    input-id="loaded-date-from"
                                />
                                <label for="loaded-date-from">Loaded Date From</label>
                            </FloatLabel>

                            <FloatLabel class="w-full" variant="in">
                                <Calendar
                                    v-model="filters.loaded_date_to"
                                    dateFormat="yy-mm-dd"
                                    class="w-full"
                                    showIcon
                                    input-id="loaded-date-to"
                                />
                                <label for="loaded-date-to">Loaded Date To</label>
                            </FloatLabel>

                            <!-- Unloaded Date Range -->
                            <FloatLabel class="w-full" variant="in">
                                <Calendar
                                    v-model="filters.unloaded_date_from"
                                    dateFormat="yy-mm-dd"
                                    class="w-full"
                                    showIcon
                                    input-id="unloaded-date-from"
                                />
                                <label for="unloaded-date-from">Unloaded Date From</label>
                            </FloatLabel>

                            <FloatLabel class="w-full" variant="in">
                                <Calendar
                                    v-model="filters.unloaded_date_to"
                                    dateFormat="yy-mm-dd"
                                    class="w-full"
                                    showIcon
                                    input-id="unloaded-date-to"
                                />
                                <label for="unloaded-date-to">Unloaded Date To</label>
                            </FloatLabel>

                            <!-- Branch -->
                            <FloatLabel class="w-full" variant="in">
                                <Select
                                    v-model="filters.branch_id"
                                    :options="branches"
                                    optionLabel="label"
                                    optionValue="value"
                                    filter
                                    showClear
                                    class="w-full"
                                    input-id="branch"
                                />
                                <label for="branch">Branch/Agent</label>
                            </FloatLabel>

                            <!-- Customer Search -->
                            <FloatLabel class="w-full" variant="in">
                                <Select
                                    v-model="filters.customer_search"
                                    :options="customers"
                                    filter
                                    optionLabel="label"
                                    optionValue="value"
                                    showClear
                                    class="w-full"
                                    input-id="customer"
                                />
                                <label for="customer">Customer</label>
                            </FloatLabel>

                            <!-- Appointment Date Range -->
                            <FloatLabel class="w-full" variant="in">
                                <Calendar
                                    v-model="filters.appointment_date_from"
                                    dateFormat="yy-mm-dd"
                                    class="w-full"
                                    showIcon
                                    input-id="appointment-date-from"
                                />
                                <label for="appointment-date-from">Appointment Date From</label>
                            </FloatLabel>

                            <FloatLabel class="w-full" variant="in">
                                <Calendar
                                    v-model="filters.appointment_date_to"
                                    dateFormat="yy-mm-dd"
                                    class="w-full"
                                    showIcon
                                    input-id="appointment-date-to"
                                />
                                <label for="appointment-date-to">Appointment Date To</label>
                            </FloatLabel>

                            <!-- Token Issued Date Range -->
                            <FloatLabel class="w-full" variant="in">
                                <Calendar
                                    v-model="filters.token_issued_date_from"
                                    dateFormat="yy-mm-dd"
                                    class="w-full"
                                    showIcon
                                    input-id="token-issued-from"
                                />
                                <label for="token-issued-from">Token Issued From</label>
                            </FloatLabel>

                            <FloatLabel class="w-full" variant="in">
                                <Calendar
                                    v-model="filters.token_issued_date_to"
                                    dateFormat="yy-mm-dd"
                                    class="w-full"
                                    showIcon
                                    input-id="token-issued-to"
                                />
                                <label for="token-issued-to">Token Issued To</label>
                            </FloatLabel>

                            <!-- Document Verified Date Range -->
                            <FloatLabel class="w-full" variant="in">
                                <Calendar
                                    v-model="filters.document_verified_date_from"
                                    dateFormat="yy-mm-dd"
                                    class="w-full"
                                    showIcon
                                    input-id="doc-verified-from"
                                />
                                <label for="doc-verified-from">Document Verified From</label>
                            </FloatLabel>

                            <FloatLabel class="w-full" variant="in">
                                <Calendar
                                    v-model="filters.document_verified_date_to"
                                    dateFormat="yy-mm-dd"
                                    class="w-full"
                                    showIcon
                                    input-id="doc-verified-to"
                                />
                                <label for="doc-verified-to">Document Verified To</label>
                            </FloatLabel>

                            <!-- Cashier Invoice Date Range -->
                            <FloatLabel class="w-full" variant="in">
                                <Calendar
                                    v-model="filters.cashier_invoice_date_from"
                                    dateFormat="yy-mm-dd"
                                    class="w-full"
                                    showIcon
                                    input-id="cashier-invoice-from"
                                />
                                <label for="cashier-invoice-from">Cashier Invoice From</label>
                            </FloatLabel>

                            <FloatLabel class="w-full" variant="in">
                                <Calendar
                                    v-model="filters.cashier_invoice_date_to"
                                    dateFormat="yy-mm-dd"
                                    class="w-full"
                                    showIcon
                                    input-id="cashier-invoice-to"
                                />
                                <label for="cashier-invoice-to">Cashier Invoice To</label>
                            </FloatLabel>

                            <!-- Gate Pass Date Range -->
                            <FloatLabel class="w-full" variant="in">
                                <Calendar
                                    v-model="filters.gate_pass_date_from"
                                    dateFormat="yy-mm-dd"
                                    class="w-full"
                                    showIcon
                                    input-id="gate-pass-from"
                                />
                                <label for="gate-pass-from">Gate Pass Date From</label>
                            </FloatLabel>

                            <FloatLabel class="w-full" variant="in">
                                <Calendar
                                    v-model="filters.gate_pass_date_to"
                                    dateFormat="yy-mm-dd"
                                    class="w-full"
                                    showIcon
                                    input-id="gate-pass-to"
                                />
                                <label for="gate-pass-to">Gate Pass Date To</label>
                            </FloatLabel>

                            <!-- Shipment/Container -->
                            <FloatLabel class="w-full" variant="in">
                                <Select
                                    v-model="filters.container_id"
                                    :options="containers"
                                    filter
                                    optionLabel="reference"
                                    optionValue="id"
                                    showClear
                                    class="w-full"
                                    input-id="container"
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

                            <!-- HBL Type -->
                            <FloatLabel class="w-full" variant="in">
                                <Select
                                    v-model="filters.hbl_type"
                                    :options="hblTypes"
                                    class="w-full"
                                    input-id="hbl-type"
                                />
                                <label for="hbl-type">HBL Type</label>
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
                        :lazy="true"
                        :loading="loading"
                        :paginator="true"
                        :rows="lazyParams.per_page"
                        :rowsPerPageOptions="[10, 25, 50, 100]"
                        :sortField="lazyParams.sort_field"
                        :sortOrder="lazyParams.sort_order === 'asc' ? 1 : -1"
                        :totalRecords="totalRecords"
                        :value="records"
                        :export-filename="exportCSVFilename"
                        context-menu
                        dataKey="id"
                        currentPageReportTemplate="Showing {first} to {last} of {totalRecords} records"
                        paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                        sortMode="single"
                        stripedRows
                        removable-sort
                        row-hover
                        tableStyle="min-width: 50rem"
                        @page="onPage"
                        @sort="onSort"
                        @rowContextmenu="onRowContextMenu"
                    >
                        <template #header>
                            <div class="flex flex-col sm:flex-row justify-between gap-4">
                                <!-- Search Field -->
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
                            </div>
                        </template>

                        <template #empty>
                            <div class="empty-state">
                                <i class="ti ti-database-off text-6xl text-gray-300"></i>
                                <p class="text-gray-500 mt-4">No HBL records found</p>
                            </div>
                        </template>

                        <template #loading> Loading HBL report data. Please wait.</template>

                        <Column field="hbl_number" header="HBL" sortable>
                            <template #body="slotProps">
                                <div class="flex items-center space-x-2">
                                    <i v-if="slotProps.data.latest_detain_record?.is_rtf"
                                       v-tooltip.left="`Detained by ${slotProps.data.latest_detain_record?.detain_type || 'RTF'}`"
                                       class="ti ti-lock-square-rounded-filled text-2xl text-red-500"></i>
                                    <i v-else-if="slotProps.data.is_rtf"
                                       v-tooltip.left="`RTF`"
                                       class="ti ti-lock-square-rounded-filled text-2xl text-red-500"></i>
                                    <div>
                                        <span class="font-medium">{{ slotProps.data.hbl_number ?? slotProps.data.hbl }}</span>
                                        <br v-if="slotProps.data.is_short_loaded">
                                        <Tag v-if="slotProps.data.is_short_loaded" :severity="`warn`" :value="`Short Loaded`" icon="pi pi-exclamation-triangle" size="small"></Tag>
                                    </div>
                                </div>
                            </template>
                        </Column>

                        <Column field="cargo_type" header="Cargo Type" sortable>
                            <template #body="slotProps">
                                <Tag :icon="resolveCargoType(slotProps.data).icon" :severity="resolveCargoType(slotProps.data).color" :value="slotProps.data.cargo_type" class="text-sm"></Tag>
                            </template>
                        </Column>

                        <Column field="hbl_name" header="HBL Name">
                            <template #body="slotProps">
                                <a :href="`hbls/get-hbls-by-user/${slotProps.data.hbl_name}`"
                                   class="hover:underline" target="_blank">
                                    <i class="pi pi-external-link mr-1" style="font-size: 0.75rem"></i>
                                    {{ slotProps.data.hbl_name }}
                                </a>
                                <div class="text-gray-500 text-sm">{{slotProps.data.email}}</div>
                                <a :href="`hbls/get-hbls-by-user/${slotProps.data.contact_number}`"
                                   class="text-gray-500 hover:underline text-sm" target="_blank">
                                    <i class="pi pi-external-link mr-1" style="font-size: 0.75rem"></i>
                                    {{ slotProps.data.contact_number }}
                                </a>
                            </template>
                        </Column>

                        <Column field="warehouse" header="Warehouse" sortable>
                            <template #body="slotProps">
                                <Tag :severity="resolveWarehouse(slotProps.data)" :value="slotProps.data.warehouse"></Tag>
                            </template>
                        </Column>

                        <Column field="consignee_name" header="Consignee">
                            <template #body="slotProps">
                                <div>{{ slotProps.data.consignee_name }}</div>
                                <div class="text-gray-500 text-sm">{{slotProps.data.consignee_contact}}</div>
                            </template>
                        </Column>

                        <Column field="hbl_type" header="HBL Type" sortable>
                            <template #body="slotProps">
                                <Tag :severity="resolveHBLType(slotProps.data)" :value="slotProps.data.hbl_type"></Tag>
                            </template>
                        </Column>

                        <Column field="total_packages" header="Packages" style="min-width: 100px">
                            <template #body="{ data }">
                                <span class="font-semibold">{{ data.total_packages }}</span>
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

                        <template #footer> In total there are {{ totalRecords }} HBL records. </template>
                    </DataTable>
                </template>
            </Card>
        </div>
    </AppLayout>

    <HBLDetailModal
        :hbl-id="selectedHBLID"
        :show="showConfirmViewHBLModal"
        @close="closeModal"
        @update:show="showConfirmViewHBLModal = $event"
    />
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

/* Empty State */
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
