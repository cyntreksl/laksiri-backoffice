<script setup xmlns="http://www.w3.org/1999/html">
import {onMounted, ref} from "vue";
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

const hblStatusColor = (status) => {
    switch (status) {
        case 'HBL Preparation by warehouse':
        case 'HBL Preparation by driver':
            return 'bg-primary';
        case 'Cash Received by Accountant':
            return 'bg-secondary';
        case 'Container Loading':
            return 'bg-success';
        case 'Container Shipped':
            return 'bg-error';
        case 'Container Arrival':
            return 'bg-slate-500';
        case 'Blocked By RTF':
            return 'bg-red-500';
        case 'Revert To Cash Settlement':
            return 'bg-amber-400';
        case 'Container Unloaded in Colombo':
            return 'bg-gray-400';
        case 'Container Loading in Colombo':
            return 'bg-success';
        case 'Container Unloaded in Nintavur':
            return 'bg-red-600';
        case 'Container In Transit':
            return 'bg-cyan-600';
        case 'Container Reached Destination ':
            return 'bg-emerald-600';
        default:
            return 'bg-gray-400';
    }
};

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

const getStatusColor = (status, index, total) => {
    if (index === total - 1) {
        return 'success'; // Latest status
    }

    switch (status.toLowerCase()) {
        case 'delivered':
        case 'container reached destination':
            return 'success';
        case 'blocked by rtf':
            return 'danger';
        case 'revert to cash settlement':
            return 'warning';
        case 'container in transit':
        case 'out for delivery':
            return 'info';
        default:
            return 'primary';
    }
};

