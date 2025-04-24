<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import {onMounted, ref, watch} from "vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import moment from "moment";
import {usePage} from "@inertiajs/vue3";
import ImageViewModal from "@/Pages/Arrival/Partials/ImageView.vue";
import Button from "primevue/button";
import IconField from "primevue/iconfield";
import Panel from "primevue/panel";
import Card from "primevue/card";
import Column from "primevue/column";
import InputIcon from "primevue/inputicon";
import InputText from "primevue/inputtext";
import DataTable from "primevue/datatable";
import DatePicker from "primevue/datepicker";
import FloatLabel from "primevue/floatlabel";
import {FilterMatchMode} from "@primevue/core/api";
import axios from "axios";
import {debounce} from "lodash";

const baseUrl = ref("/unloading-issues-list");
const loading = ref(true);
const unloadingIssues = ref([]);
const unloadingIssueId = ref(null);
const totalRecords = ref(0);
const perPage = ref(10);
const currentPage = ref(1);
const dt = ref();
const fromDate = ref(moment(new Date()).subtract(365, "days").toISOString().split("T")[0]);
const toDate = ref(moment(new Date()).toISOString().split("T")[0]);
const isShowImageModal = ref(false);

const filters = ref({
    global: {value: null, matchMode: FilterMatchMode.CONTAINS},
});

const fetchUnloadingIssues = async (page = 1, search = "", sortField = 'created_at', sortOrder = 0) => {
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
        unloadingIssues.value = response.data.data;
        totalRecords.value = response.data.meta.total;
        currentPage.value = response.data.meta.current_page;
    } catch (error) {
        console.error("Error fetching Unloading Issues:", error);
    } finally {
        loading.value = false;
    }
};

const debouncedFetchUnloadingIssues = debounce((searchValue) => {
    fetchUnloadingIssues(1, searchValue);
}, 1000);

watch(() => filters.value.global.value, (newValue) => {
    if (newValue !== null) {
        debouncedFetchUnloadingIssues(newValue);
    }
});

watch(() => fromDate.value, (newValue) => {
    fetchUnloadingIssues(1, filters.value.global.value);
});

watch(() => toDate.value, (newValue) => {
    fetchUnloadingIssues(1, filters.value.global.value);
});

const onPageChange = (event) => {
    perPage.value = event.rows;
    currentPage.value = event.page + 1;
    fetchUnloadingIssues(currentPage.value);
};

const onSort = (event) => {
    fetchUnloadingIssues(currentPage.value, filters.value.global.value, event.sortField, event.sortOrder);
};

onMounted(() => {
    fetchUnloadingIssues();
});

const clearFilter = () => {
    filters.value = {
        global: {value: null, matchMode: FilterMatchMode.CONTAINS},
    };
    fromDate.value = moment(new Date()).subtract(30, "days").toISOString().split("T")[0];
    toDate.value = moment(new Date()).toISOString().split("T")[0];
    fetchUnloadingIssues(currentPage.value);
};

const exportCSV = () => {
    dt.value.exportCSV();
};

const handleOpenImageModal = (id) => {
    isShowImageModal.value = true;
    unloadingIssueId.value = id;
}

const closeImageModal = () => {
    isShowImageModal.value = false;
    unloadingIssueId.value = null;
}
</script>
<template>
    <AppLayout v-if="usePage().props.currentBranch.type === 'Destination' && $page.props.user.roles.includes('boned area')" title="Unloading Issues">
        <template #header>Unloading Issues</template>

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
                    <DataTable
                        ref="dt"
                        v-model:filters="filters"
                        :globalFilterFields="['hbl', 'hbl_name', 'consignee_name']"
                        :loading="loading"
                        :rows="perPage"
                        :rowsPerPageOptions="[5, 10, 20, 50, 100]"
                        :totalRecords="totalRecords"
                        :value="unloadingIssues"
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
                                    Unloading Issues
                                </div>
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

                        <template #empty>No unloading issues found.</template>

                        <template #loading>Loading unloading issues data. Please wait.</template>

                        <Column field="hbl" header="HBL" sortable></Column>

                        <Column field="branch" header="Origin" sortable></Column>

                        <Column field="hbl_name" header="Name"></Column>

                        <Column field="consignee_name" header="Consignee Name"></Column>

                        <Column field="created_at" header="Created Date" sortable></Column>

                        <Column field="weight" header="Weight">
                            <template #body="slotProps">
                                <div class="flex items-center">
                                    <i class="ti ti-scale-outline mr-1 text-blue-500" style="font-size: 1rem"></i>
                                    {{ slotProps.data.weight ? slotProps.data.weight.toFixed(2) : '-' }}
                                </div>
                            </template>
                        </Column>

                        <Column field="volume" header="Volume">
                            <template #body="slotProps">
                                <div class="flex items-center">
                                    <i class="ti ti-scale mr-1 text-blue-500" style="font-size: 1rem"></i>
                                    {{ slotProps.data.volume ? slotProps.data.volume.toFixed(3) : '-' }}
                                </div>
                            </template>
                        </Column>

                        <Column field="quantity" header="Quantity">
                            <template #body="slotProps">
                                <div class="flex items-center">
                                    <i class="ti ti-package mr-1 text-blue-500" style="font-size: 1rem"></i>
                                    {{ slotProps.data.quantity }}
                                </div>
                            </template>
                        </Column>

                        <Column field="issue" header="Issue"></Column>

                        <Column field="rtf" header="RTF">
                            <template #body="{ data }">
                                <i :class="{ 'pi-times text-red-500': !data.rtf, 'pi-check text-green-400': data.rtf }" class="pi"></i>
                            </template>
                        </Column>

                        <Column field="is_damaged" header="Damaged">
                            <template #body="{ data }">
                                <i :class="{ 'pi-times text-red-500': data.is_damaged === 'No', 'pi-check text-green-400': data.is_damaged === 'Yes' }" class="pi"></i>
                            </template>
                        </Column>

                        <Column field="type" header="Type"></Column>

                        <Column field="is_fixed" header="Fix">
                            <template #body="{ data }">
                                <i :class="{ 'pi-times text-red-500': !data.is_fixed, 'pi-check text-green-400': data.is_fixed }" class="pi"></i>
                            </template>
                        </Column>

                        <Column :exportable="false">
                            <template #body="slotProps">
                                <Button v-tooltip.left="'Show Attachments'" icon="pi
