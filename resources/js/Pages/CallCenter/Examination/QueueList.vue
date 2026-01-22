<script setup>
import Breadcrumb from "@/Components/Breadcrumb.vue";
import {ref} from "vue";
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
import {computed} from "vue";

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
    return props.examinationQueue.filter(queue => !queue.left_at);
});

// Helper function to get release status info
const getReleaseStatusInfo = (queue) => {
    if (queue.release_status === 'fully_released') {
        return {
            icon: 'ti ti-tag-starred',
            color: 'text-success',
            severity: 'success',
            label: 'Fully Released',
            tooltip: `All ${queue.package_count} packages released from Bonded Area`,
            canProceed: true
        };
    } else if (queue.release_status === 'partially_released') {
        return {
            icon: 'ti ti-tag',
            color: 'text-warning',
            severity: 'warning',
            label: `Partially Released (${queue.released_package_count}/${queue.package_count})`,
            tooltip: `${queue.released_package_count} of ${queue.package_count} packages released, ${queue.held_package_count} still on hold`,
            canProceed: true
        };
    } else {
        return {
            icon: 'ti ti-tag-off',
            color: 'text-error',
            severity: 'danger',
            label: 'Not Released',
            tooltip: 'No packages released from Bonded Area',
            canProceed: false
        };
    }
};

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
                            <div class="flex items-center gap-2">
                                <div class="text-2xl flex items-center">
                                    <i
                                        :class="[getReleaseStatusInfo(slotProps.data).icon, 'mr-1', getReleaseStatusInfo(slotProps.data).color]"
                                        :v-tooltip="getReleaseStatusInfo(slotProps.data).tooltip"
                                    ></i>
                                    {{ slotProps.data.token }}
                                </div>
                            </div>
                        </template>
                    </Column>
                    <Column field="customer" header="Customer"></Column>
                    <Column field="hbl_number" header="HBL Number">
                         <template #body="slotProps">
                            {{ slotProps.data.hbl_number || slotProps.data.hbl?.hbl_number || slotProps.data.reference }}
                        </template>
                    </Column>
                    <Column field="release_status" header="Release Status">
                        <template #body="slotProps">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <Tag
                                        :severity="getReleaseStatusInfo(slotProps.data).severity"
                                        :value="getReleaseStatusInfo(slotProps.data).label"
                                    />
                                    <i
                                        v-if="slotProps.data.release_status === 'partially_released'"
                                        v-tooltip.top="getReleaseStatusInfo(slotProps.data).tooltip"
                                        class="pi pi-info-circle text-warning cursor-help"
                                    ></i>
                                </div>
                                <div v-if="slotProps.data.release_status === 'partially_released'" class="text-xs text-gray-600 space-y-0.5">
                                    <div class="flex items-center gap-1">
                                        <i class="pi pi-check-circle text-success" style="font-size: 0.7rem"></i>
                                        <span>Released: {{ slotProps.data.released_package_count }}</span>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <i class="pi pi-clock text-warning" style="font-size: 0.7rem"></i>
                                        <span>On Hold: {{ slotProps.data.held_package_count }}</span>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </Column>
                    <Column field="package_count" header="Total Packages">
                        <template #body="slotProps">
                            <div class="flex items-center">
                                <i class="ti ti-package mr-1 text-blue-500" style="font-size: 1rem"></i>
                                {{ slotProps.data.package_count }}
                            </div>
                        </template>
                    </Column>
<!--                    <Column field="created_at" header="Created At"></Column>-->
                    <Column field="" style="width: 10%">
                        <template #body="{ data }">
                            <Button
                                v-if="getReleaseStatusInfo(data).canProceed"
                                class="mr-2"
                                icon="ti ti-arrow-right"
                                rounded
                                size="small"
                                :severity="data.release_status === 'partially_released' ? 'warning' : 'success'"
                                @click.prevent="() => router.visit(route('call-center.examination.create', data.id))"
                            />
                            <Tag v-else severity="danger" value="Waiting" />
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
                            getReleaseStatusInfo(queue).canProceed
                                ? `border border-${getReleaseStatusInfo(queue).severity} hover:bg-${getReleaseStatusInfo(queue).severity}/10 cursor-pointer`
                                : 'border border-error hover:bg-error/10 cursor-not-allowed',
                        ]"
                        @click.prevent="getReleaseStatusInfo(queue).canProceed && router.visit(route('call-center.examination.create', queue.id))"
                    >
                        <template #content>
                            <div class="flex justify-between items-center mb-4">
                                <div class="text-2xl">
                                    <i
                                        :class="[getReleaseStatusInfo(queue).icon, 'mr-1', getReleaseStatusInfo(queue).color]"
                                        :v-tooltip="getReleaseStatusInfo(queue).tooltip"
                                    ></i>
                                </div>
                                <div :class="['flex items-center gap-2', getReleaseStatusInfo(queue).color]">
                                    <i class="ti ti-packages text-2xl"></i>
                                    <span class="text-2xl font-semibold">{{ queue.package_count }}</span>
                                </div>
                            </div>

                            <!-- Release Status Badge -->
                            <div class="mb-4 flex justify-center">
                                <Tag
                                    :severity="getReleaseStatusInfo(queue).severity"
                                    :value="getReleaseStatusInfo(queue).label"
                                    class="text-sm"
                                />
                            </div>

                            <!-- Package Count Details for Partial Release -->
                            <div v-if="queue.release_status === 'partially_released'" class="mb-4 flex justify-center gap-2">
                                <Tag :value="`${queue.released_package_count} Released`" class="text-xs" severity="success" />
                                <Tag :value="`${queue.held_package_count} On Hold`" class="text-xs" severity="warning" />
                            </div>

                            <div class="text-center mb-6">
                                <h1 class="text-5xl xl:text-[80px] font-extrabold text-gray-900 tracking-wide">
                                    {{ queue?.token }}
                                </h1>
                            </div>

                            <div class="flex flex-wrap justify-center gap-1">
                                <Tag
                                    :severity="getReleaseStatusInfo(queue).severity"
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
