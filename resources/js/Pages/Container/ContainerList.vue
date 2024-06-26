<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { onMounted, reactive, ref } from "vue";
import { Link, router } from "@inertiajs/vue3";
import { Grid, h } from "gridjs";
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

const props = defineProps({
  cargoTypes: {
    type: Object,
    default: () => {},
  },
  containerTypes: {
    type: Object,
    default: () => {},
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
  etdStartDate: "",
  etdEndDate: "",
  cargoType: Object.values(props.cargoTypes),
  containerType: Object.values(props.containerTypes),
  status: "",
});

const data = reactive({
  columnVisibility: {
    id: false,
    cargo_type: true,
    container_type: true,
    reference: true,
    bl_number: true,
    awb_number: true,
    container_number: true,
    seal_number: true,
    maximum_volume: false,
    minimum_volume: false,
    maximum_weight: false,
    minimum_weight: false,
    maximum_volumetric_weight: false,
    minimum_volumetric_weight: false,
    estimated_time_of_departure: true,
    estimated_time_of_arrival: true,
    vessel_name: false,
    voyage_number: false,
    shipping_line: false,
    port_of_loading: false,
    port_of_discharge: false,
    flight_number: false,
    airline_name: false,
    airport_of_departure: false,
    airport_of_arrival: false,
    cargo_class: false,
    status: true,
    loading_started_at: false,
    loading_ended_at: false,
    unloading_started_at: false,
    unloading_ended_at: false,
    loading_started_by: false,
    loading_ended_by: false,
    unloading_started_by: false,
    unloading_ended_by: false,
    actions: true,
  },
});

const baseUrl = ref("/container-list");

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
  { name: "ID", hidden: !data.columnVisibility.id },
  { name: "Cargo Type", hidden: !data.columnVisibility.cargo_type },
  { name: "Container Type", hidden: !data.columnVisibility.cargo_type },
  { name: "Reference", hidden: !data.columnVisibility.reference },
  { name: "BL Number", hidden: !data.columnVisibility.bl_number },
  { name: "AWB Number", hidden: !data.columnVisibility.awb_number },
  { name: "Container Number", hidden: !data.columnVisibility.container_number },
  { name: "Seal Number", hidden: !data.columnVisibility.seal_number },
  { name: "Maximum Volume", hidden: !data.columnVisibility.maximum_volume },
  { name: "Minimum Volume", hidden: !data.columnVisibility.minimum_volume },
  { name: "Maximum Weight", hidden: !data.columnVisibility.maximum_weight },
  { name: "Minimum Weight", hidden: !data.columnVisibility.minimum_weight },
  {
    name: "Maximum Volumetric Weight",
    hidden: !data.columnVisibility.maximum_volumetric_weight,
  },
  {
    name: "Minimum Volumetric Weight",
    hidden: !data.columnVisibility.minimum_volumetric_weight,
  },
  { name: "ETD", hidden: !data.columnVisibility.estimated_time_of_departure },
  { name: "ETA", hidden: !data.columnVisibility.estimated_time_of_arrival },
  { name: "Vessel Name", hidden: !data.columnVisibility.vessel_name },
  { name: "Voyager Number", hidden: !data.columnVisibility.voyage_number },
  { name: "Shipping Line", hidden: !data.columnVisibility.shipping_line },
  { name: "Loading Port", hidden: !data.columnVisibility.port_of_loading },
  { name: "Discharge Port", hidden: !data.columnVisibility.port_of_discharge },
  { name: "Flight Number", hidden: !data.columnVisibility.flight_number },
  { name: "Airline Name", hidden: !data.columnVisibility.airline_name },
  {
    name: "Departure Airport",
    hidden: !data.columnVisibility.airport_of_departure,
  },
  {
    name: "Arrival Airport",
    hidden: !data.columnVisibility.airport_of_arrival,
  },
  { name: "Cargo Class", hidden: !data.columnVisibility.cargo_class },
  { name: "Status", hidden: !data.columnVisibility.status },
  {
    name: "Loading Started At",
    hidden: !data.columnVisibility.loading_started_at,
  },
  { name: "Loading Ended At", hidden: !data.columnVisibility.loading_ended_at },
  {
    name: "Unloading Started At",
    hidden: !data.columnVisibility.unloading_started_at,
  },
  {
    name: "Unloading Ended At",
    hidden: !data.columnVisibility.unloading_ended_at,
  },
  {
    name: "Loading Started By",
    hidden: !data.columnVisibility.loading_started_by,
  },
  { name: "Loading Ended By", hidden: !data.columnVisibility.loading_ended_by },
  {
    name: "Unloading Started By",
    hidden: !data.columnVisibility.unloading_started_by,
  },
  {
    name: "Unloading Ended By",
    hidden: !data.columnVisibility.unloading_ended_by,
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
              "btn size-8 p-0 text-success hover:bg-success/20 focus:bg-success/20 active:bg-success/25",
            onClick: () =>
              router.visit(
                route("loading.loading-points.index", {
                  container: row.cells[0].data,
                  cargoType: row.cells[1].data,
                })
              ),
            "x-tooltip..placement.bottom.success": "'Load to Container'",
          },
          [
            h(
              "svg",
              {
                xmlns: "http://www.w3.org/2000/svg",
                viewBox: "0 0 24 24",
                class:
                  "size-6 icon icon-tabler icons-tabler-outline icon-tabler-truck-loading",
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
                  d: "M2 3h1a2 2 0 0 1 2 2v10a2 2 0 0 0 2 2h15",
                }),
                h("path", {
                  d: "M9 6m0 3a3 3 0 0 1 3 -3h4a3 3 0 0 1 3 3v2a3 3 0 0 1 -3 3h-4a3 3 0 0 1 -3 -3z",
                }),
                h("path", {
                  d: "M9 19m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0",
                }),
                h("path", {
                  d: "M18 19m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0",
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
</script>
<template>
  <AppLayout title="Container List">
    <template #header>Container List</template>

    <Breadcrumb />

    <div class="flex justify-end mt-4">
      <Link :href="route('loading.loading-containers.create')">
        <PrimaryButton> Create New Container </PrimaryButton>
      </Link>
    </div>

    <div class="card mt-4">
      <div>
        <div class="flex items-center justify-between p-2">
          <div class="">
            <div class="flex">
              <h2
                class="text-base font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100"
              >
                Container List
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
                    class="tag rounded-r-none bg-slate-150 text-slate-800 hover:bg-slate-200 focus:bg-slate-200 active:bg-slate-200/80 dark:bg-navy-500 dark:text-navy-100 dark:hover:bg-navy-450 dark:focus:bg-navy-450 dark:active:bg-navy-450/90"
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
                    class="ml-4 tag rounded-r-none bg-slate-150 text-slate-800 hover:bg-slate-200 focus:bg-slate-200 active:bg-slate-200/80 dark:bg-navy-500 dark:text-navy-100 dark:hover:bg-navy-450 dark:focus:bg-navy-450 dark:active:bg-navy-450/90"
                  >
                    To Date
                  </div>
                  <div
                    class="tag rounded-l-none bg-warning text-white hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:hover:bg-accent-focus dark:focus:bg-accent-focus dark:active:bg-accent/90"
                  >
                    {{ filters.toDate }}
                  </div>
                </div>
                <div>
                  <div
                    v-for="(mode, index) in filters.cargoType"
                    v-if="filters.cargoType"
                    :key="index"
                    class="badge bg-navy-700 text-white dark:bg-navy-900 ml-2"
                  >
                    {{ mode }}
                  </div>

                  <div
                    v-for="(mode, index) in filters.containerType"
                    v-if="filters.containerType"
                    :key="index"
                    class="badge bg-cyan-500 text-white dark:bg-cyan-900 ml-2"
                  >
                    {{ mode }}
                  </div>

                  <div
                    v-if="filters.status"
                    class="badge bg-success text-white ml-2"
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
      <template #title> Filter Containers </template>

      <template #content>
        <div>
          <InputLabel value="From" />
          <DatePicker v-model="filters.fromDate" placeholder="Choose date..." />
        </div>

        <div>
          <InputLabel value="To" />
          <DatePicker v-model="filters.toDate" placeholder="Choose date..." />
        </div>

        <FilterBorder />

        <div>
          <InputLabel value="ETD Start Date" />
          <DatePicker
            v-model="filters.etdStartDate"
            placeholder="Choose date..."
          />
        </div>

        <div>
          <InputLabel value="ETD End Date" />
          <DatePicker
            v-model="filters.etdEndDate"
            placeholder="Choose date..."
          />
        </div>

        <FilterBorder />

        <FilterHeader value="Cargo Types" />

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
        </label>

        <FilterBorder />

        <FilterHeader value="Container Types" />

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

        <FilterBorder />

        <FilterHeader value="Status" />

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

        <FilterBorder />

        <!--Filter Now Action Button-->
        <SoftPrimaryButton class="space-x-2" @click="applyFilters">
          <i class="fa-solid fa-filter"></i>
          <span>Apply Filters</span>
        </SoftPrimaryButton>
      </template>
    </FilterDrawer>
  </AppLayout>
</template>
