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

                <Card v-else class="!bg-gradient-to-r !from-purple-500 !to-indigo-600 !py-2 !sm:py-2">
                    <template #content>
                        <div v-if="Object.keys(paymentRecord).length > 0" class="text-white space-y-8">
                            <div>
                                <div class="flex items-center space-x-2">
                                    <h2 class="font-medium tracking-wide">Total Amount</h2>
                                </div>
                                <p class="text-2xl font-semibold">{{ currencyCode }} {{ parseFloat(paymentRecord.grand_total).toFixed(2) }}</p>
                            </div>

                            <div>
                                <div class="flex items-center space-x-2">
                                    <h2 class="font-medium tracking-wide">Paid Amount</h2>
                                </div>
                                <p class="text-2xl font-semibold">{{ currencyCode }} {{ parseFloat(hbl.paid_amount).toFixed(2) }}</p>
                            </div>

                            <div>
                                <div class="flex items-center space-x-2">
                                    <h2 class="font-medium tracking-wide">Outstanding</h2>
                                </div>
                                <p class="text-2xl font-semibold">{{ currencyCode }} {{ (paymentRecord.grand_total - hbl.paid_amount).toFixed(2) }}</p>
                            </div>

                            <div>
                                <div class="flex items-center space-x-2">
                                    <h2 class="font-medium tracking-wide">Status</h2>
                                </div>
                                <p class="text-2xl font-semibold">{{paymentRecord.status}}</p>
                            </div>

                            <div>
                                <div class="flex items-center space-x-2">
                                    <h2 class="font-medium tracking-wide">Paid At</h2>
                                </div>
                                <p class="text-2xl font-semibold">{{moment(paymentRecord.updated_at).format('dddd, MMMM Do YYYY, h:mm:ss a')}}</p>
                            </div>
                        </div>

                        <div v-else class="px-4 text-white sm:px-5">
                            <div class="flex items-center space-x-2">
                                <h2 class="text-base font-medium tracking-wide">Sorry, No Payment Records.</h2>
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
