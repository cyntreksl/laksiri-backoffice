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
import ToggleSwitch from 'primevue/toggleswitch';
import InputLabel from "@/Components/InputLabel.vue";
import Panel from "primevue/panel";
import DatePicker from "primevue/datepicker";
import FloatLabel from "primevue/floatlabel";
import moment from "moment";

const confirm = useConfirm();
const baseUrl = ref("container-payment-list");
const loading = ref(true);
const containerPayments = ref([]);
const totalRecords = ref(0);
const perPage = ref(10);
const currentPage = ref(1);
const selectedContainerPayment = ref(null);
const selectedContainerPaymentId = ref(null);
const isDialogVisible = ref(false);
const showEditContainerPaymentDialog = ref(false);
const checked = ref(false);
const showEditNewContainerPaymentDialog = ref(false);
const fromDate = ref(moment(new Date()).subtract(24, "months").toISOString().split("T")[0]);

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
});

const form = useForm({
    container_id: "",
    container_reference: "",
    do_charge: 0,
    demurrage_charge: 0,
    assessment_charge: 0,
    slpa_charge: 0,
    refund_charge: 0,
    clearance_charge: 0
});
const containerReference = ref("");
const confirmViewEditContainerPayment = (containerPayment) => {
    form.container_id= containerPayment.data.container_id;
    form.do_charge= containerPayment.data.do_charge;
    form.demurrage_charge= containerPayment.data.demurrage_charge;
    form.assessment_charge= containerPayment.data.assessment_charge;
    form.slpa_charge= containerPayment.data.slpa_charge;
    form.refund_charge= containerPayment.data.refund_charge;
    form.clearance_charge= containerPayment.data.clearance_charge;
    containerReference.value = containerPayment.data.containerReference;
    isDialogVisible.value = true;
};

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

const closeEditContainerPaymentModal = () => {
    form.reset();
    containerReference.value = "";
    showEditContainerPaymentDialog.value = false;
    isDialogVisible.value = false;
}

const onDialogShow = () => {
    document.body.classList.add('p-overflow-hidden');
};

const onDialogHide = () => {
    form.reset();
    form.clearErrors();
    document.body.classList.remove('p-overflow-hidden');
};

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

const handleEditContainerPayment = async () => {
    form.post(route("container-payment.store"), {
        onSuccess: (page) => {
            form.reset();
            closeEditContainerPaymentModal();
            fetchContainerPayments();
            push.success("Container Payment Updated Successfully!");
        },
        onError: () => console.log("error"),
        preserveScroll: true,
        preserveState: true,
    });
}

