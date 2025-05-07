<script setup>
import {router} from "@inertiajs/vue3";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import {computed, ref} from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import Column from "primevue/column";
import Card from "primevue/card";
import DataTable from "primevue/datatable";
import DataView from "primevue/dataview";
import Button from "primevue/button";
import SelectButton from "primevue/selectbutton";
import Tag from "primevue/tag";

const props = defineProps({
    packageQueue: {
        type: Object,
        default: () => {}
    }
})

const layout = ref('list');
const options = ref(['list', 'grid']);

const filteredPackageQueue = computed(() => {
    return props.packageQueue.filter(q => {
        return q.is_released == false
    });
})
</script>

<template>
    <AppLayout title="Queue List">
        <template #header>Queue List</template>

        <Breadcrumb />

        <DataView :layout="layout" :value="filteredPackageQueue" class="my-5">
            <template #header>
                <div class="flex justify-between items-center">
                    <div class="text-lg font-medium">
                        Package Calling Queue
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
                            <div class="flex flex-wrap space-x-1 mt-1">
                                <div v-for="(hbl_package, index) in slotProps.data?.hbl_packages" :key="index">
                                    <Tag
                                        :value="`${hbl_package.quantity} ${hbl_package.package_type}`"
                                        severity="info"
                                    />
                                </div>
                            </div>
                        </template>
                    </Column>
                    <Column field="created_at" header="Created At"></Column>
                    <Column field="" style="width: 10%">
                        <template #body="{ data }">
                            <Button
                                class="mr-2"
                                icon="ti ti-arrow-right"
                                rounded
                                size="small"
                                @click.prevent="() => router.visit(route('call-center.package.create', data.id))"
                            />
                        </template>
                    </Column>
                    <template #footer> In total there are {{ slotProps.items.length }} tokens.</template>
                </DataTable>
            </template>

            <template #grid="slotProps">
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-5 my-5 p-5">
                    <Card v-for="queue in slotProps.items" :key="queue.id" class="!border !border-info rounded-2xl bg-white cursor-pointer hover:bg-info/10" @click.prevent="() => router.visit(route('call-center.package.create', queue.id))">
                        <template #content>
                            <div class="flex justify-between items-center mb-6">
                                <div class="flex items-center gap-2 text-info">
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
                                <template v-for="(hbl_package, index) in queue?.hbl_packages" :key="index">
                                    <Tag
                                        :value="`${hbl_package.quantity} ${hbl_package.package_type}`"
                                        class="rounded-full px-4 py-2 text-base"
                                        severity="info"
                                    />
                                </template>
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
