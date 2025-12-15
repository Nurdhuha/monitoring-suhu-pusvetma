<?php

namespace App\Imports;

use App\Models\DataSuhu;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DataSuhuImport implements ToModel, WithHeadingRow, WithCustomCsvSettings, WithBatchInserts, WithChunkReading, WithCalculatedFormulas
{
    private $rowCount = 0;
    private $insertedCount = 0;

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        $this->rowCount++;
        
        // Dump first 3 rows for debugging
        if ($this->rowCount <= 3) {
            Log::info('DataSuhuImport: RAW ROW ' . $this->rowCount, [
                'keys' => array_keys($row),
                'values' => $row
            ]);
        }

        // Fix temperature decimal (comma to dot)
        $temperature = isset($row['temperature']) ? str_replace(',', '.', trim($row['temperature'])) : null;

        // Validate required fields
        if (empty($row['device_id']) || empty($row['section']) || $temperature === null || $temperature === '') {
            Log::warning('DataSuhuImport: Skipping row ' . $this->rowCount . ' due to missing required fields.', [
                'device_id' => $row['device_id'] ?? 'MISSING',
                'section' => $row['section'] ?? 'MISSING',
                'temperature' => $temperature
            ]);
            return null; // Skip this row
        }
        
        // Validate section
        $section = strtolower(trim($row['section']));
        if (!in_array($section, ['pagi', 'sore'])) {
            Log::warning('DataSuhuImport: Skipping row ' . $this->rowCount . ' due to invalid section: ' . $section);
            return null;
        }

        // Validate device_id exists
        if (!\App\Models\Device::find($row['device_id'])) {
            Log::warning('DataSuhuImport: Skipping row ' . $this->rowCount . ' due to device not found: ' . $row['device_id']);
            return null;
        }

        // Handle Date - check both 'created_at' and 'createdat' (WithHeadingRow removes underscores)
        $createdAt = Carbon::now();
        $dateColumn = isset($row['created_at']) ? 'created_at' : (isset($row['createdat']) ? 'createdat' : null);
        
        if ($dateColumn && isset($row[$dateColumn]) && !empty(trim($row[$dateColumn]))) {
            try {
                $dateValue = trim($row[$dateColumn]);
                
                // Log the raw date value for debugging
                if ($this->rowCount <= 3) {
                    Log::info('DataSuhuImport: Row ' . $this->rowCount . ' date column: ' . $dateColumn . ', value: ' . $dateValue);
                }
                
                if (is_numeric($dateValue)) {
                    // Convert Excel date number to DateTime, then to Carbon
                    $dateTime = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($dateValue);
                    $createdAt = Carbon::instance($dateTime);
                } else {
                    // Parse string date
                    $createdAt = Carbon::parse($dateValue);
                }
                
                if ($this->rowCount <= 3) {
                    Log::info('DataSuhuImport: Row ' . $this->rowCount . ' parsed date: ' . $createdAt->format('Y-m-d H:i:s'));
                }
            } catch (\Exception $e) {
                Log::warning('DataSuhuImport: Date parse failed for row ' . $this->rowCount . ': ' . $e->getMessage() . ', value: ' . ($dateValue ?? 'null'));
            }
        } else {
            Log::warning('DataSuhuImport: Row ' . $this->rowCount . ' - No date column found or empty. Using current time.');
        }

        $this->insertedCount++;
        Log::info('DataSuhuImport: Inserting row ' . $this->rowCount . ' with created_at: ' . $createdAt->format('Y-m-d H:i:s'));

        return new DataSuhu([
            'device_id'   => $row['device_id'], 
            'section'     => $section,
            'temperature' => (float) $temperature,
            'user_id'     => Auth::id(),
            'created_at'  => $createdAt,
            'updated_at'  => $createdAt,
        ]);
    }

    public function batchSize(): int
    {
        return 100;
    }

    public function chunkSize(): int
    {
        return 100;
    }

    public function getCsvSettings(): array
    {
        return [
            'input_encoding' => 'UTF-8',
            'delimiter' => ';',
        ];
    }

    public function __destruct()
    {
        Log::info('DataSuhuImport: Completed. Total rows processed: ' . $this->rowCount . ', Inserted: ' . $this->insertedCount);
    }
}
