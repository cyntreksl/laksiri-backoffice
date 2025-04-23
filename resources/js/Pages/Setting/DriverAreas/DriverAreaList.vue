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
import Tag from "primevue/tag";
import InputIcon from "primevue/inputicon";
import Card from "primevue/card";
import Button from "primevue/button";
import Column from "primevue/column";
import InputText from "primevue/inputtext";
import DataTable from "primevue/datatable";
import IconField from "primevue/iconfield";
import CreateDriverAreasForm from "@/Pages/Setting/DriverAreas/CreateDriverAreasForm.vue";
import EditDriverAreasForm from "@/Pages/Setting/DriverAreas/EditDriverAreasForm.vue";

const props = defineProps({
    zones: {
        type: Object,
        default: () => {
        },
    },
});

const baseUrl = ref("/driver-areas/list");
const loading = ref(true);
const areas = ref([]);
const selectedArea = ref({});
const totalRecords = ref(0);
const perPage = ref(10);
const currentPage = ref(1);
const dt = ref();
const confirm = useConfirm();
const showAreaCreateDialog = ref(false);
const showAreaEditDialog = ref(false);

const filters = ref({
    global: {value: null, matchMode: FilterMatchMode.CONTAINS},
});

const fetchAreas = async (page = 1, search = "", sortField = 'created_at', sortOrder = 0) => {
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
        areas.value = response.data.data;
        totalRecords.value = response.data.meta.total;
        currentPage.value = response.data.meta.current_page;
    } catch (error) {
        console.error("Error fetching areas:", error);
    } finally {
        loading.value = false;
    }
};

const debouncedFetchAreas = debounce((searchValue) => {
    fetchAreas(1, searchValue);
}, 1000);

watch(() => filters.value.global.value, (newValue) => {
    if (newValue !== null) {
        debouncedFetchAreas(newValue);
    }
});

const onPageChange = (event) => {
    perPage.value = event.rows;
    currentPage.value = event.page + 1;
    fetchAreas(currentPage.value);
};

const onSort = (event) => {
    fetchAreas(currentPage.value, filters.value.global.value, event.sortField, event.sortOrder);
};

onMounted(() => {
    fetchAreas();
});

const clearFilter = () => {
    filters.value = {
        global: {value: null, matchMode: FilterMatchMode.CONTAINS},
    };
    fetchAreas(currentPage.value);
};

const exportCSV = () => {
    dt.value.exportCSV();
};

const handleEditDialog = (data) => {
    selectedArea.value = data;
    showAreaEditDialog.value = true;
}

const confirmZoneDelete = (id) => {
    confirm.require({
        message: 'Would you like to delete this driver area record?',
        header: 'Delete Driver Area?',
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
            router.delete(route("setting.driver-areas.destroy", id), {
                preserveScroll: true,
                onSuccess: () => {
                    push.success("Driver Area Deleted Successfully!");
                    fetchAreas(currentPage.value);
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
    <AppLayout title="Driver Areas">
        <template #header>Driver Areas</template>

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
                        :value="areas"
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
                                    Driver Areas
                                </div>

                                <Button label="Create New Driver Area"
                                        size="small"
                                        @click="showAreaCreateDialog = !showAreaCreateDialog"/>
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

                        <template #empty>No areas found.</template>

                        <template #loading>Loading areas data. Please wait.</template>

                        <Column field="name" header="Name"></Column>

                        <Column field="zones" header="Areas">
                            <template #body="slotProps">
                                <div v-if="slotProps.data.zones">
                                    <Tag
                                        v-for="(zone, index) in slotProps.data.zones"
                                        :key="index"
                                        :value="zone.name"
                                        class="mr-1 mb-1"
                                        severity="info"
                                    />
                                </div>
                            </template>
                        </Column>

                        <Column field="branch_name" header="Branch"></Column>

                        <Column field="created_at" header="Created At" sortable></Column>

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
                                        @click="confirmZoneDelete(data.id)"
                                    />
                                </div>
                            </template>
                        </Column>

                        <template #footer> In total there are {{ areas ? totalRecords : 0 }} areas.</template>
                    </DataTable>
                </template>
            </Card>
        </div>
    </AppLayout>

    <CreateDriverAreasForm :visible="showAreaCreateDialog" :zones="zones"
                           @close="showAreaCreateDialog = false"
                           @update:visible="showAreaCreateDialog = $event"/>

    <EditDriverAreasForm :driver-area="selectedArea" :visible="showAreaEditDialog" :zones="zones"
                         @close="showAreaEditDialog = false"
                         @update:visible="showAreaEditDialog = $event"/>
</template>
