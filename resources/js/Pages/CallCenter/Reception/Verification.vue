<script setup>
import {router, useForm, usePage} from "@inertiajs/vue3";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import InputError from "@/Components/InputError.vue";
import {ref} from "vue";
import {push} from "notivue";
import moment from "moment";
import AppLayout from "@/Layouts/AppLayout.vue";
import Tab from "primevue/tab";
import TabPanel from "primevue/tabpanel";
import TabShipment from "@/Pages/Common/Dialog/HBL/Tabs/TabShipment.vue";
import TabStatus from "@/Pages/Common/Dialog/HBL/Tabs/TabStatus.vue";
import TabDocuments from "@/Pages/Common/Dialog/HBL/Tabs/TabDocuments.vue";
import TabList from "primevue/tablist";
import TabHBLDetails from "@/Pages/Common/Dialog/HBL/Tabs/TabHBLDetails.vue";
import TabPanels from "primevue/tabpanels";
import Tabs from "primevue/tabs";
import TabHBLPayments from "@/Pages/Common/Dialog/HBL/Tabs/TabHBLPayments.vue";
import Card from 'primevue/card';
import Skeleton from 'primevue/skeleton';
import Button from 'primevue/button';
import {useConfirm} from "primevue/useconfirm";
import Checkbox from "primevue/checkbox";
import Textarea from "primevue/textarea";
import IftaLabel from "primevue/iftalabel";

const props = defineProps({
    verificationDocuments: {
        type: Array,
        default: () => []
    },
    customerQueue: {
        type: Object,
        default: () => {}
    },
    hblId: {
        type: Number,
        default: null
    },
})

const hbl = ref({});
const hblTotalSummary = ref({});
const isLoadingHbl = ref(false);
const paymentRecord = ref([]);
const isLoading = ref(false);
const currencyCode = ref(usePage().props.currentBranch.currency_symbol || "SAR");
const confirm = useConfirm();

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
    }
};

if (props.hblId !== null) {
    fetchHBL();
    getHBLTotalSummary();
}

const form = useForm({
    customer_queue: props.customerQueue,
    is_checked: {},
    note: ''
});

const updateChecked = (doc, isChecked) => {
    form.is_checked = { ...form.is_checked, [doc]: isChecked };
};

const handleReceptionVerify = () => {
    if (Object.keys(form.is_checked).length === 0) {
        push.error('Please check the documents first!');
        return 0;
    }

    confirm.require({
        message: 'Are you sure to verify this customer?',
        header: 'Verify?',
        icon: 'pi pi-info-circle',
        rejectLabel: 'Cancel',
        rejectProps: {
            label: 'Cancel',
            severity: 'secondary',
            outlined: true
        },
        acceptProps: {
            label: 'Verify',
            severity: 'success'
        },
        accept: () => {
            form.post(route("call-center.reception.store"), {
                onSuccess: () => {
                    router.visit(route("call-center.reception.queue.list"));
                    form.reset();
                    push.success('Verified Successfully!');
                },
                onError: () => {
                    push.error('Something went to wrong!');
                },
                preserveScroll: true,
                preserveState: true,
            });
        },
        reject: () => {
        }
    });
}

const getHBLPayments = async () => {
    isLoading.value = true;
    try {
        const response = await fetch(`/call-center/get-hbl-pricing/${props.customerQueue?.token_id}`, {
            method: "GET",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": usePage().props.csrf,
            },
        });

        if (!response.ok) {
            throw new Error("Network response was not ok.");
        }

        paymentRecord.value = await response.json();
    } catch (error) {
        console.error("Error:", error);
    } finally {
        isLoading.value = false;
    }
};

getHBLPayments();
</script>

