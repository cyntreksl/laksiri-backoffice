<script setup>
import Tab from "@/Components/Tab.vue";
import { ref, watch, onMounted } from 'vue';
import { Circle, CheckCircle2 } from 'lucide-vue-next';
import axios from 'axios';
import { usePage } from '@inertiajs/vue3';

const props = defineProps({
    container: {
        type: Object,
        required: true,
    }
});

// Base steps that are always shown
const seaShipmentSteps = [
    { id: 1, text: "Receive Notification from agent" },
    { id: 2, text: "Receive Arrival Notice from Shipping company before shipment reach to colombo Port" },
    { id: 3, text: "Receive Original BL from Agent (Seaway Bill, Telex Release, Surrender BL – Original BL Mode)" },
    { id: 4, text: "Collect Delivery Order From Shipping Company" },
    { id: 5, text: "Prepare Import Entry – Customs Portal" },
    { id: 6, text: "Customs Clearance at Port" },
    { id: 7, text: "Transport Arrangement from Port to Colombo Warehouse" },
    { id: 8, text: "Receive Container at Colombo Warehouse" },
    { id: 9, text: "Receive Customs Documents at Warehouse (After various Approvals)" },
    { id: 10, text: "Get Container Unloading Approval from customs" },
    { id: 11, text: "Prepare Unloading related File (Manifest, Tally sheet, Location Sheet, Letter Registration)" },
    { id: 12, text: "Get customs Approval after Unloaded the Container (From this point we can inform customer)" },
    { id: 13, text: "Return the Empty Container to the Shipping Company Yard" },
];

const airShipmentSteps = [
    { id: 1, text: "Receive Notification from agent" },
    { id: 5, text: "Prepare Import Entry – Customs Portal" },
    { id: 6, text: "Combined Customs Clearance and Transport at Airport" },
    { id: 8, text: "Receive Cargo at Colombo Warehouse" },
    { id: 9, text: "Receive Customs Documents at Warehouse (After various Approvals)" },
    { id: 10, text: "Get Cargo Unloading Approval from customs" },
    { id: 11, text: "Prepare Unloading related File (Manifest, Tally sheet, Location Sheet, Letter Registration)" },
    { id: 12, text: "Get customs Approval after Unloaded the Cargo (From this point we can inform customer)" },
];

const checklist = ref([]);
const loading = ref(false);
const currentUser = usePage().props.auth.user;

const loadHandlingProcedures = async () => {
    try {
        loading.value = true;
        const response = await axios.get(`/containers/${props.container.id}/handling-procedures`);
        const procedures = response.data;

        // Get the appropriate base steps based on cargo type
        const baseSteps = props.container?.cargo_type === 'Air Cargo' ? airShipmentSteps : seaShipmentSteps;

        // Map the base steps and merge with saved procedures
        checklist.value = baseSteps.map(step => {
            const savedProcedure = procedures.find(p => p.step_id === step.id);
            return {
                ...step,
                checked: savedProcedure?.is_completed || false,
                completed_by: savedProcedure?.completed_by || null,
                completed_at: savedProcedure?.completed_at || null
            };
        });
    } catch (error) {
        console.error('Error loading procedures:', error);
    } finally {
        loading.value = false;
    }
};

// Initialize checklist when cargo type changes
watch(() => props.container?.cargo_type, (newType) => {
    checklist.value = (newType === 'Air Cargo' ? airShipmentSteps : seaShipmentSteps).map(step => ({
        ...step,
        checked: false,
        completed_by: null,
        completed_at: null
    }));
    if (props.container?.id) {
        loadHandlingProcedures();
    }
}, { immediate: true });

const toggleCheck = async (step) => {
    if (loading.value) return;

    try {
        loading.value = true;
        const response = await axios.post(`/containers/${props.container.id}/handling-procedures`, {
            step_id: step.id,
            is_completed: !step.checked
        });

        // Update the step with the response data
        const updatedProcedure = response.data;
        step.checked = updatedProcedure.is_completed;
        step.completed_by = updatedProcedure.completed_by;
        step.completed_at = updatedProcedure.completed_at;
    } catch (error) {
        console.error('Error updating procedure:', error);
    } finally {
        loading.value = false;
    }
};

const formatDate = (date) => {
    if (!date) return '';
    return new Date(date).toLocaleString();
};

const getProgress = () => {
    const totalSteps = checklist.value.length;
    const completedSteps = checklist.value.filter(step => step.checked).length;
    return Math.round((completedSteps / totalSteps) * 100);
};
</script>

<template>
    <Tab label="Handling Procedure" name="tabHandlingProcedure">
        <div class="p-4">
            <!-- Progress Bar -->
            <div class="mb-6">
                <div class="flex justify-between mb-1">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-200">Progress</span>
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ getProgress() }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                    <div class="bg-blue-600 h-2.5 rounded-full" :style="{ width: `${getProgress()}%` }"></div>
                </div>
            </div>

            <!-- Shipment Type Header -->
            <div class="mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                    {{ props.container?.cargo_type === 'Air Cargo' ? 'Air Shipment' : 'Sea Shipment' }} Handling Procedure
                </h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Handling Procedure from port to our warehouse
                </p>
            </div>

            <!-- Updated Checklist with User and Timestamp -->
            <div class="space-y-3">
                <div v-for="step in checklist"
                     :key="step.id"
                     @click="toggleCheck(step)"
                     class="flex items-start gap-3 p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors cursor-pointer"
                >
                    <div class="flex-shrink-0 mt-0.5 relative group">
                        <Circle
                            v-if="!step.checked"
                            class="w-5 h-5 text-gray-400 group-hover:text-gray-500 transition-colors"
                        />
                        <CheckCircle2
                            v-else
                            class="w-5 h-5 text-emerald-500 animate-scale-check"
                        />
                    </div>
                    <div class="flex flex-col flex-grow">
                        <span
                            class="text-sm text-gray-700 dark:text-gray-200 transition-all duration-200"
                            :class="{
                                'text-emerald-600 dark:text-emerald-400': step.checked,
                                'line-through opacity-50': step.checked
                            }"
                        >
                            {{ step.text }}
                        </span>
                        <span v-if="step.checked" class="text-xs text-gray-500 mt-1">
                            Completed by {{ step.completed_by?.name }} at {{ formatDate(step.completed_at) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </Tab>
</template>

<style scoped>
@keyframes scale-check {
    0% {
        transform: scale(0.8);
        opacity: 0;
    }
    50% {
        transform: scale(1.1);
    }
    100% {
        transform: scale(1);
        opacity: 1;
    }
}

.animate-scale-check {
    animation: scale-check 0.2s ease-out forwards;
}
</style>
