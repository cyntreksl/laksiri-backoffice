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
import SecondaryButton from "@/Components/SecondaryButton.vue";
import {computed, ref} from "vue";

const props = defineProps({
    courierAgent: {
        type: Object,
        required: true
    },
    countryCodes: {
        type: Array,
        default: () => [],
    }
});

const splitCountryCode = (fullNumber) => {
    if(fullNumber){
        for (let code of props.countryCodes) {
            if (fullNumber.startsWith(code)) {
                return code;
            }
        }
    }
};
const splitContactNumber = (fullNumber) => {
    if(fullNumber) {
        for (let code of props.countryCodes) {
            if (fullNumber.startsWith(code)) {
                return fullNumber.slice(code.length);
            }
        }
    }
};

const countryCode1 = ref(splitCountryCode(props.courierAgent.contact_number_1));
const contactNumber1 = ref(splitContactNumber(props.courierAgent.contact_number_1));

const additionalNumberCountryCode = ref(splitCountryCode(props.courierAgent.contact_number_2) ?? '+94');
const additionalNumber = ref(splitContactNumber(props.courierAgent.contact_number_2));

const form = useForm({
    company_name: props.courierAgent.company_name,
    website: props.courierAgent.website,
    contact_number_1: props.courierAgent.contact_number_1,
    contact_number_2: props.courierAgent.contact_number_2,
    email: props.courierAgent.email,
    address: props.courierAgent.address,
    logo: props.courierAgent.logo,
    invoice_header: props.courierAgent.invoice_header,
    invoice_footer: props.courierAgent.invoice_footer,
    _method: 'PUT'
});

const handleAgentUpdate = () => {
    if (logoInput.value) {
        form.logo = logoInput.value.files[0];
    }

    form.contact_number_1 = countryCode1.value + contactNumber1.value;

    if(additionalNumber.value){
        form.contact_number_2 = additionalNumberCountryCode.value + additionalNumber.value;
    }

    form.post(route("couriers.courier-agents.update", props.courierAgent.id), {
        onSuccess: () => {
            router.visit(route("couriers.courier-agents.index"));
            push.success('Courier Agent updated successfully!');
        },
        onError: () => {
            push.error('Something went wrong!');
        },
        preserveScroll: true,
        preserveState: true,
    });
}

const logoInput = ref(null);
const logoPreview = ref(null);

const selectNewLogo = () => {
    logoInput.value.click();
};

const updateLogoPreview = () => {
    const logo = logoInput.value.files[0];
    if (!logo) return;

    const reader = new FileReader();
    reader.onload = (e) => {
        logoPreview.value = e.target.result;
    };
    reader.readAsDataURL(logo);
};

const clearLogoFileInput = () => {
    logoInput.value.value = "";
    logoPreview.value = null;
};
</script>

<template>
    <AppLayout title="Edit Courier Agent">
        <template #header>Courier Agent</template>

        <Breadcrumb/>
        <form @submit.prevent="handleAgentUpdate">
            <div class="flex items-center justify-between p-2 my-5">
                <h2 class="text-base font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100">
                    Edit Courier Agent
                </h2>

                <div class="flex justify-end bottom-0 space-x-5">
                    <DangerOutlineButton @click="router.visit(route('courier-agents.index'))">Cancel</DangerOutlineButton>
                    <PrimaryButton :class="{ 'opacity-50': form.processing }" :disabled="form.processing"
                                   class="space-x-2"
                                   type="submit"
                    >
                        <span>Update Courier Agent</span>
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
                            <!-- Mobile Number 1 -->
                            <div class="sm:col-span-2">
                                <div class="grid grid-cols-1 sm:grid-cols-3">
                                    <InputLabel class="col-span-3" for="mobile_number" value="Mobile Number"/>
                                    <div>
                                        <select
                                            v-model="countryCode1"
                                            x-init="$el._tom = new Tom($el)"
                                            class="w-full rounded-r-0"
                                        >
                                            <option v-for="(code, index) in countryCodes" :key="index" :value="code">
                                                {{ code }}
                                            </option>
                                        </select>
                                    </div>
                                    <div class="col-span-2">
                                        <input
                                            v-model="contactNumber1"
                                            class="form-input rounded-l-lg w-full border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:z-10 hover:border-slate-400 focus:z-10 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent rounded-r-lg"
                                            placeholder="123 4567 890"
                                            type="text"
                                        />
                                    </div>
                                    <InputError class="col-span-3" :message="form.errors.contact_number_1"/>
                                </div>
                            </div>
                            <!-- Mobile Number 2 -->
                            <div class="sm:col-span-2">
                                <div class="grid grid-cols-1 sm:grid-cols-3">
                                    <InputLabel class="col-span-3" for="mobile_number" value="Mobile Number"/>
                                    <div>
                                        <select
                                            v-model="additionalNumberCountryCode"
                                            x-init="$el._tom = new Tom($el)"
                                            class="w-full rounded-r-0"
                                        >
                                            <option v-for="(code, index) in countryCodes" :key="index" :value="code">
                                                {{ code }}
                                            </option>
                                        </select>
                                    </div>
                                    <div class="col-span-2">
                                        <input
                                            v-model="additionalNumber"
                                            class="form-input rounded-l-lg w-full border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:z-10 hover:border-slate-400 focus:z-10 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent rounded-r-lg"
                                            placeholder="123 4567 890"
                                            type="text"
                                        />
                                    </div>
                                    <InputError class="col-span-3" :message="form.errors.contact_number_2"/>
                                </div>
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
                <!-- other Part -->
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
                        </div>
                    </div>
                    <!-- Logo Upload -->
                    <div class="card px-4 py-4 sm:px-5">
                        <div class="grid grid-cols-2">
                            <h2
                                class="text-lg font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100"
                            >
                                Company Logo
                            </h2>
                        </div>
                        <div class="sm:col-span-4">
                            <div>
                                <input
                                    id="logo"
                                    ref="logoInput"
                                    class="hidden"
                                    type="file"
                                    @change="updateLogoPreview"
                                >

                                <!-- Current Photo -->
                                <div v-show="!logoPreview" class="mt-2">
                                    <img v-if="courierAgent && courierAgent.logo_url"  :src="courierAgent.logo_url" alt="logo" class="rounded-full h-20 w-20 object-cover">
                                </div>

                                <!-- New Photo Preview -->
                                <div v-show="logoPreview" class="mt-2">
                                    <span
                                        :style="'background-image: url(\'' + logoPreview + '\');'"
                                        class="block rounded-full w-20 h-20 bg-cover bg-no-repeat bg-center"
                                    />
                                </div>

                                <SecondaryButton class="mt-2 me-2" type="button" @click.prevent="selectNewLogo">
                                    Select A New Logo
                                </SecondaryButton>

                                <InputError :message="form.errors.logo" class="mt-2" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </AppLayout>
</template>
