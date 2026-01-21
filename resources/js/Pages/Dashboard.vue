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
        type: Object,
        default: () => ({
            total: [],
            exceptions: [],
            assigned: [],
            collected: [],
        }),
    },
    totalPickups: {
        type: Number,
        default: 0,
    },
    hblChartData:{
        type: Array,
        default: () => [],
    },

})

const driverChartOptions = computed(() => {
    return {
        colors: ["#0EA5E9", "#34D399", "#EF4444", "#FBBF24"],
        series: [
            {
                name: "Assigned",
                data: Object.values(props.driverChartData.assigned),
            },
            {
                name: "Total",
                data: Object.values(props.driverChartData.total),
            },
            {
                name: "Exceptions",
                data: Object.values(props.driverChartData.exceptions),
            },
            {
                name: "Collected",
                data: Object.values(props.driverChartData.collected),
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
            show: true,
            position: 'top'
        },
        xaxis: {
            categories: Object.keys(props.driverChartData.total),
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

const hblChartOptions = computed(() => {
    return {
        colors: ["#0EA5E9"],
        series: [
            {
                name: "HBL",
                data: Object.values(props.hblChartData),
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
            show: true,
            position: 'top'
        },
        xaxis: {
            categories: Object.keys(props.hblChartData),
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

        <div v-if="!usePage().props.user?.roles?.includes('customer')" class="grid grid-cols-2 gap-4 sm:grid-cols-4 sm:gap-5 lg:grid-cols-4 lg:gap-5">
            <DashboardCard :count="totalHBLs" icon="box" icon-color="info" title="Total HBL"/>
            <DashboardCard :count="loadedShipments" icon="truck-ramp-box" icon-color="error" title="Loaded Shipment"/>
            <DashboardCard :count="totalContainers" icon="truck-moving" icon-color="primary" title="Total Containers"/>
            <DashboardCard :count="totalPickups" icon="person-biking" icon-color="primary" title="Total Pickups"/>
        </div>


        <div v-if="!usePage().props.user?.roles?.includes('customer')" class="grid grid-cols-2 gap-4 sm:grid-cols-4 sm:gap-5 lg:grid-cols-2 lg:gap-6 mt-10">
            <div class="card">
                <div class="my-3 flex items-center justify-between px-4">
                    <h2 class="font-medium tracking-wide text-slate-700 dark:text-navy-100">
                        Pickups
                    </h2>
                </div>
                <div class="grid grid-cols-2 gap-3 px-4">

                </div>
                <div class="mt-3 px-3">
                    <apexchart :options="driverChartOptions" :series="driverChartOptions.series"></apexchart>
                </div>
            </div>
            <div class="card">
                <div class="my-3 flex items-center justify-between px-4">
                    <h2 class="font-medium tracking-wide text-slate-700 dark:text-navy-100">
                        HBL
                    </h2>
                </div>
                <div class="grid grid-cols-2 gap-3 px-4">

                </div>
                <div class="mt-3 px-3">
                    <apexchart :options="hblChartOptions" :series="hblChartOptions.series"></apexchart>
                </div>
            </div>
        </div>
    </AppLayout>
</template>


