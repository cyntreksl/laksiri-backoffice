<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import {router, useForm} from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import {push} from "notivue";
import {QuillEditor} from '@vueup/vue-quill'
import '@vueup/vue-quill/dist/vue-quill.snow.css';

const props = defineProps({
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
    branch: {
        type: Object,
        default: () => {
        }
    },
    settings: {
        type: Object,
        default: () => {
        }
    }
})

const form = useForm({
    name: props.branch.name,
    type: props.branch.type || null,
    currency_name: props.branch.currency_name || '',
    currency_symbol: props.branch.currency_symbol || '',
    cargo_modes: JSON.parse(props.branch.cargo_modes) || [],
    delivery_types: JSON.parse(props.branch.delivery_types) || [],
    package_types: JSON.parse(props.branch.package_types) || [],
});

const handleBranchUpdate = () => {
    form.put(route("branches.update", props.branch.id), {
        onSuccess: () => {
            router.visit(route("branches.edit", props.branch.id));
            push.success('Branch updated successfully!');
        },
        onError: () => {
            push.error('Something went to wrong!');
        },
        preserveScroll: true,
        preserveState: true,
    });
}

const settingForm = useForm({
    invoice_header_title: props.settings ? props.settings.invoice_header_title : '',
    invoice_header_subtitle: props.settings ? props.settings.invoice_header_subtitle : '',
    invoice_header_address: props.settings ? props.settings.invoice_header_address : '',
    invoice_header_telephone: props.settings ? props.settings.invoice_header_telephone : '',
    invoice_footer_title: props.settings ? props.settings.invoice_footer_title : '',
    invoice_footer_text: props.settings ? props.settings.invoice_footer_text : '',
    logo: '',
});

