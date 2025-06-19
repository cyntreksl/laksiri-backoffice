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
import LoadedShipmentDetailDialog from "@/Pages/Common/Dialog/Container/Index.vue";
import {useConfirm} from "primevue/useconfirm";
import {push} from "notivue";
import AddVesselModal from "@/Pages/Clearance/VesselSchedule/Partials/AddVesselModal.vue";
import Checkbox from "primevue/checkbox";
import Skeleton from "primevue/skeleton";
import moment from "moment";
import RequestsList from "@/Pages/Clearance/VesselSchedule/RequestsList.vue";
import InfoDisplay from "@/Pages/Common/Components/InfoDisplay.vue";
import TreeTable from 'primevue/treetable';

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
const loadingContainerData = ref(false);
const loadingPaymentData = ref(false);
const selectedKey = ref();

const form = useForm({
    container_id: selectedContainer.value.id ?? '',
    do_charge: 0,
    demurrage_charge: 0,
    assessment_charge: 0,
    slpa_charge: 0,
    refund_charge: 0,
    clearance_charge: 0
});

const fetchLoadedContainer = async () => {
    loadingContainerData.value = true;
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
    }finally {
        loadingContainerData.value = false;
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

const fetchContainerPayment = async () => {
    loadingPaymentData.value = true;
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
    } finally {
        loadingPaymentData.value = false;
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

const confirmRemoveContainer = (ContainerId) => {
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
    note: selectedContainer.value.note,
    is_reached: Boolean(selectedContainer.value.is_reached),
    reached_date: selectedContainer.value?.reached_date,
    return_date: selectedContainer.value?.return_date,
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

watch(
    () => selectedContainer.value,
    (newContainer) => {
        fetchLoadedContainer();
        // Update the form fields when selectedContainer changes
        updateForm.note = newContainer?.note ?? '';
        updateForm.is_reached = Boolean(newContainer?.is_reached);
        updateForm.is_returned = Boolean(newContainer?.is_returned);
        updateForm.reached_date = newContainer?.reached_date ?? null;
        updateForm.return_date = newContainer?.return_date ?? null;
    },
    { deep: true }
);

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

    if (moment(updateForm.return_date).isValid()) {
        updateForm.return_date = moment(updateForm.return_date).format("YYYY-MM-DD");
    } else {
        updateForm.return_date = null;
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

const treeTableData = computed(() => {
    if (!props.vesselSchedule?.clearance_containers) {
        selectedContainer.value = null;
        return [];
    }

    const daysOfWeek = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
    const weekGroups = [];
    let firstShipmentSelected = false;

    daysOfWeek.forEach(day => {
        const dayNode = {
            key: day,
            data: {
                day: day,
                date: '',
                itemsCount: 0,
                type: 'day'
            },
            children: []
        };

        const dayItems = props.vesselSchedule.clearance_containers.filter(item => {
            if (!item.estimated_time_of_arrival || !moment(item.estimated_time_of_arrival).isValid()) {
                return false;
            }
            return moment(item.estimated_time_of_arrival).format('dddd') === day;
        });

        if (dayItems.length > 0) {
            const m = moment(dayItems[0].estimated_time_of_arrival);
            dayNode.data.date = m.format('MMM Do, YYYY');
            dayNode.data.itemsCount = dayItems.length;

            dayItems.forEach((item, index) => {
                const shipmentNode = {
                    key: item.id,
                    data: {
                        ...item,
                        type: 'shipment'
                    }
                };
                dayNode.children.push(shipmentNode);

                // Select the first shipment in the first day with shipments
                if (!firstShipmentSelected && index === 0) {
                    selectedContainer.value = item;
                    firstShipmentSelected = true;
                }
            });
        }

        weekGroups.push(dayNode);
    });

    return weekGroups;
});

const isPaymentInputDisabled = computed(() => {
    return isFinanceApproved.value ||
        usePage().props.auth.user.roles[0]?.name === 'finance Team';
});

const onNodeSelect = (event) => {
    if (event.data.type === 'shipment') {
        selectedContainer.value = event.data;
    }
};
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
                    <TreeTable
                        v-model:selectionKeys="selectedKey"
                        :meta-key-selection="false"
                        :value="treeTableData"
                        scroll-height="1000px"
                        scrollable
                        selection-mode="single"
                        @node-select="onNodeSelect"
                    >
                        <Column expander field="day">
                            <template #body="{ node }">
                                <div>
                                    <div v-if="node.data.type === 'day'" class="flex items-center">
                                        <h2 class="font-semibold">
                                            {{ node.data.day }}
                                            <span>({{ node.data.itemsCount || 0 }})</span>
                                        </h2>
                                        <span v-if="node.data.date" class="ml-2 text-gray-500 text-xs">{{ node.data.date }}</span>
                                        <span v-if="node.data.itemsCount === 0" class="ml-2 text-gray-500 text-xs italic">No Shipments</span>
                                    </div>

                                    <div v-else>
                                        <div class="grid grid-cols-2">
                                            <div class="space-y-2">
                                                <InfoDisplay :value="node.data.reference " label="Reference"/>
                                                <InfoDisplay v-if="node.data.type === 'shipment'" :value="node.data.container_type " label="Type"/>
                                            </div>
                                            <div class="space-y-2">
                                                <InfoDisplay v-if="node.data.type === 'shipment'" :value="node.data.estimated_time_of_arrival " label="ETA"/>
                                                <InfoDisplay v-if="node.data.type === 'shipment'" :value="node.data.cargo_type " label="Cargo Type"/>
                                                <InfoDisplay v-if="node.data.type === 'shipment'" :value="node.data.warehouse?.name " label="Warehouse"/>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </Column>

                        <Column>
                            <template #body="{ node }">
                                <div v-if="node.data.type === 'shipment'" class="flex items-center space-x-2">
                                    <Button v-tooltip="'Remove From Vessel Schedule'" class="p-button-sm p-button-text p-button-danger"
                                            icon="pi pi-trash"
                                            @click="confirmRemoveContainer(node.key)" />
                                </div>
                            </template>
                        </Column>
                    </TreeTable>
                </template>
            </Card>

            <!-- Tabs Panel -->
            <Card class="col-span-12 lg:col-span-9 border-2 border-gray-200 !shadow-none">
                <template #title>
                    <div v-if="loadingContainerData" class="flex flex-col space-y-3">
                        <div class="flex items-center space-x-4">
                            <Skeleton height="1rem" width="8rem"></Skeleton>
                            <Skeleton height="1rem" width="2rem"></Skeleton>
                            <Skeleton height="1rem" width="8rem"></Skeleton>
                        </div>
                        <div class="flex items-center justify-between">
                            <Skeleton height="2rem" width="12rem"></Skeleton>
                            <Skeleton height="2.5rem" width="10rem"></Skeleton>
                        </div>
                    </div>
                    <div v-else class="flex flex-col md:flex-row md:justify-between gap-4">
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
                        <a :href="route('clearance.vessel-schedule.download-shipment-release-pdf', selectedContainer.id)">
                            <Button icon="pi pi-file-pdf" label="Download Release PDF" severity="success"
                                    size="small"/>
                        </a>
                    </div>
                </template>

                <template #content>
                    <Tabs v-if="selectedContainer" value="0">
                        <TabList>
                            <Tab value="0">HBLS</Tab>
                            <Tab value="1">MHBLs</Tab>
                            <Tab value="2">Payments</Tab>
                            <Tab value="3">Shipment Details</Tab>
                        </TabList>
                        <TabPanels>
                            <!-- HBLs Tab -->
                            <TabPanel value="0">
                                <div v-if="loadingContainerData" class="space-y-4">
                                    <div v-for="i in 5" :key="i" class="flex items-center space-x-4 p-4 border rounded">
                                        <Skeleton height="1rem" width="6rem"></Skeleton>
                                        <Skeleton height="1rem" width="4rem"></Skeleton>
                                        <div class="flex-1">
                                            <Skeleton class="mb-2" height="1rem" width="8rem"></Skeleton>
                                            <Skeleton height="0.8rem" width="6rem"></Skeleton>
                                        </div>
                                        <Skeleton height="1rem" width="5rem"></Skeleton>
                                        <div class="flex-1">
                                            <Skeleton class="mb-2" height="1rem" width="7rem"></Skeleton>
                                            <Skeleton height="0.8rem" width="10rem"></Skeleton>
                                        </div>
                                    </div>
                                </div>
                                <div v-else class="overflow-x-auto">
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
                                <div v-if="loadingContainerData" class="space-y-4">
                                    <div v-for="i in 5" :key="i" class="flex items-center space-x-4 p-4 border rounded">
                                        <Skeleton height="1rem" width="6rem"></Skeleton>
                                        <Skeleton height="1rem" width="5rem"></Skeleton>
                                        <Skeleton height="1rem" width="4rem"></Skeleton>
                                        <div class="flex-1">
                                            <Skeleton class="mb-2" height="1rem" width="8rem"></Skeleton>
                                            <Skeleton height="0.8rem" width="6rem"></Skeleton>
                                        </div>
                                        <Skeleton height="1rem" width="5rem"></Skeleton>
                                        <div class="flex-1">
                                            <Skeleton class="mb-2" height="1rem" width="7rem"></Skeleton>
                                            <Skeleton height="0.8rem" width="10rem"></Skeleton>
                                        </div>
                                    </div>
                                </div>
                                <div v-else class="overflow-x-auto">
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
                                <div v-if="loadingPaymentData" class="space-y-4">
                                    <div v-for="i in 5" :key="i" class="flex items-center space-x-4 p-4 border rounded">
                                        <Skeleton height="1rem" width="6rem"></Skeleton>
                                        <Skeleton height="1rem" width="5rem"></Skeleton>
                                        <Skeleton height="1rem" width="4rem"></Skeleton>
                                        <div class="flex-1">
                                            <Skeleton class="mb-2" height="1rem" width="8rem"></Skeleton>
                                            <Skeleton height="0.8rem" width="6rem"></Skeleton>
                                        </div>
                                        <Skeleton height="1rem" width="5rem"></Skeleton>
                                        <div class="flex-1">
                                            <Skeleton class="mb-2" height="1rem" width="7rem"></Skeleton>
                                            <Skeleton height="0.8rem" width="10rem"></Skeleton>
                                        </div>
                                    </div>
                                </div>
                                <template v-else>
                                    <form @submit.prevent="handleContainerPaymentCreate">
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                            <div>
                                                <IftaLabel>
                                                    <InputNumber
                                                        v-model="form.do_charge"
                                                        :disabled="isPaymentInputDisabled"
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
                                                                 :disabled="isPaymentInputDisabled"
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
                                                                 :disabled="isPaymentInputDisabled"
                                                                 :maxFractionDigits="2" :minFractionDigits="2"
                                                                 class="w-full" inputId="assessment-charge" min="0"
                                                                 step="any" variant="filled"/>
                                                    <label for="assessment-charge">Assessment Charge</label>
                                                </IftaLabel>
                                                <InputError :message="form.errors.assessment_charge"/>
                                            </div>

                                            <div>
                                                <IftaLabel>
                                                    <InputNumber v-model="form.slpa_charge" :disabled="isPaymentInputDisabled"
                                                                 :maxFractionDigits="2" :minFractionDigits="2"
                                                                 class="w-full"
                                                                 inputId="slap-charge" min="0" step="any" variant="filled"/>
                                                    <label for="slap-charge">SLAP Charge</label>
                                                </IftaLabel>
                                                <InputError :message="form.errors.slpa_charge"/>
                                            </div>

                                            <div>
                                                <IftaLabel>
                                                    <InputNumber v-model="form.refund_charge" :disabled="isPaymentInputDisabled"
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
                                                                 :disabled="isPaymentInputDisabled"
                                                                 :maxFractionDigits="2" :minFractionDigits="2"
                                                                 class="w-full" inputId="clearance-charge" min="0"
                                                                 step="any" variant="filled"/>
                                                    <label for="clearance-charge">Clearance Charge</label>
                                                </IftaLabel>
                                                <InputError :message="form.errors.clearance_charge"/>
                                            </div>

                                            <template v-if="$page.props.auth.user.roles[0]?.name !== 'finance Team'">
                                                <div v-if="!isFinanceApproved"  class="col-span-1 md:col-span-2 text-right">
                                                    <Button :label="isContainerPayment ? 'Edit Payment' : 'Save Payment'"
                                                            icon="pi pi-save" size="small" type="submit"/>
                                                </div>
                                            </template>
                                        </div>
                                    </form>

                                    <RequestsList :container-id="selectedContainer.id"/>
                                </template>
                            </TabPanel>

                            <!-- Shipment Details Tab -->
                            <TabPanel value="3">
                                <div v-if="loadingContainerData" class="space-y-4">
                                    <div v-for="i in 5" :key="i" class="flex items-center space-x-4 p-4 border rounded">
                                        <Skeleton height="1rem" width="6rem"></Skeleton>
                                        <Skeleton height="1rem" width="5rem"></Skeleton>
                                        <Skeleton height="1rem" width="4rem"></Skeleton>
                                        <div class="flex-1">
                                            <Skeleton class="mb-2" height="1rem" width="8rem"></Skeleton>
                                            <Skeleton height="0.8rem" width="6rem"></Skeleton>
                                        </div>
                                        <Skeleton height="1rem" width="5rem"></Skeleton>
                                        <div class="flex-1">
                                            <Skeleton class="mb-2" height="1rem" width="7rem"></Skeleton>
                                            <Skeleton height="0.8rem" width="10rem"></Skeleton>
                                        </div>
                                    </div>
                                </div>
                                <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 sm:gap-10">

                                    <InfoDisplay :value="selectedContainer.cargo_type" label="Cargo Mode"/>
                                    <InfoDisplay :value="selectedContainer.container_type" label="Container Type"/>
                                    <InfoDisplay :value="selectedContainer.reference" label="Reference"/>
                                    <InfoDisplay :value="selectedContainer.container_number" label="Container Number"/>
                                    <InfoDisplay :value="selectedContainer.seal_number" label="Seal Number"/>
                                    <InfoDisplay :value="selectedContainer.bl_number" label="BL Number"/>
                                    <InfoDisplay :value="selectedContainer.estimated_time_of_departure" label="EDT"/>
                                    <InfoDisplay :value="selectedContainer.estimated_time_of_arrival" label="ETA"/>
                                    <InfoDisplay :value="selectedContainer.vessel_name" label="Vessel Name"/>
                                    <InfoDisplay :value="selectedContainer.voyage_number" label="Voyage Number"/>
                                    <InfoDisplay :value="selectedContainer.shipping_line" label="Shipping Line"/>
                                    <InfoDisplay :value="selectedContainer.port_of_loading" label="Port of Loading"/>
                                    <InfoDisplay :value="selectedContainer.port_of_discharge" label="Port of Discharge"/>
                                    <InfoDisplay :value="selectedContainer.loading_started_at" label="Loading Started Time"/>
                                    <InfoDisplay :value="selectedContainer.loading_ended_at" label="Loading End Time"/>
                                    <InfoDisplay :value="selectedContainer.status" label="Last Status"/>

                                    <div>
                                        <IftaLabel>
                                            <InputText v-model="updateForm.note" class="w-full"
                                                       placeholder="Type something..." variant="filled"/>
                                            <label>Note</label>
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
                                            <DatePicker v-model="updateForm.return_date" class="w-full"
                                                        date-format="yy-mm-dd" icon-display="input"
                                                        placeholder="Set Return Date" show-icon variant="filled"/>
                                            <label>Return Date</label>
                                        </IftaLabel>
                                        <InputError :message="updateForm.errors.return_date"/>
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
