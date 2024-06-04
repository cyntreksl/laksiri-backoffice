<script setup>
import {provide, ref} from "vue";

const tabs = ref([]);
const activeTab = ref(null);

const addTab = (tab) => {
    tabs.value.push(tab);
    if (!activeTab.value) {
        selectTab(tab);
    }
};

const selectTab = (tab) => {
    activeTab.value = tab.name;
};

provide('addTab', addTab);
provide('selectTab', selectTab);
provide('activeTab', activeTab);
</script>

<template>
    <div class="tabs flex flex-col">
        <div
            class="is-scrollbar-hidden overflow-x-auto rounded-lg bg-slate-200 text-slate-600 dark:bg-navy-800 dark:text-navy-200"
        >
            <div class="tabs-list flex px-1.5 py-1">
                <button
                    v-for="(tab, index) in tabs"
                    :key="index"
                    :class="activeTab === tab.name ? 'bg-white shadow dark:bg-navy-500 dark:text-navy-100' : 'hover:text-slate-800 focus:text-slate-800 dark:hover:text-navy-100 dark:focus:text-navy-100'"
                    class="btn shrink-0 space-x-2 px-3 py-1.5 font-medium"
                    @click="selectTab(tab)"
                >
                    <slot :tab="tab" name="icon"/>
                    <span> {{ tab.label}} </span>
                </button>
            </div>
        </div>
        <div class="tab-content pt-4">
            <slot />
        </div>
    </div>
</template>
