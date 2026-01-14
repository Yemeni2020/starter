<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CustomersReportExport implements FromArray, WithHeadings
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
            ['New customers', $this->data['new_customers'] ?? 0, ''],
            ['Top spenders', '', ''],
        ];

        foreach ($this->data['top_spenders'] ?? [] as $item) {
            $rows[] = [
                $item->user?->name ?? 'Unknown',
                $item->total_spent ?? 0,
                $item->orders_count ?? 0,
            ];
        }

        return $rows;
    }
}
