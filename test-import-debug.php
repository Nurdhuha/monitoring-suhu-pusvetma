<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Login as first user
$user = App\Models\User::first();
Auth::login($user);
echo "Logged in as: {$user->name} (ID: {$user->id})\n";

// Count before
$countBefore = App\Models\DataSuhu::count();
echo "Records before import: {$countBefore}\n";

// Import
$filePath = __DIR__ . '/Coolroom Depan Januari.csv';
echo "Importing file: {$filePath}\n";

try {
    Excel::import(new App\Imports\DataSuhuImport, $filePath);
    echo "Import completed!\n";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "\nTrace:\n" . $e->getTraceAsString() . "\n";
}

// Count after
$countAfter = App\Models\DataSuhu::count();
echo "Records after import: {$countAfter}\n";
echo "New records: " . ($countAfter - $countBefore) . "\n";

// Show last 3
$lastRecords = App\Models\DataSuhu::orderBy('id', 'desc')->take(3)->get();
echo "\nLast 3 records:\n";
foreach ($lastRecords as $record) {
    echo "  ID: {$record->id}, Device: {$record->device_id}, Section: {$record->section}, Temp: {$record->temperature}, Created: {$record->created_at}\n";
}
