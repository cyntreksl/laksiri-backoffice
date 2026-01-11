<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import Timeline from 'primevue/timeline';
import Card from 'primevue/card';
import Tag from 'primevue/tag';
import { push } from 'notivue';

const props = defineProps({
    container: {
        type: Object,
        required: true,
    },
});

const detainHistory = ref([]);
const loading = ref(false);

const fetchDetainHistory = async () => {
    if (!props.container?.id) return;

    loading.value = true;
    try {
        const response = await axios.get(route('loading.containers.detain-history', props.container.id));
        if (response.data.success) {
            detainHistory.value = response.data.data;
        }
    } catch (error) {
        console.error('Error fetching detain history:', error);
        push.error('Failed to load detain history');
    } finally {
        loading.value = false;
    }
};

const getActionColor = (action) => {
    return action === 'detain' ? 'danger' : 'success';
};

const getActionIcon = (action) => {
    return action === 'detain' ? 'pi pi-lock' : 'pi pi-lock-open';
};

const getEntityIcon = (entityType) => {
    switch (entityType) {
        case 'Shipment':
            return 'ti ti-container';
        case 'HBL':
            return 'ti ti-file-text';
        case 'Package':
            return 'ti ti-package';
        default:
            return 'ti ti-circle';
    }
};

const getEntityColor = (entityType) => {
    switch (entityType) {
        case 'Shipment':
            return 'primary';
        case 'HBL':
            return 'info';
        case 'Package':
            return 'secondary';
        default:
            return 'contrast';
    }
};

const formatDate = (date) => {
    if (!date) return 'N/A';
    return new Date(date).toLocaleString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const hasRecords = computed(() => detainHistory.value.length > 0);

onMounted(() => {
    fetchDetainHistory();
});
</script>

<template>
    <div class="detain-history-container">
        <div v-if="loading" class="flex justify-center items-center py-8">
            <i class="pi pi-spin pi-spinner text-4xl text-primary"></i>
        </div>

        <div v-else-if="!hasRecords" class="text-center py-8">
            <i class="ti ti-info-circle text-6xl text-gray-400 mb-4"></i>
            <p class="text-gray-500 text-lg">No detain history available for this shipment</p>
        </div>

        <Timeline v-else :value="detainHistory" class="customized-timeline">
            <template #marker="slotProps">
                <span
                    :class="{
                        'bg-red-500': slotProps.item.action === 'detain',
                        'bg-green-500': slotProps.item.action === 'lift'
                    }"
                    class="flex w-8 h-8 items-center justify-center text-white rounded-full z-10 shadow-sm"
                >
                    <i :class="getActionIcon(slotProps.item.action)"></i>
                </span>
            </template>

            <template #content="slotProps">
                <Card class="mt-3 shadow-sm">
                    <template #title>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <Tag
                                    :severity="getActionColor(slotProps.item.action)"
                                    :value="slotProps.item.action === 'detain' ? 'DETAINED' : 'LIFTED'"
                                    class="text-xs font-semibold"
                                />
                                <Tag
                                    :severity="getEntityColor(slotProps.item.entity_type)"
                                    class="text-xs"
                                >
                                    <i :class="getEntityIcon(slotProps.item.entity_type)" class="mr-1"></i>
                                    {{ slotProps.item.entity_type }}
                                </Tag>
                            </div>
                            <span class="text-sm text-gray-500 font-normal">
                                {{ formatDate(slotProps.item.created_at) }}
                            </span>
                        </div>
                    </template>

                    <template #content>
                        <div class="space-y-3">
                            <!-- Entity Reference -->
                            <div class="flex items-start">
                                <span class="font-semibold text-gray-700 min-w-[140px]">Reference:</span>
                                <span class="text-gray-900">{{ slotProps.item.entity_reference }}</span>
                            </div>

                            <!-- Detain Type -->
                            <div v-if="slotProps.item.detain_type" class="flex items-start">
                                <span class="font-semibold text-gray-700 min-w-[140px]">Detain Type:</span>
                                <Tag :value="slotProps.item.detain_type" class="text-xs" severity="warn" />
                            </div>

                            <!-- Detain Reason -->
                            <div v-if="slotProps.item.detain_reason" class="flex items-start">
                                <span class="font-semibold text-gray-700 min-w-[140px]">Detain Reason:</span>
                                <span class="text-gray-900">{{ slotProps.item.detain_reason }}</span>
                            </div>

                            <!-- Lift Reason -->
                            <div v-if="slotProps.item.lift_reason" class="flex items-start">
                                <span class="font-semibold text-gray-700 min-w-[140px]">Lift Reason:</span>
                                <span class="text-gray-900">{{ slotProps.item.lift_reason }}</span>
                            </div>

                            <!-- Remarks -->
                            <div v-if="slotProps.item.remarks" class="flex items-start">
                                <span class="font-semibold text-gray-700 min-w-[140px]">Remarks:</span>
                                <span class="text-gray-900 italic">{{ slotProps.item.remarks }}</span>
                            </div>

                            <!-- Created By -->
                            <div v-if="slotProps.item.created_by" class="flex items-start">
                                <span class="font-semibold text-gray-700 min-w-[140px]">
                                    {{ slotProps.item.action === 'detain' ? 'Detained By:' : 'Lifted By:' }}
                                </span>
                                <span class="text-gray-900">
                                    {{ slotProps.item.action === 'detain'
                                        ? slotProps.item.created_by.name
                                        : (slotProps.item.lifted_by?.name || 'N/A')
                                    }}
                                </span>
                            </div>

                            <!-- Entity Level Badge -->
                            <div class="flex items-start">
                                <span class="font-semibold text-gray-700 min-w-[140px]">Level:</span>
                                <Tag
                                    :value="slotProps.item.entity_level || 'N/A'"
                                    class="text-xs uppercase"
                                    severity="secondary"
                                />
                            </div>
                        </div>
                    </template>
                </Card>
            </template>
        </Timeline>
    </div>
</template>

<style scoped>
.detain-history-container {
    padding: 1rem;
    max-height: 600px;
    overflow-y: auto;
}

.customized-timeline :deep(.p-timeline-event-content) {
    line-height: 1;
}

.customized-timeline :deep(.p-timeline-event-opposite) {
    flex: 0;
}

.customized-timeline :deep(.p-card) {
    margin-top: 0;
}

.customized-timeline :deep(.p-card-body) {
    padding: 1rem;
}

.customized-timeline :deep(.p-card-title) {
    font-size: 1rem;
    margin-bottom: 0.5rem;
}

.customized-timeline :deep(.p-card-content) {
    padding-top: 0;
}
</style>
