<?php

namespace App\Exports;

use App\Models\Hotel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class HotelsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Hotel::select([
            'id',
            'code',
            'status'
        ])->orderBy('id', 'asc')->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Client Hotel Code',
            'Hotelbeds Hotel Code',
            'IsActive'
        ];
    }

    /**
     * @param mixed $hotel
     */
    public function map($hotel): array
    {
        return [
            $hotel->id,
            $hotel->code,
            $hotel->status ? 1 : 0,
        ];
    }

    /**
     * @param Worksheet $sheet
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold
            1 => ['font' => ['bold' => true]],
        ];
    }
}
