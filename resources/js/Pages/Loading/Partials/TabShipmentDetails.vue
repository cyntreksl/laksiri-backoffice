<script setup>
import DangerOutlineButton from "@/Components/DangerOutlineButton.vue";
import InputError from "@/Components/InputError.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import Tab from "@/Components/Tab.vue";
import InputLabel from "@/Components/InputLabel.vue";
import AccordionPanel from "@/Components/AccordionPanel.vue";
import TextInput from "@/Components/TextInput.vue";
import DeleteLoadingConfirmationModal from "@/Pages/Loading/Partials/DeleteLoadingConfirmationModal.vue";
import {ref, watchEffect} from "vue";
import {router, useForm} from "@inertiajs/vue3";
import {push} from "notivue";
import Checkbox from "@/Components/Checkbox.vue";

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

const showConfirmDeleteLoadingModal = ref(false);

const confirmDeleteLoadedShipment = () => {
    showConfirmDeleteLoadingModal.value = true;
};

const closeDeleteConfirmationModal = () => {
    showConfirmDeleteLoadingModal.value = false;
}

const handleDeleteLoadedShipment = () => {
    router.put(route("loading.containers.delete-loading", props.container.id), {}, {
        preserveScroll: true,
        onSuccess: () => {
            emit('close')
            closeDeleteConfirmationModal()
            router.visit(route('loading.loaded-containers.index'))
            push.success('Loaded Shipment Deleted Successfully!');
        },
        onError: () => {
            closeDeleteConfirmationModal();
            push.error('Something went to wrong!');
        }
    })
}

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
    loading_started_at: props.container.loading_started_at
});

