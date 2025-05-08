<script setup>
import {router} from "@inertiajs/vue3";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import {computed, ref} from "vue";
import DashboardCard from "@/Components/Widgets/DashboardCard.vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import DataView from 'primevue/dataview';
import Button from 'primevue/button';
import SelectButton from 'primevue/selectbutton';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Card from 'primevue/card';
import Divider from 'primevue/divider';

const props = defineProps({
    receptionQueue: {
        type: Object,
        default: () => {
        }
    },
    receptionQueueCounts: {
        type: Object,
        default: () => {
        }
    }
})

const layout = ref('list');
const options = ref(['list', 'grid']);

const filteredReceptionQueue = computed(() => {
    return props.receptionQueue.filter(q => q.is_reception_verified === false);
})
</script>

<template>
    <AppLayout title="Reception Queue List">
        <template #header>Reception Queue List</template>

        <Breadcrumb/>

        <div class="grid grid-cols-1 gap-3 mt-4 sm:grid-cols-2 md:grid-cols-3">
            <DashboardCard :count="props.receptionQueueCounts.total" icon="briefcase" icon-color="secondary"
                           title="Total"/>
            <DashboardCard :count="props.receptionQueueCounts.pending" icon="hourglass-half" icon-color="warning"
                           title="Pending Job"/>
            <DashboardCard :count="props.receptionQueueCounts.completed" icon="thumbs-up" icon-color="success"
                           title="Completed"/>
        </div>

        <DataView :layout="layout" :value="filteredReceptionQueue" class="my-5">
            <template #header>
                <div class="flex justify-between items-center">
                    <div class="text-lg font-medium">
                        Reception Verification Queue
                    </div>
                    <SelectButton v-model="layout" :allowEmpty="false" :options="options">
                        <template #option="{ option }">
                            <i :class="[option === 'list' ? 'pi pi-bars' : 'pi pi-table']" />
                        </template>
                    </SelectButton>
                </div>
            </template>

            <template #list="slotProps">
                <DataTable :rows="10" :rowsPerPageOptions="[5, 10, 20, 50]" :value="slotProps.items" paginator row-hover tableStyle="min-width: 50rem">
                    <Column field="token" header="Token">
                        <template #body="slotProps">
                            <div class="flex items-center text-2xl">
                                <i class="ti ti-tag mr-1 text-blue-500"></i>
                                {{ slotProps.data.token }}
                            </div>
                        </template>
                    </Column>
                    <Column field="customer" header="Customer"></Column>
                    <Column field="reference" header="Reference"></Column>
                    <Column field="package_count" header="Packages">
                        <template #body="slotProps">
                            <div class="flex items-center">
                                <i class="ti ti-package mr-1 text-blue-500" style="font-size: 1rem"></i>
                                {{ slotProps.data.package_count }}
                            </div>
                        </template>
                    </Column>
                    <Column field="created_at" header="Created At"></Column>
                    <Column field="" style="width: 10%">
                        <template #body="{ data }">
                            <Button
                                class="mr-2"
                                icon="pi pi-check"
                                rounded
                                size="small"
                                @click.prevent="() => router.visit(route('call-center.reception.create', data.id))"
                            />
                        </template>
                    </Column>
                    <template #footer> In total there are {{ slotProps.items.length }} tokens.</template>
                </DataTable>
            </template>

            <template #grid="slotProps">
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-5 my-5 p-5">
                    <Card v-for="queue in slotProps.items" :key="queue.id"
                          class="hover:cursor-pointer hover:bg-emerald-100 !shadow-none border" @click.prevent="() => router.visit(route('call-center.reception.create', queue.id))">
                        <template #content>
                            <div class="text-center">
                                <h1 class="text-7xl text-black font-bold">{{ queue.token }}</h1>
                            </div>
                            <div class="my-2 grow">
                                <h3 class="text-lg font-medium text-slate-700 dark:text-navy-100">
                                    {{ queue.hbl?.hbl_number }}
                                </h3>
                            </div>
                            <Divider />
                            <div class="block text-xs space-y-1">
                                <div>
                                    <i class="pi pi-calendar mr-2 text-info"></i>
                                    {{queue.created_at}}
                                </div>

                                <div>
                                    <i class="pi pi-user mr-2 text-success"></i>
                                    {{queue.customer}}
                                </div>
                            </div>
                        </template>
                    </Card>
                </div>
            </template>

            <template #empty>
                <div class="flex p-10 justify-center">
                    No Tokens found.
                </div>
            </template>
        </DataView>
    </AppLayout>
</template>
