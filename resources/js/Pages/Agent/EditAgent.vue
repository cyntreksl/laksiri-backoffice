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
    cargo_modes: props.agent.cargo_modes,
    delivery_types: props.agent.delivery_types,
    package_types: props.agent.package_types,
    is_third_party_agent: props.agent.is_third_party_agent,
});

const handleBranchUpdate = () => {
    form.put(route("agents.edit", props.agent.id), {
        onSuccess: () => {
            push.success('Branch updated successfully!');
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
    <AppLayout title="Edit Agent">
        <template #header>Agents</template>

        <Breadcrumb />
        <form @submit.prevent="handleBranchUpdate">
            <div class="flex items-center justify-between p-2 my-5">
                <h2 class="text-base font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100">
                    Edit Agent
                </h2>

                <div class="flex justify-end bottom-0 space-x-5">
                    <DangerOutlineButton @click="router.visit(route('agents.index'))">Cancel</DangerOutlineButton>
                    <PrimaryButton :class="{ 'opacity-50': form.processing }" :disabled="form.processing" class="space-x-2" type="submit">
                        <span>Update Agent</span>
                        <svg class="size-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </PrimaryButton>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-5 mt-4 gap-4">
                <div class="sm:col-span-3 space-y-5">
                    <div class="card px-4 py-4 sm:px-5">
                        <div class="grid grid-cols-2">
                            <h2 class="text-lg font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100">
                                Basic Details
                            </h2>
                        </div>
                        <div class="grid sm:grid-cols-4 gap-5 mt-3">
                            <div class="sm:col-span-2">
                                <InputLabel value="Name" />
                                <TextInput v-model="form.name" class="w-full" />
                                <InputError :message="form.errors.name" />
                            </div>

                            <div class="sm:col-span-1">
                                <InputLabel value="Branch Code" />
                                <TextInput v-model="form.branch_code" class="w-full" />
                                <InputError :message="form.errors.branch_code" />
                            </div>

                            <div class="sm:col-span-1">
                                <label class="block">
                                    <InputLabel value="Type" />
                                    <select v-model="form.type" class="form-select w-full rounded-lg border border-slate-300 bg-white px-3 py-2 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:bg-navy-700 dark:hover:border-navy-400 dark:focus:border-accent">
                                        <option :value="null" disabled>Select Type</option>
                                        <option v-for="(branchType, index) in branchTypes" :key="index" :value="branchType">
                                            {{ branchType }}
                                        </option>
                                    </select>
                                </label>
                                <InputError :message="form.errors.type" />
                            </div>

                            <div class="sm:col-span-3">
                                <InputLabel value="Currency Name" />
                                <TextInput v-model="form.currency_name" class="w-full" placeholder="Sri Lankan Rupee" />
                                <InputError :message="form.errors.currency_name" />
                            </div>

                            <div class="sm:col-span-1">
                                <InputLabel value="Currency Symbol" />
                                <TextInput v-model="form.currency_symbol" class="w-full" placeholder="LKR" />
                                <InputError :message="form.errors.currency_symbol" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="sm:col-span-2 space-y-5">
                    <div class="card px-4 py-4 sm:px-5">
                        <div class="grid grid-cols-2">
                            <h2 class="text-lg font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100">
                                Cargo Modes
                            </h2>
                        </div>
                        <div class="my-5">
                            <div class="space-x-5">
                                <label class="block">
                                    <span>Select Cargo Modes</span>
                                    <select autocomplete="off" class="mt-1.5 w-full" multiple placeholder="Select a Cargo Mode..." v-model="form.cargo_modes" x-init="$el._tom = new Tom($el, {plugins: ['remove_button']})">
                                        <option value="">Select a Cargo Mode...</option>
                                        <option v-for="(cargoMode, index) in cargoModes" :key="index" :value="cargoMode">{{ cargoMode }}</option>
                                    </select>
                                </label>
                            </div>
                            <InputError :message="form.errors.cargo_modes" />
                        </div>
                    </div>

                    <div class="card px-4 py-4 sm:px-5">
                        <div class="grid grid-cols-2">
                            <h2 class="text-lg font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100">
                                Delivery Types
                            </h2>
                        </div>
                        <div class="my-5">
                            <div class="space-x-5">
                                <label class="block">
                                    <span>Select Delivery Types</span>
                                    <select autocomplete="off" class="mt-1.5 w-full" multiple placeholder="Select a Delivery Type..." v-model="form.delivery_types" x-init="$el._tom = new Tom($el, {plugins: ['remove_button']})">
                                        <option value="">Select a Delivery Type...</option>
                                        <option v-for="(deliveryType, index) in deliveryTypes" :key="index" :value="deliveryType">{{ deliveryType }}</option>
                                    </select>
                                </label>
                            </div>
                            <InputError :message="form.errors.delivery_types" />
                        </div>
                    </div>

                    <div class="card px-4 py-4 sm:px-5">
                        <div class="grid grid-cols-2">
                            <h2 class="text-lg font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100">
                                Package Types
                            </h2>
                        </div>
                        <div class="my-5">
                            <div class="space-x-5">
                                <label class="block">
                                    <span>Select Package Types</span>
                                    <select autocomplete="off" class="mt-1.5 w-full" multiple placeholder="Select a Package Type..." v-model="form.package_types" x-init="$el._tom = new Tom($el, {plugins: ['remove_button']})">
                                        <option value="">Select a Package Type...</option>
                                        <option v-for="(packageType, index) in packageTypes" :key="index" :value="packageType">{{ packageType }}</option>
                                    </select>
                                </label>
                            </div>
                            <InputError :message="form.errors.package_types" />
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </AppLayout>
</template>

