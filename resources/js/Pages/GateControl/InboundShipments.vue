<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import {onMounted, ref, watch} from "vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import moment from "moment";
import {router, usePage} from "@inertiajs/vue3";
import {FilterMatchMode} from "@primevue/core/api";
import axios from "axios";
import {debounce} from "lodash";
import InputIcon from "primevue/inputicon";
import Select from "primevue/select";
import Card from "primevue/card";
import InputText from "primevue/inputtext";
import FloatLabel from "primevue/floatlabel";
import DataTable from "primevue/datatable";
import Tag from "primevue/tag";
import ContextMenu from "primevue/contextmenu";
import Panel from "primevue/panel";
import Button from "primevue/button";
import DatePicker from "primevue/datepicker";
import Column from "primevue/column";
import IconField from "primevue/iconfield";
import LoadedShipmentDetailDialog from "@/Pages/Common/Dialog/Container/Index.vue";
import {push} from "notivue";
import {useConfirm} from "primevue/useconfirm";
import LongVehicle from '@/../images/illustrations/long-vehicle.png';
import CargoPlane from '@/../images/illustrations/cargo-plane.png';

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
    seaContainerOptions: {
        type: Array,
        required: true,
    },
    airContainerOptions: {
        type: Array,
        required: true,
    },
});

const baseUrl = ref("/gate-control/get-after-dispatch-shipments-list");
const loading = ref(true);
const containers = ref([]);
const totalRecords = ref(0);
const perPage = ref(10);
const currentPage = ref(1);
const cm = ref();
const selectedContainer = ref([]);
const dt = ref();
const cargoTypes = ref(['Sea Cargo', 'Air Cargo']);
const fromDate = ref(moment(new Date()).subtract(6, "month").toISOString().split("T")[0]);
const toDate = ref(moment(new Date()).toISOString().split("T")[0]);
const etdStartDate = ref('');
const etdEndDate = ref('');
const showConfirmShipmentModal = ref(false);
const inboundShipment = ref(null);
const confirm = useConfirm();

const filters = ref({
    global: {value: null, matchMode: FilterMatchMode.CONTAINS},
    cargo_type: {value: null, matchMode: FilterMatchMode.EQUALS},
    container_type: {value: null, matchMode: FilterMatchMode.EQUALS},
    status: {value: null, matchMode: FilterMatchMode.EQUALS},
});

const menuModel = ref([
    {
        label: 'Mark as Arrived',
        icon: 'pi pi-fw pi-directions',
        command: () => confirmMarkAsArrived(selectedContainer),
        disabled: () => !usePage().props.user.permissions.includes('mark-shipment-arrived-to-warehouse') || selectedContainer.value.status === 'ARRIVED PRIMARY WAREHOUSE',
    },
    // {
    //     label: 'Show',
    //     icon: 'pi pi-fw pi-search',
    //     command: () => confirmViewShipment(selectedContainer.value.id),
    //     disabled: !usePage().props.user.permissions.includes('shipment.show'),
    // },
    // {
    //     label: 'Download Manifest',
    //     icon: 'pi pi-fw pi-download',
    //     url: () => route("loading.loaded-containers.manifest.export", selectedContainer.value.id),
    //     disabled: !usePage().props.user.permissions.includes('shipment.download manifest'),
    // },
]);

const fetchInboundShipments = async (page = 1, search = "", sortField = 'created_at', sortOrder = 0) => {
    loading.value = true;
    try {
        const response = await axios.get(baseUrl.value, {
            params: {
                page,
                per_page: perPage.value,
                search,
                cargoType: filters.value.cargo_type.value || "",
                sort_field: sortField,
                sort_order: sortOrder === 1 ? "asc" : "desc",
                fromDate: moment(fromDate.value).format("YYYY-MM-DD"),
                toDate: moment(toDate.value).format("YYYY-MM-DD"),
                containerType: filters.value.container_type.value || "",
                status: filters.value.status.value || "",
                etdStartDate: etdStartDate.value ? moment(etdStartDate.value).format("YYYY-MM-DD") : null,
                etdEndDate: etdEndDate.value ? moment(etdEndDate.value).format("YYYY-MM-DD") : null,
            }
        });
        containers.value = response.data.data;
        totalRecords.value = response.data.meta.total;
        currentPage.value = response.data.meta.current_page;
    } catch (error) {
        console.error("Error fetching Inbound Shipments:", error);
    } finally {
        loading.value = false;
    }
};

