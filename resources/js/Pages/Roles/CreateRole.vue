<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import { router, useForm } from "@inertiajs/vue3";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import { ref, computed, onMounted } from "vue";
import { push } from "notivue";
import Button from "primevue/button";
import Card from "primevue/card";
import InputText from "primevue/inputtext";
import Checkbox from "primevue/checkbox";
import PermissionGroup from "./PermissionGroup.vue";

const props = defineProps({
    permissionGroups: {
        type: Array,
        default: () => [],
    },
});

const allChecked = ref(false);
const groupPermissions = ref({});
const selectedPermissions = ref({});
const loadedGroups = ref(new Set());

// Computed property to check if all groups are loaded
const allGroupsLoaded = computed(() => {
    return loadedGroups.value.size === props.permissionGroups.length;
});

// Computed property to get all selected permission IDs
const allSelectedPermissionIds = computed(() => {
    const allIds = [];
    Object.values(selectedPermissions.value).forEach(groupPerms => {
        allIds.push(...groupPerms);
    });
    return allIds;
});

// Handle when permissions are loaded from a group component
const handlePermissionsLoaded = ({ groupName, permissions }) => {
    groupPermissions.value[groupName] = permissions;
    loadedGroups.value.add(groupName);
};

// Handle when permissions change in a group component
const handlePermissionChanged = ({ groupName, permissions }) => {
    selectedPermissions.value[groupName] = permissions;

    // Update allChecked state based on whether all permissions are selected
    if (allGroupsLoaded.value) {
        const totalPermissions = Object.values(groupPermissions.value).flat().length;
        const totalSelected = allSelectedPermissionIds.value.length;
        allChecked.value = totalPermissions > 0 && totalSelected === totalPermissions;
    }
};

const initializeSelectedPermissions = () => {
    props.permissionGroups.forEach(group => {
        selectedPermissions.value[group.name] = [];
    });
};

const handleAllPermissionsCheck = () => {
    // The individual PermissionGroup components will handle this via props
};

// Form setup
const form = useForm({
    name: "",
    permissions: [],
});

// Handle role creation
const handleRoleCreate = () => {
    form.permissions = allSelectedPermissionIds.value;

    form.post(route("users.roles.store"), {
        onSuccess: () => {
            router.visit(route("users.roles.index"));
            form.reset();
            push.success('Role Created Successfully!');
        },
        onError: () => {
            push.error('Something went wrong!');
        },
        preserveScroll: true,
        preserveState: true,
    });
};

// Initialize on mount
onMounted(() => {
    initializeSelectedPermissions();
});
</script>

<template>
    <AppLayout title="Create Role">
        <template #header>Create Role</template>

        <Breadcrumb />

        <form class="mt-5" @submit.prevent="handleRoleCreate">
            <div class="sm:col-span-2 space-y-5">
                <!-- Action Buttons -->
                <div class="flex justify-end space-x-5">
                    <Button
                        label="Cancel"
                        severity="danger"
                        variant="outlined"
                        @click="router.visit(route('users.roles.index'))"
                    />

                    <Button
                        :class="{ 'opacity-50': form.processing || !allGroupsLoaded }"
                        :disabled="form.processing || !allGroupsLoaded"
                        icon="pi pi-arrow-right"
                        iconPos="right"
                        label="Create Role"
                        type="submit"
                    />
                </div>
            </div>

            <div class="grid grid-cols-1 my-4 gap-4">
                <div class="sm:col-span-3 space-y-5">
                    <Card>
                        <template #title>Create Role</template>
                        <template #content>
                            <div class="grid grid-cols-1 gap-5 mt-3">
                                <!-- Role Name -->
                                <div>
                                    <InputLabel value="Role Name" />
                                    <InputText
                                        v-model="form.name"
                                        class="w-full"
                                        placeholder="Enter Role Name"
                                        type="text"
                                    />
                                    <InputError :message="form.errors.name" />
                                </div>

                                <!-- All Permissions Checkbox -->
                                <div>
                                    <label class="flex flex-row items-center py-3 cursor-pointer">
                                        <Checkbox
                                            v-model="allChecked"
                                            :disabled="!allGroupsLoaded"
                                            binary
                                            @change="handleAllPermissionsCheck"
                                        />
                                        <span class="ml-2 text-gray-700 font-medium dark:text-dark-typography">
                                            All Permissions
                                            <span v-if="!allGroupsLoaded" class="text-sm text-gray-500 ml-1">
                                                (Loading...)
                                            </span>
                                        </span>
                                    </label>
                                </div>

                                <hr>

                                <!-- Permission Groups -->
                                <div class="space-y-4">
                                    <PermissionGroup
                                        v-for="(permissionGroup, index) in permissionGroups"
                                        :key="index"
                                        :all-checked="allChecked"
                                        :permission-group="permissionGroup"
                                        :role-permissions="[]"
                                        @permissions-loaded="handlePermissionsLoaded"
                                        @permission-changed="handlePermissionChanged"
                                    />
                                </div>

                                <!-- Loading indicator -->
                                <div v-if="!allGroupsLoaded" class="text-center py-4">
                                    <div class="inline-flex items-center space-x-2">
                                        <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-blue-600"></div>
                                        <span class="text-gray-600">Loading permissions...</span>
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
