<script setup>
import {onMounted, reactive, ref} from "vue";
import {Link} from '@inertiajs/vue3'
import {Grid, h} from "gridjs";
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SoftPrimaryButton from "@/Components/SoftPrimaryButton.vue";
import FilterDrawer from "@/Components/FilterDrawer.vue";
import InputLabel from "@/Components/InputLabel.vue";
import DatePicker from "@/Components/DatePicker.vue";
import FilterBorder from "@/Components/FilterBorder.vue";
import moment from "moment";
import ColumnVisibilityPopover from "@/Components/ColumnVisibilityPopover.vue";
import Checkbox from "@/Components/Checkbox.vue";
import Switch from "@/Components/Switch.vue";

defineProps({
    users: {
        type: Object,
        default: () => {
        },
    },
})

const wrapperRef = ref(null);
let grid = null;

const showFilters = ref(false);
const fromDate = moment(new Date()).subtract(7, 'days').format('YYYY-MM-DD');
const toDate = moment(new Date()).format('YYYY-MM-DD');

const filters = reactive({
    fromDate: fromDate,
    toDate: toDate,
    cargoMode: ["Air Cargo", "Sea Cargo", "Door to Door"],
    createdBy: '',
})

const data = reactive({
    columnVisibility: {
        id: false,
        reference: true,
        hbl: true,
        hbl_name: true,
        consignee_name: true,
        consignee_address: true,
        consignee_contact: true,
        email: false,
        address: false,
        contact_number: true,
        cargo_type: true,
        hbl_type: true,
        status: false,
        actions: true,
    }
});

const baseUrl = ref('/hbl-list')

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
    {name: 'HBL', hidden: !data.columnVisibility.hbl},
    {name: 'HBL Name', hidden: !data.columnVisibility.hbl_name},
    {name: 'Consignee Name', hidden: !data.columnVisibility.consignee_name},
    {name: 'Consignee Address', hidden: !data.columnVisibility.consignee_address},
    {name: 'Consignee Contact', hidden: !data.columnVisibility.consignee_contact},
    {name: 'Email', hidden: !data.columnVisibility.email},
    {name: 'Address', hidden: !data.columnVisibility.address},
    {name: 'Contact', hidden: !data.columnVisibility.contact_number},
    {name: 'Cargo Mode', hidden: !data.columnVisibility.cargo_type},
    {name: 'HBL Type', hidden: !data.columnVisibility.hbl_type},
    {name: 'Status', hidden: !data.columnVisibility.status},
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
</script>

<template>
    <AppLayout title="HBL List">
        <template #header>HBL List</template>

        <Breadcrumb/>
        <div class="flex justify-end mt-5">
            <Link :href="route('hbls.create')">
                <PrimaryButton>
                    Create New HBL
                </PrimaryButton>
            </Link>
        </div>
        <div class="card mt-4">
            <div>
                <div class="flex items-center justify-between p-2">
                    <h2 class="text-base font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100">
                        HBL List
                    </h2>

                    <div class="flex">
                        <ColumnVisibilityPopover>
                            <label class="inline-flex items-center space-x-2">
                                <Checkbox :checked="data.columnVisibility.reference"
                                          @change="toggleColumnVisibility('reference', $event)"/>
                                <span class="hover:cursor-pointer">Reference</span>
                            </label>

                            <label class="inline-flex items-center space-x-2">
                                <Checkbox :checked="data.columnVisibility.hbl"
                                          @change="toggleColumnVisibility('hbl', $event)"/>
                                <span class="hover:cursor-pointer">HBL</span>
                            </label>

                            <label class="inline-flex items-center space-x-2">
                                <Checkbox :checked="data.columnVisibility.hbl_name"
                                          @change="toggleColumnVisibility('hbl_name', $event)"/>
                                <span class="hover:cursor-pointer">HBL Name</span>
                            </label>

                            <label class="inline-flex items-center space-x-2">
                                <Checkbox :checked="data.columnVisibility.consignee_name"
                                          @change="toggleColumnVisibility('consignee_name', $event)"/>
                                <span class="hover:cursor-pointer">Consignee Name</span>
                            </label>

                            <label class="inline-flex items-center space-x-2">
                                <Checkbox :checked="data.columnVisibility.consignee_address"
                                          @change="toggleColumnVisibility('consignee_address', $event)"/>
                                <span class="hover:cursor-pointer">Consignee Address</span>
                            </label>

                            <label class="inline-flex items-center space-x-2">
                                <Checkbox :checked="data.columnVisibility.consignee_contact"
                                          @change="toggleColumnVisibility('consignee_contact', $event)"/>
                                <span class="hover:cursor-pointer">Consignee Contact</span>
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
                                <Checkbox :checked="data.columnVisibility.hbl_type"
                                          @change="toggleColumnVisibility('hbl_type', $event)"/>
                                <span class="hover:cursor-pointer">HBL Type</span>
                            </label>

                            <label class="inline-flex items-center space-x-2">
                                <Checkbox :checked="data.columnVisibility.status"
                                          @change="toggleColumnVisibility('status', $event)"/>
                                <span class="hover:cursor-pointer">Status</span>
                            </label>
                        </ColumnVisibilityPopover>
                        <button
                            x-tooltip.placement.top="'Filters'" @click="showFilters=true"
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

        <FilterDrawer :show="showFilters" @close="showFilters = false">
            <template #title>
                Filter HBL
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
                    <Switch v-model="filters.cargoMode" label="Air Cargo" value="Air Cargo"/>
                </label>

                <label class="inline-flex items-center space-x-2 mt-2">
                    <Switch v-model="filters.cargoMode" label="Sea Cargo" value="Sea Cargo"/>
                </label>

                <label class="inline-flex items-center space-x-2 mt-2">
                    <Switch v-model="filters.cargoMode" label="Door to Door" value="Door to Door"/>
                </label>

                <FilterBorder/>

                <div>
                    <span class="font-medium">Created By</span>
                </div>

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

                <!--Filter Now Action Button-->
                <SoftPrimaryButton class="space-x-2" @click="applyFilters">
                    <i class="fa-solid fa-filter"></i>
                    <span>Apply Filters</span>
                </SoftPrimaryButton>
            </template>
        </FilterDrawer>
    </AppLayout>
</template>
