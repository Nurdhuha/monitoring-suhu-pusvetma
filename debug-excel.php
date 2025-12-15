<?php
error_reporting(E_ALL & ~E_WARNING);
require __DIR__.'/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$filePath = __DIR__ . '/Logbook Suhu Coolroom dan Freezer 2025 (2).xlsx';

$spreadsheet = IOFactory::load($filePath);
$sheet = $spreadsheet->getSheetByName('CR 2025');
$highestRow = $sheet->getHighestRow();

$monthMap = [
    'Januari' => 1, 'Februari' => 2, 'Maret' => 3, 'April' => 4,
    'Mei' => 5, 'Juni' => 6, 'Juli' => 7, 'Agustus' => 8,
    'September' => 9, 'Oktober' => 10, 'November' => 11, 'Desember' => 12
];

$result = ['months' => [], 'analysis' => []];

// Find all months
for ($row = 1; $row <= $highestRow; $row++) {
    $colA = $sheet->getCell('A' . $row)->getValue();
    $colC = $sheet->getCell('C' . $row)->getValue();
    
    if ($colA && trim($colA) === 'BULAN :') {
        $monthName = trim($colC);
        $result['months'][] = [
            'row' => $row,
            'month' => $monthName,
            'month_num' => $monthMap[$monthName] ?? null
        ];
    }
}

// Analyze Juli and Agustus
foreach ($result['months'] as $idx => $m) {
    if (in_array($m['month'], ['Juni', 'Juli', 'Agustus', 'September'])) {
        $startRow = $m['row'];
        $nextMonthRow = isset($result['months'][$idx + 1]) ? $result['months'][$idx + 1]['row'] : $highestRow;
        
        $analysis = [
            'month' => $m['month'],
            'start_row' => $startRow,
            'next_month_row' => $nextMonthRow,
            'sample_data' => []
        ];
        
        // Get sample data rows
        for ($r = $startRow + 5; $r <= min($startRow + 15, $nextMonthRow); $r++) {
            $colA = $sheet->getCell('A' . $r)->getValue();
            if (is_numeric($colA) && $colA >= 1 && $colA <= 31) {
                $row_data = ['row' => $r, 'TGL' => $colA];
                
                // Coolroom Depan
                $row_data['CR_Depan_Pagi'] = $sheet->getCell('C' . $r)->getCalculatedValue();
                $row_data['CR_Depan_Sore'] = $sheet->getCell('E' . $r)->getCalculatedValue();
                
                // Coolroom Tengah 
                $row_data['CR_Tengah_Pagi'] = $sheet->getCell('G' . $r)->getCalculatedValue();
                $row_data['CR_Tengah_Sore'] = $sheet->getCell('I' . $r)->getCalculatedValue();
                
                // Coolroom Belakang
                $row_data['CR_Belakang_Pagi'] = $sheet->getCell('K' . $r)->getCalculatedValue();
                $row_data['CR_Belakang_Sore'] = $sheet->getCell('M' . $r)->getCalculatedValue();
                
                $analysis['sample_data'][] = $row_data;
            }
        }
        
        $result['analysis'][] = $analysis;
    }
}

file_put_contents(__DIR__ . '/debug-analysis.json', json_encode($result, JSON_PRETTY_PRINT));
echo "Saved to debug-analysis.json\n";
