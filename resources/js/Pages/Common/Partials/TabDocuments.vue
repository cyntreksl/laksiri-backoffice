<script setup>
import Tab from "@/Components/Tab.vue";
import {useForm} from "@inertiajs/vue3";
import {ref} from "vue";
import {push} from "notivue";

const props = defineProps({
    hbl: {
        type: Object,
        default: () => {
        },
    }
});

const hblDocumentsRecords = ref([]);

const fetchHBLDocuments = async () => {
    try {
        const response = await fetch(`hbls/get-hbl-documents/${props.hbl.id}`, {
            method: "GET",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content")
            },
        });

        if (!response.ok) {
            throw new Error('Network response was not ok.');
        } else {
            hblDocumentsRecords.value = await response.json();
        }
    } catch (error) {
        console.error(error.message);
    }
}

fetchHBLDocuments();

const blDocumentInput = ref(null);
const manifestDocumentInput = ref(null);
const receiptDocumentInput = ref(null);

const selectNewDoc = (refName) => {
    if (refName === 'blDocumentInput') {
        blDocumentInput.value.click();
    } else if (refName === 'manifestDocumentInput') {
        manifestDocumentInput.value.click();
    } else{
        receiptDocumentInput.value.click();
    }
};

const handleFileInput = (event, docType) => {
    form.document = event.target.files[0];
    if (docType === 'blDocument') {
        form.document_name = 'BL From Shipping Line';
    } else if (docType === 'manifestDocument') {
        form.document_name = 'Manifest';
    } else {
        form.document_name = 'Receipt for Freight Charges';
    }
};

const form = useForm({
    hbl_id: props.hbl.id,
    document_name: null,
    document: null,
})

const handleFileUpload = () => {
    form.post(route('hbls.upload.document'), {
        preserveScroll: true,
        onSuccess: () => {
            push.success('Document Uploaded!');
            form.reset();
            fetchHBLDocuments();
        },
        onError: () => {
            push.error('Something went to wrong!')
        }
    })
}
</script>