const getUserFriendlyStatus = (status) => {
    const statusMap = {
        'HBL Preparation by warehouse': 'Shipment Picked Up',
        'HBL Preparation by driver': 'Shipment Picked Up',
        'Cash Received by Accountant': 'Arrived',
        'Container Loading': 'Under Process',
        'Container Shipped': 'Departed',
        'Container Arrival': 'Arrived',
        'Blocked By RTF': 'Shipment held for inspection',
        'Container Unloaded in Colombo': 'Transferred',
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
        'Container Unloaded in Colombo': 'Your shipment has been transferred to the next stage of delivery.',
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
        'Container Loading': '4-6 hours',
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
            <div class="max-w-7xl mx-auto px-4 py-6">
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
                    <div class="flex space-x-2">
                        <input
                            v-model="reference"
                            class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                            placeholder="Enter HBL number"
                            type="text"
                            @keyup.enter="handleSubmit"
                        />
                        <button
                            :disabled="isLoading"
                            class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors"
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
        <div v-if="hblStatus.length > 0 && !isLoading" class="max-w-7xl mx-auto px-4 py-6">
            <!-- Tracking Result Header -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-t-lg px-6 py-4">
                <h2 class="text-lg font-semibold">
                    Tracking Result | HBL Number: {{ reference }}
                </h2>
            </div>

            <!-- Main Content Grid -->
            <div class="bg-white rounded-b-lg shadow-lg overflow-hidden">
                <div class="grid grid-cols-1 min-h-[600px]">
                    <!-- Timeline Section -->
                    <div class="p-6 border-r border-gray-200">
                        <!-- Progress Overview -->
                        <div class="mb-6 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg p-4 border border-blue-100">
                            <div class="flex items-center justify-between mb-3">
                                <h3 class="font-semibold text-gray-800 flex items-center">
                                    <i class="pi pi-truck mr-2 text-blue-600"></i>
                                    Shipment Progress
                                </h3>
                                <span class="text-sm text-blue-600 font-medium">
                                    {{ hblStatus.length }} of {{ hblStatus.length + 1 }} steps completed
                                </span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2 mb-2">
                                <div :style="{ width: ((hblStatus.length / (hblStatus.length + 1)) * 100) + '%' }"
                                     class="bg-gradient-to-r from-blue-500 to-green-500 h-2 rounded-full transition-all duration-500"></div>
                            </div>
                        </div>

                        <div class="timeline-container">
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
                                                class="flex w-10 h-10 items-center justify-center text-white rounded-full z-20 shadow-lg border-3 border-white transition-all duration-300 hover:scale-110 relative"
                                            >
                                                <i :class="getStatusIcon(log.status)" class="text-sm"></i>
                                            </span>
                                            <div v-if="index === 0"
                                                 class="absolute -top-1 -right-1 w-3 h-3 bg-green-400 rounded-full animate-ping"></div>
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
                                                            <h3 class="font-bold text-gray-900 text-base mb-1">
                                                                {{ getUserFriendlyStatus(log.status) }}
                                                            </h3>
                                                            <p class="text-xs text-gray-500 uppercase tracking-wide font-medium">
                                                                {{ log.status }}
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

                                                    <!-- Date and Time Info -->
                                                    <div class="grid grid-cols-2 gap-3">
                                                        <div class="flex items-center space-x-2 bg-white rounded-lg p-2 border border-gray-100">
                                                            <i class="pi pi-calendar text-blue-500"></i>
                                                            <div>
                                                                <p class="text-xs text-gray-500 font-medium">Date</p>
                                                                <p class="text-sm font-semibold text-gray-800">
                                                                    {{ moment(log.created_at).format('MMM DD, YYYY') }}
                                                                </p>
                                                            </div>
                                                        </div>
                                                        <div class="flex items-center space-x-2 bg-white rounded-lg p-2 border border-gray-100">
                                                            <i class="pi pi-clock text-green-500"></i>
                                                            <div>
                                                                <p class="text-xs text-gray-500 font-medium">Time</p>
                                                                <p class="text-sm font-semibold text-gray-800">
                                                                    {{ moment(log.created_at).format('hh:mm A') }}
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Estimated Time (if available) -->
                                                    <div v-if="getEstimatedTime(log.status, hblStatus.length - 1 - index, hblStatus.length)"
                                                         class="flex items-center space-x-2 bg-amber-50 rounded-lg p-2 border border-amber-200">
                                                        <i class="pi pi-hourglass text-amber-600"></i>
                                                        <div>
                                                            <p class="text-xs text-amber-600 font-medium">Estimated Duration</p>
                                                            <p class="text-sm font-semibold text-amber-800">
                                                                {{ getEstimatedTime(log.status, hblStatus.length - 1 - index, hblStatus.length) }}
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </template>
                                        </Card>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="mt-8 space-y-4">

                                <!-- Enhanced Shipment Details Card -->
                                <Card class="bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-200">
                                    <template #content>
                                        <div class="space-y-4">
                                            <div class="text-center">
                                                <div class="inline-flex items-center justify-center w-12 h-12 bg-green-100 rounded-full mb-3">
                                                    <i class="pi pi-check-circle text-green-600 text-xl"></i>
                                                </div>
                                                <h4 class="font-bold text-gray-800 text-lg">Shipment Active</h4>
                                                <p class="text-sm text-gray-600">Your package is being tracked</p>
                                            </div>

                                            <div class="grid grid-cols-1 gap-3">
                                                <div class="bg-white rounded-lg p-3 border border-blue-100">
                                                    <div class="flex items-center justify-between">
                                                        <span class="text-sm font-medium text-gray-600">HBL Number</span>
                                                        <span class="text-sm font-bold text-blue-600">{{ reference }}</span>
                                                    </div>
                                                </div>
                                                <div class="bg-white rounded-lg p-3 border border-blue-100">
                                                    <div class="flex items-center justify-between">
                                                        <span class="text-sm font-medium text-gray-600">Last Updated</span>
                                                        <span class="text-sm font-bold text-gray-800">
                                                            {{ moment(hblStatus[hblStatus.length - 1]?.created_at).fromNow() }}
                                                        </span>
                                                    </div>
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
        <div v-if="isLoading" class="max-w-7xl mx-auto px-4 py-6">
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
    left: 20px;
    top: 40px;
    bottom: 40px;
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
    margin-right: 1rem;
    flex-shrink: 0;
}

.timeline-content {
    flex: 1;
    margin-top: -0.5rem;
}

.timeline-container {
    max-height: 600px;
    overflow-y: auto;
    padding-right: 12px;
    padding-top: 12px;
    scroll-behavior: smooth;
}

.timeline-container::-webkit-scrollbar {
    width: 6px;
}

.timeline-container::-webkit-scrollbar-track {
    background: #f8fafc;
    border-radius: 3px;
}

.timeline-container::-webkit-scrollbar-thumb {
    background: linear-gradient(to bottom, #cbd5e1, #94a3b8);
    border-radius: 3px;
    transition: background 0.3s ease;
}

.timeline-container::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(to bottom, #94a3b8, #64748b);
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

/* Button Enhancements */
.p-button {
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.3s ease;
    text-transform: none;
    letter-spacing: 0.25px;
}

.p-button:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.p-button.p-button-sm {
    padding: 0.625rem 1.25rem;
    font-size: 0.875rem;
}

.p-button.p-button-outlined {
    border-width: 2px;
    background: rgba(255, 255, 255, 0.8);
    backdrop-filter: blur(10px);
}

/* Progress Bar Animation */
@keyframes progress-fill {
    0% { width: 0%; }
    100% { width: var(--progress-width); }
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

/* Gradient Backgrounds */
.gradient-blue {
    background: linear-gradient(135deg, #dbeafe, #bfdbfe);
}

.gradient-green {
    background: linear-gradient(135deg, #dcfce7, #bbf7d0);
}

.gradient-amber {
    background: linear-gradient(135deg, #fef3c7, #fde68a);
}

/* Enhanced Typography */
.timeline-title {
    background: linear-gradient(135deg, #1f2937, #374151);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

/* Responsive Improvements */
@media (max-width: 768px) {
    .timeline-container {
        max-height: 400px;
        padding-right: 8px;
    }

    .p-card .p-card-content {
        padding: 1rem;
    }

    .customized-timeline :deep(.p-timeline-event-marker) {
        width: 2rem;
        height: 2rem;
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
