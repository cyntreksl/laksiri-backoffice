<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import {onMounted, ref, watch} from "vue";
import {useConfirm} from "primevue/useconfirm";
import moment from "moment";
import {FilterMatchMode} from "@primevue/core/api";
import {router, usePage} from "@inertiajs/vue3";
import axios from "axios";
import {debounce} from "lodash";
import {push} from "notivue";
import FloatLabel from "primevue/floatlabel";
import ContextMenu from "primevue/contextmenu";
import DataTable from "primevue/datatable";
import InputIcon from "primevue/inputicon";
import Tag from "primevue/tag";
import InputText from "primevue/inputtext";
import DatePicker from "primevue/datepicker";
import Column from "primevue/column";
import Button from "primevue/button";
import IconField from "primevue/iconfield";
import Panel from "primevue/panel";
import Card from "primevue/card";
import Select from "primevue/select";
import HBLDetailModal from "@/Pages/Common/Dialog/HBL/Index.vue";

const props = defineProps({
    hblTypes: {
        type: Array,
        default: () => [],
    },
});

const baseUrl = ref("/bonded-warehouse-list");
const loading = ref(true);
const hbls = ref([]);
const totalRecords = ref(0);
const perPage = ref(10);
const currentPage = ref(1);
const showConfirmViewHBLModal = ref(false);
const cm = ref();
const selectedHBL = ref(null);
const selectedHBLID = ref(null);
const confirm = useConfirm();
const dt = ref();
const fromDate = ref(moment(new Date()).subtract(7, "days").toISOString().split("T")[0]);
const toDate = ref(moment(new Date()).toISOString().split("T")[0]);
const hblTypes = ref(['UPB', 'Door to Door', 'Gift']);

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    hbl_type: { value: null, matchMode: FilterMatchMode.EQUALS },
});

const menuModel = ref([
    {
        label: "View",
        icon: "pi pi-fw pi-search",
        command: () => confirmViewHBL(selectedHBL),
        disabled: !usePage().props.user.permissions.includes('bonded.show'),
    },
    {
        label: "Mark As Short Loading",
        icon: "pi pi-fw pi-flag",
        command: () => confirmShortLoading(selectedHBL.value.id),
        disabled: !usePage().props.user.permissions.includes('bonded.mark as short loading'),
    },
]);

const fetchHBLs = async (page = 1, search = "", sortField = 'created_at', sortOrder = 0) => {
    loading.value = true;
    try {
        const response = await axios.get(baseUrl.value, {
            params: {
                page,
                per_page: perPage.value,
                search,
                deliveryType: filters.value.hbl_type.value || "",
                sort_field: sortField,
                sort_order: sortOrder === 1 ? "asc" : "desc",
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

const debouncedFetchHBLs = debounce((searchValue) => {
    fetchHBLs(1, searchValue);
}, 1000);

watch(() => filters.value.global.value, (newValue) => {
    if (newValue !== null) {
        debouncedFetchHBLs(newValue);
    }
});

watch(() => filters.value.hbl_type.value, (newValue) => {
    fetchHBLs(1, filters.value.global.value);
});

watch(() => fromDate.value, (newValue) => {
    fetchHBLs(1, filters.value.global.value);
});

watch(() => toDate.value, (newValue) => {
    fetchHBLs(1, filters.value.global.value);
});

const onPageChange = (event) => {
    perPage.value = event.rows;
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
        hbl_type: { value: null, matchMode: FilterMatchMode.EQUALS },
    };
    fromDate.value = moment(new Date()).subtract(7, "days").toISOString().split("T")[0];
    toDate.value = moment(new Date()).toISOString().split("T")[0];
    fetchHBLs(currentPage.value);
};

const confirmShortLoading = (id) => {
    confirm.require({
        message: 'Would you like to mark as short load this hbl record?',
        header: 'Mark as Short Load?',
        icon: 'pi pi-info-circle',
        rejectLabel: 'Cancel',
        rejectProps: {
            label: 'Cancel',
            severity: 'secondary',
            outlined: true
        },
        acceptProps: {
            label: 'Mark as Short Load',
            severity: 'warn'
        },
        accept: () => {
            router.get(route("arrival.hbls.mark-as-short-loading", id), {
                preserveScroll: true,
                onSuccess: () => {
                    push.success("Mark As a Short Loaded");
                    fetchHBLs(currentPage.value)
                },
            });
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
    <AppLayout title="Bonded Warehouse">
        <template #header>Bonded Warehouse</template>

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
                    <ContextMenu ref="cm" :model="menuModel" @hide="selectedHBL = null" />
                    <DataTable
                        ref="dt"
                        v-model:contextMenuSelection="selectedHBL"
                        v-model:filters="filters"
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
                                    Bonded Warehouse
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

                        <template #empty> No bonded warehouse found. </template>

                        <template #loading> Loading bonded warehouse data. Please wait.</template>

                        <Column field="hbl_number" header="HBL" sortable>
                            <template #body="slotProps">
                                <span class="font-medium">{{ slotProps.data.hbl_number ?? slotProps.data.hbl }}</span>
                                <br v-if="slotProps.data.is_short_load">
                                <Tag v-if="slotProps.data.is_short_load" :severity="`warn`" :value="`Short Loaded`" icon="pi pi-exclamation-triangle" size="small"></Tag>
                            </template>
                        </Column>

                        <Column field="hbl_name" header="Name"></Column>

                        <Column field="consignee_name" header="Consignee"></Column>

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
                                    {{ slotProps.data.quantity }}
                                </div>
                            </template>
                        </Column>

                        <Column field="created_at" header="Created At" sortable></Column>

                        <Column field="hbl_type" header="HBL Type" sortable>
                            <template #body="slotProps">
                                <Tag :severity="resolveHBLType(slotProps.data)" :value="slotProps.data.hbl_type"></Tag>
                            </template>
                            <template #filter="{ filterModel, filterCallback }">
                                <Select v-model="filterModel.value" :options="hblTypes" :showClear="true" placeholder="Select One" style="min-width: 12rem" />
                            </template>
                        </Column>

                        <template #footer> In total there are {{ hbls ? totalRecords : 0 }} bonded warehouse data.</template>
                    </DataTable>
                </template>
            </Card>
        </div>
    </AppLayout>

    <HBLDetailModal
        :hbl-id="selectedHBLID"
        :show="showConfirmViewHBLModal"
        @close="closeModal"
        @update:show="showConfirmViewHBLModal = $event"
    />
</template>
