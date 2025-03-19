<script setup>
import {computed, onMounted, reactive, ref, watch} from "vue";
import {Link, router, usePage} from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import FloatLabel from 'primevue/floatlabel';
import {useConfirm} from "primevue/useconfirm";
import Select from 'primevue/select';
import Chip from 'primevue/chip';
import InputText from 'primevue/inputtext';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import Card from "primevue/card";
import ContextMenu from 'primevue/contextmenu';
import Panel from 'primevue/panel';
import DatePicker from 'primevue/datepicker';
import Button from "primevue/button";
import Tag from 'primevue/tag';
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import axios from "axios";
import {FilterMatchMode} from '@primevue/core/api';
import moment from "moment";
import {debounce} from "lodash";
import PrimaryButton from "@/Components/PrimaryButton.vue";

const props = defineProps({
    users: {
        type: Object,
        default: () => {
        },
    },
    hbls: {
        type: Object,
        default: () => {
        },
    },
    paymentStatus: {
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

const baseUrl = ref("/hbl-list");
const loading = ref(true);
const hbls = ref([]);
const totalRecords = ref(0);
const perPage = ref(10);
const currentPage = ref(1);
const showConfirmViewHBLModal = ref(false);
const cm = ref();
const selectedHBL = ref(null);
const selectedHBLs = ref([]);
const selectedHBLID = ref(null);
const showConfirmViewPickupModal = ref(false);
const confirm = useConfirm();
const showAssignDriverDialog = ref(false);
const dt = ref();
const fromDate = ref(moment(new Date()).subtract(24, "months").toISOString().split("T")[0]);
const toDate = ref(moment(new Date()).toISOString().split("T")[0]);
const warehouses = ref(['COLOMBO', 'NINTAVUR',]);
const hblTypes = ref(['UPB', 'Door to Door', 'Gift']);
const cargoTypes = ref(['Sea Cargo', 'Air Cargo']);

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    reference: { value: null, matchMode: FilterMatchMode.CONTAINS },
    warehouse: { value: null, matchMode: FilterMatchMode.EQUALS },
    hbl_type: { value: null, matchMode: FilterMatchMode.EQUALS },
    cargo_type: { value: null, matchMode: FilterMatchMode.EQUALS },
    is_hold: { value: null, matchMode: FilterMatchMode.EQUALS },
});

const menuModel = ref([
    {label: 'View', icon: 'pi pi-fw pi-search', command: () => confirmViewHBL(selectedHBL)},
    {label: 'Call Flag', icon: 'pi pi-fw pi-flag', command: () => confirmViewHBL(selectedHBL)},
    {label: 'Edit', icon: 'pi pi-fw pi-pencil', command: () => router.visit(route("hbls.edit", selectedHBL.value.id))},
    {label: 'Hold', icon: 'pi pi-fw pi-pause-circle', command: () => confirmViewHBL(selectedHBL)},
    {label: 'Download', icon: 'pi pi-fw pi-download', url: () => route("hbls.download", selectedHBL.value.id)},
    {label: 'Invoice', icon: 'pi pi-fw pi-receipt', url: () => route("hbls.download.invoice", selectedHBL.value.id)},
    {label: 'Barcode', icon: 'pi pi-fw pi-barcode', url: () => route("hbls.download.barcode", selectedHBL.value.id)},
    {label: 'Delete', icon: 'pi pi-fw pi-times', command: () => confirmHBLDelete(selectedHBL)},
]);

const fetchHBLs = async (page = 1, search = "", sortField = 'id', sortOrder = 1) => {
    loading.value = true;
    try {
        const response = await axios.get(baseUrl.value, {
            params: {
                page,
                per_page: perPage.value,
                search,
                warehouse: filters.value.warehouse.value || "",
                hblType: filters.value.hbl_type.value || "",
                cargoMode: filters.value.cargo_type.value || "",
                isHold: filters.value.is_hold.value || false,
                sort_field: sortField,
                sort_order: sortOrder === 1 ? "asc" : "desc",
            }
        });
        hbls.value = response.data.data;
        totalRecords.value = response.data.meta.total; // Correct total count
        currentPage.value = response.data.meta.current_page; // Correct current page
    } catch (error) {
        console.error("Error fetching HBLs:", error);
    } finally {
        loading.value = false;
    }
};

const debouncedFetchHBLs = debounce((searchValue) => {
    fetchHBLs(1, searchValue);
}, 1000);
watch(() => filters.value.global.value, (newValue) => {
    if (newValue !== null) {
        debouncedFetchHBLs(newValue);
    }
});
watch(() => filters.value.warehouse.value, (newValue) => {
    fetchHBLs(1, filters.value.global.value);
});
watch(() => filters.value.hbl_type.value, (newValue) => {
    fetchHBLs(1, filters.value.global.value);
});
watch(() => filters.value.cargo_type.value, (newValue) => {
    fetchHBLs(1, filters.value.global.value);
});
watch(() => filters.value.is_hold.value, (newValue) => {
    fetchHBLs(1, filters.value.global.value);
});
const onPageChange = (event) => {
    currentPage.value = event.page + 1;
    fetchHBLs(currentPage.value);
};
const onSort = (event) => {
    fetchHBLs(currentPage.value, filters.value.global.value, event.sortField, event.sortOrder);
};
onMounted(() => {
    fetchHBLs();
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
        reference: { value: null, matchMode: FilterMatchMode.CONTAINS },
        warehouse: { value: null, matchMode: FilterMatchMode.EQUALS },
        hbl_type: { value: null, matchMode: FilterMatchMode.EQUALS },
        cargo_type: { value: null, matchMode: FilterMatchMode.EQUALS },
        is_hold: { value: null, matchMode: FilterMatchMode.EQUALS },
    };
    fetchHBLs(currentPage.value);
};
const confirmHBLDelete = (hbl) => {
    selectedHBLID.value = hbl.value.id;
    confirm.require({
        message: 'Would you like to delete this hbl record?',
        header: 'Delete HBL?',
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
            router.delete(route("hbls.destroy", selectedHBLID.value), {
                preserveScroll: true,
                onSuccess: () => {
                    push.success("HBL record Deleted Successfully!");
                    router.visit(route("hbls.index"), {only: ["hbls"]});
                },
                onError: () => {
                    push.error("Something went to wrong!");
                },
            });
            selectedHBLID.value = null;
        },
        reject: () => {
        }
    });
};

