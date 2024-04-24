<template>
    <AppLayout title="Pick Up Job">
        <template #header>Pick Up Job - Create</template>

        <div class="card px-4 py-4 sm:px-5">
            <ul class="flex flex-wrap items-center space-x-2">
                <li class="flex items-center space-x-2">
                    <a class="text-primary transition-colors hover:text-primary-focus dark:text-accent-light dark:hover:text-accent"
                        href="#">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="size-4.5"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.5"
                                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"
                            />
                        </svg>
                    </a>
                    <svg
                        x-ignore
                        xmlns="http://www.w3.org/2000/svg"
                        class="size-3.5"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M9 5l7 7-7 7"
                        />
                    </svg>
                </li>
                <li class="flex items-center space-x-2">
                    <a class="flex items-center space-x-1.5 text-primary transition-colors hover:text-primary-focus dark:text-accent-light dark:hover:text-accent"
                        href="#">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="size-4.5"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.5"
                                d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"
                            ></path>
                        </svg>
                        <span>Pick Up</span>
                    </a>
                    <svg
                        x-ignore
                        xmlns="http://www.w3.org/2000/svg"
                        class="size-3.5"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M9 5l7 7-7 7"
                        />
                    </svg>
                </li>
                <li>
                    <div class="flex items-center space-x-1.5">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="size-4.5 text-slate-400 dark:text-navy-300"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.5"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                            />
                        </svg>
                        <span>Create</span>
                    </div>
                </li>
            </ul>
        </div>

        <div class="grid grid-cols-5 mt-4 gap-4">
            <div class="col-span-3">
                <div class="card px-4 py-4 sm:px-5">
                    <div>
                        <h2 class="text-lg font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100">
                            Basic Details
                        </h2>
                    </div>
                    <div class="pt-2">

                        <label class="block">
                            <span>Choose Username:</span>
                            <input
                                class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                placeholder="Username"
                                type="text"
                            />
                        </label>

                    </div>
                </div>
            </div>
            <div class="col-span-2">
                <div class="card px-4 py-4 sm:px-5">
                    <div>
                        <h2 class="text-lg font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100">
                            Simple Card
                        </h2>
                    </div>
                    <div class="pt-2">
                        <p>
                            Lorem ipsum dolor sit, amet consectetur adipisicing elit. Aut
                            laboriosam praesentium adipisci iure doloribus accusamus animi
                            quos.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script>
import AppLayout from "@/Layouts/AppLayout.vue";

export default {
    components: {AppLayout},
    setup() {
        return {}
    }
}
</script>
