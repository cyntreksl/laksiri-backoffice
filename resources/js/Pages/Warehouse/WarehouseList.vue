<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import {computed, onMounted, ref, watch} from "vue";
import {router, usePage} from "@inertiajs/vue3";
import SimpleOverviewWidget from "@/Components/Widgets/SimpleOverviewWidget.vue";
import Card from "primevue/card";
import FloatLabel from "primevue/floatlabel";
import DataTable from "primevue/datatable";
import DatePicker from "primevue/datepicker";
import ContextMenu from "primevue/contextmenu";
import InputIcon from "primevue/inputicon";
import InputText from "primevue/inputtext";
import Column from "primevue/column";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import Tag from "primevue/tag";
import Panel from "primevue/panel";
import Button from "primevue/button";
import IconField from "primevue/iconfield";
import Select from "primevue/select";
import Checkbox from "primevue/checkbox";
import HBLDetailModal from "@/Pages/Common/HBLDetailModal.vue";
import {useConfirm} from "primevue/useconfirm";
import moment from "moment";
import {FilterMatchMode} from "@primevue/core/api";
import axios from "axios";
import {debounce} from "lodash";
import {push} from "notivue";
import AssignZoneModal from "@/Pages/Warehouse/Partials/AssignZoneModal.vue";

const props = defineProps({
    drivers: {
        type: Object,
        default: () => {
        },
    },
    officers: {
        type: Object,
        default: () => {
        },
    },
    paymentStatus: {
        type: Object,
        default: () => {
        },
    },
    warehouseZones: {
        type: Object,
        default: () => {
        },
    },
    warehouses: {
        type: Object,
        default: () => {
        },
    },
});

const baseUrl = ref("/get-warehouse-list");
const loading = ref(true);
const hbls = ref([]);
const totalRecords = ref(0);
const perPage = ref(100);
const currentPage = ref(1);
const showConfirmViewHBLModal = ref(false);
const cm = ref();
const selectedHBL = ref(null);
const selectedHBLs = ref([]);
const selectedHBLID = ref(null);
const confirm = useConfirm();
const warehouses = ref(['COLOMBO', 'NINTAVUR',]);
const hblTypes = ref(['UPB', 'Door to Door', 'Gift']);
const cargoTypes = ref(['Sea Cargo', 'Air Cargo']);
const fromDate = ref(moment(new Date()).subtract(1, "month").toISOString().split("T")[0]);
const toDate = ref(moment(new Date()).toISOString().split("T")[0]);
const totalRecord = ref(0);
const totalGrandAmount = ref(0);
const totalPaidAmount = ref(0);
const totalWeight = ref(0);
const totalVolume = ref(0);
const totalQuantity = ref(0);
const showConfirmAssignZoneModal = ref(false);

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    warehouse: { value: null, matchMode: FilterMatchMode.EQUALS },
    hbl_type: { value: null, matchMode: FilterMatchMode.EQUALS },
    cargo_type: { value: null, matchMode: FilterMatchMode.EQUALS },
    is_hold: { value: null, matchMode: FilterMatchMode.EQUALS },
    user: {value: null, matchMode: FilterMatchMode.EQUALS},
    payments: {value: null, matchMode: FilterMatchMode.EQUALS},
});

const menuModel = ref([
    {
        label: "View",
        icon: "pi pi-fw pi-search",
        command: () => confirmViewHBL(selectedHBL),
        disabled: !usePage().props.user.permissions.includes('warehouse.show'),
    },
    {
        label: computed(() => (selectedHBL.value?.is_hold ? 'Release' : 'Hold')),
        icon: computed(() => (selectedHBL.value?.is_hold ? 'pi pi-fw pi-play-circle' : 'pi pi-fw pi-pause-circle')) ,
        command: () => confirmHBLHold(selectedHBL),
        disabled: !usePage().props.user.permissions.includes('warehouse.hold and release'),
    },
    {
        label: "Download Barcode",
        icon: "pi pi-fw pi-barcode",
        url: () => route("back-office.warehouses.download.barcode", selectedHBL.value.id),
        disabled: !usePage().props.user.permissions.includes('warehouse.download barcode'),
    },
]);

