<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import {computed, reactive, ref} from "vue";
import {Grid, h, html} from "gridjs";
import {push} from "notivue";
import moment from "moment";
import SoftPrimaryButton from "@/Components/SoftPrimaryButton.vue";
import FilterDrawer from "@/Components/FilterDrawer.vue";
import Switch from "@/Components/Switch.vue";
import DatePicker from "@/Components/DatePicker.vue";
import FilterBorder from "@/Components/FilterBorder.vue";
import InputLabel from "@/Components/InputLabel.vue";
import FilterHeader from "@/Components/FilterHeader.vue";
import ColumnVisibilityPopover from "@/Components/ColumnVisibilityPopover.vue";
import Checkbox from "@/Components/Checkbox.vue";
import PaymentModal from "@/Pages/CashSettlement/Partials/PaymentModal.vue";
import NoRecordsFound from "@/Components/NoRecordsFound.vue";
import HoldConfirmationModal from "@/Pages/CashSettlement/Partials/HoldConfirmationModal.vue";
import {router, usePage} from "@inertiajs/vue3";
import HBLDetailModal from "@/Pages/Common/HBLDetailModal.vue";
import SimpleOverviewWidget from "@/Components/Widgets/SimpleOverviewWidget.vue";

const props = defineProps({
    drivers: {
        type: Object,
        default: () => {
        },
    },
    officers: {
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

const showFilters = ref(false);
const fromDate = moment(new Date()).subtract(1, "month").format("YYYY-MM-DD");
const toDate = moment(new Date()).format("YYYY-MM-DD");
const wrapperRef = ref(null);
let grid = null;
const perPage = ref(10);
const filters = reactive({
    fromDate: fromDate,
    toDate: toDate,
    isHold: false,
    drivers: {},
    officers: {},
    cargoMode: ["Air Cargo", "Sea Cargo"],
    paymentStatus: [],
});

const data = reactive({
    columnVisibility: {
        hbl: true,
        hbl_name: true,
        address: false,
        picked_date: true,
        weight: true,
        volume: true,
        grand_total: true,
        paid_amount: true,
        cargo_type: true,
        hbl_type: false,
        officer: false,
        is_hold: true,
        status: true,
        actions: true,
    },
    selectedData: {},
});

const toggleColumnVisibility = (columnName) => {
    data.columnVisibility[columnName] = !data.columnVisibility[columnName];
    updateGridConfig();
    grid.forceRender();
};

const updateGridConfig = () => {
    grid.updateConfig({
        columns: createColumns(),
    });
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
    {name: "HBL", hidden: !data.columnVisibility.hbl},
    {name: "Name", hidden: !data.columnVisibility.hbl_name},
    {name: "Address", hidden: !data.columnVisibility.address},
    {name: "Picked Date", hidden: !data.columnVisibility.picked_date},
    {
        name: "Weight",
        hidden: !data.columnVisibility.weight,
        formatter: (cell) => cell.toFixed(2),
    },
    {
        name: "Volume",
        hidden: !data.columnVisibility.volume,
        formatter: (cell) => cell.toFixed(3),
    },
    {
        name: "Amount",
        hidden: !data.columnVisibility.grand_total,
        formatter: (cell) => cell.toFixed(2),
    },
    {
        name: "Paid",
        hidden: !data.columnVisibility.paid_amount,
        formatter: (cell) => cell.toFixed(2),
    },
    {
        name: "Cargo Mode",
        sort: false,
        hidden: !data.columnVisibility.cargo_type,
        formatter: (_, row) =>
            row.cells[9].data == "Sea Cargo"
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
                    row.cells[9].data
                )
                : row.cells[9].data == "Air Cargo"
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
                        row.cells[9].data,
                    ])
                    : row.cells[9].data,
    },
    {name: "Delivery Type", hidden: !data.columnVisibility.hbl_type},
    {name: "Officer", hidden: !data.columnVisibility.officer},
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
        name: "Status",
        hidden: !data.columnVisibility.status,
        formatter: (cell) => {
            if (cell === "Full Paid") {
                return html(`<div class="badge space-x-2.5 text-success">
    <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="size-4 icon icon-tabler icons-tabler-outline icon-tabler-check"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l5 5l10 -10" /></svg>
    <span>${cell}</span>
  </div>`);
            } else if (cell === "Partial Paid") {
                return html(`<div class="badge space-x-2.5 text-warning">
    <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="size-4 icon icon-tabler icons-tabler-outline icon-tabler-question-mark"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M8 8a3.5 3 0 0 1 3.5 -3h1a3.5 3 0 0 1 3.5 3a3 3 0 0 1 -2 3a3 4 0 0 0 -2 4" /><path d="M12 19l0 .01" /></svg>
    <span>${cell}</span>
  </div>`);
            } else if (cell === "Unpaid") {
                return html(`<div class="badge space-x-2.5 text-error">
    <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="size-4 icon icon-tabler icons-tabler-outline icon-tabler-x"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M18 6l-12 12" /><path d="M6 6l12 12" /></svg>
    <span>${cell}</span>
  </div>`);
            } else {
                return cell;
            }
        },
    },
    {name: "Officer", hidden: !data.columnVisibility.officer},
    {
        name: "Actions",
        hidden: !data.columnVisibility.actions,
        sort: false,
        formatter: (_, row) => {
            return h("div", {}, [
                usePage().props.user.permissions.includes('cash.show') ?
                    h(
                        "a",
                        {
                            className:
                                "btn size-8 p-0 text-success hover:bg-success/20 focus:bg-success/20 active:bg-success/25 mr-2",
                            onClick: () => confirmViewHBL(row.cells[0].data?.id),
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
                usePage().props.user.permissions.includes('cash.update payment') ?
                    h(
                        "button",
                        {
                            className:
                                "btn size-8 p-0 text-error hover:bg-error/20 focus:bg-error/20 active:bg-error/25",
                            onClick: () => confirmPayment(row.cells),
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
                                    strokeWidth: 1.5,
                                },
                                [
                                    h("path", {
                                        strokeLinecap: "round",
                                        strokeLinejoin: "round",
                                        d: "M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z",
                                    }),
                                ]
                            ),
                        ]
                    ) : null,
                usePage().props.user.permissions.includes('cash.hold and release') ?
                    h(
                        "button",
                        {
                            className:
                                "btn size-8 p-0 text-primary hover:bg-primary/20 focus:bg-primary/20 active:bg-primary/25",
                            onClick: () => confirmIsHold(row.cells),
                            "x-tooltip..placement.bottom.primary": row.cells[12].data
                                ? "'Release HBL'"
                                : "'Hold HBL'",
                        },
                        [
                            row.cells[12].data
                                ? h(
                                    "svg",
                                    {
                                        xmlns: "http://www.w3.org/2000/svg",
                                        viewBox: "0 0 24 24",
                                        class:
                                            "icon icon-tabler icons-tabler-outline icon-tabler-player-play",
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
                                        class:
                                            "icon icon-tabler icons-tabler-outline icon-tabler-player-pause",
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
                    ) : null,
            ]);
        },
    },
];

const baseUrl = ref("/cash-settlement-list");

const constructUrl = () => {
    const params = new URLSearchParams();
    for (const key in filters) {
        if (filters.hasOwnProperty(key)) {
            params.append(key, filters[key].toString());
        }
    }
    return baseUrl.value + "?" + params.toString();
};

const initializeGrid = () => {
    const visibleColumns = Object.keys(data.columnVisibility);

    grid = new Grid({
        columns: createColumns(),
        search: {
            debounceTimeout: 1000,
            server: {
                url: (prev, keyword) => `${prev}?search=${keyword}`,
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
                    row.push({id: item.id});
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
                    row.push({id: item.id});
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

const totalRecord = ref(0);
const totalGrandAmount = ref(0);
const totalPaidAmount = ref(0);

const getCashSettlementSummary = async (filters) => {
    try {
        const response = await fetch("/cash-settlement-summery", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document
                    .querySelector('meta[name="csrf-token"]')
                    .getAttribute("content"),
            },
            body: JSON.stringify(filters),
        });

        if (!response.ok) {
            throw new Error("Network response was not ok.");
        }

        const data = await response.json();
        totalRecord.value = data.totalRecords;
        totalGrandAmount.value = data.sumAmount;
        totalPaidAmount.value = data.sumPaidAmount;
    } catch (error) {
        console.error("Error:", error);
    }
};

const cashReceived = async () => {
    const idList = selectedData.value.map((item) => item[0]);
    try {
        const response = await fetch("/cash-received", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document
                    .querySelector('meta[name="csrf-token"]')
                    .getAttribute("content"),
            },
            body: JSON.stringify({hbl_ids: idList}),
        });

        if (!response.ok) {
            throw new Error("Network response was not ok.");
        } else {
            window.location.reload();
            push.success("Cash collected successfully!");
        }
    } catch (error) {
        console.error("Error:", error);
    }
};

const isDataEmpty = computed(() => selectedData.value.length === 0);
const countOfSelectedData = computed(() => selectedData.value.length);
const valueOfSelectedData = computed(() => {
    return selectedData.value.reduce((total, item) => {
        const grandTotal = parseFloat(item[7] || 0);
        return total + grandTotal;
    }, 0);
});
const paidValueOfSelectedData = computed(() => {
    return selectedData.value.reduce((total, item) => {
        const grandTotal = parseFloat(item[8] || 0);
        return total + grandTotal;
    }, 0);
});

const pageReady = async () => {
    await getCashSettlementSummary();
    if (totalRecord.value > 0) {
        initializeGrid();
    } else {
        console.log("no data");
    }
};

pageReady();

const showConfirmPaymentModal = ref(false);
const hblData = ref({});

const confirmPayment = (row) => {
    hblData.value = row;
    showConfirmPaymentModal.value = true;
};

const closeModal = () => {
    showConfirmPaymentModal.value = false;
    hblData.value = null;
};

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
        route("hbls.toggle-hold", hblData.value[0].data.id),
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                closeHoldModal();
                push.success(
                    hblData.value[12].data
                        ? "Released " + hblData.value[1].data
                        : "Hold " + hblData.value[1].data
                );

                router.visit(route("back-office.cash-settlements.index"));
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
    filters.isHold = false;
    filters.drivers = {};
    filters.officers = {};
    filters.cargoMode = ["Air Cargo", "Sea Cargo"];
    filters.paymentStatus = [];
    applyFilters();
};

const showConfirmViewHBLModal = ref(false);
const hblId = ref(null);

const confirmViewHBL = async (id) => {
    hblId.value = id;
    showConfirmViewHBLModal.value = true;
};

const closeShowHBLModal = () => {
    showConfirmViewHBLModal.value = false;
};

const exportURL = computed(() => {
    const params = new URLSearchParams();
    for (const key in filters) {
        if (filters.hasOwnProperty(key)) {
            params.append(key, filters[key].toString());
        }
    }
    return '/cash-settlements/export' + "?" + params.toString();
});

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
    <AppLayout title="Cash Settlements">
        <template #header>Cash Settlements</template>

        <Breadcrumb/>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-2 mt-3">
            <SimpleOverviewWidget :count="totalRecord" bg-color="white" title="HBL Count"/>

            <SimpleOverviewWidget :count="totalGrandAmount.toLocaleString()" bg-color="white" title="HBL Amount"/>

            <SimpleOverviewWidget :count="totalPaidAmount.toLocaleString()" bg-color="white" title="HBL Paid Amount"/>

            <SimpleOverviewWidget :count="countOfSelectedData" bg-color="white" title="Selected HBL Count"/>

            <SimpleOverviewWidget :count="valueOfSelectedData.toLocaleString()" bg-color="white"
                                  title="Selected HBL Amount"/>

            <SimpleOverviewWidget :count="paidValueOfSelectedData.toLocaleString()" bg-color="white"
                                  title="Selected HBL Paid Amount"/>
        </div>

        <div class="card mt-4">
            <div>
                <div class="flex items-center justify-between p-2">
                    <div class="">
                        <div class="flex items-center">
                            <h2
                                class="text-base font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100"
                            >
                                Cash Settlement List
                            </h2>

                            <div class="flex m-3">
                                <select class="form-select mt-1.5 w-full rounded-full border border-slate-300 bg-white px-8 py-2 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:bg-navy-700 dark:hover:border-navy-400 dark:focus:border-accent" @change="handlePerPageChange">
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
                                        {{ mode }}
                                    </div>
                                    <div
                                        v-for="(type, index) in filters.deliveryType"
                                        v-if="filters.deliveryType"
                                        :key="index"
                                        class="badge bg-success text-white ml-2"
                                    >
                                        {{ type }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-1 ml-1 grid sm:grid-cols-2 md:grid-cols-2">
                        <div class="flex ml-5">
                            <ColumnVisibilityPopover>
                                <label class="inline-flex items-center space-x-2">
                                    <Checkbox
                                        :checked="data.columnVisibility.address"
                                        @change="toggleColumnVisibility('address', $event)"
                                    />
                                    <span class="hover:cursor-pointer">Address</span>
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
                                    <span class="hover:cursor-pointer">Delivery Type</span>
                                </label>

                                <label class="inline-flex items-center space-x-2">
                                    <Checkbox
                                        :checked="data.columnVisibility.officer"
                                        @change="toggleColumnVisibility('officer', $event)"
                                    />
                                    <span class="hover:cursor-pointer">Officer</span>
                                </label>
                            </ColumnVisibilityPopover>

                            <button
                                class="btn size-8 rounded-full p-0 hover:bg-slate-300/20 focus:bg-slate-300/20 active:bg-slate-300/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25"
                                x-tooltip.placement.top="'Filter result'"
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
                        <div>
                            <button
                                v-if="$page.props.user.permissions.includes('cash.cash received')"
                                :class="{
                  'bg-primary hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:hover:bg-accent-focus dark:focus:bg-accent-focus dark:active:bg-accent/90':
                    !isDataEmpty,
                  'bg-gray-300 cursor-not-allowed': isDataEmpty,
                }"
                                :disabled="isDataEmpty"
                                class="btn font-medium text-white"
                                @click="cashReceived"
                            >
                                Cash Received
                            </button>
                        </div>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="is-scrollbar-hidden min-w-full overflow-x-auto p-3">
                        <div v-if="totalRecord > 0" ref="wrapperRef"></div>
                        <NoRecordsFound v-else/>
                    </div>
                </div>
            </div>
        </div>

        <FilterDrawer :show="showFilters" @close="showFilters = false">
            <template #title> Filter Cash Settlement</template>

            <template #content>
                <div>
                    <InputLabel value="From"/>
                    <DatePicker v-model="filters.fromDate" placeholder="Choose date..."/>
                </div>

                <div>
                    <InputLabel value="To"/>
                    <DatePicker v-model="filters.toDate" placeholder="Choose date..."/>
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

                <FilterHeader value="Is Hold"/>

                <label class="inline-flex items-center space-x-2 mt-2">
                    <Switch v-model="filters.isHold" label="Is Hold" value="true"/>
                </label>

                <FilterBorder/>

                <FilterHeader value="Payment Status"/>

                <label
                    v-for="item in paymentStatus"
                    :key="item"
                    class="inline-flex items-center space-x-2 mt-2"
                >
                    <Switch v-model="filters.paymentStatus" :label="item" :value="item"/>
                </label>

                <FilterBorder/>

                <FilterHeader value="Select Drivers"/>

                <select
                    v-model="filters.drivers"
                    autocomplete="off"
                    class="mt-1.5 w-full"
                    placeholder="Select drivers..."
                    x-init="$el._tom = new Tom($el,{   plugins: ['remove_button']})"
                >
                    <option value="">Select drivers...</option>
                    <option v-for="(driver, id) in drivers" :key="id" :value="driver.id">
                        {{ driver.name }}
                    </option>
                </select>

                <FilterBorder/>

                <FilterHeader value="Select Officers"/>

                <select
                    v-model="filters.officers"
                    autocomplete="off"
                    class="mt-1.5 w-full"
                    placeholder="Select officers..."
                    x-init="$el._tom = new Tom($el,{   plugins: ['remove_button']})"
                >
                    <option value="">Select officers...</option>
                    <option
                        v-for="(officer, id) in officers"
                        :key="id"
                        :value="officer.id"
                    >
                        {{ officer.name }}
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

        <PaymentModal
            :hbl-data="hblData"
            :show="showConfirmPaymentModal"
            @close="closeModal"
        />

        <HoldConfirmationModal
            :hbl-data="hblData"
            :show="showConfirmHoldModal"
            @close="closeHoldModal"
            @toggle-hold="toggleHold"
        />

        <HBLDetailModal
            :hbl-id="hblId"
            :show="showConfirmViewHBLModal"
            @close="closeShowHBLModal"
        />
    </AppLayout>
</template>
