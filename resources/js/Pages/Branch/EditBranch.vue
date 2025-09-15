<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import {router, useForm, usePage} from "@inertiajs/vue3";
import InputError from "@/Components/InputError.vue";
import {push} from "notivue";
import '@vueup/vue-quill/dist/vue-quill.snow.css';
import {ref} from "vue";
import Select from 'primevue/select';
import Tabs from 'primevue/tabs';
import TabList from 'primevue/tablist';
import Tab from 'primevue/tab';
import TabPanels from 'primevue/tabpanels';
import TabPanel from 'primevue/tabpanel';
import IftaLabel from 'primevue/iftalabel';
import InputText  from 'primevue/inputtext';
import InputNumber   from 'primevue/inputnumber';
import MultiSelect from 'primevue/multiselect';
import Button from "primevue/button";
import Textarea from 'primevue/textarea';
import Checkbox from 'primevue/checkbox';

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
    },
    countryCodes: {
        type: Array,
        default: () => [],
    },
    notificationTypes: {
        type: Array,
        default: () => []
    },
    countryNames: {
        type: Array,
        default: () => []
    },
    timezones: {
        type: Array,
        default: () => [],
    },
})

const form = useForm({
    name: props.branch.name,
    branch_code: props.branch.branch_code,
    timezone: props.branch.timezone,
    type: props.branch.type || null,
    currency_name: props.branch.currency_name || '',
    currency_symbol: props.branch.currency_symbol || '',
    country_code: props.branch.country_code || '',
    country: props.branch.country || '',
    email: props.branch.email || '',
    container_delays: props.branch.container_delays,
    maximum_demurrage_discount: props.branch.maximum_demurrage_discount,
    cargo_modes: JSON.parse(props.branch.cargo_modes) || [],
    delivery_types: JSON.parse(props.branch.delivery_types) || [],
    package_types: JSON.parse(props.branch.package_types) || [],
    is_prepaid: Number(props.branch.is_prepaid),
});

