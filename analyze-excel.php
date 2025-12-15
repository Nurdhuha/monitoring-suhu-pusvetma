<?php
error_reporting(E_ALL & ~E_WARNING);

require __DIR__.'/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$filePath = __DIR__ . '/Logbook Suhu Coolroom dan Freezer 2025 (2).xlsx';
$outputFile = __DIR__ . '/excel-structure.json';

try {
    $spreadsheet = IOFactory::load($filePath);
    $sheet = $spreadsheet->getSheetByName('CR 2025');
    
    $result = [
        'sheet' => 'CR 2025',
        'rows' => []
    ];
    
    // Read rows 1-40 to understand structure
    for ($row = 1; $row <= 40; $row++) {
        $data = [];
        foreach (range('A', 'N') as $col) {
            $cell = $sheet->getCell($col.$row);
            $val = $cell->getCalculatedValue();
            if ($val !== null && $val !== '') {
                $data[$col] = is_string($val) ? substr(str_replace(["\n", "\r"], ' ', $val), 0, 25) : $val;
            }
        }
        if (!empty($data)) {
            $result['rows'][$row] = $data;
        }
    }
    
    file_put_contents($outputFile, json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    echo "Output saved to: $outputFile\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
