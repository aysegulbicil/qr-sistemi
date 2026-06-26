<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\DepartmentModel;
use App\Services\ReportService;

class Reports extends BaseController
{
    private function params(): array
    {
        return [
            (string) ($this->request->getGet('type') ?: 'summary'),
            (string) ($this->request->getGet('start') ?: date('Y-m-01')),
            (string) ($this->request->getGet('end') ?: date('Y-m-d')),
            $this->request->getGet('dept') ? (int) $this->request->getGet('dept') : null,
        ];
    }

    public function index()
    {
        [$type, $start, $end, $dept] = $this->params();

        return view('admin/reports/index', [
            'report'      => (new ReportService())->build($type, $start, $end, $dept),
            'type'        => $type,
            'start'       => $start,
            'end'         => $end,
            'dept'        => $dept,
            'types'       => (new ReportService())->types(),
            'departments' => (new DepartmentModel())->ordered(),
        ]);
    }

    public function printView()
    {
        [$type, $start, $end, $dept] = $this->params();

        return view('admin/reports/print', [
            'report' => (new ReportService())->build($type, $start, $end, $dept),
        ]);
    }

    public function export()
    {
        [$type, $start, $end, $dept] = $this->params();
        $report = (new ReportService())->build($type, $start, $end, $dept);

        $slug = preg_replace('/[^a-z0-9]+/i', '-', $this->asciiFold($report['title']));
        $name = strtolower(trim($slug, '-')) . '-' . date('Ymd') . '.csv';

        $fh = fopen('php://temp', 'r+');
        fwrite($fh, "\xEF\xBB\xBF"); // UTF-8 BOM (Excel)
        fputcsv($fh, $report['columns'], ';');
        foreach ($report['rows'] as $row) {
            fputcsv($fh, $row, ';');
        }
        rewind($fh);
        $csv = stream_get_contents($fh);
        fclose($fh);

        return $this->response
            ->setHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $name . '"')
            ->setBody($csv);
    }

    private function asciiFold(string $s): string
    {
        return strtr($s, ['ç' => 'c', 'Ç' => 'C', 'ğ' => 'g', 'Ğ' => 'G', 'ı' => 'i', 'İ' => 'I', 'ö' => 'o', 'Ö' => 'O', 'ş' => 's', 'Ş' => 'S', 'ü' => 'u', 'Ü' => 'U']);
    }
}
