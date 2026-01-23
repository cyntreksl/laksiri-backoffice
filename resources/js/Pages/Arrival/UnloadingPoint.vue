<script setup>
import moment from "moment";
import draggable from 'vuedraggable'
import ActionMessage from "@/Components/ActionMessage.vue";
import {computed, ref, onMounted, onUnmounted} from "vue";
import {router, usePage} from "@inertiajs/vue3";
import ReviewModal from "@/Pages/Arrival/Partials/ReviewModal.vue";
import CreateUnloadingIssueModal from "@/Pages/Arrival/Partials/CreateUnloadingIssueModal.vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import Button from "primevue/button";
import { push } from 'notivue';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import Dropdown from "primevue/dropdown";
import {useConfirm} from "primevue/useconfirm";
import axios from 'axios';
import Pusher from 'pusher-js';

const props = defineProps({
    container: {
        type: Object,
        default: () => {
        }
    },
    cargoTypes: {
        type: Array,
        default: () => []
    },
    hblTypes: {
        type: Array,
        default: () => []
    },
    warehouses: {
        type: Array,
        default: () => []
    },
    packagesWithMhbl: {
        type: Array,
        default: () => []
    },
    packagesWithoutMhbl: {
        type: Array,
        default: () => []
    },
})

const searchQuery = ref('');
const containerArr = ref([]);
const mhblContainerArr = ref([]);
const warehouseArr = ref([]);
const warehouseMHBLArr = ref([]);

const groupedPackages = props.packagesWithoutMhbl
    .filter(p => p.pivot?.status !== 'draft-unload')
    .reduce((acc, p) => {
        const hbl_number = p.hbl.hbl_number;
        if (!acc[hbl_number]) {
            acc[hbl_number] = [];
        }
        acc[hbl_number].push(p);
        return acc;
    }, {});

const groupedMHBLPackages = props.packagesWithMhbl
    .filter(p => p.pivot?.status !== 'draft-unload')
    .reduce((acc, p) => {
        const mhblReference = p.hbl.mhbl.reference;
        if (!acc[mhblReference]) {
            acc[mhblReference] = [];
        }
        acc[mhblReference].push(p);
        return acc;
    }, {});

const groupedWarehousePackages = props.packagesWithoutMhbl
    .filter(p => p.pivot?.status === 'draft-unload');

const groupedWarehouseMHBLPackages = props.packagesWithMhbl
    .filter(p => p.pivot?.status === 'draft-unload').reduce((acc, p) => {
        const mhblReference = p.hbl.mhbl.reference;
        if (!acc[mhblReference]) {
            acc[mhblReference] = [];
        }
        acc[mhblReference].push(p);
        return acc;
    }, {});

containerArr.value = Object.keys(groupedPackages).map(hbl_number => {
    return {
        hbl_number: hbl_number,
        expanded: true,
        packages: groupedPackages[hbl_number]
    };
});

mhblContainerArr.value = Object.keys(groupedMHBLPackages).map(mhblReference => {
    console.log(groupedMHBLPackages[mhblReference][0]);
    return {
        mhblReference: mhblReference,
        expanded: true,
        packages: groupedMHBLPackages[mhblReference]
    };
});


warehouseArr.value = groupedWarehousePackages;
warehouseMHBLArr.value = Object.keys(groupedWarehouseMHBLPackages).map(mhblReference => {
    return {
        mhblReference: mhblReference,
        expanded: true,
        packages: groupedWarehouseMHBLPackages[mhblReference]
    };
});

const filteredPackages = computed(() => {
    if (!searchQuery.value) {
        return containerArr.value;
    }
    return containerArr.value.filter(packageData => {
        return packageData?.hbl_number.toLowerCase().includes(searchQuery.value.toLowerCase());
    });
})

const filteredMHBLPackages = computed(() => {
    if (!searchQuery.value) {
        return mhblContainerArr.value;
    }
    return mhblContainerArr.value.filter(packageData => {
        const searchLower = searchQuery.value.toLowerCase();

        // Search in MHBL number
        const mhblNumber = packageData?.packages?.[0]?.hbl?.mhbl?.hbl_number || packageData?.mhblReference || '';
        if (mhblNumber.toLowerCase().includes(searchLower)) {
            return true;
        }

        // Search in individual package HBL numbers
        return packageData?.packages?.some(pkg => {
            const hblNumber = pkg?.hbl?.hbl_number || '';
            return hblNumber.toLowerCase().includes(searchLower);
        });
    });
})


const handleUnloadToWarehouse = (groupIndex, packageIndex) => {
    if (groupIndex !== -1 && packageIndex !== -1) {
        const packageToMove = containerArr.value[groupIndex].packages.splice(packageIndex, 1)[0];
        warehouseArr.value.push(packageToMove);
        // If the group is empty after removal, remove the group
        if (containerArr.value[groupIndex].packages.length === 0) {
            containerArr.value.splice(groupIndex, 1);
        }
        handleCreateDraftUnload([packageToMove]);
    }
}

const handleUnloadHBLGroupToWarehouse = (groupIndex) => {
    if (groupIndex !== -1 && containerArr.value[groupIndex]) {
        const hblGroup = containerArr.value[groupIndex];
        const packagesToMove = [...hblGroup.packages];

        confirm.require({
            message: `Are you sure you want to unload all ${packagesToMove.length} package(s) from HBL ${hblGroup.hbl_number}?`,
            header: 'Unload All Packages',
            icon: 'pi pi-exclamation-triangle',
            rejectLabel: 'Cancel',
            rejectProps: {
                label: 'Cancel',
                severity: 'secondary',
                outlined: true
            },
            acceptProps: {
                label: 'Yes, Unload All',
                severity: 'success'
            },
            accept: async () => {
                try {
                    // Get HBL ID from first package
                    const hblId = packagesToMove[0]?.hbl_id;
                    const packageIds = packagesToMove.map(pkg => pkg.id);

                    await axios.post(route("arrival.unload-container.unload-hbl-group"), {
                        container_id: route().params.container,
                        hbl_id: hblId,
                        package_ids: packageIds,
                    });

                    // Move all packages to warehouse
                    packagesToMove.forEach(pkg => {
                        warehouseArr.value.push(pkg);
                    });

                    // Remove the group from container
                    containerArr.value.splice(groupIndex, 1);

                    push.success(`Successfully unloaded ${packagesToMove.length} package(s) from HBL ${hblGroup.hbl_number}`);
                } catch (error) {
                    console.error('Failed to unload HBL group:', error);
                    push.error('Failed to unload HBL group');
                    // Revert UI changes on error
                    router.reload({ only: ['packagesWithoutMhbl', 'packagesWithMhbl'] });
                }
            },
            reject: () => {
            }
        });
    }
}

const handleUnloadMHBLToWarehouse = (groupIndex, packageIndex) => {
    if (groupIndex !== -1 && packageIndex !== -1) {
        const packageToMove = mhblContainerArr.value[groupIndex].packages.splice(packageIndex, 1)[0];

        // Find or create MHBL group in warehouse
        const mhblReference = packageToMove.hbl.mhbl.reference;
        let warehouseMHBLGroup = warehouseMHBLArr.value.find(mhbl => mhbl.mhblReference === mhblReference);

        if (warehouseMHBLGroup) {
            warehouseMHBLGroup.packages.push(packageToMove);
        } else {
            warehouseMHBLArr.value.push({
                mhblReference: mhblReference,
                expanded: true,
                packages: [packageToMove]
            });
        }

        // If the group is empty after removal, remove the group
        if (mhblContainerArr.value[groupIndex].packages.length === 0) {
            mhblContainerArr.value.splice(groupIndex, 1);
        }

        handleCreateDraftUnload([packageToMove]);
    }
}

