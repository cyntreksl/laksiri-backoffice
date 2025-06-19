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
import SimpleOverviewWidget from "@/Components/Widgets/SimpleOverviewWidget.vue";
import CallFlagModal from "@/Pages/HBL/Partials/CallFlagModal.vue";
import CallFlagListDialog from "./Components/CallFlagListDialog.vue";

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
    warehouses: {
        type: Object,
        default: () => {
        },
    },
});

const loading = ref(true);
const hbls = ref([]);
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
    agent: {value: null, matchMode: FilterMatchMode.EQUALS},
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

// Summary statistics for appointments - will be fetched separately
const summaryStats = ref({
    totalAppointments: 0,
    pastAppointments: 0,
    todayAppointments: 0,
    thisWeekAppointments: 0,
    upcomingAppointments: 0
});

const fetchSummaryStats = async () => {
    try {
        const response = await axios.get('/call-center/appointments-data', {
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
                agent: filters.value.agent.value || "",
            }
        });

        const allAppointments = response.data.data || [];
        const today = moment().format('YYYY-MM-DD');
        const thisWeekEnd = moment().endOf('week').format('YYYY-MM-DD');

        const stats = {
            totalAppointments: allAppointments.length,
            pastAppointments: 0,
            todayAppointments: 0,
            thisWeekAppointments: 0,
            upcomingAppointments: 0
        };

        allAppointments.forEach(appointment => {
            const appointmentDate = appointment.appointment_date;
            if (appointmentDate === today) {
                stats.todayAppointments++;
            } else if (appointmentDate > today && appointmentDate <= thisWeekEnd) {
                stats.thisWeekAppointments++;
            } else if (appointmentDate > thisWeekEnd) {
                stats.upcomingAppointments++;
            }
            // Note: We don't count past appointments in the widgets
        });

        summaryStats.value = stats;
        console.log("Appointment Summary Stats:", stats);
    } catch (error) {
        console.error("Error fetching appointment summary stats:", error);
    }
};

const fetchHBLs = async (page = 1, search = "", sortField = 'appointment_date', sortOrder = 0) => {
    loading.value = true;
    try {
        const response = await axios.get('/call-center/appointments-data', {
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
                agent: filters.value.agent.value || "",
            }
        });

        // Map CallFlag data to HBL-like structure for display
        const appointmentData = response.data.data.map(callFlag => ({
            ...callFlag.hbl,
            appointment_date: callFlag.appointment_date,
            appointment_notes: callFlag.appointment_notes,
            call_outcome: callFlag.call_outcome,
            latest_call_flag: {
                date: callFlag.date,
                notes: callFlag.notes,
                causer: callFlag.causer
            },
            call_flags: [callFlag] // Include the call flag for compatibility
        }));

        hbls.value = appointmentData;
        totalRecords.value = response.data.meta?.total || 0;
        currentPage.value = response.data.meta?.current_page || 1;

        // Fetch summary stats when filters change
        if (page === 1) {
            await fetchSummaryStats();
        }
    } catch (error) {
        console.error("Error fetching appointments:", error);
        hbls.value = [];
        totalRecords.value = 0;
    } finally {
        loading.value = false;
    }
};

const debouncedFetchHBLs = debounce((searchValue) => {
    fetchHBLs(1, searchValue);
}, 1000);

const refreshSummaryStats = debounce(() => {
    fetchSummaryStats();
}, 500);

watch(() => filters.value.global.value, (newValue) => {
    if (newValue !== null) {
        debouncedFetchHBLs(newValue);
        refreshSummaryStats();
    }
});

watch(() => filters.value.warehouse.value, (newValue) => {
    fetchHBLs(1, filters.value.global.value);
    refreshSummaryStats();
});

watch(() => filters.value.hbl_type.value, (newValue) => {
    fetchHBLs(1, filters.value.global.value);
    refreshSummaryStats();
});

watch(() => filters.value.cargo_type.value, (newValue) => {
    fetchHBLs(1, filters.value.global.value);
    refreshSummaryStats();
});

watch(() => filters.value.is_hold.value, (newValue) => {
    fetchHBLs(1, filters.value.global.value);
    refreshSummaryStats();
});

watch(() => filters.value.user.value, (newValue) => {
    fetchHBLs(1, filters.value.global.value);
    refreshSummaryStats();
});

watch(() => filters.value.payments.value, (newValue) => {
    fetchHBLs(1, filters.value.global.value);
    refreshSummaryStats();
});

watch(() => fromDate.value, (newValue) => {
    fetchHBLs(1, filters.value.global.value);
    refreshSummaryStats();
});

watch(() => toDate.value, (newValue) => {
    fetchHBLs(1, filters.value.global.value);
    refreshSummaryStats();
});

