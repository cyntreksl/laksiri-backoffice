<script setup>
import InfoDisplay from "@/Pages/Common/Components/InfoDisplay.vue";
import PostSkeleton from "@/Components/PostSkeleton.vue";
import Card from 'primevue/card';
import Avatar from 'primevue/avatar';

const props = defineProps({
    courier: {
        type: Object,
        default: () => ({}),
    },
    isLoading: {
        type: Boolean,
        required: true,
    },
});
</script>

<template>
    <div class="grid grid-cols-12 gap-6">
        <!-- Shipper Details -->
        <div class="col-span-12 lg:col-span-6">
            <PostSkeleton v-if="isLoading" />

            <Card v-else class="border h-full">
                <template #title>
                    <div class="flex items-center space-x-2">
                        <i class="ti ti-user-pentagon text-blue-600"></i>
                        <span>Shipper Details</span>
                    </div>
                </template>
                <template #content>
                    <div class="flex items-start gap-4 mb-4">
                        <Avatar
                            :label="courier?.name?.charAt(0) || 'U'"
                            class="!bg-blue-200 flex-shrink-0"
                            size="xlarge"
                            style="width: 64px; height: 64px"
                        />
                        <div class="flex flex-col min-w-0 flex-grow">
                            <p class="font-medium text-gray-900 truncate">{{ courier?.name || 'N/A' }}</p>
                            <p class="text-gray-500 text-sm truncate">{{ courier?.contact_number || 'N/A' }}</p>
                            <p class="text-gray-500 text-sm break-all">{{ courier?.email || 'N/A' }}</p>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <InfoDisplay :value="courier?.address || 'N/A'" label="Address"/>
                        <InfoDisplay :value="courier?.nic || 'N/A'" label="NIC / Passport"/>
                        <InfoDisplay :value="courier?.iq_number || 'N/A'" label="IQ Number"/>
                    </div>
                </template>
            </Card>
        </div>

        <!-- Consignee Details -->
        <div class="col-span-12 lg:col-span-6">
            <PostSkeleton v-if="isLoading" />

            <Card v-else class="border h-full">
                <template #title>
                    <div class="flex items-center space-x-2">
                        <i class="ti ti-user-heart text-green-600"></i>
                        <span>Consignee Details</span>
                    </div>
                </template>
                <template #content>
                    <div class="flex items-start gap-4 mb-4">
                        <Avatar
                            :label="courier?.consignee_name?.charAt(0) || 'U'"
                            class="!bg-green-200 flex-shrink-0"
                            size="xlarge"
                            style="width: 64px; height: 64px"
                        />
                        <div class="flex flex-col min-w-0 flex-grow">
                            <p class="font-medium text-gray-900 truncate">{{ courier?.consignee_name || 'N/A' }}</p>
                            <p class="text-gray-500 text-sm truncate">{{ courier?.consignee_contact || 'N/A' }}</p>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <InfoDisplay :value="courier?.consignee_address || 'N/A'" label="Address"/>
                        <InfoDisplay :value="courier?.consignee_nic || 'N/A'" label="NIC / Passport"/>
                        <InfoDisplay :value="courier?.consignee_note || 'N/A'" label="Note"/>
                    </div>
                </template>
            </Card>
        </div>

        <!-- Courier Information -->
        <div class="col-span-12">
            <PostSkeleton v-if="isLoading" />

            <Card v-else class="!bg-amber-50 !border !border-amber-200 !shadow-none">
                <template #title>
                    <div class="flex items-center space-x-2">
                        <i class="ti ti-truck text-amber-600"></i>
                        <span>Courier Information</span>
                    </div>
                </template>
                <template #content>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5">
                        <InfoDisplay :value="courier?.courier_number || 'N/A'" label="Courier Number"/>
                        <InfoDisplay :value="courier?.cargo_type || 'N/A'" label="Cargo Type"/>
                        <InfoDisplay :value="courier?.hbl_type || 'N/A'" label="HBL Type"/>
                        <InfoDisplay :value="courier?.agent?.company_name || 'N/A'" label="Courier Agent"/>
                        <InfoDisplay :value="courier?.status?.toUpperCase() || 'N/A'" label="Status"/>
                        <InfoDisplay :value="courier?.created_at ? new Date(courier.created_at).toLocaleDateString() : 'N/A'" label="Created Date"/>
                    </div>
                </template>
            </Card>
        </div>

        <!-- Package Details -->
        <div class="col-span-12" v-if="!isLoading && courier?.packages && courier.packages.length > 0">
            <Card class="border">
                <template #title>
                    <div class="flex items-center space-x-2">
                        <i class="ti ti-packages text-purple-600"></i>
                        <span>Package Details ({{ courier.packages.length }} packages)</span>
                    </div>
                </template>
                <template #content>
                    <div class="grid gap-4">
                        <Card
                            v-for="(pkg, index) in courier.packages"
                            :key="pkg.id || index"
                            class="!bg-gray-50 !border !border-gray-200 !shadow-sm"
                        >
                            <template #content>
                                <div class="flex items-center space-x-2 mb-3">
                                    <i class="ti ti-package text-xl text-purple-600"></i>
                                    <p class="text-lg font-medium">
                                        Package {{ index + 1 }} - {{ pkg.type || 'Unknown Type' }}
                                    </p>
                                </div>

                                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
                                    <InfoDisplay :value="`${pkg.length || 0} cm`" label="Length"/>
                                    <InfoDisplay :value="`${pkg.width || 0} cm`" label="Width"/>
                                    <InfoDisplay :value="`${pkg.height || 0} cm`" label="Height"/>
                                    <InfoDisplay :value="pkg.quantity || 0" label="Quantity"/>
                                    <InfoDisplay :value="`${pkg.weight || 0} kg`" label="Weight"/>
                                    <InfoDisplay :value="`${pkg.volume || 0} m³`" label="Volume"/>
                                </div>

                                <div v-if="pkg.remarks" class="mt-3">
                                    <InfoDisplay :value="pkg.remarks" label="Remarks"/>
                                </div>
                            </template>
                        </Card>
                    </div>
                </template>
            </Card>
        </div>

        <!-- Loading or No Packages Message -->
        <div v-else class="col-span-12">
            <PostSkeleton v-if="isLoading" />

            <Card v-else class="border">
                <template #content>
                    <div class="text-center py-8">
                        <i class="ti ti-package-off text-4xl text-gray-400 mb-4"></i>
                        <p class="text-gray-500">No package information available</p>
                    </div>
                </template>
            </Card>
        </div>
    </div>
</template>
