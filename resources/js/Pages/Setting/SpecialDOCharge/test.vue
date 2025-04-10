<div
    v-if="showAddNewPriceRuleDialog"
    class="fixed px-2 inset-0 z-[100] flex flex-col items-center justify-center overflow-y-auto"
    role="dialog"
>
<div
    class="absolute inset-0 bg-slate-900/60 transition-opacity duration-300"
    x-show="true"
    @click="false"
></div>

<div
    class="relative w-auto sm:w-1/2 h-auto sm:h-1/5 md:h-fit lg:h-fit rounded-lg bg-white transition-opacity duration-300 dark:bg-navy-700"
>
    <div
        class="flex justify-between rounded-t-lg bg-slate-200 px-4 py-3 dark:bg-navy-800 sm:px-5"
    >
        <h3 class="text-base font-medium text-slate-700 dark:text-navy-100">
            {{ editMode ? "Edit Package" : "Add New Price Rule" }}
        </h3>
        <button
            class="btn -mr-1.5 size-7 rounded-full p-0 hover:bg-slate-300/20 focus:bg-slate-300/20 active:bg-slate-300/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25"
            @click="closeAddPriceRuleModal"
        >
            <svg
                class="size-4.5"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                viewBox="0 0 24 24"
                xmlns="http://www.w3.org/2000/svg"
            >
                <path
                    d="M6 18L18 6M6 6l12 12"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                ></path>
            </svg>
        </button>
    </div>
    <div class="px-4 py-4 sm:px-5">
        <div class="mt-4 space-y-4">
            <div class="grid grid-cols-4 gap-4">
                <div class="col-span-4 md:col-span-2">
                    <label class="block">
                                  <span
                                  >Condition
                                    <span class="text-red-500 text-sm">*</span></span
                                  >
                        <input
                            v-model="priceRuleItem.condition"
                            :disabled="ruleList.length <= 0"
                            class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                            placeholder="Type Condition"
                            type="text"
                        />
                    </label>
                </div>
                <div class="col-span-4 md:col-span-2">
                    <label class="block">
                                  <span
                                  >True Action
                                    <span class="text-red-500 text-sm">*</span></span
                                  >
                        <input
                            v-model="priceRuleItem.true_action"
                            class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                            type="text"
                            placeholder="Set True Action"
                        />
                    </label>
                </div>
                <div class="col-span-4 md:col-span-2">
                    <label class="block">
                                  <span
                                  >Bill Price
                                    <span class="text-red-500 text-sm">*</span></span
                                  >
                        <input
                            v-model="priceRuleItem.bill_price"
                            class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                            min="0"
                            placeholder="0.00"
                            type="number"
                        />
                    </label>
                </div>
                <div class="col-span-4 md:col-span-2">
                    <label class="block">
                                  <span
                                  >Bill VAT (%)
                                    <span class="text-red-500 text-sm">*</span></span
                                  >
                        <input
                            v-model="priceRuleItem.bill_vat"
                            class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                            min="0"
                            placeholder="0.00"
                            step="any"
                            type="number"
                        />
                    </label>
                </div>
                <div class="col-span-4 md:col-span-2">
                    <label class="block">
                                  <span
                                  >Volume Charge
                                    <span class="text-red-500 text-sm">*</span></span
                                  >
                        <input
                            v-model="priceRuleItem.volume_charges"
                            class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                            placeholder="Set Volume Charges"
                            type="text"
                        />
                    </label>
                </div>
                <div class="col-span-4 md:col-span-2">
                    <label class="block">
                                  <span
                                  >Per Package Charges
                                    <span class="text-red-500 text-sm">*</span></span
                                  >
                        <input
                            v-model="priceRuleItem.per_package_charges"
                            class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                            placeholder="Set Per Package Charges"
                            type="text"
                        />
                    </label>
                </div>
                <div class="col-span-4 md:col-span-2">
                    <label class="block">
                                  <span
                                  >Is Editable
                                  </span
                                  >
                        <Checkbox class="ml-4" v-model="priceRuleItem.is_editable" :checked="priceRuleItem.is_editable"/>
                    </label>
                </div>
            </div>
            <div class="space-x-2 text-right">
                <SecondaryButton
                    class="min-w-[7rem]"
                    @click="closeAddPriceRuleModal"
                >
                    Cancel
                </SecondaryButton>
                <PrimaryButton
                    class="min-w-[7rem]"
                    type="button"
                    @click="addPriceRuleData"
                >
                    {{ editMode ? "Edit" : "Add" }}
                </PrimaryButton>
            </div>
        </div>
    </div>
</div>
</div>
<script setup lang="ts">
import PrimaryButton from "@/Components/PrimaryButton.vue";
import Checkbox from "@/Components/Checkbox.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
</script>
