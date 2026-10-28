<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReportRequest;
use App\Models\Category;
use App\Services\ReportService;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;
use Symfony\Component\HttpFoundation\Response;

class ReportController extends Controller
{
    public function index(ReportRequest $request, ReportService $reports): InertiaResponse
    {
        $type = $request->string('type')->toString() ?: 'stock';
        $filters = $this->filters($request);
        $report = $reports->build($type, $filters);

        return Inertia::render('Reports/Index', [
            'type' => $type,
            'report' => $report,
            'types' => collect(ReportService::types())->map(fn (string $label, string $value) => [
                'value' => $value,
                'label' => $label,
            ])->values()->all(),
            'categories' => Category::query()->orderBy('name')->get(['id', 'name']),
            'filters' => [
                'category_id' => $filters['category_id'],
                'status' => $filters['status'],
                'from' => $filters['from']->format('Y-m-d'),
                'to' => $filters['to']->format('Y-m-d'),
            ],
        ]);
    }

    public function download(ReportRequest $request, ReportService $reports): Response
    {
        $type = $request->string('type')->toString() ?: 'stock';
        $report = $reports->build($type, $this->filters($request));
        $report['generated_by'] = $request->user()->name;

        $html = view('reports.pdf.document', compact('report'))->render();

        $mpdf = $this->pdf();
        $mpdf->WriteHTML($html);

        $filename = $type.'-report-'.now()->format('Ymd-His').'.pdf';
        $output = $mpdf->Output($filename, Destination::STRING_RETURN);

        return response($output, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Cache-Control' => 'private, max-age=0, must-revalidate',
        ]);
    }

    protected function filters(ReportRequest $request): array
    {
        $from = $request->filled('from') ? Carbon::parse($request->input('from'))->startOfDay() : now()->startOfMonth();
        $to = $request->filled('to') ? Carbon::parse($request->input('to'))->endOfDay() : now();

        return [
            'category_id' => $request->filled('category_id') ? (int) $request->input('category_id') : null,
            'status' => $request->string('status')->toString() ?: 'all',
            'from' => $from,
            'to' => $to,
        ];
    }

    protected function pdf(): Mpdf
    {
        $fontDir = resource_path('fonts');

        $defaultConfig = (new ConfigVariables)->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];

        $defaultFontConfig = (new FontVariables)->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];

        return new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'orientation' => 'P',
            'margin_left' => 12,
            'margin_right' => 12,
            'margin_top' => 16,
            'margin_bottom' => 18,
            'default_font' => 'notonaskh',
            'fontDir' => array_merge($fontDirs, [$fontDir]),
            'fontdata' => $fontData + [
                'notonaskh' => [
                    'R' => 'NotoNaskhArabic-Regular.ttf',
                    'B' => 'NotoNaskhArabic-Bold.ttf',
                    'useOTL' => 0xFF,
                    'useKashida' => 75,
                ],
            ],
            'autoScriptToLang' => true,
            'autoLangToFont' => true,
            'default_font_size' => 9,
            'tempDir' => storage_path('app/mpdf'),
        ]);
    }
}
