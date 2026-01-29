<script setup>
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
import Button from "primevue/button";
import {computed} from "vue";

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

// Filter branches based on user's branch type
const filteredBranches = computed(() => {
    const userBranchType = usePage().props?.auth.user.active_branch_type;
    const userRole = usePage().props?.auth.user.role;
    
    // Only Super admin can see all branches
    if (props.isSuperAdmin || userRole === 'super admin') {
        return props.branches;
    }
    
    if (!userBranchType) {
        return props.branches;
    }
    
    return props.branches.filter(branch => {
        // Departure users can only see departure branches (case-insensitive comparison)
        if (userBranchType.toLowerCase() === 'departure') {
            return branch.type.toLowerCase() === 'departure';
        }
        // Destination users can only see destination branches (case-insensitive comparison)
        if (userBranchType.toLowerCase() === 'destination') {
            return branch.type.toLowerCase() === 'destination';
        }
        return false;
    });
});
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
                <Select v-model="form.primary_branch_id" :options="filteredBranches" class="w-full" option-label="name" option-value="id" placeholder="Select a primary branch" />
                <InputError :message="form.errors.primary_branch_id"/>
            </div>

            <div v-if="isSuperAdmin">
                <InputLabel value="Select Secondary Branch"/>
                <MultiSelect v-model="form.secondary_branches" :maxSelectedLabels="3" :options="filteredBranches" class="w-full" display="chip" filter option-label="name" option-value="id" placeholder="Select secondary branches"/>
                <InputError :message="form.errors.secondary_branches"/>
            </div>

            <div class="col-span-1 sm:col-span-2">
                <InputLabel value="Role" />
                <Select
                    v-model="form.role"
                    :options="filteredRoles"
                    class="w-full"
                    option-label="formattedName"
                    option-value="name"
                    placeholder="Select a role"
                >
                </Select>
                <InputError :message="form.errors.role" />
            </div>
        </div>

        <div class="flex justify-end gap-2 mt-5">
            <Button label="Cancel" severity="secondary" type="button" @click="emit('close')"></Button>
            <Button :class="{ 'opacity-25': form.processing }" :disabled="form.processing" label="Create User" type="button"
                    @click="createUser"></Button>
        </div>
    </Dialog>
</template>
