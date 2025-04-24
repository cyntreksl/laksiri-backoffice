<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import {router, useForm} from "@inertiajs/vue3";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import {push} from "notivue";
import {ref, watch} from "vue";
import Card from 'primevue/card';
import Button from "primevue/button";
import SelectButton from "primevue/selectbutton";
import InputText from "primevue/inputtext";
import InputNumber from 'primevue/inputnumber';
import Message from 'primevue/message';

defineProps({
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
})

const form = useForm({
    destination_branch_id: null,
    cargo_mode: '',
    hbl_type: '',
    rule_title: '',
    length: '',
    width: '',
    height: '',
    per_package_charge: '',
    bill_price: 0,
    volume_charges: 0,
    bill_vat: 0,
    measure_type: 'cm'
});

const ruleLength = ref(0);
const ruleWidth = ref(0);
const ruleHeight = ref(0);

const conversionFactors = {
    cm: 1,
    m: 100,
    in: 2.54,
    ft: 30.48,
};

function convertMeasurements(measureType, value) {
    const factor = conversionFactors[measureType] || 1;
    return (value * factor).toFixed(2);
}

watch(
    () => form.measure_type,
    (newMeasureType) => {
        ruleLength.value = convertMeasurements(newMeasureType, form.length);
        ruleWidth.value = convertMeasurements(newMeasureType, form.width);
        ruleHeight.value = convertMeasurements(newMeasureType, form.height);
    }
);

watch(
    [() => form.length],
    ([newLength]) => {
        ruleLength.value = convertMeasurements(form.measure_type, newLength);
    }
);

watch(
    [() => form.width],
    ([newWidth]) => {
        ruleWidth.value = convertMeasurements(form.measure_type, newWidth);
    }
);

watch(
    [() => form.height],
    ([newHeight]) => {
        ruleHeight.value = convertMeasurements(form.measure_type, newHeight);
    }
);


const handlePriceRuleCreate = () => {
    form.length = ruleLength.value;
    form.width = ruleWidth.value;
    form.height = ruleHeight.value;
    form.post(route("setting.package-prices.store"), {
        onSuccess: () => {
            form.reset();
            router.visit(route("setting.package-prices.index"));
            push.success('Package rule created successfully!');
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
    <AppLayout title="Create Price Rule">
        <template #header>Create Package Price Rule</template>

        <Breadcrumb/>

        <form @submit.prevent="handlePriceRuleCreate">

            <div class="flex items-center justify-end p-2 my-5 space-x-2">
                <Button label="Cancel" severity="danger" variant="outlined" @click="router.visit(route('setting.package-prices.index'))" />

                <Button :class="{ 'opacity-50': form.processing }" :disabled="form.processing" icon="pi pi-arrow-right" iconPos="right" label="Create Package Rule" type="submit" />
            </div>

            <div class="grid grid-cols-1 mt-4 gap-4">
                <div class="sm:col-span-3 space-y-5">
                    <Card>
                        <template #title>Create Package Price Rule</template>
                        <template #content>
                            <div class="grid sm:grid-cols-2 gap-5 mt-3">

                                <div>
                                    <InputLabel class="mb-1" value="Destination Branch"/>
                                    <SelectButton v-model="form.destination_branch_id" :options="branches" name="HBL Type" option-label="name" option-value="id"/>
                                    <InputError :message="form.errors.destination_branch_id"/>
                                </div>

                                <div>
                                    <InputLabel class="mb-1" value="Cargo Mode"/>
                                    <SelectButton v-model="form.cargo_mode" :options="cargoModes" name="Cargo Type">
                                        <template #option="slotProps">
                                            <div class="flex items-center">
                                                <i v-if="slotProps.option === 'Sea Cargo'" class="ti ti-ship mr-2"></i>
                                                <i v-else class="ti ti-plane mr-2"></i>
                                                <span>{{ slotProps.option }}</span>
                                            </div>
                                        </template>
                                    </SelectButton>
                                    <InputError :message="form.errors.cargo_mode"/>
                                </div>

                                <div>
                                    <InputLabel class="mb-1" value="HBL Type"/>
                                    <SelectButton v-model="form.hbl_type" :options="hblTypes" name="HBL Type" />
                                    <InputError :message="form.errors.hbl_type"/>
                                </div>

                                <div>
                                    <InputLabel value="Rule Title"/>
                                    <InputText v-model="form.rule_title" class="w-full" placeholder="Enter Rule Name" />
                                    <InputError :message="form.errors.rule_title"/>
                                </div>

                                <div>
                                    <InputLabel class="mb-1" value="Measure Type"/>
                                    <SelectButton v-model="form.measure_type" :options="['cm', 'm', 'in', 'ft']" name="Measure Type" />
                                    <InputError :message="form.errors.measure_type"/>
                                </div>

                                <div>
                                    <InputLabel value="Package Length"/>
                                    <InputNumber v-model="form.length" class="w-full" fluid max-fraction-digits="2" min="0" placeholder="0.00" step="0.01"/>
                                    <Message severity="info" variant="simple">{{ruleLength}} cm</Message>
                                    <InputError :message="form.errors.length"/>
                                </div>

                                <div>
                                    <InputLabel value="Package Width"/>
                                    <InputNumber v-model="form.width" class="w-full" fluid max-fraction-digits="2" min="0" placeholder="0.00" step="0.01"/>
                                    <Message severity="info" variant="simple">{{ruleWidth}} cm</Message>
                                    <InputError :message="form.errors.width"/>
                                </div>

                                <div>
                                    <InputLabel value="Package Height"/>
                                    <InputNumber v-model="form.height" class="w-full" fluid max-fraction-digits="2" min="0" placeholder="0.00" step="0.01"/>
                                    <Message severity="info" variant="simple">{{ruleHeight}} cm</Message>
                                    <InputError :message="form.errors.height"/>
                                </div>

                                <div>
                                    <InputLabel value="Package Charge"/>
                                    <InputNumber v-model="form.per_package_charge" class="w-full" fluid max-fraction-digits="2" min="0" placeholder="0.00" step="0.01"/>
                                    <InputError :message="form.errors.per_package_charge"/>
                                </div>

                                <div>
                                    <InputLabel value="Bill Price"/>
                                    <InputNumber v-model="form.bill_price" class="w-full" fluid max-fraction-digits="2" min="0" placeholder="0.00" step="0.01"/>
                                    <InputError :message="form.errors.bill_price"/>
                                </div>

                                <div>
                                    <InputLabel value="Volume Charge"/>
                                    <InputNumber v-model="form.volume_charges" class="w-full" fluid max-fraction-digits="2" min="0" placeholder="0.00" step="0.01"/>
                                    <InputError :message="form.errors.volume_charges"/>
                                </div>

                                <div>
                                    <InputLabel value="Bill Vat"/>
                                    <InputNumber v-model="form.bill_vat" class="w-full" fluid max-fraction-digits="2" min="0" placeholder="0.00" step="0.01"/>
                                    <InputError :message="form.errors.bill_vat"/>
                                </div>

                            </div>
                        </template>
                    </Card>
                </div>
            </div>
        </form>
    </AppLayout>
</template>
