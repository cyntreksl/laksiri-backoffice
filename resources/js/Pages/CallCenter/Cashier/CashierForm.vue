<script setup>
import Breadcrumb from "@/Components/Breadcrumb.vue";
import {ref, watch, computed} from "vue";
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
import Dialog from "primevue/dialog";
import PaymentSummaryCard from "@/Pages/CallCenter/Components/PaymentSummaryCard.vue";

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
const showPaymentDialog = ref(false);
const summaryTotalDue = ref(0);

const computedOutstanding = computed(() => {
    return (
        parseFloat(summaryTotalDue.value || 0) +
        parseFloat(form.additional_charges || 0) -
        parseFloat(form.discount || 0)
    );
});

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
    discount: 0,
    additional_charges: 0,
});

const handleUpdatePayment = () => {
    const outstandingAmount = parseFloat(computedOutstanding.value);
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
            data: {
                ...form,
                additional_charges: form.additional_charges,
                discount: form.discount,
            }
        });
    }
}

// Watch for the dialog open to set the default amount
watch(showPaymentDialog, (val) => {
    if (val) {
        form.paid_amount = parseFloat(computedOutstanding.value.toFixed(2));
    }
});

// Always sync amount with outstanding
watch(computedOutstanding, (val) => {
    form.paid_amount = parseFloat(val.toFixed(2));
});
</script>

<template>
    <AppLayout title="Settle Payments">
        <template #header>Settle Payments</template>

        <Breadcrumb/>

        <div class="grid grid-cols-12 gap-5 mt-5">
            <div class="col-span-8">
                <Card>
                    <template #content>
                        <Tabs value="1">
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

            <div class="col-span-4">
                <div class="flex mb-4">
                    <Button v-show="(paymentRecord.grand_total - hbl.paid_amount) !== 0"
                            class="p-button-lg p-button-primary w-full"
                            icon="pi pi-credit-card"
                            label="Pay Now"
                            raised
                            size="large"
                            style="min-width: 180px; font-size: 1.25rem;"
                            @click="showPaymentDialog = true"/>
                </div>

                <Skeleton v-if="isLoading" height="350px" width="100%"></Skeleton>

                <PaymentSummaryCard v-if="props.hblId" :hbl-id="props.hblId" @update:total-due="summaryTotalDue = $event" />
            </div>
        </div>

        <Dialog v-model:visible="showPaymentDialog" :style="{ width: '500px' }" header="Pay Now" modal>
            <div class="grid grid-cols-1 gap-5 mt-3">
                <!-- Outstanding Amount Widget (now inside dialog) -->
                <div v-if="computedOutstanding" class="mb-2 p-4 rounded-xl shadow bg-gradient-to-r from-red-100 to-orange-100 border border-red-200 flex flex-col items-center">
                    <div class="flex items-center gap-2 mb-2">
                        <i class="pi pi-exclamation-circle text-red-600 text-2xl"></i>
                        <span class="font-semibold text-lg text-red-800">Outstanding</span>
                    </div>
                    <div class="text-3xl font-bold text-red-700">
                        {{ currencyCode }} {{ parseFloat(computedOutstanding).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}
                    </div>
                </div>
                <div v-show="computedOutstanding !== 0">
                    <IftaLabel>
                        <InputNumber v-model="form.paid_amount" :max="computedOutstanding"
                                     :maxFractionDigits="2" :minFractionDigits="2" class="w-full" inputId="paid-amount"
                                     min="0" step="any"
                                     variant="filled"/>
                        <label for="paid-amount">Amount ({{ currencyCode }})</label>
                    </IftaLabel>
                    <InputError :message="form.errors.paid_amount"/>
                </div>

                <!-- Additional Charges Field -->
                <div>
                    <IftaLabel>
                        <InputNumber v-model="form.additional_charges" :maxFractionDigits="2" :minFractionDigits="2"
                                     class="w-full" inputId="additional-charges" min="0" step="any"
                                     variant="filled"/>
                        <label for="additional-charges">Additional Charges</label>
                    </IftaLabel>
                    <InputError :message="form.errors.additional_charges"/>
                </div>

                <!-- Discount Field -->
                <div>
                    <IftaLabel>
                        <InputNumber v-model="form.discount" :maxFractionDigits="2" :minFractionDigits="2"
                                     class="w-full" inputId="discount" min="0" step="any"
                                     variant="filled"/>
                        <label for="discount">Discount</label>
                    </IftaLabel>
                    <InputError :message="form.errors.discount"/>
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
            <template #footer>
                <Button class="p-button-text" icon="pi pi-times" label="Cancel" @click="showPaymentDialog = false" />
                <Button
                  :class="{ 'opacity-25': form.processing }"
                  :disabled="form.processing"
                  icon="pi pi-wallet"
                  icon-class="animate-pulse"
                  label="Pay Now"
                  @click="handleUpdatePayment"
                />
            </template>
        </Dialog>
    </AppLayout>
</template>
