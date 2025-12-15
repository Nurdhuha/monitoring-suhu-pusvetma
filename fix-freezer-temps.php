<?php
error_reporting(E_ALL & ~E_WARNING);
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\DataSuhu;
use Illuminate\Support\Facades\DB;

echo "=== UPDATE FREEZER DATA TO NEGATIVE ===\n\n";

$freezerIds = [4, 5, 6, 7, 8, 9, 10];
$deviceNames = [
    4 => 'Freezer 1',
    5 => 'Freezer 2',
    6 => 'Freezer 3',
    7 => 'Freezer 4',
    8 => 'Freezer 5',
    9 => 'Freezer 6',
    10 => 'Freezer 7',
];

// Count positive values before update
$positiveCountBefore = DataSuhu::whereIn('device_id', $freezerIds)
    ->where('temperature', '>', 0)
    ->count();

echo "Positive Freezer values before update: $positiveCountBefore\n\n";

// Show sample of what will be updated
echo "Sample values to be converted:\n";
$samples = DataSuhu::whereIn('device_id', $freezerIds)
    ->where('temperature', '>', 0)
    ->orderBy('created_at')
    ->limit(5)
    ->get();

foreach ($samples as $s) {
    $name = $deviceNames[$s->device_id];
    $newTemp = -abs($s->temperature);
    echo "  {$s->created_at} | $name | {$s->temperature}°C -> {$newTemp}°C\n";
}

echo "\nUpdating all positive Freezer temperatures to negative...\n";

// Update all positive Freezer temperatures to negative
$updated = DB::table('data_suhu')
    ->whereIn('device_id', $freezerIds)
    ->where('temperature', '>', 0)
    ->update([
        'temperature' => DB::raw('temperature * -1')
    ]);

echo "Updated: $updated records\n";

// Verify
$positiveCountAfter = DataSuhu::whereIn('device_id', $freezerIds)
    ->where('temperature', '>', 0)
    ->count();

echo "\nPositive Freezer values after update: $positiveCountAfter\n";

// Show new stats
echo "\n=== New Freezer Stats ===\n";
foreach ($freezerIds as $deviceId) {
    $name = $deviceNames[$deviceId];
    
    $stats = DataSuhu::where('device_id', $deviceId)
        ->selectRaw('MIN(temperature) as min_temp, MAX(temperature) as max_temp, AVG(temperature) as avg_temp')
        ->first();
    
    echo "$name: Min={$stats->min_temp}°C, Max={$stats->max_temp}°C, Avg=" . round($stats->avg_temp, 1) . "°C\n";
}

echo "\n=== DONE ===\n";
