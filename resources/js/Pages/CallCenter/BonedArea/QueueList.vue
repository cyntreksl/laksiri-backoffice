<script setup>
import {router} from "@inertiajs/vue3";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import {computed, ref} from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import Column from "primevue/column";
import Card from "primevue/card";
import DataTable from "primevue/datatable";
import DataView from "primevue/dataview";
import Button from "primevue/button";
import SelectButton from "primevue/selectbutton";
import Tag from "primevue/tag";
import PackageReleaseDialog from "@/Pages/CallCenter/BonedArea/PackageReleaseDialog.vue";
import Dialog from "primevue/dialog";
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import Accordion from 'primevue/accordion';
import AccordionTab from 'primevue/accordiontab';
import Checkbox from 'primevue/checkbox';
import Dropdown from 'primevue/dropdown';
import Divider from 'primevue/divider';
import Panel from 'primevue/panel';
import {push} from "notivue";
import axios from 'axios';
import {useForm} from "@inertiajs/vue3";

const props = defineProps({
    packageQueue: {
        type: Object,
        default: () => {
        }
    }
})

const layout = ref('list');
const options = ref(['list', 'grid']);
const showPackageReleaseDialog = ref(false);
const selectedToken = ref([]);

const filteredPackageQueue = computed(() => {
    return props.packageQueue.filter(q => {
        // Show tokens that haven't left AND have packages that are not fully released
        // A token should appear if: not left AND (not released OR has held packages)
        return !q.left_at && (!q.is_released || (q.held_package_count && q.held_package_count > 0));
    });
})

// Helper function to determine package tag color based on release status
const getPackageTagSeverity = (queueData, packageId) => {
    const releasedPackages = queueData.released_packages || {};
    const isReleased = releasedPackages[packageId];
    return isReleased ? 'success' : 'warning';
};

const handlePackageRelease = (token) => {
    selectedToken.value = token;
    showPackageReleaseDialog.value = true;
}

const closePackageReleaseModal = () => {
    selectedToken.value = [];
    showPackageReleaseDialog.value = false;
}

const returnDialogVisible = ref(false);
const returnForm = useForm({
    token_number: '',
    package_details: null,
    remarks: '',
    selected_packages: [] // Will contain individual package IDs
});

const showReturnDialog = () => {
    returnDialogVisible.value = true;
};

const loadPackageDetails = async () => {
    if (!returnForm.token_number) {
        returnForm.package_details = null;
        return;
    }

    try {
        const response = await axios.get(`/call-center/get-packages-for-return/${returnForm.token_number}`);
        returnForm.package_details = response.data;
        returnForm.selected_packages = []; // Reset selections
    } catch (error) {
        // Handle validation errors (422)
        if (error.response?.status === 422) {
            const errorData = error.response.data;

            // Check if it's a Laravel validation error with errors object
            if (errorData.errors) {
                // Get all error messages from the errors object
                const errorMessages = Object.values(errorData.errors).flat();

                // Display each error message
                errorMessages.forEach(message => {
                    push.error({
                        title: 'Validation Error',
                        message: message,
                        duration: 6000
                    });
                });
            }
            // Check if it's our custom error format
            else if (errorData.error || errorData.details) {
                push.error({
                    title: errorData.error || 'Validation Error',
                    message: errorData.details || 'Invalid token or queue status',
                    duration: 6000
                });
            }
            // Fallback for other 422 errors
            else {
                push.error({
                    title: 'Validation Error',
                    message: errorData.message || 'Invalid token or queue status',
                    duration: 6000
                });
            }
        }
        // Handle not found errors (404)
        else if (error.response?.status === 404) {
            const errorData = error.response.data;
            push.error({
                title: errorData.error || 'Not Found',
                message: errorData.details || 'No packages found for this token',
                duration: 5000
            });
        }
        // Handle other errors
        else {
            push.error({
                title: 'Error Loading Packages',
                message: error.response?.data?.error || error.message || 'An unexpected error occurred',
                duration: 5000
            });
        }
        returnForm.package_details = null;
    }
};

