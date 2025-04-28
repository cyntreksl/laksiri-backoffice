<script setup>
import DestinationAppLayout from "@/Layouts/DestinationAppLayout.vue";
import {router, useForm, usePage} from "@inertiajs/vue3";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import VerifyConfirmationModal from "@/Pages/CallCenter/Verification/Partials/VerifyConfirmationModal.vue";
import {ref} from "vue";
import {push} from "notivue";
import moment from "moment";
import HBLDetailContent from "@/Pages/Common/Partials/HBLDetailContent.vue";
import AppLayout from "@/Layouts/AppLayout.vue";

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
    pickupId: {
        type: Number,
        default: null
    },
})

const hbl = ref({});
const pickup = ref({});
const hblTotalSummary = ref({});
const isLoadingHbl = ref(false);

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

const fetchPickup = async () => {
    isLoadingHbl.value = true;

    try {
        const response = await fetch(`/pickups/${props.pickupId}`, {
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
            pickup.value = data;
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

const form = useForm({
    customer_queue: props.customerQueue,
    is_checked: {},
    note: ''
});

const updateChecked = (doc, isChecked) => {
    form.is_checked = { ...form.is_checked, [doc]: isChecked };
};

const showConfirmVerifyModal = ref(false);

const closeModal = () => {
    showConfirmVerifyModal.value = false;
};

const handleVerifyDocuments = () => {
    if (Object.keys(form.is_checked).length === 0) {
        push.error('Please check the documents first!');
        closeModal();
        return 0;
    }

    form.post(route("call-center.verification.store"), {
        onSuccess: () => {
            closeModal();
            router.visit(route("call-center.verification.queue.list"));
            form.reset();
            push.success('Verified Successfully!');
        },
        onError: () => {
            push.error('Something went to wrong!');
        },
        preserveScroll: true,
        preserveState: true,
    });
}

const paymentRecord = ref([]);
const isLoading = ref(false);

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
    <AppLayout title="Documents Verification">
        <template #header>Documents Verification</template>

        <!-- Breadcrumb -->
        <Breadcrumb />

        <div class="grid-container">
            <div class="left-section w-full">
                <div class="grid grid-cols-1 mt-4 gap-4">
                    <div class="sm:col-span-3 space-y-5">
                        <div class="card px-4 py-4 sm:px-5">
                            <HBLDetailContent :hbl="hbl" :isLoading="isLoadingHbl" :showAuditDetails="false" :editPermission="false" :hbl-total-summary="hblTotalSummary"/>
                        </div>
                    </div>
                </div>
            </div>
            <div class="right-section w-full">
                <div v-if="isLoading" class="flex animate-pulse flex-col my-2">
                    <div class="h-48 w-full rounded-lg bg-slate-150 dark:bg-navy-500"></div>
                    <div class="flex space-x-5 py-4">
                        <div class="flex flex-1 flex-col justify-between py-2">
                            <div class="h-3 w-10/12 rounded bg-slate-150 dark:bg-navy-500"></div>
                            <div class="h-6 w-full rounded bg-slate-150 dark:bg-navy-500"></div>
                        </div>
                    </div>
                </div>

                <div v-else :class="`flex rounded-lg bg-gradient-to-r ${Object.keys(paymentRecord).length > 0 ? 'from-purple-500 to-indigo-600' : 'from-red-500 to-pink-600'} py-5 sm:py-6 my-4`">
                    <div v-if="Object.keys(paymentRecord).length > 0" class="px-4 text-white sm:px-5">
                        <div class="-mt-1 flex items-center space-x-2">
                            <h2 class="text-base font-medium tracking-wide">Balance</h2>
                        </div>

                        <div class="mt-3">
                            <p class="text-2xl font-semibold">{{(paymentRecord.grand_total - hbl.paid_amount).toFixed(2)}}</p>
                        </div>

                        <div class="mt-4 flex space-x-7">
                            <div>
                                <p class="text-indigo-100">Total</p>
                                <div class="mt-1 flex items-center space-x-2">
                                    <div class="flex size-7 items-center justify-center rounded-full bg-black/20">
                                        <svg  class="size-4 icon icon-tabler icons-tabler-outline icon-tabler-cash"  fill="none"  height="24"  stroke="currentColor"  stroke-linecap="round"  stroke-linejoin="round"  stroke-width="2"  viewBox="0 0 24 24"  width="24"  xmlns="http://www.w3.org/2000/svg"><path d="M0 0h24v24H0z" fill="none" stroke="none"/><path d="M7 9m0 2a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2v6a2 2 0 0 1 -2 2h-10a2 2 0 0 1 -2 -2z" /><path d="M14 14m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" /><path d="M17 9v-2a2 2 0 0 0 -2 -2h-10a2 2 0 0 0 -2 2v6a2 2 0 0 0 2 2h2" /></svg>
                                    </div>
                                    <p class="text-base font-medium">{{parseFloat(paymentRecord.grand_total).toFixed(2)}}</p>
                                </div>
                            </div>

                            <div>
                                <p class="text-indigo-100">Paid Amount</p>
                                <div class="mt-1 flex items-center space-x-2">
                                    <div class="flex size-7 items-center justify-center rounded-full bg-black/20">
                                        <svg  class="size-4 icon icon-tabler icons-tabler-outline icon-tabler-cash"  fill="none"  height="24"  stroke="currentColor"  stroke-linecap="round"  stroke-linejoin="round"  stroke-width="2"  viewBox="0 0 24 24"  width="24"  xmlns="http://www.w3.org/2000/svg"><path d="M0 0h24v24H0z" fill="none" stroke="none"/><path d="M7 9m0 2a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2v6a2 2 0 0 1 -2 2h-10a2 2 0 0 1 -2 -2z" /><path d="M14 14m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" /><path d="M17 9v-2a2 2 0 0 0 -2 -2h-10a2 2 0 0 0 -2 2v6a2 2 0 0 0 2 2h2" /></svg>
                                    </div>
                                    <p class="text-base font-medium">{{parseFloat(hbl.paid_amount).toFixed(2)}}</p>
                                </div>
                            </div>

                            <div>
                                <p class="text-indigo-100">Status</p>
                                <div class="mt-1 flex items-center space-x-2">
                                    <div class="flex size-7 items-center justify-center rounded-full bg-black/20">
                                        <svg  class="size-4 icon icon-tabler icons-tabler-outline icon-tabler-hierarchy-2"  fill="none"  height="24"  stroke="currentColor"  stroke-linecap="round"  stroke-linejoin="round"  stroke-width="2"  viewBox="0 0 24 24"  width="24"  xmlns="http://www.w3.org/2000/svg"><path d="M0 0h24v24H0z" fill="none" stroke="none"/><path d="M10 3h4v4h-4z" /><path d="M3 17h4v4h-4z" /><path d="M17 17h4v4h-4z" /><path d="M7 17l5 -4l5 4" /><path d="M12 7l0 6" /></svg>
                                    </div>
                                    <p class="text-base font-medium">{{paymentRecord.status}}</p>
                                </div>
                            </div>

                            <div>
                                <p class="text-indigo-100">Paid At</p>
                                <div class="mt-1 flex items-center space-x-2">
                                    <div class="flex size-7 items-center justify-center rounded-full bg-black/20">
                                        <svg  class="size-4 icon icon-tabler icons-tabler-outline icon-tabler-calendar-month"  fill="none"  height="24"  stroke="currentColor"  stroke-linecap="round"  stroke-linejoin="round"  stroke-width="2"  viewBox="0 0 24 24"  width="24"  xmlns="http://www.w3.org/2000/svg"><path d="M0 0h24v24H0z" fill="none" stroke="none"/><path d="M4 7a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12z" /><path d="M16 3v4" /><path d="M8 3v4" /><path d="M4 11h16" /><path d="M7 14h.013" /><path d="M10.01 14h.005" /><path d="M13.01 14h.005" /><path d="M16.015 14h.005" /><path d="M13.015 17h.005" /><path d="M7.01 17h.005" /><path d="M10.01 17h.005" /></svg>
                                    </div>
                                    <p class="text-base font-medium">{{moment(paymentRecord.updated_at).format('dddd, MMMM Do YYYY, h:mm:ss a')}}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-else class="px-4 text-white sm:px-5">
                        <div class="-mt-1 flex items-center space-x-2">
                            <h2 class="text-base font-medium tracking-wide">No Payment Records</h2>
                        </div>
                    </div>
                </div>

                <!-- Create Pickup Form -->
                <div class="grid grid-cols-1 mt-4 gap-4">
                    <div class="sm:col-span-3 space-y-5">
                        <div class="card px-4 py-4 sm:px-5">
                            <div class="grid grid-cols-2">
                                <h2
                                    class="text-lg font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100"
                                >
                                    Document Verification
                                </h2>
                            </div>
                            <div class="grid grid-cols-2 gap-5 mt-3">
                                <div class="space-y-4">
                                    <InputLabel v-for="(doc, index) in verificationDocuments" :key="index" class="cursor-pointer">
                                        <input
                                            :checked="form.is_checked[doc] || false"
                                            :value="doc"
                                            class="form-checkbox is-basic size-5 rounded border-slate-400/70 checked:border-primary checked:bg-primary hover:border-primary focus:border-primary dark:border-navy-400 dark:checked:border-accent dark:checked:bg-accent dark:hover:border-accent dark:focus:border-accent mr-3"
                                            type="checkbox"
                                            @change="(event) => updateChecked(doc, event.target.checked)"
                                        >
                                        {{ doc }}
                                    </InputLabel>
                                </div>

                                <div class="col-span-2">
                                    <InputLabel value="Note" />
                                    <label class="block">
                                        <textarea
                                            v-model="form.note"
                                            class="form-textarea w-full resize-none rounded-lg border border-slate-300 bg-transparent p-2.5 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                            placeholder="Type note here..."
                                            rows="4"
                                        ></textarea>
                                    </label>
                                    <InputError :message="form.errors.note" />
                                </div>
                            </div>
                            <div class="text-right mt-3">
                                <PrimaryButton type="button" @click="showConfirmVerifyModal = !showConfirmVerifyModal">Verify</PrimaryButton>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>



        <VerifyConfirmationModal :show="showConfirmVerifyModal" @close="closeModal" @verify-customer="handleVerifyDocuments"/>
    </AppLayout>
</template>

<style>

.grid-container {
    display: grid;
    grid-template-columns: 2fr 1fr; /* 2/3 for the left, 1/3 for the right */
    gap: 10px; /* Optional: Adds some space between the two sections */
    height: 100vh; /* Optional: Full viewport height */
}
.left-section {
    padding: 8px;
}
.right-section {
    padding: 8px;
}

@media (max-width: 768px) {
        .grid-container {
            grid-template-columns: 1fr; /* Stacks sections one by one */
            height: auto; /* Adjust height for mobile */
        }
    }

</style>
