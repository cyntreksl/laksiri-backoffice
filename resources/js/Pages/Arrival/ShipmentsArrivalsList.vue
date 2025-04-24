<script setup>
import {onMounted, ref, watch} from "vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import moment from "moment";
import LoadedShipmentDetailModal from "@/Pages/Loading/Partials/LoadedShipmentDetailModal.vue";
import {router, usePage} from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import {FilterMatchMode} from "@primevue/core/api";
import axios from "axios";
import {debounce} from "lodash";
import Tag from "primevue/tag";
import Button from "primevue/button";
import Column from "primevue/column";
import Panel from "primevue/panel";
import Card from "primevue/card";
import DataTable from "primevue/datatable";
import InputText from "primevue/inputtext";
import FloatLabel from "primevue/floatlabel";
import Select from "primevue/select";
import InputIcon from "primevue/inputicon";
import ContextMenu from "primevue/contextmenu";
import IconField from "primevue/iconfield";
import DatePicker from 'primevue/datepicker';
import {push} from "notivue";

const props = defineProps({
    cargoTypes: {
        type: Object,
        default: () => {
        },
    },
    containerTypes: {
        type: Object,
        default: () => {
        },
    },
    containers: {
        type: Object,
        default: () => {
        },
    },
    containerStatus: {
        type: Array,
        default: () => [],
    },
    branches: {
        type: Array,
        default: () => [],
    },
    seaContainerOptions: {
        type: Array,
        default: () => [],
    },
    airContainerOptions: {
        type: Array,
        default: () => [],
    }
});

const baseUrl = ref("/loaded-container-list");
const loading = ref(true);
const shipmentArrivals = ref([]);
const totalRecords = ref(0);
const perPage = ref(10);
const currentPage = ref(1);
const cm = ref();
const selectedShipment = ref([]);
const dt = ref();
const cargoTypes = ref(['Sea Cargo', 'Air Cargo']);
const fromDate = ref(moment(new Date()).subtract(1, "month").toISOString().split("T")[0]);
const toDate = ref(moment(new Date()).toISOString().split("T")[0]);
const etdStartDate = ref('');
const etdEndDate = ref('');
const selectedContainer = ref({});
const showConfirmLoadedShipmentModal = ref(false);

const filters = ref({
    global: {value: null, matchMode: FilterMatchMode.CONTAINS},
    cargo_type: {value: null, matchMode: FilterMatchMode.EQUALS},
    container_type: {value: null, matchMode: FilterMatchMode.EQUALS},
    status: {value: null, matchMode: FilterMatchMode.EQUALS},
    branch: {value: null, matchMode: FilterMatchMode.EQUALS},
});

const menuModel = ref([
    {
        label: "View",
        icon: "ti ti-search text-lg",
        command: () => confirmViewLoadedShipment(selectedShipment.value.id),
        disabled: !usePage().props.user.permissions.includes('arrivals.show'),
    },
    {
        label: 'Download Manifest',
        icon: 'ti ti-download text-lg',
        url: () => route("loading.loaded-containers.manifest.export", selectedShipment.value.id),
        disabled: !usePage().props.user.permissions.includes('arrivals.download manifest'),
    },
    {
        label: "Unload",
        icon: "ti ti-wrecking-ball text-lg",
        command: () => router.visit(
            route("arrival.unloading-points.index", {
                container: selectedShipment.value.id,
            })
        ),
        disabled: !usePage().props.user.permissions.includes('arrivals.unload'),
    },
    {
        label: "Mark As Reached",
        icon: "ti ti-navigation-check text-lg",
        command: () => router.visit(
            route("arrival.shipments-arrivals.containers.markAsReachedContainer", selectedShipment.value.id), {
                onSuccess: () => push.success('Mark As Reached')
            }),
        disabled: () =>
            !usePage().props.user.permissions.includes('arrivals.mark as reached') ||Shipments Arrivals
            ["REACHED"].includes(selectedShipment.value.is_reached),
    },
]);

