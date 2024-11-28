<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import {computed, onMounted, reactive, ref} from "vue";
import {Grid, h, html} from "gridjs";
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
import {router, usePage} from "@inertiajs/vue3";
import ShortLoadingConfirmationModal from "@/Pages/Arrival/Partials/ShortLoadingConfirmationModal.vue";
import {push} from "notivue";
import HBLDetailModal from "@/Pages/Common/HBLDetailModal.vue";
import DestinationAppLayout from "@/Layouts/DestinationAppLayout.vue";

const props = defineProps({
    hblTypes: {
        type: Array,
        default: () => [],
    },
});

const wrapperRef = ref(null);
let grid = null;
const perPage = ref(10);
const showFilters = ref(false);
const fromDate = moment(new Date()).subtract(7, "days").format("YYYY-MM-DD");
const toDate = moment(new Date()).format("YYYY-MM-DD");

const filters = reactive({
    fromDate: fromDate,
    toDate: toDate,
    deliveryType: Object.values(props.hblTypes),
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
        is_short_load: true,
        actions: true,
    },
});

const baseUrl = ref("/bonded-warehouse-list");

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
            limit: perPage.value,
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
    {
        name: "Weight",
        hidden: !data.columnVisibility.weight,
        formatter: (_, row) => {
            return html(`<div class="flex items-center">
<svg class="icon icon-tabler icons-tabler-outline icon-tabler-weight text-info mr-2"
                         fill="none" height="24" stroke="currentColor" stroke-linecap="round"
                         stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" width="24"
                         xmlns="http://www.w3.org/2000/svg">
                        <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                        <path d="M12 6m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0"/>
                        <path
                            d="M6.835 9h10.33a1 1 0 0 1 .984 .821l1.637 9a1 1 0 0 1 -.984 1.179h-13.604a1 1 0 0 1 -.984 -1.179l1.637 -9a1 1 0 0 1 .984 -.821z"/>
                    </svg> ${row.cells[5].data}
</div>`)
        }
    },
    {
        name: "Volume",
        hidden: !data.columnVisibility.volume,
        formatter: (_, row) => {
            return html(`<div class="flex items-center">
<svg class="icon icon-tabler icons-tabler-outline icon-tabler-scale text-info mr-2"
                         fill="none"
                         height="24" stroke="currentColor" stroke-linecap="round"
                         stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" width="24"
                         xmlns="http://www.w3.org/2000/svg">
                        <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                        <path d="M7 20l10 0"/>
                        <path d="M6 6l6 -1l6 1"/>
                        <path d="M12 3l0 17"/>
                        <path d="M9 12l-3 -6l-3 6a3 3 0 0 0 6 0"/>
                        <path d="M21 12l-3 -6l-3 6a3 3 0 0 0 6 0"/>
                    </svg> ${row.cells[6].data}
</div>`)
        }
    },
    {name: "Quantity", hidden: !data.columnVisibility.quantity},
    {name: "Type", hidden: !data.columnVisibility.hbl_type},
    {
        name: "Is Short Load",
        hidden: !data.columnVisibility.is_short_load,
        formatter: (_, row) => {
            if (row.cells[9].data) {
                return html('<svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-circle-check text-error"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" /><path d="M9 12l2 2l4 -4" /></svg>')
            }
        }
    },
    {
        name: "Actions",
        sort: false,
        hidden: !data.columnVisibility.actions,
        formatter: (_, row) => {
            return h("div", {}, [
                usePage().props.user.permissions.includes('bonded.show') ?
                    h(
                        "button",
                        {
                            className:
                                "btn size-8 p-0 text-primary hover:bg-primary/20 focus:bg-primary/20 active:bg-primary/25",
                            onClick: () => confirmViewHBL(row.cells[0].data),
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
                    ) : null,
                // usePage().props.user.permissions.includes('bonded.complete registration') ?
                //     h(
                //         "button",
                //         {
                //             className:
                //                 "btn size-8 p-0 text-success hover:bg-success/20 focus:bg-success/20 active:bg-success/25",
                //             onClick: () =>
                //                 router.visit(
                //                     route("arrival.unloading-points.index", {
                //                         container: row.cells[0].data,
                //                     })
                //                 ),
                //             "x-tooltip..placement.bottom.success": "'Complete Reception'",
                //         },
                //         [
                //             h(
                //                 "svg",
                //                 {
                //                     xmlns: "http://www.w3.org/2000/svg",
                //                     viewBox: "0 0 24 24",
                //                     class:
                //                         "size-6 icon icon-tabler icons-tabler-outline icon-tabler-rosette-discount-check",
                //                     fill: "none",
                //                     stroke: "currentColor",
                //                     strokeWidth: 2,
                //                     strokeLinecap: "round",
                //                     strokeLinejoin: "round",
                //                 },
                //                 [
                //                     h("path", {
                //                         stroke: "none",
                //                         d: "M0 0h24v24H0z",
                //                         fill: "none",
                //                     }),
                //                     h("path", {
                //                         d: "M5 7.2a2.2 2.2 0 0 1 2.2 -2.2h1a2.2 2.2 0 0 0 1.55 -.64l.7 -.7a2.2 2.2 0 0 1 3.12 0l.7 .7c.412 .41 .97 .64 1.55 .64h1a2.2 2.2 0 0 1 2.2 2.2v1c0 .58 .23 1.138 .64 1.55l.7 .7a2.2 2.2 0 0 1 0 3.12l-.7 .7a2.2 2.2 0 0 0 -.64 1.55v1a2.2 2.2 0 0 1 -2.2 2.2h-1a2.2 2.2 0 0 0 -1.55 .64l-.7 .7a2.2 2.2 0 0 1 -3.12 0l-.7 -.7a2.2 2.2 0 0 0 -1.55 -.64h-1a2.2 2.2 0 0 1 -2.2 -2.2v-1a2.2 2.2 0 0 0 -.64 -1.55l-.7 -.7a2.2 2.2 0 0 1 0 -3.12l.7 -.7a2.2 2.2 0 0 0 .64 -1.55v-1",
                //                     }),
                //                     h("path", {
                //                         d: "M9 12l2 2l4 -4",
                //                     }),
                //                 ]
                //             ),
                //         ]
                //     ) : null,
                usePage().props.user.permissions.includes('bonded.mark as short loading') ?
                    row.cells[9].data === 1 ||
                    h(
                        "button",
                        {
                            className:
                                "btn size-8 p-0 text-warning hover:bg-warning/20 focus:bg-warning/20 active:bg-warning/25",
                            onClick: () => confirmShortLoading(row.cells[0].data),
                            "x-tooltip..placement.bottom.success": "'Mark As Short Loading'",
                        },
                        [
                            h(
                                "svg",
                                {
                                    xmlns: "http://www.w3.org/2000/svg",
                                    viewBox: "0 0 24 24",
                                    class:
                                        "size-6 icon icon-tabler icons-tabler-outline icon-tabler-viewport-short",
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
                                        d: "M12 3v7l3 -3",
                                    }),
                                    h("path", {
                                        d: "M9 7l3 3",
                                    }),
                                    h("path", {
                                        d: "M12 21v-7l3 3",
                                    }),
                                    h("path", {
                                        d: "M9 17l3 -3",
                                    }),
                                    h("path", {
                                        d: "M18 9h1a2 2 0 0 1 2 2v2a2 2 0 0 1 -2 2h-1",
                                    }),
                                    h("path", {
                                        d: "M6 9h-1a2 2 0 0 0 -2 2v2a2 2 0 0 0 2 2h1",
                                    }),
                                ]
                            ),
                        ]
                    ) : null,
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
            total: (response) => {
                if (response && response.meta) {
                    return response.meta.total;
                } else {
                    throw new Error("Invalid total count in server response");
                }
            },
        },
    });
    grid.forceRender();
};

