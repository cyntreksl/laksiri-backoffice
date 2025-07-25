<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import { onMounted, ref, watch } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import Card from "primevue/card";
import DataTable from "primevue/datatable";
import InputIcon from "primevue/inputicon";
import InputText from "primevue/inputtext";
import Column from "primevue/column";
import Button from "primevue/button";
import IconField from "primevue/iconfield";
import { useConfirm } from "primevue/useconfirm";
import { FilterMatchMode } from "@primevue/core/api";
import axios from "axios";
import { debounce } from "lodash";
import { push } from "notivue";
import moment from "moment";

const confirm = useConfirm();
const baseUrl = ref("/get-container-payment-request-list");
const loading = ref(true);
const containerPayments = ref([]);
const totalRecords = ref(0);
const perPage = ref(10);
const currentPage = ref(1);
const selectedContainerPayments = ref([]);
const visible = ref(false);
const selectedContainerPayment = ref();

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    created_at: { value: null, matchMode: FilterMatchMode.EQUALS },
});

const fetchContainerPaymentRequests = async (
    page = 1,
    search = "",
    sortField = "id",
    sortOrder = 0
) => {
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
                    ? moment(filters.value.created_at.value).format(
                          "YYYY-MM-DD"
                      )
                    : null,
            },
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
    fetchContainerPaymentRequests(currentPage.value);
};

const onSort = (event) => {
    fetchContainerPaymentRequests(
        currentPage.value,
        filters.value.global.value,
        event.sortField,
        event.sortOrder
    );
};

onMounted(() => {
    fetchContainerPaymentRequests();
});

const debouncedFetchContainerPayments = debounce((searchValue) => {
    fetchContainerPaymentRequests(1, searchValue);
}, 1000);

watch(
    () => filters.value.global.value,
    (newValue) => {
        if (newValue !== null) {
            debouncedFetchContainerPayments(newValue);
        }
    }
);

watch(
    () => filters.value.created_at.value,
    (newValue) => {
        fetchContainerPaymentRequests(1, filters.value.global.value);
    }
);

const approveSinglePayment = (paymentId, chargeType) => {
    confirm.require({
        message: `Are you sure you want to approve ${chargeType.replace(
            "_",
            " "
        )}?`,
        header: `Approve ${chargeType.replace("_", " ")}?`,
        icon: "pi pi-info-circle",
        rejectLabel: "Cancel",
        rejectProps: {
            label: "Cancel",
            severity: "secondary",
            outlined: true,
        },
        acceptProps: {
            label: `Approve ${chargeType.replace("_", " ")}`,
            severity: "success",
        },
        accept: async () => {
            router.post(route("container-payment.approve-single"), {
                container_payment_id: paymentId,
                charge_type: chargeType,
            }, {
                preserveScroll: true,
                onSuccess: () => {
                    push.success(
                        `${chargeType.replace("_", " ")} approved successfully!`
                    );
                    fetchContainerPaymentRequests(currentPage.value);
                },
                onError: () => {
                    push.error("Something went wrong!");
                },
            });
        },
        reject: () => {},
    });
};

