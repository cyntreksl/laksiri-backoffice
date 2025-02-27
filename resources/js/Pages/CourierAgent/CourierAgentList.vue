<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import {Link, router} from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import {push} from "notivue";
import {computed, onMounted, reactive, ref} from "vue";
import DeleteCourierAgentConfirmationModal from "@/Pages/CourierAgent/Partials/DeleteCourierAgentConfirmationModal.vue";
import {Grid, h, html} from "gridjs";
import Popper from "vue3-popper";
import NoRecordsFound from "@/Components/NoRecordsFound.vue";
import InputLabel from "@/Components/InputLabel.vue";
import SoftPrimaryButton from "@/Components/SoftPrimaryButton.vue";
import FilterBorder from "@/Components/FilterBorder.vue";
import FilterDrawer from "@/Components/FilterDrawer.vue";
import DatePicker from "@/Components/DatePicker.vue";
import moment from "moment";

const props = defineProps({
    courierAgents: {
        type: Object,
        default: () => {}
    }
});

// Grid setup
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
        id: true,
        company_name: true,
        website: true,
        contact_number_1: true,
        email: true,
        address: true,
        actions: true,
    },
});

const baseUrl = ref("courier-agents/list");

const toggleColumnVisibility = (columnName) => {
    data.columnVisibility[columnName] = !data.columnVisibility[columnName];
    updateGridConfig();
    grid.forceRender();
};

