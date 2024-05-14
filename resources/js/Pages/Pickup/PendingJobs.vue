<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import {onMounted, reactive, ref} from "vue";
import {Grid, h} from "gridjs";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import AssignDriverModal from "@/Pages/Pickup/Partials/AssignDriverModal.vue";
import moment from "moment";
import FilterDrawer from "@/Components/FilterDrawer.vue";
import SoftPrimaryButton from "@/Components/SoftPrimaryButton.vue";
import InputLabel from "@/Components/InputLabel.vue";
import DatePicker from "@/Components/DatePicker.vue";
import FilterBorder from "@/Components/FilterBorder.vue";
import ColumnVisibilityPopover from "@/Components/ColumnVisibilityPopover.vue";
import Checkbox from "@/Components/Checkbox.vue";

defineProps({
    drivers: {
        type: Object,
        default: () => {
        },
    }
})

const wrapperRef = ref(null);
let grid = null;

const showFilters = ref(false);
const fromDate = moment(new Date()).subtract(7, 'days').format('YYYY-MM-DD');
const toDate = moment(new Date()).format('YYYY-MM-DD');

const filters = reactive({
    fromDate: fromDate,
    toDate: toDate,
    cargoMode: ["Air Cargo", "Sea Cargo"],
    isUrgent: true,
    isImportant: true,
})

const data = reactive({
    columnVisibility: {
        id: false,
        reference: true,
        name: true,
        email: false,
        address: true,
        contact_number: true,
        cargo_type: true,
        pickup_date: true,
        pickup_time_start: false,
        pickup_time_end: false,
        actions: true,
    },
});

const baseUrl = ref('/pickup-list')

const toggleColumnVisibility = columnName => {
    data.columnVisibility[columnName] = !data.columnVisibility[columnName];
    updateGridConfig();
    grid.forceRender();
};

const initializeGrid = () => {
    const visibleColumns = Object.keys(data.columnVisibility);

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
                    let colName = Object.keys(data.columnVisibility).filter(key => data.columnVisibility[key])[col.index];

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
            url: constructUrl(),
            then: data => data.data.map(item => {
                const row = [];
                // row.push({id: item.id})
                visibleColumns.forEach(column => {
                    row.push(item[column]);
                });
                return row;
            }),
            total: response => {
                if (response && response.meta && response.meta.total) {
                    return response.meta.total;
                } else {
                    throw new Error('Invalid total count in server response');
                }
            }
        }
    });

    grid.render(wrapperRef.value);
};

