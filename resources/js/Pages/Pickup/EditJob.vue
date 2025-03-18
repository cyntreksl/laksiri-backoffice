<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import {router, useForm} from "@inertiajs/vue3";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import {ref, watch} from "vue";
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
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import {push} from "notivue";
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
    pickup: {
        type: Object,
        default: () => {
        },
    },
});

const form = useForm({
    name: props.pickup.name,
    email: props.pickup.email,
    contact_number: props.pickup.contact_number,
    additional_mobile_number: props.pickup.additional_mobile_number,
    whatsapp_number: props.pickup.whatsapp_number,
    address: props.pickup.address,
    note_type: JSON.parse(props.pickup.package_types) || [],
    notes: props.pickup.notes,
    pickup_type: props.pickup.pickup_type,
    pickup_note: props.pickup.pickup_note,
    cargo_type: props.pickup.cargo_type,
    location: "",
    zone_id: props.pickup.zone_id,
    pickup_date: new Date(props.pickup.pickup_date),
    pickup_time_start: props.pickup.pickup_time_start,
    pickup_time_end: props.pickup.pickup_time_end,
});

const isSameContactNumber = ref(props.pickup.contact_number === props.pickup.whatsapp_number);

const addContactToWhatsapp = () => {
    if (isSameContactNumber.value) {
        form.whatsapp_number = form.contact_number;
    } else {
        resetWhatsappNumber();
    }
};

const resetWhatsappNumber = () => {
    form.whatsapp_number = props.pickup.whatsapp_number
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

const handlePickupUpdate = () => {
    form.pickup_date = moment(form.pickup_date).format("YYYY-MM-DD");

    form.put(route("pickups.update", props.pickup.id), {
        onSuccess: () => {
            form.reset();
            router.visit(route("pickups.index"));
            push.success("Pickup updated successfully!");
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
</script>

<template>
    <AppLayout title="Pick Up Job - Edit">
        <template #header>Pick Up Job - Edit</template>

        <!-- Breadcrumb -->
        <Breadcrumb/>

        <form @submit.prevent="handlePickupUpdate">
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
                                    <InputText v-model="form.contact_number" placeholder="123 4567 890"/>
                                    <InputError :message="form.errors.contact_number" class="col-span-1"/>
                                </div>

                                <div class="col-span-2 md:col-span-1">
                                    <InputLabel value="Additional Mobile Number"/>
                                    <InputText v-model="form.additional_mobile_number" placeholder="123 4567 890"/>
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
                                    <InputText v-model="form.whatsapp_number" placeholder="123 4567 890"/>
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
                                    <DatePicker v-model="form.pickup_date" class="w-full mt-1" date-format="yy-mm-dd" inline />
                                    <InputError :message="form.errors.pickup_date"/>
                                </div>
                            </div>
                            <div class="my-5 space-y-5">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <InputLabel value="Start Pickup Time"/>
                                        <label class="relative flex">
                                            <input
                                                v-model="form.pickup_time_start"
                                                class="form-input peer w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 pl-9 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                                placeholder="Choose time..."
                                                type="text"
                                                x-init="$el._x_flatpickr = flatpickr($el,{enableTime: true,noCalendar: true,dateFormat: 'H:i',time_24hr:true})"
                                            />
                                            <span class="pointer-events-none absolute flex h-full w-10 items-center justify-center text-slate-400 peer-focus:text-primary dark:text-navy-300 dark:peer-focus:text-accent ti ti-clock text-lg" />
                                        </label>
                                        <InputError :message="form.errors.pickup_time_start"/>
                                    </div>

                                    <div>
                                        <InputLabel value="End Pickup Time"/>
                                        <label class="relative flex">
                                            <input
                                                v-model="form.pickup_time_end"
                                                class="form-input peer w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 pl-9 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                                placeholder="Choose time..."
                                                type="text"
                                                x-init="$el._x_flatpickr = flatpickr($el,{enableTime: true,noCalendar: true,dateFormat: 'H:i',time_24hr:true})"
                                            />
                                            <span class="pointer-events-none absolute flex h-full w-10 items-center justify-center text-slate-400 peer-focus:text-primary dark:text-navy-300 dark:peer-focus:text-accent ti ti-clock text-lg" />
                                        </label>
                                        <InputError :message="form.errors.pickup_time_end"/>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </Card>

                    <div class="flex justify-end items-center bottom-0 space-x-5">
                        <Button label="Cancel" severity="danger" variant="outlined"  @click="router.visit(route('pickups.index'))" />
                        <Button :class="{ 'opacity-50': form.processing }" :disabled="form.processing || packageTypes.length === 0" icon="pi pi-arrow-right" iconPos="right" label="Update Job" type="submit" />
                    </div>
                </div>
            </div>
        </form>
    </AppLayout>
</template>
