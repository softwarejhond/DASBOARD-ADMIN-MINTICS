<?php
require_once __DIR__ . '/../../vendor/autoload.php'; // Incluye el autoload de Composer
require_once __DIR__ . '/../../controller/conexion.php'; // Incluye la conexión a la base de datos

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Verificar que se reciba una solicitud POST con el mes y año
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $month = $_POST['month'] ?? null;
    $year = $_POST['year'] ?? null;

    if (empty($month) || empty($year)) {
        echo json_encode(['error' => 'Faltan datos requeridos (mes y año)']);
        exit;
    }

    // Obtener los datos de asistencia del mes y año especificados
    $sql = "SELECT 
                ar.student_id,
                ar.course_id,
                ar.modality,
                ar.sede,
                ar.class_date,
                ar.attendance_status,
                g.full_name,
                g.institutional_email,
                g.bootcamp_name
            FROM attendance_records ar
            JOIN groups g ON ar.student_id = g.number_id AND ar.course_id = g.id_bootcamp
            WHERE MONTH(ar.class_date) = ? AND YEAR(ar.class_date) = ?
            ORDER BY ar.class_date ASC";

    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        echo json_encode(['error' => 'Error en la preparación de la consulta: ' . mysqli_error($conn)]);
        exit;
    }

    mysqli_stmt_bind_param($stmt, "ii", $month, $year);
    if (!mysqli_stmt_execute($stmt)) {
        echo json_encode(['error' => 'Error en la ejecución de la consulta: ' . mysqli_stmt_error($stmt)]);
        exit;
    }

    $result = mysqli_stmt_get_result($stmt);
    if (!$result) {
        echo json_encode(['error' => 'Error al obtener resultados: ' . mysqli_error($conn)]);
        exit;
    }

    // Crear un nuevo archivo de Excel
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Encabezados de la hoja
    $sheet->setCellValue('A1', 'ID Estudiante');
    $sheet->setCellValue('B1', 'Nombre Completo');
    $sheet->setCellValue('C1', 'Correo Institucional');
    $sheet->setCellValue('D1', 'Curso');
    $sheet->setCellValue('E1', 'Nombre del Bootcamp');
    $sheet->setCellValue('F1', 'Modalidad');
    $sheet->setCellValue('G1', 'Sede');
    $sheet->setCellValue('H1', 'Fecha');
    $sheet->setCellValue('I1', 'Estado de Asistencia');

    // Llenar la hoja con los datos
    $row = 2;
    while ($data = mysqli_fetch_assoc($result)) {
        $sheet->setCellValue('A' . $row, $data['student_id']);
        $sheet->setCellValue('B' . $row, $data['full_name']);
        $sheet->setCellValue('C' . $row, $data['institutional_email']);
        $sheet->setCellValue('D' . $row, $data['course_id']);
        $sheet->setCellValue('E' . $row, $data['bootcamp_name']); // Nombre del bootcamp
        $sheet->setCellValue('F' . $row, $data['modality']);
        $sheet->setCellValue('G' . $row, $data['sede']);
        $sheet->setCellValue('H' . $row, $data['class_date']);
        $sheet->setCellValue('I' . $row, $data['attendance_status']);
        $row++;
    }

    // Guardar el archivo Excel
    $writer = new Xlsx($spreadsheet);
    $filename = 'informe_asistencias_' . $month . '_' . $year . '.xlsx';

    // Enviar el archivo al navegador
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');

    $writer->save('php://output');
    exit;
} else {
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}