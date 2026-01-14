<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ProductsReportExport implements FromArray, WithHeadings
{
    public function __construct(private array $data)
    {
    }

    public function headings(): array
    {
        return [
            'Metric',
            'Value',
            'Extra',
        ];
    }

    public function array(): array
    {
        $rows = [
            ['Low stock items', '', ''],
        ];

        foreach ($this->data['low_stock'] ?? [] as $product) {
            $rows[] = [$product->name, $product->stock, ''];
        }

        $rows[] = ['Best sellers', '', ''];

        foreach ($this->data['best_sellers'] ?? [] as $item) {
            $rows[] = [
                $item->product?->name ?? 'Unknown',
                $item->total_qty ?? 0,
                $item->total_revenue ?? 0,
            ];
        }

        return $rows;
    }
}
