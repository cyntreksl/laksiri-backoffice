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
import Tag from 'primevue/tag';

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

            // Initialize form.released_packages based on existing released packages
            const existingReleasedPackages = props.packageQueue?.released_packages || {};
            form.released_packages = { ...existingReleasedPackages };
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

const updateChecked = (packageId, packageLabel, isChecked) => {
    // Prevent checking if package is already released
    if (isPackageReleased(packageId)) {
        return;
    }
    form.released_packages = { ...form.released_packages, [packageId]: isChecked ? packageLabel : false };
};

const form = useForm({
    package_queue: props.packageQueue,
    released_packages: {},
    note: ''
});

const hasSelectedPackages = computed(() => {
    // Only count packages that are selected AND not already released
    return Object.entries(form.released_packages).some(([id, value]) => {
        return value && !isPackageReleased(parseInt(id));
    });
});

// Check if a package is already released
const isPackageReleased = (packageId) => {
    // Check from the packageQueue's released_packages tracking
    const existingReleasedPackages = props.packageQueue?.released_packages || {};
    const isInReleasedList = existingReleasedPackages[packageId] === true || existingReleasedPackages[packageId];

    // Also check the actual package's release_status
    const packageData = hblPackages.value.find(p => p.id === packageId);
    const hasReleasedStatus = packageData?.release_status === 'released';

    return isInReleasedList || hasReleasedStatus;
};

// Get count of packages by release status
const packageCounts = computed(() => {
    const total = hblPackages.value.length;
    const released = hblPackages.value.filter(p => isPackageReleased(p.id)).length;
    const held = total - released;
    return { total, released, held };
});

const handleUpdateReleasePackages = () => {
    // Validate that at least one package is selected
    const selectedPackages = Object.entries(form.released_packages)
        .filter(([id, value]) => value && !isPackageReleased(parseInt(id)));

    if (selectedPackages.length === 0) {
        push.error('Please select at least one package to release.');
        return;
    }

    // Filter out any already-released packages from the submission
    const validReleasedPackages = {};
    Object.entries(form.released_packages).forEach(([id, value]) => {
        if (value && !isPackageReleased(parseInt(id))) {
            validReleasedPackages[id] = value;
        }
    });

    // Double-check we have valid packages
    if (Object.keys(validReleasedPackages).length === 0) {
        push.error('All selected packages have already been released. Please refresh the page.');
        return;
    }

    form.package_queue = props.packageQueue;
    form.released_packages = validReleasedPackages;

    form.post(route("call-center.package.store"), {
        onSuccess: () => {
            router.visit(route("call-center.package.queue.list"));
            form.reset();
            push.success('Package(s) released successfully!');
        },
        onError: (errors) => {
            // Display specific error messages
            if (errors.error) {
                push.error(errors.error);
            } else {
                const errorMessage = Object.values(errors).join(', ');
                push.error(errorMessage || 'Something went wrong!');
            }
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
            <!-- Package Status Summary -->
            <div v-if="hblPackages.length > 0" class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-4">
                <div class="grid grid-cols-3 gap-2 text-sm">
                    <div>
                        <strong class="text-blue-800">Total:</strong>
                        <span class="ml-2">{{ packageCounts.total }}</span>
                    </div>
                    <div>
                        <strong class="text-green-800">Released:</strong>
                        <span class="ml-2">{{ packageCounts.released }}</span>
                    </div>
                    <div>
                        <strong class="text-orange-800">On Hold:</strong>
                        <span class="ml-2">{{ packageCounts.held }}</span>
                    </div>
                </div>
            </div>

            <!-- Warning if all packages are already released -->
            <div v-if="packageCounts.held === 0 && hblPackages.length > 0" class="bg-yellow-50 border border-yellow-300 rounded-lg p-4 mb-4 flex items-start gap-3">
                <i class="pi pi-exclamation-triangle text-yellow-600 text-xl"></i>
                <div>
                    <p class="font-semibold text-yellow-800">All Packages Already Released</p>
                    <p class="text-sm text-yellow-700">All packages for this token have already been released to the Examination Queue.</p>
                </div>
            </div>

            <div class="space-y-3">
                <div
                    v-for="(p, index) in hblPackages"
                    :key="p.id"
                    :class="{
                        'bg-green-50 border-green-300 opacity-75': isPackageReleased(p.id),
                        'bg-white border-gray-200': !isPackageReleased(p.id)
                    }"
                    class="flex items-center justify-between gap-3 p-3 rounded-lg border"
                >
                    <div class="flex items-center gap-3 flex-1">
                        <Checkbox
                            :checked="form.released_packages[p.id] || false"
                            :disabled="isPackageReleased(p.id)"
                            :input-id="`${p.package_type}-${index}`"
                            :pt="{
                                root: {
                                    class: isPackageReleased(p.id) ? 'cursor-not-allowed' : 'cursor-pointer'
                                }
                            }"
                            :value="p.id"
                            @change="(event) => updateChecked(p.id, `${p.package_type} PKG ${index + 1}`, event.target.checked)"
                        />
                        <label
                            :class="isPackageReleased(p.id) ? 'cursor-not-allowed' : 'cursor-pointer'"
                            :for="`${p.package_type}-${index}`"
                            class="flex-1"
                        >
                            <div class="font-medium">{{ p.package_type }}</div>
                            <div class="text-xs text-gray-600">
                                Qty: {{ p.quantity }} | Size: {{ p.length }}×{{ p.width }}×{{ p.height }}
                            </div>
                        </label>
                    </div>
                    <div v-if="isPackageReleased(p.id)">
                        <Tag severity="success" value="Already Released" />
                    </div>
                    <div v-else>
                        <Tag severity="warning" value="On Hold" />
                    </div>
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
            <Button
                :disabled="isLoading"
                icon="pi pi-refresh"
                label="Refresh"
                outlined
                severity="secondary"
                type="button"
                @click="getHBLPackagesByReference"
            />
            <Button label="Cancel" severity="secondary" type="button" @click="emit('close')"></Button>
            <Button :class="{ 'opacity-25': form.processing || !hasSelectedPackages }" :disabled="form.processing || !hasSelectedPackages" label="Release Package" type="button"
                    @click="handleUpdateReleasePackages"></Button>
        </div>

    </Dialog>
</template>
