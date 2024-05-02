<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import {onMounted, reactive, ref} from "vue";
import {Grid, h} from "gridjs";
import Popper from "vue3-popper";
import CreateUserForm from "@/Pages/User/Partials/CreateUserForm.vue";
import {router} from "@inertiajs/vue3";

const props = defineProps({
    users: {
        type: Object,
        default: () => {
        }
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
})
const wrapperRef = ref(null);
let grid = null;

const data = reactive({
    UserData: props.users,
    columnVisibility: {
        id: false,
        username: true,
        primary_branch_id: true,
        created_at: true,
        status: true,
        last_login_at: true,
        last_logout_at: true,
        branches: true,
        actions: true,
    }
});

const initializeGrid = () => {
    grid = new Grid({
        search: true,
        pagination: {
            limit: 20
        },
        sort: true,
        columns: createColumns(),
        data: () => {
            return new Promise(resolve => {
                setTimeout(() =>
                    resolve(createData()), 2000);
            });
        },
    });

    grid.render(wrapperRef.value);
};

const createColumns = () => [
    {name: 'ID', hidden: !data.columnVisibility.id},
    {name: 'Username', hidden: !data.columnVisibility.username},
    {name: 'Primary Branch', hidden: !data.columnVisibility.primary_branch_id},
    {name: 'Created At', hidden: !data.columnVisibility.created_at},
    {name: 'Status', hidden: !data.columnVisibility.status},
    {name: 'Last Login', hidden: !data.columnVisibility.last_login_at},
    {name: 'Last Logout', hidden: !data.columnVisibility.last_logout_at},
    {name: 'Secondary Branches', hidden: !data.columnVisibility.branches},
    {
        name: 'Actions',
        hidden: !data.columnVisibility.actions,
        formatter: (cell, row) => {
            return h('div', (
                h('button', {
                    onClick: () => alert(`Editing "${row.cells[0].data}" "${row.cells[1].data}"`)
                }, 'Edit'),
                    h('button', {
                        onClick: () => alert(`Deleting "${row.cells[0].data}" "${row.cells[1].data}"`)
                    }, 'Delete')
            ))
        },
    },
];

const createData = () =>
    data.UserData.map(user => [
        user.id,
        user.username,
        user.primary_branch.name,
        user.created_at,
        user.status,
        user.last_login_at,
        user.last_logout_at,
        user.branches.map(branch => branch.name + " "),
    ]);

const updateGridConfig = () => {
    grid.updateConfig({
        columns: createColumns(),
    });
};

const toggleColumnVisibility = columnName => {
    data.columnVisibility[columnName] = !data.columnVisibility[columnName];
    updateGridConfig();
    grid.forceRender();
};

onMounted(() => {
    initializeGrid();
})

const handleDeleteUser = (userId) => {
    router.delete(route("users.destroy", userId), {
        preserveScroll: true,
        onSuccess: () => {
            return route.push('users.index')
        },
    })
}

function deleteItem() {
    console.log('h')
}

function editItem() {
    console.log('h')
}
</script>

<template>
    <AppLayout title="User Management">
        <template #header>User Management</template>

        <CreateUserForm :branches="branches" :roles="roles"/>

        <div class="card mt-4">
            <div>
                <div class="flex items-center justify-between p-2">
                    <h2 class="text-base font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100">
                        User Management
                    </h2>

                    <div class="flex">
                        <Popper>
                            <button
                                class="btn size-8 rounded-full p-0 hover:bg-slate-300/20 focus:bg-slate-300/20 active:bg-slate-300/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25">
                                <i class="fa-solid fa-grip"></i>
                            </button>
                            <template #content>
                                <div class="max-w-[16rem]">
                                    <div
                                        class="popper-box w-64 rounded-lg border border-slate-150 bg-white shadow-soft dark:border-navy-600 dark:bg-navy-700">
                                        <div
                                            class="rounded-md border border-slate-150 bg-white p-4 dark:border-navy-600 dark:bg-navy-700">
                                            <h3 class="text-base font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100">
                                                Select Columns
                                            </h3>
                                            <p class="mt-1 text-xs+">Choose which columns you want to see </p>
                                            <div class="mt-4 flex flex-col space-y-4 text-slate-600 dark:text-navy-100">
                                                <label class="inline-flex items-center space-x-2">
                                                    <input
                                                        :checked="data.columnVisibility.username"
                                                        @change="toggleColumnVisibility('username', $event)"
                                                        class="form-checkbox is-basic size-5 rounded border-slate-400/70 checked:border-primary checked:bg-primary hover:border-primary focus:border-primary dark:border-navy-400 dark:checked:border-accent dark:checked:bg-accent dark:hover:border-accent dark:focus:border-accent"
                                                        type="checkbox"
                                                    />
                                                    <p>Reference</p>
                                                </label>

                                                <label class="inline-flex items-center space-x-2">
                                                    <input
                                                        :checked="data.columnVisibility.primary_branch_id"
                                                        @change="toggleColumnVisibility('primary_branch_id', $event)"
                                                        class="form-checkbox is-basic size-5 rounded border-slate-400/70 checked:border-primary checked:bg-primary hover:border-primary focus:border-primary dark:border-navy-400 dark:checked:border-accent dark:checked:bg-accent dark:hover:border-accent dark:focus:border-accent"
                                                        type="checkbox"
                                                    />
                                                    <p>Name</p>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </Popper>

                        <button
                            class="btn size-8 rounded-full p-0 hover:bg-slate-300/20 focus:bg-slate-300/20 active:bg-slate-300/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25">
                            <i class="fa-solid fa-filter"></i>
                        </button>
                    </div>
                </div>

                <div class=" mt-3">
                    <div class="is-scrollbar-hidden min-w-full overflow-x-auto">
                        <div ref="wrapperRef"></div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
