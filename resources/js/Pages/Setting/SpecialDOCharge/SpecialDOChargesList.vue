<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import {router} from "@inertiajs/vue3";
import {ref} from "vue";
import {push} from "notivue";
import {useConfirm} from "primevue/useconfirm";
import {FilterMatchMode} from "@primevue/core/api";
import Tag from "primevue/tag";
import Card from "primevue/card";
import Button from "primevue/button";
import Column from "primevue/column";
import Select from "primevue/select";
import DataTable from "primevue/datatable";
import ContextMenu from "primevue/contextmenu";

defineProps({
    charges: {
        type: Object,
        default: () => {
        },
    },
    branches: {
        type: Array,
        default: () => []
    },
});

const confirm = useConfirm();
const perPage = ref(10);
const currentPage = ref(1);
const cm = ref();
const cargoTypes = ref(['Sea Cargo', 'Air Cargo']);
const hblTypes = ref(['UPB', 'Door to Door', 'Gift']);
const selectedCharge = ref(null);

const menuModel = ref([
    {
        label: "Edit",
        icon: "pi pi-fw pi-pencil",
        command: () => router.visit(route("setting.prices.edit", selectedCharge.value.id)),
    },
    {
        label: "Delete",
        icon: "pi pi-fw pi-times",
        command: () => confirmDeleteCharge(selectedCharge.value.id),
    },
]);

const filters = ref({
    global: {value: null, matchMode: FilterMatchMode.CONTAINS},
    hbl_type: { value: null, matchMode: FilterMatchMode.EQUALS },
    cargo_mode: { value: null, matchMode: FilterMatchMode.EQUALS },
    agent: { value: null, matchMode: FilterMatchMode.EQUALS },
});

const onPageChange = (event) => {
    perPage.value = event.rows;
    currentPage.value = event.page + 1;
};

const onRowContextMenu = (event) => {
    cm.value.show(event.originalEvent);
};

const confirmDeleteCharge = (id) => {
    confirm.require({
        message: 'Are you sure you want to delete DO Charge record?',
        header: 'Delete DO charge?',
        icon: 'pi pi-info-circle',
        rejectLabel: 'Cancel',
        rejectProps: {
            label: 'Cancel',
            severity: 'secondary',
            outlined: true
        },
        acceptProps: {
            label: 'Delete',
            severity: 'warn'
        },
        accept: () => {
            router.delete(route("setting.special-do-charges.destroy", id), {
                preserveScroll: true,
                onSuccess: () => {
                    push.success("Special DO charge record deleted successfully!");
                    router.visit(route("setting.special-do-charges.index"));
                },
                onError: () => {
                    push.error("Something went to wrong!");
                },
            });
        },
        reject: () => {
        }
    });
};

const resolveCargoType = (cargo_type) => {
    switch (cargo_type) {
        case 'Sea Cargo':
            return {
                icon: "ti ti-sailboat",
                color: "success",
            };
        case 'Air Cargo':
            return {
                icon: "ti ti-plane-tilt",
                color: "info",
            };
        default:
            return null;
    }
};

const resolveHBLType = (hbl_type) => {
    switch (hbl_type) {
        case 'UPB':
            return 'secondary';
        case 'Gift':
            return 'warn';
        case 'Door to Door':
            return 'info';
        default:
            return null;
    }
};
</script>

<template>
    <AppLayout title="Special DO Charges">
        <template #header>Special DO Charges</template>

        <Breadcrumb/>

        <div>
            <Card class="my-5">
                <template #content>
                    <ContextMenu ref="cm" :model="menuModel" @hide="selectedCharge = null" />
                    <DataTable
                        v-model:contextMenuSelection="selectedCharge"
                        v-model:filters="filters"
                        :rows="perPage"
                        :rowsPerPageOptions="[5, 10, 20, 50, 100]"
                        :totalRecords="Object.keys(charges).length"
                        :value="charges"
                        context-menu
                        dataKey="id"
                        filter-display="menu"
                        paginator
                        removable-sort
                        row-hover
                        tableStyle="min-width: 50rem"
                        @page="onPageChange"
                        @rowContextmenu="onRowContextMenu">

                        <template #header>
                            <div class="flex flex-col sm:flex-row justify-between items-center mb-2">
                                <div class="text-lg font-medium">
                                    Special DO Charges
                                </div>
                                <div>
                                    <Button
                                        class="w-full"
                                        @click="router.visit(route('setting.special-do-charges.create'))"
                                    >
                                        Create New DO Charge
                                    </Button>
                                </div>
                            </div>
                        </template>

                        <template #empty> No Special DO Charges found.</template>

                        <Column field="agent" header="Agent" sortable>
                            <template #filter="{ filterModel, filterCallback }">
                                <Select v-model="filterModel.value" :options="branches" :showClear="true" option-label="name" option-value="name" placeholder="Select One" style="min-width: 12rem" />
                            </template>
                        </Column>

                        <Column field="cargo_type" header="Cargo Type" sortable>
                            <template #body="slotProps">
                                <Tag :icon="resolveCargoType(slotProps.data.cargo_type).icon" :severity="resolveCargoType(slotProps.data.cargo_type).color" :value="slotProps.data.cargo_type" class="text-sm"></Tag>
                            </template>
                            <template #filter="{ filterModel, filterCallback }">
                                <Select v-model="filterModel.value" :options="cargoTypes" :showClear="true" placeholder="Select One" style="min-width: 12rem" />
                            </template>
                        </Column>

                        <Column field="condition" header="Condition"></Column>

                        <Column field="collected" header="Collected"></Column>

                        <Column field="quantity_basis" header="Quantity Basis"></Column>

                        <Column field="hbl_type" header="HBL Type" sortable>
                            <template #body="slotProps">
                                <Tag :severity="resolveHBLType(slotProps.data.hbl_type)" :value="slotProps.data.hbl_type"></Tag>
                            </template>
                            <template #filter="{ filterModel, filterCallback }">
                                <Select v-model="filterModel.value" :options="hblTypes" :showClear="true" placeholder="Select One" style="min-width: 12rem" />
                            </template>
                        </Column>

                        <Column field="package_type" header="Package Type"></Column>

                        <Column field="charge" header="Charge">
                            <template #body="slotProps">
                                <div class="flex items-center justify-end">
                                    <i class="ti ti-cash mr-1 text-blue-500" style="font-size: 1rem"></i>
                                    <span>{{slotProps.data.charge.toFixed(2)}}</span>
                                </div>
                            </template>
                        </Column>

                        <template #footer> In total there are {{ charges ? Object.keys(charges).length : 0 }}
                            special DO charge records.
                        </template>
                    </DataTable>
                </template>
            </Card>
        </div>
    </AppLayout>
</template>
