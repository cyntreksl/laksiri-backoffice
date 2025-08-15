<script setup>
import {ref} from "vue";
import {router, usePage} from "@inertiajs/vue3";
import {useMonochromeSelector} from "@/composable/monochromeMode.js";
import {useDarkModeSelector} from "@/composable/darkMode.js";
import Select from 'primevue/select';
import Popover from 'primevue/popover';
import Avatar from 'primevue/avatar';
import Button from 'primevue/button';

const props = defineProps({
    isSidebarExpanded: {
        type: Boolean,
        default: false,
    }
})

const emit = defineEmits(["update:isSidebarExpanded"]);

const page = usePage();
const monochromeModeSelector = useMonochromeSelector();
const darkModeSelector = useDarkModeSelector();
darkModeSelector.setDarkMode();
monochromeModeSelector.setMonochromeMode();
const op = ref();

const userBranches = page.props.userBranch;
const isDarkMode = darkModeSelector.isDarkMode;

const logout = () => {
    router.post(route("logout"));
};

const toggleMonochromeMode = () => {
    monochromeModeSelector.toggleMonochromeMode();
};

const toggleDarkMode = () => {
    darkModeSelector.toggleDarkMode();
};

const toggleSideBar = () => {
    const newValue = !props.isSidebarExpanded;
    emit("update:isSidebarExpanded", newValue);
    localStorage.setItem("sidebar-expanded", newValue);
    setSidebarState(newValue);
};

const setSidebarState = (expanded) => {
    if (expanded) {
        document.body.classList.add("is-sidebar-open");
    } else {
        document.body.classList.remove("is-sidebar-open");
    }
};

const toggle = (event) => {
    op.value.toggle(event);
}

const setBranch = (branch) => {

    fetch("/switch-branch", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": usePage().props.csrf,
        },
        body: JSON.stringify({branch_name: branch}),
    })
        .then((response) => {
            if (!response.ok) {
                throw new Error("Failed to switch branch");
            }
            return response.json();
        })
        .then((data) => {
            if (route().current("branches.edit")) {
                router.visit(route("branches.edit", data.branchId), {
                    replace: true,
                });
            } else {
                window.location.reload();
            }
        })
        .catch((error) => {
            console.error("Error:", error);
        });
};
</script>

