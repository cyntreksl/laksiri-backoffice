<script setup>
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import {ref, watchEffect} from "vue";
import {router, useForm} from "@inertiajs/vue3";
import {push} from "notivue";
import Button from "primevue/button";
import InputText from "primevue/inputtext";
import InputNumber from "primevue/inputnumber";
import Select from "primevue/select";
import DatePicker from "primevue/datepicker";
import Checkbox from 'primevue/checkbox';
import Divider from 'primevue/divider';
import moment from "moment";
import {useConfirm} from "primevue/useconfirm";
import { useDwellTime } from '@/composable/useDwellTime';
import DetainDialog from "@/Pages/Common/Dialog/DetainDialog.vue";

const props = defineProps({
    container: {
        type: Object,
        default: () => {
        },
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

const emit = defineEmits(['close']);

const confirm = useConfirm();

const arrivedDwellTimeNow = useDwellTime(() => props.container.arrived_at_primary_warehouse);
const departedDwellTimeNow = useDwellTime(() => props.container.departed_at_primary_warehouse);

const showDetainDialog = ref(false);
const detainDialogMode = ref('detain');

const handleDeleteLoadedShipment = () => {
    confirm.require({
        message: 'Would you like to delete this loaded shipment?',
        header: 'Delete Loaded Shipment?',
        icon: 'pi pi-info-circle',
        rejectLabel: 'Cancel',
        rejectProps: {
            label: 'Cancel',
            severity: 'secondary',
            outlined: true
        },
        acceptProps: {
            label: 'Delete',
            severity: 'danger'
        },
        accept: () => {
            router.put(route("loading.containers.delete-loading", props.container.id), {}, {
                preserveScroll: true,
                onSuccess: () => {
                    emit('close')
                    router.visit(route('loading.loaded-containers.index'))
                    push.success('Loaded Shipment Deleted Successfully!');
                },
                onError: () => {
                    push.error('Something went to wrong!');
                }
            })
        },
        reject: () => {
        }
    });
}

const isDepartureDisabled = (status) => {
    const disabledStatuses = ["UNLOADED", "REACHED DESTINATION"];
    return disabledStatuses.includes(status);
};

const form = useForm({
    cargo_type: props.container.cargo_type,
    reference: props.container.reference,
    awb_number: props.container.awb_number,
    estimated_time_of_departure: props.container.estimated_time_of_departure,
    estimated_time_of_arrival: props.container.estimated_time_of_arrival,
    status: props.container.status,
    note: props.container.note,
    is_reached: Boolean(props.container.is_reached),
    reached_date: props.container.reached_date,
    container_type:  props.container.container_type,
    bl_number: props.container.bl_number,
    container_number: props.container.container_number,
    seal_number: props.container.seal_number,
    vessel_name: props.container.vessel_name,
    voyage_number: props.container.voyage_number,
    shipping_line: props.container.shipping_line,
    port_of_loading: props.container.port_of_loading,
    port_of_discharge: props.container.port_of_discharge,
    flight_number: props.container.flight_number,
    airline_name: props.container.airline_name,
    airport_of_departure: props.container.airport_of_departure,
    airport_of_arrival: props.container.airport_of_arrival,
    cargo_class: props.container.cargo_class,
    loading_ended_at: props.container.loading_ended_at,
    loading_started_at: props.container.loading_started_at,
    shipment_weight: props.container.shipment_weight
});

const handleUpdateContainer = () => {
    if (moment(form.estimated_time_of_departure).isValid()) {
        form.estimated_time_of_departure = moment(form.estimated_time_of_departure).format("YYYY-MM-DD");
    } else {
        form.estimated_time_of_departure = null;
    }

    if (moment(form.estimated_time_of_arrival).isValid()) {
        form.estimated_time_of_arrival = moment(form.estimated_time_of_arrival).format("YYYY-MM-DD");
    } else {
        form.estimated_time_of_arrival = null;
    }

    if (moment(form.reached_date).isValid()) {
        form.reached_date = moment(form.reached_date).format("YYYY-MM-DD");
    } else {
        form.reached_date = null;
    }

    form.put(route("loading.loading-containers.update", props.container.id), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            emit('close');
            router.visit(route("loading.loaded-containers.index"));
            push.success('Container Updated Successfully!');
        },
        onError: () => {
            form.reset();
        }
    });
}

const handleRTFContainer = () => {
    detainDialogMode.value = 'detain';
    showDetainDialog.value = true;
};

const handleUndoRTFContainer = () => {
    detainDialogMode.value = 'lift';
    showDetainDialog.value = true;
};

const confirmDetainAction = (data) => {
    console.log('confirmDetainAction called with data:', data);
    console.log('Container ID:', props.container.id);
    console.log('Mode:', detainDialogMode.value);

    if (detainDialogMode.value === 'detain') {
        console.log('Posting detain request');
        router.post(
            route("loading.containers.set.detain", props.container.id),
            data,
            {
                preserveScroll: true,
                onSuccess: () => {
                    console.log('Detain success');
                    push.success(`Shipment detained by ${data.detain_type}`);
                    showDetainDialog.value = false;
                    emit('close');
                    router.visit(route('loading.loaded-containers.index'));
                },
                onError: (errors) => {
                    console.error('Detain error:', errors);
                    push.error(errors?.message || 'Something went wrong!');
                }
            }
        );
    } else {
        console.log('Posting lift detain request');
        router.post(
            route("loading.containers.unset.detain", props.container.id),
            data,
            {
                preserveScroll: true,
                onSuccess: () => {
                    console.log('Lift detain success');
                    push.success('Shipment detain lifted successfully!');
                    showDetainDialog.value = false;
                    emit('close');
                    router.visit(route('loading.loaded-containers.index'));
                },
                onError: (errors) => {
                    console.error('Lift detain error:', errors);
                    push.error(errors?.message || 'Something went wrong!');
                }
            }
        );
    }
};

const containerTypes = ref(props.seaContainerOptions);

watchEffect(() => {
    if (form.cargo_type === "Sea Cargo") {
        containerTypes.value = props.seaContainerOptions;
    } else {
        containerTypes.value = props.airContainerOptions;
    }
});
</script>

<template>
    <div
        class="mt-3 flex flex-col items-center justify-between space-y-2 text-center sm:flex-row sm:space-y-0 sm:text-left">
        <div>
            <div class="flex items-center space-x-2 text-xs text-slate-400">
                <div class="flex items-center">
                    <i class="ti ti-plane-departure text-xl mr-2"></i>
                    {{ container.branch.name }}
                </div>
                <div>
                    <i class="ti ti-arrow-narrow-right text-xl"></i>
                </div>
                <div class="flex items-center">
                    <i class="ti ti-plane-arrival text-xl mr-2"></i>
                    {{ container.warehouse.name }}
                </div>
            </div>
            <h3 class="text-2xl font-semibold text-slate-700 dark:text-navy-100">
                {{ container.reference }}
            </h3>
            <div v-if="container.arrived_at_primary_warehouse" class="text-yellow-500 italic text-sm">
                Warehouse Arrived At {{container.arrived_at_primary_warehouse}}
                ({{ arrivedDwellTimeNow }})
            </div>
            <div v-if="container.departed_at_primary_warehouse" class="text-red-500 italic text-sm">
                Warehouse Arrived At {{container.departed_at_primary_warehouse}}
                ({{ departedDwellTimeNow }})
            </div>
        </div>
        <div class="flex items-center space-x-2">
            <template v-if="$page.props.user.permissions.includes('set_rtf')">
              <Button v-if="!container?.latest_detain_record?.is_rtf" icon="pi pi-lock" label="Detain Shipment"
                    severity="warn" size="small" variant="outlined" @click.prevent="handleRTFContainer" />
            </template>

            <template v-if="$page.props.user.permissions.includes('lift_rtf')">
              <Button v-if="container?.latest_detain_record?.is_rtf" icon="pi pi-unlock" label="Lift Detain"
                    severity="success" size="small" variant="outlined" @click.prevent="handleUndoRTFContainer" />
            </template>

            <Button :disabled="container.status === 'IN TRANSIT' || container.status === 'REACHED DESTINATION'" icon="pi pi-trash" label="Delete Shipment"
                    severity="danger" size="small" @click.prevent="handleDeleteLoadedShipment" />

            <Button :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing" icon="pi pi-save" label="Save Changes"
                    severity="info" size="small" @click="handleUpdateContainer"/>
        </div>
    </div>

    <Divider />

    <div class="grid grid-cols-1 sm:grid-cols-4 gap-x-4 gap-y-8 my-5">

        <!-- General Cargo Information -->
        <div class="col-span-4">
            <h2 class="text-lg font-semibold">
                <i class="pi pi-info-circle text-info mr-2"></i>
                General Information
            </h2>
        </div>

        <div>
            <InputLabel value="Cargo Mode"/>
            <InputText id="cargo_type" v-model="form.cargo_type" class="w-full" disabled placeholder="Enter Cargo Mode"/>
        </div>

        <div>
            <InputLabel value="Reference"/>
            <InputText id="reference" v-model="form.reference" class="w-full" placeholder="Enter Reference"/>
            <InputError :message="form.errors.reference"/>
        </div>

        <div>
            <InputLabel value="ETD"/>
            <DatePicker v-model="form.estimated_time_of_departure" class="w-full mt-1" date-format="yy-mm-dd" icon-display="input" placeholder="Set EDT" show-icon/>
            <InputError :message="form.errors.estimated_time_of_departure"/>
        </div>

        <div>
            <InputLabel value="ETA"/>
            <DatePicker v-model="form.estimated_time_of_arrival" class="w-full mt-1" date-format="yy-mm-dd" icon-display="input" placeholder="Set ETA" show-icon/>
            <InputError :message="form.errors.estimated_time_of_arrival"/>
        </div>

        <!-- Sea Cargo Specific -->
        <template v-if="form.cargo_type === 'Sea Cargo'">
            <div class="col-span-4">
                <h2 class="text-lg font-semibold">
                    <i class="pi pi-info-circle text-info mr-2"></i>
                    Sea Cargo Information
                </h2>
            </div>

            <div>
                <InputLabel value="Container Type"/>
                <Select v-model="form.container_type" :options="containerTypes" class="w-full" placeholder="Choose One"/>
                <InputError :message="form.errors.container_type"/>
            </div>

            <div>
                <InputLabel value="Container Number"/>
                <InputText v-model="form.container_number" class="w-full" placeholder="Enter Container Number"/>
                <InputError :message="form.errors.container_number"/>
            </div>

            <div>
                <InputLabel value="Seal Number"/>
                <InputText v-model="form.seal_number" class="w-full" placeholder="Enter Seal Number"/>
                <InputError :message="form.errors.seal_number"/>
            </div>

            <div>
                <InputLabel value="BL Number"/>
                <InputText v-model="form.bl_number" class="w-full" placeholder="Enter BL Number"/>
                <InputError :message="form.errors.bl_number"/>
            </div>

            <div>
                <InputLabel value="Vessel Name"/>
                <InputText v-model="form.vessel_name" class="w-full" placeholder="Enter Vessel Name"/>
                <InputError :message="form.errors.vessel_name"/>
            </div>

            <div>
                <InputLabel value="Voyage Number"/>
                <InputText v-model="form.voyage_number" class="w-full" placeholder="Enter Voyage Number"/>
                <InputError :message="form.errors.voyage_number"/>
            </div>

            <div>
                <InputLabel value="Shipping Line"/>
                <InputText v-model="form.shipping_line" class="w-full" placeholder="Enter Shipping Line"/>
                <InputError :message="form.errors.shipping_line"/>
            </div>

            <div>
                <InputLabel value="Port of Loading"/>
                <InputText v-model="form.port_of_loading" class="w-full" placeholder="Enter Port of Loading"/>
                <InputError :message="form.errors.port_of_loading"/>
            </div>

            <div>
                <InputLabel value="Port of Discharge"/>
                <InputText v-model="form.port_of_discharge" class="w-full" placeholder="Enter Port of Discharge"/>
                <InputError :message="form.errors.port_of_discharge"/>
            </div>

            <div>
                <InputLabel value="Shipment Weight"/>
                <InputNumber v-model="form.shipment_weight" :maxFractionDigits="2" :minFractionDigits="2" class="w-full" min="0" placeholder="Enter Shipment Weight" step="any" />
                <InputError :message="form.errors.shipment_weight"/>
            </div>
        </template>

        <!-- Air Cargo Specific -->
        <template v-else-if="form.cargo_type === 'Air Cargo'">
            <div class="col-span-4">
                <h2 class="text-lg font-semibold">
                    <i class="pi pi-info-circle text-info mr-2"></i>
                    Air Cargo Information
                </h2>
            </div>

            <div>
                <InputLabel value="AWB Number"/>
                <InputText v-model="form.awb_number" class="w-full" placeholder="Enter AWB Number"/>
                <InputError :message="form.errors.awb_number"/>
            </div>

            <div>
                <InputLabel value="Flight Number"/>
                <InputText v-model="form.flight_number" class="w-full" placeholder="Enter Flight Number"/>
                <InputError :message="form.errors.flight_number"/>
            </div>

            <div>
                <InputLabel value="Airline Name"/>
                <InputText v-model="form.airline_name" class="w-full" placeholder="Enter Airline Name"/>
                <InputError :message="form.errors.airline_name"/>
            </div>

            <div>
                <InputLabel value="Airport of Departure"/>
                <InputText v-model="form.airport_of_departure" class="w-full" placeholder="Enter Airport of Departure"/>
                <InputError :message="form.errors.airport_of_departure"/>
            </div>

            <div>
                <InputLabel value="Airport of Arrival"/>
                <InputText v-model="form.airport_of_arrival" class="w-full" placeholder="Enter Airport of Arrival"/>
                <InputError :message="form.errors.airport_of_arrival"/>
            </div>
        </template>

        <!-- Common Final Details -->
        <div class="col-span-4">
            <h2 class="text-lg font-semibold">
                <i class="pi pi-info-circle text-info mr-2"></i>
                Shipment Status
            </h2>
        </div>

        <div>
            <InputLabel value="Last Status"/>
            <Select v-model="form.status" :option-disabled="(option) => $page.props.currentBranch.type !== 'Destination' && isDepartureDisabled(option)" :options="containerStatus" class="w-full" placeholder="Choose One" />
            <InputError :message="form.errors.status"/>
        </div>

        <div>
            <InputLabel value="Note"/>
            <InputText v-model="form.note" class="w-full" placeholder="Type something..."/>
            <InputError :message="form.errors.note"/>
        </div>

        <div>
            <InputLabel value="Loading Started Time"/>
            <InputText v-model="form.loading_started_at" class="w-full" disabled/>
            <InputError :message="form.errors.loading_started_at"/>
        </div>

        <div>
            <InputLabel value="Loading End Time"/>
            <InputText v-model="form.loading_ended_at" class="w-full" disabled/>
            <InputError :message="form.errors.loading_ended_at"/>
        </div>

        <div>
            <InputLabel value="Reached Date"/>
            <DatePicker v-model="form.reached_date" :disabled="$page.props.currentBranch.type !== 'Destination'" class="w-full mt-1" date-format="yy-mm-dd" icon-display="input" placeholder="Set Reached Date" show-icon/>
            <InputError :message="form.errors.reached_date"/>
        </div>

        <div v-if="$page.props.currentBranch.type === 'Destination'" class="flex items-center gap-2">
            <Checkbox v-model="form.is_reached" binary inputId="is_reached" />
            <label class="font-medium text-sm" for="is_reached">Reached Destination?</label>
            <InputError :message="form.errors.is_reached"/>
        </div>

    </div>

    <DetainDialog
        :entity-name="container.reference"
        :mode="detainDialogMode"
        :visible="showDetainDialog"
        entity-type="shipment"
        @confirm="confirmDetainAction"
        @update:visible="showDetainDialog = $event"
    />

</template>
