<script setup>
import {computed} from "vue";
import {usePage, Link} from "@inertiajs/vue3";

const insertBetween = (items, insertion) => {
    return items.flatMap(
        (value, index, array) =>
            array.length - 1 !== index
                ? [value, insertion]
                : value,
    )
}

const breadcrumbs = computed(() => insertBetween(usePage().props.breadcrumbs || [], '/'))
</script>

<template>
    <div class="card px-4 py-4 sm:px-5">
        <ul v-if="breadcrumbs" class="flex flex-wrap items-center space-x-2">
            <li v-for="(page, index) in breadcrumbs" :key="index" class="flex items-center space-x-2">
                <svg
                    v-if="page === '/'"
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
                <template v-else>
                    <div v-if="!page.current">
                        <Link
                            v-if="page.title === 'Home'"
                            class="text-primary transition-colors hover:text-primary-focus dark:text-accent-light dark:hover:text-accent"
                            :href="page.url"
                        >
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
                        </Link>
                        <Link
                            v-else
                            class="flex items-center text-primary space-x-1.5 transition-colors hover:text-primary-focus dark:text-accent-light dark:hover:text-accent"
                            :href="page.url ? page.url : ''"
                        >
                            <span>{{ page.title }}</span>
                        </Link>
                    </div>
                    <div v-else class="flex">
                        <span>{{ page.title }}</span>
                    </div>
                </template>
            </li>
        </ul>
    </div>
</template>
