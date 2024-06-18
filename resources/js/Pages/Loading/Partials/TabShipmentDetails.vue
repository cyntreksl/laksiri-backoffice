<script setup>
import DangerOutlineButton from "@/Components/DangerOutlineButton.vue";
import InputError from "@/Components/InputError.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import Tab from "@/Components/Tab.vue";
import InputLabel from "@/Components/InputLabel.vue";
import DatePicker from "@/Components/DatePicker.vue";
import AccordionPanel from "@/Components/AccordionPanel.vue";
import TextInput from "@/Components/TextInput.vue";

const props = defineProps({
    container: {
        type: Object,
        default: () => {
        },
    }
});
</script>

<template>
    <Tab label="Shipment Details" name="tabShipment">

        <div
            class="flex flex-col items-center justify-between space-y-2 text-center sm:flex-row sm:space-y-0 sm:text-left">
            <div>
                <h3 class="text-xl font-semibold text-slate-700 dark:text-navy-100">
                    Shipment
                </h3>
                <p class="mt-1 hidden sm:block">{{ container.reference }}</p>
            </div>
        </div>

        <AccordionPanel show-panel title="Loading Details">
            <template #header-image>
                <div
                    class="flex size-8 items-center justify-center rounded-lg p-1 text-primary dark:bg-accent-light/10 dark:text-accent-light">
                    <svg class="icon icon-tabler icons-tabler-outline icon-tabler-truck-loading" fill="none"
                         height="24" stroke="currentColor"
                         stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24"
                         width="24"
                         xmlns="http://www.w3.org/2000/svg">
                        <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                        <path d="M2 3h1a2 2 0 0 1 2 2v10a2 2 0 0 0 2 2h15"/>
                        <path
                            d="M9 6m0 3a3 3 0 0 1 3 -3h4a3 3 0 0 1 3 3v2a3 3 0 0 1 -3 3h-4a3 3 0 0 1 -3 -3z"/>
                        <path d="M9 19m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/>
                        <path d="M18 19m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/>
                    </svg>
                </div>
            </template>
            <div class="px-4 py-4 sm:px-5">

                <div class="grid grid-cols-1 sm:grid-cols-4 gap-x-4 gap-y-8">
                    <div>
                        <label class="block">
                            <InputLabel value="Cargo Mode"/>
                            <select
                                class="form-select w-full rounded-lg border border-slate-300 bg-white px-3 py-2 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:bg-navy-700 dark:hover:border-navy-400 dark:focus:border-accent"
                            >
                                <option :value="null" disabled>
                                    Select Cargo Mode
                                </option>
                                <option>
                                    Sea Cargo
                                </option>
                            </select>
                        </label>
                        <InputError/>
                    </div>

                    <div>
                        <InputLabel value="Reference"/>
                        <TextInput class="w-full" placeholder="Reference"/>
                        <InputError/>
                    </div>

                    <div>
                        <InputLabel value="AWB No"/>
                        <TextInput class="w-full"/>
                        <InputError/>
                    </div>

                    <div>
                        <InputLabel value="EDT"/>
                        <DatePicker/>
                        <InputError/>
                    </div>

                    <div>
                        <InputLabel value="ETA"/>
                        <DatePicker/>
                        <InputError/>
                    </div>
                </div>

            </div>
        </AccordionPanel>

        <AccordionPanel show-panel title="Loading Status">
            <template #header-image>
                <div
                    class="flex size-8 items-center justify-center rounded-lg p-1 text-primary dark:bg-accent-light/10 dark:text-accent-light">
                    <svg class="icon icon-tabler icons-tabler-outline icon-tabler-status-change" fill="none"
                         height="24" stroke="currentColor"
                         stroke-linecap="round"
                         stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24"
                         width="24"
                         xmlns="http://www.w3.org/2000/svg">
                        <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                        <path d="M6 18m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/>
                        <path d="M18 18m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/>
                        <path d="M6 12v-2a6 6 0 1 1 12 0v2"/>
                        <path d="M15 9l3 3l3 -3"/>
                    </svg>
                </div>
            </template>
            <div class="px-4 py-4 sm:px-5">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-8">
                    <div>
                        <label class="block">
                            <InputLabel value="Last Status"/>
                            <select
                                class="form-select w-full rounded-lg border border-slate-300 bg-white px-3 py-2 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:bg-navy-700 dark:hover:border-navy-400 dark:focus:border-accent"
                            >
                                <option :value="null" disabled>
                                    Select Status
                                </option>
                                <option>
                                    Status
                                </option>
                            </select>
                        </label>
                        <InputError/>
                    </div>

                    <div>
                        <InputLabel value="Note"/>
                        <TextInput class="w-full" placeholder="Type something..."/>
                        <InputError/>
                    </div>

                    <div>
                        <InputLabel value="Reached Destination?"/>
                        <TextInput class="w-full"/>
                        <InputError/>
                    </div>

                    <div>
                        <InputLabel value="Reached Date"/>
                        <DatePicker/>
                        <InputError/>
                    </div>
                </div>
            </div>
        </AccordionPanel>

        <div class="flex space-x-2 items-center justify-end">
            <DangerOutlineButton>
                <svg class="size-5 mr-2" fill="none" stroke="currentColor" stroke-width="1.5"
                     viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"
                        stroke-linecap="round"
                        stroke-linejoin="round"/>
                </svg>

                Delete Loading
            </DangerOutlineButton>

            <PrimaryButton>
                <svg class="icon icon-tabler icons-tabler-outline icon-tabler-device-floppy size-5 mr-2"
                     fill="none" height="24" stroke="currentColor" stroke-linecap="round"
                     stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" width="24"
                     xmlns="http://www.w3.org/2000/svg">
                    <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                    <path d="M6 4h10l4 4v10a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2"/>
                    <path d="M12 14m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/>
                    <path d="M14 4l0 4l-6 0l0 -4"/>
                </svg>
                Save Changes
            </PrimaryButton>
        </div>
    </Tab>
</template>

<style scoped>

</style>
