<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class TestHeaderReader implements ToArray, WithHeadingRow
{
    public function array(array $array)
    {
        echo "Total rows: " . count($array) . "\n\n";
        
        if (count($array) > 0) {
            echo "First row keys:\n";
            print_r(array_keys($array[0]));
            
            echo "\nFirst row data:\n";
            print_r($array[0]);
            
            echo "\nSecond row data:\n";
            if (isset($array[1])) {
                print_r($array[1]);
            }
        }
        
        return $array;
    }
}

$filePath = __DIR__ . '/Coolroom Depan Januari.csv';
echo "Reading file: {$filePath}\n\n";

Excel::import(new TestHeaderReader, $filePath);

echo "\n\nDone!\n";
