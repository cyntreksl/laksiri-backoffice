<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import {router} from "@inertiajs/vue3";
import Card from "primevue/card";
import Button from "primevue/button";
import Column from "primevue/column";
import DataTable from "primevue/datatable";
import Chip from "primevue/chip";
import Tag from "primevue/tag";
import {ref, computed} from "vue";
import dayjs from "dayjs";
import relativeTime from "dayjs/plugin/relativeTime";
import InputText from "primevue/inputtext";
import IconField from "primevue/iconfield";
import InputIcon from "primevue/inputicon";
import {FilterMatchMode} from "@primevue/core/api";

dayjs.extend(relativeTime);

const props = defineProps({
    role: {
        type: Object,
        required: true,
    },
});

const perPage = ref(10);

const filters = ref({
    global: {value: null, matchMode: FilterMatchMode.CONTAINS},
});

const permissionSearch = ref('');

const formatDate = (date) => {
    if (!date) return 'N/A';
    return dayjs(date).format('MMM DD, YYYY h:mm A');
};

const formatRelativeTime = (date) => {
    if (!date) return 'N/A';
    return dayjs(date).fromNow();
};

const formatPermissionName = (name) => {
    if (name.includes('.')) {
        let parts = name.split('.');
        return parts.map(part =>
            part.split('_')
                .map(word => word.charAt(0).toUpperCase() + word.slice(1))
                .join(' ')
        ).join(' ');
    } else if (name.includes('_')) {
        return name.split('_')
            .map(word => word.charAt(0).toUpperCase() + word.slice(1))
            .join(' ');
    }
    return name.charAt(0).toUpperCase() + name.slice(1);
};

// Group permissions by group_name
const groupedPermissions = computed(() => {
    if (!props.role.permissions || props.role.permissions.length === 0) {
        return {};
    }
    
    const groups = {};
    props.role.permissions.forEach(permission => {
        const groupName = permission.group_name || 'Other';
        if (!groups[groupName]) {
            groups[groupName] = [];
        }
        groups[groupName].push(permission);
    });
    
    return groups;
});

// Filtered permissions based on search
const filteredGroupedPermissions = computed(() => {
    if (!permissionSearch.value) {
        return groupedPermissions.value;
    }
    
    const searchLower = permissionSearch.value.toLowerCase();
    const filtered = {};
    
    Object.entries(groupedPermissions.value).forEach(([groupName, permissions]) => {
        // Check if group name matches
        const groupMatches = groupName.toLowerCase().includes(searchLower);
        
        // Filter permissions that match
        const matchingPermissions = permissions.filter(permission => {
            const permissionName = formatPermissionName(permission.name).toLowerCase();
            return permissionName.includes(searchLower) || permission.name.toLowerCase().includes(searchLower);
        });
        
        // Include group if group name matches or has matching permissions
        if (groupMatches || matchingPermissions.length > 0) {
            filtered[groupName] = groupMatches ? permissions : matchingPermissions;
        }
    });
    
    return filtered;
});

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
        case 'clearance team':
            return {
                icon: 'ti ti-backpack-off',
                color: 'text-rose-500',
            }
        default:
            return {
                icon: 'ti ti-user-question',
                color: 'text-stone-500',
            }
    }
};

const resolveStatus = (status) =>
    ({
        ACTIVE: "success",
        DEACTIVATE: "danger",
        INACTIVE: "warn",
        INVITED: "info",
    }[status]);
</script>