const debouncedFetchInboundShipments = debounce((searchValue) => {
    fetchInboundShipments(1, searchValue);
}, 1000);

watch(() => filters.value.global.value, (newValue) => {
    if (newValue !== null) {
        debouncedFetchInboundShipments(newValue);
    }
});

watch(() => filters.value.cargo_type.value, (newValue) => {
    fetchInboundShipments(1, filters.value.global.value);
});

watch(() => fromDate.value, (newValue) => {
    fetchInboundShipments(1, filters.value.global.value);
});

watch(() => filters.value.container_type.value, (newValue) => {
    fetchInboundShipments(1, filters.value.global.value);
});

watch(() => filters.value.status.value, (newValue) => {
    fetchInboundShipments(1, filters.value.global.value);
});

watch(() => toDate.value, (newValue) => {
    fetchInboundShipments(1, filters.value.global.value);
});

watch(() => etdStartDate.value, (newValue) => {
    fetchInboundShipments(1, filters.value.global.value);
});

watch(() => etdEndDate.value, (newValue) => {
    fetchInboundShipments(1, filters.value.global.value);
});

const onPageChange = (event) => {
    perPage.value = event.rows;
    currentPage.value = event.page + 1;
    fetchInboundShipments(currentPage.value);
};

const onSort = (event) => {
    fetchInboundShipments(currentPage.value, filters.value.global.value, event.sortField, event.sortOrder);
};

onMounted(() => {
    fetchInboundShipments();
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
    };
    fromDate.value = moment(new Date()).subtract(7, "days").toISOString().split("T")[0];
    toDate.value = moment(new Date()).toISOString().split("T")[0];
    etdStartDate.value = '';
    etdEndDate.value = '';
    fetchInboundShipments(currentPage.value);
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
        case 'IN TRANSIT':
            return {
                icon: "ti ti-tir",
                color: "help",
            };
        case 'REACHED DESTINATION':
            return {
                icon: "ti ti-checks",
                color: "success",
            };
        case 'ARRIVED PRIMARY WAREHOUSE':
            return {
                icon: "pi pi-directions",
                color: "success",
            };
        default:
            return {
                icon: "ti ti-question-mark",
                color: "danger",
            };
    }
};

const confirmViewShipment = (id) => {
    inboundShipment.value = props.containers.find(
        (container) => container.id === id
    );
    showConfirmShipmentModal.value = true;
};

const confirmMarkAsArrived = (container) => {
    confirm.require({
        message: `Would you like to mark shipment ${container.value?.reference} as arrived at the warehouse?`,
        header: `Mark as Arrived?`,
        icon: 'pi pi-directions',
        rejectLabel: 'Cancel',
        rejectProps: {
            label: 'Cancel',
            severity: 'secondary',
            outlined: true
        },
        acceptProps: {
            label: 'Mark as Arrived',
            severity: 'success'
        },
        accept: () => {
            router.put(
                route("gate-control.inbound-shipments.update-status", container.value?.id),
                {},
                {
                    preserveScroll: true,
                    onSuccess: () => {
                        push.success(`Operation Successfully!`);
                        fetchInboundShipments(currentPage.value);
                    },
                    onError: () => {
                        push.error("Something went to wrong!");
                    },
                }
            );
        },
        reject: () => {
        }
    });
};

const closeModal = () => {
    showConfirmShipmentModal.value = false;
    inboundShipment.value = null;
};

