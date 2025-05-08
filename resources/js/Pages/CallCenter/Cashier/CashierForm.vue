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
import TabHBLPayments from "@/Pages/Common/Dialog/HBL/Tabs/TabHBLPayments.vue";
import Skeleton from "primevue/skeleton";
import TabStatus from "@/Pages/Common/Dialog/HBL/Tabs/TabStatus.vue";
import Textarea from "primevue/textarea";
import TabDocuments from "@/Pages/Common/Dialog/HBL/Tabs/TabDocuments.vue";
import TabList from "primevue/tablist";
import Tabs from "primevue/tabs";
import TabPanels from "primevue/tabpanels";
import Button from "primevue/button";
import InputNumber from "primevue/inputnumber";

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
    }
})

const hbl = ref({});
const hblTotalSummary = ref({});
const isLoadingHbl = ref(false);
const paymentRecord = ref([]);
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
    if (form.paid_amount < (paymentRecord.value.grand_total - hbl.value.paid_amount).toFixed(2)) {
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
                                        <span>Payments</span>
                                    </a>
                                </Tab>
                                <Tab v-if="Object.keys(hbl).length !== 0" value="2">
                                    <a class="flex items-center gap-2 text-inherit">
                                        <i class="pi pi-truck"/>
                                        <span>Shipment</span>
                                    </a>
                                </Tab>
                                <Tab value="3">
                                    <a class="flex items-center gap-2 text-inherit">
                                        <i class="pi pi-chart-bar"/>
                                        <span>Status & Audit</span>
                                    </a>
                                </Tab>
                                <Tab value="4">
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
                                    <TabHBLPayments :hbl="hbl" :hbl-total-summary="hblTotalSummary"/>
                                </TabPanel>
                                <TabPanel value="2">
                                    <TabShipment v-if="hbl" :hbl="hbl"/>
                                </TabPanel>
                                <TabPanel value="3">
                                    <TabStatus v-if="hbl" :hbl="hbl"/>
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

                <Card v-else class="!bg-gradient-to-r !from-purple-500 !to-indigo-600 !py-2 !sm:py-2">
                    <template #content>
                        <div v-if="Object.keys(paymentRecord).length > 0" class="text-white space-y-8">
                            <div>
                                <div class="flex items-center space-x-2">
                                    <h2 class="font-medium tracking-wide">Total Amount</h2>
                                </div>
                                <p class="text-2xl font-semibold">{{ currencyCode }}
                                    {{ parseFloat(paymentRecord.grand_total).toFixed(2) }}</p>
                            </div>

                            <div>
                                <div class="flex items-center space-x-2">
                                    <h2 class="font-medium tracking-wide">Paid Amount</h2>
                                </div>
                                <p class="text-2xl font-semibold">{{ currencyCode }}
                                    {{ parseFloat(hbl.paid_amount).toFixed(2) }}</p>
                            </div>

                            <div>
                                <div class="flex items-center space-x-2">
                                    <h2 class="font-medium tracking-wide">Outstanding</h2>
                                </div>
                                <p class="text-2xl font-semibold">{{ currencyCode }}
                                    {{ (paymentRecord.grand_total - hbl.paid_amount).toFixed(2) }}</p>
                            </div>

                            <div>
                                <div class="flex items-center space-x-2">
                                    <h2 class="font-medium tracking-wide">Status</h2>
                                </div>
                                <p class="text-2xl font-semibold">{{ paymentRecord.status }}</p>
                            </div>

                            <div>
                                <div class="flex items-center space-x-2">
                                    <h2 class="font-medium tracking-wide">Paid At</h2>
                                </div>
                                <p class="text-2xl font-semibold">
                                    {{ moment(paymentRecord.updated_at).format('dddd, MMMM Do YYYY, h:mm:ss a') }}</p>
                            </div>
                        </div>

                        <div v-else class="px-4 text-white sm:px-5">
                            <div class="flex items-center space-x-2">
                                <h2 class="text-base font-medium tracking-wide">Sorry, No Payment Records.</h2>
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
                                    <InputNumber v-model="form.paid_amount" :max="(paymentRecord.grand_total - hbl.paid_amount)"
                                                 :maxFractionDigits="2" :minFractionDigits="2" class="w-full" inputId="do-charge"
                                                 min="0" step="any"
                                                 variant="filled"/>
                                    <label for="do-charge">Amount</label>
                                </IftaLabel>
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
