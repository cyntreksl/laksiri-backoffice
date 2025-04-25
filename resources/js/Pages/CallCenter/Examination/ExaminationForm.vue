<script setup>
import DestinationAppLayout from "@/Layouts/DestinationAppLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import InputError from "@/Components/InputError.vue";
import {router, useForm, usePage} from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import {push} from "notivue";
import InputLabel from "@/Components/InputLabel.vue";
import { ref } from "vue";
import HBLDetailContent from "@/Pages/Common/Partials/HBLDetailContent.vue";
import AppLayout from "@/Layouts/AppLayout.vue";

const props = defineProps({
    customerQueue: {
        type: Object,
        default: () => {}
    },
    reference: {
        type: String,
        required: true,
    },
    hblId: {
        type: Number,
        default: null
    }
})

const hbl = ref({});
const pickup = ref({});
const hblTotalSummary = ref({});
const isLoadingHbl = ref(false);

const fetchHBL = async () => {
    isLoadingHbl.value = true;

    try {
        const response = await fetch(`/hbls/${props.hblId}`, {
            method: "GET",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": usePage().props.csrf
            },
        });

        if (!response.ok) {
            throw new Error('Network response was not ok.');
        } else {
            const data = await response.json();
            hbl.value = data.hbl;
        }

    } catch (error) {
        console.log(error);
    } finally {
        isLoadingHbl.value = false;
    }
}

const fetchPickup = async () => {
    isLoadingHbl.value = true;

    try {
        const response = await fetch(`/pickups/${props.pickupId}`, {
            method: "GET",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": usePage().props.csrf
            },
        });

        if (!response.ok) {
            throw new Error('Network response was not ok.');
        } else {
            const data = await response.json();
            pickup.value = data;
        }

    } catch (error) {
        console.log(error);
    } finally {
        isLoadingHbl.value = false;
    }
}

const getHBLTotalSummary = async () => {
    try {
        const response = await fetch(`/hbls/get-total-summary/${props.hblId}`, {
            method: "GET",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": usePage().props.csrf,
            },
        });

        if (!response.ok) {
            throw new Error("Network response was not ok.");
        }else{
            hblTotalSummary.value = await response.json();
        }
    } catch (error) {
        console.error("Error:", error);
    } finally {
        // isLoading.value = false;
    }
};

if (props.hblId !== null) {
    fetchHBL();
    getHBLTotalSummary();
}

const hblPackages = ref([]);
const isLoading = ref(false);

const getHBLPackagesByReference = async () => {
    isLoading.value = true;
    try {
        const response = await fetch(`/get-hbl-packages-by-reference/${props.reference}`, {
            method: "GET",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": usePage().props.csrf
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

getHBLPackagesByReference();

const updateChecked = (pType, isChecked) => {
    form.released_packages = { ...form.released_packages, [pType]: isChecked };
};

const form = useForm({
    customer_queue: props.customerQueue,
    note: '',
    released_packages: {},
});

const handleUpdateReleaseHBLPackages = () => {
    form.post(route("call-center.examination.store"), {
        onSuccess: () => {
            router.visit(route("call-center.examination.queue.list"));
            form.reset();
            push.success('HBL Packages Released Successfully!');
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
    <AppLayout title="Release HBL Package">
        <template #header>Release HBL Package</template>

        <!-- Breadcrumb -->
        <Breadcrumb />

        <div class="grid-container">
            <div class="left-section w-full">
                <div class="grid grid-cols-1 mt-4 gap-4">
                    <div class="sm:col-span-3 space-y-5">
                        <div class="card px-4 py-4 sm:px-5">
                            <HBLDetailContent :hbl="hbl" :isLoading="isLoadingHbl" :showAuditDetails="false" :editPermission="false" :hbl-total-summary="hblTotalSummary"/>
                        </div>
                    </div>
                </div>
            </div>
            <div class="right-section w-full">
                <!-- Create Pickup Form -->
                <div class="grid grid-cols-1 mt-4 gap-4">
                    <div class="sm:col-span-3 space-y-5">
                        <div class="card px-4 py-4 sm:px-5">
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
                                <h2
                                    class="text-lg font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100"
                                >
                                    Release HBL Packages
                                </h2>

                                <div class="mt-4 space-y-4">
                                    <InputLabel v-for="(p, index) in hblPackages" :key="p.id" class="cursor-pointer">
                                        <input
                                            :checked="form.released_packages[p.id] || false"
                                            :value="p.id"
                                            class="form-checkbox is-basic size-5 rounded border-slate-400/70 checked:border-primary checked:bg-primary hover:border-primary focus:border-primary dark:border-navy-400 dark:checked:border-accent dark:checked:bg-accent dark:hover:border-accent dark:focus:border-accent mr-3"
                                            type="checkbox"
                                            @change="(event) => updateChecked(`${p.package_type} PKG ${index + 1}`, event.target.checked)"
                                        >
                                        {{ p.package_type }} PKG {{ index + 1 }}
                                    </InputLabel>
                                </div>

                                <div class="mt-4">
                                    <InputLabel value="Note" />
                                    <label class="block">
                                        <textarea
                                            v-model="form.note"
                                            class="form-textarea w-full resize-none rounded-lg border border-slate-300 bg-transparent p-2.5 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                            placeholder="Type note here..."
                                            rows="4"
                                        ></textarea>
                                    </label>
                                    <InputError :message="form.errors.note" />
                                </div>

                                <PrimaryButton
                                    :class="{ 'opacity-25': form.processing }"
                                    :disabled="form.processing"
                                    class="mt-3 float-right"
                                    @click="handleUpdateReleaseHBLPackages"
                                >
                                    Release & Create Gate Pass
                                </PrimaryButton>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style>

.grid-container {
    display: grid;
    grid-template-columns: 2fr 1fr; /* 2/3 for the left, 1/3 for the right */
    gap: 10px; /* Optional: Adds some space between the two sections */
    height: 100vh; /* Optional: Full viewport height */
}
.left-section {
    padding: 8px;
}
.right-section {
    padding: 8px;
}

@media (max-width: 768px) {
        .grid-container {
            grid-template-columns: 1fr; /* Stacks sections one by one */
            height: auto; /* Adjust height for mobile */
        }
    }

</style>
