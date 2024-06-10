<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import {router, useForm} from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import InputError from "@/Components/InputError.vue";
import DangerOutlineButton from "@/Components/DangerOutlineButton.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import {push} from "notivue";
import Checkbox from "@/Components/Checkbox.vue";

const props = defineProps({
    cargoModes: {
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

const form = useForm({
    destination_branch_id: props.priceRule.destination_branch_id || null,
    cargo_mode: props.priceRule.cargo_mode || '',
    price_mode: props.priceRule.price_mode || '',
    condition: props.priceRule.condition || '',
    true_action: props.priceRule.true_action || '',
    false_action: props.priceRule.false_action || '',
    bill_price: props.priceRule.bill_price || null,
    bill_vat: props.priceRule.bill_vat || null,
    destination_charges: props.priceRule.destination_charges || '',
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
        <template #header>Pricing</template>

        <Breadcrumb :id="priceRule.id"/>

        <form @submit.prevent="handlePriceRuleUpdate">

            <div class="flex items-center justify-between p-2 my-5">
                <h2 class="text-base font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100">
                    Edit Price Rule
                </h2>

                <div class="flex justify-end bottom-0 space-x-5">
                    <DangerOutlineButton @click="router.visit(route('setting.prices.index'))">Cancel</DangerOutlineButton>
                    <PrimaryButton :class="{ 'opacity-50': form.processing }" :disabled="form.processing"
                                   class="space-x-2"
                                   type="submit"
                    >
                        <span>Update Price Rule</span>
                        <svg
                            class="size-5"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.5"
                            viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <path
                                d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                        </svg>
                    </PrimaryButton>
                </div>
            </div>

            <div class="grid grid-cols-1 mt-4 gap-4">
                <div class="sm:col-span-3 space-y-5">
                    <div class="card px-4 py-4 sm:px-5">
                        <div class="grid grid-cols-2">
                            <h2 class="text-lg font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100">
                                Basic Details
                            </h2>
                        </div>
                        <div class="grid sm:grid-cols-2 gap-5 mt-3">

                            <div>
                                <label class="block">
                                    <InputLabel value="Destination Branch"/>
                                    <select
                                        v-model="form.destination_branch_id"
                                        class="form-select w-full rounded-lg border border-slate-300 bg-white px-3 py-2 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:bg-navy-700 dark:hover:border-navy-400 dark:focus:border-accent"
                                    >
                                        <option :value="null" disabled>
                                            Select Branch
                                        </option>
                                        <option v-for="branch in branches" :key="branch.id" :value="branch.id">
                                            {{ branch.name }}
                                        </option>
                                    </select>
                                </label>
                                <InputError :message="form.errors.destination_branch_id"/>
                            </div>

                            <div>
                                <InputLabel value="Cargo Mode"/>
                                <label for="">
                                    <select
                                        v-model="form.cargo_mode"
                                        autocomplete="off"
                                        class="w-full"
                                        placeholder="Select a Cargo Mode..."
                                        x-init="$el._tom = new Tom($el)"
                                    >
                                        <option value="">Select a Cargo Mode...</option>
                                        <option v-for="(cargoMode, index) in cargoModes" :key="index" :value="cargoMode">{{cargoMode}}</option>
                                    </select>
                                </label>
                                <InputError :message="form.errors.cargo_mode"/>
                            </div>

                            <div>
                                <InputLabel value="Price Mode"/>
                                <label for="">
                                    <select
                                        v-model="form.price_mode"
                                        autocomplete="off"
                                        class="w-full"
                                        placeholder="Select a Price Mode..."
                                        x-init="$el._tom = new Tom($el)"
                                    >
                                        <option value="">Select a Price Mode...</option>
                                        <option value="weight">Weight</option>
                                        <option value="volume">Volume</option>
                                    </select>
                                </label>
                                <InputError :message="form.errors.price_mode"/>
                            </div>

                            <div>
                                <InputLabel value="Condition"/>
                                <TextInput v-model="form.condition" class="w-full" placeholder="Type Condition"/>
                                <InputError :message="form.errors.condition"/>
                            </div>

                            <div>
                                <InputLabel value="True Action"/>
                                <TextInput v-model="form.true_action" class="w-full" placeholder="Set True Action"/>
                                <InputError :message="form.errors.true_action"/>
                            </div>

                            <div>
                                <InputLabel value="False Action"/>
                                <TextInput v-model="form.false_action" class="w-full" placeholder="Set False Action"/>
                                <InputError :message="form.errors.false_action"/>
                            </div>

                            <div>
                                <InputLabel value="Bill Price"/>
                                <TextInput v-model="form.bill_price" class="w-full" min="0" placeholder="0.00" type="number"/>
                                <InputError :message="form.errors.bill_price"/>
                            </div>

                            <div>
                                <InputLabel value="Bill VAT (%)"/>
                                <TextInput v-model="form.bill_vat" class="w-full" min="0" placeholder="0.00" step="any" type="number"/>
                                <InputError :message="form.errors.bill_vat"/>
                            </div>

                            <div>
                                <InputLabel value="Destination Charges"/>
                                <TextInput v-model="form.destination_charges" class="w-full" placeholder="Set Destination Charges"/>
                                <InputError :message="form.errors.destination_charges"/>
                            </div>

                            <div>
                                <InputLabel value="Is Editable"/>
                                <Checkbox v-model="form.is_editable" :checked="form.is_editable"/>
                                <InputError :message="form.errors.is_editable"/>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </AppLayout>
</template>
