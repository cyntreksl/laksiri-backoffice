<script setup>
import Breadcrumb from "@/Components/Breadcrumb.vue";
import {ref} from "vue";
import moment from "moment";
import InputError from "@/Components/InputError.vue";
import {router, useForm, usePage} from "@inertiajs/vue3";
import {push} from "notivue";
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
import InputNumber from "primevue/inputnumber";
import TabHBLCharge from "@/Pages/Common/Dialog/HBL/Tabs/TabHBLCharge.vue";
import TabPayments from "@/Pages/Common/Dialog/HBL/Tabs/TabPayments.vue";

const props = defineProps({
    customerQueue: {
        type: Object,
        default: () => {
        }
    },
    hblId: {
        type: Number,
        default: null
    },
    doCharge: {
        type: Number,
        default: null
    },
    branch: {
        type: Object,
        default: null
    },
    currencyRate : {
        type: Number,
        default: 1.0
    }
})

const hbl = ref({});
const hblTotalSummary = ref({});
const isLoadingHbl = ref(false);
const paymentRecord = ref([]);
const isLoading = ref(false);
const currencyCode = ref(usePage().props.currentBranch.currency_symbol || "SAR");

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
        } else {
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

const form = useForm({
    paid_amount: 0,
    customer_queue: props.customerQueue,
    note: '',
    do_charge: props.doCharge,
});

const handleUpdatePayment = () => {
    const outstandingAmount = parseFloat((paymentRecord.value.grand_total - hbl.value.paid_amount) * props.currencyRate);
    if (form.paid_amount < outstandingAmount) {
        push.error('Please pay full amount');
    } else {
        form.post(route("call-center.cashier.store"), {
            onSuccess: () => {
                router.visit(route("call-center.cashier.queue.list"));
                form.reset();
                push.success('Payment Update Successfully!');
                // Trigger the download of the PDF
                window.location.href = route("hbls.getCashierReceipt", {hbl: props.hblId});
            },
            onError: () => {
                push.error('Something went to wrong!');
            },
            preserveScroll: true,
            preserveState: true,
        });
    }
}
</script>

<template>
    <AppLayout title="Settle Payments">
        <template #header>Settle Payments</template>

        <Breadcrumb/>

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
                                    <TabHBLDetails :hbl="hbl" :is-loading="isLoading"/>
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

                <Card v-else class="shadow-lg border-0 overflow-hidden">
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
                                            <p class="text-xs text-gray-500">{{ parseFloat(paymentRecord.grand_total).toFixed(2) }} x {{ props.currencyRate }}</p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-xl font-bold text-gray-900">{{ currencyCode }} {{ parseFloat(paymentRecord.grand_total * props.currencyRate).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}</p>
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
                                            <p class="text-xs text-gray-500">{{ parseFloat(hbl.paid_amount).toFixed(2) }} x {{ props.currencyRate }}</p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-xl font-bold text-green-700">{{ currencyCode }} {{ parseFloat(hbl.paid_amount * props.currencyRate).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}</p>
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
                                            <p class="text-xs text-gray-500">{{ (paymentRecord.grand_total - hbl.paid_amount).toFixed(2) }} x {{ props.currencyRate }}</p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-xl font-bold"
                                           :class="(paymentRecord.grand_total - hbl.paid_amount) > 0 ? 'text-orange-700' : 'text-green-700'">
                                            {{ currencyCode }} {{ parseFloat((paymentRecord.grand_total - hbl.paid_amount) * props.currencyRate).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}
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

                <Card class="my-5">
                    <template #title>
                        Update Payment
                    </template>
                    <template #content>
                        <div class="grid grid-cols-1 gap-5 mt-3">
                            <div v-show="(paymentRecord.grand_total - hbl.paid_amount) !== 0">
                                <IftaLabel>
                                    <InputNumber v-model="form.paid_amount" :max="parseFloat((paymentRecord.grand_total - hbl.paid_amount) * props.currencyRate)"
                                                 :maxFractionDigits="2" :minFractionDigits="2" class="w-full" inputId="paid-amount"
                                                 min="0" step="any"
                                                 variant="filled"/>
                                    <label for="paid-amount">Amount ({{ currencyCode }})</label>
                                </IftaLabel>
                                <div class="text-xs text-gray-500 mt-1">Outstanding: {{ currencyCode }} {{ parseFloat((paymentRecord.grand_total - hbl.paid_amount) * props.currencyRate).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}</div>
                                <InputError :message="form.errors.paid_amount"/>
                            </div>

                            <div>
                                <IftaLabel>
                                    <InputNumber v-model="form.do_charge" :maxFractionDigits="2" :minFractionDigits="2"
                                                 class="w-full" inputId="do-charge" min="0" step="any"
                                                 variant="filled"/>
                                    <label for="do-charge">DO Charges</label>
                                </IftaLabel>
                                <InputError :message="form.errors.do_charge"/>
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
                            <Button v-show="(paymentRecord.grand_total - hbl.paid_amount) !== 0"
                                    :class="{ 'opacity-25': form.processing }"
                                    :disabled="form.processing" icon="pi pi-check"
                                    label="Update Payment" size="small" @click="handleUpdatePayment"/>
                        </div>
                    </template>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
