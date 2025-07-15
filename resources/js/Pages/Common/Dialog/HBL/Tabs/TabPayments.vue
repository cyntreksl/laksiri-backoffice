<script setup>
import { ref, watch, computed } from 'vue';
import Card from 'primevue/card';
import Timeline from 'primevue/timeline';
import Skeleton from 'primevue/skeleton';
import { usePage } from '@inertiajs/vue3';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import Textarea from 'primevue/textarea';

const props = defineProps({
    hbl: {
        type: Object,
        default: () => ({}),
    },
});

const payments = ref([]);
const isLoading = ref(false);

const showCancelDialog = ref(false);
const cancelReason = ref('');
const cancelLoading = ref(false);
const cancelError = ref('');
const paymentToCancel = ref(null);

const fetchPayments = async (hbl) => {
    if (!hbl || !hbl.id) return;
    isLoading.value = true;
    try {
        const response = await fetch(`/hbls/${hbl.id}/payments`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': usePage().props.csrf,
            },
        });
        if (!response.ok) throw new Error('Network response was not ok.');
        payments.value = await response.json();
    } catch (e) {
        payments.value = [];
    } finally {
        isLoading.value = false;
    }
};

watch(() => props.hbl, (newVal) => {
    if (newVal && newVal.id) fetchPayments(newVal);
}, { immediate: true });

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleString();
};

const formatAmount = (amount, currency = 'LKR') => {
    if (amount == null) return `${currency} 0.00`;
    return `${currency} ${parseFloat(amount).toLocaleString('en-US', { minimumFractionDigits: 2 })}`;
};

const totalPaid = computed(() => {
    const sum = payments.value
        .filter(p => !p.is_cancelled)
        .reduce((sum, p) => sum + parseFloat(p.paid_amount || 0), 0);
    return Math.round(sum * 100) / 100; // round to 2 decimal places
});
const totalDue = computed(() => payments.value.length > 0 ? payments.value[0].due_amount : 0);
const transactionCount = computed(() => payments.value.length);

const statusColor = (item) => {
    if (item.is_cancelled) return 'bg-red-500';
    if (item.due_amount === 0) return 'bg-green-500';
    if (item.paid_amount > 0) return 'bg-yellow-400';
    return 'bg-gray-300';
};

const statusBadge = (item) => {
    if (item.is_cancelled) return { text: 'Cancelled', color: 'bg-red-100 text-red-700 border border-red-200' };
    if (item.due_amount === 0) return { text: 'Paid', color: 'bg-green-100 text-green-700 border border-green-200' };
    if (item.paid_amount > 0) return { text: 'Partial', color: 'bg-yellow-100 text-yellow-700 border border-yellow-200' };
    return { text: 'Pending', color: 'bg-gray-100 text-gray-600 border border-gray-200' };
};

const statusIcon = (item) => {
    if (item.is_cancelled) return 'pi pi-times';
    if (item.due_amount === 0) return 'pi pi-check';
    if (item.paid_amount > 0) return 'pi pi-exclamation-circle';
    return 'pi pi-clock';
};

const openCancelDialog = (payment) => {
    paymentToCancel.value = payment;
    cancelReason.value = '';
    cancelError.value = '';
    showCancelDialog.value = true;
};

const closeCancelDialog = () => {
    showCancelDialog.value = false;
    paymentToCancel.value = null;
    cancelReason.value = '';
    cancelError.value = '';
};

const confirmCancelPayment = async () => {
    if (!paymentToCancel.value) return;
    cancelLoading.value = true;
    cancelError.value = '';
    try {
        const response = await fetch(`/hbls/payments/${paymentToCancel.value.id}/cancel`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': usePage().props.csrf,
            },
            body: JSON.stringify({ reason: cancelReason.value })
        });
        if (!response.ok) throw new Error('Failed to cancel payment');
        // Refresh payments
        await fetchPayments(props.hbl);
        closeCancelDialog();
    } catch (e) {
        cancelError.value = 'Cancellation failed. Please try again.';
    } finally {
        cancelLoading.value = false;
    }
};
</script>

