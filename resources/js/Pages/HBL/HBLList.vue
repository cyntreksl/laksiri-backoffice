<script setup>
import {computed, onMounted, reactive, ref} from "vue";
import {Link, router, usePage} from "@inertiajs/vue3";
import {Grid, h, html} from "gridjs";
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
import DeleteHBLConfirmationModal from "@/Pages/HBL/Partials/DeleteHBLConfirmationModal.vue";
import {push} from "notivue";
import HoldConfirmationModal from "@/Pages/HBL/Partials/HoldConfirmationModal.vue";
import HBLDetailModal from "@/Pages/Common/HBLDetailModal.vue";
import CallFlagModal from "@/Pages/HBL/Partials/CallFlagModal.vue";

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
const showFilters = ref(false);
const fromDate = moment("2023-01-01").format("YYYY-MM-DD");
const toDate = moment(new Date()).format("YYYY-MM-DD");

const filters = reactive({
    fromDate: fromDate,
    toDate: toDate,
    cargoMode: ["Air Cargo", "Sea Cargo"],
    hblType: ["UPB", "Gift", "Door to Door"],
    isHold: false,
    warehouse: ["COLOMBO", "NINTAVUR"],
    createdBy: "",
    paymentStatus: [],
});

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
        warehouse: false,
        status: false,
        is_hold: false,
        view: true,
        actions: true,
    },
});

