<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import Timeline from 'primevue/timeline';
import Card from 'primevue/card';
import Tag from 'primevue/tag';
import Divider from 'primevue/divider';
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

const getDetainTypeSeverity = (type) => {
    const severities = {
        'RTF': 'danger',
        'DDC': 'warn',
        'SDDC': 'warn',
        'IAU': 'warn',
        'DC': 'info',
        'CO': 'secondary',
        'ICT': 'contrast'
    };
    return severities[type] || 'secondary';
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

const getRelativeTime = (date) => {
    if (!date) return '';
    const now = new Date();
    const past = new Date(date);
    const diffMs = now - past;
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMs / 3600000);
    const diffDays = Math.floor(diffMs / 86400000);

    if (diffMins < 1) return 'Just now';
    if (diffMins < 60) return `${diffMins} min${diffMins > 1 ? 's' : ''} ago`;
    if (diffHours < 24) return `${diffHours} hour${diffHours > 1 ? 's' : ''} ago`;
    if (diffDays < 7) return `${diffDays} day${diffDays > 1 ? 's' : ''} ago`;
    return formatDate(date);
};

const hasRecords = computed(() => detainHistory.value.length > 0);
const totalRecords = computed(() => detainHistory.value.length);
const detainCount = computed(() => detainHistory.value.filter(r => r.action === 'detain').length);
const liftCount = computed(() => detainHistory.value.filter(r => r.action === 'lift').length);

onMounted(() => {
    fetchDetainHistory();
});
</script>

<template>
    <div class="detain-history-wrapper">
        <!-- Loading State -->
        <div v-if="loading" class="flex flex-col justify-center items-center py-12">
            <i class="pi pi-spin pi-spinner text-5xl text-primary mb-4"></i>
            <p class="text-gray-500">Loading detain history...</p>
        </div>

        <!-- Empty State -->
        <div v-else-if="!hasRecords" class="empty-state">
            <div class="empty-state-content">
                <i class="ti ti-history-off text-7xl text-gray-300 mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-600 mb-2">No Detain History</h3>
                <p class="text-gray-500">This shipment has no detain or lift records yet.</p>
            </div>
        </div>

        <!-- History Content -->
        <div v-else class="detain-history-container">
            <!-- Summary Header -->
            <div class="summary-header">
                <div class="summary-card">
                    <i class="ti ti-list-details text-2xl text-primary"></i>
                    <div>
                        <p class="text-xs text-gray-500 uppercase">Total Records</p>
                        <p class="text-2xl font-bold text-gray-800">{{ totalRecords }}</p>
                    </div>
                </div>
                <div class="summary-card">
                    <i class="pi pi-lock text-2xl text-red-500"></i>
                    <div>
                        <p class="text-xs text-gray-500 uppercase">Detained</p>
                        <p class="text-2xl font-bold text-red-600">{{ detainCount }}</p>
                    </div>
                </div>
                <div class="summary-card">
                    <i class="pi pi-lock-open text-2xl text-green-500"></i>
                    <div>
                        <p class="text-xs text-gray-500 uppercase">Lifted</p>
                        <p class="text-2xl font-bold text-green-600">{{ liftCount }}</p>
                    </div>
                </div>
            </div>

            <Divider />

            <!-- Timeline -->
            <Timeline :value="detainHistory" class="customized-timeline">
                <template #marker="slotProps">
                    <span
                        :class="{
                            'bg-red-500': slotProps.item.action === 'detain',
                            'bg-green-500': slotProps.item.action === 'lift'
                        }"
                        class="timeline-marker"
                    >
                        <i :class="getActionIcon(slotProps.item.action)"></i>
                    </span>
                </template>

                <template #content="slotProps">
                    <Card class="history-card">
                        <template #title>
                            <div class="card-header">
                                <div class="header-left">
                                    <Tag
                                        :severity="getActionColor(slotProps.item.action)"
                                        :value="slotProps.item.action === 'detain' ? 'DETAINED' : 'LIFTED'"
                                        class="action-tag"
                                    >
                                        <template #icon>
                                            <i :class="getActionIcon(slotProps.item.action)" class="mr-1"></i>
                                        </template>
                                    </Tag>
                                    <Tag
                                        :severity="getEntityColor(slotProps.item.entity_type)"
                                        class="entity-tag"
                                    >
                                        <i :class="getEntityIcon(slotProps.item.entity_type)" class="mr-1"></i>
                                        {{ slotProps.item.entity_type }}
                                    </Tag>
                                </div>
                                <div class="header-right">
                                    <span :title="formatDate(slotProps.item.created_at)" class="timestamp">
                                        <i class="pi pi-clock mr-1"></i>
                                        {{ getRelativeTime(slotProps.item.created_at) }}
                                    </span>
                                </div>
                            </div>
                        </template>

                        <template #content>
                            <div class="card-content">
                                <!-- Reference -->
                                <div class="info-row">
                                    <div class="info-label">
                                        <i class="ti ti-hash text-gray-400"></i>
                                        Reference
                                    </div>
                                    <div class="info-value font-mono">{{ slotProps.item.entity_reference }}</div>
                                </div>

                                <!-- Detain Type -->
                                <div v-if="slotProps.item.detain_type" class="info-row">
                                    <div class="info-label">
                                        <i class="ti ti-tag text-gray-400"></i>
                                        Detain Type
                                    </div>
                                    <div class="info-value">
                                        <Tag
                                            :severity="getDetainTypeSeverity(slotProps.item.detain_type)"
                                            :value="slotProps.item.detain_type"
                                            class="text-xs font-semibold"
                                        />
                                    </div>
                                </div>

                                <!-- Detain Reason -->
                                <div v-if="slotProps.item.detain_reason" class="info-row highlight-row detain-reason">
                                    <div class="info-label">
                                        <i class="ti ti-alert-circle text-red-500"></i>
                                        Detain Reason
                                    </div>
                                    <div class="info-value">{{ slotProps.item.detain_reason }}</div>
                                </div>

                                <!-- Lift Reason -->
                                <div v-if="slotProps.item.lift_reason" class="info-row highlight-row lift-reason">
                                    <div class="info-label">
                                        <i class="ti ti-circle-check text-green-500"></i>
                                        Lift Reason
                                    </div>
                                    <div class="info-value">{{ slotProps.item.lift_reason }}</div>
                                </div>

                                <!-- Remarks -->
                                <div v-if="slotProps.item.remarks" class="info-row highlight-row remarks">
                                    <div class="info-label">
                                        <i class="ti ti-note text-blue-500"></i>
                                        Remarks
                                    </div>
                                    <div class="info-value italic">{{ slotProps.item.remarks }}</div>
                                </div>

                                <Divider class="my-3" />

                                <!-- Footer Info -->
                                <div class="card-footer">
                                    <div v-if="slotProps.item.created_by" class="footer-item">
                                        <i class="pi pi-user text-gray-400"></i>
                                        <span class="footer-label">
                                            {{ slotProps.item.action === 'detain' ? 'Detained by' : 'Lifted by' }}:
                                        </span>
                                        <span class="footer-value">
                                            {{ slotProps.item.action === 'detain'
                                                ? slotProps.item.created_by.name
                                                : (slotProps.item.lifted_by?.name || 'N/A')
                                            }}
                                        </span>
                                    </div>
                                    <div class="footer-item">
                                        <i class="ti ti-layers-intersect text-gray-400"></i>
                                        <span class="footer-label">Level:</span>
                                        <Tag
                                            :value="slotProps.item.entity_level || 'N/A'"
                                            class="text-xs uppercase"
                                            rounded
                                            severity="secondary"
                                        />
                                    </div>
                                </div>
                            </div>
                        </template>
                    </Card>
                </template>
            </Timeline>
        </div>
    </div>
