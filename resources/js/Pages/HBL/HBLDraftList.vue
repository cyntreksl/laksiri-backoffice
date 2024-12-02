<script setup>
import {computed, onMounted, reactive, ref} from "vue";
import {router, usePage} from "@inertiajs/vue3";
import {Grid, h, html} from "gridjs";
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import ColumnVisibilityPopover from "@/Components/ColumnVisibilityPopover.vue";
import Checkbox from "@/Components/Checkbox.vue";
import DeleteHBLConfirmationModal from "@/Pages/HBL/Partials/DeleteHBLConfirmationModal.vue";
import {push} from "notivue";
import HBLDetailModal from "@/Pages/Common/HBLDetailModal.vue";

const props = defineProps({
    users: {
        type: Object,
        default: () => {
        },
    },
    hbls: {
        type: Object,
        default: () => {
        },
    },
    paymentStatus: {
        type: Object,
        default: () => {
        },
    },
});

const wrapperRef = ref(null);
let grid = null;
const perPage = ref(10);
const data = reactive({
    columnVisibility: {
        id: false,
        reference: false,
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
        hbl_number: false,
        actions: true,
    },
});

const baseUrl = ref("/hbl-draft-list");

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
    {name: "Reference", hidden: true},
    {
        name: "HBL", hidden: !data.columnVisibility.hbl,

        formatter: (_, row) => {
            return row.cells[15].data || row.cells[1].data
        },
    },
    {
        name: "HBL Name",
        hidden: !data.columnVisibility.hbl_name,
    },
    {name: "Consignee Name", hidden: !data.columnVisibility.consignee_name},
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
    {name: "Email", hidden: !data.columnVisibility.email, sort: false},
    {name: "Address", hidden: !data.columnVisibility.address, sort: false},
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
                    {className: "flex"},
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
                    ? h("span", {className: "flex space-x-2"}, [
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
    {name: "HBL Type", hidden: !data.columnVisibility.hbl_type},
    {name: "Warehouse", hidden: !data.columnVisibility.warehouse},
    {name: "Status", hidden: !data.columnVisibility.status},
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
            return h("div", {className: "flex space-x-2"}, [
                usePage().props.user.permissions.includes('hbls.show') ?
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
                    ) : null,
                usePage().props.user.permissions.includes('hbls.delete') ?
                    h(
                        "button",
                        {
                            className:
                                "btn size-8 p-0 text-error hover:bg-error/20 focus:bg-error/20 active:bg-error/25",
                            onClick: () => confirmDeleteHBL(row.cells[0].data),
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
    return baseUrl.value + "?" + params.toString();
};

const showConfirmDeleteHBLModal = ref(false);
const hblId = ref(null);
const selectedHBL = ref({});

const confirmDeleteHBL = (id) => {
    hblId.value = id;
    showConfirmDeleteHBLModal.value = true;
};

const handleDeleteHBL = () => {
    router.delete(route("hbls.destroy", hblId.value), {
        preserveScroll: true,
        onSuccess: () => {
            closeModal();
            push.success("HBL record Deleted Successfully!");
            router.visit(route("hbls.index"), {only: ["hbls"]});
        },
        onError: () => {
            closeModal();
            push.error("Something went to wrong!");
        },
    });
};

const showConfirmViewHBLModal = ref(false);

const confirmViewHBL = async (id) => {
    hblId.value = id;
    showConfirmViewHBLModal.value = true;
};

const exportURL = computed(() => {
    const params = new URLSearchParams();
    return '/hbls/list/export' + "?" + params.toString();
});

const closeModal = () => {
    showConfirmDeleteHBLModal.value = false;
    showConfirmViewHBLModal.value = false;
    hblId.value = null;
    selectedHBL.value = null;
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
    <AppLayout title="HBL Draft List">
        <template #header>HBL Draft List</template>

        <Breadcrumb/>

        <div class="card mt-4">
            <div>
                <div class="flex items-center justify-between p-2">
                    <div class="">
                        <div class="flex items-center">
                            <h2
                                class="text-base font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100"
                            >
                                HBL Draft List
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

        <DeleteHBLConfirmationModal
            :show="showConfirmDeleteHBLModal"
            @close="closeModal"
            @delete-hbl="handleDeleteHBL"
        />

        <HBLDetailModal
            :hbl-id="hblId"
            :show="showConfirmViewHBLModal"
            @close="closeModal"
        />
    </AppLayout>
</template>
