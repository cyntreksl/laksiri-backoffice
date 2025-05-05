<script setup>
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
import {useForm} from "@inertiajs/vue3";

const props = defineProps({
    vesselSchedules: {
        type: Object,
        default: () => {
        },
    },
    containers: {
        type: Object,
        default: () => {
        },
    },
});

const form = useForm({
    do_charges: 0,
    demurrage_charges: 0,
    assessment_charges: 0,
    slap_charges: 0,
    refund: 0,
    clearance_charges: 0
})

const documentForm = useForm({
    name: "",
    date: "",
})

</script>

<template>
    <AppLayout title="Vessel Schedule">
        <template #header>Vessel Schedule</template>

        <Breadcrumb/>

        <div class="flex items-center mt-5 space-x-2">
            <h1 class="text-3xl ml-2 font-medium text-gray-700">Vessel Schedule {{vesselSchedules?.start_date}} to {{vesselSchedules?.end_date}}</h1>
        </div>

        <div class="grid grid-cols-12 gap-4 my-5">
            <Card class="col-span-4 border-2 border-gray-200 !shadow-none">
                <template #title>
                    <div class="flex justify-between">
                        <div>
                            <div class="flex items-center space-x-2 text-xs text-gray-400">
                                <div>
                                    {{vesselSchedules?.start_date}}
                                </div>
                                <div>
                                    <i class="ti ti-arrow-narrow-right text-xl"></i>
                                </div>
                                <div>{{vesselSchedules?.end_date}}</div>
                            </div>
                            <div>
                                Available Vessels
                            </div>
                        </div>
                    </div>
                </template>
                <template #subtitle>{{containers.length}} Vessels</template>
                <template #content>
                    <div class="space-y-5">
                        <Card
                            class="!bg-transparent cursor-pointer !shadow-none transition-all duration-300 ease-in-out hover:bg-gradient-to-r hover:from-gray-700 hover:via-gray-800 hover:to-gray-900 hover:text-white border-2 border-black"
                            v-for="(container, index) in vesselSchedules?.containers"
                        >
                            <template #content>
                                <div class="flex justify-between text-sm">
                                    <div>
                                        {{container?.container_type}}
                                    </div>
                                    <div>
                                        {{container?.cargo_type}}
                                    </div>
                                    <div>
                                        {{container?.warehouse.name}}
                                    </div>
                                </div>

                                <div class="my-8 flex items-center justify-between">
                                    <h1 class="text-3xl font-medium">{{container?.reference}}</h1>
                                    <i class="ti ti-ship text-4xl"></i>
                                </div>

                                <div class="flex justify-between text-sm">
                                    <div>
                                        ONEY344OL333333
                                    </div>
                                    <div>
                                        12547845
                                    </div>
                                </div>
                            </template>
                        </Card>


                    </div>
                </template>
            </Card>

            <Card class="col-span-8 border-2 border-gray-200 !shadow-none">
                <template #title>
                    <div class="flex justify-between">
                        <div>
                            <div class="flex items-center space-x-2 text-xs text-gray-400">
                                <div class="flex items-center">
                                    <i class="ti ti-plane-departure text-xl mr-2"></i>
                                    RIYADH
                                </div>
                                <div>
                                    <i class="ti ti-arrow-narrow-right text-xl"></i>
                                </div>
                                <div class="flex items-center">
                                    <i class="ti ti-plane-arrival text-xl mr-2"></i>
                                    COLOMBO
                                </div>
                            </div>
                            <div class="flex items-center space-x-2">
                                <div class="text-xl">
                                    QT-LD-000019
                                </div>
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
                            <Tab value="0">Packages</Tab>
                            <Tab value="1">Payments</Tab>
                            <Tab value="2">Documents</Tab>
                        </TabList>
                        <TabPanels>
                            <TabPanel value="0">
                                <div class="mb-5">
                                    <h3 class="text-xl font-medium">
                                        <i class="pi pi-box text-success mr-2"></i>
                                        HBLs & Packages
                                    </h3>
                                </div>
                                <div class="space-y-1">
                                    <Card class="border border-gray-100 !shadow-none">
                                        <template #title>
                                            <div class="text-sm">
                                                HBL 1
                                            </div>
                                        </template>
                                        <template #content>
                                            <ul class="space-y-1">
                                                <li class="flex justify-between items-center bg-gray-100 p-2 px-5 rounded-lg hover:bg-gray-200">
                                                    <div class="flex items-center">
                                                        <i class="ti ti-package mr-3"></i>
                                                        <div class="font-medium">CARTOON</div>
                                                    </div>
                                                    <div class="text-xs font-medium">12CM x 10CM x 10CM</div>
                                                    <div v-tooltip.left="'Weight'" class="font-medium">120kg</div>
                                                    <div v-tooltip.left="'Quantity'" class="font-medium">1</div>
                                                </li>

                                                <li class="flex justify-between items-center bg-gray-100 p-2 px-5 rounded-lg hover:bg-gray-200">
                                                    <div class="flex items-center">
                                                        <i class="ti ti-package mr-3"></i>
                                                        <div class="font-medium">CARTOON</div>
                                                    </div>
                                                    <div class="text-xs font-medium">12CM x 10CM x 10CM</div>
                                                    <div v-tooltip.left="'Weight'" class="font-medium">120kg</div>
                                                    <div v-tooltip.left="'Quantity'" class="font-medium">1</div>
                                                </li>
                                                <li class="flex justify-between items-center bg-gray-100 p-2 px-5 rounded-lg hover:bg-gray-200">
                                                    <div class="flex items-center">
                                                        <i class="ti ti-package mr-3"></i>
                                                        <div class="font-medium">CARTOON</div>
                                                    </div>
                                                    <div class="text-xs font-medium">12CM x 10CM x 10CM</div>
                                                    <div v-tooltip.left="'Weight'" class="font-medium">120kg</div>
                                                    <div v-tooltip.left="'Quantity'" class="font-medium">1</div>
                                                </li>
                                            </ul>
                                        </template>
                                    </Card>

                                    <Card class="border border-gray-100 !shadow-none">
                                        <template #title>
                                            <div class="text-sm">
                                                HBL 2
                                            </div>
                                        </template>
                                        <template #content>
                                            <ul class="space-y-1">
                                                <li class="flex justify-between items-center bg-gray-100 p-2 px-5 rounded-lg hover:bg-gray-200">
                                                    <div class="flex items-center">
                                                        <i class="ti ti-package mr-3"></i>
                                                        <div class="font-medium">CARTOON</div>
                                                    </div>
                                                    <div class="text-xs font-medium">12CM x 10CM x 10CM</div>
                                                    <div v-tooltip.left="'Weight'" class="font-medium">120kg</div>
                                                    <div v-tooltip.left="'Quantity'" class="font-medium">1</div>
                                                </li>

                                                <li class="flex justify-between items-center bg-gray-100 p-2 px-5 rounded-lg hover:bg-gray-200">
                                                    <div class="flex items-center">
                                                        <i class="ti ti-package mr-3"></i>
                                                        <div class="font-medium">CARTOON</div>
                                                    </div>
                                                    <div class="text-xs font-medium">12CM x 10CM x 10CM</div>
                                                    <div v-tooltip.left="'Weight'" class="font-medium">120kg</div>
                                                    <div v-tooltip.left="'Quantity'" class="font-medium">1</div>
                                                </li>
                                            </ul>
                                        </template>
                                    </Card>
                                </div>
                            </TabPanel>
                            <TabPanel value="1">
                                <div class="grid grid-cols-2 gap-5">
                                    <div>
                                        <IftaLabel>
                                            <InputNumber v-model="form.do_charges" :maxFractionDigits="2" :minFractionDigits="2" class="w-full" inputId="do-charge" min="0" step="any" variant="filled" />
                                            <label for="do-charge">DO Charge</label>
                                        </IftaLabel>
                                        <InputError :message="form.errors.do_charges"/>
                                    </div>

                                    <div>
                                        <IftaLabel>
                                            <InputNumber v-model="form.demurrage_charges" :maxFractionDigits="2" :minFractionDigits="2" class="w-full" inputId="demurrage-charge" min="0" step="any" variant="filled" />
                                            <label for="demurrage-charge">Demurrage Charge</label>
                                        </IftaLabel>
                                        <InputError :message="form.errors.demurrage_charges"/>
                                    </div>

                                    <div>
                                        <IftaLabel>
                                            <InputNumber v-model="form.assessment_charges" :maxFractionDigits="2" :minFractionDigits="2" class="w-full" inputId="assessment-charge" min="0" step="any" variant="filled" />
                                            <label for="assessment-charge">Assessment Charge</label>
                                        </IftaLabel>
                                        <InputError :message="form.errors.assessment_charges"/>
                                    </div>

                                    <div>
                                        <IftaLabel>
                                            <InputNumber v-model="form.slap_charges" :maxFractionDigits="2" :minFractionDigits="2" class="w-full" inputId="slap-charge" min="0" step="any" variant="filled" />
                                            <label for="slap-charge">SLAP Charge</label>
                                        </IftaLabel>
                                        <InputError :message="form.errors.slap_charges"/>
                                    </div>

                                    <div>
                                        <IftaLabel>
                                            <InputNumber v-model="form.refund" :maxFractionDigits="2" :minFractionDigits="2" class="w-full" inputId="refund-charge" min="0" step="any" variant="filled" />
                                            <label for="refund-charge">Refund</label>
                                        </IftaLabel>
                                        <InputError :message="form.errors.refund"/>
                                    </div>

                                    <div>
                                        <IftaLabel>
                                            <InputNumber v-model="form.clearance_charges" :maxFractionDigits="2" :minFractionDigits="2" class="w-full" inputId="clearance-charge" min="0" step="any" variant="filled" />
                                            <label for="clearance-charge">Clearance Charge</label>
                                        </IftaLabel>
                                        <InputError :message="form.errors.clearance_charges"/>
                                    </div>

                                    <div class="col-span-2 text-right">
                                        <Button icon="pi pi-save" label="Save Payments" size="small" />
                                    </div>
                                </div>
                            </TabPanel>
                            <TabPanel value="2">
                                <div class="grid grid-cols-2 gap-5">
                                    <div>
                                        <IftaLabel>
                                            <InputText v-model="documentForm.name" class="w-full" variant="filled" />
                                            <label>Document Name</label>
                                        </IftaLabel>
                                        <InputError :message="documentForm.errors.name"/>
                                    </div>

                                    <div>
                                        <IftaLabel>
                                            <DatePicker v-model="documentForm.date" class="w-full" variant="filled" />
                                            <label>Date</label>
                                        </IftaLabel>
                                        <InputError :message="documentForm.errors.date"/>
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
