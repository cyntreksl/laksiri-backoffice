<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import {router, useForm, usePage} from "@inertiajs/vue3";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import {computed, ref, watch} from "vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import {push} from "notivue";
import Card from 'primevue/card';
import InputText from 'primevue/inputtext';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import Select from 'primevue/select';
import Checkbox from 'primevue/checkbox';
import Textarea from 'primevue/textarea';
import MultiSelect from 'primevue/multiselect';
import SelectButton from 'primevue/selectbutton';
import DatePicker from 'primevue/datepicker';
import Button from 'primevue/button';
import moment from "moment";

const props = defineProps({
    packageTypes: {
        type: Object,
        default: () => {
        },
    },
    cargoTypes: {
        type: Object,
        default: () => {
        },
    },
    zones: {
        type: Object,
        default: () => {
        },
    },
    pickupTypes: {
        type: Object,
        default: () => {
        },
    },
    countryCodes: {
        type: Array,
        default: () => [],
    },

});

const findCountryCodeByBranch = () => {
    return usePage().props.currentBranch.country_code;
};

const countryCode = ref(findCountryCodeByBranch());
const contactNumber = ref("");

// Get today's date in yyyy-mm-dd format
const today = new Date();
const formattedToday = today.toISOString().split("T")[0];

const isSameContactNumber = ref(false);

const additionalMobileCountryCode = ref(findCountryCodeByBranch());
const additionalMobileNumber = ref("");

const whatsappNumberCountryCode = ref(findCountryCodeByBranch());
const whatsappNumber = ref("");

const form = useForm({
    name: "",
    email: "",
    contact_number: computed(() => countryCode.value + contactNumber.value),
    additional_mobile_number: "",
    whatsapp_number: "",
    address: "",
    note_type: [],
    notes: "",
    pickup_type: "",
    pickup_note: "",
    cargo_type: "",
    location: "",
    zone_id: null,
    pickup_date: formattedToday,
    pickup_time_start: "",
    pickup_time_end: "",
});

const addContactToWhatsapp = () => {
    if (isSameContactNumber.value) {
        whatsappNumberCountryCode.value = countryCode.value;
        whatsappNumber.value = contactNumber.value;
    } else {
        resetWhatsappNumber();
    }
};

const resetWhatsappNumber = () => {
    whatsappNumberCountryCode.value = findCountryCodeByBranch();
    whatsappNumber.value = "";
};

// Method to find zone ID based on address
const findZoneIdByAddress = (address) => {
    for (const zone of props.zones) {
        for (const area of zone.areas) {
            if (address.includes(area.name)) {
                return zone.id;
            }
        }
    }
    return null;
};

// Watcher to update zone_id based on address
watch(
    () => form.address,
    (newAddress) => {
        form.zone_id = findZoneIdByAddress(newAddress);
    }
);

const handlePickupCreate = () => {
    form.additional_mobile_number = additionalMobileCountryCode.value + additionalMobileNumber.value;

    form.whatsapp_number = whatsappNumberCountryCode.value + whatsappNumber.value;

    if(form.additional_mobile_number === additionalMobileCountryCode.value){
        form.additional_mobile_number = "";
    }

    if(form.whatsapp_number === whatsappNumberCountryCode.value){
        form.whatsapp_number = "";
    }

    form.pickup_date = moment(form.pickup_date).format("YYYY-MM-DD");

    form.pickup_time_start = moment(form.pickup_time_start).format("HH:mm");

    form.pickup_time_end = moment(form.pickup_time_end).format("HH:mm");

    form.post(route("pickups.store"), {
        onSuccess: () => {
            form.reset();
            router.visit(route("pickups.index"));
            push.success("Pickup added successfully!");
        },
        onError: () => {
            push.error("Something went to wrong!");
        },
        preserveScroll: true,
        preserveState: true,
    });
};

watch(
    () => form.note_type,
    (newValue) => {
        form.notes = props.packageTypes.find(type => type.name === newValue)?.description || newValue
    }
);

