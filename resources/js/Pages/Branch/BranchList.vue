<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import {Link} from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";

defineProps({
    branches: {
        type: Object,
        default: () => {}
    }
})

</script>

<template>
    <AppLayout title="Branches">
        <template #header>Branches</template>

        <Breadcrumb/>

        <div class="flex items-center justify-between p-2 my-5">
            <h2 class="text-base font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100">
                Branches
            </h2>

            <Link :href="route('branches.create')">
                <PrimaryButton>
                    Create New Branch
                </PrimaryButton>
            </Link>
        </div>

        <div class="is-scrollbar-hidden min-w-full overflow-x-auto"
        >
            <table class="is-hoverable w-full text-left">
                <thead>
                <tr>
                    <th
                        class="whitespace-nowrap rounded-tl-lg bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                    >
                        Name
                    </th>
                    <th
                        class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                    >
                        Type
                    </th>
                    <th
                        class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                    >
                        Currency Name
                    </th>
                    <th
                        class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                    >
                        Currency Symbol
                    </th>
                    <th
                        class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                    >
                        Cargo Modes
                    </th>
                    <th
                        class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                    >
                        Delivery Types
                    </th>
                    <th
                        class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                    >
                        Package Types
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
                    v-for="branch in branches" :key="branch.id"
                    class="border-y border-transparent border-b-slate-200 dark:border-b-navy-500 last:border-0 bg-white"
                >
                    <td
                        class="whitespace-nowrap px-4 py-3 sm:px-5"
                    >
                        {{branch.name}}
                    </td>
                    <td
                        class="whitespace-nowrap px-4 py-3 sm:px-5"
                    >
                        {{branch.type}}
                    </td>
                    <td
                        class="whitespace-nowrap px-4 py-3 sm:px-5"
                    >
                        {{branch.currency_name}}
                    </td>
                    <td
                        class="whitespace-nowrap px-4 py-3 sm:px-5"
                    >
                        {{branch.currency_symbol}}
                    </td>
                    <td
                        class="whitespace-nowrap px-4 py-3 sm:px-5"
                    >
                        <span v-for="cmode in JSON.parse(branch.cargo_modes)" class="badge bg-info/10 text-info dark:bg-info/15 ml-2">
                            {{cmode}}
                        </span>
                    </td>
                    <td
                        class="whitespace-nowrap px-4 py-3 sm:px-5"
                    >
                        <span v-for="dtype in JSON.parse(branch.delivery_types)" class="badge bg-success/10 text-success dark:bg-success/15 ml-2">
                            {{dtype}}
                        </span>
                    </td>
                    <td
                        class="whitespace-nowrap px-4 py-3 sm:px-5"
                    >
                        <span v-for="ptype in JSON.parse(branch.package_types)" class="badge bg-warning/10 text-warning dark:bg-warning/15 ml-2">
                            {{ptype}}
                        </span>
                    </td>
                    <td class="whitespace-nowrap px-4 py-3 sm:px-5">

                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </AppLayout>
</template>
