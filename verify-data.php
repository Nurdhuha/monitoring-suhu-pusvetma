<?php
error_reporting(E_ALL & ~E_WARNING);
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\DataSuhu;
use Illuminate\Support\Facades\DB;

echo "=== VERIFIKASI DATA IMPORT ===\n\n";

// Total records
$total = DataSuhu::count();
echo "Total records in database: $total\n\n";

// Check by device
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

echo "=== Records per Device ===\n";
$deviceCounts = DataSuhu::select('device_id', DB::raw('count(*) as count'))
    ->groupBy('device_id')
    ->orderBy('device_id')
    ->get();

foreach ($deviceCounts as $row) {
    $name = $deviceNames[$row->device_id] ?? "Device {$row->device_id}";
    echo sprintf("  Device %d (%s): %d records\n", $row->device_id, $name, $row->count);
}

// Check Freezer temps
echo "\n=== Freezer Temperature Check ===\n";
$freezerIds = [4, 5, 6, 7, 8, 9, 10];
$freezerStats = DataSuhu::whereIn('device_id', $freezerIds)
    ->selectRaw('
        SUM(CASE WHEN temperature > 0 THEN 1 ELSE 0 END) as positive,
        SUM(CASE WHEN temperature < 0 THEN 1 ELSE 0 END) as negative,
        SUM(CASE WHEN temperature = 0 THEN 1 ELSE 0 END) as zero,
        COUNT(*) as total
    ')
    ->first();

echo "  Total Freezer records: {$freezerStats->total}\n";
echo "  Negative temps: {$freezerStats->negative}\n";
echo "  Positive temps: {$freezerStats->positive}\n";
echo "  Zero temps: {$freezerStats->zero}\n";

if ($freezerStats->positive > 0) {
    echo "\n  WARNING: Some positive Freezer temps found!\n";
} else {
    echo "\n  ✓ All Freezer temps are negative or zero\n";
}

echo "\n=== Date Range ===\n";
$dateRange = DataSuhu::selectRaw('MIN(created_at) as min_date, MAX(created_at) as max_date')->first();
echo "  From: " . $dateRange->min_date . "\n";
echo "  To:   " . $dateRange->max_date . "\n";

echo "\n=== VERIFICATION COMPLETE ===\n";
