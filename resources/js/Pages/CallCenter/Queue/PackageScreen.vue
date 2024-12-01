<script setup>
import {ref} from "vue";
import ScreenLayout from "@/Layouts/ScreenLayout.vue";
import { usePage } from '@inertiajs/vue3';

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
        <div v-if="waitingScreen" class="flex h-full w-full justify-center items-center">
            <p class="text-9xl uppercase">
                Waiting...
            </p>
        </div>
        <div v-else class="grid grid-cols-2 grid-rows-2 h-full">
            <div class="bg-gray-200 h-full p-5">
                <div
                    v-if="firstToken.token"
                    class="card cursor-pointer flex flex-col justify-center items-center p-4 text-center sm:p-5 h-full rounded-lg bg-lime-300 space-y-3">
                    <h1 class="text-5xl text-black">NOW</h1>
                    <h1 class="text-7xl xl:text-[120px] text-black font-bold">{{ firstToken.hbl?.hbl_number }}</h1>
                    <h3 class="text-2xl font-medium text-slate-700 dark:text-navy-100">
                        {{ firstToken.package_count }} Packages
                    </h3>
                </div>
            </div>

            <div class="col-start-2 row-span-2 bg-gray-200 h-full">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 p-5">
                    <template v-for="queue in packageQueue">
                        <div
                            class="card grow cursor-pointer hover:bg-info/20 items-center p-4 text-center sm:p-5 border w-50 rounded-lg">
                            <div class="my-5">
                                <h1 class="text-4xl text-black font-bold">{{ queue.hbl?.hbl_number }}</h1>
                            </div>
                            <div class="my-2 grow">
                                <h3 class="text-2xl font-medium text-slate-700 dark:text-navy-100">
                                    {{ queue.package_count }} Packages
                                </h3>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <div class="bg-gray-200 h-full p-5">
                <div
                    v-if="nextToken.token"
                    class="card cursor-pointer flex flex-col justify-center items-center p-4 text-center sm:p-5 h-full rounded-lg bg-yellow-300 space-y-3">
                    <h1 class="text-5xl text-black">NEXT</h1>
                    <h1 class="text-7xl xl:text-[120px] text-black font-bold">{{ firstToken.hbl?.hbl_number }}</h1>
                    <h3 class="text-2xl font-medium text-slate-700 dark:text-navy-100">
                        {{ firstToken.package_count }} Packages
                    </h3>
                </div>
            </div>
        </div>
    </ScreenLayout>
</template>
