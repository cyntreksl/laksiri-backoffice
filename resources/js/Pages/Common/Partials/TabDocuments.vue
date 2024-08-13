<script setup>
import Tab from "@/Components/Tab.vue";
import {router, useForm} from "@inertiajs/vue3";
import {computed, onMounted, ref, watch} from "vue";
import {push} from "notivue";
import DeleteDocConfirmationModal from "@/Pages/Common/Partials/DeleteDocConfirmationModal.vue";

const props = defineProps({
    hblId: {
        type: Number,
        required: true,
    }
});

const isLoading = ref(false);
const hblDocumentsRecords = ref([]);

const fetchHBLDocuments = async () => {
    isLoading.value = true;
    try {
        const response = await fetch(`hbls/get-hbl-documents/${props.hblId}`, {
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
    } finally {
        isLoading.value = false;
    }
}

const passportInput = ref(null);
const nicInput = ref(null);
const specialNoteInput = ref(null);
const packingListInput = ref(null);

const selectNewDoc = (refName) => {
    if (refName === 'passportInput') {
        passportInput.value.click();
    } else if (refName === 'nicInput') {
        nicInput.value.click();
    } else if (refName === 'packingListInput') {
        packingListInput.value.click();
    } else {
        specialNoteInput.value.click();
    }
};

const handleFileInput = (event, docType) => {
    form.document = event.target.files[0];
    if (docType === 'passport') {
        form.document_name = 'Copy of Passport';
    } else if (docType === 'nic') {
        form.document_name = 'Copy of NIC';
    } else if (docType === 'packing') {
        form.document_name = 'Packing List';
    } else {
        form.document_name = 'Special Note';
    }
};

const form = useForm({
    hbl_id: props.hblId,
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

const showConfirmDeleteDocModal = ref(false);
const docId = ref(null);
const docName = ref('');

const confirmDeleteDoc = (id, name) => {
    docId.value = id;
    docName.value = name;
    showConfirmDeleteDocModal.value = true;
};

const closeDeleteModal = () => {
    docId.value = null;
    docName.value = '';
    showConfirmDeleteDocModal.value = false;
};

const handleDeleteDoc = () => {
    router.delete(route("hbls.destroy.document", docId.value), {
        preserveScroll: true,
        onSuccess: () => {
            closeDeleteModal();
            push.success('Document Deleted Successfully!');
            fetchHBLDocuments();
        },
    });
};

watch(() => props.hblId, (newVal) => {
    if (newVal !== undefined) {
        fetchHBLDocuments();
    }
});

onMounted(() => {
    if (props.hblId !== null && props.hblId !== undefined) {
        fetchHBLDocuments();
    }
});
</script>

<template>
    <Tab label="Documents" name="tabDocuments">
        <div class="grid grid-cols-1 gap-5">
            <div class="col-span-1 sm:col-span-2">
                <h2 class="text-base font-medium tracking-wide text-slate-800 line-clamp-1 dark:text-navy-100">
                    List of Documents
                </h2>
                <div class="is-scrollbar-hidden min-w-full overflow-x-auto mt-5">
                    <div
                        v-if="isLoading"
                        class="flex animate-pulse flex-col border border-slate-150 dark:border-navy-500"
                    >
                        <div class="w-full px-6 py-4">
                            <div class="h-3 w-full rounded bg-slate-150 dark:bg-navy-500"></div>
                        </div>

                        <div class="w-full px-6 py-4">
                            <div class="h-3 w-full rounded bg-slate-150 dark:bg-navy-500"></div>
                        </div>

                        <div class="w-full px-6 py-4">
                            <div class="h-3 w-full rounded bg-slate-150 dark:bg-navy-500"></div>
                        </div>

                        <div class="w-full px-6 py-4">
                            <div class="h-3 w-full rounded bg-slate-150 dark:bg-navy-500"></div>
                        </div>
                    </div>
                    <table v-else class="is-hoverable w-full text-left">
                        <tbody>
                        <tr>
                            <td class="whitespace-nowrap rounded-l-lg px-4 py-3 sm:px-5 flex items-center">
                                <svg
                                    v-if="hblDocumentsRecords.some(doc => doc.document_name === 'Copy of Passport')"
                                    class="size-7 icon icon-tabler icons-tabler-outline icon-tabler-check mr-3 text-success"
                                    fill="none" height="24" stroke="currentColor" stroke-linecap="round"
                                    stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" width="24"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                                    <path d="M5 12l5 5l10 -10"/>
                                </svg>

                                <svg v-else
                                     class="size-7 icon icon-tabler icons-tabler-outline icon-tabler-exclamation-mark mr-3 text-warning"
                                     fill="none" height="24" stroke="currentColor" stroke-linecap="round"
                                     stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" width="24"
                                     xmlns="http://www.w3.org/2000/svg">
                                    <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                                    <path d="M12 19v.01"/>
                                    <path d="M12 15v-10"/>
                                </svg>

                                Copy of Passport
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 rounded-r-lg sm:px-5">
                                <form v-if="$page.props.user.permissions.includes('hbls.upload documents')"
                                      class="flex items-center space-x-4 float-right"
                                      @submit.prevent="handleFileUpload()">

                                    <a  v-if="hblDocumentsRecords.some(doc => doc.document_name === 'Copy of Passport')" :href="route('hbls.download.document-hbl-document', hblDocumentsRecords.find(doc => doc.document_name === 'Copy of Passport').id)">
                                        <svg   class="icon icon-tabler icons-tabler-outline icon-tabler-download"  fill="none"  height="24"  stroke="currentColor"  stroke-linecap="round"  stroke-linejoin="round"  stroke-width="2"  viewBox="0 0 24 24"  width="24"  xmlns="http://www.w3.org/2000/svg"><path d="M0 0h24v24H0z" fill="none" stroke="none"/><path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2" /><path d="M7 11l5 5l5 -5" /><path d="M12 4l0 12" /></svg>
                                    </a>

                                    <input ref="passportInput" hidden type="file"
                                           @input="handleFileInput($event, 'passport')"/>

                                    <svg
                                        class="icon icon-tabler icons-tabler-outline icon-tabler-upload hover:cursor-pointer"
                                        fill="none" height="24" stroke="currentColor" stroke-linecap="round"
                                        stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" width="24"
                                        xmlns="http://www.w3.org/2000/svg"
                                        @click.prevent="selectNewDoc('passportInput')">
                                        <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                                        <path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2"/>
                                        <path d="M7 9l5 -5l5 5"/>
                                        <path d="M12 4l0 12"/>
                                    </svg>

                                    <button
                                        v-show="form.document_name === 'Copy of Passport'"
                                        class="btn py-1 font-medium text-primary hover:bg-primary/20 focus:bg-primary/20 active:bg-primary/25 dark:text-accent-light dark:hover:bg-accent-light/20 dark:focus:bg-accent-light/20 dark:active:bg-accent-light/25"
                                        type="submit"
                                    >
                                        Save
                                    </button>

                                    <template v-if="$page.props.user.permissions.includes('hbls.delete documents')">
                                        <button
                                            v-if="hblDocumentsRecords.some(doc => doc.document_name === 'Copy of Passport')"
                                            class="btn size-7 rounded-full bg-slate-150 p-0 font-medium text-slate-800 hover:bg-slate-200 hover:shadow-lg hover:shadow-slate-200/50 focus:bg-slate-200 focus:shadow-lg focus:shadow-slate-200/50 active:bg-slate-200/80 dark:bg-navy-500 dark:text-navy-50 dark:hover:bg-navy-450 dark:hover:shadow-navy-450/50 dark:focus:bg-navy-450 dark:focus:shadow-navy-450/50 dark:active:bg-navy-450/90"
                                            @click.prevent="confirmDeleteDoc(hblDocumentsRecords.find(doc => doc.document_name === 'Copy of Passport').id, 'Copy of Passport')"
                                        >
                                            <svg
                                                class="size-5 icon icon-tabler icons-tabler-filled icon-tabler-trash text-error"
                                                fill="currentColor" viewBox="0 0 24 24"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                                                <path
                                                    d="M20 6a1 1 0 0 1 .117 1.993l-.117 .007h-.081l-.919 11a3 3 0 0 1 -2.824 2.995l-.176 .005h-8c-1.598 0 -2.904 -1.249 -2.992 -2.75l-.005 -.167l-.923 -11.083h-.08a1 1 0 0 1 -.117 -1.993l.117 -.007h16z"/>
                                                <path
                                                    d="M14 2a2 2 0 0 1 2 2a1 1 0 0 1 -1.993 .117l-.007 -.117h-4l-.007 .117a1 1 0 0 1 -1.993 -.117a2 2 0 0 1 1.85 -1.995l.15 -.005h4z"/>
                                            </svg>
                                        </button>
                                    </template>
                                </form>
                            </td>
                        </tr>
                        <tr>
                            <td class="whitespace-nowrap rounded-l-lg px-4 py-3 sm:px-5 flex items-center">
                                <svg v-if="hblDocumentsRecords.some(doc => doc.document_name === 'Copy of NIC')"
                                     class="size-7 icon icon-tabler icons-tabler-outline icon-tabler-check mr-3 text-success"
                                     fill="none" height="24" stroke="currentColor" stroke-linecap="round"
                                     stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" width="24"
                                     xmlns="http://www.w3.org/2000/svg">
                                    <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                                    <path d="M5 12l5 5l10 -10"/>
                                </svg>

                                <svg v-else
                                     class="size-7 icon icon-tabler icons-tabler-outline icon-tabler-exclamation-mark mr-3 text-warning"
                                     fill="none" height="24" stroke="currentColor" stroke-linecap="round"
                                     stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" width="24"
                                     xmlns="http://www.w3.org/2000/svg">
                                    <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                                    <path d="M12 19v.01"/>
                                    <path d="M12 15v-10"/>
                                </svg>

                                Copy of NIC
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 rounded-r-lg sm:px-5">
                                <form v-if="$page.props.user.permissions.includes('hbls.upload documents')"
                                      class="flex items-center space-x-4 float-right"
                                      @submit.prevent="handleFileUpload()">

                                    <a  v-if="hblDocumentsRecords.some(doc => doc.document_name === 'Copy of NIC')" :href="route('hbls.download.document-hbl-document', hblDocumentsRecords.find(doc => doc.document_name === 'Copy of NIC').id)">
                                        <svg   class="icon icon-tabler icons-tabler-outline icon-tabler-download"  fill="none"  height="24"  stroke="currentColor"  stroke-linecap="round"  stroke-linejoin="round"  stroke-width="2"  viewBox="0 0 24 24"  width="24"  xmlns="http://www.w3.org/2000/svg"><path d="M0 0h24v24H0z" fill="none" stroke="none"/><path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2" /><path d="M7 11l5 5l5 -5" /><path d="M12 4l0 12" /></svg>
                                    </a>

                                    <input ref="nicInput" hidden type="file"
                                           @input="handleFileInput($event, 'nic')"/>

                                    <svg
                                        class="icon icon-tabler icons-tabler-outline icon-tabler-upload hover:cursor-pointer"
                                        fill="none" height="24" stroke="currentColor" stroke-linecap="round"
                                        stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" width="24"
                                        xmlns="http://www.w3.org/2000/svg"
                                        @click.prevent="selectNewDoc('nicInput')">
                                        <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                                        <path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2"/>
                                        <path d="M7 9l5 -5l5 5"/>
                                        <path d="M12 4l0 12"/>
                                    </svg>

                                    <button
                                        v-show="form.document_name === 'Copy of NIC'"
                                        class="btn py-1 font-medium text-primary hover:bg-primary/20 focus:bg-primary/20 active:bg-primary/25 dark:text-accent-light dark:hover:bg-accent-light/20 dark:focus:bg-accent-light/20 dark:active:bg-accent-light/25"
                                        type="submit"
                                    >
                                        Save
                                    </button>

                                    <template v-if="$page.props.user.permissions.includes('hbls.delete documents')">
                                        <button
                                            v-if="hblDocumentsRecords.some(doc => doc.document_name === 'Copy of NIC')"
                                            class="btn size-7 rounded-full bg-slate-150 p-0 font-medium text-slate-800 hover:bg-slate-200 hover:shadow-lg hover:shadow-slate-200/50 focus:bg-slate-200 focus:shadow-lg focus:shadow-slate-200/50 active:bg-slate-200/80 dark:bg-navy-500 dark:text-navy-50 dark:hover:bg-navy-450 dark:hover:shadow-navy-450/50 dark:focus:bg-navy-450 dark:focus:shadow-navy-450/50 dark:active:bg-navy-450/90"
                                            @click.prevent="confirmDeleteDoc(hblDocumentsRecords.find(doc => doc.document_name === 'Copy of NIC').id, 'Copy of NIC')"
                                        >
                                            <svg
                                                class="size-5 icon icon-tabler icons-tabler-filled icon-tabler-trash text-error"
                                                fill="currentColor" viewBox="0 0 24 24"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                                                <path
                                                    d="M20 6a1 1 0 0 1 .117 1.993l-.117 .007h-.081l-.919 11a3 3 0 0 1 -2.824 2.995l-.176 .005h-8c-1.598 0 -2.904 -1.249 -2.992 -2.75l-.005 -.167l-.923 -11.083h-.08a1 1 0 0 1 -.117 -1.993l.117 -.007h16z"/>
                                                <path
                                                    d="M14 2a2 2 0 0 1 2 2a1 1 0 0 1 -1.993 .117l-.007 -.117h-4l-.007 .117a1 1 0 0 1 -1.993 -.117a2 2 0 0 1 1.85 -1.995l.15 -.005h4z"/>
                                            </svg>
                                        </button>
                                    </template>
                                </form>
                            </td>
                        </tr>
                        <tr>
                            <td class="whitespace-nowrap rounded-l-lg px-4 py-3 sm:px-5 flex items-center">
                                <svg v-if="hblDocumentsRecords.some(doc => doc.document_name === 'Packing List')"
                                     class="size-7 icon icon-tabler icons-tabler-outline icon-tabler-check mr-3 text-success"
                                     fill="none" height="24" stroke="currentColor" stroke-linecap="round"
                                     stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" width="24"
                                     xmlns="http://www.w3.org/2000/svg">
                                    <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                                    <path d="M5 12l5 5l10 -10"/>
                                </svg>

                                <svg v-else
                                     class="size-7 icon icon-tabler icons-tabler-outline icon-tabler-exclamation-mark mr-3 text-warning"
                                     fill="none" height="24" stroke="currentColor" stroke-linecap="round"
                                     stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" width="24"
                                     xmlns="http://www.w3.org/2000/svg">
                                    <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                                    <path d="M12 19v.01"/>
                                    <path d="M12 15v-10"/>
                                </svg>

                                Packing List
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 rounded-r-lg sm:px-5">
                                <form v-if="$page.props.user.permissions.includes('hbls.upload documents')"
                                      class="flex items-center space-x-4 float-right"
                                      @submit.prevent="handleFileUpload()">

                                    <a  v-if="hblDocumentsRecords.some(doc => doc.document_name === 'Packing List')" :href="route('hbls.download.document-hbl-document', hblDocumentsRecords.find(doc => doc.document_name === 'Packing List').id)">
                                        <svg   class="icon icon-tabler icons-tabler-outline icon-tabler-download"  fill="none"  height="24"  stroke="currentColor"  stroke-linecap="round"  stroke-linejoin="round"  stroke-width="2"  viewBox="0 0 24 24"  width="24"  xmlns="http://www.w3.org/2000/svg"><path d="M0 0h24v24H0z" fill="none" stroke="none"/><path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2" /><path d="M7 11l5 5l5 -5" /><path d="M12 4l0 12" /></svg>
                                    </a>

                                    <input ref="packingListInput" hidden type="file"
                                           @input="handleFileInput($event, 'packing')"/>

                                    <svg
                                        class="icon icon-tabler icons-tabler-outline icon-tabler-upload hover:cursor-pointer"
                                        fill="none" height="24" stroke="currentColor" stroke-linecap="round"
                                        stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" width="24"
                                        xmlns="http://www.w3.org/2000/svg"
                                        @click.prevent="selectNewDoc('packingListInput')">
                                        <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                                        <path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2"/>
                                        <path d="M7 9l5 -5l5 5"/>
                                        <path d="M12 4l0 12"/>
                                    </svg>

                                    <button
                                        v-show="form.document_name === 'Packing List'"
                                        class="btn py-1 font-medium text-primary hover:bg-primary/20 focus:bg-primary/20 active:bg-primary/25 dark:text-accent-light dark:hover:bg-accent-light/20 dark:focus:bg-accent-light/20 dark:active:bg-accent-light/25"
                                        type="submit"
                                    >
                                        Save
                                    </button>

                                    <template v-if="$page.props.user.permissions.includes('hbls.delete documents')">
                                        <button
                                            v-if="hblDocumentsRecords.some(doc => doc.document_name === 'Packing List')"
                                            class="btn size-7 rounded-full bg-slate-150 p-0 font-medium text-slate-800 hover:bg-slate-200 hover:shadow-lg hover:shadow-slate-200/50 focus:bg-slate-200 focus:shadow-lg focus:shadow-slate-200/50 active:bg-slate-200/80 dark:bg-navy-500 dark:text-navy-50 dark:hover:bg-navy-450 dark:hover:shadow-navy-450/50 dark:focus:bg-navy-450 dark:focus:shadow-navy-450/50 dark:active:bg-navy-450/90"
                                            @click.prevent="confirmDeleteDoc(hblDocumentsRecords.find(doc => doc.document_name === 'Packing List').id, 'Packing List')"
                                        >
                                            <svg
                                                class="size-5 icon icon-tabler icons-tabler-filled icon-tabler-trash text-error"
                                                fill="currentColor" viewBox="0 0 24 24"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                                                <path
                                                    d="M20 6a1 1 0 0 1 .117 1.993l-.117 .007h-.081l-.919 11a3 3 0 0 1 -2.824 2.995l-.176 .005h-8c-1.598 0 -2.904 -1.249 -2.992 -2.75l-.005 -.167l-.923 -11.083h-.08a1 1 0 0 1 -.117 -1.993l.117 -.007h16z"/>
                                                <path
                                                    d="M14 2a2 2 0 0 1 2 2a1 1 0 0 1 -1.993 .117l-.007 -.117h-4l-.007 .117a1 1 0 0 1 -1.993 -.117a2 2 0 0 1 1.85 -1.995l.15 -.005h4z"/>
                                            </svg>
                                        </button>
                                    </template>
                                </form>
                            </td>
                        </tr>
                        <tr>
                            <td class="whitespace-nowrap rounded-l-lg px-4 py-3 sm:px-5 flex items-center">
                                <svg
                                    v-if="hblDocumentsRecords.some(doc => doc.document_name === 'Special Note')"
                                    class="icon icon-tabler icons-tabler-outline icon-tabler-check mr-3 text-success size-7"
                                    fill="none" height="24" stroke="currentColor" stroke-linecap="round"
                                    stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" width="24"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                                    <path d="M5 12l5 5l10 -10"/>
                                </svg>

                                <svg v-else
                                     class="size-7 icon icon-tabler icons-tabler-outline icon-tabler-exclamation-mark mr-3 text-warning"
                                     fill="none" height="24" stroke="currentColor" stroke-linecap="round"
                                     stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" width="24"
                                     xmlns="http://www.w3.org/2000/svg">
                                    <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                                    <path d="M12 19v.01"/>
                                    <path d="M12 15v-10"/>
                                </svg>

                                Special Note
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 rounded-r-lg sm:px-5">
                                <form v-if="$page.props.user.permissions.includes('hbls.upload documents')"
                                      class="flex items-center space-x-4 float-right"
                                      @submit.prevent="handleFileUpload()">

                                    <a  v-if="hblDocumentsRecords.some(doc => doc.document_name === 'Special Note')" :href="route('hbls.download.document-hbl-document', hblDocumentsRecords.find(doc => doc.document_name === 'Special Note').id)">
                                        <svg   class="icon icon-tabler icons-tabler-outline icon-tabler-download"  fill="none"  height="24"  stroke="currentColor"  stroke-linecap="round"  stroke-linejoin="round"  stroke-width="2"  viewBox="0 0 24 24"  width="24"  xmlns="http://www.w3.org/2000/svg"><path d="M0 0h24v24H0z" fill="none" stroke="none"/><path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2" /><path d="M7 11l5 5l5 -5" /><path d="M12 4l0 12" /></svg>
                                    </a>

                                    <input ref="specialNoteInput" hidden type="file"
                                           @input="handleFileInput($event, 'specialNote')"/>

                                    <svg
                                        class="icon icon-tabler icons-tabler-outline icon-tabler-upload hover:cursor-pointer"
                                        fill="none" height="24" stroke="currentColor" stroke-linecap="round"
                                        stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" width="24"
                                        xmlns="http://www.w3.org/2000/svg"
                                        @click.prevent="selectNewDoc('specialNoteInput')">
                                        <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                                        <path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2"/>
                                        <path d="M7 9l5 -5l5 5"/>
                                        <path d="M12 4l0 12"/>
                                    </svg>

                                    <button
                                        v-show="form.document_name === 'Special Note'"
                                        class="btn py-1 font-medium text-primary hover:bg-primary/20 focus:bg-primary/20 active:bg-primary/25 dark:text-accent-light dark:hover:bg-accent-light/20 dark:focus:bg-accent-light/20 dark:active:bg-accent-light/25"
                                        type="submit"
                                    >
                                        Save
                                    </button>

                                    <template v-if="$page.props.user.permissions.includes('hbls.delete documents')">
                                        <button
                                            v-if="hblDocumentsRecords.some(doc => doc.document_name === 'Special Note')"
                                            class="btn size-6 rounded-full bg-slate-150 p-0 font-medium text-slate-800 hover:bg-slate-200 hover:shadow-lg hover:shadow-slate-200/50 focus:bg-slate-200 focus:shadow-lg focus:shadow-slate-200/50 active:bg-slate-200/80 dark:bg-navy-500 dark:text-navy-50 dark:hover:bg-navy-450 dark:hover:shadow-navy-450/50 dark:focus:bg-navy-450 dark:focus:shadow-navy-450/50 dark:active:bg-navy-450/90"
                                            @click.prevent="confirmDeleteDoc(hblDocumentsRecords.find(doc => doc.document_name === 'Special Note').id, 'Special Note')"
                                        >
                                            <svg
                                                class="size-5 icon icon-tabler icons-tabler-filled icon-tabler-trash text-error"
                                                fill="currentColor" viewBox="0 0 24 24"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                                                <path
                                                    d="M20 6a1 1 0 0 1 .117 1.993l-.117 .007h-.081l-.919 11a3 3 0 0 1 -2.824 2.995l-.176 .005h-8c-1.598 0 -2.904 -1.249 -2.992 -2.75l-.005 -.167l-.923 -11.083h-.08a1 1 0 0 1 -.117 -1.993l.117 -.007h16z"/>
                                                <path
                                                    d="M14 2a2 2 0 0 1 2 2a1 1 0 0 1 -1.993 .117l-.007 -.117h-4l-.007 .117a1 1 0 0 1 -1.993 -.117a2 2 0 0 1 1.85 -1.995l.15 -.005h4z"/>
                                            </svg>
                                        </button>
                                    </template>
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
                            <svg
                                class="size-5.5 text-primary dark:text-white icon icon-tabler icons-tabler-outline icon-tabler-pdf"
                                fill="none" height="24" stroke="currentColor"
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
                    <template v-if="$page.props.user.permissions.includes('hbls.download pdf')">
                        <a v-if="hblId" :href="route('hbls.download', hblId)">
                            <svg class="icon icon-tabler icons-tabler-outline icon-tabler-download" fill="none" height="24"
                                 stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                 viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg">
                                <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                                <path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2"/>
                                <path d="M7 11l5 5l5 -5"/>
                                <path d="M12 4l0 12"/>
                            </svg>
                        </a>
                    </template>
                </div>
            </div>
        </div>

        <DeleteDocConfirmationModal :doc-name="docName" :show="showConfirmDeleteDocModal" @close="closeDeleteModal"
                                    @delete-doc="handleDeleteDoc"/>
    </Tab>
</template>
