<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import {onMounted, ref, watch} from "vue";
import { router, useForm, usePage} from "@inertiajs/vue3";
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
import Dialog from "primevue/dialog";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import Panel from "primevue/panel";
import DatePicker from "primevue/datepicker";
import FloatLabel from "primevue/floatlabel";
import moment from "moment/moment.js";

const confirm = useConfirm();
const baseUrl = ref("approved-container-payments-list");
const loading = ref(true);
const containerPayments = ref([]);
const totalRecords = ref(0);
const perPage = ref(10);
const currentPage = ref(1);
const selectedContainerPayments = ref([]);
const selectedContainerPaymentId = ref(null);
const isDialogVisible = ref(false);
const showEditContainerPaymentDialog = ref(false);
const checked = ref(false);
const showEditNewContainerPaymentDialog = ref(false);
const fromDate = ref(moment(new Date()).subtract(24, "months").toISOString().split("T")[0]);

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
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
                fromDate: moment(fromDate.value).format("YYYY-MM-DD"),
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

watch(() => fromDate.value, (newValue) => {
    fetchContainerPayments(1, filters.value.global.value);
});

// Updated formatDate function with error handling and fallback options
const formatTime = (timeStr) => {
    const date = new Date(timeStr);

    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0'); // Months are 0-based
    const year = String(date.getFullYear()).slice(-2); // Get last 2 digits of year

    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');

    return `${day}-${month}-${year} ${hours}:${minutes}`;
};

const revokeContainerPayments = () => {
    const idList = selectedContainerPayments.value.map((item) => item.id);
    confirm.require({
        message: 'Are you sure you want to revoke approvals?',
        header: 'Revoke Approvals?',
        icon: 'pi pi-info-circle',
        rejectLabel: 'Cancel',
        rejectProps: {
            label: 'Cancel',
            severity: 'secondary',
            outlined: true
        },
        acceptProps: {
            label: 'Revoke Approvals',
            severity: 'warn'
        },
        accept: async () => {
            router.post(route("finance.container-payment.revoke-approval"), {
                data: {
                    container_payments_ids: idList,
                },
                preserveScroll: true,
                onSuccess: () => {
                    push.success("Container Payment Approvals Revoked successfully!");
                },
                onError: () => {
                    push.error("Something went wrong!");
                },
            });
            selectedContainerPayments.value = [];
            await fetchContainerPayments();
        },
        reject: () => {
            selectedContainerPayments.value = [];
        }
    });
}

const form = useForm({
    paymentsIds: [],
    payment_received_by: "",
});

const showMarkAsPaidModal = ref(false);

const onDialogShow = () => {
    document.body.classList.add('p-overflow-hidden');
};

const onDialogHide = () => {
    form.reset();
    form.clearErrors();
    document.body.classList.remove('p-overflow-hidden');
};

const closeShowPaidModal = () => {
    form.reset();
    showMarkAsPaidModal.value = false;
}

const handleMarkAsPaid = () => {
    form.paymentsIds = selectedContainerPayments.value.map((item) => item.id);
    console.log(selectedContainerPayments.value, form.data());
}


</script>
<template>
    <AppLayout title="Container Payments Requests">
        <template #header>Container Payments Requests</template>
        <Breadcrumb/>

        <div>
            <Panel :collapsed="true" class="mt-5" header="Advance Filters" toggleable>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                    <FloatLabel class="w-full" variant="in">
                        <DatePicker v-model="fromDate" class="w-full" date-format="yy-mm-dd" input-id="from-date"/>
                        <label for="from-date">Created Date</label>
                    </FloatLabel>
                </div>
            </Panel>
        </div>
        <Card class="my-5">
            <template #content>
                <DataTable
                    v-model:selection="selectedContainerPayments"
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
                                Container Payment Requests
                            </div>
                            <div class="flex items-center gap-3">
                                <Button
                                    type="button"
                                    v-if="usePage().props.user.permissions.includes('payment-container.approve')"
                                    label="Mark As Paid" icon="ti ti-cash"
                                    :disabled="selectedContainerPayments.length === 0"
                                    @click="showMarkAsPaidModal = true" />
                                <Button
                                    type="button"
                                    v-if="usePage().props.user.permissions.includes('payment-container.approve')"
                                    label="Revoke Approvals" icon="ti ti-cash"
                                    :disabled="selectedContainerPayments.length === 0"
                                    @click="revokeContainerPayments" />
                            </div>
                        </div>
                        <div class="flex flex-col sm:flex-row justify-between gap-4">
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
                    <template #empty> No Container Payment found. </template>
                    <template #loading> Loading Container Payment data. Please wait.</template>
                    <Column headerStyle="width: 3rem" selectionMode="multiple"></Column>
                    <Column field="containerReference" header="Container Reference" sortable></Column>
                    <Column field="do_charge" header="DO Charge" sortable></Column>
                    <Column field="demurrage_charge" header="Demurrage Charge" sortable></Column>
                    <Column field="assessment_charge" header="Assessment Charge" sortable></Column>
                    <Column field="slpa_charge" header="SLPA Charge" sortable></Column>
                    <Column field="refund_charge" header="Refund Charge" sortable></Column>
                    <Column field="clearance_harge" header="Clearance Charge" sortable></Column>
                    <Column field="total" header="Total" sortable></Column>
                    <Column field="created_at" header="Created Date" sortable>
                        <template #body="{ data }">
                            {{formatTime(data.created_at)}}
                        </template>
                    </Column>
                    <template #footer> In total there are {{ containerPayments ? totalRecords : 0 }} Container Payments.</template>
                </DataTable>
            </template>
        </Card>

        <!-- Add New Air Line Dialog -->
        <Dialog
            v-model:visible="showMarkAsPaidModal"
            modal
            header="Mark As Paid"
            :style="{ width: '90%', maxWidth: '450px' }"
            :block-scroll="true"
            @hide="onDialogHide"
            @show="onDialogShow"
        >
            <div class="mt-4">
                <InputLabel value="Payment Received By"/>
                <InputText
                    v-model="form.payment_received_by"
                    class="w-full p-inputtext"
                    placeholder="Enter Payment Receiver Name"
                    required
                    type="text"
                />
                <InputError :message="form.errors.payment_received_by"/>
            </div>

            <template #footer>
                <div class="flex flex-wrap justify-end gap-2">
                    <Button label="Cancel" class="p-button-text" @click="closeShowPaidModal"/>
                    <Button
                        label="Mark As Paid"
                        class="p-button-primary"
                        icon="pi pi-check"
                        @click.prevent="handleMarkAsPaid()"
                    />
                </div>
            </template>
        </Dialog>
    </AppLayout>
</template>
