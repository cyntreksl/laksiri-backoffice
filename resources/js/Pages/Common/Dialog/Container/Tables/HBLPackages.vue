<script setup>
import {router, usePage} from "@inertiajs/vue3";
import {push} from "notivue";
import {computed, onMounted, ref, watchEffect} from "vue";
import HBLDetailModal from "@/Pages/Common/HBLDetailModal.vue";
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from "primevue/button";
import {useConfirm} from "primevue/useconfirm";
import MHBLDetailDialog from "@/Pages/Common/Dialog/MHBL/Index.vue";

const props = defineProps({
    container: {
        type: Object,
        default: () => {
        },
    },
    containerHBLS: {
        type: Array,
        default: () => [],
    },
    containerMHBLS: {
        type: Object,
        default: () => {
        },
    },
    filteredMHBLsLHBL: {
        type: Array,
        default: () => [],
    }
});

const emit = defineEmits(['fetContainerData']);

const containerData = ref({});
const confirm = useConfirm();
const showConfirmViewHBLModal = ref(false);
const hblRecord = ref({});
const packageCounts = ref([]);
const mhblId = ref(null);
const showConfirmViewMHBLModal = ref(false);

watchEffect(() => {
    containerData.value = props.container;
});

const getMHBLPackageCount = (mhblId) => {
    const relatedHBLs = props.filteredMHBLsLHBL.filter(item => item.mhbl?.id === mhblId);
    if (relatedHBLs.length > 0) {
        return relatedHBLs.reduce((sum, item) => sum + (item.packages_count || 0), 0);
    }else return 0;
}

const handleRemoveHBLFromContainer = (id) => {
    confirm.require({
        message: 'Would you like to unload this hbl record from this container?',
        header: 'Unload HBL?',
        icon: 'pi pi-info-circle',
        rejectLabel: 'Cancel',
        rejectProps: {
            label: 'Cancel',
            severity: 'secondary',
            outlined: true
        },
        acceptProps: {
            label: 'Unload',
            severity: 'warn'
        },
        accept: () => {
            router.put(route('loading.containers.unload.hbl', containerData.value.id), {
                    hbl_id: id
                },
                {
                    onSuccess: () => {
                        containerData.value = {
                            ...containerData.value,
                            hbls: Object.values(containerData.value.hbls).filter(hbl => hbl.id !== id)
                        };
                        emit('fetContainerData');
                        props.containerHBLS = props.containerHBLS.filter(item => item.id !== id);
                        if (containerData.value.hbls.length === 0) {
                            router.visit(route('loading.loaded-containers.index'));
                        }
                        push.success('Unloaded successfully!');
                    },
                    onError: () => {
                        console.error('Something went to wrong!');
                    },
                    preserveScroll: true,
                    preserveState: true,
                }
            )
        },
        reject: () => {
        }
    });
}

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

const fetchPackageCount = async () => {
    const mhbls = Object.values(props.containerMHBLS);

    mhbls.forEach(mhbl => {
        packageCounts.value[mhbl.id] = mhbl.hbls.reduce((total, hbl) => {
            return total + (hbl.packages_count || 0);
        }, 0);
    });
};

onMounted(() => {
    fetchPackageCount()
});

const confirmViewMHBL = async (id) => {
    mhblId.value = id;
    showConfirmViewMHBLModal.value = true;
};

const closeShowMHBLModal = () => {
    mhblId.value = null;
    showConfirmViewMHBLModal.value = false;
};

