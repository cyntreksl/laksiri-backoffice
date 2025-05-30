<script setup>
import {computed, ref} from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import Card from 'primevue/card';
import Button from 'primevue/button';
import DatePicker from "primevue/datepicker";
import {router} from "@inertiajs/vue3";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import moment from "moment";
import Panel from "primevue/panel";
import FloatLabel from "primevue/floatlabel";

const props = defineProps({
    vesselSchedules: {
        type: Object,
        default: () => ({}),
    },
});

const perPage = ref(10);
const currentPage = ref(1);

const fromDate = ref(moment(new Date()).subtract(12, "months").toISOString().split("T")[0]);
const toDate = ref(moment(new Date()).add(2, "weeks").toISOString().split("T")[0]);

// Filtered schedules based on fromDate and toDate
const filteredSchedules = computed(() => {
    return Object.values(props.vesselSchedules).filter(item => {
        return moment(item.start_date).isSameOrAfter(fromDate.value) && moment(item.end_date).isSameOrBefore(toDate.value);
    });
});

const onPageChange = (event) => {
    perPage.value = event.rows;
    currentPage.value = event.page + 1;
};

const latestRecordId = computed(() => {
    if (!filteredSchedules.value.length) return null;

    return filteredSchedules.value.reduce((latest, item) => {
        return new Date(item.created_at) > new Date(latest.created_at) ? item : latest;
    }).id;
});

const rowClass = (data) => {
    if (data.id === latestRecordId.value) {
        return '!bg-green-100';
    }
}

const clearFilter = () => {
    fromDate.value = moment(new Date()).subtract(7, "days").toISOString().split("T")[0];
    toDate.value = moment(new Date()).toISOString().split("T")[0];
};

const exportCSV = () => {
    dt.value.exportCSV();
};
</script>

<template>
    <AppLayout title="Vessel Schedules">
        <template #header>Vessel Schedules</template>

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
                        :row-class="rowClass"
                        :rows="perPage"
                        :rowsPerPageOptions="[5, 10, 20, 50, 100]"
                        :totalRecords="Object.keys(filteredSchedules).length"
                        :value="filteredSchedules"
                        data-key="id"
                        filter-display="menu"
                        lazy
                        paginator
                        removable-sort
                        row-hover
                        tableStyle="min-width: 50rem"
                        @page="onPageChange"
                    >

                        <template #header>
                            <div class="flex flex-col sm:flex-row justify-between items-center mb-2">
                                <div class="text-lg font-medium">
                                    Vessel Schedules
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
                            </div>
                        </template>

                        <template #empty> No vessel schedules found.</template>

                        <template #loading> Loading vessel schedules data. Please wait.</template>

                        <Column field="reference" header="Date">
                            <template #body="slotProps">
                                <i class="ti ti-calendar"></i>
                                {{slotProps.data.start_date}} -
                                <i class="ti ti-calendar"></i>
                                {{slotProps.data.end_date}}
                            </template>
                        </Column>

                        <Column field="reference" header="Shipments">
                            <template #body="slotProps">
                                {{slotProps.data?.clearance_containers.length || 0}}
                            </template>
                        </Column>

                        <Column field="" style="width: 10%">
                            <template #body="{ data }">
                                <Button
                                    class="mr-2"
                                    icon="pi pi-arrow-right"
                                    rounded
                                    size="small"
                                    @click="router.visit(route('clearance.vessel-schedule.show', data.id))"
                                />
                            </template>
                        </Column>

                        <template #footer> In total there are {{ vesselSchedules ? Object.keys(filteredSchedules).length : 0 }} vessel schedules. </template>
                    </DataTable>
                </template>
            </Card>
        </div>
    </AppLayout>
</template>
