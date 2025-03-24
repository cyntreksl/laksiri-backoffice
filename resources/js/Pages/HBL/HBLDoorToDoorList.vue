<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import {computed, onMounted, ref, watch} from "vue";
import {push} from "notivue";
import moment from "moment";
import InputIcon from "primevue/inputicon";
import InputText from "primevue/inputtext";
import DataTable from "primevue/datatable";
import Checkbox from "primevue/checkbox";
import Panel from "primevue/panel";
import Card from "primevue/card";
import Button from "primevue/button";
import Column from "primevue/column";
import Select from "primevue/select";
import Tag from "primevue/tag";
import IconField from "primevue/iconfield";
import ContextMenu from "primevue/contextmenu";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import {router, useForm, usePage} from "@inertiajs/vue3";
import DatePicker from "primevue/datepicker";
import FloatLabel from "primevue/floatlabel";
import HBLDetailModal from "@/Pages/Common/HBLDetailModal.vue";
import CallFlagModal from "@/Pages/HBL/Partials/CallFlagModal.vue";
import {useConfirm} from "primevue/useconfirm";
import {FilterMatchMode} from "@primevue/core/api";
import axios from "axios";
import {debounce} from "lodash";

const props = defineProps({
    users: {
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

const baseUrl = ref("/hbl-door-to-door-list");
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
const fromDate = ref(moment(new Date()).subtract(1, "month").toISOString().split("T")[0]);
const toDate = ref(moment(new Date()).toISOString().split("T")[0]);
const warehouses = ref(['COLOMBO', 'NINTAVUR',]);
const hblTypes = ref(['UPB', 'Door to Door', 'Gift']);
const cargoTypes = ref(['Sea Cargo', 'Air Cargo']);
const showConfirmViewCallFlagModal = ref(false);
const hblName = ref("");
const selectedHBLs = ref([]);
const isCreateMHBL = ref(false);

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    warehouse: { value: null, matchMode: FilterMatchMode.EQUALS },
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
        disabled: !usePage().props.user.permissions.includes("hbls.show"),
    },
    {
        label: "Call Flag",
        icon: "pi pi-fw pi-flag",
        command: () => confirmViewCallFlagModal(selectedHBL),
        disabled: !usePage().props.user.permissions.includes("hbls.edit"),
    },
    {
        label: "Edit",
        icon: "pi pi-fw pi-pencil",
        command: () => router.visit(route("hbls.edit", selectedHBL.value.id)),
        disabled: !usePage().props.user.permissions.includes("hbls.edit"),
    },
    {
        label: computed(() => (selectedHBL.value?.is_hold ? 'Release' : 'Hold')),
        icon: computed(() => (selectedHBL.value?.is_hold ? 'pi pi-fw pi-play-circle' : 'pi pi-fw pi-pause-circle')) ,
        command: () => confirmHBLHold(selectedHBL),
        disabled: !usePage().props.user.permissions.includes("hbls.hold and release"),
    },
    {
        label: "Download",
        icon: "pi pi-fw pi-download",
        url: () => route("hbls.download", selectedHBL.value.id),
        disabled: !usePage().props.user.permissions.includes("hbls.download pdf"),
    },
    {
        label: "Invoice",
        icon: "pi pi-fw pi-receipt",
        url: () => route("hbls.download.invoice", selectedHBL.value.id),
        disabled: !usePage().props.user.permissions.includes("hbls.download invoice"),
    },
    {
        label: "Barcode",
        icon: "pi pi-fw pi-barcode",
        url: () => route("hbls.download.barcode", selectedHBL.value.id),
        disabled: !usePage().props.user.permissions.includes("hbls.download barcode"),
    },
    {
        label: "Delete",
        icon: "pi pi-fw pi-times",
        command: () => confirmHBLDelete(selectedHBL),
        disabled: !usePage().props.user.permissions.includes("hbls.delete"),
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
                warehouse: filters.value.warehouse.value || "",
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

watch(() => filters.value.cargo_type.value, (newValue) => {
    fetchHBLs(1, filters.value.global.value);
});

watch(() => filters.value.is_hold.value, (newValue) => {
    fetchHBLs(1, filters.value.global.value);
});

watch(() => filters.value.user.value, (newValue) => {
    fetchHBLs(1, filters.value.global.value);
});

watch(() => filters.value.payments.value, (newValue) => {
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
        warehouse: { value: null, matchMode: FilterMatchMode.EQUALS },
        cargo_type: { value: null, matchMode: FilterMatchMode.EQUALS },
        is_hold: { value: null, matchMode: FilterMatchMode.EQUALS },
        user: {value: null, matchMode: FilterMatchMode.EQUALS},
        payments: {value: null, matchMode: FilterMatchMode.EQUALS},
    };
    fromDate.value = moment(new Date()).subtract(24, "months").toISOString().split("T")[0];
    toDate.value = moment(new Date()).toISOString().split("T")[0];
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
                    fetchHBLs(currentPage.value);
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
                        fetchHBLs(currentPage.value);
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

const confirmViewCallFlagModal = (hbl) => {
    selectedHBLID.value = hbl.value.id;
    hblName.value = hbl.value.hbl_name;
    showConfirmViewCallFlagModal.value = true;
};

const closeCallFlagModal = () => {
    showConfirmViewCallFlagModal.value = false;
    selectedHBLID.value = null;
    hblName.value = "";
};

const form = useForm({
    hbls: [],
    cargo_type: '',
    hbl_type: '',
    warehouse: '',
});

watch(
    () => selectedHBLs.value.length,
    (newCount) => {
        const cargo_type = selectedHBLs.value[0].cargo_type;

        const hbl_type = selectedHBLs.value[0].hbl_type;

        const warehouse = selectedHBLs.value[0].warehouse;

        const checkEqualCargoMode = selectedHBLs.value.every((item, index) => {
            return item.cargo_type === cargo_type;
        });

        const checkEqualHBLType = selectedHBLs.value.every((item, index) => {
            return item.hbl_type === hbl_type;
        });

        const checkEqualWarehouse = selectedHBLs.value.every((item, index) => {
            return item.warehouse === warehouse;
        });

        isCreateMHBL.value = !!(checkEqualCargoMode && checkEqualHBLType && checkEqualWarehouse);

        form.cargo_type = cargo_type;
        form.hbl_type = hbl_type;
        form.warehouse = warehouse;
    }
);

const createMHBL = () => {
    form.hbls = selectedHBLs.value.map((item) => item.id);

    form.get(route("mhbls.create"), {
        onError: () => {
            push.error('Something went to wrong!');
        },
        preserveScroll: true,
        preserveState: true,
    });
};

const exportCSV = () => {
    dt.value.exportCSV();
};
</script>
<template>
    <AppLayout title="Door to Door HBLs">
        <template #header>Door to Door HBLs</template>

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
                        <Select v-model="filters.payments.value" :options="paymentStatus" :showClear="true" class="w-full" input-id="payment-status" />
                        <label for="payment-status">Payment Status</label>
                    </FloatLabel>

                    <FloatLabel class="w-full" variant="in">
                        <Select v-model="filters.user.value" :options="users" :showClear="true" class="w-full" input-id="user" option-label="name" option-value="id" />
                        <label for="user">Created By</label>
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
                                    Door to Door HBLs
                                </div>
                                <div>
                                    <PrimaryButton :disabled="selectedHBLs.length === 0 || !isCreateMHBL" class="w-full" @click="createMHBL">Create MHBL</PrimaryButton>
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

                        <template #empty> No hbls found. </template>

                        <template #loading> Loading hbl data. Please wait.</template>

                        <Column headerStyle="width: 3rem">
                            <template #body="{ data }">
                                <div class="text-center">
                                    <template v-if="data.is_hold === 0 && data.is_released === 0 && data.mhbl === null && data.status !== 'reached'">
                                        <Checkbox v-model="selectedHBLs" :value="data" />
                                    </template>

                                    <template v-else>
                                        <Button v-tooltip="`MHBL: ${data.mhbl}`" aria-label="MHBL" icon="pi pi-info-circle" rounded severity="warn" size="small" variant="text" />
                                    </template>
                                </div>
                            </template>
                        </Column>

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

                        <Column field="hbl_name" header="HBL Name">
                            <template #body="slotProps">
                                <a :href="`hbls/get-hbls-by-user/${slotProps.data.hbl_name}`"
                                   class="hover:underline" target="_blank">
                                    <i class="pi pi-external-link mr-1" style="font-size: 0.75rem"></i>
                                    {{ slotProps.data.hbl_name }}
                                </a>
                                <div class="text-gray-500 text-sm">{{slotProps.data.email}}</div>
                                <a :href="`hbls/get-hbls-by-user/${slotProps.data.contact_number}`"
                                   class="text-gray-500 hover:underline text-sm" target="_blank">
                                    <i class="pi pi-external-link mr-1" style="font-size: 0.75rem"></i>
                                    {{ slotProps.data.contact_number }}
                                </a>
                            </template>
                        </Column>

                        <Column field="address" header="Address"></Column>

                        <Column field="warehouse" header="Warehouse" sortable>
                            <template #body="slotProps">
                                <Tag :severity="resolveWarehouse(slotProps.data)" :value="slotProps.data.warehouse.toUpperCase()"></Tag>
                            </template>
                            <template #filter="{ filterModel, filterCallback }">
                                <Select v-model="filterModel.value" :options="warehouses" :showClear="true" placeholder="Select One" style="min-width: 12rem" />
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

                        <Column field="status" header="Status" hidden></Column>

                        <Column field="is_hold" header="Hold">
                            <template #body="{ data }">
                                <i :class="{ 'pi-pause-circle text-yellow-500': data.is_hold, 'pi-play-circle text-green-400': !data.is_hold }" class="pi"></i>
                            </template>
                            <template #filter="{ filterModel, filterCallback }">
                                <Checkbox v-model="filterModel.value" :indeterminate="filterModel.value === null" binary />
                            </template>
                        </Column>

                        <Column field="hbl_number" header="HBL Number" hidden sortable></Column>

                        <Column field="is_released" header="Released" hidden></Column>

                        <template #footer> In total there are {{ hbls ? totalRecords : 0 }} HBLs. </template>
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

    <CallFlagModal
        :caller-name="hblName"
        :hbl-id="selectedHBLID"
        :visible="showConfirmViewCallFlagModal"
        @close="closeCallFlagModal"
        @update:visible="showConfirmViewCallFlagModal = $event"/>
</template>
