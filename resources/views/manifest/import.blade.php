<x-base-layout title="Manifest Import">
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white shadow-lg rounded-lg overflow-hidden">
            <div class="bg-blue-600 text-white px-6 py-4">
                <h1 class="text-2xl font-bold">Import Shipping Manifest</h1>
                <p class="text-blue-100 mt-1">Upload an Excel file containing shipping manifest data</p>
            </div>

            <div class="p-6">
                @if(session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('info'))
                    <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded mb-4">
                        {{ session('info') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="mb-8">
                    <h2 class="text-lg font-semibold text-gray-800 mb-3">Instructions</h2>
                    <ul class="list-disc list-inside text-gray-600 space-y-2">
                        <li>Upload an Excel file (.xlsx or .xls) containing shipping manifest data</li>
                        <li>The system will extract and identify different types of data (containers, HBLs, packages, etc.)</li>
                        <li>Review the extracted data before confirming the import</li>
                        <li>The data will be stored in temporary tables for validation before final import</li>
                    </ul>
                </div>

                <form action="{{ route('manifest.import.upload') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:border-gray-400 transition-colors">
                        <div class="space-y-4">
                            <div class="mx-auto w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                </svg>
                            </div>
                            
                            <div>
                                <label for="manifest_file" class="block text-sm font-medium text-gray-700 mb-2">
                                    Select Manifest Excel File
                                </label>
                                <input 
                                    type="file" 
                                    name="manifest_file" 
                                    id="manifest_file" 
                                    accept=".xlsx,.xls" 
                                    required
                                    class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                                >
                            </div>
                            
                            <p class="text-xs text-gray-500">
                                Maximum file size: 10MB. Supported formats: .xlsx, .xls
                            </p>
                        </div>
                    </div>

                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-yellow-800">Important Note</h3>
                                <div class="mt-2 text-sm text-yellow-700">
                                    <p>This will upload and process the manifest data. You will have a chance to review all extracted data before confirming the final import into the system.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <button 
                            type="submit" 
                            class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                        >
                            Upload and Process File
                        </button>
                    </div>
                </form>

                <div class="mt-8 pt-6 border-t border-gray-200">
                    <h3 class="text-lg font-medium text-gray-800 mb-3">Sample Data Format</h3>
                    <p class="text-sm text-gray-600 mb-3">
                        The system expects Excel files with shipping manifest data containing information about:
                    </p>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 text-sm">
                        <div class="bg-gray-50 p-3 rounded">
                            <h4 class="font-medium text-gray-800">Header Info</h4>
                            <p class="text-gray-600">OBL number, vessel details, shipper info</p>
                        </div>
                        <div class="bg-gray-50 p-3 rounded">
                            <h4 class="font-medium text-gray-800">Containers</h4>
                            <p class="text-gray-600">Container numbers, types, specifications</p>
                        </div>
                        <div class="bg-gray-50 p-3 rounded">
                            <h4 class="font-medium text-gray-800">HBL Data</h4>
                            <p class="text-gray-600">House Bill of Lading information</p>
                        </div>
                        <div class="bg-gray-50 p-3 rounded">
                            <h4 class="font-medium text-gray-800">Packages</h4>
                            <p class="text-gray-600">Package details, weights, volumes</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Custom file input styling */
    input[type="file"]::-webkit-file-upload-button {
        visibility: hidden;
    }
    
    input[type="file"]::before {
        content: 'Choose File';
        display: inline-block;
        background: linear-gradient(top, #f9f9f9, #e3e3e3);
        border: 1px solid #999;
        border-radius: 3px;
        padding: 5px 8px;
        outline: none;
        white-space: nowrap;
        cursor: pointer;
        text-shadow: 1px 1px #fff;
        font-weight: 700;
        font-size: 10pt;
    }
    
    input[type="file"]:hover::before {
        border-color: black;
    }
    
    input[type="file"]:active::before {
        background: -webkit-linear-gradient(top, #e3e3e3, #f9f9f9);
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('manifest_file');
    const form = fileInput.closest('form');
    
    fileInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            // Validate file size (10MB)
            const maxSize = 10 * 1024 * 1024;
            if (file.size > maxSize) {
                alert('File size must be less than 10MB');
                e.target.value = '';
                return;
            }
            
            // Validate file type
            const validTypes = [
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/vnd.ms-excel'
            ];
            if (!validTypes.includes(file.type)) {
                alert('Please select a valid Excel file (.xlsx or .xls)');
                e.target.value = '';
                return;
            }
        }
    });
    
    form.addEventListener('submit', function(e) {
        const submitButton = form.querySelector('button[type="submit"]');
        submitButton.disabled = true;
        submitButton.innerHTML = 'Processing...';
    });
});
</script>
</x-base-layout>