const initializeGrid = () => {
    const visibleColumns = Object.keys(data.columnVisibility).filter(
        (key) => data.columnVisibility[key] && key !== 'actions'
    );

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
                    visibleColumns.forEach((column) => {
                        row.push(item[column]);
                    });
                    // Add the actions column with the ID for reference
                    if (data?.columnVisibility?.actions) {
                        row.push(item.id);
                    }

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
    {name: "ID", hidden: !data.columnVisibility.id},
    {name: "Company Name", hidden: !data.columnVisibility.company_name},
    {name: "Website", hidden: !data.columnVisibility.website},
    {name: "Contact Number", hidden: !data.columnVisibility.contact_number_1},
    {name: "Email", hidden: !data.columnVisibility.email},
    {name: "Address", hidden: !data.columnVisibility.address},
    {
        name: "Actions",
        sort: false,
        hidden: !data.columnVisibility.actions,
        formatter: (id) => {
            return h("div", {
                className: "flex space-x-2"
            }, [
                h(
                    "a",
                    {
                        className: "btn size-8 p-0 text-info hover:bg-info/20 focus:bg-info/20 active:bg-info/25",
                        href: route("courier-agents.edit", id),
                    },
                    [
                        h(
                            "svg",
                            {
                                xmlns: "http://www.w3.org/2000/svg",
                                viewBox: "0 0 512 512",
                                class: "size-4.5",
                                fill: "currentColor",
                            },
                            [
                                h("path", {
                                    d: "M471 58.9L453.1 71c9.4 9.4 9.4 24.6 0 33.9L424 134.1 377.9 88 407 58.9c9.4-9.4 24.6-9.4 33.9 0zM209.8 256.2L344 121.9 390.1 168 255.8 302.2c-2.9 2.9-6.5 5-10.4 6.1l-58.5 16.7 16.7-58.5c1.1-3.9 3.2-7.5 6.1-10.4zM373.1 25L175.8 222.2c-8.7 8.7-15 19.4-18.3 31.1l-28.6 100c-2.4 8.4-.1 17.4 6.1 23.6s15.2 8.5 23.6 6.1l100-28.6c11.8-3.4 22.5-9.7 31.1-18.3L487 138.9c28.1-28.1 28.1-73.7 0-101.8L474.9 25C446.8-3.1 401.2-3.1 373.1 25zM88 64C39.4 64 0 103.4 0 152V424c0 48.6 39.4 88 88 88H360c48.6 0 88-39.4 88-88V312c0-13.3-10.7-24-24-24s-24 10.7-24 24V424c0 22.1-17.9 40-40 40H88c-22.1 0-40-17.9-40-40V152c0-22.1 17.9-40 40-40H200c13.3 0 24-10.7 24-24s-10.7-24-24-24H88z",
                                }),
                            ]
                        ),
                    ]
                ),
                h(
                    "button",
                    {
                        className: "btn size-8 p-0 text-error hover:bg-error/20 focus:bg-error/20 active:bg-error/25",
                        onClick: () => confirmDeleteAgent(id),
                    },
                    [
                        h(
                            "svg",
                            {
                                xmlns: "http://www.w3.org/2000/svg",
                                viewBox: "0 0 448 512",
                                class: "size-4.5",
                                fill: "currentColor",
                            },
                            [
                                h("path", {
                                    d: "M170.5 51.6L151.5 80h145l-19-28.4c-1.5-2.2-4-3.6-6.7-3.6H177.1c-2.7 0-5.2 1.3-6.7 3.6zm147-26.6L354.2 80H368h48 8c13.3 0 24 10.7 24 24s-10.7 24-24 24h-8V432c0 44.2-35.8 80-80 80H112c-44.2 0-80-35.8-80-80V128H24c-13.3 0-24-10.7-24-24S10.7 80 24 80h8H80 93.8l36.7-55.1C140.9 9.4 158.4 0 177.1 0h93.7c18.7 0 36.2 9.4 46.6 24.9zM80 128V432c0 17.7 14.3 32 32 32H336c17.7 0 32-14.3 32-32V128H80zm80 64V400c0 8.8-7.2 16-16 16s-16-7.2-16-16V192c0-8.8 7.2-16 16-16s16 7.2 16 16zm80 0V400c0 8.8-7.2 16-16 16s-16-7.2-16-16V192c0-8.8 7.2-16 16-16s16 7.2 16 16zm80 0V400c0 8.8-7.2 16-16 16s-16-7.2-16-16V192c0-8.8 7.2-16 16-16s16 7.2 16 16z",
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

// Delete functionality
const showDeleteCourierAgentConfirmationModal = ref(false);
const CourierAgentId = ref(null);

const confirmDeleteAgent = (id) => {
    CourierAgentId.value = id;
    showDeleteCourierAgentConfirmationModal.value = true;
};

const closeModal = () => {
    showDeleteCourierAgentConfirmationModal.value = false;
    CourierAgentId.value = null;
};

const handleDeleteCourierAgent = () => {
    router.delete(route("courier-agents.destroy", CourierAgentId.value), {
        preserveScroll: true,
        onSuccess: () => {
            closeModal();
            push.success("Courier Agent Deleted Successfully!");
            router.visit(route("courier-agents.index"));
        },
    });
};

// Filter functionality
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
    const visibleColumns = Object.keys(data.columnVisibility).filter(
        (key) => data.columnVisibility[key] && key !== 'actions'
    );

    grid.updateConfig({
        server: {
            url: newUrl,
            then: (data) => {
                if (data.data.length === 0) {
                    // Return a placeholder row for "No matching data"
                    return [
                        [...visibleColumns.map(() => "No matching data found"), ""]
                    ];
                }

                // Map the data to visible columns
                return data.data.map((item) => {
                    const row = [];
                    visibleColumns.forEach((column) => {
                        row.push(item[column]);
                    });

                    // Add the actions column with the ID for reference
                    if (data.columnVisibility.actions) {
                        row.push(item.id);
                    }

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
    return '/courier-agents/list/export' + "?" + params.toString();
});
</script>

<template>
    <AppLayout title="Courier Agents">
        <template #header> Courier Agents</template>

        <Breadcrumb/>

        <div class="card mt-4">
            <div>
                <div class="flex items-center justify-between p-2">
                    <h2 class="text-base font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100">
                        Courier Agent Management
                    </h2>

                    <div class="flex">
                        <!-- Filter Button -->
                        <button
                            class="btn size-8 rounded-full p-0 hover:bg-slate-300/20 focus:bg-slate-300/20 active:bg-slate-300/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25"
                            @click="showFilters = true"
                        >
                            <i class="fa-solid fa-filter"></i>
                        </button>

                        <!-- Export Button -->
                        <a
                            :href="exportURL"
                            class="btn size-8 ml-2 rounded-full p-0 hover:bg-slate-300/20 focus:bg-slate-300/20 active:bg-slate-300/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25"
                        >
                            <i class="fa-solid fa-download"></i>
                        </a>

                        <!-- Column Selector -->
                        <Popper>
                            <button class="btn size-8 ml-2 rounded-full p-0 hover:bg-slate-300/20 focus:bg-slate-300/20 active:bg-slate-300/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25">
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
                                                        :checked="data.columnVisibility.company_name"
                                                        class="form-checkbox is-basic size-5 rounded border-slate-400/70 checked:border-primary checked:bg-primary hover:border-primary focus:border-primary dark:border-navy-400 dark:checked:border-accent dark:checked:bg-accent dark:hover:border-accent dark:focus:border-accent"
                                                        type="checkbox"
                                                        @change="toggleColumnVisibility('company_name')"
                                                    />
                                                    <p>Company Name</p>
                                                </label>

                                                <label class="inline-flex items-center space-x-2">
                                                    <input
                                                        :checked="data.columnVisibility.website"
                                                        class="form-checkbox is-basic size-5 rounded border-slate-400/70 checked:border-primary checked:bg-primary hover:border-primary focus:border-primary dark:border-navy-400 dark:checked:border-accent dark:checked:bg-accent dark:hover:border-accent dark:focus:border-accent"
                                                        type="checkbox"
                                                        @change="toggleColumnVisibility('website')"
                                                    />
                                                    <p>Website</p>
                                                </label>

                                                <label class="inline-flex items-center space-x-2">
                                                    <input
                                                        :checked="data.columnVisibility.contact_number_1"
                                                        class="form-checkbox is-basic size-5 rounded border-slate-400/70 checked:border-primary checked:bg-primary hover:border-primary focus:border-primary dark:border-navy-400 dark:checked:border-accent dark:checked:bg-accent dark:hover:border-accent dark:focus:border-accent"
                                                        type="checkbox"
                                                        @change="toggleColumnVisibility('contact_number_1')"
                                                    />
                                                    <p>Contact Number</p>
                                                </label>

                                                <label class="inline-flex items-center space-x-2">
                                                    <input
                                                        :checked="data.columnVisibility.email"
                                                        class="form-checkbox is-basic size-5 rounded border-slate-400/70 checked:border-primary checked:bg-primary hover:border-primary focus:border-primary dark:border-navy-400 dark:checked:border-accent dark:checked:bg-accent dark:hover:border-accent dark:focus:border-accent"
                                                        type="checkbox"
                                                        @change="toggleColumnVisibility('email')"
                                                    />
                                                    <p>Email</p>
                                                </label>

                                                <label class="inline-flex items-center space-x-2">
                                                    <input
                                                        :checked="data.columnVisibility.address"
                                                        class="form-checkbox is-basic size-5 rounded border-slate-400/70 checked:border-primary checked:bg-primary hover:border-primary focus:border-primary dark:border-navy-400 dark:checked:border-accent dark:checked:bg-accent dark:hover:border-accent dark:focus:border-accent"
                                                        type="checkbox"
                                                        @change="toggleColumnVisibility('address')"
                                                    />
                                                    <p>Address</p>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </Popper>

                        <Link :href="route('courier-agents.create')" class="ml-2">
                            <PrimaryButton>
                                Create New Courier Agent
                            </PrimaryButton>
                        </Link>
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

        <DeleteCourierAgentConfirmationModal
            :show="showDeleteCourierAgentConfirmationModal"
            @close="closeModal"
            @deleteCourierAgent="handleDeleteCourierAgent"
        />

        <FilterDrawer :show="showFilters" @close="showFilters = false">
            <template #title>Filter Courier Agents</template>

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