watch(() => filters.value.agent.value, (newValue) => {
    fetchHBLs(1, filters.value.global.value);
    refreshSummaryStats();
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

const getAppointmentStatus = (appointmentDate) => {
    if (!appointmentDate) return { label: 'No Date', severity: 'contrast' };

    const appointment = moment(appointmentDate);
    const today = moment();

    if (appointment.isBefore(today, 'day')) {
        return { label: 'Past', severity: 'contrast' };
    } else if (appointment.isSame(today, 'day')) {
        return { label: 'Today', severity: 'success' };
    } else if (appointment.diff(today, 'days') <= 7) {
        return { label: 'This Week', severity: 'warn' };
    } else {
        return { label: 'Upcoming', severity: 'info' };
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
        agent: {value: null, matchMode: FilterMatchMode.EQUALS},
    };
    fromDate.value = moment(new Date()).subtract(24, "months").toISOString().split("T")[0];
    toDate.value = moment(new Date()).toISOString().split("T")[0];
    fetchHBLs(currentPage.value);
    refreshSummaryStats();
};

const confirmViewCallFlagModal = (hbl) => {
    selectedHBLID.value = hbl.value.id;
    selectedHBLData.value = hbl.value;
    hblName.value = hbl.value.hbl_name;
    showConfirmViewCallFlagModal.value = true;
};

const closeCallFlagModal = () => {
    showConfirmViewCallFlagModal.value = false;
    selectedHBLID.value = null;
    selectedHBLData.value = null;
    hblName.value = "";
};

const onCallFlagCreated = () => {
    fetchHBLs(currentPage.value, filters.value.global.value);
    refreshSummaryStats();
    selectedHBL.value = null;
    selectedHBLData.value = null;
};

const confirmViewCallFlagList = (hbl) => {
    selectedHBLID.value = hbl.value.id;
    selectedHBLData.value = hbl.value;
    showCallFlagListDialog.value = true;
};

const closeCallFlagListDialog = () => {
    showCallFlagListDialog.value = false;
    selectedHBLID.value = null;
    selectedHBLData.value = null;
};

const openCallHistory = (hblData) => {
    selectedHBLID.value = hblData.id;
    selectedHBLData.value = hblData;
    showCallFlagListDialog.value = true;
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
    <AppLayout title="Appointment List">
        <template #header>
            <div class="flex items-center gap-2">
                <i class="ti ti-calendar-check text-2xl text-green-600"></i>
                <span>Appointment List</span>
            </div>
        </template>

        <Breadcrumb />

        <!-- Summary Widgets for Appointments -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mt-5">
            <SimpleOverviewWidget
                title="Total Appointments"
                :count="summaryStats.totalAppointments"
                icon="ti ti-calendar-check"
                color="success"
            />
            <SimpleOverviewWidget
                title="Today"
                :count="summaryStats.todayAppointments"
                icon="ti ti-calendar-today"
                color="primary"
            />
            <SimpleOverviewWidget
                title="This Week"
                :count="summaryStats.thisWeekAppointments"
                icon="ti ti-calendar-week"
                color="warn"
            />
            <SimpleOverviewWidget
                title="Upcoming"
                :count="summaryStats.upcomingAppointments"
                icon="ti ti-calendar-forward"
                color="info"
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
                                <div class="flex items-center gap-2 text-lg font-medium">
                                    <i class="ti ti-calendar-check text-green-600"></i>
                                    <span>Scheduled Appointments</span>
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
                                        placeholder="Search appointments..."
                                        size="small"
                                    />
                                </IconField>
                            </div>
                        </template>

                        <template #empty> No appointments found. </template>
                        <template #loading> Loading appointment data. Please wait.</template>

                        <Column field="hbl_number" header="HBL" sortable style="width: 120px">
                            <template #body="slotProps">
                                <span class="font-medium">{{ slotProps.data.hbl_number ?? slotProps.data.hbl }}</span>
                                <br v-if="slotProps.data.is_short_loaded">
                                <Tag v-if="slotProps.data.is_short_loaded" :severity="`warn`" :value="`Short Loaded`" icon="pi pi-exclamation-triangle" size="small"></Tag>
                            </template>
                        </Column>

                        <Column field="cargo_type" header="Cargo Type" sortable style="width: 130px">
                            <template #body="slotProps">
                                <Tag :icon="resolveCargoType(slotProps.data).icon" :severity="resolveCargoType(slotProps.data).color" :value="slotProps.data.cargo_type" class="text-sm"></Tag>
                            </template>
                        </Column>

                        <Column field="hbl_name" header="Customer">
                            <template #body="slotProps">
                                <div class="font-medium">{{ slotProps.data.hbl_name }}</div>
                                <div class="text-gray-500 text-sm">{{ slotProps.data.email }}</div>
                                <div class="text-gray-500 text-sm">{{ slotProps.data.contact_number }}</div>
                            </template>
                        </Column>

                        <Column field="consignee_name" header="Consignee">
                            <template #body="slotProps">
                                <div class="font-medium">{{ slotProps.data.consignee_name }}</div>
                                <div class="text-gray-500 text-sm">{{ slotProps.data.consignee_contact }}</div>
                            </template>
                        </Column>

                        <Column field="appointment_date" header="Appointment" sortable style="width: 180px">
                            <template #body="slotProps">
                                <div v-if="slotProps.data.appointment_date" class="space-y-1">
                                    <div class="font-medium">{{ formatDate(slotProps.data.appointment_date) }}</div>
                                    <Tag
                                        :value="getAppointmentStatus(slotProps.data.appointment_date).label"
                                        :severity="getAppointmentStatus(slotProps.data.appointment_date).severity"
                                        size="small"
                                        icon="ti ti-calendar"
                                    />
                                </div>
                                <div v-else class="text-gray-400 text-sm">No specific date</div>
                            </template>
                        </Column>

                        <Column field="latest_call_flag" header="Last Contact" style="width: 150px">
                            <template #body="slotProps">
                                <div v-if="slotProps.data.latest_call_flag" class="space-y-1">
                                    <div class="text-sm">{{ formatDate(slotProps.data.latest_call_flag.date) }}</div>
                                    <div class="text-xs text-gray-500">{{ slotProps.data.latest_call_flag.causer?.name || 'Unknown' }}</div>
                                </div>
                                <div v-else class="text-gray-400 text-sm">No contact</div>
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

                        <template #footer> In total there are {{ totalRecords }} appointments. </template>
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
