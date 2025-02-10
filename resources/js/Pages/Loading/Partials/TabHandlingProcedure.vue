<script setup>
import Tab from "@/Components/Tab.vue";
import { ref, watch } from 'vue';
import { Circle, CheckCircle2 } from 'lucide-vue-next';

const props = defineProps({
    container: {
        type: Object,
        default: () => ({}),
    }
});


const seaShipmentSteps = [
    { id: 1, text: "Receive Notification from agent", checked: false },
    { id: 2, text: "Receive Arrival Notice from Shipping company before shipment reach to colombo Port", checked: false },
    { id: 3, text: "Receive Original BL from Agent (Seaway Bill, Telex Release, Surrender BL – Original BL Mode)", checked: false },
    { id: 4, text: "Collect Delivery Order From Shipping Company", checked: false },
    { id: 5, text: "Prepare Import Entry – Customs Portal", checked: false },
    { id: 6, text: "Customs Clearance at Port", checked: false },
    { id: 7, text: "Transport Arrangement from Port to Colombo Warehouse", checked: false },
    { id: 8, text: "Receive Container at Colombo Warehouse", checked: false },
    { id: 9, text: "Receive Customs Documents at Warehouse (After various Approvals)", checked: false },
    { id: 10, text: "Get Container Unloading Approval from customs", checked: false },
    { id: 11, text: "Prepare Unloading related File (Manifest, Tally sheet, Location Sheet, Letter Registration)", checked: false },
    { id: 12, text: "Get customs Approval after Unloaded the Container (From this point we can inform customer)", checked: false },
    { id: 13, text: "Return the Empty Container to the Shipping Company Yard", checked: false },
];

const airShipmentSteps = [
    { id: 1, text: "Receive Notification from agent", checked: false },
    { id: 5, text: "Prepare Import Entry – Customs Portal", checked: false },
    { id: 6, text: "Combined Customs Clearance and Transport at Airport", checked: false },
    { id: 8, text: "Receive Cargo at Colombo Warehouse", checked: false },
    { id: 9, text: "Receive Customs Documents at Warehouse (After various Approvals)", checked: false },
    { id: 10, text: "Get Cargo Unloading Approval from customs", checked: false },
    { id: 11, text: "Prepare Unloading related File (Manifest, Tally sheet, Location Sheet, Letter Registration)", checked: false },
    { id: 12, text: "Get customs Approval after Unloaded the Cargo (From this point we can inform customer)", checked: false },
];

const checklist = ref(props.container?.cargo_type === 'Air Cargo' ? airShipmentSteps : seaShipmentSteps);

const toggleCheck = (step) => {
    step.checked = !step.checked;
    // Here you can add API call to save the status
};

const getProgress = () => {
    const totalSteps = checklist.value.length;
    const completedSteps = checklist.value.filter(step => step.checked).length;
    return Math.round((completedSteps / totalSteps) * 100);
};

watch(() => props.container?.cargo_type, (newType) => {
    checklist.value = newType === 'Air Cargo' ? airShipmentSteps : seaShipmentSteps;
});
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

            <!-- Updated Checklist -->
            <div class="space-y-3">
                <div v-for="step in checklist"
                     :key="step.id"
                     class="flex items-start gap-3 p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                    <button
                        @click="toggleCheck(step)"
                        class="flex-shrink-0 mt-0.5 relative group"
                    >
                        <Circle 
                            v-if="!step.checked"
                            class="w-5 h-5 text-gray-400 group-hover:text-gray-500 transition-colors"
                        />
                        <CheckCircle2
                            v-else
                            class="w-5 h-5 text-emerald-500 animate-scale-check"
                        />
                    </button>
                    <span
                        class="text-sm text-gray-700 dark:text-gray-200 transition-all duration-200"
                        :class="{
                            'text-emerald-600 dark:text-emerald-400': step.checked,
                            'line-through opacity-50': step.checked
                        }"
                    >
                        {{ step.text }}
                    </span>
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
