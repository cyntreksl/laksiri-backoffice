<script setup>
import { ref, onMounted, computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Breadcrumb from '@/Components/Breadcrumb.vue';
import Card from 'primevue/card';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Calendar from 'primevue/calendar';
import InputText from 'primevue/inputtext';
import IftaLabel from 'primevue/iftalabel';
import { push } from 'notivue';

const loading = ref(false);
const payments = ref([]);
const totalRecords = ref(0);
const summary = ref({
    total_transactions: 0,
    total_collected: 0,
    payment_methods: {
        cash: 0,
        card: 0,
        transfer: 0
    }
});

// Filters - default to last 30 days to show data
const dateFrom = ref(new Date(new Date().setDate(new Date().getDate() - 30)));
const dateTo = ref(new Date());
const hblReference = ref('');
const customerName = ref('');

// Pagination
const first = ref(0);
const rows = ref(15);
const currentPage = ref(1);

const fetchData = async () => {
    loading.value = true;

    try {
        const params = new URLSearchParams({
            page: currentPage.value,
            per_page: rows.value,
            date_from: dateFrom.value ? formatDate(dateFrom.value) : '',
            date_to: dateTo.value ? formatDate(dateTo.value) : '',
            hbl_reference: hblReference.value || '',
            customer_name: customerName.value || '',
        });

        console.log('Fetching data with params:', params.toString());

        const response = await fetch(`/call-center/cashier/reports/data?${params}`, {
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': usePage().props.csrf
            }
        });

        console.log('Response status:', response.status);

        if (!response.ok) {
            const errorText = await response.text();
            console.error('Error response:', errorText);
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        console.log('Received data:', data);

        payments.value = data.data;
        totalRecords.value = data.meta.total;
        summary.value = data.summary;
    } catch (error) {
        console.error('Error fetching data:', error);
        push.error('Failed to load report data: ' + error.message);
    } finally {
        loading.value = false;
    }
};

const formatDate = (date) => {
    if (!date) return '';
    const d = new Date(date);
    const year = d.getFullYear();
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
};

const formatDateTime = (dateString) => {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleString('en-US', {
        year: 'numeric',
        month: 'short',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(amount || 0);
};

const onPage = (event) => {
    first.value = event.first;
    currentPage.value = event.page + 1;
    rows.value = event.rows;
    fetchData();
};

const applyFilters = () => {
    currentPage.value = 1;
    first.value = 0;
    fetchData();
};

const resetFilters = () => {
    dateFrom.value = new Date(new Date().setDate(new Date().getDate() - 30));
    dateTo.value = new Date();
    hblReference.value = '';
    customerName.value = '';
    applyFilters();
};

const exportPDF = () => {
    const params = new URLSearchParams({
        date_from: dateFrom.value ? formatDate(dateFrom.value) : '',
        date_to: dateTo.value ? formatDate(dateTo.value) : '',
        hbl_reference: hblReference.value || '',
        customer_name: customerName.value || '',
    });

    window.location.href = `/call-center/cashier/reports/export-pdf?${params}`;
};

const exportExcel = () => {
    const params = new URLSearchParams({
        date_from: dateFrom.value ? formatDate(dateFrom.value) : '',
        date_to: dateTo.value ? formatDate(dateTo.value) : '',
        hbl_reference: hblReference.value || '',
        customer_name: customerName.value || '',
    });

    window.location.href = `/call-center/cashier/reports/export-excel?${params}`;
};

onMounted(() => {
    fetchData();
});
</script>

<template>
    <AppLayout title="Daily Collection Report">
        <template #header>Daily Collection Report</template>

        <Breadcrumb />

        <div class="mt-5 space-y-5">
            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <Card class="bg-gradient-to-br from-blue-50 to-blue-100">
                    <template #content>
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600 mb-1">Total Transactions</p>
                                <p class="text-3xl font-bold text-blue-700">{{ summary.total_transactions }}</p>
                            </div>
                            <i class="pi pi-list text-4xl text-blue-400"></i>
                        </div>
                    </template>
                </Card>

                <Card class="bg-gradient-to-br from-green-50 to-green-100">
                    <template #content>
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600 mb-1">Total Collected</p>
                                <p class="text-3xl font-bold text-green-700">LKR {{ formatCurrency(summary.total_collected) }}</p>
                            </div>
                            <i class="pi pi-wallet text-4xl text-green-400"></i>
                        </div>
                    </template>
                </Card>

                <Card class="bg-gradient-to-br from-purple-50 to-purple-100">
                    <template #content>
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600 mb-1">Average Transaction</p>
                                <p class="text-3xl font-bold text-purple-700">
                                    LKR {{ formatCurrency(summary.total_transactions > 0 ? summary.total_collected / summary.total_transactions : 0) }}
                                </p>
                            </div>
                            <i class="pi pi-chart-line text-4xl text-purple-400"></i>
                        </div>
                    </template>
                </Card>
            </div>

            <!-- Filters -->
            <Card>
                <template #title>
                    <div class="flex items-center gap-2">
                        <i class="pi pi-filter"></i>
                        <span>Filters</span>
                    </div>
                </template>
                <template #content>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <IftaLabel>
                            <Calendar v-model="dateFrom" class="w-full" dateFormat="yy-mm-dd" inputId="date-from" showIcon />
                            <label for="date-from">Date From</label>
                        </IftaLabel>

                        <IftaLabel>
                            <Calendar v-model="dateTo" class="w-full" dateFormat="yy-mm-dd" inputId="date-to" showIcon />
                            <label for="date-to">Date To</label>
                        </IftaLabel>

                        <IftaLabel>
                            <InputText v-model="hblReference" class="w-full" inputId="hbl-ref" />
                            <label for="hbl-ref">HBL Reference</label>
                        </IftaLabel>

                        <IftaLabel>
                            <InputText v-model="customerName" class="w-full" inputId="customer" />
                            <label for="customer">Customer Name</label>
                        </IftaLabel>

                        <div class="flex items-end gap-2 md:col-span-2">
                            <Button class="flex-1" icon="pi pi-check" label="Apply" @click="applyFilters" />
                            <Button class="flex-1" icon="pi pi-refresh" label="Reset" severity="secondary" @click="resetFilters" />
                        </div>
                    </div>
                </template>
            </Card>

            <!-- Data Table -->
            <Card>
                <template #title>
                    <div class="flex justify-between items-center">
                        <span>Collection Records</span>
                        <div class="flex gap-2">
                            <Button
                                :disabled="loading || totalRecords === 0"
                                icon="pi pi-file-pdf"
                                label="Export PDF"
                                severity="danger"
                                @click="exportPDF"
                            />
                            <Button
                                :disabled="loading || totalRecords === 0"
                                icon="pi pi-file-excel"
                                label="Export Excel"
                                severity="success"
                                @click="exportExcel"
                            />
                        </div>
                    </div>
                </template>
                <template #content>
                    <DataTable
                        :first="first"
                        :loading="loading"
                        :rows="rows"
                        :rowsPerPageOptions="[10, 15, 25, 50]"
                        :totalRecords="totalRecords"
                        :value="payments"
                        class="p-datatable-sm"
                        lazy
                        paginator
                        stripedRows
                        @page="onPage"
                    >
                        <template #empty>
                            <div class="flex flex-col items-center justify-center py-12">
                                <i class="pi pi-inbox text-6xl text-gray-300 mb-4"></i>
                                <h3 class="text-xl font-semibold text-gray-700 mb-2">No Collection Records Found</h3>
                                <p class="text-gray-500 text-center max-w-md">
                                    There are no payment collections for the selected date range and filters.
                                    <br />
                                    Try adjusting your filters or date range to see more results.
                                </p>
                            </div>
                        </template>
                        <Column field="created_at" header="Date" style="min-width: 150px">
                            <template #body="{ data }">
                                {{ formatDateTime(data.created_at) }}
                            </template>
                        </Column>
                        <Column field="receipt_number" header="Receipt No" style="min-width: 130px">
                            <template #body="{ data }">
                                <span class="font-mono text-sm">{{ data.receipt_number || 'N/A' }}</span>
                            </template>
                        </Column>
                        <Column field="invoice_number" header="Invoice No" style="min-width: 130px">
                            <template #body="{ data }">
                                <span class="font-mono text-sm">{{ data.invoice_number || 'N/A' }}</span>
                            </template>
                        </Column>
                        <Column field="token.reference" header="HBL Reference" style="min-width: 150px">
                            <template #body="{ data }">
                                <span class="font-semibold">{{ data.token?.reference || 'N/A' }}</span>
                            </template>
                        </Column>
                        <Column field="token.customer.name" header="Customer" style="min-width: 180px">
                            <template #body="{ data }">
                                <div>
                                    <div class="font-medium">{{ data.token?.customer?.name || 'N/A' }}</div>
                                    <div class="text-xs text-gray-500">{{ data.token?.customer?.contact || '' }}</div>
                                </div>
                            </template>
                        </Column>
                        <Column field="paid_amount" header="Amount (LKR)" style="min-width: 130px">
                            <template #body="{ data }">
                                <span class="font-bold text-green-600">{{ formatCurrency(data.paid_amount) }}</span>
                            </template>
                        </Column>
                        <Column header="Status" style="min-width: 100px">
                            <template #body>
                                <span class="px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                    Paid
                                </span>
                            </template>
                        </Column>
                        <Column field="verified_by" header="Collected By" style="min-width: 150px">
                            <template #body="{ data }">
                                {{ data.verified_by?.name || 'N/A' }}
                            </template>
                        </Column>
                        <Column field="note" header="Notes" style="min-width: 200px">
                            <template #body="{ data }">
                                <span class="text-sm text-gray-600">{{ data.note || '-' }}</span>
                            </template>
                        </Column>
                    </DataTable>
                </template>
            </Card>
        </div>
    </AppLayout>
</template>
