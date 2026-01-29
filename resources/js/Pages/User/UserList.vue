<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import {onMounted, ref, watch} from "vue";
import CreateUserForm from "@/Pages/User/Partials/CreateUserForm.vue";
import {router, usePage} from "@inertiajs/vue3";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import {push} from "notivue";
import Card from "primevue/card";
import InputText from "primevue/inputtext";
import ContextMenu from "primevue/contextmenu";
import Button from "primevue/button";
import IconField from "primevue/iconfield";
import Column from "primevue/column";
import InputIcon from "primevue/inputicon";
import Tag from "primevue/tag";
import DataTable from "primevue/datatable";
import {useConfirm} from "primevue/useconfirm";
import {FilterMatchMode} from "@primevue/core/api";
import axios from "axios";
import {debounce} from "lodash";

const props = defineProps({
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
    userRole: {
        type: String,
        required: false
    },
    currentBranch: {
        type: Number,
        required: false
    },
    isSuperAdmin: {
        type: Boolean,
        required: false
    },
});

const baseUrl = ref("/user-list");
const loading = ref(true);
const users = ref([]);
const selectedUser = ref(null);
const selectedUserID = ref(null);
const totalRecords = ref(0);
const perPage = ref(10);
const currentPage = ref(1);
const dt = ref();
const cm = ref();
const confirm = useConfirm();
const showUserCreateDialog = ref(false);

const filters = ref({
    global: {value: null, matchMode: FilterMatchMode.CONTAINS},
});

const menuModel = ref([
    {
        label: "Edit",
        icon: "pi pi-fw pi-pencil",
        command: () => router.visit(route("users.edit", selectedUser.value.id)),
        disabled: !usePage().props.user.permissions.includes('users.edit'),
    },
    {
        label: "Delete",
        icon: "pi pi-fw pi-times",
        command: () => confirmUserDelete(selectedUser),
        disabled: !usePage().props.user.permissions.includes('users.delete'),
    },
]);

const fetchUsers = async (page = 1, search = "", sortField = 'created_at', sortOrder = 0) => {
    loading.value = true;
    try {
        const response = await axios.get(baseUrl.value, {
            params: {
                page,
                per_page: perPage.value,
                search,
                sort_field: sortField,
                sort_order: sortOrder === 1 ? "asc" : "desc",
            }
        });
        users.value = response.data.data;
        totalRecords.value = response.data.meta.total;
        currentPage.value = response.data.meta.current_page;
    } catch (error) {
        console.error("Error fetching users:", error);
    } finally {
        loading.value = false;
    }
};

const debouncedFetchUsers = debounce((searchValue) => {
    fetchUsers(1, searchValue);
}, 1000);

watch(() => filters.value.global.value, (newValue) => {
    if (newValue !== null) {
        debouncedFetchUsers(newValue);
    }
});

const onPageChange = (event) => {
    perPage.value = event.rows;
    currentPage.value = event.page + 1;
    fetchUsers(currentPage.value);
};

const onSort = (event) => {
    fetchUsers(currentPage.value, filters.value.global.value, event.sortField, event.sortOrder);
};

const onRowContextMenu = (event) => {
    cm.value.show(event.originalEvent);
};

onMounted(() => {
    fetchUsers();
});

const clearFilter = () => {
    filters.value = {
        global: {value: null, matchMode: FilterMatchMode.CONTAINS},
    };
    fetchUsers(currentPage.value);
};

const exportCSV = () => {
    dt.value.exportCSV();
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

const resolveStatus = (status) =>
    ({
        ACTIVE: "success",
        DEACTIVATE: "danger",
        INACTIVE: "warn",
        INVITED: "info",
    }[status]);

const confirmUserDelete = (user) => {
    selectedUserID.value = user.value.id;
    confirm.require({
        message: 'Would you like to delete this user record?',
        header: 'Delete User?',
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
            router.delete(route("users.destroy", selectedUserID.value), {
                preserveScroll: true,
                onSuccess: () => {
                    push.success("User Deleted Successfully!");
                    fetchUsers(currentPage.value);
                },
                onError: () => {
                    push.error("Something went to wrong!");
                },
            });
            selectedUserID.value = null;
        },
        reject: () => {
        }
    });
}
</script>

