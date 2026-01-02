<script setup>
import Breadcrumb from "@/Components/Breadcrumb.vue";
import {onMounted, ref, watch} from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import {FilterMatchMode} from "@primevue/core/api";
import axios from "axios";
import {debounce} from "lodash";
import Button from "primevue/button";
import Card from "primevue/card";
import Column from "primevue/column";
import DataTable from "primevue/datatable";
import Select from "primevue/select";
import DatePicker from "primevue/datepicker";
import moment from "moment";

const props = defineProps({
    // Remove customers and users from props - will load via API
})

const baseUrl = ref("/call-center/cashier/paid/list");
const loading = ref(true);
const tokens = ref([]);
const totalRecords = ref(0);
const perPage = ref(10);
const currentPage = ref(1);
const dt = ref();
const customers = ref([]);
const users = ref([]);
const loadingCustomers = ref(false);
const loadingUsers = ref(false);

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    customer: { value: null, matchMode: FilterMatchMode.CONTAINS },
    reception: { value: null, matchMode: FilterMatchMode.CONTAINS },
    verified_by: { value: null, matchMode: FilterMatchMode.CONTAINS },
    paid_at: { value: null, matchMode: FilterMatchMode.EQUALS },
});

const fetchTokens = async (
    page = 1,
    search = "",
    sortField = 'created_at',
    sortOrder = 0,
    customer = "",
    reception = "",
    verified_by = "",
    paid_at = ""
) => {
    loading.value = true;
    try {
        let paidAtParam = '';
        if (paid_at && moment(paid_at).isValid()) {
            paidAtParam = moment(paid_at).format("YYYY-MM-DD");
        }
        const params = {
            page,
            per_page: perPage.value,
            search,
            sort_field: sortField,
            sort_order: sortOrder === 1 ? "asc" : "desc",
            customer,
            reception,
            verified_by,
        };
        if (paidAtParam) {
            params.paid_at = paidAtParam;
        }
        const response = await axios.get(baseUrl.value, { params });
        tokens.value = response.data.data;
        totalRecords.value = response.data.meta.total;
        currentPage.value = response.data.meta.current_page;
    } catch (error) {
        console.error("Error fetching tokens:", error);
    } finally {
        loading.value = false;
    }
};

const debouncedFetchTokens = debounce((searchValue) => {
    fetchTokens(1, searchValue);
}, 1000);

watch(() => filters.value.global.value, () => {
    if (filters.value.global.value !== null) {
        debouncedFetchTokens(filters.value.global.value);
    }
});

watch(() => filters.value.customer.value, () => {
    fetchTokens(
        1,
        filters.value.global.value,
        'created_at',
        0,
        filters.value.customer.value,
        filters.value.reception.value,
        filters.value.verified_by.value,
        filters.value.paid_at.value
    );
});

watch(() => filters.value.reception.value, () => {
    fetchTokens(
        1,
        filters.value.global.value,
        'created_at',
        0,
        filters.value.customer.value,
        filters.value.reception.value,
        filters.value.verified_by.value,
        filters.value.paid_at.value
    );
});

watch(() => filters.value.verified_by.value, () => {
    fetchTokens(
        1,
        filters.value.global.value,
        'created_at',
        0,
        filters.value.customer.value,
        filters.value.reception.value,
        filters.value.verified_by.value,
        filters.value.paid_at.value
    );
});

watch(() => filters.value.paid_at.value, () => {
    fetchTokens(
        1,
        filters.value.global.value,
        'created_at',
        0,
        filters.value.customer.value,
        filters.value.reception.value,
        filters.value.verified_by.value,
        filters.value.paid_at.value
    );
});

const onPageChange = (event) => {
    perPage.value = event.rows;
    currentPage.value = event.page + 1;
    fetchTokens(
        currentPage.value,
        filters.value.global.value,
        'created_at',
        0,
        filters.value.customer.value,
        filters.value.reception.value,
        filters.value.verified_by.value,
        filters.value.paid_at.value
    );
};

const onSort = (event) => {
    fetchTokens(
        currentPage.value,
        filters.value.global.value,
        event.sortField,
        event.sortOrder,
        filters.value.customer.value,
        filters.value.reception.value,
        filters.value.verified_by.value,
        filters.value.paid_at.value
    );
};

onMounted(() => {
    fetchTokens();
    loadCustomers();
    loadUsers();
});

const loadCustomers = async (search = '') => {
    loadingCustomers.value = true;
    try {
        const response = await axios.get('/call-center/cashier/search-customers', {
            params: { search }
        });
        customers.value = response.data;
    } catch (error) {
        console.error("Error loading customers:", error);
    } finally {
        loadingCustomers.value = false;
    }
};

