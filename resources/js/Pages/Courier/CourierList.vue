<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import {onMounted, ref, watch} from "vue";
import {Link, router, useForm, usePage} from "@inertiajs/vue3";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import {useConfirm} from "primevue/useconfirm";
import moment from "moment";
import {FilterMatchMode} from "@primevue/core/api";
import axios from "axios";
import {debounce} from "lodash";
import {push} from "notivue";
import FloatLabel from "primevue/floatlabel";
import Card from "primevue/card";
import InputText from "primevue/inputtext";
import ContextMenu from "primevue/contextmenu";
import Panel from "primevue/panel";
import Button from "primevue/button";
import IconField from "primevue/iconfield";
import Column from "primevue/column";
import InputIcon from "primevue/inputicon";
import DatePicker from "primevue/datepicker";
import DataTable from "primevue/datatable";
import Select from "primevue/select";
import Tag from "primevue/tag";

const baseUrl = ref("/couriers/list");
const loading = ref(true);
const couriers = ref([]);
const selectedCourier = ref(null);
const selectedCouriers = ref([]);
const selectedCourierID = ref(null);
const totalRecords = ref(0);
const perPage = ref(10);
const currentPage = ref(1);
const hblTypes = ref(['UPB', 'Door to Door', 'Gift']);
const cargoTypes = ref(['Sea Cargo', 'Air Cargo']);
const status = ref(['pending', 'on courier', 'delivered']);
const dt = ref();
const cm = ref();
const confirm = useConfirm();
const fromDate = ref(moment(new Date()).subtract(12, "months").toISOString().split("T")[0]);
const toDate = ref(moment(new Date()).toISOString().split("T")[0]);

const filters = ref({
    global: {value: null, matchMode: FilterMatchMode.CONTAINS},
    hbl_type: { value: null, matchMode: FilterMatchMode.EQUALS },
    cargo_type: { value: null, matchMode: FilterMatchMode.EQUALS },
    status: {value: null, matchMode: FilterMatchMode.EQUALS},
});

const menuModel = ref([
    {
        label: "Edit",
        icon: "pi pi-fw pi-pencil",
        command: () => router.visit(route("couriers.edit", selectedCourier.value.id)),
        disabled: !usePage().props.user.permissions.includes("courier.edit"),
    },
    {
        label: "Delete",
        icon: "pi pi-fw pi-times",
        command: () => confirmCourierDelete(selectedCourier),
        disabled: !usePage().props.user.permissions.includes("courier.delete"),
    },
]);

const fetchCouriers = async (page = 1, search = "", sortField = 'created_at', sortOrder = 0) => {
    loading.value = true;
    try {
        const response = await axios.get(baseUrl.value, {
            params: {
                page,
                per_page: perPage.value,
                search,
                sort_field: sortField,
                sort_order: sortOrder === 1 ? "asc" : "desc",
                fromDate: moment(fromDate.value).format("YYYY-MM-DD"),
                toDate: moment(toDate.value).format("YYYY-MM-DD"),
                cargoMode: filters.value.cargo_type.value || "",
                deliveryType: filters.value.hbl_type.value || "",
                status: filters.value.status.value || "",
            }
        });
        couriers.value = response.data.data;
        totalRecords.value = response.data.meta.total;
        currentPage.value = response.data.meta.current_page;
    } catch (error) {
        console.error("Error fetching couriers:", error);
    } finally {
        loading.value = false;
    }
};

const debouncedFetchCouriers = debounce((searchValue) => {
    fetchCouriers(1, searchValue);
}, 1000);

watch(() => filters.value.global.value, (newValue) => {
    if (newValue !== null) {
        debouncedFetchCouriers(newValue);
    }
});

watch(() => fromDate.value, (newValue) => {
    fetchCouriers(1, filters.value.global.value);
});

watch(() => toDate.value, (newValue) => {
    fetchCouriers(1, filters.value.global.value);
});

watch(() => filters.value.status.value, (newValue) => {
    fetchCouriers(1, filters.value.global.value);
});

watch(() => filters.value.hbl_type.value, (newValue) => {
    fetchCouriers(1, filters.value.global.value);
});

watch(() => filters.value.cargo_type.value, (newValue) => {
    fetchCouriers(1, filters.value.global.value);
});

const onPageChange = (event) => {
    perPage.value = event.rows;
    currentPage.value = event.page + 1;
    fetchCouriers(currentPage.value);
};

const onSort = (event) => {
    fetchCouriers(currentPage.value, filters.value.global.value, event.sortField, event.sortOrder);
};

const onRowContextMenu = (event) => {
    cm.value.show(event.originalEvent);
};

onMounted(() => {
    fetchCouriers();
});

const clearFilter = () => {
    filters.value = {
        global: {value: null, matchMode: FilterMatchMode.CONTAINS},
        hbl_type: { value: null, matchMode: FilterMatchMode.EQUALS },
        cargo_type: { value: null, matchMode: FilterMatchMode.EQUALS },
        status: {value: null, matchMode: FilterMatchMode.EQUALS},
    };
    fromDate.value = moment(new Date()).subtract(12, "months").toISOString().split("T")[0];
    toDate.value = moment(new Date()).toISOString().split("T")[0];
    fetchCouriers(currentPage.value);
};

const exportCSV = () => {
    dt.value.exportCSV();
};

