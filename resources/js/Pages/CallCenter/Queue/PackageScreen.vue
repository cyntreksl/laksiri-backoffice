<script setup>
import {ref} from "vue";
import ScreenLayout from "@/Layouts/ScreenLayout.vue";
import {usePage} from '@inertiajs/vue3';
import Splitter from 'primevue/splitter';
import SplitterPanel from 'primevue/splitterpanel';
import Card from 'primevue/card';
import Tag from 'primevue/tag';

const packageQueue = ref([]);
const firstToken = ref({});
const nextToken = ref({});

const waitingScreen = ref(false);

const getPackageQueue = async () => {
    try {
        const response = await fetch(`/call-center/get-package-queue`, {
            method: "GET",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": usePage().props.csrf,
            },
        });

        if (!response.ok) {
            throw new Error('Network response was not ok.');
        } else {
            const data = await response.json();

            const filteredData = data.filter((item) => {
                return item.is_released == false
            })

            if (filteredData.length === 0) {
                waitingScreen.value = true;
            }

            if (filteredData.length > 0) {
                firstToken.value = filteredData[0];
            }

            if (filteredData.length > 1) {
                nextToken.value = filteredData[1];
            }

            // Store the rest of the array (starting from the third element)
            packageQueue.value = filteredData.slice(2);
        }

    } catch (error) {
        console.log(error);
    }
}

setInterval(getPackageQueue, 3000);

</script>

<template>
    <ScreenLayout title="Package Queue">
        <div class="h-screen">
            <Splitter class="h-full mb-8 !bg-slate-50">
                <SplitterPanel class="flex items-center justify-center">
                    <Splitter class="h-full !bg-slate-50" layout="vertical">
                        <SplitterPanel class="p-5">
                            <Card v-if="firstToken.token" class="!shadow-sm !border !border-success !shadow-success !h-full rounded-2xl bg-white">
                                <template #content>
                                    <div class="flex justify-between items-center mb-6">
                                        <div>
                                            <h1 class="text-4xl text-gray-800 font-semibold tracking-tight">NOW</h1>
                                            <h2 class="text-sm text-gray-500 mt-1">Current Token</h2>
                                        </div>
                                        <div class="flex items-center gap-2 text-success">
                                            <i class="ti ti-packages text-4xl"></i>
                                            <span class="text-4xl font-semibold">{{ firstToken.package_count }}</span>
                                        </div>
                                    </div>

                                    <div class="text-center mb-6">
                                        <h1 class="text-6xl xl:text-[100px] font-extrabold text-gray-900 tracking-wide">
                                            {{ firstToken.token }}
                                        </h1>
                                    </div>

                                    <div class="flex flex-wrap justify-center gap-3">
                                        <template v-for="(hbl_package, index) in firstToken.hbl_packages" :key="index">
                                            <Tag
                                                :value="`${hbl_package.quantity} ${hbl_package.package_type}`"
                                                class="rounded-full px-4 py-2 text-base"
                                                severity="success"
                                                style="font-size: 1rem"
                                            />
                                        </template>
                                    </div>
                                </template>
                            </Card>
                        </SplitterPanel>
                        <SplitterPanel class="p-5">
                            <Card v-if="nextToken.token" class="!shadow-sm !border !border-info !shadow-info !h-full rounded-2xl bg-white">
                                <template #content>
                                    <div class="flex justify-between items-center mb-6">
                                        <div>
                                            <h1 class="text-4xl text-gray-800 font-semibold tracking-tight">NEXT</h1>
                                            <h2 class="text-sm text-gray-500 mt-1">Next Token</h2>
                                        </div>
                                        <div class="flex items-center gap-2 text-info">
                                            <i class="ti ti-packages text-4xl"></i>
                                            <span class="text-4xl font-semibold">{{ nextToken.package_count }}</span>
                                        </div>
                                    </div>

                                    <div class="text-center mb-6">
                                        <h1 class="text-6xl xl:text-[100px] font-extrabold text-gray-900 tracking-wide">
                                            {{ nextToken.token }}
                                        </h1>
                                    </div>

                                    <div class="flex flex-wrap justify-center gap-3">
                                        <template v-for="(hbl_package, index) in nextToken.hbl_packages" :key="index">
                                            <Tag
                                                :value="`${hbl_package.quantity} ${hbl_package.package_type}`"
                                                class="rounded-full px-4 py-2 text-base"
                                                severity="info"
                                                style="font-size: 1rem"
                                            />
                                        </template>
                                    </div>
                                </template>
                            </Card>
                        </SplitterPanel>
                    </Splitter>
                </SplitterPanel>
                <SplitterPanel class="p-5">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 p-5">
                        <template v-for="queue in packageQueue">
                            <Card class="!shadow-sm !border !border-warning !shadow-warning !h-full rounded-2xl bg-white">
                                <template #content>
                                    <div class="flex justify-end items-center mb-6">
                                        <div class="flex items-center gap-2 text-warning">
                                            <i class="ti ti-packages text-4xl"></i>
                                            <span class="text-4xl font-semibold">{{ queue.package_count }}</span>
                                        </div>
                                    </div>

                                    <div class="text-center mb-6">
                                        <h1 class="text-6xl xl:text-[100px] font-extrabold text-gray-900 tracking-wide">
                                            {{ queue.token }}
                                        </h1>
                                    </div>

                                    <div class="flex flex-wrap justify-center gap-3">
                                        <template v-for="(hbl_package, index) in queue.hbl_packages" :key="index">
                                            <Tag
                                                :value="`${hbl_package.quantity} ${hbl_package.package_type}`"
                                                class="rounded-full px-4 py-2 text-base"
                                                severity="warn"
                                                style="font-size: 1rem"
                                            />
                                        </template>
                                    </div>
                                </template>
                            </Card>
                        </template>
                    </div>
                </SplitterPanel>
            </Splitter>
        </div>
    </ScreenLayout>
</template>
