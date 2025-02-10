<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import {onMounted, reactive, ref} from "vue";
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
import {usePage} from "@inertiajs/vue3";
import DestinationAppLayout from "@/Layouts/DestinationAppLayout.vue";
import ImageViewModal from "@/Pages/Arrival/Partials/ImageView.vue";

const wrapperRef = ref(null);
let grid = null;
const perPage = ref(10);
const showFilters = ref(false);
const fromDate = moment(new Date()).subtract(30, "days").format("YYYY-MM-DD");
const toDate = moment(new Date()).format("YYYY-MM-DD");

const filters = reactive({
    fromDate: fromDate,
    toDate: toDate,
});

const data = reactive({
    columnVisibility: {
        id: false,
        hbl: true,
        branch: true,
        hbl_name: true,
        consignee_name: true,
        created_at: true,
        weight: true,
        volume: true,
        quantity: true,
        issue: true,
        rtf: true,
        is_damaged: true,
        type: true,
        is_fixed: true,
        actions: true,
    },
});

const baseUrl = ref("/unloading-issues-list");

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

const imageImageViewModal = ref(false);
const unloadingIssueID = ref(null);
const confirmViewIssue = async (id) => {
    unloadingIssueID.value = id;
    imageImageViewModal.value = true;
};
const closeShowHBLModal = () => {
    imageImageViewModal.value = false;
};
const createColumns = () => [
    {name: "ID", hidden: !data.columnVisibility.id},
    {name: "HBL", hidden: !data.columnVisibility.hbl},
    {name: "Origin", hidden: !data.columnVisibility.branch},
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
                    </svg> ${row.cells[6].data}
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
                    </svg> ${row.cells[7].data}
</div>`)
        }
    },
    {name: "Quantity", hidden: !data.columnVisibility.quantity},
    {name: "Issue", hidden: !data.columnVisibility.issue},
    {
        name: "RTF",
        hidden: !data.columnVisibility.rtf,
        formatter: (cell) => {
            if (cell === 1) {
                return html(`<div class="badge space-x-2.5 text-red-500">
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icon-tabler-check">
        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
        <path d="M5 12l5 5l10 -10" />
    </svg>
    <span></span>
</div>`);
            } else {
                return html(`<div class="badge space-x-2.5 text-warning">
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icon-tabler-x">
        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
        <line x1="18" y1="6" x2="6" y2="18" />
        <line x1="6" y1="6" x2="18" y2="18" />
    </svg>
    <span></span>
</div>
`);
            }
        },
    },

    {name: "Damaged", hidden: !data.columnVisibility.is_damaged},
    {name: "Type", hidden: !data.columnVisibility.type},
    {
        name: "Fix",
        hidden: !data.columnVisibility.is_fixed,
        formatter: (cell) => {
            if (cell === 1) {
                return html(`<div class="badge space-x-2.5 text-success">
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icon-tabler-check">
        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
        <path d="M5 12l5 5l10 -10" />
    </svg>
    <span></span>
</div>`);
            } else {
                return html(`<div class="badge space-x-2.5 text-red-500">
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icon-tabler-x">
        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
        <line x1="18" y1="6" x2="6" y2="18" />
        <line x1="6" y1="6" x2="18" y2="18" />
    </svg>
    <span></span>
</div>
`);
            }
        },
    },
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
                        onClick: () => confirmViewIssue(row.cells[0].data),
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
            then: (data) => {
                if (data.data.length === 0) {
                    // Return a placeholder row for "No matching data"
                    return [
                        visibleColumns.map(() => "No matching data found"),
                    ];
                }

                // Map the data to visible columns
                return data.data.map((item) => {
                    const row = [];
                    visibleColumns.forEach((column) => {
                        row.push(item[column]);
                    });
                    return row;
                });
            },
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
    applyFilters();
};

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
    <DestinationAppLayout v-if="usePage().props.currentBranch.type === 'Destination' && $page.props.user.roles.includes('boned area')" title="Unloading Issues">
        <template #header>Unloading Issues</template>

        <Breadcrumb/>

        <div class="card mt-4">
            <div>
                <div class="flex items-center justify-between p-2">
                    <div class="">
                        <div class="flex items-center">
                            <h2
                                class="text-base font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100"
                            >
                                Unloading Issues
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
            <template #title> Filter Unloading Issues</template>

            <template #content>

                <div class="grid grid-cols-2  space-x-2">
                    <!--Filter Rest Button-->
                    <SoftPrimaryButton class="space-x-2" @click="resetFilter">
                        <i class="fa-solid fa-refresh"></i>
                        <span>Reset</span>
                    </SoftPrimaryButton>
                    <!--Filter Now Action Button-->
                    <button class="btn border border-primary font-medium text-primary hover:bg-primary hover:text-white focus:bg-primary focus:text-white active:bg-primary/90 dark:border-accent dark:text-accent-light dark:hover:bg-accent dark:hover:text-white dark:focus:bg-accent dark:focus:text-white dark:active:bg-accent/90" @click="applyFilters">
                        <i class="fa-solid fa-filter"></i>
                        <span>Apply</span>
                    </button>
                </div>
                <div>
                    <InputLabel value="From"/>
                    <DatePicker v-model="filters.fromDate" placeholder="Choose date..."/>
                </div>

                <div>
                    <InputLabel value="To"/>
                    <DatePicker v-model="filters.toDate" placeholder="Choose date..."/>
                </div>

                <FilterBorder/>


            </template>
        </FilterDrawer>

        <ImageViewModal
            :unloadingIssueID="unloadingIssueID"
            :show="imageImageViewModal"
            @close="closeShowHBLModal"
        />
    </DestinationAppLayout>

    <AppLayout v-else title="Unloading Issues">
        <template #header>Unloading Issues</template>

        <Breadcrumb/>

        <div class="card mt-4">
            <div>
                <div class="flex items-center justify-between p-2">
                    <div class="">
                        <div class="flex items-center">
                            <h2
                                class="text-base font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100"
                            >
                                Unloading Issues
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
            <template #title> Filter Unloading Issues</template>

            <template #content>

                <div class="grid grid-cols-2  space-x-2">
                    <!--Filter Rest Button-->
                    <SoftPrimaryButton class="space-x-2" @click="resetFilter">
                        <i class="fa-solid fa-refresh"></i>
                        <span>Reset</span>
                    </SoftPrimaryButton>
                    <!--Filter Now Action Button-->
                    <button class="btn border border-primary font-medium text-primary hover:bg-primary hover:text-white focus:bg-primary focus:text-white active:bg-primary/90 dark:border-accent dark:text-accent-light dark:hover:bg-accent dark:hover:text-white dark:focus:bg-accent dark:focus:text-white dark:active:bg-accent/90" @click="applyFilters">
                        <i class="fa-solid fa-filter"></i>
                        <span>Apply</span>
                    </button>
                </div>
                <div>
                    <InputLabel value="From"/>
                    <DatePicker v-model="filters.fromDate" placeholder="Choose date..."/>
                </div>

                <div>
                    <InputLabel value="To"/>
                    <DatePicker v-model="filters.toDate" placeholder="Choose date..."/>
                </div>

                <FilterBorder/>


            </template>
        </FilterDrawer>
        <ImageViewModal
            :unloadingIssueID="unloadingIssueID"
            :show="imageImageViewModal"
            @close="closeShowHBLModal"
        />
    </AppLayout>
</template>
