<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import Button from 'primevue/button';
import Image from 'primevue/image';
import { push } from 'notivue';

const props = defineProps({
    container: {
        type: Object,
        required: true,
    },
});

const unloadingIssues = ref([]);
const loading = ref(false);
const expandedRows = ref([]);

const fetchUnloadingIssues = async () => {
    if (!props.container?.id) return;

    loading.value = true;
    try {
        const response = await axios.get(route('loading.containers.unloading-issues', props.container.id));

        if (response.data.success) {
            unloadingIssues.value = response.data.data;
            console.log('Unloading issues loaded:', unloadingIssues.value);
            // Log first item to see structure
            if (unloadingIssues.value.length > 0) {
                console.log('First issue structure:', unloadingIssues.value[0]);
            }
        } else {
            console.error('Failed to fetch unloading issues:', response.data.message);
        }
    } catch (error) {
        console.error('Error fetching unloading issues:', error);
        console.error('Error response:', error.response?.data);
        push.error('Failed to load unloading issues');
    } finally {
        loading.value = false;
    }
};

const getIssueSeverity = (issue) => {
    if (issue.rtf) return 'danger';
    if (issue.is_damaged) return 'warn';
    return 'info';
};

const getStatusSeverity = (isFixed) => {
    return isFixed ? 'success' : 'warn';
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

const hasIssues = computed(() => unloadingIssues.value.length > 0);
const totalIssues = computed(() => unloadingIssues.value.length);
const damagedCount = computed(() => unloadingIssues.value.filter(i => i.is_damaged).length);
const rtfCount = computed(() => unloadingIssues.value.filter(i => i.rtf).length);
const fixedCount = computed(() => unloadingIssues.value.filter(i => i.is_fixed).length);

onMounted(() => {
    fetchUnloadingIssues();
});
</script>

<template>
    <div class="unloading-issues-wrapper">
        <!-- Loading State -->
        <div v-if="loading" class="flex flex-col justify-center items-center py-12">
            <i class="pi pi-spin pi-spinner text-5xl text-primary mb-4"></i>
            <p class="text-gray-500">Loading unloading issues...</p>
        </div>

        <!-- Empty State -->
        <div v-else-if="!hasIssues" class="empty-state">
            <div class="empty-state-content">
                <i class="ti ti-circle-check text-7xl text-green-300 mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-600 mb-2">No Unloading Issues</h3>
                <p class="text-gray-500">This shipment has no reported unloading issues.</p>
            </div>
        </div>

        <!-- Issues Content -->
        <div v-else class="unloading-issues-container">
            <!-- Summary Header -->
            <div class="summary-header">
                <div class="summary-card">
                    <i class="ti ti-alert-triangle text-2xl text-orange-500"></i>
                    <div>
                        <p class="text-xs text-gray-500 uppercase">Total Issues</p>
                        <p class="text-2xl font-bold text-gray-800">{{ totalIssues }}</p>
                    </div>
                </div>
                <div class="summary-card">
                    <i class="ti ti-package-off text-2xl text-red-500"></i>
                    <div>
                        <p class="text-xs text-gray-500 uppercase">Damaged</p>
                        <p class="text-2xl font-bold text-red-600">{{ damagedCount }}</p>
                    </div>
                </div>
                <div class="summary-card">
                    <i class="ti ti-flag text-2xl text-purple-500"></i>
                    <div>
                        <p class="text-xs text-gray-500 uppercase">RTF</p>
                        <p class="text-2xl font-bold text-purple-600">{{ rtfCount }}</p>
                    </div>
                </div>
                <div class="summary-card">
                    <i class="ti ti-circle-check text-2xl text-green-500"></i>
                    <div>
                        <p class="text-xs text-gray-500 uppercase">Fixed</p>
                        <p class="text-2xl font-bold text-green-600">{{ fixedCount }}</p>
                    </div>
                </div>
            </div>

            <!-- Data Table -->
            <DataTable
                v-model:expandedRows="expandedRows"
                :rows="10"
                :rowsPerPageOptions="[5, 10, 20, 50]"
                :value="unloadingIssues"
                class="p-datatable-sm"
                dataKey="id"
                paginator
                stripedRows
            >
                <Column expander style="width: 3rem" />

                <Column field="hbl_package.hbl.hbl_number" header="HBL Number" sortable>
                    <template #body="slotProps">
                        <span class="font-mono font-semibold">
                            {{ slotProps.data.hbl_package?.hbl?.hbl_number || 'N/A' }}
                        </span>
                    </template>
                </Column>

                <Column field="type" header="Type" sortable>
                    <template #body="slotProps">
                        <Tag
                            :severity="getIssueSeverity(slotProps.data)"
                            :value="slotProps.data.type || 'General'"
                            class="text-xs"
                        />
                    </template>
                </Column>

                <Column field="is_damaged" header="Damaged" sortable>
                    <template #body="slotProps">
                        <Tag
                            :severity="slotProps.data.is_damaged ? 'danger' : 'secondary'"
                            :value="slotProps.data.is_damaged ? 'Yes' : 'No'"
                            class="text-xs"
                        />
                    </template>
                </Column>

                <Column field="rtf" header="RTF" sortable>
                    <template #body="slotProps">
                        <Tag
                            :severity="slotProps.data.rtf ? 'danger' : 'secondary'"
                            :value="slotProps.data.rtf ? 'Yes' : 'No'"
                            class="text-xs"
                        />
                    </template>
                </Column>

                <Column field="is_fixed" header="Status" sortable>
                    <template #body="slotProps">
                        <Tag
                            :severity="getStatusSeverity(slotProps.data.is_fixed)"
                            class="text-xs"
                        >
                            <i :class="slotProps.data.is_fixed ? 'pi pi-check' : 'pi pi-clock'" class="mr-1"></i>
                            {{ slotProps.data.is_fixed ? 'Fixed' : 'Pending' }}
                        </Tag>
                    </template>
                </Column>

                <Column field="created_at" header="Reported" sortable>
                    <template #body="slotProps">
                        <span class="text-sm text-gray-600">
                            {{ formatDate(slotProps.data.created_at) }}
                        </span>
                    </template>
                </Column>

                <!-- Expanded Row Template -->
                <template #expansion="slotProps">
                    <div class="expanded-content">
                        <div class="expanded-grid">
                            <!-- Issue Details -->
                            <div class="detail-section">
                                <h4 class="section-title">
                                    <i class="ti ti-info-circle"></i>
                                    Issue Details
                                </h4>
                                <div class="detail-item">
                                    <span class="detail-label">Issue Description:</span>
                                    <span class="detail-value">{{ slotProps.data.issue || 'N/A' }}</span>
                                </div>
                                <div v-if="slotProps.data.note" class="detail-item">
                                    <span class="detail-label">Note:</span>
                                    <span class="detail-value">{{ slotProps.data.note }}</span>
                                </div>
                                <div v-if="slotProps.data.remarks" class="detail-item">
                                    <span class="detail-label">Remarks:</span>
                                    <span class="detail-value italic">{{ slotProps.data.remarks }}</span>
                                </div>
                            </div>

                            <!-- Package Details -->
                            <div class="detail-section">
                                <h4 class="section-title">
                                    <i class="ti ti-package"></i>
                                    Package Information
                                </h4>
                                <div v-if="slotProps.data.hbl_package">
                                    <div class="detail-item">
                                        <span class="detail-label">Package Number:</span>
                                        <span class="detail-value font-mono">
                                            {{ slotProps.data.hbl_package.package_number || 'N/A' }}
                                        </span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">Package Type:</span>
                                        <span class="detail-value">
                                            {{ slotProps.data.hbl_package.package_type || 'N/A' }}
                                        </span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">Weight:</span>
                                        <span class="detail-value">
                                            {{ slotProps.data.hbl_package.weight ? slotProps.data.hbl_package.weight + ' kg' : 'N/A' }}
                                        </span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">Volume:</span>
                                        <span class="detail-value">
                                            {{ slotProps.data.hbl_package.volume ? slotProps.data.hbl_package.volume + ' m³' : 'N/A' }}
                                        </span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">Quantity:</span>
                                        <span class="detail-value">
                                            {{ slotProps.data.hbl_package.quantity || 'N/A' }}
                                        </span>
                                    </div>
                                    <div v-if="slotProps.data.hbl_package.remarks" class="detail-item">
                                        <span class="detail-label">Package Remarks:</span>
                                        <span class="detail-value italic">
                                            {{ slotProps.data.hbl_package.remarks }}
                                        </span>
                                    </div>
                                </div>
                                <div v-else class="text-gray-500 italic">
                                    No package information available
                                </div>
                            </div>

                            <!-- Images Section -->
                            <div v-if="slotProps.data.files && slotProps.data.files.length > 0" class="detail-section full-width">
                                <h4 class="section-title">
                                    <i class="ti ti-photo"></i>
                                    Issue Images ({{ slotProps.data.files.length }})
                                </h4>
                                <div class="images-grid">
                                    <div
                                        v-for="file in slotProps.data.files"
                                        :key="file.id"
                                        class="image-item"
                                    >
                                        <Image
                                            :alt="`Issue image ${file.id}`"
                                            :src="file.file_path"
                                            class="issue-image"
                                            preview
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </DataTable>
        </div>
    </div>
</template>

<style scoped>
.unloading-issues-wrapper {
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

.unloading-issues-container {
    padding: 1rem;
}

/* Summary Header */
.summary-header {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
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

/* Expanded Content */
.expanded-content {
    padding: 1.5rem;
    background: linear-gradient(135deg, #f9fafb 0%, #ffffff 100%);
    border-radius: 0.5rem;
    margin: 0.5rem 0;
}

.expanded-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
}

.detail-section {
    background: white;
    padding: 1rem;
    border-radius: 0.5rem;
    border: 1px solid #e5e7eb;
}

.detail-section.full-width {
    grid-column: 1 / -1;
}

.section-title {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 1rem;
    font-weight: 600;
    color: #374151;
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid #e5e7eb;
}

.detail-item {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
    margin-bottom: 0.75rem;
}

.detail-item:last-child {
    margin-bottom: 0;
}

.detail-label {
    font-size: 0.75rem;
    font-weight: 600;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.025em;
}

.detail-value {
    font-size: 0.875rem;
    color: #1f2937;
    word-break: break-word;
}

/* Images Grid */
.images-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 1rem;
}

.image-item {
    position: relative;
    aspect-ratio: 1;
    border-radius: 0.5rem;
    overflow: hidden;
    border: 2px solid #e5e7eb;
    transition: all 0.2s ease;
}

.image-item:hover {
    border-color: #3b82f6;
    transform: scale(1.02);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.2);
}

.issue-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* Responsive Design */
@media (max-width: 768px) {
    .summary-header {
        grid-template-columns: repeat(2, 1fr);
    }

    .expanded-grid {
        grid-template-columns: 1fr;
    }

    .images-grid {
        grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
    }
}
</style>
