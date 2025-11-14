<?php

namespace App\Exports;

use App\Models\DataSuhu;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class DataSuhuExport implements FromCollection, WithHeadings, WithMapping
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        // Ambil semua data suhu dengan relasi device dan user
        return DataSuhu::with(['device', 'user'])->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        // Tentukan nama kolom di file Excel
        return [
            'ID',
            'Device Name',
            'Location',
            'Admin',
            'Section',
            'Temperature (°C)',
            'Recorded At',
        ];
    }

    /**
     * @param mixed $dataSuhu
     *
     * @return array
     */
    public function map($dataSuhu): array
    {
        // Petakan setiap baris data ke kolom yang sesuai
        return [
            $dataSuhu->id,
            $dataSuhu->device->name ?? 'N/A',
            $dataSuhu->device->location ?? 'N/A',
            $dataSuhu->user->name ?? 'N/A',
            ucfirst($dataSuhu->section),
            $dataSuhu->temperature,
            $dataSuhu->created_at->format('d/m/Y H:i:s'),
        ];
    }
}