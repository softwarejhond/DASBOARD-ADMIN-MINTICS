<?php
ob_start();

require '../../vendor/autoload.php';
require '../../controller/conexion.php';

// Consulta SQL para obtener los datos de los inscritos

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Verifica si se ha solicitado la exportación a Excel
if (isset($_GET['exportar']) && $_GET['exportar'] === 'excel') {
    // Asegúrate de que $data esté definida y sea un array
    if (!isset($data) || !is_array($data)) {
        die("No hay datos para exportar.");
    }

    // Crea un nuevo objeto Spreadsheet
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Agrega los encabezados de la tabla
    $sheet->setCellValue('A1', 'Tipo ID');
    $sheet->setCellValue('B1', 'Número');
    $sheet->setCellValue('C1', 'Nombre Completo');
    $sheet->setCellValue('D1', 'Edad');
    $sheet->setCellValue('E1', 'Correo');
    $sheet->setCellValue('F1', 'Teléfono principal');
    $sheet->setCellValue('G1', 'Teléfono secundario');
    $sheet->setCellValue('H1', 'Medio de contacto');
    $sheet->setCellValue('I1', 'Contacto de emergencia');
    $sheet->setCellValue('J1', 'Teléfono del contacto');
    $sheet->setCellValue('K1', 'Nacionalidad');
    $sheet->setCellValue('L1', 'Departamento');
    $sheet->setCellValue('M1', 'Municipio');
    $sheet->setCellValue('N1', 'Ocupación');
    $sheet->setCellValue('O1', 'Tiempo de obligaciones');
    $sheet->setCellValue('P1', 'Sede de elección');
    $sheet->setCellValue('Q1', 'Programa de interés');
    $sheet->setCellValue('R1', 'Horario');
    $sheet->setCellValue('S1', 'Dispositivo');
    $sheet->setCellValue('T1', 'Internet');
    $sheet->setCellValue('U1', 'Estado');
    $sheet->setCellValue('V1', 'Medio de contacto');
    $sheet->setCellValue('W1', 'Información de llamada');

    // Llena la hoja con los datos de la tabla
    $row = 2;
    foreach ($data as $item) {
        $sheet->setCellValue('A' . $row, $item['typeID']);
        $sheet->setCellValue('B' . $row, $item['number_id']);
        $sheet->setCellValue('C' . $row, $item['first_name'] . ' ' . $item['second_name'] . ' ' . $item['first_last'] . ' ' . $item['second_last']);
        $sheet->setCellValue('D' . $row, $item['age']);
        $sheet->setCellValue('E' . $row, $item['email']);
        $sheet->setCellValue('F' . $row, $item['first_phone']);
        $sheet->setCellValue('G' . $row, $item['second_phone']);
        $sheet->setCellValue('H' . $row, $item['contactMedium']);
        $sheet->setCellValue('I' . $row, $item['emergency_contact_name']);
        $sheet->setCellValue('J' . $row, $item['emergency_contact_number']);
        $sheet->setCellValue('K' . $row, $item['nationality']);
        $sheet->setCellValue('L' . $row, $item['departamento']);
        $sheet->setCellValue('M' . $row, $item['municipio']);
        $sheet->setCellValue('N' . $row, $item['occupation']);
        $sheet->setCellValue('O' . $row, $item['time_obligations']);
        $sheet->setCellValue('P' . $row, $item['headquarters']);
        $sheet->setCellValue('Q' . $row, $item['program']);
        $sheet->setCellValue('R' . $row, $item['schedules']);
        $sheet->setCellValue('S' . $row, $item['technologies']);
        $sheet->setCellValue('T' . $row, $item['internet']);
        $sheet->setCellValue('U' . $row, $item['status']);
        $sheet->setCellValue('V' . $row, $item['contactMedium']);
        $sheet->setCellValue('W' . $row, $item['detail']);
        $row++;
    }

    $writer = new Xlsx($spreadsheet);

    // Define el nombre del archivo
    $filename = 'reporte_inscritos_' . date('Y-m-d') . '.xlsx';

    ob_end_clean(); // Limpia el buffer antes de enviar los headers

    // Envía los encabezados para forzar la descarga
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');

    $writer->save('php://output');
    exit;
}
?>