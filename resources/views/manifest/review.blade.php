<x-base-layout title="Review Manifest Data">
<div class="container mx-auto px-4 py-8">
    <div class="max-w-7xl mx-auto">
        <div class="bg-white shadow-lg rounded-lg overflow-hidden">
            <div class="bg-blue-600 text-white px-6 py-4">
                <h1 class="text-2xl font-bold">Review Extracted Manifest Data</h1>
                <p class="text-blue-100 mt-1">Please review the extracted data before confirming import</p>
            </div>

            <div class="p-6">
                @if(session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                        {{ session('success') }}
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

                <!-- Summary Section -->
                <div class="bg-gray-50 rounded-lg p-6 mb-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Import Summary</h2>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-blue-600">{{ $summary['total'] }}</div>
                            <div class="text-sm text-gray-600">Total Rows</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-green-600">{{ $summary['processed'] }}</div>
                            <div class="text-sm text-gray-600">Processed</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-yellow-600">{{ $summary['pending'] }}</div>
                            <div class="text-sm text-gray-600">Pending</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-red-600">{{ $summary['errors'] }}</div>
                            <div class="text-sm text-gray-600">Errors</div>
                        </div>
                    </div>
                </div>

                <!-- Data Type Summary -->
                @if(!empty($summary['by_type']))
                <div class="bg-white border rounded-lg p-4 mb-6">
                    <h3 class="text-md font-semibold text-gray-800 mb-3">Data Types Found</h3>
                    <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
                        @foreach($summary['by_type'] as $type => $count)
                        <div class="text-center bg-gray-50 p-3 rounded">
                            <div class="text-lg font-bold text-gray-700">{{ $count }}</div>
                            <div class="text-xs text-gray-600 capitalize">{{ str_replace('_', ' ', $type) }}</div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Tabs for different data types -->
                <div class="border-b border-gray-200 mb-6">
                    <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                        <button class="tab-button active border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm" data-tab="headers">
                            Headers ({{ $groupedData['headers']->count() }})
                        </button>
                        <button class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm" data-tab="containers">
                            Containers ({{ $groupedData['containers']->count() }})
                        </button>
                        <button class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm" data-tab="hbls">
                            HBLs ({{ $groupedData['hbls']->count() }})
                        </button>
                        <button class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm" data-tab="packages">
                            Packages ({{ $groupedData['packages']->count() }})
                        </button>
                        <button class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm" data-tab="unknown">
                            Unknown ({{ $groupedData['unknown']->count() }})
                        </button>
                    </nav>
                </div>

                <!-- Tab Content -->
                <div class="tab-content">
                    <!-- Headers Tab -->
                    <div id="headers-tab" class="tab-pane active">
                        <h3 class="text-lg font-semibold text-gray-800 mb-3">Header Information</h3>
                        @if($groupedData['headers']->count() > 0)
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Row</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">OBL Number</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vessel</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Extracted Text</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($groupedData['headers'] as $row)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $row->row_number }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $row->obl_number ?? '-' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $row->vessel_name ?? '-' }}</td>
                                            <td class="px-6 py-4 text-sm text-gray-900">{{ Str::limit($row->extracted_text, 100) }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-gray-500 text-center py-8">No header data found</p>
                        @endif
                    </div>

                    <!-- Containers Tab -->
                    <div id="containers-tab" class="tab-pane">
                        <h3 class="text-lg font-semibold text-gray-800 mb-3">Container Information</h3>
                        @if($groupedData['containers']->count() > 0)
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Row</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Container Number</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Extracted Text</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($groupedData['containers'] as $row)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $row->row_number }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $row->container_number ?? '-' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $row->container_type ?? '-' }}</td>
                                            <td class="px-6 py-4 text-sm text-gray-900">{{ Str::limit($row->extracted_text, 100) }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-gray-500 text-center py-8">No container data found</p>
                        @endif
                    </div>

                    <!-- HBLs Tab -->
                    <div id="hbls-tab" class="tab-pane">
                        <h3 class="text-lg font-semibold text-gray-800 mb-3">House Bill of Lading Information</h3>
                        @if($groupedData['hbls']->count() > 0)
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Row</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">HBL Number</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Address</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($groupedData['hbls'] as $row)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $row->row_number }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $row->hbl_number ?? '-' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $row->hbl_contact ?? '-' }}</td>
                                            <td class="px-6 py-4 text-sm text-gray-900">{{ Str::limit($row->hbl_address, 80) ?? '-' }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-gray-500 text-center py-8">No HBL data found</p>
                        @endif
                    </div>

                    <!-- Packages Tab -->
                    <div id="packages-tab" class="tab-pane">
                        <h3 class="text-lg font-semibold text-gray-800 mb-3">Package Information</h3>
                        @if($groupedData['packages']->count() > 0)
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Row</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Package Type</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Volume</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($groupedData['packages'] as $row)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $row->row_number }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $row->package_type ?? '-' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $row->package_volume ?? '-' }}</td>
                                            <td class="px-6 py-4 text-sm text-gray-900">{{ $row->package_description ?? '-' }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-gray-500 text-center py-8">No package data found</p>
                        @endif
                    </div>

                    <!-- Unknown Tab -->
                    <div id="unknown-tab" class="tab-pane">
                        <h3 class="text-lg font-semibold text-gray-800 mb-3">Unidentified Data</h3>
                        @if($groupedData['unknown']->count() > 0)
                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
                                <p class="text-yellow-700 text-sm">
                                    These rows could not be automatically categorized. Please review manually.
                                </p>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Row</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Extracted Text</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($groupedData['unknown'] as $row)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $row->row_number }}</td>
                                            <td class="px-6 py-4 text-sm text-gray-900">{{ $row->extracted_text }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-gray-500 text-center py-8">All data was successfully categorized</p>
                        @endif
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-between items-center mt-8 pt-6 border-t border-gray-200">
                    <form action="{{ route('manifest.import.cancel') }}" method="POST" class="inline">
                        @csrf
                        <input type="hidden" name="session" value="{{ $sessionId }}">
                        <button 
                            type="submit" 
                            class="bg-gray-500 hover:bg-gray-600 text-white font-medium py-2 px-6 rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2"
                            onclick="return confirm('Are you sure you want to cancel this import? All extracted data will be deleted.')"
                        >
                            Cancel Import
                        </button>
                    </form>

                    <form action="{{ route('manifest.import.confirm') }}" method="POST" class="inline">
                        @csrf
                        <input type="hidden" name="session" value="{{ $sessionId }}">
                        <button 
                            type="submit" 
                            class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-6 rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2"
                            onclick="return confirm('Are you sure you want to proceed with importing this data into the system?')"
                        >
                            Confirm Import
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .tab-pane {
        display: none;
    }
    .tab-pane.active {
        display: block;
    }
    .tab-button.active {
        border-color: #3B82F6;
        color: #3B82F6;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tab functionality
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabPanes = document.querySelectorAll('.tab-pane');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const tabName = this.getAttribute('data-tab');
            
            // Remove active class from all buttons and panes
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabPanes.forEach(pane => pane.classList.remove('active'));
            
            // Add active class to clicked button and corresponding pane
            this.classList.add('active');
            document.getElementById(tabName + '-tab').classList.add('active');
        });
    });
});
</script>
</x-base-layout>