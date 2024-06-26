<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import {Link, router} from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import {ref} from "vue";
import {push} from "notivue";

const props = defineProps({
    roles: {
        type: Object,
        default: () => {
        },
    },
});

const showConfirmDeleteRoleModal = ref(false);
const roleId = ref(null);

const confirmDeleteRole = (id) => {
    roleId.value = id;
    showConfirmDeleteRoleModal.value = true;
};

const closeModal = () => {
    showConfirmDeleteRoleModal.value = false;
    roleId.value = null;
};

const handleDeleteRole = () => {
    router.delete(route("hbls.destroy", roleId.value), {
        preserveScroll: true,
        onSuccess: () => {
            closeModal();
            push.success("Role Deleted Successfully!");
            roleId.value = null;
            router.visit(route("users.roles.index"));
        },
        onError: () => {
            closeModal();
            push.error("Something went to wrong!");
        },
    });
};

const formatPermissionName = (name) => {
    return name.split('.').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
};
</script>

<template>
    <AppLayout title="Roles">
        <template #header>Roles</template>

        <Breadcrumb/>

        <div class="flex justify-end mt-5">
            <Link :href="route('users.roles.create')">
                <PrimaryButton> Create New Role</PrimaryButton>
            </Link>
        </div>

        <div class="card mt-4">
            <div class="flex items-center justify-between p-2">
                <h2
                    class="text-base font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100"
                >
                    Roles & Permissions
                </h2>
            </div>

            <div class="mt-3">
                <div class="is-scrollbar-hidden min-w-full overflow-x-auto">
                    <div
                        class="is-scrollbar-hidden min-w-full overflow-x-auto"
                    >
                        <table class="is-zebra w-full text-left">
                            <thead>
                            <tr>
                                <th
                                    class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                                >
                                    Role Name
                                </th>
                                <th
                                    class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                                >
                                    Permissions
                                </th>
                                <th
                                    class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                                >
                                    Actions
                                </th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="(item, index) in roles">
                                <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                    {{ item.name }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 sm:px-5 space-x-2">
                                    <div
                                        v-for="permission in item.permissions"
                                        class="badge bg-primary/10 text-primary dark:bg-accent-light/15 dark:text-accent-light"
                                    >
                                        {{formatPermissionName(permission.name)}}
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 sm:px-5 space-x-2">
                                    <button
                                        class="btn size-9 p-0 font-medium text-error hover:bg-error/20 focus:bg-error/20 active:bg-error/25"
                                        @click.prevent="confirmRemovePackage(index)"
                                    >
                                        <i class="fa-solid fa-trash"></i>
                                    </button>

                                    <button
                                        class="btn size-9 p-0 font-medium text-success hover:bg-success/20 focus:bg-success/20 active:bg-success/25"
                                        @click.prevent="openEditModal(index)"
                                    >
                                        <i class="fa-solid fa-edit"></i>
                                    </button>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
