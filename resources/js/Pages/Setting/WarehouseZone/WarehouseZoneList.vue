<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import {onMounted, ref, watch} from "vue";
import {router} from "@inertiajs/vue3";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import {push} from "notivue";
import {useConfirm} from "primevue/useconfirm";
import {FilterMatchMode} from "@primevue/core/api";
import axios from "axios";
import {debounce} from "lodash";
import InputIcon from "primevue/inputicon";
import Card from "primevue/card";
import Button from "primevue/button";
import Column from "primevue/column";
import InputText from "primevue/inputtext";
import DataTable from "primevue/datatable";
import IconField from "primevue/iconfield";
import CreateWarehouseZoneForm from "@/Pages/Setting/WarehouseZone/Partials/CreateWarehouseZoneForm.vue";
import CreateDriverAreasForm from "@/Pages/Setting/DriverAreas/CreateDriverAreasForm.vue";
import EditWarehouseZoneForm from "@/Pages/Setting/WarehouseZone/Partials/EditWarehouseZoneForm.vue";

defineProps({
    branches: {
        type: Object,
        default: () => {
        },
    },
});

const baseUrl = ref("/warehouse-zones/list");
const loading = ref(true);
const warehouseZones = ref([]);
const selectedWarehouseZone = ref({});
const totalRecords = ref(0);
const perPage = ref(10);
const currentPage = ref(1);
const dt = ref();
const confirm = useConfirm();
const showWarehouseZoneCreateDialog = ref(false);
const showWarehouseZoneEditDialog = ref(false);

const filters = ref({
    global: {value: null, matchMode: FilterMatchMode.CONTAINS},
});

const fetchWarehouseZones = async (page = 1, search = "", sortField = 'created_at', sortOrder = 0) => {
    loading.value = true;
    try {
        const response = await axios.get(baseUrl.value, {
            params: {
                page,
                per_page: perPage.value,
                search,
                sort_field: sortField,
                sort_order: sortOrder === 1 ? "asc" : "desc",
            }
        });
        warehouseZones.value = response.data.data;
        totalRecords.value = response.data.meta.total;
        currentPage.value = response.data.meta.current_page;
    } catch (error) {
        console.error("Error fetching warehouse zones:", error);
    } finally {
        loading.value = false;
    }
};

const debouncedFetchWarehouseZones = debounce((searchValue) => {
    fetchWarehouseZones(1, searchValue);
}, 1000);

watch(() => filters.value.global.value, (newValue) => {
    if (newValue !== null) {
        debouncedFetchWarehouseZones(newValue);
    }
});

const onPageChange = (event) => {
    perPage.value = event.rows;
    currentPage.value = event.page + 1;
    fetchWarehouseZones(currentPage.value);
};

const onSort = (event) => {
    fetchWarehouseZones(currentPage.value, filters.value.global.value, event.sortField, event.sortOrder);
};

onMounted(() => {
    fetchWarehouseZones();
});

const clearFilter = () => {
    filters.value = {
        global: {value: null, matchMode: FilterMatchMode.CONTAINS},
    };
    fetchWarehouseZones(currentPage.value);
};

const exportCSV = () => {
    dt.value.exportCSV();
};

const handleEditDialog = (data) => {
    selectedWarehouseZone.value = data;
    showWarehouseZoneEditDialog.value = true;
}

const confirmWarehouseZoneDelete = (id) => {
    confirm.require({
        message: 'Would you like to delete this warehouse zone record?',
        header: 'Delete Warehouse Zone?',
        icon: 'pi pi-info-circle',
        rejectLabel: 'Cancel',
        rejectProps: {
            label: 'Cancel',
            severity: 'secondary',
            outlined: true
        },
        acceptProps: {
            label: 'Delete',
            severity: 'danger'
        },
        accept: () => {
            router.delete(route("setting.warehouse-zones.delete", id), {
                preserveScroll: true,
                onSuccess: () => {
                    push.success("Warehouse Zone Deleted Successfully!");
                    fetchWarehouseZones(currentPage.value);
                },
                onError: () => {
                    push.error("Something went to wrong!");
                },
            });
        },
        reject: () => {
        }
    });
}
</script>

