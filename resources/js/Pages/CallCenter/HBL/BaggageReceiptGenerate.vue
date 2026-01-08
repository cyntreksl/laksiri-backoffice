<script setup>
import {onMounted, ref, watch} from "vue";
import {router} from "@inertiajs/vue3";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import Card from "primevue/card";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import Button from "primevue/button";
import Tag from "primevue/tag";
import Panel from "primevue/panel";
import FloatLabel from "primevue/floatlabel";
import InputIcon from "primevue/inputicon";
import InputText from "primevue/inputtext";
import IconField from "primevue/iconfield";
import DatePicker from 'primevue/datepicker';
import ContextMenu from "primevue/contextmenu";
import Select from "primevue/select";
import {FilterMatchMode} from "@primevue/core/api";
import axios from "axios";
import {debounce} from "lodash";
import {push} from "notivue";
import moment from "moment";

const loading = ref(true);
const shipments = ref([]);
const totalRecords = ref(0);
const perPage = ref(10);
const currentPage = ref(1);
const selectedShipment = ref(null);
const cm = ref();
const dt = ref();
const fromDate = ref(moment(new Date()).subtract(12, "months").toISOString().split("T")[0]);
const toDate = ref(moment(new Date()).toISOString().split("T")[0]);
const cargoTypes = ref(['Sea Cargo', 'Air Cargo']);
const containerTypes = ref(['20FT General', '20FT High Cube', '40FT General', '40FT High Cube', 'Custom']);

const filters = ref({
    global: {value: null, matchMode: FilterMatchMode.CONTAINS},
    cargo_type: {value: null, matchMode: FilterMatchMode.EQUALS},
    container_type: {value: null, matchMode: FilterMatchMode.EQUALS},
});

const menuModel = ref([
    {
        label: 'Download All',
        icon: 'pi pi-download',
        url: () => route("call-center.hbls.generate-all-baggage-receipts", selectedShipment.value.id),
    },
    {
        label: 'Print All',
        icon: 'pi pi-print',
        url: () => route("call-center.hbls.stream-all-baggage-receipts", selectedShipment.value.id),
    },
    {
        label: 'Download ZIP',
        icon: 'pi pi-file-export',
        url: () => route("call-center.hbls.generate-baggage-receipts-zip", selectedShipment.value.id),
    },
]);

const fetchShipments = async (page = 1, search = "", sortField = 'arrived_at_primary_warehouse', sortOrder = 0) => {
    loading.value = true;
    try {
        const response = await axios.get('/call-center/baggage-receipts/shipments', {
            params: {
                page,
                per_page: perPage.value,
                search,
                sort_field: sortField,
                sort_order: sortOrder === 1 ? "asc" : "desc",
                fromDate: moment(fromDate.value).format("YYYY-MM-DD"),
                toDate: moment(toDate.value).format("YYYY-MM-DD"),
            }
        });

        shipments.value = response.data.data;
        totalRecords.value = response.data.meta.total;
        currentPage.value = response.data.meta.current_page;
    } catch (error) {
        console.error("Error fetching shipments:", error);
        push.error("Failed to load shipments");
    } finally {
        loading.value = false;
    }
};

const debouncedFetchShipments = debounce((searchValue) => {
    fetchShipments(1, searchValue || "");
}, 1000);

watch(() => filters.value.global.value, (newValue) => {
    debouncedFetchShipments(newValue);
});

watch(() => fromDate.value, () => {
    fetchShipments(1, filters.value.global.value || "");
});

watch(() => toDate.value, () => {
    fetchShipments(1, filters.value.global.value || "");
});

watch(() => filters.value.cargo_type.value, () => {
    fetchShipments(1, filters.value.global.value || "");
});

watch(() => filters.value.container_type.value, () => {
    fetchShipments(1, filters.value.global.value || "");
});

const onPageChange = (event) => {
    perPage.value = event.rows;
    currentPage.value = event.page + 1;
    fetchShipments(currentPage.value, filters.value.global.value);
};

const onSort = (event) => {
    fetchShipments(currentPage.value, filters.value.global.value, event.sortField, event.sortOrder);
};

const onRowContextMenu = (event) => {
    event.originalEvent.preventDefault();
    selectedShipment.value = event.data;
    if (cm.value) {
        cm.value.show(event.originalEvent);
    }
};

