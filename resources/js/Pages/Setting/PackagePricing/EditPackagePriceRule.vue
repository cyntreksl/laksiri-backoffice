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
    packageRule: {
        type: Object,
        default: () => {}
    },
});

const form = useForm({
    destination_branch_id: props.packageRule.destination_branch_id || null,
    cargo_mode: props.packageRule.cargo_mode || '',
    hbl_type: props.packageRule.hbl_type || '',
    rule_title: props.packageRule.rule_title || '',
    length: props.packageRule.length || '',
    width: props.packageRule.width || '',
    height: props.packageRule.height || '',
});

const handlePackagePriceRuleUpdate = () => {
    form.put(route("setting.package-prices.update", props.packageRule.id), {
        onSuccess: () => {
            form.reset();
            router.visit(route("setting.package-prices.index"));
            push.success('Package price rule updated successfully!');
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
        <template #header>Package Pricing</template>

        <Breadcrumb/>
        <form @submit.prevent="handlePackagePriceRuleUpdate">

            <div class="flex items-center justify-between p-2 my-5">
                <h2 class="text-base font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100">
                    Edit Package Price Rule
                </h2>

                <div class="flex justify-end bottom-0 space-x-5">
                    <DangerOutlineButton @click="router.visit(route('setting.package-prices.index'))">Cancel</DangerOutlineButton>
                    <PrimaryButton :class="{ 'opacity-50': form.processing }" :disabled="form.processing"
                                   class="space-x-2"
                                   type="submit"
                    >
                        <span>Update Package Rule</span>
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
                                <InputLabel value="HBL Type"/>
                                <label for="">
                                    <select
                                        v-model="form.hbl_type"
                                        autocomplete="off"
                                        class="w-full"
                                        placeholder="Select a HBL Type..."
                                        x-init="$el._tom = new Tom($el)"
                                    >
                                        <option value="">Select a HBL Type...</option>
                                        <option v-for="(hblType, index) in hblTypes" :key="index" :value="hblType">{{hblType}}</option>
                                    </select>
                                </label>
                                <InputError :message="form.errors.hbl_type"/>
                            </div>

                            <div>
                                <InputLabel value="Rule Title"/>
                                <TextInput v-model="form.rule_title" class="w-full" placeholder="Rule Name"/>
                                <InputError :message="form.errors.rule_title"/>
                            </div>

                            <div>
                                <InputLabel value="Package Length"/>
                                <TextInput v-model="form.length" class="w-full" min="0" placeholder="0.00" type="number"/>
                                <InputError :message="form.errors.length"/>
                            </div>

                            <div>
                                <InputLabel value="Package Width"/>
                                <TextInput v-model="form.width" class="w-full" min="0" placeholder="0.00" type="number"/>
                                <InputError :message="form.errors.width"/>
                            </div>

                            <div>
                                <InputLabel value="Package Height"/>
                                <TextInput v-model="form.height" class="w-full" min="0" placeholder="0.00" type="number"/>
                                <InputError :message="form.errors.height"/>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </form>
    </AppLayout>
</template>
