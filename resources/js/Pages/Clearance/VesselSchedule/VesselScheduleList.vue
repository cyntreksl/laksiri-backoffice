<script setup>
import {onMounted, ref, watch} from "vue";
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
import AddHBLModal from "@/Pages/Common/Dialog/Container/Dialog/AddHBLModal.vue";

const props = defineProps({
    vesselSchedules: {
        type: Object,
        default: () => ({}),
    },
    containers: {
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
const selectedContainer = ref(props.containers[0] ?? {});
const containerData = ref({});
const filteredHBLS = ref([]);
const filteredMHBLS = ref([]);
const containerPaymentData = ref({});
const isContainerPayment = ref(false);
const isFinanceApproved = ref(false);

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
        }else{
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

const showConfirmAddVesselModal = ref(false);
const confirmAddVesselModal = () => {
    showConfirmAddVesselModal.value = true;
};

const closeAddVesselModal = () => {
    showConfirmAddVesselModal.value = false;
}

const reloadPage = () => {
    window.location.reload();
}

</script>

<template>
    <AppLayout title="Vessel Schedule">
        <template #header>Vessel Schedule</template>

        <Breadcrumb/>

        <div class="mt-5 space-x-2">
            <h1 class="text-3xl ml-1 font-medium text-gray-700">
                Vessel Schedule
            </h1>
            <div class="flex items-center space-x-2 text-gray-400">
                <div>{{ vesselSchedules?.start_date }}</div>
                <div><i class="ti ti-arrow-narrow-right text-xl" v-if="vesselSchedules?.end_date"></i></div>
                <div>{{ vesselSchedules?.end_date }}</div>
            </div>
        </div>

        <div class="grid grid-cols-12 gap-4 my-5">
            <!-- Container List -->
            <Card class="col-span-4 border-2 border-gray-200 !shadow-none">
                <template #title>
                    <div class="flex justify-between items-center">
                        <div class="text-lg font-semibold">Available Vessels</div>
                        <Button icon="pi pi-plus"
                                label="Add Vessel To Schedule"
                                size="small" @click.prevent="confirmAddVesselModal()"/>
                    </div>
                </template>
                <template #subtitle>{{ containers.length }} Vessels</template>
                <template #content>
                    <VirtualScroller :item-size="vesselSchedules?.containers.length"
                                     :items="vesselSchedules?.containers" style="height: 400px">>
                        <template v-slot:item="{ item, options }">
                            <Card
                                :class="[
                                    '!bg-transparent cursor-pointer !shadow-none transition-all duration-300 ease-in-out border-2 mb-3',
                                    selectedContainer?.id === item?.id
                                    ? '!border-none !bg-gradient-to-r !from-gray-700 !via-gray-800 !to-gray-900 !text-white'
                                    : 'border-black hover:bg-gradient-to-r hover:from-gray-700 hover:via-gray-800 hover:to-gray-900 hover:text-white'
                                ]"
                                style="height: 200px"
                                @click="selectedContainer = item"
                            >
                                <template #content>
                                    <div class="flex justify-between text-sm">
                                        <div>{{ item?.container_type }}</div>
                                        <div>{{ item?.cargo_type }}</div>
                                        <div>{{ item?.warehouse.name }}</div>
                                    </div>
                                    <div class="my-8 flex items-center justify-between">
                                        <h1 class="text-3xl font-medium">{{ item?.reference }}</h1>
                                        <i class="ti ti-ship text-4xl"></i>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <div>{{ item?.bl_number }}</div>
                                        <div>{{ item?.awb_number }}</div>
                                    </div>
                                </template>
                            </Card>
                        </template>
                    </VirtualScroller>

                </template>
            </Card>

            <!-- Tabs Panel -->
            <Card class="col-span-8 border-2 border-gray-200 !shadow-none">
                <template #title>
                    <div class="flex justify-between">
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
                                <Button icon="pi pi-eye" rounded severity="info" size="small" variant="text" @click.prevent="showConfirmLoadedShipmentModal = !showConfirmLoadedShipmentModal"/>
                            </div>
                        </div>
                        <div class="space-x-2">
                            <Button v-tooltip.left="'Clear Vessel'" icon="pi pi-check" rounded severity="success"
                                    size="small" variant="outlined" @click.prevent="confirmVesselClear()"/>
                            <Button v-tooltip.left="'Mark As Return'" icon="pi pi-times" rounded severity="danger"
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
                            <Tab value="3">Documents</Tab>
                        </TabList>
                        <TabPanels>
                            <!-- HBLs Tab -->
                            <TabPanel value="0">
                                <DataTable :rows="10" :rowsPerPageOptions="[5, 10, 20, 50, 100]" :value="filteredHBLS"
                                           paginator row-hover
                                           tableStyle="min-width: 50rem">
                                    <template #empty>No HBLs found.</template>
                                    <Column class="font-bold" field="hbl_number" header="HBL"></Column>
                                    <Column field="packages_count" header="Packages">
                                        <template #body="slotProps">
                                            <div class="flex items-center">
                                                <i class="ti ti-package mr-1 text-blue-500" style="font-size: 1rem"></i>
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
                                            <div class="text-gray-500 text-sm">{{ slotProps.data.consignee_address }}
                                            </div>
                                        </template>
                                    </Column>
                                </DataTable>
                            </TabPanel>

                            <!-- MHBLs Tab -->
                            <TabPanel value="1">
                                <DataTable :rows="10" :rowsPerPageOptions="[5, 10, 20, 50, 100]" :value="filteredMHBLS"
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
                                                <i class="ti ti-package mr-1 text-blue-500" style="font-size: 1rem"></i>
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
                                            <div class="text-gray-500 text-sm">{{ slotProps.data.consignee_address }}
                                            </div>
                                        </template>
                                    </Column>
                                </DataTable>
                            </TabPanel>

                            <!-- Payments Tab -->
                            <TabPanel value="2">
                                <form @submit.prevent="handleContainerPaymentCreate">
                                    <div class="grid grid-cols-2 gap-5">
                                        <div>
                                            <IftaLabel>
                                                <InputNumber
                                                    v-model="form.do_charge"
                                                    :maxFractionDigits="2"
                                                    :minFractionDigits="2"
                                                    class="w-full" inputId="do-charge"
                                                    min="0" step="any" variant="filled"
                                                    :disabled="isFinanceApproved"
                                                />
                                                <label for="do-charge">DO Charge</label>
                                            </IftaLabel>
                                            <InputError :message="form.errors.do_charge"/>
                                        </div>

                                        <div>
                                            <IftaLabel>
                                                <InputNumber v-model="form.demurrage_charge" :maxFractionDigits="2"
                                                             :minFractionDigits="2" class="w-full"
                                                             inputId="demurrage-charge" min="0" step="any"
                                                             variant="filled" :disabled="isFinanceApproved"/>
                                                <label for="demurrage-charge">Demurrage Charge</label>
                                            </IftaLabel>
                                            <InputError :message="form.errors.demurrage_charge"/>
                                        </div>

                                        <div>
                                            <IftaLabel>
                                                <InputNumber v-model="form.assessment_charge" :maxFractionDigits="2"
                                                             :minFractionDigits="2" class="w-full"
                                                             inputId="assessment-charge" min="0" step="any"
                                                             variant="filled" :disabled="isFinanceApproved"/>
                                                <label for="assessment-charge">Assessment Charge</label>
                                            </IftaLabel>
                                            <InputError :message="form.errors.assessment_charge"/>
                                        </div>

                                        <div>
                                            <IftaLabel>
                                                <InputNumber v-model="form.slpa_charge" :maxFractionDigits="2"
                                                             :minFractionDigits="2" class="w-full" inputId="slap-charge"
                                                             min="0" step="any" variant="filled" :disabled="isFinanceApproved"/>
                                                <label for="slap-charge">SLAP Charge</label>
                                            </IftaLabel>
                                            <InputError :message="form.errors.slpa_charge"/>
                                        </div>

                                        <div>
                                            <IftaLabel>
                                                <InputNumber v-model="form.refund_charge" :maxFractionDigits="2"
                                                             :minFractionDigits="2" class="w-full" inputId="refund-charge"
                                                             min="0" step="any" variant="filled" :disabled="isFinanceApproved"/>
                                                <label for="refund-charge">Refund</label>
                                            </IftaLabel>
                                            <InputError :message="form.errors.refund_charge"/>
                                        </div>

                                        <div>
                                            <IftaLabel>
                                                <InputNumber v-model="form.clearance_charge" :maxFractionDigits="2"
                                                             :minFractionDigits="2" class="w-full"
                                                             inputId="clearance-charge" min="0" step="any"
                                                             variant="filled" :disabled="isFinanceApproved"/>
                                                <label for="clearance-charge">Clearance Charge</label>
                                            </IftaLabel>
                                            <InputError :message="form.errors.clearance_charge"/>
                                        </div>

                                        <div class="col-span-2 text-right" v-if="!isFinanceApproved">
                                            <Button icon="pi pi-save" :label="isContainerPayment ? 'Edit Payment' : 'Save Payment'"  size="small" type="submit"/>
                                        </div>
                                    </div>
                                </form>
                            </TabPanel>

                            <!-- Documents Tab -->
                            <TabPanel value="3">
                                <div class="grid grid-cols-2 gap-5">
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

                                    <div class="col-span-2 text-right">
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

    <LoadedShipmentDetailDialog :air-container-options="airContainerOptions" :container="selectedContainer" :container-status="containerStatus" :sea-container-options="seaContainerOptions" :show="showConfirmLoadedShipmentModal"
                                @close="closeModal"
                                @update:show="showConfirmLoadedShipmentModal = $event" />

    <AddVesselModal
        :visible="showConfirmAddVesselModal"
        :vessel-schedule="vesselSchedules"
        @close="closeAddVesselModal"
        @reloadPage="reloadPage"
        @update:visible="showConfirmAddVesselModal = $event"
    />
</template>
