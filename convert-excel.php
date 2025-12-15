<?php
/**
 * Script untuk mengkonversi Logbook Suhu Coolroom ke format database
 * 
 * Mapping:
 * - Coolroom Depan: device_id = 1
 * - Coolroom Tengah: device_id = 2
 * - Coolroom Belakang: device_id = 3
 */

error_reporting(E_ALL & ~E_WARNING);

require __DIR__.'/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$filePath = __DIR__ . '/Logbook Suhu Coolroom dan Freezer 2025 (2).xlsx';
$outputFile = __DIR__ . '/data_suhu_import.csv';

// Device mapping
$devices = [
    'depan' => ['id' => 1, 'pagi_suhu' => 'C', 'sore_suhu' => 'E'],
    'tengah' => ['id' => 2, 'pagi_suhu' => 'G', 'sore_suhu' => 'I'],
    'belakang' => ['id' => 3, 'pagi_suhu' => 'K', 'sore_suhu' => 'M'],
];

// Mapping month names to numbers
$monthMap = [
    'Januari' => 1, 'Februari' => 2, 'Maret' => 3, 'April' => 4,
    'Mei' => 5, 'Juni' => 6, 'Juli' => 7, 'Agustus' => 8,
    'September' => 9, 'Oktober' => 10, 'November' => 11, 'Desember' => 12
];

echo "Loading file...\n";

try {
    $spreadsheet = IOFactory::load($filePath);
    $sheet = $spreadsheet->getSheetByName('CR 2025');
    
    if (!$sheet) {
        die("Sheet 'CR 2025' not found\n");
    }
    
    $highestRow = $sheet->getHighestRow();
    echo "Processing sheet 'CR 2025' with $highestRow rows...\n";
    
    $records = [];
    $currentMonth = null;
    $currentYear = 2025;
    $recordCount = 0;
    
    for ($row = 1; $row <= $highestRow; $row++) {
        $colA = $sheet->getCell('A' . $row)->getValue();
        $colC = $sheet->getCell('C' . $row)->getValue();
        $colE = $sheet->getCell('E' . $row)->getValue();
        
        // Check if this row contains month info (BULAN :)
        if ($colA && trim($colA) === 'BULAN :') {
            if ($colC && isset($monthMap[trim($colC)])) {
                $currentMonth = $monthMap[trim($colC)];
                if ($colE && is_numeric($colE)) {
                    $currentYear = (int) $colE;
                }
                echo "  Found month: $colC $currentYear (Month number: $currentMonth)\n";
            }
            continue;
        }
        
        // Skip header rows
        if ($colA === 'TGL' || $colA === 'JAM' || !is_numeric($colA)) {
            continue;
        }
        
        // If we don't have a month yet, skip
        if (!$currentMonth) {
            continue;
        }
        
        $day = (int) $colA;
        
        // Validate day
        if ($day < 1 || $day > 31) {
            continue;
        }
        
        // Create date
        $date = sprintf('%04d-%02d-%02d', $currentYear, $currentMonth, $day);
        
        // Process each device
        foreach ($devices as $name => $config) {
            $deviceId = $config['id'];
            
            // Get morning temperature
            $suhuPagi = $sheet->getCell($config['pagi_suhu'] . $row)->getCalculatedValue();
            if (is_numeric($suhuPagi)) {
                $records[] = [
                    'device_id' => $deviceId,
                    'section' => 'pagi',
                    'temperature' => round($suhuPagi, 1),
                    'created_at' => $date . ' 08:00:00'
                ];
                $recordCount++;
            }
            
            // Get afternoon temperature
            $suhuSore = $sheet->getCell($config['sore_suhu'] . $row)->getCalculatedValue();
            if (is_numeric($suhuSore)) {
                $records[] = [
                    'device_id' => $deviceId,
                    'section' => 'sore',
                    'temperature' => round($suhuSore, 1),
                    'created_at' => $date . ' 15:30:00'
                ];
                $recordCount++;
            }
        }
    }
    
    echo "\nTotal records to export: $recordCount\n";
    
    // Write to CSV
    $fp = fopen($outputFile, 'w');
    
    // Write header
    fputcsv($fp, ['device_id', 'section', 'temperature', 'created_at'], ';');
    
    // Write data
    foreach ($records as $record) {
        fputcsv($fp, $record, ';');
    }
    
    fclose($fp);
    
    echo "CSV file created: $outputFile\n";
    echo "\nPreview (first 10 records):\n";
    echo str_repeat('-', 60) . "\n";
    echo sprintf("%-10s %-8s %-12s %s\n", 'device_id', 'section', 'temperature', 'created_at');
    echo str_repeat('-', 60) . "\n";
    
    for ($i = 0; $i < min(10, count($records)); $i++) {
        $r = $records[$i];
        echo sprintf("%-10s %-8s %-12s %s\n", $r['device_id'], $r['section'], $r['temperature'], $r['created_at']);
    }
    
    echo str_repeat('-', 60) . "\n";
    echo "\nDone! You can now import '$outputFile' using the application.\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