const confirmCourierDelete = (courier) => {
    selectedCourierID.value = courier.value.id;
    confirm.require({
        message: 'Would you like to delete this courier record?',
        header: 'Delete Courier?',
        icon: 'pi pi-info-circle',
        rejectLabel: 'Cancel',
        rejectProps: {
            label: 'Cancel',
            severity: 'secondary',
            outlined: true
        },
        acceptProps: {
            label: 'Delete',
            severity: 'danger'
        },
        accept: () => {
            router.delete(route("couriers.destroy", selectedCourierID.value), {
                preserveScroll: true,
                onSuccess: () => {
                    push.success("Courier Deleted Successfully!");
                    fetchCouriers(currentPage.value);
                },
                onError: () => {
                    push.error("Something went to wrong!");
                },
            });
            selectedCourierID.value = null;
        },
        reject: () => {
        }
    });
}

const resolveCargoType = (cargo_type) => {
    switch (cargo_type) {
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

const resolveHBLType = (hbl_type) => {
    switch (hbl_type) {
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

const resolveStatus = (status) => {
    switch (status) {
        case 'pending':
            return 'secondary';
        case 'on courier':
            return 'warn';
        case 'delivered':
            return 'success';
        default:
            return null;
    }
};

const form = useForm({
    couriers: [],
    status: '',
});

const handleChangeStatus = () => {
    form.couriers = selectedCouriers.value.map((item) => item.id);
    form.post(route("couriers.change-status"), {
        onSuccess: () => {
            push.success('Courier status changed');
            fetchCouriers(currentPage.value);
        },
        onError: () => {
            push.error('Something went to wrong!');
        },
        preserveScroll: true,
        preserveState: true,
    });
}
</script>

<template>
    <AppLayout title="Couriers">
        <template #header>Couriers</template>

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
                </div>
            </Panel>

            <Card class="my-5">
                <template #content>
                    <ContextMenu ref="cm" :model="menuModel" @hide="selectedCourier = null" />
                    <DataTable
                        ref="dt"
                        v-model:contextMenuSelection="selectedCourier"
                        v-model:filters="filters"
                        v-model:selection="selectedCouriers"
                        :loading="loading"
                        :rows="perPage"
                        :rowsPerPageOptions="[5, 10, 20, 50, 100]"
                        :totalRecords="totalRecords"
                        :value="couriers"
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
                                    Couriers
                                </div>

                                <div class="flex space-x-2 items-center">
                                    <Link v-if="$page.props.user.permissions.includes('courier.create')" :href="route('couriers.create')">
                                        <Button label="Create Courier" size="small" />
                                    </Link>

                                    <Select v-model="form.status" :disabled="selectedCouriers.length === 0" :options="status" class="w-full md:w-56" placeholder="Select a Status" @change="handleChangeStatus"/>
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

                        <template #empty>No couriers found.</template>

                        <template #loading>Loading couriers data. Please wait.</template>

                        <Column headerStyle="width: 3rem" selectionMode="multiple"></Column>

                        <Column field="courier_number" header="Courier Number" sortable></Column>

                        <Column field="agent.company_name" header="Courier Agent" sortable>
                        </Column>

                        <Column field="cargo_type" header="Cargo Type" sortable>
                            <template #body="slotProps">
                                <Tag :icon="resolveCargoType(slotProps.data.cargo_type).icon" :severity="resolveCargoType(slotProps.data.cargo_type).color" :value="slotProps.data.cargo_type" class="text-sm"></Tag>
                            </template>
                            <template #filter="{ filterModel }">
                                <Select v-model="filterModel.value" :options="cargoTypes" :showClear="true" placeholder="Select One" style="min-width: 12rem" />
                            </template>
                        </Column>

                        <Column field="hbl_type" header="HBL Type" sortable>
                            <template #body="slotProps">
                                <Tag :severity="resolveHBLType(slotProps.data.hbl_type)" :value="slotProps.data.hbl_type"></Tag>
                            </template>
                            <template #filter="{ filterModel, filterCallback }">
                                <Select v-model="filterModel.value" :options="hblTypes" :showClear="true" placeholder="Select One" style="min-width: 12rem" />
                            </template>
                        </Column>

                        <Column field="name" header="Name" sortable>
                            <template #body="slotProps">
                                <div>{{ slotProps.data.name }}</div>
                                <div class="text-gray-500 text-sm">{{slotProps.data.email}}</div>
                                <div class="text-gray-500 text-sm">{{slotProps.data.contact_number}}</div>
                            </template>
                        </Column>

                        <Column field="address" header="Address"></Column>

                        <Column field="nic" header="NIC"></Column>

                        <Column field="iq_number" header="IQ Number"></Column>



                        <Column field="consignee" header="Consignee">
                            <template #body="slotProps">
                                <div>{{ slotProps.data.consignee_name }}</div>
                                <div class="text-gray-500 text-sm">{{slotProps.data.consignee_nic}}</div>
                                <div class="text-gray-500 text-sm">{{slotProps.data.consignee_contact}}</div>
                            </template>
                        </Column>

                        <Column field="consignee_address" header="Consignee Address"></Column>

                        <Column field="consignee_note" header="Consignee Note"></Column>



                        <Column field="courier_agent" header="Courier Agent"></Column>

                        <Column field="status" header="Status" sortable>
                            <template #body="slotProps">
                                <Tag :severity="resolveStatus(slotProps.data.status)" :value="slotProps.data.status.toUpperCase()"></Tag>
                            </template>
                            <template #filter="{ filterModel, filterCallback }">
                                <Select v-model="filterModel.value" :options="status" :showClear="true" placeholder="Select One" style="min-width: 12rem" />
                            </template>
                        </Column>

                        <template #footer> In total there are {{ couriers ? totalRecords : 0 }} couriers. </template>
                    </DataTable>
                </template>
            </Card>
        </div>
    </AppLayout>
</template>
