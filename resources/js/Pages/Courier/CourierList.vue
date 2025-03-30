<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import {computed, onMounted, reactive, ref} from "vue";
import {Grid, h, html} from "gridjs";
import Popper from "vue3-popper";
import {Link, router, usePage} from "@inertiajs/vue3";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import DatePicker from "@/Components/DatePicker.vue";
import InputLabel from "@/Components/InputLabel.vue";
import SoftPrimaryButton from "@/Components/SoftPrimaryButton.vue";
import FilterBorder from "@/Components/FilterBorder.vue";
import FilterDrawer from "@/Components/FilterDrawer.vue";
import moment from "moment";
import {push} from "notivue";
import NoRecordsFound from "@/Components/NoRecordsFound.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import Switch from "@/Components/Switch.vue";
import FilterHeader from "@/Components/FilterHeader.vue";
import DeleteCourierConfirmationModal from "@/Pages/Courier/Partials/DeleteCourierConfirmationModal.vue";
import ChangeStatusModal from "@/Pages/Courier/Partials/ChangeStatusModal.vue";
import ColumnVisibilityPopover from "@/Components/ColumnVisibilityPopover.vue";
import Checkbox from "@/Components/Checkbox.vue";

defineProps({
    zones: {
        type: Object,
        default: () => {
        },
    },
});

const wrapperRef = ref(null);
let grid = null;
const isData = ref(false)

const showFilters = ref(false);
const fromDate = moment(new Date()).subtract(12, "months").format("YYYY-MM-DD");
const toDate = moment(new Date()).format("YYYY-MM-DD");

const filters = reactive({
    fromDate: fromDate,
    toDate: toDate,
    cargoMode: ["Air Cargo", "Sea Cargo"],
    hblType: ["UPB", "Gift", "Door to Door"],
    status: ["pending", "on courier", "delivered"],
});

const data = reactive({
    columnVisibility: {
        select_couriers: true,
        id: false,
        courier_number: true,
        name: true,
        email: false,
        contact_number: true,
        nic: false,
        iq_number: false,
        address: false,
        consignee_name: true,
        consignee_nic: false,
        consignee_contact: true,
        consignee_address: false,
        consignee_note: false,
        courier_agent: true,
        cargo_type: true,
        hbl_type: true,
        status: true,
        created_at: false,
    },
});

const baseUrl = ref("/couriers-list");

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
                    let colName = Object.keys(data.columnVisibility).filter(
                        (key) => data.columnVisibility[key]
                    )[col.index];

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

const selectedCourierData = ref([]);
const isChangeStatus = ref(false);
const showChangeStatusModal = ref(false);

