<script setup>
import Tab from "@/Components/Tab.vue";
import {ref} from "vue";
import {router, useForm, usePage} from "@inertiajs/vue3";
import {push} from "notivue";
import DeleteDocConfirmationModal from "@/Pages/Loading/Partials/DeleteDocConfirmationModal.vue";
//my vue file
import vueFilePond from "vue-filepond";
import 'filepond/dist/filepond.min.css';
import 'filepond-plugin-image-preview/dist/filepond-plugin-image-preview.min.css';
import FilePondPluginFileValidateType from "filepond-plugin-file-validate-type";
import FilePondPluginImagePreview from "filepond-plugin-image-preview";


const props = defineProps({
    containerId: {
        type: Number,
        required: true,
    }
});

const FilePond = vueFilePond(
    FilePondPluginFileValidateType,
    FilePondPluginImagePreview
);
const myFiles = ref([]);
const csrfToken = usePage().props.csrf;

const containerDocumentsRecords = ref([]);

const fetchContainerDocuments = async () => {
    try {
        const response = await fetch(`containers/get-container-documents/${props.containerId}`, {
            method: "GET",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": usePage().props.csrf,
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
        console.log("File uploaded:", form.document.name);
        console.log("Document type:", docType);
        console.log("Document name set as:", form.document_name);
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

const fileUploads = () => {
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
const verifyContainerDocuments = async (event) => {
    const isChecked = event.target.checked; // Checkbox value
    // console.log('Checkbox value:', isChecked);
    try {
        const response = await fetch(`loaded-containers/verify`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": usePage().props.csrf, // CSRF token
            },
            body: JSON.stringify({
                containerId: props.containerId, // Container ID
                isChecked: isChecked,     // Checkbox value
            }),
        });

        if (!response.ok) {
            throw new Error('Network response was not ok.');
        }else {
            const data = await response.json();
            push.success(data.message);
        }

    } catch (error) {
        console.error( error.message);

    }
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
                                <label class="inline-flex items-center space-x-2">
                                    <input :disabled="$page.props.currentBranch.type === 'Destination'"
                                           v-if="containerDocumentsRecords.some(doc => doc.document_name === 'BL From Shipping Line')"
                                           class="form-switch h-5 w-10 rounded-full bg-slate-300 before:rounded-full before:bg-slate-50 checked:bg-primary checked:before:bg-white dark:bg-navy-900 dark:before:bg-navy-300 dark:checked:bg-accent dark:checked:before:bg-white"
                                           type="checkbox"
                                           :checked="containerDocumentsRecords.find(doc => doc.document_name === 'BL From Shipping Line').is_verified === 1"
                                           @change="verifyContainerDocuments($event)"

                                    />
                                </label>
                            </td>

                            <td class="whitespace-nowrap px-4 py-3 rounded-r-lg sm:px-5">

                                <form v-if="$page.props.user.permissions.includes('container.upload documents')"
                                      class="flex items-center space-x-4 float-right"
                                      @submit.prevent="handleFileUpload">

                                    <a v-if="containerDocumentsRecords.some(doc => doc.document_name === 'BL From Shipping Line')"
                                       :href="route('loading.containers-documents.download', containerDocumentsRecords.find(doc => doc.document_name === 'BL From Shipping Line').id)">
                                        <svg class="icon icon-tabler icons-tabler-outline icon-tabler-download"
                                             fill="none" height="24" stroke="currentColor" stroke-linecap="round"
                                             stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" width="24"
                                             xmlns="http://www.w3.org/2000/svg">
                                            <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                                            <path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2"/>
                                            <path d="M7 11l5 5l5 -5"/>
                                            <path d="M12 4l0 12"/>
                                        </svg>
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

                                    <template
                                        v-if="$page.props.user.permissions.includes('container.delete documents')">
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
                                <label class="inline-flex items-center space-x-2">
                                    <input :disabled="$page.props.currentBranch.type === 'Destination'"
                                           class="form-switch h-5 w-10 rounded-full bg-slate-300 before:rounded-full before:bg-slate-50 checked:bg-primary checked:before:bg-white dark:bg-navy-900 dark:before:bg-navy-300 dark:checked:bg-accent dark:checked:before:bg-white"
                                           type="checkbox"

                                    />
                                </label>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 rounded-r-lg sm:px-5">
                                <form v-if="$page.props.user.permissions.includes('container.upload documents')"
                                      class="flex items-center space-x-4 float-right"
                                      @submit.prevent="handleFileUpload()">

                                    <a v-if="containerDocumentsRecords.some(doc => doc.document_name === 'Manifest')"
                                       :href="route('loading.containers-documents.download', containerDocumentsRecords.find(doc => doc.document_name === 'Manifest').id)">
                                        <svg class="icon icon-tabler icons-tabler-outline icon-tabler-download"
                                             fill="none" height="24" stroke="currentColor" stroke-linecap="round"
                                             stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" width="24"
                                             xmlns="http://www.w3.org/2000/svg">
                                            <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                                            <path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2"/>
                                            <path d="M7 11l5 5l5 -5"/>
                                            <path d="M12 4l0 12"/>
                                        </svg>
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

                                    <template
                                        v-if="$page.props.user.permissions.includes('container.delete documents')">
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
                                <label class="inline-flex items-center space-x-2">
                                    <input :disabled="$page.props.currentBranch.type === 'Destination'"
                                        class="form-switch h-5 w-10 rounded-full bg-slate-300 before:rounded-full before:bg-slate-50 checked:bg-primary checked:before:bg-white dark:bg-navy-900 dark:before:bg-navy-300 dark:checked:bg-accent dark:checked:before:bg-white"
                                        type="checkbox"
                                    />
                                </label>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 rounded-r-lg sm:px-5">
                                <form v-if="$page.props.user.permissions.includes('container.upload documents')"
                                      class="flex items-center space-x-4 float-right"
                                      @submit.prevent="handleFileUpload()">

                                    <a v-if="containerDocumentsRecords.some(doc => doc.document_name === 'Receipt for Freight Charges')"
                                       :href="route('loading.containers-documents.download', containerDocumentsRecords.find(doc => doc.document_name === 'Receipt for Freight Charges').id)">
                                        <svg class="icon icon-tabler icons-tabler-outline icon-tabler-download"
                                             fill="none" height="24" stroke="currentColor" stroke-linecap="round"
                                             stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" width="24"
                                             xmlns="http://www.w3.org/2000/svg">
                                            <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                                            <path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2"/>
                                            <path d="M7 11l5 5l5 -5"/>
                                            <path d="M12 4l0 12"/>
                                        </svg>
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

                                    <template
                                        v-if="$page.props.user.permissions.includes('container.delete documents')">
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
                    <div class="filepond-container">
                        <file-pond
                            name="files"
                            ref="pond"
                            label-idle="Drop files here or <span class='filepond--label-action'>Browse</span>"
                            allow-multiple="true"
                            accepted-file-types="image/jpeg, image/png, application/pdf"
                            :server="{
                                url: `/any-file-manager/${containerId}`,
                                process: {
                                  headers: {
                                    'X-CSRF-TOKEN': csrfToken,
                                  },
                                },
                              }"
                            style="border: 2px dashed #e2e7ee; border-radius: 2px; padding: 2px;"
                            @init="onFilePondInit"
                            @processfile="onFileUploadComplete"
                        />
                    </div>
                    <div class="mt-8">
                        <h2 class="text-base font-medium tracking-wide text-slate-800 line-clamp-1 dark:text-navy-100 mb-4">
                            Uploaded Files
                        </h2>
                        <div class="overflow-x-auto">
                            <table class="is-hoverable w-full text-left">
                                <thead>
                                <tr class="border-y border-transparent border-b-slate-200 dark:border-b-navy-500">
                                    <th class="whitespace-nowrap px-4 py-3 font-semibold text-slate-800 dark:text-navy-100">
                                        File Name
                                    </th>
                                    <th class="whitespace-nowrap px-4 py-3 font-semibold text-slate-800 dark:text-navy-100 text-right">
                                        Actions
                                    </th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr
                                    v-for="file in containerDocumentsRecords"
                                    :key="file.id"
                                    class="border-y border-transparent border-b-slate-200 dark:border-b-navy-500"
                                >
                                    <td class="whitespace-nowrap px-4 py-3">
                                        {{ file.document_name }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-right">
                                        <!-- Download Button -->
                                        <a
                                            :href="route('loading.containers-documents.download', file.id  )"
                                            download
                                            class="btn h-8 w-8 rounded-full p-0 hover:bg-slate-300/20 focus:bg-slate-300/20 active:bg-slate-300/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25"
                                            title="Download"
                                        >
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                class="h-5 w-5"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"
                                                />
                                            </svg>
                                        </a>
                                    </td>
                                </tr>
                                <tr v-if="containerDocumentsRecords.length === 0">
                                    <td
                                        colspan="2"
                                        class="px-4 py-3 text-center text-slate-500 dark:text-navy-200"
                                    >
                                        No files uploaded yet
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>


                </div>

            </div>
        </div>
        <DeleteDocConfirmationModal :doc-name="docName" :show="showConfirmDeleteDocModal" @close="closeDeleteModal"
                                    @delete-doc="handleDeleteDoc"/>
    </Tab>
</template>
