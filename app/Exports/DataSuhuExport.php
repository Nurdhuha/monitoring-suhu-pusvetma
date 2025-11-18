<?php

namespace App\Exports;

use App\Models\DataSuhu;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class DataSuhuExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $startDate;
    protected $endDate;

    public function __construct($startDate = null, $endDate = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    /**
    * @return \Illuminate\Database\Eloquent\Builder
    */
    public function query()
    {
        $query = DataSuhu::with(['device', 'user']);

        if ($this->startDate) {
            $query->whereDate('created_at', '>=', $this->startDate);
        }

        if ($this->endDate) {
            $query->whereDate('created_at', '<=', $this->endDate);
        }

        return $query;
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