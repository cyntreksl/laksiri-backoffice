<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import {onMounted, ref, watch} from "vue";
import Card from "primevue/card";
import DataTable from "primevue/datatable";
import InputIcon from "primevue/inputicon";
import InputText from "primevue/inputtext";
import Column from "primevue/column";
import Button from "primevue/button";
import IconField from "primevue/iconfield";
import {FilterMatchMode} from "@primevue/core/api";
import axios from "axios";
import {debounce} from "lodash";
import DatePicker from "primevue/datepicker";
import moment from "moment";

const baseUrl = ref("/container-payment-completed-list");
const loading = ref(true);
const containerPayments = ref([]);
const totalRecords = ref(0);
const perPage = ref(10);
const currentPage = ref(1);

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    created_at: { value: null, matchMode: FilterMatchMode.EQUALS },
});

const fetchContainerPayments = async (page = 1, search = "", sortField = 'id', sortOrder = 0) => {
    loading.value = true;
    try {
        const response = await axios.get(baseUrl.value, {
            params: {
                page,
                per_page: perPage.value,
                search,
                sort_field: sortField,
                sort_order: sortOrder === 1 ? "desc" : "asc",
                fromDate: filters.value.created_at?.value
                    ? moment(filters.value.created_at.value).format("YYYY-MM-DD")
                    : null,
            }
        });
        containerPayments.value = response.data.data;
        totalRecords.value = response.data.meta.total;
        currentPage.value = response.data.meta.current_page;
    } catch (error) {
        console.error("Error fetching Container Payments:", error);
    } finally {
        loading.value = false;
    }
};

const onPageChange = (event) => {
    perPage.value = event.rows;
    currentPage.value = event.page + 1;
    fetchContainerPayments(currentPage.value);
};

const onSort = (event) => {
    fetchContainerPayments(currentPage.value, filters.value.global.value, event.sortField, event.sortOrder);
};

onMounted(() => {
    fetchContainerPayments();
});

const debouncedFetchContainerPayments = debounce((searchValue) => {
    fetchContainerPayments(1, searchValue);
}, 1000);

watch(() => filters.value.global.value, (newValue) => {
    if (newValue !== null) {
        debouncedFetchContainerPayments(newValue);
    }
});

watch(() => filters.value.created_at.value, (newValue) => {
    fetchContainerPayments(1, filters.value.global.value);
});

const clearFilter = () => {
    filters.value = {
        global: { value: null, matchMode: FilterMatchMode.CONTAINS },
        created_at: { value: null, matchMode: FilterMatchMode.EQUALS },
    };
    fetchContainerPayments(currentPage.value);
};
</script>
<template>
    <AppLayout title="Completed Container Payments">
        <template #header>Completed Container Payments</template>

        <Breadcrumb/>

        <Card class="my-5">
            <template #content>
                <DataTable
                    v-model:filters="filters"
                    :loading="loading"
                    :rows="perPage"
                    :rowsPerPageOptions="[5, 10, 20, 50, 100]"
                    :totalRecords="totalRecords"
                    :value="containerPayments"
                    dataKey="id"
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
                                Completed Container Payments
                            </div>
                        </div>
                        <div class="flex flex-col sm:flex-row justify-between gap-4">
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
                            </div>

                            <!-- Search Field -->
                            <IconField class="w-full sm:w-auto">
                                <InputIcon>
                                    <i class="pi pi-search" />
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
                    <template #empty> No Completed Container Payments found. </template>
                    <template #loading> Loading Completed Container Payments data. Please wait.</template>
                    <Column field="containerReference" header="Container Reference" sortable></Column>
                    <Column field="do_charge" header="DO Charge" header-class="!text-right">
                        <template #body="slotProps">
                            <div class="text-right">
                                {{ slotProps.data.do_charge.toFixed(2) }}
                            </div>
                        </template>
                    </Column>
                    <Column field="demurrage_charge" header="Demurrage Charge" header-class="!text-right">
                        <template #body="slotProps">
                            <div class="text-right">
                                {{ slotProps.data.demurrage_charge.toFixed(2) }}
                            </div>
                        </template>
                    </Column>
                    <Column field="assessment_charge" header="Assessment Charge" header-class="!text-right">
                        <template #body="slotProps">
                            <div class="text-right">
                                {{ slotProps.data.assessment_charge.toFixed(2) }}
                            </div>
                        </template>
                    </Column>
                    <Column field="slpa_charge" header="SLPA Charge" header-class="!text-right">
                        <template #body="slotProps">
                            <div class="text-right">
                                {{ slotProps.data.slpa_charge.toFixed(2) }}
                            </div>
                        </template>
                    </Column>
                    <Column field="refund_charge" header="Refund Charge" header-class="!text-right">
                        <template #body="slotProps">
                            <div class="text-right">
                                {{ slotProps.data.refund_charge.toFixed(2) }}
                            </div>
                        </template>
                    </Column>
                    <Column field="clearance_charge" header="Clearance Charge" header-class="!text-right">
                        <template #body="slotProps">
                            <div class="text-right">
                                {{ slotProps.data.clearance_charge.toFixed(2) }}
                            </div>
                        </template>
                    </Column>
                    <Column field="total" header="Total" header-class="!text-right">
                        <template #body="slotProps">
                            <div class="text-right">
                                {{ slotProps.data.total.toFixed(2) }}
                            </div>
                        </template>
                    </Column>
                    <Column field="created_at" header="Created At" sortable>
                        <template #filter="{ filterModel, filterCallback }">
                            <DatePicker v-model="filterModel.value" class="w-full" date-format="yy-mm-dd" placeholder="Set Date"/>
                        </template>
                    </Column>
                    <Column field="is_finance_approved" header="Finance Approval" >
                        <template #body="{ data }">
                            <div class="text-center">
                                <i :class="{ 'pi-check-circle text-green-500': data.is_finance_approved, 'pi-times-circle text-red-400': !data.is_finance_approved }" class="pi"></i>
                            </div>
                        </template>
                    </Column>
                    <template #footer> In total there are {{ containerPayments ? totalRecords : 0 }} Completed Container Payments.</template>
                </DataTable>
            </template>
        </Card>
    </AppLayout>
</template>