const resetFilter = () => {
    filters.fromDate = fromDate;
    filters.toDate = toDate;
    filters.deliveryType = Object.values(props.hblTypes);
    applyFilters();
};

const showConfirmShortLoadingModal = ref(false);
const hblId = ref(null);

const confirmShortLoading = (id) => {
    hblId.value = id;
    showConfirmShortLoadingModal.value = true;
};

const closeShortLoadingModal = () => {
    hblId.value = null;
    showConfirmShortLoadingModal.value = false;
};

const handleMarkAsShortLoading = () => {
    router.get(route("arrival.hbls.mark-as-short-loading", hblId.value), {
        preserveScroll: true,
        onSuccess: () => {
            closeShortLoadingModal();
            push.success("Mark As a Short Loaded");
            router.visit(route("arrival.bonded-warehouses.index"));
        },
    });
}

const showConfirmViewHBLModal = ref(false);

const confirmViewHBL = async (id) => {
    hblId.value = id;
    showConfirmViewHBLModal.value = true;
};

const closeShowHBLModal = () => {
    showConfirmViewHBLModal.value = false;
};

const exportURL = computed(() => {
    const params = new URLSearchParams();
    for (const key in filters) {
        if (filters.hasOwnProperty(key)) {
            params.append(key, filters[key].toString());
        }
    }
    return '/bonded-warehouse/list/export' + "?" + params.toString();
});

const handlePerPageChange = (event) => {
    perPage.value = parseInt(event.target.value);

    grid.updateConfig({
        pagination: {
            limit: perPage.value,
            server: {
                url: (prev, page, limit) =>
                    `${prev}&limit=${limit}&offset=${page * limit}`,
            },
        },
    });

    grid.forceRender()
};
</script>
<template>
    <DestinationAppLayout title="Bonded Warehouse">
        <template #header>Bonded Warehouse</template>

        <Breadcrumb/>

        <div class="card mt-4">
            <div>
                <div class="flex items-center justify-between p-2">
                    <div class="">
                        <div class="flex items-center">
                            <h2
                                class="text-base font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100"
                            >
                                Bonded Warehouse
                            </h2>

                            <div class="flex m-3">
                                <select class="form-select w-full rounded border border-slate-300 bg-white px-8 py-1 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:bg-navy-700 dark:hover:border-navy-400 dark:focus:border-accent" @change="handlePerPageChange">
                                    <option value="10">10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                            </div>
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
                        <ColumnVisibilityPopover v-if="false">
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

                        <a :href="exportURL">
                            <button
                                class="flex btn size-8 rounded-full p-0 hover:bg-slate-300/20 focus:bg-slate-300/20 active:bg-slate-300/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25"
                                x-tooltip.placement.top="'Download CSV'"
                            >
                                <i class="fa-solid fa-cloud-arrow-down"></i>
                            </button>
                        </a>
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
                        v-model="filters.deliveryType"
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

        <ShortLoadingConfirmationModal :show="showConfirmShortLoadingModal" @close="closeShortLoadingModal"
                                       @short-loading="handleMarkAsShortLoading"/>

        <HBLDetailModal
            :hbl-id="hblId"
            :show="showConfirmViewHBLModal"
            @close="closeShowHBLModal"
        />
    </DestinationAppLayout>
</template>
