<script setup>
import { ref, onMounted, watch, computed } from "vue";
import Dialog from "primevue/dialog";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import Tag from "primevue/tag";
import Button from "primevue/button";
import TabView from "primevue/tabview";
import TabPanel from "primevue/tabpanel";
import moment from "moment";
import axios from "axios";
import Badge from 'primevue/badge';
import Skeleton from 'primevue/skeleton';

const props = defineProps({
    visible: {
        type: Boolean,
        default: false,
    },
    hblId: {
        type: String,
        required: true,
    },
    hblData: {
        type: Object,
        default: () => ({})
    }
});

const emit = defineEmits(['close', 'update:visible']);

const loading = ref(false);
const callFlags = ref([]);
const followUps = ref([]);
const appointments = ref([]);
const activeTab = ref(0);

const fetchCallFlags = async () => {
    if (!props.hblId) return;

    loading.value = true;
    try {
        const response = await axios.get(`/get-call-flags/${props.hblId}`);
        callFlags.value = response.data || [];

        // Filter follow-ups and appointments
        followUps.value = callFlags.value.filter(call => call.followup_date);
        appointments.value = callFlags.value.filter(call => call.appointment_date);

    } catch (error) {
        console.error('Error fetching call flags:', error);
        callFlags.value = [];
        followUps.value = [];
        appointments.value = [];
    } finally {
        loading.value = false;
    }
};

// Computed properties for different types of flags
const allCalls = computed(() => callFlags.value || []);
const pendingFollowUps = computed(() =>
    followUps.value.filter(call => {
        if (!call.followup_date) return false;
        const followUpDate = moment(call.followup_date);
        const today = moment();
        return followUpDate.isSameOrBefore(today, 'day');
    })
);
const upcomingAppointments = computed(() =>
    appointments.value.filter(call => {
        if (!call.appointment_date) return false;
        const appointmentDate = moment(call.appointment_date);
        const today = moment();
        return appointmentDate.isSameOrAfter(today, 'day');
    })
);

// Tab counts
const allCallsCount = computed(() => allCalls.value.length);
const followUpsCount = computed(() => pendingFollowUps.value.length);
const appointmentsCount = computed(() => upcomingAppointments.value.length);

watch(() => props.visible, (newVal) => {
    if (newVal && props.hblId) {
        fetchCallFlags();
    }
});

watch(() => props.hblId, (newValue) => {
    if (newValue && props.visible) {
        fetchCallFlags();
    }
});

onMounted(() => {
    if (props.visible && props.hblId) {
        fetchCallFlags();
    }
});

const getCallOutcomeSeverity = (outcome) => {
    switch (outcome) {
        case 'contacted': return 'success';
        case 'no_answer': return 'warn';
        case 'busy': return 'warn';
        case 'appointment_scheduled': return 'info';
        case 'not_available': return 'contrast';
        default: return 'secondary';
    }
};

const formatCallOutcome = (outcome) => {
    switch (outcome) {
        case 'contacted': return 'Successfully Contacted';
        case 'no_answer': return 'No Answer';
        case 'busy': return 'Line Busy';
        case 'appointment_scheduled': return 'Appointment Scheduled';
        case 'not_available': return 'Not Available';
        default: return outcome;
    }
};

// Get follow-up status
const getFollowUpStatus = (followUpDate) => {
    if (!followUpDate) return null;
    const followUp = moment(followUpDate);
    const today = moment();

    if (followUp.isBefore(today, 'day')) {
        return { label: 'Overdue', severity: 'danger' };
    } else if (followUp.isSame(today, 'day')) {
        return { label: 'Due Today', severity: 'warn' };
    } else {
        return { label: 'Scheduled', severity: 'info' };
    }
};

// Get appointment status
const getAppointmentStatus = (appointmentDate) => {
    if (!appointmentDate) return null;
    const appointment = moment(appointmentDate);
    const today = moment();

    if (appointment.isBefore(today, 'day')) {
        return { label: 'Past', severity: 'contrast' };
    } else if (appointment.isSame(today, 'day')) {
        return { label: 'Today', severity: 'success' };
    } else {
        return { label: 'Upcoming', severity: 'info' };
    }
};

// Close dialog
const closeDialog = () => {
    emit('update:visible', false);
    emit('close');
};

// Format date helper
const formatDate = (date) => {
    if (!date) return '-';
    return moment(date).format('MMM DD, YYYY');
};

// Format datetime helper
const formatDateTime = (datetime) => {
    if (!datetime) return '-';
    return moment(datetime).format('MMM DD, YYYY hh:mm A');
};
</script>

