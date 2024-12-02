<script setup>
import DestinationAppLayout from "@/Layouts/DestinationAppLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import {onMounted, reactive, ref} from "vue";
import {Grid} from "gridjs";

const wrapperRef = ref(null);
let grid = null;

const data = reactive({
    columnVisibility: {
        id: false,
        hbl: true,
        token: true,
        customer: true,
        reception: true,
        package_count: true,
        verified_at: true,
        verified_by: true,
        is_checked: true,
        note: true,
    },
});

const baseUrl = ref("/call-center/verification/verified/list");

const initializeGrid = () => {
    const visibleColumns = Object.keys(data.columnVisibility);

    grid = new Grid({
        columns: createColumns(),
        search: false,
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
    {
        name: "HBL",
        hidden: !data.columnVisibility.hbl,
        sort: false,
        formatter: cell => {
            return cell?.hbl_number
        }
    },
    {name: "Token", hidden: !data.columnVisibility.token},
    {name: "Customer", hidden: !data.columnVisibility.customer, sort: false},
    {name: "Reception", hidden: !data.columnVisibility.reception, sort: false},
    {name: "Packages", hidden: !data.columnVisibility.package_count, sort: false},
    {name: "Verified At", hidden: !data.columnVisibility.verified_at},
    {name: "Verified By", hidden: !data.columnVisibility.verified_by},
    {
        name: "Is Checked",
        hidden: !data.columnVisibility.is_checked,
        formatter: (cell) => {
            if (typeof cell === 'object' && cell !== null) {
                // Convert the object into a readable string format, e.g., "Passport: true, HBL Receipt: true"
                return Object.entries(cell)
                    .map(([key, value]) => `${key}: ${value}`)
                    .join(', ');
            }

            // If the value is not an object, just return it directly
            return cell;
        },
    },
    {
        name: "Note",
        hidden: !data.columnVisibility.note,
        formatter: (cell) => cell ? cell : '-',
    },
];

onMounted(() => {
    initializeGrid();
});

const constructUrl = () => {
    const params = new URLSearchParams();
    return baseUrl.value + "?" + params.toString();
};
</script>

<template>
    <DestinationAppLayout title="Verified Queue List">
        <template #header>Verified Queue List</template>

        <Breadcrumb />

        <div class="card mt-4">
            <div>
                <div class="flex items-center justify-between p-2">
                    <div class="">
                        <div class="flex">
                            <h2
                                class="text-base font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100"
                            >
                                Verified Queue List
                            </h2>
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
    </DestinationAppLayout>
</template>
