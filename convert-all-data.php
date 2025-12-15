<?php
/**
 * Script untuk mengkonversi Logbook Suhu Coolroom DAN Freezer ke format database
 * 
 * FIXED VERSION:
 * - Handle koma sebagai pemisah desimal
 * - Tambah Freezer 7 (device_id = 10)
 * - Skip tanggal invalid
 * 
 * Mapping Device:
 * - Coolroom Depan: device_id = 1
 * - Coolroom Tengah: device_id = 2
 * - Coolroom Belakang: device_id = 3
 * - Freezer 1: device_id = 4
 * - Freezer 2: device_id = 5
 * - Freezer 3: device_id = 6
 * - Freezer 4: device_id = 7
 * - Freezer 5: device_id = 8
 * - Freezer 6: device_id = 9
 * - Freezer 7: device_id = 10
 */

error_reporting(E_ALL & ~E_WARNING);

require __DIR__.'/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$filePath = __DIR__ . '/Logbook Suhu Coolroom dan Freezer 2025 (2).xlsx';
$outputFile = __DIR__ . '/data_suhu_all_import.csv';

// Helper function to parse temperature (handle comma as decimal)
function parseTemperature($value) {
    if ($value === null || $value === '') {
        return null;
    }
    
    // If already numeric, return as float
    if (is_numeric($value)) {
        return round((float) $value, 1);
    }
    
    // If string, replace comma with dot
    if (is_string($value)) {
        $value = str_replace(',', '.', trim($value));
        if (is_numeric($value)) {
            return round((float) $value, 1);
        }
    }
    
    return null;
}

// Helper function to validate date
function isValidDate($year, $month, $day) {
    return checkdate($month, $day, $year);
}

// Freezer device mapping (including Freezer 7)
$freezerDeviceMap = [
    1 => 4,
    2 => 5,
    3 => 6,
    4 => 7,
    5 => 8,
    6 => 9,
    7 => 10,  // Freezer 7 added
];

// Coolroom device mapping
$coolroomDevices = [
    'depan' => ['id' => 1, 'pagi_suhu' => 'C', 'sore_suhu' => 'E'],
    'tengah' => ['id' => 2, 'pagi_suhu' => 'G', 'sore_suhu' => 'I'],
    'belakang' => ['id' => 3, 'pagi_suhu' => 'K', 'sore_suhu' => 'M'],
];

// Freezer sheets mapping (Jan-Oct 2025)
$freezerSheets = [
    'F Jan 25' => ['month' => 1, 'year' => 2025],
    'F Feb 25' => ['month' => 2, 'year' => 2025],
    'F Mar 25' => ['month' => 3, 'year' => 2025],
    'F Apr 25' => ['month' => 4, 'year' => 2025],
    'F Mei 25' => ['month' => 5, 'year' => 2025],
    'F Jun 25' => ['month' => 6, 'year' => 2025],
    'F Jul 25' => ['month' => 7, 'year' => 2025],
    'F Agu 25' => ['month' => 8, 'year' => 2025],
    'F Sep 25' => ['month' => 9, 'year' => 2025],
    'F Okt 25' => ['month' => 10, 'year' => 2025],
];

// Month name mapping for Coolroom sheet
$monthMap = [
    'Januari' => 1, 'Februari' => 2, 'Maret' => 3, 'April' => 4,
    'Mei' => 5, 'Juni' => 6, 'Juli' => 7, 'Agustus' => 8,
    'September' => 9, 'Oktober' => 10, 'November' => 11, 'Desember' => 12
];

echo "Loading file...\n";

