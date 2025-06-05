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
import DatePicker from "primevue/datepicker";
import moment from "moment";

const confirm = useConfirm();
const baseUrl = ref("approved-container-payments-list");
const loading = ref(true);
const containerPayments = ref([]);
const totalRecords = ref(0);
const perPage = ref(10);
const currentPage = ref(1);
const selectedContainerPayments = ref([]);
const showMarkAsPaidModal = ref(false);

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    created_at: { value: null, matchMode: FilterMatchMode.EQUALS },
});

const fetchContainerApprovedPayments = async (page = 1, search = "", sortField = 'id', sortOrder = 0) => {
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
    fetchContainerApprovedPayments(currentPage.value);
};

const onSort = (event) => {
    fetchContainerApprovedPayments(currentPage.value, filters.value.global.value, event.sortField, event.sortOrder);
};

onMounted(() => {
    fetchContainerApprovedPayments();
});

const debouncedFetchContainerPayments = debounce((searchValue) => {
    fetchContainerApprovedPayments(1, searchValue);
}, 1000);

watch(() => filters.value.global.value, (newValue) => {
    if (newValue !== null) {
        debouncedFetchContainerPayments(newValue);
    }
});

watch(() => filters.value.created_at.value, (newValue) => {
    fetchContainerApprovedPayments(1, filters.value.global.value);
});

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
            router.post(route("container-payment.revoke-approval"), {
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
            await fetchContainerApprovedPayments();
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
    form.post(route("container-payment.complete-payment"), {
        onSuccess: () => {
            closeShowPaidModal();
            form.reset();
            fetchContainerApprovedPayments();
            push.success("Container Payment Completed Successfully!");
        },
        onError: () => {
            push.error("Something went to wrong!");
        },
        preserveScroll: true,
        preserveState: true
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
    <AppLayout title="Container Approved Payments">
        <template #header>Container Approved Payments</template>

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
                                Container Approved Payments
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
                    <template #empty> No Container Approved Payments found. </template>
                    <template #loading> Loading Container Approved Payments data. Please wait.</template>
                    <Column v-if="usePage().props.user.permissions.includes('payment-container.approve')" headerStyle="width: 3rem" selectionMode="multiple"></Column>
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
                    <template #footer> In total there are {{ containerPayments ? totalRecords : 0 }} Container Approved Payments.</template>
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
            <div>
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
