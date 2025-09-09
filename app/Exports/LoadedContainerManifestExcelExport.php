<?php

namespace App\Exports;

use App\Actions\Branch\GetBranchByName;
use App\Actions\MHBL\GetMHBLById;
use App\Enum\WarehouseType;
use App\Models\Container;
use App\Models\HBL;
use App\Models\Scopes\BranchScope;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Style\Font;

class LoadedContainerManifestExcelExport implements FromCollection, ShouldAutoSize, WithStyles, WithEvents, WithCustomStartCell
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

        if (!empty($this->container?->shipment_weight) && $this->container->shipment_weight > 0) {
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
        $hblHeaders = ['SR NO', 'HBL NO', 'NAME OF SHIPPER', 'NAME OF CONSIGNEES', 'TYPE OF PKGS', 'NO.OF PKGS', 'VOLUME CBM', 'GWHT', 'DESCRIPTION', 'DELIVERY', 'REMARKS'];
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

            // Determine block height
            $dataRowCount = max(4, $packageCount);
            $totalBlockRows = $dataRowCount + 1;

            // Merges for the entire block
            $worksheet->mergeCells("A{$startRow}:A" . ($startRow + $totalBlockRows - 1)); // SR NO
            $worksheet->setCellValue("A{$startRow}", $serialNumber++);

            // Set the first column style
            $worksheet->getStyle("A{$startRow}")->getFont()->setBold(true);

            $worksheet->mergeCells("I{$startRow}:I" . ($startRow + $totalBlockRows - 1)); // DESCRIPTION
            $worksheet->setCellValue("I{$startRow}", "PERSONAL\nEFFECTS");

            $worksheet->mergeCells("J{$startRow}:J" . ($startRow + $totalBlockRows - 1)); // DELIVERY
            $worksheet->setCellValue("J{$startRow}", $item[13] ?? 'CMB');

            $worksheet->mergeCells("K{$startRow}:K" . ($startRow + $totalBlockRows - 1)); // REMARKS
            $worksheet->setCellValue("K{$startRow}", "GIFT CARGO\nDOH & CMB\nPAID");

            $worksheet->mergeCells("D{$startRow}:D" . ($startRow + $totalBlockRows - 1)); // NAME OF CONSIGNEES
            $worksheet->setCellValue("D{$startRow}", ($item[5] ?? '') . "\n" . ($item[6] ?? '') . "\n" . ($item[8] ?? ''));

            // Merge for data rows only
            $worksheet->mergeCells("B{$startRow}:B" . ($startRow + $dataRowCount - 1)); // HBL NO
            $worksheet->setCellValue("B{$startRow}", $item[0] ?? '');

            // Fill row by row
            // Row 1
            $worksheet->setCellValue("C{$startRow}", $item[1] ?? '');
            $worksheet->setCellValue("E{$startRow}", $packages[0]['package_type'] ?? '');
            $worksheet->setCellValue("F{$startRow}", $packages[0]['quantity'] ?? '');

            // Row 2
            $r = $startRow + 1;
            $worksheet->setCellValue("C{$r}", $item[2] ?? '');
            $worksheet->setCellValue("E{$r}", $packages[1]['package_type'] ?? '');
            $worksheet->setCellValue("F{$r}", $packages[1]['quantity'] ?? '');

            // Row 3
            $r = $startRow + 2;
            $worksheet->setCellValue("C{$r}", $item[14] ?? '');
            $worksheet->setCellValue("E{$r}", $packages[2]['package_type'] ?? '');
            $worksheet->setCellValue("F{$r}", $packages[2]['quantity'] ?? '');

            // Row 4
            $r = $startRow + 3;
            $worksheet->setCellValue("C{$r}", $item[4] ?? '');
            $worksheet->setCellValue("E{$r}", $packages[3]['package_type'] ?? '');
            $worksheet->setCellValue("F{$r}", $packages[3]['quantity'] ?? '');

            // Additional Package Rows
            if ($packageCount > 4) {
                for ($i = 4; $i < $packageCount; $i++) {
                    $r = $startRow + $i;
                    $worksheet->setCellValue("E{$r}", $packages[$i]['package_type'] ?? '');
                    $worksheet->setCellValue("F{$r}", $packages[$i]['quantity'] ?? '');
                }
            }

            // Total Row
            $totalRow = $startRow + $dataRowCount;
            $worksheet->setCellValue("B{$totalRow}", "PP No");
            $worksheet->setCellValue("C{$totalRow}", $item[3] ?? ''); // PP Number
            $worksheet->setCellValue("E{$totalRow}", 'TOTAL');
            $worksheet->setCellValue("F{$totalRow}", $totalQuantity);
            $worksheet->setCellValue("G{$totalRow}", number_format($totalVolume, 3));
            $worksheet->setCellValue("H{$totalRow}", number_format($hblweight, 2));

            // Style Total Row
            $worksheet->getStyle("E{$totalRow}:H{$totalRow}")->getFont()->setBold(true);
            $worksheet->getStyle("E{$totalRow}:H{$totalRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THICK);

            // Set Vertical Alignments for the block
            $worksheet->getStyle("A{$startRow}:K{$totalRow}")->getAlignment()->setVertical(Alignment::VERTICAL_TOP)->setWrapText(true);
            $worksheet->getStyle("A{$startRow}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER)->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $worksheet->getStyle("B{$startRow}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER)->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $worksheet->getStyle("C{$startRow}:C{$totalRow}")->getAlignment()->setVertical(Alignment::VERTICAL_TOP)->setHorizontal(Alignment::HORIZONTAL_LEFT)->setWrapText(true);
            $worksheet->getStyle("D{$startRow}")->getAlignment()->setVertical(Alignment::VERTICAL_TOP)->setWrapText(true);
            $worksheet->getStyle("E{$startRow}:H{$totalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $worksheet->getStyle("I{$startRow}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER)->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $worksheet->getStyle("J{$startRow}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER)->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $worksheet->getStyle("K{$startRow}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER)->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Remove all borders for first 4 rows in columns E, F, G, H (TYPE OF PKGS, NO.OF PKGS, VOLUME CBM, GWHT)
            $firstFourRowsEnd = $startRow + 3; // First 4 rows (0-based, so +3)
            $worksheet->getStyle("E{$startRow}:H{$firstFourRowsEnd}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_NONE);

            // Fill NAME OF SHIPPER, NAME OF CONSIGNEES, TYPE OF PKGS, NO.OF PKGS, VOLUME CBM, and GWHT data cells with white color
            for ($i = 0; $i < $dataRowCount; $i++) {
                $row = $startRow + $i;
                $worksheet->getStyle("C{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFFFFF');
                $worksheet->getStyle("D{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFFFFF');
                $worksheet->getStyle("E{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFFFFF');
                $worksheet->getStyle("F{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFFFFF');
                $worksheet->getStyle("G{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFFFFF');
                $worksheet->getStyle("H{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFFFFF');
            }

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

        $worksheet->setCellValue("C{$currentRow}", "UBP CARGO - " . ($this->upbCount ?? 0));
        $worksheet->setCellValue("C" . ($currentRow + 1), "GIFT CARGO - " . ($this->giftCount ?? 0));
        $worksheet->setCellValue("C" . ($currentRow + 2), "DOOR TO DOOR CARGO - " . ($this->d2dCount ?? 0));
        $worksheet->getStyle("C{$currentRow}:C" . ($currentRow + 2))->getFont()->setBold(true);

        // --- Borders and Final Styling ---
        $worksheet->getStyle("A1:K{$lastDataRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // Remove borders from green colored cells (TYPE OF PKGS column) - must be after global border styling
        $currentRowForBorderRemoval = 10;
        $serialNumberForBorderRemoval = 1;
        foreach ($data as $item) {
            $startRowForBorderRemoval = $currentRowForBorderRemoval;
            $packages = collect($item[9]);
            $packageCount = $packages->count();
            $dataRowCount = max(4, $packageCount);
            $totalBlockRows = $dataRowCount + 1;

            // Remove all borders from NAME OF SHIPPER, NAME OF CONSIGNEES, TYPE OF PKGS, NO.OF PKGS, VOLUME CBM, and GWHT columns, then add left border
            for ($i = 0; $i < $dataRowCount; $i++) {
                $row = $startRowForBorderRemoval + $i;
                // Remove all borders completely from NAME OF SHIPPER (column C)
                $worksheet->getStyle("C{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_NONE);
                // Add left border back to NAME OF SHIPPER (column C)
                $worksheet->getStyle("C{$row}")->getBorders()->getLeft()->setBorderStyle(Border::BORDER_THIN);

                // Remove all borders completely from NAME OF CONSIGNEES (column D)
                $worksheet->getStyle("D{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_NONE);
                // Add left border back to NAME OF CONSIGNEES (column D)
                $worksheet->getStyle("D{$row}")->getBorders()->getLeft()->setBorderStyle(Border::BORDER_THIN);

                // Remove all borders completely from TYPE OF PKGS (column E)
                $worksheet->getStyle("E{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_NONE);
                // Add left border back to TYPE OF PKGS (column E)
                $worksheet->getStyle("E{$row}")->getBorders()->getLeft()->setBorderStyle(Border::BORDER_THIN);

                // Remove all borders completely from NO.OF PKGS (column F)
                $worksheet->getStyle("F{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_NONE);
                // Add left border back to NO.OF PKGS (column F)
                $worksheet->getStyle("F{$row}")->getBorders()->getLeft()->setBorderStyle(Border::BORDER_THIN);

                // Remove all borders completely from VOLUME CBM (column G)
                $worksheet->getStyle("G{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_NONE);
                // Add left border back to VOLUME CBM (column G)
                $worksheet->getStyle("G{$row}")->getBorders()->getLeft()->setBorderStyle(Border::BORDER_THIN);

                // Remove all borders completely from GWHT (column H)
                $worksheet->getStyle("H{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_NONE);
                // Add left border back to GWHT (column H)
                $worksheet->getStyle("H{$row}")->getBorders()->getLeft()->setBorderStyle(Border::BORDER_THIN);
            }

            $currentRowForBorderRemoval = $startRowForBorderRemoval + $totalBlockRows + 1;
        }
        //

        // ### NEW: Set Column Widths ###
        $worksheet->getColumnDimension('A')->setWidth(8);   // SR NO
        $worksheet->getColumnDimension('B')->setWidth(12);  // HBL NO
        $worksheet->getColumnDimension('C')->setWidth(22);  // NAME OF SHIPPER
        $worksheet->getColumnDimension('D')->setWidth(25);  // NAME OF CONSIGNEES
        $worksheet->getColumnDimension('E')->setWidth(10);   // CHR
        $worksheet->getColumnDimension('F')->setWidth(10);  // NO.OF PKGS
        $worksheet->getColumnDimension('G')->setWidth(10);  // VOLUME CBM
        $worksheet->getColumnDimension('H')->setWidth(10);  // GWHT
        $worksheet->getColumnDimension('I')->setWidth(15);  // DESCRIPTION
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

                if ($stillLoadedPackages->isEmpty())
                    continue;

                $hbl = HBL::withoutGlobalScope(BranchScope::class)->with('mhbl')->find($hblId);
                if ($hbl && $hbl->mhbl) {
                    $loadedMHBLPackages[$hbl->mhbl->id][] = $stillLoadedPackages;
                } else if ($hbl) {
                    $loadedHBLPackages[$hblId] = ['hbl' => $hbl, 'packages' => $stillLoadedPackages];
                }
            }
        }

        foreach ($loadedMHBLPackages as $mhblId => $mhblPackage) {
            try {
                $mhbl = GetMHBLById::run($mhblId);
                $hblPackages = [];
                if (!empty($mhbl->hbls)) {
                    foreach ($mhbl->hbls as $mhblHBL) {
                        foreach ($mhblHBL->packages as $hblPackage) {
                            $hblPackages[] = $hblPackage;
                        }
                    }
                }
                $warehouse = $this->getWarehouseCode($mhbl->hbls[0] ?? null);
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
                    'Gift', // 11
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
        if (!$hbl)
            return null;
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
