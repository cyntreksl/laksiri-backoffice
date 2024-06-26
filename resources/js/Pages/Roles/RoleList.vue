<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";

const props = defineProps({
  roles: {
    type: Object,
    default: () => {},
  },
});
</script>

<template>
  <AppLayout title="Roles">
    <template #header>Roles</template>

    <Breadcrumb />

    <div class="card mt-4">
        <div class="flex items-center justify-between p-2">
            <h2
                class="text-base font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100"
            >
                Roles
            </h2>

            <div class="mt-3">
                <div class="is-scrollbar-hidden min-w-full overflow-x-auto">
                    <div ref="wrapperRef"></div>
                </div>
            </div>
        </div>
    </div>
  </AppLayout>
</template>
