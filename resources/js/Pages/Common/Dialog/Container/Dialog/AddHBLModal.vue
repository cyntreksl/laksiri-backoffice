<script setup>
import {ref} from "vue";
import moment from "moment";
import {router, usePage} from "@inertiajs/vue3";
import Dialog from "primevue/dialog";
import Button from "primevue/button";
import InputText from "primevue/inputtext";
import Message from 'primevue/message';
import Card from 'primevue/card';

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

const emit = defineEmits(["update:visible"]);

const hblRef = ref('');
const hblData = ref(null);
const errorMessage = ref('');
const loadingPackageId = ref(null);

const getHBLWithPackages = async () => {
    errorMessage.value = '';
    hblData.value = null;

    try {
        const response = await fetch("/containers/get-hbl/packages", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": usePage().props.csrf,
            },
            body: JSON.stringify({'reference': hblRef.value, 'cargo_type': props.container.cargo_type})
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
            hblData.value = data.hbl;
        }

    } catch (error) {
        errorMessage.value = error.message;
    }
}

const handleLoad = (packages) => {
    if (!Array.isArray(packages)) {
        packages = [packages];
    }

    loadingPackageId.value = packages[0].id;

    router.post(route("loading.loaded-containers.store"), {
            container_id: props.container.id,
            packages,
        },
        {
            onSuccess: () => {
                getHBLWithPackages();
            },
            onError: () => {
                console.error('Something went to wrong!');
            },
            onFinish: () => {
                loadingPackageId.value = null;
            },
            preserveScroll: true,
            preserveState: true,
        });
}
</script>

<template>
    <Dialog :style="{ width: '25rem' }" :visible="visible" header="HBL Add to Shipment" modal @update:visible="(newValue) => $emit('update:visible', newValue)">

        <div v-if="hblData">
            <Card v-for="(hblPackage, index) in hblData.packages" v-if="hblData.packages.length > 0" class="border mb-3">
                <template #title>
                    {{ hblData.hbl }}
                </template>
                <template #content>
                    <div class="flex justify-between items-center">
                        <div class="space-y-3 rounded-lg">
                            <div class="flex flex-wrap gap-1">
                                <div
                                    class="badge space-x-1 bg-slate-150 py-1 px-1.5 text-slate-800 dark:bg-navy-500 dark:text-navy-100">
                                    <i class="ti ti-calendar"></i>
                                    <span>{{
                                            moment(hblPackage.created_at).format('YYYY-MM-DD')
                                        }}</span>
                                </div>

                                <div
                                    class="badge space-x-1 bg-warning/10 py-1 px-1.5 text-warning dark:bg-warning/15">
                                    <i class="ti ti-scale"></i>
                                    <span>Volume {{ hblPackage.volume }}</span>
                                </div>

                                <div
                                    class="badge space-x-1 bg-error/10 py-1 px-1.5 text-error dark:bg-error/15">
                                    <i class="ti ti-weight"></i>
                                    <span>Weight {{ hblPackage.volume }}</span>
                                </div>

                                <div
                                    class="badge space-x-1 bg-success/10 py-1 px-1.5 text-success dark:bg-success/15">
                                    <i class="ti ti-packages"></i>
                                    <span>Quantity {{ hblPackage.quantity }}</span>
                                </div>
                            </div>
                            <p class="mt-px font-medium text-slate-400 dark:text-navy-300">
                                {{ hblPackage.package_type }}
                            </p>
                        </div>
                        <div class="px-2.5">
                            <Button v-tooltip="'Click to Load'" :disabled="loadingPackageId === hblPackage.id" icon="ti ti-corner-up-right-double text-2xl" rounded severity="success" variant="text" @click.prevent="handleLoad(hblPackage)">
                                <i v-if="loadingPackageId === hblPackage.id" class="pi pi-spin pi-spinner text-xl"></i>
                            </Button>
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
                    <InputText v-model="hblRef" autocomplete="off" class="w-full" placeholder="Enter HBL Reference" required="true" />
                </div>
                <Button :disabled="!hblRef" class="w-full mt-2" icon="pi pi-check" label="Confirm"
                        type="button" @click.prevent="getHBLWithPackages"/>
            </div>
        </div>
    </Dialog>
</template>