const fetchShipmentArrivals = async (page = 1, search = "", sortField = 'created_at', sortOrder = 0) => {
    loading.value = true;
    try {
        const response = await axios.get(baseUrl.value, {
            params: {
                page,
                per_page: perPage.value,
                search,
                cargoMode: filters.value.cargo_type.value || "",
                sort_field: sortField,
                sort_order: sortOrder === 1 ? "asc" : "desc",
                fromDate: moment(fromDate.value).format("YYYY-MM-DD"),
                toDate: moment(toDate.value).format("YYYY-MM-DD"),
                containerType: filters.value.container_type.value || "",
                status: filters.value.status.value || "",
                etdStartDate: etdStartDate.value ? moment(etdStartDate.value).format("YYYY-MM-DD") : null,
                etdEndDate: etdEndDate.value ? moment(etdEndDate.value).format("YYYY-MM-DD") : null,
                branch: filters.value.branch.value || "",
            }
        });
        shipmentArrivals.value = response.data.data;
        totalRecords.value = response.data.meta.total;
        currentPage.value = response.data.meta.current_page;
    } catch (error) {
        console.error("Error fetching shipment arrivals:", error);
    } finally {
        loading.value = false;
    }
};

const debouncedFetchShipmentArrivals = debounce((searchValue) => {
    fetchShipmentArrivals(1, searchValue);
}, 1000);

watch(() => filters.value.global.value, (newValue) => {
    if (newValue !== null) {
        debouncedFetchShipmentArrivals(newValue);
    }
});

watch(() => filters.value.cargo_type.value, (newValue) => {
    fetchShipmentArrivals(1, filters.value.global.value);
});

watch(() => fromDate.value, (newValue) => {
    fetchShipmentArrivals(1, filters.value.global.value);
});

watch(() => filters.value.container_type.value, (newValue) => {
    fetchShipmentArrivals(1, filters.value.global.value);
});

watch(() => filters.value.status.value, (newValue) => {
    fetchShipmentArrivals(1, filters.value.global.value);
});

watch(() => toDate.value, (newValue) => {
    fetchShipmentArrivals(1, filters.value.global.value);
});

watch(() => etdStartDate.value, (newValue) => {
    fetchShipmentArrivals(1, filters.value.global.value);
});

watch(() => etdEndDate.value, (newValue) => {
    fetchShipmentArrivals(1, filters.value.global.value);
});

watch(() => filters.value.branch.value, (newValue) => {
    fetchShipmentArrivals(1, filters.value.global.value);
});

const onPageChange = (event) => {
    perPage.value = event.rows;
    currentPage.value = event.page + 1;
    fetchShipmentArrivals(currentPage.value);
};

const onSort = (event) => {
    fetchShipmentArrivals(currentPage.value, filters.value.global.value, event.sortField, event.sortOrder);
};

onMounted(() => {
    fetchShipmentArrivals();
});

const onRowContextMenu = (event) => {
    cm.value.show(event.originalEvent);
};

