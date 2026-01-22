<script setup>
import Breadcrumb from "@/Components/Breadcrumb.vue";
import InputError from "@/Components/InputError.vue";
import {router, useForm, usePage} from "@inertiajs/vue3";
import {push} from "notivue";
import { ref } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import Tab from "primevue/tab";
import TabPanel from "primevue/tabpanel";
import TabShipment from "@/Pages/Common/Dialog/HBL/Tabs/TabShipment.vue";
import Card from "primevue/card";
import TabHBLDetails from "@/Pages/Common/Dialog/HBL/Tabs/TabHBLDetails.vue";
import IftaLabel from "primevue/iftalabel";
import Skeleton from "primevue/skeleton";
import TabStatus from "@/Pages/Common/Dialog/HBL/Tabs/TabStatus.vue";
import Textarea from "primevue/textarea";
import TabDocuments from "@/Pages/Common/Dialog/HBL/Tabs/TabDocuments.vue";
import TabList from "primevue/tablist";
import Tabs from "primevue/tabs";
import TabPanels from "primevue/tabpanels";
import Button from "primevue/button";
import Checkbox from "primevue/checkbox";
import TabPayments from "@/Pages/Common/Dialog/HBL/Tabs/TabPayments.vue";
import TabHBLCharge from "@/Pages/Common/Dialog/HBL/Tabs/TabHBLCharge.vue";

const props = defineProps({
    customerQueue: {
        type: Object,
        default: () => {}
    },
    reference: {
        type: String,
        required: true,
    },
    hblId: {
        type: Number,
        default: null
    }
})

const hbl = ref({});
const hblTotalSummary = ref({});
const isLoadingHbl = ref(false);
const hblPackages = ref([]);
const isLoading = ref(false);

const fetchHBL = async () => {
    isLoadingHbl.value = true;

    try {
        const response = await fetch(`/hbls/${props.hblId}`, {
            method: "GET",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": usePage().props.csrf
            },
        });

        if (!response.ok) {
            throw new Error('Network response was not ok.');
        } else {
            const data = await response.json();
            hbl.value = data.hbl;
        }

    } catch (error) {
        console.log(error);
    } finally {
        isLoadingHbl.value = false;
    }
}

const getHBLTotalSummary = async () => {
    try {
        const response = await fetch(`/hbls/get-total-summary/${props.hblId}`, {
            method: "GET",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": usePage().props.csrf,
            },
        });

        if (!response.ok) {
            throw new Error("Network response was not ok.");
        }else{
            hblTotalSummary.value = await response.json();
        }
    } catch (error) {
        console.error("Error:", error);
    } finally {
        // isLoading.value = false;
    }
};

if (props.hblId !== null) {
    fetchHBL();
    getHBLTotalSummary();
}

const getHBLPackagesByReference = async () => {
    isLoading.value = true;
    try {
        const response = await fetch(`/get-hbl-packages-by-reference/${props.reference}`, {
            method: "GET",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": usePage().props.csrf
            },
        });

        if (!response.ok) {
            throw new Error('Network response was not ok.');
        } else {
            hblPackages.value = await response.json();
        }

    } catch (error) {
        console.log(error);
    } finally {
        isLoading.value = false;
    }
}

getHBLPackagesByReference();

const updateChecked = (packageId, isChecked) => {
    form.released_packages = { ...form.released_packages, [packageId]: isChecked };
};

const form = useForm({
    customer_queue_id: props.customerQueue.id,
    note: '',
    released_packages: {},
});

const handleUpdateReleaseHBLPackages = () => {
    // Validate that at least one package is selected
    const selectedPackages = Object.values(form.released_packages).filter(Boolean);
    
    if (selectedPackages.length === 0) {
        push.error('Please select at least one package to release.');
        return;
    }

    form.post(route("call-center.examination.store"), {
        onSuccess: () => {
            push.success(`${selectedPackages.length} package(s) released successfully!`);
            
            // Open gate pass in new tab
            const gatePassUrl = route("hbls.download.gate-pass", {
                hbl: props.hblId,
                customer_queue: props.customerQueue.id
            });
            window.open(gatePassUrl, '_blank');
            
            // Navigate to queue list
            router.visit(route("call-center.examination.queue.list"));
            form.reset();
        },
        onError: (errors) => {
            const errorMessage = Object.values(errors).join(', ');
            push.error(errorMessage || 'Something went wrong!');
        },
        preserveScroll: true,
        preserveState: true,
    });
}
</script>

