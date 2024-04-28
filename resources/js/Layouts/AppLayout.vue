<template>
    <Head :title="title" />
    <div class="flex grow bg-slate-50 dark:bg-navy-900">
        <!-- Sidebar -->
        <div class="sidebar print:hidden">
            <!-- Main Sidebar -->
            <div class="main-sidebar">
                <div
                    class="flex h-full w-full flex-col items-center border-r border-slate-150 bg-white dark:border-navy-700 dark:bg-navy-800"
                >
                    <!-- Application Logo -->
                    <div class="flex pt-4">
                        <a :href="route('dashboard')">
                            <img
                                class="size-11 transition-transform duration-500 ease-in-out hover:rotate-[360deg]"
                                :src="logo"
                                alt="logo"
                            />
                        </a>
                    </div>

                    <!-- Main Sections Links -->
                    <div
                        class="is-scrollbar-hidden flex grow flex-col space-y-4 overflow-y-auto pt-6"
                    >
                        <!-- Dashboard -->
                        <a
                            @click="setMenu('home')"
                            x-tooltip.placement.right="'Dashboard'"
                            :class="[
                                activeMenu === 'home'
                                    ? 'bg-primary/10 text-primary'
                                    : '',
                            ]"
                            class="flex size-11 items-center justify-center rounded-lg outline-none transition-colors duration-200 hover:bg-primary/20 focus:bg-primary/20 active:bg-primary/25 dark:bg-navy-600 dark:text-accent-light dark:hover:bg-navy-450 dark:focus:bg-navy-450 dark:active:bg-navy-450/90"
                        >
                            <svg
                                class="size-7"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    fill="currentColor"
                                    fill-opacity=".3"
                                    d="M5 14.059c0-1.01 0-1.514.222-1.945.221-.43.632-.724 1.453-1.31l4.163-2.974c.56-.4.842-.601 1.162-.601.32 0 .601.2 1.162.601l4.163 2.974c.821.586 1.232.88 1.453 1.31.222.43.222.935.222 1.945V19c0 .943 0 1.414-.293 1.707C18.414 21 17.943 21 17 21H7c-.943 0-1.414 0-1.707-.293C5 20.414 5 19.943 5 19v-4.94Z"
                                ></path>
                                <path
                                    fill="currentColor"
                                    d="M3 12.387c0 .267 0 .4.084.441.084.041.19-.04.4-.204l7.288-5.669c.59-.459.885-.688 1.228-.688.343 0 .638.23 1.228.688l7.288 5.669c.21.163.316.245.4.204.084-.04.084-.174.084-.441v-.409c0-.48 0-.72-.102-.928-.101-.208-.291-.355-.67-.65l-7-5.445c-.59-.459-.885-.688-1.228-.688-.343 0-.638.23-1.228.688l-7 5.445c-.379.295-.569.442-.67.65-.102.208-.102.448-.102.928v.409Z"
                                ></path>
                                <path
                                    fill="currentColor"
                                    d="M11.5 15.5h1A1.5 1.5 0 0 1 14 17v3.5h-4V17a1.5 1.5 0 0 1 1.5-1.5Z"
                                ></path>
                                <path
                                    fill="currentColor"
                                    d="M17.5 5h-1a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5Z"
                                ></path>
                            </svg>
                        </a>
                        <!-- Pickup -->
                        <a
                            @click="setMenu('pickups')"
                            x-tooltip.placement.right="'Pickup'"
                            :class="[
                                activeMenu === 'pickups'
                                    ? 'bg-primary/10 text-primary'
                                    : '',
                            ]"
                            class="flex size-11 items-center justify-center rounded-lg outline-none transition-colors duration-200 hover:bg-primary/20 focus:bg-primary/20 active:bg-primary/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25"
                        >
                            <svg
                                class="size-7"
                                viewBox="0 0 24 24"
                                fill="none"
                                xmlns="http://www.w3.org/2000/svg"
                            >
                                <path
                                    d="M5 8H19V16C19 17.8856 19 18.8284 18.4142 19.4142C17.8284 20 16.8856 20 15 20H9C7.11438 20 6.17157 20 5.58579 19.4142C5 18.8284 5 17.8856 5 16V8Z"
                                    fill="currentColor"
                                    fill-opacity="0.3"
                                ></path>
                                <path
                                    d="M12 8L11.7608 5.84709C11.6123 4.51089 10.4672 3.5 9.12282 3.5V3.5C7.68381 3.5 6.5 4.66655 6.5 6.10555V6.10555C6.5 6.97673 6.93539 7.79026 7.66025 8.2735L9.5 9.5"
                                    stroke="currentColor"
                                    stroke-linecap="round"
                                ></path>
                                <path
                                    d="M12 8L12.2392 5.84709C12.3877 4.51089 13.5328 3.5 14.8772 3.5V3.5C16.3162 3.5 17.5 4.66655 17.5 6.10555V6.10555C17.5 6.97673 17.0646 7.79026 16.3397 8.2735L14.5 9.5"
                                    stroke="currentColor"
                                    stroke-linecap="round"
                                ></path>
                                <rect
                                    x="4"
                                    y="8"
                                    width="16"
                                    height="3"
                                    rx="1"
                                    fill="currentColor"
                                ></rect>
                                <path
                                    d="M12 11V15"
                                    stroke="currentColor"
                                    stroke-linecap="round"
                                ></path>
                            </svg>
                        </a>
                    </div>

                    <!-- Bottom Links -->
                    <div class="flex flex-col items-center space-y-3 py-3">
                        <!-- Settings -->
                        <a
                            href="#"
                            class="flex size-11 items-center justify-center rounded-lg outline-none transition-colors duration-200 hover:bg-primary/20 focus:bg-primary/20 active:bg-primary/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25"
                        >
                            <svg
                                class="size-7"
                                viewBox="0 0 24 24"
                                fill="none"
                                xmlns="http://www.w3.org/2000/svg"
                            >
                                <path
                                    fill-opacity="0.3"
                                    fill="currentColor"
                                    d="M2 12.947v-1.771c0-1.047.85-1.913 1.899-1.913 1.81 0 2.549-1.288 1.64-2.868a1.919 1.919 0 0 1 .699-2.607l1.729-.996c.79-.474 1.81-.192 2.279.603l.11.192c.9 1.58 2.379 1.58 3.288 0l.11-.192c.47-.795 1.49-1.077 2.279-.603l1.73.996a1.92 1.92 0 0 1 .699 2.607c-.91 1.58-.17 2.868 1.639 2.868 1.04 0 1.899.856 1.899 1.912v1.772c0 1.047-.85 1.912-1.9 1.912-1.808 0-2.548 1.288-1.638 2.869.52.915.21 2.083-.7 2.606l-1.729.997c-.79.473-1.81.191-2.279-.604l-.11-.191c-.9-1.58-2.379-1.58-3.288 0l-.11.19c-.47.796-1.49 1.078-2.279.605l-1.73-.997a1.919 1.919 0 0 1-.699-2.606c.91-1.58.17-2.869-1.639-2.869A1.911 1.911 0 0 1 2 12.947Z"
                                ></path>
                                <path
                                    fill="currentColor"
                                    d="M11.995 15.332c1.794 0 3.248-1.464 3.248-3.27 0-1.807-1.454-3.272-3.248-3.272-1.794 0-3.248 1.465-3.248 3.271 0 1.807 1.454 3.271 3.248 3.271Z"
                                ></path>
                            </svg>
                        </a>

                        <!-- Profile -->
                        <div class="flex">
                            <button
                                @click="toggleProfileBar = !toggleProfileBar"
                                class="avatar size-12"
                            >
                                <img
                                    class="rounded-full"
                                    :src="
                                        $page.props.auth.user.profile_photo_url
                                    "
                                    alt="avatar"
                                />
                                <span
                                    class="absolute right-0 size-3.5 rounded-full border-2 border-white bg-success dark:border-navy-700"
                                ></span>
                            </button>

                            <div
                                class="popper-root fixed"
                                :class="[toggleProfileBar ? 'show' : '']"
                                style="
                                    position: fixed;
                                    inset: auto auto 0px 0px;
                                    margin: 0px;
                                    transform: translate(76px, -10px);
                                "
                                data-popper-placement="right-end"
                            >
                                <div
                                    class="popper-box w-64 rounded-lg border border-slate-150 bg-white shadow-soft dark:border-navy-600 dark:bg-navy-700"
                                >
                                    <div
                                        class="flex items-center space-x-4 rounded-t-lg bg-slate-100 py-5 px-4 dark:bg-navy-800"
                                    >
                                        <div class="avatar size-14">
                                            <img
                                                class="rounded-full"
                                                :src="
                                                    $page.props.auth.user
                                                        .profile_photo_url
                                                "
                                                alt="avatar"
                                            />
                                        </div>
                                        <div>
                                            <a
                                                href="#"
                                                class="text-base font-medium text-slate-700 hover:text-primary focus:text-primary dark:text-navy-100 dark:hover:text-accent-light dark:focus:text-accent-light"
                                            >
                                                {{ $page.props.auth.user.name }}
                                            </a>
                                            <p
                                                class="text-xs text-slate-400 dark:text-navy-300"
                                            >
                                                {{
                                                    $page.props.auth.user.email
                                                }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="flex flex-col pt-2 pb-5">
                                        <a
                                            :href="route('profile.show')"
                                            class="group flex items-center space-x-3 py-2 px-4 tracking-wide outline-none transition-all hover:bg-slate-100 focus:bg-slate-100 dark:hover:bg-navy-600 dark:focus:bg-navy-600"
                                        >
                                            <div
                                                class="flex size-8 items-center justify-center rounded-lg bg-warning text-white"
                                            >
                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    class="size-4.5"
                                                    fill="none"
                                                    viewBox="0 0 24 24"
                                                    stroke="currentColor"
                                                    stroke-width="2"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                                                    ></path>
                                                </svg>
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
                                                <button
                                                    class="btn h-9 w-full space-x-2 bg-primary text-white hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:hover:bg-accent-focus dark:focus:bg-accent-focus dark:active:bg-accent/90"
                                                >
                                                    <svg
                                                        xmlns="http://www.w3.org/2000/svg"
                                                        class="size-5"
                                                        fill="none"
                                                        viewBox="0 0 24 24"
                                                        stroke="currentColor"
                                                    >
                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            stroke-width="1.5"
                                                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"
                                                        ></path>
                                                    </svg>
                                                    <span>Logout</span>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar Panel -->
            <div class="sidebar-panel">
                <div
                    class="flex h-full grow flex-col bg-white pl-[var(--main-sidebar-width)] dark:bg-navy-750"
                >
                    <!-- Sidebar Panel Header -->
                    <div
                        class="flex h-18 w-full items-center justify-between pl-4 pr-1"
                    >
                        <p
                            class="text-base tracking-wider text-slate-800 dark:text-navy-100"
                        >
                            <slot name="header" />
                        </p>
                        <button
                            class="btn size-7 rounded-full p-0 text-primary hover:bg-slate-300/20 focus:bg-slate-300/20 active:bg-slate-300/25 dark:text-accent-light/80 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25 xl:hidden"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="size-6"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M15 19l-7-7 7-7"
                                ></path>
                            </svg>
                        </button>
                    </div>

                    <!-- Sidebar Panel Body -->
                    <div
                        class="h-[calc(100%-4.5rem)] overflow-x-hidden pb-6 simplebar-scrollable-y"
                        data-simplebar="init"
                    >
                        <div
                            class="simplebar-wrapper"
                            style="margin: 0px 0px -24px"
                        >
                            <div class="simplebar-height-auto-observer-wrapper">
                                <div
                                    class="simplebar-height-auto-observer"
                                ></div>
                            </div>
                            <div class="simplebar-mask">
                                <div
                                    class="simplebar-offset"
                                    style="right: 0px; bottom: 0px"
                                >
                                    <div
                                        class="simplebar-content-wrapper"
                                        tabindex="0"
                                        role="region"
                                        aria-label="scrollable content"
                                        style="
                                            height: 100%;
                                            overflow: hidden scroll;
                                        "
                                    >
                                        <div
                                            class="simplebar-content"
                                            style="padding: 0px 0px 24px"
                                        >
                                            <ul
                                                class="flex flex-1 flex-col px-4 font-inter"
                                            >
                                                <li
                                                    v-for="item in childMenuList"
                                                >
                                                    <a
                                                        :href="
                                                            route(item.route)
                                                        "
                                                        :class="
                                                            route().current() ===
                                                            item.route
                                                                ? 'font-medium text-primary dark:text-accent-light'
                                                                : 'text-slate-600 hover:text-slate-900 dark:text-navy-200 dark:hover:text-navy-50'
                                                        "
                                                        class="flex py-2 text-xs+ tracking-wide outline-none transition-colors duration-300 ease-in-out font-medium text-primary dark:text-accent-light"
                                                    >
                                                        {{ item.title }}
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div
                                class="simplebar-placeholder"
                                style="width: 240px; height: 686px"
                            ></div>
                        </div>
                        <div
                            class="simplebar-track simplebar-horizontal"
                            style="visibility: hidden"
                        >
                            <div
                                class="simplebar-scrollbar"
                                style="width: 0px; display: none"
                            ></div>
                        </div>
                        <div
                            class="simplebar-track simplebar-vertical"
                            style="visibility: visible"
                        >
                            <div
                                class="simplebar-scrollbar"
                                style="
                                    height: 126px;
                                    display: block;
                                    transform: translate3d(0px, 0px, 0px);
                                "
                            ></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- App Header Wrapper-->
        <nav
            class="header before:bg-white dark:before:bg-navy-750 print:hidden"
        >
            <!-- App Header  -->
            <div
                class="header-container relative flex w-full bg-white dark:bg-navy-750 print:hidden"
            >
                <!-- Header Items -->
                <div class="flex w-full items-center justify-between">
                    <!-- Left: Sidebar Toggle Button -->
                    <div class="size-7">
                        <button
                            @click="toggleSideBar"
                            class="menu-toggle ml-0.5 flex size-7 flex-col justify-center space-y-1.5 text-primary outline-none focus:outline-none dark:text-accent-light/80"
                        >
                            <span></span>
                            <span></span>
                            <span></span>
                        </button>
                    </div>

                    <!-- Right: Header buttons -->
                    <div class="-mr-1.5 flex items-center space-x-2">
                        <!-- Dark Mode Toggle -->
                        <button
                            @click="toggleDarkMode"
                            class="btn size-8 rounded-full p-0 hover:bg-slate-300/20 focus:bg-slate-300/20 active:bg-slate-300/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25"
                        >
                            <svg
                                v-if="isDarkMode"
                                class="size-6 text-amber-400"
                                fill="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    d="M11.75 3.412a.818.818 0 01-.07.917 6.332 6.332 0 00-1.4 3.971c0 3.564 2.98 6.494 6.706 6.494a6.86 6.86 0 002.856-.617.818.818 0 011.1 1.047C19.593 18.614 16.218 21 12.283 21 7.18 21 3 16.973 3 11.956c0-4.563 3.46-8.31 7.925-8.948a.818.818 0 01.826.404z"
                                ></path>
                            </svg>
                            <svg
                                v-else
                                xmlns="http://www.w3.org/2000/svg"
                                x-show="!$store.global.isDarkModeEnabled"
                                x-transition:enter="transition-transform duration-200 ease-out absolute origin-top"
                                x-transition:enter-start="scale-75"
                                x-transition:enter-end="scale-100 static"
                                class="size-6 text-amber-400"
                                viewBox="0 0 20 20"
                                fill="currentColor"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z"
                                    clip-rule="evenodd"
                                ></path>
                            </svg>
                        </button>

                        <!-- Dark Mode Toggle -->
                        <button
                            @click="toggleMonochromeMode"
                            class="btn size-8 rounded-full p-0 hover:bg-slate-300/20 focus:bg-slate-300/20 active:bg-slate-300/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25"
                        >
                            <i
                                class="fa-solid fa-palette bg-gradient-to-r from-sky-400 to-blue-600 bg-clip-text text-lg font-semibold"
                            ></i>
                        </button>

                        <!-- Notification-->
                        <div class="flex">
                            <button
                                class="btn relative size-8 rounded-full p-0 hover:bg-slate-300/20 focus:bg-slate-300/20 active:bg-slate-300/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="size-5 text-slate-500 dark:text-navy-100"
                                    stroke="currentColor"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="1.5"
                                        d="M15.375 17.556h-6.75m6.75 0H21l-1.58-1.562a2.254 2.254 0 01-.67-1.596v-3.51a6.612 6.612 0 00-1.238-3.85 6.744 6.744 0 00-3.262-2.437v-.379c0-.59-.237-1.154-.659-1.571A2.265 2.265 0 0012 2c-.597 0-1.169.234-1.591.65a2.208 2.208 0 00-.659 1.572v.38c-2.621.915-4.5 3.385-4.5 6.287v3.51c0 .598-.24 1.172-.67 1.595L3 17.556h12.375zm0 0v1.11c0 .885-.356 1.733-.989 2.358A3.397 3.397 0 0112 22a3.397 3.397 0 01-2.386-.976 3.313 3.313 0 01-.989-2.357v-1.111h6.75z"
                                    ></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content Wrapper -->
        <main class="main-content w-full h-screen pb-8 p-4">
            <slot />
        </main>
    </div>
</template>
<script>
import {customRef, reactive, ref} from "vue";
import { useMonochromeSelector } from "../composable/monochromeMode.js";
import { useDarkModeSelector } from "../composable/darkMode.js";
import { Head, router } from "@inertiajs/vue3";
import logo from "../../images/app-logo.svg";

export default {
    components: { Head },
    props: {
        title: "",
    },
    setup() {
        const toggleProfileBar = ref(false);

        const monochromeModeSelector = useMonochromeSelector();
        const darkModeSelector = useDarkModeSelector();
        darkModeSelector.setDarkMode();
        monochromeModeSelector.setMonochromeMode();
        const isDarkMode = darkModeSelector.isDarkMode;

        const toggleMonochromeMode = () => {
            monochromeModeSelector.toggleMonochromeMode();
        };

        const toggleDarkMode = () => {
            darkModeSelector.toggleDarkMode();
        };

        const logout = () => {
            router.post(route("logout"));
        };

        const isSidebarExpanded = ref(
            localStorage.getItem("sidebar-expanded") === "true",
        );

        const toggleSideBar = () => {
            isSidebarExpanded.value = !isSidebarExpanded.value;
            localStorage.setItem("sidebar-expanded", isSidebarExpanded.value);
            setSidebarState();
        };

        const setSidebarState = () => {
            if (isSidebarExpanded.value) {
                document.body.classList.add("is-sidebar-open");
            } else {
                document.body.classList.remove("is-sidebar-open");
            }
        };

        setSidebarState();

        const current = route().current();
        const mainRoute = current.split('.')[0];
        const activeMenu = ref(mainRoute);

        const childMenuList = reactive([]);

        const setMenu = (menu) => {
            switch (menu) {
                case "home":
                    childMenuList.splice(
                        0,
                        childMenuList.length,
                        {
                            title: "Dashboard1",
                            route: "dashboard",
                        },
                        {
                            title: "Dashboard2",
                            route: "dashboard2",
                        },
                    );
                    break;
                case "pickups":
                    childMenuList.splice(
                        0,
                        childMenuList.length,
                        {
                            title: "Create Job",
                            route: "pickups.create",
                        },
                        {
                            title: "Pending Jobs",
                            route: "pickups.index",
                        },
                        {
                            title: "Pickup Ordering",
                            route: "dashboard2",
                        },
                        {
                            title: "Job Exception",
                            route: "dashboard2",
                        },
                        {
                            title: "All Jobs",
                            route: "dashboard2",
                        },
                    );
                    break;
            }
            activeMenu.value = menu;
        };

        setMenu(mainRoute);

        return {
            toggleSideBar,
            toggleDarkMode,
            toggleProfileBar,
            toggleMonochromeMode,
            isDarkMode,
            logout,
            logo,
            setMenu,
            childMenuList,
            activeMenu,
        };
    },
};
</script>
