<?php

namespace App\Exports;

use App\Actions\Branch\GetBranchByName;
use App\Actions\MHBL\GetMHBLById;
use App\Enum\WarehouseType;
use App\Models\Container;
use App\Models\HBL;
use App\Models\Scopes\BranchScope;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LoadedContainerManifestExcelExport implements FromCollection, ShouldAutoSize, WithCustomStartCell, WithEvents, WithStyles
{
    use Exportable;

    private Container $container;

    private ?array $processedData = null;

    private ?int $giftCount = null;

    private ?int $upbCount = null;

    private ?int $d2dCount = null;

    private ?string $cargoType = null;

    private $settings;

    private $branch;

    public function __construct(Container $container, $settings = null, $branch = null)
    {
        $this->container = $container;
        $this->settings = $settings;
        $this->branch = $branch;
    }

    public function setProcessedData(array $data, int $giftCount, int $upbCount, int $d2dCount, string $cargoType): void
    {
        $this->processedData = $data;
        $this->giftCount = $giftCount;
        $this->upbCount = $upbCount;
        $this->d2dCount = $d2dCount;
        $this->cargoType = $cargoType;
    }

    public function startCell(): string
    {
        return 'A1';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $this->styleSheet($event->sheet);
            },
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return []; // All styling is handled in the event
    }

    private function styleSheet($sheet)
    {
        $worksheet = $sheet->getDelegate();
        $worksheet->getParent()->getDefaultStyle()->getFont()->setName('Times New Roman');
        $data = $this->processedData ?? $this->prepareData();

        // --- Calculate Totals ---
        $total_nototal = 0;
        $total_vtotal = 0;
        $total_gtotal = 0;

        if (! empty($this->container?->shipment_weight) && $this->container->shipment_weight > 0) {
            $total_gtotal = $this->container->shipment_weight;
        } else {
            foreach ($data as $item) {
                foreach ($item[9] as $package) {
                    $total_gtotal += $package['actual_weight'] ?? 0;
                }
            }
        }
        foreach ($data as $item) {
            foreach ($item[9] as $package) {
                $total_nototal += $package['quantity'] ?? 0;
                $total_vtotal += $package['volume'] ?? 0;
            }
        }

        // --- Main Header (Replicates HTML Header) ---
        // Row 1 - First header row with border
        $worksheet->getStyle('A1:K1')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // Fill the background color of the first row with light gray
        $worksheet->getStyle('A1:K1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('EEEEEE');

        $worksheet->mergeCells('A1:B1');
        $worksheet->setCellValue('A1', 'OBL');
        $worksheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $worksheet->setCellValue('C1', $this->container?->bl_number ?? 'ONEYDOHF00020500');
        $worksheet->getStyle('C1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        $worksheet->mergeCells('D1:J1');
        $worksheet->setCellValue('D1', 'UNIVERSAL FREIGHT SERVICES');
        $worksheet->getStyle('H1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $worksheet->setCellValue('K1', 'SHIPMENT  NO' . ($this->container?->reference ?? '2745'));
        $worksheet->getStyle('K1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        $worksheet->getStyle('A1:K1')->getFont()->setBold(true)->setSize(11);        // Row 2 - Manifest title with gray background
        $worksheet->mergeCells('A2:K2');
        $worksheet->setCellValue('A2', 'CARGO MANIFEST');
        $worksheet->getStyle('A2')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('DDDDDD');
        $worksheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $worksheet->getStyle('A2')->getFont()->setBold(true)->setSize(14);
        $worksheet->getStyle('A2:K2')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // Row 3 - Vessel info row
        $worksheet->getStyle('A3:K3')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $worksheet->getStyle('A3:K3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('EEEEEE');

        $worksheet->setCellValue('A3', 'VESSEL  :');
        $worksheet->mergeCells('B3:C3');
        $worksheet->setCellValue('B3', $this->container?->vessel_name ?? 'YM COSMOS');

        $worksheet->mergeCells('D3:G3');
        $worksheet->setCellValue('D3', 'DATE LOADED : ' . ($this->container?->loading_started_at ? \Carbon\Carbon::parse($this->container?->loading_started_at)->format('d.m.Y') : '09.01.2025'));

        $worksheet->setCellValue('H3', 'VOYAGE:');
        $worksheet->setCellValue('I3', $this->container?->voyage_number ?? '181E');

        $worksheet->setCellValue('J3', 'ETA:');
        $worksheet->setCellValue('K3', $this->container?->estimated_time_of_arrival ? \Carbon\Carbon::parse($this->container?->estimated_time_of_arrival)->format('d.m.Y') : '03.02.2025');

        $worksheet->getStyle('A3:K3')->getFont()->setBold(true);


        // Row 4-6 - Shipper, consignee, notify info - Individual rows
        // Row 4 - Shipper
        $worksheet->getStyle('A4:K4')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $worksheet->getStyle('A4:K4')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('EEEEEE');
        $worksheet->setCellValue('A4', 'SHIPPER');
        $worksheet->mergeCells('B4:K4');
        $worksheet->setCellValue('B4', ': UNIVERSAL FREIGHT SERVICES, P.O.BOX: 55239, DOHA, QATAR. TEL: +974 4620961 TEL/FAX: +974 4620812');
        $worksheet->getStyle('A4:K4')->getFont()->setBold(true);

        // Row 5 - Consignee
        $worksheet->getStyle('A5:K5')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $worksheet->getStyle('A5:K5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('EEEEEE');
        $worksheet->setCellValue('A5', 'CONSIGNEE');
        $worksheet->mergeCells('B5:K5');
        $worksheet->setCellValue('B5', ': LAKSIRI SEVA (PVT) LTD. NO: 66, NEW NUGE ROAD, PELIYAGODA, SRI LANKA');
        $worksheet->getStyle('A5:K5')->getFont()->setBold(true);

        // Row 6 - Notify
        $worksheet->getStyle('A6:K6')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $worksheet->getStyle('A6:K6')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('EEEEEE');
        $worksheet->setCellValue('A6', 'NOTIFY');
        $worksheet->mergeCells('B6:K6');
        $worksheet->setCellValue('B6', ': LAKSIRI SEVA (PVT) LTD. NO: 31, ST.ANTHONY\'S MAWATHA, COLOMBO - 03, SRI LANKA.  TEL : +94 11-2574180 / 11-47722800');
        $worksheet->getStyle('A6:K6')->getFont()->setBold(true);

        // Row 7 - Container number
        $worksheet->getStyle('A7:K7')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $worksheet->getStyle('A7:K7')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('EEEEEE');
        $worksheet->setCellValue('A7', 'CONTR NO :');
        $worksheet->mergeCells('B7:C7');
        $worksheet->setCellValue('B7', $this->container?->container_number ?? 'TCLU1650570');
        $worksheet->mergeCells('D7:G7');
        $worksheet->setCellValue('D7', 'SEAL NO: ' . ($this->container?->seal_number ?? 'No Seal No'));
        $worksheet->getStyle('D7:I7')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $worksheet->mergeCells('H7:K7');
        $worksheet->setCellValue('H7', 'CONTAINER TYPE : ' . ($this->container?->container_type ?? 'No data'));
        $worksheet->getStyle('A7:K7')->getFont()->setBold(true);

        // Row 8 - Package count
        $worksheet->getStyle('A8:K8')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $worksheet->getStyle('A8:K8')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('EEEEEE');
        $worksheet->setCellValue('A8', 'NO OF PKG:');
        $worksheet->mergeCells('B8:C8');
        $worksheet->setCellValue('B8', number_format($total_nototal, 0));
        $worksheet->mergeCells('D8:G8');
        $worksheet->setCellValue('D8', 'TOTAL  VOLUME  : ' . number_format($total_vtotal, 3));
        $worksheet->mergeCells('H8:K8');
        $worksheet->setCellValue('H8', 'TOTAL WEIGHT: KG        ' . number_format($total_gtotal, 3));
        $worksheet->getStyle('H8')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $worksheet->getStyle('A8:K8')->getFont()->setBold(true);
        $worksheet->getStyle('A8:K8')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // --- HBL Table Header ---
        // Create a clean header row with column titles
        $hblHeaders = ['SR NO', 'HBL NO', 'NAME OF SHIPPER', 'NAME OF CONSIGNEES', 'TYPE OF PKGS', 'NO.OF PKGS', 'VOLUME CBM', 'GWHT', 'DESCRIPTION OF CARGO', 'DELIVERY', 'REMARKS'];
        $worksheet->fromArray($hblHeaders, null, 'A9');
        $worksheet->getStyle('A9:K9')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $worksheet->getStyle('A9:K9')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setWrapText(true);
        $worksheet->getStyle('A9:K9')->getFont()->setBold(true);

        // Add gray background color
        $worksheet->getStyle('A9:K9')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFCCCCCC'); // Light gray color

        // --- HBL Data Rows Loop ---
        $currentRow = 10; // Start immediately after the header row
        $serialNumber = 1; // Start from 1 since we don't have any sample rows anymore

        foreach ($data as $item) {
            $startRow = $currentRow;
            $packages = collect($item[9]);
            $packageCount = $packages->count();

            $totalQuantity = $packages->sum('quantity');
            $totalVolume = $packages->sum('volume');
            $hblweight = ($total_vtotal > 0) ? (($total_gtotal / $total_vtotal) * $totalVolume) : 0;

            // Determine block height - minimum 4 rows for shipper data + 1 for total
            $dataRowCount = max(4, $packageCount);
            $totalBlockRows = $dataRowCount + 1; // +1 for the total row

            // SR NO - Merge for entire block including total row
            $worksheet->mergeCells("A{$startRow}:A" . ($startRow + $totalBlockRows - 1));
            $worksheet->setCellValue("A{$startRow}", $serialNumber++);
            $worksheet->getStyle("A{$startRow}")->getFont()->setBold(true);
            $worksheet->getStyle("A{$startRow}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER)->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // HBL NO - Merge for data rows only (excluding total row)
            $worksheet->mergeCells("B{$startRow}:B" . ($startRow + $dataRowCount - 1));
            $worksheet->setCellValue("B{$startRow}", $item[0] ?? '');
            $worksheet->getStyle("B{$startRow}")->getAlignment()->setVertical(Alignment::VERTICAL_TOP)->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // NAME OF SHIPPER - Merge for entire block (excluding total row to not affect PP no data)
            $worksheet->mergeCells("C{$startRow}:C" . ($startRow + $dataRowCount - 1));
            $shipperInfo = ($item[1] ?? '') . "\n" . ($item[2] ?? '') . "\n" . ($item[14] ?? '') . "\n" . ($item[4] ?? '');
            $worksheet->setCellValue("C{$startRow}", $shipperInfo);
            $worksheet->getStyle("C{$startRow}")->getAlignment()->setVertical(Alignment::VERTICAL_TOP)->setWrapText(true);

            // NAME OF CONSIGNEES - Merge for entire block
            $worksheet->mergeCells("D{$startRow}:D" . ($startRow + $totalBlockRows - 1));
            $consigneeInfo = ($item[5] ?? '') . "\n" . ($item[6] ?? '') . "\n" . ($item[8] ?? '');
            $worksheet->setCellValue("D{$startRow}", $consigneeInfo);
            $worksheet->getStyle("D{$startRow}")->getAlignment()->setVertical(Alignment::VERTICAL_TOP)->setWrapText(true);

            // TYPE OF PKGS and NO.OF PKGS - Fill package data row by row
            for ($i = 0; $i < $dataRowCount; $i++) {
                $row = $startRow + $i;
                if (isset($packages[$i])) {
                    $worksheet->setCellValue("E{$row}", $packages[$i]['package_type'] ?? '');
                    $worksheet->setCellValue("F{$row}", $packages[$i]['quantity'] ?? '');
                }
                // Center align package data
                $worksheet->getStyle("E{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $worksheet->getStyle("F{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Increase row height for data rows to provide more space
                $worksheet->getRowDimension($row)->setRowHeight(25); // Increased from 18 to 25 for better readability
            }

            // VOLUME CBM and GWHT - Leave empty for individual package rows, will be filled in total row

            // DESCRIPTION - Merge for entire block like example image
            $worksheet->mergeCells("I{$startRow}:I" . ($startRow + $totalBlockRows - 1));
            $worksheet->setCellValue("I{$startRow}", "PERSONAL\nEFFECTS");
            $worksheet->getStyle("I{$startRow}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER)->setHorizontal(Alignment::HORIZONTAL_CENTER)->setWrapText(true);

            // DELIVERY - Merge for entire block
            $worksheet->mergeCells("J{$startRow}:J" . ($startRow + $totalBlockRows - 1));
            $worksheet->setCellValue("J{$startRow}", $item[13] ?? 'CMB');
            $worksheet->getStyle("J{$startRow}")->getFont()->setBold(true);
            $worksheet->getStyle("J{$startRow}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER)->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // REMARKS - Merge for entire block
            $worksheet->mergeCells("K{$startRow}:K" . ($startRow + $totalBlockRows - 1));

            // Build REMARKS format to match blade file exactly
            $hblType = $item[11] ?? 'Gift'; // HBL type (Gift, UBP, D2D, etc.)
            $warehouse = $item[13] ?? 'CMB'; // Warehouse (CMB/NTR)
            $isDepartureChargesPaid = $item[15] ?? false; // is_departure_charges_paid
            $isDestinationChargesPaid = $item[16] ?? false; // is_destination_charges_paid
            $status = $item[17] ?? ''; // Status (BALANCE, SHORT LOADED, etc.)
            $amount = $item[19] ?? ''; // Amount with currency symbol

            $branchCode = $this->container->branch?->branch_code ?? $this->branch?->branch_code ?? 'DOH';

            // Start with cargo type (like in blade file line 238)
            $remarksValue = '';
            $normalizedHblType = strtoupper($hblType);
            if ($normalizedHblType == 'GIFT') {
                $remarksValue = 'GIFT CARGO' . "\n";
            } elseif ($normalizedHblType == 'UBP') {
                $remarksValue = 'UBP CARGO' . "\n";
            } elseif ($normalizedHblType == 'D2D') {
                $remarksValue = 'DOOR TO DOOR CARGO' . "\n";
            }

            // Add HBL Type
            $remarksValue .= $hblType;

            // Add payment logic exactly as in blade file
            if ($isDepartureChargesPaid && $isDestinationChargesPaid) {
                $remarksValue .= "\n" . $branchCode . ' & ' . $warehouse . "\nPAID";
            } elseif ($isDepartureChargesPaid) {
                $remarksValue .= "\nPAID";
            } else {
                $remarksValue .= "\nNOT PAID\nPLEASE COLLECT\n" . $amount . '/-';
            }

            // If there's a status, add it as additional line
            if (!empty($status)) {
                $remarksValue .= "\n" . $status;
            }

            $worksheet->setCellValue("K{$startRow}", $remarksValue);
            $worksheet->getStyle("K{$startRow}")->getFont()->setBold(true);
            // Align REMARKS to top and center horizontally, preserve wrap
            $worksheet->getStyle("K{$startRow}")->getAlignment()
                ->setVertical(Alignment::VERTICAL_TOP)
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setWrapText(true);

            // Total Row
            $totalRow = $startRow + $dataRowCount;
            $worksheet->setCellValue("B{$totalRow}", 'PP No');
            $worksheet->setCellValue("C{$totalRow}", $item[3] ?? ''); // PP Number
            $worksheet->setCellValue("E{$totalRow}", 'TOTAL');
            $worksheet->setCellValue("F{$totalRow}", $totalQuantity);
            $worksheet->setCellValue("G{$totalRow}", number_format($totalVolume, 3));
            $worksheet->setCellValue("H{$totalRow}", number_format($hblweight, 2));

            // Style Total Row
            $worksheet->getStyle("C{$totalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT); // Align PP No to left
            $worksheet->getStyle("E{$totalRow}:H{$totalRow}")->getFont()->setBold(true);
            $worksheet->getStyle("E{$totalRow}:H{$totalRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THICK);
            $worksheet->getStyle("E{$totalRow}:H{$totalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Set general alignment and wrapping
            $worksheet->getStyle("C{$startRow}:C" . ($startRow + $dataRowCount - 1))->getAlignment()->setVertical(Alignment::VERTICAL_TOP)->setHorizontal(Alignment::HORIZONTAL_LEFT)->setWrapText(true);

            // Fill data cells with white background
            for ($i = 0; $i < $dataRowCount; $i++) {
                $row = $startRow + $i;
                $worksheet->getStyle("C{$row}:H{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFFFFF');
            }

            // Remove borders from package data cells (will be re-added selectively)
            $worksheet->getStyle("E{$startRow}:H" . ($startRow + $dataRowCount - 1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_NONE);

            $currentRow = $totalRow + 1;
        }

        // --- Grand Total Section ---
        $lastDataRow = $currentRow - 1;
        $currentRow++; // spacer

        $worksheet->mergeCells("D{$currentRow}:E{$currentRow}");
        $worksheet->setCellValue("D{$currentRow}", 'GRAND TOTAL');
        $worksheet->getStyle("D{$currentRow}")->getFont()->setBold(true);
        $worksheet->setCellValue("F{$currentRow}", number_format($total_nototal, 0));
        $worksheet->setCellValue("G{$currentRow}", number_format($total_vtotal, 3));
        $worksheet->setCellValue("H{$currentRow}", number_format($total_gtotal, 2));
        $worksheet->getStyle("F{$currentRow}:H{$currentRow}")->getFont()->setBold(true)->setUnderline(Font::UNDERLINE_SINGLE);

        $currentRow += 2; // spacer

        $worksheet->setCellValue("C{$currentRow}", 'UBP CARGO - ' . ($this->upbCount ?? 0));
        $worksheet->setCellValue('C' . ($currentRow + 1), 'GIFT CARGO - ' . ($this->giftCount ?? 0));
        $worksheet->setCellValue('C' . ($currentRow + 2), 'DOOR TO DOOR CARGO - ' . ($this->d2dCount ?? 0));
        $worksheet->getStyle("C{$currentRow}:C" . ($currentRow + 2))->getFont()->setBold(true);

        // --- Borders and Final Styling ---
        $worksheet->getStyle("A1:K{$lastDataRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // Remove borders from data cells and add selective borders
        $currentRowForBorderRemoval = 10;
        foreach ($data as $item) {
            $startRowForBorderRemoval = $currentRowForBorderRemoval;
            $packages = collect($item[9]);
            $packageCount = $packages->count();
            $dataRowCount = max(4, $packageCount);
            $totalBlockRows = $dataRowCount + 1;

            // Remove all borders from data cells in columns C-H, then add left borders
            for ($i = 0; $i < $dataRowCount; $i++) {
                $row = $startRowForBorderRemoval + $i;

                // Remove all borders and add left border for each column
                $columns = ['C', 'D', 'E', 'F', 'G', 'H'];
                foreach ($columns as $col) {
                    $worksheet->getStyle("{$col}{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_NONE);
                    $worksheet->getStyle("{$col}{$row}")->getBorders()->getLeft()->setBorderStyle(Border::BORDER_THIN);
                }
            }

            $currentRowForBorderRemoval = $startRowForBorderRemoval + $totalBlockRows;
        }

        // ### NEW: Set Column Widths ###
        $worksheet->getColumnDimension('A')->setWidth(5);   // SR NO
        $worksheet->getColumnDimension('B')->setWidth(12);  // HBL NO
        $worksheet->getColumnDimension('C')->setWidth(22);  // NAME OF SHIPPER
        $worksheet->getColumnDimension('D')->setWidth(25);  // NAME OF CONSIGNEES
        $worksheet->getColumnDimension('E')->setWidth(10);   // CHR
        $worksheet->getColumnDimension('F')->setWidth(10);  // NO.OF PKGS
        $worksheet->getColumnDimension('G')->setWidth(10);  // VOLUME CBM
        $worksheet->getColumnDimension('H')->setWidth(10);  // GWHT
        $worksheet->getColumnDimension('I')->setWidth(18);  // DESCRIPTION
        $worksheet->getColumnDimension('J')->setWidth(15);  // DELIVERY
        $worksheet->getColumnDimension('K')->setWidth(20);  // REMARKS

        // Add overall border to the entire table
        $worksheet->getStyle("A1:K{$lastDataRow}")->getBorders()->getOutline()->setBorderStyle(Border::BORDER_MEDIUM);
    }

    public function collection()
    {
        return collect([]);
    }

    public function prepareData(): array
    {
        $data = [];
        $loadedMHBLPackages = [];
        $loadedHBLPackages = [];

        $this->container->load(['hbl_packages', 'duplicate_hbl_packages']);
        $currentlyLoadedPackageIds = $this->container->hbl_packages ? $this->container->hbl_packages->pluck('id')->toArray() : [];

        if ($this->container->duplicate_hbl_packages) {
            foreach ($this->container->duplicate_hbl_packages->groupBy('hbl_id') as $hblId => $packages) {
                $stillLoadedPackages = $packages->filter(fn($package) => in_array($package->id, $currentlyLoadedPackageIds));

                if ($stillLoadedPackages->isEmpty()) {
                    continue;
                }

                $hbl = HBL::withoutGlobalScope(BranchScope::class)->with('mhbl')->find($hblId);
                if ($hbl && $hbl->mhbl) {
                    $loadedMHBLPackages[$hbl->mhbl->id][] = $stillLoadedPackages;
                } elseif ($hbl) {
                    $loadedHBLPackages[$hblId] = ['hbl' => $hbl, 'packages' => $stillLoadedPackages];
                }
            }
        }

        foreach ($loadedMHBLPackages as $mhblId => $mhblPackage) {
            try {
                $mhbl = GetMHBLById::run($mhblId);
                $hblPackages = [];
                if (! empty($mhbl->hbls)) {
                    foreach ($mhbl->hbls as $mhblHBL) {
                        foreach ($mhblHBL->packages as $hblPackage) {
                            $hblPackages[] = $hblPackage;
                        }
                    }
                }
                $warehouse = $this->getWarehouseCode($mhbl->hbls[0] ?? null);
                // Get the actual HBL type from the first HBL in the MHBL, default to 'Gift' if not found
                $hblType = 'Gift'; // default
                if (!empty($mhbl->hbls) && $mhbl->hbls->count() > 0) {
                    $hblType = $mhbl->hbls->first()->hbl_type ?? 'Gift';
                }
                
                $data[] = [
                    $mhbl->hbl_number ?: $mhbl->reference, // 0
                    $mhbl->shipper->name ?? '', // 1
                    $mhbl->shipper->address ?? '', // 2
                    $mhbl->shipper->pp_or_nic_no ?? '', // 3
                    $mhbl->shipper->mobile_number ?? '', // 4
                    $mhbl->consignee->name ?? '', // 5
                    $mhbl->consignee->address ?? '', // 6
                    $mhbl->consignee->pp_or_nic_no ?? '', // 7
                    $mhbl->consignee->mobile_number ?? '', // 8
                    collect($hblPackages ?? []), // 9
                    $mhbl->hbls[0]->paid_amount > 0 ? 'PAID' : 'UNPAID', // 10
                    $hblType, // 11 - Use the actual HBL type
                    '', // 12
                    $warehouse, // 13
                    '', // 14
                    1, // 15
                    0, // 16
                    null,
                    null,
                    null,
                ];
            } catch (\Exception $e) {
                Log::error('Error processing MHBL package: ' . $e->getMessage());

                continue;
            }
        }

        foreach ($loadedHBLPackages as $hblData) {
            try {
                $hbl = $hblData['hbl'];
                $warehouse = $this->getWarehouseCode($hbl);
                $hblLoadedContainers = $hbl->packages ? $hbl->packages->load('duplicate_containers')->pluck('duplicate_containers')->flatten()->unique('id')->sortByDesc('created_at') : collect();
                $hblLoadedLatestContainer = $hblLoadedContainers->first();

                $status = (count($hblLoadedContainers) > 1 && $hblLoadedLatestContainer && $hblLoadedLatestContainer['id'] === $this->container['id']) ? 'BALANCE' : 'SHORT LOADED';
                if (($hbl->packages ? $hbl->packages->every(fn($p) => $p->duplicate_containers->isNotEmpty()) : false) && count($hblLoadedContainers) === 1) {
                    $status = '';
                }

                $referencesString = $hbl->packages ? $hbl->packages->load('duplicate_containers')->pluck('duplicate_containers')->flatten()->pluck('reference')->unique()->reject(fn($ref) => $ref == $this->container['reference'])->implode(',') : '';

                $data[] = [
                    $hbl->hbl_number ?: $hbl->reference, // 0
                    $hbl->hbl_name, // 1
                    $hbl->address, // 2
                    $hbl->nic, // 3
                    $hbl->contact_number, // 4
                    $hbl->consignee_name, // 5
                    $hbl->consignee_address, // 6
                    $hbl->consignee_nic, // 7
                    $hbl->consignee_contact . ($hbl->consignee_additional_mobile_number ? '/' . $hbl->consignee_additional_mobile_number : ''), // 8
                    $loadedHBLPackages[$hbl->id]['packages'], // 9
                    $hbl->paid_amount > 0 ? 'PAID' : 'UNPAID', // 10
                    $hbl->hbl_type, // 11
                    $hbl->other_charge, // 12
                    $warehouse, // 13
                    $hbl->iq_number, // 14
                    $hbl->is_departure_charges_paid, // 15
                    $hbl->is_destination_charges_paid, // 16
                    $status, // 17
                    $referencesString ? "SHIP NO. $referencesString" : null, // 18
                    ($hbl->branch['currency_symbol'] ?? '') . ' ' . ($hbl['grand_total'] ?? ''), // 19
                ];
            } catch (\Exception $e) {
                Log::error('Error processing HBL package: ' . $e->getMessage());

                continue;
            }
        }

        return $data;
    }

    private function getWarehouseCode($hbl): ?string
    {
        if (! $hbl) {
            return null;
        }
        if ($hbl->warehouse_id ?? null) {
            return match ($hbl->warehouse_id) {
                GetBranchByName::run(WarehouseType::COLOMBO->value)['id'] => 'CMB',
                GetBranchByName::run(WarehouseType::NINTAVUR->value)['id'] => 'NTR',
                default => null,
            };
        } elseif ($hbl->warehouse ?? null) {
            return match (ucwords($hbl->warehouse)) {
                WarehouseType::COLOMBO->value => 'CMB',
                WarehouseType::NINTAVUR->value => 'NTR',
                default => null,
            };
        }

        return null;
    }
}
