<script setup>
import { useForm } from "@inertiajs/vue3";
import InputError from "@/Components/InputError.vue";
import Card from 'primevue/card';
import Button from "primevue/button";
import InputLabel from "@/Components/InputLabel.vue";
import InputText from "primevue/inputtext";
import DatePicker from "primevue/datepicker";
import moment from "moment";
import {push} from "notivue";
import Dialog from "primevue/dialog";
import SelectButton from 'primevue/selectbutton';

const props = defineProps({
    visible: {
        type: Boolean,
        default: false,
    },
    cargoType: {
        type: String,
        default: "Sea Cargo",
    },
    vesselSchedule: {
        type: Object,
        default: () => ({}),
    },
    seaContainerOptions: {
        type: Array,
        required: true,
    },
    warehouses: {
        type: Object,
        default: () => {},
    },
})

const emit = defineEmits(["update:visible", 'close']);

const form = useForm({
    cargo_type: props.cargoType,
    container_type: "Custom",
    reference: props.referenceNum,
    bl_number: "",
    awb_number: "",
    estimated_time_of_departure: "",
    estimated_time_of_arrival: "",
    container_number: "",
    seal_number: "",
    vessel_name: "",
    voyage_number: "",
    shipping_line: "",
    port_of_loading: "",
    port_of_discharge: "",
    flight_number: "",
    air_line_id: "",
    airline_name: "",
    airport_of_departure: "",
    airport_of_arrival: "",
    departure_time: "",
    arrival_time: "",
    cargo_class: "",
    vessel_schedule_id: props.vesselSchedule?.id,
    target_warehouse: "",
});

const handleCreate = () => {
    if (form.estimated_time_of_departure !== 'Invalid date') {
        form.estimated_time_of_departure = moment(form.estimated_time_of_departure).format("YYYY-MM-DD");
    } else {
        form.estimated_time_of_departure = null;
    }

    if (form.estimated_time_of_arrival !== 'Invalid date') {
        form.estimated_time_of_arrival = moment(form.estimated_time_of_arrival).format("YYYY-MM-DD");
    } else {
        form.estimated_time_of_arrival = null;
    }

    form.post(route("loading.loading-containers.store"), {
        onSuccess: (shipment) => {
            push.success('Container Created Successfully!')
            form.reset();
            emit('close');
        },
        onError: () => console.log("error"),
        onFinish: () => console.log("finish"),
        preserveScroll: true,
        preserveState: true,
    });
};
</script>

