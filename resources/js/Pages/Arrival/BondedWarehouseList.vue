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
import Switch from "@/Components/Switch.vue";
import FilterHeader from "@/Components/FilterHeader.vue";
import {router} from "@inertiajs/vue3";

const props = defineProps({
    hblTypes: {
        type: Array,
        default: () => [],
    },
});

const wrapperRef = ref(null);
let grid = null;

const showFilters = ref(false);
const fromDate = moment(new Date()).subtract(7, "days").format("YYYY-MM-DD");
const toDate = moment(new Date()).format("YYYY-MM-DD");

const filters = reactive({
    fromDate: fromDate,
    toDate: toDate,
    hblType: Object.values(props.hblTypes),
});

const data = reactive({
    columnVisibility: {
        id: false,
        hbl: true,
        hbl_name: true,
        consignee_name: true,
        created_at: true,
        weight: true,
        volume: true,
        quantity: true,
        hbl_type: true,
        actions: true,
    },
});

const baseUrl = ref("/loaded-container-list");

const toggleColumnVisibility = (columnName) => {
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
                url: (prev, keyword) => `${prev}&search=${keyword}`,
            },
        },
        sort: {
            multiColumn: false,
            server: {
                url: (prev, columns) => {
                    if (!columns.length) return prev;
                    const col = columns[0];
                    const dir = col.direction === 1 ? "asc" : "desc";
                    let colName = visibleColumns[col.index];
                    return `${prev}&order=${colName}&dir=${dir}`;
                },
            },
        },
        pagination: {
            limit: 10,
            server: {
                url: (prev, page, limit) =>
                    `${prev}&limit=${limit}&offset=${page * limit}`,
            },
        },
        server: {
            url: constructUrl(),
            then: (data) =>
                data.data.map((item) => {
                    const row = [];
                    // row.push({id: item.id})
                    visibleColumns.forEach((column) => {
                        row.push(item[column]);
                    });
                    return row;
                }),
            total: (response) => {
                if (response && response.meta) {
                    return response.meta.total;
                } else {
                    throw new Error("Invalid total count in server response");
                }
            },
        },
    });

    grid.render(wrapperRef.value);
};