const clearFilter = () => {
    filters.value = {
        global: {value: null, matchMode: FilterMatchMode.CONTAINS},
        cargo_type: {value: null, matchMode: FilterMatchMode.EQUALS},
        container_type: {value: null, matchMode: FilterMatchMode.EQUALS},
    };
    fromDate.value = moment(new Date()).subtract(12, "months").toISOString().split("T")[0];
    toDate.value = moment(new Date()).toISOString().split("T")[0];
    fetchShipments(1);
};

onMounted(() => {
    fetchShipments();
});

const exportCSV = () => {
    dt.value.exportCSV();
};

const resolveCargoType = (container) => {
    switch (container.cargo_type) {
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

const resolveContainerType = (container) => {
    switch (container.container_type) {
        case '20FT General':
            return 'help';
        case '20FT High Cube':
            return 'warn';
        case '40FT General':
            return 'info';
        case '40FT High Cube':
            return 'primary';
        case 'Custom':
            return 'secondary';
        default:
            return 'danger';
    }
};
</script>

<template>
    <AppLayout title="Baggage Receipt Generation">
        <template #header>Baggage Receipt Generation</template>

        <Breadcrumb />

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
                </div>
            </Panel>

            <Card class="my-5">
                <template #content>
                    <ContextMenu ref="cm" :hidden="!selectedShipment" :model="menuModel"/>
                    <DataTable
                        ref="dt"
                        v-model:contextMenuSelection="selectedShipment"
                        v-model:filters="filters"
                        :globalFilterFields="['reference', 'bl_number', 'awb_number', 'vessel_name', 'port_of_discharge', 'container_number']"
                        :loading="loading"
                        :rows="perPage"
                        :rowsPerPageOptions="[5, 10, 20, 50, 100]"
                        :totalRecords="totalRecords"
                        :value="shipments"
                        context-menu
                        data-key="id"
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
                                    Baggage Receipt Generation
                                </div>
                                <Button
                                    icon="pi pi-arrow-left"
                                    label="Back to HBL List"
                                    severity="secondary"
                                    size="small"
                                    @click="router.visit(route('call-center.hbls.index'))"/>
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

                        <template #empty>No shipments found.</template>

                        <template #loading>Loading shipments data. Please wait.</template>

                        <Column field="container_type" header="Container Type" sortable>
                            <template #body="slotProps">
                                <Tag :severity="resolveContainerType(slotProps.data)"
                                     :value="slotProps.data.container_type" class="text-sm"></Tag>
                            </template>
                            <template #filter="{ filterModel }">
                                <Select v-model="filterModel.value" :options="containerTypes" :showClear="true"
                                        placeholder="Select One" style="min-width: 12rem" />
                            </template>
                        </Column>

                        <Column field="reference" header="Reference" sortable>
                            <template #body="slotProps">
                                <div class="font-medium">{{ slotProps.data.reference }}</div>
                            </template>
                        </Column>

                        <Column field="bl_number" header="BL Number" sortable></Column>

                        <Column field="awb_number" header="AWB Number" sortable></Column>

                        <Column field="cargo_type" header="Cargo Type" sortable>
                            <template #body="slotProps">
                                <Tag :icon="resolveCargoType(slotProps.data).icon"
                                     :severity="resolveCargoType(slotProps.data).color"
                                     :value="slotProps.data.cargo_type" class="text-sm"></Tag>
                            </template>

                            <template #filter="{ filterModel }">
                                <Select v-model="filterModel.value" :options="cargoTypes" :showClear="true"
                                        placeholder="Select One" style="min-width: 12rem"/>
                            </template>
                        </Column>

                        <Column field="vessel_name" header="Vessel Name" sortable></Column>

                        <Column field="port_of_discharge" header="Port of Discharge" sortable></Column>

                        <Column field="estimated_time_of_arrival" header="ETA" sortable></Column>

                        <Column field="estimated_time_of_departure" header="ETD" sortable></Column>

                        <Column field="hbl_count" header="HBL Count" sortable>
                            <template #body="slotProps">
                                <Tag :value="slotProps.data.hbl_count" severity="info"></Tag>
                            </template>
                        </Column>

                        <template #footer>
                            In total there are {{ shipments ? totalRecords : 0 }} shipments.
                        </template>
                    </DataTable>
                </template>
            </Card>
        </div>
    </AppLayout>
</template>


<style>
.p-tag-icon {
    font-size: 15px;
    margin-right: 3px;
}
</style>
