<script setup>
import Breadcrumb from "@/Components/Breadcrumb.vue";
import {computed, ref} from "vue";
import DashboardCard from "@/Components/Widgets/DashboardCard.vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import {router} from "@inertiajs/vue3";
import Column from "primevue/column";
import Card from "primevue/card";
import DataTable from "primevue/datatable";
import DataView from "primevue/dataview";
import Button from "primevue/button";
import SelectButton from "primevue/selectbutton";
import Tag from "primevue/tag";

const props = defineProps({
    examinationQueue: {
        type: Object,
        default: () => {
        }
    },
    examinationQueueCounts: {
        type: Object,
        default: () => {
        }
    }
})

const layout = ref('list');
const options = ref(['list', 'grid']);

const filteredExaminationQueue = computed(() => {
    return props.examinationQueue.filter(q => {
        return q.is_verified === true && q.is_paid === true && q.is_force_released === false
    });
})
</script>

<template>
    <AppLayout title="Examination Queue">
        <template #header>Examination Queue</template>

        <Breadcrumb/>

        <div class="grid grid-cols-1 gap-3 mt-4 sm:grid-cols-2 md:grid-cols-3">
            <DashboardCard :count="props.examinationQueueCounts.total" icon="briefcase" icon-color="secondary"
                           title="Total"/>
            <DashboardCard :count="props.examinationQueueCounts.pending" icon="hourglass-half" icon-color="warning"
                           title="Pending Job"/>
            <DashboardCard :count="props.examinationQueueCounts.completed" icon="thumbs-up" icon-color="success"
                           title="Completed"/>
        </div>


        <DataView :layout="layout" :value="filteredExaminationQueue" class="my-5">
            <template #header>
                <div class="flex justify-between items-center">
                    <div class="text-lg font-medium">
                        Examination Queue
                    </div>
                    <SelectButton v-model="layout" :allowEmpty="false" :options="options">
                        <template #option="{ option }">
                            <i :class="[option === 'list' ? 'pi pi-bars' : 'pi pi-table']"/>
                        </template>
                    </SelectButton>
                </div>
            </template>

            <template #list="slotProps">
                <DataTable :rows="10" :rowsPerPageOptions="[5, 10, 20, 50]" :value="slotProps.items" paginator row-hover
                           tableStyle="min-width: 50rem">
                    <Column field="token" header="Token">
                        <template #body="slotProps">
                            <div class="flex items-center text-2xl">
                                <i v-if="slotProps.data.is_released_from_boned_area"
                                   v-tooltip="'Released From Boned Area'"
                                   class="ti ti-tag-starred mr-1 text-success"></i>
                                <i v-else v-tooltip="'Not Released From Boned Area'"
                                   class="ti ti-tag-off mr-1 text-error"></i>
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
                                v-if="data.is_released_from_boned_area"
                                class="mr-2"
                                icon="ti ti-arrow-right"
                                rounded
                                size="small"
                                @click.prevent="() => router.visit(route('call-center.examination.create', data.id))"
                            />
                        </template>
                    </Column>
                    <template #footer> In total there are {{ slotProps.items.length }} tokens.</template>
                </DataTable>
            </template>

            <template #grid="slotProps">
                <div
                    class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-5 my-5 p-5">
                    <Card
                        v-for="queue in slotProps.items"
                        :key="queue.id"
                        :class="[
    'rounded-2xl bg-white',
    queue.is_released_from_boned_area ? 'border border-success hover:bg-success/10 cursor-pointer' : 'border border-error hover:bg-error/10 cursor-not-allowed',
  ]"
                        @click.prevent="queue.is_released_from_boned_area && router.visit(route('call-center.examination.create', queue.id))"
                    >
                        <template #content>
                            <div class="flex justify-between items-center mb-6">
                                <div class="text-2xl">
                                    <i
                                        v-if="queue.is_released_from_boned_area"
                                        v-tooltip="'Released From Boned Area'"
                                        class="ti ti-tag-starred mr-1 text-success"
                                    ></i>
                                    <i
                                        v-else
                                        v-tooltip="'Not Released From Boned Area'"
                                        class="ti ti-tag-off mr-1 text-error"
                                    ></i>
                                </div>
                                <div
                                    :class="['flex items-center gap-2 ', queue.is_released_from_boned_area ? 'text-success' : 'text-error']">
                                    <i class="ti ti-packages text-2xl"></i>
                                    <span class="text-2xl font-semibold">{{ queue.package_count }}</span>
                                </div>
                            </div>

                            <div class="text-center mb-6">
                                <h1 class="text-5xl xl:text-[80px] font-extrabold text-gray-900 tracking-wide">
                                    {{ queue?.token }}
                                </h1>
                            </div>

                            <div class="flex flex-wrap justify-center gap-1">
                                <Tag
                                    :severity="queue.is_released_from_boned_area ? 'success' : 'danger'"
                                    :value="queue.customer"
                                    class="rounded-full px-4 py-2 text-base"
                                />
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