<template>
    <AppLayout title="System Users">
        <template #header>System Users</template>

        <Breadcrumb/>

        <div>
            <Card class="my-5">
                <template #content>
                    <ContextMenu ref="cm" :model="menuModel" @hide="selectedUser = null"/>
                    <DataTable
                        ref="dt"
                        v-model:contextMenuSelection="selectedUser"
                        v-model:filters="filters"
                        :loading="loading"
                        :rows="perPage"
                        :rowsPerPageOptions="[5, 10, 20, 50, 100]"
                        :totalRecords="totalRecords"
                        :value="users"
                        context-menu
                        data-key="id"
                        filter-display="menu"
                        lazy
                        paginator
                        removable-sort
                        row-hover
                        tableStyle="min-width: 50rem"
                        @page="onPageChange"
                        @rowContextmenu="onRowContextMenu"
                        @sort="onSort">

                        <template #header>
                            <div class="flex flex-col sm:flex-row justify-between items-center mb-2">
                                <div class="text-lg font-medium">
                                    System Users
                                </div>

                                <Button v-if="$page.props.user.permissions.includes('users.create')" label="Create User"
                                        size="small"
                                        @click="showUserCreateDialog = !showUserCreateDialog"/>
                            </div>
                            <div class="flex flex-col sm:flex-row justify-between gap-4">
                                <!-- Button Group -->
                                <div class="flex flex-col sm:flex-row gap-2">
                                    <Button
                                        icon="pi pi-filter-slash"
                                        label="Clear Filters"
                                        outlined
                                        severity="contrast"
                                        size="small"
                                        type="button"
                                        @click="clearFilter()"
                                    />

                                    <Button
                                        icon="pi pi-external-link"
                                        label="Export"
                                        severity="contrast"
                                        size="small"
                                        @click="exportCSV($event)"
                                    />
                                </div>

                                <!-- Search Field -->
                                <IconField class="w-full sm:w-auto">
                                    <InputIcon>
                                        <i class="pi pi-search"/>
                                    </InputIcon>
                                    <InputText
                                        v-model="filters.global.value"
                                        class="w-full"
                                        placeholder="Keyword Search"
                                        size="small"
                                    />
                                </IconField>
                            </div>
                        </template>

                        <template #empty>No users found.</template>

                        <template #loading>Loading users data. Please wait.</template>

                        <Column field="username" header="Username" sortable></Column>

                        <Column field="role" header="Role" sortable>
                            <template #body="slotProps">
                                <div class="flex items-center space-x-2 cursor-pointer hover:opacity-80"
                                     @click="() => router.visit(route('users.roles.show', slotProps.data.role_id))">
                                    <i :class="[resolveRoleIcon(slotProps.data.role).icon, resolveRoleIcon(slotProps.data.role).color]"
                                       class="text-lg"></i>
                                    <div :class="resolveRoleIcon(slotProps.data.role).color" class="font-medium">
                                        {{ slotProps.data.role }}
                                    </div>
                                </div>
                            </template>
                        </Column>

                        <Column field="primary_branch_name" header="Primary Branch"></Column>

                        <Column field="created_at" header="Created At"></Column>

                        <Column field="status" header="Status">
                            <template #body="slotProps">
                                <div v-if="slotProps.data.status">
                                    <Tag
                                        :severity="resolveStatus(slotProps.data.status)"
                                        :value="slotProps.data.status"
                                        class="mr-1 mb-1"
                                    />
                                </div>
                            </template>
                        </Column>

                        <Column field="last_login_at" header="Last Login"></Column>

                        <template #footer> In total there are {{ users ? totalRecords : 0 }} users.</template>
                    </DataTable>
                </template>
            </Card>
        </div>
    </AppLayout>

    <CreateUserForm :branches="branches" :is-super-admin="isSuperAdmin" :roles="roles" :user-branch="currentBranch"
                    :user-role="userRole" :visible="showUserCreateDialog"
                    @close="showUserCreateDialog = false"
                    @update:visible="showUserCreateDialog = $event"/>
</template>
