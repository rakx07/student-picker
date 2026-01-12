<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SelectedExport implements FromArray, WithHeadings
{
    public function __construct(private array $selected)
    {
    }

    public function headings(): array
    {
        return ['Draw No', 'Student Name', 'Drawn At'];
    }

    public function array(): array
    {
        return array_map(function ($row) {
            return [
                $row['draw_no'] ?? '',
                $row['name'] ?? '',
                $row['drawn_at'] ?? '',
            ];
        }, $this->selected);
    }
}
