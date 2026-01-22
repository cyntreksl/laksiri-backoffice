<script setup>
import InputError from "@/Components/InputError.vue";
import {router, useForm, usePage} from "@inertiajs/vue3";
import Checkbox from 'primevue/checkbox';
import {push} from "notivue";
import {ref, watch, computed} from "vue";
import Dialog from "primevue/dialog";
import Button from "primevue/button";
import Textarea from 'primevue/textarea';
import IftaLabel from 'primevue/iftalabel';

const props = defineProps({
    visible: {
        type: Boolean,
        default: false,
    },
    packageQueue: {
        type: Object,
        default: () => {}
    }
})

const emit = defineEmits(["update:visible"]);

const hblPackages = ref([]);
const isLoading = ref(false);

const getHBLPackagesByReference = async () => {
    isLoading.value = true;
    try {
        const response = await fetch(`/get-hbl-packages-by-reference/${props.packageQueue.reference}`, {
            method: "GET",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": usePage().props.csrf,
            },
        });

        if (!response.ok) {
            throw new Error('Network response was not ok.');
        } else {
            hblPackages.value = await response.json();
        }

    } catch (error) {
        console.log(error);
    } finally {
        isLoading.value = false;
    }
}

watch(
    () => props.visible,
    (newVal) => {
        if (newVal && props.packageQueue?.reference) {
            getHBLPackagesByReference();
        }
    }
);

const updateChecked = (pType, isChecked) => {
    form.released_packages = { ...form.released_packages, [pType]: isChecked };
};

const form = useForm({
    package_queue: props.packageQueue,
    released_packages: {},
    note: ''
});

const hasSelectedPackages = computed(() => {
    return Object.values(form.released_packages).some(Boolean);
});

const handleUpdateReleasePackages = () => {
    // Validate that at least one package is selected
    const selectedPackages = Object.values(form.released_packages).filter(Boolean);
    
    if (selectedPackages.length === 0) {
        push.error('Please select at least one package to release.');
        return;
    }

    form.package_queue = props.packageQueue;

    form.post(route("call-center.package.store"), {
        onSuccess: () => {
            router.visit(route("call-center.package.queue.list"));
            form.reset();
            push.success('Package Released!');
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
    <Dialog :style="{ width: '40rem' }" :visible="visible" header="Package Release" modal @update:visible="(newValue) => $emit('update:visible', newValue)">

        <div v-if="isLoading" class="flex animate-pulse flex-col">
            <div class="h-48 w-full rounded-lg bg-slate-150 dark:bg-navy-500"></div>
            <div class="flex space-x-5 py-4">
                <div class="flex flex-1 flex-col justify-between py-2">
                    <div class="h-3 w-10/12 rounded bg-slate-150 dark:bg-navy-500"></div>
                    <div class="h-6 w-full rounded bg-slate-150 dark:bg-navy-500"></div>
                </div>
            </div>
        </div>
        <div v-else>
            <div class="space-y-4">
                <div v-for="(p, index) in hblPackages" :key="p.id" class="flex items-center gap-2">
                    <Checkbox :checked="form.released_packages[p.id] || false" :input-id="`${p.package_type}-${index}`"
                              :value="p.id" @change="(event) => updateChecked(`${p.package_type} PKG ${index + 1}`, event.target.checked)" />
                    <label :for="`${p.package_type}-${index}`" class="cursor-pointer">
                        {{ p.package_type }}
                    </label>
                </div>
            </div>

            <div class="mt-4">
                <IftaLabel>
                    <Textarea id="description" v-model="form.note" class="w-full" cols="30" placeholder="Type note here..." rows="5" style="resize: none" />
                    <label for="description">Note</label>
                </IftaLabel>
                <InputError :message="form.errors.note" />
            </div>
        </div>

        <div class="flex justify-end gap-2 mt-3">
            <Button label="Cancel" severity="secondary" type="button" @click="emit('close')"></Button>
            <Button :class="{ 'opacity-25': form.processing || !hasSelectedPackages }" :disabled="form.processing || !hasSelectedPackages" label="Release Package" type="button"
                    @click="handleUpdateReleasePackages"></Button>
        </div>

    </Dialog>
</template>