const fetchCashSettlements = async (page = 1, search = "", sortField = 'created_at', sortOrder = 0) => {
    loading.value = true;
    try {
        const response = await axios.get(baseUrl.value, {
            params: {
                page,
                per_page: perPage.value,
                search,
                warehouse: filters.value.warehouse.value || "",
                deliveryType: filters.value.hbl_type.value || "",
                cargoMode: filters.value.cargo_type.value || "",
                isHold: filters.value.is_hold.value || false,
                sort_field: sortField,
                sort_order: sortOrder === 1 ? "asc" : "desc",
                createdBy: filters.value.user.value || "",
                paymentStatus: filters.value.payments.value || [],
                fromDate: moment(fromDate.value).format("YYYY-MM-DD"),
                toDate: moment(toDate.value).format("YYYY-MM-DD"),
            }
        });
        hbls.value = response.data.data;
        totalRecords.value = response.data.meta.total;
        currentPage.value = response.data.meta.current_page;
    } catch (error) {
        console.error("Error fetching HBLs:", error);
    } finally {
        loading.value = false;
    }
};

const getWarehouseSummary = async () => {
    try {
        const response = await fetch("/warehouse-summery", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document
                    .querySelector('meta[name="csrf-token"]')
                    .getAttribute("content"),
            },
            body: JSON.stringify({
                warehouse: filters.value.warehouse.value,
                deliveryType: filters.value.hbl_type.value,
                cargoMode: filters.value.cargo_type.value,
                isHold: filters.value.is_hold.value,
                officers: filters.value.user.value,
                paymentStatus: filters.value.payments.value,
                fromDate: moment(fromDate.value).format("YYYY-MM-DD"),
                toDate: moment(toDate.value).format("YYYY-MM-DD"),
            }),
        });

        if (!response.ok) {
            throw new Error("Network response was not ok.");
        }

        const data = await response.json();
        totalRecord.value = data.totalRecords;
        totalGrandAmount.value = data.sumAmount;
        totalPaidAmount.value = data.sumPaidAmount;
        totalWeight.value = data.sumWeight;
        totalVolume.value = data.sumVolume;
        totalQuantity.value = data.sumQuantity;
    } catch (error) {
        console.error("Error:", error);
    }
};

const debouncedFetchHBLs = debounce((searchValue) => {
    fetchCashSettlements(1, searchValue);
}, 1000);

watch(() => filters.value.global.value, (newValue) => {
    if (newValue !== null) {
        debouncedFetchHBLs(newValue);
    }
});

watch(() => filters.value.warehouse.value, (newValue) => {
    fetchCashSettlements(1, filters.value.global.value);
    getWarehouseSummary();
});

watch(() => filters.value.hbl_type.value, (newValue) => {
    fetchCashSettlements(1, filters.value.global.value);
    getWarehouseSummary();
});

watch(() => filters.value.cargo_type.value, (newValue) => {
    fetchCashSettlements(1, filters.value.global.value);
    getWarehouseSummary();
});

watch(() => filters.value.is_hold.value, (newValue) => {
    fetchCashSettlements(1, filters.value.global.value);
    getWarehouseSummary();
});

watch(() => filters.value.user.value, (newValue) => {
    fetchCashSettlements(1, filters.value.global.value);
    getWarehouseSummary();
});

watch(() => filters.value.payments.value, (newValue) => {
    fetchCashSettlements(1, filters.value.global.value);
    getWarehouseSummary();
});

watch(() => fromDate.value, (newValue) => {
    fetchCashSettlements(1, filters.value.global.value);
    getWarehouseSummary();
});

