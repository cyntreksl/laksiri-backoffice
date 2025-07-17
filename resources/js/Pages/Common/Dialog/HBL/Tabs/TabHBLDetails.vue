<script setup>
import InfoDisplay from "@/Pages/Common/Components/InfoDisplay.vue";
import SimpleOverviewWidget from "@/Components/Widgets/SimpleOverviewWidget.vue";
import PostSkeleton from "@/Components/PostSkeleton.vue";
import {watch} from "vue";
import Card from 'primevue/card';
import Avatar from 'primevue/avatar';
import InfoDisplayChip from "@/Pages/Common/Components/InfoDisplayChip.vue";
import Button from "primevue/button";
import {router} from "@inertiajs/vue3";
import {push} from "notivue";
import {useConfirm} from "primevue/useconfirm";

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

console.log(props.hbl?.packages)

const confirm = useConfirm();

const handleRTFHBLPackage = (packageId) => {
    confirm.require({
        message: 'Would you like to RTF this package?',
        header: 'RTF Package?',
        icon: 'pi pi-info-circle',
        rejectLabel: 'Cancel',
        rejectProps: {
            label: 'Cancel',
            severity: 'secondary',
            outlined: true
        },
        acceptProps: {
            label: 'Sure, RTF',
            severity: 'warn'
        },
        accept: () => {
            router.post(route("hbl-packages.set.rtf", packageId), {}, {
                preserveScroll: true,
                onSuccess: () => {
                    push.success('Package going to RTF');
                    window.location.reload();
                },
                onError: () => {
                    push.error('Something went to wrong!');
                }
            })
        },
        reject: () => {
        }
    })
}

const handleUndoRTFHBLPackage = (packageId) => {
    confirm.require({
        message: 'Would you like to Undo RTF for this package?',
        header: 'Undo RTF Package?',
        icon: 'pi pi-info-circle',
        rejectLabel: 'Cancel',
        rejectProps: {
            label: 'Cancel',
            severity: 'secondary',
            outlined: true
        },
        acceptProps: {
            label: 'Sure, Remove RTF',
            severity: 'warn'
        },
        accept: () => {
            router.post(route("hbl-packages.unset.rtf", packageId), {}, {
                preserveScroll: true,
                onSuccess: () => {
                    push.success('Undo RTF for this package successfully!');
                    window.location.reload();
                },
                onError: () => {
                    push.error('Something went to wrong!');
                }
            })
        },
        reject: () => {
        }
    })
}

watch(
    () => props.pickup,
    (newVal) => {
        console.log("Pickup data updated in child:", newVal);
    },
    { immediate: true }
);
</script>

