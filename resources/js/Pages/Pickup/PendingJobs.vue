<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import {computed, onMounted, reactive, ref} from "vue";
import {Grid, h, html} from "gridjs";
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
import {Link, router, usePage} from "@inertiajs/vue3";
import {push} from "notivue";
import DeletePickupConfirmationModal from "@/Pages/Pickup/Partials/DeletePickupConfirmationModal.vue";
import SimpleOverviewWidget from "@/Components/Widgets/SimpleOverviewWidget.vue";
import HBLDetailModal from "@/Pages/Common/HBLDetailModal.vue";
import RetryPickupConfirmationModal from "@/Pages/Pickup/Partials/RetryPickupConfirmationModal.vue";

const props = defineProps({
    drivers: {
        type: Object,
        default: () => {
        },
    },
    users: {
        type: Object,
        default: () => {
        },
    },
    zones: {
        type: Object,
        default: () => {
        },
    },
});

const wrapperRef = ref(null);
let grid = null;
const perPage = ref(10);

const showFilters = ref(false);
const fromDate = moment(new Date()).subtract(7, "days").format("YYYY-MM-DD");
const toDate = moment(new Date()).format("YYYY-MM-DD");

const filters = reactive({
    fromDate: fromDate,
    toDate: toDate,
    cargoMode: ["Air Cargo", "Sea Cargo"],
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
        pickup_date: true,
        cargo_type: true,
        driver: true,
        pickup_type: true,
        packages: true,
        exception_note: true,
        actions: true,
    },
});