const createColumns = () => [
    {name: "ID", hidden: !data.columnVisibility.id},
    {name: "HBL", hidden: !data.columnVisibility.hbl},
    {name: "Name", hidden: !data.columnVisibility.hbl_name},
    {name: "Consignee Name", hidden: !data.columnVisibility.consignee_name},
    {name: "Created Date", hidden: !data.columnVisibility.created_at},
    {name: "Weight", hidden: !data.columnVisibility.weight},
    {name: "Volume", hidden: !data.columnVisibility.volume},
    {name: "Quantity", hidden: !data.columnVisibility.quantity},
    {name: "Type", hidden: !data.columnVisibility.hbl_type},
    {
        name: "Actions",
        sort: false,
        hidden: !data.columnVisibility.actions,
        formatter: (_, row) => {
            return h("div", {}, [
                h(
                    "button",
                    {
                        className:
                            "btn size-8 p-0 text-primary hover:bg-primary/20 focus:bg-primary/20 active:bg-primary/25",
                        onClick: () => confirmViewLoadedShipment(row.cells[0].data),
                        "x-tooltip..placement.bottom.primary": "'View'",
                    },
                    [
                        h(
                            "svg",
                            {
                                xmlns: "http://www.w3.org/2000/svg",
                                viewBox: "0 0 24 24",
                                class:
                                    "size-6 icon icon-tabler icons-tabler-outline icon-tabler-eye",
                                fill: "none",
                                stroke: "currentColor",
                                strokeWidth: 2,
                                strokeLinecap: "round",
                                strokeLinejoin: "round",
                            },
                            [
                                h("path", {
                                    stroke: "none",
                                    d: "M0 0h24v24H0z",
                                    fill: "none",
                                }),
                                h("path", {
                                    d: "M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0",
                                }),
                                h("path", {
                                    d: "M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6",
                                }),
                            ]
                        ),
                    ]
                ),
                h(
                    "button",
                    {
                        className:
                            "btn size-8 p-0 text-success hover:bg-success/20 focus:bg-success/20 active:bg-success/25",
                        onClick: () =>
                            router.visit(
                                route("arrival.unloading-points.index", {
                                    container: row.cells[0].data,
                                })
                            ),
                        "x-tooltip..placement.bottom.success": "'Complete Reception'",
                    },
                    [
                        h(
                            "svg",
                            {
                                xmlns: "http://www.w3.org/2000/svg",
                                viewBox: "0 0 24 24",
                                class:
                                    "size-6 icon icon-tabler icons-tabler-outline icon-tabler-wrecking-ball",
                                fill: "none",
                                stroke: "currentColor",
                                strokeWidth: 2,
                                strokeLinecap: "round",
                                strokeLinejoin: "round",
                            },
                            [
                                h("path", {
                                    stroke: "none",
                                    d: "M0 0h24v24H0z",
                                    fill: "none",
                                }),
                                h("path", {
                                    d: "M19 13m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0",
                                }),
                                h("path", {
                                    d: "M4 17m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0",
                                }),
                                h("path", {
                                    d: "M13 17m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0",
                                }),
                                h("path", {
                                    d: "M13 19l-9 0",
                                }),
                                h("path", {
                                    d: "M4 15l9 0",
                                }),
                                h("path", {
                                    d: "M8 12v-5h2a3 3 0 0 1 3 3v5",
                                }),
                                h("path", {
                                    d: "M5 15v-2a1 1 0 0 1 1 -1h7",
                                }),
                                h("path", {
                                    d: "M19 11v-7l-6 7",
                                }),
                            ]
                        ),
                    ]
                ),
                h(
                    "button",
                    {
                        className:
                            "btn size-8 p-0 text-success hover:bg-success/20 focus:bg-success/20 active:bg-success/25",
                        onClick: () =>
                            router.visit(
                                route("arrival.unloading-points.index", {
                                    container: row.cells[0].data,
                                })
                            ),
                        "x-tooltip..placement.bottom.success": "'Mark As Short Loading'",
                    },
                    [
                        h(
                            "svg",
                            {
                                xmlns: "http://www.w3.org/2000/svg",
                                viewBox: "0 0 24 24",
                                class:
                                    "size-6 icon icon-tabler icons-tabler-outline icon-tabler-wrecking-ball",
                                fill: "none",
                                stroke: "currentColor",
                                strokeWidth: 2,
                                strokeLinecap: "round",
                                strokeLinejoin: "round",
                            },
                            [
                                h("path", {
                                    stroke: "none",
                                    d: "M0 0h24v24H0z",
                                    fill: "none",
                                }),
                                h("path", {
                                    d: "M19 13m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0",
                                }),
                                h("path", {
                                    d: "M4 17m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0",
                                }),
                                h("path", {
                                    d: "M13 17m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0",
                                }),
                                h("path", {
                                    d: "M13 19l-9 0",
                                }),
                                h("path", {
                                    d: "M4 15l9 0",
                                }),
                                h("path", {
                                    d: "M8 12v-5h2a3 3 0 0 1 3 3v5",
                                }),
                                h("path", {
                                    d: "M5 15v-2a1 1 0 0 1 1 -1h7",
                                }),
                                h("path", {
                                    d: "M19 11v-7l-6 7",
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
});

const constructUrl = () => {
    const params = new URLSearchParams();
    for (const key in filters) {
        if (filters.hasOwnProperty(key)) {
            params.append(key, filters[key].toString());
        }
    }
    return baseUrl.value + "?" + params.toString();
};

const applyFilters = () => {
    showFilters.value = false;
    const newUrl = constructUrl();
    const visibleColumns = Object.keys(data.columnVisibility);
    grid.updateConfig({
        server: {
            url: newUrl,
            then: (data) =>
                data.data.map((item) => {
                    const row = [];
                    visibleColumns.forEach((column) => {
                        row.push(item[column]);
                    });
                    return row;
                }),
        },
    });
    grid.forceRender();
};

const selectedContainer = ref({});
const showConfirmLoadedShipmentModal = ref(false);

const confirmViewLoadedShipment = (id) => {
    selectedContainer.value = props.containers.find(
        (container) => container.id === id
    );
    showConfirmLoadedShipmentModal.value = true;
};

const closeModal = () => {
    showConfirmLoadedShipmentModal.value = false;
    selectedContainer.value = {};
};

const resetFilter = () => {
    filters.fromDate = fromDate;
    filters.toDate = toDate;
    filters.hblType = Object.values(props.hblTypes);
    applyFilters();
};
</script>
<template>
    <AppLayout title="Bonded Warehouse">
        <template #header>Bonded Warehouse</template>

        <Breadcrumb/>

        <div class="card mt-4">
            <div>
                <div class="flex items-center justify-between p-2">
                    <div class="">
                        <div class="flex">
                            <h2
                                class="text-base font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100"
                            >
                                Bonded Warehouse
                            </h2>
                        </div>

                        <div
                            class="flex items-center mt-2 text-sm text-slate-500 dark:text-gray-300"
                        >
                            <div
                                class="mr-4 cursor-pointer"
                                x-tooltip.info.placement.bottom="'Applied Filters'"
                            >
                                Filter Options:
                            </div>
                            <div class="flex -space-x-px">
                                <div>
                                    <div
                                        class="mb-1 tag rounded-r-none bg-slate-150 text-slate-800 hover:bg-slate-200 focus:bg-slate-200 active:bg-slate-200/80 dark:bg-navy-500 dark:text-navy-100 dark:hover:bg-navy-450 dark:focus:bg-navy-450 dark:active:bg-navy-450/90"
                                    >
                                        From Date
                                    </div>
                                    <div
                                        class="tag rounded-l-none bg-primary text-white hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:hover:bg-accent-focus dark:focus:bg-accent-focus dark:active:bg-accent/90"
                                    >
                                        {{ filters.fromDate }}
                                    </div>
                                </div>
                                <div>
                                    <div
                                        class="mb-1 ml-4 tag rounded-r-none bg-slate-150 text-slate-800 hover:bg-slate-200 focus:bg-slate-200 active:bg-slate-200/80 dark:bg-navy-500 dark:text-navy-100 dark:hover:bg-navy-450 dark:focus:bg-navy-450 dark:active:bg-navy-450/90"
                                    >
                                        To Date
                                    </div>
                                    <div
                                        class="tag rounded-l-none bg-warning text-white hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:hover:bg-accent-focus dark:focus:bg-accent-focus dark:active:bg-accent/90"
                                    >
                                        {{ filters.toDate }}
                                    </div>
                                </div>
                                <div class="grid sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6">
                                    <div
                                        v-for="(type, index) in filters.hblType"
                                        v-if="filters.hblType"
                                        :key="index"
                                        class="mb-1 badge bg-cyan-500 text-white dark:bg-cyan-900 ml-2"
                                    >
                                        {{ type }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex">
                        <ColumnVisibilityPopover>
                            <label class="inline-flex items-center space-x-2">
                                <Checkbox
                                    :checked="data.columnVisibility.maximum_volume"
                                    @change="toggleColumnVisibility('maximum_volume', $event)"
                                />
                                <span class="hover:cursor-pointer">Maximum Volume</span>
                            </label>

                            <label class="inline-flex items-center space-x-2">
                                <Checkbox
                                    :checked="data.columnVisibility.minimum_volume"
                                    @change="toggleColumnVisibility('minimum_volume', $event)"
                                />
                                <span class="hover:cursor-pointer">Minimum Volume</span>
                            </label>
                        </ColumnVisibilityPopover>

                        <button
                            class="btn size-8 rounded-full p-0 hover:bg-slate-300/20 focus:bg-slate-300/20 active:bg-slate-300/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25"
                            x-tooltip.placement.top="'Filters'"
                            @click="showFilters = true"
                        >
                            <i class="fa-solid fa-filter"></i>
                        </button>
                    </div>
                </div>

                <div class="mt-3">
                    <div class="is-scrollbar-hidden min-w-full overflow-x-auto">
                        <div ref="wrapperRef"></div>
                    </div>
                </div>
            </div>
        </div>

        <FilterDrawer :show="showFilters" @close="showFilters = false">
            <template #title> Filter Containers</template>

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

                <FilterHeader value="Cargo Types"/>

                <label
                    v-for="hblType in hblTypes"
                    :key="hblType"
                    class="inline-flex items-center space-x-2 mt-2"
                >
                    <Switch
                        v-model="filters.hblType"
                        :label="hblType"
                        :value="hblType"
                    />
                </label>

                <FilterBorder/>

                <!--Filter Now Action Button-->
                <SoftPrimaryButton class="space-x-2" @click="applyFilters">
                    <i class="fa-solid fa-filter"></i>
                    <span>Apply Filters</span>
                </SoftPrimaryButton>
                <!--Filter Rest Button-->
                <SoftPrimaryButton class="space-x-2" @click="resetFilter">
                    <i class="fa-solid fa-refresh"></i>
                    <span>Reset Filters</span>
                </SoftPrimaryButton>
            </template>
        </FilterDrawer>
    </AppLayout>
</template>