<template>
    <AppLayout title="Release HBL Package">
        <template #header>Release HBL Package</template>

        <Breadcrumb />

        <div class="grid grid-cols-12 gap-5 mt-5">
            <div class="col-span-9">
                <Card>
                    <template #content>
                        <Tabs value="0">
                            <TabList>
                                <Tab value="0">
                                    <a class="flex items-center gap-2 text-inherit">
                                        <i class="pi pi-info-circle"/>
                                        <span>Details</span>
                                    </a>
                                </Tab>
                                <Tab v-if="Object.keys(hbl).length !== 0" value="1">
                                    <a class="flex items-center gap-2 text-inherit">
                                        <i class="pi pi-dollar"/>
                                        <span>Charges</span>
                                    </a>
                                </Tab>
                                <Tab value="2">
                                    <a class="flex items-center gap-2 text-inherit">
                                        <i class="pi pi-wallet" />
                                        <span>Payments</span>
                                    </a>
                                </Tab>
                                <Tab v-if="Object.keys(hbl).length !== 0" value="3">
                                    <a class="flex items-center gap-2 text-inherit">
                                        <i class="pi pi-truck"/>
                                        <span>Shipment</span>
                                    </a>
                                </Tab>
                                <Tab value="4">
                                    <a class="flex items-center gap-2 text-inherit">
                                        <i class="pi pi-chart-bar"/>
                                        <span>Status & Audit</span>
                                    </a>
                                </Tab>
                                <Tab value="5">
                                    <a class="flex items-center gap-2 text-inherit">
                                        <i class="pi pi-file"/>
                                        <span>Documents</span>
                                    </a>
                                </Tab>
                            </TabList>
                            <TabPanels>
                                <TabPanel value="0">
                                    <TabHBLDetails :compact="true" :hbl="hbl" :is-loading="isLoading"/>
                                </TabPanel>
                                <TabPanel value="1">
                                    <TabHBLCharge :hbl="hbl"></TabHBLCharge>
                                </TabPanel>
                                <TabPanel value="2">
                                    <TabPayments :hbl="hbl"></TabPayments>
                                </TabPanel>
                                <TabPanel value="3">
                                    <TabShipment v-if="hbl" :hbl="hbl"/>
                                </TabPanel>
                                <TabPanel value="4">
                                    <TabStatus v-if="hbl" :hbl="hbl"/>
                                </TabPanel>
                                <TabPanel value="5">
                                    <TabDocuments v-if="hbl" :hbl-id="hbl.id"/>
                                </TabPanel>
                            </TabPanels>
                        </Tabs>
                    </template>
                </Card>
            </div>

            <div class="col-span-3">
                <Skeleton v-if="isLoading" height="350px" width="100%"></Skeleton>

                <Card v-else>
                    <template #title>
                        Release HBL Packages
                    </template>
                    <template #content>
                        <!-- Package Status Summary -->
                        <div v-if="hblPackages.length > 0" class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-4">
                            <div class="text-sm">
                                <div class="font-semibold text-blue-800 mb-1">Released from Bonded Area</div>
                                <div class="text-blue-700">{{ hblPackages.length }} package(s) available for examination</div>
                            </div>
                        </div>

                        <!-- No packages warning -->
                        <div v-if="hblPackages.length === 0" class="bg-yellow-50 border border-yellow-300 rounded-lg p-4 mb-4">
                            <div class="flex items-start gap-3">
                                <i class="pi pi-exclamation-triangle text-yellow-600 text-xl"></i>
                                <div>
                                    <p class="font-semibold text-yellow-800">No Packages Available</p>
                                    <p class="text-sm text-yellow-700">No packages have been released from the Bonded Area yet. Please release packages from the Package Queue first.</p>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-5 mt-3">
                            <div class="space-y-3">
                                <div 
                                    v-for="(p, index) in hblPackages" 
                                    :key="p.id" 
                                    class="flex items-start gap-3 p-3 rounded-lg border border-gray-200 bg-white hover:bg-gray-50"
                                >
                                    <Checkbox 
                                        :checked="form.released_packages[p.id] || false" 
                                        :input-id="`package-${p.id}`"
                                        :value="p.id"  
                                        @change="(event) => updateChecked(p.id, event.target.checked)" 
                                    />
                                    <label :for="`package-${p.id}`" class="cursor-pointer flex-1">
                                        <div class="font-medium text-gray-900">{{ p.package_type }}</div>
                                        <div class="text-xs text-gray-600 mt-1 space-y-0.5">
                                            <div>Qty: {{ p.quantity }} | Size: {{ p.length }}×{{ p.width }}×{{ p.height }}</div>
                                            <div v-if="p.weight">Weight: {{ p.weight }}</div>
                                            <div v-if="p.bond_storage_number" class="text-blue-600">Bond: {{ p.bond_storage_number }}</div>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <div>
                                <IftaLabel>
                                    <Textarea id="description" v-model="form.note" class="w-full" cols="30"
                                              placeholder="Type note here..." rows="5" style="resize: none"
                                              variant="filled"/>
                                    <label for="description">Note</label>
                                </IftaLabel>
                                <InputError :message="form.errors.note"/>
                            </div>
                        </div>

                        <div class="text-right mt-3">
                            <Button 
                                :class="{ 'opacity-25': form.processing || hblPackages.length === 0 }"
                                :disabled="form.processing || hblPackages.length === 0" 
                                icon="pi pi-arrow-right" 
                                icon-pos="right"
                                label="Release & Create Gate Pass" 
                                size="small" 
                                @click="handleUpdateReleaseHBLPackages"
                            />
                        </div>
                    </template>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