const createColumns = () => [
    {
        name: "#",
        sort: false,
        hidden: !data.columnVisibility.select_couriers,
        formatter: (_, row) => {
            return h("input", {
                type: "checkbox",
                className:
                    "form-checkbox is-basic size-4 rounded border-slate-400/70 checked:bg-primary checked:border-primary hover:border-primary focus:border-primary dark:border-navy-400 dark:checked:bg-accent dark:checked:border-accent dark:hover:border-accent dark:focus:border-accent",
                onChange: (event) => {
                    const isChecked = event.target.checked;
                    if (isChecked) {
                        const rowData = row.cells.map((cell) => cell.data);
                        selectedCourierData.value.push(rowData);
                    } else {
                        const index = selectedCourierData.value.findIndex((selectedRow) => {
                            const rowData = row.cells.map((cell) => cell.data);
                            return JSON.stringify(selectedRow) === JSON.stringify(rowData);
                        });
                        if (index !== -1) {
                            selectedCourierData.value.splice(index, 1);
                        }
                    }
                    if(selectedCourierData.value.length > 0 ){
                        isChangeStatus.value = true;
                    }else isChangeStatus.value = false;
                },
            });
        },
    },
    { name: "ID", hidden: !data.columnVisibility.id },
    { name: "Courier Number", hidden: !data.columnVisibility.courier_number },
    { name: "Name", hidden: !data.columnVisibility.name },
    { name: "Email", hidden: !data.columnVisibility.email, sort: false },
    { name: "Contact Number", hidden: !data.columnVisibility.contact_number, sort: false  },
    { name: "NIC", hidden: !data.columnVisibility.nic, sort: false  },
    { name: "IQ Number", hidden: !data.columnVisibility.iq_number, sort: false  },
    { name: "Address", hidden: !data.columnVisibility.address, sort: false  },
    { name: "Consignee Name", hidden: !data.columnVisibility.consignee_name },
    { name: "Consignee NIC", hidden: !data.columnVisibility.consignee_nic, sort: false  },
    { name: "Consignee Contact", hidden: !data.columnVisibility.consignee_contact, sort: false  },
    { name: "Consignee Address", hidden: !data.columnVisibility.consignee_address, sort: false  },
    { name: "Consignee Note", hidden: !data.columnVisibility.consignee_note, sort: false  },
    { name: "Courier Agent", hidden: !data.columnVisibility.courier_agent, sort: false  },
    {
        name: "Cargo Type",
        hidden: !data.columnVisibility.cargo_type,
        sort: false,
        formatter: (cell, row) =>
            cell == "Sea Cargo"
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
                    cell
                )
                : cell == "Air Cargo"
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
                        cell,
                    ])
                    : cell,
    },
    { name: "HBL Type", hidden: !data.columnVisibility.hbl_type  },
    {
        name: "Status",
        hidden: !data.columnVisibility.status,
        formatter: (cell) =>
            html(`<div class="${resolveStatus(cell.replace(/\s+/g, ''))}">${cell}</div>`),
    },
    {
        name: "Created At",
        sort: false,
        hidden: !data.columnVisibility.created_at,
        formatter: (cell) => moment(cell).format("MMMM Do YYYY, h:mm:ss A"),
    },
    {
        name: "Actions",
        sort: false,
        formatter: (_, row) => {
            return h("div", { class: "flex space-x-2" }, [
                usePage().props.user.permissions.includes("courier.edit")
                    ? h(
                        "a",
                        {
                            className:
                                "btn size-8 p-0 text-info hover:bg-info/20 focus:bg-info/20 active:bg-info/25",
                            href: route("couriers.edit", row.cells[1].data),
                        },
                        [
                            h("svg", { xmlns: "http://www.w3.org/2000/svg", viewBox: "0 0 512 512", class: "size-4.5", fill: "none" }, [
                                h("path", {
                                    d: "M471 58.9L453.1 71c9.4 9.4 9.4 24.6 0 33.9L424 134.1 377.9 88 407 58.9c9.4-9.4 24.6-9.4 33.9 0zM209.8 256.2L344 121.9 390.1 168 255.8 302.2c-2.9 2.9-6.5 5-10.4 6.1l-58.5 16.7 16.7-58.5c1.1-3.9 3.2-7.5 6.1-10.4zM373.1 25L175.8 222.2c-8.7 8.7-15 19.4-18.3 31.1l-28.6 100c-2.4 8.4-.1 17.4 6.1 23.6s15.2 8.5 23.6 6.1l100-28.6c11.8-3.4 22.5-9.7 31.1-18.3L487 138.9c28.1-28.1 28.1-73.7 0-101.8L474.9 25C446.8-3.1 401.2-3.1 373.1 25zM88 64C39.4 64 0 103.4 0 152V424c0 48.6 39.4 88 88 88H360c48.6 0 88-39.4 88-88V312c0-13.3-10.7-24-24-24s-24 10.7-24 24V424c0 22.1-17.9 40-40 40H88c-22.1 0-40-17.9-40-40V152c0-22.1 17.9-40 40-40H200c13.3 0 24-10.7 24-24s-10.7-24-24-24H88z",
                                    fill: "currentColor",
                                }),
                            ]),
                        ]
                    )
                    : null,
                usePage().props.user.permissions.includes("courier.delete")
                    ? h(
                        "button",
                        {
                            className:
                                "btn size-8 p-0 text-error hover:bg-error/20 focus:bg-error/20 active:bg-error/25",
                            onClick: () => confirmDeleteCourier(row.cells[1].data),
                        },
                        [
                            h("svg", { xmlns: "http://www.w3.org/2000/svg", viewBox: "0 0 448 512", class: "size-4.5", fill: "none" }, [
                                h("path", {
                                    d: "M170.5 51.6L151.5 80h145l-19-28.4c-1.5-2.2-4-3.6-6.7-3.6H177.1c-2.7 0-5.2 1.3-6.7 3.6zm147-26.6L354.2 80H368h48 8c13.3 0 24 10.7 24 24s-10.7 24-24 24h-8V432c0 44.2-35.8 80-80 80H112c-44.2 0-80-35.8-80-80V128H24c-13.3 0-24-10.7-24-24S10.7 80 24 80h8H80 93.8l36.7-55.1C140.9 9.4 158.4 0 177.1 0h93.7c18.7 0 36.2 9.4 46.6 24.9zM80 128V432c0 17.7 14.3 32 32 32H336c17.7 0 32-14.3 32-32V128H80zm80 64V400c0 8.8-7.2 16-16 16s-16-7.2-16-16V192c0-8.8 7.2-16 16-16s16 7.2 16 16zm80 0V400c0 8.8-7.2 16-16 16s-16-7.2-16-16V192c0-8.8 7.2-16 16-16s16 7.2 16 16zm80 0V400c0 8.8-7.2 16-16 16s-16-7.2-16-16V192c0-8.8 7.2-16 16-16s16 7.2 16 16z",
                                    fill: "currentColor",
                                }),
                            ]),
                        ]
                    )
                    : null,
            ]);
        },
    },
];


