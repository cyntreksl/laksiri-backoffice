<script setup>
import InfoDisplay from "@/Pages/Common/Components/InfoDisplay.vue";
import SimpleOverviewWidget from "@/Components/Widgets/SimpleOverviewWidget.vue";
import PostSkeleton from "@/Components/PostSkeleton.vue";
import {computed} from "vue";
import Card from 'primevue/card';
import Avatar from 'primevue/avatar';

const props = defineProps({
    mhbl: {
        type: Object,
        default: () => ({}),
    },
    isLoading: {
        type: Boolean,
        required: true,
    }
});

// Computed property to calculate total volume
const totalVolume = computed(() => {
    if (!props.mhbl?.hbls) return "0.00";

    // Calculate the sum of all package volumes
    const total = props.mhbl.hbls.reduce((totalHBL, hbl) => {
        if (hbl.packages) {
            return totalHBL + hbl.packages.reduce((totalPkg, pkg) => totalPkg + (pkg.volume || 0), 0);
        }
        return totalHBL;
    }, 0);

    return total.toFixed(3);
});

// Computed property to calculate total weight
const totalWeight = computed(() => {
    if (!props.mhbl?.hbls) return "0.00";

    // Calculate the sum of all package volumes
    const total = props.mhbl.hbls.reduce((totalHBL, hbl) => {
        if (hbl.packages) {
            return totalHBL + hbl.packages.reduce((totalPkg, pkg) => totalPkg + (pkg.weight || 0), 0);
        }
        return totalHBL;
    }, 0);

    return total.toFixed(2);
});
</script>

<template>
    <div v-if="mhbl && Object.keys(mhbl).length > 0" class="grid grid-cols-12 gap-5">
        <div class="col-span-6 space-y-4">
            <PostSkeleton v-if="isLoading" />

            <Card v-else class="!bg-emerald-50 !border !border-emerald-200 !shadow-none">
                <template #content>
                    <div class="flex items-center space-x-5">
                        <p class="text-3xl uppercase font-normal">
                            {{ $page.props.currentBranch.name }}
                        </p>
                        <i class="pi pi-arrow-right"></i>
                        <p class="text-3xl uppercase font-normal">
                            {{ mhbl?.warehouse?.name }}
                        </p>
                    </div>

                    <div class="grid grid-cols-5 mt-10 gap-1">
                        <InfoDisplay :value="mhbl?.reference" label="Job Ref"/>

                        <InfoDisplay :value="mhbl?.hbl_number" label="MHBL Number"/>

                        <InfoDisplay label="Delivery Type" value="Gift"/>

                        <InfoDisplay :value="mhbl?.cargo_type" label="Cargo Mode"/>
                    </div>
                </template>
            </Card>

            <div class="grid grid-cols-3 gap-5">
                <PostSkeleton v-if="isLoading" />

                <SimpleOverviewWidget v-else :count="mhbl?.hbls.length ?? 0" class="bg-slate-100" title="HBLs">
                    <i class="ti ti-app-window text-emerald-500 text-3xl"></i>
                </SimpleOverviewWidget>

                <PostSkeleton v-if="isLoading" />

                <SimpleOverviewWidget v-else :count="totalVolume" class="bg-slate-100" title="Volume">
                    <i class="ti ti-scale text-emerald-500 text-3xl"></i>
                </SimpleOverviewWidget>

                <PostSkeleton v-if="isLoading" />

                <SimpleOverviewWidget v-else :count="totalWeight" class="bg-slate-100" title="Weight">
                    <i class="ti ti-weight text-emerald-500 text-3xl"></i>
                </SimpleOverviewWidget>
            </div>
        </div>

        <div class="col-span-3 space-y-5">
            <PostSkeleton v-if="isLoading" />

            <Card v-else class="border">
                <template #title>
                    <div class="flex items-center space-x-2">
                        <i class="ti ti-user-pentagon"></i>
                        <span>Shipper Details</span>
                    </div>
                </template>
                <template #content>
                    <div class="flex space-x-3">
                        <Avatar :label="mhbl?.shipper.name.charAt(0)" class="mr-2 !bg-emerald-200" size="xlarge" />
                        <div>
                            <p>{{ mhbl?.shipper.name }}</p>
                            <p class="text-gray-500">{{ mhbl?.shipper.mobile_number }}</p>
                            <p class="text-gray-500">{{ mhbl?.shipper?.email }}</p>
                        </div>
                    </div>

                    <div class="my-5 space-y-3">
                        <InfoDisplay :value="mhbl?.shipper.address" label="Address"/>

                        <InfoDisplay :value="mhbl?.shipper.iq_number" label="IQ Number"/>

                        <InfoDisplay :value="mhbl?.shipper.pp_or_nic_no" label="Passport Number"/>

                        <InfoDisplay :value="mhbl?.shipper.location" label="Location"/>

                        <InfoDisplay :value="mhbl?.warehouse.name" label="Warehouse Location"/>
                    </div>
                </template>
            </Card>
        </div>

        <div class="col-span-3 space-y-5">
            <PostSkeleton v-if="isLoading" />

            <Card v-else class="border">
                <template #title>
                    <div class="flex items-center space-x-2">
                        <i class="ti ti-user-pentagon"></i>
                        <span>Consignee Details</span>
                    </div>
                </template>
                <template #content>
                    <div class="flex space-x-3">
                        <Avatar :label="mhbl?.consignee.name.charAt(0)" class="mr-2 !bg-emerald-200" size="xlarge" />
                        <div>
                            <p>{{ mhbl?.consignee.name }}</p>
                            <p class="text-gray-500">{{ mhbl?.consignee.mobile_number }}</p>
                        </div>
                    </div>

                    <div class="my-5 space-y-3">
                        <InfoDisplay :value="mhbl?.consignee.address" label="Address"/>

                        <InfoDisplay :value="mhbl?.consignee.pp_or_nic_no" label="NIC / PP No"/>
                    </div>
                </template>
            </Card>
        </div>
    </div>
</template>
