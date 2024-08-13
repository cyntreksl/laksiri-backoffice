<script setup>
import {ref} from "vue";
import {Link} from "@inertiajs/vue3";
import DestinationAppLayout from "@/Layouts/DestinationAppLayout.vue";

const documentVerificationQueue = ref([]);
const cashierQueue = ref([]);
const examinationQueue = ref([]);

const getDocumentVerificationQueue = async () => {
    try {
        const response = await fetch(`/call-center/get-document-verification-queue`, {
            method: "GET",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content")
            },
        });

        if (!response.ok) {
            throw new Error('Network response was not ok.');
        } else {
            documentVerificationQueue.value = await response.json();
        }

    } catch (error) {
        console.log(error);
    }
}

getDocumentVerificationQueue()

setInterval(getDocumentVerificationQueue, 3000);
</script>

<template>
    <DestinationAppLayout title="Queue List">
        <template #header>Queue List</template>

        <main class="w-full mt-10">
            <div class="flex h-[calc(100vh-8.5rem)] flex-grow flex-col">
                <div class="flex w-full items-start space-x-4 px-[var(--margin-x)] transition-all duration-[.25s]"
                     x-init="Sortable.create($el, {
                animation: 200,
                easing: 'cubic-bezier(0, 0, 0.2, 1)',
                delay: 150,
                delayOnTouchOnly: true,
                draggable: '.board-draggable',
                handle: '.board-draggable-handler'
            })">
                    <div class="relative flex max-h-full w-1/3 shrink-0 flex-col">
                        <div class="board-draggable-handler flex items-center justify-between px-0.5 pb-3">
                            <div class="flex items-center space-x-4">
                                <div class="flex size-12 items-center justify-center rounded-lg bg-info/10 text-info">
                                    <svg class="icon icon-tabler icons-tabler-outline icon-tabler-certificate size-8"
                                         fill="none" height="24" stroke="currentColor" stroke-linecap="round"
                                         stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" width="24"
                                         xmlns="http://www.w3.org/2000/svg">
                                        <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                                        <path d="M15 15m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0"/>
                                        <path d="M13 17.5v4.5l2 -1.5l2 1.5v-4.5"/>
                                        <path
                                            d="M10 19h-5a2 2 0 0 1 -2 -2v-10c0 -1.1 .9 -2 2 -2h14a2 2 0 0 1 2 2v10a2 2 0 0 1 -1 1.73"/>
                                        <path d="M6 9l12 0"/>
                                        <path d="M6 12l3 0"/>
                                        <path d="M6 15l2 0"/>
                                    </svg>
                                </div>
                                <h3 class="text-2xl font-bold text-slate-700 dark:text-navy-100">
                                    Document Verification
                                </h3>
                            </div>
                        </div>
                        <div class="is-scrollbar-hidden relative space-y-5 overflow-y-auto p-0.5 mx-auto"
                             x-init="Sortable.create($el, {
                            animation: 200,
                            group: 'board-cards',
                            easing: 'cubic-bezier(0, 0, 0.2, 1)',
                            direction: 'vertical',
                            delay: 150,
                            delayOnTouchOnly: true,
                        })">
                            <template v-for="queue in documentVerificationQueue">
                                <Link :href="route('call-center.verification.create', queue.id)" class="card grow cursor-pointer hover:bg-info/20 items-center p-4 text-center sm:p-5 border w-80 rounded-lg">
                                    <div class="my-5">
                                        <h1 class="text-8xl text-black font-bold">{{ queue.token }}</h1>
                                    </div>
                                    <div class="my-2 grow">
                                        <h3 class="text-2xl font-medium text-slate-700 dark:text-navy-100">
                                            {{ queue.reference }}
                                        </h3>
                                    </div>
                                    <div class="mt-3 flex space-x-1">
                                        <button
                                            class="btn h-7 rounded-full bg-slate-150 px-3 text-xs+ font-medium text-slate-800 hover:bg-slate-200 focus:bg-slate-200 active:bg-slate-200/80 dark:bg-navy-500 dark:text-navy-50 dark:hover:bg-navy-450 dark:focus:bg-navy-450 dark:active:bg-navy-450/90">
                                            {{ queue.customer }}
                                        </button>
                                    </div>
                                </Link>
                            </template>
                        </div>
                    </div>

                    <div class="relative flex max-h-full w-1/3 shrink-0 flex-col">
                        <div class="board-draggable-handler flex items-center justify-between px-0.5 pb-3">
                            <div class="flex items-center space-x-4">
                                <div
                                    class="flex size-12 items-center justify-center rounded-lg bg-warning/10 text-warning">
                                    <svg class="icon icon-tabler icons-tabler-outline icon-tabler-cash-register size-8"
                                         fill="none" height="24" stroke="currentColor" stroke-linecap="round"
                                         stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" width="24"
                                         xmlns="http://www.w3.org/2000/svg">
                                        <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                                        <path
                                            d="M21 15h-2.5c-.398 0 -.779 .158 -1.061 .439c-.281 .281 -.439 .663 -.439 1.061c0 .398 .158 .779 .439 1.061c.281 .281 .663 .439 1.061 .439h1c.398 0 .779 .158 1.061 .439c.281 .281 .439 .663 .439 1.061c0 .398 -.158 .779 -.439 1.061c-.281 .281 -.663 .439 -1.061 .439h-2.5"/>
                                        <path d="M19 21v1m0 -8v1"/>
                                        <path
                                            d="M13 21h-7c-.53 0 -1.039 -.211 -1.414 -.586c-.375 -.375 -.586 -.884 -.586 -1.414v-10c0 -.53 .211 -1.039 .586 -1.414c.375 -.375 .884 -.586 1.414 -.586h2m12 3.12v-1.12c0 -.53 -.211 -1.039 -.586 -1.414c-.375 -.375 -.884 -.586 -1.414 -.586h-2"/>
                                        <path
                                            d="M16 10v-6c0 -.53 -.211 -1.039 -.586 -1.414c-.375 -.375 -.884 -.586 -1.414 -.586h-4c-.53 0 -1.039 .211 -1.414 .586c-.375 .375 -.586 .884 -.586 1.414v6m8 0h-8m8 0h1m-9 0h-1"/>
                                        <path d="M8 14v.01"/>
                                        <path d="M8 17v.01"/>
                                        <path d="M12 13.99v.01"/>
                                        <path d="M12 17v.01"/>
                                    </svg>
                                </div>
                                <h3 class="text-2xl font-bold text-slate-700 dark:text-navy-100">
                                    Cashier
                                </h3>
                            </div>
                        </div>
                        <div class="is-scrollbar-hidden relative space-y-2.5 overflow-y-auto p-0.5"
                             x-init="Sortable.create($el, {
                            animation: 200,
                            group: 'board-cards',
                            easing: 'cubic-bezier(0, 0, 0.2, 1)',
                            direction: 'vertical',
                            delay: 150,
                            delayOnTouchOnly: true,
                        })">
                            <div v-for="queue in cashierQueue"
                                 class="card grow cursor-pointer hover:bg-purple-100 items-center p-4 text-center sm:p-5">
                                <div class="my-5">
                                    <h1 class="text-8xl text-black font-bold">{{ queue.token }}</h1>
                                </div>
                                <div class="my-2 grow">
                                    <h3 class="text-2xl font-medium text-slate-700 dark:text-navy-100">
                                        {{ queue.reference }}
                                    </h3>
                                </div>
                                <div class="mt-3 flex space-x-1">
                                    <button
                                        class="btn h-7 rounded-full bg-slate-150 px-3 text-xs+ font-medium text-slate-800 hover:bg-slate-200 focus:bg-slate-200 active:bg-slate-200/80 dark:bg-navy-500 dark:text-navy-50 dark:hover:bg-navy-450 dark:focus:bg-navy-450 dark:active:bg-navy-450/90">
                                        {{ queue.customer }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="relative flex max-h-full w-1/3 shrink-0 flex-col">
                        <div class="board-draggable-handler flex items-center justify-between px-0.5 pb-3">
                            <div class="flex items-center space-x-4">
                                <div
                                    class="flex size-12 items-center justify-center rounded-lg bg-secondary/10 text-secondary dark:bg-secondary-light/15 dark:text-secondary-light">
                                    <svg class="icon icon-tabler icons-tabler-outline icon-tabler-binoculars size-8"
                                         fill="none" height="24" stroke="currentColor" stroke-linecap="round"
                                         stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" width="24"
                                         xmlns="http://www.w3.org/2000/svg">
                                        <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                                        <path d="M7 16m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0"/>
                                        <path d="M17 16m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0"/>
                                        <path
                                            d="M16.346 9.17l-.729 -1.261c-.16 -.248 -1.056 -.203 -1.117 .091l-.177 1.38"/>
                                        <path
                                            d="M19.761 14.813l-2.84 -5.133c-.189 -.31 -.592 -.68 -1.421 -.68c-.828 0 -1.5 .448 -1.5 1v6"/>
                                        <path d="M7.654 9.17l.729 -1.261c.16 -.249 1.056 -.203 1.117 .091l.177 1.38"/>
                                        <path
                                            d="M4.239 14.813l2.84 -5.133c.189 -.31 .592 -.68 1.421 -.68c.828 0 1.5 .448 1.5 1v6"/>
                                        <rect height="2" width="4" x="10" y="12"/>
                                    </svg>
                                </div>
                                <h3 class="text-2xl font-bold text-slate-700 dark:text-navy-100">
                                    Examination
                                </h3>
                            </div>
                        </div>
                        <div class="is-scrollbar-hidden relative space-y-2.5 overflow-y-auto p-0.5"
                             x-init="Sortable.create($el, {
                            animation: 200,
                            group: 'board-cards',
                            easing: 'cubic-bezier(0, 0, 0.2, 1)',
                            direction: 'vertical',
                            delay: 150,
                            delayOnTouchOnly: true,
                        })">
                            <div v-for="queue in examinationQueue"
                                 class="card grow cursor-pointer hover:bg-purple-100 items-center p-4 text-center sm:p-5">
                                <div class="my-5">
                                    <h1 class="text-8xl text-black font-bold">{{ queue.token }}</h1>
                                </div>
                                <div class="my-2 grow">
                                    <h3 class="text-2xl font-medium text-slate-700 dark:text-navy-100">
                                        {{ queue.reference }}
                                    </h3>
                                </div>
                                <div class="mt-3 flex space-x-1">
                                    <button
                                        class="btn h-7 rounded-full bg-slate-150 px-3 text-xs+ font-medium text-slate-800 hover:bg-slate-200 focus:bg-slate-200 active:bg-slate-200/80 dark:bg-navy-500 dark:text-navy-50 dark:hover:bg-navy-450 dark:focus:bg-navy-450 dark:active:bg-navy-450/90">
                                        {{ queue.customer }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </DestinationAppLayout>
</template>
