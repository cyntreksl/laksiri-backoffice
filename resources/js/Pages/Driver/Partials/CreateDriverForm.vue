<script setup>
import {router, useForm} from "@inertiajs/vue3";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import {push} from "notivue";
import Dialog from "primevue/dialog";
import InputIcon from "primevue/inputicon";
import InputText from "primevue/inputtext";
import IconField from "primevue/iconfield";
import Message from 'primevue/message';
import DatePicker from 'primevue/datepicker';
import MultiSelect from 'primevue/multiselect';
import Button from "primevue/button";
import moment from "moment";

defineProps({
    zones: {
        type: Object,
        default: () => {
        },
    },
    visible: {
        type: Boolean,
        default: false,
    },
})

const emit = defineEmits(["update:visible"]);

const form = useForm({
    name: '',
    username: '',
    password: '',
    password_confirmation: '',
    working_hours_start: '',
    working_hours_end: '',
    preferred_zone: [],
    contact: '',
    role: 'driver',
});

const createDriver = () => {
    form.working_hours_start = moment(form.working_hours_start).format("YYYY-MM-DD");
    form.working_hours_end = moment(form.working_hours_end).format("YYYY-MM-DD");

    form.post(route("users.drivers.store"), {
        onSuccess: () => {
            router.visit(route("users.drivers.index"));
            form.reset();
            emit('close');
            push.success('Driver Created Successfully!');
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
    <Dialog :style="{ width: '60rem' }" :visible="visible" header="Create Driver" modal @update:visible="(newValue) => $emit('update:visible', newValue)">

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">

            <div>
                <InputLabel value="Name"/>
                <IconField>
                    <InputIcon class="pi pi-user" />
                    <InputText v-model="form.name" class="w-full" placeholder="Enter Name" />
                </IconField>
                <InputError :message="form.errors.name"/>
            </div>

            <div>
                <InputLabel value="Username"/>
                <IconField>
                    <InputIcon class="pi pi-user" />
                    <InputText v-model="form.username" class="w-full" placeholder="Enter Username" />
                </IconField>
                <InputError :message="form.errors.username"/>
            </div>

            <div>
                <InputLabel value="Mobile"/>
                <IconField>
                    <InputIcon class="pi pi-phone" />
                    <InputText v-model="form.contact" class="w-full" placeholder="+947XXXXXXXX" />
                </IconField>
                <Message severity="secondary" size="small" variant="simple">This number will appear in SMS of customers.</Message>
                <InputError :message="form.errors.contact"/>
            </div>

            <div>
                <InputLabel value="Password"/>
                <IconField>
                    <InputIcon class="pi pi-lock" />
                    <InputText v-model="form.password" class="w-full" placeholder="Enter Password" type="password"/>
                </IconField>
                <InputError :message="form.errors.password"/>
            </div>

            <div>
                <InputLabel value="Re-enter Password"/>
                <IconField>
                    <InputIcon class="pi pi-lock" />
                    <InputText v-model="form.password_confirmation" class="w-full" placeholder="Enter Confirm Password" type="password"/>
                </IconField>
                <InputError :message="form.errors.password_confirmation"/>
            </div>

            <div>
                <InputLabel value="Working Hours Start"/>
                <DatePicker v-model="form.working_hours_start" fluid iconDisplay="input" showIcon />
                <Message severity="secondary" size="small" variant="simple">For driver location tracking purpose.</Message>
                <InputError :message="form.errors.working_hours_start"/>
            </div>

            <div>
                <InputLabel value="Working Hours End"/>
                <DatePicker v-model="form.working_hours_end" fluid iconDisplay="input" showIcon />
                <Message severity="secondary" size="small" variant="simple">Tracking will be only in working hours.</Message>
                <InputError :message="form.errors.working_hours_end"/>
            </div>

            <div>
                <InputLabel value="Preferred Zone"/>
                <MultiSelect v-model="form.preferred_zone" :maxSelectedLabels="3" :options="zones" class="w-full" filter option-label="name" option-value="name" placeholder="Select Preferred Zones" />
                <Message severity="secondary" size="small" variant="simple">Comma separated values. Auto Zone Assignments, it will come as a notification.</Message>
                <InputError :message="form.errors.preferred_zone"/>
            </div>
        </div>

        <div class="flex justify-end gap-2">
            <Button label="Cancel" severity="secondary" type="button" @click="emit('close')"></Button>
            <Button :class="{ 'opacity-25': form.processing }" :disabled="form.processing" label="Create Driver" type="button"
                    @click="createDriver"></Button>
        </div>
    </Dialog>
</template>
