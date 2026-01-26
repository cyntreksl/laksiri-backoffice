<script setup xmlns="http://www.w3.org/1999/html">
import {onMounted, ref, computed} from "vue";
import moment from "moment";
import {Head} from "@inertiajs/vue3";
import DashboardMeet from "../../images/illustrations/dashboard-meet.svg";
import DashboardMeetDark from "../../images/illustrations/dashboard-meet-dark.svg";
import Card from 'primevue/card';
import Badge from 'primevue/badge';

const props = defineProps({
    reference: {
        type: String,
        required: false,
    }
});

const reference = ref(props.reference ?? null);
const errorMessage = ref('');
const isLoading = ref(false);
const hblStatus = ref([]);
const hblDetails = ref(null);
const containerDetails = ref(null);

// Computed property for current status
const currentStatus = computed(() => {
    if (hblStatus.value.length === 0) return '-';
    const latestStatus = hblStatus.value[hblStatus.value.length - 1]?.status;
    return getUserFriendlyStatus(latestStatus) || latestStatus || '-';
});

const handleSubmit = async () => {
    errorMessage.value = '';

    if (!reference.value) {
        errorMessage.value = "Please enter the valid reference first!";
        return;
    }

    isLoading.value = true;

    try {
        const response = await fetch(`/get-hbl-status-by-reference/${reference.value}`, {
            method: "GET",
            headers: {
                "Content-Type": "application/json",
            },
        });

        if (!response.ok) {
            if (response.status === 404) {
                hblStatus.value = [];
                errorMessage.value = 'Invalid reference!';
            }
            throw new Error('Network response was not ok.');
        } else {
            hblStatus.value = await response.json();
        }

        // Fetch HBL details including pickup dates
        try {
            const detailsRes = await fetch(`/get-hbl-details-by-reference/${reference.value}`, {
                method: "GET",
                headers: {
                    "Content-Type": "application/json",
                },
            });

            if (!detailsRes.ok) {
                hblDetails.value = null;
            } else {
                hblDetails.value = await detailsRes.json();
            }
        } catch (e) {
            hblDetails.value = null;
        }

        // Fetch Container details including loading start and ETD
        try {
            const contRes = await fetch(`/get-container-details-by-reference/${reference.value}`, {
                method: "GET",
                headers: {
                    "Content-Type": "application/json",
                },
            });

            if (!contRes.ok) {
                containerDetails.value = null;
            } else {
                containerDetails.value = await contRes.json();
            }
        } catch (e) {
            containerDetails.value = null;
        }

    } catch (error) {
        console.log(error);
    } finally {
        isLoading.value = false;
    }
};

onMounted(() => {
    if (reference.value) {
        handleSubmit();
    }
});

const getStatusIcon = (status) => {
    switch (status.toLowerCase()) {
        case 'delivered':
        case 'container reached destination':
            return 'pi pi-check-circle';
        case 'out for delivery':
        case 'container in transit':
            return 'pi pi-truck';
        case 'customs clearance':
            return 'pi pi-file';
        case 'transferred':
            return 'pi pi-refresh';
        case 'arrived':
        case 'container arrival':
            return 'pi pi-home';
        case 'departed':
        case 'container shipped':
            return 'pi pi-send';
        case 'under process':
        case 'hbl preparation by warehouse':
        case 'hbl preparation by driver':
            return 'pi pi-cog';
        case 'cash received by accountant':
            return 'pi pi-dollar';
        case 'container loading':
        case 'container loading in colombo':
            return 'pi pi-box';
        case 'container unloaded in colombo':
        case 'container unloaded in nintavur':
            return 'pi pi-download';
        case 'blocked by rtf':
            return 'pi pi-ban';
        case 'revert to cash settlement':
            return 'pi pi-undo';
        default:
            return 'pi pi-circle';
    }
};