<template>
    <Tab label="Documents" name="tabDocuments">
        <div class="grid grid-cols-1 gap-5">
            <div class="col-span-1 sm:col-span-2">
                <h2 class="text-base font-medium tracking-wide text-slate-800 line-clamp-1 dark:text-navy-100">
                    List of Documents
                </h2>
                <div class="is-scrollbar-hidden min-w-full overflow-x-auto mt-5">
                    <table class="is-hoverable w-full text-left">
                        <tbody>
                        <tr>
                            <td class="whitespace-nowrap px-4 py-3 sm:px-5 flex items-center">
                                <svg v-if="hblDocumentsRecords.some(doc => doc.document_name === 'BL From Shipping Line')" class="icon icon-tabler icons-tabler-outline icon-tabler-check mr-3 text-success"  fill="none"  height="24"  stroke="currentColor"  stroke-linecap="round"  stroke-linejoin="round"  stroke-width="2"  viewBox="0 0 24 24"  width="24"  xmlns="http://www.w3.org/2000/svg"><path d="M0 0h24v24H0z" fill="none" stroke="none"/><path d="M5 12l5 5l10 -10" /></svg>

                                <svg v-else  class="icon icon-tabler icons-tabler-outline icon-tabler-exclamation-mark mr-3 text-warning"  fill="none"  height="24"  stroke="currentColor"  stroke-linecap="round"  stroke-linejoin="round"  stroke-width="2"  viewBox="0 0 24 24"  width="24"  xmlns="http://www.w3.org/2000/svg"><path d="M0 0h24v24H0z" fill="none" stroke="none"/><path d="M12 19v.01" /><path d="M12 15v-10" /></svg>

                                BL From Shipping Line
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 rounded-r-lg sm:px-5">
                                <form class="flex items-center space-x-4" @submit.prevent="handleFileUpload()">

                                    <input ref="blDocumentInput" hidden type="file" @input="handleFileInput($event, 'blDocument')"/>

                                    <svg class="icon icon-tabler icons-tabler-outline icon-tabler-upload hover:cursor-pointer" fill="none"  height="24"  stroke="currentColor"  stroke-linecap="round"  stroke-linejoin="round"  stroke-width="2"  viewBox="0 0 24 24"  width="24"  xmlns="http://www.w3.org/2000/svg"  @click.prevent="selectNewDoc('blDocumentInput')"><path d="M0 0h24v24H0z" fill="none" stroke="none"/><path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2" /><path d="M7 9l5 -5l5 5" /><path d="M12 4l0 12" /></svg>

                                    <button v-show="form.document_name === 'BL From Shipping Line'" type="submit">
                                        <svg  class="icon icon-tabler icons-tabler-filled icon-tabler-circle-check text-success"  fill="currentColor"  height="24"  viewBox="0 0 24 24"  width="24"  xmlns="http://www.w3.org/2000/svg"><path d="M0 0h24v24H0z" fill="none" stroke="none"/><path d="M17 3.34a10 10 0 1 1 -14.995 8.984l-.005 -.324l.005 -.324a10 10 0 0 1 14.995 -8.336zm-1.293 5.953a1 1 0 0 0 -1.32 -.083l-.094 .083l-3.293 3.292l-1.293 -1.292l-.094 -.083a1 1 0 0 0 -1.403 1.403l.083 .094l2 2l.094 .083a1 1 0 0 0 1.226 0l.094 -.083l4 -4l.083 -.094a1 1 0 0 0 -.083 -1.32z" /></svg>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <tr>
                            <td class="whitespace-nowrap rounded-l-lg px-4 py-3 sm:px-5 flex items-center">
                                <svg v-if="hblDocumentsRecords.some(doc => doc.document_name === 'Manifest')" class="icon icon-tabler icons-tabler-outline icon-tabler-check mr-3 text-success"  fill="none"  height="24"  stroke="currentColor"  stroke-linecap="round"  stroke-linejoin="round"  stroke-width="2"  viewBox="0 0 24 24"  width="24"  xmlns="http://www.w3.org/2000/svg"><path d="M0 0h24v24H0z" fill="none" stroke="none"/><path d="M5 12l5 5l10 -10" /></svg>

                                <svg v-else  class="icon icon-tabler icons-tabler-outline icon-tabler-exclamation-mark mr-3 text-warning"  fill="none"  height="24"  stroke="currentColor"  stroke-linecap="round"  stroke-linejoin="round"  stroke-width="2"  viewBox="0 0 24 24"  width="24"  xmlns="http://www.w3.org/2000/svg"><path d="M0 0h24v24H0z" fill="none" stroke="none"/><path d="M12 19v.01" /><path d="M12 15v-10" /></svg>

                                Manifest
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 rounded-r-lg sm:px-5">
                                <form class="flex items-center space-x-4" @submit.prevent="handleFileUpload()">

                                    <input ref="manifestDocumentInput" hidden type="file" @input="handleFileInput($event, 'manifestDocument')"/>

                                    <svg class="icon icon-tabler icons-tabler-outline icon-tabler-upload hover:cursor-pointer" fill="none"  height="24"  stroke="currentColor"  stroke-linecap="round"  stroke-linejoin="round"  stroke-width="2"  viewBox="0 0 24 24"  width="24"  xmlns="http://www.w3.org/2000/svg"  @click.prevent="selectNewDoc('manifestDocumentInput')"><path d="M0 0h24v24H0z" fill="none" stroke="none"/><path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2" /><path d="M7 9l5 -5l5 5" /><path d="M12 4l0 12" /></svg>

                                    <button v-show="form.document_name === 'Manifest'" type="submit">
                                        <svg  class="icon icon-tabler icons-tabler-filled icon-tabler-circle-check text-success"  fill="currentColor"  height="24"  viewBox="0 0 24 24"  width="24"  xmlns="http://www.w3.org/2000/svg"><path d="M0 0h24v24H0z" fill="none" stroke="none"/><path d="M17 3.34a10 10 0 1 1 -14.995 8.984l-.005 -.324l.005 -.324a10 10 0 0 1 14.995 -8.336zm-1.293 5.953a1 1 0 0 0 -1.32 -.083l-.094 .083l-3.293 3.292l-1.293 -1.292l-.094 -.083a1 1 0 0 0 -1.403 1.403l.083 .094l2 2l.094 .083a1 1 0 0 0 1.226 0l.094 -.083l4 -4l.083 -.094a1 1 0 0 0 -.083 -1.32z" /></svg>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <tr>
                            <td class="whitespace-nowrap rounded-l-lg px-4 py-3 sm:px-5 flex items-center">
                                <svg v-if="hblDocumentsRecords.some(doc => doc.document_name === 'Receipt for Freight Charges')" class="icon icon-tabler icons-tabler-outline icon-tabler-check mr-3 text-success"  fill="none"  height="24"  stroke="currentColor"  stroke-linecap="round"  stroke-linejoin="round"  stroke-width="2"  viewBox="0 0 24 24"  width="24"  xmlns="http://www.w3.org/2000/svg"><path d="M0 0h24v24H0z" fill="none" stroke="none"/><path d="M5 12l5 5l10 -10" /></svg>

                                <svg v-else  class="icon icon-tabler icons-tabler-outline icon-tabler-exclamation-mark mr-3 text-warning"  fill="none"  height="24"  stroke="currentColor"  stroke-linecap="round"  stroke-linejoin="round"  stroke-width="2"  viewBox="0 0 24 24"  width="24"  xmlns="http://www.w3.org/2000/svg"><path d="M0 0h24v24H0z" fill="none" stroke="none"/><path d="M12 19v.01" /><path d="M12 15v-10" /></svg>

                                Receipt for Freight Charges
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 rounded-r-lg sm:px-5">
                                <form class="flex items-center space-x-4" @submit.prevent="handleFileUpload()">

                                    <input ref="receiptDocumentInput" hidden type="file" @input="handleFileInput($event, 'receiptDocument')"/>

                                    <svg class="icon icon-tabler icons-tabler-outline icon-tabler-upload hover:cursor-pointer" fill="none"  height="24"  stroke="currentColor"  stroke-linecap="round"  stroke-linejoin="round"  stroke-width="2"  viewBox="0 0 24 24"  width="24"  xmlns="http://www.w3.org/2000/svg"  @click.prevent="selectNewDoc('receiptDocumentInput')"><path d="M0 0h24v24H0z" fill="none" stroke="none"/><path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2" /><path d="M7 9l5 -5l5 5" /><path d="M12 4l0 12" /></svg>

                                    <button v-show="form.document_name === 'Receipt for Freight Charges'" type="submit">
                                        <svg  class="icon icon-tabler icons-tabler-filled icon-tabler-circle-check text-success"  fill="currentColor"  height="24"  viewBox="0 0 24 24"  width="24"  xmlns="http://www.w3.org/2000/svg"><path d="M0 0h24v24H0z" fill="none" stroke="none"/><path d="M17 3.34a10 10 0 1 1 -14.995 8.984l-.005 -.324l.005 -.324a10 10 0 0 1 14.995 -8.336zm-1.293 5.953a1 1 0 0 0 -1.32 -.083l-.094 .083l-3.293 3.292l-1.293 -1.292l-.094 -.083a1 1 0 0 0 -1.403 1.403l.083 .094l2 2l.094 .083a1 1 0 0 0 1.226 0l.094 -.083l4 -4l.083 -.094a1 1 0 0 0 -.083 -1.32z" /></svg>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="my-4">
            <h2 class="text-base font-medium tracking-wide text-slate-800 line-clamp-1 dark:text-navy-100">
                Other Attachments
            </h2>

            <div class="whitespace-nowrap py-3 hover:bg-gray-50 rounded">
                <div class="flex justify-between items-center space-x-4 px-2">
                    <div class="flex items-center space-x-4">
                        <div
                            class="relative flex size-9 shrink-0 items-center justify-center rounded-lg bg-primary/10 dark:bg-accent">
                            <svg class="size-5.5 text-primary dark:text-white icon icon-tabler icons-tabler-outline icon-tabler-pdf" fill="none" height="24" stroke="currentColor"
                                 stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24"
                                 width="24"
                                 xmlns="http://www.w3.org/2000/svg">
                                <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                                <path d="M10 8v8h2a2 2 0 0 0 2 -2v-4a2 2 0 0 0 -2 -2h-2z"/>
                                <path d="M3 12h2a2 2 0 1 0 0 -4h-2v8"/>
                                <path d="M17 12h3"/>
                                <path d="M21 8h-4v8"/>
                            </svg>
                        </div>
                        <span class="font-medium text-slate-700 dark:text-navy-100">HBL Record</span>
                    </div>
                    <a :href="route('hbls.download', hbl.id)">
                        <svg  class="icon icon-tabler icons-tabler-outline icon-tabler-download"  fill="none"  height="24"  stroke="currentColor"  stroke-linecap="round"  stroke-linejoin="round"  stroke-width="2"  viewBox="0 0 24 24"  width="24"  xmlns="http://www.w3.org/2000/svg"><path d="M0 0h24v24H0z" fill="none" stroke="none"/><path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2" /><path d="M7 11l5 5l5 -5" /><path d="M12 4l0 12" /></svg>
                    </a>
                </div>
            </div>
        </div>
    </Tab>
</template>
