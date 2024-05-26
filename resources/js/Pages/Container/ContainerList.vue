<template>
<AppLayout title="Container List">
    <template #header>Container List</template>
    <Breadcrumb></Breadcrumb>
    <div class="flex justify-end mt-5">
        <Link :href="route('loading.loading-containers.create')">
            <PrimaryButton>
                Create New Container
            </PrimaryButton>
        </Link>
    </div>

    <div class="card mt-4">
        <div>
            <div class="flex items-center justify-between p-2">
                <div class="">
                    <div class="flex">
                        <h2 class="text-base font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100">
                            Container List
                        </h2>
                    </div>
                </div>

                <div class="flex">

                </div>
            </div>

            <div class=" mt-3">
                <div class="is-scrollbar-hidden min-w-full overflow-x-auto">
                    <div ref="wrapperRef"></div>
                </div>
            </div>
        </div>
    </div>
</AppLayout>
</template>
<script>
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import {Link} from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import ColumnVisibilityPopover from "@/Components/ColumnVisibilityPopover.vue";
import Checkbox from "@/Components/Checkbox.vue";

export default {
    components: {Checkbox, ColumnVisibilityPopover, PrimaryButton, Link, Breadcrumb, AppLayout},
    setup() {
        return {
            //
        }
    }
}
</script>
<style scoped>

</style>
