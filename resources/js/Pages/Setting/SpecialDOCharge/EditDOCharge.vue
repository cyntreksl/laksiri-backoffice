<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import {router, useForm} from "@inertiajs/vue3";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import {push} from "notivue";
import Button from "primevue/button";
import Card from "primevue/card";
import InputText from "primevue/inputtext";
import SelectButton from "primevue/selectbutton";
import {reactive, ref, watch, watchEffect} from "vue";
import Select from "primevue/select";
import InputNumber from "primevue/inputnumber";

const props = defineProps({
    cargoModes: {
        type: Array,
        default: () => []
    },
    hblTypes: {
        type: Array,
        default: () => []
    },
    branches: {
        type: Object,
        default: () => {}
    },
    charge: {
        type: Object,
        default: () => {}
    },
    packageTypes: {
        type: Array,
        default: () => []
    },
})

const collectionMethods = ref([
    { name: 'Destination', value: 'Destination' },
    { name: 'Departure', value: 'Departure' }
]);

const priceRuleItem = reactive({
    condition: 0,
    collected: '',
    quantity_basis: 1,
    package_type: '',
    charge: 0,
});

const form = useForm({
    agent_id: props.charge.agent_id || null,
    cargo_type: props.charge.cargo_type || '',
    hbl_type: props.charge.hbl_type || '',
    condition: props.charge.condition || '',
    collected: props.charge.collected || '',
    package_type: props.charge.package_type || null,
    charge: props.charge.charge || '',
});

const handleUpdate = () => {
    form.package_type = priceRuleItem.package_type;

    form.put(route("setting.special-do-charges.update", props.charge.id), {
        onSuccess: () => {
            form.reset();
            router.visit(route("setting.special-do-charges.index"));
            push.success('DO charge updated successfully!');
        },
        onError: () => {
            push.error('Something went to wrong!');
        },
        preserveScroll: true,
        preserveState: true,
    });
}

watch([() => props.charge.agent_id], ([newAgent]) => {
    filteredPackageType();
});

watchEffect(() => {
    priceRuleItem.package_type = props.charge.package_type;
})

const filteredPackageType = () => {
    return props.packageTypes.filter(pkg => pkg.branch_id === props.charge.agent_id);
};
</script>

<template>
    <AppLayout title="Edit DO Charge">
        <template #header>Edit DO Charge</template>

        <Breadcrumb/>

        <form @submit.prevent="handleUpdate">

            <div class="flex items-center justify-end p-2 my-5 space-x-2">
                <Button label="Cancel" severity="danger" variant="outlined" @click="router.visit(route('setting.special-do-charges.index'))" />

                <Button :class="{ 'opacity-50': form.processing }" :disabled="form.processing" icon="pi pi-arrow-right" iconPos="right" label="Save DO Charge" type="submit" />
            </div>

            <div class="grid grid-cols-1 mt-4 gap-4">
                <div class="sm:col-span-3 space-y-5">
                    <Card>
                        <template #title>Edit DO Charge</template>
                        <template #content>
                            <div class="grid sm:grid-cols-2 gap-5 mt-3">

                                <div>
                                    <InputLabel class="mb-1" value="Agent"/>
                                    <Select
                                        v-model="form.agent_id"
                                        :options="branches"
                                        class="w-full"
                                        option-value="id"
                                        optionLabel="name"
                                        placeholder="Select an Agent"
                                    />
                                    <InputError :message="form.errors.agent_id"/>
                                </div>

                                <div>
                                    <InputLabel class="mb-1" value="Cargo Type"/>
                                    <SelectButton v-model="form.cargo_type" :options="cargoModes" name="Cargo Type">
                                        <template #option="slotProps">
                                            <div class="flex items-center">
                                                <i v-if="slotProps.option === 'Sea Cargo'" class="ti ti-ship mr-2"></i>
                                                <i v-else class="ti ti-plane mr-2"></i>
                                                <span>{{ slotProps.option }}</span>
                                            </div>
                                        </template>
                                    </SelectButton>
                                    <InputError :message="form.errors.cargo_type"/>
                                </div>

                                <div>
                                    <InputLabel class="mb-1" value="HBL Type"/>
                                    <SelectButton v-model="form.hbl_type" :options="hblTypes" name="HBL Type" />
                                    <InputError :message="form.errors.hbl_type"/>
                                </div>

                                <div>
                                    <InputLabel class="mb-1" value="Collection Method"/>
                                    <Select
                                        v-model="form.collected"
                                        :options="collectionMethods"
                                        class="w-full"
                                        option-label="name"
                                        option-value="value"
                                        placeholder="Collection Method"
                                    />
                                    <InputError :message="form.errors.collected"/>
                                </div>

                                <div>
                                    <InputLabel value="Condition"/>
                                    <InputText v-model="form.condition" class="w-full" placeholder="Type Condition"/>
                                    <InputError :message="form.errors.condition"/>
                                </div>

                                <div>
                                    <InputLabel value="Quantity Basis"/>
                                    <Select
                                        v-model="priceRuleItem.package_type"
                                        :options="filteredPackageType()"
                                        class="w-full"
                                        option-value="name"
                                        optionLabel="name"
                                        placeholder="Select a Collection Method"
                                    />
                                    <InputError :message="form.errors.package_type"/>
                                </div>

                                <div>
                                    <InputLabel value="Charge"/>
                                    <InputNumber v-model="form.charge" :maxFractionDigits="5" :minFractionDigits="2" class="w-full" min="0" placeholder="Set Charge" step="1"/>
                                    <InputError :message="form.errors.charge"/>
                                </div>
                            </div>
                        </template>
                    </Card>
                </div>
            </div>
        </form>
    </AppLayout>
</template>
