<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import {onMounted, reactive, ref} from "vue";
import {className, Grid, h, html} from "gridjs";
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


const editItem = (row) => {
    console.log('Edit item:', row);
    // Add your edit logic here
};

const deleteItem = (row) => {
    console.log('Delete item:', row);
    // Add your delete logic here
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
        formatter: (_, row) => {
            return h('div', {}, [
                h('button', {
                    className: 'btn size-8 p-0 text-info hover:bg-info/20 focus:bg-info/20 active:bg-info/25 mr-2',
                    onClick: () => editItem(row)
                }, [
                    h('svg', {
                        xmlns: 'http://www.w3.org/2000/svg',
                        viewBox: '0 0 512 512',
                        class: 'size-4.5',
                        fill: 'none',
                    }, [
                        h('path', {
                            d: 'M471 58.9L453.1 71c9.4 9.4 9.4 24.6 0 33.9L424 134.1 377.9 88 407 58.9c9.4-9.4 24.6-9.4 33.9 0zM209.8 256.2L344 121.9 390.1 168 255.8 302.2c-2.9 2.9-6.5 5-10.4 6.1l-58.5 16.7 16.7-58.5c1.1-3.9 3.2-7.5 6.1-10.4zM373.1 25L175.8 222.2c-8.7 8.7-15 19.4-18.3 31.1l-28.6 100c-2.4 8.4-.1 17.4 6.1 23.6s15.2 8.5 23.6 6.1l100-28.6c11.8-3.4 22.5-9.7 31.1-18.3L487 138.9c28.1-28.1 28.1-73.7 0-101.8L474.9 25C446.8-3.1 401.2-3.1 373.1 25zM88 64C39.4 64 0 103.4 0 152V424c0 48.6 39.4 88 88 88H360c48.6 0 88-39.4 88-88V312c0-13.3-10.7-24-24-24s-24 10.7-24 24V424c0 22.1-17.9 40-40 40H88c-22.1 0-40-17.9-40-40V152c0-22.1 17.9-40 40-40H200c13.3 0 24-10.7 24-24s-10.7-24-24-24H88z',
                            fill: 'currentColor',
                        })
                    ])
                ]),
                h('button', {
                    className: 'btn size-8 p-0 text-error hover:bg-error/20 focus:bg-error/20 active:bg-error/25',
                    onClick: () => handleDeleteUser(row)
                }, [
                    h('svg', {
                        xmlns: 'http://www.w3.org/2000/svg',
                        viewBox: '0 0 448 512',
                        class: 'size-4.5',
                        fill: 'none',
                    }, [
                        h('path', {
                            d: 'M170.5 51.6L151.5 80h145l-19-28.4c-1.5-2.2-4-3.6-6.7-3.6H177.1c-2.7 0-5.2 1.3-6.7 3.6zm147-26.6L354.2 80H368h48 8c13.3 0 24 10.7 24 24s-10.7 24-24 24h-8V432c0 44.2-35.8 80-80 80H112c-44.2 0-80-35.8-80-80V128H24c-13.3 0-24-10.7-24-24S10.7 80 24 80h8H80 93.8l36.7-55.1C140.9 9.4 158.4 0 177.1 0h93.7c18.7 0 36.2 9.4 46.6 24.9zM80 128V432c0 17.7 14.3 32 32 32H336c17.7 0 32-14.3 32-32V128H80zm80 64V400c0 8.8-7.2 16-16 16s-16-7.2-16-16V192c0-8.8 7.2-16 16-16s16 7.2 16 16zm80 0V400c0 8.8-7.2 16-16 16s-16-7.2-16-16V192c0-8.8 7.2-16 16-16s16 7.2 16 16zm80 0V400c0 8.8-7.2 16-16 16s-16-7.2-16-16V192c0-8.8 7.2-16 16-16s16 7.2 16 16z',
                            fill: 'currentColor',
                        })
                    ])
                ]),
            ]);
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