<template>
    <Dialog
        :visible="visible"
        :style="{ width: '80vw', maxWidth: '1200px' }"
        header="Call History & Schedule"
        modal
        @update:visible="closeDialog"
    >
        <div class="space-y-4">
            <!-- HBL Info Header -->
            <div v-if="hblData" class="bg-blue-50 p-4 rounded-lg border">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                    <div>
                        <span class="font-medium text-gray-700">HBL:</span>
                        <span class="ml-2">{{ hblData.hbl_number || hblData.hbl }}</span>
                    </div>
                    <div>
                        <span class="font-medium text-gray-700">Customer:</span>
                        <span class="ml-2">{{ hblData.hbl_name }}</span>
                    </div>
                    <div>
                        <span class="font-medium text-gray-700">Consignee:</span>
                        <span class="ml-2">{{ hblData.consignee_name }}</span>
                    </div>
                </div>
            </div>

            <!-- Loading Skeleton -->
            <div v-if="loading" class="space-y-4">
                <div class="bg-white p-4 rounded-lg border">
                    <div class="flex items-center space-x-4 mb-4">
                        <Skeleton height="2rem" width="8rem" />
                        <Skeleton height="2rem" width="8rem" />
                        <Skeleton height="2rem" width="8rem" />
                    </div>
                    <div class="space-y-3">
                        <div v-for="i in 3" :key="i" class="flex items-center space-x-4">
                            <Skeleton height="1.5rem" width="10rem" />
                            <Skeleton height="1.5rem" width="12rem" />
                            <Skeleton height="1.5rem" width="8rem" />
                            <Skeleton height="1.5rem" width="6rem" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabs for different views -->
            <TabView v-else v-model:activeIndex="activeTab" class="mt-4">
                <!-- All Calls Tab -->
                <TabPanel>
                    <template #header>
                        <div class="flex items-center gap-2">
                            <i class="ti ti-phone text-sm"></i>
                            <span>All Calls</span>
                            <Badge v-if="allCallsCount > 0" :value="allCallsCount" severity="info" />
                        </div>
                    </template>

                    <DataTable
                        :value="allCalls"
                        :rows="10"
                        :paginator="allCallsCount > 10"
                        responsiveLayout="scroll"
                        class="mt-4"
                        :emptyMessage="allCallsCount === 0 ? 'No calls found' : ''"
                    >
                        <Column field="date" header="Date" sortable style="width: 150px">
                            <template #body="slotProps">
                                <div class="text-sm">
                                    {{ formatDate(slotProps.data.date) }}
                                </div>
                            </template>
                        </Column>

                        <Column field="caller" header="Agent" style="width: 150px">
                            <template #body="slotProps">
                                <div class="text-sm font-medium">{{ slotProps.data.causer?.name || 'Unknown' }}</div>
                            </template>
                        </Column>

                        <Column field="call_outcome" header="Outcome" style="width: 150px">
                            <template #body="slotProps">
                                <Tag
                                    v-if="slotProps.data.call_outcome"
                                    :value="slotProps.data.call_outcome.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())"
                                    :severity="getCallOutcomeSeverity(slotProps.data.call_outcome)"
                                    size="small"
                                />
                                <span v-else class="text-gray-400 text-sm">-</span>
                            </template>
                        </Column>

                        <Column field="notes" header="Notes" style="min-width: 250px">
                            <template #body="slotProps">
                                <div class="text-sm">{{ slotProps.data.notes || '-' }}</div>
                            </template>
                        </Column>

                        <Column field="followup_date" header="Follow-up" style="width: 150px">
                            <template #body="slotProps">
                                <div v-if="slotProps.data.followup_date" class="space-y-1">
                                    <div class="text-sm">{{ formatDate(slotProps.data.followup_date) }}</div>
                                    <Tag
                                        v-if="getFollowUpStatus(slotProps.data.followup_date)"
                                        :value="getFollowUpStatus(slotProps.data.followup_date).label"
                                        :severity="getFollowUpStatus(slotProps.data.followup_date).severity"
                                        size="small"
                                    />
                                </div>
                                <span v-else class="text-gray-400 text-sm">-</span>
                            </template>
                        </Column>

                        <Column field="appointment_date" header="Appointment" style="width: 150px">
                            <template #body="slotProps">
                                <div v-if="slotProps.data.appointment_date" class="space-y-1">
                                    <div class="text-sm">{{ formatDate(slotProps.data.appointment_date) }}</div>
                                    <Tag
                                        v-if="getAppointmentStatus(slotProps.data.appointment_date)"
                                        :value="getAppointmentStatus(slotProps.data.appointment_date).label"
                                        :severity="getAppointmentStatus(slotProps.data.appointment_date).severity"
                                        size="small"
                                    />
                                </div>
                                <span v-else class="text-gray-400 text-sm">-</span>
                            </template>
                        </Column>
                    </DataTable>
                </TabPanel>

                <!-- Follow-ups Tab -->
                <TabPanel>
                    <template #header>
                        <div class="flex items-center gap-2">
                            <i class="ti ti-clock-hour-4 text-sm"></i>
                            <span>Follow-ups</span>
                            <Badge v-if="followUpsCount > 0" :value="followUpsCount" severity="warn" />
                        </div>
                    </template>

                    <DataTable
                        :value="pendingFollowUps"
                        :rows="10"
                        :paginator="followUpsCount > 10"
                        responsiveLayout="scroll"
                        class="mt-4"
                        :emptyMessage="followUpsCount === 0 ? 'No follow-ups due' : ''"
                    >
                        <Column field="followup_date" header="Follow-up Date" sortable style="width: 150px">
                            <template #body="slotProps">
                                <div class="space-y-1">
                                    <div class="text-sm font-medium">{{ formatDate(slotProps.data.followup_date) }}</div>
                                    <Tag
                                        :value="getFollowUpStatus(slotProps.data.followup_date).label"
                                        :severity="getFollowUpStatus(slotProps.data.followup_date).severity"
                                        size="small"
                                    />
                                </div>
                            </template>
                        </Column>

                        <Column field="date" header="Call Date" sortable style="width: 120px">
                            <template #body="slotProps">
                                <div class="text-sm">{{ formatDate(slotProps.data.date) }}</div>
                            </template>
                        </Column>

                        <Column field="caller" header="Agent" style="width: 150px">
                            <template #body="slotProps">
                                <div class="text-sm font-medium">{{ slotProps.data.causer?.name || 'Unknown' }}</div>
                            </template>
                        </Column>

                        <Column field="call_outcome" header="Last Outcome" style="width: 150px">
                            <template #body="slotProps">
                                <Tag
                                    v-if="slotProps.data.call_outcome"
                                    :value="slotProps.data.call_outcome.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())"
                                    :severity="getCallOutcomeSeverity(slotProps.data.call_outcome)"
                                    size="small"
                                />
                            </template>
                        </Column>

                        <Column field="notes" header="Notes" style="min-width: 300px">
                            <template #body="slotProps">
                                <div class="text-sm">{{ slotProps.data.notes || '-' }}</div>
                            </template>
                        </Column>
                    </DataTable>
                </TabPanel>

                <!-- Appointments Tab -->
                <TabPanel>
                    <template #header>
                        <div class="flex items-center gap-2">
                            <i class="ti ti-calendar-check text-sm"></i>
                            <span>Appointments</span>
                            <Badge v-if="appointmentsCount > 0" :value="appointmentsCount" severity="success" />
                        </div>
                    </template>

                    <DataTable
                        :value="upcomingAppointments"
                        :rows="10"
                        :paginator="appointmentsCount > 10"
                        responsiveLayout="scroll"
                        class="mt-4"
                        :emptyMessage="appointmentsCount === 0 ? 'No appointments scheduled' : ''"
                    >
                        <Column field="appointment_date" header="Appointment Date" sortable style="width: 150px">
                            <template #body="slotProps">
                                <div class="space-y-1">
                                    <div class="text-sm font-medium">{{ formatDate(slotProps.data.appointment_date) }}</div>
                                    <Tag
                                        :value="getAppointmentStatus(slotProps.data.appointment_date).label"
                                        :severity="getAppointmentStatus(slotProps.data.appointment_date).severity"
                                        size="small"
                                    />
                                </div>
                            </template>
                        </Column>

                        <Column field="date" header="Call Date" sortable style="width: 120px">
                            <template #body="slotProps">
                                <div class="text-sm">{{ formatDate(slotProps.data.date) }}</div>
                            </template>
                        </Column>

                        <Column field="caller" header="Agent" style="width: 150px">
                            <template #body="slotProps">
                                <div class="text-sm font-medium">{{ slotProps.data.causer?.name || 'Unknown' }}</div>
                            </template>
                        </Column>

                        <Column field="appointment_notes" header="Appointment Notes" style="min-width: 250px">
                            <template #body="slotProps">
                                <div class="text-sm">{{ slotProps.data.appointment_notes || '-' }}</div>
                            </template>
                        </Column>

                        <Column field="notes" header="Call Notes" style="min-width: 200px">
                            <template #body="slotProps">
                                <div class="text-sm">{{ slotProps.data.notes || '-' }}</div>
                            </template>
                        </Column>
                    </DataTable>
                </TabPanel>
            </TabView>
        </div>

        <template #footer>
            <div class="flex justify-end">
                <Button
                    label="Close"
                    severity="secondary"
                    @click="closeDialog"
                />
            </div>
        </template>
    </Dialog>
</template>

<style scoped>
.p-datatable-sm .p-datatable-tbody > tr > td {
    padding: 0.5rem;
}

.p-datatable-sm .p-datatable-thead > tr > th {
    padding: 0.5rem;
    font-size: 0.875rem;
}

:deep(.p-tabview-tab-list) {
    border-bottom: 1px solid #e5e7eb;
}

:deep(.p-tabview-tab-active .p-tabview-tab-content) {
    background-color: #3b82f6;
    color: white;
}

:deep(.p-datatable .p-datatable-thead > tr > th) {
    background-color: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    color: #374151;
    font-weight: 600;
}

:deep(.p-datatable .p-datatable-tbody > tr:nth-child(odd)) {
    background-color: #f9fafb;
}

:deep(.p-datatable .p-datatable-tbody > tr:hover) {
    background-color: #f3f4f6;
}
</style>