const baseUrl = ref("/pickup-list");
const totalPickups = ref(0);

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
                    row.push({id: item.id});
                    visibleColumns.forEach((column) => {
                        row.push(item[column]);
                    });
                    return row;
                }),
            total: (response) => {
                if (response && response.meta) {
                    totalPickups.value = response.meta.total;
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
        attributes: (cell, row) => {
            // add these attributes to the td elements only
            if (cell && row.cells[8].data && row.cells[8].data !== '-') {
                return {
                    'data-cell-content': cell,
                    'style': 'background-color: #e0f2fe',
                };
            }

            if (cell && (row.cells[6].data < moment().format('YYYY-MM-DD'))) {
                return {
                    'data-cell-content': cell,
                    'style': 'background-color: #ffe4e6',
                };
            }
        },
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
    {
        name: "Reference",
        hidden: !data.columnVisibility.reference,
        attributes: (cell, row) => {
            // add these attributes to the td elements only
            if (cell && row.cells[8].data && row.cells[8].data !== '-') {
                return {
                    'data-cell-content': cell,
                    'style': 'background-color: #e0f2fe',
                };
            }

            if (cell && (row.cells[6].data < moment().format('YYYY-MM-DD'))) {
                return {
                    'data-cell-content': cell,
                    'style': 'background-color: #ffe4e6',
                };
            }
        }
    },
    {
        name: "Name",
        hidden: !data.columnVisibility.name,
        attributes: (cell, row) => {
            // add these attributes to the td elements only
            if (cell && row.cells[8].data && row.cells[8].data !== '-') {
                return {
                    'data-cell-content': cell,
                    'style': 'background-color: #e0f2fe',
                };
            }

            if (cell && (row.cells[6].data < moment().format('YYYY-MM-DD'))) {
                return {
                    'data-cell-content': cell,
                    'style': 'background-color: #ffe4e6',
                };
            }
        },
        formatter: (cell) => {
            if (!cell) return '';
            let value = cell.toString();

            if (value.length < 20) {
                return html(`<a style="text-decoration: underline; color: blue" href="pickups/get-pending-jobs-by-user/${cell}">${value}</a>`);
            }

            return html(`<a style="text-decoration: underline; color: blue" href="pickups/get-pending-jobs-by-user/${cell}">${value.substring(0, 20) + '...'}</a>`);
        }
    },
    {
        name: "Email",
        hidden: !data.columnVisibility.email,
        attributes: (cell, row) => {
            // add these attributes to the td elements only
            if (cell && row.cells[8].data && row.cells[8].data !== '-') {
                return {
                    'data-cell-content': cell,
                    'style': 'background-color: #e0f2fe',
                };
            }

            if (cell && (row.cells[6].data < moment().format('YYYY-MM-DD'))) {
                return {
                    'data-cell-content': cell,
                    'style': 'background-color: #ffe4e6',
                };
            }
        },
    },
    {
        name: "Address",
        hidden: !data.columnVisibility.address,
        sort: false,
        formatter: (cell) => {
            if (!cell) return '';
            let value = cell.toString();

            if (value.length < 20) {
                return value;
            }
            return value.substring(0, 20) + '...';
        },
        attributes: (cell, row) => {
            // add these attributes to the td elements only
            if (cell && row.cells[8].data && row.cells[8].data !== '-') {
                return {
                    'data-cell-content': cell,
                    'style': 'background-color: #e0f2fe',
                };
            }

            if (cell && (row.cells[6].data < moment().format('YYYY-MM-DD'))) {
                return {
                    'data-cell-content': cell,
                    'style': 'background-color: #ffe4e6',
                };
            }
        },
    },
    {
        name: "Contact",
        hidden: !data.columnVisibility.contact_number,
        sort: true,
        attributes: (cell, row) => {
            // add these attributes to the td elements only
            if (cell && row.cells[8].data && row.cells[8].data !== '-') {
                return {
                    'data-cell-content': cell,
                    'style': 'background-color: #e0f2fe',
                };
            }

            if (cell && (row.cells[6].data < moment().format('YYYY-MM-DD'))) {
                return {
                    'data-cell-content': cell,
                    'style': 'background-color: #ffe4e6',
                };
            }
        },
        formatter: (cell) => {
            if (!cell) return '';

            return html(`<a style="text-decoration: underline; color: blue" href="pickups/get-pending-jobs-by-user/${cell}">${cell}</a>`);
        }
    },
    {
        name: "Pickup Date",
        hidden: !data.columnVisibility.pickup_date,
        sort: true,
        attributes: (cell, row) => {
            // add these attributes to the td elements only
            if (cell && row.cells[8].data && row.cells[8].data !== '-') {
                return {
                    'data-cell-content': cell,
                    'style': 'background-color: #e0f2fe',
                };
            }

            if (cell && (row.cells[6].data < moment().format('YYYY-MM-DD'))) {
                return {
                    'data-cell-content': cell,
                    'style': 'background-color: #ffe4e6',
                };
            }
        }
    },
    {
        name: "Cargo Mode",
        sort: false,
        hidden: !data.columnVisibility.cargo_type,
        attributes: (cell, row) => {
            // add these attributes to the td elements only
            if (cell && row.cells[8].data && row.cells[8].data !== '-') {
                return {
                    'data-cell-content': cell,
                    'style': 'background-color: #e0f2fe',
                };
            }

            if (cell && (row.cells[6].data < moment().format('YYYY-MM-DD'))) {
                return {
                    'data-cell-content': cell,
                    'style': 'background-color: #ffe4e6',
                };
            }
        },
        formatter: (_, row) =>
            row.cells[7].data == "Sea Cargo"
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
                    row.cells[7].data
                )
                : row.cells[7].data == "Air Cargo"
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
                        row.cells[7].data,
                    ])
                    : row.cells[7].data,
    },

    {
        name: "Driver",
        hidden: !data.columnVisibility.driver,
        attributes: (cell, row) => {
            // add these attributes to the td elements only
            if (cell && row.cells[8].data && row.cells[8].data !== '-') {
                return {
                    'data-cell-content': cell,
                    'style': 'background-color: #e0f2fe',
                };
            }

            if (cell && (row.cells[6].data < moment().format('YYYY-MM-DD'))) {
                return {
                    'data-cell-content': cell,
                    'style': 'background-color: #ffe4e6',
                };
            }
        },
        formatter: (cell) => {
            return cell
                ? cell !== '-' && html(
                    `<div class="flex item-center"><svg xmlns="http://www.w3.org/2000/svg"  width="18"  height="18"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-steering-wheel mr-1"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" /><path d="M12 12m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" /><path d="M12 14l0 7" /><path d="M10 12l-6.75 -2" /><path d="M14 12l6.75 -2" /></svg> ${cell} </div>`
                )
                : null;
        },
    },
    {
        name: "Pickup Type",
        hidden: !data.columnVisibility.pickup_type,
        attributes: (cell, row) => {
            // add these attributes to the td elements only
            if (cell && row.cells[8].data && row.cells[8].data !== '-') {
                return {
                    'data-cell-content': cell,
                    'style': 'background-color: #e0f2fe',
                };
            }

            if (cell && (row.cells[6].data < moment().format('YYYY-MM-DD'))) {
                return {
                    'data-cell-content': cell,
                    'style': 'background-color: #ffe4e6',
                };
            }
        }
    },
    {
        name: "Packages",
        hidden: !data.columnVisibility.packages,
        sort: false,
        attributes: (cell, row) => {
            // add these attributes to the td elements only
            if (cell && row.cells[8].data && row.cells[8].data !== '-') {
                return {
                    'data-cell-content': cell,
                    'style': 'background-color: #e0f2fe',
                };
            }

            if (cell && (row.cells[6].data < moment().format('YYYY-MM-DD'))) {
                return {
                    'data-cell-content': cell,
                    'style': 'background-color: #ffe4e6',
                };
            }
        },
        formatter: (cell) => {
            if (!cell) return '';
            let value = cell.toString();

            if (value.length < 20) {
                return value;
            }
            return value.substring(0, 20) + '...';
        }
    },
    {
        name: "Exception",
        hidden: !data.columnVisibility.exception_note,
        attributes: (cell, row) => {
            // add these attributes to the td elements only
            if (cell && row.cells[8].data && row.cells[8].data !== '-') {
                return {
                    'data-cell-content': cell,
                    'style': 'background-color: #e0f2fe',
                };
            }

            if (cell && (row.cells[6].data < moment().format('YYYY-MM-DD'))) {
                return {
                    'data-cell-content': cell,
                    'style': 'background-color: #ffe4e6',
                };
            }
        },
        sort: false,
        formatter: (cell) => {
            return cell
                ? cell !== '-' && html(
                `<div class="badge space-x-2.5 bg-red-100 text-red-500 dark:bg-red-100"> ${cell}</div>`
            )
                : null;
        }
    },
    {
        name: "Actions",
        sort: false,
        hidden: !data.columnVisibility.actions,
        formatter: (_, row) => {
            return h("div", {className: "flex space-x-2"}, [
                usePage().props.user.permissions.includes('pickups.show') ?
                    h(
                        "a",
                        {
                            className:
                                "btn size-8 p-0 text-success hover:bg-success/20 focus:bg-success/20 active:bg-success/25 mr-2",
                            onClick: () => confirmViewPickup(row.cells[0].data?.id),
                            "x-tooltip..placement.bottom.primary": "'View Pickup'",
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
                usePage().props.user.permissions.includes('pickups.edit') ?
                    h(
                        "button",
                        {
                            className:
                                "btn size-8 p-0 text-info hover:bg-info/20 focus:bg-info/20 active:bg-info/25 mr-2",
                            onClick: () => router.visit(route("pickups.edit", row.cells[0].data?.id)),
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
                    )
                    : null,
                usePage().props.user.permissions.includes('pickups.retry') ?
                    row.cells[11].data ?
                        h(
                            "button",
                            {
                                className:
                                    "btn size-8 p-0 text-secondary hover:bg-secondary/20 focus:bg-secondary/20 active:bg-secondary/25 mr-2",
                                onClick: () => confirmRetry(row.cells[0].data?.id),
                                "x-tooltip..placement.bottom.primary": "'Retry'",
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
                                            d: "M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99",
                                        }),
                                    ]
                                ),
                            ]
                        ) : null
                    : null,
                usePage().props.user.permissions.includes('pickups.delete') ?
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
                totalPickups.value = data.meta.total;
                return data.data.map((item) => {
                    const row = [];
                    row.push({id: item.id});
                    visibleColumns.forEach((column) => {
                        row.push(item[column]);
                    });
                    return row;
                })
            },
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

const showConfirmAssignDriverModal = ref(false);
const isDataEmpty = computed(() => selectedData.value.length === 0);
const countOfSelectedData = computed(() => selectedData.value.length);
const idList = ref([]);

const confirmAssignDriver = () => {
    idList.value = selectedData.value.map((item) => item[0]);
    showConfirmAssignDriverModal.value = true;
};

const pickupId = ref(null);
const showConfirmDeletePickupModal = ref(false);

const closeModal = () => {
    showConfirmAssignDriverModal.value = false;
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
            router.visit(route("pickups.index"), {only: ["pickups"]});
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
    filters.createdBy = "";
    filters.zoneBy = "";
    applyFilters();
};

const exportURL = computed(() => {
    const params = new URLSearchParams();
    for (const key in filters) {
        if (filters.hasOwnProperty(key)) {
            params.append(key, filters[key].toString());
        }
    }
    return '/pickups/list/export' + "?" + params.toString();
});

const showConfirmViewPickupModal = ref(false);

const confirmViewPickup = async (id) => {
    pickupId.value = id;
    showConfirmViewPickupModal.value = true;
};

const closeViewModal = () => {
    showConfirmViewPickupModal.value = false;
    pickupId.value = null;
};

const showConfirmRetryModal = ref(false);

const confirmRetry = async (id) => {
    pickupId.value = id;
    showConfirmRetryModal.value = true;
};

const closeRetryModal = () => {
    showConfirmRetryModal.value = false;
    pickupId.value = null;
};

const handleRetryPickup = () => {
    router.get(route("pickups.exceptions.retry", pickupId.value), {
        preserveScroll: true,
        onSuccess: () => {
            closeRetryModal();
            push.success("Added into Pending Jobs!");
            router.visit(route("pickups.index"), {only: ["pickups"]});
        },
        onError: () => {
            closeRetryModal();
            push.error("Something went to wrong!");
        },
    });
}

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
    <AppLayout title="Pending Pickups">
        <template #header>Pending Pickups</template>

        <Breadcrumb/>

        <div class="flex justify-end mt-5">
            <Link v-if="$page.props.user.permissions.includes('pickups.create')" :href="route('pickups.create')">
                <PrimaryButton>Create New Pending Job</PrimaryButton>
            </Link>
        </div>

        <div class="flex justify-end mt-4">
            <SimpleOverviewWidget :count="totalPickups" class="bg-white" title="Total Pending Pickups">
                <svg class="icon icon-tabler icons-tabler-filled icon-tabler-briefcase text-info" fill="currentColor"
                     height="24" viewBox="0 0 24 24" width="24"
                     xmlns="http://www.w3.org/2000/svg">
                    <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                    <path
                        d="M22 13.478v4.522a3 3 0 0 1 -3 3h-14a3 3 0 0 1 -3 -3v-4.522l.553 .277a20.999 20.999 0 0 0 18.897 -.002l.55 -.275zm-8 -11.478a3 3 0 0 1 3 3v1h2a3 3 0 0 1 3 3v2.242l-1.447 .724a19.002 19.002 0 0 1 -16.726 .186l-.647 -.32l-1.18 -.59v-2.242a3 3 0 0 1 3 -3h2v-1a3 3 0 0 1 3 -3h4zm-2 8a1 1 0 0 0 -1 1a1 1 0 1 0 2 .01c0 -.562 -.448 -1.01 -1 -1.01zm2 -6h-4a1 1 0 0 0 -1 1v1h6v-1a1 1 0 0 0 -1 -1z"/>
                </svg>
            </SimpleOverviewWidget>
        </div>

        <div class="card mt-4">
            <div>
                <div class="flex items-center justify-between p-2">
                    <div class="">
                        <div class="flex items-center">
                            <h2
                                class="text-base font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100"
                            >
                                Pending Pickups
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
                            <div class="flex space-x-px">
                                <div>
                                    <div
                                        class="mb-1 badge bg-slate-150 text-slate-800 hover:bg-slate-200 focus:bg-slate-200 active:bg-slate-200/80 dark:bg-navy-500 dark:text-navy-100 dark:hover:bg-navy-450 dark:focus:bg-navy-450 dark:active:bg-navy-450/90"
                                    >
                                        <i class="mr-1 fas fa-calendar-alt"></i>
                                        From Date
                                    </div>
                                    <div
                                        class="tag badge bg-primary text-white hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:hover:bg-accent-focus dark:focus:bg-accent-focus dark:active:bg-accent/90"
                                    >
                                        {{ filters.fromDate }}
                                    </div>
                                </div>
                                <div>
                                    <div
                                        class="mb-1 ml-1 badge bg-slate-150 text-slate-800 hover:bg-slate-200 focus:bg-slate-200 active:bg-slate-200/80 dark:bg-navy-500 dark:text-navy-100 dark:hover:bg-navy-450 dark:focus:bg-navy-450 dark:active:bg-navy-450/90"
                                    >
                                        <i class="mr-1 far fa-calendar-alt"></i>
                                        To &nbsp;Date
                                    </div>
                                    <div
                                        class="tag badge bg-warning text-white hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:hover:bg-accent-focus dark:focus:bg-accent-focus dark:active:bg-accent/90"
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
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-1 ml-1 grid sm:grid-cols-2 md:grid-cols-2">
                        <div class="flex ml-5">
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
                                        :checked="data.columnVisibility.pickup_time_start"
                                        @change="
                      toggleColumnVisibility('pickup_time_start', $event)
                    "
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
                                class="flex btn size-8 rounded-full p-0 hover:bg-slate-300/20 focus:bg-slate-300/20 active:bg-slate-300/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25"
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
                        <div>
                            <PrimaryButton
                                v-if="$page.props.user.permissions.includes('pickups.assign driver')"
                                :disabled="isDataEmpty"
                                class="flex"
                                @click="confirmAssignDriver"
                            >
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
                                    <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                                    <path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0"/>
                                    <path d="M12 12m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/>
                                    <path d="M12 14l0 7"/>
                                    <path d="M10 12l-6.75 -2"/>
                                    <path d="M14 12l6.75 -2"/>
                                </svg>
                                Assign Driver ({{ countOfSelectedData }})
                            </PrimaryButton>
                        </div>
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
            <template #title> Filter Pending Jobs</template>

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

                <FilterBorder/>

                <FilterHeader value="Zone"/>

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

        <DeletePickupConfirmationModal
            :show="showConfirmDeletePickupModal"
            @close="closeModal"
            @delete-pickup="handleDeletePickup"
        />

        <HBLDetailModal
            :pickup-id="pickupId"
            :show="showConfirmViewPickupModal"
            @close="closeViewModal"
        />

        <RetryPickupConfirmationModal
            :show="showConfirmRetryModal"
            @close="closeRetryModal"
            @retry-pickup="handleRetryPickup"
        />
    </AppLayout>
</template>
