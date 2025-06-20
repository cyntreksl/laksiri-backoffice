<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Button from 'primevue/button';
import {router} from "@inertiajs/vue3";
import {ref} from "vue";

const selectedOption = ref('csv');

const goToNext = () => {
    if (selectedOption.value === 'csv') {
        router.visit(route('third-party-shipments.create'));
    } else if (selectedOption.value === 'manual') {
        router.visit(route('third-party-shipments.create.v2'));
    }
};
</script>

<template>
    <AppLayout title="Create Third Party Shipment">
        <div class="max-w-xl mx-auto p-6 space-y-4">
            <h2 class="text-lg font-medium dark:text-white">
                Please Choose Option to Create Third Party Shipment
            </h2>

            <div class="border border-gray-200 rounded-md overflow-hidden divide-y divide-gray-200">

                <!-- CSV Upload Option -->
                <label class="block cursor-pointer">
                    <input v-model="selectedOption" class="sr-only" name="access" type="radio" value="csv">
                    <div :class="['flex items-start p-4', selectedOption === 'csv' ? 'bg-blue-50' : 'hover:bg-gray-50']">
                        <div :class="['h-5 w-5 mt-1.5 border-2 rounded-full flex items-center justify-center', selectedOption === 'csv' ? 'border-blue-600' : 'border-gray-400']">
                            <div v-if="selectedOption === 'csv'" class="h-2.5 w-2.5 bg-blue-600 rounded-full"></div>
                        </div>
                        <div class="ml-4">
                            <div :class="selectedOption === 'csv' ? 'text-blue-700' : 'text-gray-900 dark:text-white'" class="text-sm font-medium">With Uploading CSV</div>
                            <div :class="selectedOption === 'csv' ? 'text-blue-600' : 'text-gray-500'" class="text-sm">
                                Upload a file to generate third party shipments automatically.
                            </div>
                        </div>
                    </div>
                </label>

                <!-- Manual Entry Option -->
                <label class="block cursor-pointer">
                    <input v-model="selectedOption" class="sr-only" name="access" type="radio" value="manual">
                    <div :class="['flex items-start p-4', selectedOption === 'manual' ? 'bg-blue-50' : 'hover:bg-gray-50']">
                        <div :class="['h-5 w-5 mt-1.5 border-2 rounded-full flex items-center justify-center', selectedOption === 'manual' ? 'border-blue-600' : 'border-gray-400']">
                            <div v-if="selectedOption === 'manual'" class="h-2.5 w-2.5 bg-blue-600 rounded-full"></div>
                        </div>
                        <div class="ml-4">
                            <div :class="selectedOption === 'manual' ? 'text-blue-700' : 'text-gray-900 dark:text-white'" class="text-sm font-medium">With Entering HBL Manually</div>
                            <div :class="selectedOption === 'manual' ? 'text-blue-600' : 'text-gray-500'" class="text-sm">
                                Enter HBL details manually one by one.
                            </div>
                        </div>
                    </div>
                </label>
            </div>

            <div class="flex justify-end pt-2">
                <Button icon="pi pi-arrow-right" label="Next" severity="info" @click="goToNext" />
            </div>
        </div>
    </AppLayout>
</template>
