<script setup>
import {usePage} from "@inertiajs/vue3";
import {ref, watchEffect} from "vue";
import HBLDetailModal from "@/Pages/Common/Dialog/HBL/Index.vue";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import Button from "primevue/button";

const props = defineProps({
    container: {
        type: Object,
        default: () => {
        },
    },
    containerHBLS: {
        type: Object,
        default: () => {
        },
    }
});

const containerData = ref({});
const showConfirmViewHBLModal = ref(false);
const hblRecord = ref({});

watchEffect(() => {
    containerData.value = props.container;
});

const fetchHBL = async (id) => {
    try {
        const response = await fetch(`hbls/${id}`, {
            method: "GET",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": usePage().props.csrf,
            },
        });

        if (!response.ok) {
            throw new Error('Network response was not ok.');
        } else {
            const data = await response.json();
            hblRecord.value = data.hbl;
        }

    } catch (error) {
        console.log(error);
    }
}

const confirmViewHBL = async (id) => {
    await fetchHBL(id);
    showConfirmViewHBLModal.value = true;
};

const closeShowHBLModal = () => {
    showConfirmViewHBLModal.value = false;
};
</script>

<template>
    <div class="my-5">
        <DataTable :rows="10" :rowsPerPageOptions="[5, 10, 20, 50, 100]" :value="containerHBLS" paginator row-hover
                   tableStyle="min-width: 50rem">
            <template #empty>No MHBLs found.</template>
            <Column class="font-bold" field="mhbl" header="MHBL">
                <template #body="{data}">
                    <div class="flex items-center gap-2">
                        <span :class="{'text-gray-400 line-through': data.is_fully_unloaded}">
                            {{ data.mhbl.hbl_number || data.mhbl.reference || '-' }}
                        </span>
                        <span v-if="data.is_fully_unloaded"
                              class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-700">
                            Unloaded
                        </span>
                        <span v-else-if="data.has_unloaded_packages"
                              class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-700">
                            Partially Unloaded
                        </span>
                    </div>
                </template>
            </Column>
            <Column field="hbl_number" header="HBL">
                <template #body="{data}">
                    <span :class="{'text-gray-400': data.is_fully_unloaded}">{{ data.hbl_number || '-' }}</span>
                </template>
            </Column>
            <Column field="packages_count" header="Packages">
                <template #body="{data}">
                    <div :class="{'text-gray-400': data.is_fully_unloaded}" class="flex items-center">
                        <i class="ti ti-package mr-1 text-blue-500" style="font-size: 1rem"></i>
                        {{ data.packages_count }}
                    </div>
                </template>
            </Column>
            <Column field="hbl_name" header="Name">
                <template #body="slotProps">
                    <div :class="{'text-gray-400': slotProps.data.is_fully_unloaded}">{{ slotProps.data.hbl_name }}</div>
                    <div :class="{'text-gray-300': slotProps.data.is_fully_unloaded}" class="text-gray-500 text-sm">{{slotProps.data.nic}}</div>
                    <div :class="{'text-gray-300': slotProps.data.is_fully_unloaded}" class="text-gray-500 text-sm">{{slotProps.data.address}}</div>
                </template>
            </Column>
            <Column field="contact_number" header="Contact">
                <template #body="{data}">
                    <span :class="{'text-gray-400': data.is_fully_unloaded}">{{ data.contact_number || '-' }}</span>
                </template>
            </Column>
            <Column field="consignee_name" header="Consignee">
                <template #body="slotProps">
                    <div :class="{'text-gray-400': slotProps.data.is_fully_unloaded}">{{ slotProps.data.consignee_name }}</div>
                    <div :class="{'text-gray-300': slotProps.data.is_fully_unloaded}" class="text-gray-500 text-sm">{{slotProps.data.consignee_address}}</div>
                </template>
            </Column>
            <Column field="" header="Actions" style="width: 10%">
                <template #body="{ data }">
                    <div class="flex gap-2">
                        <Button
                            v-tooltip="'View Details'"
                            icon="pi pi-eye"
                            outlined
                            rounded
                            size="small"
                            @click.prevent="confirmViewHBL(data.id)"
                        />
                    </div>
                </template>
            </Column>
        </DataTable>
    </div>

    <HBLDetailModal
        :hbl-id="hblRecord?.id"
        :show="showConfirmViewHBLModal"
        @close="closeShowHBLModal"
        @update:show="showConfirmViewHBLModal = $event"
    />
</template>
