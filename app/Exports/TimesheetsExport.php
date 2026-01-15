<?php

namespace App\Exports;

use App\Modules\HR\Models\Timesheet;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TimesheetsExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, ShouldQueue
{
    use Exportable;

    public function __construct(
        protected array $filters = []
    ) {}

    public function query()
    {
        $query = Timesheet::with(['employee', 'project', 'task']);

        if (!empty($this->filters['employee_id'])) {
            $query->where('employee_id', $this->filters['employee_id']);
        }

        if (!empty($this->filters['project_id'])) {
            $query->where('project_id', $this->filters['project_id']);
        }

        if (!empty($this->filters['date_from'])) {
            $query->whereDate('date', '>=', $this->filters['date_from']);
        }

        if (!empty($this->filters['date_to'])) {
            $query->whereDate('date', '<=', $this->filters['date_to']);
        }

        if (isset($this->filters['billable'])) {
            $query->where('billable', $this->filters['billable']);
        }

        return $query->orderBy('date', 'desc');
    }

    public function headings(): array
    {
        return [
            'Date',
            'Employe',
            'Projet',
            'Tache',
            'Heures',
            'Facturable',
            'Description',
        ];
    }

    public function map($timesheet): array
    {
        return [
            $timesheet->date?->format('d/m/Y'),
            $timesheet->employee?->full_name ?? 'N/A',
            $timesheet->project?->name ?? 'N/A',
            $timesheet->task?->title ?? '-',
            number_format($timesheet->hours, 2, ',', ' '),
            $timesheet->billable ? 'Oui' : 'Non',
            $timesheet->description,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E76F51']
                ],
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF']
                ]
            ],
        ];
    }
}
