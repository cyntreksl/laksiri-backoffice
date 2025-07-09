<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import {onMounted, ref, watch} from "vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import moment from "moment";
import DatePicker from 'primevue/datepicker';
import PrimaryButton from "@/Components/PrimaryButton.vue";
import {Link, router, usePage} from "@inertiajs/vue3";
import {push} from "notivue";
import HBLDetailModal from "@/Pages/Common/Dialog/HBL/Index.vue";
import Select from "primevue/select";
import InputIcon from "primevue/inputicon";
import Card from "primevue/card";
import DataTable from "primevue/datatable";
import Tag from "primevue/tag";
import Chip from "primevue/chip";
import Button from "primevue/button";
import IconField from "primevue/iconfield";
import Panel from "primevue/panel";
import FloatLabel from "primevue/floatlabel";
import Column from "primevue/column";
import InputText from "primevue/inputtext";
import ContextMenu from "primevue/contextmenu";
import AssignDriverDialog from "@/Pages/Pickup/Partials/AssignDriverDialog.vue";
import {useConfirm} from "primevue/useconfirm";
import {FilterMatchMode} from "@primevue/core/api";
import axios from "axios";
import {debounce} from "lodash";
import Dialog from 'primevue/dialog';
import Textarea from 'primevue/textarea';

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
    userData: {
        type: String,
        default: ''
    },
    exceptions: {
        type: Object,
        default: () => {
        },
    },
});

const baseUrl = ref("/pickup-list");
const loading = ref(true);
const pickups = ref([]);
const totalRecords = ref(0);
const perPage = ref(100);
const currentPage = ref(1);
const showConfirmViewPickupModal = ref(false);
const cm = ref();
const selectedPickup = ref(null);
const selectedPickups = ref([]);
const selectedPickupID = ref(null);
const confirm = useConfirm();
const showAssignDriverDialog = ref(false);
const dt = ref();
const showDeleteDialog = ref(false);
const deleteRemarks = ref('');
const deleteMainReason = ref(null);
const deleteTarget = ref(null);

const filters = ref({
    global: {value: null, matchMode: FilterMatchMode.CONTAINS},
    cargo_type: {value: null, matchMode: FilterMatchMode.EQUALS},
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
        label: 'Edit',
        icon: 'pi pi-fw pi-pencil',
        command: () => router.visit(route("pickups.edit", selectedPickup.value.id)),
        disabled: !usePage().props.user.permissions.includes('pickups.edit'),
    },
    {
        label: 'Retry',
        icon: 'pi pi-fw pi-refresh',
        command: () => confirmPickupRetry(selectedPickup),
        disabled: !usePage().props.user.permissions.includes('pickups.retry'),
    },
    {
        label: 'Delete',
        icon: 'pi pi-fw pi-times',
        command: () => confirmPickupDelete(selectedPickup),
        disabled: !usePage().props.user.permissions.includes('pickups.delete'),
    },
]);

const cargoTypes = ref(['Sea Cargo', 'Air Cargo']);
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
                cargoMode: filters.value.cargo_type.value || "",
                sort_field: sortField,
                sort_order: sortOrder === 1 ? "asc" : "desc",
                fromDate: moment(fromDate.value).format("YYYY-MM-DD"),
                toDate: moment(toDate.value).format("YYYY-MM-DD"),
                createdBy: filters.value.user.value || "",
                zoneBy: filters.value.zone.value || "",
                driverBy: filters.value.driver.value || [],
                userData: props.userData,
                view: 'pending',
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

watch(() => filters.value.cargo_type.value, (newValue) => {
    fetchPickups(1, filters.value.global.value);
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


const rowClass = (data) => {
    if (data.driver !== '-') {
        return '!bg-blue-100'
    }

    if (data.pickup_date < moment().format('YYYY-MM-DD')) {
        return '!bg-red-100'
    }
}

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
        cargo_type: {value: null, matchMode: FilterMatchMode.EQUALS},
        driver: {value: [], matchMode: FilterMatchMode.EQUALS},
        user: {value: null, matchMode: FilterMatchMode.EQUALS},
        zone: {value: null, matchMode: FilterMatchMode.EQUALS},
    };
    fromDate.value = moment(new Date()).subtract(7, "days").toISOString().split("T")[0];
    toDate.value = moment(new Date()).toISOString().split("T")[0];
    fetchPickups(currentPage.value);
};

