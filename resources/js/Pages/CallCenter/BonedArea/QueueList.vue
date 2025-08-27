<!-- QueueList.vue -->
<script setup>
import {router} from "@inertiajs/vue3";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import {computed, ref} from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import Column from "primevue/column";
import Card from "primevue/card";
import DataTable from "primevue/datatable";
import DataView from "primevue/dataview";
import Button from "primevue/button";
import SelectButton from "primevue/selectbutton";
import Tag from "primevue/tag";
import PackageReleaseDialog from "@/Pages/CallCenter/BonedArea/PackageReleaseDialog.vue";
import Dialog from "primevue/dialog";
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import {push} from "notivue";
import axios from 'axios';
import {useForm} from "@inertiajs/vue3";

const props = defineProps({
    packageQueue: {
        type: Object,
        default: () => {
        }
    }
})

const layout = ref('list');
const options = ref(['list', 'grid']);
const showPackageReleaseDialog = ref(false);
const selectedToken = ref([]);

const filteredPackageQueue = computed(() => {
    return props.packageQueue.filter(q => {
        return q.is_released == false
    });
})

const handlePackageRelease = (token) => {
    selectedToken.value = token;
    showPackageReleaseDialog.value = true;
}

const closePackageReleaseModal = () => {
    selectedToken.value = [];
    showPackageReleaseDialog.value = false;
}

const returnDialogVisible = ref(false);
const returnForm = useForm({
    token_number: '',
    package_details: null,
    remarks: ''
});

const showReturnDialog = () => {
    returnDialogVisible.value = true;
};

const loadPackageDetails = async () => {
    if (!returnForm.token_number) return;

    try {
        const response = await axios.get(`/call-center/get-package-details-by-token/${returnForm.token_number}`);
        returnForm.package_details = response.data;
    } catch (error) {
        if (error.response.status === 404) {
            push.error('Package not found!');
        }
        returnForm.package_details = null;
    }
};