const confirmDeleteContainerPayment = (containerPayment) => {
    selectedContainerPaymentId.value = containerPayment.data.id;
    confirm.require({
        message: 'Are you sure you want to delete Container Payment?',
        header: 'Delete Container Payment?',
        icon: 'pi pi-info-circle',
        rejectLabel: 'Cancel',
        rejectProps: {
            label: 'Cancel',
            severity: 'secondary',
            outlined: true
        },
        acceptProps: {
            label: 'Delete',
            severity: 'warn'
        },
        accept: () => {
            router.delete(route("container-payment.destroy", selectedContainerPaymentId.value), {
                preserveScroll: true,
                onSuccess: () => {
                    push.success("Container Payment Deleted Successfully!");
                    const currentRoute = route().current();
                    router.visit(route(currentRoute, route().params));
                },
                onError: () => {
                    push.error("Something went to wrong!");
                },
            });
            selectedContainerPaymentId.value = null;
        },
        reject: () => {
            selectedContainerPaymentId.value = null;
        }
    });
};
</script>
<template>
    <AppLayout title="Container Payments">
        <template #header>Container Payments</template>
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
                    v-model:selection="selectedContainerPayment"
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
                                Container Payments
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
                    <Column field="is_finance_approved" header="Finance Approval" >
                        <template #body="{ data }">
                            <i :class="{ 'pi-check-circle text-green-500': data.is_finance_approved, 'pi-times-circle text-red-400': !data.is_finance_approved }" class="pi"></i>
                        </template>
                    </Column>
                    <Column field="" header="Actions" style="width: 10%">
                        <template #body="{ data }">
                            <Button
                                icon="pi pi-pencil"
                                outlined
                                rounded
                                size="small"
                                class="p-1 text-xs h-3 w-3 mr-1"
                                @click="confirmViewEditContainerPayment({ data })"
                                :disabled="!usePage().props.user.permissions.includes('payment-container.edit')"
                            />
                            <Button
                                icon="pi pi-trash"
                                outlined
                                rounded
                                severity="danger"
                                size="small"
                                @click="confirmDeleteContainerPayment({ data })"
                                :disabled="!usePage().props.user.permissions.includes('payment-container.delete')"
                            />

                        </template>
                    </Column>
                    <template #footer> In total there are {{ containerPayments ? totalRecords : 0 }} Container Payments.</template>
                </DataTable>
            </template>
        </Card>

        <Dialog
            v-model:visible="isDialogVisible"
            modal
            header="Edit Container Payment"
            :style="{ width: '50rem' }"
            :block-scroll="true"
            @hide="onDialogHide"
            @show="onDialogShow"
        >
            <div class="space-y-4 grid grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <InputLabel value="Container Reference" />
                    <InputText
                        v-model="containerReference"
                        class="w-full"
                        required
                        type="text"
                        disabled
                    />
                </div>
                <div>
                    <InputLabel value="DO Charge" />
                    <InputText
                        v-model="form.do_charge"
                        class="w-full"
                        placeholder="Enter DO Charge"
                        required
                        type="number"
                        min="0.00"
                        step="0.01"
                    />
                    <InputError :message="form.errors.do_charge" />
                </div>

                <div>
                    <InputLabel value="Demurrage Charge" />
                    <InputText
                        v-model="form.demurrage_charge"
                        class="w-full"
                        placeholder="Enter Demurrage Charge"
                        required
                        type="number"
                        min="0.00"
                        step="0.01"
                    />
                    <InputError :message="form.errors.demurrage_charge" />
                </div>

                <div>
                    <InputLabel value="Assessment Charge" />
                    <InputText
                        v-model="form.assessment_charge"
                        class="w-full"
                        placeholder="Enter Assessment Charge"
                        required
                        type="number"
                        min="0.00"
                        step="0.01"
                    />
                    <InputError :message="form.errors.assessment_charge" />
                </div>

                <div>
                    <InputLabel value="SLPA Charge" />
                    <InputText
                        v-model="form.slpa_charge"
                        class="w-full"
                        placeholder="Enter SLPA Charge"
                        required
                        type="number"
                        min="0.00"
                        step="0.01"
                    />
                    <InputError :message="form.errors.slpa_charge" />
                </div>

                <div>
                    <InputLabel value="Refund Charge" />
                    <InputText
                        v-model="form.refund_charge"
                        class="w-full"
                        placeholder="Enter Refund Charge"
                        required
                        type="number"
                        min="0.00"
                        step="0.01"
                    />
                    <InputError :message="form.errors.refund_charge" />
                </div>

                <div>
                    <InputLabel value="Clearance Charge" />
                    <InputText
                        v-model="form.clearance_charge"
                        class="w-full"
                        placeholder="Enter Clearance Charge"
                        required
                        type="number"
                        min="0.00"
                        step="0.01"
                    />
                    <InputError :message="form.errors.clearance_charge" />
                </div>

            </div>

            <!-- Dialog Footer -->
            <template #footer>
                <div class="flex flex-wrap justify-end gap-2">
                    <Button
                        label="Cancel"
                        class="p-button-text"
                        @click="closeEditContainerPaymentModal"
                    />
                    <Button
                        label="Update Container Payment"
                        class="p-button-primary"
                        icon="pi pi-check"
                        @click.prevent="handleEditContainerPayment()"
                    />
                </div>
            </template>
        </Dialog>
    </AppLayout>
</template>
