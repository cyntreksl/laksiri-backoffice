<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import {onMounted, ref, watch} from "vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import FloatLabel from 'primevue/floatlabel';
import {useConfirm} from "primevue/useconfirm";
import Select from 'primevue/select';
import Chip from 'primevue/chip';
import InputText from 'primevue/inputtext';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import Card from "primevue/card";
import ContextMenu from 'primevue/contextmenu';
import Panel from 'primevue/panel';
import DatePicker from 'primevue/datepicker';
import Button from "primevue/button";
import Tag from 'primevue/tag';
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import axios from "axios";
import {FilterMatchMode} from '@primevue/core/api';
import {debounce} from "lodash";
import {router, Link, usePage} from "@inertiajs/vue3";
import {push} from "notivue";
import HBLDetailModal from "@/Pages/Common/HBLDetailModal.vue";
import moment from "moment";
import AssignDriverDialog from "@/Pages/Pickup/Partials/AssignDriverDialog.vue";

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

const baseUrl = ref("/pickup-exception-list");
const loading = ref(true);
const pickups = ref([]);
const totalRecords = ref(0);
const perPage = ref(100);
const currentPage = ref(1);
const showConfirmViewPickupModal = ref(false);
const cm = ref();
const selectedPickup = ref([]);
const selectedPickups = ref([]);
const selectedPickupID = ref(null);
const confirm = useConfirm();
const showAssignDriverDialog = ref(false);
const dt = ref();

const filters = ref({
    global: {value: null, matchMode: FilterMatchMode.CONTAINS},
    driver: {value: null, matchMode: FilterMatchMode.EQUALS},
    user: {value: null, matchMode: FilterMatchMode.EQUALS},
    zone: {value: null, matchMode: FilterMatchMode.EQUALS},
});

const menuModel = ref([
    {
        label: 'View',
        icon: 'pi pi-fw pi-search',
        command: () => confirmViewPickup(selectedPickup),
        disabled: !usePage().props.user.permissions.includes('pickups.show'),
    },
    {
        label: 'Retry',
        icon: 'pi pi-fw pi-refresh',
        command: () => confirmPickupRetry(selectedPickup),
        disabled: !usePage().props.user.permissions.includes('pickups.retry'),
    },
]);

const fromDate = ref(moment(new Date()).subtract(7, "days").toISOString().split("T")[0]);
const toDate = ref(moment(new Date()).toISOString().split("T")[0]);

const fetchPickups = async (page = 1, search = "", sortField = 'id', sortOrder = 1) => {
    loading.value = true;
    try {
        const response = await axios.get(baseUrl.value, {
            params: {
                page,
                per_page: perPage.value,
                search,
                sort_field: sortField,
                sort_order: sortOrder === 1 ? "asc" : "desc",
                fromDate: moment(fromDate.value).format("YYYY-MM-DD"),
                toDate: moment(toDate.value).format("YYYY-MM-DD"),
                createdBy: filters.value.user.value || "",
                zoneBy: filters.value.zone.value || "",
                driverBy: filters.value.driver.value || [],
            }
        });
        pickups.value = response.data.data;
        totalRecords.value = response.data.meta.total;
        currentPage.value = response.data.meta.current_page;
    } catch (error) {
        console.error("Error fetching Pickups:", error);
    } finally {
        loading.value = false;
    }
};

const debouncedFetchPickups = debounce((searchValue) => {
    fetchPickups(1, searchValue);
}, 1000);

watch(() => filters.value.global.value, (newValue) => {
    if (newValue !== null) {
        debouncedFetchPickups(newValue);
    }
});

watch(() => filters.value.driver.value, (newValue) => {
    fetchPickups(1, filters.value.global.value);
});

watch(() => filters.value.user.value, (newValue) => {
    fetchPickups(1, filters.value.global.value);
});

watch(() => filters.value.zone.value, (newValue) => {
    fetchPickups(1, filters.value.global.value);
});

watch(() => fromDate.value, (newValue) => {
    fetchPickups(1, filters.value.global.value);
});

watch(() => toDate.value, (newValue) => {
    fetchPickups(1, filters.value.global.value);
});

const onPageChange = (event) => {
    perPage.value = event.rows;
    currentPage.value = event.page + 1;
    fetchPickups(currentPage.value);
};

const onSort = (event) => {
    fetchPickups(currentPage.value, filters.value.global.value, event.sortField, event.sortOrder);
};

onMounted(() => {
    fetchPickups();
});

const onRowContextMenu = (event) => {
    cm.value.show(event.originalEvent);
};

