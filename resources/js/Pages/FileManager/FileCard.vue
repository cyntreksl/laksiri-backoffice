<script setup>
import {Menu, MenuButton, MenuItems, MenuItem} from '@headlessui/vue'
import {router} from "@inertiajs/vue3";

defineProps({
    file: {
        type: Object,
        default: () => {
        }
    }
})

const handleDownloadFile = (id) => {
    router.get(route('file-manager.download.single', id))
}
</script>

<template>
    <div class="card swiper-slide w-56 shrink-0 p-3 pt-4 ">
        <div class="flex items-center justify-between">
            <svg class="size-14 text-yellow-500" fill="currentColor"
                 viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                <path
                    d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"/>
            </svg>
            <Menu as="div" class="relative inline-block text-left">
                <div>
                    <MenuButton
                        class="btn size-8 rounded-full p-0 hover:bg-slate-300/20 focus:bg-slate-300/20 active:bg-slate-300/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25">
                        <svg
                            class="size-5"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <path
                                d="M5 12h.01M12 12h.01M19 12h.01M6 12a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0z"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            ></path>
                        </svg>
                    </MenuButton>
                </div>

                <transition enter-active-class="transition ease-out duration-100"
                            enter-from-class="transform opacity-0 scale-95"
                            enter-to-class="transform opacity-100 scale-100"
                            leave-active-class="transition ease-in duration-75"
                            leave-from-class="transform opacity-100 scale-100"
                            leave-to-class="transform opacity-0 scale-95">
                    <MenuItems
                        class="absolute right-0 z-50 mt-2 w-56 origin-top-right rounded-md bg-white shadow-lg ring-1 ring-gray-300 focus:outline-none">
                        <div class="py-1">
                            <MenuItem v-slot="{ active }" class="flex items-center">
                                <div :class="[active ? 'bg-gray-100 text-gray-900' : 'text-gray-700', 'cursor-pointer block px-4 py-2 text-sm']"
                                   @click.prevent="handleDownloadFile(file.id)">
                                    <svg class="icon icon-tabler icons-tabler-outline icon-tabler-download mr-2" fill="none" height="18" stroke="currentColor"
                                         stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24"
                                         width="18"
                                         xmlns="http://www.w3.org/2000/svg">
                                        <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                                        <path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2"/>
                                        <path d="M7 11l5 5l5 -5"/>
                                        <path d="M12 4l0 12"/>
                                    </svg>
                                    Download
                                </div>
                            </MenuItem>
                            <form action="#" method="POST">
                                <MenuItem v-slot="{ active }" class="flex items-center">
                                    <button :class="[active ? 'bg-red-100 text-red-600' : 'text-red-700', 'block w-full px-4 py-2 text-left text-sm']"
                                            type="submit">
                                        <svg class="icon icon-tabler icons-tabler-outline icon-tabler-trash-x mr-2" fill="none" height="18"
                                             stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                             viewBox="0 0 24 24" width="18"
                                             xmlns="http://www.w3.org/2000/svg">
                                            <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                                            <path d="M4 7h16"/>
                                            <path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12"/>
                                            <path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3"/>
                                            <path d="M10 12l4 4m0 -4l-4 4"/>
                                        </svg>
                                        Delete
                                    </button>
                                </MenuItem>
                            </form>
                        </div>
                    </MenuItems>
                </transition>
            </Menu>
        </div>
        <div class="pt-5 text-base font-medium tracking-wide text-primary dark:text-accent-light">
            {{ file.name }}
        </div>
        <div class="mt-1.5 flex items-center justify-between">
            <p class="text-salte-400 text-xs+ dark:text-navy-300">
                {{ file.type }}
            </p>
            <p class="font-medium text-slate-600 dark:text-navy-100">
                {{ file.size }}
            </p>
        </div>
    </div>
</template>
