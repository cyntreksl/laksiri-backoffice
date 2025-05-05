<script setup>
import {computed} from "vue";
import {router, useForm} from "@inertiajs/vue3";
import {push} from "notivue";
import Dialog from "primevue/dialog";
import Button from "primevue/button";
import Card from "primevue/card";

const props = defineProps({
    visible: {
        type: Boolean,
        default: false,
    },
    warehouseArray: {
        type: Object,
        default: () => {
        },
    },
    warehouseMHBLs: {
        type: Object,
        default: () => {
        },
    }
});

const emit = defineEmits(["update:visible"]);

const countPackages = (packageHBlId) => {
    return props.warehouseArray.filter(item => item.hbl_id === packageHBlId).length;
}

const uniqueContainerArray = computed(() => {
    const seen = new Set();
    return props.warehouseArray.filter(item => {
        const hblId = item.hbl.id;
        if (!seen.has(hblId)) {
            seen.add(hblId);
            return true;
        }
        return false;
    });
});

const form = useForm({
    container_id: route().params.container,
    packages: [],
});

const handleFinishUnloading = () => {
    props.warehouseMHBLs.forEach(mhbl => {
        mhbl.packages.forEach(pkg => {
            form.packages.push(pkg);
        });
    });

    props.warehouseArray.forEach(pkg => {
        form.packages.push(pkg);
    });

    form.post(route("arrival.unload-container.unload"), {
        onSuccess: () => {
            push.success('Unloading successfully!');
            emit('close');
            router.visit(route("arrival.unloading-points.index", {
                'container': route().params.container,
            }));
        },
        onError: () => {
            push.error('Something went to wrong!');
        },
        preserveScroll: true,
        preserveState: true,
    });
}
</script>

<template>
    <Dialog :style="{ width: '50rem' }" :visible="visible" header="Unloaded Summery" modal
            @update:visible="(newValue) => $emit('update:visible', newValue)">

        <div class="grid grid-cols-3 gap-4 mb-4">
            <Card
                v-for="(packageData, index) in uniqueContainerArray"
                :key="index"
                class="!shadow-md !border rounded-2xl bg-white"
            >
                <template #content>
                    <div class="flex items-center space-x-4">
                        <div class="flex items-center justify-center w-12 h-12 bg-info/10 text-info rounded-full">
                            <i class="ti ti-box text-2xl"></i>
                        </div>
                        <div>
                            <div class="text-lg font-semibold text-gray-800">
                                {{ packageData.hbl.hbl_number }}
                            </div>
                            <div class="text-sm text-success flex items-center gap-1 mt-1">
                                <i class="ti ti-packages"></i>
                                {{ countPackages(packageData.hbl_id) }} Packages
                            </div>
                        </div>
                    </div>
                </template>
            </Card>
        </div>

        <div v-if="warehouseMHBLs.length > 0" class="grid grid-cols-3 gap-4">
            <Card v-for="(mhbl, index) in warehouseMHBLs" class="!shadow-md !border rounded-2xl bg-white">
                <template #content>
                    <div class="flex items-center space-x-4">
                        <div class="flex items-center justify-center w-12 h-12 bg-warning/10 text-warning rounded-full">
                            <i class="ti ti-box text-2xl"></i>
                        </div>
                        <div>
                            <div class="text-lg font-semibold text-gray-800">
                                {{ mhbl.packages[0].hbl.mhbl.hbl_number || mhbl.mhblReference }}
                            </div>
                            <div class="text-sm text-success flex items-center gap-1 mt-1">
                                <i class="ti ti-packages"></i>
                                {{ mhbl.packages.length }} Packages
                            </div>
                        </div>
                    </div>
                </template>
            </Card>
        </div>

        <div class="flex justify-end gap-2 mt-5">
            <Button label="Cancel" severity="secondary" type="button" @click="emit('close')"></Button>
            <Button :class="{ 'opacity-25': form.processing }" :disabled="form.processing" label="Finish Unload"
                    type="button"
                    @click="handleFinishUnloading"></Button>
        </div>
    </Dialog>
</template>
