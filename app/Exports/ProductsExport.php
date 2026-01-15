<?php

namespace App\Exports;

use App\Modules\Inventory\Models\Product;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductsExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, ShouldQueue
{
    use Exportable;

    public function __construct(
        protected array $filters = []
    ) {}

    public function query()
    {
        $query = Product::with('category');

        if (!empty($this->filters['type'])) {
            $query->where('type', $this->filters['type']);
        }

        if (!empty($this->filters['category_id'])) {
            $query->where('category_id', $this->filters['category_id']);
        }

        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('name');
    }

    public function headings(): array
    {
        return [
            'Reference',
            'Nom',
            'Type',
            'Categorie',
            'Prix d\'achat',
            'Prix de vente',
            'Marge (%)',
            'Stock',
        ];
    }

    public function map($product): array
    {
        $margin = $product->selling_price > 0 && $product->purchase_price > 0
            ? round((($product->selling_price - $product->purchase_price) / $product->selling_price) * 100, 2)
            : 0;

        return [
            $product->reference,
            $product->name,
            ucfirst($product->type),
            $product->category?->name ?? 'N/A',
            number_format($product->purchase_price, 2, ',', ' ') . ' FCFA',
            number_format($product->selling_price, 2, ',', ' ') . ' FCFA',
            $margin . '%',
            $product->total_stock ?? 0,
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
