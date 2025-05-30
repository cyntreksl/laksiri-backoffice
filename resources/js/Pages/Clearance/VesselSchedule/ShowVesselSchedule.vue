<script setup>
import {computed, onMounted, ref, watch} from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import Card from 'primevue/card';
import Button from 'primevue/button';
import Tabs from 'primevue/tabs';
import TabList from 'primevue/tablist';
import Tab from 'primevue/tab';
import TabPanels from 'primevue/tabpanels';
import TabPanel from 'primevue/tabpanel';
import InputError from "@/Components/InputError.vue";
import InputNumber from "primevue/inputnumber";
import InputText from "primevue/inputtext";
import DatePicker from "primevue/datepicker";
import IftaLabel from "primevue/iftalabel";
import {router, useForm, usePage} from "@inertiajs/vue3";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import VirtualScroller from 'primevue/virtualscroller';
import LoadedShipmentDetailDialog from "@/Pages/Common/Dialog/Container/Index.vue";
import {useConfirm} from "primevue/useconfirm";
import {push} from "notivue";
import AddVesselModal from "@/Pages/Clearance/VesselSchedule/Partials/AddVesselModal.vue";
import Checkbox from "primevue/checkbox";
import moment from "moment";

const props = defineProps({
    vesselSchedule: {
        type: Object,
        default: () => ({}),
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

const showConfirmLoadedShipmentModal = ref(false);
const confirm = useConfirm();
const selectedContainer = ref(props.vesselSchedule?.clearance_containers[0] ?? {});
const containerData = ref({});
const filteredHBLS = ref([]);
const filteredMHBLS = ref([]);
const containerPaymentData = ref({});
const isContainerPayment = ref(false);
const isFinanceApproved = ref(false);
const containerId = ref('');
const showConfirmAddVesselModal = ref(false);

const form = useForm({
    container_id: selectedContainer.value.id ?? '',
    do_charge: 0,
    demurrage_charge: 0,
    assessment_charge: 0,
    slpa_charge: 0,
    refund_charge: 0,
    clearance_charge: 0
});

const documentForm = useForm({
    name: "",
    date: "",
});

const fetchLoadedContainer = async () => {
    try {
        const response = await fetch(`/loaded-containers/get-container/${selectedContainer.value.id}`, {
            method: "GET",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": usePage().props.csrf,
            },
        });
        if (!response.ok) {
            throw new Error(`Failed to fetch container ${selectedContainer.value.id}`);
        }
        containerData.value = (await response.json())[0];
        hbls();
        mhbls();
        await fetchContainerPayment();
    } catch (error) {
        console.error("Error:", error);
    }
};

onMounted(() => {
    fetchLoadedContainer();
});

watch(
    [
        () => selectedContainer.value,
    ],
    ([selectedContainer]) => {
        fetchLoadedContainer();
    }
);

const hbls = () => {
    const hbls = containerData.value.hbls;
    filteredHBLS.value = Object.values(hbls).filter(hbl => hbl.mhbl === null);
}

const mhbls = () => {
    const hbls = containerData.value.hbls;
    filteredMHBLS.value = Object.values(hbls).filter(hbl => hbl.mhbl !== null);
}

const closeModal = () => {
    showConfirmLoadedShipmentModal.value = false;
};

const confirmVesselClear = (id) => {
    confirm.require({
        message: 'Are you sure you want to release this vessel?',
        header: 'Vessel Release?',
        icon: 'pi pi-info-circle',
        rejectLabel: 'Cancel',
        rejectProps: {
            label: 'Cancel',
            severity: 'secondary',
            outlined: true
        },
        acceptProps: {
            label: 'Release Vessel',
            severity: 'warn'
        },
        accept: () => {

        },
        reject: () => {
        }
    });
};

const confirmVesselReturn = (id) => {
    confirm.require({
        message: 'Are you sure you want to return or reject this vessel?',
        header: 'Vessel Return?',
        icon: 'pi pi-info-circle',
        rejectLabel: 'Cancel',
        rejectProps: {
            label: 'Cancel',
            severity: 'secondary',
            outlined: true
        },
        acceptProps: {
            label: 'Release Vessel',
            severity: 'danger'
        },
        accept: () => {

        },
        reject: () => {
        }
    });
};

const fetchContainerPayment = async () => {
    try {
        const response = await fetch(`/container-payment/${selectedContainer.value.id}`, {
            method: "GET",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": usePage().props.csrf,
            },
        });
        if (!response.ok) {
            throw new Error(`Failed to fetch container payments ${selectedContainer.value.id}`);
        }
        containerPaymentData.value = (await response.json());
        isContainerPayment.value = Object.keys(containerPaymentData.value).length > 0;
        if (containerPaymentData.value && Object.keys(containerPaymentData.value).length > 0) {
            form.do_charge = containerPaymentData.value.do_charge;
            form.demurrage_charge = containerPaymentData.value.demurrage_charge;
            form.assessment_charge = containerPaymentData.value.assessment_charge;
            form.slpa_charge = containerPaymentData.value.slpa_charge;
            form.refund_charge = containerPaymentData.value.refund_charge;
            form.clearance_charge = containerPaymentData.value.clearance_charge;
            isFinanceApproved.value = containerPaymentData.value.is_finance_approved;
        } else {
            form.container_id = selectedContainer.value.id;
            form.do_charge = 0;
            form.demurrage_charge = 0;
            form.assessment_charge = 0;
            form.slpa_charge = 0;
            form.refund_charge = 0;
            form.clearance_charge = 0;
            isFinanceApproved.value = false;
        }
    } catch (error) {
        console.error("Error:", error);
    }
};

const handleContainerPaymentCreate = async () => {
    form.post(route("container-payment.store"), {
        onSuccess: (page) => {
            fetchContainerPayment();
            push.success("Container Payment Created Successfully!");
        },
        onError: () => console.log("error"),
        preserveScroll: true,
        preserveState: true,
    });
};

const confirmAddVesselModal = () => {
    showConfirmAddVesselModal.value = true;
};

const closeAddVesselModal = () => {
    showConfirmAddVesselModal.value = false;
}

const reloadPage = () => {
    window.location.reload();
}

const confirmRemoveContainerHold = (ContainerId) => {
    containerId.value = ContainerId;
    confirm.require({
        message: `Would you like to remove this shipment form this vessel schedule?`,
        header: `Remove Shipment?`,
        icon: 'pi pi-info-circle',
        rejectLabel: 'Cancel',
        rejectProps: {
            label: 'Cancel',
            severity: 'secondary',
            outlined: true
        },
        acceptProps: {
            label: `Remove container`,
            severity: 'warn'
        },
        accept: () => {
            router.post(
                route("clearance.vessel-schedule.remove-vessel", props.vesselSchedule.id),
                {containerID: containerId.value},
                {
                    preserveScroll: true,
                    onSuccess: () => {
                        push.success(`Removed Successfully!`);
                        reloadPage();
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

const updateForm = useForm({
    cargo_type: selectedContainer.value.cargo_type,
    reference: selectedContainer.value.reference,
    awb_number: selectedContainer.value.awb_number,
    estimated_time_of_departure: selectedContainer.value.estimated_time_of_departure,
    estimated_time_of_arrival: selectedContainer.value.estimated_time_of_arrival,
    status: selectedContainer.value.status,
    note: selectedContainer.value.note,
    is_reached: Boolean(selectedContainer.value.is_reached),
    reached_date: selectedContainer.value.reached_date,
    container_type: selectedContainer.value.container_type,
    bl_number: selectedContainer.value.bl_number,
    container_number: selectedContainer.value.container_number,
    seal_number: selectedContainer.value.seal_number,
    vessel_name: selectedContainer.value.vessel_name,
    voyage_number: selectedContainer.value.voyage_number,
    shipping_line: selectedContainer.value.shipping_line,
    port_of_loading: selectedContainer.value.port_of_loading,
    port_of_discharge: selectedContainer.value.port_of_discharge,
    flight_number: selectedContainer.value.flight_number,
    airline_name: selectedContainer.value.airline_name,
    airport_of_departure: selectedContainer.value.airport_of_departure,
    airport_of_arrival: selectedContainer.value.airport_of_arrival,
    cargo_class: selectedContainer.value.cargo_class,
    loading_ended_at: selectedContainer.value.loading_ended_at,
    loading_started_at: selectedContainer.value.loading_started_at,
    is_returned: Boolean(selectedContainer.value.is_returned),
});

watch(() => updateForm.is_reached, (newValue) => {
    if (newValue) {
        updateForm.is_returned = false;
    }
});

watch(() => updateForm.is_returned, (newValue) => {
    if (newValue) {
        updateForm.is_reached = false;
        updateForm.reached_date = '';
    }
});

const handleUpdateContainer = () => {
    if (updateForm.estimated_time_of_departure !== 'Invalid date') {
        updateForm.estimated_time_of_departure = moment(updateForm.estimated_time_of_departure).format("YYYY-MM-DD");
    } else {
        updateForm.estimated_time_of_departure = null;
    }

    if (updateForm.estimated_time_of_arrival !== 'Invalid date') {
        updateForm.estimated_time_of_arrival = moment(updateForm.estimated_time_of_arrival).format("YYYY-MM-DD");
    } else {
        updateForm.estimated_time_of_arrival = null;
    }

    if (moment(updateForm.reached_date).isValid()) {
        updateForm.reached_date = moment(updateForm.reached_date).format("YYYY-MM-DD");
    } else {
        updateForm.reached_date = null;
    }

    updateForm.post(route("clearance.vessel-schedule.update-container", selectedContainer.value.id), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            reloadPage();
            push.success('Container Updated Successfully!');
        },
        onError: () => {
            updateForm.reset();
        }
    });
}

// Using Moment.js for grouped shipments
const groupedShipments = computed(() => {
    if (!props.vesselSchedule?.clearance_containers) {
        return [];
    }

    const groups = {};

    props.vesselSchedule.clearance_containers.forEach(item => {
        // Ensure estimated_time_of_arrival is valid before processing
        if (!item.estimated_time_of_arrival || !moment(item.estimated_time_of_arrival).isValid()) {
            return;
        }

        const m = moment(item.estimated_time_of_arrival);
        const dayOfWeek = m.format('dddd'); // 'Monday', 'Tuesday', etc.
        const fullDate = m.format('MMM Do, YYYY'); // 'Jun 2nd, 2025' or similar (adjust format as needed)
        const groupingKey = m.format('YYYY-MM-DD'); // Use a date string for consistent grouping regardless of time

        if (!groups[groupingKey]) {
            groups[groupingKey] = {
                day: dayOfWeek,
                date: fullDate,
                momentObject: m.startOf('day'), // Store moment object for sorting by date
                items: []
            };
        }
        groups[groupingKey].items.push(item);
    });

    // Convert the object to an array and sort them by the date
    const sortedGroups = Object.keys(groups)
        .map(key => groups[key])
        .sort((a, b) => a.momentObject.diff(b.momentObject)); // Sort using moment objects

    return sortedGroups;
});

selectedContainer.value = groupedShipments.value[0]?.items[0] ?? null;
</script>

<template>
    <AppLayout title="Vessel Schedule">
        <template #header>Vessel Schedule</template>

        <Breadcrumb/>

        <div class="mt-5 flex justify-between items-center">
            <div>
                <p class="text-gray-800">Schedule for</p>
                <div class="flex items-center space-x-2 text-2xl text-gray-800 mt-1">
                    <div>
                        <i class="ti ti-calendar"></i>
                        {{ vesselSchedule?.start_date }}
                    </div>
                    <div>
                        <i v-if="vesselSchedule?.end_date" class="ti ti-arrow-narrow-right"></i>
                    </div>
                    <div>
                        <i class="ti ti-calendar"></i>
                        {{ vesselSchedule?.end_date }}
                    </div>
                </div>
            </div>

            <a :href="route('clearance.vessel-schedule.download', vesselSchedule?.id)">
                <Button
                    icon="pi pi-download"
                    label="Download Vessel Schedule"
                    size="small"
                    severity="help"
                />
            </a>
        </div>

        <div class="grid grid-cols-12 gap-4 my-5">
            <!-- Container List -->
            <Card class="col-span-12 lg:col-span-3 border-2 border-gray-200 !shadow-none">
                <template #title>
                    <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-2">
                        <div class="text-base font-semibold">Available Shipments</div>
                        <Button icon="pi pi-plus"
                                label="Add Shipment"
                                size="small" @click.prevent="confirmAddVesselModal()"/>
                    </div>
                </template>
                <template #subtitle>{{ vesselSchedule?.clearance_containers.length }} Shipment(s)</template>
                <template #content>
                    <VirtualScroller :item-size="170" :items="groupedShipments" style="height: 400px">
                        <template v-slot:item="{ item: dayGroup }">
                            <div class="mb-4">
                                <h2 class="text-base font-semibold mb-2">
                                    {{ dayGroup.day }} ({{ dayGroup.items.length }})
                                </h2>
                                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-1">
                                    <div v-for="item in dayGroup.items" :key="item.id" :class="['flex flex-col space-y-3 rounded-xl p-4 bg-gradient-to-tr hover:cursor-pointer', selectedContainer?.id === item?.id ? 'from-purple-700 to-purple-500' : 'from-violet-700 to-violet-500']"
                                         style="height: 170px"
                                         @click="selectedContainer = item">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center space-x-1 rounded-lg bg-violet-500 px-2 py-1">
                                                <div :class="['h-4 w-4 rounded-md ', selectedContainer?.id === item?.id ? 'animate-spin bg-green-300' : 'animate-none bg-orange-300']"></div>
                                                <div class="text-white text-xs">{{ item?.container_type }}
                                                </div>
                                            </div>
                                            <div class="text-violet-300 text-xs">
                                                {{ moment(item?.estimated_time_of_arrival).format('DD MMM YYYY') }}
                                            </div>
                                        </div>
                                        <h2 class="text-lg font-medium text-white">{{ item?.reference }}</h2>
                                        <div class="flex justify-between">
                                            <i class="ti ti-ship text-2xl text-white"></i>
                                            <div class="flex space-x-4 items-center">
                                                <div class="flex items-center space-x-1 text-violet-300">
                                                    <div class="text-xs">{{ item?.cargo_type }}</div>
                                                </div>
                                                <div class="flex items-center space-x-1 text-white">
                                                    <div class="text-xs uppercase">{{ item?.warehouse.name }}</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex justify-between items-center space-x-4">
                                            <div class="h-2 rounded-full bg-violet-500 flex-1">
                                                <div
                                                    class="w-[100%] h-2 rounded-full from-green-300 to-green-400 bg-gradient-to-l"></div>
                                            </div>
                                            <div v-tooltip="'Remove From Vessel Schedule'"
                                                 class="text-red-300 text-xs whitespace-nowrap cursor-pointer"
                                                 @click.prevent="confirmRemoveContainerHold(item?.id)">Remove
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </VirtualScroller>
                </template>
            </Card>

            <!-- Tabs Panel -->
            <Card class="col-span-12 lg:col-span-9 border-2 border-gray-200 !shadow-none">
                <template #title>
                    <div class="flex flex-col md:flex-row md:justify-between gap-4">
                        <div>
                            <div class="flex items-center space-x-2 text-xs text-gray-400">
                                <div class="flex items-center">
                                    <i class="ti ti-plane-departure text-xl mr-2"></i>
                                    {{ selectedContainer?.branch?.name ?? '' }}
                                </div>
                                <div><i class="ti ti-arrow-narrow-right text-xl"></i></div>
                                <div class="flex items-center">
                                    <i class="ti ti-plane-arrival text-xl mr-2"></i>
                                    {{ selectedContainer?.warehouse?.name ?? '' }}
                                </div>
                            </div>
                            <div class="flex items-center space-x-2">
                                <div class="text-xl">{{ selectedContainer.reference ?? '' }}</div>
                                <Button icon="pi pi-eye" rounded severity="info" size="small" variant="text"
                                        @click.prevent="showConfirmLoadedShipmentModal = !showConfirmLoadedShipmentModal"/>
                            </div>
                        </div>
                        <div class="flex flex-col sm:flex-row gap-2">
                            <Button label="Clear Shipment" severity="success"
                                    size="small" @click.prevent="confirmVesselClear()"/>
                            <Button label="Mark as a return" severity="danger"
                                    size="small" variant="outlined" @click.prevent="confirmVesselReturn()"/>
                        </div>
                    </div>
                </template>

                <template #content>
                    <Tabs value="0">
                        <TabList>
                            <Tab value="0">HBLS</Tab>
                            <Tab value="1">MHBLs</Tab>
                            <Tab value="2">Payments</Tab>
                            <Tab value="3">Shipment Details</Tab>
                            <Tab value="4">Documents</Tab>
                        </TabList>
                        <TabPanels>
                            <!-- HBLs Tab -->
                            <TabPanel value="0">
                                <div class="overflow-x-auto">
                                    <DataTable :rows="10" :rowsPerPageOptions="[5, 10, 20, 50, 100]"
                                               :value="filteredHBLS"
                                               paginator row-hover
                                               tableStyle="min-width: 50rem">
                                        <template #empty>No HBLs found.</template>
                                        <Column class="font-bold" field="hbl_number" header="HBL"></Column>
                                        <Column field="packages_count" header="Packages">
                                            <template #body="slotProps">
                                                <div class="flex items-center">
                                                    <i class="ti ti-package mr-1 text-blue-500"
                                                       style="font-size: 1rem"></i>
                                                    {{ slotProps.data.packages_count }}
                                                </div>
                                            </template>
                                        </Column>
                                        <Column field="hbl_name" header="HBL Name">
                                            <template #body="slotProps">
                                                <div>{{ slotProps.data.hbl_name }}</div>
                                                <div class="text-gray-500 text-sm">{{ slotProps.data.nic }}</div>
                                                <div class="text-gray-500 text-sm">{{ slotProps.data.address }}</div>
                                            </template>
                                        </Column>
                                        <Column field="contact_number" header="Contact"></Column>
                                        <Column field="consignee_name" header="Consignee">
                                            <template #body="slotProps">
                                                <div>{{ slotProps.data.consignee_name }}</div>
                                                <div class="text-gray-500 text-sm">{{
                                                        slotProps.data.consignee_address
                                                    }}
                                                </div>
                                            </template>
                                        </Column>
                                    </DataTable>
                                </div>
                            </TabPanel>

                            <!-- MHBLs Tab -->
                            <TabPanel value="1">
                                <div class="overflow-x-auto">
                                    <DataTable :rows="10" :rowsPerPageOptions="[5, 10, 20, 50, 100]"
                                               :value="filteredMHBLS"
                                               paginator row-hover
                                               tableStyle="min-width: 50rem">
                                        <template #empty>No MHBLs found.</template>
                                        <Column class="font-bold" field="mhbl" header="MHBL">
                                            <template #body="{data}">
                                                {{ data.mhbl.hbl_number || data.mhbl.reference || '-' }}
                                            </template>
                                        </Column>
                                        <Column field="hbl_number" header="HBL">
                                            <template #body="{data}">
                                                {{ data.hbl_number || '-' }}
                                            </template>
                                        </Column>
                                        <Column field="packages_count" header="Packages">
                                            <template #body="{data}">
                                                <div class="flex items-center">
                                                    <i class="ti ti-package mr-1 text-blue-500"
                                                       style="font-size: 1rem"></i>
                                                    {{ data.packages_count }}
                                                </div>
                                            </template>
                                        </Column>
                                        <Column field="hbl_name" header="Name">
                                            <template #body="slotProps">
                                                <div>{{ slotProps.data.hbl_name }}</div>
                                                <div class="text-gray-500 text-sm">{{ slotProps.data.nic }}</div>
                                                <div class="text-gray-500 text-sm">{{ slotProps.data.address }}</div>
                                            </template>
                                        </Column>
                                        <Column field="contact_number" header="Contact">
                                            <template #body="{data}">
                                                {{ data.contact_number || '-' }}
                                            </template>
                                        </Column>
                                        <Column field="consignee_name" header="Consignee">
                                            <template #body="slotProps">
                                                <div>{{ slotProps.data.consignee_name }}</div>
                                                <div class="text-gray-500 text-sm">{{
                                                        slotProps.data.consignee_address
                                                    }}
                                                </div>
                                            </template>
                                        </Column>
                                    </DataTable>
                                </div>
                            </TabPanel>

                            <!-- Payments Tab -->
                            <TabPanel value="2">
                                <form @submit.prevent="handleContainerPaymentCreate">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                        <div>
                                            <IftaLabel>
                                                <InputNumber
                                                    v-model="form.do_charge"
                                                    :disabled="isFinanceApproved"
                                                    :maxFractionDigits="2"
                                                    :minFractionDigits="2" class="w-full"
                                                    inputId="do-charge" min="0" step="any"
                                                    variant="filled"
                                                />
                                                <label for="do-charge">DO Charge</label>
                                            </IftaLabel>
                                            <InputError :message="form.errors.do_charge"/>
                                        </div>

                                        <div>
                                            <IftaLabel>
                                                <InputNumber v-model="form.demurrage_charge"
                                                             :disabled="isFinanceApproved"
                                                             :maxFractionDigits="2" :minFractionDigits="2"
                                                             class="w-full" inputId="demurrage-charge" min="0"
                                                             step="any" variant="filled"/>
                                                <label for="demurrage-charge">Demurrage Charge</label>
                                            </IftaLabel>
                                            <InputError :message="form.errors.demurrage_charge"/>
                                        </div>

                                        <div>
                                            <IftaLabel>
                                                <InputNumber v-model="form.assessment_charge"
                                                             :disabled="isFinanceApproved"
                                                             :maxFractionDigits="2" :minFractionDigits="2"
                                                             class="w-full" inputId="assessment-charge" min="0"
                                                             step="any" variant="filled"/>
                                                <label for="assessment-charge">Assessment Charge</label>
                                            </IftaLabel>
                                            <InputError :message="form.errors.assessment_charge"/>
                                        </div>

                                        <div>
                                            <IftaLabel>
                                                <InputNumber v-model="form.slpa_charge" :disabled="isFinanceApproved"
                                                             :maxFractionDigits="2" :minFractionDigits="2"
                                                             class="w-full"
                                                             inputId="slap-charge" min="0" step="any" variant="filled"/>
                                                <label for="slap-charge">SLAP Charge</label>
                                            </IftaLabel>
                                            <InputError :message="form.errors.slpa_charge"/>
                                        </div>

                                        <div>
                                            <IftaLabel>
                                                <InputNumber v-model="form.refund_charge" :disabled="isFinanceApproved"
                                                             :maxFractionDigits="2" :minFractionDigits="2"
                                                             class="w-full"
                                                             inputId="refund-charge" min="0" step="any"
                                                             variant="filled"/>
                                                <label for="refund-charge">Refund</label>
                                            </IftaLabel>
                                            <InputError :message="form.errors.refund_charge"/>
                                        </div>

                                        <div>
                                            <IftaLabel>
                                                <InputNumber v-model="form.clearance_charge"
                                                             :disabled="isFinanceApproved"
                                                             :maxFractionDigits="2" :minFractionDigits="2"
                                                             class="w-full" inputId="clearance-charge" min="0"
                                                             step="any" variant="filled"/>
                                                <label for="clearance-charge">Clearance Charge</label>
                                            </IftaLabel>
                                            <InputError :message="form.errors.clearance_charge"/>
                                        </div>

                                        <div v-if="!isFinanceApproved" class="col-span-1 md:col-span-2 text-right">
                                            <Button :label="isContainerPayment ? 'Edit Payment' : 'Save Payment'"
                                                    icon="pi pi-save" size="small" type="submit"/>
                                        </div>
                                    </div>
                                </form>
                            </TabPanel>

                            <!-- Shipment Details Tab -->
                            <TabPanel value="3">
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                                    <div>
                                        <IftaLabel>
                                            <InputText v-model="updateForm.cargo_type" class="w-full" disabled
                                                       variant="filled"/>
                                            <label>Cargo Mode</label>
                                        </IftaLabel>
                                    </div>

                                    <div>
                                        <IftaLabel>
                                            <InputText v-model="updateForm.container_type" class="w-full" disabled
                                                       variant="filled"/>
                                            <label>Container Type</label>
                                        </IftaLabel>
                                    </div>

                                    <div>
                                        <IftaLabel>
                                            <InputText v-model="updateForm.reference" class="w-full" disabled
                                                       variant="filled"/>
                                            <label>Reference</label>
                                        </IftaLabel>
                                    </div>

                                    <div>
                                        <IftaLabel>
                                            <InputText v-model="updateForm.container_number" class="w-full" disabled
                                                       variant="filled"/>
                                            <label>Container Number</label>
                                        </IftaLabel>
                                    </div>

                                    <div>
                                        <IftaLabel>
                                            <InputText v-model="updateForm.seal_number" class="w-full" disabled
                                                       variant="filled"/>
                                            <label>Seal Number</label>
                                        </IftaLabel>
                                    </div>

                                    <div>
                                        <IftaLabel>
                                            <InputText v-model="updateForm.bl_number" class="w-full" disabled
                                                       variant="filled"/>
                                            <label>BL Number</label>
                                        </IftaLabel>
                                    </div>

                                    <div>
                                        <IftaLabel>
                                            <InputText v-model="updateForm.estimated_time_of_departure" class="w-full"
                                                       disabled variant="filled"/>
                                            <label>EDT</label>
                                        </IftaLabel>
                                    </div>

                                    <div>
                                        <IftaLabel>
                                            <InputText v-model="updateForm.estimated_time_of_arrival" class="w-full"
                                                       disabled variant="filled"/>
                                            <label>ETA</label>
                                        </IftaLabel>
                                    </div>

                                    <div>
                                        <IftaLabel>
                                            <InputText v-model="updateForm.vessel_name" class="w-full" disabled
                                                       variant="filled"/>
                                            <label>Vessel Name</label>
                                        </IftaLabel>
                                    </div>

                                    <div>
                                        <IftaLabel>
                                            <InputText v-model="updateForm.voyage_number" class="w-full" disabled
                                                       variant="filled"/>
                                            <label>Voyage Number</label>
                                        </IftaLabel>
                                    </div>

                                    <div>
                                        <IftaLabel>
                                            <InputText v-model="updateForm.shipping_line" class="w-full" disabled
                                                       variant="filled"/>
                                            <label>Shipping Line</label>
                                        </IftaLabel>
                                    </div>

                                    <div>
                                        <IftaLabel>
                                            <InputText v-model="updateForm.port_of_loading" class="w-full" disabled
                                                       variant="filled"/>
                                            <label>Port of Loading</label>
                                        </IftaLabel>
                                    </div>

                                    <div>
                                        <IftaLabel>
                                            <InputText v-model="updateForm.port_of_discharge" class="w-full" disabled
                                                       variant="filled"/>
                                            <label>Port of Discharge</label>
                                        </IftaLabel>
                                    </div>

                                    <div>
                                        <IftaLabel>
                                            <InputText v-model="updateForm.loading_started_at" class="w-full" disabled
                                                       variant="filled"/>
                                            <label>Loading Started Time</label>
                                        </IftaLabel>
                                    </div>

                                    <div>
                                        <IftaLabel>
                                            <InputText v-model="updateForm.loading_ended_at" class="w-full" disabled
                                                       variant="filled"/>
                                            <label>Loading End Time</label>
                                        </IftaLabel>
                                    </div>

                                    <div>
                                        <IftaLabel>
                                            <InputText v-model="updateForm.status" class="w-full" disabled
                                                       variant="filled"/>
                                            <label>Last Status</label>
                                        </IftaLabel>
                                    </div>

                                    <div>
                                        <IftaLabel>
                                            <InputText v-model="updateForm.note" class="w-full"
                                                       placeholder="Type something..." variant="filled"/>
                                            <label>note</label>
                                        </IftaLabel>
                                        <InputError :message="updateForm.errors.note"/>
                                    </div>

                                    <div>
                                        <IftaLabel>
                                            <DatePicker v-model="updateForm.reached_date" class="w-full"
                                                        date-format="yy-mm-dd" icon-display="input"
                                                        placeholder="Set Reached Date" show-icon variant="filled"/>
                                            <label>Reached Date</label>
                                        </IftaLabel>
                                        <InputError :message="updateForm.errors.reached_date"/>
                                    </div>

                                    <div>
                                        <IftaLabel>
                                            <Checkbox v-model="updateForm.is_reached" binary inputId="is_reached"/>
                                            <label class="font-medium text-sm ml-5" for="is_reached"> Reached
                                                Destination? </label>
                                        </IftaLabel>
                                        <InputError :message="updateForm.errors.is_reached"/>
                                    </div>

                                    <div>
                                        <IftaLabel>
                                            <Checkbox v-model="updateForm.is_returned" binary inputId="is_reached"/>
                                            <label class="font-medium text-sm ml-5" for="is_reached"> Is
                                                Returned? </label>
                                        </IftaLabel>
                                        <InputError :message="updateForm.errors.is_returned"/>
                                    </div>

                                    <div class="text-right col-span-1 sm:col-span-2 lg:col-span-4">
                                        <Button :class="{ 'opacity-25': updateForm.processing }"
                                                :disabled="updateForm.processing" icon="pi pi-save" label="Save Changes"
                                                severity="info" size="small" @click="handleUpdateContainer"/>
                                    </div>
                                </div>
                            </TabPanel>

                            <!-- Documents Tab -->
                            <TabPanel value="4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                    <div>
                                        <IftaLabel>
                                            <InputText v-model="documentForm.name" class="w-full" variant="filled"/>
                                            <label>Document Name</label>
                                        </IftaLabel>
                                        <InputError :message="documentForm.errors.name"/>
                                    </div>

                                    <div>
                                        <IftaLabel>
                                            <DatePicker v-model="documentForm.date" class="w-full" variant="filled"/>
                                            <label>Date</label>
                                        </IftaLabel>
                                        <InputError :message="documentForm.errors.date"/>
                                    </div>

                                    <div class="col-span-1 md:col-span-2 text-right">
                                        <Button icon="pi pi-file-pdf" label="Generate PDF" size="small"/>
                                    </div>
                                </div>
                            </TabPanel>
                        </TabPanels>
                    </Tabs>
                </template>
            </Card>
        </div>
    </AppLayout>

    <LoadedShipmentDetailDialog :air-container-options="airContainerOptions" :container="selectedContainer"
                                :container-status="containerStatus" :sea-container-options="seaContainerOptions"
                                :show="showConfirmLoadedShipmentModal"
                                @close="closeModal"
                                @update:show="showConfirmLoadedShipmentModal = $event"/>

    <AddVesselModal
        :vessel-schedule="vesselSchedule"
        :visible="showConfirmAddVesselModal"
        @close="closeAddVesselModal"
        @reloadPage="reloadPage"
        @update:visible="showConfirmAddVesselModal = $event"
    />
</template>
