<script setup>
import {ref} from "vue";
import {usePage} from "@inertiajs/vue3";
import Dialog from "primevue/dialog";
import Button from "primevue/button";
import InputText from "primevue/inputtext";
import Message from 'primevue/message';
import {push} from "notivue";
import moment from "moment/moment.js";

const props = defineProps({
    visible: {
        type: Boolean,
        default: false,
    },
    vesselSchedule: {
        type: Object,
        default: () => ({}),
    },
});

const emit = defineEmits(["update:visible","reloadPage","close"]);

const containerRef = ref('');
const errorMessage = ref('');
const container = ref(null);

const getContainer = async () => {
    errorMessage.value = '';
    container.value = null;

    try {
        const response = await fetch(`/containers/get-container-by-reference/${containerRef.value}?vesselScheduleId=${props.vesselSchedule.id}`, {
            method: "GET",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": usePage().props.csrf,
            },
        });

        if (!response.ok) {
            const errorData = await response.json();
            if (errorData.errors && errorData.errors.reference) {
                throw new Error(errorData.errors.reference[0]);
            } else {
                throw new Error('Network response was not ok.');
            }
        } else {
            container.value = await response.json();
        }

    } catch (error) {
        errorMessage.value = error.message;
    }
}

const addVesselToSchedule = async (containerId) => {
    errorMessage.value = '';

    try {
        const response = await fetch(`/clearance/vessel-schedule/add-vessel/${props.vesselSchedule.id}`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": usePage().props.csrf,
            },
            body: JSON.stringify({ containerId: containerId })
        });

        if (!response.ok) {
            const errorData = await response.json();
            if (errorData.errors && errorData.errors.reference) {
                throw new Error(errorData.errors.reference[0]);
            } else {
                throw new Error('Network response was not ok.');
            }
        } else {
            containerRef.value = '';
            push.success('Container added to Vessel Schedule Successfully.')
            emit('close');
            emit('reloadPage');

        }

    } catch (error) {
        errorMessage.value = error.message;
    }
}
</script>

<template>
    <Dialog :style="{ width: '30rem' }" :visible="visible" header="Add Shipment to Schedule" modal @update:visible="(newValue) => $emit('update:visible', newValue)">
        <div class="flex">
            <div class="w-full">
                <Message v-if="errorMessage" class="my-1" closable severity="error">{{ errorMessage }}</Message>

                <div v-if="container" class="flex flex-col space-y-3 rounded-xl p-4 bg-gradient-to-tr hover:cursor-pointer from-violet-700 to-violet-500 my-5"
                     style="height: 170px">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-1 rounded-lg bg-violet-500 px-2 py-1">
                            <div class="h-4 w-4 rounded-md bg-green-300"></div>
                            <div class="text-white text-xs">{{ container?.container_type }}
                            </div>
                        </div>
                        <div class="text-violet-300 text-xs">
                            {{ moment(container?.estimated_time_of_arrival).format('DD MMM YYYY') }}
                        </div>
                    </div>
                    <h2 class="text-lg font-medium text-white">{{ container?.reference }}</h2>
                    <div class="flex justify-between">
                        <i class="ti ti-ship text-2xl text-white"></i>
                        <div class="flex space-x-4 items-center">
                            <div class="flex items-center space-x-1 text-violet-300">
                                <div class="text-xs">{{ container?.cargo_type }}</div>
                            </div>
                            <div class="flex items-center space-x-1 text-white">
                                <div class="text-xs uppercase">{{ container?.warehouse.name }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-between items-center space-x-4">
                        <div class="h-2 rounded-full bg-violet-500 flex-1">
                            <div
                                class="w-[100%] h-2 rounded-full from-green-300 to-green-400 bg-gradient-to-l"></div>
                        </div>
                        <div v-tooltip="'Add to Vessel Schedule'"
                             class="text-green-300 text-xs whitespace-nowrap cursor-pointer" @click.prevent="addVesselToSchedule(container.id)">Add
                        </div>
                    </div>
                </div>

                <div>
                    <InputText v-model="containerRef" autocomplete="off" class="w-full" placeholder="Enter Container Reference" required="true" />
                </div>

                <Button :disabled="!containerRef" class="w-full mt-2" label="Check Shipment"
                        type="button" @click.prevent="getContainer"/>
            </div>
        </div>
    </Dialog>
</template>
