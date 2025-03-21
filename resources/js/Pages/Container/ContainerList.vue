<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import {onMounted, ref, watch} from "vue";
import {Link, router, usePage} from "@inertiajs/vue3";
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
import PrimaryButton from "@/Components/PrimaryButton.vue";
import RadioButton from "@/Components/RadioButton.vue";
import NoRecordsFound from "@/Components/NoRecordsFound.vue";
import Card from "primevue/card";
import FloatLabel from "primevue/floatlabel";
import DataTable from "primevue/datatable";
import ContextMenu from "primevue/contextmenu";
import InputIcon from "primevue/inputicon";
import InputText from "primevue/inputtext";
import Column from "primevue/column";
import Tag from "primevue/tag";
import Panel from "primevue/panel";
import Button from "primevue/button";
import IconField from "primevue/iconfield";
import Select from "primevue/select";
import {useConfirm} from "primevue/useconfirm";
import {FilterMatchMode} from "@primevue/core/api";
import axios from "axios";
import {debounce} from "lodash";
import {push} from "notivue";

const props = defineProps({
    cargoTypes: {
        type: Object,
        default: () => {
        },
    },
    containerTypes: {
        type: Object,
        default: () => {
        },
    },
});

const baseUrl = ref("/container-list");
const loading = ref(true);
const containers = ref([]);
const totalRecords = ref(0);
const perPage = ref(10);
const currentPage = ref(1);
const cm = ref();
const selectedContainer = ref([]);
const selectedContainerID = ref(null);
const confirm = useConfirm();
const dt = ref();
const cargoTypes = ref(['Sea Cargo', 'Air Cargo']);
const fromDate = ref(moment(new Date()).subtract(7, "days").toISOString().split("T")[0]);
const toDate = ref(moment(new Date()).toISOString().split("T")[0]);

const filters = ref({
    global: {value: null, matchMode: FilterMatchMode.CONTAINS},
    cargo_type: {value: null, matchMode: FilterMatchMode.EQUALS},
});

const menuModel = ref([
    {
        label: 'Edit',
        icon: 'pi pi-fw pi-pencil',
        command: () => router.visit(route("containers.edit", selectedContainer.value.id)),
        disabled: !usePage().props.user.permissions.includes('containers.edit'),
    },
    {
        label: 'Delete',
        icon: 'pi pi-fw pi-times',
        command: () => confirmPickupDelete(selectedContainer),
        disabled: !usePage().props.user.permissions.includes('containers.delete'),
    },
]);

const fetchContainers = async (page = 1, search = "", sortField = 'created_at', sortOrder = 0) => {
    loading.value = true;
    try {
        const response = await axios.get(baseUrl.value, {
            params: {
                page,
                per_page: perPage.value,
                search,
                cargoMode: filters.value.cargo_type.value || "",
                sort_field: sortField,
                sort_order: sortOrder === 1 ? "asc" : "desc",
                fromDate: moment(fromDate.value).format("YYYY-MM-DD"),
                toDate: moment(toDate.value).format("YYYY-MM-DD"),
            }
        });
        containers.value = response.data.data;
        totalRecords.value = response.data.meta.total; // Correct total count
        currentPage.value = response.data.meta.current_page; // Correct current page
    } catch (error) {
        console.error("Error fetching Pickups:", error);
    } finally {
        loading.value = false;
    }
};

const debouncedFetchPickups = debounce((searchValue) => {
    fetchContainers(1, searchValue);
}, 1000);

watch(() => filters.value.global.value, (newValue) => {
    if (newValue !== null) {
        debouncedFetchPickups(newValue);
    }
});

watch(() => filters.value.cargo_type.value, (newValue) => {
    fetchContainers(1, filters.value.global.value);
});

watch(() => fromDate.value, (newValue) => {
    fetchContainers(1, filters.value.global.value);
});

watch(() => toDate.value, (newValue) => {
    fetchContainers(1, filters.value.global.value);
});

const onPageChange = (event) => {
    currentPage.value = event.page + 1;
    fetchContainers(currentPage.value);
};

const onSort = (event) => {
    fetchContainers(currentPage.value, filters.value.global.value, event.sortField, event.sortOrder);
};

onMounted(() => {
    fetchContainers();
});