const getUserFriendlyStatus = (status) => {
    const statusMap = {
        'HBL Preparation by warehouse': 'Shipment Pickup',
        'HBL Preparation by driver': 'Shipment Picked Up',
        // 'Cash Received by Accountant': 'Arrived',
        'Container Loading': 'Shipment Export process',
        // 'Container Shipped': 'Departed',
        'Container Arrival': 'Shipment Arrival process',
        'Blocked By RTF': 'Shipment held for inspection',
        'Container Unloaded in Colombo': 'Shipment',
        'Container Reached Destination': 'Delivered'
        // All other statuses are hidden from display
        // 'Revert To Cash Settlement': 'Payment method changed',
        // 'Container Loading in Colombo': 'Container being loaded in Colombo',
        // 'Container Unloaded in Nintavur': 'Container unloaded at Nintavur',
        // 'Container In Transit': 'Package is on the way to you',
    };
    return statusMap[status] || null; // Return null for unmapped statuses to hide them
};

const getStatusDescription = (status) => {
    const descriptions = {
        'HBL Preparation by warehouse': 'Your shipment has been picked up and is being processed for delivery.',
        'HBL Preparation by driver': 'Your shipment has been picked up and is being processed for delivery.',
        'Cash Received by Accountant': 'Your shipment has arrived at origin warehouse.',
        'Container Loading': 'Your shipment is currently being processed and prepared for departure.',
        'Container Shipped': 'Your shipment has departed to Sri Lanka and is on its way.',
        'Container Arrival': 'Your shipment has arrived at the destination and is being processed.',
        'Blocked By RTF': 'Your shipment is temporarily held for routine customs inspection.',
        'Container Unloaded in Colombo': 'Please book an appointment for cargo collection.',
        'Container Reached Destination': 'Congratulations! Your package has been successfully delivered.'
        // All other statuses are hidden from display
        // 'Revert To Cash Settlement': 'Payment method has been updated as per your request.',
        // 'Container Loading in Colombo': 'Your package is being loaded for the next leg of its journey.',
        // 'Container Unloaded in Nintavur': 'Container has been unloaded at the final destination.',
        // 'Container In Transit': 'Your package is currently traveling to its destination.',
    };
    return descriptions[status] || null; // Return null for unmapped statuses to hide them
};

const getEstimatedTime = (status, index, total) => {
    if (index === total - 1) {
        return null; // Current status
    }

    const estimates = {
        'HBL Preparation by warehouse': '1-2 business days',
        'HBL Preparation by driver': '2-4 hours',
        'Container Shipped': '3-5 days',
        'Container In Transit': '1-2 days',
        'Container Arrival': '6-12 hours'
    };
    return estimates[status];
};
</script>

