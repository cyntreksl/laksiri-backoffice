<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import {router, useForm, usePage} from "@inertiajs/vue3";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import {onMounted, ref, watch} from "vue";
import {push} from "notivue";
import AppPreLoader from "@/Components/AppPreLoader.vue";
import Button from "primevue/button";
import Card from "primevue/card";
import InputText from "primevue/inputtext";
import Checkbox from "primevue/checkbox";

const props = defineProps({
    permissionGroups: {
        type: Array,
        default: () => [],
    },
    allPermissions: {
        type: Object,
        default: () => ({}),
    },
    role: {
        type: Object,
        default: () => ({}),
    },
});

const allChecked = ref(false);
const groupChecked = ref([]);
const permissionChecked = ref({});
const permissions = ref({});
const loading = ref(false);

const fetchPermissions = async () => {
    loading.value = true;
    try {
        for (const group of props.permissionGroups) {
            try {
                const response = await fetch(`/permissions/${group.name}`, {
                    method: "GET",
                    headers: {
                        "Content-Type": "application/json",
                        // "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content")
                        "X-CSRF-TOKEN": usePage().props.csrf,
                    },
                });

                if (!response.ok) {
                    throw new Error('Network response was not ok.');
                } else {
                    permissions.value[group.name] = await response.json();
                }
            } catch (error) {
                console.error(error.message);
            }
        }
    } catch (error) {
        console.error("Error fetching permissions:", error);
    } finally {
        loading.value = false;
    }
};

// Initialize groupChecked and permissionChecked arrays when props change
const initializeChecks = () => {
    groupChecked.value = props.permissionGroups.map(group => {
        const groupPermissions = permissions.value[group.name] || [];
        return groupPermissions.every(permission => props.role.permissions.some(rp => rp.id === permission.id));
    });

    permissionChecked.value = {};
    Object.keys(permissions.value).forEach(groupName => {
        permissions.value[groupName].forEach(permission => {
            permissionChecked.value[permission.id] = props.role.permissions.some(rp => rp.id === permission.id);
        });
    });
};

onMounted(async () => {
    await fetchPermissions();
    initializeChecks();
});

watch([() => props.permissionGroups], async () => {
    await fetchPermissions();
    initializeChecks();
});

const checkAllPermissions = () => {
    const checked = allChecked.value;
    groupChecked.value = props.permissionGroups.map(() => checked);
    Object.keys(permissionChecked.value).forEach(id => {
        permissionChecked.value[id] = checked;
    });
};

const checkGroupPermissions = (index) => {
    const checked = groupChecked.value[index];
    const groupName = props.permissionGroups[index].name;
    permissions.value[groupName].forEach(permission => {
        permissionChecked.value[permission.id] = checked;
    });
};

const checkSinglePermission = (index) => {
    const groupName = props.permissionGroups[index].name;
    const groupPermissions = permissions.value[groupName];
    groupChecked.value[index] = groupPermissions.every(permission => permissionChecked.value[permission.id]);
};

const formatPermissionName = (name) => {
    // Remove the first word before the first dot
    let parts = name.split('.');
    parts.shift(); // Remove the first part

    // Capitalize the first letter of each remaining word and join them with a space
    return parts.map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
};

const form = useForm({
    name: props.role.name,
    permissions: {},
});

const handleRoleUpdate = () => {
    form.permissions = Object.keys(permissionChecked.value)
        .filter(id => permissionChecked.value[id])
        .map(id => id);

    form.put(route("users.roles.update", props.role.id), {
        onSuccess: () => {
            router.visit(route("users.roles.index"));
            form.reset();
            push.success('Role Updated Successfully!');
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
    <AppLayout title="Edit Role">
        <template #header>Edit Role</template>

        <Breadcrumb/>

        <AppPreLoader v-if="loading" />

        <form v-else class="mt-5" @submit.prevent="handleRoleUpdate">
            <div class="sm:col-span-2 space-y-5">
                <!-- Action Buttons -->
                <div class="flex justify-end space-x-5">
                    <Button label="Cancel" severity="danger" variant="outlined" @click="router.visit(route('users.roles.index'))" />

                    <Button :class="{ 'opacity-50': form.processing }" :disabled="form.processing" icon="pi pi-arrow-right" iconPos="right" label="Update Role" type="submit" />
                </div>
            </div>
            <div class="grid grid-cols-1 my-4 gap-4">
                <div class="sm:col-span-3 space-y-5">
                    <Card>
                        <template #title>Edit Role</template>
                        <template #content>
                            <div class="grid grid-cols-1 gap-5 mt-3">
                                <div>
                                    <InputLabel value="Role Name"/>
                                    <InputText v-model="form.name" class="w-full" placeholder="Enter Role Name" type="text" />
                                    <InputError :message="form.errors.name"/>
                                </div>

                                <div>
                                    <label class="flex flex-row items-center py-3 cursor-pointer">
                                        <Checkbox v-model="allChecked" binary @change="checkAllPermissions"/>
                                        <span class="ml-2 text-gray-700 font-medium dark:text-dark-typography">All Permissions</span>
                                    </label>
                                </div>

                                <hr>

                                <div v-for="(permissionGroup, index) in permissionGroups" :key="index"
                                     class="grid grid-cols-1 gap-2 bg-slate-100 px-5 rounded-lg">
                                    <div class="col-span-3 sm:col-span-1">
                                        <label class="flex flex-row items-center py-3 cursor-pointer">
                                            <Checkbox :id="`group-${index}`" v-model="groupChecked[index]" binary @change="checkGroupPermissions(index)"/>
                                            <span class="ml-2 text-gray-700 font-medium dark:text-dark-typography">{{
                                                    permissionGroup.name
                                                }}</span>
                                        </label>
                                    </div>
                                    <div class="col-span-3 sm:col-span-2 py-3">
                                        <div class="grid grid-cols-4 gap-4">
                                            <label v-for="(permission, pIndex) in permissions[permissionGroup.name]"
                                                   :key="pIndex" class="flex flex-row items-center cursor-pointer py-0">
                                                <Checkbox :id="`permission-${permission.id}`" v-model="permissionChecked[permission.id]" binary class="!text-indigo-600 !focus:border-indigo-300 !focus:ring-indigo-200" @change="checkSinglePermission(index)"/>
                                                <span class="ml-2 text-gray-700 dark:text-dark-typography">{{
                                                        formatPermissionName(permission.name)
                                                    }}</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </Card>
                </div>
            </div>
        </form>
    </AppLayout>
</template>
