<script setup>
import { ref, computed } from "vue";
import { router, useForm, usePage } from "@inertiajs/vue3";
import { push } from "notivue";
import vueFilePond from "vue-filepond";
import 'filepond/dist/filepond.min.css';
import 'filepond-plugin-image-preview/dist/filepond-plugin-image-preview.min.css';
import FilePondPluginFileValidateType from "filepond-plugin-file-validate-type";
import FilePondPluginImagePreview from "filepond-plugin-image-preview";
import Button from 'primevue/button';
import {useConfirm} from "primevue/useconfirm";

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

const csrfToken = usePage().props.csrf;
const confirm = useConfirm();
const containerDocumentsRecords = ref([]);
const blDocumentInput = ref(null);
const manifestDocumentInput = ref(null);
const receiptDocumentInput = ref(null);
const currentBranch = computed(() => usePage().props.currentBranch.type);

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

fetchContainerDocuments();

const onFileUploadComplete = (error, file) => {
    if (!error) {
        console.log("File uploaded successfully:", file);
        fetchContainerDocuments();
    } else {
        console.error("File upload failed:", error);
    }
};

const selectNewDoc = (refName) => {
    if (refName === 'blDocumentInput') {
        blDocumentInput.value.click();
    } else if (refName === 'manifestDocumentInput') {
        manifestDocumentInput.value.click();
    } else {
        receiptDocumentInput.value.click();
    }
};

const form = useForm({
    container_id: props.containerId,
    document_name: null,
    document: null,
})

const handleFileInput = (event, docType) => {
    form.document = event.target.files[0];

    if (docType === 'blDocument') {
        form.document_name = 'BL From Shipping Line';
    } else if (docType === 'manifestDocument') {
        form.document_name = 'Manifest';
    } else {
        form.document_name = 'Receipt for Freight Charges';
    }

    if (form.document) {
        form.post(route('loading.containers.upload.document'), {
            preserveScroll: true,
            onSuccess: () => {
                push.success('Document Uploaded!');
                form.reset();
                fetchContainerDocuments();
            },
            onError: () => {
                push.error('Something went wrong!');
            }
        });
    }
};

const handleDeleteDoc = (id, name) => {
    confirm.require({
        message: 'Would you like to delete this document?',
        header: `Delete ${name}?`,
        icon: 'pi pi-info-circle',
        rejectLabel: 'Cancel',
        rejectProps: {
            label: 'Cancel',
            severity: 'secondary',
            outlined: true
        },
        acceptProps: {
            label: 'Delete',
            severity: 'danger'
        },
        accept: () => {
            router.delete(route("loading.containers.destroy.document", id), {
                preserveScroll: true,
                onSuccess: () => {
                    push.success('Document Deleted Successfully!');
                    fetchContainerDocuments();
                },
            });
        },
        reject: () => {
        }
    });
};

const verifyContainerDocuments = (docId) => {
    confirm.require({
        message: 'Would you like to verify this document?',
        header: `Verify?`,
        icon: 'pi pi-info-circle',
        rejectLabel: 'Cancel',
        rejectProps: {
            label: 'Cancel',
            severity: 'secondary',
            outlined: true
        },
        acceptProps: {
            label: 'Verify',
            severity: 'success'
        },
        accept: async () => {
            try {
                const response = await fetch(`loaded-containers/verify`, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": usePage().props.csrf,
                    },
                    body: JSON.stringify({
                        containerId: docId,
                        isChecked: true,
                    }),
                });

                if (!response.ok) {
                    throw new Error('Network response was not ok.');
                } else {
                    push.success('Document Verified!');
                    await fetchContainerDocuments();
                }

            } catch (error) {
                console.error(error.message);
            }
        },
        reject: () => {
        }
    });
};

const filteredFiles = computed(() => {
    if (currentBranch.value === 'Destination') {
        return containerDocumentsRecords.value.filter(file => file.is_verified === 1);
    }
    return containerDocumentsRecords.value;
});
</script>

