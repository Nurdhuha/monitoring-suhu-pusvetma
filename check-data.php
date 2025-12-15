<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\DataSuhu;
use Illuminate\Support\Facades\DB;

echo "=== DATA SUHU SUMMARY ===\n\n";

$total = DataSuhu::count();
echo "Total records in database: $total\n\n";

echo "=== By Device ===\n";
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
];

foreach ($deviceCounts as $row) {
    $name = $deviceNames[$row->device_id] ?? "Device {$row->device_id}";
    echo sprintf("  Device %d (%s): %d records\n", $row->device_id, $name, $row->count);
}

echo "\n=== Date Range ===\n";
$dateRange = DataSuhu::selectRaw('MIN(created_at) as min_date, MAX(created_at) as max_date')->first();
echo "  From: " . $dateRange->min_date . "\n";
echo "  To:   " . $dateRange->max_date . "\n";

echo "\n=== Sample Data (first 5) ===\n";
$samples = DataSuhu::orderBy('created_at')->limit(5)->get();
foreach ($samples as $s) {
    $deviceName = $deviceNames[$s->device_id] ?? "Device {$s->device_id}";
    echo sprintf("  %s | %s | %.1f°C | %s\n", $s->created_at, $deviceName, $s->temperature, $s->section);
}

echo "\nDone!\n";
