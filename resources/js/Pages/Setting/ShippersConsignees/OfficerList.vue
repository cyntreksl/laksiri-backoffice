<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";

import CreateShipper from "@/Pages/Setting/ShippersConsignees/CreateShipper.vue";
import CreateConsignee from "@/Pages/Setting/ShippersConsignees/CreateConsignee.vue";
import {Link} from "@inertiajs/vue3";
defineProps({
    allOfficers: {
        type: Object,
        default: () => {},
    },
});


</script>

<template>
    <AppLayout title="Officerss">
        <template #header>Officers</template>

        <Breadcrumb/>

        <div class="flex items-center justify-between p-2 my-5">
            <h2 class="text-base font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100">
                Shipper & Consignees
            </h2>
            <div class="flex gap-2">
                <CreateShipper />
                <CreateConsignee />
            </div>
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
                        class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                    >
                        Email
                    </th>
                    <th
                        class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                    >
                       Mobile Number
                    </th>

                    <th
                        class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                    >
                        Officer Type
                    </th>
                    <th
                        class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                    >
                       NIC
                    </th>
                    <th
                        class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                    >
                        Residency No
                    </th>
                    <th
                        class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                    >
                        Address
                    </th>
                    <th
                        class="whitespace-nowrap rounded-tr-lg bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                    >
                        Action
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
                        {{officer.type}}
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
                    <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                        <div class="flex space-x-2">
                            <Link
                                :href="route('setting.shipper-consignees.edit', officer.id)"
                                class="btn size-8 p-0 text-info hover:bg-info/20 focus:bg-info/20 active:bg-info/25"
                            >
                                <svg
                                    class="size-4.5"
                                    fill="none"
                                    viewBox="0 0 512 512"
                                    xmlns="http://www.w3.org/2000/svg"
                                >
                                    <path
                                        d="M471 58.9L453.1 71c9.4 9.4 9.4 24.6 0 33.9L424 134.1 377.9 88 407 58.9c9.4-9.4 24.6-9.4 33.9 0zM209.8 256.2L344 121.9 390.1 168 255.8 302.2c-2.9 2.9-6.5 5-10.4 6.1l-58.5 16.7 16.7-58.5c1.1-3.9 3.2-7.5 6.1-10.4zM373.1 25L175.8 222.2c-8.7 8.7-15 19.4-18.3 31.1l-28.6 100c-2.4 8.4-.1 17.4 6.1 23.6s15.2 8.5 23.6 6.1l100-28.6c11.8-3.4 22.5-9.7 31.1-18.3L487 138.9c28.1-28.1 28.1-73.7 0-101.8L474.9 25C446.8-3.1 401.2-3.1 373.1 25zM88 64C39.4 64 0 103.4 0 152V424c0 48.6 39.4 88 88 88H360c48.6 0 88-39.4 88-88V312c0-13.3-10.7-24-24-24s-24 10.7-24 24V424c0 22.1-17.9 40-40 40H88c-22.1 0-40-17.9-40-40V152c0-22.1 17.9-40 40-40H200c13.3 0 24-10.7 24-24s-10.7-24-24-24H88z"
                                        fill="currentColor"
                                    ></path>
                                </svg>
                            </Link>
                        </div>
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
<style>
.flex {
    display: flex;
}
.gap-2 {
    gap: 0.5rem; /* Adjust the gap as needed */
}
</style>