const togglePackageSelection = (packageData) => {
    const index = returnForm.selected_packages.findIndex(
        p => p.hbl_package_id === packageData.hbl_package_id
    );

    if (index >= 0) {
        // Remove from selection
        returnForm.selected_packages.splice(index, 1);
    } else {
        // Add to selection (held packages only)
        returnForm.selected_packages.push({
            hbl_package_id: packageData.hbl_package_id,
            package_queue_id: packageData.package_queue_id,
            package_type: packageData.package_type,
            quantity: packageData.quantity,
            hbl_reference: packageData.hbl_reference
        });
    }
};

const isPackageSelected = (packageData) => {
    return returnForm.selected_packages.some(
        p => p.hbl_package_id === packageData.hbl_package_id
    );
};

// Get individual packages list
const allPackages = computed(() => {
    if (!returnForm.package_details?.individual_packages) {
        return [];
    }
    return returnForm.package_details.individual_packages;
});

// Computed properties to optimize the complex filtering logic
const hblGroupSelectionStates = computed(() => {
    if (!returnForm.package_details?.hbl_groups) {
        return {};
    }

    const states = {};

    returnForm.package_details.hbl_groups.forEach((hblGroup, index) => {
        const heldPackages = hblGroup.packages.filter(pkg => !pkg.is_released);
        const allSelected = heldPackages.every(pkg => isPackageSelected(pkg));
        const noneSelected = heldPackages.every(pkg => !isPackageSelected(pkg));
        const someSelected = !allSelected && !noneSelected;

        states[index] = {
            heldPackagesCount: heldPackages.length,
            allSelected,
            noneSelected,
            someSelected,
            buttonLabel: allSelected ? 'Deselect All' : 'Select All Held',
            buttonSeverity: allSelected ? 'secondary' : 'info',
            disabled: heldPackages.length === 0
        };
    });

    return states;
});

const selectAllPackagesInHBL = (hblGroup, hblIndex) => {
    const state = hblGroupSelectionStates.value[hblIndex];
    const heldPackages = hblGroup.packages.filter(pkg => !pkg.is_released);

    if (state.allSelected) {
        // Deselect all packages in this HBL
        heldPackages.forEach(pkg => {
            const index = returnForm.selected_packages.findIndex(
                p => p.package_queue_id === pkg.package_queue_id
            );
            if (index >= 0) {
                returnForm.selected_packages.splice(index, 1);
            }
        });
    } else {
        // Select all held packages in this HBL
        heldPackages.forEach(pkg => {
            if (!isPackageSelected(pkg)) {
                returnForm.selected_packages.push({
                    package_queue_id: pkg.package_queue_id,
                    reference: pkg.reference,
                    package_count: pkg.package_count
                });
            }
        });
    }
};

const removeSelectedPackage = (index) => {
    returnForm.selected_packages.splice(index, 1);
};

const handleReturnPackage = () => {
    if (returnForm.selected_packages.length === 0) {
        push.error('Please select at least one package to return!');
        return;
    }

    returnForm.post(route('call-center.package.return'), {
        onSuccess: () => {
            push.success(`${returnForm.selected_packages.length} package(s) returned successfully!`);
            returnDialogVisible.value = false;
            returnForm.reset();
            // Refresh the package queue data
            router.reload({ only: ['packageQueue'] });
        },
        onError: (errors) => {
            push.error('Error returning package: ' + Object.values(errors).join(', '));
        },
        preserveState: true,
        preserveScroll: true,
    });
};

const logDialogVisible = ref(false);
const selectedTokenLogs = ref([]);

const showLogDialog = (token) => {
    // Fetch logs for this token - you'll need to implement this API endpoint
    axios.get(`/call-center/package-logs/${token.id}`)
        .then(response => {
            selectedTokenLogs.value = response.data;
            logDialogVisible.value = true;
        })
        .catch(error => {
            push.error('Error fetching logs');
        });
};

const remarksDialogVisible = ref(false);
const selectedTokenRemarks = ref(null);
const loadingRemarks = ref(false);