const onRowContextMenu = (event) => {
    cm.value.show(event.originalEvent);
};

const clearFilter = () => {
    filters.value = {
        global: {value: null, matchMode: FilterMatchMode.CONTAINS},
        cargo_type: {value: null, matchMode: FilterMatchMode.EQUALS},
    };
    fetchContainers(currentPage.value);
};

const resolveCargoType = (container) => {
    switch (container.cargo_type) {
        case 'Sea Cargo':
            return {
                icon: "ti ti-sailboat",
                color: "success",
            };
        case 'Air Cargo':
            return {
                icon: "ti ti-plane-tilt",
                color: "info",
            };
        default:
            return null;
    }
};

const resolveContainerType = (container) => {
    switch (container.container_type) {
        case '20FT General':
            return 'help';
        case '20FT High Cube':
            return 'warn';
        case '40FT General':
            return 'info';
        case '40FT High Cube':
            return 'primary';
        case 'Custom':
            return 'secondary';
        default:
            return 'danger';
    }
};

const resolveContainerStatus = (container) => {
    switch (container.status) {
        case 'LOADED':
            return {
                icon: "ti ti-package",
                color: "success",
            };
        case 'Container Ordered':
            return {
                icon: "ti ti-clock-play",
                color: "info",
            };
        case 'IN TRANSIT':
            return {
                icon: "ti ti-tir",
                color: "info",
            };
        case 'UNLOADED':
            return {
                icon: "ti ti-package-off",
                color: "info",
            };
        case 'REACHED DESTINATION':
            return {
                icon: "ti ti-checks",
                color: "info",
            };
        default:
            return null;
    }
};

const exportCSV = () => {
    dt.value.exportCSV();
};