const baseUrl = ref("/hbl-list");

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
    {name: "Reference", hidden: !data.columnVisibility.reference},
    {name: "HBL", hidden: !data.columnVisibility.hbl},
    {
        name: "HBL Name",
        hidden: !data.columnVisibility.hbl_name,
        formatter: (cell) => {
            if (!cell) return "";
            let value = cell.toString();

            if (value.length < 20) {
                return html(
                    `<a style="text-decoration: underline; color: blue" href="hbls/get-hbls-by-user/${cell}">${value}</a>`
                );
            }

            return html(
                `<a style="text-decoration: underline; color: blue" href="hbls/get-hbls-by-user/${cell}">${
                    value.substring(0, 20) + "..."
                }</a>`
            );
        },
    },
    {name: "Consignee Name", hidden: !data.columnVisibility.consignee_name},
    {
        name: "Consignee Address",
        hidden: !data.columnVisibility.consignee_address,
        sort: false,
        formatter: (cell) => {
            if (!cell) return "";
            let value = cell.toString();

            if (value.length < 10) {
                return html(
                    `<a style="text-decoration: underline; color: blue" href="hbls/get-hbls-by-user/${cell}">${value}</a>`
                );
            }

        //     hover icon and show full address
            // Display with info icon and tooltip for addresses longer than 20 characters
            return html(
                `<a style="text-decoration: underline; color: blue" href="hbls/get-hbls-by-user/${cell}">
                ${value.substring(0, 10) + "..."}
            </a>
            <i class="fa fa-info-circle"
            x-tooltip.placement.bottom.error="'${value}'"
               class="info-icon"></i>`
            );
        },
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
        formatter: (cell) => {
            if (!cell) return "";

            return html(
                `<a style="text-decoration: underline; color: blue" href="hbls/get-hbls-by-user/${cell}">${cell} </a>`
            );
        },
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
                            class: "icon icon-tabler icons-tabler-outline icon-tabler-ship mr-2",
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
                                class: "icon icon-tabler icons-tabler-outline icon-tabler-plane mr-2",
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
    // {
    //     name: "Actions",
    //     sort: false,
    //     hidden: !data.columnVisibility.actions,
    //     formatter: (_, row) => {
    //         return h("div", {className: "flex space-x-2"}, [
    //             usePage().props.user.permissions.includes("hbls.show")
    //                 ? h(
    //                     "a",
    //                     {
    //                         className:
    //                             "btn size-8 p-0 text-success hover:bg-success/20 focus:bg-success/20 active:bg-success/25 mr-2",
    //                         onClick: () => confirmViewHBL(row.cells[0].data),
    //                         "x-tooltip..placement.bottom.primary":
    //                             "'View HBL'",
    //                     },
    //                     [
    //                         h(
    //                             "svg",
    //                             {
    //                                 xmlns: "http://www.w3.org/2000/svg",
    //                                 viewBox: "0 0 24 24",
    //                                 class: "icon icon-tabler icons-tabler-outline icon-tabler-eye",
    //                                 fill: "none",
    //                                 height: 24,
    //                                 width: 24,
    //                                 stroke: "currentColor",
    //                                 strokeLinecap: "round",
    //                                 strokeLinejoin: "round",
    //                             },
    //                             [
    //                                 h("path", {
    //                                     d: "M0 0h24v24H0z",
    //                                     fill: "none",
    //                                     stroke: "none",
    //                                 }),
    //                                 h("path", {
    //                                     d: "M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0",
    //                                 }),
    //                                 h("path", {
    //                                     d: "M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6",
    //                                 }),
    //                             ]
    //                         ),
    //                     ]
    //                 )
    //                 : null,
    //         ]);
    //     },
    // },
    {
        name: "",
        sort: false,
        formatter: (_, row) => {
            return usePage().props.user.permissions.includes("hbls.show")
                ? h(
                    "a",
                    {
                        className:
                            "btn size-8 p-0 text-success hover:bg-success/20 focus:bg-success/20 active:bg-success/25 mr-2",
                        onClick: () => confirmViewHBL(row.cells[0].data),
                        "x-tooltip..placement.bottom.primary":
                            "'View HBL'",
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
                )
                : null
        }
    },
    {
        name: "",
        sort: false,
        hidden: !data.columnVisibility.actions,
        formatter: (_, row) => {
            return h("div", { className: "flex space-x-2 relative group" }, [
                // Popover action button (Hamburger or similar)
                h(
                    "a",
                    {
                        className:
                            "btn size-5 p-0 hover:bg-dark/20 focus:bg-dark/20 active:bg-dark/25 mr-2",
                    },
                    [
                        h(
                            "svg",
                            {
                                xmlns: "http://www.w3.org/2000/svg",
                                viewBox: "0 0 128 512",
                                class: "icon icon-ellipsis",
                                fill: "currentColor",
                                height: 24,
                                width: 24,
                            },
                            [
                                h("path", {
                                    d: "M64 360a56 56 0 1 0 0 112 56 56 0 1 0 0-112zm0-160a56 56 0 1 0 0 112 56 56 0 1 0 0-112zM120 96A56 56 0 1 0 8 96a56 56 0 1 0 112 0z",
                                })
                            ]
                        )

                    ]),

                // Popover content
                h("div", {
                    className: "absolute hidden group-hover:block bg-white shadow-lg rounded-md mt-2 w-32 z-100 right-full mr-2 transition-opacity duration-200 ease-in-out opacity-0 group-hover:opacity-100",
                }, [
                    usePage().props.user.permissions.includes("hbls.edit") && h("a", {
                        href: "#",
                        className: "block px-4 py-2 text-gray-700 hover:bg-gray-100",
                        onClick: () => confirmViewCallFlagModal(row.cells),
                    }, "Call Flag"),

                    usePage().props.user.permissions.includes("hbls.edit") && h("a", {
                        href: "#",
                        className: "block px-4 py-2 text-gray-700 hover:bg-gray-100",
                        onClick: () => router.visit(route("hbls.edit", row.cells[0].data)),
                    }, "Edit"),

                    usePage().props.user.permissions.includes("hbls.show") && h("a", {
                        href: "#",
                        className: "block px-4 py-2 text-gray-700 hover:bg-gray-100",
                        onClick: () => confirmViewHBL(row.cells[0].data),
                    }, "View HBL"),

                    usePage().props.user.permissions.includes("hbls.hold and release") && h("a", {
                        href: "#",
                        className: "block px-4 py-2 text-gray-700 hover:bg-gray-100",
                        onClick: () => confirmIsHold(row.cells),
                    }, row.cells[14].data ? "Release HBL" : "Hold HBL"),

                    usePage().props.user.permissions.includes("hbls.download pdf") && h("a", {
                        href: route("hbls.download", row.cells[0].data),
                        className: "block px-4 py-2 text-gray-700 hover:bg-gray-100",
                    }, "Download HBL"),

                    usePage().props.user.permissions.includes("hbls.download invoice") && h("a", {
                        href: route("hbls.download.invoice", row.cells[0].data),
                        className: "block px-4 py-2 text-gray-700 hover:bg-gray-100",
                    }, "Invoice"),

                    usePage().props.user.permissions.includes("hbls.download barcode") && h("a", {
                        href: route("hbls.download.barcode", row.cells[0].data),
                        className: "block px-4 py-2 text-gray-700 hover:bg-gray-100",
                    }, "Barcode"),

                    usePage().props.user.permissions.includes("hbls.delete") && h("a", {
                        href: "#",
                        className: "block px-4 py-2 text-gray-700 hover:bg-gray-100",
                        onClick: () => confirmDeleteHBL(row.cells[0].data),
                    }, "Delete"),
                ]),
            ]);
        }
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

const hblData = ref({});

const showConfirmHoldModal = ref(false);

const confirmIsHold = (row) => {
    hblData.value = row;
    showConfirmHoldModal.value = true;
};

const closeHoldModal = () => {
    showConfirmHoldModal.value = false;
};

const toggleHold = () => {
    router.put(
        route("hbls.toggle-hold", hblData.value[0].data),
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                closeHoldModal();
                push.success(
                    hblData.value[14].data
                        ? "Released " + hblData.value[1].data
                        : "Hold " + hblData.value[1].data
                );
                router.visit(route("hbls.index"));
                hblData.value = {};
            },
            onError: () => {
                push.error("Something went to wrong!");
            },
        }
    );
};

const resetFilter = () => {
    filters.fromDate = fromDate;
    filters.toDate = toDate;
    filters.cargoMode = ["Air Cargo", "Sea Cargo", "Door to Door"];
    filters.hblType = ["UPB", "Gift", "Door to Door"];
    filters.isHold = false;
    filters.warehouse = ["COLOMBO", "NINTAVUR"];
    filters.createdBy = "";
    filters.paymentStatus = [];
    applyFilters();
};

const exportURL = computed(() => {
    const params = new URLSearchParams();
    for (const key in filters) {
        if (filters.hasOwnProperty(key)) {
            params.append(key, filters[key].toString());
        }
    }
    return "/hbls/list/export" + "?" + params.toString();
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

    grid.forceRender();
};

const showConfirmViewCallFlagModal = ref(false);
const hblName = ref("");

const confirmViewCallFlagModal = async (row) => {
    hblId.value = row[0].data;
    hblName.value = row[3].data;
    showConfirmViewCallFlagModal.value = true;
};

const closeCallFlagModal = () => {
    showConfirmViewCallFlagModal.value = false;
    hblId.value = null;
    hblName.value = "";
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
    <AppLayout title="HBL List">
        <template #header>HBL List</template>

        <Breadcrumb/>
        <div class="flex justify-end mt-5">
            <Link
                v-if="$page.props.user.permissions.includes('hbls.create')"
                :href="route('hbls.create')"
            >
                <PrimaryButton> Create New HBL</PrimaryButton>
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
                                HBL List
                            </h2>
                            <div class="flex m-3">
                                <select
                                    class="form-select w-full rounded border border-slate-300 bg-white px-8 py-1 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:bg-navy-700 dark:hover:border-navy-400 dark:focus:border-accent"
                                    @change="handlePerPageChange"
                                >
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
                                class="mr-4 cursor-pointer self-start"
                                x-tooltip.info.placement.bottom="'Applied Filters'"
                            >
                                Filter Options:
                            </div>
                            <div class="flex">
                                <div>
                                    <div
                                        class="mb-1 tag rounded-r-none bg-slate-150 text-slate-800 hover:bg-slate-200 focus:bg-slate-200 active:bg-slate-200/80 dark:bg-navy-500 dark:text-navy-100 dark:hover:bg-navy-450 dark:focus:bg-navy-450 dark:active:bg-navy-450/90"
                                    >
                                        From Date
                                    </div>
                                    <div
                                        class="mb-1 tag rounded-l-none bg-primary text-white hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:hover:bg-accent-focus dark:focus:bg-accent-focus dark:active:bg-accent/90"
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
                                <div
                                    class="grid sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6"
                                >
                                    <div
                                        v-for="(
                                            mode, index
                                        ) in filters.cargoMode"
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
                                        v-for="(
                                            item, index
                                        ) in filters.warehouse"
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
                                    @change="
                                        toggleColumnVisibility(
                                            'reference',
                                            $event
                                        )
                                    "
                                />
                                <span class="hover:cursor-pointer"
                                >Reference</span
                                >
                            </label>

                            <label class="inline-flex items-center space-x-2">
                                <Checkbox
                                    :checked="data.columnVisibility.hbl"
                                    @change="
                                        toggleColumnVisibility('hbl', $event)
                                    "
                                />
                                <span class="hover:cursor-pointer">HBL</span>
                            </label>

                            <label class="inline-flex items-center space-x-2">
                                <Checkbox
                                    :checked="data.columnVisibility.hbl_name"
                                    @change="
                                        toggleColumnVisibility(
                                            'hbl_name',
                                            $event
                                        )
                                    "
                                />
                                <span class="hover:cursor-pointer"
                                >HBL Name</span
                                >
                            </label>

                            <label class="inline-flex items-center space-x-2">
                                <Checkbox
                                    :checked="
                                        data.columnVisibility.consignee_name
                                    "
                                    @change="
                                        toggleColumnVisibility(
                                            'consignee_name',
                                            $event
                                        )
                                    "
                                />
                                <span class="hover:cursor-pointer"
                                >Consignee Name</span
                                >
                            </label>

                            <label class="inline-flex items-center space-x-2">
                                <Checkbox
                                    :checked="
                                        data.columnVisibility.consignee_address
                                    "
                                    @change="
                                        toggleColumnVisibility(
                                            'consignee_address',
                                            $event
                                        )
                                    "
                                />
                                <span class="hover:cursor-pointer"
                                >Consignee Address</span
                                >
                            </label>

                            <label class="inline-flex items-center space-x-2">
                                <Checkbox
                                    :checked="
                                        data.columnVisibility.consignee_contact
                                    "
                                    @change="
                                        toggleColumnVisibility(
                                            'consignee_contact',
                                            $event
                                        )
                                    "
                                />
                                <span class="hover:cursor-pointer"
                                >Consignee Contact</span
                                >
                            </label>

                            <label class="inline-flex items-center space-x-2">
                                <Checkbox
                                    :checked="data.columnVisibility.email"
                                    @change="
                                        toggleColumnVisibility('email', $event)
                                    "
                                />
                                <span class="hover:cursor-pointer">Email</span>
                            </label>

                            <label class="inline-flex items-center space-x-2">
                                <Checkbox
                                    :checked="data.columnVisibility.address"
                                    @change="
                                        toggleColumnVisibility(
                                            'address',
                                            $event
                                        )
                                    "
                                />
                                <span class="hover:cursor-pointer"
                                >Address</span
                                >
                            </label>

                            <label class="inline-flex items-center space-x-2">
                                <Checkbox
                                    :checked="
                                        data.columnVisibility.contact_number
                                    "
                                    @change="
                                        toggleColumnVisibility(
                                            'contact_number',
                                            $event
                                        )
                                    "
                                />
                                <span class="hover:cursor-pointer"
                                >Contact</span
                                >
                            </label>

                            <label class="inline-flex items-center space-x-2">
                                <Checkbox
                                    :checked="data.columnVisibility.cargo_type"
                                    @change="
                                        toggleColumnVisibility(
                                            'cargo_type',
                                            $event
                                        )
                                    "
                                />
                                <span class="hover:cursor-pointer"
                                >Cargo Mode</span
                                >
                            </label>

                            <label class="inline-flex items-center space-x-2">
                                <Checkbox
                                    :checked="data.columnVisibility.hbl_type"
                                    @change="
                                        toggleColumnVisibility(
                                            'hbl_type',
                                            $event
                                        )
                                    "
                                />
                                <span class="hover:cursor-pointer"
                                >HBL Type</span
                                >
                            </label>

                            <label class="inline-flex items-center space-x-2">
                                <Checkbox
                                    :checked="data.columnVisibility.warehouse"
                                    @change="
                                        toggleColumnVisibility(
                                            'warehouse',
                                            $event
                                        )
                                    "
                                />
                                <span class="hover:cursor-pointer"
                                >Warehouse</span
                                >
                            </label>

                            <label class="inline-flex items-center space-x-2">
                                <Checkbox
                                    :checked="data.columnVisibility.status"
                                    @change="
                                        toggleColumnVisibility('status', $event)
                                    "
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
            <template #title> Filter HBL</template>

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
                    <DatePicker
                        v-model="filters.fromDate"
                        placeholder="Choose date..."
                    />
                </div>

                <div>
                    <InputLabel value="To"/>
                    <DatePicker
                        v-model="filters.toDate"
                        placeholder="Choose date..."
                    />
                </div>

                <FilterBorder/>

                <FilterHeader value="Cargo Mode"/>

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

                <FilterBorder/>

                <FilterHeader value="HBL Type"/>

                <label class="inline-flex items-center space-x-2 mt-2">
                    <Switch v-model="filters.hblType" label="UPB" value="UPB"/>
                </label>

                <label class="inline-flex items-center space-x-2 mt-2">
                    <Switch
                        v-model="filters.hblType"
                        label="Gift"
                        value="Gift"
                    />
                </label>

                <label class="inline-flex items-center space-x-2 mt-2">
                    <Switch
                        v-model="filters.hblType"
                        label="Door to Door"
                        value="Door to Door"
                    />
                </label>

                <FilterBorder/>

                <FilterHeader value="Payment Status"/>

                <label
                    v-for="item in paymentStatus"
                    :key="item"
                    class="inline-flex items-center space-x-2 mt-2"
                >
                    <Switch
                        v-model="filters.paymentStatus"
                        :label="item"
                        :value="item"
                    />
                </label>

                <FilterBorder/>

                <FilterHeader value="Is Hold"/>

                <label class="inline-flex items-center space-x-2 mt-2">
                    <Switch
                        v-model="filters.isHold"
                        label="Is Hold"
                        value="true"
                    />
                </label>

                <FilterBorder/>

                <FilterHeader value="Warehouse"/>

                <label class="inline-flex items-center space-x-2 mt-2">
                    <Switch
                        v-model="filters.warehouse"
                        label="COLOMBO"
                        value="COLOMBO"
                    />
                </label>

                <label class="inline-flex items-center space-x-2 mt-2">
                    <Switch
                        v-model="filters.warehouse"
                        label="NINTAVUR"
                        value="NINTAVUR"
                    />
                </label>

                <FilterBorder/>

                <FilterHeader value="Created By"/>

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


            </template>
        </FilterDrawer>

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

        <HoldConfirmationModal
            :hbl-data="hblData"
            :show="showConfirmHoldModal"
            @close="closeHoldModal"
            @toggle-hold="toggleHold"
        />

        <CallFlagModal
            :caller-name="hblName"
            :hbl-id="hblId"
            :show="showConfirmViewCallFlagModal"
            @close="closeCallFlagModal"/>
    </AppLayout>
</template>