const resolveCargoType = (pickup) => {
    switch (pickup.cargo_type) {
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

const exportCSV = () => {
    dt.value.exportCSV();
};

const openDeleteDialog = (pickup) => {
    deleteTarget.value = pickup.value;
    showDeleteDialog.value = true;
    deleteRemarks.value = '';
    deleteMainReason.value = null;
};

const openBulkDeleteDialog = () => {
    deleteTarget.value = selectedPickups.value.map(item => item.id);
    showDeleteDialog.value = true;
    deleteRemarks.value = '';
    deleteMainReason.value = null;
};

const handleDeleteConfirm = () => {
    if (!deleteMainReason.value) {
        push.error("Please select a main reason for deletion.");
        return;
    }
    if (Array.isArray(deleteTarget.value)) {
        // Bulk delete
        router.post(
            route("pickups.delete"),
            {
                pickupIds: deleteTarget.value,
                remarks: deleteRemarks.value,
                main_reason: deleteMainReason.value,
            },
            {
                preserveScroll: true,
                onSuccess: () => {
                    push.success("Pickups Deleted Successfully!");
                    const currentRoute = route().current();
                    router.visit(route(currentRoute, route().params));
                },
                onError: () => {
                    push.error("Something went wrong!");
                },
            }
        );
        selectedPickups.value = [];
    } else {
        // Single delete
        router.delete(route("pickups.destroy", deleteTarget.value.id), {
            data: {
                remarks: deleteRemarks.value,
                main_reason: deleteMainReason.value,
            },
            preserveScroll: true,
            onSuccess: () => {
                push.success("Pickup record Deleted Successfully!");
                const currentRoute = route().current();
                router.visit(route(currentRoute, route().params));
            },
            onError: () => {
                push.error("Something went wrong!");
            },
        });
    }
    showDeleteDialog.value = false;
    deleteTarget.value = null;
};

const confirmPickupDelete = (pickup) => {
    openDeleteDialog(pickup);
};

const confirmPickupsDelete = () => {
    openBulkDeleteDialog();
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
                    const currentRoute = route().current();
                    router.visit(route(currentRoute, route().params));
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

const handleConfirmDriverRemove = (pickupId) => {
    confirm.require({
        message: 'Are you certain you want to unassign the driver from this pickup?',
        header: 'Unassign Driver?',
        icon: 'pi pi-info-circle',
        rejectLabel: 'Cancel',
        rejectProps: {
            label: 'Cancel',
            severity: 'secondary',
            outlined: true
        },
        acceptProps: {
            label: 'Unassign',
            severity: 'warn'
        },
        accept: () => {
            router.put(route("pickups.driver.unassign", pickupId), {
                preserveScroll: true,
                onSuccess: () => {
                    push.success("Driver Unassigned!");
                    const currentRoute = route().current();
                    router.visit(route(currentRoute, route().params));
                },
                onError: () => {
                    push.error("Something went wrong!");
                },
            });
        },
        reject: () => {
            const currentRoute = route().current();
            router.visit(route(currentRoute, route().params));
        }
    });
}
</script>
<template>
    <AppLayout :title="`${userData}'s Pickups`">
        <template #header>{{userData}}'s Pickups</template>

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
                    <ContextMenu ref="cm" :model="menuModel" @hide="selectedPickup = null"/>
                    <DataTable
                        ref="dt"
                        v-model:contextMenuSelection="selectedPickup"
                        v-model:filters="filters"
                        v-model:selection="selectedPickups"
                        :globalFilterFields="['name']"
                        :loading="loading"
                        :row-class="rowClass"
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
                                    {{userData}}'s Pickups ({{ pickups ? pickups.length : 0}})
                                </div>
                                <Link v-if="$page.props.user.permissions.includes('pickups.create')" :href="route('pickups.create')">
                                    <PrimaryButton class="w-full">Create New Pending Job</PrimaryButton>
                                </Link>
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

                        <template #empty> No pickups found.</template>

                        <template #loading> Loading pickups data. Please wait.</template>

                        <Column headerStyle="width: 3rem" selectionMode="multiple"></Column>

                        <Column field="reference" header="Reference" sortable>
                            <template #body="slotProps">
                                <div>{{ slotProps.data.reference }}</div>
                                <div class="text-blue-400 text-sm">{{ slotProps.data.status }}</div>
                            </template>
                        </Column>

                        <Column field="name" header="Name">
                            <template #body="slotProps">
                                <div>
                                    {{ slotProps.data.name }}
                                </div>
                                <div class="text-gray-500 text-sm">{{ slotProps.data.address }}</div>
                                <div>
                                    {{ slotProps.data.contact_number }}
                                </div>
                            </template>
                        </Column>

                        <Column field="pickup_date" header="Pickup Date" sortable></Column>

                        <Column field="cargo_type" header="Cargo Type" sortable>
                            <template #body="slotProps">
                                <Tag :icon="resolveCargoType(slotProps.data).icon"
                                     :severity="resolveCargoType(slotProps.data).color"
                                     :value="slotProps.data.cargo_type" class="text-sm"></Tag>
                            </template>
                            <template #filter="{ filterModel, filterCallback }">
                                <Select v-model="filterModel.value" :options="cargoTypes" :showClear="true"
                                        placeholder="Select One" style="min-width: 12rem" />
                            </template>
                        </Column>

                        <Column field="driver" header="Driver">
                            <template #body="slotProps">
                                <Chip
                                    v-if="slotProps.data.driver !== '-'"
                                    :label="slotProps.data.driver"
                                    class="!bg-blue-100"
                                    icon="ti ti-steering-wheel"
                                    removable
                                    @remove="handleConfirmDriverRemove(slotProps.data.id)"
                                />
                            </template>

                            <template #filter="{ filterModel, filterCallback }">
                                <Select v-model="filterModel.value" :options="drivers" :showClear="true" option-label="name" option-value="id" placeholder="Select One" style="min-width: 12rem" />
                            </template>
                        </Column>

                        <Column field="pickup_type" header="Pickup Type"></Column>

                        <Column field="packages" header="Packages">
                            <template #body="slotProps">
                                <div class="font-medium pb-2">
                                    {{slotProps.data.notes}}
                                </div>

                                <div v-if="Array.isArray(slotProps.data.packages)" class="flex flex-wrap mb-1 gap-2">
                                    <Chip v-for="(type, index) in slotProps.data.packages" :key="index" :label="type.package_type" icon="pi pi-box"/>
                                </div>
                                <div v-else-if="typeof slotProps.data.packages === 'string'" class="flex flex-wrap mb-1 gap-2">
                                    <Chip v-for="(type, index) in slotProps.data.packages.split(',').map(type => type.trim())" :key="index" :label="type" icon="pi pi-box"/>
                                </div>
                                <div v-else>
                                    {{ slotProps.data.packages || '-' }}
                                </div>
                            </template>
                        </Column>

                        <template #footer> In total there are {{ pickups ? pickups.length : 0 }} pickups. </template>
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

    <Dialog v-model:visible="showDeleteDialog" :closable="false" :modal="true" :style="{ width: '35rem' }" header="Delete Pickup">
        <div class="mb-4">
            <label class="block mb-2 font-medium">Main Reason <span class="text-red-500">*</span></label>
            <Select v-model="deleteMainReason" :options="exceptions.results" class="w-full" option-label="name" option-value="name" placeholder="Select reason" />
        </div>
        <div class="mb-4">
            <label class="block mb-2 font-medium">Remarks (optional)</label>
            <Textarea v-model="deleteRemarks" class="w-full" placeholder="Enter remarks (optional)" />
        </div>
        <div class="flex justify-end gap-2">
            <Button label="Cancel" severity="secondary" @click="showDeleteDialog = false" />
            <Button label="Delete" severity="danger" @click="handleDeleteConfirm" />
        </div>
    </Dialog>
</template>