const showRemarksDialog = async (token) => {
    selectedTokenRemarks.value = {
        ...token,
        hbl_packages: token.hbl_packages || []
    };
    remarksDialogVisible.value = true;
    loadingRemarks.value = true;

    // Check if hbl_packages exists and is an array
    if (!token.hbl_packages || !Array.isArray(token.hbl_packages) || token.hbl_packages.length === 0) {
        loadingRemarks.value = false;
        push.warning('No packages found for this token');
        return;
    }

    // Fetch remarks for each package
    try {
        const packagesWithRemarks = await Promise.all(
            token.hbl_packages.map(async (pkg) => {
                try {
                    const { data } = await axios.get(`/remarks/package/${pkg.id}`);
                    const remarksList = data.data || data || [];

                    return {
                        ...pkg,
                        remarks_list: remarksList
                    };
                } catch (error) {
                    console.error(`Error fetching remarks for package ${pkg.id}:`, error.response || error);
                    return {
                        ...pkg,
                        remarks_list: []
                    };
                }
            })
        );

        selectedTokenRemarks.value = {
            ...token,
            hbl_packages: packagesWithRemarks
        };
    } catch (error) {
        console.error('Error loading package remarks:', error);
        push.error('Error loading package remarks');
    } finally {
        loadingRemarks.value = false;
    }
};

const formatDate = (dateString) => {
    if (!dateString) return '';

    try {
        // Handle both ISO format and Laravel's datetime format
        const date = new Date(dateString.includes(' ') ? dateString.replace(' ', 'T') : dateString);
        return date.toLocaleString();
    } catch (error) {
        console.error('Error formatting date:', error);
        return dateString;
    }
};

// Add remark functionality
const newRemark = ref({});
const addingRemark = ref({});

const addPackageRemark = async (packageId) => {
    if (!newRemark.value[packageId]?.trim()) return;

    addingRemark.value[packageId] = true;

    try {
        await axios.post(`/hbl-packages/${packageId}/remarks`, {
            body: newRemark.value[packageId]
        });

        // Clear input
        newRemark.value[packageId] = '';

        // Refetch remarks for this specific package
        const { data } = await axios.get(`/remarks/package/${packageId}`);
        const remarksList = data.data || data || [];

        // Update the remarks_list for this package
        const packageIndex = selectedTokenRemarks.value.hbl_packages.findIndex(pkg => pkg.id === packageId);
        if (packageIndex !== -1) {
            selectedTokenRemarks.value.hbl_packages[packageIndex].remarks_list = remarksList;
        }

        push.success('Remark added successfully!');
    } catch (error) {
        console.error('Error adding remark:', error);
        push.error('Failed to add remark. Please try again.');
    } finally {
        addingRemark.value[packageId] = false;
    }
};
</script>

