<script setup>
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import DialogModal from "@/Components/DialogModal.vue";
import {router, useForm} from "@inertiajs/vue3";
import InputError from "@/Components/InputError.vue";
import TextInput from "@/Components/TextInput.vue";
import {push} from "notivue";

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    hblData: {
        type: Object,
        default: () => {
        }
    }
});

const emit = defineEmits(['close']);

const form = useForm({
    paid_amount: 0,
});

const handleUpdatePayment = () => {
    form.put(route("back-office.cash-settlements.payment.update", props.hblData[0].data.id), {
        preserveScroll: true,
        onSuccess: () => {
            emit('close');
            push.success(props.hblData[1].data + ' Payment Updated!')
            router.visit(route('back-office.cash-settlements.index'))
        },
        onError: () => {
            push.error('Something went to wrong!');
        }
    })
}
</script>

<template>
    <DialogModal :maxWidth="'xl'" :show="show" @close="$emit('close')">
        <template #title>
            Update Payment - {{ hblData[1].data }}
        </template>

        <template #content>
            <div class="flex rounded-lg bg-gradient-to-r from-blue-500 to-indigo-600 py-5 sm:py-6">
                <div class="px-4 text-white sm:px-5">
                    <div class="-mt-1 flex items-center space-x-2">
                        <h2 class="text-base font-medium tracking-wide">Balance</h2>
                    </div>

                    <div class="mt-3">
                        <p class="text-2xl font-semibold">{{ (hblData[7].data - hblData[8].data).toFixed(2) }}</p>
                    </div>

                    <div class="mt-4 flex space-x-7">
                        <div>
                            <p class="text-indigo-100">Amount</p>
                            <div class="mt-1 flex items-center space-x-2">
                                <div class="flex size-7 items-center justify-center rounded-full bg-black/20">
                                    <svg class="size-4" fill="none" stroke="currentColor"
                                         viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M7 11l5-5m0 0l5 5m-5-5v12" stroke-linecap="round" stroke-linejoin="round"
                                              stroke-width="2"/>
                                    </svg>
                                </div>
                                <p class="text-base font-medium">{{ hblData[7].data.toFixed(2) }}</p>
                            </div>
                        </div>
                        <div>
                            <p class="text-indigo-100">Paid</p>
                            <div class="mt-1 flex items-center space-x-2">
                                <div class="flex size-7 items-center justify-center rounded-full bg-black/20">
                                    <svg  class="size-4 icon icon-tabler icons-tabler-outline icon-tabler-check"  fill="none"  height="24"  stroke="currentColor"  stroke-linecap="round"  stroke-linejoin="round"  stroke-width="2"  viewBox="0 0 24 24"  width="24"  xmlns="http://www.w3.org/2000/svg"><path d="M0 0h24v24H0z" fill="none" stroke="none"/><path d="M5 12l5 5l10 -10" /></svg>
                                </div>
                                <p class="text-base font-medium">{{ hblData[8].data.toFixed(2) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-4">
                <TextInput
                    v-show="hblData[7].data - hblData[8].data !== 0"
                    v-model="form.paid_amount"
                    :max="hblData[7].data - hblData[8].data"
                    class="w-full"
                    min="0"
                    placeholder="Enter Amount"
                    required
                    type="number"
                />
                <InputError :message="form.errors.paid_amount"/>
            </div>
        </template>

        <template #footer>
            <SecondaryButton @click="$emit('close')">
                Cancel
            </SecondaryButton>
            <PrimaryButton
                v-show="hblData[7].data - hblData[8].data !== 0"
                :class="{ 'opacity-25': form.processing }"
                :disabled="form.processing"
                class="ms-3"
                @click="handleUpdatePayment"
            >
                Update Payment
            </PrimaryButton>
        </template>
    </DialogModal>
</template>
