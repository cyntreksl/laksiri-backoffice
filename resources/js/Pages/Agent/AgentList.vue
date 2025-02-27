<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import {computed, onMounted, reactive, ref} from "vue";
import {Grid, h, html} from "gridjs";
import Popper from "vue3-popper";
import {router, usePage} from "@inertiajs/vue3";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import {push} from "notivue";
import DeleteAgentConfirmationModal from "@/Pages/Agent/Partials/DeleteAgentConfirmationModal.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import NoRecordsFound from "@/Components/NoRecordsFound.vue";
import InputLabel from "@/Components/InputLabel.vue";
import SoftPrimaryButton from "@/Components/SoftPrimaryButton.vue";
import FilterBorder from "@/Components/FilterBorder.vue";
import FilterDrawer from "@/Components/FilterDrawer.vue";
import DatePicker from "@/Components/DatePicker.vue";
import moment from "moment";

defineProps({
    agents: {
        type: Object,
        default: () => {}
    }
});

const wrapperRef = ref(null);
let grid = null;
const isData = ref(false);

const showFilters = ref(false);
const fromDate = moment(new Date()).subtract(12, "months").format("YYYY-MM-DD");
const toDate = moment(new Date()).format("YYYY-MM-DD");

const filters = reactive({
    fromDate: fromDate,
    toDate: toDate,
    search: '',
});

const data = reactive({
    columnVisibility: {
        id: false,
        name: true,
        type: true,
        branch_code: true,
        currency_name: true,
        currency_symbol: true,
        cargo_modes: true,
        delivery_types: true,
        package_types: true,
        actions: true,
    },
});

