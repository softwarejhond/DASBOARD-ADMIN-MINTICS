<?php
require __DIR__ . '../../../vendor/autoload.php';
require __DIR__ . '/../../controller/conexion.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

// Crear nueva hoja de cálculo
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Reporte de Horas');

// Establecer encabezados
$sheet->setCellValue('A1', 'Número de Identificación');
$sheet->setCellValue('B1', 'Nombre del Estudiante');
$sheet->setCellValue('C1', 'Programa');

// Títulos principales
$sheet->setCellValue('D1', 'Técnico');
$sheet->setCellValue('G1', 'Inglés');
$sheet->setCellValue('J1', 'Habilidades');
$sheet->setCellValue('M1', 'TOTALES');

// Subtítulos - Corregir el bucle
$subHeaders = ['Horas actuales', 'Horas reales', 'Total de Horas'];
$columns = ['D', 'G', 'J', 'M'];
foreach ($columns as $col) {
    $currentCol = $col;
    for ($i = 0; $i < 3; $i++) {
        $sheet->setCellValue($currentCol . '2', $subHeaders[$i]);
        $currentCol = chr(ord($currentCol) + 1);
    }
}

// Consulta SQL
$sql = "SELECT * FROM groups";
$result = $conn->query($sql);

$row = 3; // Comenzar datos en fila 3
while($data = $result->fetch_assoc()) {
    // Datos básicos
    $sheet->setCellValue('A' . $row, $data['number_id']);
    $sheet->setCellValue('B' . $row, $data['full_name']);
    $sheet->setCellValue('C' . $row, $data['id_bootcamp'] . ' - ' . $data['bootcamp_name']);
    
    // Técnico
    $sheet->setCellValue('D' . $row, $data['b_intensity']);
    $sheet->setCellValue('E' . $row, $data['b_reals']); // Horas reales
    $sheet->setCellValue('F' . $row, 120); // Total de horas técnico
    
    // Inglés
    $sheet->setCellValue('G' . $row, $data['ec_intensity']);
    $sheet->setCellValue('H' . $row, $data['ec_reals']); // Horas reales
    $sheet->setCellValue('I' . $row, 24); // Total de horas inglés
    
    // Habilidades
    $sheet->setCellValue('J' . $row, $data['s_intensity']);
    $sheet->setCellValue('K' . $row, $data['s_reals']); // Horas reales
    $sheet->setCellValue('L' . $row, 15); // Total de horas habilidades
    
    // Totales
    $totalActual = $data['b_intensity'] + $data['ec_intensity'] + $data['s_intensity'];
    $totalReales = $data['b_reals'] + $data['ec_reals'] + $data['s_reals'];
    $sheet->setCellValue('M' . $row, $totalActual);
    $sheet->setCellValue('N' . $row, $totalReales); // Total horas reales
    $sheet->setCellValue('O' . $row, 159); // Total de horas general
    
    $row++;
}

// Fusionar celdas de encabezados
$sheet->mergeCells('A1:A2'); // Número de Identificación
$sheet->mergeCells('B1:B2'); // Nombre del Estudiante
$sheet->mergeCells('C1:C2'); // Programa
$sheet->mergeCells('D1:F1'); // Técnico 
$sheet->mergeCells('G1:I1'); // Inglés
$sheet->mergeCells('J1:L1'); // Habilidades
$sheet->mergeCells('M1:O1'); // TOTALES

// Estilo para encabezados
$headerStyle = [
    'font' => [
        'bold' => true,
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
    ],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['rgb' => 'CCCCCC'],
    ],
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
        ],
    ],
];

// Aplicar estilos
$sheet->getStyle('A1:O2')->applyFromArray($headerStyle);

// Autoajustar columnas
foreach(range('A','O') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Crear segunda y tercera hoja (puedes personalizar según necesites)
$spreadsheet->createSheet();
$spreadsheet->getSheet(1)->setTitle('Hoja 2');
$spreadsheet->createSheet();
$spreadsheet->getSheet(2)->setTitle('Hoja 3');

// Configurar segunda hoja - Estadísticas de Bootcamps
$sheet2 = $spreadsheet->getSheet(1);
$sheet2->setTitle('Conteo Bootcamps');

// Establecer títulos
$sheet2->setCellValue('A1', 'Bootcamp');
$sheet2->setCellValue('B1', 'Inscritos');

// Consulta SQL para obtener bootcamps únicos y su conteo
$sqlBootcamps = "SELECT bootcamp_name, COUNT(*) as total 
                 FROM groups 
                 GROUP BY bootcamp_name 
                 ORDER BY bootcamp_name";
                 
$resultBootcamps = $conn->query($sqlBootcamps);

$row = 2; // Comenzar datos en fila 2
$totalInscritos = 0; // Variable para el total

while($bootcampData = $resultBootcamps->fetch_assoc()) {
    $sheet2->setCellValue('A' . $row, $bootcampData['bootcamp_name']);
    $sheet2->setCellValue('B' . $row, $bootcampData['total']);
    $totalInscritos += $bootcampData['total']; // Sumar al total
    $row++;
}

// Añadir fila de total
$sheet2->setCellValue('A' . $row, 'TOTAL INSCRITOS');
$sheet2->setCellValue('B' . $row, $totalInscritos);

// Estilo para la fila de total
$totalStyle = [
    'font' => [
        'bold' => true,
    ],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['rgb' => 'E6E6E6'],
    ],
    'borders' => [
        'top' => [
            'borderStyle' => Border::BORDER_MEDIUM,
        ],
    ],
];

// Aplicar estilos
$sheet2->getStyle('A1:B1')->applyFromArray($headerStyle);
$sheet2->getStyle('A' . $row . ':B' . $row)->applyFromArray($totalStyle);
$sheet2->getColumnDimension('A')->setAutoSize(true);
$sheet2->getColumnDimension('B')->setAutoSize(true);

// Asegurarse de que el archivo se guarde correctamente al final
try {
    $writer = new Xlsx($spreadsheet);
    $filename = 'Reporte_Horas_' . date('Y-m-d_H-i-s') . '.xlsx';
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    $writer->save('php://output');
    exit;
} catch (Exception $e) {
    echo "Error al generar el archivo: " . $e->getMessage();
}