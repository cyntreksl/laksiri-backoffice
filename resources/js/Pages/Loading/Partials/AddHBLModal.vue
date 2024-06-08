<script setup>
import DialogModal from "@/Components/DialogModal.vue";
import InputError from "@/Components/InputError.vue";
import TextInput from "@/Components/TextInput.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import {ref} from "vue";
import moment from "moment";
import {router} from "@inertiajs/vue3";

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    container: {
        type: Object,
        default: () => {
        },
    }
});

const emit = defineEmits(['close']);

const hblRef = ref('');
const hblData = ref(null);
const errorMessage = ref('');

const getHBLWithPackages = async () => {
    errorMessage.value = ''; // Clear previous error
    hblData.value = null; // Clear previous data

    try {
        const response = await fetch("/containers/get-hbl/packages", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content")
            },
            body: JSON.stringify({'reference': hblRef.value, 'cargo_type': props.container.cargo_type})
        });

        if (!response.ok) {
            const errorData = await response.json();
            if (errorData.errors && errorData.errors.reference) {
                throw new Error(errorData.errors.reference[0]);
            } else {
                throw new Error('Network response was not ok.');
            }
        } else {
            const data = await response.json();
            hblData.value = data.hbl;
        }

    } catch (error) {
        errorMessage.value = error.message;
    }
}

const handleLoad = (packages) => {
    if (!Array.isArray(packages)) {
        packages = [packages];
    }

    router.post(route("loading.loaded-containers.store"), {
            container_id: props.container.id,
            packages,
        },
        {
            onSuccess: () => {
                getHBLWithPackages();
            },
            onError: () => {
                console.error('Something went to wrong!');
            },
            preserveScroll: true,
            preserveState: true,
        });
}
</script>

<template>
    <DialogModal :closeable="true" :maxWidth="'4xl'" :show="show" @close="close">
        <template #title>
            <div class="flex justify-between items-center">
                <div>HBL Add to Shipment</div>
                <button
                    class="text-gray-500 jus text-right hover:text-red-500 focus:outline-none"
                    @click="$emit('close')"
                >
                    <svg class="size-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"
                         xmlns="http://www.w3.org/2000/svg">
                        <path d="M6 18 18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            </div>

        </template>
        <template #content>
            <div class="flex">
                <div class="w-full">
                    <div>
                        <TextInput v-model="hblRef" class="w-full" placeholder="Enter HBL Reference"/>
                        <InputError :message="errorMessage"/>
                    </div>
                    <PrimaryButton :disabled="!hblRef" class="w-full mt-2" @click.prevent="getHBLWithPackages">Confirm</PrimaryButton>
                </div>
            </div>

            <div v-if="hblData" class="is-scrollbar-hidden relative space-y-2.5 overflow-y-auto p-0.5 mt-5">
                <div v-for="(hblPackage, index) in hblData.packages" class="card cursor-pointer border shadow-sm">
                    <div class="flex justify-between items-center">
                        <div class="space-y-3 rounded-lg px-2.5 pb-2 pt-1.5">
                            <div>
                                <div class="flex justify-between">
                                    <p class="font-medium tracking-wide text-lg text-slate-600 dark:text-navy-100">
                                        {{ hblData.hbl }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex flex-wrap gap-1">
                                <div
                                    class="badge space-x-1 bg-slate-150 py-1 px-1.5 text-slate-800 dark:bg-navy-500 dark:text-navy-100">
                                    <svg class="size-3.5" fill="none" stroke="currentColor"
                                         viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                                            stroke-linecap="round" stroke-linejoin="round"
                                            stroke-width="2"/>
                                    </svg>
                                    <span>{{
                                            moment(hblPackage.created_at).format('YYYY-MM-DD')
                                        }}</span>
                                </div>

                                <div
                                    class="badge space-x-1 bg-warning/10 py-1 px-1.5 text-warning dark:bg-warning/15">
                                    <svg
                                        class="size-4 icon icon-tabler icons-tabler-outline icon-tabler-scale"
                                        fill="none"
                                        stroke="currentColor" stroke-linecap="round"
                                        stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24"
                                        width="24"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                                        <path d="M7 20l10 0"/>
                                        <path d="M6 6l6 -1l6 1"/>
                                        <path d="M12 3l0 17"/>
                                        <path d="M9 12l-3 -6l-3 6a3 3 0 0 0 6 0"/>
                                        <path d="M21 12l-3 -6l-3 6a3 3 0 0 0 6 0"/>
                                    </svg>
                                    <span>Volume {{ hblPackage.volume }}</span>
                                </div>

                                <div
                                    class="badge space-x-1 bg-error/10 py-1 px-1.5 text-error dark:bg-error/15">
                                    <svg
                                        class="size-4 icon icon-tabler icons-tabler-outline icon-tabler-weight"
                                        fill="none" height="24" stroke="currentColor"
                                        stroke-linecap="round"
                                        stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24"
                                        width="24"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                                        <path d="M12 6m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0"/>
                                        <path
                                            d="M6.835 9h10.33a1 1 0 0 1 .984 .821l1.637 9a1 1 0 0 1 -.984 1.179h-13.604a1 1 0 0 1 -.984 -1.179l1.637 -9a1 1 0 0 1 .984 -.821z"/>
                                    </svg>
                                    <span>Weight {{ hblPackage.volume }}</span>
                                </div>

                                <div
                                    class="badge space-x-1 bg-success/10 py-1 px-1.5 text-success dark:bg-success/15">
                                    <svg
                                        class="size-4 icon icon-tabler icons-tabler-outline icon-tabler-hash"
                                        fill="none" stroke="currentColor"
                                        stroke-linecap="round" stroke-linejoin="round"
                                        stroke-width="2"
                                        viewBox="0 0 24 24"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                                        <path d="M5 9l14 0"/>
                                        <path d="M5 15l14 0"/>
                                        <path d="M11 4l-4 16"/>
                                        <path d="M17 4l-4 16"/>
                                    </svg>
                                    <span>Quantity {{ hblPackage.quantity }}</span>
                                </div>
                            </div>
                            <p class="mt-px font-medium text-slate-400 dark:text-navy-300">
                                {{ hblPackage.package_type }}
                            </p>
                        </div>
                        <div class="px-2.5">
                            <svg
                                class="icon icon-tabler icons-tabler-outline icon-tabler-corner-up-right-double hover:text-success"
                                fill="none" height="24" stroke="currentColor" stroke-linecap="round"
                                stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24"
                                width="24"
                                x-tooltip.placement.top.success="'Click to Load'"
                                xmlns="http://www.w3.org/2000/svg"
                                @click.prevent="handleLoad(hblPackage)">
                                <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                                <path d="M4 18v-6a3 3 0 0 1 3 -3h7"/>
                                <path d="M10 13l4 -4l-4 -4m5 8l4 -4l-4 -4"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </DialogModal>
</template>