<template>
    <div class="grid grid-cols-1">
        <div class="col-span-1 sm:col-span-2">
            <div class="is-scrollbar-hidden min-w-full overflow-x-auto mt-5">
                <table class="is-hoverable w-full text-left">
                    <tbody>
                    <tr v-if="!containerDocumentsRecords.some(file => file.document_name === 'BL From Shipping Line')">
                        <td class="whitespace-nowrap rounded-l-lg px-4 py-3 sm:px-5 flex items-center">
                            <i class="ti ti-file-unknown text-2xl text-warning mr-3"></i>
                            BL From Shipping Line
                        </td>

                        <td class="whitespace-nowrap px-4 py-3 rounded-r-lg sm:px-5">
                            <div v-if="$page.props.user.permissions.includes('container.upload documents')"
                                  class="flex items-center space-x-4 float-right">

                                <input ref="blDocumentInput" hidden type="file"
                                       @input="handleFileInput($event, 'blDocument')"/>

                                <i class="ti ti-cloud-upload text-2xl hover:cursor-pointer" @click.prevent="selectNewDoc('blDocumentInput')"></i>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="!containerDocumentsRecords.some(file => file.document_name === 'Manifest')">
                        <td class="whitespace-nowrap rounded-l-lg px-4 py-3 sm:px-5 flex items-center">
                            <i class="ti ti-file-unknown text-2xl text-warning mr-3"></i>
                            Manifest
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 rounded-r-lg sm:px-5">
                            <div v-if="$page.props.user.permissions.includes('container.upload documents')"
                                  class="flex items-center space-x-4 float-right">

                                <input ref="manifestDocumentInput" hidden type="file"
                                       @input="handleFileInput($event, 'manifestDocument')"/>

                                <i class="ti ti-cloud-upload text-2xl hover:cursor-pointer" @click.prevent="selectNewDoc('manifestDocumentInput')"></i>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="!containerDocumentsRecords.some(file => file.document_name === 'Receipt for Freight Charges')">
                        <td class="whitespace-nowrap rounded-l-lg px-4 py-3 sm:px-5 flex items-center">
                            <i class="ti ti-file-unknown text-2xl text-warning mr-3"></i>
                            Receipt for Freight Charges
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 rounded-r-lg sm:px-5">
                            <div v-if="$page.props.user.permissions.includes('container.upload documents')"
                                  class="flex items-center space-x-4 float-right">

                                <input ref="receiptDocumentInput" hidden type="file"
                                       @input="handleFileInput($event, 'receiptDocument')"/>

                                <i class="ti ti-cloud-upload text-2xl hover:cursor-pointer" @click.prevent="selectNewDoc('receiptDocumentInput')"></i>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>
                <div class="filepond-container" style="margin: 20px 0; padding: 10px;" >
                    <file-pond
                        ref="pond"
                        :server="{
                                url: `/any-file-manager/${containerId}`,
                                process: {
                                  headers: {
                                    'X-CSRF-TOKEN': csrfToken,
                                  },
                                },
                              }"
                        accepted-file-types="image/jpeg, image/png, application/pdf, text/csv, application/vnd.ms-excel, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, text/plain"
                        allow-multiple="true"
                        label-idle="Drop files here or <span class='filepond--label-action'>Browse</span>"
                        name="files"
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
                            <tbody>
                            <tr
                                v-for="file in filteredFiles"
                                :key="file.id"
                                class="border-y border-transparent border-b-slate-200 dark:border-b-navy-500"
                            >
                                <td class="flex items-center">
                                    <i v-if="file.is_verified" v-tooltip="'Verified'" class="ti ti-file-certificate text-2xl mr-3 text-success"></i>
                                    <i v-else class="ti ti-file text-2xl mr-3"></i>
                                    {{ file.document_name }}
                                </td>
                                <td class="whitespace-nowrap space-x-2 text-right">
                                    <a
                                        v-if="$page.props.user.permissions.includes('container.download documents')"
                                        :href="route('loading.containers-documents.download', file.id)"
                                    >
                                        <Button v-tooltip="'Download'" icon="pi pi-download" rounded size="small" variant="text" />
                                    </a>

                                    <Button v-if="!file.is_verified" v-tooltip="'Verify'" :disabled="currentBranch === 'Destination'" icon="pi pi-file-check" rounded severity="info" size="small" variant="text" @click.prevent="verifyContainerDocuments(file.id)" />

                                    <Button v-tooltip="'Delete'" :disabled="!$page.props.user.permissions.includes('container.delete documents')" aria-label="Cancel" icon="pi pi-trash" rounded severity="danger" size="small" variant="text" @click.prevent="handleDeleteDoc(file.id, file.document_name)" />
                                </td>
                            </tr>
                            <tr class="border-y border-transparent border-b-slate-200 dark:border-b-navy-500">
                                <td class="flex items-center">
                                    <i class="ti ti-file text-2xl mr-3"></i>
                                    Loading Point Document
                                </td>
                                <td class="whitespace-nowrap space-x-2 text-right">
                                    <a
                                        v-if="$page.props.user.permissions.includes('hbls.download pdf')"
                                        :href="route('loading.loaded-containers.download-loading', containerId)">
                                        <Button aria-label="Cancel" icon="pi pi-download" rounded size="small" variant="text" />
                                    </a>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
