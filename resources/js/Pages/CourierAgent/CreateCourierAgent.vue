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

defineProps({

})

const form = useForm({
    company_name: "",
    website: "",
    contact_number_1: "",
    contact_number_2: "",
    email: "",
    address: "",
    logo: null,
    invoice_header: "",
    invoice_footer: "",

});

const handleBranchCreate = () => {
    form.post(route("courier-agents.store"), {
        onSuccess: () => {
            form.reset();
            router.visit(route("courier-agents.index"));
            push.success('Courier Agent added successfully!');
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
    <AppLayout title="Create Agent">
        <template #header>Courier Agent</template>

        <Breadcrumb/>
        <form @submit.prevent="handleBranchCreate">

            <div class="flex items-center justify-between p-2 my-5">
                <h2 class="text-base font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100">
                    Create New Courier Agent
                </h2>

                <div class="flex justify-end bottom-0 space-x-5">
                    <DangerOutlineButton @click="router.visit(route('agents.index'))">Cancel</DangerOutlineButton>
                    <PrimaryButton :class="{ 'opacity-50': form.processing }" :disabled="form.processing"
                                   class="space-x-2"
                                   type="submit"
                    >
                        <span>Create a Courier Agent</span>
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
                                <InputLabel value="Company Name"/>
                                <TextInput v-model="form.company_name" class="w-full"/>
                                <InputError :message="form.errors.company_name"/>
                            </div>

                            <div class="sm:col-span-2">
                                <InputLabel value="Website"/>
                                <TextInput v-model="form.website" class="w-full"/>
                                <InputError :message="form.errors.website"/>
                            </div>

<!--                            <div class="sm:col-span-1">-->
<!--                                <label class="block">-->
<!--                                    <InputLabel value="Type"/>-->
<!--                                    <select-->
<!--                                        v-model="form.type"-->
<!--                                        class="form-select w-full rounded-lg border border-slate-300 bg-white px-3 py-2 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:bg-navy-700 dark:hover:border-navy-400 dark:focus:border-accent"-->
<!--                                    >-->
<!--                                        <option :value="null" disabled>-->
<!--                                            Select Type-->
<!--                                        </option>-->
<!--                                        <option v-for="(branchType, index) in branchTypes" :key="index"-->
<!--                                                :value="branchType">-->
<!--                                            {{ branchType }}-->
<!--                                        </option>-->
<!--                                    </select>-->
<!--                                </label>-->
<!--                                <InputError :message="form.errors.type"/>-->
<!--                            </div>-->

                            <div class="sm:col-span-2">
                                <InputLabel value="Contact Number 1"/>
                                <TextInput v-model="form.contact_number_1" class="w-full"/>
                                <InputError :message="form.errors.contact_number_1"/>
                            </div>

                            <div class="sm:col-span-2">
                                <InputLabel value="Contact Number 2"/>
                                <TextInput v-model="form.contact_number_2" class="w-full"/>
                                <InputError :message="form.errors.contact_number_2"/>
                            </div>
                            <div class="sm:col-span-2">
                                <InputLabel value="Email"/>
                                <TextInput v-model="form.email" class="w-full"/>
                                <InputError :message="form.errors.email"/>
                            </div>
                            <div class="sm:col-span-2">
                                <InputLabel value="Address"/>
                                <TextInput v-model="form.address" class="w-full"/>
                                <InputError :message="form.errors.address"/>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="sm:col-span-2 space-y-5">
                    <div class="card px-4 py-4 sm:px-5">
                        <div class="grid grid-cols-2">
                            <h2
                                class="text-lg font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100"
                            >
                                Invoice Header
                            </h2>
                        </div>
                        <div class="my-5">
                            <div class="space-x-5">
                                <div class="sm:col-span-2 mb-4">
                                    <TextInput v-model="form.invoice_header" class="w-full"
                                               placeholder="Enter Header Title"/>
                                    <InputError :message="form.errors.invoice_header"/>
                                </div>
                            </div>
                            <InputError :message="form.errors.invoice_header"/>
                        </div>
                    </div>

                    <div class="card px-4 py-4 sm:px-5">
                        <div class="grid grid-cols-2">
                            <h2
                                class="text-lg font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100"
                            >
                                Invoice Footer
                            </h2>
                        </div>
                        <div class="my-5">
                            <div class="space-x-5">
                                <div class="sm:col-span-2 mb-4">
                                    <TextInput v-model="form.invoice_footer" class="w-full"
                                               placeholder="Enter Footer Title"/>
                                    <InputError :message="form.errors.invoice_footer"/>
                                </div>
                            </div>
                            <InputError :message="form.errors.invoice_footer"/>
                        </div>
                    </div>

                    <div class="card px-4 py-4 sm:px-5">
                        <div class="grid grid-cols-2">
                            <h2
                                class="text-lg font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100"
                            >
                                Package Types
                            </h2>
                        </div>
                        <div class="my-5">
                            <div class="space-x-5">
                                <label class="block">
                                    <span>Select Package Types</span>
                                    <select
                                        autocomplete="off"
                                        class="mt-1.5 w-full"
                                        multiple
                                        placeholder="Select a Package Type..."
                                        v-model="form.package_types"
                                        x-init="$el._tom = new Tom($el, {plugins: ['remove_button']})"
                                    >
                                        <option value="">Select a Package Type...</option>
                                        <option v-for="(packageType, index) in packageTypes" :key="index"
                                                :value="packageType">{{ packageType }}
                                        </option>
                                    </select>
                                </label>
                            </div>
                            <InputError :message="form.errors.package_types"/>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </AppLayout>
</template>
