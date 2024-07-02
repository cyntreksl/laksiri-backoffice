<script setup>
import { onMounted, reactive, ref } from "vue";
import { Link, router } from "@inertiajs/vue3";
import { Grid, h, html } from "gridjs";
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
import { push } from "notivue";
import HBLDetailModal from "@/Pages/Common/HBLDetailModal.vue";
import RestoreHBLConfirmationModal from "@/Pages/HBL/Partials/RestoreHBLConfirmationModal.vue";

const props = defineProps({
  users: {
    type: Object,
    default: () => {},
  },
  hbls: {
    type: Object,
    default: () => {},
  },
  paymentStatus: {
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
  hblType: ["UBP", "Gift", "Door to Door"],
  isHold: false,
  warehouse: ["COLOMBO", "NINTAVUR"],
  createdBy: "",
  paymentStatus: [],
});

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
  },
});

const baseUrl = ref("/hbl-cancelled-list");

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
  { name: "Reference", hidden: !data.columnVisibility.reference },
  { name: "HBL", hidden: !data.columnVisibility.hbl },
  { name: "HBL Name", hidden: !data.columnVisibility.hbl_name },
  { name: "Consignee Name", hidden: !data.columnVisibility.consignee_name },
  {
    name: "Consignee Address",
    hidden: !data.columnVisibility.consignee_address,
    sort: false,
  },
  {
    name: "Consignee Contact",
    hidden: !data.columnVisibility.consignee_contact,
    sort: false,
  },
  { name: "Email", hidden: !data.columnVisibility.email, sort: false },
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
      row.cells[10].data == "Sea Cargo"
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
            row.cells[10].data
          )
        : row.cells[10].data == "Air Cargo"
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
            row.cells[10].data,
          ])
        : row.cells[10].data,
  },
  { name: "HBL Type", hidden: !data.columnVisibility.hbl_type },
  { name: "Warehouse", hidden: !data.columnVisibility.warehouse },
  { name: "Status", hidden: !data.columnVisibility.status },
  {
    name: "Is Hold",
    hidden: !data.columnVisibility.is_hold,
    formatter: (cell) => {
      return cell
        ? html(`<div></div class="text-center"><svg xmlns="http://www.w3.org/2000/svg" class="size-5 text-warning" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                  </svg></div>`)
        : null;
    },
    sort: false,
  },
  {
    name: "Actions",
    sort: false,
    hidden: !data.columnVisibility.actions,
    formatter: (_, row) => {
      return h("div", { className: "flex space-x-2" }, [
        h(
          "a",
          {
            className:
              "btn size-8 p-0 text-success hover:bg-success/20 focus:bg-success/20 active:bg-success/25 mr-2",
            onClick: () => confirmViewHBL(row.cells[0].data),
            "x-tooltip..placement.bottom.primary": "'View HBL'",
          },
          [
            h(
              "svg",
              {
                xmlns: "http://www.w3.org/2000/svg",
                viewBox: "0 0 24 24",
                class: "icon icon-tabler icons-tabler-outline icon-tabler-eye",
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
          "a",
          {
            className:
              "btn size-8 p-0 text-pink-500 hover:bg-pink-500/20 focus:bg-pink-500/20 active:bg-pink-500/25",
            href: route("hbls.download", row.cells[0].data),
            "x-tooltip..placement.bottom.primary": "'Download HBL'",
          },
          [
            h(
              "svg",
              {
                xmlns: "http://www.w3.org/2000/svg",
                viewBox: "0 0 24 24",
                class:
                  "icon icon-tabler icons-tabler-outline icon-tabler-download",
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
                  d: "M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2",
                }),
                h("path", {
                  d: "M7 11l5 5l5 -5",
                }),
                h("path", {
                  d: "M12 4l0 12",
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
            onClick: () => confirmRestoreHBL(row.cells[0].data),
            "x-tooltip..placement.bottom.error": "'Restore HBL'",
          },
          [
            h(
              "svg",
              {
                xmlns: "http://www.w3.org/2000/svg",
                viewBox: "0 0 24 24",
                class:
                  "icon icon-tabler icons-tabler-outline icon-tabler-trash-off",
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
                  d: "M3 3l18 18",
                }),
                h("path", {
                  d: "M4 7h3m4 0h9",
                }),
                h("path", {
                  d: "M10 11l0 6",
                }),
                h("path", {
                  d: "M14 14l0 3",
                }),
                h("path", {
                  d: "M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l.077 -.923",
                }),
                h("path", {
                  d: "M18.384 14.373l.616 -7.373",
                }),
                h("path", {
                  d: "M9 5v-1a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3",
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

const hblId = ref(null);
const selectedHBL = ref({});
const showConfirmViewHBLModal = ref(false);
const showConfirmRestoreHBLModal = ref(false);

const closeModal = () => {
  showConfirmViewHBLModal.value = false;
  showConfirmRestoreHBLModal.value = false;
  hblId.value = null;
  selectedHBL.value = null;
};

const hblRecord = ref({});

const fetchHBL = async (id) => {
  try {
    const response = await fetch(route("hbls.show", id), {
      method: "GET",
      headers: {
        "Content-Type": "application/json",
        "X-CSRF-TOKEN": document
          .querySelector('meta[name="csrf-token"]')
          .getAttribute("content"),
      },
    });

    if (!response.ok) {
      throw new Error("Network response was not ok.");
    } else {
      const data = await response.json();
      hblRecord.value = data.hbl;
    }
  } catch (error) {
    console.log(error);
  }
};

const confirmViewHBL = async (id) => {
  await fetchHBL(id);
  showConfirmViewHBLModal.value = true;
};

const confirmRestoreHBL = (id) => {
  hblId.value = id;
  showConfirmRestoreHBLModal.value = true;
};

const handleRestoreHBL = () => {
  router.get(route("hbls.restore", hblId.value), {
    preserveScroll: true,
    onSuccess: () => {
      closeModal();
      push.success("HBL record restored successfully!");
      router.visit(route("hbls.cancelled-hbls"), { only: ["hbls"] });
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
  filters.hblType = ["UBP", "Gift", "Door to Door"];
  filters.isHold = false;
  filters.warehouse = ["COLOMBO", "NINTAVUR"];
  filters.createdBy = "";
  filters.paymentStatus = [];
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
</script>

<template>
  <AppLayout title="Cancelled HBL List">
    <template #header>Cancelled HBL List</template>

    <Breadcrumb />
    <div class="flex justify-end mt-5">
      <Link :href="route('hbls.create')">
        <PrimaryButton> Create New HBL </PrimaryButton>
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
                Cancelled HBL List
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
                    class="mb-1 badge bg-slate-150 text-slate-800 hover:bg-slate-200 focus:bg-slate-200 active:bg-slate-200/80 dark:bg-navy-500 dark:text-navy-100 dark:hover:bg-navy-450 dark:focus:bg-navy-450 dark:active:bg-navy-450/90"
                  >
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
                    class="mb-1 ml-4 badge bg-slate-150 text-slate-800 hover:bg-slate-200 focus:bg-slate-200 active:bg-slate-200/80 dark:bg-navy-500 dark:text-navy-100 dark:hover:bg-navy-450 dark:focus:bg-navy-450 dark:active:bg-navy-450/90"
                  >
                    To Date
                  </div>
                  <div
                    class="badge bg-warning text-white hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:hover:bg-accent-focus dark:focus:bg-accent-focus dark:active:bg-accent/90"
                  >
                    {{ filters.toDate }}
                  </div>
                </div>
                <div class="grid sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6">
                  <div
                    v-for="(mode, index) in filters.cargoMode"
                    v-if="filters.cargoMode"
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
                    v-for="(type, index) in filters.hblType"
                    v-if="filters.hblType"
                    :key="index"
                    class="mb-1 badge bg-fuchsia-600 text-white dark:bg-fuchsia-600 ml-2"
                  >
                    {{ type }}
                  </div>

                  <div
                    v-for="(item, index) in filters.warehouse"
                    v-if="filters.warehouse"
                    :key="index"
                    class="mb-1 badge bg-pink-600 text-white dark:bg-pink-600 ml-2"
                  >
                    {{ item }}
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
                  :checked="data.columnVisibility.hbl"
                  @change="toggleColumnVisibility('hbl', $event)"
                />
                <span class="hover:cursor-pointer">HBL</span>
              </label>

              <label class="inline-flex items-center space-x-2">
                <Checkbox
                  :checked="data.columnVisibility.hbl_name"
                  @change="toggleColumnVisibility('hbl_name', $event)"
                />
                <span class="hover:cursor-pointer">HBL Name</span>
              </label>

              <label class="inline-flex items-center space-x-2">
                <Checkbox
                  :checked="data.columnVisibility.consignee_name"
                  @change="toggleColumnVisibility('consignee_name', $event)"
                />
                <span class="hover:cursor-pointer">Consignee Name</span>
              </label>

              <label class="inline-flex items-center space-x-2">
                <Checkbox
                  :checked="data.columnVisibility.consignee_address"
                  @change="toggleColumnVisibility('consignee_address', $event)"
                />
                <span class="hover:cursor-pointer">Consignee Address</span>
              </label>

              <label class="inline-flex items-center space-x-2">
                <Checkbox
                  :checked="data.columnVisibility.consignee_contact"
                  @change="toggleColumnVisibility('consignee_contact', $event)"
                />
                <span class="hover:cursor-pointer">Consignee Contact</span>
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
                  :checked="data.columnVisibility.hbl_type"
                  @change="toggleColumnVisibility('hbl_type', $event)"
                />
                <span class="hover:cursor-pointer">HBL Type</span>
              </label>

              <label class="inline-flex items-center space-x-2">
                <Checkbox
                  :checked="data.columnVisibility.warehouse"
                  @change="toggleColumnVisibility('warehouse', $event)"
                />
                <span class="hover:cursor-pointer">Warehouse</span>
              </label>

              <label class="inline-flex items-center space-x-2">
                <Checkbox
                  :checked="data.columnVisibility.status"
                  @change="toggleColumnVisibility('status', $event)"
                />
                <span class="hover:cursor-pointer">Status</span>
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
      <template #title> Filter HBL </template>

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

        <FilterHeader value="HBL Type" />

        <label class="inline-flex items-center space-x-2 mt-2">
          <Switch v-model="filters.hblType" label="UBP" value="UBP" />
        </label>

        <label class="inline-flex items-center space-x-2 mt-2">
          <Switch v-model="filters.hblType" label="Gift" value="Gift" />
        </label>

        <label class="inline-flex items-center space-x-2 mt-2">
          <Switch
            v-model="filters.hblType"
            label="Door to Door"
            value="Door to Door"
          />
        </label>

        <FilterBorder />

        <FilterHeader value="Payment Status" />

        <label
          v-for="item in paymentStatus"
          :key="item"
          class="inline-flex items-center space-x-2 mt-2"
        >
          <Switch v-model="filters.paymentStatus" :label="item" :value="item" />
        </label>

        <FilterBorder />

        <FilterHeader value="Is Hold" />

        <label class="inline-flex items-center space-x-2 mt-2">
          <Switch v-model="filters.isHold" label="Is Hold" value="true" />
        </label>

        <FilterBorder />

        <FilterHeader value="Warehouse" />

        <label class="inline-flex items-center space-x-2 mt-2">
          <Switch v-model="filters.warehouse" label="COLOMBO" value="COLOMBO" />
        </label>

        <label class="inline-flex items-center space-x-2 mt-2">
          <Switch
            v-model="filters.warehouse"
            label="NINTAVUR"
            value="NINTAVUR"
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

        <!--Filter Now Action Button-->
        <SoftPrimaryButton class="space-x-2" @click="applyFilters">
          <i class="fa-solid fa-filter"></i>
          <span>Apply Filters</span>
        </SoftPrimaryButton>

        <SoftPrimaryButton class="space-x-2" @click="resetFilter">
          <i class="fa-solid fa-refresh"></i>
          <span>Reset Filters</span>
        </SoftPrimaryButton>
      </template>
    </FilterDrawer>

    <RestoreHBLConfirmationModal
      :show="showConfirmRestoreHBLModal"
      @close="closeModal"
      @restore-hbl="handleRestoreHBL"
    />

    <HBLDetailModal
      :hbl="hblRecord"
      :show="showConfirmViewHBLModal"
      @close="closeModal"
    />
  </AppLayout>
</template>
