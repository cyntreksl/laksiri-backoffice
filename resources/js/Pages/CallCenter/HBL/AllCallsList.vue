<script setup>
import {computed, onMounted, ref, watch} from "vue";
import { router, usePage } from "@inertiajs/vue3";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import HBLDetailModal from "@/Pages/Common/Dialog/HBL/Index.vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import Select from "primevue/select";
import InputIcon from "primevue/inputicon";
import FloatLabel from "primevue/floatlabel";
import InputText from "primevue/inputtext";
import Panel from "primevue/panel";
import Button from "primevue/button";
import ContextMenu from "primevue/contextmenu";
import Tag from "primevue/tag";
import IconField from "primevue/iconfield";
import DataTable from "primevue/datatable";
import Card from "primevue/card";
import Column from "primevue/column";
import Checkbox from "primevue/checkbox";
import DatePicker from "primevue/datepicker";
import {useConfirm} from "primevue/useconfirm";
import moment from "moment";
import {FilterMatchMode} from "@primevue/core/api";
import axios from "axios";
import {debounce} from "lodash";
import {push} from "notivue";
import InfoDisplay from "@/Pages/Common/Components/InfoDisplay.vue";
import CallFlagModal from "@/Pages/HBL/Partials/CallFlagModal.vue";
import CallFlagListDialog from "./Components/CallFlagListDialog.vue";
import SimpleOverviewWidget from "@/Components/Widgets/SimpleOverviewWidget.vue";

const props = defineProps({
    users: {
        type: Object,
        default: () => {},
    },
    paymentStatus: {
        type: Object,
        default: () => {},
    },
    warehouses: {
        type: Object,
        default: () => {
        },
    },
});

const loading = ref(true);
const hbls = ref([]);
const allCallFlags = ref([]);
const totalRecords = ref(0);
const perPage = ref(10);
const currentPage = ref(1);
const showConfirmViewHBLModal = ref(false);
const cm = ref();
const selectedHBL = ref(null);
const selectedHBLData = ref(null);
const selectedHBLID = ref(null);
const confirm = useConfirm();
const dt = ref();
const fromDate = ref(moment(new Date()).subtract(24, "months").toISOString().split("T")[0]);
const toDate = ref(moment(new Date()).toISOString().split("T")[0]);
const warehouses = ref(['COLOMBO', 'NINTAVUR',]);
const hblTypes = ref(['UPB', 'Door to Door', 'Gift']);
const cargoTypes = ref(['Sea Cargo', 'Air Cargo']);
const callOutcomes = ref(['contacted', 'no_answer', 'busy', 'appointment_scheduled', 'not_available']);
const showConfirmViewCallFlagModal = ref(false);
const showCallFlagListDialog = ref(false);
const hblName = ref("");

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    warehouse: { value: null, matchMode: FilterMatchMode.EQUALS },
    hbl_type: { value: null, matchMode: FilterMatchMode.EQUALS },
    cargo_type: { value: null, matchMode: FilterMatchMode.EQUALS },
    is_hold: { value: null, matchMode: FilterMatchMode.EQUALS },
    user: {value: null, matchMode: FilterMatchMode.EQUALS},
    payments: {value: null, matchMode: FilterMatchMode.EQUALS},
    call_outcome: {value: null, matchMode: FilterMatchMode.EQUALS},
    agent: {value: null, matchMode: FilterMatchMode.EQUALS},
});

// Summary statistics - will be fetched separately
const summaryStats = ref({
    totalCalls: 0,
    appointments: 0,
    followups: 0,
    noAction: 0
});

const menuModel = ref([
    {
        label: "View",
        icon: "pi pi-fw pi-search",
        command: () => confirmViewHBL(selectedHBL),
        visible: usePage().props.user.permissions.includes("hbls.show"),
    },
    {
        label: "Call Flag",
        icon: "pi pi-fw pi-flag",
        command: () => confirmViewCallFlagModal(selectedHBL),
        visible: usePage().props.user.permissions.includes("hbls.call flag"),
    },
    {
        label: "View Call History",
        icon: "pi pi-fw pi-history",
        command: () => confirmViewCallFlagList(selectedHBL),
        visible: usePage().props.user.permissions.includes("hbls.call flag"),
    },
]);

