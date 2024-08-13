<script setup>
import {ref} from "vue";
import ScreenLayout from "@/Layouts/ScreenLayout.vue";

const examinationQueue = ref([]);
const firstToken = ref({});
const nextToken = ref({});

const waitingScreen = ref(false);

const getExaminationQueue = async () => {
    try {
        const response = await fetch(`/call-center/get-examination-queue`, {
            method: "GET",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content")
            },
        });

        if (!response.ok) {
            throw new Error('Network response was not ok.');
        } else {
            const data = await response.json();

            if (data.length === 0) {
                waitingScreen.value = true;
            }

            if (data.length > 0) {
                firstToken.value = data[0];
            }

            if (data.length > 1) {
                nextToken.value = data[1];
            }

            // Store the rest of the array (starting from the third element)
            examinationQueue.value = data.slice(2);
        }

    } catch (error) {
        console.log(error);
    }
}

getExaminationQueue()

setInterval(getExaminationQueue, 3000);

</script>

<template>
    <ScreenLayout title="Document Verification Queue">
        <div v-if="waitingScreen" class="flex h-full w-full justify-center items-center">
            <p class="text-9xl uppercase">
                Waiting...
            </p>
        </div>
        <div v-else class="grid grid-cols-2 grid-rows-2 h-full">
            <div class="bg-gray-200 h-full p-5">
                <div
                    class="card cursor-pointer flex flex-col justify-center items-center p-4 text-center sm:p-5 h-full rounded-lg">
                    <h1 class="text-9xl xl:text-[200px] text-black font-bold">{{ firstToken.token }}</h1>
                    <h3 class="text-2xl font-medium text-slate-700 dark:text-navy-100">
                        {{ firstToken.reference }}
                    </h3>
                    <button
                        class="btn h-7 rounded-full bg-slate-150 px-3 text-xs+ font-medium text-slate-800 hover:bg-slate-200 focus:bg-slate-200 active:bg-slate-200/80 dark:bg-navy-500 dark:text-navy-50 dark:hover:bg-navy-450 dark:focus:bg-navy-450 dark:active:bg-navy-450/90">
                        {{ firstToken.customer }}
                    </button>
                </div>
            </div>

            <div class="col-start-2 row-span-2 bg-gray-200 h-full">
                <div class="grid grid-cols-3 gap-5 p-5">
                    <template v-for="queue in examinationQueue">
                        <div
                            class="card grow cursor-pointer hover:bg-info/20 items-center p-4 text-center sm:p-5 border w-80 rounded-lg">
                            <div class="my-5">
                                <h1 class="text-8xl text-black font-bold">{{ queue.token }}</h1>
                            </div>
                            <div class="my-2 grow">
                                <h3 class="text-2xl font-medium text-slate-700 dark:text-navy-100">
                                    {{ queue.reference }}
                                </h3>
                            </div>
                            <div class="mt-3 flex space-x-1">
                                <button
                                    class="btn h-7 rounded-full bg-slate-150 px-3 text-xs+ font-medium text-slate-800 hover:bg-slate-200 focus:bg-slate-200 active:bg-slate-200/80 dark:bg-navy-500 dark:text-navy-50 dark:hover:bg-navy-450 dark:focus:bg-navy-450 dark:active:bg-navy-450/90">
                                    {{ queue.customer }}
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <div class="bg-gray-200 h-full p-5">
                <div
                    class="card cursor-pointer flex flex-col justify-center items-center p-4 text-center sm:p-5 h-full rounded-lg">
                    <h1 class="text-9xl xl:text-[200px] text-black font-bold">{{ nextToken.token }}</h1>
                    <h3 class="text-2xl font-medium text-slate-700 dark:text-navy-100">
                        {{ nextToken.reference }}
                    </h3>
                    <button
                        class="btn h-7 rounded-full bg-slate-150 px-3 text-xs+ font-medium text-slate-800 hover:bg-slate-200 focus:bg-slate-200 active:bg-slate-200/80 dark:bg-navy-500 dark:text-navy-50 dark:hover:bg-navy-450 dark:focus:bg-navy-450 dark:active:bg-navy-450/90">
                        {{ nextToken.customer }}
                    </button>
                </div>
            </div>
        </div>
    </ScreenLayout>
</template>
