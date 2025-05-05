<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import Card from 'primevue/card';
import Button from 'primevue/button';
import {router} from "@inertiajs/vue3";

const props = defineProps({
    vesselSchedules: {
        type: Object,
        default: () => {
        },
    },
    containers: {
        type: Object,
        default: () => {
        },
    },
});

</script>

<template>
    <AppLayout title="Vessel Schedule">
        <template #header>Vessel Schedule</template>

        <Breadcrumb/>

        <div class="flex items-center mt-5 space-x-2">
            <h1 class="text-3xl ml-2 font-medium text-gray-700">Vessel Schedule {{vesselSchedules.start_date}} to {{vesselSchedules.end_date}}</h1>
        </div>

        <div class="grid grid-cols-12 gap-4 my-5">
            <Card class="col-span-4 border-2 border-gray-200 !shadow-none">
                <template #title>
                    <div class="flex justify-between">
                        <div>
                            <div class="flex items-center space-x-2 text-xs text-gray-400">
                                <div>
                                    {{vesselSchedules.start_date}}
                                </div>
                                <div>
                                    <i class="ti ti-arrow-narrow-right text-xl"></i>
                                </div>
                                <div>{{vesselSchedules.end_date}}</div>
                            </div>
                            <div>
                                Available Vessels
                            </div>
                        </div>
                    </div>
                </template>
                <template #subtitle>{{containers.length}} Vessels</template>
                <template #content>
                    <div class="space-y-5">
                        <Card
                            class="!bg-transparent cursor-pointer !shadow-none transition-all duration-300 ease-in-out hover:bg-gradient-to-r hover:from-gray-700 hover:via-gray-800 hover:to-gray-900 hover:text-white border-2 border-black"
                            v-for="(container, index) in vesselSchedules.containers"
                        >
                            <template #content>
                                <div class="flex justify-between text-sm">
                                    <div>
                                        {{container.container_type}}
                                    </div>
                                    <div>
                                        {{container.cargo_type}}
                                    </div>
                                    <div>
                                        {{container.warehouse.name}}
                                    </div>
                                </div>

                                <div class="my-8 flex items-center justify-between">
                                    <h1 class="text-3xl font-medium">{{container.reference}}</h1>
                                    <i class="ti ti-ship text-4xl"></i>
                                </div>

                                <div class="flex justify-between text-sm">
                                    <div>
                                        ONEY344OL333333
                                    </div>
                                    <div>
                                        12547845
                                    </div>
                                </div>
                            </template>
                        </Card>


                    </div>
                </template>
            </Card>

            <Card class="col-span-8 border-2 border-gray-200 !shadow-none">
                <template #title>
                    <div class="flex justify-between">
                        <div>
                            <div class="flex items-center space-x-2 text-xs text-gray-400">
                                <div class="flex items-center">
                                    <i class="ti ti-plane-departure text-xl mr-2"></i>
                                    RIYADH
                                </div>
                                <div>
                                    <i class="ti ti-arrow-narrow-right text-xl"></i>
                                </div>
                                <div class="flex items-center">
                                    <i class="ti ti-plane-arrival text-xl mr-2"></i>
                                    COLOMBO
                                </div>
                            </div>
                            <div>
                                Package Details
                            </div>
                        </div>
                        <div class="space-x-2">
                            <Button v-tooltip.left="'Clear Vessel'" icon="pi pi-check" rounded severity="success" size="small" variant="outlined" />
                            <Button v-tooltip.left="'Mark As Return'" icon="pi pi-times" rounded severity="danger" size="small" variant="outlined" />
                        </div>
                    </div>
                </template>
                <template #subtitle>
                    QT-LD-000019
                </template>
                <template #content>
                    <div class="space-y-1">
                        <Card class="border border-gray-100 !shadow-none">
                            <template #title>
                                <div class="text-sm">
                                    HBL 1
                                </div>
                            </template>
                            <template #content>
                                <ul class="space-y-1">
                                    <li class="flex justify-between items-center bg-gray-100 p-2 px-5 rounded-lg hover:bg-gray-200">
                                        <div class="flex items-center">
                                            <i class="ti ti-package mr-3"></i>
                                            <div class="font-medium">CARTOON</div>
                                        </div>
                                        <div class="text-xs font-medium">12CM x 10CM x 10CM</div>
                                        <div v-tooltip.left="'Weight'" class="font-medium">120kg</div>
                                        <div v-tooltip.left="'Quantity'" class="font-medium">1</div>
                                    </li>

                                    <li class="flex justify-between items-center bg-gray-100 p-2 px-5 rounded-lg hover:bg-gray-200">
                                        <div class="flex items-center">
                                            <i class="ti ti-package mr-3"></i>
                                            <div class="font-medium">CARTOON</div>
                                        </div>
                                        <div class="text-xs font-medium">12CM x 10CM x 10CM</div>
                                        <div v-tooltip.left="'Weight'" class="font-medium">120kg</div>
                                        <div v-tooltip.left="'Quantity'" class="font-medium">1</div>
                                    </li>
                                    <li class="flex justify-between items-center bg-gray-100 p-2 px-5 rounded-lg hover:bg-gray-200">
                                        <div class="flex items-center">
                                            <i class="ti ti-package mr-3"></i>
                                            <div class="font-medium">CARTOON</div>
                                        </div>
                                        <div class="text-xs font-medium">12CM x 10CM x 10CM</div>
                                        <div v-tooltip.left="'Weight'" class="font-medium">120kg</div>
                                        <div v-tooltip.left="'Quantity'" class="font-medium">1</div>
                                    </li>
                                </ul>
                            </template>
                        </Card>

                        <Card class="border border-gray-100 !shadow-none">
                            <template #title>
                                <div class="text-sm">
                                    HBL 2
                                </div>
                            </template>
                            <template #content>
                                <ul class="space-y-1">
                                    <li class="flex justify-between items-center bg-gray-100 p-2 px-5 rounded-lg hover:bg-gray-200">
                                        <div class="flex items-center">
                                            <i class="ti ti-package mr-3"></i>
                                            <div class="font-medium">CARTOON</div>
                                        </div>
                                        <div class="text-xs font-medium">12CM x 10CM x 10CM</div>
                                        <div v-tooltip.left="'Weight'" class="font-medium">120kg</div>
                                        <div v-tooltip.left="'Quantity'" class="font-medium">1</div>
                                    </li>

                                    <li class="flex justify-between items-center bg-gray-100 p-2 px-5 rounded-lg hover:bg-gray-200">
                                        <div class="flex items-center">
                                            <i class="ti ti-package mr-3"></i>
                                            <div class="font-medium">CARTOON</div>
                                        </div>
                                        <div class="text-xs font-medium">12CM x 10CM x 10CM</div>
                                        <div v-tooltip.left="'Weight'" class="font-medium">120kg</div>
                                        <div v-tooltip.left="'Quantity'" class="font-medium">1</div>
                                    </li>
                                </ul>
                            </template>
                        </Card>
                    </div>
                </template>
            </Card>
        </div>
    </AppLayout>
</template>

<style scoped>

</style>
