<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import {onMounted, ref, watch} from "vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import moment from "moment";
import {FilterMatchMode} from "@primevue/core/api";
import axios from "axios";
import {debounce} from "lodash";
import FloatLabel from "primevue/floatlabel";
import Card from "primevue/card";
import InputText from "primevue/inputtext";
import InputIcon from "primevue/inputicon";
import Tag from "primevue/tag";
import Panel from "primevue/panel";
import DatePicker from "primevue/datepicker";
import DataTable from "primevue/datatable";
import Button from "primevue/button";
import IconField from "primevue/iconfield";
import Column from "primevue/column";

const baseUrl = ref("/customer-list");
const loading = ref(true);
const customers = ref([]);
const totalRecords = ref(0);
const perPage = ref(10);
const currentPage = ref(1);
const dt = ref();
const fromDate = ref(moment(new Date()).subtract(1, "months").toISOString().split("T")[0]);
const toDate = ref(moment(new Date()).toISOString().split("T")[0]);

const filters = ref({
    global: {value: null, matchMode: FilterMatchMode.CONTAINS},
});

const fetchCustomers = async (page = 1, search = "", sortField = 'created_at', sortOrder = 0) => {
    loading.value = true;
    try {
        const response = await axios.get(baseUrl.value, {
            params: {
                page,
                per_page: perPage.value,
                search,
                sort_field: sortField,
                sort_order: sortOrder === 1 ? "asc" : "desc",
                fromDate: moment(fromDate.value).format("YYYY-MM-DD"),
                toDate: moment(toDate.value).format("YYYY-MM-DD"),
            }
        });
        customers.value = response.data.data;
        totalRecords.value = response.data.meta.total;
        currentPage.value = response.data.meta.current_page;
    } catch (error) {
        console.error("Error fetching customers:", error);
    } finally {
        loading.value = false;
    }
};

const debouncedFetchCustomers = debounce((searchValue) => {
    fetchCustomers(1, searchValue);
}, 1000);

watch(() => filters.value.global.value, (newValue) => {
    if (newValue !== null) {
        debouncedFetchCustomers(newValue);
    }
});

watch(() => fromDate.value, (newValue) => {
    fetchCustomers(1, filters.value.global.value);
});

watch(() => toDate.value, (newValue) => {
    fetchCustomers(1, filters.value.global.value);
});

const onPageChange = (event) => {
    perPage.value = event.rows;
    currentPage.value = event.page + 1;
    fetchCustomers(currentPage.value);
};

const onSort = (event) => {
    fetchCustomers(currentPage.value, filters.value.global.value, event.sortField, event.sortOrder);
};

onMounted(() => {
    fetchCustomers();
});

const clearFilter = () => {
    filters.value = {
        global: {value: null, matchMode: FilterMatchMode.CONTAINS},
    };
    fromDate.value = moment(new Date()).subtract(1, "months").toISOString().split("T")[0];
    toDate.value = moment(new Date()).toISOString().split("T")[0];
    fetchCustomers(currentPage.value);
};

const exportCSV = () => {
    dt.value.exportCSV();
};

const resolveStatus = (status) =>
    ({
        ACTIVE: "success",
        DEACTIVATE: "danger",
        INACTIVE: "warn",
        INVITED: "info",
    }[status]);
</script>

<template>
    <AppLayout title="Customers">
        <template #header>Customers</template>

        <Breadcrumb/>

        <div>
            <Panel :collapsed="true" class="mt-5" header="Advance Filters" toggleable>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                    <FloatLabel class="w-full" variant="in">
                        <DatePicker v-model="fromDate" class="w-full" date-format="yy-mm-dd" input-id="from-date"/>
                        <label for="from-date">From Date</label>
                    </FloatLabel>

                    <FloatLabel class="w-full" variant="in">
                        <DatePicker v-model="toDate" class="w-full" date-format="yy-mm-dd" input-id="to-date"/>
                        <label for="to-date">To Date</label>
                    </FloatLabel>
                </div>
            </Panel>

            <Card class="my-5">
                <template #content>
                    <DataTable
                        ref="dt"
                        v-model:filters="filters"
                        :loading="loading"
                        :rows="perPage"
                        :rowsPerPageOptions="[5, 10, 20, 50, 100]"
                        :totalRecords="totalRecords"
                        :value="customers"
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
                                    Customers
                                </div>
                            </div>
                            <div class="flex flex-col sm:flex-row justify-between gap-4">
                                <!-- Button Group -->
                                <div class="flex flex-col sm:flex-row gap-2">
                                    <Button
                                        icon="pi pi-filter-slash"
                                        label="Clear Filters"
                                        outlined
                                        severity="contrast"
                                        size="small"
                                        type="button"
                                        @click="clearFilter()"
                                    />

                                    <Button
                                        icon="pi pi-external-link"
                                        label="Export"
                                        severity="contrast"
                                        size="small"
                                        @click="exportCSV($event)"
                                    />
                                </div>

                                <!-- Search Field -->
                                <IconField class="w-full sm:w-auto">
                                    <InputIcon>
                                        <i class="pi pi-search"/>
                                    </InputIcon>
                                    <InputText
                                        v-model="filters.global.value"
                                        class="w-full"
                                        placeholder="Keyword Search"
                                        size="small"
                                    />
                                </IconField>
                            </div>
                        </template>

                        <template #empty>No customers found.</template>

                        <template #loading>Loading customers data. Please wait.</template>

                        <Column field="username" header="Username" sortable></Column>

                        <Column field="name" header="Name" sortable></Column>

                        <Column field="primary_branch_name" header="Primary Branch"></Column>

                        <Column field="created_at" header="Created At"></Column>

                        <Column field="status" header="Status">
                            <template #body="slotProps">
                                <div v-if="slotProps.data.status">
                                    <Tag
                                        :severity="resolveStatus(slotProps.data.status)"
                                        :value="slotProps.data.status"
                                        class="mr-1 mb-1"
                                    />
                                </div>
                            </template>
                        </Column>

                        <template #footer> In total there are {{ customers ? totalRecords : 0 }} customers.</template>
                    </DataTable>
                </template>
            </Card>
        </div>
    </AppLayout>
</template>
