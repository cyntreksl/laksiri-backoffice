<script setup>
import { ref, computed } from 'vue';
import AppLayout from "@/Layouts/AppLayout.vue";
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    container: {
        type: Object,
        required: true
    },
    auditLogs: {
        type: Array,
        default: () => []
    },
    filters: {
        type: Object,
        default: () => ({})
    }
});

const formatDate = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleString();
};

const getActionSeverity = (action) => {
    return action === 'unload' ? 'success' : 'danger';
};

const getLevelSeverity = (level) => {
    const severities = {
        'package': 'info',
        'hbl': 'warning',
        'mhbl': 'primary'
    };
    return severities[level] || 'secondary';
};

const goBack = () => {
    router.visit(route('arrival.unloading-points.index', props.container.id));
};
</script>

<template>
    <AppLayout title="Unloading Audit Logs">
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold">Unloading Audit Logs</h2>
                    <p class="text-sm text-slate-600 dark:text-navy-300 mt-1">
                        Container: {{ container.reference }} ({{ container.cargo_type }})
                    </p>
                </div>
                <Button
                    icon="pi pi-arrow-left"
                    label="Back to Unloading Point"
                    severity="secondary"
                    @click="goBack"
                />
            </div>
        </template>

        <div class="px-[var(--margin-x)] py-5">
            <DataTable
                :rows="20"
                :rowsPerPageOptions="[10, 20, 50, 100]"
                :value="auditLogs"
                class="p-datatable-sm"
                paginator
                stripedRows
            >
                <Column field="created_at" header="Date & Time" sortable>
                    <template #body="slotProps">
                        {{ formatDate(slotProps.data.created_at) }}
                    </template>
                </Column>

                <Column field="action" header="Action" sortable>
                    <template #body="slotProps">
                        <Tag
                            :severity="getActionSeverity(slotProps.data.action)"
                            :value="slotProps.data.action.toUpperCase()"
                        />
                    </template>
                </Column>

                <Column field="level" header="Level" sortable>
                    <template #body="slotProps">
                        <Tag
                            :severity="getLevelSeverity(slotProps.data.level)"
                            :value="slotProps.data.level.toUpperCase()"
                        />
                    </template>
                </Column>

                <Column field="hbl_number" header="HBL Number" sortable>
                    <template #body="slotProps">
                        {{ slotProps.data.hbl_number || '-' }}
                    </template>
                </Column>

                <Column field="mhbl_number" header="MHBL Number" sortable>
                    <template #body="slotProps">
                        {{ slotProps.data.mhbl_number || '-' }}
                    </template>
                </Column>

                <Column field="package_count" header="Package Count" sortable>
                    <template #body="slotProps">
                        <span class="font-semibold">{{ slotProps.data.package_count }}</span>
                    </template>
                </Column>

                <Column field="performed_by" header="Performed By" sortable>
                    <template #body="slotProps">
                        {{ slotProps.data.performed_by?.name || 'Unknown' }}
                    </template>
                </Column>

                <Column header="Details">
                    <template #body="slotProps">
                        <div class="text-xs text-slate-600 dark:text-navy-300">
                            {{ slotProps.data.description }}
                        </div>
                    </template>
                </Column>
            </DataTable>
        </div>
    </AppLayout>
</template>