<template>
    <div v-if="hbl && Object.keys(hbl).length > 0" class="grid grid-cols-12 gap-5 lg:gap-5 md:gap-3 sm:gap-2">
        <div class="col-span-12 lg:col-span-6 md:col-span-12 sm:col-span-12 space-y-4">
            <PostSkeleton v-if="isLoading"/>

            <Card v-else class="!bg-emerald-50 !border !border-emerald-200 !shadow-none">
                <template #content>
                    <div class="flex items-center space-x-5">
                        <div v-if="hbl?.latest_rtf_record?.is_rtf" class="flex space-x-2">
                            <i v-tooltip.left="`RTF`" class="ti ti-lock-square-rounded-filled text-2xl text-red-500"></i>
                        </div>
                        <p class="text-3xl uppercase font-normal">
                            {{ hbl?.branch.name }}
                        </p>
                        <i class="pi pi-arrow-right"></i>
                        <p class="text-3xl uppercase font-normal">
                            {{ hbl?.warehouse }}
                        </p>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 mt-10 gap-1">
                        <InfoDisplay :value="hbl?.reference" label="Job Ref"/>

                        <InfoDisplay :value="hbl?.hbl_number" label="HBL Number"/>

                        <InfoDisplay :value="hbl?.cr_number" label="CR Number"/>

                        <InfoDisplay :value="hbl?.hbl_type" label="Delivery Type"/>

                        <InfoDisplay :value="hbl?.cargo_type" label="Cargo Mode"/>
                    </div>
                </template>
            </Card>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-5">
                <PostSkeleton v-if="isLoading"/>

                <SimpleOverviewWidget v-else :count="hbl?.packages_count ?? 0" class="bg-slate-100" title="Packages">
                    <i class="ti ti-packages text-emerald-500 text-3xl"></i>
                </SimpleOverviewWidget>

                <PostSkeleton v-if="isLoading"/>

                <SimpleOverviewWidget v-else
                                      :count="hbl?.packages_sum_volume != null ? hbl.packages_sum_volume.toFixed(3) : '0.00'"
                                      class="bg-slate-100" title="Volume">
                    <i class="ti ti-scale text-emerald-500 text-3xl"></i>
                </SimpleOverviewWidget>

                <PostSkeleton v-if="isLoading"/>

                <SimpleOverviewWidget v-else
                                      :count="hbl?.packages_sum_actual_weight != null ? hbl.packages_sum_actual_weight.toFixed(2) : '0.00'"
                                      class="bg-slate-100" title="Weight">
                    <i class="ti ti-weight text-emerald-500 text-3xl"></i>
                </SimpleOverviewWidget>
            </div>

            <PostSkeleton v-if="isLoading"/>

            <template v-else>
                <Card
                    v-for="item in hbl.packages"
                    v-if="hbl && hbl.packages && hbl.packages.length > 0" :key="item.id"
                    :class="[item.is_hold ? '!bg-orange-100' : '!bg-white', '!border !border-neutral-300 !shadow-md !rounded-md']"
                >
                    <template #content>
                        <div v-if="item.is_hold" class="flex items-center text-xs text-orange-800">
                            <i class="pi pi-exclamation-triangle mr-1"></i>
                            <span>This Package is on hold</span>
                        </div>

                        <div v-if="item.is_rtf" class="flex items-center text-xs text-orange-800">
                            <i class="pi pi-exclamation-triangle mr-1"></i>
                            <span>This Package is on RTF</span>
                        </div>

                        <div class="flex items-center space-x-2">
                            <div class="flex items-center space-x-2">
                                <i v-if="item?.latest_rtf_record?.is_rtf" v-tooltip.left="`RTF`" class="ti ti-lock-square-rounded-filled text-2xl text-red-500"></i>
                                <i class="ti ti-package text-xl"></i>
                                <p class="text-xl uppercase font-normal">
                                    {{ item.package_type ?? '-' }}
                                </p>
                                <i
                                    v-tooltip="item.is_loaded ? 'Loaded to Shipment' : 'Not Loaded to Shipment'"
                                    :class="item.is_loaded ? 'ti ti-circle-check-filled text-xl text-success' : 'ti ti-circle-x-filled text-xl text-error'"
                                ></i>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 mt-1 gap-2">
                            <InfoDisplay :value="`${item.length ?? 0} CM`" label="Length"/>

                            <InfoDisplay :value="item.width ?? 0" label="Width"/>

                            <InfoDisplay :value="item.height ?? 0" label="Height"/>

                            <InfoDisplay :value="item.quantity ?? 0" label="Quantity"/>

                            <InfoDisplay :value="item.actual_weight.toFixed(2) ?? 0" label="Weight"/>

                            <InfoDisplay :value="`${item.volume.toFixed(3) ?? 0} M.CU`" label="Volume"/>

                            <div class="col-span-2">
                                <InfoDisplay :value="item.remarks ?? '-'" label="Remarks"/>
                            </div>
                        </div>

                        <div class="mt-3">
                            <template v-if="$page.props.user.permissions.includes('set_rtf')">
                                <Button v-if="!item?.latest_rtf_record?.is_rtf" icon="pi pi-lock" label="Set RTF Package"
                                        severity="warn" size="small" variant="outlined" @click.prevent="handleRTFHBLPackage(item.id)" />
                            </template>

                            <template v-if="$page.props.user.permissions.includes('lift_rtf')">
                                <Button v-if="item?.latest_rtf_record?.is_rtf" icon="pi pi-unlock" label="Lift RTF Package"
                                        severity="warn" size="small" variant="outlined" @click.prevent="handleUndoRTFHBLPackage(item.id)" />
                            </template>
                        </div>
                    </template>
                </Card>
            </template>
        </div>

        <div class="col-span-12 lg:col-span-3 md:col-span-6 sm:col-span-12 space-y-5">
            <PostSkeleton v-if="isLoading"/>

            <Card v-else class="border">
                <template #title>
                    <div class="flex items-center space-x-2">
                        <i class="ti ti-user-pentagon"></i>
                        <span>Shipper Details</span>
                    </div>
                </template>
                <template #content>
                    <div class="flex items-start gap-4">
                        <!-- Fixed width avatar -->
                        <Avatar
                            :label="hbl?.hbl_name?.charAt(0)"
                            class="!bg-emerald-200 flex-shrink-0"
                            size="xlarge"
                            style="width: 64px; height: 64px"
                        />

                        <!-- Flexible content area -->
                        <div class="flex flex-col min-w-0 flex-grow">
                            <p class="font-medium text-gray-900 truncate">{{ hbl?.hbl_name }}</p>
                            <p class="text-gray-500 text-sm truncate">{{ hbl?.contact_number }}</p>
                            <p class="text-gray-500 text-sm break-all">{{ hbl?.email }}</p>
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

        <div class="col-span-12 lg:col-span-3 md:col-span-6 sm:col-span-12 space-y-5">
            <PostSkeleton v-if="isLoading"/>

            <Card v-else class="border">
                <template #title>
                    <div class="flex items-center space-x-2">
                        <i class="ti ti-user-pentagon"></i>
                        <span>Consignee Details</span>
                    </div>
                </template>
                <template #content>
                    <div class="flex items-start gap-4">
                        <!-- Fixed width avatar -->
                        <Avatar
                            :label="hbl?.consignee_name.charAt(0)"
                            class="!bg-emerald-200 flex-shrink-0"
                            size="xlarge"
                            style="width: 64px; height: 64px"
                        />

                        <!-- Flexible content area -->
                        <div class="flex flex-col min-w-0 flex-grow">
                            <p class="font-medium text-gray-900 truncate">{{ hbl?.consignee_name }}</p>
                            <p class="text-gray-500 text-sm truncate">{{ hbl?.consignee_contact }}</p>
                        </div>
                    </div>

                    <div class="my-5 space-y-3">
                        <InfoDisplay :value="hbl?.consignee_address" label="Address"/>

                        <InfoDisplay :value="hbl?.consignee_nic" label="NIC / PP No"/>

                        <InfoDisplay :value="hbl?.consignee_additional_mobile_number"
                                     label="Additional Contact Number"/>

                        <InfoDisplay :value="hbl?.consignee_whatsapp_number" label="Whatsapp Number"/>

                        <InfoDisplay :value="hbl?.consignee_note" label="Note"/>
                    </div>
                </template>
            </Card>
        </div>
    </div>

    <div v-else class="grid grid-cols-12 gap-5">
        <div class="col-span-12 lg:col-span-4 md:col-span-6 sm:col-span-12 space-y-5">
            <PostSkeleton v-if="isLoading"/>

            <Card v-else>
                <template #title>
                    <div class="flex items-center space-x-2">
                        <i class="ti ti-user-pentagon"></i>
                        <span>Shipper Details</span>
                    </div>
                </template>
                <template #content>
                    <div class="flex space-x-3">
                        <Avatar :label="pickup?.name?.charAt(0) ?? ''" class="mr-2 !bg-amber-200" size="xlarge"/>
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

            <!-- Deletion Details Card -->
            <Card v-if="pickup?.delete_main_reason || pickup?.delete_remarks" class="relative overflow-hidden !border !border-red-200 !bg-red-50 !shadow-sm">
                <template #title>
                    <div class="flex items-center gap-2 pl-2 border-l-4 border-red-500 py-1">
                        <i class="ti ti-alert-circle-filled text-2xl text-red-500"></i>
                        <span class="font-semibold text-base text-red-700">Deletion Details</span>
                    </div>
                </template>
                <template #content>
                    <div class="px-2 py-2">
                        <div v-if="pickup?.delete_main_reason" class="mb-2">
                            <div class="text-xs text-red-500 font-medium uppercase tracking-wide">Delete Reason</div>
                            <div class="text-base font-semibold text-red-800">{{ pickup.delete_main_reason }}</div>
                        </div>
                        <div v-if="pickup?.delete_main_reason && pickup?.delete_remarks" class="my-2">
                            <hr class="border-t border-dashed border-red-200" />
                        </div>
                        <div v-if="pickup?.delete_remarks">
                            <div class="text-xs text-red-500 font-medium uppercase tracking-wide">Delete Remarks</div>
                            <div class="text-base text-red-700">{{ pickup.delete_remarks }}</div>
                        </div>
                    </div>
                </template>
            </Card>
            <!-- End Deletion Details Card -->
        </div>

        <div class="col-span-12 lg:col-span-8 md:col-span-6 sm:col-span-12 space-y-4">
            <PostSkeleton v-if="isLoading"/>

            <Card v-else class="!bg-amber-50 !border !border-amber-200 !shadow-none">
                <template #content>
                    <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5 lg:gap-10">
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

                        <InfoDisplayChip :value="pickup?.packages" label="Package Types"/>
                    </div>
                </template>
            </Card>
        </div>
    </div>
</template>
