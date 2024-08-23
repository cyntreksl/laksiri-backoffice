<script setup>
import DestinationAppLayout from "@/Layouts/DestinationAppLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import InputError from "@/Components/InputError.vue";
import {router, useForm} from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import {push} from "notivue";
import InputLabel from "@/Components/InputLabel.vue";
import {ref} from "vue";

const props = defineProps({
    customerQueue: {
        type: Object,
        default: () => {}
    },
    reference: {
        type: String,
        required: true,
    }
})

const hblPackages = ref([]);
const isLoading = ref(false);

const getHBLPackagesByReference = async () => {
    isLoading.value = true;
    try {
        const response = await fetch(`/get-hbl-packages-by-reference/${props.reference}`, {
            method: "GET",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content")
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
    <DestinationAppLayout title="Release HBL Package">
        <template #header>Release HBL Package</template>

        <!-- Breadcrumb -->
        <Breadcrumb />

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
    </DestinationAppLayout>
</template>
