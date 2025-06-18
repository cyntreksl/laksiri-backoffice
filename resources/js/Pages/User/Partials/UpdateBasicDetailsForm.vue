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
import Select from "primevue/select";
import {computed} from "vue";

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

const formatRoleName = (name) => {
    return name
        .replace(/_/g, ' ') // Replace underscores with spaces
        .replace(/-/g, ' ') // Replace hyphens with spaces
        .split(' ') // Split into words
        .map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase()) // Capitalize each word
        .join(' '); // Join back with spaces
};

// Filter roles based on branch type and format names
const filteredRoles = computed(() => {
    return props.roles
        .filter(role => {
            return usePage().props?.auth.user.active_branch_type !== 'destination' ||
                (role.name !== 'call center' && role.name !== 'boned area');
        })
        .map(role => ({
            ...role,
            formattedName: formatRoleName(role.name)
        }));
});
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

                    <div>
                        <InputLabel value="Email"/>
                        <IconField>
                            <InputIcon class="pi pi-envelope"/>
                            <InputText v-model="form.email" class="w-full" placeholder="Enter Email Address"/>
                        </IconField>
                        <InputError :message="form.errors.email"/>
                    </div>

                    <div>
                        <InputLabel value="Role"/>
                        <Select
                            v-model="form.role_id"
                            :options="filteredRoles"
                            class="w-full"
                            option-label="formattedName"
                            option-value="id"
                            placeholder="Select a role"
                        >
                        </Select>
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
