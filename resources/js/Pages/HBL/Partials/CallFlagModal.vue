<script setup>
import {useForm} from "@inertiajs/vue3";
import {push} from "notivue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import {watch, computed, ref, onMounted} from "vue";
import moment from "moment";
import Dialog from "primevue/dialog";
import Button from "primevue/button";
import InputText from "primevue/inputtext";
import Textarea from "primevue/textarea";
import DatePicker from 'primevue/datepicker';
import Divider from "primevue/divider";
import Checkbox from "primevue/checkbox";
import Message from "primevue/message";
import axios from "axios";

const props = defineProps({
    visible: {
        type: Boolean,
        default: false,
    },
    hblId: {
        type: String,
        required: true,
    },
    callerName: {
        type: String,
        required: true,
    },
    hblData: {
        type: Object,
        default: () => ({})
    }
});

const emit = defineEmits(['close', 'call-flag-created']);

const needsFollowUp = ref(false);
const pricingSummary = ref(null);
const loadingPricing = ref(false);

const form = useForm({
    caller: "",
    date: new Date(),
    notes: "",
    followup_date: "",
    call_outcome: "contacted", // contacted, no_answer, busy, appointment_scheduled
    appointment_date: "",
    appointment_notes: "",
});

// Computed property to get the receiver name from HBL data
const receiverName = computed(() => {
    return props.hblData?.consignee_name || props.callerName || "";
});

// Function to calculate default follow-up date
const getDefaultFollowUpDate = () => {
    const today = moment(); // Create a new moment instance
    const dayOfWeek = today.day(); // 0 = Sunday, 5 = Friday

    if (dayOfWeek === 5) {
        // If today is Friday, add 3 days to get Monday
        return moment().add(3, 'days').toDate();
    } else {
        // Otherwise, add 1 day for tomorrow
        return moment().add(1, 'days').toDate();
    }
};

// Function to fetch pricing summary
const fetchPricingSummary = async () => {
    if (!props.hblId) return;

    loadingPricing.value = true;

    try {
        // Use hblData from props first
        const hblDetails = props.hblData;

        if (!hblDetails) {
            pricingSummary.value = null;
            loadingPricing.value = false;
            return;
        }

        // Fetch payments
        const paymentsResponse = await axios.get(`/hbls/${props.hblId}/payments`);
        const payments = paymentsResponse.data;

        // Calculate summary from payments (LKR only)
        const lkrPayments = payments.filter(p => !p.is_cancelled && p.base_currency_code === 'LKR');
        const totalPaid = lkrPayments.reduce((sum, p) => sum + parseFloat(p.paid_amount || 0), 0);

        const grandTotal = parseFloat(hblDetails.grand_total || 0);
        const discount = parseFloat(hblDetails.discount || 0);
        const outstanding = grandTotal - totalPaid;

        pricingSummary.value = {
            grandTotal: grandTotal,
            paidAmount: totalPaid,
            outstanding: outstanding,
            discount: discount,
            currency: 'LKR'
        };
    } catch (error) {
        console.error('Error fetching pricing summary:', error);
        pricingSummary.value = null;
    } finally {
        loadingPricing.value = false;
    }
};

watch(() => props.visible, (newVal) => {
    if (newVal) {
        // Reset form when modal opens
        form.reset();
        form.caller = receiverName.value;
        form.date = new Date();
        needsFollowUp.value = false;
        pricingSummary.value = null;

        // Fetch pricing summary
        fetchPricingSummary();
    }
});

watch(() => receiverName.value, (newVal) => {
    if (newVal) {
        form.caller = newVal;
    }
});

// Watch for needsFollowUp changes to set default date
watch(() => needsFollowUp.value, (newVal) => {
    if (newVal) {
        // Always set the default date when checkbox is checked
        form.followup_date = getDefaultFollowUpDate();
    } else {
        // Clear the date when checkbox is unchecked
        form.followup_date = "";
    }
});

const handleCreateCallFlag = () => {
    // Format dates properly
    form.date = moment(form.date).format("YYYY-MM-DD");

    // Only include followup_date if needsFollowUp is true
    if (needsFollowUp.value && form.followup_date) {
        form.followup_date = moment(form.followup_date).format("YYYY-MM-DD");
    } else {
        form.followup_date = null;
    }

    // Format appointment date if provided
    if (form.appointment_date) {
        form.appointment_date = moment(form.appointment_date).format("YYYY-MM-DD");
    }

    form.post(route("hbls.create-call-flag", props.hblId), {
        preserveScroll: true,
        onSuccess: () => {
            emit('close');
            emit('call-flag-created');
            push.success('Call Flag Added Successfully!');
            form.reset();
            needsFollowUp.value = false;
        },
        onError: () => {
            push.error('Failed to create call flag. Please try again.');
        }
    });
};

const callOutcomeOptions = [
    { label: 'Successfully Contacted', value: 'contacted' },
    { label: 'No Answer', value: 'no_answer' },
    { label: 'Line Busy', value: 'busy' },
    { label: 'Appointment Scheduled', value: 'appointment_scheduled' },
    { label: 'Customer Not Available', value: 'not_available' },
];
</script>

