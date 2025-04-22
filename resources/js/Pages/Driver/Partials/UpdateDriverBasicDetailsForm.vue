<script setup>
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import { router, useForm } from "@inertiajs/vue3";
import { push } from "notivue";
import InputIcon from "primevue/inputicon";
import Card from "primevue/card";
import Button from "primevue/button";
import InputText from "primevue/inputtext";
import IconField from "primevue/iconfield";
import Message from "primevue/message";
import DatePicker from "primevue/datepicker";
import MultiSelect from "primevue/multiselect";
import moment from "moment";

const props = defineProps({
  user: {
    type: Object,
    default: () => {},
  },
  zones: {
    type: Object,
    default: () => {},
  },
});

const form = useForm({
  name: props.user.name,
  contact: props.user.contact,
  working_hours_start: props.user.working_hours_start,
  working_hours_end: props.user.working_hours_end,
  preferred_zone: props.user.preferred_zone
    ? props.user.preferred_zone.split(",")
    : "",
});

const handleUpdateDriver = () => {
    form.working_hours_start = moment(form.working_hours_start).format("YYYY-MM-DD");
    form.working_hours_end = moment(form.working_hours_end).format("YYYY-MM-DD");

  form.put(route("users.driver.update", props.user.id), {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      push.success("Driver Details Updated Successfully!");
      router.visit(route("users.drivers.index"));
    },
    onError: () => {
      form.reset();
    },
  });
};
</script>


<template>
    <form
        @submit.prevent="handleUpdateDriver"
    >
        <Card>
            <template #title>Driver Details</template>
            <template #content>
                <div class="grid grid-cols-3 gap-5 mt-3">
                    <div>
                        <InputLabel value="Name"/>
                        <IconField>
                            <InputIcon class="pi pi-user"/>
                            <InputText v-model="form.name" class="w-full" placeholder="Enter Name"/>
                        </IconField>
                        <InputError :message="form.errors.name"/>
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
            </template>
            <template #footer>
                <div class="inline-block float-right mt-3">
                    <Button :class="{ 'opacity-25': form.processing }" :disabled="form.processing" class="w-full"
                            label="Update Driver Details" type="submit"/>
                </div>
            </template>
        </Card>
    </form>
</template>
