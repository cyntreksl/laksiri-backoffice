<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import Card from "primevue/card";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import FloatLabel from "primevue/floatlabel";
import Button from "primevue/button";
import IconField from "primevue/iconfield";
import Panel from "primevue/panel";
import DatePicker from "primevue/datepicker";
import InputIcon from "primevue/inputicon";
import InputText from "primevue/inputtext";
import {onMounted, ref, watch} from "vue";
import moment from "moment";
import {FilterMatchMode} from "@primevue/core/api";
import axios from "axios";
import {debounce} from "lodash";
import Tag from "primevue/tag";
import {useConfirm} from "primevue/useconfirm";
import ContextMenu from 'primevue/contextmenu';
import {Link, router, usePage} from "@inertiajs/vue3";
import {push} from "notivue";

defineProps({
    agents: {
        type: Object,
        default: () => {}
    }
});

const baseUrl = ref("/couriers/third-party-agents/list");
const loading = ref(true);
const thirdPartyAgents = ref([]);
const selectedThirdPartyAgent = ref(null);
const selectedThirdPartyAgentID = ref(null);
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
        command: () => router.visit(route("couriers.agents.edit", selectedThirdPartyAgent.value.id)),
        disabled: !usePage().props.user.permissions.includes("third-party-agents.edit"),
    },
    {
        label: "Delete",
        icon: "pi pi-fw pi-times",
        command: () => confirmThirdPartyAgentDelete(selectedThirdPartyAgent),
        disabled: !usePage().props.user.permissions.includes("third-party-agents.delete"),
    },
]);

const fetchThirdPartyAgents = async (page = 1, search = "", sortField = 'created_at', sortOrder = 0) => {
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
        thirdPartyAgents.value = response.data.data;
        totalRecords.value = response.data.meta.total;
        currentPage.value = response.data.meta.current_page;
    } catch (error) {
        console.error("Error fetching third party agents:", error);
    } finally {
        loading.value = false;
    }
};

const debouncedFetchThirdPartyAgents = debounce((searchValue) => {
    fetchThirdPartyAgents(1, searchValue);
}, 1000);

watch(() => filters.value.global.value, (newValue) => {
    if (newValue !== null) {
        debouncedFetchThirdPartyAgents(newValue);
    }
});

watch(() => fromDate.value, (newValue) => {
    fetchThirdPartyAgents(1, filters.value.global.value);
});

watch(() => toDate.value, (newValue) => {
    fetchThirdPartyAgents(1, filters.value.global.value);
});

const onPageChange = (event) => {
    perPage.value = event.rows;
    currentPage.value = event.page + 1;
    fetchThirdPartyAgents(currentPage.value);
};

const onSort = (event) => {
    fetchThirdPartyAgents(currentPage.value, filters.value.global.value, event.sortField, event.sortOrder);
};

const onRowContextMenu = (event) => {
    cm.value.show(event.originalEvent);
};

onMounted(() => {
    fetchThirdPartyAgents();
});

const clearFilter = () => {
    filters.value = {
        global: {value: null, matchMode: FilterMatchMode.CONTAINS},
    };
    fromDate.value = moment(new Date()).subtract(30, "days").toISOString().split("T")[0];
    toDate.value = moment(new Date()).toISOString().split("T")[0];
    fetchThirdPartyAgents(currentPage.value);
};

const exportCSV = () => {
    dt.value.exportCSV();
};

const resolveType = (type) => {
    switch (type) {
        case 'Destination':
            return 'secondary';
        case 'Departure':
            return 'warn';
        default:
            return null;
    }
};

const confirmThirdPartyAgentDelete = (thirdPartyAgent) => {
    selectedThirdPartyAgentID.value = thirdPartyAgent.value.id;
    confirm.require({
        message: 'Would you like to delete this third party agent record?',
        header: 'Delete Third Party Agent?',
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
            router.delete(route("couriers.agents.destroy", selectedThirdPartyAgentID.value), {
                preserveScroll: true,
                onSuccess: () => {
                    push.success("Third Party Agent Deleted Successfully!");
                    fetchThirdPartyAgents(currentPage.value);
                },
                onError: () => {
                    push.error("Something went to wrong!");
                },
            });
            selectedThirdPartyAgentID.value = null;
        },
        reject: () => {
        }
    });
}
</script>

<template>
    <AppLayout title="Third Party Agents">
        <template #header>Third Party Agents</template>

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
                    <ContextMenu ref="cm" :model="menuModel" @hide="selectedThirdPartyAgent = null" />
                    <DataTable
                        ref="dt"
                        v-model:contextMenuSelection="selectedThirdPartyAgent"
                        v-model:filters="filters"
                        :loading="loading"
                        :rows="perPage"
                        :rowsPerPageOptions="[5, 10, 20, 50, 100]"
                        :totalRecords="totalRecords"
                        :value="thirdPartyAgents"
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
                                    Third Party Agents
                                </div>

                                <Link v-if="$page.props.user.permissions.includes('third-party-agents.create')" :href="route('couriers.agents.create')">
                                    <Button label="Create Third Party Agent" size="small" />
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

                        <template #empty>No third party agents found.</template>

                        <template #loading>Loading third party agents data. Please wait.</template>

                        <Column field="name" header="Name" sortable></Column>

                        <Column field="type" header="Type" sortable>
                            <template #body="slotProps">
                                <Tag :severity="resolveType(slotProps.data.type)" :value="slotProps.data.type"></Tag>
                            </template>
                        </Column>

                        <Column field="branch_code" header="Branch Code"></Column>

                        <Column field="currency" header="Currency">
                            <template #body="slotProps">
                                <div>{{ slotProps.data.currency_name }}</div>
                                <div class="text-gray-500 text-sm">{{slotProps.data.country_code}}</div>
                            </template>
                        </Column>

                        <Column field="cargo_modes" header="Cargo Modes">
                            <template #body="slotProps">
                                <div v-if="slotProps.data.cargo_modes">
                                    <Tag
                                        v-for="(mode, index) in JSON.parse(slotProps.data.cargo_modes)"
                                        :key="index"
                                        :value="mode"
                                        class="mr-1 mb-1"
                                    />
                                </div>
                            </template>
                        </Column>

                        <Column field="delivery_types" header="Delivery Types">
                            <template #body="slotProps">
                                <div v-if="slotProps.data.delivery_types">
                                    <Tag
                                        v-for="(type, index) in JSON.parse(slotProps.data.delivery_types)"
                                        :key="index"
                                        :value="type"
                                        class="mr-1 mb-1"
                                        severity="info"
                                    />
                                </div>
                            </template>
                        </Column>

                        <Column field="package_types" header="Package Types">
                            <template #body="slotProps">
                                <div v-if="slotProps.data.package_types">
                                    <Tag
                                        v-for="(type, index) in JSON.parse(slotProps.data.package_types)"
                                        :key="index"
                                        :value="type"
                                        class="mr-1 mb-1"
                                        severity="warn"
                                    />
                                </div>
                            </template>
                        </Column>

                        <template #footer> In total there are {{ thirdPartyAgents ? totalRecords : 0 }} third party agents. </template>
                    </DataTable>
                </template>
            </Card>
        </div>
    </AppLayout>
</template>
