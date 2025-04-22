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

const props = defineProps({
  user: {
    type: Object,
    default: () => {},
  },
});

const form = useForm({
  username: props.user.username,
  password: "",
  password_confirmation: "",
});

const handleUpdateDriverPassword = () => {
  form.put(route("users.driver.password.update", props.user.id), {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      push.success("Driver Password Updated Successfully");
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
        @submit.prevent="handleUpdateDriverPassword"
    >
        <Card>
            <template #title>Driver Credentials</template>
            <template #content>
                <div class="grid grid-cols-3 gap-5 mt-3">
                    <div>
                        <InputLabel value="Username"/>
                        <IconField>
                            <InputIcon class="pi pi-user" />
                            <InputText v-model="form.username" class="w-full" placeholder="Enter Username" />
                        </IconField>
                        <InputError :message="form.errors.username"/>
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
                </div>
            </template>
            <template #footer>
                <div class="inline-block float-right mt-3">
                    <Button :class="{ 'opacity-25': form.processing }" :disabled="form.processing" class="w-full"
                            label="Change Driver Credentials" type="submit"/>
                </div>
            </template>
        </Card>
    </form>
</template>
