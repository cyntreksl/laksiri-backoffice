<script setup>
import { ref, onMounted } from 'vue';
import Card from 'primevue/card';
import Timeline from 'primevue/timeline';
import Tag from 'primevue/tag';
import PostSkeleton from '@/Components/PostSkeleton.vue';
import axios from 'axios';
import { push } from 'notivue';
import moment from 'moment';

const props = defineProps({
    hblId: {
        type: Number,
        required: true
    }
});

const loading = ref(true);
const detainHistory = ref([]);

onMounted(() => {
    fetchDetainHistory();
});

const fetchDetainHistory = async () => {
    loading.value = true;
    try {
        const response = await axios.get(`/hbls/${props.hblId}/detain-history`);
        detainHistory.value = response.data.data || [];
    } catch (error) {
        console.error('Error fetching detain history:', error);
        push.error('Failed to load detain history');
    } finally {
        loading.value = false;
    }
};

const getStatusSeverity = (record) => {
    if (record.action === 'detain') {
        return 'danger';
    } else {
        return 'success';
    }
};

const getStatusIcon = (record) => {
    if (record.action === 'detain') {
        return 'pi pi-lock';
    } else {
        return 'pi pi-unlock';
    }
};

const getEntityLevelBadge = (level) => {
    const badges = {
        'shipment': { severity: 'info', icon: 'ti ti-ship' },
        'hbl': { severity: 'warn', icon: 'ti ti-file-text' },
        'package': { severity: 'secondary', icon: 'ti ti-package' }
    };
    return badges[level] || { severity: 'secondary', icon: 'ti ti-help' };
};

const formatDate = (dateString) => {
    return moment(dateString).format('MMM DD, YYYY HH:mm');
};

const getDetainTypeColor = (type) => {
    const colors = {
        'RTF': 'bg-red-100 text-red-700 border-red-300',
        'DDC': 'bg-blue-100 text-blue-700 border-blue-300',
        'SDDC': 'bg-purple-100 text-purple-700 border-purple-300',
        'IAU': 'bg-orange-100 text-orange-700 border-orange-300',
        'DC': 'bg-green-100 text-green-700 border-green-300',
        'CO': 'bg-yellow-100 text-yellow-700 border-yellow-300',
        'ICT': 'bg-indigo-100 text-indigo-700 border-indigo-300'
    };
    return colors[type] || 'bg-gray-100 text-gray-700 border-gray-300';
};
</script>

<template>
    <div class="space-y-4">
        <PostSkeleton v-if="loading" />

        <Card v-else-if="detainHistory.length === 0" class="border">
            <template #content>
                <div class="text-center py-8 text-gray-500">
                    <i class="pi pi-info-circle text-4xl mb-3"></i>
                    <p>No detain history found for this HBL</p>
                </div>
            </template>
        </Card>

        <Card v-else class="border">
            <template #title>
                <div class="flex items-center gap-2">
                    <i class="ti ti-history text-xl"></i>
                    <span>Detain History</span>
                    <Tag :value="`${detainHistory.length} Records`" severity="info" />
                </div>
            </template>
            <template #content>
                <Timeline :value="detainHistory" class="customized-timeline">
                    <template #marker="slotProps">
                        <span
                            :class="[
                                'flex items-center justify-center w-8 h-8 rounded-full',
                                slotProps.item.action === 'detain' ? 'bg-red-500' : 'bg-green-500'
                            ]"
                        >
                            <i :class="[getStatusIcon(slotProps.item), 'text-white']"></i>
                        </span>
                    </template>

                    <template #content="slotProps">
                        <Card class="mt-3 shadow-sm border">
                            <template #content>
                                <div class="space-y-3">
                                    <!-- Header -->
                                    <div class="flex items-start justify-between">
                                        <div class="flex items-center gap-2">
                                            <Tag
                                                :icon="getStatusIcon(slotProps.item)"
                                                :severity="getStatusSeverity(slotProps.item)"
                                                :value="slotProps.item.action === 'detain' ? 'DETAINED' : 'LIFTED'"
                                            />
                                            <span
                                                v-if="slotProps.item.detain_type"
                                                :class="[
                                                    'px-2 py-1 text-xs font-semibold rounded border',
                                                    getDetainTypeColor(slotProps.item.detain_type)
                                                ]"
                                            >
                                                {{ slotProps.item.detain_type }}
                                            </span>
                                        </div>
                                        <Tag
                                            :severity="getEntityLevelBadge(slotProps.item.entity_level).severity"
                                            :value="slotProps.item.entity_level?.toUpperCase()"
                                        >
                                            <template #icon>
                                                <i :class="[getEntityLevelBadge(slotProps.item.entity_level).icon, 'mr-1']"></i>
                                            </template>
                                        </Tag>
                                    </div>

                                    <!-- Detain Reason -->
                                    <div v-if="slotProps.item.detain_reason" class="bg-gray-50 p-3 rounded">
                                        <p class="text-xs text-gray-500 font-semibold mb-1">
                                            {{ slotProps.item.action === 'detain' ? 'Detain Reason' : 'Original Detain Reason' }}
                                        </p>
                                        <p class="text-sm text-gray-700">{{ slotProps.item.detain_reason }}</p>
                                    </div>

                                    <!-- Lift Reason -->
                                    <div v-if="slotProps.item.lift_reason" class="bg-green-50 p-3 rounded">
                                        <p class="text-xs text-green-600 font-semibold mb-1">Lift Reason</p>
                                        <p class="text-sm text-gray-700">{{ slotProps.item.lift_reason }}</p>
                                    </div>

                                    <!-- Remarks -->
                                    <div v-if="slotProps.item.remarks" class="bg-blue-50 p-3 rounded">
                                        <p class="text-xs text-blue-600 font-semibold mb-1">Remarks</p>
                                        <p class="text-sm text-gray-700">{{ slotProps.item.remarks }}</p>
                                    </div>

                                    <!-- Footer Info -->
                                    <div class="flex items-center justify-between text-xs text-gray-500 pt-2 border-t">
                                        <div class="flex items-center gap-4">
                                            <div v-if="slotProps.item.action === 'detain' && slotProps.item.detained_by">
                                                <i class="pi pi-user mr-1"></i>
                                                <span class="font-semibold">Detained by:</span>
                                                {{ slotProps.item.detained_by.name }}
                                            </div>
                                            <div v-if="slotProps.item.action === 'lift' && slotProps.item.lifted_by_user">
                                                <i class="pi pi-user mr-1"></i>
                                                <span class="font-semibold">Lifted by:</span>
                                                {{ slotProps.item.lifted_by_user.name }}
                                            </div>
                                        </div>
                                        <div>
                                            <i class="pi pi-clock mr-1"></i>
                                            {{ formatDate(slotProps.item.action === 'detain' ? slotProps.item.created_at : slotProps.item.lifted_at) }}
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </Card>
                    </template>
                </Timeline>
            </template>
        </Card>
    </div>
</template>

<style scoped>
:deep(.p-timeline-event-content) {
    width: 100%;
}

:deep(.p-timeline-event-opposite) {
    display: none;
}
</style>
