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
import Dialog from "primevue/dialog";

const baseUrl = ref("/container-payment-completed-list");
const loading = ref(true);
const containerPayments = ref([]);
const totalRecords = ref(0);
const perPage = ref(10);
const currentPage = ref(1);
const visible = ref(false);
const selectedContainerPayment = ref();

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

const displayInfo = (paymentRequest) => {
    visible.value = true;
    selectedContainerPayment.value = paymentRequest;
}
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
                            <p class="font-semibold text-emerald-500">
                                {{ slotProps.data.do_charge.toFixed(2) }}
                            </p>
                            <p v-if="slotProps.data.do_charge_requested_at">Requested: {{slotProps.data.do_charge_requested_at}}</p>
                            <p v-if="slotProps.data.do_charge_approved_at">Approved: {{slotProps.data.do_charge_approved_at}}</p>
                            <p v-if="slotProps.data.do_charge_requested_by">Requested: {{slotProps.data.do_charge_requested_by}}</p>
                            <p v-if="slotProps.data.do_charge_approved_by">Approved: {{slotProps.data.do_charge_approved_by}}</p>
                            <div class="flex items-center">
                                <div v-if="slotProps.data.do_charge_finance_approved" class="text-green-500">
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
                    <Column field="demurrage_charge" header="Demurrage Charge" header-class="!text-right">
                        <template #body="slotProps">
                            <p class="font-semibold text-emerald-500">
                                {{ slotProps.data.demurrage_charge.toFixed(2) }}
                            </p>
                            <p v-if="slotProps.data.demurrage_charge_requested_at">Requested: {{slotProps.data.demurrage_charge_requested_at}}</p>
                            <p v-if="slotProps.data.demurrage_charge_approved_at">Approved: {{slotProps.data.demurrage_charge_approved_at}}</p>
                            <p v-if="slotProps.data.demurrage_charge_requested_by">Requested: {{slotProps.data.demurrage_charge_requested_by}}</p>
                            <p v-if="slotProps.data.demurrage_charge_approved_by">Approved: {{slotProps.data.demurrage_charge_approved_by}}</p>
                            <div class="flex items-center">
                                <div v-if="slotProps.data.demurrage_charge_finance_approved" class="text-green-500">
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
                    <Column field="assessment_charge" header="Assessment Charge" header-class="!text-right">
                        <template #body="slotProps">
                            <p class="font-semibold text-emerald-500">
                                {{ slotProps.data.assessment_charge.toFixed(2) }}
                            </p>
                            <p v-if="slotProps.data.assessment_charge_requested_at">Requested: {{slotProps.data.assessment_charge_requested_at}}</p>
                            <p v-if="slotProps.data.assessment_charge_approved_at">Approved: {{slotProps.data.assessment_charge_approved_at}}</p>
                            <p v-if="slotProps.data.assessment_charge_requested_by">Requested: {{slotProps.data.assessment_charge_requested_by}}</p>
                            <p v-if="slotProps.data.assessment_charge_approved_by">Approved: {{slotProps.data.assessment_charge_approved_by}}</p>
                            <div class="flex items-center">
                                <div v-if="slotProps.data.assessment_charge_finance_approved" class="text-green-500">
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
                    <Column field="slpa_charge" header="SLPA Charge" header-class="!text-right">
                        <template #body="slotProps">
                            <p class="font-semibold text-emerald-500">
                                {{ slotProps.data.slpa_charge.toFixed(2) }}
                            </p>
                            <p v-if="slotProps.data.slpa_charge_requested_at">Requested: {{slotProps.data.slpa_charge_requested_at}}</p>
                            <p v-if="slotProps.data.slpa_charge_approved_at">Approved: {{slotProps.data.slpa_charge_approved_at}}</p>
                            <p v-if="slotProps.data.slpa_charge_requested_by">Requested: {{slotProps.data.slpa_charge_requested_by}}</p>
                            <p v-if="slotProps.data.slpa_charge_approved_by">Approved: {{slotProps.data.slpa_charge_approved_by}}</p>
                            <div class="flex items-center">
                                <div v-if="slotProps.data.slpa_charge_finance_approved" class="text-green-500">
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
                    <Column field="refund_charge" header="Refund Charge" header-class="!text-right">
                        <template #body="slotProps">
                            <p class="font-semibold text-emerald-500">
                                {{ slotProps.data.refund_charge.toFixed(2) }}
                            </p>
                            <p v-if="slotProps.data.refund_charge_requested_at">Requested: {{slotProps.data.refund_charge_requested_at}}</p>
                            <p v-if="slotProps.data.refund_charge_approved_at">Approved: {{slotProps.data.refund_charge_approved_at}}</p>
                            <p v-if="slotProps.data.refund_charge_requested_by">Requested: {{slotProps.data.refund_charge_requested_by}}</p>
                            <p v-if="slotProps.data.refund_charge_approved_by">Approved: {{slotProps.data.refund_charge_approved_by}}</p>
                            <div class="flex items-center">
                                <div v-if="slotProps.data.refund_charge_finance_approved" class="text-green-500">
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
                    <Column field="clearance_charge" header="Clearance Charge" header-class="!text-right">
                        <template #body="slotProps">
                            <p class="font-semibold text-emerald-500">
                                {{ slotProps.data.clearance_charge.toFixed(2) }}
                            </p>
                            <p v-if="slotProps.data.clearance_charge_requested_at">Requested: {{slotProps.data.clearance_charge_requested_at}}</p>
                            <p v-if="slotProps.data.clearance_charge_approved_at">Approved: {{slotProps.data.clearance_charge_approved_at}}</p>
                            <p v-if="slotProps.data.clearance_charge_requested_by">Requested: {{slotProps.data.clearance_charge_requested_by}}</p>
                            <p v-if="slotProps.data.clearance_charge_approved_by">Approved: {{slotProps.data.clearance_charge_approved_by}}</p>
                            <div class="flex items-center">
                                <div v-if="slotProps.data.clearance_charge_finance_approved" class="text-green-500">
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
                    <Column field="total" header="Total" header-class="!text-right">
                        <template #body="slotProps">
                            <div class="text-right">
                                {{ slotProps.data.total.toFixed(2) }}
                            </div>
                        </template>
                    </Column>
