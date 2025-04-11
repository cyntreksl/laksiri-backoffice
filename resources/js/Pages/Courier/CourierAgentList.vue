<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import {onMounted, ref, watch} from "vue";
import {useConfirm} from "primevue/useconfirm";
import moment from "moment";
import {FilterMatchMode} from "@primevue/core/api";
import {Link, router, usePage} from "@inertiajs/vue3";
import axios from "axios";
import {debounce} from "lodash";
import {push} from "notivue";
import InputIcon from "primevue/inputicon";
import Card from "primevue/card";
import InputText from "primevue/inputtext";
import FloatLabel from "primevue/floatlabel";
import DataTable from "primevue/datatable";
import Avatar from 'primevue/avatar';
import DatePicker from "primevue/datepicker";
import ContextMenu from "primevue/contextmenu";
import Panel from "primevue/panel";
import Button from "primevue/button";
import Column from "primevue/column";
import IconField from "primevue/iconfield";

const props = defineProps({
    courierAgents: {
        type: Object,
        default: () => {}
    }
});

const baseUrl = ref("/couriers/courier-agents/list");
const loading = ref(true);
const courierAgents = ref([]);
const selectedCourierAgent = ref(null);
const selectedCourierAgentID = ref(null);
const totalRecords = ref(0);
const perPage = ref(10);
const currentPage = ref(1);
const dt = ref();
const cm = ref();
const confirm = useConfirm();
const fromDate = ref(moment(new Date()).subtract(12, "months").toISOString().split("T")[0]);
const toDate = ref(moment(new Date()).toISOString().split("T")[0]);

const filters = ref({
    global: {value: null, matchMode: FilterMatchMode.CONTAINS},
});

const menuModel = ref([
    {
        label: "Edit",
        icon: "pi pi-fw pi-pencil",
        command: () => router.visit(route("couriers.courier-agents.edit", selectedCourierAgent.value.id)),
        disabled: !usePage().props.user.permissions.includes("courier-agents.edit"),
    },
    {
        label: "Delete",
        icon: "pi pi-fw pi-times",
        command: () => confirmCourierAgentDelete(selectedCourierAgent),
        disabled: !usePage().props.user.permissions.includes("courier-agents.delete"),
    },
]);

const fetchCourierAgents = async (page = 1, search = "", sortField = 'created_at', sortOrder = 0) => {
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
            }
        });
        courierAgents.value = response.data.data;
        totalRecords.value = response.data.meta.total;
        currentPage.value = response.data.meta.current_page;
    } catch (error) {
        console.error("Error fetching courier agents:", error);
    } finally {
        loading.value = false;
    }
};

const debouncedFetchCourierAgentAgents = debounce((searchValue) => {
    fetchCourierAgents(1, searchValue);
}, 1000);

watch(() => filters.value.global.value, (newValue) => {
    if (newValue !== null) {
        debouncedFetchCourierAgentAgents(newValue);
    }
});

watch(() => fromDate.value, (newValue) => {
    fetchCourierAgents(1, filters.value.global.value);
});

watch(() => toDate.value, (newValue) => {
    fetchCourierAgents(1, filters.value.global.value);
});

const onPageChange = (event) => {
    perPage.value = event.rows;
    currentPage.value = event.page + 1;
    fetchCourierAgents(currentPage.value);
};

const onSort = (event) => {
    fetchCourierAgents(currentPage.value, filters.value.global.value, event.sortField, event.sortOrder);
};

const onRowContextMenu = (event) => {
    cm.value.show(event.originalEvent);
};

onMounted(() => {
    fetchCourierAgents();
});

const clearFilter = () => {
    filters.value = {
        global: {value: null, matchMode: FilterMatchMode.CONTAINS},
    };
    fromDate.value = moment(new Date()).subtract(12, "months").toISOString().split("T")[0];
    toDate.value = moment(new Date()).toISOString().split("T")[0];
    fetchCourierAgents(currentPage.value);
};

const exportCSV = () => {
    dt.value.exportCSV();
};

const confirmCourierAgentDelete = (courierAgent) => {
    selectedCourierAgentID.value = courierAgent.value.id;
    confirm.require({
        message: 'Would you like to delete this courier agent record?',
        header: 'Delete Courier Agent?',
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
            router.delete(route("couriers.courier-agents.destroy", selectedCourierAgentID.value), {
                preserveScroll: true,
                onSuccess: () => {
                    push.success("Courier Agent Deleted Successfully!");
                    fetchCourierAgents(currentPage.value);
                },
                onError: () => {
                    push.error("Something went to wrong!");
                },
            });
            selectedCourierAgentID.value = null;
        },
        reject: () => {
        }
    });
}
</script>

<template>
    <AppLayout title="Courier Agents">
        <template #header>Courier Agents</template>

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
                </div>
            </Panel>

            <Card class="my-5">
                <template #content>
                    <ContextMenu ref="cm" :model="menuModel" @hide="selectedCourierAgent = null" />
                    <DataTable
                        ref="dt"
                        v-model:contextMenuSelection="selectedCourierAgent"
                        v-model:filters="filters"
                        :loading="loading"
                        :rows="perPage"
                        :rowsPerPageOptions="[5, 10, 20, 50, 100]"
                        :totalRecords="totalRecords"
                        :value="courierAgents"
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
                                    Courier Agents
                                </div>

                                <Link v-if="$page.props.user.permissions.includes('third-party-agents.create')" :href="route('couriers.courier-agents.create')">
                                    <Button label="Create Courier Agent" size="small" />
                                </Link>
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

                        <template #empty>No courier agents found.</template>

                        <template #loading>Loading courier agents data. Please wait.</template>

                        <Column field="logo" header="Logo">
                            <template #body="{ data }">
                                <div class="flex">
                                    <img v-if="data.logo" :alt="data.logo" :src="data.logo_url" style="width: 32px" />
                                    <Avatar v-else icon="ti ti-truck-delivery" shape="circle" />
                                </div>
                            </template>
                        </Column>

                        <Column field="company_name" header="Company Name" sortable></Column>

                        <Column field="website" header="Website"></Column>

                        <Column field="contact" header="Contact">
                            <template #body="slotProps">
                                <div>{{ slotProps.data.email }}</div>
                                <div class="text-gray-500 text-sm">{{slotProps.data.contact_number_1}}</div>
                                <div class="text-gray-500 text-sm">{{slotProps.data.contact_number_2}}</div>
                            </template>
                        </Column>

                        <Column field="address" header="Address"></Column>

                        <template #footer> In total there are {{ courierAgents ? totalRecords : 0 }} courier agents. </template>
                    </DataTable>
                </template>
            </Card>
        </div>
    </AppLayout>
</template>
