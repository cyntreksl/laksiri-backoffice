<script setup>
import {router, useForm, usePage} from "@inertiajs/vue3";
import InputError from "@/Components/InputError.vue";
import {push} from "notivue";
import Dialog from "primevue/dialog";
import {ref} from "vue";
import InputNumber from 'primevue/inputnumber';
import Button from "primevue/button";

const props = defineProps({
    visible: {
        type: Boolean,
        default: false,
    },
    hbl: {
        type: Object,
        required: true,
    }
});

const emit = defineEmits(['close']);

const currencyCode = ref(usePage().props.currentBranch.currency_symbol || "SAR");

const form = useForm({
    paid_amount: 0,
});

const handleUpdatePayment = () => {
    form.put(route("back-office.cash-settlements.payment.update", props.hbl?.id), {
        preserveScroll: true,
        onSuccess: () => {
            emit('close');
            push.success(props.hbl?.hbl + ' Payment Updated!')
            router.visit(route('back-office.cash-settlements.index'))
        },
        onError: () => {
            push.error('Something went to wrong!');
        }
    })
}
</script>

<template>
    <Dialog :header="`Update Payment - ${hbl?.hbl || hbl?.reference}`" :style="{ width: '45rem' }" :visible="visible" modal @update:visible="(newValue) => $emit('update:visible', newValue)">

        <div class="rounded-lg bg-gradient-to-r from-green-500 to-indigo-600 py-5 sm:py-6">
            <div class="grid grid-cols-1 sm:grid-cols-3 px-4 text-white sm:px-5">
                <div>
                    <div class="-mt-1 flex items-center space-x-2">
                        <h2 class="text-base font-medium tracking-wide">Total Amount</h2>
                    </div>

                    <div class="mt-3">
                        <p class="text-2xl font-semibold">{{ currencyCode }} {{ (hbl?.grand_total).toFixed(2) }}</p>
                    </div>
                </div>

                <div>
                    <div class="-mt-1 flex items-center space-x-2">
                        <h2 class="text-base font-medium tracking-wide">Paid Amount</h2>
                    </div>

                    <div class="mt-3">
                        <p class="text-2xl font-semibold">{{ currencyCode }} {{ (hbl?.paid_amount).toFixed(2) }}</p>
                    </div>
                </div>

                <div>
                    <div class="-mt-1 flex items-center space-x-2">
                        <h2 class="text-base font-medium tracking-wide">Outstanding</h2>
                    </div>

                    <div class="mt-3">
                        <p class="text-2xl font-semibold">{{ currencyCode }} {{ (hbl?.grand_total - hbl?.paid_amount).toFixed(2) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4">
            <InputNumber v-show="(hbl?.grand_total - hbl?.paid_amount) !== 0"
                         v-model="form.paid_amount"
                         :max="hbl?.grand_total - hbl?.paid_amount"
                         class="w-full"
                         fluid
                         min="0"
                         placeholder="Enter Amount" required />
            <InputError :message="form.errors.paid_amount"/>
        </div>

        <template #footer>
            <Button label="Cancel" severity="secondary" type="button" @click="emit('close')"></Button>

            <Button v-show="(hbl?.grand_total - hbl?.paid_amount) !== 0"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                    class="ms-3"
                    label="Update Payment"
                    @click="handleUpdatePayment"></Button>
        </template>
    </Dialog>
</template>
