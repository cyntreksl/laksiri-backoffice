<script setup>
import {Link} from "@inertiajs/vue3";
import DestinationAppLayout from "@/Layouts/DestinationAppLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import { computed } from "vue";
import DashboardCard from "@/Components/Widgets/DashboardCard.vue";
import AppLayout from "@/Layouts/AppLayout.vue";

const props = defineProps({
    cashierQueue: {
        type: Object,
        default: () => {}
    },
    cashierQueueCounts: {
        type: Object,
        default: () => {}
    }
})

const filteredCashierQueue = computed(() => {
    return props.cashierQueue.filter(q => {
        return q.is_verified === true && q.is_paid === false
    });
})
</script>

<template>
    <AppLayout title="Queue List">
        <template #header>Queue List</template>

        <Breadcrumb />

        <div class="grid grid-cols-1 gap-3 mt-4 sm:grid-cols-2 md:grid-cols-3">
            <DashboardCard :count="props.cashierQueueCounts.total" icon="briefcase" icon-color="secondary" title="Total"/>
            <DashboardCard :count="props.cashierQueueCounts.pending" icon="hourglass-half" icon-color="warning" title="Pending Job"/>
            <DashboardCard :count="props.cashierQueueCounts.completed" icon="thumbs-up" icon-color="success" title="Completed"/>
        </div>

        <div v-if="Object.keys(filteredCashierQueue).length > 0" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-5 my-5">
            <Link v-for="queue in filteredCashierQueue" :key="queue.id" :href="route('call-center.cashier.create', queue.id)" class="card grow cursor-pointer hover:bg-blue-300 items-center p-4 text-center sm:p-5 border w-60 rounded-lg">
                <div class="my-5">
                    <h1 class="text-7xl text-black font-bold">{{ queue.token }}</h1>
                </div>
                <div class="my-2 grow">
                    <h3 class="text-lg font-medium text-slate-700 dark:text-navy-100">
                        {{ queue.hbl?.hbl_number }}
                    </h3>
                </div>
                <div class="mt-3 flex space-x-1">
                    <button
                        class="btn h-7 rounded-full bg-slate-150 px-3 text-xs+ font-medium text-slate-800 hover:bg-slate-200 focus:bg-slate-200 active:bg-slate-200/80 dark:bg-navy-500 dark:text-navy-50 dark:hover:bg-navy-450 dark:focus:bg-navy-450 dark:active:bg-navy-450/90">
                        {{ queue.customer }}
                    </button>
                </div>
            </Link>
        </div>

        <div v-else class="flex justify-center mt-20 w-full">
            <p class="text-xl">No Tokens Available</p>
        </div>
    </AppLayout>
</template>