const handleReturnPackage = () => {
    returnForm.post(route('call-center.package.return'), {
        onSuccess: () => {
            push.success('Package returned successfully!');
            returnDialogVisible.value = false;
            returnForm.reset();
            // Refresh the package queue data
            router.reload({ only: ['packageQueue'] });
        },
        onError: (errors) => {
            push.error('Error returning package: ' + Object.values(errors).join(', '));
        },
        preserveState: true,
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout title="Queue List">
        <template #header>Queue List</template>

        <Breadcrumb/>

        <DataView :layout="layout" :value="filteredPackageQueue" class="my-5">
            <template #header>
                <div class="flex justify-between items-center">
                    <div class="text-lg font-medium">
                        Package Calling Queue
                    </div>
                    <div class="flex items-center gap-2">
                        <Button label="Return Package" severity="warning" type="button" @click="showReturnDialog"></Button>
                        <SelectButton v-model="layout" :allowEmpty="false" :options="options">
                            <template #option="{ option }">
                                <i :class="[option === 'list' ? 'pi pi-bars' : 'pi pi-table']"/>
                            </template>
                        </SelectButton>
                    </div>
                </div>
            </template>

            <template #list="slotProps">
                <DataTable :rows="10" :rowsPerPageOptions="[5, 10, 20, 50]" :value="slotProps.items" paginator row-hover
                           tableStyle="min-width: 50rem">
                    <Column field="token" header="Token">
                        <template #body="slotProps">
                            <div class="flex items-center text-2xl">
                                <i class="ti ti-tag mr-1 text-blue-500"></i>
                                {{ slotProps.data.token }}
                            </div>
                        </template>
                    </Column>
                    <Column field="customer" header="Customer"></Column>
                    <Column field="reference" header="Reference"></Column>
                    <Column field="package_count" header="Packages">
                        <template #body="slotProps">
                            <div class="flex items-center">
                                <i class="ti ti-package mr-1 text-blue-500" style="font-size: 1rem"></i>
                                {{ slotProps.data.package_count }}
                            </div>
                            <div class="flex flex-wrap space-x-1 mt-1">
                                <div v-for="(hbl_package, index) in slotProps.data?.hbl_packages" :key="index">
                                    <Tag
                                        :value="`${hbl_package.quantity} ${hbl_package.package_type}`"
                                        severity="info"
                                    />
                                </div>
                            </div>
                        </template>
                    </Column>
                    <Column field="created_at" header="Created At"></Column>
                    <Column field="" style="width: 10%">
                        <template #body="{ data }">
                            <Button
                                class="mr-2"
                                icon="ti ti-arrow-right"
                                rounded
                                size="small"
                                @click.prevent="handlePackageRelease(data)"
                            />
                        </template>
                    </Column>
                    <template #footer> In total there are {{ slotProps.items.length }} tokens.</template>
                </DataTable>
            </template>

            <template #grid="slotProps">
                <div
                    class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-5 my-5 p-5">
                    <Card v-for="queue in slotProps.items" :key="queue.id"
                          class="!border !border-info rounded-2xl bg-white cursor-pointer hover:bg-info/10"
                          @click.prevent="handlePackageRelease(queue)">
                        <template #content>
                            <div class="flex justify-between items-center mb-6">
                                <div class="flex items-center gap-2 text-info">
                                    <i class="ti ti-packages text-2xl"></i>
                                    <span class="text-2xl font-semibold">{{ queue.package_count }}</span>
                                </div>
                            </div>

                            <div class="text-center mb-6">
                                <h1 class="text-5xl xl:text-[80px] font-extrabold text-gray-900 tracking-wide">
                                    {{ queue?.token }}
                                </h1>
                            </div>

                            <div class="flex flex-wrap justify-center gap-1">
                                <template v-for="(hbl_package, index) in queue?.hbl_packages" :key="index">
                                    <Tag
                                        :value="`${hbl_package.quantity} ${hbl_package.package_type}`"
                                        class="rounded-full px-4 py-2 text-base"
                                        severity="info"
                                    />
                                </template>
                            </div>
                        </template>
                    </Card>
                </div>
            </template>

            <template #empty>
                <div class="flex p-10 justify-center">
                    No Tokens found.
                </div>
            </template>
        </DataView>
    </AppLayout>

    <PackageReleaseDialog :package-queue="selectedToken" :visible="showPackageReleaseDialog"
                          @close="closePackageReleaseModal"
                          @update:visible="showPackageReleaseDialog = $event"/>

    <Dialog :style="{ width: '40rem' }" :visible="returnDialogVisible" header="Return Package" modal @update:visible="returnDialogVisible = $event">
        <div class="space-y-4">
            <div class="field">
                <label for="token_number" class="block mb-2">Token Number</label>
                <InputText
                    id="token_number"
                    v-model="returnForm.token_number"
                    class="w-full"
                    @blur="loadPackageDetails"
                    @keyup.enter="loadPackageDetails"
                />
                <div v-if="returnForm.errors.token_number" class="text-red-500 text-sm mt-1">
                    {{ returnForm.errors.token_number }}
                </div>
            </div>

            <div v-if="returnForm.package_details" class="border rounded p-4">
                <h3 class="font-bold mb-2">Package Details</h3>
                <div class="grid grid-cols-2 gap-2">
                    <div><strong>Reference:</strong> {{ returnForm.package_details.reference }}</div>
                    <div><strong>Customer:</strong> {{ returnForm.package_details.customer }}</div>
                    <div><strong>Package Count:</strong> {{ returnForm.package_details.package_count }}</div>
                    <div><strong>Released At:</strong> {{ returnForm.package_details.released_at }}</div>
                </div>

                <div class="mt-3" v-if="returnForm.package_details.released_packages">
                    <h4 class="font-bold mb-1">Released Packages:</h4>
                    <ul class="list-disc pl-5">
                        <li v-for="(pkg, key) in returnForm.package_details.released_packages" :key="key">
                            {{ key }}
                        </li>
                    </ul>
                </div>
            </div>

            <div class="field">
                <label for="return_remarks" class="block mb-2">Remarks</label>
                <Textarea
                    id="return_remarks"
                    v-model="returnForm.remarks"
                    class="w-full"
                    rows="3"
                    placeholder="Enter return remarks..."
                />
                <div v-if="returnForm.errors.remarks" class="text-red-500 text-sm mt-1">
                    {{ returnForm.errors.remarks }}
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-2 mt-4">
            <Button label="Cancel" severity="secondary" type="button" @click="returnDialogVisible = false"></Button>
            <Button
                :disabled="!returnForm.package_details || returnForm.processing"
                :class="{ 'opacity-75': returnForm.processing }"
                label="Process Return"
                type="button"
                @click="handleReturnPackage"
            ></Button>
        </div>
    </Dialog>
</template>
