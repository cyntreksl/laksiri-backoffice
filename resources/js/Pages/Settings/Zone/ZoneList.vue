<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import {onMounted, reactive, ref} from "vue";
import {Grid, h, html} from "gridjs";
import Popper from "vue3-popper";
import {router} from "@inertiajs/vue3";
import notification from "@/magics/notification.js";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import DeleteZoneConfirmationModal from "@/Pages/Settings/Partials/DeleteZoneConfirmationModal.vue";
import CreateZoneForm from "@/Pages/Settings/Partials/CreateZoneForm.vue";

const props = defineProps({
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
    ZoneData: {},
    columnVisibility: {
        id: false,
        name: true,
        areas: true,
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
                    let colName = ['id', 'name','areas'][col.index];

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
            url: '/settings/zones/list?',
            then: data => data.data.map(item => [
                item.id,
                item.name,
                item.areas.map(area => area.name).join('/')
            ]),
            total: data => data.meta.total
        }

    });

    grid.render(wrapperRef.value);
};

const createColumns = () => [
    {name: 'ID', hidden: !data.columnVisibility.id},
    {name: 'Name', hidden: !data.columnVisibility.name},
    {
        name: 'Areas',
        hidden: !data.columnVisibility.areas,

        formatter: (_, row) => {
            return h('div', {
                class: 'inline-space mt-3 flex grow flex-wrap items-start'
            }, [
                row.cells[2].data.split('/').map(area => h('span', {
                    class: 'tag rounded-full bg-success/10 text-success hover:bg-success/20 focus:bg-success/20 active:bg-success/25'
                }, area.toUpperCase()))
            ]);
        }
    },

    {
        name: 'Actions',
        sort: false,
        hidden: !data.columnVisibility.actions,
        formatter: (_, row) => {
            return h('div', {}, [
                h('button', {
                    className: 'btn size-8 p-0 text-error hover:bg-error/20 focus:bg-error/20 active:bg-error/25',
                    onClick: () => confirmDeleteZone(row.cells[0].data)
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

const showConfirmDeleteZoneModal = ref(false);
const zoneId = ref(null);

const confirmDeleteZone = (id) => {
    zoneId.value = id;
    showConfirmDeleteZoneModal.value = true;
};

const closeModal = () => {
    showConfirmDeleteZoneModal.value = false;
}

const handleDeleteZone = () => {
    router.delete(route("settings.zones.destroy", zoneId.value), {
        preserveScroll: true,
        onSuccess: () => {
            closeModal();
            notification({
                text: 'Zone Deleted Successfully!',
                variant: 'success',
            });
            zoneId.value = null;
            router.visit(route('settings.zones.index'), {only: ['users']})
        },
    })
}
</script>

<template>
    <AppLayout title="Zones">
        <template #header>Settings</template>

        <Breadcrumb/>

        <CreateZoneForm/>

        <div class="card mt-4">
            <div>
                <div class="flex items-center justify-between p-2">
                    <h2 class="text-base font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100">
                        Zones
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
                                                        @change="toggleColumnVisibility('id', $event)"
                                                        class="form-checkbox is-basic size-5 rounded border-slate-400/70 checked:border-primary checked:bg-primary hover:border-primary focus:border-primary dark:border-navy-400 dark:checked:border-accent dark:checked:bg-accent dark:hover:border-accent dark:focus:border-accent"
                                                        type="checkbox"
                                                    />
                                                    <p>ID</p>
                                                </label>

                                                <label class="inline-flex items-center space-x-2">
                                                    <input
                                                        :checked="data.columnVisibility.created_at"
                                                        @change="toggleColumnVisibility('created_at', $event)"
                                                        class="form-checkbox is-basic size-5 rounded border-slate-400/70 checked:border-primary checked:bg-primary hover:border-primary focus:border-primary dark:border-navy-400 dark:checked:border-accent dark:checked:bg-accent dark:hover:border-accent dark:focus:border-accent"
                                                        type="checkbox"
                                                    />
                                                    <p>Created At</p>
                                                </label>

                                                <label class="inline-flex items-center space-x-2">
                                                    <input
                                                        :checked="data.columnVisibility.last_login_at"
                                                        @change="toggleColumnVisibility('last_login_at', $event)"
                                                        class="form-checkbox is-basic size-5 rounded border-slate-400/70 checked:border-primary checked:bg-primary hover:border-primary focus:border-primary dark:border-navy-400 dark:checked:border-accent dark:checked:bg-accent dark:hover:border-accent dark:focus:border-accent"
                                                        type="checkbox"
                                                    />
                                                    <p>Last Login</p>
                                                </label>

                                                <label class="inline-flex items-center space-x-2">
                                                    <input
                                                        :checked="data.columnVisibility.last_logout_at"
                                                        @change="toggleColumnVisibility('last_logout_at', $event)"
                                                        class="form-checkbox is-basic size-5 rounded border-slate-400/70 checked:border-primary checked:bg-primary hover:border-primary focus:border-primary dark:border-navy-400 dark:checked:border-accent dark:checked:bg-accent dark:hover:border-accent dark:focus:border-accent"
                                                        type="checkbox"
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

        <DeleteZoneConfirmationModal :show="showConfirmDeleteZoneModal" @close="closeModal"
                                     @delete-zone="handleDeleteZone"/>
    </AppLayout>
</template>
