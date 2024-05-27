<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import {Link} from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";

defineProps({
    priceRules: {
        type: Object,
        default: () => {}
    }
})

</script>

<template>
    <AppLayout title="Pricing">
        <template #header>Pricing</template>

        <Breadcrumb/>

        <div class="flex items-center justify-between p-2 my-5">
            <h2 class="text-base font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100">
                Price Rules
            </h2>

            <Link :href="route('setting.prices.create')">
                <PrimaryButton>
                    Create New Price Rule
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
                        Destination
                    </th>
                    <th
                        class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                    >
                        Cargo Mode
                    </th>
                    <th
                        class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                    >
                        Price Mode
                    </th>
                    <th
                        class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                    >
                        Condition
                    </th>
                    <th
                        class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                    >
                        True Action
                    </th>
                    <th
                        class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                    >
                        False Action
                    </th>
                    <th
                        class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                    >
                        Is Editable
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
                    v-for="priceRule in priceRules"
                    v-if="priceRules && Object.keys(priceRules).length > 0" :key="priceRule.id"
                    class="border-y border-transparent border-b-slate-200 dark:border-b-navy-500 last:border-0 bg-white"
                >
                    <td
                        class="whitespace-nowrap px-4 py-3 sm:px-5"
                    >
                        {{priceRule.destination_branch_id}}
                    </td>
                    <td
                        class="whitespace-nowrap px-4 py-3 sm:px-5"
                    >
                        {{priceRule.cargo_mode}}
                    </td>
                    <td
                        class="whitespace-nowrap px-4 py-3 sm:px-5"
                    >
                        {{priceRule.price_mode}}
                    </td>
                    <td
                        class="whitespace-nowrap px-4 py-3 sm:px-5"
                    >
                        {{priceRule.condition}}
                    </td>
                    <td
                        class="whitespace-nowrap px-4 py-3 sm:px-5"
                    >
                        {{priceRule.true_action}}
                    </td>
                    <td
                        class="whitespace-nowrap px-4 py-3 sm:px-5"
                    >
                        {{priceRule.false_action}}
                    </td>
                    <td
                        class="whitespace-nowrap px-4 py-3 sm:px-5"
                    >
                        {{priceRule.is_editable}}
                    </td>
                    <td class="whitespace-nowrap px-4 py-3 sm:px-5">

                    </td>
                </tr>
                <tr
                    v-else
                    class="border-y border-transparent border-b-slate-200 dark:border-b-navy-500 last:border-0 bg-white"
                >
                    <td
                        class="whitespace-nowrap px-4 py-3 sm:px-5"
                        colspan="8"
                    >
                        No Price Rules.
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </AppLayout>
</template>
