<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import { Link, router } from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import DeletePriceRuleConfirmationModal from "@/Pages/Setting/Pricing/Partials/DeletePriceRuleConfirmationModal.vue";
import { ref } from "vue";
import { push } from "notivue";

defineProps({
  priceRules: {
    type: Object,
    default: () => {},
  },
});

const showConfirmDeletePriceRuleModal = ref(false);
const priceRuleId = ref(null);

const confirmDeletePriceRule = (id) => {
  priceRuleId.value = id;
  showConfirmDeletePriceRuleModal.value = true;
};

const closeModal = () => {
  showConfirmDeletePriceRuleModal.value = false;
  priceRuleId.value = null;
};

const handleDeletePriceRule = () => {
  router.delete(route("setting.prices.destroy", priceRuleId.value), {
    preserveScroll: true,
    onSuccess: () => {
      closeModal();
      push.success("Price Rule Deleted Successfully!");
      router.visit(route("setting.prices.index"));
    },
  });
};
</script>

<template>
  <AppLayout title="Pricing">
    <template #header>Pricing</template>

    <Breadcrumb />

    <div class="flex items-center justify-between p-2 my-5">
      <h2
        class="text-base font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100"
      >
        Price Rules
      </h2>

      <Link :href="route('setting.prices.create')">
        <PrimaryButton> Create New Price Rule </PrimaryButton>
      </Link>
    </div>

    <div class="min-w-full overflow-auto">
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
              Bill Price
            </th>
            <th
              class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
            >
              Bill VAT (%)
            </th>
            <th
              class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
            >
              Destination Charges
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
            v-if="priceRules && Object.keys(priceRules).length > 0"
            :key="priceRule.id"
            class="border-y border-transparent border-b-slate-200 dark:border-b-navy-500"
          >
            <td class="whitespace-nowrap px-4 py-3 sm:px-5">
              {{ priceRule.destination_branch_name }}
            </td>
            <td class="whitespace-nowrap px-4 py-3 sm:px-5">
              {{ priceRule.cargo_mode }}
            </td>
            <td class="whitespace-nowrap px-4 py-3 sm:px-5">
              {{ priceRule.price_mode }}
            </td>
            <td class="whitespace-nowrap px-4 py-3 sm:px-5">
              {{ priceRule.condition }}
            </td>
            <td class="whitespace-nowrap px-4 py-3 sm:px-5">
              {{ priceRule.true_action }}
            </td>
            <td class="whitespace-nowrap px-4 py-3 sm:px-5">
              {{ priceRule.false_action }}
            </td>
            <td class="whitespace-nowrap px-4 py-3 sm:px-5">
              {{ priceRule.bill_price }}
            </td>
            <td class="whitespace-nowrap px-4 py-3 sm:px-5">
              {{ priceRule.bill_vat }}
            </td>
            <td class="whitespace-nowrap px-4 py-3 sm:px-5">
              {{ priceRule.per_package_charges ? parseFloat(priceRule.per_package_charges) + parseFloat(priceRule.volume_charges) : null }}
            </td>
            <td class="whitespace-nowrap px-4 py-3 sm:px-5">
              <svg
                v-if="priceRule.is_editable"
                class="size-6 text-success"
                fill="none"
                stroke="currentColor"
                stroke-width="1.5"
                viewBox="0 0 24 24"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path
                  d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                />
              </svg>
              <svg
                v-else
                class="size-6 text-error"
                fill="none"
                stroke="currentColor"
                stroke-width="1.5"
                viewBox="0 0 24 24"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path
                  d="M6 18 18 6M6 6l12 12"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                />
              </svg>
            </td>
            <td class="whitespace-nowrap px-4 py-3 sm:px-5">
              <div class="flex space-x-2">
                <Link
                  :href="route('setting.prices.edit', priceRule.id)"
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
                <button
                  class="btn size-8 p-0 text-error hover:bg-error/20 focus:bg-error/20 active:bg-error/25"
                  @click="confirmDeletePriceRule(priceRule.id)"
                >
                  <svg
                    class="size-4.5"
                    fill="none"
                    viewBox="0 0 448 512"
                    xmlns="http://www.w3.org/2000/svg"
                  >
                    <path
                      d="M170.5 51.6L151.5 80h145l-19-28.4c-1.5-2.2-4-3.6-6.7-3.6H177.1c-2.7 0-5.2 1.3-6.7 3.6zm147-26.6L354.2 80H368h48 8c13.3 0 24 10.7 24 24s-10.7 24-24 24h-8V432c0 44.2-35.8 80-80 80H112c-44.2 0-80-35.8-80-80V128H24c-13.3 0-24-10.7-24-24S10.7 80 24 80h8H80 93.8l36.7-55.1C140.9 9.4 158.4 0 177.1 0h93.7c18.7 0 36.2 9.4 46.6 24.9zM80 128V432c0 17.7 14.3 32 32 32H336c17.7 0 32-14.3 32-32V128H80zm80 64V400c0 8.8-7.2 16-16 16s-16-7.2-16-16V192c0-8.8 7.2-16 16-16s16 7.2 16 16zm80 0V400c0 8.8-7.2 16-16 16s-16-7.2-16-16V192c0-8.8 7.2-16 16-16s16 7.2 16 16zm80 0V400c0 8.8-7.2 16-16 16s-16-7.2-16-16V192c0-8.8 7.2-16 16-16s16 7.2 16 16z"
                      fill="currentColor"
                    ></path>
                  </svg>
                </button>
              </div>
            </td>
          </tr>
          <tr
            v-else
            class="border-y border-transparent border-b-slate-200 dark:border-b-navy-500 last:border-0 bg-white"
          >
            <td class="whitespace-nowrap px-4 py-3 sm:px-5" colspan="8">
              No Price Rules.
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <DeletePriceRuleConfirmationModal
      :show="showConfirmDeletePriceRuleModal"
      @close="closeModal"
      @delete-price-rule="handleDeletePriceRule"
    />
  </AppLayout>
</template>
