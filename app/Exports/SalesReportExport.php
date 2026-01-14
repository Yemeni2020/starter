<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SalesReportExport implements FromArray, WithHeadings
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
            ['Total orders', $this->data['total_orders'] ?? 0, ''],
            ['Revenue', $this->data['revenue'] ?? 0, ''],
            ['Paid orders', $this->data['paid_orders'] ?? 0, ''],
            ['Unpaid orders', $this->data['unpaid_orders'] ?? 0, ''],
            ['Top products', '', ''],
        ];

        foreach ($this->data['top_products'] ?? [] as $item) {
            $rows[] = [
                $item->product?->name ?? 'Unknown',
                $item->total_qty ?? 0,
                $item->total_revenue ?? 0,
            ];
        }

        return $rows;
    }
}