pi-paperclip" rounded severity="contrast" size="small" @click="handleOpenImageModal(slotProps.data.id)"/>
                            </template>
                        </Column>

                        <template #footer> In total there are {{ unloadingIssues ? totalRecords : 0 }} unloading issues. </template>
                    </DataTable>
                </template>
            </Card>
        </div>
    </AppLayout>

    <AppLayout v-else title="Unloading Issues">
        <template #header>Unloading Issues</template>

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
                    <DataTable
                        ref="dt"
                        v-model:filters="filters"
                        :globalFilterFields="['hbl', 'hbl_name', 'consignee_name']"
                        :loading="loading"
                        :rows="perPage"
                        :rowsPerPageOptions="[5, 10, 20, 50, 100]"
                        :totalRecords="totalRecords"
                        :value="unloadingIssues"
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
                                    Unloading Issues
                                </div>
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

                        <template #empty>No unloading issues found.</template>

                        <template #loading>Loading unloading issues data. Please wait.</template>

                        <Column field="hbl" header="HBL" sortable></Column>

                        <Column field="branch" header="Origin" sortable></Column>

                        <Column field="hbl_name" header="Name"></Column>

                        <Column field="consignee_name" header="Consignee Name"></Column>

                        <Column field="created_at" header="Created Date" sortable></Column>

                        <Column field="weight" header="Weight">
                            <template #body="slotProps">
                                <div class="flex items-center">
                                    <i class="ti ti-scale-outline mr-1 text-blue-500" style="font-size: 1rem"></i>
                                    {{ slotProps.data.weight ? slotProps.data.weight.toFixed(2) : '-' }}
                                </div>
                            </template>
                        </Column>

                        <Column field="volume" header="Volume">
                            <template #body="slotProps">
                                <div class="flex items-center">
                                    <i class="ti ti-scale mr-1 text-blue-500" style="font-size: 1rem"></i>
                                    {{ slotProps.data.volume ? slotProps.data.volume.toFixed(3) : '-' }}
                                </div>
                            </template>
                        </Column>

                        <Column field="quantity" header="Quantity">
                            <template #body="slotProps">
                                <div class="flex items-center">
                                    <i class="ti ti-package mr-1 text-blue-500" style="font-size: 1rem"></i>
                                    {{ slotProps.data.quantity }}
                                </div>
                            </template>
                        </Column>

                        <Column field="issue" header="Issue"></Column>

                        <Column field="rtf" header="RTF">
                            <template #body="{ data }">
                                <i :class="{ 'pi-times text-red-500': !data.rtf, 'pi-check text-green-400': data.rtf }" class="pi"></i>
                            </template>
                        </Column>

                        <Column field="is_damaged" header="Damaged">
                            <template #body="{ data }">
                                <i :class="{ 'pi-times text-red-500': data.is_damaged === 'No', 'pi-check text-green-400': data.is_damaged === 'Yes' }" class="pi"></i>
                            </template>
                        </Column>

                        <Column field="type" header="Type"></Column>

                        <Column field="is_fixed" header="Fix">
                            <template #body="{ data }">
                                <i :class="{ 'pi-times text-red-500': !data.is_fixed, 'pi-check text-green-400': data.is_fixed }" class="pi"></i>
                            </template>
                        </Column>

                        <Column :exportable="false">
                            <template #body="slotProps">
                                <Button v-tooltip.left="'Show Attachments'" icon="pi
pi-paperclip" rounded severity="contrast" size="small" @click="handleOpenImageModal(slotProps.data.id)"/>
                            </template>
                        </Column>

                        <template #footer> In total there are {{ unloadingIssues ? totalRecords : 0 }} unloading issues. </template>
                    </DataTable>
                </template>
            </Card>
        </div>
    </AppLayout>

    <ImageViewModal :show="isShowImageModal"
                    :unloadingIssueID="unloadingIssueId"
                    @close="closeImageModal"
                    @update:visible="isShowImageModal = $event" />
</template>
