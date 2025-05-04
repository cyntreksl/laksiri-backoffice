<script setup>
import {ref} from "vue";
import HBLDetailModal from "@/Pages/Common/HBLDetailModal.vue";
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from "primevue/button";
import Tag from "primevue/tag";

const props = defineProps({
    mhbl: {
        type: Object,
        default: () => ({}),
    },
});

const showConfirmViewHBLModal = ref(false);
const hblId = ref(null);

const confirmViewHBL = (id) => {
    hblId.value = id;
    showConfirmViewHBLModal.value = true;
};

const closeModal = () => {
    showConfirmViewHBLModal.value = false;
    hblId.value = null;
};

const getPackageMeasures = (packages) => {
    const totalVolume = packages.reduce((total, pkg) =>
            total + (pkg.volume ?? 0),
        0);

    const totalWeight = packages.reduce((total, pkg) =>
            total + (pkg.weight ?? 0),
        0);

    return [
        totalVolume,
        totalWeight
    ];
};

const resolveWarehouse = (hbl) => {
    switch (hbl.warehouse.toUpperCase()) {
        case 'COLOMBO':
            return 'info';
        case 'NINTAVUR':
            return 'danger';
        default:
            return null;
    }
};

const resolveHBLType = (hbl) => {
    switch (hbl.hbl_type) {
        case 'UPB':
            return 'secondary';
        case 'Gift':
            return 'warn';
        case 'Door to Door':
            return 'info';
        default:
            return null;
    }
};

const resolveCargoType = (hbl) => {
    switch (hbl.cargo_type) {
        case 'Sea Cargo':
            return {
                icon: "ti ti-sailboat",
                color: "success",
            };
        case 'Air Cargo':
            return {
                icon: "ti ti-plane-tilt",
                color: "info",
            };
        default:
            return null;
    }
};
</script>

<template>
    <div class="my-5">
        <DataTable :rows="10" :rowsPerPageOptions="[5, 10, 20, 50, 100]" :value="mhbl.hbls" paginator row-hover
                   tableStyle="min-width: 50rem">
            <template #empty>No HBLs found.</template>
            <Column class="font-bold" field="hbl_number" header="HBL"></Column>
            <Column field="packages_count" header="Packages">
                <template #body="slotProps">
                    <div class="flex items-center">
                        <i class="ti ti-package mr-1 text-blue-500" style="font-size: 1rem"></i>
                        {{ slotProps.data.packages.length ?? 0 }}
                    </div>
                </template>
            </Column>
            <Column field="cargo_type" header="Cargo Type" sortable>
                <template #body="slotProps">
                    <Tag :icon="resolveCargoType(slotProps.data).icon" :severity="resolveCargoType(slotProps.data).color" :value="slotProps.data.cargo_type" class="text-sm"></Tag>
                </template>
            </Column>
            <Column field="hbl_name" header="HBL Name">
                <template #body="slotProps">
                    <div>{{ slotProps.data.hbl_name }}</div>
                    <div class="text-gray-500 text-sm">{{slotProps.data.nic}}</div>
                    <div class="text-gray-500 text-sm">{{slotProps.data.address}}</div>
                </template>
            </Column>
            <Column field="hbl_type" header="HBL Type" sortable>
                <template #body="slotProps">
                    <Tag :severity="resolveHBLType(slotProps.data)" :value="slotProps.data.hbl_type"></Tag>
                </template>
            </Column>
            <Column field="grand_volume" header="Grand Volume">
                <template #body="slotProps">
                    <div class="flex items-center">
                        <i class="ti ti-scale mr-1 text-blue-500" style="font-size: 1rem"></i>
                        {{ getPackageMeasures(slotProps.data.packages)[0].toFixed(3) ?? 0 }}
                    </div>
                </template>
            </Column>
            <Column field="grand_weight" header="Grand Weight">
                <template #body="slotProps">
                    <div class="flex items-center">
                        <i class="ti ti-scale-outline mr-1 text-blue-500" style="font-size: 1rem"></i>
                        {{ getPackageMeasures(slotProps.data.packages)[1].toFixed(2) ?? 0 }}
                    </div>
                </template>
            </Column>
            <Column field="warehouse" header="Warehouse" sortable>
                <template #body="slotProps">
                    <Tag :severity="resolveWarehouse(slotProps.data)" :value="slotProps.data.warehouse.toUpperCase()"></Tag>
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
        :hbl-id="hblId"
        :show="showConfirmViewHBLModal"
        @close="closeModal"
        @update:show="showConfirmViewHBLModal = $event"
    />
</template>