const clearFilter = () => {
    filters.value = {
        global: {value: null, matchMode: FilterMatchMode.CONTAINS},
        cargo_type: {value: null, matchMode: FilterMatchMode.EQUALS},
        container_type: {value: null, matchMode: FilterMatchMode.EQUALS},
        status: {value: null, matchMode: FilterMatchMode.EQUALS},
        branch: {value: null, matchMode: FilterMatchMode.EQUALS},
    };
    fromDate.value = moment(new Date()).subtract(1, "month").toISOString().split("T")[0];
    toDate.value = moment(new Date()).toISOString().split("T")[0];
    etdStartDate.value = '';
    etdEndDate.value = '';
    fetchShipmentArrivals(currentPage.value);
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

const resolveContainerStatus = (container) => {
    switch (container.status) {
        case 'LOADED':
            return {
                icon: "ti ti-package",
                color: "info",
            };
        case 'Container Ordered':
            return {
                icon: "ti ti-clock-play",
                color: "secondary",
            };
        case 'IN TRANSIT':
            return {
                icon: "ti ti-tir",
                color: "help",
            };
        case 'UNLOADED':
            return {
                icon: "ti ti-package-off",
                color: "warn",
            };
        case 'REACHED DESTINATION':
            return {
                icon: "ti ti-checks",
                color: "success",
            };
        default:
            return {
                icon: "ti ti-question-mark",
                color: "danger",
            };
    }
};

const exportCSV = () => {
    dt.value.exportCSV();
};

const confirmViewLoadedShipment = (id) => {
    const container = props.containers.find(
        (container) => container.id === id
    );

    if (container) {
        selectedContainer.value = container;
        showConfirmLoadedShipmentModal.value = true;
    } else {
        console.error('Container not found with id:', id);
    }
};

const closeModal = () => {
    showConfirmLoadedShipmentModal.value = false;
    selectedShipment.value = [];
};
</script>

<template>
    <AppLayout title="Shipments Arrivals">
        <template #header>Shipments Arrivals</template>

        <Breadcrumb/>

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
                        <DatePicker v-model="etdStartDate" class="w-full" date-format="yy-mm-dd"
                                    input-id="etd-start-date"/>
                        <label for="etd-start-date">ETD Start Date</label>
                    </FloatLabel>

                    <FloatLabel class="w-full" variant="in">
                        <DatePicker v-model="etdEndDate" class="w-full" date-format="yy-mm-dd" input-id="etd-end-date"/>
                        <label for="etd-end-date">ETD End Date</label>
                    </FloatLabel>
                </div>
            </Panel>

            <Card class="my-5">
                <template #content>
                    <ContextMenu ref="cm" :model="menuModel" @hide="selectedShipment.length < 1"/>
                    <DataTable
                        ref="dt"
                        v-model:contextMenuSelection="selectedShipment"
                        v-model:filters="filters"
                        :globalFilterFields="['reference', 'bl_number', 'awb_number', 'container_number', 'seal_number', 'vessel_name', 'voyage_number', 'shipping_line']"
                        :loading="loading"
                        :rows="perPage"
                        :rowsPerPageOptions="[5, 10, 20, 50, 100]"
                        :totalRecords="totalRecords"
                        :value="shipmentArrivals"
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
                                    Shipments Arrivals
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

                        <template #empty>No shipment arrivals found.</template>

                        <template #loading>Loading shipment arrivals data. Please wait.</template>

                        <Column field="container_type" header="Container Type" sortable>
                            <template #body="slotProps">
                                <Tag :severity="resolveContainerType(slotProps.data)"
                                     :value="slotProps.data.container_type" class="text-sm"></Tag>
                            </template>
                            <template #filter="{ filterModel }">
                                <Select v-model="filterModel.value" :options="containerTypes" :showClear="true"
                                        placeholder="Select One" style="min-width: 12rem"/>
                            </template>
                        </Column>

                        <Column field="branch" header="Origin">
                            <template #filter="{ filterModel }">
                                <Select v-model="filterModel.value"
                                        :options="branches"
                                        :showClear="true"
                                        option-label="name"
                                        option-value="id"
                                        placeholder="Select One" style="min-width: 12rem"/>
                            </template>
                        </Column>

                        <Column field="reference" header="Reference" sortable></Column>

                        <Column field="bl_number" header="BL Number" sortable></Column>

                        <Column field="awb_number" header="AWB Number" sortable></Column>

                        <Column field="container_number" header="Container Number" sortable></Column>

                        <Column field="seal_number" header="Seal Number" sortable></Column>

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

                        <Column field="status" header="Status">
                            <template #body="slotProps">
                                <Tag :icon="resolveContainerStatus(slotProps.data).icon"
                                     :severity="resolveContainerStatus(slotProps.data).color"
                                     :value="slotProps.data.status" class="text-sm uppercase"></Tag>
                            </template>

                            <template #filter="{ filterModel }">
                                <Select v-model="filterModel.value"
                                        :options="['LOADED', 'REACHED DESTINATION', 'UNLOADED', 'IN TRANSIT', 'CONTAINER ORDERED']"
                                        :showClear="true"
                                        placeholder="Select One" style="min-width: 12rem"/>
                            </template>
                        </Column>

                        <Column field="note" header="Note"></Column>

                        <Column field="is_reached" header="Is Reached">
                            <template #body="{data}">
                                <div class="flex items-center space-x-2">
                                    <i :class="[
        data.is_reached === 'REACHED' ? 'pi pi-check-circle text-success' : 'pi pi-info-circle text-warning'
      ]">
                                    </i>
                                    <div :class="data.is_reached === 'REACHED' ? 'text-success' : 'text-warning'">
                                        {{ data.is_reached }}
                                    </div>
                                </div>
                            </template>
                        </Column>

                        <template #footer> In total there are {{ shipmentArrivals ? totalRecords : 0 }} shipment
                            arrivals.
                        </template>
                    </DataTable>
                </template>
            </Card>
        </div>
    </AppLayout>

    <LoadedShipmentDetailModal :air-container-options="airContainerOptions"
                               :container="selectedContainer"
                               :container-status="containerStatus"
                               :sea-container-options="seaContainerOptions"
                               :show="showConfirmLoadedShipmentModal"
                               @close="closeModal" />
</template>

<style>
.p-tag-icon {
    font-size: 15px;
    margin-right: 3px;
}
</style>
