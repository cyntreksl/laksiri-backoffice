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
import HBLDetailModal from "@/Pages/Common/Dialog/HBL/Index.vue";
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

const baseUrl = ref("/pickup-list");
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
    cargo_type: {value: null, matchMode: FilterMatchMode.EQUALS},
    driver: {value: null, matchMode: FilterMatchMode.EQUALS},
    pickup_date: {value: null, matchMode: FilterMatchMode.EQUALS},
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
                pickupDate: filters.value.pickup_date.value || "",
                zoneBy: filters.value.zone.value || "",
                driverBy: filters.value.driver.value || [],
            }
        });
        pickups.value = response.data.data;
        totalRecords.value = response.data.meta.total; // Correct total count
        currentPage.value = response.data.meta.current_page; // Correct current page
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

watch(() => filters.value.pickup_date.value, (newValue) => {
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
        pickup_date: {value: null, matchMode: FilterMatchMode.EQUALS},
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

const getFormattedDate = (daysFromToday) => {
    const date = new Date();
    date.setDate(date.getDate() + daysFromToday);
    return date.toISOString().split("T")[0]; // Formats as YYYY-MM-DD
};

const pickupDateOptions = ref([
    { label: "Tomorrow", value: getFormattedDate(1) },
    { label: "Day after Tomorrow", value: getFormattedDate(2) },
    { label: "One Week Later", value: getFormattedDate(7) }
]);

const confirmPickupDelete = (pickup) => {
    selectedPickupID.value = pickup.value.id;
    confirm.require({
        message: 'Would you like to delete this pickup record?',
        header: 'Delete Pickup?',
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
            router.delete(route("pickups.destroy", selectedPickupID.value), {
                preserveScroll: true,
                onSuccess: () => {
                    push.success("Pickup record Deleted Successfully!");
                    router.visit(route("pickups.index"), {only: ["pickups"]});
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
                route("pickups.delete"),
                {
                    pickupIds: idList,
                },
                {
                    preserveScroll: true,
                    onSuccess: () => {
                        push.success("Pickups Deleted Successfully!");
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
                    router.visit(route("pickups.index"), {only: ["pickups"]});
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

const parsePackageTypes = (str) => {
    if (!str) return [];

    if (Array.isArray(str)) return str;

    try {
        const parsed = JSON.parse(str);
        if (Array.isArray(parsed)) {
            return parsed.map(type => type.trim().replace(/^["']|["']$/g, ''));
        }
    } catch (e) {
        // Fallback: split by comma
    }

    return str.split(',').map(type => type.trim().replace(/^["']|["']$/g, ''));
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
                    router.visit(route("pickups.index"), {only: ["pickups"]});
                },
                onError: () => {
                    push.error("Something went wrong!");
                },
            });
        },
        reject: () => {
            router.visit(route("pickups.index"), {only: ["pickups"]});
        }
    });
}
</script>
<template>
    <AppLayout title="Pending Pickups">
        <template #header>Pending Pickups</template>

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
                        <Select v-model="filters.pickup_date.value" :options="pickupDateOptions" :showClear="true" class="w-full" input-id="pickup-date" option-label="label" option-value="value" />
                        <label for="pickup-date">Select Pickup Date</label>
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
                                    Pending Jobs
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

                        <template #empty> No pickups found.</template>

                        <template #loading> Loading pickups data. Please wait.</template>

                        <Column headerStyle="width: 3rem" selectionMode="multiple"></Column>

                        <Column field="reference" header="Reference" headerStyle="width: 12rem" sortable>
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
                            </template>
                        </Column>

                        <Column field="address" header="Address" />

                        <Column field="contact_number" header="Contact">
                            <template #body="slotProps">
                                <Link :href="`pickups/get-pending-jobs-by-user/${slotProps.data.contact_number}`"
                                      class="text-blue-600 underline text-sm">
                                    {{ slotProps.data.contact_number }}
                                </Link>
                            </template>
                        </Column>

                        <Column field="pickup_date" header="Pickup Date" sortable></Column>

                        <Column field="cargo_type" header="Cargo Type" sortable>
                            <template #body="slotProps">
                                <Tag :icon="resolveCargoType(slotProps.data).icon"
                                     :severity="resolveCargoType(slotProps.data).color"
                                     :value="slotProps.data.cargo_type" class="text-xs"></Tag>
                            </template>
                            <template #filter="{ filterModel, filterCallback }">
                                <Select v-model="filterModel.value" :options="cargoTypes" :showClear="true"
                                        placeholder="Select One" style="min-width: 12rem" >
                                    <template #option="slotProps">
                                        <Tag :value="slotProps.option"/>
                                    </template>
                                </Select>
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
                                <Select v-model="filterModel.value" :options="drivers" :showClear="true" option-label="name" option-value="id"
                                        placeholder="Select One" style="min-width: 12rem" />
                            </template>
                        </Column>

                        <Column field="pickup_type" header="Pickup Type" hidden></Column>

                         <Column field="packages" header="Packages">
                            <template #body="slotProps">
                                <div class="flex flex-wrap mb-1 gap-2">
                                    <template v-if="slotProps.data.package_types">
                                        <Chip v-for="(type, index) in parsePackageTypes(slotProps.data.package_types)" :key="index" :label="type" icon="pi pi-box"
                                        />
                                    </template>
                                    <template v-else>
                                        {{ '-' }}
                                    </template>
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
</template>
