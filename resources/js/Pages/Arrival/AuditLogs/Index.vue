<script setup>
import { ref, computed } from 'vue';
import AppLayout from "@/Layouts/AppLayout.vue";
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import Dropdown from 'primevue/dropdown';
import Calendar from 'primevue/calendar';
import Card from 'primevue/card';
import { router } from '@inertiajs/vue3';
import moment from 'moment';

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

// Filter states
const selectedAction = ref(props.filters.action || null);
const selectedLevel = ref(props.filters.level || null);
const dateRange = ref(null);

// Filter options
const actionOptions = [
    { label: 'All Actions', value: null },
    { label: 'Unload', value: 'unload' },
    { label: 'Reload', value: 'reload' }
];

const levelOptions = [
    { label: 'All Levels', value: null },
    { label: 'Package', value: 'package' },
    { label: 'HBL', value: 'hbl' },
    { label: 'MHBL', value: 'mhbl' }
];

// Computed statistics
const statistics = computed(() => {
    const stats = {
        total: props.auditLogs.length,
        unloads: 0,
        reloads: 0,
        packageLevel: 0,
        hblLevel: 0,
        mhblLevel: 0,
        totalPackages: 0,
        uniqueUsers: new Set()
    };

    props.auditLogs.forEach(log => {
        if (log.action === 'unload') stats.unloads++;
        if (log.action === 'reload') stats.reloads++;
        if (log.level === 'package') stats.packageLevel++;
        if (log.level === 'hbl') stats.hblLevel++;
        if (log.level === 'mhbl') stats.mhblLevel++;
        stats.totalPackages += log.package_count || 0;
        if (log.performed_by?.id) stats.uniqueUsers.add(log.performed_by.id);
    });

    return {
        ...stats,
        uniqueUsers: stats.uniqueUsers.size
    };
});

// Filtered logs
const filteredLogs = computed(() => {
    let logs = [...props.auditLogs];

    if (selectedAction.value) {
        logs = logs.filter(log => log.action === selectedAction.value);
    }

    if (selectedLevel.value) {
        logs = logs.filter(log => log.level === selectedLevel.value);
    }

    if (dateRange.value && dateRange.value.length === 2) {
        const [start, end] = dateRange.value;
        logs = logs.filter(log => {
            const logDate = new Date(log.created_at);
            return logDate >= start && logDate <= end;
        });
    }

    return logs;
});

const formatDate = (dateString) => {
    if (!dateString) return '';
    return moment(dateString).format('MMM DD, YYYY HH:mm:ss');
};

