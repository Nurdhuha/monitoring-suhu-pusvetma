<?php

namespace App\Exports;

use App\Models\DataSuhu;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class DataSuhuExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $deviceId;
    protected $startDate;
    protected $endDate;

    public function __construct($deviceId = null, $startDate = null, $endDate = null)
    {
        $this->deviceId = $deviceId;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    /**
    * @return \Illuminate\Database\Eloquent\Builder
    */
    public function query()
    {
        $query = DataSuhu::with(['device', 'user'])->latest();

        if ($this->deviceId) {
            $query->where('device_id', $this->deviceId);
        }

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
            'Device Name',
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
            $dataSuhu->device->name ?? 'N/A',
            $dataSuhu->user->name ?? 'N/A',
            ucfirst($dataSuhu->section),
            $dataSuhu->temperature,
            $dataSuhu->created_at->format('d/m/Y H:i:s'),
        ];
    }
}