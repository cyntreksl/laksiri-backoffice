<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import Card from "primevue/card";
import InputText from "primevue/inputtext";
import Button from "primevue/button";
import Checkbox from "primevue/checkbox";
import Textarea from "primevue/textarea";
import IftaLabel from "primevue/iftalabel";
import { useForm } from "@inertiajs/vue3";
import { ref } from "vue";
import { push } from "notivue";

const props = defineProps({
    packages: {
        type: Array,
        default: () => []
    },
    token: {
        type: Object,
        default: null
    },
    hbl: {
        type: Object,
        default: null
    },
    error: {
        type: String,
        default: null
    }
});

const tokenNumber = ref('');

const form = useForm({
    token_id: props.token?.id,
    package_ids: [],
    note: '',
});

const searchToken = () => {
    window.location.href = route('call-center.examination.return-to-bond', { token: tokenNumber.value });
};

const updateChecked = (packageId, isChecked) => {
    if (isChecked) {
        form.package_ids.push(packageId);
    } else {
        form.package_ids = form.package_ids.filter(id => id !== packageId);
    }
};

const handleReturnToBond = () => {
    if (form.package_ids.length === 0) {
        push.error('Please select at least one package');
        return;
    }

    form.post(route('call-center.examination.return-to-bond.store'), {
        onSuccess: () => {
            push.success('Packages returned to bond storage successfully');
            form.reset();
        },
        onError: () => {
            push.error('Failed to return packages to bond storage');
        },
    });
};
</script>

<template>
    <AppLayout title="Return Packages to Bond Storage">
        <template #header>Return Packages to Bond Storage</template>

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

            <div v-if="token && packages.length > 0" class="col-span-12">
                <Card>
                    <template #title>Held Packages for Token: {{ token.token }}</template>
                    <template #subtitle>HBL Reference: {{ hbl?.reference }}</template>
                    <template #content>
                        <div class="space-y-4">
                            <div v-for="pkg in packages" :key="pkg.id" class="flex items-center gap-2 p-3 border rounded">
                                <Checkbox
                                    :input-id="`pkg-${pkg.id}`"
                                    :value="pkg.id"
                                    @change="(event) => updateChecked(pkg.id, event.target.checked)"
                                />
                                <label :for="`pkg-${pkg.id}`" class="cursor-pointer flex-1">
                                    <div>
                                        <strong>{{ pkg.package_type }}</strong> (ID: {{ pkg.id }})
                                    </div>
                                    <div class="text-sm text-gray-600">
                                        Weight: {{ pkg.weight }} kg | Status: {{ pkg.release_status }}
                                    </div>
                                </label>
                            </div>

                            <div class="mt-4">
                                <IftaLabel>
                                    <Textarea
                                        id="note"
                                        v-model="form.note"
                                        class="w-full"
                                        placeholder="Enter note..."
                                        rows="3"
                                        variant="filled"
                                    />
                                    <label for="note">Note</label>
                                </IftaLabel>
                            </div>

                            <div class="text-right mt-4">
                                <Button
                                    :disabled="form.processing"
                                    icon="pi pi-arrow-left"
                                    label="Return to Bond Storage"
                                    severity="warning"
                                    @click="handleReturnToBond"
                                />
                            </div>
                        </div>
                    </template>
                </Card>
            </div>

            <div v-else-if="token && packages.length === 0 && !error" class="col-span-12">
                <Card>
                    <template #content>
                        <p class="text-gray-600">No held packages found for this token.</p>
                    </template>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
