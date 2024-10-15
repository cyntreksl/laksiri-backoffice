<script setup>
defineProps({
    file: {
        type: Object,
        default: () => {}
    }
})
</script>

<template>
    <div class="card swiper-slide w-56 shrink-0 p-3 pt-4 ">
        <div class="flex items-center justify-between">
            <svg class="size-14 text-yellow-500" fill="currentColor"
                 viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                <path
                    d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"/>
            </svg>
            <button
                class="btn -mr-2 size-8 rounded-full p-0 hover:bg-slate-300/20 focus:bg-slate-300/20 active:bg-slate-300/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25">
                <svg class="size-5" fill="none" stroke="currentColor"
                     stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" stroke-linecap="round"
                          stroke-linejoin="round"/>
                </svg>
            </button>
        </div>
        <div class="pt-5 text-base font-medium tracking-wide text-primary dark:text-accent-light">
            {{file.name}}
        </div>
        <div class="mt-1.5 flex items-center justify-between">
            <p class="text-salte-400 text-xs+ dark:text-navy-300">
                {{file.type}}
            </p>
            <p class="font-medium text-slate-600 dark:text-navy-100">
                {{file.size}}
            </p>
        </div>
    </div>
</template>
