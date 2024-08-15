<script setup>
import {Link} from "@inertiajs/vue3";
import DestinationAppLayout from "@/Layouts/DestinationAppLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";

defineProps({
    verificationQueue: {
        type: Object,
        default: () => {}
    }
})
</script>

<template>
    <DestinationAppLayout title="Queue List">
        <template #header>Queue List</template>

        <Breadcrumb />

        <div v-if="Object.keys(verificationQueue).length > 0" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-5 my-5">
            <Link v-for="queue in verificationQueue" :key="queue.id" :href="route('call-center.verification.create', queue.id)" class="card grow cursor-pointer hover:bg-green-300 items-center p-4 text-center sm:p-5 border w-60 rounded-lg">
                <div class="my-5">
                    <h1 class="text-7xl text-black font-bold">{{ queue.token }}</h1>
                </div>
                <div class="my-2 grow">
                    <h3 class="text-lg font-medium text-slate-700 dark:text-navy-100">
                        {{ queue.reference }}
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
    </DestinationAppLayout>
</template>
