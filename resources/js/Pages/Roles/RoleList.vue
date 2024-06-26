<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import {Link, router} from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import {onMounted, reactive, ref} from "vue";
import {Grid, h} from "gridjs";
import {push} from "notivue";

const props = defineProps({
    roles: {
        type: Object,
        default: () => {
        },
    },
});

const wrapperRef = ref(null);
let grid = null;

const data = reactive({
    columnVisibility: {
        id: false,
        name: true,
        permissions: true,
        actions: true,
    },
});

const baseUrl = ref("/role-list");

const initializeGrid = () => {
    const visibleColumns = Object.keys(data.columnVisibility);

    grid = new Grid({
        columns: createColumns(),
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
    { name: "ID", hidden: !data.columnVisibility.id },
    { name: "Role Name", hidden: !data.columnVisibility.name },
    { name: "Permissions", hidden: !data.columnVisibility.permissions },
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
                        onClick: () => router.visit(route("users.roles.edit", row.cells[0].data)),
                        "x-tooltip..placement.bottom.primary": "'Edit Role'",
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
                        onClick: () => confirmDeleteRole(row.cells[0].data),
                        "x-tooltip..placement.bottom.error": "'Delete Role'",
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
    return baseUrl.value + "?" + params.toString();
};

const showConfirmDeleteRoleModal = ref(false);
const roleId = ref(null);

const confirmDeleteRole = (id) => {
    roleId.value = id;
    showConfirmDeleteRoleModal.value = true;
};

const closeModal = () => {
    showConfirmDeleteRoleModal.value = false;
    roleId.value = null;
};

const handleDeleteRole = () => {
    router.delete(route("hbls.destroy", roleId.value), {
        preserveScroll: true,
        onSuccess: () => {
            closeModal();
            push.success("Role Deleted Successfully!");
            roleId.value = null;
            router.visit(route("users.roles.index"));
        },
        onError: () => {
            closeModal();
            push.error("Something went to wrong!");
        },
    });
};
</script>

<template>
    <AppLayout title="Roles">
        <template #header>Roles</template>

        <Breadcrumb/>

        <div class="flex justify-end mt-5">
            <Link :href="route('users.roles.create')">
                <PrimaryButton> Create New Role</PrimaryButton>
            </Link>
        </div>

        <div class="card mt-4">
            <div class="flex items-center justify-between p-2">
                <h2
                    class="text-base font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100"
                >
                    Roles
                </h2>
            </div>

            <div class="mt-3">
                <div class="is-scrollbar-hidden min-w-full overflow-x-auto">
                    <div ref="wrapperRef"></div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
