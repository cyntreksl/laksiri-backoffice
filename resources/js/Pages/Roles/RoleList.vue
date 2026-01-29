<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import {router} from "@inertiajs/vue3";
import {ref} from "vue";
import {push} from "notivue";
import {useConfirm} from "primevue/useconfirm";
import Card from "primevue/card";
import Button from "primevue/button";
import Column from "primevue/column";
import DataTable from "primevue/datatable";

const props = defineProps({
    roles: {
        type: Object,
        default: () => {
        },
    },
});

const perPage = ref(10);
const dt = ref();
const confirm = useConfirm();

const formatPermissionName = (name) => {
    // Check if the name contains a dot (first type) or underscore (second type)
    if (name.includes('.')) {
        // The first type: 'group.permission_name'
        let parts = name.split('.');

        // Process each part: capitalize the first letter and replace underscores with spaces
        return parts.map(part =>
            part.split('_')
                .map(word => word.charAt(0).toUpperCase() + word.slice(1))
                .join(' ')
        ).join(' ');
    } else if (name.includes('_')) {
        // Split by underscores, capitalize each word, and join with spaces
        return name.split('_')
            .map(word => word.charAt(0).toUpperCase() + word.slice(1))
            .join(' ');
    }

    // Fallback for other cases
    return name.charAt(0).toUpperCase() + name.slice(1);
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

const confirmRoleDelete = (id) => {
    confirm.require({
        message: 'Would you like to delete this role record?',
        header: 'Delete Role?',
        icon: 'pi pi-info-circle',
        rejectLabel: 'Cancel',
        rejectProps: {
            label: 'Cancel',
            severity: 'secondary',
            outlined: true
        },
        acceptProps: {
            label: 'Delete',
            severity: 'danger'
        },
        accept: () => {
            router.delete(route("users.roles.destroy", id), {
                preserveScroll: true,
                onSuccess: () => {
                    push.success("Role Deleted Successfully!");
                    router.visit(route("users.roles.index"));
                },
                onError: () => {
                    push.error("Something went to wrong!");
                },
            });
        },
        reject: () => {
        }
    });
}
</script>

<template>
    <AppLayout title="Roles">
        <template #header>Roles</template>

        <Breadcrumb/>

        <div>
            <Card class="my-5">
                <template #content>
                    <DataTable
                        ref="dt"
                        :rows="perPage"
                        :rowsPerPageOptions="[5, 10, 20, 50, 100]"
                        :totalRecords="Object.keys(roles).length"
                        :value="roles"
                        data-key="id"
                        paginator
                        removable-sort
                        row-hover
                        tableStyle="min-width: 50rem"
                    >

                        <template #header>
                            <div class="flex flex-col sm:flex-row justify-between items-center mb-2">
                                <div class="text-lg font-medium">
                                    Roles & Permissions
                                </div>

                                <Button label="Create New Role" size="small"
                                        @click.prevent="() => router.visit(route('users.roles.create'))"/>
                            </div>
                        </template>

                        <template #empty>No roles found.</template>

                        <template #loading>Loading roles data. Please wait.</template>

                        <Column field="name" header="Role" sortable>
                            <template #body="slotProps">
                                <div class="flex items-center space-x-2 cursor-pointer hover:opacity-80"
                                     @click="() => router.visit(route('users.roles.show', slotProps.data.id))">
                                    <i :class="[resolveRoleIcon(slotProps.data.name).icon, resolveRoleIcon(slotProps.data.name).color]"
                                       class="text-lg"></i>
                                    <div :class="resolveRoleIcon(slotProps.data.name).color" class="font-medium">
                                        {{ slotProps.data.name.toUpperCase() }}
                                    </div>
                                </div>
                            </template>
                        </Column>

<!--                        <Column field="permissions" header="Permissions">-->
<!--                            <template #body="slotProps">-->
<!--                                <Chip v-for="permission in slotProps.data.permissions" :label="formatPermissionName(permission.name)"  class="mr-1 mb-1 border border-sky-500 !bg-sky-100"/>-->
<!--                            </template>-->
<!--                        </Column>-->

                        <Column field="" header="Actions" style="width: 10%">
                            <template #body="{ data }">
                                <Button
                                    v-if="$page.props.user.permissions.includes('roles.edit')"
                                    class="p-1 text-xs h-3 w-3 mr-1"
                                    icon="pi pi-pencil"
                                    outlined
                                    rounded
                                    size="small"
                                    @click="() => router.visit(route('users.roles.edit', data.id))"
                                />
                                <Button
                                    v-if="$page.props.user.permissions.includes('roles.delete')"
                                    :disabled="data.name === 'admin'"
                                    icon="pi pi-trash"
                                    outlined
                                    rounded
                                    severity="danger"
                                    size="small"
                                    @click.prevent="confirmRoleDelete(data.id)"
                                />
                            </template>
                        </Column>

                        <template #footer> In total there are {{ roles ? Object.keys(roles).length : 0 }} roles.</template>
                    </DataTable>
                </template>
            </Card>
        </div>
    </AppLayout>
</template>
