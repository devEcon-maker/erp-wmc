<?php

namespace App\Modules\Core\Services;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportService
{
    public function exportToExcel(Collection|EloquentCollection $collection, array $columns, string $filename): BinaryFileResponse
    {
        $export = new \App\Exports\GenericExport($collection, $columns);
        return Excel::download($export, $filename . '.xlsx');
    }

    public function exportToCsv(Collection|EloquentCollection $collection, array $columns, string $filename): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '.csv"',
        ];

        $callback = function () use ($collection, $columns) {
            $file = fopen('php://output', 'w');

            // UTF-8 BOM for Excel compatibility
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Headers
            fputcsv($file, array_values($columns), ';');

            // Data
            foreach ($collection as $item) {
                $row = [];
                foreach (array_keys($columns) as $key) {
                    $value = data_get($item, $key);
                    if ($value instanceof \DateTime || $value instanceof \Carbon\Carbon) {
                        $value = $value->format('d/m/Y H:i');
                    }
                    $row[] = $value;
                }
                fputcsv($file, $row, ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportToPdf(string $view, array $data, string $filename): \Illuminate\Http\Response
    {
        $pdf = Pdf::loadView($view, $data);
        $pdf->setPaper('a4', 'landscape');

        return $pdf->download($filename . '.pdf');
    }
}