const handleUnloadMHBLGroupToWarehouse = (groupIndex) => {
    if (groupIndex !== -1 && mhblContainerArr.value[groupIndex]) {
        const mhblGroup = mhblContainerArr.value[groupIndex];
        const packagesToMove = [...mhblGroup.packages];
        const mhblReference = mhblGroup.mhblReference;
        const mhblNumber = packagesToMove[0]?.hbl?.mhbl?.hbl_number || mhblReference;

        confirm.require({
            message: `Are you sure you want to unload all ${packagesToMove.length} package(s) from MHBL ${mhblNumber}?`,
            header: 'Unload All Packages',
            icon: 'pi pi-exclamation-triangle',
            rejectLabel: 'Cancel',
            rejectProps: {
                label: 'Cancel',
                severity: 'secondary',
                outlined: true
            },
            acceptProps: {
                label: 'Yes, Unload All',
                severity: 'success'
            },
            accept: async () => {
                try {
                    // Get MHBL ID from first package
                    const mhblId = packagesToMove[0]?.hbl?.mhbl?.id;
                    const packageIds = packagesToMove.map(pkg => pkg.id);

                    await axios.post(route("arrival.unload-container.unload-mhbl-group"), {
                        container_id: route().params.container,
                        mhbl_id: mhblId,
                        package_ids: packageIds,
                    });

                    // Find or create MHBL group in warehouse
                    let warehouseMHBLGroup = warehouseMHBLArr.value.find(mhbl => mhbl.mhblReference === mhblReference);

                    if (warehouseMHBLGroup) {
                        warehouseMHBLGroup.packages.push(...packagesToMove);
                    } else {
                        warehouseMHBLArr.value.push({
                            mhblReference: mhblReference,
                            expanded: true,
                            packages: packagesToMove
                        });
                    }

                    // Remove the group from container
                    mhblContainerArr.value.splice(groupIndex, 1);

                    push.success(`Successfully unloaded ${packagesToMove.length} package(s) from MHBL ${mhblNumber}`);
                } catch (error) {
                    console.error('Failed to unload MHBL group:', error);
                    push.error('Failed to unload MHBL group');
                    // Revert UI changes on error
                    router.reload({ only: ['packagesWithoutMhbl', 'packagesWithMhbl'] });
                }
            },
            reject: () => {
            }
        });
    }
}

const handleReloadMHBLToContainer = (groupIndex, packageIndex) => {
    if (groupIndex !== -1 && packageIndex !== -1) {
        const packageToMove = warehouseMHBLArr.value[groupIndex].packages.splice(packageIndex, 1)[0];
        const mhblReference = packageToMove.hbl.mhbl.reference;

        // Find or create MHBL group in container
        let containerMHBLGroup = mhblContainerArr.value.find(mhbl => mhbl.mhblReference === mhblReference);

        if (containerMHBLGroup) {
            containerMHBLGroup.packages.push(packageToMove);
        } else {
            mhblContainerArr.value.push({
                mhblReference: mhblReference,
                expanded: true,
                packages: [packageToMove]
            });
        }

        // If the warehouse group is empty after removal, remove the group
        if (warehouseMHBLArr.value[groupIndex].packages.length === 0) {
            warehouseMHBLArr.value.splice(groupIndex, 1);
        }

        handleRemoveDraftUnload([packageToMove]);
    }
}
const handleReLoadToContainer = (index) => {
    if (index !== -1) {
        const packageToMove = warehouseArr.value.splice(index, 1)[0];
        const group = containerArr.value.find(g => g.hbl_number === packageToMove.hbl.hbl_number);
        if (group) {
            group.packages.push(packageToMove);
        } else {
            containerArr.value.push({
                hbl_number: packageToMove.hbl.hbl_number,
                expanded: true,
                packages: [packageToMove]
            });
        }
        handleRemoveDraftUnload([packageToMove]);
    }
}

const showReviewModal = ref(false);

const handlePackageChange = () => {
    containerArr.value = [...containerArr.value];
    warehouseArr.value = [...warehouseArr.value];
}

const draftTextEnabled = ref(false);

const handleCreateDraftUnload = async (packages) => {
    try {
        await axios.post(route("arrival.unload-container.unload"), {
            container_id: route().params.container,
            packages,
            is_draft: true,
        });

        draftTextEnabled.value = true;
        setTimeout(() => draftTextEnabled.value = false, 3000);
    } catch (error) {
        console.error('Something went to wrong!', error);
        push.error('Failed to unload package');
        // Revert UI changes on error
        router.reload({ only: ['packagesWithoutMhbl', 'packagesWithMhbl'] });
    }
}

const handleRemoveDraftUnload = async (packages) => {
    try {
        await axios.post(route("arrival.unload-container.reload"), {
            container_id: route().params.container,
            package_id: packages[0].id,
        });

        draftTextEnabled.value = true;
        setTimeout(() => draftTextEnabled.value = false, 3000);
    } catch (error) {
        console.error('Something went to wrong!', error);
        push.error('Failed to reload package');
        // Revert UI changes on error
        router.reload({ only: ['packagesWithoutMhbl', 'packagesWithMhbl'] });
    }
}

const showUnloadingIssueModal = ref(false);

const hblPackageId = ref(null);
const confirm = useConfirm();

// Detain By dropdown options
const detainByOptions = [
    { label: 'RTF', value: 'RTF' },
    { label: 'DDC', value: 'DDC' },
    { label: 'SDDC', value: 'SDDC' },
    { label: 'IAU', value: 'IAU' },
    { label: 'DC', value: 'DC' },
    { label: 'CO', value: 'CO' },
    { label: 'ICT', value: 'ICT' }
];

const showDetainModal = ref(false);
const selectedPackageForDetain = ref(null);
const selectedDetainBy = ref(null);

const openDetainModal = (packageItem) => {
    selectedPackageForDetain.value = packageItem;
    selectedDetainBy.value = null;
    showDetainModal.value = true;
}

const closeDetainModal = () => {
    showDetainModal.value = false;
    selectedPackageForDetain.value = null;
    selectedDetainBy.value = null;
}

const handleDetainPackage = () => {
    if (!selectedPackageForDetain.value || !selectedDetainBy.value) return;

    const packageId = selectedPackageForDetain.value.id;
    const detainType = selectedDetainBy.value;

    confirm.require({
        message: `Would you like to detain this package by ${detainType}?`,
        header: `Detain Package by ${detainType}?`,
        icon: 'pi pi-info-circle',
        rejectLabel: 'Cancel',
        rejectProps: {
            label: 'Cancel',
            severity: 'secondary',
            outlined: true
        },
        acceptProps: {
            label: `Sure, Detain by ${detainType}`,
            severity: 'warn'
        },
        accept: () => {
            router.post(route("hbl-packages.set.detain", packageId), { 
                detain_type: detainType,
                detain_reason: detainType
            }, {
                preserveScroll: true,
                onSuccess: () => {
                    push.success(`Package detained by ${detainType}`);
                    closeDetainModal();
                    window.location.reload();
                },
                onError: () => {
                    push.error('Something went wrong!');
                }
            })
        },
        reject: () => {
        }
    })
}

