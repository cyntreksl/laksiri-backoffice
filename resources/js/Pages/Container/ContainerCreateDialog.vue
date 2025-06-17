<script setup>
import { useForm, router } from "@inertiajs/vue3";
import InputError from "@/Components/InputError.vue";
import {ref, watchEffect} from "vue";
import Card from 'primevue/card';
import Button from "primevue/button";
import InputLabel from "@/Components/InputLabel.vue";
import InputText from "primevue/inputtext";
import DatePicker from "primevue/datepicker";
import moment from "moment";
import {push} from "notivue";
import Select from "primevue/select";

const props = defineProps({
    cargoType:"Sea Cargo" ,
    warehouse: 2,
    airLines: {
        type: Array,
        default: () => [],
    },
})

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
    target_warehouse: props.warehouse,
});

const containerTypes = ref(props.seaContainerOptions);

watchEffect(() => {
    if (form.cargo_type === "Sea Cargo") {
        containerTypes.value = props.seaContainerOptions;
    } else {
        containerTypes.value = props.airContainerOptions;
    }
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
            console.log(shipment)
            push.success('Container Created Successfully!')
            // router.visit(route("loading.loading-containers.index"));
            form.reset();
            emit('containerCreated',true);
        },
        onError: () => console.log("error"),
        onFinish: () => console.log("finish"),
        preserveScroll: true,
        preserveState: true,
    });
};

const emit = defineEmits(['containerCreated']);
</script>

<template>
        <form @submit.prevent="handleCreate">
            <input type="hidden" v-model="form.target_warehouse"/>
            <div class="my-4 gap-4">
                <div class="sm:col-span-3 space-y-5">
                    <Card>
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

                    <Card v-if="form.cargo_type === 'Sea Cargo'">
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

                    <Card v-else>
                        <template #title>Flight Details</template>
                        <template #content>
                            <div class="grid grid-cols-4 gap-5 mt-3">

                                <div class="col-span-1">
                                    <InputLabel value="Flight Number"/>
                                    <InputText v-model="form.flight_number" class="w-full" placeholder="Enter Flight Number"/>
                                    <InputError :message="form.errors.flight_number" />
                                </div>

                                <div class="col-span-3">
                                    <InputLabel value="Airline Name"/>
                                    <Select
                                        v-model="form.airline_name"
                                        :options="airLines"
                                        class="w-full"
                                        filter
                                        option-label="name"
                                        option-value="name"
                                        placeholder="Select Air Line"
                                    />
                                    <InputError :message="form.errors.airline_name"/>
                                </div>

                                <div class="col-span-2">
                                    <InputLabel value="Airport of Departure"/>
                                    <InputText v-model="form.airport_of_departure" class="w-full" placeholder="Enter Airport of Departure"/>
                                    <InputError :message="form.errors.airport_of_departure" />
                                </div>

                                <div class="col-span-2">
                                    <InputLabel value="Airport of Arrival"/>
                                    <InputText v-model="form.airport_of_arrival" class="w-full" placeholder="Enter Airport of Arrival"/>
                                    <InputError :message="form.errors.airport_of_arrival" />
                                </div>
                            </div>
                        </template>
                    </Card>

                    <!-- Action Buttons -->
                    <div class="flex justify-end space-x-5">
                        <Button :class="{ 'opacity-50': form.processing }" :disabled="form.processing" icon="pi pi-arrow-right" iconPos="right" label="Create Container" type="submit" />
                    </div>
                </div>
            </div>
        </form>
</template>
