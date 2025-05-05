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
import {useForm, usePage} from "@inertiajs/vue3";
import DataTable from "primevue/datatable";
import Column from "primevue/column";

const props = defineProps({
    vesselSchedules: {
        type: Object,
        default: () => ({}),
    },
    containers: {
        type: Object,
        default: () => ({}),
    },
});

const form = useForm({
    do_charges: 0,
    demurrage_charges: 0,
    assessment_charges: 0,
    slap_charges: 0,
    refund: 0,
    clearance_charges: 0
});

const documentForm = useForm({
    name: "",
    date: "",
});

const selectedContainer = ref(props.containers[0]);
const containerData = ref({});
const filteredHBLS = ref([]);
const filteredMHBLS = ref([]);
const filteredMHBLsLHBL = ref([]);

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
            throw new Error(`Failed to fetch container ${props.container.id}`);
        }
        containerData.value = (await response.json())[0];
        hbls();
        mhbls();
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
</script>

<template>
    <AppLayout title="Vessel Schedule">
        <template #header>Vessel Schedule</template>

        <Breadcrumb />

        <div class="flex items-center mt-5 space-x-2">
            <h1 class="text-3xl ml-2 font-medium text-gray-700">
                Vessel Schedule {{ vesselSchedules?.start_date }} to {{ vesselSchedules?.end_date }}
            </h1>
        </div>

        <div class="grid grid-cols-12 gap-4 my-5">
            <!-- Container List -->
            <Card class="col-span-4 border-2 border-gray-200 !shadow-none">
                <template #title>
                    <div class="flex justify-between">
                        <div>
                            <div class="flex items-center space-x-2 text-xs text-gray-400">
                                <div>{{ vesselSchedules?.start_date }}</div>
                                <div><i class="ti ti-arrow-narrow-right text-xl"></i></div>
                                <div>{{ vesselSchedules?.end_date }}</div>
                            </div>
                            <div>Available Vessels</div>
                        </div>
                    </div>
                </template>
                <template #subtitle>{{ containers.length }} Vessels</template>
                <template #content>
                    <div class="space-y-5">
                        <Card
                            v-for="(container, index) in vesselSchedules?.containers"
                            :key="index"
                            @click="selectedContainer = container"
                            :class="[
                                '!bg-transparent cursor-pointer !shadow-none transition-all duration-300 ease-in-out border-2',
                                selectedContainer?.reference === container.reference
                                    ? 'bg-gradient-to-r from-gray-700 via-gray-800 to-gray-900 text-white'
                                    : 'border-black hover:bg-gradient-to-r hover:from-gray-700 hover:via-gray-800 hover:to-gray-900 hover:text-white'
                            ]"
                        >
                            <template #content>
                                <div class="flex justify-between text-sm">
                                    <div>{{ container?.container_type }}</div>
                                    <div>{{ container?.cargo_type }}</div>
                                    <div>{{ container?.warehouse.name }}</div>
                                </div>
                                <div class="my-8 flex items-center justify-between">
                                    <h1 class="text-3xl font-medium">{{ container?.reference }}</h1>
                                    <i class="ti ti-ship text-4xl"></i>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <div>ONEY344OL333333</div>
                                    <div>12547845</div>
                                </div>
                            </template>
                        </Card>
                    </div>
                </template>
            </Card>

            <!-- Tabs Panel -->
            <Card class="col-span-8 border-2 border-gray-200 !shadow-none">
                <template #title>
                    <div class="flex justify-between">
                        <div>
                            <div class="flex items-center space-x-2 text-xs text-gray-400">
                                <div class="flex items-center">
                                    <i class="ti ti-plane-departure text-xl mr-2"></i> RIYADH
                                </div>
                                <div><i class="ti ti-arrow-narrow-right text-xl"></i></div>
                                <div class="flex items-center">
                                    <i class="ti ti-plane-arrival text-xl mr-2"></i> COLOMBO
                                </div>
                            </div>
                            <div class="flex items-center space-x-2">
                                <div class="text-xl">{{selectedContainer.reference}}</div>
                                <Button icon="pi pi-eye" rounded severity="info" size="small" variant="text" />
                            </div>
                        </div>
                        <div class="space-x-2">
                            <Button v-tooltip.left="'Clear Vessel'" icon="pi pi-check" rounded severity="success" size="small" variant="outlined" />
                            <Button v-tooltip.left="'Mark As Return'" icon="pi pi-times" rounded severity="danger" size="small" variant="outlined" />
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
                                <DataTable :rows="10" :rowsPerPageOptions="[5, 10, 20, 50, 100]" :value="filteredHBLS" paginator row-hover
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
                                            <div class="text-gray-500 text-sm">{{slotProps.data.nic}}</div>
                                            <div class="text-gray-500 text-sm">{{slotProps.data.address}}</div>
                                        </template>
                                    </Column>
                                    <Column field="contact_number" header="Contact"></Column>
                                    <Column field="consignee_name" header="Consignee">
                                        <template #body="slotProps">
                                            <div>{{ slotProps.data.consignee_name }}</div>
                                            <div class="text-gray-500 text-sm">{{slotProps.data.consignee_address}}</div>
                                        </template>
                                    </Column>
                                </DataTable>
                            </TabPanel>

                            <!-- HBLs Tab -->
                            <TabPanel value="1">
                                <DataTable :rows="10" :rowsPerPageOptions="[5, 10, 20, 50, 100]" :value="filteredMHBLS" paginator row-hover
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
                                            <div class="text-gray-500 text-sm">{{slotProps.data.nic}}</div>
                                            <div class="text-gray-500 text-sm">{{slotProps.data.address}}</div>
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
                                            <div class="text-gray-500 text-sm">{{slotProps.data.consignee_address}}</div>
                                        </template>
                                    </Column>
                                </DataTable>
                            </TabPanel>

                            <!-- Payments Tab -->
                            <TabPanel value="2">
                                <div class="grid grid-cols-2 gap-5">
                                    <div>
                                        <IftaLabel>
                                            <InputNumber v-model="form.do_charges" :maxFractionDigits="2" :minFractionDigits="2" class="w-full" inputId="do-charge" min="0" step="any" variant="filled" />
                                            <label for="do-charge">DO Charge</label>
                                        </IftaLabel>
                                        <InputError :message="form.errors.do_charges" />
                                    </div>

                                    <div>
                                        <IftaLabel>
                                            <InputNumber v-model="form.demurrage_charges" :maxFractionDigits="2" :minFractionDigits="2" class="w-full" inputId="demurrage-charge" min="0" step="any" variant="filled" />
                                            <label for="demurrage-charge">Demurrage Charge</label>
                                        </IftaLabel>
                                        <InputError :message="form.errors.demurrage_charges" />
                                    </div>

                                    <div>
                                        <IftaLabel>
                                            <InputNumber v-model="form.assessment_charges" :maxFractionDigits="2" :minFractionDigits="2" class="w-full" inputId="assessment-charge" min="0" step="any" variant="filled" />
                                            <label for="assessment-charge">Assessment Charge</label>
                                        </IftaLabel>
                                        <InputError :message="form.errors.assessment_charges" />
                                    </div>

                                    <div>
                                        <IftaLabel>
                                            <InputNumber v-model="form.slap_charges" :maxFractionDigits="2" :minFractionDigits="2" class="w-full" inputId="slap-charge" min="0" step="any" variant="filled" />
                                            <label for="slap-charge">SLAP Charge</label>
                                        </IftaLabel>
                                        <InputError :message="form.errors.slap_charges" />
                                    </div>

                                    <div>
                                        <IftaLabel>
                                            <InputNumber v-model="form.refund" :maxFractionDigits="2" :minFractionDigits="2" class="w-full" inputId="refund-charge" min="0" step="any" variant="filled" />
                                            <label for="refund-charge">Refund</label>
                                        </IftaLabel>
                                        <InputError :message="form.errors.refund" />
                                    </div>

                                    <div>
                                        <IftaLabel>
                                            <InputNumber v-model="form.clearance_charges" :maxFractionDigits="2" :minFractionDigits="2" class="w-full" inputId="clearance-charge" min="0" step="any" variant="filled" />
                                            <label for="clearance-charge">Clearance Charge</label>
                                        </IftaLabel>
                                        <InputError :message="form.errors.clearance_charges" />
                                    </div>

                                    <div class="col-span-2 text-right">
                                        <Button icon="pi pi-save" label="Save Payments" size="small" />
                                    </div>
                                </div>
                            </TabPanel>

                            <!-- Documents Tab -->
                            <TabPanel value="3">
                                <div class="grid grid-cols-2 gap-5">
                                    <div>
                                        <IftaLabel>
                                            <InputText v-model="documentForm.name" class="w-full" variant="filled" />
                                            <label>Document Name</label>
                                        </IftaLabel>
                                        <InputError :message="documentForm.errors.name" />
                                    </div>

                                    <div>
                                        <IftaLabel>
                                            <DatePicker v-model="documentForm.date" class="w-full" variant="filled" />
                                            <label>Date</label>
                                        </IftaLabel>
                                        <InputError :message="documentForm.errors.date" />
                                    </div>

                                    <div class="col-span-2 text-right">
                                        <Button icon="pi pi-file-pdf" label="Generate PDF" size="small" />
                                    </div>
                                </div>
                            </TabPanel>
                        </TabPanels>
                    </Tabs>
                </template>
            </Card>
        </div>
    </AppLayout>
</template>