const isPackageDetained = (packageItem) => {
    if (!packageItem) return false;

    // Check latest detain record (Laravel serializes relationships to snake_case in JSON)
    const latestRecord = packageItem.latest_detain_record || packageItem.latestDetainRecord;

    // Package is detained if latest record exists and is_rtf is true
    return latestRecord && latestRecord.is_rtf === true;
}

const isHBLGroupDetained = (hblGroup) => {
    return hblGroup?.packages?.some(pkg => isPackageDetained(pkg));
}

const isMHBLGroupDetained = (mhblGroup) => {
    return mhblGroup?.packages?.some(pkg => isPackageDetained(pkg));
}

const confirmShowCreateIssueModal = (packageId) => {
    hblPackageId.value = packageId;
    showUnloadingIssueModal.value = true;
}

const confirmShowMHBLCreateIssueModal = (packageID) => {
    hblPackageId.value = packageID;
    showUnloadingIssueModal.value = true;
}

const reviewContainer = () => {
    showReviewModal.value = true
}
const isRemarkVisible = ref(false);
const selectedPackage = ref(null);
const remarks = ref([]);
const newRemark = ref('');
const loading = ref(false);
const fetching = ref(false);

const openRemarksDialog = (pkg) => {
    selectedPackage.value = pkg;
    isRemarkVisible.value = true;
    fetchRemarks();
};

const closeRemarksDialog = () => {
    isRemarkVisible.value = false;
    selectedPackage.value = null;
    remarks.value = [];
    newRemark.value = '';
};

const fetchRemarks = async () => {
    if (!selectedPackage.value) return;
    fetching.value = true;

    try {
        const { data } = await axios.get(`/remarks/package/${selectedPackage.value.id}`);
        remarks.value = data.data || data;
    } catch (error) {
        console.error('Error fetching remarks:', error);
        push.error('Failed to load remarks');
    } finally {
        fetching.value = false;
    }
};

const addRemark = async () => {
    if (!newRemark.value.trim() || !selectedPackage.value) return;
    loading.value = true;

    try {
        // Send the remark to the server
        await axios.post(`/hbl-packages/${selectedPackage.value.id}/remarks`, {
            body: newRemark.value
        });

        // Clear input
        newRemark.value = '';

        // Refetch remarks from database
        await fetchRemarks();

        push.success('Remark added successfully');
    } catch (error) {
        console.error('Error adding remark:', error);
        push.error('Failed to add remark');
    } finally {
        loading.value = false;
    }
};

const formatDate = (dateString) => {
    if (!dateString) return '';

    try {
        const date = new Date(dateString.includes(' ') ? dateString.replace(' ', 'T') : dateString);
        return date.toLocaleString();
    } catch (error) {
        console.error('Error formatting date:', error);
        return dateString;
    }
};

// Pusher real-time updates
let pusher = null;
let channel = null;

const page = usePage();

const initializePusher = () => {
    const pusherConfig = page.props.pusher;

    if (!pusherConfig || !pusherConfig.key) {
        console.warn('Pusher not configured');
        return;
    }

    pusher = new Pusher(pusherConfig.key, {
        cluster: pusherConfig.cluster,
        forceTLS: pusherConfig.forceTLS,
    });

    channel = pusher.subscribe(`container.${props.container.id}`);

    // Listen for package unload events
    channel.bind('package.unload', (data) => {
        // Skip if this is our own action (we already updated the UI)
        if (data.user_id === page.props.auth.user.id) {
            return;
        }

        // Debug: log the received data
        console.log('Package unload event data:', {
            user_id: data.user_id,
            user_name: data.user_name,
            action: data.action
        });

        handleRealTimeUnload(data.package, data.user_name);
    });

    // Listen for package reload events
    channel.bind('package.reload', (data) => {
        // Skip if this is our own action (we already updated the UI)
        if (data.user_id === page.props.auth.user.id) {
            return;
        }

        handleRealTimeReload(data.package, data.user_name);
    });
};

const handleRealTimeUnload = (packageData, userName) => {
    const packageId = packageData.id;
    const hblNumber = packageData.hbl?.hbl_number;
    const mhblReference = packageData.hbl?.mhbl?.reference;

    // Handle regular HBL packages
    if (!mhblReference) {
        // Find and remove from container
        let found = false;
        for (let i = 0; i < containerArr.value.length; i++) {
            const group = containerArr.value[i];
            const packageIndex = group.packages.findIndex(p => p.id === packageId);

            if (packageIndex !== -1) {
                const packageToMove = group.packages.splice(packageIndex, 1)[0];
                warehouseArr.value.push(packageToMove);

                // Remove group if empty
                if (group.packages.length === 0) {
                    containerArr.value.splice(i, 1);
                }
                found = true;
                break;
            }
        }

        if (!found) {
            // Package might be in a different state, but don't refresh - let Pusher handle it
            console.warn('Package not found in container array for real-time update');
        }
    } else {
        // Handle MHBL packages
        let found = false;
        for (let i = 0; i < mhblContainerArr.value.length; i++) {
            const group = mhblContainerArr.value[i];
            const packageIndex = group.packages.findIndex(p => p.id === packageId);

            if (packageIndex !== -1) {
                const packageToMove = group.packages.splice(packageIndex, 1)[0];

                // Find or create MHBL group in warehouse
                let warehouseMHBLGroup = warehouseMHBLArr.value.find(mhbl => mhbl.mhblReference === mhblReference);

                if (warehouseMHBLGroup) {
                    warehouseMHBLGroup.packages.push(packageToMove);
                } else {
                    warehouseMHBLArr.value.push({
                        mhblReference: mhblReference,
                        expanded: true,
                        packages: [packageToMove]
                    });
                }

                // Remove group if empty
                if (group.packages.length === 0) {
                    mhblContainerArr.value.splice(i, 1);
                }
                found = true;
                break;
            }
        }

        if (!found) {
            // Package might be in a different state, but don't refresh - let Pusher handle it
            console.warn('Package not found in MHBL container array for real-time update');
        }
    }

    const displayName = userName && userName.trim() ? userName : 'another user';
    push.info(`Package unloaded by ${displayName}`);
};

