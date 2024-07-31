<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import DashboardCard from "@/Components/Widgets/DashboardCard.vue";
import {computed} from "vue";
import {usePage} from "@inertiajs/vue3";

const props = defineProps({
    assignedJobs: {
        type: Number,
        default: 0,
    },
    pickedJobs: {
        type: Number,
        default: 0,
    },
    pendingJobs: {
        type: Number,
        default: 0,
    },
    totalHBLs: {
        type: Number,
        default: 0,
    },
    loadedShipments: {
        type: Number,
        default: 0,
    },
    totalContainers: {
        type: Number,
        default: 0,
    },
    warehouses: {
        type: Number,
        default: 0,
    },
    cashSettlements: {
        type: Number,
        default: 0,
    },
    totalDrivers: {
        type: Number,
        default: 0,
    },
    driverAssignedJobs: {
        type: Number,
        default: 0,
    },
    driverChartData: {
        type: Array,
        default: () => [],
    }
})

const driverChartOptions = computed(() => {
    return {
        colors: ["#0EA5E9"],
        series: [
            {
                name: "Assigned",
                data: Object.values(props.driverChartData),
            },
        ],
        chart: {
            parentHeightOffset: 0,
            height: 249,
            type: "area",
            toolbar: {
                show: false,
            },
        },
        fill: {
            type: "gradient",
            gradient: {
                shadeIntensity: 1,
                inverseColors: false,
                opacityFrom: 0.35,
                opacityTo: 0.05,
                stops: [20, 100, 100, 100],
            },
        },
        dataLabels: {
            enabled: false,
        },
        stroke: {
            width: 2,
            curve: "smooth",
        },
        plotOptions: {
            bar: {
                borderRadius: 5,
                barHeight: "90%",
                columnWidth: "40%",
            },
        },
        legend: {
            show: false,
        },
        xaxis: {
            categories: Object.keys(props.driverChartData),
        },
        yaxis: {
            labels: {
                offsetX: -12,
                offsetY: 0,
            },
        },
        grid: {
            padding: {
                left: 0,
                right: 0,
                top: -10,
                bottom: 8,
            },
        },
    }
})
</script>

<template>
    <AppLayout title="Home">
        <template #header>Dashboard</template>

        <div v-if="usePage().props.auth.user.roles[0].name !== 'customer'" class="grid grid-cols-2 gap-4 sm:grid-cols-4 sm:gap-5 lg:grid-cols-6 lg:gap-6">
            <DashboardCard :count="assignedJobs" icon="briefcase" icon-color="secondary" title="Assigned Job"/>
            <DashboardCard :count="pickedJobs" icon="person-biking" icon-color="success" title="Picked"/>
            <DashboardCard :count="pendingJobs" icon="hourglass-half" icon-color="warning" title="Pending Job"/>
            <DashboardCard :count="totalHBLs" icon="box" icon-color="info" title="Total HBL"/>
            <DashboardCard :count="loadedShipments" icon="truck-ramp-box" icon-color="error" title="Loaded Shipment"/>
            <DashboardCard :count="totalContainers" icon="truck-moving" icon-color="primary" title="Total Containers"/>
            <DashboardCard :count="warehouses" icon="warehouse" icon-color="dark" title="Warehouse"/>
            <DashboardCard :count="cashSettlements" icon="cash-register" icon-color="warning" title="Cash Settlements"/>
        </div>


        <div v-if="usePage().props.auth.user.roles[0].name !== 'customer'" class="grid grid-cols-2 gap-4 sm:grid-cols-4 sm:gap-5 lg:grid-cols-3 lg:gap-6 mt-10">
            <div class="card">
                <div class="my-3 flex items-center justify-between px-4">
                    <h2 class="font-medium tracking-wide text-slate-700 dark:text-navy-100">
                        Drivers
                    </h2>
                </div>
                <div class="grid grid-cols-2 gap-3 px-4">
                    <div class="rounded-lg bg-slate-100 p-3 dark:bg-navy-600">
                        <div class="flex justify-between space-x-1">
                            <p class="text-xl font-semibold text-slate-700 dark:text-navy-100">
                                {{ totalDrivers }}
                            </p>
                            <i class="fa fa-person-biking text-xl text-info"></i>
                        </div>
                        <p class="mt-1 text-xs+">Total Drivers</p>
                    </div>
                    <div class="rounded-lg bg-slate-100 p-3 dark:bg-navy-600">
                        <div class="flex justify-between">
                            <p class="text-xl font-semibold text-slate-700 dark:text-navy-100">
                                {{ driverAssignedJobs }}
                            </p>
                            <i class="fa fa-briefcase text-xl text-info"></i>
                        </div>
                        <p class="mt-1 text-xs+">Total Assigned</p>
                    </div>
                </div>
                <div class="mt-3 px-3">
                    <apexchart :options="driverChartOptions" :series="driverChartOptions.series"></apexchart>
                </div>
            </div>
        </div>
    </AppLayout>
</template>