<template>
    <AppLayout title="Queue List">
        <template #header>Queue List</template>

        <Breadcrumb/>

        <DataView :layout="layout" :value="filteredPackageQueue" class="my-5">
            <template #header>
                <div class="flex justify-between items-center">
                    <div class="text-lg font-medium">
                        Package Calling Queue
                    </div>
                    <div class="flex items-center gap-2">
                        <Button label="Return Package" severity="warning" type="button" @click="showReturnDialog"></Button>
                        <SelectButton v-model="layout" :allowEmpty="false" :options="options">
                            <template #option="{ option }">
                                <i :class="[option === 'list' ? 'pi pi-bars' : 'pi pi-table']"/>
                            </template>
                        </SelectButton>
                    </div>
                </div>
            </template>

            <template #list="slotProps">
                <DataTable :rows="10" :rowsPerPageOptions="[5, 10, 20, 50]" :value="slotProps.items" paginator row-hover
                           tableStyle="min-width: 50rem">
                    <Column field="token" header="Token">
                        <template #body="slotProps">
                            <div class="flex items-center text-2xl">
                                <i class="ti ti-tag mr-1 text-blue-500"></i>
                                {{ slotProps.data.token }}
                            </div>
                        </template>
                    </Column>
                    <Column field="customer" header="Customer"></Column>
                    <Column field="reference" header="HBL">
                        <template #body="slotProps">
                            <div v-if="slotProps.data.hbl" class="flex items-center space-x-2">
                                <div>
                                    <div class="font-medium">{{ slotProps.data.hbl.hbl_number ?? slotProps.data.hbl.hbl }}</div>
                                    <div class="text-sm text-gray-500">{{ slotProps.data.hbl.reference }}</div>
                                </div>
                            </div>
                            <span v-else class="text-gray-400">N/A</span>
                        </template>
                    </Column>
                    <Column field="package_count" header="Packages">
                        <template #body="slotProps">
                            <div class="flex items-center gap-2 mb-1">
                                <i class="ti ti-package mr-1 text-blue-500" style="font-size: 1rem"></i>
                                <span class="font-medium">{{ slotProps.data.package_count }} Total</span>
                            </div>

                            <!-- Package Status Summary -->
                            <div v-if="slotProps.data.released_package_count || slotProps.data.held_package_count" class="flex gap-2 text-xs mb-2">
                                <Tag v-if="slotProps.data.released_package_count" :value="`${slotProps.data.released_package_count} Released`" severity="info" />
                                <Tag v-if="slotProps.data.held_package_count" :value="`${slotProps.data.held_package_count} On Hold`" severity="warn" />
                            </div>

                            <!-- Individual Package Tags -->
                            <div class="flex flex-wrap gap-1">
                                <div v-for="(hbl_package, index) in slotProps.data?.hbl_packages" :key="index">
                                    <Tag
                                        :value="`${hbl_package.quantity} ${hbl_package.package_type}`"
                                        :severity="getPackageTagSeverity(slotProps.data, hbl_package.id)"
                                    />
                                </div>
                            </div>
                        </template>
                    </Column>
                    <Column field="created_at" header="Created At"></Column>
                    <Column field="" header="Actions" style="width: 15%">
                        <template #body="{ data }">
                            <div class="flex items-center">
                                <Button
                                    class="mr-2"
                                    icon="ti ti-arrow-right"
                                    rounded
                                    size="small"
                                    @click.prevent="handlePackageRelease(data)"
                                />
                                <Button
                                    class="mr-2"
                                    icon="ti ti-eye"
                                    rounded
                                    size="small"
                                    severity="secondary"
                                    @click.prevent="showLogDialog(data)"
                                    v-tooltip="'View Release Logs'"
                                />
                                <Button
                                    v-tooltip="'View Package Remarks'"
                                    icon="ti ti-message-circle"
                                    rounded
                                    severity="info"
                                    size="small"
                                    @click.prevent="showRemarksDialog(data)"
                                />
                            </div>
                        </template>
                    </Column>
                    <template #footer> In total there are {{ slotProps.items.length }} tokens.</template>
                </DataTable>
            </template>

            <template #grid="slotProps">
                <div
                    class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-5 my-5 p-5">
                    <Card v-for="queue in slotProps.items" :key="queue.id"
                          class="!border !border-info rounded-2xl bg-white cursor-pointer hover:bg-info/10"
                          @click.prevent="handlePackageRelease(queue)">
                        <template #content>
                            <div class="flex justify-between items-center mb-4">
                                <div class="flex items-center gap-2 text-info">
                                    <i class="ti ti-packages text-2xl"></i>
                                    <span class="text-2xl font-semibold">{{ queue.package_count }}</span>
                                </div>
                            </div>

                            <!-- Package Status Summary -->
                            <div v-if="queue.released_package_count || queue.held_package_count" class="flex justify-center gap-2 mb-4">
                                <Tag v-if="queue.released_package_count" :value="`${queue.released_package_count} Released`" class="text-xs" severity="success" />
                                <Tag v-if="queue.held_package_count" :value="`${queue.held_package_count} On Hold`" class="text-xs" severity="warning" />
                            </div>

                            <div class="text-center mb-6">
                                <h1 class="text-5xl xl:text-[80px] font-extrabold text-gray-900 tracking-wide">
                                    {{ queue?.token }}
                                </h1>
                            </div>

                            <div class="flex flex-wrap justify-center gap-1">
                                <template v-for="(hbl_package, index) in queue?.hbl_packages" :key="index">
                                    <Tag
                                        :value="`${hbl_package.quantity} ${hbl_package.package_type}`"
                                        class="rounded-full px-4 py-2 text-base"
                                        :severity="getPackageTagSeverity(queue, hbl_package.id)"
                                    />
                                </template>
                            </div>
                        </template>
                    </Card>
                </div>
            </template>

            <template #empty>
                <div class="flex p-10 justify-center">
                    No Tokens found.
                </div>
            </template>
        </DataView>
    </AppLayout>

    <PackageReleaseDialog :package-queue="selectedToken" :visible="showPackageReleaseDialog"
                          @close="closePackageReleaseModal"
                          @update:visible="showPackageReleaseDialog = $event"/>

    <Dialog :style="{ width: '70rem' }" :visible="returnDialogVisible" header="Return Held Packages to Bond Storage" modal @update:visible="returnDialogVisible = $event">
        <div class="space-y-6">
            <!-- Token Number Input -->
            <div class="field">
                <label class="block mb-2 font-semibold" for="token_number">Token Number or HBL Reference</label>
                <div class="flex gap-2">
                    <InputText
                        id="token_number"
                        v-model="returnForm.token_number"
                        class="flex-1"
                        placeholder="Enter token number (e.g., 1, 2, 3...) or HBL reference"
                        @keyup.enter="loadPackageDetails"
                    />
                    <Button
                        label="Load Packages"
                        icon="pi pi-search"
                        @click="loadPackageDetails"
                        :disabled="!returnForm.token_number"
                    />
                </div>
                <small class="text-gray-500">You can search by token number or HBL reference</small>
                <div v-if="returnForm.errors.token_number" class="text-red-500 text-sm mt-1">
                    {{ returnForm.errors.token_number }}
                </div>
            </div>

            <!-- Package Details Section -->
            <div v-if="returnForm.package_details" class="space-y-4">
                <!-- Summary -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <h3 class="font-bold text-lg mb-2 text-blue-800">📦 Token Summary</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                        <div>
                            <strong>Token:</strong> {{ returnForm.package_details.token_number }}
                        </div>
                        <div>
                            <strong>Customer:</strong> {{ returnForm.package_details.customer }}
                        </div>
                        <div>
                            <strong>HBL:</strong> {{ returnForm.package_details.hbl_reference }}
                        </div>
                        <div>
                            <strong>Total Packages:</strong> {{ returnForm.package_details.summary?.total_packages || allPackages.length }}
                        </div>
                        <div>
                            <strong>Available for Return:</strong>
                            <Tag :value="returnForm.package_details.summary?.available_for_return || allPackages.length" severity="warning" />
                        </div>
                    </div>
                    <!-- Validation Status Badge -->
                    <div class="mt-3 pt-3 border-t border-blue-200">
                        <div class="flex items-center gap-2 text-xs">
                            <i class="pi pi-check-circle text-green-600"></i>
                            <span class="text-green-700 font-medium">✓ Token validated: Created today and in Examination Queue</span>
                        </div>
                    </div>
                </div>

                <!-- Individual Packages List -->
                <div class="space-y-3">
                    <h4 class="font-bold text-base flex items-center gap-2">
                        <i class="pi pi-list"></i>
                        Select Held Packages to Return to Bond Storage
                    </h4>

                    <!-- Package List - Flat display of all held packages -->
                    <div class="grid gap-3 max-h-96 overflow-y-auto">
                        <div
                            v-for="packageData in allPackages"
                            :key="packageData.id"
                            class="border rounded-lg p-4 transition-all"
                            :class="{
                                'border-orange-300 bg-orange-50': isPackageSelected(packageData),
                                'border-yellow-300 bg-yellow-50': !isPackageSelected(packageData)
                            }"
                        >
                            <div class="flex items-start justify-between">
                                <div class="flex items-start gap-3 flex-1">
                                    <!-- Selection Checkbox -->
                                    <Checkbox
                                        :modelValue="isPackageSelected(packageData)"
                                        @update:modelValue="togglePackageSelection(packageData)"
                                        :binary="true"
                                    />

                                    <!-- Package Info -->
                                    <div class="flex-1">
                                        <div class="grid grid-cols-2 md:grid-cols-4 gap-2 text-sm mb-2">
                                            <div><strong>Package Type:</strong> {{ packageData.package_type }}</div>
                                            <div><strong>Quantity:</strong> {{ packageData.quantity }}</div>
                                            <div><strong>Size:</strong> {{ packageData.length }}x{{ packageData.width }}x{{ packageData.height }}</div>
                                            <div><strong>Status:</strong>
                                                <Tag severity="warning" value="Held" />
                                            </div>
                                        </div>
                                        <div class="grid grid-cols-2 md:grid-cols-4 gap-2 text-xs text-gray-600 mb-2">
                                            <div><strong>Weight:</strong> {{ packageData.weight || 0 }}</div>
                                            <div><strong>Volume:</strong> {{ packageData.volume || 0 }}</div>
                                            <div v-if="packageData.bond_storage_number"><strong>Bond Storage:</strong> {{ packageData.bond_storage_number }}</div>
                                            <div v-if="packageData.released_at"><strong>Released:</strong> {{ new Date(packageData.released_at).toLocaleDateString() }}</div>
                                        </div>
                                        <div v-if="packageData.remarks" class="text-xs text-gray-600 mb-2">
                                            <strong>Remarks:</strong> {{ packageData.remarks }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Selected Packages Summary -->
                <div v-if="returnForm.selected_packages.length > 0" class="bg-orange-50 border border-orange-200 rounded-lg p-4">
                    <h4 class="font-bold text-orange-800 mb-2 flex items-center gap-2">
                        <i class="pi pi-check-circle"></i>
                        Selected Held Packages for Return to Bond ({{ returnForm.selected_packages.length }})
                    </h4>
                    <div class="flex flex-wrap gap-2">
                        <div
                            v-for="(pkg, index) in returnForm.selected_packages"
                            :key="index"
                            class="flex items-center gap-2 bg-white border border-orange-300 rounded-full px-3 py-1 text-sm"
                        >
                            <span>{{ pkg.package_type }} (Qty: {{ pkg.quantity }}) - {{ pkg.hbl_reference }}</span>
                            <Button
                                icon="pi pi-times"
                                severity="danger"
                                text
                                rounded
                                size="small"
                                @click="removeSelectedPackage(index)"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Remarks -->
            <div class="field">
                <label class="block mb-2 font-semibold" for="return_remarks">Return to Bond Remarks</label>
                <Textarea
                    id="return_remarks"
                    v-model="returnForm.remarks"
                    class="w-full"
                    rows="3"
                    placeholder="Enter reason for returning to bond storage..."
                />
                <div v-if="returnForm.errors.remarks" class="text-red-500 text-sm mt-1">
                    {{ returnForm.errors.remarks }}
                </div>
                <div v-if="returnForm.errors.selected_packages" class="text-red-500 text-sm mt-1">
                    {{ returnForm.errors.selected_packages }}
                </div>
            </div>
        </div>

        <template #footer>
            <div class="flex justify-between items-center">
                <div class="text-sm text-gray-600">
                    <span v-if="returnForm.selected_packages.length > 0">
                        {{ returnForm.selected_packages.length }} package(s) selected for return
                    </span>
                </div>
                <div class="flex gap-2">
                    <Button
                        label="Cancel"
                        severity="secondary"
                        @click="returnDialogVisible = false"
                    />
                    <Button
                        :disabled="returnForm.selected_packages.length === 0 || returnForm.processing"
                        :class="{ 'opacity-75': returnForm.processing }"
                        icon="pi pi-arrow-left"
                        label="Return to Bond Storage"
                        severity="warning"
                        @click="handleReturnPackage"
                    />
                </div>
            </div>
        </template>
    </Dialog>

    <!-- Add Log Dialog -->
    <Dialog
        :style="{ width: '50rem' }"
        :visible="logDialogVisible"
        header="Package Release Logs"
        modal
        @update:visible="logDialogVisible = $event"
    >
        <DataTable
            :value="selectedTokenLogs"
            class="p-datatable-sm"
            responsive-layout="scroll"
        >
            <Column field="created_at" header="Date">
                <template #body="slotProps">
                    {{ new Date(slotProps.data.created_at).toLocaleString() }}
                </template>
            </Column>
            <Column field="type" header="Type">
                <template #body="slotProps">
                    <Tag :severity="slotProps.data.type === 'return' ? 'warning' : 'success'">
                        {{ slotProps.data.type }}
                    </Tag>
                </template>
            </Column>
            <Column field="packages" header="Packages">
                <template #body="slotProps">
                    <div v-for="(pkg, index) in slotProps.data.packages" :key="index">
                        <div v-if="typeof pkg === 'object'">
                            {{ pkg.reference }} - {{ pkg.package_count }} packages
                        </div>
                        <div v-else>
                            {{ pkg }}
                        </div>
                    </div>
                </template>
            </Column>
            <Column field="remarks" header="Remarks">
                <template #body="slotProps">
                    <span v-if="slotProps.data.remarks">{{ slotProps.data.remarks }}</span>
                    <span v-else class="text-gray-400">No remarks</span>
                </template>
            </Column>

            <Column header="Created By">
                <template #body="slotProps">
        <span v-if="slotProps.data.created_by?.name">
            {{ slotProps.data.created_by.name }}
        </span>
                    <span v-else class="text-gray-400">Unknown user</span>
                </template>
            </Column>

            <template #empty>
                <div class="flex flex-col items-center justify-center py-12 text-gray-500">
                    <i class="pi pi-inbox text-6xl mb-4 text-gray-300"></i>
                    <p class="text-lg font-medium mb-1">No Release Logs Found</p>
                    <p class="text-sm">This token has no package release or return history yet.</p>
                </div>
            </template>
        </DataTable>

        <template #footer>
            <Button
                label="Close"
                severity="secondary"
                @click="logDialogVisible = false"
            />
        </template>
    </Dialog>

    <!-- Package Remarks Dialog -->
    <Dialog
        :style="{ width: '70rem' }"
        :visible="remarksDialogVisible"
        header="Package Remarks"
        modal
        @update:visible="remarksDialogVisible = $event"
    >
        <div v-if="selectedTokenRemarks" class="space-y-4">
            <!-- Loading overlay -->
            <div
                v-if="loadingRemarks"
                class="absolute inset-0 bg-white bg-opacity-70 flex items-center justify-center z-10 rounded-lg"
            >
                <div class="flex flex-col items-center">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
                    <p class="mt-2 text-gray-600">Loading remarks...</p>
                </div>
            </div>

            <!-- Token Info -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                <div class="grid grid-cols-2 md:grid-cols-3 gap-3 text-sm">
                    <div>
                        <strong class="text-blue-800">Token:</strong>
                        <span class="ml-2">{{ selectedTokenRemarks.token }}</span>
                    </div>
                    <div>
                        <strong class="text-blue-800">Customer:</strong>
                        <span class="ml-2">{{ selectedTokenRemarks.customer }}</span>
                    </div>
                    <div>
                        <strong class="text-blue-800">Reference:</strong>
                        <span class="ml-2">{{ selectedTokenRemarks.reference }}</span>
                    </div>
                </div>
            </div>

            <!-- Packages with Remarks -->
            <div v-if="!loadingRemarks" class="space-y-4 max-h-[600px] overflow-y-auto pr-2">
                <h4 class="font-bold text-base flex items-center gap-2 mb-3 sticky top-0 bg-white z-10 py-2">
                    <i class="pi pi-box"></i>
                    Package Details & Remarks ({{ selectedTokenRemarks.hbl_packages?.length || 0 }} {{ selectedTokenRemarks.hbl_packages?.length === 1 ? 'package' : 'packages' }})
                </h4>

                <div
                    v-for="(hbl_package, index) in selectedTokenRemarks.hbl_packages"
                    :key="index"
                    class="border border-gray-200 rounded-lg p-4 bg-gray-50"
                >
                    <!-- Package Header -->
                    <div class="flex items-start justify-between mb-3 pb-3 border-b border-gray-200">
                        <div class="flex items-center gap-2">
                            <Tag
                                :value="`${hbl_package.quantity} ${hbl_package.package_type}`"
                                severity="info"
                            />
                            <span v-if="hbl_package.bond_storage_number" class="text-xs text-gray-500">
                                Bond: {{ hbl_package.bond_storage_number }}
                            </span>
                        </div>
                        <div class="text-xs text-gray-500">
                            {{ hbl_package.length }}×{{ hbl_package.width }}×{{ hbl_package.height }}
                        </div>
                    </div>

                    <!-- Remarks Section -->
                    <div class="space-y-3">
                        <div class="flex items-center gap-2 mb-2">
                            <i class="pi pi-comments text-blue-500"></i>
                            <strong class="text-sm text-gray-700">
                                Remarks ({{ hbl_package.remarks_list?.length || 0 }})
                            </strong>
                        </div>

                        <!-- Remarks List (Chat Style) -->
                        <div class="bg-white rounded-lg p-3 min-h-[100px] max-h-80 overflow-y-auto">
                            <!-- Empty state -->
                            <div
                                v-if="!hbl_package.remarks_list || hbl_package.remarks_list.length === 0"
                                class="flex items-center justify-center h-20 text-gray-400"
                            >
                                <div class="text-center">
                                    <i class="pi pi-inbox text-3xl mb-2 block text-gray-300"></i>
                                    <p class="text-sm">No remarks yet</p>
                                </div>
                            </div>

                            <!-- Remarks messages -->
                            <div v-else class="space-y-2">
                                <div
                                    v-for="(remark, remarkIndex) in hbl_package.remarks_list"
                                    :key="remarkIndex"
                                    :class="remark?.user?.id === $page.props.auth.user.id ? 'justify-end' : 'justify-start'"
                                    class="flex"
                                >
                                    <div
                                        :class="remark?.user?.id === $page.props.auth.user.id ? 'bg-blue-500 text-white' : 'bg-gray-100 text-gray-700'"
                                        class="max-w-[70%] rounded-lg p-3 shadow-sm"
                                    >
                                        <p class="text-xs font-semibold mb-1">{{ remark?.user?.name || 'Unknown' }}</p>
                                        <p class="text-sm break-words whitespace-pre-wrap">{{ remark.body }}</p>
                                        <small class="block text-xs mt-1 opacity-70">
                                            {{ formatDate(remark.created_at) }}
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Add Remark Input -->
                        <div class="flex items-center gap-2 mt-3">
                            <InputText
                                v-model="newRemark[hbl_package.id]"
                                :disabled="addingRemark[hbl_package.id]"
                                class="flex-1"
                                placeholder="Type a remark..."
                                @keyup.enter="addPackageRemark(hbl_package.id)"
                            />
                            <Button
                                :disabled="addingRemark[hbl_package.id] || !newRemark[hbl_package.id]?.trim()"
                                :loading="addingRemark[hbl_package.id]"
                                icon="pi pi-send"
                                severity="info"
                                @click="addPackageRemark(hbl_package.id)"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="!selectedTokenRemarks.hbl_packages || selectedTokenRemarks.hbl_packages.length === 0" class="flex flex-col items-center justify-center py-12 text-gray-500">
                <i class="pi pi-inbox text-6xl mb-4 text-gray-300"></i>
                <p class="text-lg font-medium mb-1">No Packages Found</p>
                <p class="text-sm">This token has no package information available.</p>
            </div>
        </div>

        <template #footer>
            <Button
                label="Close"
                severity="secondary"
                @click="remarksDialogVisible = false"
            />
        </template>
    </Dialog>
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
