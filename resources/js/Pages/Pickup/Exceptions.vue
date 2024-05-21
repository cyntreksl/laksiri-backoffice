<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import {onMounted, reactive, ref} from "vue";
import {Grid, h} from "gridjs";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import moment from "moment";
import FilterDrawer from "@/Components/FilterDrawer.vue";
import SoftPrimaryButton from "@/Components/SoftPrimaryButton.vue";
import InputLabel from "@/Components/InputLabel.vue";
import DatePicker from "@/Components/DatePicker.vue";
import FilterBorder from "@/Components/FilterBorder.vue";
import ColumnVisibilityPopover from "@/Components/ColumnVisibilityPopover.vue";
import Checkbox from "@/Components/Checkbox.vue";
import FilterHeader from "@/Components/FilterHeader.vue";

defineProps({
    drivers: {
        type: Object,
        default: () => {
        },
    },
    users: {
        type: Object,
        default: () => {
        },
    },
    zones: {
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
    createdBy: '',
    zoneBy: '',
})

const data = reactive({
    columnVisibility: {
        reference: true,
        name: true,
        zone: true,
        picker_note: true,
        address: true,
        pickup_date: true,
        created_date: true,
        driver: true,
        auth: true,
        actions: true,
    },
});

const baseUrl = ref('/pickup-exception-list')

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
                url: (prev, keyword) => `${prev}&search=${keyword}`
            }
        },
        sort: {
            multiColumn: false,
            server: {
                url: (prev, columns) => {
                    if (!columns.length) return prev;
                    const col = columns[0];
                    const dir = col.direction === 1 ? 'asc' : 'desc';
                    let colName = visibleColumns[col.index];
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
                if (response && response.meta) {
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
    {name: 'Reference', hidden: !data.columnVisibility.reference},
    {name: 'Name', hidden: !data.columnVisibility.name},
    {name: 'Zone', hidden: !data.columnVisibility.zone},
    {name: 'Picker Note', hidden: !data.columnVisibility.picker_note},
    {name: 'Address', hidden: !data.columnVisibility.address, sort: false},
    {name: 'Pickup Date', hidden: !data.columnVisibility.pickup_date},
    {name: 'Created Date', hidden: !data.columnVisibility.created_date},
    {name: 'Driver', hidden: !data.columnVisibility.driver},
    {name: 'Auth', hidden: !data.columnVisibility.auth},
    {
        name: 'Actions',
        sort: false,
        hidden: !data.columnVisibility.actions,
        formatter: (_, row) => {
            return h('div', {}, [
                h('button', {
                    className: 'btn size-8 p-0 text-error hover:bg-error/20 focus:bg-error/20 active:bg-error/25',
                    onClick: () => alert(row.cells[0].data)
                }, [
                    h('svg', {
                        xmlns: 'http://www.w3.org/2000/svg',
                        viewBox: '0 0 24 24',
                        class: 'size-4.5',
                        fill: 'none',
                        stroke: "currentColor",
                        strokeWidth: 1.5,
                    }, [
                        h('path', {
                            strokeLinecap: 'round',
                            strokeLinejoin: 'round',
                            d: 'M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99',
                        }),
                    ])
                ]),

                h('button', {
                    className: 'btn size-8 p-0 text-error hover:bg-error/20 focus:bg-error/20 active:bg-error/25',
                    onClick: () => alert(row.cells[0].data)
                }, [
                    h('svg', {
                        xmlns: 'http://www.w3.org/2000/svg',
                        viewBox: '0 0 24 24',
                        class: 'size-4.5',
                        fill: 'none',
                        stroke: "currentColor",
                        strokeWidth: 1.5,
                    }, [
                        h('path', {
                            strokeLinecap: 'round',
                            strokeLinejoin: 'round',
                            d: 'M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z',
                        }),
                        h('path', {
                            strokeLinecap: 'round',
                            strokeLinejoin: 'round',
                            d: 'M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z',
                        }),
                    ]),
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
</script>
<template>
    <AppLayout title="Pickups Exceptions">
        <template #header>Pickups Exceptions</template>

        <Breadcrumb/>

        <div class="card mt-4">
            <div>
                <div class="flex items-center justify-between p-2">
                    <div class="">
                        <div class="flex">
                            <h2 class="text-base font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100">
                                Pickups Exceptions
                            </h2>
                        </div>

                        <div class="flex items-center mt-2 text-sm text-slate-500 dark:text-gray-300">
                            <div class="mr-4 cursor-pointer" x-tooltip.info.placement.bottom="'Applied Filters'">
                                Filter Options:
                            </div>
                            <div class="flex -space-x-px">
                                <div>
                                    <div
                                        class="tag rounded-r-none bg-slate-150 text-slate-800 hover:bg-slate-200 focus:bg-slate-200 active:bg-slate-200/80 dark:bg-navy-500 dark:text-navy-100 dark:hover:bg-navy-450 dark:focus:bg-navy-450 dark:active:bg-navy-450/90">
                                        From Date
                                    </div>
                                    <div
                                        class="tag rounded-l-none bg-primary text-white hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:hover:bg-accent-focus dark:focus:bg-accent-focus dark:active:bg-accent/90">
                                        {{ filters.fromDate }}
                                    </div>
                                </div>
                                <div>
                                    <div
                                        class="ml-4 tag rounded-r-none bg-slate-150 text-slate-800 hover:bg-slate-200 focus:bg-slate-200 active:bg-slate-200/80 dark:bg-navy-500 dark:text-navy-100 dark:hover:bg-navy-450 dark:focus:bg-navy-450 dark:active:bg-navy-450/90">
                                        To Date
                                    </div>
                                    <div
                                        class="tag rounded-l-none bg-warning text-white hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:hover:bg-accent-focus dark:focus:bg-accent-focus dark:active:bg-accent/90">
                                        {{ filters.toDate }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


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
                                <Checkbox :checked="data.columnVisibility.zone"
                                          @change="toggleColumnVisibility('zone', $event)"/>
                                <span class="hover:cursor-pointer">Zone</span>
                            </label>

                            <label class="inline-flex items-center space-x-2">
                                <Checkbox :checked="data.columnVisibility.picker_note"
                                          @change="toggleColumnVisibility('picker_note', $event)"/>
                                <span class="hover:cursor-pointer">Pickup Note</span>
                            </label>

                            <label class="inline-flex items-center space-x-2">
                                <Checkbox :checked="data.columnVisibility.address"
                                          @change="toggleColumnVisibility('address', $event)"/>
                                <span class="hover:cursor-pointer">Address</span>
                            </label>

                            <label class="inline-flex items-center space-x-2">
                                <Checkbox :checked="data.columnVisibility.pickup_date"
                                          @change="toggleColumnVisibility('pickup_date', $event)"/>
                                <span class="hover:cursor-pointer">Pickup Date</span>
                            </label>

                            <label class="inline-flex items-center space-x-2">
                                <Checkbox :checked="data.columnVisibility.created_date"
                                          @change="toggleColumnVisibility('created_date', $event)"/>
                                <span class="hover:cursor-pointer">Created Date</span>
                            </label>

                            <label class="inline-flex items-center space-x-2">
                                <Checkbox :checked="data.columnVisibility.driver"
                                          @change="toggleColumnVisibility('driver', $event)"/>
                                <span class="hover:cursor-pointer">Driver</span>
                            </label>

                            <label class="inline-flex items-center space-x-2">
                                <Checkbox :checked="data.columnVisibility.auth"
                                          @change="toggleColumnVisibility('auth', $event)"/>
                                <span class="hover:cursor-pointer">Auth</span>
                            </label>
                        </ColumnVisibilityPopover>

                        <button class="btn size-8 rounded-full p-0 hover:bg-slate-300/20 focus:bg-slate-300/20 active:bg-slate-300/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25" x-tooltip.placement.top="'Filters'"
                                @click="showFilters=true">
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

        <FilterDrawer :show="showFilters" @close="showFilters = false">
            <template #title>
                Filter Pickup Exceptions
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

                <FilterHeader value="Created By"/>

                <select
                    v-model="filters.createdBy"
                    autocomplete="off"
                    class="w-full"
                    multiple
                    placeholder="Select a User..."
                    x-init="$el._tom = new Tom($el,{
            plugins: ['remove_button'],
            create: true,
          })"
                >
                    <option v-for="user in users" :value="user.id">{{ user.name }}</option>
                </select>

                <FilterBorder/>

                <FilterHeader value="Zone"/>

                <select
                    v-model="filters.zoneBy"
                    autocomplete="off"
                    class="w-full"
                    multiple
                    placeholder="Select a Zone..."
                    x-init="$el._tom = new Tom($el,{
            plugins: ['remove_button'],
            create: true,
          })"
                >
                    <option v-for="zone in zones" :value="zone.id">{{ zone.zone_name }}</option>
                </select>


                <!--Filter Now Action Button-->
                <SoftPrimaryButton class="space-x-2" @click="applyFilters">
                    <i class="fa-solid fa-filter"></i>
                    <span>Apply Filters</span>
                </SoftPrimaryButton>
            </template>
        </FilterDrawer>
    </AppLayout>
</template>
