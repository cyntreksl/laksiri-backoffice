<script setup>
import { computed, onMounted, reactive, ref } from "vue";
import { Link, router, usePage } from "@inertiajs/vue3";
import { Grid, h, html } from "gridjs";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SoftPrimaryButton from "@/Components/SoftPrimaryButton.vue";
import InputLabel from "@/Components/InputLabel.vue";
import DatePicker from "@/Components/DatePicker.vue";
import ColumnVisibilityPopover from "@/Components/ColumnVisibilityPopover.vue";
import Checkbox from "@/Components/Checkbox.vue";
import Switch from "@/Components/Switch.vue";
import HBLDetailModal from "@/Pages/Common/HBLDetailModal.vue";
import DestinationAppLayout from "@/Layouts/DestinationAppLayout.vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import NoRecordsFound from "@/Components/NoRecordsFound.vue";

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
const isData = ref(false)

const showFilters = ref(false);

const filters = reactive({
    fromDate: "",
    toDate: "",
    cargoMode: [],
    hblType: [],
    isHold: false,
    isDelayed: false,
    warehouse: [],
    createdBy: "",
    paymentStatus: [],
});

const data = reactive({
    columnVisibility: {
        id: false,
        hbl_number: true,
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
        tokens: true,
        actions: true,
        system_status: false,
    },
});