<template>
    <Head title="Tracking"/>

    <div class="min-h-screen bg-gray-50">

        <!-- Search Section -->
        <div class="bg-white shadow-sm border-b">
            <div class="max-w-3xl mx-auto px-4 py-6">
                <div class="max-w-md mx-auto">
                    <h1 class="text-2xl font-bold text-center mb-4 text-gray-800">Track Your Shipment</h1>

                    <!-- Error Message -->
                    <div
                        v-if="errorMessage"
                        class="mb-4 alert flex overflow-hidden rounded-lg bg-red-50 text-red-600 border border-red-200"
                    >
                        <div class="flex flex-1 items-center space-x-3 p-3">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
                            </svg>
                            <div class="flex-1">{{ errorMessage }}</div>
                        </div>
                    </div>

                    <!-- Search Input -->
                    <div class="flex flex-col sm:flex-row sm:space-x-2 space-y-2 sm:space-y-0">
                        <input
                            v-model="reference"
                            class="flex-1 w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                            placeholder="Enter HBL number"
                            type="text"
                            @keyup.enter="handleSubmit"
                        />
                        <button
                            :disabled="isLoading"
                            class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors w-full sm:w-auto"
                            @click="handleSubmit"
                        >
                            <svg v-if="isLoading" class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" fill="currentColor"></path>
                            </svg>
                            <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Results Section -->
        <div v-if="hblStatus.length > 0 && !isLoading" class="max-w-4xl mx-auto px-4 py-6">
            <!-- Tracking Result Header -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-t-lg px-6 py-5 shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-2xl font-bold mb-1">
                            Shipment Tracking
                        </h2>
                        <p class="text-blue-100 text-sm">HBL Number: {{ reference }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-blue-100 mb-1">Last Updated</p>
                        <p class="text-sm font-semibold">{{ hblStatus.length ? moment(hblStatus[hblStatus.length - 1]?.created_at).format('MMM DD, YYYY hh:mm A') : '-' }}</p>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="bg-white rounded-b-lg shadow-lg overflow-hidden">
                <!-- Basic Information Card -->
                <div class="p-6 bg-gradient-to-br from-blue-50 to-indigo-50 border-b-2 border-blue-200">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                        <i class="pi pi-info-circle mr-2 text-blue-600"></i>
                        Shipment Information
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <!-- Shipper Name -->
                        <div class="bg-white rounded-lg p-4 shadow-sm border border-blue-100 hover:shadow-md transition-shadow">
                            <div class="flex items-start">
                                <div class="flex-shrink-0 w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                                    <i class="pi pi-user text-blue-600"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Shipper Name</p>
                                    <p :title="hblDetails?.shipper_name || '-'" class="text-sm font-bold text-gray-900 truncate">
                                        {{ hblDetails?.shipper_name || '-' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Consignee Name -->
                        <div class="bg-white rounded-lg p-4 shadow-sm border border-blue-100 hover:shadow-md transition-shadow">
                            <div class="flex items-start">
                                <div class="flex-shrink-0 w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                                    <i class="pi pi-users text-green-600"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Consignee Name</p>
                                    <p :title="hblDetails?.consignee_name || '-'" class="text-sm font-bold text-gray-900 truncate">
                                        {{ hblDetails?.consignee_name || '-' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- No of Packages -->
                        <div class="bg-white rounded-lg p-4 shadow-sm border border-blue-100 hover:shadow-md transition-shadow">
                            <div class="flex items-start">
                                <div class="flex-shrink-0 w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                                    <i class="pi pi-box text-purple-600"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">No of Packages</p>
                                    <p class="text-sm font-bold text-gray-900">
                                        {{ hblDetails?.packages_count ?? '-' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Current Status -->
                        <div class="bg-white rounded-lg p-4 shadow-sm border border-green-200 hover:shadow-md transition-shadow">
                            <div class="flex items-start">
                                <div class="flex-shrink-0 w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                                    <i class="pi pi-check-circle text-green-600"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Current Status</p>
                                    <p class="text-sm font-bold text-green-700">
                                        {{ currentStatus }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- ETD -->
                        <div class="bg-white rounded-lg p-4 shadow-sm border border-blue-100 hover:shadow-md transition-shadow">
                            <div class="flex items-start">
                                <div class="flex-shrink-0 w-10 h-10 bg-amber-100 rounded-lg flex items-center justify-center mr-3">
                                    <i class="pi pi-calendar text-amber-600"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Estimated Departure (ETD)</p>
                                    <p class="text-sm font-bold text-gray-900">
                                        {{ containerDetails?.estimated_time_of_departure ? moment(containerDetails.estimated_time_of_departure).format('MMM DD, YYYY') : '-' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- ETA -->
                        <div class="bg-white rounded-lg p-4 shadow-sm border border-blue-100 hover:shadow-md transition-shadow">
                            <div class="flex items-start">
                                <div class="flex-shrink-0 w-10 h-10 bg-emerald-100 rounded-lg flex items-center justify-center mr-3">
                                    <i class="pi pi-calendar-plus text-emerald-600"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Estimated Arrival (ETA)</p>
                                    <p class="text-sm font-bold text-gray-900">
                                        {{ containerDetails?.estimated_time_of_arrival ? moment(containerDetails.estimated_time_of_arrival).format('MMM DD, YYYY') : '-' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Timeline Section -->
                <div class="p-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-6 flex items-center">
                        <i class="pi pi-history mr-2 text-blue-600"></i>
                        Shipment Timeline
                    </h3>

                    <!-- Custom Left-Aligned Timeline -->
                    <div class="custom-timeline relative">
                        <!-- Continuous Vertical Line -->
                        <div class="timeline-vertical-line"></div>

                        <div v-for="(log, index) in hblStatus.slice().reverse().filter(log => getUserFriendlyStatus(log.status))" :key="index" class="timeline-item relative">
                            <!-- Timeline Marker -->
                            <div class="timeline-marker">
                                <div class="relative">
                                    <span
                                        :class="{
                                            'bg-gradient-to-r from-green-500 to-green-600 animate-pulse': index === 0,
                                            'bg-gradient-to-r from-blue-500 to-blue-600': index !== 0
                                        }"
                                        class="flex w-12 h-12 items-center justify-center text-white rounded-full z-20 shadow-lg border-4 border-white transition-all duration-300 hover:scale-110 relative"
                                    >
                                        <i :class="getStatusIcon(log.status)" class="text-lg"></i>
                                    </span>
                                    <div v-if="index === 0"
                                         class="absolute -top-1 -right-1 w-4 h-4 bg-green-400 rounded-full animate-ping"></div>
                                </div>
                            </div>

                            <!-- Content -->
                            <div class="timeline-content">
                                <Card :class="{
                                          'border-l-green-500 bg-green-50': index === 0,
                                          'border-l-blue-500 bg-blue-50': index !== 0
                                      }"
                                      class="shadow-md hover:shadow-lg transition-all duration-300 border-l-4">
                                    <template #content>
                                        <div class="space-y-3">
                                            <!-- Status Header -->
                                            <div class="flex justify-between items-start">
                                                <div class="flex-1">
                                                    <h3 class="font-bold text-gray-900 text-lg mb-1">
                                                        {{ getUserFriendlyStatus(log.status) }}
                                                    </h3>
                                                    <p class="text-xs text-gray-500 uppercase tracking-wide font-medium">
                                                        {{ moment(log.created_at).format('MMM DD, YYYY • hh:mm A') }}
                                                    </p>
                                                </div>

                                                <Badge
                                                    :severity="index === 0 ? 'success' : 'info'"
                                                    :value="index === 0 ? 'Current' : 'Completed'"
                                                    class="ml-2 font-semibold"
                                                />
                                            </div>

                                            <!-- Description -->
                                            <div class="bg-white rounded-lg p-3 border border-gray-100">
                                                <p class="text-sm text-gray-700 leading-relaxed">
                                                    {{ getStatusDescription(log.status) }}
                                                </p>
                                            </div>

                                            <!-- Shipment export process (container) details within timeline -->
                                            <div v-if="getUserFriendlyStatus(log.status) === 'Shipment Export process' && containerDetails"
                                                 class="grid grid-cols-1 gap-3">
                                                <div class="flex items-center justify-between bg-white rounded-lg p-3 border border-green-100">
                                                    <div class="flex items-center">
                                                        <i class="pi pi-calendar text-amber-600 mr-2" />
                                                        <span class="text-sm font-medium text-gray-600">Schedule to Load</span>
                                                    </div>
                                                    <span class="text-sm font-bold text-gray-800">
                                                        {{ containerDetails.loading_started_at ? moment(containerDetails.loading_started_at).format('MMM DD, YYYY') : '-' }}
                                                    </span>
                                                </div>
                                                <div class="flex items-center justify-between bg-white rounded-lg p-3 border border-amber-100">
                                                    <div class="flex items-center">
                                                        <i class="pi pi-send text-amber-600 mr-2" />
                                                        <span class="text-sm font-medium text-gray-600">{{ containerDetails?.is_etd_past ? 'Shipment Departed' : 'Estimated Departure' }}</span>
                                                    </div>
                                                    <span class="text-sm font-bold text-gray-800">
                                                        {{ containerDetails?.estimated_time_of_departure ? moment(containerDetails.estimated_time_of_departure).format('MMM DD, YYYY') : '-' }}
                                                    </span>
                                                </div>
                                            </div>

                                            <!-- Shipment arrival process (container) details within timeline -->
                                            <div v-if="getUserFriendlyStatus(log.status) === 'Shipment Arrival process' && containerDetails"
                                                 class="grid grid-cols-1 gap-3">
                                                <!-- ETA to destination port -->
                                                <div class="flex items-center justify-between bg-white rounded-lg p-3 border border-emerald-100">
                                                    <div class="flex items-center">
                                                        <i class="pi pi-calendar text-emerald-600 mr-2" />
                                                        <span class="text-sm font-medium text-gray-600">ETA to Destination Port</span>
                                                    </div>
                                                    <span class="text-sm font-bold text-gray-800">
                                                        {{ containerDetails?.estimated_time_of_arrival ? moment(containerDetails.estimated_time_of_arrival).format('MMM DD, YYYY') : '-' }}
                                                    </span>
                                                </div>
                                                <!-- Shipment Arrived to destination port – date (only when ETA expired) -->
                                                <div v-if="containerDetails?.is_eta_past" class="flex items-center justify-between bg-white rounded-lg p-3 border border-emerald-100">
                                                    <div class="flex items-center">
                                                        <i class="pi pi-flag-fill text-emerald-600 mr-2" />
                                                        <span class="text-sm font-medium text-gray-600">Arrived at {{ containerDetails?.port_of_discharge || 'Destination Port' }}</span>
                                                    </div>
                                                    <span class="text-sm font-bold text-gray-800">
                                                        {{ containerDetails?.reached_date ? moment(containerDetails.reached_date).format('MMM DD, YYYY') : '-' }}
                                                    </span>
                                                </div>
                                            </div>

                                            <!-- Shipment pickup details within timeline -->
                                            <div v-if="getUserFriendlyStatus(log.status) === 'Shipment Pickup' && hblDetails"
                                                 class="grid grid-cols-1 gap-3">
                                                <div class="flex items-center justify-between bg-white rounded-lg p-3 border border-green-100">
                                                    <div class="flex items-center">
                                                        <i class="pi pi-calendar text-blue-500 mr-2" />
                                                        <span class="text-sm font-medium text-gray-600">Pickup Request Appointment</span>
                                                    </div>
                                                    <span class="text-sm font-bold text-gray-800">{{ hblDetails.booking_received_date ? moment(hblDetails.booking_received_date).format('MMM DD, YYYY • hh:mm A') : '-' }}</span>
                                                </div>
                                                <div v-if="hblDetails.booking_assign_to_driver_date" class="flex items-center justify-between bg-white rounded-lg p-3 border border-green-100">
                                                    <div class="flex items-center">
                                                        <i class="pi pi-user text-blue-500 mr-2" />
                                                        <span class="text-sm font-medium text-gray-600">Assigned to Driver for Pickup</span>
                                                    </div>
                                                    <span class="text-sm font-bold text-gray-800">{{ hblDetails.booking_assign_to_driver_date ? moment(hblDetails.booking_assign_to_driver_date).format('MMM DD, YYYY • hh:mm A') : '-' }}</span>
                                                </div>
                                                <div class="flex items-center justify-between bg-white rounded-lg p-3 border border-green-100">
                                                    <div class="flex items-center">
                                                        <i class="pi pi-check text-blue-500 mr-2" />
                                                        <span class="text-sm font-medium text-gray-600">Shipment Collected</span>
                                                    </div>
                                                    <span class="text-sm font-bold text-gray-800">{{ hblDetails.cargo_received_date ? moment(hblDetails.cargo_received_date).format('MMM DD, YYYY • hh:mm A') : '-' }}</span>
                                                </div>
                                            </div>

                                            <!-- Shipment Loading details -->
                                            <div v-if="getUserFriendlyStatus(log.status) === 'Shipment Export process' && hblDetails"
                                                 class="grid grid-cols-1 gap-3 mt-3">
                                                <div v-if="hblDetails.loading_ended_at" class="flex items-center justify-between bg-white rounded-lg p-3 border border-amber-100">
                                                    <div class="flex items-center">
                                                        <i class="pi pi-box text-amber-600 mr-2" />
                                                        <span class="text-sm font-medium text-gray-600">Loaded to Shipment</span>
                                                    </div>
                                                    <span class="text-sm font-bold text-gray-800">
                                                        {{ moment(hblDetails.loading_ended_at).format('MMM DD, YYYY • hh:mm A') }}
                                                    </span>
                                                </div>
                                            </div>

                                            <!-- Shipment Arrival details -->
                                            <div v-if="getUserFriendlyStatus(log.status) === 'Shipment Arrival process' && hblDetails"
                                                 class="grid grid-cols-1 gap-3 mt-3">
                                                <div v-if="hblDetails.reached_date" class="flex items-center justify-between bg-white rounded-lg p-3 border border-emerald-100">
                                                    <div class="flex items-center">
                                                        <i class="pi pi-flag-fill text-emerald-600 mr-2" />
                                                        <span class="text-sm font-medium text-gray-600">Reached Destination Port</span>
                                                    </div>
                                                    <span class="text-sm font-bold text-gray-800">
                                                        {{ moment(hblDetails.reached_date).format('MMM DD, YYYY • hh:mm A') }}
                                                    </span>
                                                </div>
                                                <div v-if="hblDetails.arrived_at_primary_warehouse" class="flex items-center justify-between bg-white rounded-lg p-3 border border-emerald-100">
                                                    <div class="flex items-center">
                                                        <i class="pi pi-home text-emerald-600 mr-2" />
                                                        <span class="text-sm font-medium text-gray-600">Reached Destination Warehouse</span>
                                                    </div>
                                                    <span class="text-sm font-bold text-gray-800">
                                                        {{ moment(hblDetails.arrived_at_primary_warehouse).format('MMM DD, YYYY • hh:mm A') }}
                                                    </span>
                                                </div>
                                            </div>

                                            <!-- Shipment Unloaded details -->
                                            <div v-if="getUserFriendlyStatus(log.status) === 'Shipment' && hblDetails"
                                                 class="grid grid-cols-1 gap-3">
                                                <div v-if="hblDetails.unloading_ended_at" class="flex items-center justify-between bg-white rounded-lg p-3 border border-blue-100">
                                                    <div class="flex items-center">
                                                        <i class="pi pi-download text-blue-600 mr-2" />
                                                        <span class="text-sm font-medium text-gray-600">Shipment Unloaded</span>
                                                    </div>
                                                    <span class="text-sm font-bold text-gray-800">
                                                        {{ moment(hblDetails.unloading_ended_at).format('MMM DD, YYYY • hh:mm A') }}
                                                    </span>
                                                </div>
                                                <div class="bg-blue-50 rounded-lg p-3 border border-blue-200">
                                                    <div class="flex items-start">
                                                        <i class="pi pi-info-circle text-blue-600 mr-2 mt-0.5"></i>
                                                        <div>
                                                            <p class="text-sm font-semibold text-blue-900 mb-1">Ready for Collection</p>
                                                            <p class="text-xs text-blue-700">Please book an appointment for cargo collection at your convenience.</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Door to Door specific milestones -->
                                            <div v-if="hblDetails?.hbl_type === 'door-to-door' && getUserFriendlyStatus(log.status) === 'Delivered'"
                                                 class="grid grid-cols-1 gap-3">
                                                <div v-if="hblDetails.is_released" class="flex items-center justify-between bg-white rounded-lg p-3 border border-green-100">
                                                    <div class="flex items-center">
                                                        <i class="pi pi-file-check text-green-600 mr-2" />
                                                        <span class="text-sm font-medium text-gray-600">Cleared from Customs</span>
                                                    </div>
                                                    <span class="text-sm font-bold text-gray-800">
                                                        <i class="pi pi-check-circle text-green-600"></i> Cleared
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </Card>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Loading State -->
        <div v-if="isLoading" class="max-w-3xl mx-auto px-4 py-6">
            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="animate-pulse">
                    <div class="h-6 bg-gray-200 rounded mb-4 w-1/3"></div>
                    <div class="space-y-4">
                        <div class="h-4 bg-gray-200 rounded w-full"></div>
                        <div class="h-4 bg-gray-200 rounded w-3/4"></div>
                        <div class="h-4 bg-gray-200 rounded w-1/2"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Welcome State -->
        <div v-if="hblStatus.length === 0 && !isLoading" class="max-w-4xl mx-auto px-4 py-12">
            <div class="text-center">
                <img :src="DashboardMeet" alt="Track your shipment" class="w-64 mx-auto mb-8 dark:hidden" />
                <img :src="DashboardMeetDark" alt="Track your shipment" class="w-64 mx-auto mb-8 hidden dark:block" />
                <h2 class="text-2xl font-bold text-gray-900 mb-4">Welcome to Laksiri Cargo Tracking</h2>
                <p class="text-gray-600 mb-8">Enter your HBL number above to get real-time updates on your shipment.</p>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-3xl mx-auto">
                    <div class="bg-white rounded-lg shadow-sm p-6 border">
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-4 mx-auto">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
                            </svg>
                        </div>
                        <h3 class="font-semibold text-gray-900 mb-2">Real-time Tracking</h3>
                        <p class="text-sm text-gray-600">Get live updates on your shipment location and status.</p>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm p-6 border">
                        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mb-4 mx-auto">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
                            </svg>
                        </div>
                        <h3 class="font-semibold text-gray-900 mb-2">Interactive Map</h3>
                        <p class="text-sm text-gray-600">View your package route on an interactive map.</p>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm p-6 border">
                        <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mb-4 mx-auto">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
                            </svg>
                        </div>
                        <h3 class="font-semibold text-gray-900 mb-2">Delivery Timeline</h3>
                        <p class="text-sm text-gray-600">See detailed timeline of your package journey.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Timeline Enhancements */
.customized-timeline :deep(.p-timeline-event-marker) {
    border: 3px solid #ffffff;
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
    transition: all 0.3s ease;
}

.customized-timeline :deep(.p-timeline-event-connector) {
    background: linear-gradient(to bottom, #3b82f6, #10b981);
    width: 3px;
    border-radius: 2px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

/* Custom Timeline Styles */
.custom-timeline {
    position: relative;
    padding-left: 0;
}

.timeline-vertical-line {
    position: absolute;
    left: 24px;
    top: 50px;
    bottom: 50px;
    width: 3px;
    background: linear-gradient(to bottom, #3b82f6, #10b981);
    border-radius: 2px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    z-index: 1;
}

.timeline-item {
    display: flex;
    align-items: flex-start;
    margin-bottom: 2rem;
    position: relative;
}

.timeline-marker {
    position: relative;
    z-index: 20;
    margin-right: 1.5rem;
    flex-shrink: 0;
}

.timeline-content {
    flex: 1;
    margin-top: -0.5rem;
}

/* Enhanced Card Styling */
.p-card {
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    transition: all 0.3s ease;
    backdrop-filter: blur(10px);
}

.p-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}

.p-card .p-card-content {
    padding: 1.25rem;
}

/* Badge Enhancements */
.p-badge {
    font-size: 0.75rem;
    padding: 0.375rem 0.75rem;
    border-radius: 20px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.p-badge.p-badge-success {
    background: linear-gradient(135deg, #10b981, #059669);
    animation: pulse-success 2s infinite;
}

.p-badge.p-badge-info {
    background: linear-gradient(135deg, #3b82f6, #2563eb);
}

/* Pulse Animation for Current Status */
@keyframes pulse-success {
    0%, 100% {
        box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
    }
    50% {
        box-shadow: 0 0 0 10px rgba(16, 185, 129, 0);
    }
}

/* Responsive Improvements */
@media (max-width: 768px) {
    .timeline-vertical-line {
        left: 18px;
        top: 30px;
        bottom: 30px;
    }

    .timeline-marker {
        margin-right: 1rem;
    }

    .timeline-marker .rounded-full {
        width: 2.5rem !important;
        height: 2.5rem !important;
    }

    .p-card .p-card-content {
        padding: 1rem;
    }
}

/* Loading States */
.skeleton {
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: loading 1.5s infinite;
}

@keyframes loading {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}

/* Interactive Elements */
.interactive-card {
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.interactive-card:hover {
    transform: scale(1.02);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
}

/* Status Specific Styling */
.status-current {
    position: relative;
    overflow: hidden;
}

.status-current::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
    animation: shimmer 2s infinite;
}

@keyframes shimmer {
    0% { left: -100%; }
    100% { left: 100%; }
}
</style>