const confirmPickupDelete = (pickup) => {
    selectedContainerID.value = pickup.value.id;
    confirm.require({
        message: 'Would you like to delete this pickup record?',
        header: 'Delete Pickup?',
        icon: 'pi pi-info-circle',
        rejectLabel: 'Cancel',
        rejectProps: {
            label: 'Cancel',
            severity: 'secondary',
            outlined: true
        },
        acceptProps: {
            label: 'Delete',
            severity: 'danger'
        },
        accept: () => {
            router.delete(route("containers.destroy", selectedContainerID.value), {
                preserveScroll: true,
                onSuccess: () => {
                    push.success("Pickup record Deleted Successfully!");
                    router.visit(route("containers.index"), {only: ["containers"]});
                },
                onError: () => {
                    push.error("Something went to wrong!");
                },
            });
            selectedContainerID.value = null;
        },
        reject: () => {
            selectedContainerID.value = null;
        }
    });
};
</script>
<template>
    <AppLayout v-if="$page.props.currentBranch.type === 'Destination' && $page.props.user.roles.includes('boned area')" title="Containers">
        <template #header>Containers</template>

        <Breadcrumb/>

        <div class="flex justify-end mt-4">
            <Link v-if="$page.props.user.permissions.includes('container.create')"
                  :href="route('loading.loading-containers.create')">
                <PrimaryButton> Create New Shipment</PrimaryButton>
            </Link>
        </div>

        <div class="card mt-4">
            <div>
                <div class="flex items-center justify-between p-2">
                    <div class="">
                        <div class="flex items-center">
                            <h2
                                class="text-base font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100"
                            >
                                Container List
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
                        <br/>
                        <div
                            class="mr-4 cursor-pointer"
                            x-tooltip.info.placement.bottom="'Applied Filters'"
                        >
                            Filter Options:
                        </div>

                        <div
                            class="grid sm:grid-cols-1 md:grid-cols-1 lg:grid-cols-1 items-center mt-2 text-sm text-slate-500 dark:text-gray-300"
                        >
                            <div class="flex -space-x-px">
                                <div>
                                    <div
                                        class="mb-1 badge bg-slate-150 text-slate-800 hover:bg-slate-200 focus:bg-slate-200 active:bg-slate-200/80 dark:bg-navy-500 dark:text-navy-100 dark:hover:bg-navy-450 dark:focus:bg-navy-450 dark:active:bg-navy-450/90"
                                    >
                                        <i class="mr-1 fas fa-calendar-alt"></i>
                                        From Date
                                    </div>
                                    <div
                                        class="badge bg-primary text-white hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:hover:bg-accent-focus dark:focus:bg-accent-focus dark:active:bg-accent/90"
                                    >
                                        {{ filters.fromDate }}
                                    </div>
                                </div>
                                <div>
                                    <div
                                        class="mb-1 ml-2 badge bg-slate-150 text-slate-800 hover:bg-slate-200 focus:bg-slate-200 active:bg-slate-200/80 dark:bg-navy-500 dark:text-navy-100 dark:hover:bg-navy-450 dark:focus:bg-navy-450 dark:active:bg-navy-450/90"
                                    >
                                        <i class="mr-1 far fa-calendar-alt"></i>
                                        To &nbsp;Date
                                    </div>
                                    <div
                                        class="badge bg-warning text-white hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:hover:bg-accent-focus dark:focus:bg-accent-focus dark:active:bg-accent/90"
                                    >
                                        {{ filters.toDate }}
                                    </div>
                                </div>
                                <div class="grid sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6">
                                    <div
                                        v-for="(mode, index) in filters.cargoType"
                                        v-if="filters.cargoType"
                                        :key="index"
                                        class="mb-1 badge bg-navy-700 text-white dark:bg-navy-900 ml-2"
                                    >
                    <span v-if="mode == 'Sea Cargo'">
                      <div v-html="shipIcon"></div>
                    </span>
                                        <span v-if="mode == 'Air Cargo'">
                      <div v-html="planeIcon"></div>
                    </span>
                                        {{ mode }}
                                    </div>

                                    <div
                                        v-for="(mode, index) in filters.containerType"
                                        v-if="filters.containerType"
                                        :key="index"
                                        class="mb-1 badge bg-cyan-500 text-white dark:bg-cyan-900 ml-2"
                                    >
                                        {{ mode }}
                                    </div>

                                    <div
                                        v-if="filters.status"
                                        class="mb-1 badge bg-success text-white ml-2"
                                    >
                                        {{ filters.status }}
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

                            <label class="inline-flex items-center space-x-2">
                                <Checkbox
                                    :checked="data.columnVisibility.maximum_weight"
                                    @change="toggleColumnVisibility('maximum_weight', $event)"
                                />
                                <span class="hover:cursor-pointer">Maximum Weight</span>
                            </label>

                            <label class="inline-flex items-center space-x-2">
                                <Checkbox
                                    :checked="data.columnVisibility.minimum_weight"
                                    @change="toggleColumnVisibility('minimum_weight', $event)"
                                />
                                <span class="hover:cursor-pointer">Minimum Weight</span>
                            </label>

                            <label class="inline-flex items-center space-x-2">
                                <Checkbox
                                    :checked="data.columnVisibility.maximum_volumetric_weight"
                                    @change="
                    toggleColumnVisibility('maximum_volumetric_weight', $event)
                  "
                                />
                                <span class="hover:cursor-pointer"
                                >Maximum Volumetric Weight</span
                                >
                            </label>

                            <label class="inline-flex items-center space-x-2">
                                <Checkbox
                                    :checked="data.columnVisibility.minimum_volumetric_weight"
                                    @change="
                    toggleColumnVisibility('minimum_volumetric_weight', $event)
                  "
                                />
                                <span class="hover:cursor-pointer"
                                >Minimum Volumetric Weight</span
                                >
                            </label>

                            <label class="inline-flex items-center space-x-2">
                                <Checkbox
                                    :checked="data.columnVisibility.vessel_name"
                                    @change="toggleColumnVisibility('vessel_name', $event)"
                                />
                                <span class="hover:cursor-pointer">Vessel Name</span>
                            </label>

                            <label class="inline-flex items-center space-x-2">
                                <Checkbox
                                    :checked="data.columnVisibility.voyage_number"
                                    @change="toggleColumnVisibility('voyage_number', $event)"
                                />
                                <span class="hover:cursor-pointer">Voyager Number</span>
                            </label>

                            <label class="inline-flex items-center space-x-2">
                                <Checkbox
                                    :checked="data.columnVisibility.shipping_line"
                                    @change="toggleColumnVisibility('shipping_line', $event)"
                                />
                                <span class="hover:cursor-pointer">Shipping Line</span>
                            </label>

                            <label class="inline-flex items-center space-x-2">
                                <Checkbox
                                    :checked="data.columnVisibility.port_of_loading"
                                    @change="toggleColumnVisibility('port_of_loading', $event)"
                                />
                                <span class="hover:cursor-pointer">Loading Port</span>
                            </label>

                            <label class="inline-flex items-center space-x-2">
                                <Checkbox
                                    :checked="data.columnVisibility.port_of_discharge"
                                    @change="toggleColumnVisibility('port_of_discharge', $event)"
                                />
                                <span class="hover:cursor-pointer">Discharge Port</span>
                            </label>

                            <label class="inline-flex items-center space-x-2">
                                <Checkbox
                                    :checked="data.columnVisibility.flight_number"
                                    @change="toggleColumnVisibility('flight_number', $event)"
                                />
                                <span class="hover:cursor-pointer">Flight Number</span>
                            </label>

                            <label class="inline-flex items-center space-x-2">
                                <Checkbox
                                    :checked="data.columnVisibility.airline_name"
                                    @change="toggleColumnVisibility('airline_name', $event)"
                                />
                                <span class="hover:cursor-pointer">Airline Name</span>
                            </label>

                            <label class="inline-flex items-center space-x-2">
                                <Checkbox
                                    :checked="data.columnVisibility.airport_of_departure"
                                    @change="
                    toggleColumnVisibility('airport_of_departure', $event)
                  "
                                />
                                <span class="hover:cursor-pointer">Departure Airport</span>
                            </label>

                            <label class="inline-flex items-center space-x-2">
                                <Checkbox
                                    :checked="data.columnVisibility.airport_of_arrival"
                                    @change="toggleColumnVisibility('airport_of_arrival', $event)"
                                />
                                <span class="hover:cursor-pointer">Arrival Airport</span>
                            </label>

                            <label class="inline-flex items-center space-x-2">
                                <Checkbox
                                    :checked="data.columnVisibility.cargo_class"
                                    @change="toggleColumnVisibility('cargo_class', $event)"
                                />
                                <span class="hover:cursor-pointer">Cargo Class</span>
                            </label>

                            <label class="inline-flex items-center space-x-2">
                                <Checkbox
                                    :checked="data.columnVisibility.loading_started_at"
                                    @change="toggleColumnVisibility('loading_started_at', $event)"
                                />
                                <span class="hover:cursor-pointer">Loading Started At</span>
                            </label>

                            <label class="inline-flex items-center space-x-2">
                                <Checkbox
                                    :checked="data.columnVisibility.loading_ended_at"
                                    @change="toggleColumnVisibility('loading_ended_at', $event)"
                                />
                                <span class="hover:cursor-pointer">Loading Ended At</span>
                            </label>

                            <label class="inline-flex items-center space-x-2">
                                <Checkbox
                                    :checked="data.columnVisibility.unloading_started_at"
                                    @change="
                    toggleColumnVisibility('unloading_started_at', $event)
                  "
                                />
                                <span class="hover:cursor-pointer">Unloading Started At</span>
                            </label>

                            <label class="inline-flex items-center space-x-2">
                                <Checkbox
                                    :checked="data.columnVisibility.unloading_ended_at"
                                    @change="toggleColumnVisibility('unloading_ended_at', $event)"
                                />
                                <span class="hover:cursor-pointer">Unloading Ended At</span>
                            </label>

                            <label class="inline-flex items-center space-x-2">
                                <Checkbox
                                    :checked="data.columnVisibility.loading_started_by"
                                    @change="toggleColumnVisibility('loading_started_by', $event)"
                                />
                                <span class="hover:cursor-pointer">Loading Started By</span>
                            </label>

                            <label class="inline-flex items-center space-x-2">
                                <Checkbox
                                    :checked="data.columnVisibility.loading_ended_by"
                                    @change="toggleColumnVisibility('loading_ended_by', $event)"
                                />
                                <span class="hover:cursor-pointer">Loading Ended By</span>
                            </label>

                            <label class="inline-flex items-center space-x-2">
                                <Checkbox
                                    :checked="data.columnVisibility.unloading_started_by"
                                    @change="
                    toggleColumnVisibility('unloading_started_by', $event)
                  "
                                />
                                <span class="hover:cursor-pointer">Unloading Started By</span>
                            </label>

                            <label class="inline-flex items-center space-x-2">
                                <Checkbox
                                    :checked="data.columnVisibility.unloading_ended_by"
                                    @change="toggleColumnVisibility('unloading_ended_by', $event)"
                                />
                                <span class="hover:cursor-pointer">Unloading Ended By</span>
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
                        <div v-show="isData" ref="wrapperRef"></div>
                        <NoRecordsFound v-show="!isData"/>
                    </div>
                </div>
            </div>
        </div>

        <FilterDrawer :show="showFilters" @close="showFilters = false">
            <template #title> Filter Containers</template>

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

                <div>
                    <InputLabel value="ETD Start Date"/>
                    <DatePicker
                        v-model="filters.etdStartDate"
                        placeholder="Choose date..."
                    />
                </div>

                <div>
                    <InputLabel value="ETD End Date"/>
                    <DatePicker
                        v-model="filters.etdEndDate"
                        placeholder="Choose date..."
                    />
                </div>

                <FilterBorder/>

                <FilterHeader value="Cargo Types"/>

                <label
                    v-for="cargoType in cargoTypes"
                    :key="cargoType"
                    class="inline-flex items-center space-x-2 mt-2"
                >
                    <Switch
                        v-model="filters.cargoType"
                        :label="cargoType"
                        :value="cargoType"
                    />
                    <span v-if="cargoType == 'Sea Cargo'">
            <div v-html="shipIcon"></div>
          </span>
                    <span v-if="cargoType == 'Air Cargo'">
            <div v-html="planeIcon"></div>
          </span>
                </label>

                <FilterBorder/>

                <FilterHeader value="Container Types"/>

                <label
                    v-for="containerType in containerTypes"
                    :key="containerType"
                    class="inline-flex items-center space-x-2 mt-2"
                >
                    <Switch
                        v-model="filters.containerType"
                        :label="containerType"
                        :value="containerType"
                    />
                </label>

                <FilterBorder/>

                <FilterHeader value="Status"/>

                <label class="inline-flex items-center space-x-2">
                    <RadioButton
                        v-model="filters.status"
                        label="Requested"
                        name="status"
                        value="requested"
                    />
                </label>

                <label class="inline-flex items-center space-x-2">
                    <RadioButton
                        v-model="filters.status"
                        label="Loading"
                        name="status"
                        value="loading"
                    />
                </label>

                <label class="inline-flex items-center space-x-2">
                    <RadioButton
                        v-model="filters.status"
                        label="Draft"
                        name="status"
                        value="draft"
                    />
                </label>

                <label class="inline-flex items-center space-x-2">
                    <RadioButton
                        v-model="filters.status"
                        label="Loaded"
                        name="status"
                        value="loaded"
                    />
                </label>

                <label class="inline-flex items-center space-x-2">
                    <RadioButton
                        v-model="filters.status"
                        label="Unloaded"
                        name="status"
                        value="unloaded"
                    />
                </label>

                <label class="inline-flex items-center space-x-2">
                    <RadioButton
                        v-model="filters.status"
                        label="Returned"
                        name="status"
                        value="returned"
                    />
                </label>

                <FilterBorder/>


            </template>
        </FilterDrawer>
    </AppLayout>

    <AppLayout v-else title="Containers">
        <template #header>Containers</template>

        <Breadcrumb/>

        <div>
            <Panel :collapsed="true" class="mt-5" header="Advance Filters" toggleable>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                    <FloatLabel class="w-full" variant="in">
                        <DatePicker v-model="fromDate" class="w-full" date-format="yy-mm-dd" input-id="from-date"/>
                        <label for="from-date">From Date</label>
                    </FloatLabel>

                    <FloatLabel class="w-full" variant="in">
                        <DatePicker v-model="toDate" class="w-full" date-format="yy-mm-dd" input-id="to-date"/>
                        <label for="to-date">To Date</label>
                    </FloatLabel>
                </div>
            </Panel>

            <Card class="my-5">
                <template #content>
                    <ContextMenu ref="cm" :model="menuModel" @hide="selectedContainer.length < 1"/>
                    <DataTable
                        ref="dt"
                        v-model:contextMenuSelection="selectedContainer"
                        v-model:filters="filters"
                        :globalFilterFields="['name']"
                        :loading="loading"
                        :rows="perPage"
                        :rowsPerPageOptions="[5, 10, 20, 50, 100]"
                        :totalRecords="totalRecords"
                        :value="containers"
                        context-menu
                        data-key="id"
                        filter-display="menu"
                        lazy
                        paginator
                        removable-sort
                        row-hover
                        tableStyle="min-width: 50rem"
                        @page="onPageChange"
                        @rowContextmenu="onRowContextMenu"
                        @sort="onSort">

                        <template #header>
                            <div class="flex flex-col sm:flex-row justify-between items-center mb-2">
                                <div class="text-lg font-medium">
                                    Containers
                                </div>
                                <Link v-if="$page.props.user.permissions.includes('container.create')" :href="route('loading.loading-containers.create')">
                                    <PrimaryButton class="w-full">Create New Shipment</PrimaryButton>
                                </Link>
                            </div>
                            <div class="flex flex-col sm:flex-row justify-between gap-4">
                                <!-- Button Group -->
                                <div class="flex flex-col sm:flex-row gap-2">
                                    <Button
                                        icon="pi pi-filter-slash"
                                        label="Clear Filters"
                                        outlined
                                        severity="contrast"
                                        size="small"
                                        type="button"
                                        @click="clearFilter()"
                                    />

                                    <Button
                                        icon="pi pi-external-link"
                                        label="Export"
                                        severity="contrast"
                                        size="small"
                                        @click="exportCSV($event)"
                                    />
                                </div>

                                <!-- Search Field -->
                                <IconField class="w-full sm:w-auto">
                                    <InputIcon>
                                        <i class="pi pi-search" />
                                    </InputIcon>
                                    <InputText
                                        v-model="filters.global.value"
                                        class="w-full"
                                        placeholder="Keyword Search"
                                        size="small"
                                    />
                                </IconField>
                            </div>
                        </template>

                        <template #empty>No containers found.</template>

                        <template #loading>Loading containers data. Please wait.</template>

                        <Column field="container_type" header="Container Type" sortable>
                            <template #body="slotProps">
                                <Tag :severity="resolveContainerType(slotProps.data)"
                                     :value="slotProps.data.container_type" class="text-sm"></Tag>
                            </template>
                            <template #filter="{ filterModel }">
                                <Select v-model="filterModel.value" :options="containerTypes" :showClear="true"
                                        placeholder="Select One" style="min-width: 12rem" />
                            </template>
                        </Column>

                        <Column field="reference" header="Reference" sortable></Column>

                        <Column field="bl_number" header="BL Number" sortable></Column>

                        <Column field="awb_number" header="AWB Number" sortable></Column>

                        <Column field="container_number" header="Container Number" sortable></Column>

                        <Column field="seal_number" header="Seal Number" sortable></Column>

                        <Column field="cargo_type" header="Cargo Type" sortable>
                            <template #body="slotProps">
                                <Tag :icon="resolveCargoType(slotProps.data).icon"
                                     :severity="resolveCargoType(slotProps.data).color"
                                     :value="slotProps.data.cargo_type" class="text-sm"></Tag>
                            </template>
                            <template #filter="{ filterModel }">
                                <Select v-model="filterModel.value" :options="cargoTypes" :showClear="true"
                                        placeholder="Select One" style="min-width: 12rem" >
                                    <template #option="slotProps">
                                        <Tag :value="slotProps.option"/>
                                    </template>
                                </Select>
                            </template>
                        </Column>

                        <Column field="estimated_time_of_arrival" header="ETA"></Column>

                        <Column field="estimated_time_of_departure" header="ETD"></Column>

                        <Column field="status" header="Status">
                            <template #body="slotProps">
                                <Tag :icon="resolveContainerStatus(slotProps.data).icon"
                                     :severity="resolveContainerStatus(slotProps.data).color"
                                     :value="slotProps.data.status" class="text-sm uppercase"></Tag>
                            </template>
                        </Column>

                        <template #footer> In total there are {{ containers ? totalRecords : 0 }} containers. </template>
                    </DataTable>
                </template>
            </Card>
        </div>
    </AppLayout>
</template>

<style>
.p-tag-icon {
    font-size: 15px;
    margin-right: 3px;
}
</style>
