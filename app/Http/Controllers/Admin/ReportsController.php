<?php

namespace App\Http\Controllers\Admin;

use App\Exports\CustomersReportExport;
use App\Exports\ProductsReportExport;
use App\Exports\SalesReportExport;
use App\Http\Controllers\Controller;
use App\Services\Reports\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportsController extends Controller
{
    public function index(Request $request, ReportService $reports)
    {
        $type = $request->input('type', 'sales');
        [$start, $end] = $this->parseDates($request);

        $reportData = match ($type) {
            'products' => $reports->productsReport($start, $end),
            'customers' => $reports->customersReport($start, $end),
            default => $reports->salesReport($start, $end),
        };

        return view('admin.reports.index', [
            'type' => $type,
            'reportData' => $reportData,
            'start' => $start,
            'end' => $end,
        ]);
    }

    public function export(string $type, string $format, Request $request, ReportService $reports)
    {
        [$start, $end] = $this->parseDates($request);

        $reportData = match ($type) {
            'products' => $reports->productsReport($start, $end),
            'customers' => $reports->customersReport($start, $end),
            default => $reports->salesReport($start, $end),
        };

        return match ($format) {
            'excel' => $this->exportExcel($type, $reportData),
            'pdf' => Pdf::loadView('admin.reports.print', [
                'type' => $type,
                'reportData' => $reportData,
                'start' => $start,
                'end' => $end,
            ])->download("report-{$type}.pdf"),
            'print' => view('admin.reports.print', [
                'type' => $type,
                'reportData' => $reportData,
                'start' => $start,
                'end' => $end,
            ]),
            default => abort(404),
        };
    }

    private function exportExcel(string $type, array $reportData)
    {
        return match ($type) {
            'products' => Excel::download(new ProductsReportExport($reportData), "report-{$type}.xlsx"),
            'customers' => Excel::download(new CustomersReportExport($reportData), "report-{$type}.xlsx"),
            default => Excel::download(new SalesReportExport($reportData), "report-{$type}.xlsx"),
        };
    }

    private function parseDates(Request $request): array
    {
        $start = $request->filled('start') ? Carbon::parse($request->input('start'))->startOfDay() : null;
        $end = $request->filled('end') ? Carbon::parse($request->input('end'))->endOfDay() : null;

        return [$start, $end];
    }
}
