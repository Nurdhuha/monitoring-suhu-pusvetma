<?php
/**
 * Script untuk mengimport data suhu ke database
 * FIXED VERSION - handles all edge cases
 */

error_reporting(E_ALL & ~E_WARNING);

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\DataSuhu;
use Illuminate\Support\Facades\DB;

$csvFile = __DIR__ . '/data_suhu_all_import.csv';

echo "=== IMPORT DATA SUHU ===\n\n";

// Read CSV
$handle = fopen($csvFile, 'r');
$header = fgetcsv($handle, 0, ';');
echo "Headers: " . implode(', ', $header) . "\n\n";

$records = [];

while (($data = fgetcsv($handle, 0, ';')) !== FALSE) {
    if (count($data) >= 4) {
        $dateStr = str_replace('"', '', $data[3]);
        
        $records[] = [
            'device_id' => (int) $data[0],
            'section' => $data[1],
            'temperature' => (float) $data[2],
            'created_at' => $dateStr,
            'updated_at' => $dateStr,
            'user_id' => 1,
        ];
    }
}

fclose($handle);

echo "Total records to import: " . count($records) . "\n";
echo "Starting import...\n\n";

// Import in batches
$batchSize = 100;
$batches = array_chunk($records, $batchSize);
$totalBatches = count($batches);
$inserted = 0;
$errors = 0;

foreach ($batches as $index => $batch) {
    $batchNum = $index + 1;
    
    try {
        DB::table('data_suhu')->insert($batch);
        $inserted += count($batch);
        
        if ($batchNum % 10 == 0 || $batchNum == $totalBatches) {
            echo "Batch $batchNum/$totalBatches: $inserted records inserted\n";
        }
    } catch (Exception $e) {
        $errors++;
        echo "Batch $batchNum ERROR: " . substr($e->getMessage(), 0, 80) . "\n";
        
        // Try inserting one by one
        foreach ($batch as $record) {
            try {
                DB::table('data_suhu')->insert($record);
                $inserted++;
            } catch (Exception $e2) {
                // Skip this record
            }
        }
    }
}

echo "\n=== IMPORT COMPLETE ===\n";
echo "Total records inserted: $inserted\n";
echo "Total errors: $errors\n";

// Verify
$totalInDb = DataSuhu::count();
echo "Total records in database: $totalInDb\n";

// Show summary by device
echo "\n=== Summary by Device ===\n";
$deviceCounts = DataSuhu::select('device_id', DB::raw('count(*) as count'))
    ->groupBy('device_id')
    ->orderBy('device_id')
    ->get();

$deviceNames = [
    1 => 'Coolroom Depan',
    2 => 'Coolroom Tengah',
    3 => 'Coolroom Belakang',
    4 => 'Freezer 1',
    5 => 'Freezer 2',
    6 => 'Freezer 3',
    7 => 'Freezer 4',
    8 => 'Freezer 5',
    9 => 'Freezer 6',
    10 => 'Freezer 7',
];

foreach ($deviceCounts as $row) {
    $name = $deviceNames[$row->device_id] ?? "Device {$row->device_id}";
    echo sprintf("  Device %d (%s): %d records\n", $row->device_id, $name, $row->count);
}

echo "\n=== DONE ===\n";