const fetchSummaryStats = async () => {
    try {
        const response = await axios.get('/call-center/all-calls-data', {
            params: {
                page: 1,
                per_page: 999999, // Get all records for summary
                search: filters.value.global.value || "",
                warehouse: filters.value.warehouse.value || "",
                deliveryType: filters.value.hbl_type.value || "",
                cargoMode: filters.value.cargo_type.value || "",
                isHold: filters.value.is_hold.value || false,
                createdBy: filters.value.user.value || "",
                paymentStatus: filters.value.payments.value || [],
                fromDate: moment(fromDate.value).format("YYYY-MM-DD"),
                toDate: moment(toDate.value).format("YYYY-MM-DD"),
                call_outcome: filters.value.call_outcome.value || "",
                agent: filters.value.agent.value || "",
            }
        });

        const allCalls = response.data.data || [];
        const stats = {
            totalCalls: allCalls.length,
            appointments: 0,
            followups: 0,
            noAction: 0
        };

        allCalls.forEach(call => {
            if (call.call_outcome === 'appointment_scheduled' || call.appointment_date) {
                stats.appointments++;
            } else if (call.followup_date) {
                stats.followups++;
            } else if (!call.call_outcome || call.call_outcome === 'no_answer' || call.call_outcome === 'busy' || call.call_outcome === 'not_available') {
                stats.noAction++;
            }
        });

        summaryStats.value = stats;
    } catch (error) {
        console.error("Error fetching summary stats:", error);
    }
};

const fetchAllCalls = async (page = 1, search = "", sortField = 'created_at', sortOrder = 0) => {
    loading.value = true;
    try {
        const response = await axios.get('/call-center/all-calls-data', {
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
                call_outcome: filters.value.call_outcome.value || "",
                agent: filters.value.agent.value || "",
            }
        });

        allCallFlags.value = response.data.data || [];
        totalRecords.value = response.data.meta?.total || 0;
        currentPage.value = response.data.meta?.current_page || 1;

        // Fetch summary stats when filters change
        if (page === 1) {
            await fetchSummaryStats();
        }
    } catch (error) {
        console.error("Error fetching all calls:", error);
        allCallFlags.value = [];
        totalRecords.value = 0;
    } finally {
        loading.value = false;
    }
};

const debouncedFetchAllCalls = debounce((searchValue) => {
    fetchAllCalls(1, searchValue);
}, 1000);

watch(() => filters.value.global.value, () => {
    if (filters.value.global.value !== null) {
        debouncedFetchAllCalls(filters.value.global.value);
    }
});

watch(() => filters.value.warehouse.value, () => {
    fetchAllCalls(1, filters.value.global.value);
});

watch(() => filters.value.hbl_type.value, () => {
    fetchAllCalls(1, filters.value.global.value);
});

watch(() => filters.value.cargo_type.value, () => {
    fetchAllCalls(1, filters.value.global.value);
});

watch(() => filters.value.is_hold.value, () => {
    fetchAllCalls(1, filters.value.global.value);
});

watch(() => filters.value.user.value, () => {
    fetchAllCalls(1, filters.value.global.value);
});

watch(() => filters.value.payments.value, () => {
    fetchAllCalls(1, filters.value.global.value);
});

watch(() => filters.value.call_outcome.value, () => {
    fetchAllCalls(1, filters.value.global.value);
});

watch(() => filters.value.agent.value, () => {
    fetchAllCalls(1, filters.value.global.value);
});

watch(() => fromDate.value, () => {
    fetchAllCalls(1, filters.value.global.value);
});

watch(() => toDate.value, () => {
    fetchAllCalls(1, filters.value.global.value);
});

const onPageChange = (event) => {
    perPage.value = event.rows;
    currentPage.value = event.page + 1;
    fetchAllCalls(currentPage.value, filters.value.global.value);
};

const onSort = (event) => {
    fetchAllCalls(currentPage.value, filters.value.global.value, event.sortField, event.sortOrder);
};

onMounted(() => {
    fetchAllCalls();
});

