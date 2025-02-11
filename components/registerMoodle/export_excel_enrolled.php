<?php
require __DIR__ . '../../../vendor/autoload.php';
require __DIR__ . '/../../conexion.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Create new Spreadsheet object
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Set headers
$sheet->setCellValue('A1', 'Tipo ID');
$sheet->setCellValue('B1', 'Número ID');
$sheet->setCellValue('C1', 'Nombre Completo');
$sheet->setCellValue('D1', 'Email');
$sheet->setCellValue('E1', 'Email Institucional');
$sheet->setCellValue('F1', 'Contraseña');
$sheet->setCellValue('G1', 'ID Bootcamp');
$sheet->setCellValue('H1', 'Bootcamp');
$sheet->setCellValue('I1', 'ID Inglés Nivelatorio');
$sheet->setCellValue('J1', 'Inglés Nivelatorio');
$sheet->setCellValue('K1', 'ID English Code');
$sheet->setCellValue('L1', 'English Code');
$sheet->setCellValue('M1', 'ID Habilidades');
$sheet->setCellValue('N1', 'Habilidades');

// Query to get data
$query = "SELECT * FROM groups";
$stmt = $conn->query($query);
$row = 2;

while ($data = mysqli_fetch_assoc($stmt)) {
    $sheet->setCellValue('A' . $row, $data['type_id']);
    $sheet->setCellValue('B' . $row, $data['number_id']);
    $sheet->setCellValue('C' . $row, $data['full_name']);
    $sheet->setCellValue('D' . $row, $data['email']);
    $sheet->setCellValue('E' . $row, $data['institutional_email']);
    $sheet->setCellValue('F' . $row, $data['password']);
    $sheet->setCellValue('G' . $row, $data['id_bootcamp']);
    $sheet->setCellValue('H' . $row, $data['bootcamp_name']);
    $sheet->setCellValue('I' . $row, $data['id_leveling_english']);
    $sheet->setCellValue('J' . $row, $data['leveling_english_name']);
    $sheet->setCellValue('K' . $row, $data['id_english_code']);
    $sheet->setCellValue('L' . $row, $data['english_code_name']);
    $sheet->setCellValue('M' . $row, $data['id_skills']);
    $sheet->setCellValue('N' . $row, $data['skills_name']);
    $row++;
}

// Auto size columns
foreach(range('A','N') as $columnID) {
    $sheet->getColumnDimension($columnID)->setAutoSize(true);
}

// Set background color for header
$sheet->getStyle('A1:N1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF808080');
// Set border for all cells
$sheet->getStyle('A1:N' . ($row - 1))->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

// Set header for download
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="matriculados_moodle.xlsx"');
header('Cache-Control: max-age=0');

// Create Excel file
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>