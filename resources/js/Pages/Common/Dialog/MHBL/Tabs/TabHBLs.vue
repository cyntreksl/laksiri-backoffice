<script setup>
import Divider from "primevue/divider";
import HBLs from "@/Pages/Common/Dialog/MHBL/Tables/HBLs.vue";

const props = defineProps({
    mhbl: {
        type: Object,
        default: () => ({}),
    },
});

</script>

<template>
    <div
        class="mt-3 flex flex-col items-center justify-between space-y-2 text-center sm:flex-row sm:space-y-0 sm:text-left">
        <div>
            <div class="flex items-center space-x-2 text-xs text-slate-400">
                <div class="flex items-center">
                    <i class="ti ti-plane-departure text-xl mr-2"></i>
                    {{ $page.props.currentBranch.name }}
                </div>
                <div>
                    <i class="ti ti-arrow-narrow-right text-xl"></i>
                </div>
                <div class="flex items-center">
                    <i class="ti ti-plane-arrival text-xl mr-2"></i>
                    {{ mhbl?.warehouse?.name }}
                </div>
            </div>
            <h3 class="text-2xl font-semibold text-slate-700 dark:text-navy-100">
                {{mhbl?.reference}}
            </h3>
        </div>
    </div>

    <Divider />

    <HBLs :mhbl="mhbl"/>
</template>
