<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class RemainingExport implements FromArray, WithHeadings
{
    public function __construct(private array $remaining)
    {
    }

    public function headings(): array
    {
        return ['Student Name'];
    }

    public function array(): array
    {
        return array_map(fn ($name) => [$name], $this->remaining);
    }
}
