<template>
    <button
        class="btn -mr-1.5 size-7 rounded-full p-0 hover:bg-red-500/20 focus:bg-slate-300/20 active:bg-slate-300/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25"
        x-tooltip.placement.bottom.error="'Close filter drawer'">
        <svg class="size-4.5" fill="none" stroke="currentColor" stroke-width="2"
             viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path d="M6 18L18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round"></path>
        </svg>
    </button>
</template>