const exportCSV = () => {
    dt.value.exportCSV();
};
</script>
<template>
    <AppLayout title="Inbound Shipments">
        <template #header>Inbound Shipments</template>

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
                        <DatePicker v-model="etdStartDate" class="w-full" date-format="yy-mm-dd" input-id="etd-start-date"/>
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
                    <ContextMenu ref="cm" :model="menuModel" @hide="selectedContainer.length < 1"/>
                    <DataTable
                        ref="dt"
                        v-model:contextMenuSelection="selectedContainer"
                        v-model:filters="filters"
                        :globalFilterFields="['reference', 'bl_number', 'awb_number', 'container_number', 'seal_number', 'vessel_name', 'voyage_number', 'shipping_line']"
                        :loading="loading"
                        :rows="perPage"
                        :rowsPerPageOptions="[5, 10, 20, 50, 100]"
                        :totalRecords="totalRecords"
                        :value="containers"
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
                                    Inbound Shipments
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

                        <template #empty>No inbound shipments found.</template>

                        <template #loading>Loading inbound shipments data. Please wait.</template>

                        <Column field="container_type" header="Container Type" sortable>
                            <template #body="slotProps">
                                <Tag :severity="resolveContainerType(slotProps.data)"
                                     :value="slotProps.data.container_type" class="text-sm"></Tag>

                                <img v-if="slotProps.data?.cargo_type === 'Sea Cargo'" :src="LongVehicle" alt="image"
                                     class="w-2/4 block xl:block rounded"/>

                                <img v-if="slotProps.data?.cargo_type === 'Air Cargo'" :src="CargoPlane" alt="image"
                                     class="w-2/4 block xl:block rounded"/>
                            </template>
                            <template #filter="{ filterModel }">
                                <Select v-model="filterModel.value" :options="containerTypes" :showClear="true"
                                        placeholder="Select One" style="min-width: 12rem" />
                            </template>
                        </Column>

                        <Column field="reference" header="Reference" sortable>
                            <template #body="slotProps">
                                <div class="font-medium">
                                    {{slotProps.data.reference}}
                                </div>
                                <span class="text-neutral-500">{{slotProps.data.container_number}}</span>
                            </template>
                        </Column>

                        <Column field="bl_number" header="BL Number" sortable></Column>

                        <Column field="awb_number" header="AWB Number" hidden sortable></Column>

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

                        <Column field="estimated_time_of_arrival" header="ETA"></Column>

                        <Column field="estimated_time_of_departure" header="ETD"></Column>

                        <Column field="status" header="Status">
                            <template #body="slotProps">
                                <div class="float-right">
                                    <Tag :icon="resolveContainerStatus(slotProps.data).icon"
                                         :severity="resolveContainerStatus(slotProps.data).color"
                                         :value="slotProps.data.status" class="text-sm uppercase"></Tag>
                                    <div class="mt-1 italic text-neutral-500 text-right">
                                        {{slotProps.data?.arrived_at_primary_warehouse}}
                                    </div>
                                </div>
                            </template>

                            <template #filter="{ filterModel }">
                                <Select v-model="filterModel.value" :options="['REACHED DESTINATION', 'IN TRANSIT', 'ARRIVED PRIMARY WAREHOUSE']" :showClear="true"
                                        placeholder="Select One" style="min-width: 12rem"/>
                            </template>
                        </Column>

                        <template #footer> In total there are {{ containers ? totalRecords : 0 }} inbound shipments. </template>
                    </DataTable>
                </template>
            </Card>
        </div>
    </AppLayout>

    <LoadedShipmentDetailDialog :air-container-options="airContainerOptions" :container="inboundShipment" :container-status="containerStatus" :sea-container-options="seaContainerOptions" :show="showConfirmShipmentModal"
           @close="closeModal"
           @update:show="showConfirmShipmentModal = $event" />
</template>

<style>
.p-tag-icon {
    font-size: 15px;
    margin-right: 3px;
}
</style>