try {
    $spreadsheet = IOFactory::load($filePath);
    $records = [];
    $skippedDates = 0;
    
    // ========== PROCESS COOLROOM DATA ==========
    echo "\n=== Processing Coolroom Data ===\n";
    $sheet = $spreadsheet->getSheetByName('CR 2025');
    
    if ($sheet) {
        $highestRow = $sheet->getHighestRow();
        $currentMonth = null;
        $currentYear = 2025;
        $coolroomCount = 0;
        
        for ($row = 1; $row <= $highestRow; $row++) {
            $colA = $sheet->getCell('A' . $row)->getValue();
            $colC = $sheet->getCell('C' . $row)->getValue();
            $colE = $sheet->getCell('E' . $row)->getValue();
            
            // Check if this row contains month info
            if ($colA && trim($colA) === 'BULAN :') {
                if ($colC && isset($monthMap[trim($colC)])) {
                    $currentMonth = $monthMap[trim($colC)];
                    if ($colE && is_numeric($colE)) {
                        $currentYear = (int) $colE;
                    }
                    echo "  Coolroom: $colC $currentYear\n";
                }
                continue;
            }
            
            // Skip non-data rows
            if ($colA === 'TGL' || $colA === 'JAM' || !is_numeric($colA) || !$currentMonth) {
                continue;
            }
            
            $day = (int) $colA;
            if ($day < 1 || $day > 31) continue;
            
            // Validate date
            if (!isValidDate($currentYear, $currentMonth, $day)) {
                $skippedDates++;
                continue;
            }
            
            $date = sprintf('%04d-%02d-%02d', $currentYear, $currentMonth, $day);
            
            foreach ($coolroomDevices as $name => $config) {
                // Get and parse temperatures (handle comma as decimal)
                $rawPagi = $sheet->getCell($config['pagi_suhu'] . $row)->getCalculatedValue();
                $suhuPagi = parseTemperature($rawPagi);
                
                if ($suhuPagi !== null) {
                    $records[] = [
                        'device_id' => $config['id'],
                        'section' => 'pagi',
                        'temperature' => $suhuPagi,
                        'created_at' => $date . ' 08:00:00'
                    ];
                    $coolroomCount++;
                }
                
                $rawSore = $sheet->getCell($config['sore_suhu'] . $row)->getCalculatedValue();
                $suhuSore = parseTemperature($rawSore);
                
                if ($suhuSore !== null) {
                    $records[] = [
                        'device_id' => $config['id'],
                        'section' => 'sore',
                        'temperature' => $suhuSore,
                        'created_at' => $date . ' 15:30:00'
                    ];
                    $coolroomCount++;
                }
            }
        }
        echo "  Total Coolroom records: $coolroomCount\n";
    }
    
    // ========== PROCESS FREEZER DATA ==========
    echo "\n=== Processing Freezer Data ===\n";
    $freezerCount = 0;
    
    foreach ($freezerSheets as $sheetName => $monthInfo) {
        $sheet = $spreadsheet->getSheetByName($sheetName);
        
        if (!$sheet) {
            echo "  Warning: Sheet '$sheetName' not found, skipping...\n";
            continue;
        }
        
        echo "  Processing: $sheetName\n";
        
        $highestRow = $sheet->getHighestRow();
        $currentFreezer = null;
        
        for ($row = 1; $row <= $highestRow; $row++) {
            $colA = $sheet->getCell('A' . $row)->getValue();
            $colC = $sheet->getCell('C' . $row)->getValue();
            
            // Detect Freezer number
            if ($colA === 'FREEZER' && is_numeric($colC)) {
                $currentFreezer = (int) $colC;
                continue;
            }
            
            // Skip header rows
            if (!$currentFreezer || $colA === 'TGL' || !is_numeric($colA)) {
                continue;
            }
            
            // Get device ID for this freezer
            if (!isset($freezerDeviceMap[$currentFreezer])) {
                continue;
            }
            $deviceId = $freezerDeviceMap[$currentFreezer];
            
            // Process LEFT side data (days 1-16)
            $dayLeft = (int) $colA;
            if ($dayLeft >= 1 && $dayLeft <= 31 && isValidDate($monthInfo['year'], $monthInfo['month'], $dayLeft)) {
                $dateLeft = sprintf('%04d-%02d-%02d', $monthInfo['year'], $monthInfo['month'], $dayLeft);
                
                $rawPagiLeft = $sheet->getCell('C' . $row)->getCalculatedValue();
                $suhuPagiLeft = parseTemperature($rawPagiLeft);
                if ($suhuPagiLeft !== null) {
                    $records[] = [
                        'device_id' => $deviceId,
                        'section' => 'pagi',
                        'temperature' => $suhuPagiLeft,
                        'created_at' => $dateLeft . ' 08:00:00'
                    ];
                    $freezerCount++;
                }
                
                $rawSoreLeft = $sheet->getCell('E' . $row)->getCalculatedValue();
                $suhuSoreLeft = parseTemperature($rawSoreLeft);
                if ($suhuSoreLeft !== null) {
                    $records[] = [
                        'device_id' => $deviceId,
                        'section' => 'sore',
                        'temperature' => $suhuSoreLeft,
                        'created_at' => $dateLeft . ' 15:30:00'
                    ];
                    $freezerCount++;
                }
            }
            
            // Process RIGHT side data (days 17-31)
            $colG = $sheet->getCell('G' . $row)->getValue();
            if (is_numeric($colG)) {
                $dayRight = (int) $colG;
                if ($dayRight >= 1 && $dayRight <= 31 && isValidDate($monthInfo['year'], $monthInfo['month'], $dayRight)) {
                    $dateRight = sprintf('%04d-%02d-%02d', $monthInfo['year'], $monthInfo['month'], $dayRight);
                    
                    $rawPagiRight = $sheet->getCell('I' . $row)->getCalculatedValue();
                    $suhuPagiRight = parseTemperature($rawPagiRight);
                    if ($suhuPagiRight !== null) {
                        $records[] = [
                            'device_id' => $deviceId,
                            'section' => 'pagi',
                            'temperature' => $suhuPagiRight,
                            'created_at' => $dateRight . ' 08:00:00'
                        ];
                        $freezerCount++;
                    }
                    
                    $rawSoreRight = $sheet->getCell('K' . $row)->getCalculatedValue();
                    $suhuSoreRight = parseTemperature($rawSoreRight);
                    if ($suhuSoreRight !== null) {
                        $records[] = [
                            'device_id' => $deviceId,
                            'section' => 'sore',
                            'temperature' => $suhuSoreRight,
                            'created_at' => $dateRight . ' 15:30:00'
                        ];
                        $freezerCount++;
                    }
                }
            }
        }
    }
    echo "  Total Freezer records: $freezerCount\n";
    echo "  Skipped invalid dates: $skippedDates\n";
    
    // ========== WRITE TO CSV ==========
    echo "\n=== Writing CSV ===\n";
    
    $totalRecords = count($records);
    echo "Total records to export: $totalRecords\n";
    
    // Sort by created_at
    usort($records, function($a, $b) {
        return strcmp($a['created_at'], $b['created_at']);
    });
    
    $fp = fopen($outputFile, 'w');
    fputcsv($fp, ['device_id', 'section', 'temperature', 'created_at'], ';');
    
    foreach ($records as $record) {
        fputcsv($fp, $record, ';');
    }
    
    fclose($fp);
    
    echo "CSV file created: $outputFile\n";
    
    // Summary by device
    echo "\n=== Summary by Device ===\n";
    $deviceCounts = [];
    foreach ($records as $r) {
        $deviceCounts[$r['device_id']] = ($deviceCounts[$r['device_id']] ?? 0) + 1;
    }
    ksort($deviceCounts);
    
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
    
    foreach ($deviceCounts as $deviceId => $count) {
        $name = $deviceNames[$deviceId] ?? "Device $deviceId";
        echo sprintf("  Device %d (%s): %d records\n", $deviceId, $name, $count);
    }
    
    echo "\n=== DONE ===\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
