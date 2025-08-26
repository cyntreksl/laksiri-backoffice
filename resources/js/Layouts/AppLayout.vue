<script setup>
import {Head} from "@inertiajs/vue3";
import SidebarNavigation from "@/Layouts/components/SidebarNavigation.vue";
import {Notification, Notivue} from "notivue";
import AppHeader from "@/Layouts/components/AppHeader.vue";

const props = defineProps({
    title: "",
})
</script>

<template xmlns="http://www.w3.org/1999/html">
    <Head :title="title"/>
    <div class="flex grow bg-slate-50 dark:bg-navy-900">
        <!-- Sidebar -->
        <SidebarNavigation />

        <!-- App Header Wrapper-->
        <AppHeader />

        <!-- Main Content Wrapper -->
        <main
            class="main-content w-full h-full pb-8 p-4"
            style="height: 100vh !important"
        >
            <ConfirmDialog></ConfirmDialog>
            <Notivue v-slot="item">
                <Notification :item="item"/>
            </Notivue>
            <slot/>
        </main>
    </div>
</template>

<style scoped>
a {
    font-size: 15px;
}
</style>