watch(() => toDate.value, (newValue) => {
    fetchCashSettlements(1, filters.value.global.value);
    getWarehouseSummary();
});

const onPageChange = (event) => {
    perPage.value = event.rows;
    currentPage.value = event.page + 1;
    fetchCashSettlements(currentPage.value);
};

const onSort = (event) => {
    fetchCashSettlements(currentPage.value, filters.value.global.value, event.sortField, event.sortOrder);
};

onMounted(() => {
    fetchCashSettlements();
    getWarehouseSummary();
});

const resolveHBLType = (hbl) => {
    switch (hbl.hbl_type) {
        case 'UPB':
            return 'secondary';
        case 'Gift':
            return 'warn';
        case 'Door to Door':
            return 'info';
        default:
            return null;
    }
};

const resolveCargoType = (hbl) => {
    switch (hbl.cargo_type) {
        case 'Sea Cargo':
            return {
                icon: "ti ti-sailboat",
                color: "success",
            };
        case 'Air Cargo':
            return {
                icon: "ti ti-plane-tilt",
                color: "info",
            };
        default:
            return null;
    }
};

const resolveWarehouse = (hbl) => {
    switch (hbl.warehouse.toUpperCase()) {
        case 'COLOMBO':
            return 'info';
        case 'NINTAVUR':
            return 'danger';
        default:
            return null;
    }
};

const resolvePaymentStatus = (hbl) => {
    switch (hbl.status) {
        case 'Partial Paid':
            return {
                icon: "pi pi-question",
                color: "warn",
            };
        case 'Not Paid':
            return {
                icon: "pi pi-times",
                color: "danger",
            };
        case 'Full Paid':
            return {
                icon: "pi pi-check",
                color: "success",
            };
        default:
            return {
                icon: "pi pi-exclamation-triangle",
                color: "secondary",
            };
    }
};

const onRowContextMenu = (event) => {
    cm.value.show(event.originalEvent);
};

const confirmViewHBL = (hbl) => {
    selectedHBLID.value = hbl.value.id;
    showConfirmViewHBLModal.value = true;
};

const closeModal = () => {
    showConfirmViewHBLModal.value = false;
    selectedHBLID.value = null;
};

const clearFilter = () => {
    filters.value = {
        global: { value: null, matchMode: FilterMatchMode.CONTAINS },
        warehouse: { value: null, matchMode: FilterMatchMode.EQUALS },
        hbl_type: { value: null, matchMode: FilterMatchMode.EQUALS },
        cargo_type: { value: null, matchMode: FilterMatchMode.EQUALS },
        is_hold: { value: null, matchMode: FilterMatchMode.EQUALS },
        user: {value: null, matchMode: FilterMatchMode.EQUALS},
        payments: {value: null, matchMode: FilterMatchMode.EQUALS},
    };
    fromDate.value = moment(new Date()).subtract(24, "months").toISOString().split("T")[0];
    toDate.value = moment(new Date()).toISOString().split("T")[0];
    fetchCashSettlements(currentPage.value);
};

const confirmHBLHold = (hbl) => {
    selectedHBLID.value = hbl.value.id;
    confirm.require({
        message: `Would you like to ${hbl.value.is_hold ? 'Release' : 'Hold'} this hbl?`,
        header: `${hbl.value.is_hold ? 'Release' : 'Hold'} HBL?`,
        icon: 'pi pi-info-circle',
        rejectLabel: 'Cancel',
        rejectProps: {
            label: 'Cancel',
            severity: 'secondary',
            outlined: true
        },
        acceptProps: {
            label: `${hbl.value.is_hold ? 'Release' : 'Hold'}`,
            severity: 'warn'
        },
        accept: () => {
            router.put(
                route("hbls.toggle-hold", selectedHBLID.value),
                {},
                {
                    preserveScroll: true,
                    onSuccess: () => {
                        push.success(`Operation Successfully!`);
                        fetchCashSettlements(currentPage.value);
                    },
                    onError: () => {
                        push.error("Something went to wrong!");
                    },
                }
            );
            selectedHBLID.value = null;
        },
        reject: () => {
        }
    });
};