const formatDateShort = (dateString) => {
    if (!dateString) return '';
    return moment(dateString).format('MMM DD, HH:mm');
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

const getActionIcon = (action) => {
    return action === 'unload' ? 'pi-arrow-down' : 'pi-arrow-up';
};

const getLevelIcon = (level) => {
    const icons = {
        'package': 'pi-box',
        'hbl': 'pi-folder',
        'mhbl': 'pi-briefcase'
    };
    return icons[level] || 'pi-circle';
};

const clearFilters = () => {
    selectedAction.value = null;
    selectedLevel.value = null;
    dateRange.value = null;
};

const goBack = () => {
    router.visit(route('arrival.unloading-points.index', props.container.id));
};

const exportLogs = () => {
    // TODO: Implement export functionality
    console.log('Export logs');
};
</script>

<template>
    <AppLayout title="Unloading Audit Logs">
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-slate-800 dark:text-navy-50">Unloading Audit Logs</h2>
                    <p class="text-sm text-slate-600 dark:text-navy-300 mt-1">
                        <i class="pi pi-box mr-1"></i>
                        Container: <span class="font-semibold">{{ container.reference }}</span>
                        <span class="mx-2">•</span>
                        {{ container.cargo_type }} {{ container.container_type }}
                    </p>
                </div>
                <div class="flex gap-2">
                    <Button
                        icon="pi pi-download"
                        label="Export"
                        outlined
                        severity="secondary"
                        size="small"
                        @click="exportLogs"
                    />
                    <Button
                        icon="pi pi-arrow-left"
                        label="Back"
                        severity="secondary"
                        size="small"
                        @click="goBack"
                    />
                </div>
            </div>
        </template>

        <div class="px-[var(--margin-x)] py-5 space-y-5">
            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Total Operations -->
                <div class="bg-white dark:bg-navy-800 rounded-lg border border-slate-200 dark:border-navy-600 p-4 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-slate-600 dark:text-navy-300 uppercase tracking-wide">Total Operations</p>
                            <p class="text-2xl font-bold text-slate-800 dark:text-navy-50 mt-1">{{ statistics.total }}</p>
                        </div>
                        <div class="flex items-center justify-center size-12 rounded-lg bg-primary/10 text-primary">
                            <i class="pi pi-list text-xl"></i>
                        </div>
                    </div>
                    <div class="mt-3 flex items-center gap-4 text-xs">
                        <span class="text-success flex items-center gap-1">
                            <i class="pi pi-arrow-down"></i>
                            {{ statistics.unloads }} unloads
                        </span>
                        <span class="text-danger flex items-center gap-1">
                            <i class="pi pi-arrow-up"></i>
                            {{ statistics.reloads }} reloads
                        </span>
                    </div>
                </div>

                <!-- Total Packages -->
                <div class="bg-white dark:bg-navy-800 rounded-lg border border-slate-200 dark:border-navy-600 p-4 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-slate-600 dark:text-navy-300 uppercase tracking-wide">Total Packages</p>
                            <p class="text-2xl font-bold text-slate-800 dark:text-navy-50 mt-1">{{ statistics.totalPackages }}</p>
                        </div>
                        <div class="flex items-center justify-center size-12 rounded-lg bg-success/10 text-success">
                            <i class="pi pi-box text-xl"></i>
                        </div>
                    </div>
                    <div class="mt-3 text-xs text-slate-600 dark:text-navy-300">
                        Across all operations
                    </div>
                </div>

                <!-- Operations by Level -->
                <div class="bg-white dark:bg-navy-800 rounded-lg border border-slate-200 dark:border-navy-600 p-4 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-slate-600 dark:text-navy-300 uppercase tracking-wide">By Level</p>
                            <p class="text-2xl font-bold text-slate-800 dark:text-navy-50 mt-1">3 Types</p>
                        </div>
                        <div class="flex items-center justify-center size-12 rounded-lg bg-warning/10 text-warning">
                            <i class="pi pi-sitemap text-xl"></i>
                        </div>
                    </div>
                    <div class="mt-3 flex items-center gap-3 text-xs">
                        <span class="text-info">{{ statistics.packageLevel }} pkg</span>
                        <span class="text-warning">{{ statistics.hblLevel }} hbl</span>
                        <span class="text-primary">{{ statistics.mhblLevel }} mhbl</span>
                    </div>
                </div>

                <!-- Unique Users -->
                <div class="bg-white dark:bg-navy-800 rounded-lg border border-slate-200 dark:border-navy-600 p-4 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-slate-600 dark:text-navy-300 uppercase tracking-wide">Active Users</p>
                            <p class="text-2xl font-bold text-slate-800 dark:text-navy-50 mt-1">{{ statistics.uniqueUsers }}</p>
                        </div>
                        <div class="flex items-center justify-center size-12 rounded-lg bg-info/10 text-info">
                            <i class="pi pi-users text-xl"></i>
                        </div>
                    </div>
                    <div class="mt-3 text-xs text-slate-600 dark:text-navy-300">
                        Performed operations
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <Card class="shadow-sm">
                <template #content>
                    <div class="flex flex-wrap items-end gap-4">
                        <div class="flex-1 min-w-[200px]">
                            <label class="block text-sm font-medium text-slate-700 dark:text-navy-100 mb-2">
                                <i class="pi pi-filter mr-1"></i>
                                Action Type
                            </label>
                            <Dropdown
                                v-model="selectedAction"
                                :options="actionOptions"
                                class="w-full"
                                optionLabel="label"
                                optionValue="value"
                                placeholder="All Actions"
                            />
                        </div>

                        <div class="flex-1 min-w-[200px]">
                            <label class="block text-sm font-medium text-slate-700 dark:text-navy-100 mb-2">
                                <i class="pi pi-sitemap mr-1"></i>
                                Operation Level
                            </label>
                            <Dropdown
                                v-model="selectedLevel"
                                :options="levelOptions"
                                class="w-full"
                                optionLabel="label"
                                optionValue="value"
                                placeholder="All Levels"
                            />
                        </div>

                        <div class="flex-1 min-w-[250px]">
                            <label class="block text-sm font-medium text-slate-700 dark:text-navy-100 mb-2">
                                <i class="pi pi-calendar mr-1"></i>
                                Date Range
                            </label>
                            <Calendar
                                v-model="dateRange"
                                :manualInput="false"
                                class="w-full"
                                dateFormat="M dd, yy"
                                placeholder="Select date range"
                                selectionMode="range"
                                showIcon
                            />
                        </div>

                        <div>
                            <Button
                                icon="pi pi-filter-slash"
                                label="Clear"
                                outlined
                                severity="secondary"
                                @click="clearFilters"
                            />
                        </div>
                    </div>

                    <div v-if="selectedAction || selectedLevel || dateRange" class="mt-4 flex items-center gap-2 text-sm">
                        <span class="text-slate-600 dark:text-navy-300">Active filters:</span>
                        <Tag v-if="selectedAction" :value="`Action: ${selectedAction}`" severity="success" />
                        <Tag v-if="selectedLevel" :value="`Level: ${selectedLevel}`" severity="warning" />
                        <Tag v-if="dateRange" severity="info" value="Date range applied" />
                        <span class="text-slate-600 dark:text-navy-300 ml-2">
                            ({{ filteredLogs.length }} of {{ statistics.total }} records)
                        </span>
                    </div>
                </template>
            </Card>

            <!-- Data Table -->
            <Card class="shadow-sm">
                <template #content>
                    <DataTable
                        :globalFilterFields="['hbl_number', 'mhbl_number', 'performed_by.name']"
                        :rows="20"
                        :rowsPerPageOptions="[10, 20, 50, 100]"
                        :value="filteredLogs"
                        class="p-datatable-sm"
                        paginator
                        responsiveLayout="scroll"
                        stripedRows
                    >
                        <template #empty>
                            <div class="text-center py-8">
                                <i class="pi pi-inbox text-4xl text-slate-400 dark:text-navy-400 mb-3"></i>
                                <p class="text-slate-600 dark:text-navy-300">No audit logs found</p>
                                <p class="text-sm text-slate-500 dark:text-navy-400 mt-1">
                                    Try adjusting your filters
                                </p>
                            </div>
                        </template>

                        <Column field="created_at" header="Date & Time" sortable style="min-width: 180px">
                            <template #body="slotProps">
                                <div class="flex flex-col">
                                    <span class="font-medium text-slate-700 dark:text-navy-100">
                                        {{ formatDateShort(slotProps.data.created_at) }}
                                    </span>
                                    <span class="text-xs text-slate-500 dark:text-navy-400">
                                        {{ moment(slotProps.data.created_at).fromNow() }}
                                    </span>
                                </div>
                            </template>
                        </Column>

                        <Column field="action" header="Action" sortable style="min-width: 120px">
                            <template #body="slotProps">
                                <Tag
                                    :severity="getActionSeverity(slotProps.data.action)"
                                    :value="slotProps.data.action.toUpperCase()"
                                    class="font-semibold"
                                >
                                    <template #default>
                                        <i :class="`pi ${getActionIcon(slotProps.data.action)} mr-1`"></i>
                                        {{ slotProps.data.action.toUpperCase() }}
                                    </template>
                                </Tag>
                            </template>
                        </Column>

                        <Column field="level" header="Level" sortable style="min-width: 120px">
                            <template #body="slotProps">
                                <Tag
                                    :severity="getLevelSeverity(slotProps.data.level)"
                                    :value="slotProps.data.level.toUpperCase()"
                                >
                                    <template #default>
                                        <i :class="`pi ${getLevelIcon(slotProps.data.level)} mr-1`"></i>
                                        {{ slotProps.data.level.toUpperCase() }}
                                    </template>
                                </Tag>
                            </template>
                        </Column>

                        <Column field="hbl_number" header="HBL Number" sortable style="min-width: 150px">
                            <template #body="slotProps">
                                <span v-if="slotProps.data.hbl_number" class="font-mono text-sm font-medium text-slate-700 dark:text-navy-100">
                                    {{ slotProps.data.hbl_number }}
                                </span>
                                <span v-else class="text-slate-400 dark:text-navy-500">-</span>
                            </template>
                        </Column>

                        <Column field="mhbl_number" header="MHBL Number" sortable style="min-width: 150px">
                            <template #body="slotProps">
                                <span v-if="slotProps.data.mhbl_number" class="font-mono text-sm font-medium text-slate-700 dark:text-navy-100">
                                    {{ slotProps.data.mhbl_number }}
                                </span>
                                <span v-else class="text-slate-400 dark:text-navy-500">-</span>
                            </template>
                        </Column>

                        <Column field="package_count" header="Packages" sortable style="min-width: 100px">
                            <template #body="slotProps">
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center justify-center size-7 rounded-full bg-primary/10 text-primary font-bold text-sm">
                                        {{ slotProps.data.package_count }}
                                    </span>
                                    <span class="text-xs text-slate-500 dark:text-navy-400">
                                        {{ slotProps.data.package_count === 1 ? 'pkg' : 'pkgs' }}
                                    </span>
                                </div>
                            </template>
                        </Column>

                        <Column field="performed_by" header="Performed By" sortable style="min-width: 150px">
                            <template #body="slotProps">
                                <div class="flex items-center gap-2">
                                    <div class="flex items-center justify-center size-8 rounded-full bg-info/10 text-info font-semibold text-xs">
                                        {{ (slotProps.data.performed_by?.name || 'U')[0].toUpperCase() }}
                                    </div>
                                    <span class="font-medium text-slate-700 dark:text-navy-100">
                                        {{ slotProps.data.performed_by?.name || 'Unknown' }}
                                    </span>
                                </div>
                            </template>
                        </Column>

                        <Column header="Details" style="min-width: 250px">
                            <template #body="slotProps">
                                <div class="text-sm text-slate-600 dark:text-navy-300">
                                    {{ slotProps.data.description }}
                                </div>
                                <div v-if="slotProps.data.ip_address" class="text-xs text-slate-400 dark:text-navy-500 mt-1">
                                    <i class="pi pi-map-marker mr-1"></i>
                                    {{ slotProps.data.ip_address }}
                                </div>
                            </template>
                        </Column>
                    </DataTable>
                </template>
            </Card>
        </div>
    </AppLayout>
</template>
