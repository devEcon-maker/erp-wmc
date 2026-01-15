<?php

namespace App\Exports;

use App\Modules\Finance\Models\Invoice;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InvoicesExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, ShouldQueue
{
    use Exportable;

    public function __construct(
        protected array $filters = []
    ) {}

    public function query()
    {
        $query = Invoice::with('contact');

        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        if (!empty($this->filters['type'])) {
            $query->where('type', $this->filters['type']);
        }

        if (!empty($this->filters['date_from'])) {
            $query->whereDate('date', '>=', $this->filters['date_from']);
        }

        if (!empty($this->filters['date_to'])) {
            $query->whereDate('date', '<=', $this->filters['date_to']);
        }

        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                    ->orWhereHas('contact', function ($q2) use ($search) {
                        $q2->where('company_name', 'like', "%{$search}%");
                    });
            });
        }

        return $query->orderBy('date', 'desc');
    }

    public function headings(): array
    {
        return [
            'Reference',
            'Type',
            'Client',
            'Date',
            'Echeance',
            'Total HT',
            'Total TTC',
            'Paye',
            'Solde',
            'Statut',
        ];
    }

    public function map($invoice): array
    {
        $statusLabels = [
            'draft' => 'Brouillon',
            'sent' => 'Envoyee',
            'paid' => 'Payee',
            'partial' => 'Partielle',
            'overdue' => 'En retard',
            'cancelled' => 'Annulee',
        ];

        return [
            $invoice->reference,
            ucfirst($invoice->type),
            $invoice->contact?->display_name ?? 'N/A',
            $invoice->date?->format('d/m/Y'),
            $invoice->due_date?->format('d/m/Y'),
            number_format($invoice->total_ht, 2, ',', ' ') . ' FCFA',
            number_format($invoice->total_ttc, 2, ',', ' ') . ' FCFA',
            number_format($invoice->paid_amount, 2, ',', ' ') . ' FCFA',
            number_format($invoice->total_ttc - $invoice->paid_amount, 2, ',', ' ') . ' FCFA',
            $statusLabels[$invoice->status] ?? $invoice->status,
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