<template>
    <div>
        <Card class="!bg-white !border !border-neutral-300 !shadow-md !rounded-md overflow-hidden">
            <template #content>
                <!-- Modern Card Header -->
                <div class="bg-gradient-to-r from-purple-50 to-indigo-100 -m-6 mb-6 p-6 border-b border-indigo-100 flex items-center gap-3">
                    <div class="p-3 bg-purple-100 rounded-lg">
                        <i class="ti ti-credit-card text-purple-600 text-2xl"></i>
                    </div>
                    <div>
                        <h3 class="text-2xl font-bold text-gray-900">Payment Transactions</h3>
                        <p class="text-sm text-gray-600">All payment activity for this HBL</p>
                    </div>
                </div>

                <!-- Summary Section -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div class="flex items-center p-4 rounded-xl bg-green-50 border border-green-100">
                        <i class="pi pi-check-circle text-green-600 text-2xl mr-3"></i>
                        <div>
                            <div class="text-xs text-gray-500">Total Paid</div>
                            <div class="text-lg font-bold text-green-700">{{ formatAmount(totalPaid, payments[0]?.base_currency_code || 'LKR') }}</div>
                        </div>
                    </div>
                    <div class="flex items-center p-4 rounded-xl bg-red-50 border border-red-100">
                        <i class="pi pi-exclamation-circle text-red-600 text-2xl mr-3"></i>
                        <div>
                            <div class="text-xs text-gray-500">Total Due</div>
                            <div class="text-lg font-bold text-red-700">{{ formatAmount(totalDue, payments[0]?.base_currency_code || 'LKR') }}</div>
                        </div>
                    </div>
                    <div class="flex items-center p-4 rounded-xl bg-blue-50 border border-blue-100">
                        <i class="pi pi-list text-blue-600 text-2xl mr-3"></i>
                        <div>
                            <div class="text-xs text-gray-500">Transactions</div>
                            <div class="text-lg font-bold text-blue-700">{{ transactionCount }}</div>
                        </div>
                    </div>
                </div>

                <!-- Loading State -->
                <div v-if="isLoading">
                    <div class="space-y-4">
                        <Skeleton v-for="i in 3" :key="i" class="rounded" height="40px" />
                    </div>
                </div>

                <!-- Timeline -->
                <div v-else-if="payments.length">
                    <ol class="space-y-4">
                        <li v-for="(item, idx) in payments" :key="item.id || idx" class="relative flex items-start group animate-fade-in-up">
                            <div :class="[
                                'flex-1 rounded-xl shadow transition-all duration-200 border-2 p-4',
                                item.is_cancelled ? 'bg-red-50 border-red-200' : item.due_amount === 0 ? 'bg-green-50 border-green-200' : item.paid_amount > 0 ? 'bg-yellow-50 border-yellow-200' : 'bg-gray-50 border-gray-200',
                                'hover:shadow-lg focus-within:shadow-lg',
                                'relative'
                            ]">
                                <div v-if="item.is_cancelled" class="absolute -top-3 -right-3 bg-red-500 text-white text-xs px-3 py-1 rounded-full shadow">Cancelled</div>
                                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2">
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs text-gray-500">{{ formatDate(item.paid_at) }}</span>
                                        <span :class="['ml-2 px-2 py-0.5 rounded text-xs font-semibold', statusBadge(item).color]">{{ statusBadge(item).text }}</span>
                                    </div>
                                    <span class="text-2xl font-bold text-gray-900">{{ formatAmount(item.paid_amount, item.base_currency_code || 'LKR') }}</span>
                                </div>
                                <div class="flex flex-wrap gap-3 mt-2 text-sm text-gray-600">
                                    <span><i class="pi pi-calculator mr-1 text-gray-400"></i>Total: <b>{{ formatAmount(item.total_amount, item.base_currency_code || 'LKR') }}</b></span>
                                    <span><i class="pi pi-exclamation-triangle mr-1 text-gray-400"></i>Due: <b>{{ formatAmount(item.due_amount, item.base_currency_code || 'LKR') }}</b></span>
                                    <span v-if="item.notes" class="italic text-gray-400"><i class="pi pi-info-circle mr-1"></i>Note: {{ item.notes }}</span>
                                </div>
                                <div v-if="!item.is_cancelled" class="mt-4 flex justify-end">
                                    <Button v-if="item.branch_id === $page.props.currentBranch.id" icon="pi pi-times" label="Cancel Payment" outlined severity="danger" size="small" @click="openCancelDialog(item)" />
                                </div>
                            </div>
                        </li>
                    </ol>
                </div>

                <!-- Empty State -->
                <div v-else class="flex items-center justify-center py-12">
                    <div class="text-center">
                        <div class="p-4 bg-gray-100 rounded-full inline-block mb-4">
                            <i class="ti ti-wallet text-gray-400 text-3xl"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-700 mb-2">No Payment Transactions</h3>
                        <p class="text-sm text-gray-500">Payment activity will appear here once available.</p>
                    </div>
                </div>
                <Dialog v-model:visible="showCancelDialog" :closable="!cancelLoading" :style="{ width: '400px' }" header="Cancel Payment" modal>
                    <div>
                        <p class="mb-2 text-gray-700">Are you sure you want to cancel this payment?</p>
                        <Textarea v-model="cancelReason" autoResize class="w-full mb-2" placeholder="Optional reason (visible in audit log)" rows="3" />
                        <div v-if="cancelError" class="text-red-500 text-sm mb-2">{{ cancelError }}</div>
                    </div>
                    <template #footer>
                        <Button :disabled="cancelLoading" label="Close" @click="closeCancelDialog" />
                        <Button :loading="cancelLoading" icon="pi pi-times" label="Confirm Cancel" severity="danger" @click="confirmCancelPayment" />
                    </template>
                </Dialog>
            </template>
        </Card>
    </div>
</template>