const createColumns = () => [
    {name: 'ID', hidden: !data.columnVisibility.id},
    {name: 'Reference', hidden: !data.columnVisibility.reference},
    {name: 'Name', hidden: !data.columnVisibility.name},
    {name: 'Email', hidden: !data.columnVisibility.email},
    {name: 'Address', hidden: !data.columnVisibility.address},
    {name: 'Contact', hidden: !data.columnVisibility.contact_number},
    {name: 'Cargo Mode', hidden: !data.columnVisibility.cargo_type},
    {name: 'Pickup Date', hidden: !data.columnVisibility.pickup_date},
    {name: 'Pickup Time Start', hidden: !data.columnVisibility.pickup_time_start},
    {name: 'Pickup Time End', hidden: !data.columnVisibility.pickup_time_end},
    {
        name: 'Actions',
        sort: false,
        hidden: !data.columnVisibility.actions,
        formatter: (_, row) => {
            return h('div', {}, [
                h('button', {
                    className: 'btn size-8 p-0 text-error hover:bg-error/20 focus:bg-error/20 active:bg-error/25',
                    onClick: () => confirmAssignDriver(row.cells[0].data)
                }, [
                    h('svg', {
                        xmlns: 'http://www.w3.org/2000/svg',
                        viewBox: '0 0 24 24',
                        width: 24,
                        height: 24,
                        class: 'size-4.5 icon icon-tabler icons-tabler-outline icon-tabler-truck',
                        fill: 'none',
                        stroke: "currentColor",
                        strokeWidth: 2,
                        strokeLinecap: "round",
                        strokeLinejoin: "round",
                    }, [
                        h('path', {
                            stroke: "none",
                            d: 'M0 0h24v24H0z',
                            fill: 'none',
                        }),
                        h('path', {
                            d: 'M7 17m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0',
                        }),
                        h('path', {
                            d: 'M17 17m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0',
                        }),
                        h('path', {
                            d: 'M5 17h-2v-11a1 1 0 0 1 1 -1h9v12m-4 0h6m4 0h2v-6h-8m0 -5h5l3 5',
                        }),
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

onMounted(() => {
    initializeGrid();
})

const constructUrl = () => {
    const params = new URLSearchParams();
    for (const key in filters) {
        if (filters.hasOwnProperty(key)) {
            params.append(key, filters[key].toString());
        }
    }
    return baseUrl.value + '?' + params.toString();
}

const applyFilters = () => {
    showFilters.value = false;
    const newUrl = constructUrl();
    const visibleColumns = Object.keys(data.columnVisibility);
    grid.updateConfig({
        server: {
            url: newUrl,
            then: data => data.data.map(item => {
                const row = [];
                visibleColumns.forEach(column => {
                    row.push(item[column]);
                });
                return row;
            }),
        }
    });
    grid.forceRender();
}

const showConfirmAssignDriverModal = ref(false);
const jobId = ref(null);

const confirmAssignDriver = (id) => {
    jobId.value = id;
    showConfirmAssignDriverModal.value = true;
};

const closeModal = () => {
    showConfirmAssignDriverModal.value = false;
    jobId.value = null;
}
</script>
<template>
    <AppLayout title="Pending Pickups">
        <template #header>Pending Pickups</template>

        <Breadcrumb/>

        <div class="card mt-4">
            <div>
                <div class="flex items-center justify-between p-2">
                    <h2 class="text-base font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100">
                        Pending Pickups
                    </h2>

                    <div class="flex">
                        <ColumnVisibilityPopover>
                            <label class="inline-flex items-center space-x-2">
                                <Checkbox :checked="data.columnVisibility.reference"
                                          @change="toggleColumnVisibility('reference', $event)"/>
                                <span class="hover:cursor-pointer">Reference</span>
                            </label>

                            <label class="inline-flex items-center space-x-2">
                                <Checkbox :checked="data.columnVisibility.name"
                                          @change="toggleColumnVisibility('name', $event)"/>
                                <span class="hover:cursor-pointer">Name</span>
                            </label>

                            <label class="inline-flex items-center space-x-2">
                                <Checkbox :checked="data.columnVisibility.email"
                                          @change="toggleColumnVisibility('email', $event)"/>
                                <span class="hover:cursor-pointer">Email</span>
                            </label>

                            <label class="inline-flex items-center space-x-2">
                                <Checkbox :checked="data.columnVisibility.address"
                                          @change="toggleColumnVisibility('address', $event)"/>
                                <span class="hover:cursor-pointer">Address</span>
                            </label>

                            <label class="inline-flex items-center space-x-2">
                                <Checkbox :checked="data.columnVisibility.contact_number"
                                          @change="toggleColumnVisibility('contact_number', $event)"/>
                                <span class="hover:cursor-pointer">Contact</span>
                            </label>

                            <label class="inline-flex items-center space-x-2">
                                <Checkbox :checked="data.columnVisibility.cargo_type"
                                          @change="toggleColumnVisibility('cargo_type', $event)"/>
                                <span class="hover:cursor-pointer">Cargo Mode</span>
                            </label>

                            <label class="inline-flex items-center space-x-2">
                                <Checkbox :checked="data.columnVisibility.pickup_date"
                                          @change="toggleColumnVisibility('pickup_date', $event)"/>
                                <span class="hover:cursor-pointer">Pickup Date</span>
                            </label>

                            <label class="inline-flex items-center space-x-2">
                                <Checkbox :checked="data.columnVisibility.pickup_time_start"
                                          @change="toggleColumnVisibility('pickup_time_start', $event)"/>
                                <span class="hover:cursor-pointer">Pickup Time Start</span>
                            </label>

                            <label class="inline-flex items-center space-x-2">
                                <Checkbox :checked="data.columnVisibility.pickup_time_end"
                                          @change="toggleColumnVisibility('pickup_time_end', $event)"/>
                                <span class="hover:cursor-pointer">Pickup Time End</span>
                            </label>
                        </ColumnVisibilityPopover>

                        <button x-tooltip.placement.top="'Filters'" @click="showFilters=true"
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

        <AssignDriverModal :drivers="drivers" :job-id="jobId" :show="showConfirmAssignDriverModal" @close="closeModal"/>

        <FilterDrawer :show="showFilters" @close="showFilters = false">
            <template #title>
                Filter Pending Jobs
            </template>

            <template #content>
                <div>
                    <InputLabel value="From"/>
                    <DatePicker v-model="filters.fromDate" placeholder="Choose date..."/>
                </div>

                <div>
                    <InputLabel value="To"/>
                    <DatePicker v-model="filters.toDate" placeholder="Choose date..."/>
                </div>

                <FilterBorder/>

                <div>
                    <span class="font-medium">Cargo Mode</span>
                </div>

                <label class="inline-flex items-center space-x-2 mt-2">
                    <input v-model="filters.cargoMode" class="form-switch h-5 w-10 rounded-full bg-slate-300 before:rounded-full before:bg-slate-50 checked:bg-primary checked:before:bg-white dark:bg-navy-900 dark:before:bg-navy-300 dark:checked:bg-accent dark:checked:before:bg-white"
                           type="checkbox"
                           value="Air Cargo"/>
                    <span>Air Cargo</span>
                </label>

                <label class="inline-flex items-center space-x-2 mt-2">
                    <input v-model="filters.cargoMode" class="form-switch h-5 w-10 rounded-full bg-slate-300 before:rounded-full before:bg-slate-50 checked:bg-primary checked:before:bg-white dark:bg-navy-900 dark:before:bg-navy-300 dark:checked:bg-accent dark:checked:before:bg-white"
                           type="checkbox"
                           value="Sea Cargo"/>
                    <span>Sea Cargo</span>
                </label>

                <FilterBorder/>

                <div>
                    <span class="font-medium">Is Urgent</span>
                </div>

                <label class="inline-flex items-center space-x-2 mt-2">
                    <input v-model="filters.isUrgent" class="form-switch h-5 w-10 rounded-full bg-slate-300 before:rounded-full before:bg-slate-50 checked:bg-primary checked:before:bg-white dark:bg-navy-900 dark:before:bg-navy-300 dark:checked:bg-accent dark:checked:before:bg-white"
                           type="checkbox"
                           value="true"/>
                    <span>Is Urgent</span>
                </label>

                <FilterBorder/>

                <div>
                    <span class="font-medium">Is Important to Customer</span>
                </div>

                <label class="inline-flex items-center space-x-2 mt-2">
                    <input v-model="filters.isImportant" class="form-switch h-5 w-10 rounded-full bg-slate-300 before:rounded-full before:bg-slate-50 checked:bg-primary checked:before:bg-white dark:bg-navy-900 dark:before:bg-navy-300 dark:checked:bg-accent dark:checked:before:bg-white"
                           type="checkbox"
                           value="true"/>
                    <span>Is Important</span>
                </label>

                <!--Filter Now Action Button-->
                <SoftPrimaryButton class="space-x-2" @click="applyFilters">
                    <i class="fa-solid fa-filter"></i>
                    <span>Apply Filters</span>
                </SoftPrimaryButton>
            </template>
        </FilterDrawer>
    </AppLayout>
</template>
<style scoped>
[type='checkbox']:checked {
    background-image: none !important;
}

[type='checkbox']:focus, [type='radio']:focus {
    --tw-ring-offset-width: 0 !important;
}
</style>