<template>
    <Dialog :style="{ width: '50rem' }" :visible="visible" header="Create Shipment" modal @update:visible="(newValue) => $emit('update:visible', newValue)">
            <input v-model="form.target_warehouse" type="hidden"/>
            <div class="my-4 gap-4">
                <div class="sm:col-span-3 space-y-5">
                    <Card class="border">
                        <template #title>Container Specs</template>
                        <template #content>
                            <div class="my-3">
                                <SelectButton v-model="form.container_type" :options="seaContainerOptions" name="container_type"/>
                                <InputError :message="form.errors.container_type" />
                            </div>
                        </template>
                    </Card>

                    <Card class="border">
                        <template #title>Target Warehouse</template>
                        <template #content>
                            <div class="my-3">
                                <SelectButton v-model="form.target_warehouse" :options="warehouses" name="target_warehouse" option-label="name" option-value="id"/>
                                <InputError :message="form.errors.target_warehouse" />
                            </div>
                        </template>
                    </Card>

                    <Card class="border">
                        <template #title>Container Details</template>
                        <template #content>
                            <div class="grid grid-cols-4 gap-5 mt-3">
                                <div class="col-span-2">
                                    <InputLabel value="Reference"/>
                                    <InputText v-model="form.reference" class="w-full" placeholder="Enter Container Reference"/>
                                    <InputError :message="form.errors.reference" />
                                </div>

                                <div v-if="form.cargo_type === 'Sea Cargo'" class="col-span-2">
                                    <InputLabel value="Container Number"/>
                                    <InputText v-model="form.container_number" class="w-full" placeholder="Enter Container Number"/>
                                    <InputError :message="form.errors.container_number" />
                                </div>

                                <div v-if="form.cargo_type === 'Sea Cargo'" class="col-span-2">
                                    <InputLabel value="Seal Number"/>
                                    <InputText v-model="form.seal_number" class="w-full" placeholder="Enter Seal Number"/>
                                    <InputError :message="form.errors.seal_number" />
                                </div>

                                <div v-if="form.cargo_type === 'Sea Cargo'" class="col-span-2">
                                    <InputLabel value="BL Number"/>
                                    <InputText v-model="form.bl_number" class="w-full" placeholder="Enter BL Number"/>
                                    <InputError :message="form.errors.bl_number" />
                                </div>

                                <div v-else class="col-span-2">
                                    <InputLabel value="AWB Number"/>
                                    <InputText v-model="form.awb_number" class="w-full" placeholder="Enter AWB Number"/>
                                    <InputError :message="form.errors.awb_number" />
                                </div>

                                <div class="col-span-2">
                                    <InputLabel value="Estimated Departure Date"/>
                                    <DatePicker v-model="form.estimated_time_of_departure" class="w-full mt-1" date-format="yy-mm-dd" icon-display="input" placeholder="Set Estimated Departure Date" show-icon/>
                                    <InputError
                                        :message="form.errors.estimated_time_of_departure"
                                    />
                                </div>

                                <div class="col-span-2">
                                    <InputLabel value="Estimated Arrival Date to Destination"/>
                                    <DatePicker v-model="form.estimated_time_of_arrival" class="w-full mt-1" date-format="yy-mm-dd" icon-display="input" placeholder="Set Estimated Arrival Date" show-icon/>
                                    <InputError :message="form.errors.estimated_time_of_arrival" />
                                </div>
                            </div>
                        </template>
                    </Card>

                    <Card class="border">
                        <template #title>Vessel Details</template>
                        <template #content>
                            <div class="grid grid-cols-4 gap-5 mt-3">

                                <div class="col-span-2">
                                    <InputLabel value="Vessel Name"/>
                                    <InputText v-model="form.vessel_name" class="w-full" placeholder="Enter Vessel Name"/>
                                    <InputError :message="form.errors.vessel_name" />
                                </div>

                                <div class="col-span-2">
                                    <InputLabel value="Voyage Number"/>
                                    <InputText v-model="form.voyage_number" class="w-full" placeholder="Enter Voyage Number"/>
                                    <InputError :message="form.errors.voyage_number" />
                                </div>

                                <div class="col-span-4">
                                    <InputLabel value="Shipping Line"/>
                                    <InputText v-model="form.shipping_line" class="w-full" placeholder="Enter Shipping Line"/>
                                    <InputError :message="form.errors.shipping_line" />
                                </div>

                                <div class="col-span-2">
                                    <InputLabel value="Port of Loading"/>
                                    <InputText v-model="form.port_of_loading" class="w-full" placeholder="Enter Port of Loading"/>
                                    <InputError :message="form.errors.port_of_loading" />
                                </div>

                                <div class="col-span-2">
                                    <InputLabel value="Port of Discharge"/>
                                    <InputText v-model="form.port_of_discharge" class="w-full" placeholder="Enter Port of Discharge"/>
                                    <InputError :message="form.errors.port_of_discharge" />
                                </div>
                            </div>
                        </template>
                    </Card>

                    <!-- Action Buttons -->
                    <div class="flex justify-end space-x-5">
                        <Button :class="{ 'opacity-50': form.processing }" :disabled="form.processing" icon="pi pi-arrow-right" iconPos="right" label="Create Container" @click.prevent="handleCreate" />
                    </div>
                </div>
            </div>
    </Dialog>
</template>
