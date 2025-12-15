<?php
error_reporting(E_ALL & ~E_WARNING);
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\DataSuhu;

$freezerIds = [4, 5, 6, 7, 8, 9, 10];

$positive = DataSuhu::whereIn('device_id', $freezerIds)->where('temperature', '>', 0)->count();
$negative = DataSuhu::whereIn('device_id', $freezerIds)->where('temperature', '<', 0)->count();
$zero = DataSuhu::whereIn('device_id', $freezerIds)->where('temperature', '=', 0)->count();

echo "Freezer Temperature Check:\n";
echo "  Positive (> 0): $positive\n";
echo "  Negative (< 0): $negative\n";
echo "  Zero (= 0): $zero\n";

if ($positive > 0) {
    echo "\nSome positive values still exist. Check sample:\n";
    $samples = DataSuhu::whereIn('device_id', $freezerIds)
        ->where('temperature', '>', 0)
        ->limit(5)
        ->get();
    foreach ($samples as $s) {
        echo "  Device {$s->device_id}: {$s->temperature}°C at {$s->created_at}\n";
    }
} else {
    echo "\nAll Freezer temperatures are negative or zero. SUCCESS!\n";
}