const loadUsers = async (search = '') => {
    loadingUsers.value = true;
    try {
        const response = await axios.get('/call-center/cashier/search-users', {
            params: { search }
        });
        users.value = response.data;
    } catch (error) {
        console.error("Error loading users:", error);
    } finally {
        loadingUsers.value = false;
    }
};

const exportCSV = () => {
    dt.value.exportCSV();
};
</script>

<template>
    <AppLayout title="Paid Queue">
        <template #header>Paid Queue</template>

        <Breadcrumb />

        <div>
            <Card class="my-5">
                <template #content>
                    <DataTable
                        ref="dt"
                        v-model:filters="filters"
                        :loading="loading"
                        :rows="perPage"
                        :rowsPerPageOptions="[5, 10, 20, 50, 100]"
                        :totalRecords="totalRecords"
                        :value="tokens"
                        data-key="id"
                        filter-display="menu"
                        lazy
                        paginator
                        removable-sort
                        row-hover
                        tableStyle="min-width: 50rem"
                        @page="onPageChange"
                        @sort="onSort">

                        <template #header>
                            <div class="flex flex-col sm:flex-row justify-between items-center mb-2">
                                <div class="text-lg font-medium">
                                    Paid Queue
                                </div>
                            </div>
                            <div class="flex flex-col sm:flex-row justify-between gap-4">
                                <Button
                                    icon="pi pi-external-link"
                                    label="Export"
                                    severity="contrast"
                                    size="small"
                                    @click="exportCSV($event)"
                                />
                            </div>
                        </template>

                        <template #empty>No tokens found.</template>

                        <template #loading>Loading tokens data. Please wait.</template>

                        <Column field="token" header="Token">
                            <template #body="slotProps">
                                <div class="flex items-center text-2xl">
                                    <i class="ti ti-tag mr-1 text-blue-500"></i>
                                    {{ slotProps.data.token }}
                                </div>
                            </template>
                        </Column>

                        <Column :showFilterMenu="true" field="customer" filterField="customer" header="Customer">
                            <template #filter="{ filterModel }">
                                <Select
                                    v-model="filterModel.value"
                                    :options="customers"
                                    :loading="loadingCustomers"
                                    filter
                                    option-label="name"
                                    option-value="name"
                                    placeholder="Select Customer"
                                    @filter="(event) => loadCustomers(event.value)"
                                />
                            </template>
                        </Column>

                        <Column :showFilterMenu="true" field="reception" filterField="reception" header="Reception">
                            <template #filter="{ filterModel }">
                                <Select
                                    v-model="filterModel.value"
                                    :options="users"
                                    :loading="loadingUsers"
                                    filter
                                    option-label="name"
                                    option-value="name"
                                    placeholder="Select Reception"
                                    @filter="(event) => loadUsers(event.value)"
                                />
                            </template>
                        </Column>

                        <Column field="package_count" header="Packages">
                            <template #body="slotProps">
                                <div class="flex items-center">
                                    <i class="ti ti-package mr-1 text-blue-500" style="font-size: 1rem"></i>
                                    {{ slotProps.data.package_count }}
                                </div>
                            </template>
                        </Column>

                        <Column :showFilterMenu="true" field="paid_at" filterField="paid_at" header="Paid At">
                            <template #filter="{ filterModel }">
                                <DatePicker
                                    v-model="filterModel.value"
                                    class="w-full"
                                    dateFormat="yy-mm-dd"
                                    placeholder="Select Date"
                                    showIcon
                                />
                            </template>
                            <template #body="slotProps">
                                {{ slotProps.data.paid_at ? moment(slotProps.data.paid_at).format('YYYY-MM-DD') : '' }}
                            </template>
                        </Column>

                        <Column field="paid_amount" header="Paid Amount">
                            <template #body="slotProps">
                                <div class="flex items-center justify-end">
                                    <i class="ti ti-cash mr-1 text-blue-500" style="font-size: 1rem"></i>
                                    {{ Number(slotProps.data.paid_amount).toFixed(2) }}
                                </div>
                            </template>
                        </Column>

                        <Column :showFilterMenu="true" field="verified_by" filterField="verified_by" header="Verified By">
                            <template #filter="{ filterModel }">
                                <Select
                                    v-model="filterModel.value"
                                    :options="users"
                                    :loading="loadingUsers"
                                    filter
                                    option-label="name"
                                    option-value="name"
                                    placeholder="Select User"
                                    @filter="(event) => loadUsers(event.value)"
                                />
                            </template>
                        </Column>

                        <Column field="note" header="Note"></Column>

                        <template #footer> In total there are {{ tokens ? totalRecords : 0 }} tokens.</template>
                    </DataTable>
                </template>
            </Card>
        </div>
    </AppLayout>
</template>
