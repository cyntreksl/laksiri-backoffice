<script setup>
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import {router, useForm, usePage} from "@inertiajs/vue3";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import {push} from "notivue";
import Dialog from "primevue/dialog";
import InputIcon from "primevue/inputicon";
import InputText from "primevue/inputtext";
import IconField from "primevue/iconfield";
import Select from 'primevue/select';
import MultiSelect from 'primevue/multiselect';
import RadioButton from 'primevue/radiobutton';
import Button from "primevue/button";

const props = defineProps({
    visible: {
        type: Boolean,
        default: false,
    },
    roles: {
        type: Object,
        default: () => {
        }
    },
    branches: {
        type: Object,
        default: () => {
        }
    },
    userRole: {
        type: String,
        required: false
    },
    userBranch: {
        type: Number,
        required: false
    },
    isSuperAdmin: {
        type: Boolean,
        required: false
    },
})

const emit = defineEmits(["update:visible"]);

const form = useForm({
    name: '',
    username: '',
    email: '',
    password: '',
    password_confirmation: '',
    primary_branch_id: props.userRole === 'admin' ? '' : props.userBranch,
    secondary_branches: props.userRole === 'admin' ? [] : [props.userBranch],
    role: '',
});

const createUser = () => {
    form.post(route("users.store"), {
        onSuccess: () => {
            router.visit(route("users.index"));
            form.reset();
            emit('close');
            push.success('User Created Successfully!');
        },
        onError: () => {
            push.error('Something went to wrong!');
        },
        preserveScroll: true,
        preserveState: true,
    });
}

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
    <Dialog :style="{ width: '60rem' }" :visible="visible" header="Create New User" modal @update:visible="(newValue) => $emit('update:visible', newValue)">
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

            <div class="col-span-1 sm:col-span-2">
                <InputLabel value="Email"/>
                <IconField>
                    <InputIcon class="pi pi-envelope" />
                    <InputText v-model="form.email" class="w-full" placeholder="Enter Email Address" />
                </IconField>
                <InputError :message="form.errors.email"/>
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

            <div v-if="userRole === 'admin'">
                <InputLabel value="Select Primary Branch"/>
                <Select v-model="form.primary_branch_id" :options="branches" class="w-full" option-label="name" option-value="id" placeholder="Select a primary branch" />
                <InputError :message="form.errors.primary_branch_id"/>
            </div>

            <div v-if="isSuperAdmin">
                <InputLabel value="Select Secondary Branch"/>
                <MultiSelect v-model="form.secondary_branches" :maxSelectedLabels="3" :options="branches" class="w-full" display="chip" filter option-label="name" option-value="id" placeholder="Select secondary branches"/>
                <InputError :message="form.errors.secondary_branches"/>
            </div>

            <div class="col-span-1 sm:col-span-2">
                <InputLabel value="Role" />
                <div class="flex flex-wrap gap-4 mt-1">
                    <div v-for="(role, index) in roles" :key="index" class="flex items-center gap-4">
                        <template v-if="usePage().props?.auth.user.active_branch_type !== 'destination' || (role.name !== 'call center' && role.name !== 'boned area')">
                            <RadioButton v-model="form.role" :input-id="role.name" :name="role.name" :value="role.name" />
                            <label :for="role.name" class="capitalize cursor-pointer">
                                <i :class="[resolveRoleIcon(role.name).icon, resolveRoleIcon(role.name).color, 'mr-1 text-lg']"></i>
                                {{role.name}}
                            </label>
                        </template>
                    </div>
                </div>
                <InputError :message="form.errors.role" />
            </div>
        </div>

        <div class="flex justify-end gap-2">
            <Button label="Cancel" severity="secondary" type="button" @click="emit('close')"></Button>
            <Button :class="{ 'opacity-25': form.processing }" :disabled="form.processing" label="Create User" type="button"
                    @click="createUser"></Button>
        </div>
    </Dialog>
</template>