const resolveStatus = (status) =>
    ({
        pending: "badge bg-success/10 text-success dark:bg-success/15",
        oncourier: "badge bg-error/10 text-error dark:bg-error/15",
        delivered: "badge bg-info/10 text-info dark:bg-info/15",
    }[status]);

const updateGridConfig = () => {
    grid.updateConfig({
        columns: createColumns(),
    });
};

onMounted(() => {
    initializeGrid();
});

const showConfirmDeleteCourierModal = ref(false);
const courierId = ref(null);

const confirmDeleteCourier = (id) => {
    courierId.value = id;
    showConfirmDeleteCourierModal.value = true;
};

const closeModal = () => {
    showConfirmDeleteCourierModal.value = false;
};

const handleDeleteCourier = () => {
    router.delete(route("couriers.destroy", courierId.value), {
        preserveScroll: true,
        onSuccess: () => {
            closeModal();
            push.success("Courier Deleted Successfully!");
            courierId.value = null;
            router.visit(route("couriers.index"), {only: ["users"]});
        },
    });
};

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
                if (data.data.length === 0) {
                    // Return a placeholder row for "No matching data"
                    return [
                        visibleColumns.map(() => "No matching data found"),
                    ];
                }

                // Map the data to visible columns
                return data.data.map((item) => {
                    const row = [];
                    visibleColumns.forEach((column) => {
                        row.push(item[column]);
                    });
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
    applyFilters();
};

const exportURL = computed(() => {
    const params = new URLSearchParams();
    for (const key in filters) {
        if (filters.hasOwnProperty(key)) {
            params.append(key, filters[key].toString());
        }
    }
    return '/couriers/list/export' + "?" + params.toString();
});
</script>

<template>
    <AppLayout title="Courier List">
        <template #header>Courier List</template>

        <Breadcrumb/>

        <div class="flex justify-end mt-5">
            <Link
                v-if="$page.props.user.permissions.includes('courier.create')"
                :href="route('couriers.create')"
            >
                <PrimaryButton> Create New Courier</PrimaryButton>
            </Link>
        </div>

        <div class="card mt-4">
            <div>
                <div class="flex items-center justify-between p-2">
                    <h2
                        class="text-base font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100"
                    >
                        Courier List
                    </h2>

                    <div class="mt-1 ml-1 grid sm:grid-cols-2 md:grid-cols-2">
                        <div class="flex ml-5">
                            <ColumnVisibilityPopover>
                                <label class="inline-flex items-center space-x-2">
                                    <input
                                        :checked="data.columnVisibility.courier_number"
                                        class="form-checkbox is-basic size-5 rounded border-slate-400/70 checked:border-primary checked:bg-primary hover:border-primary focus:border-primary dark:border-navy-400 dark:checked:border-accent dark:checked:bg-accent dark:hover:border-accent dark:focus:border-accent"
                                        type="checkbox"
                                        @change="toggleColumnVisibility('courier_number', $event)"
                                    />
                                    <p>Courier Number</p>
                                </label>

                                <label class="inline-flex items-center space-x-2">
                                    <input
                                        :checked="data.columnVisibility.name"
                                        class="form-checkbox is-basic size-5 rounded border-slate-400/70 checked:border-primary checked:bg-primary hover:border-primary focus:border-primary dark:border-navy-400 dark:checked:border-accent dark:checked:bg-accent dark:hover:border-accent dark:focus:border-accent"
                                        type="checkbox"
                                        @change="toggleColumnVisibility('name', $event)"
                                    />
                                    <p>Name</p>
                                </label>

                                <label class="inline-flex items-center space-x-2">
                                    <input
                                        :checked="data.columnVisibility.email"
                                        class="form-checkbox is-basic size-5 rounded border-slate-400/70 checked:border-primary checked:bg-primary hover:border-primary focus:border-primary dark:border-navy-400 dark:checked:border-accent dark:checked:bg-accent dark:hover:border-accent dark:focus:border-accent"
                                        type="checkbox"
                                        @change="toggleColumnVisibility('email', $event)"
                                    />
                                    <p>Email</p>
                                </label>

                                <label class="inline-flex items-center space-x-2">
                                    <input
                                        :checked="data.columnVisibility.contact_number"
                                        class="form-checkbox is-basic size-5 rounded border-slate-400/70 checked:border-primary checked:bg-primary hover:border-primary focus:border-primary dark:border-navy-400 dark:checked:border-accent dark:checked:bg-accent dark:hover:border-accent dark:focus:border-accent"
                                        type="checkbox"
                                        @change="toggleColumnVisibility('contact_number', $event)"
                                    />
                                    <p>Contact Number</p>
                                </label>

                                <label class="inline-flex items-center space-x-2">
                                    <input
                                        :checked="data.columnVisibility.nic"
                                        class="form-checkbox is-basic size-5 rounded border-slate-400/70 checked:border-primary checked:bg-primary hover:border-primary focus:border-primary dark:border-navy-400 dark:checked:border-accent dark:checked:bg-accent dark:hover:border-accent dark:focus:border-accent"
                                        type="checkbox"
                                        @change="toggleColumnVisibility('nic', $event)"
                                    />
                                    <p>NIC</p>
                                </label>

                                <label class="inline-flex items-center space-x-2">
                                    <input
                                        :checked="data.columnVisibility.iq_number"
                                        class="form-checkbox is-basic size-5 rounded border-slate-400/70 checked:border-primary checked:bg-primary hover:border-primary focus:border-primary dark:border-navy-400 dark:checked:border-accent dark:checked:bg-accent dark:hover:border-accent dark:focus:border-accent"
                                        type="checkbox"
                                        @change="toggleColumnVisibility('iq_number', $event)"
                                    />
                                    <p>IQ Number</p>
                                </label>

                                <label class="inline-flex items-center space-x-2">
                                    <input
                                        :checked="data.columnVisibility.address"
                                        class="form-checkbox is-basic size-5 rounded border-slate-400/70 checked:border-primary checked:bg-primary hover:border-primary focus:border-primary dark:border-navy-400 dark:checked:border-accent dark:checked:bg-accent dark:hover:border-accent dark:focus:border-accent"
                                        type="checkbox"
                                        @change="toggleColumnVisibility('address', $event)"
                                    />
                                    <p>Address</p>
                                </label>

                                <label class="inline-flex items-center space-x-2">
                                    <input
                                        :checked="data.columnVisibility.consignee_name"
                                        class="form-checkbox is-basic size-5 rounded border-slate-400/70 checked:border-primary checked:bg-primary hover:border-primary focus:border-primary dark:border-navy-400 dark:checked:border-accent dark:checked:bg-accent dark:hover:border-accent dark:focus:border-accent"
                                        type="checkbox"
                                        @change="toggleColumnVisibility('consignee_name', $event)"
                                    />
                                    <p>Consignee Name</p>
                                </label>

                                <label class="inline-flex items-center space-x-2">
                                    <input
                                        :checked="data.columnVisibility.consignee_nic"
                                        class="form-checkbox is-basic size-5 rounded border-slate-400/70 checked:border-primary checked:bg-primary hover:border-primary focus:border-primary dark:border-navy-400 dark:checked:border-accent dark:checked:bg-accent dark:hover:border-accent dark:focus:border-accent"
                                        type="checkbox"
                                        @change="toggleColumnVisibility('consignee_nic', $event)"
                                    />
                                    <p>Consignee NIC</p>
                                </label>

                                <label class="inline-flex items-center space-x-2">
                                    <input
                                        :checked="data.columnVisibility.consignee_contact"
                                        class="form-checkbox is-basic size-5 rounded border-slate-400/70 checked:border-primary checked:bg-primary hover:border-primary focus:border-primary dark:border-navy-400 dark:checked:border-accent dark:checked:bg-accent dark:hover:border-accent dark:focus:border-accent"
                                        type="checkbox"
                                        @change="toggleColumnVisibility('consignee_contact', $event)"
                                    />
                                    <p>Consignee Contact</p>
                                </label>

                                <label class="inline-flex items-center space-x-2">
                                    <input
                                        :checked="data.columnVisibility.consignee_address"
                                        class="form-checkbox is-basic size-5 rounded border-slate-400/70 checked:border-primary checked:bg-primary hover:border-primary focus:border-primary dark:border-navy-400 dark:checked:border-accent dark:checked:bg-accent dark:hover:border-accent dark:focus:border-accent"
                                        type="checkbox"
                                        @change="toggleColumnVisibility('consignee_address', $event)"
                                    />
                                    <p>Consignee Address</p>
                                </label>

                                <label class="inline-flex items-center space-x-2">
                                    <input
                                        :checked="data.columnVisibility.consignee_note"
                                        class="form-checkbox is-basic size-5 rounded border-slate-400/70 checked:border-primary checked:bg-primary hover:border-primary focus:border-primary dark:border-navy-400 dark:checked:border-accent dark:checked:bg-accent dark:hover:border-accent dark:focus:border-accent"
                                        type="checkbox"
                                        @change="toggleColumnVisibility('consignee_note', $event)"
                                    />
                                    <p>Consignee Note</p>
                                </label>

                                <label class="inline-flex items-center space-x-2">
                                    <input
                                        :checked="data.columnVisibility.courier_agent"
                                        class="form-checkbox is-basic size-5 rounded border-slate-400/70 checked:border-primary checked:bg-primary hover:border-primary focus:border-primary dark:border-navy-400 dark:checked:border-accent dark:checked:bg-accent dark:hover:border-accent dark:focus:border-accent"
                                        type="checkbox"
                                        @change="toggleColumnVisibility('courier_agent', $event)"
                                    />
                                    <p>Courier Agent</p>
                                </label>

                                <label class="inline-flex items-center space-x-2">
                                    <input
                                        :checked="data.columnVisibility.cargo_type"
                                        class="form-checkbox is-basic size-5 rounded border-slate-400/70 checked:border-primary checked:bg-primary hover:border-primary focus:border-primary dark:border-navy-400 dark:checked:border-accent dark:checked:bg-accent dark:hover:border-accent dark:focus:border-accent"
                                        type="checkbox"
                                        @change="toggleColumnVisibility('cargo_type', $event)"
                                    />
                                    <p>Cargo Type</p>
                                </label>

                                <label class="inline-flex items-center space-x-2">
                                    <input
                                        :checked="data.columnVisibility.hbl_type"
                                        class="form-checkbox is-basic size-5 rounded border-slate-400/70 checked:border-primary checked:bg-primary hover:border-primary focus:border-primary dark:border-navy-400 dark:checked:border-accent dark:checked:bg-accent dark:hover:border-accent dark:focus:border-accent"
                                        type="checkbox"
                                        @change="toggleColumnVisibility('hbl_type', $event)"
                                    />
                                    <p>HBL Type</p>
                                </label>

                                <label class="inline-flex items-center space-x-2">
                                    <input
                                        :checked="data.columnVisibility.status"
                                        class="form-checkbox is-basic size-5 rounded border-slate-400/70 checked:border-primary checked:bg-primary hover:border-primary focus:border-primary dark:border-navy-400 dark:checked:border-accent dark:checked:bg-accent dark:hover:border-accent dark:focus:border-accent"
                                        type="checkbox"
                                        @change="toggleColumnVisibility('status', $event)"
                                    />
                                    <p>Status</p>
                                </label>

                                <label class="inline-flex items-center space-x-2">
                                    <input
                                        :checked="data.columnVisibility.created_at"
                                        class="form-checkbox is-basic size-5 rounded border-slate-400/70 checked:border-primary checked:bg-primary hover:border-primary focus:border-primary dark:border-navy-400 dark:checked:border-accent dark:checked:bg-accent dark:hover:border-accent dark:focus:border-accent"
                                        type="checkbox"
                                        @change="toggleColumnVisibility('created_at', $event)"
                                    />
                                    <p>Created At</p>
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
                                :class="{
                                      'bg-primary hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:hover:bg-accent-focus dark:focus:bg-accent-focus dark:active:bg-accent/90':
                                        isChangeStatus,
                                      'bg-gray-300 cursor-not-allowed': !isChangeStatus,
                                    }"
                                :disabled="!isChangeStatus"
                                class="btn font-medium text-white"
                                @click="showChangeStatusModal = true"
                            >
                                Change Status
                            </button>
                        </div>
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

        <DeleteCourierConfirmationModal
            :show="showConfirmDeleteCourierModal"
            @close="closeModal"
            @delete-courier="handleDeleteCourier"
        />

        <ChangeStatusModal
            :show="showChangeStatusModal"
            :selectedCouriers="selectedCourierData"
            @close="showChangeStatusModal = false"
        />

        <FilterDrawer :show="showFilters" @close="showFilters = false">
            <template #title> Filter Couriers</template>

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

                <FilterHeader value="Status"/>

                <label class="inline-flex items-center space-x-2 mt-2">
                    <Switch v-model="filters.status" label="Pending" value="pending"/>
                </label>

                <label class="inline-flex items-center space-x-2 mt-2">
                    <Switch
                        v-model="filters.status"
                        label="On Courier"
                        value="on courier"
                    />
                </label>

                <label class="inline-flex items-center space-x-2 mt-2">
                    <Switch
                        v-model="filters.status"
                        label="Delivered"
                        value="delivered"
                    />
                </label>

                <FilterBorder/>


            </template>
        </FilterDrawer>
    </AppLayout>
</template>
