<script setup>
import {Link} from "@inertiajs/vue3";
import DestinationAppLayout from "@/Layouts/DestinationAppLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import {computed} from "vue";

const props = defineProps({
    packageQueue: {
        type: Object,
        default: () => {}
    }
})

const filteredPackageQueue = computed(() => {
    return props.packageQueue.filter(q => {
        return q.is_released == false
    });
})
</script>

<template>
    <DestinationAppLayout title="Queue List">
        <template #header>Queue List</template>

        <Breadcrumb />

        <div v-if="Object.keys(filteredPackageQueue).length > 0" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-5 my-5">
            <Link v-for="queue in filteredPackageQueue" :key="queue.id" :href="route('call-center.package.create', queue.id)" class="card grow cursor-pointer hover:bg-indigo-300 items-center p-4 text-center sm:p-5 border w-60 rounded-lg">
                <div class="my-5">
                    <h1 class="text-3xl text-black font-bold">{{ queue.reference }}</h1>
                </div>
                <div class="my-2 grow">
                    <h3 class="text-lg font-medium text-slate-700 dark:text-navy-100">
                        {{ queue.package_count }} Packages
                    </h3>
                </div>
            </Link>
        </div>

        <div v-else class="flex justify-center mt-20 w-full">
            <p class="text-xl">No Tokens Available</p>
        </div>
    </DestinationAppLayout>
</template>