const confirmViewPickup = (hbl) => {
    selectedPickupID.value = hbl.value.id;
    showConfirmViewPickupModal.value = true;
};

const closeModal = () => {
    showConfirmViewPickupModal.value = false;
    selectedPickupID.value = null;
};

const clearFilter = () => {
    filters.value = {
        global: {value: null, matchMode: FilterMatchMode.CONTAINS},
        driver: {value: [], matchMode: FilterMatchMode.EQUALS},
        user: {value: null, matchMode: FilterMatchMode.EQUALS},
        zone: {value: null, matchMode: FilterMatchMode.EQUALS},
    };
    fromDate.value = moment(new Date()).subtract(7, "days").toISOString().split("T")[0];
    toDate.value = moment(new Date()).toISOString().split("T")[0];
    fetchPickups(currentPage.value);
};

const exportCSV = () => {
    dt.value.exportCSV();
};

const confirmPickupsDelete = () => {
    const idList = selectedPickups.value.map((item) => item.id);

    confirm.require({
        message: 'Would you like to delete this pickup records?',
        header: `${idList.length} Delete Pickups?`,
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
            router.post(
                route("pickups.exceptions.delete"),
                {
                    exceptionIds: idList,
                },
                {
                    preserveScroll: true,
                    onSuccess: () => {
                        push.success("Pickup Exception Deleted Successfully!");
                        const currentRoute = route().current();
                        router.visit(route(currentRoute));
                    },
                    onError: () => {
                        push.error("Something went to wrong!");
                    },
                }
            );
            selectedPickups.value = [];
        },
        reject: () => {
            selectedPickups.value = [];
        }
    });
};

const closeAssignDriverModal = () => {
    showAssignDriverDialog.value = false;
    selectedPickups.value = [];
};

