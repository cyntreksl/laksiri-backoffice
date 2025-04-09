<script setup>
import { computed, onMounted, reactive, ref, watch } from "vue";
import { Link, router, usePage } from "@inertiajs/vue3";
import moment from "moment";
import { debounce } from "lodash";
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import DatePicker from 'primevue/datepicker';
import DeleteMHBLConfirmationModal from "@/Pages/MHBL/Partials/DeleteMHBLConfirmationModal.vue";
import { push } from "notivue";
import HoldConfirmationModal from "@/Pages/HBL/Partials/HoldConfirmationModal.vue";
import MHBLDetailModal from "@/Pages/Common/MHBLDetailModal.vue";
import Select from "primevue/select";
import Panel from "primevue/panel";
import FloatLabel from "primevue/floatlabel";
import InputText from "primevue/inputtext";
import DataTable from "primevue/datatable";
import InputIcon from "primevue/inputicon";
import Card from "primevue/card";
import Tag from "primevue/tag";
import Button from "primevue/button";
import IconField from "primevue/iconfield";
import Column from "primevue/column";
import ContextMenu from "primevue/contextmenu";
import { FilterMatchMode } from "@primevue/core/api";

const props = defineProps({
    users: {
        type: Object,
        default: () => {},
    },
    hbls: {
        type: Object,
        default: () => {},
    },
    paymentStatus: {
        type: Object,
        default: () => {},
    },
});

const baseUrl = ref("/mhbl-list");
const loading = ref(true);
const mhbls = ref([]);
const totalRecords = ref(0);
const currentPage = ref(1);
const selectedMHBL = ref(null);
const warehouses = ref(['COLOMBO', 'NINTAVUR',]);
const cargoTypes = ref(['Sea Cargo', 'Air Cargo']);
const selectedMHBLID = ref(null);
const showConfirmViewMHBLModal = ref(false);
const showConfirmDeleteMHBLModal = ref(false);
const showConfirmHoldModal = ref(false);
const cm = ref();
const dt = ref();
const perPage = ref(10);
const fromDate = ref(moment(new Date()).subtract(24, "months").toISOString().split("T")[0]);
const toDate = ref(moment(new Date()).toISOString().split("T")[0]);
const showReferenceColumn = ref(false);

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    warehouse: { value: null, matchMode: FilterMatchMode.EQUALS },
    hbl_type: { value: null, matchMode: FilterMatchMode.EQUALS },
    cargo_type: { value: null, matchMode: FilterMatchMode.EQUALS },
    is_hold: { value: null, matchMode: FilterMatchMode.EQUALS },
    user: { value: null, matchMode: FilterMatchMode.EQUALS },
    payments: { value: null, matchMode: FilterMatchMode.EQUALS },
});

const menuModel = ref([
    {
        label: 'View MHBL',
        icon: 'pi pi-fw pi-eye',
        command: () => confirmViewMHBL(selectedMHBL.value),
        disabled: !usePage().props.user.permissions.includes("hbls.show"),
    },
    {
        label: 'Edit',
        icon: 'pi pi-fw pi-pencil',

        command: () => router.visit(route("mhbls.edit", selectedMHBL.value.id)),
        disabled: !usePage().props.user.permissions.includes("hbls.edit"),
    },
    {
        label: 'Download HBLs',
        icon: 'pi pi-fw pi-download',
        command: () => {
            window.location.href = route("mhbls.hbl-list-downloads", selectedMHBL.value.id);
        },
        disabled: !usePage().props.user.permissions.includes("mhbls.download hbl list"),
    },
    {
        label: 'Delete',
        icon: 'pi pi-fw pi-trash',
        command: () => confirmDeleteMHBL(selectedMHBL.value),
        disabled: !usePage().props.user.permissions.includes("hbls.delete"),
    },
]);

const fetchMHBLs = async (page = 1, search = "", sortField = 'created_at', sortOrder = 0) => {
    loading.value = true;
    try {
        const response = await axios.get(baseUrl.value, {
            params: {
                page,
                per_page: perPage.value,
                search,
                warehouse: filters.value.warehouse.value || "",
                cargoMode: filters.value.cargo_type.value || "",
                sort_field: sortField,
                sort_order: sortOrder === 1 ? "asc" : "desc",
                createdBy: filters.value.user.value || "",
                paymentStatus: filters.value.payments.value || [],
                fromDate: moment(fromDate.value).format("YYYY-MM-DD"),
                toDate: moment(toDate.value).format("YYYY-MM-DD"),
            }
        });
        mhbls.value = response.data.data;
        totalRecords.value = response.data.meta.total;
        currentPage.value = response.data.meta.current_page;
    } catch (error) {
        console.error("Error fetching MHBLs:", error);
    } finally {
        loading.value = false;
    }
};

const debounceFetchMHBLs = debounce((searchValue) => {
    fetchMHBLs(1, searchValue);
}, 1000);

