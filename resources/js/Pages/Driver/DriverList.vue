<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import {onMounted, reactive, ref} from "vue";
import {Grid, h, html} from "gridjs";
import Popper from "vue3-popper";
import {router} from "@inertiajs/vue3";
import notification from "@/magics/notification.js";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import CreateDriverForm from "@/Pages/Driver/Partials/CreateDriverForm.vue";
import DeleteDriverConfirmationModal from "@/Pages/Driver/Partials/DeleteDriverConfirmationModal.vue";

const wrapperRef = ref(null);
let grid = null;

const data = reactive({
    DriverData: {},
    columnVisibility: {
        id: false,
        username: true,
        name: true,
        primary_branch_name: true,
        created_at: true,
        status: true,
        actions: true,
    }
});

const initializeGrid = () => {
    grid = new Grid({
        columns: createColumns(),
        search: {
            debounceTimeout: 1000,
            server: {
                url: (prev, keyword) => `${prev}?search=${keyword}`
            }
        },
        sort: {
            multiColumn: false,
            server: {
                url: (prev, columns) => {
                    if (!columns.length) return prev;

                    const col = columns[0];
                    const dir = col.direction === 1 ? 'asc' : 'desc';
                    let colName = ['id', 'username', 'primary_branch_name', 'created_at', 'status'][col.index];

                    return `${prev}&order=${colName}&dir=${dir}`;
                }
            }
        },
        pagination: {
            limit: 10,
            server: {
                url: (prev, page, limit) => `${prev}&limit=${limit}&offset=${page * limit}`
            }
        },
        server: {
            url: '/driver-list?',
            then: data => data.data.map(item => [
                item.id,
                item.username,
                item.name,
                item.primary_branch_name,
                item.created_at,
                item.status,
            ]),
            total: data => data.meta.total
        }

    });

    grid.render(wrapperRef.value);
};

const createColumns = () => [
    {name: 'ID', hidden: !data.columnVisibility.id},
    {name: 'Username', hidden: !data.columnVisibility.username},
    {name: 'Name', hidden: !data.columnVisibility.name},
    {name: 'Primary Branch Name', hidden: !data.columnVisibility.primary_branch_name, sort: false},
    {name: 'Created At', hidden: !data.columnVisibility.created_at},
    {
        name: 'Status',
        hidden: !data.columnVisibility.status,
        formatter: (cell) => html(`<div class="${resolveStatus(cell)}">${cell}</div>`)
    },
    {
        name: 'Actions',
        sort: false,
        hidden: !data.columnVisibility.actions,
        formatter: (_, row) => {
            return h('div', {}, [
                h('a', {
                    className: 'btn size-8 p-0 text-info hover:bg-info/20 focus:bg-info/20 active:bg-info/25 mr-2',
                    href: route('drivers.edit', row.cells[0].data)
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
                    onClick: () => confirmDeleteDriver(row.cells[0].data)
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

const resolveStatus = status => ({
    'ACTIVE': 'badge bg-success/10 text-success dark:bg-success/15',
    'DEACTIVATE': 'badge bg-error/10 text-error dark:bg-error/15',
    'INACTIVE': 'badge bg-warning/10 text-warning dark:bg-warning/15',
    'INVITED': 'badge bg-info/10 text-info dark:bg-info/15'
}[status]);

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

const showConfirmDeleteDriverModal = ref(false);
const driverId = ref(null);

const confirmDeleteDriver = (id) => {
    driverId.value = id;
    showConfirmDeleteDriverModal.value = true;
};

const closeModal = () => {
    showConfirmDeleteDriverModal.value = false;
}

const handleDeleteDriver = () => {
    router.delete(route("users.destroy", driverId.value), {
        preserveScroll: true,
        onSuccess: () => {
            closeModal();
            notification({
                text: 'Driver Deleted Successfully!',
                variant: 'success',
            });
            driverId.value = null;
            router.visit(route('drivers.index'), {only: ['users']})
        },
    })

}
</script>

<template>
    <AppLayout title="Driver Management">
        <template #header>Driver Management</template>

        <Breadcrumb/>

        <CreateDriverForm/>

        <div class="card mt-4">
            <div>
                <div class="flex items-center justify-between p-2">
                    <h2 class="text-base font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100">
                        Driver Management
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
                                                        :checked="data.columnVisibility.id"
                                                        class="form-checkbox is-basic size-5 rounded border-slate-400/70 checked:border-primary checked:bg-primary hover:border-primary focus:border-primary dark:border-navy-400 dark:checked:border-accent dark:checked:bg-accent dark:hover:border-accent dark:focus:border-accent"
                                                        type="checkbox"
                                                        @change="toggleColumnVisibility('id', $event)"
                                                    />
                                                    <p>ID</p>
                                                </label>

                                                <label class="inline-flex items-center space-x-2">
                                                    <input
                                                        :checked="data.columnVisibility.secondary_branch_names"
                                                        class="form-checkbox is-basic size-5 rounded border-slate-400/70 checked:border-primary checked:bg-primary hover:border-primary focus:border-primary dark:border-navy-400 dark:checked:border-accent dark:checked:bg-accent dark:hover:border-accent dark:focus:border-accent"
                                                        type="checkbox"
                                                        @change="toggleColumnVisibility('secondary_branch_names', $event)"
                                                    />
                                                    <p>Secondary Branches</p>
                                                </label>

                                                <label class="inline-flex items-center space-x-2">
                                                    <input
                                                        :checked="data.columnVisibility.created_at"
                                                        class="form-checkbox is-basic size-5 rounded border-slate-400/70 checked:border-primary checked:bg-primary hover:border-primary focus:border-primary dark:border-navy-400 dark:checked:border-accent dark:checked:bg-accent dark:hover:border-accent dark:focus:border-accent"
                                                        type="checkbox"
                                                        @change="toggleColumnVisibility('created_at', $event)"
                                                    />
                                                    <p>Created At</p>
                                                </label>

                                                <label class="inline-flex items-center space-x-2">
                                                    <input
                                                        :checked="data.columnVisibility.last_login_at"
                                                        class="form-checkbox is-basic size-5 rounded border-slate-400/70 checked:border-primary checked:bg-primary hover:border-primary focus:border-primary dark:border-navy-400 dark:checked:border-accent dark:checked:bg-accent dark:hover:border-accent dark:focus:border-accent"
                                                        type="checkbox"
                                                        @change="toggleColumnVisibility('last_login_at', $event)"
                                                    />
                                                    <p>Last Login</p>
                                                </label>

                                                <label class="inline-flex items-center space-x-2">
                                                    <input
                                                        :checked="data.columnVisibility.last_logout_at"
                                                        class="form-checkbox is-basic size-5 rounded border-slate-400/70 checked:border-primary checked:bg-primary hover:border-primary focus:border-primary dark:border-navy-400 dark:checked:border-accent dark:checked:bg-accent dark:hover:border-accent dark:focus:border-accent"
                                                        type="checkbox"
                                                        @change="toggleColumnVisibility('last_logout_at', $event)"
                                                    />
                                                    <p>Last Logout</p>
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

        <DeleteDriverConfirmationModal :show="showConfirmDeleteDriverModal" @close="closeModal"
                                       @delete-driver="handleDeleteDriver"/>
    </AppLayout>
</template>