const handleRealTimeReload = (packageData, userName) => {
    const packageId = packageData.id;
    const hblNumber = packageData.hbl?.hbl_number;
    const mhblReference = packageData.hbl?.mhbl?.reference;

    // Handle regular HBL packages
    if (!mhblReference) {
        // Find and remove from warehouse
        const warehouseIndex = warehouseArr.value.findIndex(p => p.id === packageId);

        if (warehouseIndex !== -1) {
            const packageToMove = warehouseArr.value.splice(warehouseIndex, 1)[0];
            const group = containerArr.value.find(g => g.hbl_number === hblNumber);

            if (group) {
                group.packages.push(packageToMove);
            } else {
                containerArr.value.push({
                    hbl_number: hblNumber,
                    expanded: true,
                    packages: [packageToMove]
                });
            }
        } else {
            // Package might be in a different state, but don't refresh - let Pusher handle it
            console.warn('Package not found in warehouse array for real-time reload');
        }
    } else {
        // Handle MHBL packages
        let found = false;
        for (let i = 0; i < warehouseMHBLArr.value.length; i++) {
            const group = warehouseMHBLArr.value[i];
            const packageIndex = group.packages.findIndex(p => p.id === packageId);

            if (packageIndex !== -1) {
                const packageToMove = group.packages.splice(packageIndex, 1)[0];

                // Find or create MHBL group in container
                let containerMHBLGroup = mhblContainerArr.value.find(mhbl => mhbl.mhblReference === mhblReference);

                if (containerMHBLGroup) {
                    containerMHBLGroup.packages.push(packageToMove);
                } else {
                    mhblContainerArr.value.push({
                        mhblReference: mhblReference,
                        expanded: true,
                        packages: [packageToMove]
                    });
                }

                // Remove group if empty
                if (group.packages.length === 0) {
                    warehouseMHBLArr.value.splice(i, 1);
                }
                found = true;
                break;
            }
        }

        if (!found) {
            // Package might be in a different state, but don't refresh - let Pusher handle it
            console.warn('Package not found in MHBL warehouse array for real-time reload');
        }
    }

    const displayName = userName && userName.trim() ? userName : 'another user';
    push.info(`Package reloaded to container by ${displayName}`);
};

onMounted(() => {
    initializePusher();
});

onUnmounted(() => {
    if (channel) {
        channel.unbind_all();
        pusher?.unsubscribe(`container.${props.container.id}`);
    }
    if (pusher) {
        pusher.disconnect();
    }
});

</script>