watch(() => filters.value.global.value, (newValue) => {
    if (newValue !== null) {
        debounceFetchMHBLs(newValue);
    }
});

watch(() => filters.value.warehouse.value, (newValue) => {
    fetchMHBLs(1, filters.value.global.value);
});

watch(() => filters.value.cargo_type.value, (newValue) => {
    fetchMHBLs(1, filters.value.global.value);
});

watch(() => filters.value.is_hold.value, (newValue) => {
    fetchMHBLs(1, filters.value.global.value);
});

watch(() => filters.value.user.value, (newValue) => {
    fetchMHBLs(1, filters.value.global.value);
});

watch(() => filters.value.payments.value, (newValue) => {
    fetchMHBLs(1, filters.value.global.value);
});

watch(() => fromDate.value, (newValue) => {
    fetchMHBLs(1, filters.value.global.value);
});

watch(() => toDate.value, (newValue) => {
    fetchMHBLs(1, filters.value.global.value);
});

const onPageChange = (event) => {
    perPage.value = event.rows;
    currentPage.value = event.page + 1;
    fetchMHBLs(currentPage.value);
};

const onSort = (event) => {
    fetchMHBLs(currentPage.value, filters.value.global.value, event.sortField, event.sortOrder);
};

onMounted(() => {
    fetchMHBLs();
});

const resolveMHBLType = (mhbl) => {
    switch (mhbl.hbl_type) {
        case 'Gift':
            return 'warn';
        default:
            return null;
    }
};

const resolveCargoType = (mhbl) => {
    switch (mhbl.cargo_type) {
        case 'Sea Cargo':
            return { icon: "ti ti-sailboat", color: "success" };
        case 'Air Cargo':
            return { icon: "ti ti-plane-tilt", color: "info" };
        default:
            return null;
    }
};

const resolveWarehouse = (mhbl) => {
    switch (mhbl.warehouse.toUpperCase()) {
        case 'COLOMBO':
            return 'info';
        case 'NINTAVUR':
            return 'danger';
        default:
            return null;
    }
};

const onRowContextMenu = (event) => {
    selectedMHBL.value = event.data;
    cm.value.show(event.originalEvent);
};

const confirmViewMHBL = (mhbl) => {
    selectedMHBLID.value = mhbl.id;
    showConfirmViewMHBLModal.value = true;
};

const closeModal = () => {
    showConfirmViewMHBLModal.value = false;
    showConfirmDeleteMHBLModal.value = false;
    showConfirmHoldModal.value = false;
    selectedMHBL.value = null;
};

const closeHoldModal = () => {
    showConfirmHoldModal.value = false;
};

const toggleHold = (id) => {
    router.put(
        route("mhbls.toggle-hold", id),
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                push.success(`Operation Successfully!`);
                fetchMHBLs(currentPage.value);
            },
            onError: () => {
                push.error("Something went wrong!");
            },
        }
    );
};

const handleDeleteMHBL = (id) => {
    router.delete(
        route("mhbls.destroy", id),
        {
            preserveScroll: true,
            onSuccess: () => {
                push.success(`MHBL Record Deleted Successfully!`);
                fetchMHBLs(currentPage.value);
            },
            onError: () => {
                push.error("Something went wrong!");
            },
        }
    );
};

const clearFilter = () => {
    filters.value = {
        global: { value: null, matchMode: FilterMatchMode.CONTAINS },
        warehouse: { value: null, matchMode: FilterMatchMode.EQUALS },
        cargo_type: { value: null, matchMode: FilterMatchMode.EQUALS },
        is_hold: { value: null, matchMode: FilterMatchMode.EQUALS },
        user: { value: null, matchMode: FilterMatchMode.EQUALS },
        payments: { value: null, matchMode: FilterMatchMode.EQUALS },
    };
    fromDate.value = moment(new Date()).subtract(24, "months").toISOString().split("T")[0];
    toDate.value = moment(new Date()).toISOString().split("T")[0];
    fetchMHBLs(currentPage.value);
};

const confirmDeleteMHBL = (id) => {
    selectedMHBLID.value = id;
    showConfirmDeleteMHBLModal.value = true;
};

const exportCSV = () => {
    dt.value.exportCSV();
};
</script>

