<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import {router, useForm} from "@inertiajs/vue3";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import {push} from "notivue";
import Checkbox from "@/Components/Checkbox.vue";
import Button from "primevue/button";
import Card from "primevue/card";
import InputText from "primevue/inputtext";
import SelectButton from "primevue/selectbutton";
import {ref} from "vue";

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
    priceRule: {
        type: Object,
        default: () => {}
    },
})

const modes = ref(['volume', 'weight']);

const form = useForm({
    destination_branch_id: props.priceRule.destination_branch_id || null,
    cargo_mode: props.priceRule.cargo_mode || '',
    hbl_type: props.priceRule.hbl_type || '',
    price_mode: props.priceRule.price_mode || '',
    condition: props.priceRule.condition || '',
    true_action: props.priceRule.true_action || '',
    false_action: props.priceRule.false_action || '',
    bill_price: props.priceRule.bill_price || null,
    bill_vat: props.priceRule.bill_vat || 0,
    volume_charges: props.priceRule.volume_charges || '',
    per_package_charges: props.priceRule.per_package_charges || '',
    is_editable: Boolean(props.priceRule.is_editable),
});

const handlePriceRuleUpdate = () => {
    form.put(route("setting.prices.update", props.priceRule.id), {
        onSuccess: () => {
            form.reset();
            router.visit(route("setting.prices.index"));
            push.success('Price rule updated successfully!');
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
    <AppLayout title="Edit Price Rule">
        <template #header>Edit Price Rule</template>

        <Breadcrumb/>

        <form @submit.prevent="handlePriceRuleUpdate">

            <div class="flex items-center justify-end p-2 my-5 space-x-2">
                <Button label="Cancel" severity="danger" variant="outlined" @click="router.visit(route('setting.prices.index'))" />

                <Button :class="{ 'opacity-50': form.processing }" :disabled="form.processing" icon="pi pi-arrow-right" iconPos="right" label="Save Price Rule" type="submit" />
            </div>

            <div class="grid grid-cols-1 mt-4 gap-4">
                <div class="sm:col-span-3 space-y-5">
                    <Card>
                        <template #title>Edit Price Rule</template>
                        <template #content>
                            <div class="grid sm:grid-cols-2 gap-5 mt-3">

                                <div>
                                    <InputLabel class="mb-1" value="Destination Branch"/>
                                    <SelectButton v-model="form.destination_branch_id" :options="branches" name="Destination Branch" option-label="name" option-value="id"/>
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
                                    <InputLabel class="mb-1" value="Price Mode"/>
                                    <SelectButton v-model="form.price_mode" :options="modes" name="Price Mode" />
                                    <InputError :message="form.errors.price_mode"/>
                                </div>

                                <div>
                                    <InputLabel value="Condition"/>
                                    <InputText v-model="form.condition" :disabled="form.condition === '>0' || form.condition === '> 0'" class="w-full" placeholder="Type Condition"/>
                                    <InputError :message="form.errors.condition"/>
                                </div>

                                <div>
                                    <InputLabel value="True Action"/>
                                    <InputText v-model="form.true_action" class="w-full" placeholder="Set True Action" />
                                    <InputError :message="form.errors.true_action"/>
                                </div>

                                <div class="hidden">
                                    <InputLabel value="False Action"/>
                                    <InputText v-model="form.false_action" class="w-full" placeholder="Set False Action" />
                                    <InputError :message="form.errors.false_action"/>
                                </div>

                                <div>
                                    <InputLabel value="Bill Price"/>
                                    <InputText v-model="form.bill_price" class="w-full" placeholder="Set Bill Price" />
                                    <InputError :message="form.errors.bill_price"/>
                                </div>

                                <div>
                                    <InputLabel value="Bill VAT (%)"/>
                                    <InputText v-model="form.bill_vat" class="w-full" placeholder="Set Bill VAT (%)" />
                                    <InputError :message="form.errors.bill_vat"/>
                                </div>

                                <div>
                                    <InputLabel value="Volume Charges"/>
                                    <InputText v-model="form.volume_charges" class="w-full" placeholder="Set Volume Charges" />
                                    <InputError :message="form.errors.volume_charges"/>
                                </div>

                                <div>
                                    <InputLabel value="Per Package Charges"/>
                                    <InputText v-model="form.per_package_charges" class="w-full" placeholder="Set Per Package Charges" />
                                    <InputError :message="form.errors.per_package_charges"/>
                                </div>

                                <div>
                                    <InputLabel value="Editable"/>
                                    <Checkbox v-model="form.is_editable" :checked="form.is_editable" binary inputId="is-editable"/>
                                    <InputError :message="form.errors.is_editable"/>
                                </div>
                            </div>
                        </template>
                    </Card>
                </div>
            </div>
        </form>
    </AppLayout>
</template>
