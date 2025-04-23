<script setup>
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import {useForm} from "@inertiajs/vue3";
import {push} from "notivue";
import InputIcon from "primevue/inputicon";
import Card from "primevue/card";
import Button from "primevue/button";
import InputText from "primevue/inputtext";
import IconField from "primevue/iconfield";

const props = defineProps({
    user: {
        type: Object,
        default: () => {
        }
    },
})

const form = useForm({
    password: '',
    password_confirmation: '',
});

const handleUpdatePassword = () => {
    form.put(route("users.password.change", props.user.id), {
        onSuccess: () => {
            form.reset();
            push.success('Password Updated Successfully!');
        },
        preserveScroll: true,
        preserveState: true,
    });
}
</script>

<template>
    <form
        @submit.prevent="handleUpdatePassword"
    >
        <Card>
            <template #title>Change Password</template>
            <template #content>
                <div class="grid grid-cols-2 gap-5 mt-3">
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
                            label="Change Password" type="submit"/>
                </div>
            </template>
        </Card>
    </form>
</template>
