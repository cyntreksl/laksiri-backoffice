<script setup>
import {ref} from "vue";
import moment from "moment";
import {router, usePage} from "@inertiajs/vue3";
import Dialog from "primevue/dialog";
import Button from "primevue/button";
import InputText from "primevue/inputtext";
import Message from 'primevue/message';
import Card from 'primevue/card';
import {reference} from "@popperjs/core";
import {push} from "notivue";

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

const addVesselToSchedule = async () => {
    errorMessage.value = '';

    try {
        const response = await fetch(`vessel-schedule/add-vessel/${props.vesselSchedule.id}`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": usePage().props.csrf,
            },
            body: JSON.stringify({ reference: containerRef.value })
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

// const handleLoad = (packages) => {
//     if (!Array.isArray(packages)) {
//         packages = [packages];
//     }
//
//     router.post(route("loading.loaded-containers.store"), {
//             container_id: props.container.id,
//             packages,
//         },
//         {
//             onSuccess: () => {
//                 getHBLWithPackages();
//             },
//             onError: () => {
//                 console.error('Something went to wrong!');
//             },
//             preserveScroll: true,
//             preserveState: true,
//         });
// }
</script>

<template>
    <Dialog :style="{ width: '25rem' }" :visible="visible" header="HBL Vessel to Schedule" modal @update:visible="(newValue) => $emit('update:visible', newValue)">
        <div class="flex">
            <div class="w-full">
                <Message v-if="errorMessage" class="my-1" closable severity="error">{{ errorMessage }}</Message>

                <div>
                    <InputText v-model="containerRef" autocomplete="off" class="w-full" placeholder="Enter Container Reference" required="true" />
                </div>
                <Button :disabled="!containerRef" class="w-full mt-2" icon="pi pi-check" label="Confirm"
                        type="button" @click.prevent="addVesselToSchedule"/>
            </div>
        </div>
    </Dialog>
</template>