<template>
    <nav class="header before:bg-white dark:before:bg-navy-750 print:hidden">
        <!-- App Header  -->
        <div
            class="header-container relative flex w-full bg-white dark:bg-navy-750 print:hidden"
        >
            <!-- Header Items -->
            <div class="flex w-full items-center justify-between">
                <!-- Left: Sidebar Toggle Button -->
                <div>
                    <Button v-if="!isSidebarExpanded" icon="pi pi-bars" rounded severity="contrast" variant="text" @click="toggleSideBar" />
                    <Button v-else icon="pi pi-chevron-left" rounded severity="contrast" variant="text" @click="toggleSideBar" />
                </div>

                <!-- Right: Header buttons -->
                <div class="-mr-1.5 flex items-center space-x-2">
                    <!-- Dark Mode Toggle -->
                    <button
                        class="btn size-8 rounded-full p-0 hover:bg-slate-300/20 focus:bg-slate-300/20 active:bg-slate-300/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25"
                        @click="toggleDarkMode"
                    >
                        <i v-if="isDarkMode" class="pi pi-moon text-amber-500"></i>
                        <i v-else class="pi pi-sun text-emerald-500"></i>
                    </button>

                    <!-- Dark Mode Toggle -->
                    <button
                        class="btn size-8 rounded-full p-0 hover:bg-slate-300/20 focus:bg-slate-300/20 active:bg-slate-300/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25"
                        @click="toggleMonochromeMode"
                    >
                        <i
                            class="fa-solid fa-palette bg-gradient-to-r from-sky-400 to-blue-600 bg-clip-text text-lg font-semibold"
                        ></i>
                    </button>

                    <!-- Branch-->
                    <Select v-if="userBranches.length > 0" :options="userBranches" size="small" @change="setBranch($event.value)">
                        <template #value>
                            {{ $page.props.auth.user.active_branch_name }}
                        </template>
                    </Select>

                    <!-- Profile -->
                    <div class="flex">
                        <Avatar :image="$page.props.auth.user.profile_photo_url" class="hover:cursor-pointer" shape="circle" size="large" @click="toggle"/>

                        <Popover ref="op">
                            <div
                                class="flex items-center space-x-4 rounded-lg bg-slate-100 py-5 px-4 dark:bg-navy-800"
                            >
                                <Avatar :image="$page.props.auth.user.profile_photo_url" class="hover:cursor-pointer" shape="circle" size="large" @click="toggle"/>
                                <div>
                                    <a
                                        class="text-base font-medium text-slate-700 hover:text-primary focus:text-primary dark:text-navy-100 dark:hover:text-accent-light dark:focus:text-accent-light"
                                        href="#"
                                    >
                                        {{ $page.props.auth.user.name }}
                                    </a>
                                    <p class="text-xs text-slate-400 dark:text-navy-300">
                                        {{ $page.props.auth.user.email }}
                                    </p>
                                    <p class="text-xs text-slate-400 dark:text-navy-300">
                                        {{ $page.props.auth.user.active_branch_name }}
                                    </p>
                                </div>
                            </div>

                            <div class="flex flex-col pt-2">
                                <template v-if="usePage().props.auth.user.roles[0].name !== 'customer'">
                                    <a
                                        v-if="! $page.props.user.roles.includes('viewer')"
                                        :href="
                        route(
                          'branches.edit',
                          $page.props.auth.user.active_branch_id
                        )
                      "
                                        class="group flex items-center space-x-3 py-2 px-4 tracking-wide outline-none transition-all hover:bg-slate-100 focus:bg-slate-100 dark:hover:bg-navy-600 dark:focus:bg-navy-600"
                                    >
                                        <div
                                            class="flex size-8 items-center justify-center rounded-lg bg-pink-500 text-white"
                                        >
                                            <i class="ti ti-tool text-2xl"></i>
                                        </div>

                                        <div>
                                            <h2
                                                class="font-medium text-slate-700 transition-colors group-hover:text-primary group-focus:text-primary dark:text-navy-100 dark:group-hover:text-accent-light dark:group-focus:text-accent-light"
                                            >
                                                Preferences
                                            </h2>
                                            <div
                                                class="text-xs text-slate-400 line-clamp-1 dark:text-navy-300"
                                            >
                                                Branch Preferences
                                            </div>
                                        </div>
                                    </a>
                                </template>

                                <a
                                    :href="route('profile.show')"
                                    class="group flex items-center space-x-3 py-2 px-4 tracking-wide outline-none transition-all hover:bg-slate-100 focus:bg-slate-100 dark:hover:bg-navy-600 dark:focus:bg-navy-600"
                                >
                                    <div
                                        class="flex size-8 items-center justify-center rounded-lg bg-warning text-white"
                                    >
                                        <i class="ti ti-user-circle text-2xl"></i>
                                    </div>

                                    <div>
                                        <h2
                                            class="font-medium text-slate-700 transition-colors group-hover:text-primary group-focus:text-primary dark:text-navy-100 dark:group-hover:text-accent-light dark:group-focus:text-accent-light"
                                        >
                                            Profile
                                        </h2>
                                        <div
                                            class="text-xs text-slate-400 line-clamp-1 dark:text-navy-300"
                                        >
                                            Your profile setting
                                        </div>
                                    </div>
                                </a>

                                <div class="mt-3 px-4">
                                    <form @submit.prevent="logout">
                                        <Button class="w-full" icon="pi pi-power-off" label="Logout" type="submit" />
                                    </form>
                                </div>
                            </div>
                        </Popover>
                    </div>
                </div>
            </div>
        </div>
    </nav>
</template>

<style scoped>

</style>