const confirmRevert = () => {
    confirm.require({
        message: `Would you like to revert the selected records to a cash settlement?`,
        header: `Revert to cash settlement?`,
        icon: 'pi pi-info-circle',
        rejectLabel: 'Cancel',
        rejectProps: {
            label: 'Cancel',
            severity: 'secondary',
            outlined: true
        },
        acceptProps: {
            label: 'Revert',
            severity: 'warn'
        },
        accept: () => {
            revertToCashSettlement();
        },
        reject: () => {
        }
    });
};

const revertToCashSettlement = async () => {
    const idList = selectedHBLs.value.map((item) => item.id);

    try {
        const response = await fetch("/revert-to-cash-settlement", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document
                    .querySelector('meta[name="csrf-token"]')
                    .getAttribute("content"),
            },
            body: JSON.stringify({hbl_ids: idList}),
        });

        if (!response.ok) {
            throw new Error("Network response was not ok.");
        } else {
            window.location.reload();
            push.success("Reverted to Cash Settlement successfully!");
        }
    } catch (error) {
        console.error("Error:", error);
    }
};

const confirmAssignZone = (id) => {
    selectedHBLID.value = id;
    showConfirmAssignZoneModal.value = true;
};

const closeAssignZoneModal = () => {
    selectedHBLID.value = null;
    showConfirmAssignZoneModal.value = false;
};