const handleUpdateContainer = () => {
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
    <Tab label="Shipment Details" name="tabShipment">

        <div
            class="flex flex-col items-center justify-between space-y-2 text-center sm:flex-row sm:space-y-0 sm:text-left">
            <div>
                <h3 class="text-xl font-semibold text-slate-700 dark:text-navy-100">
                    Shipment
                </h3>
                <p class="mt-1 hidden sm:block">{{ container.reference }}</p>
            </div>
        </div>

        <AccordionPanel show-panel title="Loading Details">
            <template #header-image>
                <div
                    class="flex size-8 items-center justify-center rounded-lg p-1 text-primary dark:bg-accent-light/10 dark:text-accent-light">
                    <svg class="icon icon-tabler icons-tabler-outline icon-tabler-truck-loading" fill="none"
                         height="24" stroke="currentColor"
                         stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24"
                         width="24"
                         xmlns="http://www.w3.org/2000/svg">
                        <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                        <path d="M2 3h1a2 2 0 0 1 2 2v10a2 2 0 0 0 2 2h15"/>
                        <path
                            d="M9 6m0 3a3 3 0 0 1 3 -3h4a3 3 0 0 1 3 3v2a3 3 0 0 1 -3 3h-4a3 3 0 0 1 -3 -3z"/>
                        <path d="M9 19m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/>
                        <path d="M18 19m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/>
                    </svg>
                </div>
            </template>
            <div class="px-4 py-4 sm:px-5">

                <div class="grid grid-cols-1 sm:grid-cols-4 gap-x-4 gap-y-8">
                    <div>
                        <label class="block">
                            <InputLabel value="Cargo Mode"/>
                            <TextInput v-model="form.cargo_type" class="w-full" disabled/>
                        </label>
                    </div>

                    <div v-if="form.cargo_type === 'Sea Cargo'">
                        <label class="block">
                            <InputLabel value="Container Type"/>
                            <select
                                v-model="form.container_type"
                                class="form-select w-full rounded-lg border border-slate-300 bg-white px-3 py-2 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:bg-navy-700 dark:hover:border-navy-400 dark:focus:border-accent"
                            >
                                <option v-for="containerType in containerTypes">
                                    {{ containerType }}
                                </option>
                            </select>
                        </label>
                        <InputError :message="form.errors.container_type"/>
                    </div>

                    <div>
                        <InputLabel value="Reference"/>
                        <TextInput v-model="form.reference" class="w-full" placeholder="Reference"/>
                        <InputError :message="form.errors.reference"/>
                    </div>

                    <div v-if="form.cargo_type === 'Sea Cargo'">
                        <InputLabel value="Container Number"/>
                        <TextInput v-model="form.container_number" class="w-full" placeholder="Container Number"/>
                        <InputError :message="form.errors.container_number" />
                    </div>
                    <div v-if="form.cargo_type === 'Sea Cargo'">
                        <InputLabel value="Seal Number"/>
                        <TextInput v-model="form.seal_number" class="w-full" placeholder="Seal Number"/>
                        <InputError :message="form.errors.seal_number" />
                    </div>

                    <div v-if="form.cargo_type === 'Sea Cargo'">
                        <InputLabel value="BL Number"/>
                        <TextInput v-model="form.bl_number" class="w-full"/>
                        <InputError :message="form.errors.bl_number" />
                    </div>

                    <div v-else>
                        <InputLabel value="AWB Number"/>
                        <TextInput v-model="form.awb_number" class="w-full"/>
                        <InputError :message="form.errors.awb_number" />
                    </div>

                    <div>
                        <InputLabel value="EDT"/>
                        <TextInput v-model="form.estimated_time_of_departure" class="w-full" type="date"/>
                        <InputError :message="form.errors.estimated_time_of_departure"/>
                    </div>

                    <div>
                        <InputLabel value="ETA"/>
                        <TextInput v-model="form.estimated_time_of_arrival" class="w-full" type="date"/>
                        <InputError :message="form.errors.estimated_time_of_arrival"/>
                    </div>

                    <div>
                        <InputLabel value="Vessel Name"/>
                        <TextInput v-model="form.vessel_name" class="w-full"/>
                        <InputError :message="form.errors.vessel_name" />
                    </div>
                    <div>
                        <InputLabel value="Voyage Number"/>
                        <TextInput v-model="form.voyage_number" class="w-full"/>
                        <InputError :message="form.errors.voyage_number" />
                    </div>
                    <div>
                        <InputLabel value="Shipping Line"/>
                        <TextInput v-model="form.shipping_line" class="w-full"/>
                        <InputError :message="form.errors.shipping_line" />
                    </div>
                    <div>
                        <InputLabel value="Port of Loading"/>
                        <TextInput v-model="form.port_of_loading" class="w-full"/>
                        <InputError :message="form.errors.port_of_loading" />
                    </div>
                    <div>
                        <InputLabel value="Port of Discharge"/>
                        <TextInput v-model="form.port_of_discharge" class="w-full"/>
                        <InputError :message="form.errors.port_of_discharge" />
                    </div>
                    <div>
                        <InputLabel value="Loading Started Time"/>
                        <TextInput v-model="form.loading_started_at" class="w-full"  :disabled="true" />
                        <InputError :message="form.errors.loading_started_at" />
                    </div>
                    <div>
                        <InputLabel value="Loading End Time"/>
                        <TextInput v-model="form.loading_ended_at" class="w-full"  :disabled="true" />
                        <InputError :message="form.errors.loading_ended_at" />
                    </div>
                    <template v-if="form.cargo_type === 'Air Cargo'">
                        <div>
                            <InputLabel value="Flight Number"/>
                            <TextInput v-model="form.flight_number" class="w-full"/>
                            <InputError :message="form.errors.flight_number" />
                        </div>
                        <div>
                            <InputLabel value="Airline Name"/>
                            <TextInput v-model="form.airline_name" class="w-full"/>
                            <InputError :message="form.errors.airline_name" />
                        </div>
                        <div>
                            <InputLabel value="Airport of Departure"/>
                            <TextInput v-model="form.airport_of_departure" class="w-full"/>
                            <InputError :message="form.errors.airport_of_departure" />
                        </div>
                        <div>
                            <InputLabel value="Airport of Arrival"/>
                            <TextInput v-model="form.airport_of_arrival" class="w-full"/>
                            <InputError :message="form.errors.airport_of_arrival" />
                        </div>
                    </template>
                </div>

            </div>
        </AccordionPanel>

        <AccordionPanel show-panel title="Loading Status">
            <template #header-image>
                <div
                    class="flex size-8 items-center justify-center rounded-lg p-1 text-primary dark:bg-accent-light/10 dark:text-accent-light">
                    <svg class="icon icon-tabler icons-tabler-outline icon-tabler-status-change" fill="none"
                         height="24" stroke="currentColor"
                         stroke-linecap="round"
                         stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24"
                         width="24"
                         xmlns="http://www.w3.org/2000/svg">
                        <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                        <path d="M6 18m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/>
                        <path d="M18 18m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/>
                        <path d="M6 12v-2a6 6 0 1 1 12 0v2"/>
                        <path d="M15 9l3 3l3 -3"/>
                    </svg>
                </div>
            </template>
            <div class="px-4 py-4 sm:px-5">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-8">
                    <div>
                        <label class="block">
                            <InputLabel value="Last Status"/>
                            <select
                                v-model="form.status"
                                class="form-select w-full rounded-lg border border-slate-300 bg-white px-3 py-2 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:bg-navy-700 dark:hover:border-navy-400 dark:focus:border-accent"
                            >
                                <option :value="null" disabled>
                                    Select Status
                                </option>
                                <option v-for="status in containerStatus">
                                    {{ status }}
                                </option>
                            </select>
                        </label>
                        <InputError :message="form.errors.status"/>
                    </div>

                    <div>
                        <InputLabel value="Note"/>
                        <TextInput v-model="form.note" class="w-full" placeholder="Type something..."/>
                        <InputError :message="form.errors.note"/>
                    </div>

                    <div>
                        <InputLabel value="Reached Destination?"/>
                        <Checkbox v-model="form.is_reached" />
                        <InputError :message="form.errors.is_reached"/>
                    </div>

                    <div>
                        <InputLabel value="Reached Date"/>
                        <TextInput v-model="form.reached_date" class="w-full" type="date"/>
                        <InputError :message="form.errors.reached_date"/>
                    </div>
                </div>
            </div>
        </AccordionPanel>

        <div class="flex space-x-2 items-center justify-end">
            <DangerOutlineButton @click.prevent="confirmDeleteLoadedShipment">
                <svg class="size-5 mr-2" fill="none" stroke="currentColor" stroke-width="1.5"
                     viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"
                        stroke-linecap="round"
                        stroke-linejoin="round"/>
                </svg>

                Delete Loading
            </DangerOutlineButton>

            <PrimaryButton :class="{ 'opacity-25': form.processing }"
                           :disabled="form.processing" @click="handleUpdateContainer">
                <svg class="icon icon-tabler icons-tabler-outline icon-tabler-device-floppy size-5 mr-2"
                     fill="none" height="24" stroke="currentColor" stroke-linecap="round"
                     stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" width="24"
                     xmlns="http://www.w3.org/2000/svg">
                    <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                    <path d="M6 4h10l4 4v10a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2"/>
                    <path d="M12 14m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/>
                    <path d="M14 4l0 4l-6 0l0 -4"/>
                </svg>
                Save Changes
            </PrimaryButton>
        </div>
    </Tab>

    <DeleteLoadingConfirmationModal :show="showConfirmDeleteLoadingModal" @close="closeDeleteConfirmationModal"
                                    @delete-loading="handleDeleteLoadedShipment"/>
</template>