const baseUrl = ref("/agents/list");

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
                    let colName = Object.keys(data.columnVisibility).filter(
                        (key) => data.columnVisibility[key]
                    )[col.index];

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
                    visibleColumns.forEach((column) => {
                        row.push(item[column]);
                    });
                    return row;
                }),
            total: (response) => {
                if (response && response.meta) {
                    response.meta.total > 0 ? isData.value = true : isData.value = false;
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
    {name: "ID", hidden: true},
    {name: "Name", hidden: !data.columnVisibility.name},
    {name: "Type", hidden: !data.columnVisibility.type},
    {name: "Branch Code", hidden: !data.columnVisibility.branch_code},
    {name: "Currency Name", hidden: !data.columnVisibility.currency_name},
    {name: "Currency Symbol", hidden: !data.columnVisibility.currency_symbol},
    {
        name: "Cargo Modes",
        hidden: !data.columnVisibility.cargo_modes,
        formatter: (cell) => {
            if (!cell) return '';
            try {
                const modes = JSON.parse(cell);
                return html(
                    modes.map(mode =>
                        `<span class="badge bg-info/10 text-info dark:bg-info/15 ml-2">${mode}</span>`
                    ).join('')
                );
            } catch (e) {
                return cell;
            }
        },
        sort: false
    },
    {
        name: "Delivery Types",
        hidden: !data.columnVisibility.delivery_types,
        formatter: (cell) => {
            if (!cell) return '';
            try {
                const types = JSON.parse(cell);
                return html(
                    types.map(type =>
                        `<span class="badge bg-success/10 text-success dark:bg-success/15 ml-2">${type}</span>`
                    ).join('')
                );
            } catch (e) {
                return cell;
            }
        },
        sort: false
    },
    {
        name: "Package Types",
        hidden: !data.columnVisibility.package_types,
        formatter: (cell) => {
            if (!cell) return '';
            try {
                const types = JSON.parse(cell);
                return html(
                    types.map(type =>
                        `<span class="badge bg-warning/10 text-warning dark:bg-warning/15 ml-2">${type}</span>`
                    ).join('')
                );
            } catch (e) {
                return cell;
            }
        },
        sort: false
    },
    {
        name: "Actions",
        sort: false,
        hidden: !data.columnVisibility.actions,
        formatter: (_, row) => {
            return h("div", {
                className: "flex space-x-2"
            }, [
                h(
                    "a",
                    {
                        className: "btn size-8 p-0 text-info hover:bg-info/20 focus:bg-info/20 active:bg-info/25",
                        href: route("agents.edit", row.cells[0].data),
                    },
                    [
                        h(
                            "svg",
                            {
                                xmlns: "http://www.w3.org/2000/svg",
                                viewBox: "0 0 24 24",
                                class: "size-4.5",
                                fill: "none",
                                stroke: "currentColor",
                            },
                            [
                                h("path", {
                                    d: "M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z",
                                    "stroke-linecap": "round",
                                    "stroke-linejoin": "round",
                                    "stroke-width": "1.5"
                                }),
                            ]
                        ),
                    ]
                ),
                h(
                    "button",
                    {
                        className: "btn size-8 p-0 text-error hover:bg-error/20 focus:bg-error/20 active:bg-error/25",
                        onClick: () => confirmDeleteAgent(row.cells[0].data),
                    },
                    [
                        h(
                            "svg",
                            {
                                xmlns: "http://www.w3.org/2000/svg",
                                viewBox: "0 0 24 24",
                                class: "size-4.5",
                                fill: "currentColor",
                            },
                            [
                                h("path", {
                                    d: "M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z",
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

const showDeleteAgentConfirmationModal = ref(false);
const AgentId = ref(null);

const confirmDeleteAgent = (id) => {
    AgentId.value = id;
    showDeleteAgentConfirmationModal.value = true;
};

const closeModal = () => {
    showDeleteAgentConfirmationModal.value = false;
    AgentId.value = null;
};

const handleDeleteAgent = () => {
    router.delete(route("agents.destroy", AgentId.value), {
        preserveScroll: true,
        onSuccess: () => {
            closeModal();
            push.success("Agent Deleted Successfully!");
            router.visit(route("agents.index"));
        },
    });
};

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
                    response.meta.total > 0 ? isData.value = true : isData.value = false;
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
    filters.search = '';
    applyFilters();
};

const exportURL = computed(() => {
    const params = new URLSearchParams();
    for (const key in filters) {
        if (filters.hasOwnProperty(key)) {
            params.append(key, filters[key].toString());
        }
    }
    return '/agents/list/export' + "?" + params.toString();
});
</script>

<template>
    <AppLayout title="Agents">
        <template #header>Agents</template>

        <Breadcrumb/>

        <div class="card mt-4">
            <div>
                <div class="flex items-center justify-between p-2">
                    <h2 class="text-base font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100">
                        Agent Management
                    </h2>

                    <div class="flex">
                        <Popper>
                            <button class="btn size-8 rounded-full p-0 hover:bg-slate-300/20 focus:bg-slate-300/20 active:bg-slate-300/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25">
                                <i class="fa-solid fa-grip"></i>
                            </button>
                            <template #content>
                                <div class="max-w-[16rem]">
                                    <div class="popper-box w-64 rounded-lg border border-slate-150 bg-white shadow-soft dark:border-navy-600 dark:bg-navy-700">
                                        <div class="rounded-md border border-slate-150 bg-white p-4 dark:border-navy-600 dark:bg-navy-700">
                                            <h3 class="text-base font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100">
                                                Select Columns
                                            </h3>
                                            <p class="mt-1 text-xs+">
                                                Choose which columns you want to see
                                            </p>
                                            <div class="mt-4 flex flex-col space-y-4 text-slate-600 dark:text-navy-100">

                                                <label class="inline-flex items-center space-x-2">
                                                    <input
                                                        :checked="data.columnVisibility.name"
                                                        class="form-checkbox is-basic size-5 rounded border-slate-400/70 checked:border-primary checked:bg-primary hover:border-primary focus:border-primary dark:border-navy-400 dark:checked:border-accent dark:checked:bg-accent dark:hover:border-accent dark:focus:border-accent"
                                                        type="checkbox"
                                                        @change="toggleColumnVisibility('name')"
                                                    />
                                                    <p>Name</p>
                                                </label>

                                                <label class="inline-flex items-center space-x-2">
                                                    <input
                                                        :checked="data.columnVisibility.type"
                                                        class="form-checkbox is-basic size-5 rounded border-slate-400/70 checked:border-primary checked:bg-primary hover:border-primary focus:border-primary dark:border-navy-400 dark:checked:border-accent dark:checked:bg-accent dark:hover:border-accent dark:focus:border-accent"
                                                        type="checkbox"
                                                        @change="toggleColumnVisibility('type')"
                                                    />
                                                    <p>Type</p>
                                                </label>

                                                <label class="inline-flex items-center space-x-2">
                                                    <input
                                                        :checked="data.columnVisibility.branch_code"
                                                        class="form-checkbox is-basic size-5 rounded border-slate-400/70 checked:border-primary checked:bg-primary hover:border-primary focus:border-primary dark:border-navy-400 dark:checked:border-accent dark:checked:bg-accent dark:hover:border-accent dark:focus:border-accent"
                                                        type="checkbox"
                                                        @change="toggleColumnVisibility('branch_code')"
                                                    />
                                                    <p>Branch Code</p>
                                                </label>

                                                <label class="inline-flex items-center space-x-2">
                                                    <input
                                                        :checked="data.columnVisibility.currency_name"
                                                        class="form-checkbox is-basic size-5 rounded border-slate-400/70 checked:border-primary checked:bg-primary hover:border-primary focus:border-primary dark:border-navy-400 dark:checked:border-accent dark:checked:bg-accent dark:hover:border-accent dark:focus:border-accent"
                                                        type="checkbox"
                                                        @change="toggleColumnVisibility('currency_name')"
                                                    />
                                                    <p>Currency Name</p>
                                                </label>

                                                <label class="inline-flex items-center space-x-2">
                                                    <input
                                                        :checked="data.columnVisibility.currency_symbol"
                                                        class="form-checkbox is-basic size-5 rounded border-slate-400/70 checked:border-primary checked:bg-primary hover:border-primary focus:border-primary dark:border-navy-400 dark:checked:border-accent dark:checked:bg-accent dark:hover:border-accent dark:focus:border-accent"
                                                        type="checkbox"
                                                        @change="toggleColumnVisibility('currency_symbol')"
                                                    />
                                                    <p>Currency Symbol</p>
                                                </label>

                                                <label class="inline-flex items-center space-x-2">
                                                    <input
                                                        :checked="data.columnVisibility.cargo_modes"
                                                        class="form-checkbox is-basic size-5 rounded border-slate-400/70 checked:border-primary checked:bg-primary hover:border-primary focus:border-primary dark:border-navy-400 dark:checked:border-accent dark:checked:bg-accent dark:hover:border-accent dark:focus:border-accent"
                                                        type="checkbox"
                                                        @change="toggleColumnVisibility('cargo_modes')"
                                                    />
                                                    <p>Cargo Modes</p>
                                                </label>

                                                <label class="inline-flex items-center space-x-2">
                                                    <input
                                                        :checked="data.columnVisibility.delivery_types"
                                                        class="form-checkbox is-basic size-5 rounded border-slate-400/70 checked:border-primary checked:bg-primary hover:border-primary focus:border-primary dark:border-navy-400 dark:checked:border-accent dark:checked:bg-accent dark:hover:border-accent dark:focus:border-accent"
                                                        type="checkbox"
                                                        @change="toggleColumnVisibility('delivery_types')"
                                                    />
                                                    <p>Delivery Types</p>
                                                </label>

                                                <label class="inline-flex items-center space-x-2">
                                                    <input
                                                        :checked="data.columnVisibility.package_types"
                                                        class="form-checkbox is-basic size-5 rounded border-slate-400/70 checked:border-primary checked:bg-primary hover:border-primary focus:border-primary dark:border-navy-400 dark:checked:border-accent dark:checked:bg-accent dark:hover:border-accent dark:focus:border-accent"
                                                        type="checkbox"
                                                        @change="toggleColumnVisibility('package_types')"
                                                    />
                                                    <p>Package Types</p>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </Popper>

                        <a :href="route('agents.create')" class="ml-2">
                            <PrimaryButton>
                                Create New Agent
                            </PrimaryButton>
                        </a>
                    </div>
                </div>

                <div class="mt-3">
                    <div class="is-scrollbar-hidden min-w-full overflow-x-auto">
                        <div v-show="isData" ref="wrapperRef"></div>
                        <NoRecordsFound v-show="!isData"/>
                    </div>
                </div>
            </div>
        </div>

        <DeleteAgentConfirmationModal
            :show="showDeleteAgentConfirmationModal"
            @close="closeModal"
            @deleteAgent="handleDeleteAgent"
        />

        <FilterDrawer :show="showFilters" @close="showFilters = false">
            <template #title>Filter Agents</template>

            <template #content>
                <div class="grid grid-cols-2 space-x-2">
                    <!--Filter Reset Button-->
                    <SoftPrimaryButton class="space-x-2" @click="resetFilter">
                        <i class="fa-solid fa-refresh"></i>
                        <span>Reset</span>
                    </SoftPrimaryButton>
                    <!--Filter Now Action Button-->
                    <button
                        class="btn border border-primary font-medium text-primary hover:bg-primary hover:text-white focus:bg-primary focus:text-white active:bg-primary/90 dark:border-accent dark:text-accent-light dark:hover:bg-accent dark:hover:text-white dark:focus:bg-accent dark:focus:text-white dark:active:bg-accent/90"
                        @click="applyFilters"
                    >
                        <i class="fa-solid fa-filter"></i>
                        <span>Apply</span>
                    </button>
                </div>

                <div>
                    <InputLabel value="Search"/>
                    <input
                        v-model="filters.search"
                        type="text"
                        placeholder="Search..."
                        class="form-input w-full rounded-lg border border-slate-300 bg-slate-50 py-2 px-3 placeholder:text-slate-400 focus:border-primary focus:outline-none focus:ring focus:ring-primary/50 dark:border-navy-500 dark:bg-navy-700 dark:placeholder:text-navy-300"
                    />
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
    </AppLayout>
</template>
