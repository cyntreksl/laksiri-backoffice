<script setup>
import { ref, reactive, onMounted, computed, watch } from 'vue';
import axios from 'axios';
import AppLayout from '@/Layouts/AppLayout.vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Calendar from 'primevue/calendar';
import Card from 'primevue/card';
import Panel from 'primevue/panel';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import FloatLabel from 'primevue/floatlabel';
import { push } from 'notivue';
import moment from "moment";
import { debounce } from "lodash";

const loading = ref(false);
const records = ref([]);
const totalRecords = ref(0);
const stats = ref({
    total_records: 0,
    grand_total: '0.00',
});

const lazyParams = reactive({
    page: 1,
    per_page: 25,
    sort_field: 'created_at',
    sort_order: 'asc',
});

const filters = reactive({
    date_from: new Date(new Date().setDate(new Date().getDate() - 30)),
    date_to: new Date(),
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

        const response = await axios.get(route('report.detail-invoice-analysis.data'), { params });

        if (response.data.success) {
            records.value = response.data.data;
            totalRecords.value = response.data.total;
            stats.value = response.data.stats || stats.value;
        }
    } catch (error) {
        console.error('Error fetching detail invoice analysis:', error);
        push.error('Failed to load detail invoice analysis data');
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
    filters.date_from = new Date(new Date().setDate(new Date().getDate() - 30));
    filters.date_to = new Date();
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
    window.location.href = route('report.detail-invoice-analysis.export') + '?' + queryString;
    push.success(`Export started. ${format.toUpperCase()} download will begin shortly.`);
};

const formatNumber = (value) => {
    if (!value) return '0.00';
    return parseFloat(value.toString().replace(/,/g, '')).toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
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

<template>
    <AppLayout title="Detail Invoice Analysis">
        <template #header>Detail Invoice Analysis</template>

        <div class="detail-invoice-analysis-page">
            <!-- Page Header -->
            <div class="page-header">
                <div class="header-content">
                    <div class="header-left">
                        <i class="ti ti-file-analytics text-4xl text-primary"></i>
                        <div>
                            <h1 class="text-3xl font-bold text-gray-800">Detail Invoice Analysis</h1>
                            <p class="text-gray-600 mt-1">Invoice analysis for {{ reportDate }}</p>
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
                                <p class="text-sm text-gray-500 uppercase">Total Records</p>
                                <p class="text-3xl font-bold text-gray-800">{{ stats.total_records }}</p>
                            </div>
                        </div>
                    </template>
                </Card>

                <Card class="summary-card">
                    <template #content>
                        <div class="card-content">
                            <i class="ti ti-currency-dollar text-3xl text-green-500"></i>
                            <div>
                                <p class="text-sm text-gray-500 uppercase">Grand Total</p>
                                <p class="text-3xl font-bold text-green-600">{{ formatNumber(stats.grand_total) }}</p>
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
                                    v-model="filters.search"
                                    class="w-full"
                                    input-id="search"
                                    @keyup.enter="applyFilters"
                                />
                                <label for="search">Search (Invoice/HBL)</label>
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
                        dataKey="invoice_number"
                        paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                        row-hover
                        scrollHeight="600px"
                        scrollable
                        stripedRows
                        @page="onPage"
                    >
                        <template #empty>
                            <div class="empty-state">
                                <i class="ti ti-database-off text-6xl text-gray-300"></i>
                                <p class="text-gray-500 mt-4">No invoice records found</p>
                            </div>
                        </template>

                        <template #loading> Loading invoice data. Please wait.</template>

                        <Column field="invoice_number" frozen header="Invoice No." style="min-width: 150px">
                            <template #body="{ data }">
                                <span class="font-mono font-semibold">{{ data.invoice_number }}</span>
                            </template>
                        </Column>

                        <Column field="hbl_number" header="HBL" style="min-width: 150px">
                            <template #body="{ data }">
                                <span class="font-mono">{{ data.hbl_number }}</span>
                            </template>
                        </Column>

                        <Column field="no_of_pkgs" style="min-width: 100px">
                            <template #body="{ data }">
                                <span class="text-right block">{{ data.no_of_pkgs }}</span>
                            </template>
                            <template #header>
                                <div class="text-right w-full font-bold text-nowrap">No.of Pkgs.</div>
                            </template>
                        </Column>

                        <Column field="cbm" style="min-width: 100px">
                            <template #body="{ data }">
                                <span class="font-mono text-right block">{{ data.cbm }}</span>
                            </template>
                            <template #header>
                                <div class="text-right w-full font-bold">CBM</div>
                            </template>
                        </Column>

                        <Column field="slpa" style="min-width: 120px">
                            <template #body="{ data }">
                                <span class="font-mono text-right block">{{ formatNumber(data.slpa) }}</span>
                            </template>
                            <template #header>
                                <div class="text-right w-full font-bold">SLPA</div>
                            </template>
                        </Column>

                        <Column field="handling" style="min-width: 120px">
                            <template #body="{ data }">
                                <span class="font-mono text-right block">{{ formatNumber(data.handling) }}</span>
                            </template>
                            <template #header>
                                <div class="text-right w-full font-bold">Handling</div>
                            </template>
                        </Column>

                        <Column field="bond" style="min-width: 120px">
                            <template #body="{ data }">
                                <span class="font-mono text-right block">{{ formatNumber(data.bond) }}</span>
                            </template>
                            <template #header>
                                <div class="text-right w-full font-bold">Bond</div>
                            </template>
                        </Column>

                        <Column field="demu" style="min-width: 120px">
                            <template #body="{ data }">
                                <span class="font-mono text-right block">{{ formatNumber(data.demu) }}</span>
                            </template>
                            <template #header>
                                <div class="text-right w-full font-bold">Demu.</div>
                            </template>
                        </Column>

                        <Column field="vat" style="min-width: 120px">
                            <template #body="{ data }">
                                <span class="font-mono text-right block">{{ formatNumber(data.vat) }}</span>
                            </template>
                            <template #header>
                                <div class="text-right w-full font-bold">VAT</div>
                            </template>
                        </Column>

                        <Column field="discount" style="min-width: 120px">
                            <template #body="{ data }">
                                <span class="font-mono text-right block">{{ formatNumber(data.discount) }}</span>
                            </template>
                            <template #header>
                                <div class="text-right w-full font-bold">Discount</div>
                            </template>
                        </Column>

                        <Column field="total" style="min-width: 150px">
                            <template #body="{ data }">
                                <span class="font-mono font-semibold text-green-600 text-right block">{{ formatNumber(data.total) }}</span>
                            </template>
                            <template #header>
                                <div class="text-right w-full font-bold">Total</div>
                            </template>
                        </Column>

                        <template #footer>
                            <div class="flex justify-between items-center">
                                <span>In total there are {{ totalRecords }} invoice records.</span>
                                <span class="font-semibold text-green-600">Grand Total: {{ formatNumber(stats.grand_total) }}</span>
                            </div>
                        </template>
                    </DataTable>
                </template>
            </Card>
        </div>
    </AppLayout>
</template>

<style scoped>
.detail-invoice-analysis-page {
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
    .detail-invoice-analysis-page {
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