const handleBranchUpdate = () => {
    if(form.maximum_demurrage_discount < 0){
        push.error("Maximum Demurrage Discount cannot be a negative value.");
        return;
    }
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

const photoPreview = ref(null);
const photoInput = ref(null);
const sealPreview = ref(null);
const sealInput = ref(null);
const settingForm = useForm({
    invoice_header_title: props.settings ? props.settings.invoice_header_title : '',
    invoice_header_subtitle: props.settings ? props.settings.invoice_header_subtitle : '',
    invoice_header_address: props.settings ? props.settings.invoice_header_address : '',
    invoice_header_telephone: props.settings ? props.settings.invoice_header_telephone : '',
    invoice_footer_title: props.settings ? props.settings.invoice_footer_title : '',
    invoice_footer_text: props.settings ? props.settings.invoice_footer_text : '',
    logo: props.settings ? props.settings.logo : null,
    seal: props.settings ? props.settings.seal : null,
    notification: JSON.parse(props.settings?.notification || '{}'),
});

const handleSettingUpdate = () => {
    if (photoInput.value) {
        settingForm.logo = photoInput.value.files[0];
    }
    if (sealInput.value) {
        settingForm.seal = sealInput.value.files[0];
    }

    settingForm.post(route("setting.invoice.update"), {
        onSuccess: () => {
            router.visit(route("branches.edit", props.branch.id));
            clearPhotoFileInput();
            clearSealFileInput();
            push.success('Invoice Settings updated successfully!');
        },
        onError: () => {
            push.error('Something went to wrong!');
        },
        preserveScroll: true,
        preserveState: true,
    });
}

const selectNewPhoto = () => {
    photoInput.value.click();
};

const updatePhotoPreview = () => {
    const photo = photoInput.value.files[0];

    if (! photo) return;

    const reader = new FileReader();

    reader.onload = (e) => {
        photoPreview.value = e.target.result;
    };

    reader.readAsDataURL(photo);
};

const clearPhotoFileInput = () => {
    if (photoInput.value?.value) {
        photoInput.value.value = null;
    }
};
const selectNewSeal = () => {
    sealInput.value.click();
};

const updateSealPreview = () => {
    const seal = sealInput.value.files[0];

    if (!seal) return;

    const reader = new FileReader();

    reader.onload = (e) => {
        sealPreview.value = e.target.result;
    };

    reader.readAsDataURL(seal);
};

const clearSealFileInput = () => {
    if (sealInput.value?.value) {
        sealInput.value.value = null;
    }
};

const updateChecked = (notification, isChecked) => {
    settingForm.notification = { ...settingForm.notification, [notification]: isChecked };
};
</script>

<template>
    <AppLayout title="Edit Branch">
        <template #header>Branches</template>

        <Breadcrumb :branch="branch"/>

        <Tabs class="my-5" value="0">
            <TabList>
                <Tab value="0">General</Tab>
                <Tab value="1">Invoice</Tab>
                <Tab value="2">Notification</Tab>
            </TabList>
            <TabPanels>
                <TabPanel value="0">
                    <div class="grid grid-cols-12 gap-5 my-3">
                        <div class="col-span-12">
                            <h3 class="text-lg font-medium">General Configuration</h3>
                        </div>

                        <div class="col-span-12 sm:col-span-4">
                            <IftaLabel>
                                <InputText id="name" v-model="form.name" class="w-full" variant="filled" />
                                <label for="name">Name</label>
                            </IftaLabel>
                            <InputError :message="form.errors.name"/>
                        </div>

                        <div class="col-span-12 sm:col-span-2">
                            <IftaLabel>
                                <InputText id="branch-code" v-model="form.branch_code" class="w-full" variant="filled" />
                                <label for="branch-code">Branch Code</label>
                            </IftaLabel>
                            <InputError :message="form.errors.branch_code"/>
                        </div>

                        <div class="col-span-12 sm:col-span-4">
                            <IftaLabel>
                                <InputText id="currency-name" v-model="form.currency_name" class="w-full" placeholder="Sri Lankan Rupee" variant="filled" />
                                <label for="currency-name">Currency Name</label>
                            </IftaLabel>
                            <InputError :message="form.errors.currency_name"/>
                        </div>

                        <div class="col-span-12 sm:col-span-2">
                            <IftaLabel>
                                <InputText id="currency-symbol" v-model="form.currency_symbol" class="w-full" placeholder="LKR" variant="filled" />
                                <label for="currency-symbol">Currency Symbol</label>
                            </IftaLabel>
                            <InputError :message="form.errors.currency_symbol"/>
                        </div>

                        <div class="col-span-12 sm:col-span-4">
                            <IftaLabel>
                                <Select v-model="form.country" :options="countryNames" checkmark class="w-full" filter inputId="country" variant="filled"/>
                                <label for="country">Country</label>
                            </IftaLabel>
                            <InputError :message="form.errors.country"/>
                        </div>

                        <div class="col-span-12 sm:col-span-2">
                            <IftaLabel>
                                <Select v-model="form.country_code" :options="countryCodes" checkmark class="w-full" filter inputId="country-code" variant="filled"/>
                                <label for="country-code">Country Code</label>
                            </IftaLabel>
                            <InputError :message="form.errors.country_code"/>
                        </div>

                        <div class="col-span-12 sm:col-span-4">
                            <IftaLabel>
                                <Select v-model="form.timezone" :options="timezones" checkmark class="w-full" filter inputId="timezone" variant="filled"/>
                                <label for="timezone">Timezone</label>
                            </IftaLabel>
                            <InputError :message="form.errors.timezone"/>
                        </div>

                        <div class="col-span-12 sm:col-span-2">
                            <IftaLabel>
                                <Select v-model="form.is_prepaid" :options="[{value: 1, label: 'Prepaid'}, {value: 0, label: 'Postpaid'}]" checkmark class="w-full" inputId="payment-type" option-label="label" option-value="value" variant="filled"/>
                                <label for="payment-type">Payment Type</label>
                            </IftaLabel>
                            <InputError :message="form.errors.is_prepaid"/>
                        </div>

                        <div class="col-span-12 sm:col-span-2">
                            <IftaLabel>
                                <Select v-model="form.type" :options="branchTypes" checkmark class="w-full" inputId="type" variant="filled"/>
                                <label for="type">Type</label>
                            </IftaLabel>
                            <InputError :message="form.errors.type"/>
                        </div>

                        <div class="col-span-12 sm:col-span-4">
                            <IftaLabel>
                                <InputText id="email" v-model="form.email" class="w-full" placeholder="Enter Branch Email Address" type="email" variant="filled" />
                                <label for="email">Email</label>
                            </IftaLabel>
                            <InputError :message="form.errors.email"/>
                        </div>

                        <div class="col-span-12 sm:col-span-3">
                            <IftaLabel>
                                <MultiSelect v-model="form.package_types" :options="packageTypes" class="w-full" inputId="package-type" variant="filled" />
                                <label for="package-type">Package Type</label>
                            </IftaLabel>
                            <InputError :message="form.errors.package_types"/>
                        </div>

                        <div class="col-span-12 sm:col-span-3">
                            <IftaLabel>
                                <MultiSelect v-model="form.delivery_types" :options="deliveryTypes" class="w-full" inputId="delivery-type" variant="filled" />
                                <label for="delivery-type">Delivery Type</label>
                            </IftaLabel>
                            <InputError :message="form.errors.delivery_types"/>
                        </div>

                        <div class="col-span-12 sm:col-span-3">
                            <IftaLabel>
                                <MultiSelect v-model="form.cargo_modes" :options="cargoModes" class="w-full" inputId="cargo-mode" variant="filled" />
                                <label for="cargo-mode">Cargo Mode</label>
                            </IftaLabel>
                            <InputError :message="form.errors.cargo_modes"/>
                        </div>

                        <div class="col-span-12">
                            <h3 class="text-lg font-medium">Shipment Configuration</h3>
                        </div>

                        <div class="col-span-12 sm:col-span-4">
                            <IftaLabel>
                                <InputNumber v-model="form.container_delays" fluid inputId="shipment-delay" min="0" placeholder="Enter No of Days" suffix=" days" variant="filled" />
                                <label for="shipment-delay">Shipment Delay Days</label>
                            </IftaLabel>
                            <InputError :message="form.errors.container_delays"/>
                        </div>

                        <div v-if="usePage().props.currentBranch.type === 'Destination'" class="col-span-12 sm:col-span-4">
                            <IftaLabel>
                                <InputNumber v-model="form.maximum_demurrage_discount" fluid inputId="demurrage-discount" min="0" variant="filled" />
                                <label for="demurrage-discount">Maximum Demurrage Discount (%)</label>
                            </IftaLabel>
                            <InputError :message="form.errors.maximum_demurrage_discount"/>
                        </div>

                        <div class="col-span-12 flex justify-end">
                            <Button :disabled="form.processing" label="Update Branch" @click="handleBranchUpdate" />
                        </div>
                    </div>
                </TabPanel>
                <TabPanel value="1">
                    <div class="grid grid-cols-12 gap-5 my-3">
                        <div class="col-span-12 sm:col-span-6">
                            <h2 class="text-lg font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100">
                                Invoice Logo (Max: 600px x 600px)
                            </h2>
                            <div>
                                <input
                                    id="photo"
                                    ref="photoInput"
                                    class="hidden"
                                    type="file"
                                    @change="updatePhotoPreview"
                                >

                                <!-- Current Profile Photo -->
                                <div v-show="!photoPreview" class="mt-2">
                                    <img v-if="settings && settings.logo_url" :src="settings.logo_url" alt="logo" class="rounded-full h-20 w-20 object-cover">
                                </div>

                                <!-- New Profile Photo Preview -->
                                <div v-show="photoPreview" class="mt-2">
                    <span
                        :style="'background-image: url(\'' + photoPreview + '\');'"
                        class="block rounded-full w-20 h-20 bg-cover bg-no-repeat bg-center"
                    />
                                </div>

                                <Button class="mt-2 me-2" size="small" type="button" @click.prevent="selectNewPhoto">
                                    Select A New Logo
                                </Button>

                                <InputError :message="settingForm.errors.logo" class="mt-2" />
                            </div>
                        </div>

                        <div class="col-span-12 sm:col-span-6">
                            <h2 class="text-lg font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100">
                                Manifest Seal (Max: 600px x 600px)
                            </h2>
                            <div>
                                <input
                                    id="seal"
                                    ref="sealInput"
                                    class="hidden"
                                    type="file"
                                    @change="updateSealPreview"
                                >

                                <!-- Current  Photo -->
                                <div v-show="!sealPreview" class="mt-2">
                                    <img v-if="settings && settings.seal_url" :src="settings.seal_url" alt="seal" class="rounded-full h-20 w-20 object-cover">
                                </div>

                                <!-- New Photo Preview -->
                                <div v-show="sealPreview" class="mt-2">
                    <span
                        :style="'background-image: url(\'' + sealPreview + '\');'"
                        class="block rounded-full w-20 h-20 bg-cover bg-no-repeat bg-center"
                    />
                                </div>

                                <Button class="mt-2 me-2" size="small" type="button" @click.prevent="selectNewSeal">
                                    Select A New Seal
                                </Button>

                                <InputError :message="settingForm.errors.seal" class="mt-2" />
                            </div>
                        </div>

                        <div class="col-span-12 sm:col-span-6">
                            <IftaLabel>
                                <InputText id="invoice-header-title" v-model="settingForm.invoice_header_title" class="w-full" variant="filled" />
                                <label for="invoice-header-title">Invoice Header Title</label>
                            </IftaLabel>
                            <InputError :message="settingForm.errors.invoice_header_title"/>
                        </div>

                        <div class="col-span-12 sm:col-span-6">
                            <IftaLabel>
                                <InputText id="invoice-header-subtitle" v-model="settingForm.invoice_header_subtitle" class="w-full" variant="filled" />
                                <label for="invoice-header-subtitle">Invoice Header Subtitle</label>
                            </IftaLabel>
                            <InputError :message="settingForm.errors.invoice_header_subtitle"/>
                        </div>

                        <div class="col-span-12 sm:col-span-6">
                            <IftaLabel>
                                <InputText id="invoice-header-address" v-model="settingForm.invoice_header_address" class="w-full" variant="filled" />
                                <label for="invoice-header-address">Invoice Header Address</label>
                            </IftaLabel>
                            <InputError :message="settingForm.errors.invoice_header_address"/>
                        </div>

                        <div class="col-span-12 sm:col-span-6">
                            <IftaLabel>
                                <InputText id="invoice-header-telephone" v-model="settingForm.invoice_header_telephone" class="w-full" variant="filled" />
                                <label for="invoice-header-telephone">Invoice Header Telephone</label>
                            </IftaLabel>
                            <InputError :message="settingForm.errors.invoice_header_telephone"/>
                        </div>

                        <div class="col-span-12 sm:col-span-6">
                            <IftaLabel>
                                <InputText id="invoice-footer-title" v-model="settingForm.invoice_footer_title" class="w-full" variant="filled" />
                                <label for="invoice-footer-title">Invoice Footer Title</label>
                            </IftaLabel>
                            <InputError :message="settingForm.errors.invoice_footer_title"/>
                        </div>

                        <div class="col-span-12 sm:col-span-6">
                            <IftaLabel>
                                <Textarea id="invoice-footer-title" v-model="settingForm.invoice_footer_text" class="w-full" cols="30" placeholder="Enter Footer Text" rows="5" style="resize: none" variant="filled" />
                                <label for="invoice-footer-title">Invoice Footer Text</label>
                            </IftaLabel>
                            <InputError :message="settingForm.errors.invoice_footer_text"/>
                        </div>

                        <div class="col-span-12 flex justify-end">
                            <Button :disabled="form.processing" label="Update Invoice" @click="handleSettingUpdate" />
                        </div>
                    </div>
                </TabPanel>
                <TabPanel value="2">
                    <div class="flex flex-wrap gap-4 my-5">
                        <div
                            v-for="(notification, index) in notificationTypes"
                            :key="notification"
                            class="flex items-center gap-2"
                        >
                            <Checkbox
                                v-model="settingForm.notification[notification]"
                                :binary="true"
                                :inputId="`notification-${index}`"
                            />
                            <label :for="`notification-${index}`">
                                {{ notification }}
                            </label>
                        </div>
                        <InputError :message="settingForm.errors.notification" class="mt-1" />
                    </div>

                    <div class="flex justify-end">
                        <Button :disabled="form.processing" label="Update Notification" @click="handleSettingUpdate" />
                    </div>
                </TabPanel>
            </TabPanels>
        </Tabs>
    </AppLayout>
</template>
