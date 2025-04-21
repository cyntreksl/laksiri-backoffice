<script setup>
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import {router, useForm, usePage} from "@inertiajs/vue3";
import Card from 'primevue/card';
import {push} from "notivue";
import Button from 'primevue/button';
import InputIcon from "primevue/inputicon";
import InputText from "primevue/inputtext";
import IconField from "primevue/iconfield";
import RadioButton from "primevue/radiobutton";

const props = defineProps({
    user: {
        type: Object,
        default: () => {
        },
    },
    roles: {
        type: Object,
        default: () => {
        },
    },
    branches: {
        type: Object,
        default: () => {
        },
    },
});

const form = useForm({
    name: props.user.name,
    username: props.user.username,
    email: props.user.email,
    role_id: props.user.roles[0]?.id,
});

const handleUpdateUser = () => {
    form.put(route("users.update", props.user.id), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            push.success('Basic Details Updated Successfully!');
            router.visit(route("users.edit", props.user.id));
        },
        onError: () => {
            form.reset();
        },
    });
};

const resolveRoleIcon = (role) => {
    switch (role.toLowerCase()) {
        case 'admin':
            return {
                icon: 'ti ti-user-shield',
                color: 'text-red-500',
            }
        case 'viewer':
            return {
                icon: 'ti ti-eye-search',
                color: 'text-orange-500',
            }
        case 'driver':
            return {
                icon: 'ti ti-steering-wheel',
                color: 'text-lime-500',
            }
        case 'call center':
            return {
                icon: 'ti ti-device-landline-phone',
                color: 'text-sky-500',
            }
        case 'bond warehouse staff':
            return {
                icon: 'ti ti-building-warehouse',
                color: 'text-indigo-500',
            }
        case 'customer':
            return {
                icon: 'ti ti-user-heart',
                color: 'text-pink-500',
            }
        case 'boned area':
            return {
                icon: 'ti ti-building-community',
                color: 'text-rose-500',
            }
        case 'front office staff':
            return {
                icon: 'ti ti-building-estate',
                color: 'text-violet-500',
            }
        case 'empty':
            return {
                icon: 'ti ti-mood-empty',
                color: 'text-cyan-500',
            }
        case 'finance team':
            return {
                icon: 'ti ti-device-desktop-dollar',
                color: 'text-teal-500',
            }
        default:
            return {
                icon: 'ti ti-user-question',
                color: 'text-stone-500',
            }
    }
};
</script>

<template>
    <form
        @submit.prevent="handleUpdateUser"
    >
        <Card>
            <template #title>Basic Details</template>
            <template #content>
                <div class="grid grid-cols-2 gap-5 mt-3">
                    <div>
                        <InputLabel value="Name"/>
                        <IconField>
                            <InputIcon class="pi pi-user"/>
                            <InputText v-model="form.name" class="w-full" placeholder="Enter Name"/>
                        </IconField>
                        <InputError :message="form.errors.name"/>
                    </div>

                    <div>
                        <InputLabel value="Username"/>
                        <IconField>
                            <InputIcon class="pi pi-user"/>
                            <InputText v-model="form.username" class="w-full" placeholder="Enter Username"/>
                        </IconField>
                        <InputError :message="form.errors.username"/>
                    </div>

                    <div class="col-span-1 sm:col-span-2">
                        <InputLabel value="Email"/>
                        <IconField>
                            <InputIcon class="pi pi-envelope"/>
                            <InputText v-model="form.email" class="w-full" placeholder="Enter Email Address"/>
                        </IconField>
                        <InputError :message="form.errors.email"/>
                    </div>

                    <div class="col-span-1 sm:col-span-2">
                        <InputLabel value="Role"/>
                        <div class="flex flex-wrap gap-4 mt-1">
                            <div v-for="(role, index) in roles" :key="index" class="flex items-center gap-4">
                                <RadioButton v-model="form.role_id" :input-id="role.name" :name="role.name"
                                             :value="role.id"/>
                                <label :for="role.name" class="capitalize cursor-pointer">
                                    <i :class="[resolveRoleIcon(role.name).icon, resolveRoleIcon(role.name).color, 'mr-1 text-lg']"></i>
                                    {{ role.name }}
                                </label>
                            </div>
                        </div>
                        <InputError :message="form.errors.role_id"/>
                    </div>
                </div>
            </template>
            <template #footer>
                <div class="inline-block float-right mt-3">
                    <Button :class="{ 'opacity-25': form.processing }" :disabled="form.processing" class="w-full"
                            label="Save User Basic Details" type="submit"/>
                </div>
            </template>
        </Card>
    </form>
</template>
