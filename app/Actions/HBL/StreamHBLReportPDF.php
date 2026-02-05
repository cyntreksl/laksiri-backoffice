<?php

namespace App\Actions\HBL;

use App\Actions\Setting\GetSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;
use Symfony\Component\HttpFoundation\Response;
use Wnx\SidecarBrowsershot\BrowsershotLambda;

class StreamHBLReportPDF
{
    use AsAction;

    public function handle(Collection $hbls, Request $request, bool $detailed = false): Response
    {
        $settings = GetSettings::run();
        
        // Calculate summary statistics
        $stats = $this->calculateStats($hbls);
        
        // Get filter information for display
        $filters = $this->getAppliedFilters($request);

        // Choose template based on detail level
        $templateView = $detailed ? 'pdf.reports.hbl-report-detailed' : 'pdf.reports.hbl-report';

        // Render the Blade template to HTML
        $template = view($templateView, [
            'hbls' => $hbls,
            'stats' => $stats,
            'filters' => $filters,
            'settings' => $settings,
            'logoPath' => $settings['logo_url'] ?? asset('images/app-logo.png'),
            'companyName' => $settings['invoice_header_title'] ?? 'Company Name',
            'generatedAt' => now()->format('Y-m-d H:i:s'),
            'generatedBy' => auth()->user()->name ?? 'System',
        ])->render();

        $filename = 'hbl-report-' . now()->format('Y-m-d-His') . '.pdf';

        // Get configuration
        $timeout = config('browsershot.timeout', 120);
        $protocolTimeout = config('browsershot.protocol_timeout', 120000);
        $args = config('browsershot.args', ['--no-sandbox', '--disable-setuid-sandbox']);

        // Generate PDF content using BrowsershotLambda with optimized settings
        $browsershot = BrowsershotLambda::html($template)
            ->showBackground()
            ->landscape()
            ->format('A4')
            ->margins(10, 10, 10, 10)
            ->timeout($timeout);

        // Add Chrome args
        foreach ($args as $arg) {
            $browsershot->addChromiumArguments([$arg]);
        }

        // Set protocol timeout via setOption
        $browsershot->setOption('protocolTimeout', $protocolTimeout);

        // Generate PDF content
        $pdfContent = $browsershot->pdf();

        return new Response(
            $pdfContent,
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $filename . '"',
            ]
        );
    }

    private function calculateStats(Collection $hbls): array
    {
        return [
            'total_hbls' => $hbls->count(),
            'total_packages' => $hbls->sum(fn($hbl) => $hbl->packages->count()),
            'total_amount' => $hbls->sum('grand_total'),
            'total_paid' => $hbls->sum('paid_amount'),
            'total_balance' => $hbls->sum(fn($hbl) => $hbl->grand_total - $hbl->paid_amount),
        ];
    }

    private function getAppliedFilters(Request $request): array
    {
        $filters = [];

        if ($request->filled('loaded_date_from') || $request->filled('loaded_date_to')) {
            $filters['Loaded Date'] = $this->formatDateRange(
                $request->input('loaded_date_from'),
                $request->input('loaded_date_to')
            );
        }

        if ($request->filled('unloaded_date_from') || $request->filled('unloaded_date_to')) {
            $filters['Unloaded Date'] = $this->formatDateRange(
                $request->input('unloaded_date_from'),
                $request->input('unloaded_date_to')
            );
        }

        if ($request->filled('branch_id')) {
            $branch = \App\Models\Branch::find($request->input('branch_id'));
            $filters['Branch'] = $branch?->name ?? 'N/A';
        }

        if ($request->filled('customer_search')) {
            $filters['Customer'] = $request->input('customer_search');
        }

        if ($request->filled('cargo_type')) {
            $filters['Cargo Type'] = $request->input('cargo_type');
        }

        if ($request->filled('hbl_type')) {
            $filters['HBL Type'] = $request->input('hbl_type');
        }

        if ($request->filled('search')) {
            $filters['Search'] = $request->input('search');
        }

        return $filters;
    }

    private function formatDateRange(?string $from, ?string $to): string
    {
        if ($from && $to) {
            return "{$from} to {$to}";
        } elseif ($from) {
            return "From {$from}";
        } elseif ($to) {
            return "Until {$to}";
        }
        return 'N/A';
    }
}