<!--                    <Column header="">-->
<!--                        <template #body="slotProps">-->
<!--                            <Button aria-label="Info" icon="pi pi-eye" rounded severity="info" size="small" type="button" @click="displayInfo(slotProps.data)" />-->
<!--                        </template>-->
<!--                    </Column>-->
                    <template #footer> In total there are {{ containerPayments ? totalRecords : 0 }} Completed Container Payments.</template>
                </DataTable>
            </template>
        </Card>
    </AppLayout>

    <Dialog v-model:visible="visible" :style="{ width: '35rem' }" header="Summery" modal>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div v-if="selectedContainerPayment?.is_finance_approved">
                <label class="block text-sm text-gray-500">Approved Time</label>
                <div class="text-gray-800 font-medium">{{ selectedContainerPayment?.finance_approved_date }}</div>
            </div>

            <div v-if="selectedContainerPayment?.is_finance_approved">
                <label class="block text-sm text-gray-500">Approved By</label>
                <div class="text-gray-800 font-medium">{{ selectedContainerPayment?.finance_approved_by }}</div>
            </div>

            <div v-if="selectedContainerPayment?.is_refund_collected">
                <label class="block text-sm text-gray-500">Collected Time</label>
                <div class="text-gray-800 font-medium">{{ selectedContainerPayment?.refund_collected_date }}</div>
            </div>
        </div>
        <div class="flex justify-end gap-2">
            <Button label="Close" severity="secondary" type="button" @click="visible = false"></Button>
        </div>
    </Dialog>
</template>
