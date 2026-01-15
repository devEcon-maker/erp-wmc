<?php

namespace App\Exports;

use App\Modules\HR\Models\LeaveBalance;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LeaveBalancesExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, ShouldQueue
{
    use Exportable;

    public function __construct(
        protected int $year,
        protected array $filters = []
    ) {}

    public function query()
    {
        $query = LeaveBalance::with(['employee', 'leaveType'])
            ->where('year', $this->year);

        if (!empty($this->filters['department_id'])) {
            $query->whereHas('employee', function ($q) {
                $q->where('department_id', $this->filters['department_id']);
            });
        }

        if (!empty($this->filters['leave_type_id'])) {
            $query->where('leave_type_id', $this->filters['leave_type_id']);
        }

        return $query->orderBy('employee_id');
    }

    public function headings(): array
    {
        return [
            'Employe',
            'Matricule',
            'Type de conge',
            'Annee',
            'Alloue',
            'Utilise',
            'En attente',
            'Disponible',
        ];
    }

    public function map($balance): array
    {
        return [
            $balance->employee?->full_name ?? 'N/A',
            $balance->employee?->employee_number ?? 'N/A',
            $balance->leaveType?->name ?? 'N/A',
            $balance->year,
            number_format($balance->allocated, 1, ',', ' '),
            number_format($balance->used, 1, ',', ' '),
            number_format($balance->pending, 1, ',', ' '),
            number_format($balance->available, 1, ',', ' '),
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
