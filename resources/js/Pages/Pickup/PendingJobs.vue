<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { computed, onMounted, reactive, ref } from "vue";
import { Grid, h, html } from "gridjs";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import AssignDriverModal from "@/Pages/Pickup/Partials/AssignDriverModal.vue";
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
import EditPickupModal from "@/Pages/Pickup/Partials/EditPickupModal.vue";
import { router } from "@inertiajs/vue3";
import { push } from "notivue";
import DeletePickupConfirmationModal from "@/Pages/Pickup/Partials/DeletePickupConfirmationModal.vue";

const props = defineProps({
  drivers: {
    type: Object,
    default: () => {},
  },
  users: {
    type: Object,
    default: () => {},
  },
  zones: {
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
  cargoMode: ["Air Cargo", "Sea Cargo"],
  isUrgent: false,
  isImportant: false,
  createdBy: "",
  zoneBy: "",
});

const data = reactive({
  columnVisibility: {
    reference: true,
    name: true,
    email: false,
    address: true,
    contact_number: true,
    cargo_type: true,
    // is_urgent_pickup: false,
    // is_from_important_customer: false,
    driver: true,
    pickup_date: true,
    // pickup_time_start: false,
    // pickup_time_end: false,
    pickup_type: true,
    // pickup_note: false,
    actions: true,
  },
});

const baseUrl = ref("/pickup-list");

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
          row.push({ id: item.id });
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

const selectedData = ref([]);

const createColumns = () => [
  {
    name: "#",
    formatter: (_, row) => {
      return h("input", {
        type: "checkbox",
        className:
          "form-checkbox is-basic size-4 rounded border-slate-400/70 checked:bg-primary checked:border-primary hover:border-primary focus:border-primary dark:border-navy-400 dark:checked:bg-accent dark:checked:border-accent dark:hover:border-accent dark:focus:border-accent",
        onChange: (event) => {
          const isChecked = event.target.checked;
          if (isChecked) {
            const rowData = row.cells.map((cell) => cell.data); // Extract data from cells array
            selectedData.value.push(rowData); // Push extracted data into selectedData
          } else {
            // Remove the specific row from selectedData (assuming uniqueness of rows)
            const index = selectedData.value.findIndex((selectedRow) => {
              const rowData = row.cells.map((cell) => cell.data);
              return JSON.stringify(selectedRow) === JSON.stringify(rowData);
            });
            if (index !== -1) {
              selectedData.value.splice(index, 1);
            }
          }
        },
      });
    },
  },
  { name: "Reference", hidden: !data.columnVisibility.reference },
  { name: "Name", hidden: !data.columnVisibility.name },
  { name: "Email", hidden: !data.columnVisibility.email },
  { name: "Address", hidden: !data.columnVisibility.address, sort: false },
  {
    name: "Contact",
    hidden: !data.columnVisibility.contact_number,
    sort: false,
  },
  {
    name: "Cargo Mode",
    sort: false,
    hidden: !data.columnVisibility.cargo_type,
    formatter: (_, row) =>
      row.cells[6].data == "Sea Cargo"
        ? h(
            "span",
            { className: "flex" },
            h(
              "svg",
              {
                xmlns: "http://www.w3.org/2000/svg",
                viewBox: "0 0 24 24",
                class:
                  "icon icon-tabler icons-tabler-outline icon-tabler-ship mr-2",
                fill: "none",
                height: 24,
                width: 24,
                stroke: "currentColor",
                strokeLinecap: "round",
                strokeLinejoin: "round",
                strokeWidth: 2,
              },
              [
                h("path", {
                  stroke: "none",
                  d: "M0 0h24v24H0z",
                  fill: "none",
                }),
                h("path", {
                  d: "M2 20a2.4 2.4 0 0 0 2 1a2.4 2.4 0 0 0 2 -1a2.4 2.4 0 0 1 2 -1a2.4 2.4 0 0 1 2 1a2.4 2.4 0 0 0 2 1a2.4 2.4 0 0 0 2 -1a2.4 2.4 0 0 1 2 -1a2.4 2.4 0 0 1 2 1a2.4 2.4 0 0 0 2 1a2.4 2.4 0 0 0 2 -1",
                }),
                h("path", {
                  d: "M4 18l-1 -5h18l-2 4",
                }),
                h("path", {
                  d: "M5 13v-6h8l4 6",
                }),
                h("path", {
                  d: "M7 7v-4h-1",
                }),
              ]
            ),
            row.cells[6].data
          )
        : row.cells[6].data == "Air Cargo"
        ? h("span", { className: "flex space-x-2" }, [
            h(
              "svg",
              {
                xmlns: "http://www.w3.org/2000/svg",
                viewBox: "0 0 24 24",
                class:
                  "icon icon-tabler icons-tabler-outline icon-tabler-plane mr-2",
                fill: "none",
                height: 24,
                width: 24,
                stroke: "currentColor",
                strokeLinecap: "round",
                strokeLinejoin: "round",
                strokeWidth: 2,
              },
              [
                h("path", {
                  stroke: "none",
                  d: "M0 0h24v24H0z",
                  fill: "none",
                }),
                h("path", {
                  d: "M16 10h4a2 2 0 0 1 0 4h-4l-4 7h-3l2 -7h-4l-2 2h-3l2 -4l-2 -4h3l2 2h4l-2 -7h3z",
                }),
              ]
            ),
            row.cells[6].data,
          ])
        : row.cells[6].data,
  },

  {
    name: "Driver",
    hidden: !data.columnVisibility.driver,
    formatter: (cell) => {
      return cell
        ? html(
            `<div class="flex item-center"><svg xmlns="http://www.w3.org/2000/svg"  width="18"  height="18"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-steering-wheel mr-1"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" /><path d="M12 12m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" /><path d="M12 14l0 7" /><path d="M10 12l-6.75 -2" /><path d="M14 12l6.75 -2" /></svg> ${cell} </div>`
          )
        : null;
    },
  },
  { name: "Pickup Date", hidden: !data.columnVisibility.pickup_date },
  //   {
  //     name: "Pickup Time Start",
  //     hidden: !data.columnVisibility.pickup_time_start,
  //   },
  //   { name: "Pickup Time End", hidden: !data.columnVisibility.pickup_time_end },
  { name: "Pickup Type", hidden: !data.columnVisibility.pickup_type },
  //   { name: "Pickup Note", hidden: !data.columnVisibility.pickup_note },
  {
    name: "Actions",
    sort: false,
    hidden: !data.columnVisibility.actions,
    formatter: (_, row) => {
      return h("div", { className: "flex space-x-2" }, [
        h(
          "button",
          {
            className:
              "btn size-8 p-0 text-info hover:bg-info/20 focus:bg-info/20 active:bg-info/25 mr-2",
            onClick: () => confirmEditPickup(row.cells[0].data?.id),
            "x-tooltip..placement.bottom.primary": "'Edit Pending Job'",
          },
          [
            h(
              "svg",
              {
                xmlns: "http://www.w3.org/2000/svg",
                viewBox: "0 0 24 24",
                class: "icon icon-tabler icons-tabler-outline icon-tabler-edit",
                fill: "none",
                height: 24,
                width: 24,
                stroke: "currentColor",
                strokeLinecap: "round",
                strokeLinejoin: "round",
              },
              [
                h("path", {
                  d: "M0 0h24v24H0z",
                  fill: "none",
                  stroke: "none",
                }),
                h("path", {
                  d: "M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1",
                }),
                h("path", {
                  d: "M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3l8.385 -8.415z",
                }),
                h("path", {
                  d: "M16 5l3 3",
                }),
              ]
            ),
          ]
        ),
        h(
          "button",
          {
            className:
              "btn size-8 p-0 text-error hover:bg-error/20 focus:bg-error/20 active:bg-error/25",
            onClick: () => confirmDeletePickup(row.cells[0].data?.id),
            "x-tooltip..placement.bottom.error": "'Delete HBL'",
          },
          [
            h(
              "svg",
              {
                xmlns: "http://www.w3.org/2000/svg",
                viewBox: "0 0 24 24",
                class:
                  "icon icon-tabler icons-tabler-outline icon-tabler-trash",
                fill: "none",
                height: 24,
                width: 24,
                stroke: "currentColor",
                strokeLinecap: "round",
                strokeLinejoin: "round",
              },
              [
                h("path", {
                  d: "M0 0h24v24H0z",
                  fill: "none",
                  stroke: "none",
                }),
                h("path", {
                  d: "M4 7l16 0",
                }),
                h("path", {
                  d: "M10 11l0 6",
                }),
                h("path", {
                  d: "M14 11l0 6",
                }),
                h("path", {
                  d: "M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12",
                }),
                h("path", {
                  d: "M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3",
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
          row.push({ id: item.id });
          visibleColumns.forEach((column) => {
            row.push(item[column]);
          });
          return row;
        }),
    },
  });

  grid.forceRender();
};

const showConfirmAssignDriverModal = ref(false);
const isDataEmpty = computed(() => selectedData.value.length === 0);
const countOfSelectedData = computed(() => selectedData.value.length);
const idList = ref([]);

const confirmAssignDriver = () => {
  idList.value = selectedData.value.map((item) => item[0]);
  showConfirmAssignDriverModal.value = true;
};

const showConfirmEditPickupModal = ref(false);
const pickupId = ref(null);
const showConfirmDeletePickupModal = ref(false);

const confirmEditPickup = (id) => {
  pickupId.value = id;
  showConfirmEditPickupModal.value = true;
};

const closeModal = () => {
  showConfirmAssignDriverModal.value = false;
  showConfirmEditPickupModal.value = false;
  pickupId.value = null;
  idList.value = [];
};

const confirmDeletePickup = (id) => {
  pickupId.value = id;
  showConfirmDeletePickupModal.value = true;
};

const handleDeletePickup = () => {
  router.delete(route("pickups.destroy", pickupId.value), {
    preserveScroll: true,
    onSuccess: () => {
      closeModal();
      push.success("Pickup record Deleted Successfully!");
      router.visit(route("pickups.index"), { only: ["pickups"] });
    },
    onError: () => {
      closeModal();
      push.error("Something went to wrong!");
    },
  });
};

const resetFilter = () => {
  filters.fromDate = fromDate;
  filters.toDate = toDate;
  filters.cargoMode = ["Air Cargo", "Sea Cargo", "Door to Door"];
  filters.isUrgent = false;
  filters.isImportant = false;
  filters.createdBy = "";
  filters.zoneBy = "";
  applyFilters();
};
const planeIcon = ref(`
<svg
  xmlns="http://www.w3.org/2000/svg"
  width="15"
  height="15"
  viewBox="0 0 24 24"
  fill="none"
  stroke="currentColor"
  stroke-width="2"
  stroke-linecap="round"
  stroke-linejoin="round"
  class="icon icon-tabler icons-tabler-outline icon-tabler-plane mr-2"
>
  <path stroke="none" d="M0 0h24v24H0z" fill="none" />
  <path d="M16 10h4a2 2 0 0 1 0 4h-4l-4 7h-3l2 -7h-4l-2 2h-3l2 -4l-2 -4h3l2 2h4l-2 -7h3z" />
</svg>
`);

const shipIcon = ref(`
<svg
  xmlns="http://www.w3.org/2000/svg"
  width="15"
  height="15"
  viewBox="0 0 24 24"
  fill="none"
  stroke="currentColor"
  stroke-width="2"
  stroke-linecap="round"
  stroke-linejoin="round"
  class="icon icon-tabler icons-tabler-outline icon-tabler-ship mr-2"
>
  <path stroke="none" d="M0 0h24v24H0z" fill="none" />
  <path d="M2 20a2.4 2.4 0 0 0 2 1a2.4 2.4 0 0 0 2 -1a2.4 2.4 0 0 1 2 -1a2.4 2.4 0 0 1 2 1a2.4 2.4 0 0 0 2 1a2.4 2.4 0 0 0 2 -1a2.4 2.4 0 0 1 2 -1a2.4 2.4 0 0 1 2 1a2.4 2.4 0 0 0 2 1a2.4 2.4 0 0 0 2 -1" />
  <path d="M4 18l-1 -5h18l-2 4" />
  <path d="M5 13v-6h8l4 6" />
  <path d="M7 7v-4h-1" />
</svg>
`);

const doorIcon = ref(`
<svg  xmlns="http://www.w3.org/2000/svg"  width="15"  height="15"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  
stroke-linejoin="round"  
class="icon icon-tabler icons-tabler-outline icon-tabler-door mr-2">
<path stroke="none" d="M0 0h24v24H0z" fill="none"/>
<path d="M14 12v.01" /><path d="M3 21h18" />
<path d="M6 21v-16a2 2 0 0 1 2 -2h8a2 2 0 0 1 2 2v16" /></svg>
`);
</script>
<template>
  <AppLayout title="Pending Pickups">
    <template #header>Pending Pickups</template>

    <Breadcrumb />

    <div class="card mt-4">
      <div>
        <div class="flex items-center justify-between p-2">
          <div class="">
            <div class="flex">
              <h2
                class="text-base font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100"
              >
                Pending Pickups
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
                    v-for="(mode, index) in filters.cargoMode"
                    v-if="filters.cargoMode"
                    :key="index"
                    class="badge bg-navy-700 text-white dark:bg-navy-900 ml-2"
                  >
                    <span v-if="mode == 'Sea Cargo'">
                      <div v-html="shipIcon"></div>
                    </span>
                    <span v-if="mode == 'Air Cargo'">
                      <div v-html="planeIcon"></div>
                    </span>
                    <span v-if="mode == 'Door to Door'">
                      <div v-html="doorIcon"></div>
                    </span>
                    {{ mode }}
                  </div>

                  <div
                    v-if="filters.isUrgent"
                    class="badge bg-success text-white ml-2"
                  >
                    Is Urgent
                  </div>

                  <div
                    v-if="filters.isImportant"
                    class="badge bg-cyan-500 text-white ml-2"
                  >
                    VIP Customer
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="flex">
            <ColumnVisibilityPopover>
              <label class="inline-flex items-center space-x-2">
                <Checkbox
                  :checked="data.columnVisibility.reference"
                  @change="toggleColumnVisibility('reference', $event)"
                />
                <span class="hover:cursor-pointer">Reference</span>
              </label>

              <label class="inline-flex items-center space-x-2">
                <Checkbox
                  :checked="data.columnVisibility.name"
                  @change="toggleColumnVisibility('name', $event)"
                />
                <span class="hover:cursor-pointer">Name</span>
              </label>

              <label class="inline-flex items-center space-x-2">
                <Checkbox
                  :checked="data.columnVisibility.email"
                  @change="toggleColumnVisibility('email', $event)"
                />
                <span class="hover:cursor-pointer">Email</span>
              </label>

              <label class="inline-flex items-center space-x-2">
                <Checkbox
                  :checked="data.columnVisibility.address"
                  @change="toggleColumnVisibility('address', $event)"
                />
                <span class="hover:cursor-pointer">Address</span>
              </label>

              <label class="inline-flex items-center space-x-2">
                <Checkbox
                  :checked="data.columnVisibility.contact_number"
                  @change="toggleColumnVisibility('contact_number', $event)"
                />
                <span class="hover:cursor-pointer">Contact</span>
              </label>

              <label class="inline-flex items-center space-x-2">
                <Checkbox
                  :checked="data.columnVisibility.cargo_type"
                  @change="toggleColumnVisibility('cargo_type', $event)"
                />
                <span class="hover:cursor-pointer">Cargo Mode</span>
              </label>

              <label class="inline-flex items-center space-x-2">
                <Checkbox
                  :checked="data.columnVisibility.pickup_date"
                  @change="toggleColumnVisibility('pickup_date', $event)"
                />
                <span class="hover:cursor-pointer">Pickup Date</span>
              </label>

              <label class="inline-flex items-center space-x-2">
                <Checkbox
                  :checked="data.columnVisibility.is_urgent_pickup"
                  @change="toggleColumnVisibility('is_urgent_pickup', $event)"
                />
                <span class="hover:cursor-pointer">Urgent Pickup</span>
              </label>

              <label class="inline-flex items-center space-x-2">
                <Checkbox
                  :checked="data.columnVisibility.is_from_important_customer"
                  @change="
                    toggleColumnVisibility('is_from_important_customer', $event)
                  "
                />
                <span class="hover:cursor-pointer">VIP Customer</span>
              </label>

              <label class="inline-flex items-center space-x-2">
                <Checkbox
                  :checked="data.columnVisibility.pickup_time_start"
                  @change="toggleColumnVisibility('pickup_time_start', $event)"
                />
                <span class="hover:cursor-pointer">Pickup Time Start</span>
              </label>

              <label class="inline-flex items-center space-x-2">
                <Checkbox
                  :checked="data.columnVisibility.pickup_time_end"
                  @change="toggleColumnVisibility('pickup_time_end', $event)"
                />
                <span class="hover:cursor-pointer">Pickup Time End</span>
              </label>
            </ColumnVisibilityPopover>

            <button
              x-tooltip.placement.top="'Filters'"
              @click="showFilters = true"
              class="btn size-8 rounded-full p-0 hover:bg-slate-300/20 focus:bg-slate-300/20 active:bg-slate-300/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25"
            >
              <i class="fa-solid fa-filter"></i>
            </button>

            <PrimaryButton :disabled="isDataEmpty" @click="confirmAssignDriver">
              <svg
                class="icon icon-tabler icons-tabler-outline icon-tabler-steering-wheel mr-1"
                fill="none"
                height="18"
                stroke="currentColor"
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                viewBox="0 0 24 24"
                width="18"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path d="M0 0h24v24H0z" fill="none" stroke="none" />
                <path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" />
                <path d="M12 12m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" />
                <path d="M12 14l0 7" />
                <path d="M10 12l-6.75 -2" />
                <path d="M14 12l6.75 -2" />
              </svg>
              Assign Driver ({{ countOfSelectedData }})
            </PrimaryButton>
          </div>
        </div>

        <div class="mt-3">
          <div class="is-scrollbar-hidden min-w-full overflow-x-auto">
            <div ref="wrapperRef"></div>
          </div>
        </div>
      </div>
    </div>

    <AssignDriverModal
      :drivers="drivers"
      :id-list="idList"
      :show="showConfirmAssignDriverModal"
      @close="closeModal"
    />

    <FilterDrawer :show="showFilters" @close="showFilters = false">
      <template #title> Filter Pending Jobs </template>

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

        <FilterHeader value="Cargo Mode" />

        <label class="inline-flex items-center space-x-2 mt-2">
          <Switch
            v-model="filters.cargoMode"
            label="Air Cargo"
            value="Air Cargo"
          />
          <div v-html="planeIcon"></div>
        </label>

        <label class="inline-flex items-center space-x-2 mt-2">
          <Switch
            v-model="filters.cargoMode"
            label="Sea Cargo"
            value="Sea Cargo"
          />
          <div v-html="shipIcon"></div>
        </label>

        <FilterBorder />

        <FilterHeader value="Is Urgent" />

        <label class="inline-flex items-center space-x-2 mt-2">
          <Switch v-model="filters.isUrgent" label="Is Urgent" value="true" />
        </label>

        <FilterBorder />

        <FilterHeader value="Is Important to Customer" />

        <label class="inline-flex items-center space-x-2 mt-2">
          <Switch
            v-model="filters.isImportant"
            label="Is Important"
            value="true"
          />
        </label>

        <FilterBorder />

        <FilterHeader value="Created By" />

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
          <option v-for="user in users" :value="user.id">
            {{ user.name }}
          </option>
        </select>

        <FilterBorder />

        <FilterHeader value="Zone" />

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
          <option v-for="zone in zones" :value="zone.id">
            {{ zone.zone_name }}
          </option>
        </select>

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

    <EditPickupModal
      :pickup-id="pickupId"
      :show="showConfirmEditPickupModal"
      :zones="zones"
      @close="closeModal"
    />

    <DeletePickupConfirmationModal
      :show="showConfirmDeletePickupModal"
      @close="closeModal"
      @delete-pickup="handleDeletePickup"
    />
  </AppLayout>
</template>
