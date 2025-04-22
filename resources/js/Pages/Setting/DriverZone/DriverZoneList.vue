<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import {onMounted, ref, watch} from "vue";
import {router} from "@inertiajs/vue3";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import {useConfirm} from "primevue/useconfirm";
import {FilterMatchMode} from "@primevue/core/api";
import axios from "axios";
import {debounce} from "lodash";
import {push} from "notivue";
import Tag from "primevue/tag";
import InputIcon from "primevue/inputicon";
import Card from "primevue/card";
import Button from "primevue/button";
import Column from "primevue/column";
import InputText from "primevue/inputtext";
import DataTable from "primevue/datatable";
import IconField from "primevue/iconfield";
import CreateZoneForm from "@/Pages/Setting/DriverZone/Partials/CreateZoneForm.vue";

const props = defineProps({
    roles: {
        type: Object,
        default: () => {
        },
    },
    areas: {
        type: Object,
        default: () => {
        },
    },
    branches: {
        type: Object,
        default: () => {
        },
    },
});

const baseUrl = ref("/zones/list");
const loading = ref(true);
const zones = ref([]);
const totalRecords = ref(0);
const perPage = ref(10);
const currentPage = ref(1);
const dt = ref();
const confirm = useConfirm();
const showZoneCreateDialog = ref(false);

const filters = ref({
    global: {value: null, matchMode: FilterMatchMode.CONTAINS},
});

const fetchZones = async (page = 1, search = "", sortField = 'created_at', sortOrder = 0) => {
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
        zones.value = response.data.data;
        totalRecords.value = response.data.meta.total;
        currentPage.value = response.data.meta.current_page;
    } catch (error) {
        console.error("Error fetching zones:", error);
    } finally {
        loading.value = false;
    }
};

const debouncedFetchZones = debounce((searchValue) => {
    fetchZones(1, searchValue);
}, 1000);

watch(() => filters.value.global.value, (newValue) => {
    if (newValue !== null) {
        debouncedFetchZones(newValue);
    }
});

const onPageChange = (event) => {
    perPage.value = event.rows;
    currentPage.value = event.page + 1;
    fetchZones(currentPage.value);
};

const onSort = (event) => {
    fetchZones(currentPage.value, filters.value.global.value, event.sortField, event.sortOrder);
};

onMounted(() => {
    fetchZones();
});

const clearFilter = () => {
    filters.value = {
        global: {value: null, matchMode: FilterMatchMode.CONTAINS},
    };
    fetchZones(currentPage.value);
};

const exportCSV = () => {
    dt.value.exportCSV();
};

const confirmZoneDelete = (id) => {
    confirm.require({
        message: 'Would you like to delete this driver zone record?',
        header: 'Delete Driver Zone?',
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
            router.delete(route("setting.zones.destroy", id), {
                preserveScroll: true,
                onSuccess: () => {
                    push.success("Driver Zone Deleted Successfully!");
                    fetchZones(currentPage.value);
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
    <AppLayout title="Driver Zones">
        <template #header>Driver Zones</template>

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
                        :value="zones"
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
                                    Driver Zones
                                </div>

                                <Button label="Create New Driver Zone"
                                        size="small"
                                        @click="showZoneCreateDialog = !showZoneCreateDialog"/>
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

                        <template #empty>No zones found.</template>

                        <template #loading>Loading zones data. Please wait.</template>

                        <Column field="name" header="Name" sortable></Column>

                        <Column field="areas" header="Areas">
                            <template #body="slotProps">
                                <div v-if="slotProps.data.areas">
                                    <Tag
                                        v-for="(area, index) in slotProps.data.areas"
                                        :key="index"
                                        :value="area.name"
                                        class="mr-1 mb-1"
                                        severity="info"
                                    />
                                </div>
                            </template>
                        </Column>

                        <Column field="" header="Actions" style="width: 10%">
                            <template #body="{ data }">
                                <Button
                                    icon="pi pi-trash"
                                    outlined
                                    rounded
                                    severity="danger"
                                    size="small"
                                    @click="confirmZoneDelete(data.id)"
                                />
                            </template>
                        </Column>

                        <template #footer> In total there are {{ zones ? totalRecords : 0 }} zones.</template>
                    </DataTable>
                </template>
            </Card>
        </div>
    </AppLayout>

    <CreateZoneForm :areas="areas" :visible="showZoneCreateDialog"
                    @close="showZoneCreateDialog = false"
                    @update:visible="showZoneCreateDialog = $event"/>
</template>