<template>
    <AppLayout title="Reception Verification">
        <template #header>Reception Verification</template>

        <Breadcrumb />

        <div class="grid grid-cols-12 gap-5 mt-5">
            <div class="col-span-9">
                <Card>
                    <template #content>
                        <Tabs value="0">
                            <TabList>
                                <Tab value="0">
                                    <a class="flex items-center gap-2 text-inherit">
                                        <i class="pi pi-info-circle" />
                                        <span>Details</span>
                                    </a>
                                </Tab>
                                <Tab v-if="Object.keys(hbl).length !== 0" value="1">
                                    <a class="flex items-center gap-2 text-inherit">
                                        <i class="pi pi-dollar" />
                                        <span>Payments</span>
                                    </a>
                                </Tab>
                                <Tab v-if="Object.keys(hbl).length !== 0" value="2">
                                    <a class="flex items-center gap-2 text-inherit">
                                        <i class="pi pi-truck" />
                                        <span>Shipment</span>
                                    </a>
                                </Tab>
                                <Tab value="3">
                                    <a class="flex items-center gap-2 text-inherit">
                                        <i class="pi pi-chart-bar" />
                                        <span>Status & Audit</span>
                                    </a>
                                </Tab>
                                <Tab value="4">
                                    <a class="flex items-center gap-2 text-inherit">
                                        <i class="pi pi-file" />
                                        <span>Documents</span>
                                    </a>
                                </Tab>
                            </TabList>
                            <TabPanels>
                                <TabPanel value="0">
                                    <TabHBLDetails :hbl="hbl" :is-loading="isLoading" />
                                </TabPanel>
                                <TabPanel value="1">
                                    <TabHBLPayments :hbl="hbl" :hbl-total-summary="hblTotalSummary"/>
                                </TabPanel>
                                <TabPanel value="2">
                                    <TabShipment v-if="hbl" :hbl="hbl" />
                                </TabPanel>
                                <TabPanel value="3">
                                    <TabStatus v-if="hbl" :hbl="hbl" />
                                </TabPanel>
                                <TabPanel value="4">
                                    <TabDocuments v-if="hbl" :hbl-id="hbl.id"/>
                                </TabPanel>
                            </TabPanels>
                        </Tabs>
                    </template>
                </Card>
            </div>

            <div class="col-span-3">
                <Skeleton v-if="isLoading" height="350px" width="100%"></Skeleton>

                <Card class="shadow-lg border-0 overflow-hidden">
                    <template #content>
                        <div v-if="Object.keys(paymentRecord).length > 0" class="space-y-6">
                            <!-- Header -->
                            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 -m-6 mb-6 p-6 border-b border-blue-100">
                                <div class="flex items-center gap-3">
                                    <div class="p-2 bg-blue-100 rounded-lg">
                                        <i class="pi pi-wallet text-blue-600 text-lg"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-900">Payment Summary</h3>
                                        <p class="text-sm text-gray-600">Transaction overview</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Amount Details -->
                            <div class="space-y-4">
                                <!-- Total Amount -->
                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl border border-gray-100">
                                    <div class="flex items-center gap-3">
                                        <div class="p-2 bg-blue-100 rounded-lg">
                                            <i class="pi pi-calculator text-blue-600"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-600">Total Amount</p>
                                            <p class="text-xs text-gray-500">Full invoice total</p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-xl font-bold text-gray-900">{{ currencyCode }} {{ parseFloat(paymentRecord.grand_total).toFixed(2) }}</p>
                                    </div>
                                </div>

                                <!-- Paid Amount -->
                                <div class="flex items-center justify-between p-4 bg-green-50 rounded-xl border border-green-100">
                                    <div class="flex items-center gap-3">
                                        <div class="p-2 bg-green-100 rounded-lg">
                                            <i class="pi pi-check-circle text-green-600"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-600">Paid Amount</p>
                                            <p class="text-xs text-gray-500">Amount received</p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-xl font-bold text-green-700">{{ currencyCode }} {{ parseFloat(hbl.paid_amount).toFixed(2) }}</p>
                                    </div>
                                </div>

                                <!-- Outstanding -->
                                <div class="flex items-center justify-between p-4 rounded-xl border"
                                     :class="(paymentRecord.grand_total - hbl.paid_amount) > 0 ? 'bg-orange-50 border-orange-100' : 'bg-green-50 border-green-100'">
                                    <div class="flex items-center gap-3">
                                        <div class="p-2 rounded-lg"
                                             :class="(paymentRecord.grand_total - hbl.paid_amount) > 0 ? 'bg-orange-100' : 'bg-green-100'">
                                            <i class="text-lg"
                                               :class="(paymentRecord.grand_total - hbl.paid_amount) > 0 ? 'pi pi-exclamation-triangle text-orange-600' : 'pi pi-check text-green-600'"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-600">Outstanding</p>
                                            <p class="text-xs text-gray-500">Remaining balance</p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-xl font-bold"
                                           :class="(paymentRecord.grand_total - hbl.paid_amount) > 0 ? 'text-orange-700' : 'text-green-700'">
                                            {{ currencyCode }} {{ (paymentRecord.grand_total - hbl.paid_amount).toFixed(2) }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Status and Date -->
                            <div class="border-t border-gray-100 pt-4 space-y-4">
                                <!-- Status -->
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="p-1.5 bg-blue-100 rounded-lg">
                                            <i class="pi pi-info-circle text-blue-600 text-sm"></i>
                                        </div>
                                        <span class="text-sm font-medium text-gray-600">Payment Status</span>
                                    </div>
                                    <div>
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium"
                                              :class="{
                                                  'bg-green-100 text-green-800': paymentRecord.status === 'Paid' || paymentRecord.status === 'Completed',
                                                  'bg-orange-100 text-orange-800': paymentRecord.status === 'Partial' || paymentRecord.status === 'Pending',
                                                  'bg-red-100 text-red-800': paymentRecord.status === 'Unpaid' || paymentRecord.status === 'Failed',
                                                  'bg-gray-100 text-gray-800': !['Paid', 'Completed', 'Partial', 'Pending', 'Unpaid', 'Failed'].includes(paymentRecord.status)
                                              }">
                                            <i class="pi pi-circle-fill text-xs mr-1.5"
                                               :class="{
                                                   'text-green-500': paymentRecord.status === 'Paid' || paymentRecord.status === 'Completed',
                                                   'text-orange-500': paymentRecord.status === 'Partial' || paymentRecord.status === 'Pending',
                                                   'text-red-500': paymentRecord.status === 'Unpaid' || paymentRecord.status === 'Failed',
                                                   'text-gray-500': !['Paid', 'Completed', 'Partial', 'Pending', 'Unpaid', 'Failed'].includes(paymentRecord.status)
                                               }"></i>
                                            {{ paymentRecord.status }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Last Updated -->
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="p-1.5 bg-gray-100 rounded-lg">
                                            <i class="pi pi-clock text-gray-600 text-sm"></i>
                                        </div>
                                        <span class="text-sm font-medium text-gray-600">Last Updated</span>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-semibold text-gray-800">{{ moment(paymentRecord.updated_at).format('MMM DD, YYYY') }}</p>
                                        <p class="text-xs text-gray-500">{{ moment(paymentRecord.updated_at).format('h:mm A') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Empty State -->
                        <div v-else class="text-center py-12">
                            <div class="flex flex-col items-center gap-4">
                                <div class="p-4 bg-gray-100 rounded-full">
                                    <i class="pi pi-wallet text-gray-400 text-3xl"></i>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-700 mb-2">No Payment Records</h3>
                                    <p class="text-sm text-gray-500">Payment information will appear here once available.</p>
                                </div>
                            </div>
                        </div>
                    </template>
                </Card>

                <Card class="mt-5">
                    <template #content>
                        <div class="grid grid-cols-2 gap-5 mt-3">
                            <div class="space-y-4">
                                <div v-for="(doc, index) in verificationDocuments" :key="index" class="flex items-center gap-2">
                                    <Checkbox :checked="form.is_checked[doc] || false" :input-id="`${doc}-${index}`"
                                              :value="doc" @change="(event) => updateChecked(doc, event.target.checked)" />
                                    <label :for="`${doc}-${index}`" class="cursor-pointer">
                                        {{ doc }}
                                    </label>
                                </div>
                            </div>

                            <div class="col-span-2">
                                <IftaLabel>
                                    <Textarea id="description" v-model="form.note" class="w-full" cols="30" placeholder="Type note here..." rows="5" style="resize: none" />
                                    <label for="description">Note</label>
                                </IftaLabel>
                                <InputError :message="form.errors.note" />
                            </div>
                        </div>

                        <div class="text-right mt-3">
                            <Button icon="pi pi-check"
                                    label="Verify" size="small" @click="handleReceptionVerify"/>
                        </div>
                    </template>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
