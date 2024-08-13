<script setup>
import Tab from "@/Components/Tab.vue";
import {ref, watch} from "vue";
import {router, useForm} from "@inertiajs/vue3";
import {push} from "notivue";
import DeleteDocConfirmationModal from "@/Pages/Loading/Partials/DeleteDocConfirmationModal.vue";

const props = defineProps({
    containerId: {
        type: Number,
        required: true,
    }
});

const containerDocumentsRecords = ref([]);

const fetchContainerDocuments = async () => {
    try {
        const response = await fetch(`containers/get-container-documents/${props.containerId}`, {
            method: "GET",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content")
            },
        });

        if (!response.ok) {
            throw new Error('Network response was not ok.');
        } else {
            containerDocumentsRecords.value = await response.json();
        }
    } catch (error) {
        console.error(error.message);
    }
}

fetchContainerDocuments()

const blDocumentInput = ref(null);
const manifestDocumentInput = ref(null);
const receiptDocumentInput = ref(null);

const selectNewDoc = (refName) => {
    if (refName === 'blDocumentInput') {
        blDocumentInput.value.click();
    } else if (refName === 'manifestDocumentInput') {
        manifestDocumentInput.value.click();
    } else {
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
    container_id: props.containerId,
    document_name: null,
    document: null,
})