</template>

<style scoped>
.detain-history-wrapper {
    min-height: 400px;
}

.empty-state {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 400px;
    padding: 2rem;
}

.empty-state-content {
    text-align: center;
    max-width: 400px;
}

.detain-history-container {
    padding: 1rem;
    max-height: 650px;
    overflow-y: auto;
}

/* Summary Header */
.summary-header {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 1rem;
    margin-bottom: 1rem;
}

.summary-card {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    border-radius: 0.5rem;
    border: 1px solid #e5e7eb;
    transition: all 0.2s ease;
}

.summary-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

/* Timeline Customization */
.customized-timeline :deep(.p-timeline-event-content) {
    line-height: 1.5;
}

.customized-timeline :deep(.p-timeline-event-opposite) {
    flex: 0;
}

.timeline-marker {
    display: flex;
    width: 2.5rem;
    height: 2.5rem;
    align-items: center;
    justify-content: center;
    color: white;
    border-radius: 50%;
    z-index: 10;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    transition: all 0.3s ease;
}

.timeline-marker:hover {
    transform: scale(1.1);
}

/* Card Styling */
.history-card {
    margin-top: 0.5rem;
    border: 1px solid #e5e7eb;
    transition: all 0.2s ease;
}

.history-card:hover {
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
    border-color: #d1d5db;
}

.history-card :deep(.p-card-body) {
    padding: 1.25rem;
}

.history-card :deep(.p-card-title) {
    font-size: 1rem;
    margin-bottom: 0.75rem;
}

.history-card :deep(.p-card-content) {
    padding-top: 0;
}

/* Card Header */
.card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.header-left {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.header-right {
    display: flex;
    align-items: center;
}

.action-tag {
    font-size: 0.75rem;
    font-weight: 600;
}

.entity-tag {
    font-size: 0.75rem;
}

.timestamp {
    font-size: 0.875rem;
    color: #6b7280;
    display: flex;
    align-items: center;
}

/* Card Content */
.card-content {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.info-row {
    display: grid;
    grid-template-columns: 160px 1fr;
    gap: 1rem;
    align-items: start;
}

.info-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 600;
    color: #374151;
    font-size: 0.875rem;
}

.info-value {
    color: #1f2937;
    font-size: 0.875rem;
    word-break: break-word;
}

.highlight-row {
    padding: 0.75rem;
    border-radius: 0.375rem;
    grid-template-columns: 1fr;
    gap: 0.5rem;
}

.detain-reason {
    background: linear-gradient(135deg, #fef2f2 0%, #fff5f5 100%);
    border-left: 3px solid #ef4444;
}

.lift-reason {
    background: linear-gradient(135deg, #f0fdf4 0%, #f7fee7 100%);
    border-left: 3px solid #22c55e;
}

.remarks {
    background: linear-gradient(135deg, #eff6ff 0%, #f0f9ff 100%);
    border-left: 3px solid #3b82f6;
}

/* Card Footer */
.card-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 1rem;
    font-size: 0.813rem;
    color: #6b7280;
}

.footer-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.footer-label {
    font-weight: 500;
}

.footer-value {
    font-weight: 600;
    color: #374151;
}

/* Scrollbar Styling */
.detain-history-container::-webkit-scrollbar {
    width: 8px;
}

.detain-history-container::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 4px;
}

.detain-history-container::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
}

.detain-history-container::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

/* Responsive Design */
@media (max-width: 768px) {
    .info-row {
        grid-template-columns: 1fr;
        gap: 0.5rem;
    }

    .card-footer {
        flex-direction: column;
        align-items: flex-start;
    }

    .summary-header {
        grid-template-columns: 1fr;
    }
}
</style>
