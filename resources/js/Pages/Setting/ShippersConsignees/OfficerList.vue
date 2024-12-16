<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";

import PrimaryButton from "@/Components/PrimaryButton.vue";
import { push } from "notivue";

import { ref } from "vue";
import { Link, router } from "@inertiajs/vue3";
import CreateExceptionNameForm from "@/Pages/Setting/ExceptionNames/CreateExceptionNameForm.vue";
defineProps({
    allOfficers: {
        type: Object,
        default: () => {},
    },
});


</script>

<template>
    <AppLayout title="Package Types">
        <template #header>Package Types</template>

        <Breadcrumb/>
        <div class="flex items-center justify-between p-2 my-5">
            <h2
                class="text-base font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100"
            >
                Shipper & Consignees
            </h2>
        </div>


        <div class="min-w-full overflow-auto">
            <table class="is-hoverable w-full text-left">
                <thead>
                <tr>
                    <th
                        class="whitespace-nowrap rounded-tl-lg bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                    >
                        #
                    </th>
                    <th
                        class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                    >
                        Name
                    </th>
                    <th
                        class="whitespace-nowrap rounded-tl-lg bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                    >
                        Email
                    </th>
                    <th
                        class="whitespace-nowrap rounded-tl-lg bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                    >
                        Office Type
                    </th>
                    <th
                        class="whitespace-nowrap rounded-tl-lg bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                    >
                       NIC
                    </th>
                    <th
                        class="whitespace-nowrap rounded-tl-lg bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                    >
                        Residency No
                    </th>
                    <th
                        class="whitespace-nowrap rounded-tr-lg bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                    >
                        Address
                    </th>





                </tr>
                </thead>
                <tbody>
                <tr
                    v-for="(officer, index) in allOfficers"
                    v-if="allOfficers && Object.keys(allOfficers).length > 0"
                    :key="officer.id"
                    class="border-y border-transparent border-b-slate-200 dark:border-b-navy-500"
                >
                    <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                        {{ index + 1 }}
                    </td>
                    <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                        {{ officer.name }}
                    </td>
                    <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                        {{officer.email}}
                    </td>
                    <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                        {{officer.mobile_number}}
                    </td>
                    <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                        {{officer.pp_or_nic_no}}
                    </td>
                    <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                        {{officer.residency_no}}
                    </td>
                    <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                        {{officer.address}}
                    </td>
                </tr>
                <tr
                    v-else
                    class="border-y border-transparent border-b-slate-200 dark:border-b-navy-500 last:border-0 bg-white"
                >
                    <td class="whitespace-nowrap px-4 py-3 sm:px-5" colspan="8">
                        No Exception Names.
                    </td>
                </tr>
                </tbody>
            </table>
        </div>

    </AppLayout>
</template>
