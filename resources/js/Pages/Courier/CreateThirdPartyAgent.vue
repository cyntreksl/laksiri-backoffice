<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import {router, useForm} from "@inertiajs/vue3";
import InputError from "@/Components/InputError.vue";
import {push} from "notivue";
import Button from "primevue/button";
import Card from "primevue/card";
import IftaLabel from "primevue/iftalabel";
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
import MultiSelect from 'primevue/multiselect';

defineProps({
    cargoModes: {
        type: Array,
        default: () => []
    },
    deliveryTypes: {
        type: Array,
        default: () => []
    },
    packageTypes: {
        type: Array,
        default: () => []
    },
    branchTypes: {
        type: Array,
        default: () => []
    },
})

const form = useForm({
    name: '',
    branch_code: '',
    type: null,
    currency_name: '',
    currency_symbol: '',
    cargo_modes: [],
    delivery_types: [],
    package_types: [],
    is_third_party_agent: true,

});

const handleBranchCreate = () => {
    form.post(route("couriers.agents.store"), {
        onSuccess: () => {
            form.reset();
            router.visit(route("couriers.agents.index"));
            push.success('Third Party Agent added successfully!');
        },
        onError: () => {
            push.error('Something went to wrong!');
        },
        preserveScroll: true,
        preserveState: true,
    });
}

</script>

<template>
    <AppLayout title="Create Third Party Agent">
        <template #header>Create Third Party Agent</template>

        <Breadcrumb/>

        <form @submit.prevent="handleBranchCreate">

            <div class="flex items-center justify-end p-2 my-5">
                <div class="flex justify-end bottom-0 space-x-5">
                    <Button label="Cancel" severity="danger" variant="outlined"  @click="router.visit(route('couriers.agents.index'))" />

                    <Button :class="{ 'opacity-50': form.processing }" :disabled="form.processing" icon="pi pi-arrow-right" iconPos="right" label="Create Third Party Agent" type="submit" />
                </div>
            </div>

            <div class="grid grid-cols-1 mt-4 gap-4">
                <div class="sm:col-span-3 space-y-5">
                    <Card>
                        <template #title>
                            Create Third Party Agent
                        </template>
                        <template #content>
                            <div class="grid sm:grid-cols-4 gap-5 mt-3">
                                <div class="sm:col-span-2">
                                    <IftaLabel>
                                        <InputText v-model="form.name" class="w-full" inputId="name" variant="filled" />
                                        <label for="name">Name</label>
                                    </IftaLabel>
                                    <InputError :message="form.errors.name"/>
                                </div>

                                <div class="sm:col-span-1">
                                    <IftaLabel>
                                        <InputText v-model="form.branch_code" class="w-full" inputId="branch_code" variant="filled" />
                                        <label for="branch_code">Branch Code</label>
                                    </IftaLabel>
                                    <InputError :message="form.errors.branch_code"/>
                                </div>

                                <div class="sm:col-span-1">
                                    <IftaLabel>
                                        <Select v-model="form.type" :options="branchTypes" class="w-full" inputId="type" variant="filled" />
                                        <label for="type">Branch Type</label>
                                    </IftaLabel>
                                    <InputError :message="form.errors.type"/>
                                </div>

                                <div class="sm:col-span-2">
                                    <IftaLabel>
                                        <InputText id="currency_name" v-model="form.currency_name" class="w-full" variant="filled" />
                                        <label for="currency_name">Currency Name</label>
                                    </IftaLabel>
                                    <InputError :message="form.errors.currency_name"/>
                                </div>

                                <div class="sm:col-span-2">
                                    <IftaLabel>
                                        <InputText id="currency_symbol" v-model="form.currency_symbol" class="w-full" placeholder="LKR" variant="filled" />
                                        <label for="currency_symbol">Currency Symbol</label>
                                    </IftaLabel>
                                    <InputError :message="form.errors.currency_symbol"/>
                                </div>

                                <div class="sm:col-span-1">
                                    <IftaLabel>
                                        <MultiSelect v-model="form.cargo_modes" :options="cargoModes" class="w-full"  inputId="cargo_modes" variant="filled" />
                                        <label for="cargo_modes">Cargo Modes</label>
                                    </IftaLabel>
                                    <InputError :message="form.errors.cargo_modes"/>
                                </div>

                                <div class="sm:col-span-2">
                                    <IftaLabel>
                                        <MultiSelect v-model="form.delivery_types" :options="deliveryTypes" class="w-full"  inputId="delivery_types" variant="filled" />
                                        <label for="delivery_types">Delivery Types</label>
                                    </IftaLabel>
                                    <InputError :message="form.errors.delivery_types"/>
                                </div>

                                <div class="sm:col-span-1">
                                    <IftaLabel>
                                        <MultiSelect v-model="form.package_types" :options="packageTypes" class="w-full"  inputId="package_types" variant="filled" />
                                        <label for="package_types">Package Types</label>
                                    </IftaLabel>
                                    <InputError :message="form.errors.package_types"/>
                                </div>
                            </div>
                        </template>
                    </Card>
                </div>
            </div>
        </form>
    </AppLayout>
</template>