const clearFilter = () => {
    filters.value = {
        global: { value: null, matchMode: FilterMatchMode.CONTAINS },
        created_at: { value: null, matchMode: FilterMatchMode.EQUALS },
    };
    fetchContainerPaymentRequests(currentPage.value);
};
</script>
<template>
    <AppLayout title="Payments Requests">
        <template #header>Payments Requests</template>

        <Breadcrumb />

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
                    @sort="onSort"
                >
                    <template #header>
                        <div
                            class="flex flex-col sm:flex-row justify-between items-center mb-2"
                        >
                            <div class="text-lg font-medium">
                                Container Payment Requests
                            </div>
                        </div>
                        <div
                            class="flex flex-col sm:flex-row justify-between gap-4"
                        >
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
                    <template #empty>
                        No Container Payment Request found.
                    </template>
                    <template #loading>
                        Loading Container Payment Request data. Please
                        wait.</template
                    >
                    <!--                    <Column v-if="usePage().props.user.permissions.includes('payment-container.approve')" headerStyle="width: 3rem" selectionMode="multiple"></Column>-->
                    <Column
                        field="containerReference"
                        header="Container Reference"
                        sortable
                    ></Column>
                    <Column
                        field="do_charge"
                        header="DO Charge"
                        header-class="!text-right"
                    >
                        <template #body="slotProps">
                            <p class="font-semibold text-emerald-500">
                                {{ slotProps.data.do_charge.toFixed(2) }}
                            </p>
                            <p v-if="slotProps.data.do_charge_requested_at">
                                {{ slotProps.data.do_charge_requested_at }}
                            </p>
                            <p v-if="slotProps.data.do_charge_requested_by">
                                {{ slotProps.data.do_charge_requested_by }}
                            </p>
                            <Button
                                v-if="
                                    usePage().props.user.permissions.includes(
                                        'payment-container.approve'
                                    )
                                "
                                :disabled="
                                    slotProps.data.do_charge === 0 ||
                                    slotProps.data.do_charge_finance_approved
                                "
                                class="my-2"
                                icon="ti ti-cash"
                                label="Approve"
                                outlined
                                severity="info"
                                size="small"
                                type="button"
                                @click="
                                    approveSinglePayment(
                                        slotProps.data.id,
                                        'do_charge'
                                    )
                                "
                            />
                        </template>
                    </Column>
                    <Column
                        field="demurrage_charge"
                        header="Demurrage Charge"
                        header-class="!text-right"
                    >
                        <template #body="slotProps">
                            <p class="font-semibold text-emerald-500">
                                {{ slotProps.data.demurrage_charge.toFixed(2) }}
                            </p>
                            <p
                                v-if="
                                    slotProps.data.demurrage_charge_requested_at
                                "
                            >
                                {{
                                    slotProps.data.demurrage_charge_requested_at
                                }}
                            </p>
                            <p
                                v-if="
                                    slotProps.data.demurrage_charge_requested_by
                                "
                            >
                                {{
                                    slotProps.data.demurrage_charge_requested_by
                                }}
                            </p>
                            <Button
                                v-if="
                                    usePage().props.user.permissions.includes(
                                        'payment-container.approve'
                                    )
                                "
                                :disabled="
                                    slotProps.data.demurrage_charge === 0 ||
                                    slotProps.data
                                        .demurrage_charge_finance_approved
                                "
                                class="my-2"
                                icon="ti ti-cash"
                                label="Approve"
                                outlined
                                severity="info"
                                size="small"
                                type="button"
                                @click="
                                    approveSinglePayment(
                                        slotProps.data.id,
                                        'demurrage_charge'
                                    )
                                "
                            />
                        </template>
                    </Column>
                    <Column
                        field="assessment_charge"
                        header="Assessment Charge"
                        header-class="!text-right"
                    >
                        <template #body="slotProps">
                            <p class="font-semibold text-emerald-500">
                                {{
                                    slotProps.data.assessment_charge.toFixed(2)
                                }}
                            </p>
                            <p
                                v-if="
                                    slotProps.data
                                        .assessment_charge_requested_at
                                "
                            >
                                {{
                                    slotProps.data
                                        .assessment_charge_requested_at
                                }}
                            </p>
                            <p
                                v-if="
                                    slotProps.data
                                        .assessment_charge_requested_by
                                "
                            >
                                {{
                                    slotProps.data
                                        .assessment_charge_requested_by
                                }}
                            </p>
                            <Button
                                v-if="
                                    usePage().props.user.permissions.includes(
                                        'payment-container.approve'
                                    )
                                "
                                :disabled="
                                    slotProps.data.assessment_charge === 0 ||
                                    slotProps.data
                                        .assessment_charge_finance_approved
                                "
                                class="my-2"
                                icon="ti ti-cash"
                                label="Approve"
                                outlined
                                severity="info"
                                size="small"
                                type="button"
                                @click="
                                    approveSinglePayment(
                                        slotProps.data.id,
                                        'assessment_charge'
                                    )
                                "
                            />
                        </template>
                    </Column>
                    <Column
                        field="slpa_charge"
                        header="SLPA Charge"
                        header-class="!text-right"
                    >
                        <template #body="slotProps">
                            <p class="font-semibold text-emerald-500">
                                {{ slotProps.data.slpa_charge.toFixed(2) }}
                            </p>
                            <p v-if="slotProps.data.slpa_charge_requested_at">
                                {{ slotProps.data.slpa_charge_requested_at }}
                            </p>
                            <p v-if="slotProps.data.slpa_charge_requested_by">
                                {{ slotProps.data.slpa_charge_requested_by }}
                            </p>
                            <Button
                                v-if="
                                    usePage().props.user.permissions.includes(
                                        'payment-container.approve'
                                    )
                                "
                                :disabled="
                                    slotProps.data.slpa_charge === 0 ||
                                    slotProps.data
                                        .slpa_charge_finance_approved
                                "
                                class="my-2"
                                icon="ti ti-cash"
                                label="Approve"
                                outlined
                                severity="info"
                                size="small"
                                type="button"
                                @click="
                                    approveSinglePayment(
                                        slotProps.data.id,
                                        'slpa_charge'
                                    )
                                "
                            />
                        </template>
                    </Column>
                    <Column
                        field="refund_charge"
                        header="Refund Charge"
                        header-class="!text-right"
                    >
                        <template #body="slotProps">
                            <p class="font-semibold text-emerald-500">
                                {{ slotProps.data.refund_charge.toFixed(2) }}
                            </p>
                            <p v-if="slotProps.data.refund_charge_requested_at">
                                {{ slotProps.data.refund_charge_requested_at }}
                            </p>
                            <p v-if="slotProps.data.refund_charge_requested_by">
                                {{ slotProps.data.refund_charge_requested_by }}
                            </p>
                            <Button
                                v-if="
                                    usePage().props.user.permissions.includes(
                                        'payment-container.approve'
                                    )
                                "
                                :disabled="
                                    slotProps.data.refund_charge === 0 ||
                                    slotProps.data
                                        .refund_charge_finance_approved
                                "
                                class="my-2"
                                icon="ti ti-cash"
                                label="Approve"
                                outlined
                                severity="info"
                                size="small"
                                type="button"
                                @click="
                                    approveSinglePayment(
                                        slotProps.data.id,
                                        'refund_charge'
                                    )
                                "
                            />
                        </template>
                    </Column>
                    <Column
                        field="clearance_charge"
                        header="Clearance Charge"
                        header-class="!text-right"
                    >
                        <template #body="slotProps">
                            <p class="font-semibold text-emerald-500">
                                {{ slotProps.data.clearance_charge.toFixed(2) }}
                            </p>
                            <p
                                v-if="
                                    slotProps.data.clearance_charge_requested_at
                                "
                            >
                                {{
                                    slotProps.data.clearance_charge_requested_at
                                }}
                            </p>
                            <p
                                v-if="
                                    slotProps.data.clearance_charge_requested_by
                                "
                            >
                                {{
                                    slotProps.data.clearance_charge_requested_by
                                }}
                            </p>
                            <Button
                                v-if="
                                    usePage().props.user.permissions.includes(
                                        'payment-container.approve'
                                    )
                                "
                                :disabled="
                                    slotProps.data.clearance_charge === 0 ||
                                    slotProps.data
                                        .clearance_charge_finance_approved
                                "
                                class="my-2"
                                icon="ti ti-cash"
                                label="Approve"
                                outlined
                                severity="info"
                                size="small"
                                type="button"
                                @click="
                                    approveSinglePayment(
                                        slotProps.data.id,
                                        'clearance_charge'
                                    )
                                "
                            />
                        </template>
                    </Column>
                    <Column
                        field="total"
                        header="Total"
                        header-class="!text-right"
                    >
                        <template #body="slotProps">
                            <div class="text-right">
                                {{ slotProps.data.total.toFixed(2) }}
                            </div>
                        </template>
                    </Column>
                    <template #footer>
                        In total there are
                        {{ containerPayments ? totalRecords : 0 }} container
                        payments requests.</template
                    >
                </DataTable>
            </template>
        </Card>
    </AppLayout>
</template>