const handleSettingUpdate = () => {
    settingForm.post(route("setting.invoice.update"), {
        onSuccess: () => {
            router.visit(route("branches.edit", props.branch.id));
            push.success('Invoice Settings updated successfully!');
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
    <AppLayout title="Edit Branch">
        <template #header>Branches</template>

        <Breadcrumb :branch="branch"/>

        <div class="grid grid-cols-1 sm:grid-cols-6 mt-4 gap-4">
            <div class="sm:col-span-3 space-y-5 mb-10">

                <div class="flex items-center justify-between p-2 my-5">
                    <h2 class="text-base font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100">
                        Edit Branch
                    </h2>

                    <div class="flex justify-end bottom-0 space-x-5">
                        <PrimaryButton :class="{ 'opacity-50': form.processing }" :disabled="form.processing"
                                       class="space-x-2"
                                       type="submit"
                                       @click="handleBranchUpdate"
                        >
                            <span>Update Branch</span>
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

                <div class="card px-4 py-4 sm:px-5">
                    <div class="grid grid-cols-2">
                        <h2 class="text-lg font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100">
                            Basic Details
                        </h2>
                    </div>
                    <div class="grid sm:grid-cols-4 gap-5 mt-3">
                        <div class="sm:col-span-2">
                            <InputLabel value="Name"/>
                            <TextInput v-model="form.name" class="w-full"/>
                            <InputError :message="form.errors.name"/>
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block">
                                <InputLabel value="Type"/>
                                <select
                                    v-model="form.type"
                                    class="form-select w-full rounded-lg border border-slate-300 bg-white px-3 py-2 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:bg-navy-700 dark:hover:border-navy-400 dark:focus:border-accent"
                                >
                                    <option :value="null" disabled>
                                        Select Type
                                    </option>
                                    <option v-for="(branchType, index) in branchTypes" :key="index"
                                            :value="branchType">
                                        {{ branchType }}
                                    </option>
                                </select>
                            </label>
                            <InputError :message="form.errors.type"/>
                        </div>

                        <div class="sm:col-span-3">
                            <InputLabel value="Currency Name"/>
                            <TextInput v-model="form.currency_name" class="w-full" placeholder="Sri Lankan Rupee"/>
                            <InputError :message="form.errors.currency_name"/>
                        </div>

                        <div class="sm:col-span-1">
                            <InputLabel value="Currency Symbol"/>
                            <TextInput v-model="form.currency_symbol" class="w-full" placeholder="LKR"/>
                            <InputError :message="form.errors.currency_symbol"/>
                        </div>
                    </div>
                </div>

                <div class="card px-4 py-4 sm:px-5">
                    <div class="grid grid-cols-2">
                        <h2
                            class="text-lg font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100"
                        >
                            Cargo Modes
                        </h2>
                    </div>
                    <div class="my-5">
                        <div class="space-x-5">
                            <label class="block">
                                <span>Select Cargo Modes</span>
                                <select
                                    v-model="form.cargo_modes"
                                    autocomplete="off"
                                    class="mt-1.5 w-full"
                                    multiple
                                    placeholder="Select a Cargo Mode..."
                                    x-init="$el._tom = new Tom($el, {plugins: ['remove_button']})"
                                >
                                    <option value="">Select a Cargo Mode...</option>
                                    <option v-for="(cargoMode, index) in cargoModes" :key="index"
                                            :value="cargoMode">{{ cargoMode }}
                                    </option>
                                </select>
                            </label>
                        </div>
                        <InputError :message="form.errors.cargo_modes"/>
                    </div>
                </div>

                <div class="card px-4 py-4 sm:px-5">
                    <div class="grid grid-cols-2">
                        <h2
                            class="text-lg font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100"
                        >
                            Delivery Types
                        </h2>
                    </div>
                    <div class="my-5">
                        <div class="space-x-5">
                            <label class="block">
                                <span>Select Delivery Types</span>
                                <select
                                    v-model="form.delivery_types"
                                    autocomplete="off"
                                    class="mt-1.5 w-full"
                                    multiple
                                    placeholder="Select a Delivery Type..."
                                    x-init="$el._tom = new Tom($el, {plugins: ['remove_button']})"
                                >
                                    <option value="">Select a Delivery Type...</option>
                                    <option v-for="(deliveryType, index) in deliveryTypes" :key="index"
                                            :value="deliveryType">{{ deliveryType }}
                                    </option>
                                </select>
                            </label>
                        </div>
                        <InputError :message="form.errors.delivery_types"/>
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
                                    v-model="form.package_types"
                                    autocomplete="off"
                                    class="mt-1.5 w-full"
                                    multiple
                                    placeholder="Select a Package Type..."
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

            <div class="sm:col-span-3 space-y-5 mb-10">

                <div class="flex items-center justify-between p-2 my-5">
                    <h2 class="text-base font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100">
                        Invoice Settings
                    </h2>

                    <div class="flex justify-end bottom-0 space-x-5">
                        <PrimaryButton :class="{ 'opacity-50': form.processing }" :disabled="form.processing"
                                       class="space-x-2"
                                       type="submit"
                                       @click="handleSettingUpdate"
                        >
                            <span>Update Invoice</span>
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

                <div class="card px-4 py-4 sm:px-5">
                    <div class="grid grid-cols-2">
                        <h2 class="text-lg font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100">
                            Invoice Header
                        </h2>
                    </div>
                    <div class="grid sm:grid-cols-4 gap-5 mt-3">
                        <div class="sm:col-span-4">
                            <div class="sm:col-span-2 mb-4">
                                <TextInput v-model="settingForm.invoice_header_title" class="w-full"
                                           placeholder="Enter Header Title"/>
                                <InputError :message="settingForm.errors.invoice_header_title"/>
                            </div>
                        </div>

                        <div class="sm:col-span-4">
                            <div class="sm:col-span-2 mb-4">
                                <TextInput v-model="settingForm.invoice_header_subtitle"
                                           class="w-full"
                                           placeholder="Enter Header Subtitle"/>
                                <InputError :message="settingForm.errors.invoice_header_subtitle"/>
                            </div>
                        </div>

                        <div class="sm:col-span-4">
                            <div class="sm:col-span-2 mb-4">
                                <TextInput v-model="settingForm.invoice_header_address"
                                           class="w-full"
                                           placeholder="Enter Header Address"/>
                                <InputError :message="settingForm.errors.invoice_header_address"/>
                            </div>
                        </div>

                        <div class="sm:col-span-4">
                            <div class="sm:col-span-2 mb-4">
                                <TextInput v-model="settingForm.invoice_header_telephone"
                                           class="w-full"
                                           placeholder="Enter Header Telphones"/>
                                <InputError :message="settingForm.errors.invoice_header_telephone"/>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card px-4 py-4 sm:px-5">
                    <div class="grid grid-cols-2">
                        <h2 class="text-lg font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100">
                            Invoice Footer
                        </h2>
                    </div>
                    <div class="grid sm:grid-cols-4 gap-5 mt-3">
                        <div class="sm:col-span-4">
                            <div class="sm:col-span-2 mb-4">
                                <TextInput v-model="settingForm.invoice_footer_title" class="w-full"
                                           placeholder="Enter Footer Title"/>
                                <InputError :message="settingForm.errors.invoice_footer_title"/>
                            </div>

                            <label class="block">
                                <QuillEditor v-model:content="settingForm.invoice_footer_text"
                                             content-type="html" placeholder="Enter Footer Text"/>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
