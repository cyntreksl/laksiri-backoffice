<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import {onMounted, ref, watch} from "vue";
import { router, usePage} from "@inertiajs/vue3";
import Card from "primevue/card";
import DataTable from "primevue/datatable";
import InputIcon from "primevue/inputicon";
import InputText from "primevue/inputtext";
import Column from "primevue/column";
import Button from "primevue/button";
import IconField from "primevue/iconfield";
import {useConfirm} from "primevue/useconfirm";
import {FilterMatchMode} from "@primevue/core/api";
import axios from "axios";
import {debounce} from "lodash";
import {push} from "notivue";
import DatePicker from "primevue/datepicker";
import moment from "moment";
import {align} from "quill/ui/icons.js";

const confirm = useConfirm();
const baseUrl = ref("container-payment-refund-list");
const loading = ref(true);
const containerRefunds = ref([]);
const totalRecords = ref(0);
const perPage = ref(10);
const currentPage = ref(1);
const selectedContainerPayments = ref([]);

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    created_at: { value: null, matchMode: FilterMatchMode.EQUALS },
});

const fetchContainerRefunds = async (page = 1, search = "", sortField = 'id', sortOrder = 0) => {
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
        containerRefunds.value = response.data.data;
        totalRecords.value = response.data.meta.total;
        currentPage.value = response.data.meta.current_page;
    } catch (error) {
        console.error("Error fetching Container Payments:", error);
    } finally {
        loading.value = false;
    }
};

watch(() => filters.value.created_at.value, (newValue) => {
    fetchContainerRefunds(1, filters.value.global.value);
});

const onPageChange = (event) => {
    perPage.value = event.rows;
    currentPage.value = event.page + 1;
    fetchContainerRefunds(currentPage.value);
};

const onSort = (event) => {
    fetchContainerRefunds(currentPage.value, filters.value.global.value, event.sortField, event.sortOrder);
};

onMounted(() => {
    fetchContainerRefunds();
});

const debouncedFetchContainerPayments = debounce((searchValue) => {
    fetchContainerRefunds(1, searchValue);
}, 1000);

watch(() => filters.value.global.value, (newValue) => {
    if (newValue !== null) {
        debouncedFetchContainerPayments(newValue);
    }
});

const confirmRefundCollection = () => {
    const idList = selectedContainerPayments.value.map((item) => item.id);
    confirm.require({
        message: 'Are you sure you want to mark as Collected?',
        header: 'Mark As Collected?',
        icon: 'pi pi-info-circle',
        rejectLabel: 'Cancel',
        rejectProps: {
            label: 'Cancel',
            severity: 'secondary',
            outlined: true
        },
        acceptProps: {
            label: 'Mark As Collected',
            severity: 'success'
        },
        accept: async () => {
            router.post(route("container-payment.refund-collection"), {
                data: {
                    container_payments_ids: idList,
                },
                onSuccess: () => {
                    push.success("Container Payment marked as collected successfully!");
                },
                onError: () => {
                    push.error("Something went wrong!");
                },
                preserveScroll: true,
            });
            selectedContainerPayments.value = [];
            await fetchContainerRefunds();
        },
        reject: () => {
            selectedContainerPayments.value = [];
        }
    });
};

const clearFilter = () => {
    filters.value = {
        global: { value: null, matchMode: FilterMatchMode.CONTAINS },
        created_at: { value: null, matchMode: FilterMatchMode.EQUALS },
    };
    fetchContainerRefunds(currentPage.value);
};
</script>

<template>
    <AppLayout title="Container Refunds">
        <template #header>Container Refunds</template>

        <Breadcrumb/>

        <Card class="my-5">
            <template #content>
                <DataTable
                    v-model:selection="selectedContainerPayments"
                    v-model:filters="filters"
                    :loading="loading"
                    :rows="perPage"
                    :rowsPerPageOptions="[5, 10, 20, 50, 100]"
                    :totalRecords="totalRecords"
                    :value="containerRefunds"
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
                                Container Refunds
                            </div>
                            <div class="flex items-center gap-3">
                                <Button
                                    type="button"
                                    v-if="usePage().props.user.permissions.includes('payment-container.collect refund')"
                                    label="Mark As Collected" icon="ti ti-cash"
                                    :disabled="selectedContainerPayments.length === 0"
                                    @click="confirmRefundCollection" />
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
                    <template #empty> No Container Refunds found. </template>
                    <template #loading> Loading Container Refunds data. Please wait.</template>
                    <Column v-if="usePage().props.user.permissions.includes('payment-container.collect refund')" headerStyle="width: 3rem" selectionMode="multiple"></Column>
                    <Column field="containerReference" header="Container Reference" sortable></Column>
                    <Column field="refund_charge" header="Refund Charge" header-class="!text-right">
                        <template #body="slotProps">
                            {{ slotProps.data.refund_charge.toFixed(2) }}
                        </template>
                    </Column>
                    <Column field="created_at" header="Created At" sortable>
                        <template #filter="{ filterModel, filterCallback }">
                            <DatePicker v-model="filterModel.value" class="w-full" date-format="yy-mm-dd" placeholder="Set Date"/>
                        </template>
                    </Column>
                    <Column field="is_finance_approved" header="Finance Approval">
                        <template #body="{ data }">
                            <div class="flex items-center">
                                <div v-if="data.is_finance_approved" class="text-green-500">
                                    <i class="pi pi-check-circle"></i>
                                    Approved
                                </div>
                                <div v-else class="text-red-400">
                                    <i class="pi pi-times-circle"></i>
                                    Not Approved
                                </div>
                            </div>
                        </template>
                    </Column>
                    <template #footer> In total there are {{ containerRefunds ? totalRecords : 0 }} container Refunds.</template>
                </DataTable>
            </template>
        </Card>
    </AppLayout>
</template>
