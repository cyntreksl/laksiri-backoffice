<script setup>
import {onMounted, ref, watch} from "vue";
import { router, usePage} from "@inertiajs/vue3";
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
import moment from "moment";

const props = defineProps({
    containerId: {
        type: Number,
        required: true
    }
})

const confirm = useConfirm();
const baseUrl = ref("/get-container-payment-request-list");
const loading = ref(true);
const containerPayments = ref([]);
const totalRecords = ref(0);
const perPage = ref(10);
const currentPage = ref(1);
const selectedContainerPayments = ref([]);

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    created_at: { value: null, matchMode: FilterMatchMode.EQUALS },
});

const fetchContainerPaymentRequests = async (page = 1, search = "", sortField = 'id', sortOrder = 0) => {
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
        containerPayments.value = response.data.data.filter(item => item.container_id === props.containerId)
        totalRecords.value = containerPayments.value.length;
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
    fetchContainerPaymentRequests(currentPage.value);
};

const onSort = (event) => {
    fetchContainerPaymentRequests(currentPage.value, filters.value.global.value, event.sortField, event.sortOrder);
};

onMounted(() => {
    fetchContainerPaymentRequests();
});

const debouncedFetchContainerPayments = debounce((searchValue) => {
    fetchContainerPaymentRequests(1, searchValue);
}, 1000);

watch(() => filters.value.global.value, (newValue) => {
    if (newValue !== null) {
        debouncedFetchContainerPayments(newValue);
    }
});

watch(() => filters.value.created_at.value, (newValue) => {
    fetchContainerPaymentRequests(1, filters.value.global.value);
});

const approveContainerPayments = () => {
    const idList = selectedContainerPayments.value.map((item) => item.id);
    confirm.require({
        message: 'Are you sure you want to approve container payments?',
        header: 'Approve Container Payments?',
        icon: 'pi pi-info-circle',
        rejectLabel: 'Cancel',
        rejectProps: {
            label: 'Cancel',
            severity: 'secondary',
            outlined: true
        },
        acceptProps: {
            label: 'Approve Container Payments',
            severity: 'success'
        },
        accept: async () => {
            router.post(route("finance.container-payment.approve"), {
                data: {
                    container_payments_ids: idList,
                },
                preserveScroll: true,
                onSuccess: () => {
                    push.success("Container Payment Approved successfully!");
                },
                onError: () => {
                    push.error("Something went wrong!");
                },
            });
            selectedContainerPayments.value = [];
            await fetchContainerPaymentRequests();
        },
        reject: () => {
            selectedContainerPayments.value = [];
        }
    });
}

const clearFilter = () => {
    filters.value = {
        global: { value: null, matchMode: FilterMatchMode.CONTAINS },
        created_at: { value: null, matchMode: FilterMatchMode.EQUALS },
    };
    fetchContainerPaymentRequests(currentPage.value);
};
</script>
<template>
    <DataTable
        v-model:filters="filters"
        v-model:selection="selectedContainerPayments"
        :loading="loading"
        :rows="perPage"
        :rowsPerPageOptions="[5, 10, 20, 50, 100]"
        :totalRecords="totalRecords"
        :value="containerPayments"
        class="border border-emerald-400 rounded-lg p-5 mt-5"
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
                    Container Payment Requests
                </div>
                <div class="flex items-center gap-3">
                    <Button
                        v-if="usePage().props.user.permissions.includes('payment-container.approve')"
                        :disabled="selectedContainerPayments.length === 0"
                        icon="ti ti-cash" label="Approve Container Payments"
                        type="button"
                        @click="approveContainerPayments" />
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
        <template #empty> No Container Payment Request found. </template>
        <template #loading> Loading Container Payment Request data. Please wait.</template>
        <Column v-if="usePage().props.user.permissions.includes('payment-container.approve')" headerStyle="width: 3rem" selectionMode="multiple"></Column>
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
        <Column field="created_at" header="Created At" sortable></Column>
        <Column field="is_finance_approved" header="Finance Approval" >
            <template #body="{ data }">
                <div class="text-center">
                    <i :class="{ 'pi-check-circle text-green-500': data.is_finance_approved, 'pi-times-circle text-red-400': !data.is_finance_approved }" class="pi"></i>
                </div>
            </template>
        </Column>
        <template #footer> In total there are {{ containerPayments ? totalRecords : 0 }} container payments requests.</template>
    </DataTable>
</template>