<template>
    <Dialog
        :visible="visible"
        :style="{ width: '32rem' }"
        header="New Call Flag"
        modal
        class="p-dialog-maximized-sm"
        @update:visible="(newValue) => $emit('update:visible', newValue)"
    >
        <div class="space-y-4">
            <!-- HBL Information Card -->
            <Message v-if="hblData" severity="info" :closable="false" class="mb-4">
                <div class="text-sm">
                    <div class="font-medium">HBL: {{ hblData.hbl_number || hblData.hbl }}</div>
                    <div class="text-gray-600">{{ hblData.hbl_name }} → {{ hblData.consignee_name }}</div>
                </div>
            </Message>

            <!-- Pricing & Invoice Summary -->
            <div v-if="loadingPricing" class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                <div class="flex items-center justify-center">
                    <i class="pi pi-spin pi-spinner text-2xl text-gray-400"></i>
                    <span class="ml-2 text-gray-600">Loading pricing information...</span>
                </div>
            </div>

            <div v-else-if="pricingSummary" class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex items-start justify-between mb-3">
                    <h3 class="font-semibold text-gray-800 flex items-center">
                        <i class="pi pi-money-bill mr-2 text-blue-600"></i>
                        Pricing & Invoice Summary (LKR)
                    </h3>
                </div>

                <div class="grid grid-cols-2 gap-3 text-sm">
                    <div>
                        <span class="text-gray-600">Grand Total:</span>
                        <div class="font-semibold text-gray-900">
                            {{ pricingSummary.grandTotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }} LKR
                        </div>
                    </div>

                    <div>
                        <span class="text-gray-600">Paid Amount:</span>
                        <div class="font-semibold text-green-600">
                            {{ pricingSummary.paidAmount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }} LKR
                        </div>
                    </div>

                    <div>
                        <span class="text-gray-600">Outstanding:</span>
                        <div :class="pricingSummary.outstanding > 0 ? 'text-red-600' : 'text-green-600'" class="font-semibold">
                            {{ pricingSummary.outstanding.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }} LKR
                        </div>
                    </div>

                    <div v-if="pricingSummary.discount > 0">
                        <span class="text-gray-600">Discount:</span>
                        <div class="font-semibold text-blue-600">
                            {{ pricingSummary.discount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }} LKR
                        </div>
                    </div>
                </div>

                <div class="mt-3 pt-3 border-t border-blue-200">
                    <p class="text-xs text-gray-600 italic">
                        <i class="pi pi-info-circle mr-1"></i>
                        Note: This pricing is based on Sri Lanka payments only and may vary.
                    </p>
                </div>
            </div>

            <!-- Caller Name -->
            <div>
                <InputLabel value="Receiver Name" for="caller-name" />
                <InputText
                    id="caller-name"
                    v-model="form.caller"
                    class="w-full"
                    placeholder="Enter receiver name"
                />
                <InputError :message="form.errors.caller"/>
            </div>

            <!-- Call Date -->
            <div>
                <InputLabel value="Call Date" for="call-date" />
                <DatePicker
                    id="call-date"
                    v-model="form.date"
                    class="w-full"
                    date-format="yy-mm-dd"
                    :max-date="new Date()"
                    show-icon
                />
                <InputError :message="form.errors.date"/>
            </div>

            <!-- Call Outcome -->
            <div>
                <InputLabel value="Call Outcome" for="call-outcome" />
                <select
                    id="call-outcome"
                    v-model="form.call_outcome"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                    <option v-for="option in callOutcomeOptions" :key="option.value" :value="option.value">
                        {{ option.label }}
                    </option>
                </select>
                <InputError :message="form.errors.call_outcome"/>
            </div>

            <!-- Notes -->
            <div>
                <InputLabel value="Call Notes" for="notes" />
                <Textarea
                    id="notes"
                    v-model="form.notes"
                    class="w-full"
                    rows="4"
                    placeholder="Enter call details, customer response, any concerns..."
                />
                <InputError :message="form.errors.notes"/>
            </div>

            <Divider />

            <!-- Follow-up Section -->
            <div class="space-y-3">
                <div class="flex items-center space-x-2">
                    <Checkbox
                        v-model="needsFollowUp"
                        input-id="needs-followup"
                        binary
                    />
                    <InputLabel value="Schedule Follow-up Call" for="needs-followup" class="!mb-0" />
                </div>

                <div v-if="needsFollowUp" class="pl-6 space-y-3">
                    <div>
                        <InputLabel value="Follow-up Date" for="followup-date" />
                        <DatePicker
                            id="followup-date"
                            v-model="form.followup_date"
                            class="w-full"
                            date-format="yy-mm-dd"
                            :min-date="new Date()"
                            show-icon
                            placeholder="Select follow-up date"
                        />
                        <InputError :message="form.errors.followup_date"/>
                    </div>
                </div>
            </div>

            <!-- Appointment Section -->
            <div v-if="form.call_outcome === 'appointment_scheduled'" class="space-y-3">
                <Divider />
                <div>
                    <InputLabel value="Appointment Date" for="appointment-date" />
                    <DatePicker
                        id="appointment-date"
                        v-model="form.appointment_date"
                        class="w-full"
                        date-format="yy-mm-dd"
                        :min-date="new Date()"
                        show-icon
                        placeholder="Select appointment date"
                    />
                    <InputError :message="form.errors.appointment_date"/>
                </div>

                <div>
                    <InputLabel value="Appointment Notes" for="appointment-notes" />
                    <Textarea
                        id="appointment-notes"
                        v-model="form.appointment_notes"
                        class="w-full"
                        rows="2"
                        placeholder="Appointment details, time, special instructions..."
                    />
                    <InputError :message="form.errors.appointment_notes"/>
                </div>
            </div>
        </div>

        <template #footer>
            <div class="flex justify-end gap-2">
                <Button
                    label="Cancel"
                    severity="secondary"
                    @click="emit('close')"
                />
                <Button
                    label="Add Call Flag"
                    :loading="form.processing"
                    :disabled="form.processing || !form.caller || !form.date"
                    @click="handleCreateCallFlag"
                />
            </div>
        </template>
    </Dialog>
</template>