<template>
    <AppLayout :title="`Role: ${role.name}`">
        <template #header>Role Details</template>

        <Breadcrumb/>

        <div class="space-y-5 my-5">
            <!-- Role Information Card -->
            <Card>
                <template #content>
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4">
                        <div class="flex items-center space-x-3">
                            <i :class="[resolveRoleIcon(role.name).icon, resolveRoleIcon(role.name).color]"
                               class="text-3xl"></i>
                            <div>
                                <h2 :class="resolveRoleIcon(role.name).color" class="text-2xl font-bold">
                                    {{ role.name.toUpperCase() }}
                                </h2>
                                <p class="text-sm text-gray-500">Role ID: {{ role.id }}</p>
                            </div>
                        </div>
                        <div class="flex gap-2 mt-4 sm:mt-0">
                            <Button
                                v-if="$page.props.user.permissions.includes('roles.edit')"
                                icon="pi pi-pencil"
                                label="Edit Role"
                                outlined
                                size="small"
                                @click="() => router.visit(route('users.roles.edit', role.id))"
                            />
                            <Button
                                icon="pi pi-arrow-left"
                                label="Back to Roles"
                                outlined
                                severity="secondary"
                                size="small"
                                @click="() => router.visit(route('users.roles.index'))"
                            />
                        </div>
                    </div>
                </template>
            </Card>

            <!-- Permissions Card -->
            <Card>
                <template #title>
                    <div class="flex items-center space-x-2">
                        <i class="pi pi-shield text-xl"></i>
                        <span>Assigned Permissions</span>
                    </div>
                </template>
                <template #content>
                    <div v-if="role.permissions && role.permissions.length > 0">
                        <!-- Search Input -->
                        <div class="mb-4">
                            <IconField class="w-full sm:w-96">
                                <InputIcon>
                                    <i class="pi pi-search"/>
                                </InputIcon>
                                <InputText
                                    v-model="permissionSearch"
                                    class="w-full"
                                    placeholder="Search permissions or groups..."
                                    size="small"
                                />
                            </IconField>
                        </div>

                        <!-- Grouped Permissions -->
                        <div v-if="Object.keys(filteredGroupedPermissions).length > 0" class="space-y-4">
                            <div
                                v-for="(permissions, groupName) in filteredGroupedPermissions"
                                :key="groupName"
                                class="bg-slate-100 px-5 py-3 rounded-lg"
                            >
                                <div class="font-semibold text-gray-700 mb-3">
                                    {{ groupName }}
                                    <span class="text-sm font-normal text-gray-500 ml-2">
                                        ({{ permissions.length }})
                                    </span>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2">
                                    <Chip
                                        v-for="permission in permissions"
                                        :key="permission.id"
                                        :label="formatPermissionName(permission.name)"
                                        class="border border-sky-500 !bg-sky-100 text-xs"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- No Results -->
                        <div v-else class="text-gray-500 text-center py-8">
                            <i class="pi pi-search text-4xl mb-2 text-gray-400"></i>
                            <p>No permissions found matching "{{ permissionSearch }}"</p>
                        </div>
                    </div>
                    <div v-else class="text-gray-500 text-center py-4">
                        No permissions assigned to this role.
                    </div>
                </template>
            </Card>

            <!-- Users with this Role Card -->
            <Card>
                <template #title>
                    <div class="flex items-center space-x-2">
                        <i class="pi pi-users text-xl"></i>
                        <span>Users with this Role</span>
                    </div>
                </template>
                <template #content>
                    <DataTable
                        v-model:filters="filters"
                        :globalFilterFields="['username', 'email', 'status']"
                        :rows="perPage"
                        :rowsPerPageOptions="[5, 10, 20, 50]"
                        :totalRecords="role.users ? role.users.length : 0"
                        :value="role.users"
                        data-key="id"
                        filter-display="menu"
                        paginator
                        removable-sort
                        row-hover
                        tableStyle="min-width: 50rem"
                    >
                        <template #header>
                            <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                                <div class="text-sm text-gray-600">
                                    Showing {{ role.users ? role.users.length : 0 }} user(s)
                                </div>
                                <IconField class="w-full sm:w-auto">
                                    <InputIcon>
                                        <i class="pi pi-search"/>
                                    </InputIcon>
                                    <InputText
                                        v-model="filters.global.value"
                                        class="w-full"
                                        placeholder="Search users..."
                                        size="small"
                                    />
                                </IconField>
                            </div>
                        </template>

                        <template #empty>No users assigned to this role.</template>

                        <Column field="username" header="Username" sortable>
                            <template #body="slotProps">
                                <Button
                                    :label="slotProps.data.username"
                                    class="p-0"
                                    link
                                    @click="() => router.visit(route('users.edit', slotProps.data.id))"
                                />
                            </template>
                        </Column>

                        <Column field="email" header="Email" sortable></Column>

                        <Column field="branches" header="Branches">
                            <template #body="slotProps">
                                <div v-if="slotProps.data.branches && slotProps.data.branches.length > 0"
                                     class="flex flex-wrap gap-1">
                                    <Chip
                                        v-for="branch in slotProps.data.branches"
                                        :key="branch.id"
                                        :class="branch.id === slotProps.data.primary_branch_id ? 'border border-blue-600 !bg-blue-100 !text-blue-800 font-semibold' : 'border border-gray-300 !bg-gray-100'"
                                        :label="branch.id === slotProps.data.primary_branch_id ? `${branch.name} (Primary)` : branch.name"
                                        class="text-xs"
                                    />
                                </div>
                                <span v-else class="text-gray-400">No branches</span>
                            </template>
                        </Column>

                        <Column field="status" header="Status" sortable>
                            <template #body="slotProps">
                                <Tag
                                    v-if="slotProps.data.status"
                                    :severity="resolveStatus(slotProps.data.status)"
                                    :value="slotProps.data.status"
                                />
                            </template>
                        </Column>

                        <Column field="created_at" header="Created At" sortable>
                            <template #body="slotProps">
                                <div class="flex flex-col">
                                    <span class="font-medium">{{ formatDate(slotProps.data.created_at) }}</span>
                                    <span class="text-xs text-gray-500">{{ formatRelativeTime(slotProps.data.created_at) }}</span>
                                </div>
                            </template>
                        </Column>

                        <template #footer>
                            Total: {{ role.users ? role.users.length : 0 }} user(s) with this role
                        </template>
                    </DataTable>
                </template>
            </Card>
        </div>
    </AppLayout>
</template>
