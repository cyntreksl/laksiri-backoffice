<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import Card from "primevue/card";
import InputText from "primevue/inputtext";
import Button from "primevue/button";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import Tag from "primevue/tag";
import { useForm } from "@inertiajs/vue3";
import { ref } from "vue";
import { push } from "notivue";

const props = defineProps({
    token: {
        type: Object,
        default: null
    },
    hbl: {
        type: Object,
        default: null
    },
    packageQueue: {
        type: Object,
        default: null
    },
    packageSummary: {
        type: Object,
        default: null
    },
    packages: {
        type: Array,
        default: () => []
    },
    error: {
        type: String,
        default: null
    }
});

const tokenNumber = ref('');

const form = useForm({
    token_id: props.token?.id,
});

const searchToken = () => {
    window.location.href = route('gate-control.complete-token', { token: tokenNumber.value });
};

const canCompleteToken = () => {
    if (!props.packageSummary) return false;
    return props.packageSummary.pending === 0 && props.packageSummary.held === 0;
};

const handleCompleteToken = () => {
    if (!canCompleteToken()) {
        push.error('Cannot complete token. There are still pending or held packages.');
        return;
    }

    form.post(route('gate-control.complete-token.store'), {
        onSuccess: () => {
            push.success('Token completed successfully');
        },
        onError: () => {
            push.error('Failed to complete token');
        },
    });
};

const getStatusSeverity = (status) => {
    switch (status) {
        case 'released':
            return 'success';
        case 'held':
            return 'warning';
        case 'returned_to_bond':
            return 'info';
        case 'pending':
            return 'secondary';
        default:
            return 'secondary';
    }
};
</script>

<template>
    <AppLayout title="Complete Token">
        <template #header>Complete Token at Gate</template>

        <Breadcrumb />

        <div class="grid grid-cols-12 gap-5 mt-5">
            <div class="col-span-12">
                <Card>
                    <template #title>Search Token</template>
                    <template #content>
                        <div class="flex gap-3">
                            <InputText v-model="tokenNumber" class="flex-1" placeholder="Enter token number" />
                            <Button icon="pi pi-search" label="Search" @click="searchToken" />
                        </div>
                    </template>
                </Card>
            </div>

            <div v-if="error" class="col-span-12">
                <Card>
                    <template #content>
                        <p class="text-red-500">{{ error }}</p>
                    </template>
                </Card>
            </div>

            <div v-if="token && packageSummary" class="col-span-12">
                <Card>
                    <template #title>Token: {{ token.token }}</template>
                    <template #subtitle>HBL Reference: {{ hbl?.reference }}</template>
                    <template #content>
                        <div class="grid grid-cols-5 gap-4 mb-6">
                            <div class="text-center p-4 bg-gray-100 rounded">
                                <div class="text-2xl font-bold">{{ packageSummary.total }}</div>
                                <div class="text-sm text-gray-600">Total Packages</div>
                            </div>
                            <div class="text-center p-4 bg-green-100 rounded">
                                <div class="text-2xl font-bold text-green-700">{{ packageSummary.released }}</div>
                                <div class="text-sm text-gray-600">Released</div>
                            </div>
                            <div class="text-center p-4 bg-yellow-100 rounded">
                                <div class="text-2xl font-bold text-yellow-700">{{ packageSummary.held }}</div>
                                <div class="text-sm text-gray-600">Held</div>
                            </div>
                            <div class="text-center p-4 bg-blue-100 rounded">
                                <div class="text-2xl font-bold text-blue-700">{{ packageSummary.returned_to_bond }}</div>
                                <div class="text-sm text-gray-600">Returned to Bond</div>
                            </div>
                            <div class="text-center p-4 bg-gray-100 rounded">
                                <div class="text-2xl font-bold">{{ packageSummary.pending }}</div>
                                <div class="text-sm text-gray-600">Pending</div>
                            </div>
                        </div>

                        <DataTable :value="packages" class="mb-4">
                            <Column field="id" header="ID" />
                            <Column field="package_type" header="Package Type" />
                            <Column field="weight" header="Weight (kg)" />
                            <Column field="release_status" header="Status">
                                <template #body="slotProps">
                                    <Tag :severity="getStatusSeverity(slotProps.data.release_status)" :value="slotProps.data.release_status" />
                                </template>
                            </Column>
                            <Column field="released_at" header="Released At">
                                <template #body="slotProps">
                                    {{ slotProps.data.released_at ? new Date(slotProps.data.released_at).toLocaleString() : '-' }}
                                </template>
                            </Column>
                        </DataTable>

                        <div v-if="!canCompleteToken()" class="p-4 bg-yellow-50 border border-yellow-200 rounded mb-4">
                            <p class="text-yellow-800">
                                <i class="pi pi-exclamation-triangle mr-2"></i>
                                Cannot complete token. There are {{ packageSummary.pending }} pending and {{ packageSummary.held }} held packages.
                            </p>
                        </div>

                        <div v-if="packageQueue?.status === 'partial'" class="p-4 bg-blue-50 border border-blue-200 rounded mb-4">
                            <p class="text-blue-800">
                                <i class="pi pi-info-circle mr-2"></i>
                                This is a partial release. Some packages have been released while others are held or returned to bond.
                            </p>
                        </div>

                        <div class="text-right">
                            <Button
                                :disabled="!canCompleteToken() || form.processing"
                                icon="pi pi-check"
                                label="Complete Token"
                                severity="success"
                                @click="handleCompleteToken"
                            />
                        </div>
                    </template>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