<template>
    <AppLayout title="Warehouse Zones">
        <template #header>Warehouse Zones</template>

        <Breadcrumb/>

        <div>
            <Card class="my-5">
                <template #content>
                    <DataTable
                        ref="dt"
                        v-model:filters="filters"
                        :loading="loading"
                        :rows="perPage"
                        :rowsPerPageOptions="[5, 10, 20, 50, 100]"
                        :totalRecords="totalRecords"
                        :value="warehouseZones"
                        data-key="id"
                        filter-display="menu"
                        lazy
                        paginator
                        removable-sort
                        row-hover
                        tableStyle="min-width: 50rem"
                        @page="onPageChange"
                        @sort="onSort">

                        <template #header>
                            <div class="flex flex-col sm:flex-row justify-between items-center mb-2">
                                <div class="text-lg font-medium">
                                    Warehouse Zones
                                </div>

                                <Button label="Create New Warehouse Zone"
                                        size="small"
                                        @click="showWarehouseZoneCreateDialog = !showWarehouseZoneCreateDialog"/>
                            </div>
                            <div class="flex flex-col sm:flex-row justify-between gap-4">
                                <!-- Button Group -->
                                <div class="flex flex-col sm:flex-row gap-2">
                                    <Button
                                        icon="pi pi-filter-slash"
                                        label="Clear Filters"
                                        outlined
                                        severity="contrast"
                                        size="small"
                                        type="button"
                                        @click="clearFilter()"
                                    />

                                    <Button
                                        icon="pi pi-external-link"
                                        label="Export"
                                        severity="contrast"
                                        size="small"
                                        @click="exportCSV($event)"
                                    />
                                </div>

                                <!-- Search Field -->
                                <IconField class="w-full sm:w-auto">
                                    <InputIcon>
                                        <i class="pi pi-search"/>
                                    </InputIcon>
                                    <InputText
                                        v-model="filters.global.value"
                                        class="w-full"
                                        placeholder="Keyword Search"
                                        size="small"
                                    />
                                </IconField>
                            </div>
                        </template>

                        <template #empty>No warehouse zones found.</template>

                        <template #loading>Loading warehouse zones data. Please wait.</template>

                        <Column field="name" header="Name"></Column>

                        <Column field="description" header="Description"></Column>

                        <Column field="branch_name" header="Branch"></Column>

                        <Column field="created_at" header="Created At"></Column>

                        <Column field="" header="Actions" style="width: 10%">
                            <template #body="{ data }">
                                <div class="flex gap-2">
                                    <Button
                                        icon="pi pi-pencil"
                                        outlined
                                        rounded
                                        size="small"
                                        @click.prevent="handleEditDialog(data)"
                                    />
                                    <Button
                                        icon="pi pi-trash"
                                        outlined
                                        rounded
                                        severity="danger"
                                        size="small"
                                        @click="confirmWarehouseZoneDelete(data.id)"
                                    />
                                </div>
                            </template>
                        </Column>

                        <template #footer> In total there are {{ warehouseZones ? totalRecords : 0 }} warehouse zones.
                        </template>
                    </DataTable>
                </template>
            </Card>
        </div>
    </AppLayout>

    <CreateWarehouseZoneForm :visible="showWarehouseZoneCreateDialog"
                             @close="showWarehouseZoneCreateDialog = false"
                             @update:visible="showWarehouseZoneCreateDialog = $event"/>

    <EditWarehouseZoneForm :visible="showWarehouseZoneEditDialog" :warehouse-zone="selectedWarehouseZone"
                           @close="showWarehouseZoneEditDialog = false"
                           @update:visible="showWarehouseZoneEditDialog = $event"/>
</template>
