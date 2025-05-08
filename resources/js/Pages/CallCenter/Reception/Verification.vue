<script setup>
import {router, useForm, usePage} from "@inertiajs/vue3";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import VerifyConfirmationModal from "@/Pages/CallCenter/Reception/Partials/VerifyConfirmModal.vue";
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
})

const hbl = ref({});
const hblTotalSummary = ref({});
const isLoadingHbl = ref(false);
const paymentRecord = ref([]);
const isLoading = ref(false);
const showConfirmVerifyModal = ref(false);

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

const form = useForm({
    customer_queue: props.customerQueue,
    is_checked: {},
    note: ''
});

const updateChecked = (doc, isChecked) => {
    form.is_checked = { ...form.is_checked, [doc]: isChecked };
};

const closeModal = () => {
    showConfirmVerifyModal.value = false;
};

const handleReceptionVerify = () => {
    if (Object.keys(form.is_checked).length === 0) {
        push.error('Please check the documents first!');
        closeModal();
        return 0;
    }

    form.post(route("call-center.reception.store"), {
        onSuccess: () => {
            closeModal();
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

        <div class="grid grid-cols-12 gap-5">
            <div class="col-span-9">
                asa
            </div>

            <div class="col-span-3">
                asa
            </div>
        </div>
    </AppLayout>
</template>