const exportURL = computed(() => {
    const params = new URLSearchParams({
        warehouse: filters.value.warehouse.value,
        deliveryType: filters.value.hbl_type.value,
        cargoMode: filters.value.cargo_type.value,
        isHold: filters.value.is_hold.value,
        officers: filters.value.user.value,
        paymentStatus: filters.value.payments.value,
        fromDate: moment(fromDate.value).format("YYYY-MM-DD"),
        toDate: moment(toDate.value).format("YYYY-MM-DD"),
    }).toString();

    return `/warehouses/export?${params}`;
});
</script>
<template>
    <AppLayout title="Warehouse">
        <template #header>Warehouse</template>

        <Breadcrumb/>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4 mt-4">
            <SimpleOverviewWidget :count="totalRecord" bg-color="white" title="HBL Count"/>

            <SimpleOverviewWidget :count="totalGrandAmount.toFixed(2)" bg-color="white" title="HBL Amount"/>

            <SimpleOverviewWidget :count="totalPaidAmount.toFixed(2)" bg-color="white" title="HBL Paid Amount"/>

            <SimpleOverviewWidget :count="totalWeight.toFixed(2)" bg-color="white" title="Total Weights"/>

            <SimpleOverviewWidget :count="totalVolume.toFixed(3)" bg-color="white" title="Total Volume"/>

            <SimpleOverviewWidget :count="totalQuantity" bg-color="white" title="Total Quantity"/>
        </div>

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

                    <FloatLabel class="w-full" variant="in">
                        <Select v-model="filters.hbl_type.value" :options="hblTypes" :showClear="true" class="w-full" input-id="hbl-type" />
                        <label for="hbl-type">HBL Type</label>
                    </FloatLabel>

                    <FloatLabel class="w-full" variant="in">
                        <Select v-model="filters.warehouse.value" :options="warehouses" :showClear="true" class="w-full" input-id="hbl-type" />
                        <label for="hbl-type">Warehouse</label>
                    </FloatLabel>

                    <FloatLabel class="w-full" variant="in">
                        <Select v-model="filters.user.value" :options="officers" :showClear="true" class="w-full" input-id="user" option-label="name" option-value="id" />
                        <label for="user">Created By</label>
                    </FloatLabel>
                </div>
            </Panel>

            <Card class="my-5">
                <template #content>
                    <ContextMenu ref="cm" :model="menuModel" @hide="selectedHBL = null" />
                    <DataTable
                        v-model:contextMenuSelection="selectedHBL"
                        v-model:filters="filters"
                        v-model:selection="selectedHBLs"
                        :globalFilterFields="['reference', 'hbl', 'hbl_name', 'email', 'address', 'contact_number', 'consignee_name', 'consignee_address', 'consignee_contact', 'cargo_type', 'hbl_type', 'warehouse', 'status', 'hbl_number']"
                        :loading="loading"
                        :rows="perPage"
                        :rowsPerPageOptions="[5, 10, 20, 50, 100]"
                        :totalRecords="totalRecords"
                        :value="hbls"
                        context-menu
                        dataKey="id"
                        filter-display="menu"
                        lazy
                        paginator
                        removable-sort
                        row-hover
                        tableStyle="min-width: 50rem"
                        @page="onPageChange"
                        @rowContextmenu="onRowContextMenu"
                        @sort="onSort">

                        <template #header>
                            <div class="flex flex-col sm:flex-row justify-between items-center mb-2">
                                <div class="text-lg font-medium">
                                    Warehouse
                                </div>
                                <div>
                                    <PrimaryButton
                                        v-if="$page.props.user.permissions.includes('warehouse.revert to cash settlement')"
                                        :disabled="selectedHBLs.length === 0"
                                        class="w-full"
                                        @click="confirmRevert"
                                    >
                                        <i class="ti ti-arrow-back-up mr-1" style="font-size: 1.3rem"></i>
                                        Revert To Cash Settlement
                                    </PrimaryButton>
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

                                    <a :href="exportURL">
                                        <Button
                                            icon="pi pi-external-link"
                                            label="Export"
                                            severity="contrast"
                                            size="small"
                                        />
                                    </a>
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

                        <template #empty> No warehouse found. </template>

                        <template #loading> Loading warehouses data. Please wait.</template>

                        <Column headerStyle="width: 3rem" selectionMode="multiple"></Column>

                        <Column field="reference" header="Reference" hidden sortable></Column>

                        <Column field="hbl_number" header="HBL" sortable>
                            <template #body="slotProps">
                                <span class="font-medium">{{ slotProps.data.hbl_number ?? slotProps.data.hbl }}</span>
                            </template>
                        </Column>

                        <Column field="cargo_type" header="Cargo Type" sortable>
                            <template #body="slotProps">
                                <Tag :icon="resolveCargoType(slotProps.data).icon" :severity="resolveCargoType(slotProps.data).color" :value="slotProps.data.cargo_type" class="text-sm"></Tag>
                            </template>
                            <template #filter="{ filterModel, filterCallback }">
                                <Select v-model="filterModel.value" :options="cargoTypes" :showClear="true" placeholder="Select One" style="min-width: 12rem" />
                            </template>
                        </Column>

                        <Column field="hbl_name" header="Shipper Name"></Column>

                        <Column field="picked_date" header="Picked Date"></Column>

                        <Column field="weight" header="Weight">
                            <template #body="slotProps">
                                <div class="flex items-center">
                                    <i class="ti ti-scale-outline mr-1 text-blue-500" style="font-size: 1rem"></i>
                                    {{ slotProps.data.weight.toFixed(2) }}
                                </div>
                            </template>
                        </Column>

                        <Column field="volume" header="Volume">
                            <template #body="slotProps">
                                <div class="flex items-center">
                                    <i class="ti ti-scale mr-1 text-blue-500" style="font-size: 1rem"></i>
                                    {{ slotProps.data.volume.toFixed(3) }}
                                </div>
                            </template>
                        </Column>

                        <Column field="packages_counts" header="Packages">
                            <template #body="slotProps">
                                <div class="flex items-center">
                                    <i class="ti ti-package mr-1 text-blue-500" style="font-size: 1rem"></i>
                                    {{ slotProps.data.packages_counts }}
                                </div>
                            </template>
                        </Column>

                        <Column field="warehouse" header="Warehouse" hidden sortable>
                            <template #body="slotProps">
                                <Tag :severity="resolveWarehouse(slotProps.data)" :value="slotProps.data.warehouse.toUpperCase()"></Tag>
                            </template>
                            <template #filter="{ filterModel, filterCallback }">
                                <Select v-model="filterModel.value" :options="warehouses" :showClear="true" placeholder="Select One" style="min-width: 12rem" />
                            </template>
                        </Column>

                        <Column field="payments" header="Status">
                            <template #body="slotProps">
                                <Tag :icon="resolvePaymentStatus(slotProps.data).icon" :severity="resolvePaymentStatus(slotProps.data).color" :value="slotProps.data.status" class="text-sm"></Tag>
                            </template>
                            <template #filter="{ filterModel, filterCallback }">
                                <Select v-model="filterModel.value" :options="paymentStatus" :showClear="true" class="w-full" input-id="payment-status" placeholder="Select One" />
                            </template>
                        </Column>

                        <Column field="grand_total" header="Amount" hidden>
                            <template #body="slotProps">
                                <div class="flex items-center">
                                    <i class="ti ti-cash mr-1 text-blue-500" style="font-size: 1rem"></i>
                                    {{ slotProps.data.grand_total.toFixed(2) }}
                                </div>
                            </template>
                        </Column>

                        <Column field="paid_amount" header="Paid" hidden>
                            <template #body="slotProps">
                                <div class="flex items-center">
                                    <i class="ti ti-cash mr-1 text-blue-500" style="font-size: 1rem"></i>
                                    {{ slotProps.data.paid_amount.toFixed(2) }}
                                </div>
                            </template>
                        </Column>

                        <Column field="hbl_type" header="HBL Type" hidden sortable>
                            <template #body="slotProps">
                                <Tag :severity="resolveHBLType(slotProps.data)" :value="slotProps.data.hbl_type"></Tag>
                            </template>
                            <template #filter="{ filterModel, filterCallback }">
                                <Select v-model="filterModel.value" :options="hblTypes" :showClear="true" placeholder="Select One" style="min-width: 12rem" />
                            </template>
                        </Column>

                        <Column field="is_hold" header="Hold">
                            <template #body="{ data }">
                                <i :class="{ 'pi-pause-circle text-yellow-500': data.is_hold, 'pi-play-circle text-green-400': !data.is_hold }" class="pi"></i>
                            </template>
                            <template #filter="{ filterModel, filterCallback }">
                                <div class="flex items-center gap-2">
                                    <Checkbox v-model="filterModel.value" :indeterminate="filterModel.value === null" binary inputId="is-hold"/>
                                    <label for="is-hold"> Is Hold </label>
                                </div>
                            </template>
                        </Column>

                        <Column field="is_released" header="Released" hidden></Column>

                        <Column field="zone" header="Zone">
                            <template #body="slotProps">
                                <Button v-if="slotProps.data.zone === null" aria-label="zone" icon="ti ti-home-plus" rounded severity="help" size="small" @click.prevent="confirmAssignZone(slotProps.data.id)" />
                                <span v-else>{{ slotProps.data.zone }}</span>
                            </template>
                        </Column>

                        <template #footer> In total there are {{ hbls ? totalRecords : 0 }} HBLs.</template>
                    </DataTable>
                </template>
            </Card>
        </div>
    </AppLayout>

    <HBLDetailModal
        :hbl-id="selectedHBLID"
        :show="showConfirmViewHBLModal"
        @close="closeModal"
    />

    <AssignZoneModal
        :hbl-id="selectedHBLID"
        :visible="showConfirmAssignZoneModal"
        :warehouse-zones="warehouseZones"
        @close="closeAssignZoneModal"
        @update:visible="showConfirmAssignZoneModal = $event"
    />
</template>