const baseUrl = ref("/call-center/hbl-list");

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
                    if (!columns.length) return `${prev}&order=id&dir=desc`;
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
    {
        name: "Actions",
        sort: false,
        hidden: !data.columnVisibility.actions,
        formatter: (_, row) => {
            return h("div", { className: "flex space-x-2" }, [
                usePage().props.user.permissions.includes("hbls.issue token") &&
                row.cells[16].data > 4.2
                    ? h(
                        "a",
                        {
                            className:
                                "btn size-8 p-0 text-info hover:bg-info/20 focus:bg-info/20 active:bg-info/25 mr-2",
                            href: route(
                                "call-center.hbls.create-token",
                                row.cells[0].data
                            ),
                            "x-tooltip..placement.bottom.primary":
                                "'Issue Token'",
                            target: "_blank",
                        },
                        [
                            h(
                                "svg",
                                {
                                    xmlns: "http://www.w3.org/2000/svg",
                                    viewBox: "0 0 24 24",
                                    class: "icon icon-tabler icons-tabler-outline icon-tabler-receipt",
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
                                        d: "M5 21v-16a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2v16l-3 -2l-2 2l-2 -2l-2 2l-2 -2l-3 2m4 -14h6m-6 4h6m-2 4h2",
                                    }),
                                ]
                            ),
                        ]
                    )
                    : null,
                usePage().props.user.permissions.includes("hbls.edit")
                    ? h(
                        "button",
                        {
                            className:
                                "btn size-8 p-0 text-info hover:bg-info/20 focus:bg-info/20 active:bg-info/25 mr-2",
                            onClick: () =>
                                router.visit(
                                    route("hbls.edit", row.cells[0].data)
                                ),
                            "x-tooltip..placement.bottom.primary":
                                "'Edit HBL'",
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
                    )
                    : null,
                usePage().props.user.permissions.includes("hbls.show")
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
                    : null,
                usePage().props.user.permissions.includes(
                    "hbls.hold and release"
                )
                    ? h(
                        "button",
                        {
                            className:
                                "btn size-8 p-0 text-primary hover:bg-primary/20 focus:bg-primary/20 active:bg-primary/25",
                            onClick: () => confirmIsHold(row.cells),
                            "x-tooltip..placement.bottom.primary": row
                                .cells[14].data
                                ? "'Release HBL'"
                                : "'Hold HBL'",
                        },
                        [
                            row.cells[14].data
                                ? h(
                                    "svg",
                                    {
                                        xmlns: "http://www.w3.org/2000/svg",
                                        viewBox: "0 0 24 24",
                                        class: "icon icon-tabler icons-tabler-outline icon-tabler-player-play",
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
                                            d: "M7 4v16l13 -8z",
                                        }),
                                    ]
                                )
                                : h(
                                    "svg",
                                    {
                                        xmlns: "http://www.w3.org/2000/svg",
                                        viewBox: "0 0 24 24",
                                        class: "icon icon-tabler icons-tabler-outline icon-tabler-player-pause",
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
                                            d: "M6 5m0 1a1 1 0 0 1 1 -1h2a1 1 0 0 1 1 1v12a1 1 0 0 1 -1 1h-2a1 1 0 0 1 -1 -1z",
                                        }),
                                        h("path", {
                                            d: "M14 5m0 1a1 1 0 0 1 1 -1h2a1 1 0 0 1 1 1v12a1 1 0 0 1 -1 1h-2a1 1 0 0 1 -1 -1z",
                                        }),
                                    ]
                                ),
                        ]
                    )
                    : null,
                usePage().props.user.permissions.includes("hbls.download pdf")
                    ? h(
                        "a",
                        {
                            className:
                                "btn size-8 p-0 text-pink-500 hover:bg-pink-500/20 focus:bg-pink-500/20 active:bg-pink-500/25",
                            href: route("hbls.download", row.cells[0].data),
                            "x-tooltip..placement.bottom.primary":
                                "'Download HBL'",
                        },
                        [
                            h(
                                "svg",
                                {
                                    xmlns: "http://www.w3.org/2000/svg",
                                    viewBox: "0 0 24 24",
                                    class: "icon icon-tabler icons-tabler-outline icon-tabler-download",
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
                    )
                    : null,
                usePage().props.user.permissions.includes("hbls.download pdf")
                    ? h(
                        "a",
                        {
                            className:
                                "btn size-8 p-0 text-pink-500 hover:bg-pink-500/20 focus:bg-pink-500/20 active:bg-pink-500/25",
                            href: route("hbls.download.baggage", row.cells[0].data),
                            "x-tooltip..placement.bottom.primary":
                                "'Download Baggage PDF'",
                        },
                        [
                            h('svg',
                                {
                                    xmlns: 'http://www.w3.org/2000/svg',
                                    width: '24',
                                    height: '24',
                                    viewBox: '0 0 24 24',
                                    fill: 'none',
                                    stroke: 'currentColor',
                                    'stroke-width': '2',
                                    'stroke-linecap': 'round',
                                    'stroke-linejoin': 'round',
                                    class: 'icon icon-tabler icons-tabler-outline icon-tabler-file-type-pdf'
                                }, [
                                    h('path', { stroke: 'none', d: 'M0 0h24v24H0z', fill: 'none' }),
                                    h('path', { d: 'M14 3v4a1 1 0 0 0 1 1h4' }),
                                    h('path', { d: 'M5 12v-7a2 2 0 0 1 2 -2h7l5 5v4' }),
                                    h('path', { d: 'M5 18h1.5a1.5 1.5 0 0 0 0 -3h-1.5v6' }),
                                    h('path', { d: 'M17 18h2' }),
                                    h('path', { d: 'M20 15h-3v6' }),
                                    h('path', { d: 'M11 15v6h1a2 2 0 0 0 2 -2v-2a2 2 0 0 0 -2 -2h-1z' })
                                ]
                            ),
                        ]
                    )
                    : null,
                usePage().props.user.permissions.includes("hbls.delete")
                    ? h(
                        "button",
                        {
                            className:
                                "btn size-8 p-0 text-error hover:bg-error/20 focus:bg-error/20 active:bg-error/25",
                            onClick: () =>
                                confirmDeleteHBL(row.cells[0].data),
                            "x-tooltip..placement.bottom.error":
                                "'Delete HBL'",
                        },
                        [
                            h(
                                "svg",
                                {
                                    xmlns: "http://www.w3.org/2000/svg",
                                    viewBox: "0 0 24 24",
                                    class: "icon icon-tabler icons-tabler-outline icon-tabler-trash",
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
                    )
                    : null,
                usePage().props.user.permissions.includes(
                    "hbls.download invoice"
                )
                    ? h(
                        "a",
                        {
                            className:
                                "btn size-8 rounded-full p-0 hover:bg-slate-300/20 focus:bg-slate-300/20 active:bg-slate-300/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25",
                            href: route(
                                "hbls.download.invoice",
                                row.cells[0].data
                            ),
                            "x-tooltip..placement.bottom.primary":
                                "'Invoice'",
                        },
                        [
                            h(
                                "svg",
                                {
                                    xmlns: "http://www.w3.org/2000/svg",
                                    viewBox: "0 0 24 24",
                                    class: "size-5",
                                    fill: "none",
                                    height: 24,
                                    width: 24,
                                    stroke: "currentColor",
                                    "stroke-width": 2,
                                    strokeLinecap: "round",
                                    strokeLinejoin: "round",
                                },
                                [
                                    h("path", {
                                        d: "M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5m-7.5 3h7.5m3-9h3.375c.621 0 1.125.504 1.125 1.125V18a2.25 2.25 0 0 1-2.25 2.25M16.5 7.5V18a2.25 2.25 0 0 0 2.25 2.25M16.5 7.5V4.875c0-.621-.504-1.125-1.125-1.125H4.125C3.504 3.75 3 4.254 3 4.875V18a2.25 2.25 0 0 0 2.25 2.25h13.5M6 7.5h3v3H6v-3Z",
                                    }),
                                ]
                            ),
                        ]
                    )
                    : null,
                usePage().props.user.permissions.includes(
                    "hbls.download barcode"
                )
                    ? h(
                        "a",
                        {
                            className:
                                "btn size-8 rounded-full p-0 hover:bg-slate-300/20 focus:bg-slate-300/20 active:bg-slate-300/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25",
                            href: route(
                                "hbls.download.barcode",
                                row.cells[0].data
                            ),
                            "x-tooltip..placement.bottom.primary":
                                "'Download Barcode'",
                        },
                        [
                            h(
                                "svg",
                                {
                                    xmlns: "http://www.w3.org/2000/svg",
                                    viewBox: "0 0 24 24",
                                    class: "icon icon-tabler icons-tabler-outline icon-tabler-file-barcode",
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
                                        d: "M0 0h24v24H0z",
                                        fill: "none",
                                        stroke: "none",
                                    }),
                                    h("path", {
                                        d: "M14 3v4a1 1 0 0 0 1 1h4",
                                    }),
                                    h("path", {
                                        d: "M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z",
                                    }),
                                    h("path", {
                                        d: "M8 13h1v3h-1z",
                                    }),
                                    h("path", {
                                        d: "M12 13v3",
                                    }),
                                    h("path", {
                                        d: "M15 13h1v3h-1z",
                                    }),
                                ]
                            ),
                        ]
                    )
                    : null,
            ]);
        },
    },
    {
        name: "ID",
        hidden: !data.columnVisibility.id,
        formatter: (_, row) => {
            return row.cells[0].data;
        }
    },
    {
        name: "HBL",
        hidden: !data.columnVisibility.hbl_number,
        formatter: (_, row) => {
            return row.cells[1].data;
        }
    },
    { name: "HBL Name", hidden: !data.columnVisibility.hbl_name },
    {
        name: "Consignee Name",
        hidden: !data.columnVisibility.consignee_name,
        formatter: (_, row) => {
            return row.cells[3].data;
        }
    },
    {
        name: "Consignee Address",
        hidden: !data.columnVisibility.consignee_address,
        sort: false,
        formatter: (_, row) => {
            return row.cells[4].data;
        }
    },
    {
        name: "Consignee Contact",
        hidden: !data.columnVisibility.consignee_contact,
        sort: false,
        formatter: (_, row) => {
            return row.cells[5].data;
        }
    },
    {
        name: "Email",
        hidden: !data.columnVisibility.email,
        sort: false,
        formatter: (_, row) => {
            return row.cells[6].data;
        }
    },
    {
        name: "Address",
        hidden: !data.columnVisibility.address,
        sort: false,
        formatter: (_, row) => {
            return row.cells[7].data;
        } },
    {
        name: "Contact",
        hidden: !data.columnVisibility.contact_number,
        sort: false,
        formatter: (_, row) => {
            console.log(row.cells);
            return row.cells[8].data;
        }
    },
    {
        name: "Cargo Mode",
        sort: false,
        hidden: !data.columnVisibility.cargo_type,
        formatter: (_, row) =>
            row.cells[9].data == "Sea Cargo"
                ? h(
                      "span",
                      { className: "flex" },
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
                      row.cells[9].data
                  )
                : row.cells[9].data == "Air Cargo"
                ? h("span", { className: "flex space-x-2" }, [
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
                      row.cells[9].data,
                  ])
                : row.cells[9].data,
    },
    {
        name: "HBL Type",
        hidden: !data.columnVisibility.hbl_type,
        formatter: (_, row) => {
            return row.cells[10].data;
        }
        },
    {
        name: "Warehouse",
        hidden: !data.columnVisibility.warehouse,
        formatter: (_, row) => {
            return row.cells[11].data;
        }
    },
    {
        name: "Status",
        hidden: !data.columnVisibility.status,
        formatter: (_, row) => {
            return row.cells[12].data;
        }
    },
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
        name: "Issued Token",
        hidden: !data.columnVisibility.tokens,
        sort: false,
        formatter: (_, row) => {
            return row.cells[14].data;
        }
    },
    {
        name: "System Status",
        hidden: !data.columnVisibility.system_status,
        formatter: (_, row) => {
            return row.cells[15].data;
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

const hblId = ref(null);
const selectedHBL = ref({});

const showConfirmViewHBLModal = ref(false);

const confirmViewHBL = async (id) => {
    hblId.value = id;
    showConfirmViewHBLModal.value = true;
};

const resetFilter = () => {
    filters.fromDate = "";
    filters.toDate = "";
    filters.cargoMode = [];
    filters.hblType = [];
    filters.isHold = false;
    filters.warehouse = [];
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
    showConfirmViewHBLModal.value = false;
    hblId.value = null;
    selectedHBL.value = null;
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

        <Breadcrumb />
        <div class="flex justify-end mt-5">
            <Link
                v-if="$page.props.user.permissions.includes('hbls.create')"
                :href="route('hbls.create')"
            >
                <PrimaryButton> Create New HBL</PrimaryButton>
            </Link>
        </div>

        <div class="card border">
            <h2
                class="mt-3 ml-5 text-base font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100"
            >
                HBL Filters
            </h2>
            <div class="grid grid-cols-1 lg:grid-cols-7 gap-5 p-5">
                <div class="space-y-2">
                    <InputLabel value="From" />
                    <DatePicker
                        v-model="filters.fromDate"
                        placeholder="Choose date..."
                    />

                    <InputLabel value="To" />
                    <DatePicker
                        v-model="filters.toDate"
                        placeholder="Choose date..."
                    />
                </div>

                <div>
                    <h2
                        class="font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100"
                    >
                        Cargo Mode
                    </h2>

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
                </div>

                <div>
                    <h2
                        class="font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100"
                    >
                        Delivery Type
                    </h2>

                    <label class="block items-center space-x-2 mt-2">
                        <Switch
                            v-model="filters.hblType"
                            label="UPB"
                            value="UPB"
                        />
                    </label>

                    <label class="block items-center space-x-2 mt-2">
                        <Switch
                            v-model="filters.hblType"
                            label="Gift"
                            value="Gift"
                        />
                    </label>

                    <label class="block items-center space-x-2 mt-2">
                        <Switch
                            v-model="filters.hblType"
                            label="Door to Door"
                            value="Door to Door"
                        />
                    </label>
                </div>

                <div>
                    <h2
                        class="font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100"
                    >
                        Payment Status
                    </h2>

                    <label
                        v-for="item in paymentStatus"
                        :key="item"
                        class="block items-center space-x-2 mt-2"
                    >
                        <Switch
                            v-model="filters.paymentStatus"
                            :label="item"
                            :value="item"
                        />
                    </label>
                </div>

                <div>
                    <h2
                        class="font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100"
                    >
                        Is Hold
                    </h2>

                    <label class="block items-center space-x-2 mt-2">
                        <Switch
                            v-model="filters.isHold"
                            label="Is Hold"
                            value="true"
                        />
                    </label>

                    <h2
                        class="font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100 mt-2"
                    >
                        Is Delayed
                    </h2>

                    <label class="block items-center space-x-2 mt-2">
                        <Switch
                            v-model="filters.isDelayed"
                            label="Is Delayed"
                            value="true"
                        />
                    </label>
                </div>

                <div>
                    <h2
                        class="font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100"
                    >
                        Warehouse
                    </h2>

                    <label class="block items-center space-x-2 mt-2">
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
                </div>

                <div>
                    <h2
                        class="font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100"
                    >
                        Created By
                    </h2>

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
                </div>
            </div>

            <div class="bg-slate-100 p-5 space-x-4 text-right">
                <SoftPrimaryButton class="space-x-2" @click="applyFilters">
                    <i class="fa-solid fa-filter"></i>
                    <span>Apply Filters</span>
                </SoftPrimaryButton>
                <!--Filter Rest Button-->
                <SoftPrimaryButton class="space-x-2" @click="resetFilter">
                    <i class="fa-solid fa-refresh"></i>
                    <span>Clear Filters</span>
                </SoftPrimaryButton>
            </div>
        </div>

        <div class="card mt-4">
            <div>
                <div class="flex items-center justify-between p-2">
                    <div class="">
                        <div class="flex">
                            <h2
                                class="text-base font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100"
                            >
                                HBL List
                            </h2>
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

        <HBLDetailModal
            :hbl-id="hblId"
            :show="showConfirmViewHBLModal"
            @close="closeModal"
        />
    </AppLayout>
</template>
