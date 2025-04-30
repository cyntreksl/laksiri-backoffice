<script setup>
import {ref} from "vue";
import moment from "moment";
import {router, usePage} from "@inertiajs/vue3";
import {push} from "notivue";
import Dialog from "primevue/dialog";
import Card from "primevue/card";
import Message from "primevue/message";
import InputText from "primevue/inputtext";
import Button from "primevue/button";

const props = defineProps({
    visible: {
        type: Boolean,
        default: false,
    },
    container: {
        type: Object,
        default: () => {
        },
    }
});

const emit = defineEmits(["update:visible", 'fetchContainer']);

const mhblRef = ref('');
const mhblData = ref(null);
const errorMessage = ref('');

const getMHBLWithHBLS = async () => {
    errorMessage.value = '';
    mhblData.value = null;

    try {
        const response = await fetch("/containers/get-unloaded-mhbl-hbl", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": usePage().props.csrf,
            },
            body: JSON.stringify({'reference': mhblRef.value})
        });

        if (!response.ok) {
            const errorData = await response.json();
            if (errorData.errors && errorData.errors.reference) {
                throw new Error(errorData.errors.reference[0]);
            } else {
                throw new Error('Network response was not ok.');
            }
        } else {
            const data = await response.json();
            mhblData.value = data.mhbl;
        }

    } catch (error) {
        errorMessage.value = error.message;
    }
}

const handleLoad = () => {
    router.post(route("loading.loaded-containers.add-mhbl"), {
            container_id: props.container.id,
            mhbl: mhblRef.value,
        },
        {
            onSuccess: () => {
                emit('close');
                emit('fetchContainer');
                push.success('Loaded MHBL successfully!');
            },
            onError: () => {
                console.error('Something went to wrong!');
            },
            preserveScroll: true,
            preserveState: true,
        });
}
</script>

<template>
    <Dialog :style="{ width: '25rem' }" :visible="visible" header="MHBL Add to Shipment" modal @update:visible="(newValue) => $emit('update:visible', newValue)">
        <div v-if="mhblData">
            <Card  v-for="(hbl, index) in mhblData.hbls" v-if="mhblData.hbls.length > 0" class="border mb-3">
                <template #title>
                    {{ hbl.hbl_number }}
                </template>
                <template #content>
                    <div class="flex justify-between items-center">
                        <div class="space-y-3 rounded-lg px-2.5 pb-2 pt-1.5">
                            <div class="flex flex-wrap gap-1">
                                <div
                                    class="badge space-x-1 bg-slate-150 py-1 px-1.5 text-slate-800 dark:bg-navy-500 dark:text-navy-100">
                                    <i class="ti ti-calendar"></i>
                                    <span>{{
                                            moment(hbl.created_at).format('YYYY-MM-DD')
                                        }}</span>
                                </div>

                                <div
                                    class="badge space-x-1 bg-warning/10 py-1 px-1.5 text-warning dark:bg-warning/15">
                                    <i class="ti ti-scale"></i>
                                    <span>Volume {{ hbl.packages.reduce((sum, pkg) => sum + pkg.volume, 0) }}</span>
                                </div>

                                <div
                                    class="badge space-x-1 bg-error/10 py-1 px-1.5 text-error dark:bg-error/15">
                                    <i class="ti ti-weight"></i>
                                    <span>Weight {{ hbl.packages.reduce((sum, pkg) => sum + pkg.weight, 0) }}</span>
                                </div>

                                <div
                                    class="badge space-x-1 bg-success/10 py-1 px-1.5 text-success dark:bg-success/15">
                                    <i class="ti ti-packages"></i>
                                    <span>Packages {{ hbl.packages.length }}</span>
                                </div>
                            </div>
                            <p class="mt-px font-medium text-slate-400 dark:text-navy-300">
                                {{ hbl.hbl_name }}
                            </p>
                        </div>
                    </div>
                </template>
            </Card>

            <Message v-else class="my-1" closable severity="error">Enter Valid HBL OR Reference Number</Message>
        </div>

        <div class="flex">
            <div class="w-full">
                <Message v-if="errorMessage" class="my-1" closable severity="error">{{ errorMessage }}</Message>
                <div>
                    <InputText v-model="mhblRef" autocomplete="off" class="w-full" placeholder="Enter HBL Reference" required="true" />
                </div>
                <Button :disabled="!mhblRef" class="w-full mt-2" icon="pi pi-check" label="Confirm"
                        type="button" @click.prevent="getMHBLWithHBLS"/>
            </div>
        </div>

        <div class="flex">
            <Button :disabled="!mhblData" class="w-full mt-2" label="Load MHBL"
                    type="button" @click.prevent="handleLoad"/>
        </div>

    </Dialog>
</template>