const exportCSV = () => {
    dt.value.exportCSV();
};
</script>

<template>
    <AppLayout title="HBL List">
        <template #header>HBL List</template>

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

<!--                    <FloatLabel class="w-full" variant="in">-->
<!--                        <Select v-model="filters.user.value" :options="users" :showClear="true" class="w-full" input-id="user" option-label="name" option-value="id"/>-->
<!--                        <label for="user">Select User</label>-->
<!--                    </FloatLabel>-->

<!--                    <FloatLabel class="w-full" variant="in">-->
<!--                        <Select v-model="filters.zone.value" :options="zones" :showClear="true" class="w-full" input-id="zone" option-label="name" option-value="id" />-->
<!--                        <label for="zone">Select Zone</label>-->
<!--                    </FloatLabel>-->
                </div>
            </Panel>

            <Card class="my-5">
                <template #content>
                    <ContextMenu ref="cm" :model="menuModel" @hide="selectedHBL = null" />
                    <DataTable
                        v-model:contextMenuSelection="selectedHBL"
                        v-model:filters="filters"
                        :globalFilterFields="['reference', 'hbl', 'hbl_name', 'email', 'address', 'contact_number', 'consignee_name', 'consignee_address', 'consignee_contact', 'cargo_type', 'hbl_type', 'warehouse', 'status', 'hbl_number']"
                        :loading="loading"
                        :rows="perPage"
                        :rowsPerPageOptions="[5, 10, 20, 50]"
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
                            <div class="flex justify-between">
                                <Button icon="pi pi-filter-slash" label="Clear" outlined severity="contrast" size="small" type="button" @click="clearFilter()" />
                                <IconField>
                                    <InputIcon>
                                        <i class="pi pi-search" />
                                    </InputIcon>
                                    <InputText v-model="filters.global.value" placeholder="Keyword Search" size="small" />
                                </IconField>
                            </div>
                        </template>
                        <template #empty> No hbls found. </template>
                        <template #loading> Loading hbl data. Please wait. </template>
                        <Column field="reference" header="Reference" hidden sortable></Column>
                        <Column field="hbl" header="HBL" sortable></Column>
                        <Column field="cargo_type" header="Cargo Type" sortable>
                            <template #body="slotProps">
                                <Tag :icon="resolveCargoType(slotProps.data).icon" :severity="resolveCargoType(slotProps.data).color" :value="slotProps.data.cargo_type" class="text-sm"></Tag>
                            </template>
                            <template #filter="{ filterModel, filterCallback }">
                                <Select v-model="filterModel.value" :options="cargoTypes" :showClear="true" placeholder="Select One" style="min-width: 12rem" @change="filterCallback()">
                                    <template #option="slotProps">
                                        <Tag :value="slotProps.option" />
                                    </template>
                                </Select>
                            </template>
                        </Column>
                        <Column field="hbl_name" header="HBL Name">
                            <template #body="slotProps">
                                <div>{{ slotProps.data.hbl_name }}</div>
                                <div class="text-gray-500 text-sm">{{slotProps.data.email}}</div>
                                <div class="text-gray-500 text-sm">{{slotProps.data.contact_number}}</div>
                            </template>
                        </Column>
                        <Column field="address" header="Address"></Column>
                        <Column field="warehouse" header="Warehouse" sortable>
                            <template #body="slotProps">
                                <Tag :severity="resolveWarehouse(slotProps.data)" :value="slotProps.data.warehouse.toUpperCase()"></Tag>
                            </template>
                            <template #filter="{ filterModel, filterCallback }">
                                <Select v-model="filterModel.value" :options="warehouses" :showClear="true" placeholder="Select One" style="min-width: 12rem" @change="filterCallback()">
                                    <template #option="slotProps">
                                        <Tag :value="slotProps.option" />
                                    </template>
                                </Select>
                            </template>
                        </Column>
                        <Column field="consignee_name" header="Consignee">
                            <template #body="slotProps">
                                <div>{{ slotProps.data.hbl_name }}</div>
                                <div class="text-gray-500 text-sm">{{slotProps.data.consignee_email}}</div>
                                <div class="text-gray-500 text-sm">{{slotProps.data.consignee_contact}}</div>
                            </template>
                        </Column>
                        <Column field="consignee_address" header="Consignee Address"></Column>
                        <Column field="hbl_type" header="HBL Type" sortable>
                            <template #body="slotProps">
                                <Tag :severity="resolveHBLType(slotProps.data)" :value="slotProps.data.hbl_type"></Tag>
                            </template>
                            <template #filter="{ filterModel, filterCallback }">
                                <Select v-model="filterModel.value" :options="hblTypes" :showClear="true" placeholder="Select One" style="min-width: 12rem" @change="filterCallback()">
                                    <template #option="slotProps">
                                        <Tag :value="slotProps.option" />
                                    </template>
                                </Select>
                            </template>
                        </Column>
                        <Column field="status" header="Status" hidden></Column>
                        <Column field="is_hold" header="Hold">
                            <template #body="{ data }">
                                <i :class="{ 'pi-pause-circle text-yellow-500': data.is_hold, 'pi-play-circle text-green-400': !data.is_hold }" class="pi"></i>
                            </template>
                            <template #filter="{ filterModel, filterCallback }">
                                <Checkbox v-model="filterModel.value" :indeterminate="filterModel.value === null" binary @change="filterCallback()" />
                            </template>
                        </Column>
                        <Column field="hbl_number" header="HBL Number" hidden sortable></Column>
                        <Column field="is_released" header="Released" hidden></Column>
                    </DataTable>
                </template>
            </Card>
        </div>
    </AppLayout>
</template>

<style>
.p-tag-icon {
    font-size: 15px;
}
</style>
