<!-- QueueList.vue -->
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
        return q.is_released == false
    });
})

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
    selected_packages: []
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
        if (error.response?.status === 404) {
            push.error('No packages found for this token!');
        } else {
            push.error(`Error loading package details: ${error.message || error.response?.status || 'Unknown error'}`);
        }
        returnForm.package_details = null;
    }
};

const togglePackageSelection = (packageData) => {
    const index = returnForm.selected_packages.findIndex(
        p => p.package_queue_id === packageData.package_queue_id
    );
    
    if (index >= 0) {
        // Remove from selection
        returnForm.selected_packages.splice(index, 1);
    } else {
        // Add to selection (only if released)
        if (packageData.is_released) {
            returnForm.selected_packages.push({
                package_queue_id: packageData.package_queue_id,
                reference: packageData.reference,
                package_count: packageData.package_count
            });
        }
    }
};

const isPackageSelected = (packageData) => {
    return returnForm.selected_packages.some(
        p => p.package_queue_id === packageData.package_queue_id
    );
};

const selectAllPackagesInHBL = (hblGroup) => {
    const releasedPackages = hblGroup.packages.filter(pkg => pkg.is_released);
    
    // Check if all released packages in this HBL are already selected
    const allSelected = releasedPackages.every(pkg => isPackageSelected(pkg));
    
    if (allSelected) {
        // Deselect all packages in this HBL
        releasedPackages.forEach(pkg => {
            const index = returnForm.selected_packages.findIndex(
                p => p.package_queue_id === pkg.package_queue_id
            );
            if (index >= 0) {
                returnForm.selected_packages.splice(index, 1);
            }
        });
    } else {
        // Select all released packages in this HBL
        releasedPackages.forEach(pkg => {
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
                    <Column field="reference" header="Reference"></Column>
                    <Column field="package_count" header="Packages">
                        <template #body="slotProps">
                            <div class="flex items-center">
                                <i class="ti ti-package mr-1 text-blue-500" style="font-size: 1rem"></i>
                                {{ slotProps.data.package_count }}
                            </div>
                            <div class="flex flex-wrap space-x-1 mt-1">
                                <div v-for="(hbl_package, index) in slotProps.data?.hbl_packages" :key="index">
                                    <Tag
                                        :value="`${hbl_package.quantity} ${hbl_package.package_type}`"
                                        severity="info"
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
                                <!-- Add log icon button -->
                                <Button
                                    class="mr-2"
                                    icon="ti ti-eye"
                                    rounded
                                    size="small"
                                    severity="secondary"
                                    @click.prevent="showLogDialog(data)"
                                    v-tooltip="'View Release Logs'"
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
                            <div class="flex justify-between items-center mb-6">
                                <div class="flex items-center gap-2 text-info">
                                    <i class="ti ti-packages text-2xl"></i>
                                    <span class="text-2xl font-semibold">{{ queue.package_count }}</span>
                                </div>
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
                                        severity="info"
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

    <Dialog :style="{ width: '70rem' }" :visible="returnDialogVisible" header="Return Package - Enhanced Selection" modal @update:visible="returnDialogVisible = $event">
        <div class="space-y-6">
            <!-- Token Number Input -->
            <div class="field">
                <label for="token_number" class="block mb-2 font-semibold">Token Number</label>
                <div class="flex gap-2">
                    <InputText
                        id="token_number"
                        v-model="returnForm.token_number"
                        class="flex-1"
                        placeholder="Enter token number (e.g., 1, 2, 3...)"
                        @keyup.enter="loadPackageDetails"
                    />
                    <Button 
                        label="Load Packages" 
                        icon="pi pi-search" 
                        @click="loadPackageDetails"
                        :disabled="!returnForm.token_number"
                    />
                </div>
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
                            <strong>Total HBLs:</strong> {{ returnForm.package_details.summary.total_hbls }}
                        </div>
                        <div>
                            <strong>Available for Return:</strong> 
                            <Tag :value="returnForm.package_details.summary.available_for_return" severity="info" />
                        </div>
                    </div>
                </div>

                <!-- HBL Groups -->
                <div class="space-y-3">
                    <h4 class="font-bold text-base flex items-center gap-2">
                        <i class="pi pi-list"></i>
                        Select Packages to Return
                    </h4>
                    
                    <Accordion multiple>
                        <AccordionTab 
                            v-for="(hblGroup, index) in returnForm.package_details.hbl_groups" 
                            :key="index"
                            :header="`📋 HBL: ${hblGroup.hbl_reference} (${hblGroup.released_packages}/${hblGroup.total_packages} released)`"
                        >
                            <div class="space-y-3">
                                <!-- HBL Actions -->
                                <div class="flex justify-between items-center bg-gray-50 p-3 rounded">
                                    <div class="text-sm text-gray-600">
                                        <strong>Customer:</strong> {{ hblGroup.customer }}
                                    </div>
                                    <Button 
                                        :label="hblGroup.packages.filter(pkg => pkg.is_released).every(pkg => isPackageSelected(pkg)) ? 'Deselect All' : 'Select All Released'"
                                        :severity="hblGroup.packages.filter(pkg => pkg.is_released).every(pkg => isPackageSelected(pkg)) ? 'secondary' : 'info'"
                                        size="small"
                                        @click="selectAllPackagesInHBL(hblGroup)"
                                        :disabled="hblGroup.packages.filter(pkg => pkg.is_released).length === 0"
                                    />
                                </div>

                                <!-- Package List -->
                                <div class="grid gap-3">
                                    <div 
                                        v-for="packageData in hblGroup.packages" 
                                        :key="packageData.id"
                                        class="border rounded-lg p-4 transition-all"
                                        :class="{
                                            'border-green-300 bg-green-50': packageData.is_released && isPackageSelected(packageData),
                                            'border-blue-300 bg-blue-50': packageData.is_released && !isPackageSelected(packageData),
                                            'border-gray-300 bg-gray-50': !packageData.is_released
                                        }"
                                    >
                                        <div class="flex items-start justify-between">
                                            <div class="flex items-start gap-3 flex-1">
                                                <!-- Selection Checkbox -->
                                                <Checkbox 
                                                    v-if="packageData.is_released"
                                                    :modelValue="isPackageSelected(packageData)"
                                                    @update:modelValue="togglePackageSelection(packageData)"
                                                    :binary="true"
                                                />
                                                <div v-else class="w-6"></div>

                                                <!-- Package Info -->
                                                <div class="flex-1">
                                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-2 text-sm mb-2">
                                                        <div><strong>Reference:</strong> {{ packageData.reference }}</div>
                                                        <div><strong>Packages:</strong> {{ packageData.package_count }}</div>
                                                        <div><strong>Status:</strong> 
                                                            <Tag 
                                                                :value="packageData.is_released ? 'Released' : 'Not Released'"
                                                                :severity="packageData.is_released ? 'success' : 'warning'"
                                                            />
                                                        </div>
                                                        <div v-if="packageData.released_at">
                                                            <strong>Released:</strong> {{ new Date(packageData.released_at).toLocaleDateString() }}
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Release History -->
                                                    <div v-if="packageData.logs && packageData.logs.length > 0" class="mt-2">
                                                        <details class="text-xs">
                                                            <summary class="cursor-pointer text-blue-600 hover:text-blue-800">View History ({{ packageData.logs.length }} entries)</summary>
                                                            <div class="mt-2 space-y-1 pl-4 border-l-2 border-gray-200">
                                                                <div v-for="log in packageData.logs" :key="log.id" class="bg-gray-100 p-2 rounded">
                                                                    <div class="flex justify-between items-center">
                                                                        <Tag :value="log.type" :severity="log.type === 'return' ? 'warning' : 'success'" />
                                                                        <span class="text-xs text-gray-500">{{ new Date(log.created_at).toLocaleString() }}</span>
                                                                    </div>
                                                                    <div v-if="log.remarks" class="text-xs mt-1">{{ log.remarks }}</div>
                                                                    <div v-if="log.created_by" class="text-xs text-gray-600">by {{ log.created_by }}</div>
                                                                </div>
                                                            </div>
                                                        </details>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </AccordionTab>
                    </Accordion>
                </div>

                <!-- Selected Packages Summary -->
                <div v-if="returnForm.selected_packages.length > 0" class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <h4 class="font-bold text-green-800 mb-2 flex items-center gap-2">
                        <i class="pi pi-check-circle"></i>
                        Selected Packages for Return ({{ returnForm.selected_packages.length }})
                    </h4>
                    <div class="flex flex-wrap gap-2">
                        <div 
                            v-for="(pkg, index) in returnForm.selected_packages" 
                            :key="index"
                            class="flex items-center gap-2 bg-white border border-green-300 rounded-full px-3 py-1 text-sm"
                        >
                            <span>{{ pkg.reference }} ({{ pkg.package_count }})</span>
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
                <label for="return_remarks" class="block mb-2 font-semibold">Return Remarks</label>
                <Textarea
                    id="return_remarks"
                    v-model="returnForm.remarks"
                    class="w-full"
                    rows="3"
                    placeholder="Enter reason for return..."
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
                        label="Process Return"
                        icon="pi pi-check"
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
            <Column field="remarks" header="Remarks"></Column>
            <Column field="createdBy.name" header="Created By"></Column>
        </DataTable>

        <template #footer>
            <Button
                label="Close"
                severity="secondary"
                @click="logDialogVisible = false"
            />
        </template>
    </Dialog>
</template>