const handleFileUpload = () => {
    form.post(route('loading.containers.upload.document'), {
        preserveScroll: true,
        onSuccess: () => {
            push.success('Document Uploaded!');
            form.reset();
            fetchContainerDocuments();
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
    router.delete(route("loading.containers.destroy.document", docId.value), {
        preserveScroll: true,
        onSuccess: () => {
            closeDeleteModal();
            push.success('Document Deleted Successfully!');
            fetchContainerDocuments();
        },
    });
};
</script>

<template>
    <Tab label="Document for Shipment" name="tabDocuments">
        <div class="grid grid-cols-1 gap-5">
            <div class="col-span-1 sm:col-span-2">
                <h2 class="text-base font-medium tracking-wide text-slate-800 line-clamp-1 dark:text-navy-100">
                    List of Documents
                </h2>
                <div class="is-scrollbar-hidden min-w-full overflow-x-auto mt-5">
                    <table class="is-hoverable w-full text-left">
                        <tbody>
                        <tr>
                            <td class="whitespace-nowrap rounded-l-lg px-4 py-3 sm:px-5 flex items-center">
                                <svg
                                    v-if="containerDocumentsRecords.some(doc => doc.document_name === 'BL From Shipping Line')"
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

                                BL From Shipping Line
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 rounded-r-lg sm:px-5">
                                <form v-if="$page.props.user.permissions.includes('container.upload documents')"
                                      class="flex items-center space-x-4 float-right"
                                      @submit.prevent="handleFileUpload">

                                    <a  v-if="containerDocumentsRecords.some(doc => doc.document_name === 'BL From Shipping Line')" :href="route('loading.containers-documents.download', containerDocumentsRecords.find(doc => doc.document_name === 'BL From Shipping Line').id)">
                                        <svg   class="icon icon-tabler icons-tabler-outline icon-tabler-download"  fill="none"  height="24"  stroke="currentColor"  stroke-linecap="round"  stroke-linejoin="round"  stroke-width="2"  viewBox="0 0 24 24"  width="24"  xmlns="http://www.w3.org/2000/svg"><path d="M0 0h24v24H0z" fill="none" stroke="none"/><path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2" /><path d="M7 11l5 5l5 -5" /><path d="M12 4l0 12" /></svg>
                                    </a>

                                    <input ref="blDocumentInput" hidden type="file"
                                           @input="handleFileInput($event, 'blDocument')"/>

                                    <svg
                                        class="icon icon-tabler icons-tabler-outline icon-tabler-upload hover:cursor-pointer"
                                        fill="none" height="24" stroke="currentColor" stroke-linecap="round"
                                        stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" width="24"
                                        xmlns="http://www.w3.org/2000/svg"
                                        @click.prevent="selectNewDoc('blDocumentInput')">
                                        <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                                        <path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2"/>
                                        <path d="M7 9l5 -5l5 5"/>
                                        <path d="M12 4l0 12"/>
                                    </svg>

                                    <button
                                        v-show="form.document_name === 'BL From Shipping Line'"
                                        class="btn py-1 font-medium text-primary hover:bg-primary/20 focus:bg-primary/20 active:bg-primary/25 dark:text-accent-light dark:hover:bg-accent-light/20 dark:focus:bg-accent-light/20 dark:active:bg-accent-light/25"
                                        type="submit"
                                    >
                                        Save
                                    </button>

                                    <template v-if="$page.props.user.permissions.includes('container.delete documents')">
                                        <button
                                            v-if="containerDocumentsRecords.some(doc => doc.document_name === 'BL From Shipping Line')"
                                            class="btn size-7 rounded-full bg-slate-150 p-0 font-medium text-slate-800 hover:bg-slate-200 hover:shadow-lg hover:shadow-slate-200/50 focus:bg-slate-200 focus:shadow-lg focus:shadow-slate-200/50 active:bg-slate-200/80 dark:bg-navy-500 dark:text-navy-50 dark:hover:bg-navy-450 dark:hover:shadow-navy-450/50 dark:focus:bg-navy-450 dark:focus:shadow-navy-450/50 dark:active:bg-navy-450/90"
                                            @click.prevent="confirmDeleteDoc(containerDocumentsRecords.find(doc => doc.document_name === 'BL From Shipping Line').id, 'BL From Shipping Line')"
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
                                <svg v-if="containerDocumentsRecords.some(doc => doc.document_name === 'Manifest')"
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

                                Manifest
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 rounded-r-lg sm:px-5">
                                <form v-if="$page.props.user.permissions.includes('container.upload documents')"
                                      class="flex items-center space-x-4 float-right"
                                      @submit.prevent="handleFileUpload()">

                                    <a  v-if="containerDocumentsRecords.some(doc => doc.document_name === 'Manifest')" :href="route('loading.containers-documents.download', containerDocumentsRecords.find(doc => doc.document_name === 'Manifest').id)">
                                        <svg   class="icon icon-tabler icons-tabler-outline icon-tabler-download"  fill="none"  height="24"  stroke="currentColor"  stroke-linecap="round"  stroke-linejoin="round"  stroke-width="2"  viewBox="0 0 24 24"  width="24"  xmlns="http://www.w3.org/2000/svg"><path d="M0 0h24v24H0z" fill="none" stroke="none"/><path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2" /><path d="M7 11l5 5l5 -5" /><path d="M12 4l0 12" /></svg>
                                    </a>

                                    <input ref="manifestDocumentInput" hidden type="file"
                                           @input="handleFileInput($event, 'manifestDocument')"/>

                                    <svg
                                        class="icon icon-tabler icons-tabler-outline icon-tabler-upload hover:cursor-pointer"
                                        fill="none" height="24" stroke="currentColor" stroke-linecap="round"
                                        stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" width="24"
                                        xmlns="http://www.w3.org/2000/svg"
                                        @click.prevent="selectNewDoc('manifestDocumentInput')">
                                        <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                                        <path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2"/>
                                        <path d="M7 9l5 -5l5 5"/>
                                        <path d="M12 4l0 12"/>
                                    </svg>

                                    <button
                                        v-show="form.document_name === 'Manifest'"
                                        class="btn py-1 font-medium text-primary hover:bg-primary/20 focus:bg-primary/20 active:bg-primary/25 dark:text-accent-light dark:hover:bg-accent-light/20 dark:focus:bg-accent-light/20 dark:active:bg-accent-light/25"
                                        type="submit"
                                    >
                                        Save
                                    </button>

                                    <template v-if="$page.props.user.permissions.includes('container.delete documents')">
                                        <button
                                            v-if="containerDocumentsRecords.some(doc => doc.document_name === 'Manifest')"
                                            class="btn size-7 rounded-full bg-slate-150 p-0 font-medium text-slate-800 hover:bg-slate-200 hover:shadow-lg hover:shadow-slate-200/50 focus:bg-slate-200 focus:shadow-lg focus:shadow-slate-200/50 active:bg-slate-200/80 dark:bg-navy-500 dark:text-navy-50 dark:hover:bg-navy-450 dark:hover:shadow-navy-450/50 dark:focus:bg-navy-450 dark:focus:shadow-navy-450/50 dark:active:bg-navy-450/90"
                                            @click.prevent="confirmDeleteDoc(containerDocumentsRecords.find(doc => doc.document_name === 'Manifest').id, 'Manifest')"
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
                                    v-if="containerDocumentsRecords.some(doc => doc.document_name === 'Receipt for Freight Charges')"
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

                                Receipt for Freight Charges
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 rounded-r-lg sm:px-5">
                                <form v-if="$page.props.user.permissions.includes('container.upload documents')"
                                      class="flex items-center space-x-4 float-right"
                                      @submit.prevent="handleFileUpload()">

                                    <a  v-if="containerDocumentsRecords.some(doc => doc.document_name === 'Receipt for Freight Charges')" :href="route('loading.containers-documents.download', containerDocumentsRecords.find(doc => doc.document_name === 'Receipt for Freight Charges').id)">
                                        <svg   class="icon icon-tabler icons-tabler-outline icon-tabler-download"  fill="none"  height="24"  stroke="currentColor"  stroke-linecap="round"  stroke-linejoin="round"  stroke-width="2"  viewBox="0 0 24 24"  width="24"  xmlns="http://www.w3.org/2000/svg"><path d="M0 0h24v24H0z" fill="none" stroke="none"/><path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2" /><path d="M7 11l5 5l5 -5" /><path d="M12 4l0 12" /></svg>
                                    </a>

                                    <input ref="receiptDocumentInput" hidden type="file"
                                           @input="handleFileInput($event, 'receiptDocument')"/>

                                    <svg
                                        class="icon icon-tabler icons-tabler-outline icon-tabler-upload hover:cursor-pointer"
                                        fill="none" height="24" stroke="currentColor" stroke-linecap="round"
                                        stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" width="24"
                                        xmlns="http://www.w3.org/2000/svg"
                                        @click.prevent="selectNewDoc('receiptDocumentInput')">
                                        <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                                        <path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2"/>
                                        <path d="M7 9l5 -5l5 5"/>
                                        <path d="M12 4l0 12"/>
                                    </svg>

                                    <button
                                        v-show="form.document_name === 'Receipt for Freight Charges'"
                                        class="btn py-1 font-medium text-primary hover:bg-primary/20 focus:bg-primary/20 active:bg-primary/25 dark:text-accent-light dark:hover:bg-accent-light/20 dark:focus:bg-accent-light/20 dark:active:bg-accent-light/25"
                                        type="submit"
                                    >
                                        Save
                                    </button>

                                    <template v-if="$page.props.user.permissions.includes('container.delete documents')">
                                        <button
                                            v-if="containerDocumentsRecords.some(doc => doc.document_name === 'Receipt for Freight Charges')"
                                            class="btn size-6 rounded-full bg-slate-150 p-0 font-medium text-slate-800 hover:bg-slate-200 hover:shadow-lg hover:shadow-slate-200/50 focus:bg-slate-200 focus:shadow-lg focus:shadow-slate-200/50 active:bg-slate-200/80 dark:bg-navy-500 dark:text-navy-50 dark:hover:bg-navy-450 dark:hover:shadow-navy-450/50 dark:focus:bg-navy-450 dark:focus:shadow-navy-450/50 dark:active:bg-navy-450/90"
                                            @click.prevent="confirmDeleteDoc(containerDocumentsRecords.find(doc => doc.document_name === 'Receipt for Freight Charges').id, 'Receipt for Freight Charges')"
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
        <DeleteDocConfirmationModal :doc-name="docName" :show="showConfirmDeleteDocModal" @close="closeDeleteModal"
                                    @delete-doc="handleDeleteDoc"/>
    </Tab>
</template>