const resolveHBLType = (hbl) => {
    switch (hbl?.hbl_type) {
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
    switch (hbl?.cargo_type) {
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

const getCallOutcomeStatus = (outcome) => {
    switch (outcome) {
        case 'contacted':
            return { label: 'Contacted', severity: 'success' };
        case 'no_answer':
            return { label: 'No Answer', severity: 'warn' };
        case 'busy':
            return { label: 'Busy', severity: 'warn' };
        case 'appointment_scheduled':
            return { label: 'Appointment', severity: 'info' };
        case 'not_available':
            return { label: 'Not Available', severity: 'contrast' };
        default:
            return { label: 'Unknown', severity: 'contrast' };
    }
};

const onRowContextMenu = (event) => {
    cm.value.show(event.originalEvent);
};

const confirmViewHBL = (callFlag) => {
    if (callFlag.value && callFlag.value.hbl) {
        selectedHBLID.value = callFlag.value.hbl.id;
        showConfirmViewHBLModal.value = true;
    }
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
        call_outcome: {value: null, matchMode: FilterMatchMode.EQUALS},
        agent: {value: null, matchMode: FilterMatchMode.EQUALS},
    };
    fromDate.value = moment(new Date()).subtract(24, "months").toISOString().split("T")[0];
    toDate.value = moment(new Date()).toISOString().split("T")[0];
    fetchAllCalls(1);
};

const confirmViewCallFlagModal = (callFlag) => {
    if (callFlag.value && callFlag.value.hbl) {
        selectedHBLID.value = callFlag.value.hbl.id;
        selectedHBLData.value = callFlag.value.hbl;
        hblName.value = callFlag.value.hbl.hbl_name;
        showConfirmViewCallFlagModal.value = true;
    }
};

const closeCallFlagModal = () => {
    showConfirmViewCallFlagModal.value = false;
    selectedHBLID.value = null;
    selectedHBLData.value = null;
    hblName.value = "";
};

const onCallFlagCreated = () => {
    fetchAllCalls(currentPage.value, filters.value.global.value);
    selectedHBL.value = null;
    selectedHBLData.value = null;
};

const confirmViewCallFlagList = (callFlag) => {
    if (callFlag.value && callFlag.value.hbl) {
        selectedHBLID.value = callFlag.value.hbl.id;
        selectedHBLData.value = callFlag.value.hbl;
        showCallFlagListDialog.value = true;
    }
};

const closeCallFlagListDialog = () => {
    showCallFlagListDialog.value = false;
    selectedHBLID.value = null;
    selectedHBLData.value = null;
};

const openCallHistory = (callFlag) => {
    if (callFlag.hbl) {
        selectedHBLID.value = callFlag.hbl.id;
        selectedHBLData.value = callFlag.hbl;
        showCallFlagListDialog.value = true;
    }
};

const exportCSV = () => {
    dt.value.exportCSV();
};

const formatDate = (date) => {
    if (!date) return '-';
    return moment(date).format('MMM DD, YYYY');
};

const formatDateTime = (datetime) => {
    if (!datetime) return '-';
    return moment(datetime).format('MMM DD, YYYY hh:mm A');
};
</script>

<template>
    <AppLayout title="All Calls">
        <template #header>
            <div class="flex items-center gap-2">
                <i class="ti ti-phone text-2xl text-blue-600"></i>
                <span>All Calls</span>
            </div>
        </template>

        <Breadcrumb />

        <!-- Summary Widgets -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mt-5">
            <SimpleOverviewWidget
                title="Total Calls"
                :count="summaryStats.totalCalls"
                icon="ti ti-phone"
                color="primary"
            />
            <SimpleOverviewWidget
                title="Appointments"
                :count="summaryStats.appointments"
                icon="ti ti-calendar-check"
                color="success"
            />
            <SimpleOverviewWidget
                title="Follow-ups"
                :count="summaryStats.followups"
                icon="ti ti-clock-hour-4"
                color="warn"
            />
            <SimpleOverviewWidget
                title="No Action"
                :count="summaryStats.noAction"
                icon="ti ti-ban"
                color="contrast"
            />
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
                        <Select v-model="filters.call_outcome.value" :options="callOutcomes" :showClear="true" class="w-full" input-id="call-outcome" />
                        <label for="call-outcome">Call Outcome</label>
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
                        :globalFilterFields="['notes', 'hbl.reference', 'hbl.hbl', 'hbl.hbl_name', 'hbl.email', 'hbl.contact_number', 'hbl.consignee_name', 'causer.name']"
                        :loading="loading"
                        :rows="perPage"
                        :rowsPerPageOptions="[5, 10, 20, 50, 100]"
                        :totalRecords="totalRecords"
                        :value="allCallFlags"
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
                                <div class="flex items-center gap-2 text-lg font-medium">
                                    <i class="ti ti-phone text-blue-600"></i>
                                    <span>All Call Records</span>
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
                                        placeholder="Search calls..."
                                        size="small"
                                    />
                                </IconField>
                            </div>
                        </template>

                        <template #empty> No calls found. </template>
                        <template #loading> Loading call data. Please wait.</template>

                        <Column field="created_at" header="Call Date" sortable style="width: 150px">
                            <template #body="slotProps">
                                <div class="font-medium">{{ formatDate(slotProps.data.created_at) }}</div>
                                <div class="text-xs text-gray-500">{{ formatDateTime(slotProps.data.created_at).split(' ').slice(1).join(' ') }}</div>
                            </template>
                        </Column>

                        <Column field="hbl.hbl_number" header="HBL" sortable style="width: 120px">
                            <template #body="slotProps">
                                <span class="font-medium">{{ slotProps.data.hbl?.hbl_number ?? slotProps.data.hbl?.hbl }}</span>
                                <br v-if="slotProps.data.hbl?.is_short_loaded">
                                <Tag v-if="slotProps.data.hbl?.is_short_loaded" :severity="`warn`" :value="`Short Loaded`" icon="pi pi-exclamation-triangle" size="small"></Tag>
                            </template>
                        </Column>

                        <Column field="hbl.cargo_type" header="Cargo Type" sortable style="width: 130px">
                            <template #body="slotProps">
                                <Tag v-if="resolveCargoType(slotProps.data.hbl)" :icon="resolveCargoType(slotProps.data.hbl).icon" :severity="resolveCargoType(slotProps.data.hbl).color" :value="slotProps.data.hbl.cargo_type" class="text-sm"></Tag>
                            </template>
                        </Column>

                        <Column field="hbl.hbl_name" header="Customer">
                            <template #body="slotProps">
                                <div class="font-medium">{{ slotProps.data.hbl?.hbl_name }}</div>
                                <div class="text-gray-500 text-sm">{{ slotProps.data.hbl?.email }}</div>
                                <div class="text-gray-500 text-sm">{{ slotProps.data.hbl?.contact_number }}</div>
                            </template>
                        </Column>

                        <Column field="causer.name" header="Agent" style="width: 120px">
                            <template #body="slotProps">
                                <div class="font-medium">{{ slotProps.data.causer?.name || 'Unknown' }}</div>
                            </template>
                        </Column>

                        <Column field="call_outcome" header="Call Outcome" style="width: 140px">
                            <template #body="slotProps">
                                <Tag
                                    v-if="slotProps.data.call_outcome"
                                    :value="getCallOutcomeStatus(slotProps.data.call_outcome).label"
                                    :severity="getCallOutcomeStatus(slotProps.data.call_outcome).severity"
                                    size="small"
                                />
                                <span v-else class="text-gray-400 text-sm">No outcome</span>
                            </template>
                        </Column>

                        <Column field="notes" header="Notes" style="width: 200px">
                            <template #body="slotProps">
                                <div v-if="slotProps.data.notes" class="text-sm">
                                    {{ slotProps.data.notes.length > 50 ? slotProps.data.notes.substring(0, 50) + '...' : slotProps.data.notes }}
                                </div>
                                <span v-else class="text-gray-400 text-sm">No notes</span>
                            </template>
                        </Column>

                        <Column field="followup_date" header="Follow-up" style="width: 120px">
                            <template #body="slotProps">
                                <div v-if="slotProps.data.followup_date" class="text-sm">
                                    <div class="font-medium">{{ formatDate(slotProps.data.followup_date) }}</div>
                                    <Tag value="Pending" severity="warn" size="small" />
                                </div>
                                <span v-else class="text-gray-400 text-sm">None</span>
                            </template>
                        </Column>

                        <Column field="appointment_date" header="Appointment" style="width: 120px">
                            <template #body="slotProps">
                                <div v-if="slotProps.data.appointment_date" class="text-sm">
                                    <div class="font-medium">{{ formatDate(slotProps.data.appointment_date) }}</div>
                                    <Tag value="Scheduled" severity="success" size="small" />
                                </div>
                                <span v-else class="text-gray-400 text-sm">None</span>
                            </template>
                        </Column>

                        <Column header="Actions" style="width: 100px">
                            <template #body="slotProps">
                                <div class="flex gap-2">
                                    <Button
                                        icon="ti ti-history"
                                        size="small"
                                        severity="info"
                                        outlined
                                        @click="openCallHistory(slotProps.data)"
                                        v-tooltip="'View Call History'"
                                    />
                                    <Button
                                        icon="ti ti-flag"
                                        size="small"
                                        severity="warn"
                                        outlined
                                        @click="confirmViewCallFlagModal({value: slotProps.data})"
                                        v-tooltip="'Add Call Flag'"
                                    />
                                </div>
                            </template>
                        </Column>

                        <template #footer> In total there are {{ totalRecords }} call records. </template>
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

    <CallFlagModal
        :caller-name="hblName"
        :hbl-id="selectedHBLID"
        :hbl-data="selectedHBLData"
        :visible="showConfirmViewCallFlagModal"
        @close="closeCallFlagModal"
        @call-flag-created="onCallFlagCreated"
        @update:visible="showConfirmViewCallFlagModal = $event"/>

    <CallFlagListDialog
        :visible="showCallFlagListDialog"
        :hbl-id="selectedHBLID"
        :hbl-data="selectedHBLData"
        @close="closeCallFlagListDialog"
        @update:visible="showCallFlagListDialog = $event"/>
</template>