const isImportant = ref(false);
const isUrgentPickup = ref(false);

watch(isImportant, (newValue) => {
    form.is_from_important_customer = newValue;
});

watch(isUrgentPickup, (newValue) => {
    form.is_urgent_pickup = newValue;
});
</script>

<template>
    <AppLayout title="Pick Up Job - Create">
        <template #header>Pick Up Job - Create</template>

        <!-- Breadcrumb -->
        <Breadcrumb/>

        <!-- Create Pickup Form -->
        <form @submit.prevent="handlePickupCreate">
            <div class="grid grid-cols-1 sm:grid-cols-5 mt-4 gap-4 my-4">
                <div class="sm:col-span-3 space-y-5">
                    <Card>
                        <template #title>Basic Details</template>
                        <template #content>
                            <div class="grid grid-cols-2 gap-5 mt-3">
                                <div class="col-span-2">
                                    <InputLabel value="Name"/>
                                    <IconField>
                                        <InputIcon class="pi pi-user" />
                                        <InputText v-model="form.name" class="w-full" placeholder="Name" />
                                    </IconField>
                                    <InputError :message="form.errors.name"/>
                                </div>

                                <div class="col-span-2">
                                    <InputLabel value="Email"/>
                                    <IconField>
                                        <InputIcon class="pi pi-envelope" />
                                        <InputText v-model="form.email" class="w-full"
                                                   placeholder="Email" type="email" />
                                    </IconField>
                                    <InputError :message="form.errors.email"/>
                                </div>

                                <div class="col-span-2 md:col-span-1">
                                    <InputLabel value="Mobile Number"/>
                                    <div class="flex flex-row">
                                        <Select v-model="countryCode" :options="countryCodes" class="w-25 !rounded-r-none !border-r-0" filter placeholder="Select a Country Code" />
                                        <InputText v-model="contactNumber" class="!rounded-l-none" placeholder="123 4567 890"/>
                                    </div>
                                    <InputError :message="form.errors.contact_number" class="col-span-1"/>
                                </div>

                                <div class="col-span-2 md:col-span-1">
                                    <InputLabel value="Additional Mobile Number"/>
                                    <div class="flex flex-row">
                                        <Select v-model="additionalMobileCountryCode" :options="countryCodes" class="w-25 !rounded-r-none !border-r-0" filter placeholder="Select a Country Code" />
                                        <InputText v-model="additionalMobileNumber" class="!rounded-l-none" placeholder="123 4567 890"/>
                                    </div>
                                    <InputError :message="form.errors.additional_mobile_number" class="col-span-1"/>
                                </div>

                                <div class="col-span-2">
                                    <div class="flex items-center gap-2">
                                        <Checkbox v-model="isSameContactNumber"
                                                  binary inputId="whatsapp" @change="addContactToWhatsapp" />
                                        <label for="whatsapp"> Use mobile number as whatsapp number </label>
                                    </div>
                                </div>

                                <div v-if="!isSameContactNumber" class="grid grid-cols-1 sm:grid-cols-2">
                                    <InputLabel class="col-span-2" value="Whatsapp Number"/>
                                    <div class="flex flex-row">
                                        <Select v-model="whatsappNumberCountryCode" :options="countryCodes" class="w-25 !rounded-r-none !border-r-0" filter placeholder="Select a Country Code" />
                                        <InputText v-model="whatsappNumber" class="!rounded-l-none" placeholder="123 4567 890"/>
                                    </div>
                                    <InputError :message="form.errors.whatsapp_number" class="col-span-2"/>
                                </div>

                                <div class="col-span-2">
                                    <InputLabel value="Address"/>
                                    <Textarea v-model="form.address" class="w-full" cols="30" placeholder="Type address here..." rows="5" />
                                    <InputError :message="form.errors.address"/>
                                </div>
                            </div>
                        </template>
                    </Card>

                    <Card>
                        <template #title>Packages</template>
                        <template #content>
                            <div class="grid grid-cols-2 gap-5 mt-3">
                                <div class="col-span-2">
                                    <label class="block">
                                        <InputLabel value="Package Type"/>
                                        <span v-if="packageTypes.length === 0" class="text-red-500">Please add at least one package type for create job.</span>
                                        <MultiSelect v-model="form.note_type" :options="packageTypes" class="w-full" filter option-label="name" option-value="name"
                                                     placeholder="Select One..." />
                                    </label>
                                    <InputError :message="form.errors.note_type"/>
                                </div>

                                <div class="col-span-2">
                                    <InputLabel value="Packages"/>
                                    <Textarea v-model="form.notes" class="w-full" cols="30" placeholder="Type Packages here..." rows="4" />
                                    <InputError :message="form.errors.notes"/>
                                </div>

                                <div class="col-span-2">
                                    <InputLabel value="Pickup Type"/>
                                    <Select v-model="form.pickup_type" :options="pickupTypes" class="w-full" filter
                                                 placeholder="Select One..." />
                                    <InputError :message="form.errors.pickup_type"/>
                                </div>

                                <div class="col-span-2">
                                    <InputLabel value="Pickup Note"/>
                                    <Textarea v-model="form.pickup_note" class="w-full" cols="30" placeholder="Type Pickup Note here..." rows="4" />
                                    <InputError :message="form.errors.pickup_note"/>
                                </div>
                            </div>
                        </template>
                    </Card>
                </div>
                <div class="sm:col-span-2 space-y-5">
                    <Card>
                        <template #title>Cargo Type</template>
                        <template #content>
                            <SelectButton v-model="form.cargo_type" :options="cargoTypes" name="Cargo Type">
                                <template #option="slotProps">
                                    <div class="flex items-center">
                                        <i v-if="slotProps.option === 'Sea Cargo'" class="ti ti-ship mr-2"></i>
                                        <i v-else class="ti ti-plane mr-2"></i>
                                        <span>{{ slotProps.option }}</span>
                                    </div>
                                </template>
                            </SelectButton>
                            <InputError :message="form.errors.cargo_type"/>
                        </template>
                    </Card>

                    <Card>
                        <template #title>Additional Details</template>
                        <template #content>
                            <div class="grid grid-cols-1 gap-5 mt-3">
                                <div>
                                <InputLabel value="Zone"/>
                                <Select v-model="form.zone_id" :options="zones" class="w-full" option-label="name" option-value="id" placeholder="Select Zone"/>
                                <InputError :message="form.errors.zone_id"/>
                            </div>

                            <div>
                                <InputLabel value="Pickup Date"/>
                                <DatePicker v-model="form.pickup_date" class="w-full mt-1" date-format="yy-mm-dd" inline @update:modelValue="adjustDate"/>
                                <InputError :message="form.errors.pickup_date"/>
                            </div>
                            </div>
                            <div class="my-5 space-y-5">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <InputLabel value="Start Pickup Time"/>
                                        <DatePicker v-model="form.pickup_time_start" class="mt-1" fluid hour-format="24" inline time-only/>
                                        <InputError :message="form.errors.pickup_time_start"/>
                                    </div>

                                    <div>
                                        <InputLabel value="End Pickup Time"/>
                                        <DatePicker v-model="form.pickup_time_end" class="mt-1" fluid hour-format="24" inline time-only/>
                                        <InputError :message="form.errors.pickup_time_end"/>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </Card>

                    <div class="flex justify-end items-center bottom-0 space-x-5">
                        <Button label="Cancel" severity="danger" variant="outlined"  @click="router.visit(route('pickups.index'))" />
                        <Button :class="{ 'opacity-50': form.processing }" :disabled="form.processing || packageTypes.length === 0" icon="pi pi-arrow-right" iconPos="right" label="Create a Job" type="submit" />
                    </div>
                </div>
            </div>
        </form>
    </AppLayout>
</template>
