<script setup>
import {Link} from "@inertiajs/vue3";
import DestinationAppLayout from "@/Layouts/DestinationAppLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import { computed } from "vue";
import DashboardCard from "@/Components/Widgets/DashboardCard.vue";

const props = defineProps({
    examinationQueue: {
        type: Object,
        default: () => {}
    },
    examinationQueueCounts: {
        type: Object,
        default: () => {}
    }
})
console.log(props.examinationQueue);
const filteredExaminationQueue = computed(() => {
    return props.examinationQueue.filter(q => {
        return q.is_verified === true && q.is_paid === true && q.is_force_released === false
    });
})
</script>

<template>
    <DestinationAppLayout title="Queue List">
        <template #header>Queue List</template>

        <Breadcrumb />

        <div class="grid grid-cols-1 gap-3 mt-4 sm:grid-cols-2 md:grid-cols-3">
            <DashboardCard :count="props.examinationQueueCounts.total" icon="briefcase" icon-color="secondary" title="Total"/>
            <DashboardCard :count="props.examinationQueueCounts.pending" icon="hourglass-half" icon-color="warning" title="Pending Job"/>
            <DashboardCard :count="props.examinationQueueCounts.completed" icon="thumbs-up" icon-color="success" title="Completed"/>
        </div>


        <div v-if="Object.keys(filteredExaminationQueue).length > 0" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-5 my-5">
            <template v-for="queue in filteredExaminationQueue" :key="queue.id">
                <Link v-if="queue.is_released_from_boned_area" :href="route('call-center.examination.create', queue.id)" class="card grow cursor-pointer hover:bg-green-200 items-center p-4 text-center sm:p-5 border w-60 rounded-lg border-green-500">
                    <div class="my-5">
                        <h1 class="text-7xl text-black font-bold">{{ queue.token }}</h1>
                    </div>
                    <div class="my-2 grow">
                        <h3 class="text-lg font-medium text-slate-700 dark:text-navy-100">
                            {{ queue.hbl?.hbl_number }}
                        </h3>
                    </div>
                    <div class="mt-3 space-y-1">
                        <button
                            class="btn h-7 rounded-full bg-slate-150 px-3 text-xs+ font-medium text-slate-800 hover:bg-slate-200 focus:bg-slate-200 active:bg-slate-200/80 dark:bg-navy-500 dark:text-navy-50 dark:hover:bg-navy-450 dark:focus:bg-navy-450 dark:active:bg-navy-450/90">
                            {{ queue.customer }}
                        </button>

                        <div v-if="queue.is_released_from_boned_area" class="flex text-xs items-center">
                            <svg  class="icon icon-tabler icons-tabler-filled icon-tabler-rosette-discount-check text-success mr-2"  fill="currentColor"  height="24"  viewBox="0 0 24 24"  width="24"  xmlns="http://www.w3.org/2000/svg"><path d="M0 0h24v24H0z" fill="none" stroke="none"/><path d="M12.01 2.011a3.2 3.2 0 0 1 2.113 .797l.154 .145l.698 .698a1.2 1.2 0 0 0 .71 .341l.135 .008h1a3.2 3.2 0 0 1 3.195 3.018l.005 .182v1c0 .27 .092 .533 .258 .743l.09 .1l.697 .698a3.2 3.2 0 0 1 .147 4.382l-.145 .154l-.698 .698a1.2 1.2 0 0 0 -.341 .71l-.008 .135v1a3.2 3.2 0 0 1 -3.018 3.195l-.182 .005h-1a1.2 1.2 0 0 0 -.743 .258l-.1 .09l-.698 .697a3.2 3.2 0 0 1 -4.382 .147l-.154 -.145l-.698 -.698a1.2 1.2 0 0 0 -.71 -.341l-.135 -.008h-1a3.2 3.2 0 0 1 -3.195 -3.018l-.005 -.182v-1a1.2 1.2 0 0 0 -.258 -.743l-.09 -.1l-.697 -.698a3.2 3.2 0 0 1 -.147 -4.382l.145 -.154l.698 -.698a1.2 1.2 0 0 0 .341 -.71l.008 -.135v-1l.005 -.182a3.2 3.2 0 0 1 3.013 -3.013l.182 -.005h1a1.2 1.2 0 0 0 .743 -.258l.1 -.09l.698 -.697a3.2 3.2 0 0 1 2.269 -.944zm3.697 7.282a1 1 0 0 0 -1.414 0l-3.293 3.292l-1.293 -1.292l-.094 -.083a1 1 0 0 0 -1.32 1.497l2 2l.094 .083a1 1 0 0 0 1.32 -.083l4 -4l.083 -.094a1 1 0 0 0 -.083 -1.32z" /></svg>

                            Released From Boned Area
                        </div>
                    </div>
                </Link>

                <div v-else class="card grow cursor-not-allowed items-center p-4 text-center sm:p-5 border w-60 rounded-lg border-red-500">
                    <div class="my-5">
                        <h1 class="text-7xl text-black font-bold">{{ queue.token }}</h1>
                    </div>
                    <div class="my-2 grow">
                        <h3 class="text-lg font-medium text-slate-700 dark:text-navy-100">
                            {{ queue.hbl?.hbl_number }}
                        </h3>
                    </div>
                    <div class="mt-3 space-y-1">
                        <button
                            class="btn h-7 rounded-full bg-slate-150 px-3 text-xs+ font-medium text-slate-800 hover:bg-slate-200 focus:bg-slate-200 active:bg-slate-200/80 dark:bg-navy-500 dark:text-navy-50 dark:hover:bg-navy-450 dark:focus:bg-navy-450 dark:active:bg-navy-450/90">
                            {{ queue.customer }}
                        </button>

                        <div v-if="queue.is_released_from_boned_area" class="flex text-xs items-center">
                            <svg  class="icon icon-tabler icons-tabler-filled icon-tabler-rosette-discount-check text-success mr-2"  fill="currentColor"  height="24"  viewBox="0 0 24 24"  width="24"  xmlns="http://www.w3.org/2000/svg"><path d="M0 0h24v24H0z" fill="none" stroke="none"/><path d="M12.01 2.011a3.2 3.2 0 0 1 2.113 .797l.154 .145l.698 .698a1.2 1.2 0 0 0 .71 .341l.135 .008h1a3.2 3.2 0 0 1 3.195 3.018l.005 .182v1c0 .27 .092 .533 .258 .743l.09 .1l.697 .698a3.2 3.2 0 0 1 .147 4.382l-.145 .154l-.698 .698a1.2 1.2 0 0 0 -.341 .71l-.008 .135v1a3.2 3.2 0 0 1 -3.018 3.195l-.182 .005h-1a1.2 1.2 0 0 0 -.743 .258l-.1 .09l-.698 .697a3.2 3.2 0 0 1 -4.382 .147l-.154 -.145l-.698 -.698a1.2 1.2 0 0 0 -.71 -.341l-.135 -.008h-1a3.2 3.2 0 0 1 -3.195 -3.018l-.005 -.182v-1a1.2 1.2 0 0 0 -.258 -.743l-.09 -.1l-.697 -.698a3.2 3.2 0 0 1 -.147 -4.382l.145 -.154l.698 -.698a1.2 1.2 0 0 0 .341 -.71l.008 -.135v-1l.005 -.182a3.2 3.2 0 0 1 3.013 -3.013l.182 -.005h1a1.2 1.2 0 0 0 .743 -.258l.1 -.09l.698 -.697a3.2 3.2 0 0 1 2.269 -.944zm3.697 7.282a1 1 0 0 0 -1.414 0l-3.293 3.292l-1.293 -1.292l-.094 -.083a1 1 0 0 0 -1.32 1.497l2 2l.094 .083a1 1 0 0 0 1.32 -.083l4 -4l.083 -.094a1 1 0 0 0 -.083 -1.32z" /></svg>

                            Released From Boned Area
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <div v-else class="flex justify-center mt-20 w-full">
            <p class="text-xl">No Tokens Available</p>
        </div>
    </DestinationAppLayout>
</template>
