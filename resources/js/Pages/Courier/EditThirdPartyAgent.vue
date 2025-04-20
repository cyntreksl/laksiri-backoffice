<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import { router, useForm } from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import InputError from "@/Components/InputError.vue";
import DangerOutlineButton from "@/Components/DangerOutlineButton.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import { push } from "notivue";
import Button from "primevue/button";
import Card from "primevue/card";
import InputText from "primevue/inputtext";
import Select from "primevue/select";
import IftaLabel from "primevue/iftalabel";
import MultiSelect from "primevue/multiselect";

const props = defineProps({
    agent: {
        type: Object,
        required: true
    },
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
});

const form = useForm({
    name: props.agent.name,
    branch_code: props.agent.branch_code,
    type: props.agent.type,
    currency_name: props.agent.currency_name,
    currency_symbol: props.agent.currency_symbol,
    cargo_modes: JSON.parse(props.agent.cargo_modes) || [],
    delivery_types: JSON.parse(props.agent.delivery_types) || [],
    package_types: JSON.parse(props.agent.package_types) || [],
    is_third_party_agent: props.agent.is_third_party_agent,
});

const handleBranchUpdate = () => {
    form.put(route("couriers.agents.update", props.agent.id), {
        onSuccess: () => {
            router.visit(route("couriers.agents.index"));
            push.success('Third Party Agent updated successfully!');
        },
        onError: () => {
            push.error('Something went wrong!');
        },
        preserveScroll: true,
        preserveState: true,
    });
};
</script>

<template>
    <AppLayout title="Edit Third Party Agent">
        <template #header>Edit Third Party Agents</template>

        <Breadcrumb />

        <form @submit.prevent="handleBranchUpdate">
            <div class="flex items-center justify-end p-2 my-5 space-x-2">
                <Button label="Cancel" severity="danger" variant="outlined"  @click="router.visit(route('couriers.agents.index'))" />

                <Button :class="{ 'opacity-50': form.processing }" :disabled="form.processing" icon="pi pi-arrow-right" iconPos="right" label="Update Third Party Agent" type="submit" />
            </div>

            <div class="grid grid-cols-1 mt-4 gap-4">
                <div class="sm:col-span-3 space-y-5">
                    <Card>
                        <template #title>
                            Edit Third Party Agent
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

