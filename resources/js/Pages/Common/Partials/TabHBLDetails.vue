<script setup>
import InfoDisplay from "@/Pages/Common/Components/InfoDisplay.vue";
import SimpleOverviewWidget from "@/Components/Widgets/SimpleOverviewWidget.vue";
import PostSkeleton from "@/Components/PostSkeleton.vue";
import {computed, watch} from "vue";
import Card from 'primevue/card';
import Avatar from 'primevue/avatar';

const props = defineProps({
    hbl: {
        type: Object,
        default: () => ({}),
    },
    pickup: {
        type: Object,
        default: () => ({}),
    },
    isLoading: {
        type: Boolean,
        required: true,
    },
});

watch(
    () => props.pickup,
    (newVal) => {
        console.log("Pickup data updated in child:", newVal);
    },
    { immediate: true }
);

const parsePackageTypes = (str) => {
    if (!str) return [];

    if (Array.isArray(str)) return str;

    try {
        const parsed = JSON.parse(str);
        if (Array.isArray(parsed)) {
            return parsed.map(type => type.trim().replace(/^["']|["']$/g, ''));
        }
    } catch (e) {
        // Fallback: comma-separated string
    }

    return str.split(',').map(type => type.trim().replace(/^["']|["']$/g, ''));
};

const packageTypes = computed(() => parsePackageTypes(props.package_type));
</script>

<template>
    <div v-if="hbl && Object.keys(hbl).length > 0" class="grid grid-cols-12 gap-5">
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
                            {{ hbl?.warehouse }}
                        </p>
                    </div>

                    <div class="grid grid-cols-5 mt-10 gap-1">
                        <InfoDisplay :value="hbl?.reference" label="Job Ref"/>

                        <InfoDisplay :value="hbl?.hbl_number" label="HBL Number"/>

                        <InfoDisplay :value="hbl?.cr_number" label="CR Number"/>

                        <InfoDisplay :value="hbl?.hbl_type" label="Delivery Type"/>

                        <InfoDisplay :value="hbl?.cargo_type" label="Cargo Mode"/>
                    </div>
                </template>
            </Card>

            <div class="grid grid-cols-3 gap-5">
                <PostSkeleton v-if="isLoading" />

                <SimpleOverviewWidget v-else :count="hbl?.packages_count ?? 0" class="bg-slate-100" title="Packages">
                    <i class="ti ti-packages text-emerald-500 text-3xl"></i>
                </SimpleOverviewWidget>

                <PostSkeleton v-if="isLoading" />

                <SimpleOverviewWidget v-else :count="hbl?.packages_sum_volume != null ? hbl.packages_sum_volume.toFixed(3) : '0.00'" class="bg-slate-100" title="Volume">
                    <i class="ti ti-scale text-emerald-500 text-3xl"></i>
                </SimpleOverviewWidget>

                <PostSkeleton v-if="isLoading" />

                <SimpleOverviewWidget v-else :count="hbl?.packages_sum_weight != null ? hbl.packages_sum_weight.toFixed(2) : '0.00'" class="bg-slate-100" title="Weight">
                    <i class="ti ti-weight text-emerald-500 text-3xl"></i>
                </SimpleOverviewWidget>
            </div>

            <PostSkeleton v-if="isLoading" />

            <template v-else>
                <Card
                    v-for="item in hbl.packages"
                    v-if="hbl && hbl.packages && hbl.packages.length > 0" :key="item.id"
                    class="!bg-white !border !border-neutral-300 !shadow-md !rounded-md"
                >
                    <template #content>
                        <div class="flex items-center space-x-2">
                            <i class="ti ti-package text-xl"></i>
                            <p class="text-xl uppercase font-normal">
                                {{ packageTypes ?? '-' }}
                            </p>
                            <i
                                v-tooltip="item.is_loaded ? 'Loaded to Shipment' : 'Not Loaded to Shipment'"
                                :class="item.is_loaded ? 'ti ti-circle-check-filled text-xl text-success' : 'ti ti-circle-x-filled text-xl text-error'"
                            ></i>
                        </div>

                        <div class="grid grid-cols-4 mt-1 gap-2">
                            <InfoDisplay :value="`${item.length ?? 0} CM`" label="Length"/>

                            <InfoDisplay :value="item.width ?? 0" label="Width"/>

                            <InfoDisplay :value="item.height ?? 0" label="Height"/>

                            <InfoDisplay :value="item.quantity ?? 0" label="Quantity"/>

                            <InfoDisplay :value="item.weight.toFixed(2) ?? 0" label="Weight"/>

                            <InfoDisplay :value="`${item.volume.toFixed(3) ?? 0} M.CU`" label="Volume"/>

                            <div class="col-span-2">
                                <InfoDisplay :value="item.remarks ?? '-'" label="Remarks"/>
                            </div>
                        </div>
                    </template>
                </Card>
            </template>
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
                        <Avatar :label="hbl?.hbl_name.charAt(0)" class="mr-2 !bg-emerald-200" size="xlarge" />
                        <div>
                            <p>{{ hbl?.hbl_name }}</p>
                            <p class="text-gray-500">{{ hbl?.contact_number }}</p>
                            <p class="text-gray-500">{{ hbl?.email }}</p>
                        </div>
                    </div>

                    <div class="my-5 space-y-3">
                        <InfoDisplay :value="hbl?.address" label="Address"/>

                        <InfoDisplay :value="hbl?.additional_mobile_number" label="Additional Contact Number"/>

                        <InfoDisplay :value="hbl?.whatsapp_number" label="Whatsapp Number"/>

                        <InfoDisplay :value="hbl?.iq_number" label="IQ Number"/>

                        <InfoDisplay :value="hbl?.nic" label="Passport Number"/>
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
                        <Avatar :label="hbl?.consignee_name.charAt(0)" class="mr-2 !bg-emerald-200" size="xlarge" />
                        <div>
                            <p>{{ hbl?.consignee_name }}</p>
                            <p class="text-gray-500">{{ hbl?.consignee_contact }}</p>
                        </div>
                    </div>

                    <div class="my-5 space-y-3">
                        <InfoDisplay :value="hbl?.consignee_address" label="Address"/>

                        <InfoDisplay :value="hbl?.consignee_nic" label="NIC / PP No"/>

                        <InfoDisplay :value="hbl?.consignee_additional_mobile_number" label="Additional Contact Number"/>

                        <InfoDisplay :value="hbl?.consignee_whatsapp_number" label="Whatsapp Number"/>

                        <InfoDisplay :value="hbl?.consignee_note" label="Note"/>
                    </div>
                </template>
            </Card>
        </div>
    </div>

    <div v-else class="grid grid-cols-12 gap-5">
        <div class="col-span-4 space-y-5">
            <PostSkeleton v-if="isLoading" />

            <Card v-else>
                <template #title>
                    <div class="flex items-center space-x-2">
                        <i class="ti ti-user-pentagon"></i>
                        <span>Shipper Details</span>
                    </div>
                </template>
                <template #content>
                    <div class="flex space-x-3">
                        <Avatar :label="pickup?.name.charAt(0)" class="mr-2 !bg-amber-200" size="xlarge" />
                        <div>
                            <p>{{ pickup?.name }}</p>
                            <p class="text-gray-500">{{ pickup?.contact_number }}</p>
                            <p class="text-gray-500">{{ pickup?.email }}</p>
                        </div>
                    </div>

                    <div class="my-5 space-y-3">
                        <InfoDisplay :value="pickup?.address" label="Address"/>

                        <InfoDisplay :value="pickup?.additional_mobile_number" label="Additional Contact Number"/>

                        <InfoDisplay :value="pickup?.whatsapp_number" label="Whatsapp Number"/>
                    </div>
                </template>
            </Card>
        </div>

        <div class="col-span-8 space-y-4">
            <PostSkeleton v-if="isLoading" />

            <Card v-else class="!bg-amber-50 !border !border-amber-200 !shadow-none">
                <template #content>
                    <div class="grid grid-cols-4 gap-10">
                        <InfoDisplay :value="pickup?.reference" label="Job Ref"/>

                        <InfoDisplay :value="pickup?.cargo_type" label="Cargo Mode"/>

                        <InfoDisplay :value="pickup?.driver" label="Driver"/>

                        <InfoDisplay :value="pickup?.zone" label="Zone"/>

                        <InfoDisplay :value="pickup?.pickup_date" label="Pickup Date"/>

                        <InfoDisplay :value="pickup?.pickup_time_start" label="Pickup Time Start"/>

                        <InfoDisplay :value="pickup?.pickup_time_end" label="Pickup Time End"/>

                        <InfoDisplay :value="pickup?.pickup_type" label="Pickup Type"/>

                        <InfoDisplay :value="pickup?.pickup_note" label="Pickup Note"/>

                        <InfoDisplay :value="pickup?.retry_attempts" label="Retry Attempts"/>

                        <InfoDisplay :value="pickup?.created_by" label="Auth"/>

                        <InfoDisplay :value="pickup?.packages" label="Package Types"/>
                    </div>
                </template>
            </Card>
        </div>
    </div>
</template>