const confirmPickupRetry = (pickup) => {
    selectedPickupID.value = pickup.value.id;
    confirm.require({
        message: 'Are you sure you want to Retry Job?',
        header: 'Retry Pickup?',
        icon: 'pi pi-info-circle',
        rejectLabel: 'Cancel',
        rejectProps: {
            label: 'Cancel',
            severity: 'secondary',
            outlined: true
        },
        acceptProps: {
            label: 'Retry',
            severity: 'warn'
        },
        accept: () => {
            router.get(route("pickups.exceptions.retry", selectedPickupID.value), {
                preserveScroll: true,
                onSuccess: () => {
                    push.success("Added into Pending Jobs!");
                    router.visit(route("pickups.exceptions"), {only: ["pickups"]});
                },
                onError: () => {
                    push.error("Something went to wrong!");
                },
            });
            selectedPickupID.value = null;
        },
        reject: () => {
            selectedPickupID.value = null;
        }
    });
};
</script>
<template>
    <AppLayout title="Pickup Exceptions">
        <template #header>Pickup Exceptions</template>

        <Breadcrumb/>

        <div>
            <Panel :collapsed="true" class="mt-5" header="Advance Filters" toggleable>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                    <FloatLabel class="w-full" variant="in">
                        <DatePicker v-model="fromDate" class="w-full" date-format="yy-mm-dd" input-id="from-date"/>
                        <label for="from-date">From Date</label>
                    </FloatLabel>

                    <FloatLabel class="w-full" variant="in">
                        <DatePicker v-model="toDate" class="w-full" date-format="yy-mm-dd" input-id="to-date"/>
                        <label for="to-date">To Date</label>
                    </FloatLabel>

                    <FloatLabel class="w-full" variant="in">
                        <Select v-model="filters.user.value" :options="users" :showClear="true" class="w-full" input-id="user" option-label="name" option-value="id"/>
                        <label for="user">Select User</label>
                    </FloatLabel>

                    <FloatLabel class="w-full" variant="in">
                        <Select v-model="filters.zone.value" :options="zones" :showClear="true" class="w-full" input-id="zone" option-label="name" option-value="id" />
                        <label for="zone">Select Zone</label>
                    </FloatLabel>
                </div>
            </Panel>

            <Card class="my-5">
                <template #content>
                    <ContextMenu ref="cm" :model="menuModel" @hide="selectedPickup.length < 1"/>
                    <DataTable
                        ref="dt"
                        v-model:contextMenuSelection="selectedPickup"
                        v-model:filters="filters"
                        v-model:selection="selectedPickups"
                        :globalFilterFields="['name']"
                        :loading="loading"
                        :rows="perPage"
                        :rowsPerPageOptions="[5, 10, 20, 50, 100]"
                        :totalRecords="totalRecords"
                        :value="pickups"
                        context-menu
                        data-key="id"
                        filter-display="menu"
                        lazy
                        paginator
                        removable-sort
                        row-hover
                        tableStyle="min-width: 50rem"
                        @page="onPageChange"
                        @rowContextmenu="onRowContextMenu"
                        @sort="onSort">

                        <template #header>
                            <div class="flex flex-col sm:flex-row justify-between items-center mb-2">
                                <div class="text-lg font-medium">
                                    Pickup Exceptions
                                </div>
                                <Button v-if="$page.props.user.permissions.includes('pickups.create')" icon="pi pi-arrow-right"
                                        icon-pos="right"
                                        label="Create New Pending Job" size="small" @click="router.visit(route('pickups.create'))"/>
                            </div>
                            <div class="flex flex-col sm:flex-row justify-between gap-4">
                                <!-- Button Group -->
                                <div class="flex flex-col sm:flex-row gap-2">
                                    <Button
                                        v-if="$page.props.user.permissions.includes('pickups.assign driver')"
                                        :disabled="selectedPickups.length === 0"
                                        icon="ti ti-steering-wheel"
                                        label="Assign Driver"
                                        severity="primary"
                                        size="small"
                                        type="button"
                                        @click="showAssignDriverDialog = !showAssignDriverDialog"
                                    />

                                    <Button
                                        v-if="$page.props.user.permissions.includes('pickups.delete')"
                                        :disabled="selectedPickups.length === 0"
                                        icon="pi pi-trash"
                                        label="Delete"
                                        severity="danger"
                                        size="small"
                                        type="button"
                                        @click="confirmPickupsDelete()"
                                    />

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
                                        <i class="pi pi-search" />
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

                        <template #empty> No pickup exceptions found.</template>

                        <template #loading> Loading pickup exceptions data. Please wait.</template>

                        <Column headerStyle="width: 3rem" selectionMode="multiple"></Column>

                        <Column field="reference" header="Reference" sortable>
                            <template #body="slotProps">
                                <div>{{ slotProps.data.reference }}</div>
                                <div class="text-blue-400 text-sm">{{ slotProps.data.status }}</div>
                            </template>
                        </Column>

                        <Column field="name" header="Name">
                            <template #body="slotProps">
                                <Link :href="`pickups/get-pending-jobs-by-user/${slotProps.data.name}`"
                                      class="text-blue-600 underline">
                                    {{ slotProps.data.name }}
                                </Link>
                                <div class="text-gray-500 text-sm">{{ slotProps.data.address }}</div>
                                <Link :href="`pickups/get-pending-jobs-by-user/${slotProps.data.contact_number}`"
                                      class="text-blue-600 underline text-sm">
                                    {{ slotProps.data.contact_number }}
                                </Link>
                            </template>
                        </Column>

                        <Column field="pickup_date" header="Pickup Date" sortable></Column>

                        <Column field="zone" header="Zone" sortable></Column>

                        <Column field="picker_note" header="Picker Note" sortable>
                            <template #body="slotProps">
                                <Tag v-if="slotProps.data.picker_note !== '-'" :value="slotProps.data.picker_note" class="text-sm" severity="danger"></Tag>
                            </template>
                        </Column>

                        <Column field="driver" header="Driver">
                            <template #body="slotProps">
                                <Chip v-if="slotProps.data.driver !== '-'" :label="slotProps.data.driver" class="!bg-blue-100" icon="ti ti-steering-wheel"/>
                            </template>

                            <template #filter="{ filterModel, filterCallback }">
                                <Select v-model="filterModel.value" :options="drivers" :showClear="true" option-label="name" option-value="id"
                                        placeholder="Select One" style="min-width: 12rem" />
                            </template>
                        </Column>

                        <Column field="created_date" header="Created" sortable></Column>

                        <Column field="auth" header="Auth"></Column>

                        <template #footer> In total there are {{ pickups ? pickups.length : 0 }} pickup exceptions. </template>
                    </DataTable>
                </template>
            </Card>
        </div>
    </AppLayout>

    <HBLDetailModal
        :pickup-id="selectedPickupID"
        :show="showConfirmViewPickupModal"
        @close="closeModal"
        @update:show="showConfirmViewPickupModal = $event"
    />

    <AssignDriverDialog
        :drivers="drivers"
        :selected-pickups="selectedPickups"
        :visible="showAssignDriverDialog"
        @close="closeAssignDriverModal"
        @update:visible="showAssignDriverDialog = $event"
    />
</template>
