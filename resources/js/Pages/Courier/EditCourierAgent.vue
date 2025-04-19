<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import {router, useForm} from "@inertiajs/vue3";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import {push} from "notivue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import {ref} from "vue";
import Card from "primevue/card";
import InputText from "primevue/inputtext";
import Select from "primevue/select";
import IftaLabel from "primevue/iftalabel";
import Button from "primevue/button";

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
</script>

<template>
    <AppLayout title="Edit Courier Agent">
        <template #header>Courier Agent</template>

        <Breadcrumb/>
        <form @submit.prevent="handleAgentUpdate">
            <div class="flex items-center justify-end p-2 my-5">
                <div class="flex justify-end bottom-0 space-x-2">
                    <Button label="Cancel" severity="danger" variant="outlined"  @click="router.visit(route('couriers.courier-agents.index'))" />

                    <Button :class="{ 'opacity-50': form.processing }" :disabled="form.processing" icon="pi pi-arrow-right" iconPos="right" label="Update Courier Agent" type="submit" />
                </div>
            </div>

            <div class="grid grid-cols-1 mt-4 gap-4">
                <Card>
                    <template #title>
                        Edit Courier Agent
                    </template>
                    <template #content>
                        <div class="grid sm:grid-cols-4 gap-5 mt-3">
                            <div class="sm:col-span-4">
                                <InputLabel value="Company Logo"/>
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

                            <div class="sm:col-span-2">
                                <InputLabel value="Company Name"/>
                                <InputText v-model="form.company_name" class="w-full" inputId="company_name" placeholder="Enter Company Name"/>
                                <InputError :message="form.errors.company_name"/>
                            </div>

                            <div class="sm:col-span-2">
                                <InputLabel value="Website"/>
                                <InputText v-model="form.website" class="w-full" inputId="website" placeholder="Enter Website URL"/>
                                <InputError :message="form.errors.website"/>
                            </div>

                            <div class="sm:col-span-2">
                                <InputLabel value="Mobile Number"/>
                                <div class="flex flex-row">
                                    <Select v-model="countryCode1" :options="countryCodes" class="w-25 !rounded-r-none !border-r-0" filter placeholder="Select a Country Code" />
                                    <InputText v-model="contactNumber1" class="!rounded-l-none w-full" placeholder="123 4567 890"/>
                                </div>
                                <InputError :message="form.errors.contact_number_1" class="col-span-1"/>
                            </div>

                            <div class="sm:col-span-2">
                                <InputLabel value="Additional Mobile Number"/>
                                <div class="flex flex-row">
                                    <Select v-model="additionalNumberCountryCode" :options="countryCodes" class="w-25 !rounded-r-none !border-r-0" filter placeholder="Select a Country Code" />
                                    <InputText v-model="additionalNumber" class="!rounded-l-none w-full" placeholder="123 4567 890"/>
                                </div>
                                <InputError :message="form.errors.contact_number_2" class="col-span-1"/>
                            </div>

                            <div class="sm:col-span-2">
                                <InputLabel value="Email"/>
                                <InputText id="email" v-model="form.email" class="w-full" placeholder="Enter Email Address"/>
                                <InputError :message="form.errors.email"/>
                            </div>

                            <div class="sm:col-span-2">
                                <InputLabel value="Address"/>
                                <InputText id="address" v-model="form.address" class="w-full" placeholder="EnterAddress"/>
                                <InputError :message="form.errors.address"/>
                            </div>

                            <div class="sm:col-span-2">
                                <IftaLabel>
                                    <InputText v-model="form.invoice_header" class="w-full" inputId="invoice_header" placeholder="Enter Invoice Header"/>
                                    <label for="invoice_header">Invoice Header</label>
                                </IftaLabel>
                                <InputError :message="form.errors.invoice_header"/>
                            </div>

                            <div class="sm:col-span-2">
                                <IftaLabel>
                                    <InputText v-model="form.invoice_footer" class="w-full" inputId="invoice_footer" placeholder="Enter Invoice Footer" />
                                    <label for="invoice_footer">Invoice Footer</label>
                                </IftaLabel>
                                <InputError :message="form.errors.invoice_footer"/>
                            </div>
                        </div>
                    </template>
                </Card>
            </div>
        </form>
    </AppLayout>
</template>
