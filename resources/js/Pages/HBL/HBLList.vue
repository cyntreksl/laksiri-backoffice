<script setup>
import {onMounted, reactive, ref} from "vue";
import {Link, router} from '@inertiajs/vue3'
import {Grid, h, html} from "gridjs";
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
import FilterHeader from "@/Components/FilterHeader.vue";
import DeleteHBLConfirmationModal from "@/Pages/HBL/Partials/DeleteHBLConfirmationModal.vue";
import {push} from "notivue";
import HoldConfirmationModal from "@/Pages/HBL/Partials/HoldConfirmationModal.vue";
import HBLDetailModal from "@/Pages/Common/HBLDetailModal.vue";

const props = defineProps({
    users: {
        type: Object,
        default: () => {
        },
    },
    hbls: {
        type: Object,
        default: () => {},
    },
    paymentStatus: {
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
    hblType: ["UBP", "Gift", "Door to Door"],
    isHold: false,
    warehouse: ["COLOMBO", "NINTAVUR"],
    createdBy: '',
    paymentStatus: Object.values(props.paymentStatus),
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
        warehouse: true,
        status: false,
        is_hold: true,
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
    {name: 'ID', hidden: !data.columnVisibility.id},
    {name: 'Reference', hidden: !data.columnVisibility.reference},
    {name: 'HBL', hidden: !data.columnVisibility.hbl},
    {name: 'HBL Name', hidden: !data.columnVisibility.hbl_name},
    {name: 'Consignee Name', hidden: !data.columnVisibility.consignee_name},
    {name: 'Consignee Address', hidden: !data.columnVisibility.consignee_address,sort: false},
    {name: 'Consignee Contact', hidden: !data.columnVisibility.consignee_contact,sort: false},
    {name: 'Email', hidden: !data.columnVisibility.email,sort: false},
    {name: 'Address', hidden: !data.columnVisibility.address,sort: false},
    {name: 'Contact', hidden: !data.columnVisibility.contact_number,sort: false},
    {name: 'Cargo Mode', hidden: !data.columnVisibility.cargo_type},
    {name: 'HBL Type', hidden: !data.columnVisibility.hbl_type},
    {name: 'Warehouse', hidden: !data.columnVisibility.warehouse},
    {name: 'Status', hidden: !data.columnVisibility.status},
    {
        name: 'Is Hold',
        hidden: !data.columnVisibility.is_hold,
        formatter: (cell) => {
            return cell ? html(`<div></div class="text-center"><svg xmlns="http://www.w3.org/2000/svg" class="size-5 text-warning" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                  </svg></div>`) : null
        },
        sort: false
    },
    {
        name: 'Actions',
        sort: false,
        hidden: !data.columnVisibility.actions,
        formatter: (_, row) => {
            return h('div', {}, [
                h('button', {
                    className: 'btn size-8 p-0 text-info hover:bg-info/20 focus:bg-info/20 active:bg-info/25 mr-2',
                    onClick: () => router.visit(route('hbls.edit', row.cells[0].data))
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
                h('a', {
                    className: 'btn size-8 p-0 text-info hover:bg-info/20 focus:bg-info/20 active:bg-info/25 mr-2',
                    onClick: () => confirmViewHBL(row.cells[0].data),
                }, [
                    h('svg', {
                        xmlns: 'http://www.w3.org/2000/svg',
                        viewBox: '0 0 24 24',
                        class: 'size-5',
                        fill: 'none',
                        strokeWidth: 2.5,
                        stroke: 'currentColor',
                    }, [
                        h('path', {
                            d: 'M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z',
                            strokeLinecap: 'round',
                            strokeLinejoin: 'round',
                        }),
                        h('path', {
                            d: 'M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z',
                            strokeLinecap: 'round',
                            strokeLinejoin: 'round',
                        }),
                    ])
                ]),
                h('button', {
                    className: 'btn size-8 p-0 text-primary hover:bg-primary/20 focus:bg-primary/20 active:bg-primary/25',
                    onClick: () => confirmIsHold(row.cells)
                }, [
                    row.cells[14].data ? h('svg', {
                            xmlns: 'http://www.w3.org/2000/svg',
                            viewBox: '0 0 24 24',
                            class: 'size-4.5',
                            fill: 'none',
                            stroke: "currentColor",
                            strokeWidth: 1.5,
                        }, [
                            h('path', {
                                strokeLinecap: "round",
                                strokeLinejoin: 'round',
                                d: 'M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
                            }),
                            h('path', {
                                strokeLinecap: "round",
                                strokeLinejoin: 'round',
                                d: 'M15.91 11.672a.375.375 0 0 1 0 .656l-5.603 3.113a.375.375 0 0 1-.557-.328V8.887c0-.286.307-.466.557-.327l5.603 3.112Z',
                            }),
                        ]) :
                        h('svg', {
                            xmlns: 'http://www.w3.org/2000/svg',
                            viewBox: '0 0 24 24',
                            class: 'size-4.5',
                            fill: 'none',
                            stroke: "currentColor",
                            strokeWidth: 1.5,
                        }, [
                            h('path', {
                                strokeLinecap: "round",
                                strokeLinejoin: 'round',
                                d: 'M14.25 9v6m-4.5 0V9M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
                            }),
                        ])
                ]),
                h(
                    "button",
                    {
                        className:
                            "btn size-8 p-0 text-error hover:bg-error/20 focus:bg-error/20 active:bg-error/25",
                        onClick: () => confirmDeleteHBL(row.cells[0].data),
                    },
                    [
                        h(
                            "svg",
                            {
                                xmlns: "http://www.w3.org/2000/svg",
                                viewBox: "0 0 448 512",
                                class: "size-4.5",
                                fill: "none",
                            },
                            [
                                h("path", {
                                    d: "M170.5 51.6L151.5 80h145l-19-28.4c-1.5-2.2-4-3.6-6.7-3.6H177.1c-2.7 0-5.2 1.3-6.7 3.6zm147-26.6L354.2 80H368h48 8c13.3 0 24 10.7 24 24s-10.7 24-24 24h-8V432c0 44.2-35.8 80-80 80H112c-44.2 0-80-35.8-80-80V128H24c-13.3 0-24-10.7-24-24S10.7 80 24 80h8H80 93.8l36.7-55.1C140.9 9.4 158.4 0 177.1 0h93.7c18.7 0 36.2 9.4 46.6 24.9zM80 128V432c0 17.7 14.3 32 32 32H336c17.7 0 32-14.3 32-32V128H80zm80 64V400c0 8.8-7.2 16-16 16s-16-7.2-16-16V192c0-8.8 7.2-16 16-16s16 7.2 16 16zm80 0V400c0 8.8-7.2 16-16 16s-16-7.2-16-16V192c0-8.8 7.2-16 16-16s16 7.2 16 16zm80 0V400c0 8.8-7.2 16-16 16s-16-7.2-16-16V192c0-8.8 7.2-16 16-16s16 7.2 16 16z",
                                    fill: "currentColor",
                                }),
                            ]
                        ),
                    ]
                ),
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

const showConfirmDeleteHBLModal = ref(false);
const hblId = ref(null);
const selectedHBL = ref({});

const confirmDeleteHBL = (id) => {
    hblId.value = id;
    showConfirmDeleteHBLModal.value = true;
};

const closeModal = () => {
    showConfirmDeleteHBLModal.value = false;
    showConfirmViewHBLModal.value = false;
    hblId.value = null;
    selectedHBL.value = null;
}

const handleDeleteHBL = () => {
    router.delete(route("hbls.destroy", hblId.value), {
        preserveScroll: true,
        onSuccess: () => {
            closeModal();
            push.success('HBL record Deleted Successfully!');
            hblId.value = null;
            router.visit(route('hbls.index'), {only: ['hbls']})
        },
        onError: () => {
            closeModal();
            push.error('Something went to wrong!');
        }
    })
}

const showConfirmViewHBLModal = ref(false);

const confirmViewHBL = (id) => {
    selectedHBL.value = props.hbls.find(hbl => hbl.id === id);
    showConfirmViewHBLModal.value = true;
};

const hblData = ref({});

const showConfirmHoldModal = ref(false);

const confirmIsHold = (row) => {
    hblData.value = row;
    showConfirmHoldModal.value = true;
};

const closeHoldModal = () => {
    showConfirmHoldModal.value = false;
}

const toggleHold = () => {
    router.put(route('hbls.toggle-hold', hblData.value[0].data), {}, {
        preserveScroll: true,
        onSuccess: () => {
            closeHoldModal()
            push.success(hblData.value[14].data ? 'Released ' + hblData.value[1].data : 'Hold ' + hblData.value[1].data)
            router.visit(route('hbls.index'))
            hblData.value = {}
        },
        onError: () => {
            push.error('Something went to wrong!');
        }
    })
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
                    <div class="">
                        <div class="flex">
                            <h2 class="text-base font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100">
                                HBL List
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
                                <div>
                                    <div v-for="(mode, index) in filters.cargoMode" v-if="filters.cargoMode"
                                         :key="index" class="badge bg-navy-700 text-white dark:bg-navy-900 ml-2">{{
                                            mode
                                        }}
                                    </div>

                                    <div v-for="(type, index) in filters.hblType" v-if="filters.hblType"
                                         :key="index" class="badge bg-fuchsia-600 text-white dark:bg-fuchsia-600 ml-2">
                                        {{
                                            type
                                        }}
                                    </div>

                                    <div v-for="(item, index) in filters.warehouse" v-if="filters.warehouse"
                                         :key="index" class="badge bg-pink-600 text-white dark:bg-pink-600 ml-2">{{
                                            item
                                        }}
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
                                <Checkbox :checked="data.columnVisibility.warehouse"
                                          @change="toggleColumnVisibility('warehouse', $event)"/>
                                <span class="hover:cursor-pointer">Warehouse</span>
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

                <FilterHeader value="Cargo Mode"/>

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

                <FilterHeader value="HBL Type"/>

                <label class="inline-flex items-center space-x-2 mt-2">
                    <Switch v-model="filters.hblType" label="UBP" value="UBP"/>
                </label>

                <label class="inline-flex items-center space-x-2 mt-2">
                    <Switch v-model="filters.hblType" label="Gift" value="Gift"/>
                </label>

                <label class="inline-flex items-center space-x-2 mt-2">
                    <Switch v-model="filters.hblType" label="Door to Door" value="Door to Door"/>
                </label>

                <FilterBorder/>

                <FilterHeader value="Payment Status"/>

                <label v-for="item in paymentStatus" :key="item" class="inline-flex items-center space-x-2 mt-2">
                    <Switch v-model="filters.paymentStatus" :label="item" :value="item"/>
                </label>

                <FilterBorder/>

                <FilterHeader value="Is Hold"/>

                <label class="inline-flex items-center space-x-2 mt-2">
                    <Switch v-model="filters.isHold" label="Is Hold" value="true"/>
                </label>

                <FilterBorder/>

                <FilterHeader value="Warehouse"/>

                <label class="inline-flex items-center space-x-2 mt-2">
                    <Switch v-model="filters.warehouse" label="COLOMBO" value="COLOMBO"/>
                </label>

                <label class="inline-flex items-center space-x-2 mt-2">
                    <Switch v-model="filters.warehouse" label="NINTAVUR" value="NINTAVUR"/>
                </label>

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

                <!--Filter Now Action Button-->
                <SoftPrimaryButton class="space-x-2" @click="applyFilters">
                    <i class="fa-solid fa-filter"></i>
                    <span>Apply Filters</span>
                </SoftPrimaryButton>
            </template>
        </FilterDrawer>

        <DeleteHBLConfirmationModal :show="showConfirmDeleteHBLModal" @close="closeModal"
                                    @delete-hbl="handleDeleteHBL"/>

        <HBLDetailModal :hbl="selectedHBL" :show="showConfirmViewHBLModal" @close="closeModal"/>

        <HoldConfirmationModal :hbl-data="hblData" :show="showConfirmHoldModal" @close="closeHoldModal"
                               @toggle-hold="toggleHold"/>
    </AppLayout>
</template>