<template>
    <AppLayout title="Unloading Point">
        <template #header>Unloading Point</template>

        <main class="kanban-app w-full">
            <div
                class="flex items-center justify-between space-x-2 px-[var(--margin-x)] py-5 transition-all duration-[.25s]">
                <div class="flex items-center space-x-1">
                    <h3 class="text-xl font-medium text-slate-700 line-clamp-1 dark:text-navy-50">
                        Unloading Point
                    </h3>
                </div>
                <div class="flex space-x-5 items-center">
                    <ActionMessage :on="draftTextEnabled">
                        <div class="flex">
                            <svg class="size-5 mr-2 icon icon-tabler icons-tabler-outline icon-tabler-file-report"
                                 fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                 stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                                <path d="M17 17m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0"/>
                                <path d="M17 13v4h4"/>
                                <path d="M12 3v4a1 1 0 0 0 1 1h4"/>
                                <path d="M11.5 21h-6.5a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v2m0 3v4"/>
                            </svg>
                            Saved as draft.
                        </div>
                    </ActionMessage>
                    <Button
                        icon="pi pi-history"
                        label="View Audit Logs"
                        outlined
                        severity="secondary"
                        size="small"
                        @click.prevent="() => $inertia.visit(route('arrival.containers.audit-logs.index', container.id))"
                    />
                    <Button :disabled="warehouseArr.length === 0 && warehouseMHBLArr.length === 0 "
                            icon="pi pi-arrow-right" icon-pos="right" label="Proceed to Review"
                            size="small" @click.prevent="reviewContainer"/>
                </div>
            </div>

            <div class="px-[var(--margin-x)] mb-3">
                <label class="relative hidden w-full max-w-[16rem] sm:flex">
                    <input
                        v-model="searchQuery"
                        class="form-input peer h-8 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 pl-9 text-sm placeholder:text-slate-400/70 hover:z-10 hover:border-slate-400 dark:border-navy-450 dark:hover:border-navy-400 focus:ring-0 disabled:pointer-events-none disabled:select-none disabled:border-none disabled:bg-zinc-100"
                        placeholder="Search on HBL Packages" type="text"/>
                    <span
                        class="pointer-events-none absolute flex h-full w-9 items-center justify-center text-slate-400 peer-focus:text-primary dark:text-navy-300 dark:peer-focus:text-accent">
                    <svg class="size-4 transition-colors duration-200" fill="currentColor"
                         viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M3.316 13.781l.73-.171-.73.171zm0-5.457l.73.171-.73-.171zm15.473 0l.73-.171-.73.171zm0 5.457l.73.171-.73-.171zm-5.008 5.008l-.171-.73.171.73zm-5.457 0l-.171.73.171-.73zm0-15.473l-.171-.73.171.73zm5.457 0l.171-.73-.171.73zM20.47 21.53a.75.75 0 101.06-1.06l-1.06 1.06zM4.046 13.61a11.198 11.198 0 010-5.115l-1.46-.342a12.698 12.698 0 000 5.8l1.46-.343zm14.013-5.115a11.196 11.196 0 010 5.115l1.46.342a12.698 12.698 0 000-5.8l-1.46.343zm-4.45 9.564a11.196 11.196 0 01-5.114 0l-.342 1.46c1.907.448 3.892.448 5.8 0l-.343-1.46zM8.496 4.046a11.198 11.198 0 015.115 0l.342-1.46a12.698 12.698 0 00-5.8 0l.343 1.46zm0 14.013a5.97 5.97 0 01-4.45-4.45l-1.46.343a7.47 7.47 0 005.568 5.568l.342-1.46zm5.457 1.46a7.47 7.47 0 005.568-5.567l-1.46-.342a5.97 5.97 0 01-4.45 4.45l.342 1.46zM13.61 4.046a5.97 5.97 0 014.45 4.45l1.46-.343a7.47 7.47 0 00-5.568-5.567l-.342 1.46zm-5.457-1.46a7.47 7.47 0 00-5.567 5.567l1.46.342a5.97 5.97 0 014.45-4.45l-.343-1.46zm8.652 15.28l3.665 3.664 1.06-1.06-3.665-3.665-1.06 1.06z"/>
                    </svg>
                </span>
                </label>
            </div>

            <div class="flex h-[calc(100vh-8.5rem)] flex-grow flex-col">
                <div
                    class="grid grid-cols-1 sm:grid-cols-2 space-x-4 px-[var(--margin-x)] transition-all duration-[.25s]">
                    <div class="relative flex flex-col h-full overflow-hidden">
                        <div class="board-draggable-handler flex items-center justify-between px-0.5 pb-3">
                            <div class="flex items-center space-x-2">
                                <div
                                    class="flex size-8 items-center justify-center rounded-lg bg-warning/10 text-warning">
                                    <svg
                                        class="icon icon-tabler icons-tabler-outline icon-tabler-tir"
                                        fill="none"
                                        height="24"
                                        stroke="currentColor"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        viewBox="0 0 24 24"
                                        width="24"
                                        xmlns="http://www.w3.org/2000/svg"
                                    >
                                        <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                                        <path d="M5 17m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/>
                                        <path d="M17 17m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/>
                                        <path d="M7 18h8m4 0h2v-6a5 7 0 0 0 -5 -7h-1l1.5 7h4.5"/>
                                        <path d="M12 18v-13h3"/>
                                        <path d="M3 17l0 -5l9 0"/>
                                    </svg>
                                </div>
                                <h3 class="text-lg text-slate-700 dark:text-navy-100">
                                    {{ container.cargo_type }} Container ({{ container?.reference }})
                                </h3>
                            </div>
                            <div>
                                <h3 class="text-lg text-slate-700 dark:text-navy-100">
                                    {{ container.container_type }}
                                </h3>
                            </div>
                        </div>
                        <div class=" pr-2 pb-4">
                            <ul v-if="Object.keys(filteredPackages).length > 0"
                                class="space-y-1 font-inter font-medium">
                                <li v-for="(hbl, groupIndex) in filteredPackages" :key="hbl.id">
                                    <div
                                        v-if="Object.keys(hbl.packages).length > 0"
                                        :class="[
                                            'mb-2 rounded-lg border p-2.5 shadow-sm transition-all',
                                            isHBLGroupDetained(hbl)
                                                ? 'border-red-300 bg-red-50 hover:bg-red-100 hover:border-red-400 dark:border-red-700 dark:bg-red-900/20 dark:hover:bg-red-900/30'
                                                : 'border-slate-200 bg-white hover:border-success/40 hover:shadow dark:border-navy-600 dark:bg-navy-800'
                                        ]"
                                    >
                                        <div class="flex items-center justify-between gap-3">
                                            <div class="flex items-center gap-2 flex-1 min-w-0">
                                        <button
                                                    class="flex-shrink-0 size-6 rounded p-0.5 hover:bg-slate-100 dark:hover:bg-navy-700 transition-colors"
                                            @click="hbl.expanded = !hbl.expanded"
                                        >
                                            <svg
                                                :class="hbl.expanded && 'rotate-90'"
                                                        class="size-4 text-slate-600 dark:text-navy-300 transition-transform duration-200"
                                                fill="currentColor"
                                                viewBox="0 0 20 20"
                                                xmlns="http://www.w3.org/2000/svg"
                                            >
                                                <path
                                                    clip-rule="evenodd"
                                                    d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                                    fill-rule="evenodd"
                                                ></path>
                                            </svg>
                                        </button>
                                        <svg
                                                    class="flex-shrink-0 size-6 text-primary"
                                            fill="currentColor"
                                            viewBox="0 0 20 20"
                                            xmlns="http://www.w3.org/2000/svg"
                                        >
                                            <path
                                                d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"
                                            ></path>
                                        </svg>
                                                <span class="font-semibold text-base text-slate-700 dark:text-navy-100 truncate">{{ hbl.hbl_number }}</span>
                                                <span v-if="isHBLGroupDetained(hbl)" class="flex-shrink-0">
                                                    <i class="pi pi-lock text-red-600 dark:text-red-400 text-sm"></i>
                                                </span>
                                                <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-sm font-medium text-slate-600 dark:bg-navy-700 dark:text-navy-300">
                                                    {{ hbl.packages.length }} {{ hbl.packages.length === 1 ? 'pkg' : 'pkgs' }}
                                                </span>
                                    </div>
                                            <Button
                                                icon="pi pi-arrow-right"
                                                label="Unload All"
                                                severity="success"
                                                size="small"
                                                class="flex-shrink-0"
                                                @click.stop.prevent="handleUnloadHBLGroupToWarehouse(groupIndex)"
                                            />
                                        </div>
                                    </div>
                                    <ul v-show="hbl.expanded" class="mt-1.5 space-y-1.5 pl-1">
                                        <draggable v-model="hbl.packages"
                                                   class="space-y-1.5"
                                                   group="people"
                                                   item-key="id"
                                                   @change="handlePackageChange">
                                            <template #item="{element, index}">
                                                <div :class="[
                                                    'flex items-center justify-between rounded-md border p-2 transition-colors',
                                                    isPackageDetained(element)
                                                        ? 'border-red-300 bg-red-50 hover:bg-red-100 dark:border-red-700 dark:bg-red-900/20 dark:hover:bg-red-900/30'
                                                        : 'border-slate-200 bg-slate-50 hover:bg-slate-100 dark:border-navy-600 dark:bg-navy-700/50 dark:hover:bg-navy-700'
                                                ]">
                                                    <div class="flex items-center gap-3 flex-1 min-w-0">
                                                        <div v-if="element.unloading_issue.length > 0" class="flex-shrink-0">
                                                            <i class="pi pi-exclamation-triangle text-warning text-sm"></i>
                                                        </div>
                                                        <div v-if="isPackageDetained(element)" class="flex-shrink-0">
                                                            <i class="pi pi-lock text-red-600 dark:text-red-400 text-sm"></i>
                                                        </div>
                                                        <div class="flex-1 min-w-0">
                                                            <p class="text-sm font-medium text-slate-600 dark:text-navy-300 mb-1">
                                                                {{ element.package_type }}
                                                            </p>
                                                            <div class="flex items-center gap-2 text-sm text-slate-500 dark:text-navy-400">
                                                                <span>Vol: {{ element.volume }}</span>
                                                                <span>•</span>
                                                                <span>Wt: {{ element.weight }}</span>
                                                                <span>•</span>
                                                                <span>Qty: {{ element.quantity }}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="flex items-center gap-1.5 flex-shrink-0">
                                                        <Button
                                                            v-if="element.unloading_issue.length === 0"
                                                            v-tooltip.top="'Create Unloading Issue'"
                                                            class="!p-1.5"
                                                            icon="pi pi-exclamation-triangle"
                                                            rounded
                                                            severity="warn"
                                                            size="small"
                                                            text
                                                            @click.prevent="confirmShowCreateIssueModal(index)"
                                                        />
                                                        <i v-else class="pi pi-exclamation-triangle text-warning text-sm"></i>
                                                        <Button
                                                            icon="pi pi-comment"
                                                            severity="secondary"
                                                            size="small"
                                                            text
                                                            rounded
                                                            class="!p-1.5"
                                                            @click="openRemarksDialog(element)"
                                                        />
                                                        <Button
                                                            icon="pi pi-arrow-right"
                                                            severity="success"
                                                            size="small"
                                                            text
                                                            rounded
                                                            class="!p-1.5"
                                                            v-tooltip.top="'Unload Package'"
                                                            @click.prevent="handleUnloadToWarehouse(groupIndex, index)"
                                                        />
                                                    </div>
                                                </div>
                                            </template>
                                        </draggable>
                                    </ul>
                                </li>
                            </ul>

                            <div v-else
                                 class="cursor-pointer border-2 border-error/20 bg-error/10 rounded-lg border-dashed">
                                <div class="flex justify-center items-center space-x-3 px-2.5 pb-2 pt-1.5 h-24">
                                    <div class="text-center">
                                        <p
                                            class="font-medium text-xl tracking-wide text-slate-400 line-clamp-2 dark:text-navy-100">
                                            Sorry! Not Found HBL Packages.
                                        </p>

                                        <p class="mt-px text-sm text-slate-400 dark:text-navy-300">
                                            Please add HBL records first.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="board-draggable-handler flex items-center justify-between px-0.5 pt-0 pb-3">
                            <div class="flex items-center space-x-2">
                                <div class="flex size-8 items-center justify-center rounded-lg bg-info/10 text-info">
                                    <i class="fa fa-boxes-packing text-base"></i>
                                </div>
                                <h3 class="text-lg text-slate-700 dark:text-navy-100">
                                    MHBL Packages
                                </h3>
                            </div>
                        </div>

                        <div class="flex-1  pr-2">
                            <ul v-if="Object.keys(filteredMHBLPackages).length > 0"
                                class="space-y-1 font-inter font-medium">
                                <li v-for="(pkg, groupIndex) in filteredMHBLPackages" :key="pkg.mhblReference">
                                    <div
                                        v-if="Object.keys(pkg.packages).length > 0"
                                        :class="[
                                            'mb-2 rounded-lg border p-2.5 shadow-sm transition-all',
                                            isMHBLGroupDetained(pkg)
                                                ? 'border-red-300 bg-red-50 hover:bg-red-100 hover:border-red-400 dark:border-red-700 dark:bg-red-900/20 dark:hover:bg-red-900/30'
                                                : 'border-slate-200 bg-white hover:border-info/40 hover:shadow dark:border-navy-600 dark:bg-navy-800'
                                        ]"
                                    >
                                        <div class="flex items-center justify-between gap-3">
                                            <div class="flex items-center gap-2 flex-1 min-w-0">
                                        <button
                                                    class="flex-shrink-0 size-6 rounded p-0.5 hover:bg-slate-100 dark:hover:bg-navy-700 transition-colors"
                                            @click="pkg.expanded = !pkg.expanded"
                                        >
                                            <svg
                                                :class="pkg.expanded && 'rotate-90'"
                                                        class="size-4 text-slate-600 dark:text-navy-300 transition-transform duration-200"
                                                fill="currentColor"
                                                viewBox="0 0 20 20"
                                                xmlns="http://www.w3.org/2000/svg"
                                            >
                                                <path
                                                    clip-rule="evenodd"
                                                    d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                                    fill-rule="evenodd"
                                                ></path>
                                            </svg>
                                        </button>
                                        <svg
                                                    class="flex-shrink-0 size-6 text-info"
                                            fill="currentColor"
                                            viewBox="0 0 20 20"
                                            xmlns="http://www.w3.org/2000/svg"
                                        >
                                            <path
                                                d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"
                                            ></path>
                                        </svg>
                                                <span class="font-semibold text-base text-slate-700 dark:text-navy-100 truncate">{{ pkg.packages[0].hbl.mhbl.hbl_number || pkg.mhblReference }}</span>
                                                <span v-if="isMHBLGroupDetained(pkg)" class="flex-shrink-0">
                                                    <i class="pi pi-lock text-red-600 dark:text-red-400 text-sm"></i>
                                                </span>
                                                <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-sm font-medium text-slate-600 dark:bg-navy-700 dark:text-navy-300">
                                                    {{ pkg.packages.length }} {{ pkg.packages.length === 1 ? 'pkg' : 'pkgs' }}
                                                </span>
                                    </div>
                                            <Button
                                                icon="pi pi-arrow-right"
                                                label="Unload All"
                                                severity="success"
                                                size="small"
                                                class="flex-shrink-0"
                                                @click.stop.prevent="handleUnloadMHBLGroupToWarehouse(groupIndex)"
                                            />
                                                                </div>
                                                            </div>
                                    <ul v-show="pkg.expanded" class="mt-1.5 space-y-1.5 pl-1">
                                        <div v-for="(element, index) in pkg.packages" :key="element.id">
                                            <div :class="[
                                                'flex items-center justify-between rounded-md border p-2 transition-colors',
                                                isPackageDetained(element)
                                                    ? 'border-red-300 bg-red-50 hover:bg-red-100 dark:border-red-700 dark:bg-red-900/20 dark:hover:bg-red-900/30'
                                                    : 'border-slate-200 bg-slate-50 hover:bg-slate-100 dark:border-navy-600 dark:bg-navy-700/50 dark:hover:bg-navy-700'
                                            ]">
                                                <div class="flex items-center gap-3 flex-1 min-w-0">
                                                    <div v-if="isPackageDetained(element)" class="flex-shrink-0">
                                                        <i class="pi pi-lock text-red-600 dark:text-red-400 text-sm"></i>
                                                    </div>
                                                    <div class="flex-1 min-w-0">
                                                        <p class="text-xs font-medium text-slate-600 dark:text-navy-300 mb-1">
                                                            {{ element.hbl.hbl_number }} • {{ element.package_type }}
                                                        </p>
                                                        <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-navy-400">
                                                            <span>Vol: {{ element.volume }}</span>
                                                            <span>•</span>
                                                            <span>Wt: {{ element.weight }}</span>
                                                            <span>•</span>
                                                            <span>Qty: {{ element.quantity }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="flex items-center gap-1.5 flex-shrink-0">
                                                    <Button
                                                        icon="pi pi-comment"
                                                        severity="secondary"
                                                        size="small"
                                                        text
                                                        rounded
                                                        class="!p-1.5"
                                                        @click="openRemarksDialog(element)"
                                                    />
                                                    <Button
                                                        icon="pi pi-arrow-right"
                                                        severity="success"
                                                        size="small"
                                                        text
                                                        rounded
                                                        class="!p-1.5"
                                                        v-tooltip.top="'Unload Package'"
                                                        @click.prevent="handleUnloadMHBLToWarehouse(groupIndex, index)"
                                                    />
                                                </div>
                                            </div>
                                        </div>
                                    </ul>
                                </li>
                            </ul>

                            <div v-else
                                 class="cursor-pointer border-2 border-error/20 bg-error/10 rounded-lg border-dashed">
                                <div class="flex justify-center items-center space-x-3 px-2.5 pb-2 pt-1.5 h-24">
                                    <div class="text-center">
                                        <p
                                            class="font-medium text-xl tracking-wide text-slate-400 line-clamp-2 dark:text-navy-100">
                                            Sorry! Not Found MHBL Packages.
                                        </p>

                                        <p class="mt-px text-sm text-slate-400 dark:text-navy-300">
                                            Please add HBL records first.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="relative flex flex-col h-full overflow-hidden">
                        <div class="board-draggable-handler flex items-center justify-between px-0.5 pb-3">
                            <div class="flex items-center space-x-2">
                                <div
                                    class="flex size-8 items-center justify-center rounded-lg bg-success/10 text-success">
                                    <svg
                                        class="icon icon-tabler icons-tabler-outline icon-tabler-building-warehouse"
                                        fill="none"
                                        height="24"
                                        stroke="currentColor"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        viewBox="0 0 24 24"
                                        width="24"
                                        xmlns="http://www.w3.org/2000/svg"
                                    >
                                        <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                                        <path d="M3 21v-13l9 -4l9 4v13"/>
                                        <path d="M13 13h4v8h-10v-6h6"/>
                                        <path d="M13 21v-9a1 1 0 0 0 -1 -1h-2a1 1 0 0 0 -1 1v3"/>
                                    </svg>
                                </div>
                                <h3 class="text-lg text-slate-700 dark:text-navy-100">
                                    Warehouse
                                </h3>
                            </div>
                        </div>
                        <div class="flex-1 pr-2 space-y-2">
                            <draggable
                                v-if="warehouseArr.length > 0"
                                v-model="warehouseArr"
                                class="space-y-1.5"
                                group="people"
                                item-key="id"
                                @change="handlePackageChange"
                            >
                                <template #item="{element, index}">
                                    <div :class="[
                                        'flex items-center justify-between rounded-md border p-2 transition-colors',
                                        isPackageDetained(element)
                                            ? 'border-red-300 bg-red-50 hover:bg-red-100 dark:border-red-700 dark:bg-red-900/20 dark:hover:bg-red-900/30'
                                            : 'border-slate-200 bg-slate-50 hover:bg-slate-100 dark:border-navy-600 dark:bg-navy-700/50 dark:hover:bg-navy-700'
                                    ]">
                                        <div class="flex items-center gap-3 flex-1 min-w-0">
                                            <div v-if="element.unloading_issue.length > 0" class="flex-shrink-0">
                                                <i class="pi pi-exclamation-triangle text-warning text-sm"></i>
                                            </div>
                                            <div v-if="isPackageDetained(element)" class="flex-shrink-0">
                                                <i class="pi pi-lock text-red-600 dark:text-red-400 text-sm"></i>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-medium text-slate-600 dark:text-navy-300 mb-1">
                                                    {{ element.hbl?.hbl_number }} • {{ element.package_type }}
                                                </p>
                                                <div class="flex items-center gap-2 text-sm text-slate-500 dark:text-navy-400">
                                                    <span>Vol: {{ element.volume }}</span>
                                                    <span>•</span>
                                                    <span>Wt: {{ element.weight }}</span>
                                                    <span>•</span>
                                                    <span>Qty: {{ element.quantity }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-1.5 flex-shrink-0">
                                            <Button
                                                v-if="element.unloading_issue.length === 0"
                                                v-tooltip.top="'Create Unloading Issue'"
                                                class="!p-1.5"
                                                icon="pi pi-exclamation-triangle"
                                                rounded
                                                severity="warn"
                                                size="small"
                                                text
                                                @click.prevent="confirmShowCreateIssueModal(element.id)"
                                            />
                                            <i v-else class="pi pi-exclamation-triangle text-warning text-sm"></i>
                                            <Button
                                                v-if="element.unloading_issue.length === 0"
                                                icon="pi pi-lock"
                                                severity="warn"
                                                size="small"
                                                text
                                                rounded
                                                class="!p-1.5"
                                                v-tooltip.top="'Detain Package'"
                                                @click.prevent="openDetainModal(element)"
                                            />
                                            <Button
                                                icon="pi pi-comment"
                                                severity="secondary"
                                                size="small"
                                                text
                                                rounded
                                                class="!p-1.5"
                                                @click="openRemarksDialog(element)"
                                            />
                                            <Button
                                                icon="pi pi-arrow-left"
                                                severity="danger"
                                                size="small"
                                                text
                                                rounded
                                                class="!p-1.5"
                                                v-tooltip.top="'Reload to Container'"
                                                @click.prevent="handleReLoadToContainer(index)"
                                            />
                                        </div>
                                    </div>
                                </template>
                            </draggable>

                            <ul v-if="warehouseMHBLArr.length > 0" class="space-y-1.5 font-inter font-medium">
                                <li v-for="(mhbl, groupIndex) in warehouseMHBLArr" :key="mhbl.mhblReference">
                                    <div
                                        v-if="mhbl.packages.length > 0"
                                        :class="[
                                            'mb-2 rounded-lg border p-2.5 shadow-sm transition-all',
                                            isMHBLGroupDetained(mhbl)
                                                ? 'border-red-300 bg-red-50 hover:bg-red-100 hover:border-red-400 dark:border-red-700 dark:bg-red-900/20 dark:hover:bg-red-900/30'
                                                : 'border-slate-200 bg-white hover:border-info/40 hover:shadow dark:border-navy-600 dark:bg-navy-800'
                                        ]"
                                    >
                                        <div class="flex items-center justify-between gap-3">
                                            <div class="flex items-center gap-2 flex-1 min-w-0">
                                        <button
                                                    class="flex-shrink-0 size-6 rounded p-0.5 hover:bg-slate-100 dark:hover:bg-navy-700 transition-colors"
                                            @click="mhbl.expanded = !mhbl.expanded"
                                        >
                                            <svg
                                                :class="mhbl.expanded && 'rotate-90'"
                                                        class="size-4 text-slate-600 dark:text-navy-300 transition-transform duration-200"
                                                fill="currentColor"
                                                viewBox="0 0 20 20"
                                                xmlns="http://www.w3.org/2000/svg"
                                            >
                                                <path
                                                    clip-rule="evenodd"
                                                    d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                                    fill-rule="evenodd"
                                                ></path>
                                            </svg>
                                        </button>
                                        <svg
                                                    class="flex-shrink-0 size-6 text-info"
                                            fill="currentColor"
                                            viewBox="0 0 20 20"
                                            xmlns="http://www.w3.org/2000/svg"
                                        >
                                            <path
                                                d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"
                                            ></path>
                                        </svg>
                                                <span class="font-semibold text-base text-slate-700 dark:text-navy-100 truncate">{{ mhbl.packages[0].hbl.mhbl.hbl_number || mhbl.mhblReference }}</span>
                                                <span v-if="isMHBLGroupDetained(mhbl)" class="flex-shrink-0">
                                                    <i class="pi pi-lock text-red-600 dark:text-red-400 text-sm"></i>
                                                </span>
                                                <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-sm font-medium text-slate-600 dark:bg-navy-700 dark:text-navy-300">
                                                    {{ mhbl.packages.length }} {{ mhbl.packages.length === 1 ? 'pkg' : 'pkgs' }}
                                                </span>
                                    </div>
                                        </div>
                                    </div>
                                    <ul v-show="mhbl.expanded" class="mt-1.5 space-y-1.5 pl-1">
                                        <div v-for="(element, index) in mhbl.packages" :key="element.id">
                                            <div :class="[
                                                'flex items-center justify-between rounded-md border p-2 transition-colors',
                                                isPackageDetained(element)
                                                    ? 'border-red-300 bg-red-50 hover:bg-red-100 dark:border-red-700 dark:bg-red-900/20 dark:hover:bg-red-900/30'
                                                    : 'border-slate-200 bg-slate-50 hover:bg-slate-100 dark:border-navy-600 dark:bg-navy-700/50 dark:hover:bg-navy-700'
                                            ]">
                                                <div class="flex items-center gap-3 flex-1 min-w-0">
                                                    <div v-if="isPackageDetained(element)" class="flex-shrink-0">
                                                        <i class="pi pi-lock text-red-600 dark:text-red-400 text-sm"></i>
                                                    </div>
                                                    <div class="flex-1 min-w-0">
                                                        <p class="text-xs font-medium text-slate-600 dark:text-navy-300 mb-1">
                                                            {{ element.hbl.hbl_number }} • {{ element.package_type }}
                                                        </p>
                                                        <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-navy-400">
                                                            <span>Vol: {{ element.volume }}</span>
                                                            <span>•</span>
                                                            <span>Wt: {{ element.weight }}</span>
                                                            <span>•</span>
                                                            <span>Qty: {{ element.quantity }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="flex items-center gap-1.5 flex-shrink-0">
                                                    <Button
                                                        v-if="element.unloading_issue.length === 0"
                                                        icon="pi pi-exclamation-triangle"
                                                        severity="warn"
                                                        size="small"
                                                        text
                                                        rounded
                                                        class="!p-1.5"
                                                        v-tooltip.top="'Create Unloading Issue'"
                                                        @click.prevent="confirmShowMHBLCreateIssueModal(element.id)"
                                                    />
                                                    <i v-else class="pi pi-exclamation-triangle text-warning text-sm"></i>
                                                    <Button
                                                        v-if="element.unloading_issue.length === 0"
                                                        icon="pi pi-lock"
                                                        severity="warn"
                                                        size="small"
                                                        text
                                                        rounded
                                                        class="!p-1.5"
                                                        v-tooltip.top="'Detain Package'"
                                                        @click.prevent="openDetainModal(element)"
                                                    />
                                                    <Button
                                                        icon="pi pi-comment"
                                                        severity="secondary"
                                                        size="small"
                                                        text
                                                        rounded
                                                        class="!p-1.5"
                                                        @click="openRemarksDialog(element)"
                                                    />
                                                    <Button
                                                        icon="pi pi-arrow-left"
                                                        severity="danger"
                                                        size="small"
                                                        text
                                                        rounded
                                                        class="!p-1.5"
                                                        v-tooltip.top="'Reload to Container'"
                                                        @click.prevent="handleReloadMHBLToContainer(groupIndex, index)"
                                                    />
                                                </div>
                                            </div>
                                        </div>
                                    </ul>
                                </li>
                            </ul>

                            <div v-if="warehouseArr.length === 0 && warehouseMHBLArr.length === 0"
                                 class="cursor-pointer border-2 rounded-lg border-dashed">
                                <div class="flex justify-center items-center space-x-3 px-2.5 pb-2 pt-1.5 h-24">
                                    <div class="text-center">
                                        <p
                                            class="font-medium text-xl tracking-wide text-slate-400 line-clamp-2 dark:text-navy-100">
                                            Warehouse
                                        </p>

                                        <p class="mt-px text-sm text-slate-400 dark:text-navy-300">
                                            Active to unloading process
                                        </p>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </main>

        <ReviewModal
            :visible="showReviewModal"
            :container="container"
            :warehouse-array="warehouseArr"
            :warehouseMHBLs="warehouseMHBLArr"
            :packages-without-mhbl="packagesWithoutMhbl"
            :packages-with-mhbl="packagesWithMhbl"
            @close="showReviewModal = false"
            @update:visible="showReviewModal = $event"
        />

        <CreateUnloadingIssueModal :hbl-package-id="hblPackageId" :visible="showUnloadingIssueModal"
                                   @close="showUnloadingIssueModal = false"
                                   @update:visible="showUnloadingIssueModal = $event"/>

        <Dialog
            v-model:visible="showDetainModal"
            :style="{ width: '32rem' }"
            header="Detain Package"
            modal
            @hide="closeDetainModal"
        >
            <div v-if="selectedPackageForDetain" class="space-y-4">
                <div class="p-3 bg-slate-50 dark:bg-navy-700 rounded-lg">
                    <p class="font-semibold text-base text-slate-700 dark:text-navy-100">{{ selectedPackageForDetain.package_type }}</p>
                    <p class="text-sm text-slate-500 dark:text-navy-400 mt-1">{{ selectedPackageForDetain.hbl?.hbl_number }}</p>
                </div>

                <div class="space-y-2">
                    <label class="block text-base font-medium text-slate-700 dark:text-navy-100">
                        Select Detain Type
                    </label>
                    <Dropdown
                        v-model="selectedDetainBy"
                        :options="detainByOptions"
                        class="w-full"
                        optionLabel="label"
                        optionValue="value"
                        placeholder="Select Detain By"
                    />
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <Button
                        label="Cancel"
                        severity="secondary"
                        outlined
                        @click="closeDetainModal"
                    />
                    <Button
                        label="Detain Package"
                        severity="warn"
                        :disabled="!selectedDetainBy"
                        @click="handleDetainPackage"
                    />
                </div>
            </div>
        </Dialog>

        <Dialog
            v-model:visible="isRemarkVisible"
            :style="{ width: '50rem' }"
            header="Package Remarks"
            modal
            @hide="closeRemarksDialog"
        >
            <div v-if="selectedPackage" class="mb-4 p-3 bg-blue-50 rounded-lg">
                <p class="font-semibold">{{ selectedPackage.package_type }}</p>
                <p class="text-base text-gray-600">{{ selectedPackage.hbl?.hbl_number }}</p>
            </div>

            <div class="flex flex-col h-[400px] border rounded-lg p-4 bg-gray-50">
                <!-- Loading overlay for fetching -->
                <div
                    v-if="fetching"
                    class="absolute inset-0 bg-white bg-opacity-70 flex items-center justify-center z-10 rounded-lg"
                >
                    <div class="flex flex-col items-center">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-success"></div>
                        <p class="mt-2 text-gray-600">Loading remarks...</p>
                    </div>
                </div>

                <!-- Chat messages -->
                <div class="flex-1 space-y-4 px-4 relative">
                    <!-- Empty state -->
                    <div
                        v-if="!fetching && remarks.length === 0"
                        class="flex items-center justify-center h-full text-gray-400"
                    >
                        <div class="text-center">
                            <i class="pi pi-comments text-4xl mb-2"></i>
                            <p>No remarks yet</p>
                            <p class="text-base">Be the first to add a remark</p>
                        </div>
                    </div>

                    <!-- Remarks list -->
                    <div
                        v-for="(item, index) in remarks"
                        :key="item.id || index"
                        :class="item?.user?.id === $page.props.auth.user.id ? 'justify-end' : 'justify-start'"
                        class="flex"
                    >
                        <div
                            :class="item?.user?.id === $page.props.auth.user.id ? 'bg-success text-white' : 'bg-white text-gray-700'"
                            class="max-w-xs rounded-lg p-3 shadow-md"
                        >
                            <p class="text-base font-semibold">{{ item?.user?.name }}</p>
                            <p class="break-words">{{ item.body }}</p>
                            <small class="block text-sm mt-1 opacity-70">
                                {{ formatDate(item.created_at) }}
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Input box -->
                <div class="flex items-center gap-2 mt-4 relative">
                    <InputText
                        v-model="newRemark"
                        :disabled="loading || fetching"
                        class="flex-1 rounded-lg px-3 py-2 focus:outline-none focus:ring focus:ring-success/90 disabled:opacity-50"
                        placeholder="Type a remark..."
                        @keyup.enter="addRemark"
                    />

                    <!-- Loading indicator for sending -->
                    <div
                        v-if="loading"
                        class="absolute right-12"
                    >
                        <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-success"></div>
                    </div>

                    <Button
                        :disabled="loading || fetching || !newRemark.trim()"
                        class="bg-success text-white px-4 py-2 rounded-lg hover:bg-success/80 disabled:opacity-50 flex items-center gap-2"
                        @click="addRemark"
                    >
                        <i class="pi pi-send text-sm"></i>
                        <span>{{ loading ? 'Sending...' : 'Send' }}</span>
                    </Button>
                </div>
            </div>
        </Dialog>
    </AppLayout>
</template>

<style scoped>
.animate-spin {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
</style>