const handleRemoveMHBLFromContainer = (id) => {
    confirm.require({
        message: 'Would you like to unload this mhbl record from this container?',
        header: 'Unload MHBL?',
        icon: 'pi pi-info-circle',
        rejectLabel: 'Cancel',
        rejectProps: {
            label: 'Cancel',
            severity: 'secondary',
            outlined: true
        },
        acceptProps: {
            label: 'Unload',
            severity: 'warn'
        },
        accept: () => {
            router.put(route('loading.containers.unload.mhbl', containerData.value.id), {
                    mhbl_id: id
                },
                {
                    onSuccess: () => {
                        containerData.value = {
                            ...containerData.value,
                            hbls: Object.values(containerData.value.hbls).filter(hbl => {
                                if (hbl.mhbl && hbl.mhbl.id !== null) {
                                    return hbl.mhbl.id !== id;
                                }
                                return true;
                            })
                        };
                        emit('fetContainerData');
                        if (containerData.value.hbls.length === 0) {
                            router.visit(route('loading.loaded-containers.index'));
                        }
                        push.success('Unloaded successfully!');
                    },
                    onError: () => {
                        console.error('Something went to wrong!');
                    },
                    preserveScroll: true,
                    preserveState: true,
                }
            )
        },
        reject: () => {
        }
    });
}

const mergedHBLData = computed(() => {
    const containerMHBLS = Array.isArray(props.containerMHBLS)
        ? props.containerMHBLS
        : Object.values(props.containerMHBLS || {});

    return [
        ...props.containerHBLS.map(hbl => ({
            type: 'HBL',
            id: hbl.id,
            hbl_number: hbl.hbl_number,
            packages_count: hbl.packages_count,
            hbl_name: hbl.hbl_name,
            nic: hbl.nic,
            address: hbl.address,
            contact_number: hbl.contact_number,
            consignee_name: hbl.consignee_name,
            consignee_address: hbl.consignee_address,
        })),
        ...containerMHBLS.map(mhbl => ({
            type: 'MHBL',
            id: mhbl.id,
            hbl_number: mhbl.hbl_number,
            packages_count: getMHBLPackageCount(mhbl.id),
            hbl_name: mhbl.shipper?.name,
            nic: mhbl.shipper?.pp_or_nic_no,
            address: mhbl.shipper?.address,
            contact_number: mhbl.shipper?.mobile_number,
            consignee_name: mhbl.consignee?.name,
            consignee_address: mhbl.consignee?.address,
        }))
    ];
})
</script>

<template>
    <div class="my-5">
        <DataTable :rows="10" :rowsPerPageOptions="[5, 10, 20, 50, 100]" :value="mergedHBLData" paginator row-hover
                   tableStyle="min-width: 50rem">
            <template #empty>No HBLs found.</template>
            <Column class="font-bold" field="hbl_number" header="HBL"></Column>
            <Column field="packages_count" header="Packages">
                <template #body="slotProps">
                    <div class="flex items-center">
                        <i class="ti ti-package mr-1 text-blue-500" style="font-size: 1rem"></i>
                        {{ slotProps.data.packages_count }}
                    </div>
                </template>
            </Column>
            <Column field="hbl_name" header="HBL Name">
                <template #body="slotProps">
                    <div>{{ slotProps.data.hbl_name }}</div>
                    <div class="text-gray-500 text-sm">{{slotProps.data.nic}}</div>
                    <div class="text-gray-500 text-sm">{{slotProps.data.address}}</div>
                </template>
            </Column>
            <Column field="contact_number" header="Contact"></Column>
            <Column field="consignee_name" header="Consignee">
                <template #body="slotProps">
                    <div>{{ slotProps.data.consignee_name }}</div>
                    <div class="text-gray-500 text-sm">{{slotProps.data.consignee_address}}</div>
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
                            @click.prevent="data.type === 'HBL' ? confirmViewHBL(data.id) : confirmViewMHBL(data.id)"
                        />
                        <Button
                            v-tooltip="'Remove From Shipment'"
                            icon="pi pi-trash"
                            outlined
                            rounded
                            severity="danger"
                            size="small"
                            @click.prevent="data.type === 'HBL' ? handleRemoveHBLFromContainer(data.id) : handleRemoveMHBLFromContainer(data.id)"
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

    <MHBLDetailDialog :mhbl-id="mhblId"
                      :show="showConfirmViewMHBLModal"
                      @close="closeShowMHBLModal"
                      @update:show="showConfirmViewMHBLModal = $event"/>
</template>
