<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";

defineProps({
    container: {
        type: Object,
        default: () => {}
    },
    hbls: {
        type: Object,
        default: () => {}
    }
})

</script>

<template>
    <AppLayout title="Loading Points">
        <template #header>Loading Points</template>

        <main class="kanban-app w-full">
            <div
                class="flex items-center justify-between space-x-2 px-[var(--margin-x)] py-5 transition-all duration-[.25s]">
                <div class="flex items-center space-x-1">
                    <h3 class="text-lg font-medium text-slate-700 line-clamp-1 dark:text-navy-50">
                        Loading Point
                    </h3>
                </div>
                <div class="flex space-x-1">
                    <button
                        class="btn size-6 rounded-full p-0 font-medium text-info hover:bg-info/20 focus:bg-info/20 active:bg-info/25 sm:h-8 sm:w-8">
                        <svg class="size-4 sm:h-5 sm:w-5" fill="currentColor" viewBox="0 0 20 20"
                             xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M8 9a3 3 0 100-6 3 3 0 000 6zM8 11a6 6 0 016 6H2a6 6 0 016-6zM16 7a1 1 0 10-2 0v1h-1a1 1 0 100 2h1v1a1 1 0 102 0v-1h1a1 1 0 100-2h-1V7z" />
                        </svg>
                    </button>
                    <div class="my-1 w-px bg-slate-200 dark:bg-navy-500"></div>
                    <div class="flex">
                        <button
                            class="btn size-6 rounded-full p-0 hover:bg-slate-300/20 focus:bg-slate-300/20 active:bg-slate-300/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25 sm:hidden sm:h-8 sm:w-8">
                            <svg class="size-4 sm:h-5 sm:w-5" fill="none" stroke="currentColor"
                                 stroke-width="1.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke-linecap="round"
                                      stroke-linejoin="round" />
                            </svg>
                        </button>

                        <button
                            class="btn size-6 rounded-full p-0 hover:bg-slate-300/20 focus:bg-slate-300/20 active:bg-slate-300/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25 sm:h-8 sm:w-8">
                            <svg class="size-4 sm:h-5 sm:w-5" fill="none" stroke="currentColor"
                                 stroke-width="1.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" stroke-linecap="round"
                                      stroke-linejoin="round" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <div class="flex h-[calc(100vh-8.5rem)] flex-grow flex-col">
                <div class="kanban-scrollbar grid grid-cols-2 space-x-4 overflow-x-auto overflow-y-hidden px-[var(--margin-x)] transition-all duration-[.25s]">
                    <div class="board-draggable relative flex max-h-full shrink-0 flex-col">
                        <div class="board-draggable-handler flex items-center justify-between px-0.5 pb-3">
                            <div class="flex items-center space-x-2">
                                <div class="flex size-8 items-center justify-center rounded-lg bg-info/10 text-info">
                                    <i class="fa fa-boxes-packing text-base"></i>
                                </div>
                                <h3 class="text-base text-slate-700 dark:text-navy-100">
                                    HBL
                                </h3>
                            </div>
                        </div>
                        <div class="is-scrollbar-hidden relative space-y-2.5 overflow-y-auto p-0.5">
                            <div v-for="hbl in hbls" :key="hbl.id" class="card cursor-pointer shadow-sm">
                                <div class="space-y-3 rounded-lg bg-success/10 px-2.5 pb-2 pt-1.5">
                                    <div>
                                        <div class="flex justify-between">
                                            <p class="font-medium tracking-wide text-success line-clamp-2">
                                                Improve animation loader
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex flex-wrap space-x-1">
                                        <div
                                            class="badge space-x-1 bg-success/10 py-1 px-1.5 text-success dark:bg-success/15">
                                            <svg class="size-3.5" fill="none" stroke="currentColor"
                                                 viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" stroke-linecap="round" stroke-linejoin="round"
                                                      stroke-width="2" />
                                            </svg>
                                            <span> Sep 12</span>
                                        </div>
                                        <div class="badge bg-warning/10 py-1 px-1.5 text-warning dark:bg-warning/15">
                                            Performance
                                        </div>
                                    </div>
                                    <div class="flex items-end justify-between">
                                        <div class="flex items-center space-x-2">
                                            <div class="avatar size-6">
                                                <img alt="avatar" class="rounded-full"
                                                     src="{{asset('images/100x100.png')}}" />
                                            </div>
                                            <p>Travis F.</p>
                                        </div>

                                        <div class="flex items-center space-x-2 text-xs text-slate-400 dark:text-navy-300">
                                            <div class="flex items-center space-x-0.5">
                                                <svg class="size-3.5" fill="none"
                                                     stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" stroke-linecap="round" stroke-linejoin="round"
                                                          stroke-width="2" />
                                                </svg>
                                                <span>4</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="board-draggable relative flex max-h-full shrink-0 flex-col">
                        <div class="board-draggable-handler flex items-center justify-between px-0.5 pb-3">
                            <div class="flex items-center space-x-2">
                                <div class="flex size-8 items-center justify-center rounded-lg bg-warning/10 text-warning">
                                    <i class="fa fa-boxes-alt text-base"></i>
                                </div>
                                <h3 class="text-base text-slate-700 dark:text-navy-100">
                                    {{container.cargo_type}} Container
                                </h3>
                            </div>
                            <div>
                                <h3 class="text-base text-slate-700 dark:text-navy-100">
                                    {{container.container_type}}
                                </h3>
                            </div>
                        </div>
                        <div class="is-scrollbar-hidden relative space-y-2.5 overflow-y-auto p-0.5">
                            <div class="card cursor-pointer shadow-sm">
                                <div class="flex space-x-3 px-2.5 pb-2 pt-1.5">
                                    <div class="w-10 shrink-0 py-1">
                                        <img alt="image" class="w-full" src="{{asset('images/illustrations/invite-user.svg')}}" />
                                    </div>
                                    <div class="flex-1 space-y-2">
                                        <div>
                                            <div class="flex justify-between">
                                                <p
                                                    class="font-medium tracking-wide text-slate-600 line-clamp-2 dark:text-navy-100">
                                                    Invite New Users
                                                </p>
                                                <svg class="-mr-1.5 size-3.5 shrink-0 text-success"
                                                     fill="currentColor" viewBox="0 0 20 20"
                                                     xmlns="http://www.w3.org/2000/svg">
                                                    <path clip-rule="evenodd"
                                                          d="M3 6a3 3 0 013-3h10a1 1 0 01.8 1.6L14.25 8l2.55 3.4A1 1 0 0116 13H6a1 1 0 00-1 1v3a1 1 0 11-2 0V6z"
                                                          fill-rule="evenodd" />
                                                </svg>
                                            </div>

                                            <p class="mt-px text-xs text-slate-400 dark:text-navy-300">
                                                Invite new users to chatroom
                                            </p>
                                        </div>
                                        <div class="flex flex-wrap space-x-1">
                                            <div
                                                class="badge space-x-1 bg-slate-150 py-1 px-1.5 text-slate-800 dark:bg-navy-500 dark:text-navy-100">
                                                <svg class="size-3.5" fill="none"
                                                     stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" stroke-linecap="round" stroke-linejoin="round"
                                                          stroke-width="2" />
                                                </svg>
                                                <span> Daily</span>
                                            </div>

                                            <div
                                                class="badge space-x-1 bg-warning/10 py-1 px-1.5 text-warning dark:bg-warning/15">
                                                <svg class="size-3.5" fill="none"
                                                     stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round"
                                                          stroke-width="2" />
                                                </svg>
                                                <span>7/10</span>
                                            </div>
                                        </div>
                                        <div class="flex items-end justify-between pt-1">
                                            <div class="flex items-center space-x-2">
                                                <div class="avatar size-6">
                                                    <img alt="avatar" class="rounded-full"
                                                         src="{{asset('images/100x100.png')}}" />
                                                </div>
                                                <p>Raul B.</p>
                                            </div>

                                            <div
                                                class="flex items-center space-x-2 text-xs text-slate-400 dark:text-navy-300">
                                                <div class="flex items-center space-x-0.5">
                                                    <svg class="size-3.5" fill="none"
                                                         stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" stroke-linecap="round"
                                                              stroke-linejoin="round"
                                                              stroke-width="2" />
                                                    </svg>
                                                    <span>2</span>
                                                </div>
                                                <div class="flex items-center space-x-0.5">
                                                    <svg class="size-3.5" fill="none"
                                                         stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" stroke-linecap="round"
                                                              stroke-linejoin="round"
                                                              stroke-width="2" />
                                                    </svg>
                                                    <span>4</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </AppLayout>
</template>