<template>
    <AppLayout title="MHBL List">
        <template #header>MHBL List</template>

        <Breadcrumb/>
        <div>
            <Panel :collapsed="true" class="mt-5" header="Advance Filters" toggleable>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                    <FloatLabel class="w-full" variant="in">
                        <DatePicker v-model="fromDate" class="w-full" date-format="yy-mm-dd" input-id="from-date" placeholder=" " />
                        <label for="from-date">From Date</label>
                    </FloatLabel>

                    <FloatLabel class="w-full" variant="in">
                        <DatePicker v-model="toDate" class="w-full" date-format="yy-mm-dd" input-id="to-date"/>
                        <label for="to-date">To Date</label>
                    </FloatLabel>

                    <FloatLabel class="w-full" variant="in">
                        <Select v-model="filters.user.value" :options="users" :showClear="true" class="w-full"
                                input-id="user" option-label="name" option-value="id"/>
                        <label for="user">Created By</label>
                    </FloatLabel>
                </div>
            </Panel>
            <Card class="my-5">
                <template #content>
                    <ContextMenu ref="cm" :model="menuModel" @hide="selectedMHBL = null"/>
                    <DataTable
                        ref="dt"
                        v-model:contextMenuSelection="selectedMHBL"
                        v-model:filters="filters"
                        :globalFilterFields="['reference', 'hbl', 'hbl_name', 'email', 'address', 'contact_number', 'consignee_name', 'consignee_address', 'consignee_contact', 'cargo_type',  'warehouse', 'status', 'mhbl_number']"
                        :loading="loading"
                        :rows="perPage"
                        :rowsPerPageOptions="[5, 10, 20, 50, 100]"
                        :totalRecords="totalRecords"
                        :value="mhbls"
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
                                     MHBLs
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

                        <template #empty> No MHBLs found.</template>

                        <template #loading> Loading MHBL data. Please wait.</template>

                        <Column v-if="showReferenceColumn" field="reference" header="Reference" sortable>
                            <template #body="slotProps">
                                <span>{{ slotProps.data.reference }}</span>
                            </template>
                        </Column>

                        <Column field="mhbl_number" header="HBL Number" sortable>
                            <template #body="slotProps">
                                <span class="font-medium">{{ slotProps.data.hbl_number }}</span>
                            </template>
                        </Column>

                        <Column field="cargo_type" header="Cargo Type" sortable>
                            <template #body="slotProps">
                                <Tag :icon="resolveCargoType(slotProps.data).icon"
                                     :severity="resolveCargoType(slotProps.data).color"
                                     :value="slotProps.data.cargo_type" class="text-sm"></Tag>
                            </template>
                            <template #filter="{ filterModel, filterCallback }">
                                <Select v-model="filterModel.value" :options="cargoTypes" :showClear="true"
                                        placeholder="Select One" style="min-width: 12rem" @change="filterCallback()"/>
                            </template>
                        </Column>

                        <Column field="shipper_name" header="Shipper">
                            <template #body="slotProps">
                                <div>{{ slotProps.data.shipper_name }}</div>
                                <div class="text-gray-500 text-sm">{{slotProps.data.shipper_contact}}</div>
                                <div class="text-gray-500 text-sm">{{slotProps.data.shipper_email}}</div>
                            </template>
                        </Column>

                        <Column field="shipper_contact" header="Shipper NIC">
                            <template #body="slotProps">
                                <div>{{ slotProps.data.shipper_nic }}</div>
                            </template>
                        </Column>

                        <Column field="warehouse" header="Warehouse" sortable>
                            <template #body="slotProps">
                                <Tag :severity="resolveWarehouse(slotProps.data)"
                                     :value="slotProps.data.warehouse.toUpperCase()"></Tag>
                            </template>
                            <template #filter="{ filterModel, filterCallback }">
                                <Select v-model="filterModel.value" :options="warehouses" :showClear="true"
                                        placeholder="Select One" style="min-width: 12rem" @change="filterCallback()"/>
                            </template>
                        </Column>

                        <Column field="consignee_name" header="Consignee">
                            <template #body="slotProps">
                                <div>{{ slotProps.data.consignee_name }}</div>
                                <div class="text-gray-500 text-sm">{{slotProps.data.consignee_contact}}</div>
                                <div class="text-gray-500 text-sm">{{slotProps.data.consignee_email}}</div>
                            </template>
                        </Column>

                        <Column field="consignee_contact" header="Consignee NIC">
                            <template #body="slotProps">
                                <div>{{ slotProps.data.consignee_nic }}</div>
                            </template>
                        </Column>

                        <Column field="hbl_type" header="HBL Type" sortable>
                            <template #body="slotProps">
                                <Tag :severity="resolveMHBLType(slotProps.data)" :value="slotProps.data.hbl_type"></Tag>
                            </template>
                        </Column>

                        <template #footer> In total there are {{ mhbls ?totalRecords :0 }} MHBLs.</template>
                    </DataTable>
                </template>
            </Card>
        </div>
    </AppLayout>

        <DeleteMHBLConfirmationModal
            :show="showConfirmDeleteMHBLModal"
            :mhbl-id="selectedMHBLID"
            @close="closeModal"
            @delete-mhbl="handleDeleteMHBL"
        />

        <MHBLDetailModal
            :mhbl-id="selectedMHBLID"
            :show="showConfirmViewMHBLModal"
            @close="closeModal"
        />

        <HoldConfirmationModal
            :hbl-id="selectedMHBLID"
        :show="showConfirmHoldModal"
        @close="closeHoldModal"
        @toggle-hold="toggleHold"
        />

</template>

<style>
.p-tag-icon {
    font-size: 15px;
}
</style>
