<script setup>
import { ref, onMounted, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import Checkbox from 'primevue/checkbox';

const props = defineProps({
    permissionGroup: {
        type: Object,
        required: true,
    },
    rolePermissions: {
        type: Array,
        default: () => [],
    },
    allChecked: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['permissions-loaded', 'permission-changed']);

const permissions = ref([]);
const loading = ref(false);
const groupChecked = ref(false);
const permissionChecked = ref({});

// Fetch permissions for this specific group
const fetchGroupPermissions = async () => {
    loading.value = true;
    try {
        const response = await fetch(`/permissions/${props.permissionGroup.name}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': usePage().props.csrf,
            },
        });

        if (!response.ok) {
            throw new Error('Network response was not ok.');
        }

        permissions.value = await response.json();
        initializeChecks();

        // Emit permissions loaded event
        emit('permissions-loaded', {
            groupName: props.permissionGroup.name,
            permissions: permissions.value,
        });

        // Emit initial permission state
        emitPermissionChange();
    } catch (error) {
        console.error(`Error fetching permissions for ${props.permissionGroup.name}:`, error);
    } finally {
        loading.value = false;
    }
};

// Initialize permission checks based on role permissions
const initializeChecks = () => {
    permissionChecked.value = {};
    permissions.value.forEach(permission => {
        permissionChecked.value[permission.id] = props.rolePermissions.some(rp => rp.id === permission.id);
    });

    // Check if all permissions in this group are selected
    groupChecked.value = permissions.value.every(permission =>
        permissionChecked.value[permission.id]
    );
};

// Handle group checkbox change
const handleGroupCheck = () => {
    const checked = groupChecked.value;
    permissions.value.forEach(permission => {
        permissionChecked.value[permission.id] = checked;
    });
    emitPermissionChange();
};

// Handle individual permission checkbox change
const handlePermissionCheck = () => {
    // Update group checkbox based on individual permissions
    groupChecked.value = permissions.value.every(permission =>
        permissionChecked.value[permission.id]
    );
    emitPermissionChange();
};

// Emit permission changes to parent
const emitPermissionChange = () => {
    const selectedPermissions = Object.keys(permissionChecked.value)
        .filter(id => permissionChecked.value[id])
        .map(id => parseInt(id));

    emit('permission-changed', {
        groupName: props.permissionGroup.name,
        permissions: selectedPermissions,
        allSelected: groupChecked.value,
    });
};

// Watch for allChecked prop changes
watch(() => props.allChecked, (newValue) => {
    if (permissions.value.length > 0) {
        groupChecked.value = newValue;
        permissions.value.forEach(permission => {
            permissionChecked.value[permission.id] = newValue;
        });
        emitPermissionChange();
    }
});

// Watch for role permissions changes (only on initial load)
const initialLoad = ref(true);
watch(() => props.rolePermissions, () => {
    if (permissions.value.length > 0 && initialLoad.value) {
        initializeChecks();
        emitPermissionChange();
        initialLoad.value = false;
    }
}, { deep: true });

// Format permission name
const formatPermissionName = (name) => {
    if (name.includes('.')) {
        let parts = name.split('.');
        return parts.map(part =>
            part.split('_')
                .map(word => word.charAt(0).toUpperCase() + word.slice(1))
                .join(' ')
        ).join(' ');
    } else if (name.includes('_')) {
        return name.split('_')
            .map(word => word.charAt(0).toUpperCase() + word.slice(1))
            .join(' ');
    }
    return name.charAt(0).toUpperCase() + name.slice(1);
};

onMounted(() => {
    fetchGroupPermissions();
});
</script>

<template>
    <div class="grid grid-cols-1 gap-2 bg-slate-100 px-5 rounded-lg">
        <div v-if="loading" class="p-4 bg-slate-100 rounded-lg animate-pulse">
            <div class="h-6 bg-gray-300 rounded w-1/3 mb-4"></div>
            <div class="grid grid-cols-4 gap-4">
                <div v-for="i in 4" :key="i" class="h-4 bg-gray-300 rounded"></div>
            </div>
        </div>

        <template v-else>
            <div class="col-span-3 sm:col-span-1">
                <label class="flex flex-row items-center py-3 cursor-pointer">
                    <Checkbox
                        v-model="groupChecked"
                        binary
                        @change="handleGroupCheck"
                    />
                    <span class="ml-2 text-gray-700 font-medium dark:text-dark-typography">
                        {{ permissionGroup.name }}
                    </span>
                </label>
            </div>

            <div class="col-span-3 sm:col-span-2 py-3">
                <div class="grid grid-cols-4 gap-4">
                    <label
                        v-for="permission in permissions"
                        :key="permission.id"
                        class="flex flex-row items-center cursor-pointer py-0"
                    >
                        <Checkbox
                            v-model="permissionChecked[permission.id]"
                            binary
                            class="!text-indigo-600 !focus:border-indigo-300 !focus:ring-indigo-200"
                            @change="handlePermissionCheck"
                        />
                        <span class="ml-2 text-gray-700 dark:text-dark-typography">
                            {{ formatPermissionName(permission.name) }}
                        </span>
                    </label>
                </div>
            </div>
        </template>
    </div>
</template>
