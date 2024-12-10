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
        released_packages: true,
        released_by: true,
        released_at: true,
        note: true,
        token: true,
    },
});

const baseUrl = ref("/call-center/package/released/list");

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
    },
    {
        name: "Packages",
        hidden: !data.columnVisibility.released_packages,
        sort: false,
        formatter: (cell) => {
            if (typeof cell === 'object' && cell !== null) {
                return Object.entries(cell)
                    .map(([key, value]) => `${key}: ${value}`)
                    .join(', ');
            }

            // If the value is not an object, just return it directly
            return cell;
        },
    },
    {name: "Released By", hidden: !data.columnVisibility.released_by, sort: false},
    {name: "Released At", hidden: !data.columnVisibility.released_at, sort: false},
    {
        name: "Note",
        hidden: !data.columnVisibility.note,
        formatter: (cell) => cell ? cell : '-',
    },
    {name: "Token", hidden: !data.columnVisibility.token, sort: false},
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
    <DestinationAppLayout title="Released Package List">
        <template #header>Released Package List</template>

        <Breadcrumb />

        <div class="card mt-4">
            <div>
                <div class="flex items-center justify-between p-2">
                    <div class="">
                        <div class="flex">
                            <h2
                                class="text-base font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100"
                            >
                                Released Package List
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
