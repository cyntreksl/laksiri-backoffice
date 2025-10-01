<?php

namespace App\Http\Controllers;

use App\Models\ExtractedManifestRow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class ManifestImportController extends Controller
{
    /**
     * Show the manifest import form
     */
    public function index()
    {
        return view('manifest.import');
    }

    /**
     * Handle the Excel file upload and initial processing
     */
    public function upload(Request $request)
    {
        $request->validate([
            'manifest_file' => 'required|mimes:xlsx,xls|max:10240', // 10MB max
        ]);

        try {
            $file = $request->file('manifest_file');
            $sessionId = Str::uuid()->toString();
            
            // Store session ID for tracking
            Session::put('manifest_import_session', $sessionId);
            
            // Process the Excel file
            $this->extractDataFromExcel($file, $sessionId);
            
            return redirect()->route('manifest.import.review', ['session' => $sessionId])
                ->with('success', 'Manifest file uploaded successfully. Please review the extracted data.');
                
        } catch (\Exception $e) {
            Log::error('Manifest import error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->withErrors(['manifest_file' => 'Error processing file: ' . $e->getMessage()]);
        }
    }

    /**
     * Show the review page with extracted data
     */
    public function review(Request $request)
    {
        $sessionId = $request->get('session') ?? Session::get('manifest_import_session');
        
        if (!$sessionId) {
            return redirect()->route('manifest.import.index')
                ->withErrors(['error' => 'No import session found. Please upload a file first.']);
        }

        // Get summary of extracted data
        $summary = ExtractedManifestRow::getSessionSummary($sessionId);
        
        // Get sample data for review
        $extractedRows = ExtractedManifestRow::bySession($sessionId)
            ->orderBy('row_number')
            ->get();

        $groupedData = [
            'headers' => $extractedRows->where('row_type', 'header'),
            'containers' => $extractedRows->where('row_type', 'container'),
            'hbls' => $extractedRows->where('row_type', 'hbl'),
            'packages' => $extractedRows->where('row_type', 'package'),
            'consignees' => $extractedRows->where('row_type', 'consignee'),
            'unknown' => $extractedRows->where('row_type', 'unknown'),
        ];

        return view('manifest.review', compact('summary', 'groupedData', 'sessionId'));
    }

    /**
     * Confirm and process the extracted data into final tables
     */
    public function confirm(Request $request)
    {
        $sessionId = $request->get('session') ?? Session::get('manifest_import_session');
        
        if (!$sessionId) {
            return redirect()->route('manifest.import.index')
                ->withErrors(['error' => 'No import session found.']);
        }

        try {
            // Here you would implement the logic to create actual HBL, container, and package records
            // For now, we'll just mark the rows as processed
            
            ExtractedManifestRow::bySession($sessionId)
                ->unprocessed()
                ->update([
                    'is_processed' => true,
                    'processing_status' => 'processed',
                    'processing_notes' => 'Confirmed and imported by user'
                ]);

            // Clear the session
            Session::forget('manifest_import_session');

            return redirect()->route('manifest.import.index')
                ->with('success', 'Manifest data has been imported successfully!');
                
        } catch (\Exception $e) {
            Log::error('Manifest confirm error: ' . $e->getMessage());
            
            return back()->withErrors(['error' => 'Error confirming import: ' . $e->getMessage()]);
        }
    }

    /**
     * Cancel the import and clean up
     */
    public function cancel(Request $request)
    {
        $sessionId = $request->get('session') ?? Session::get('manifest_import_session');
        
        if ($sessionId) {
            // Delete the extracted rows
            ExtractedManifestRow::bySession($sessionId)->delete();
        }
        
        Session::forget('manifest_import_session');
        
        return redirect()->route('manifest.import.index')
            ->with('info', 'Import cancelled and data cleaned up.');
    }

    /**
     * Extract data from Excel file using PhpSpreadsheet
     */
    private function extractDataFromExcel($file, $sessionId)
    {
        $spreadsheet = IOFactory::load($file->getPathname());
        $worksheet = $spreadsheet->getActiveSheet();
        $fileName = $file->getClientOriginalName();
        
        $rowNumber = 0;
        foreach ($worksheet->getRowIterator() as $row) {
            $rowNumber++;
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);
            
            $rowData = [];
            $extractedText = '';
            
            foreach ($cellIterator as $cell) {
                $value = $cell->getCalculatedValue();
                
                // Handle dates
                if (Date::isDateTime($cell)) {
                    $value = Date::excelToDateTimeObject($value)->format('Y-m-d');
                }
                
                $rowData[] = $value;
                if (!empty($value)) {
                    $extractedText .= $value . ' ';
                }
            }
            
            $extractedText = trim($extractedText);
            
            // Skip empty rows
            if (empty($extractedText)) {
                continue;
            }
            
            // Analyze row type and extract relevant data
            $analyzedData = $this->analyzeRowContent($extractedText, $rowData);
            
            ExtractedManifestRow::create([
                'session_id' => $sessionId,
                'file_name' => $fileName,
                'row_number' => $rowNumber,
                'row_type' => $analyzedData['type'],
                'raw_data' => $rowData,
                'extracted_text' => $extractedText,
                'obl_number' => $analyzedData['obl_number'] ?? null,
                'vessel_name' => $analyzedData['vessel_name'] ?? null,
                'shipper_info' => $analyzedData['shipper_info'] ?? null,
                'consignee_info' => $analyzedData['consignee_info'] ?? null,
                'container_number' => $analyzedData['container_number'] ?? null,
                'container_type' => $analyzedData['container_type'] ?? null,
                'hbl_number' => $analyzedData['hbl_number'] ?? null,
                'hbl_name' => $analyzedData['hbl_name'] ?? null,
                'hbl_contact' => $analyzedData['hbl_contact'] ?? null,
                'hbl_address' => $analyzedData['hbl_address'] ?? null,
                'package_type' => $analyzedData['package_type'] ?? null,
                'package_quantity' => $analyzedData['package_quantity'] ?? null,
                'package_weight' => $analyzedData['package_weight'] ?? null,
                'package_volume' => $analyzedData['package_volume'] ?? null,
                'package_description' => $analyzedData['package_description'] ?? null,
            ]);
        }
        
        Log::info("Processed {$rowNumber} rows from manifest file: {$fileName}");
    }

    /**
     * Analyze row content to determine type and extract relevant data
     */
    private function analyzeRowContent($text, $rowData)
    {
        $text = strtoupper($text);
        $result = ['type' => 'unknown'];
        
        // Check for OBL/Header information
        if (str_contains($text, 'OBL') || str_contains($text, 'UNIVERSAL FREIGHT') || str_contains($text, 'CARGO MANIFEST')) {
            $result['type'] = 'header';
            
            // Extract OBL number if present
            if (preg_match('/OBL[:\s]*([A-Z0-9]+)/', $text, $matches)) {
                $result['obl_number'] = $matches[1];
            }
            
            // Extract vessel name
            if (str_contains($text, 'VESSEL')) {
                if (preg_match('/VESSEL[:\s]*([A-Z\s]+)/', $text, $matches)) {
                    $result['vessel_name'] = trim($matches[1]);
                }
            }
        }
        
        // Check for container information
        elseif (str_contains($text, 'CONTAINER TYPE') || preg_match('/[A-Z]{4}[0-9]{7}/', $text)) {
            $result['type'] = 'container';
            
            // Extract container number (pattern: 4 letters + 7 digits)
            if (preg_match('/([A-Z]{4}[0-9]{7})/', $text, $matches)) {
                $result['container_number'] = $matches[1];
            }
            
            // Extract container type
            if (str_contains($text, '40') && str_contains($text, 'HC')) {
                $result['container_type'] = '40\' HC';
            } elseif (str_contains($text, '20')) {
                $result['container_type'] = '20\'';
            }
        }
        
        // Check for HBL information
        elseif (str_contains($text, 'PP NO:') || str_contains($text, 'P.O.BOX') || str_contains($text, 'DOHA QATAR')) {
            $result['type'] = 'hbl';
            
            // Extract PP number (HBL number)
            if (preg_match('/PP NO[:\s]*([A-Z0-9]+)/', $text, $matches)) {
                $result['hbl_number'] = $matches[1];
            }
            
            // Extract contact information
            if (preg_match('/([0-9\+\-\s]{8,})/', $text, $matches)) {
                $result['hbl_contact'] = trim($matches[1]);
            }
            
            // Extract address
            if (str_contains($text, 'P.O.BOX') || str_contains($text, 'DOHA QATAR')) {
                $result['hbl_address'] = $text;
            }
        }
        
        // Check for package information
        elseif (str_contains($text, 'PERSONAL EFFECTS') || str_contains($text, 'CMB') || str_contains($text, 'PAID')) {
            $result['type'] = 'package';
            
            // Extract package type
            if (str_contains($text, 'PERSONAL EFFECTS')) {
                $result['package_type'] = 'PERSONAL EFFECTS';
            }
            
            // Extract weight/volume
            if (preg_match('/CMB[:\s]*([0-9\.]+)/', $text, $matches)) {
                $result['package_volume'] = $matches[1];
            }
            
            // Extract status
            if (str_contains($text, 'PAID')) {
                $result['package_description'] = 'PAID';
            }
        }
        
        // Store original row data for further analysis
        $result['raw_row_data'] = $rowData;
        
        return $result;
    }
}