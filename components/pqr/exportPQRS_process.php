<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../../conexion.php';
require '../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Recoger filtros
$estado = isset($_POST['estado']) ? $_POST['estado'] : '';
$tipo = isset($_POST['tipo']) ? $_POST['tipo'] : '';

// Construir consulta SQL con filtros
$sql = "SELECT p.numero_radicado, p.tipo, p.asunto, p.descripcion, p.nombre, p.cedula, p.email, 
               p.telefono1, p.telefono2, p.fecha_creacion, p.fecha_resolucion, p.respuesta, e.nombre AS estado
        FROM pqr p
        LEFT JOIN estados e ON p.estado = e.id";

$conditions = [];

if (!empty($estado)) {
    $conditions[] = "p.estado = " . intval($estado);
}
if (!empty($tipo)) {
    $conditions[] = "p.tipo = '" . $conn->real_escape_string($tipo) . "'";
}

if (count($conditions) > 0) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}

$result = $conn->query($sql);

// Crear el archivo Excel
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Definir encabezados
$encabezados = ['Radicado', 'Tipo', 'Asunto', 'Descripción', 'Nombre', 'Cédula', 'Email', 'Teléfono 1', 'Teléfono 2', 'Fecha Creación', 'Fecha Resolución', 'Respuesta', 'Estado'];
$sheet->fromArray([$encabezados], NULL, 'A1');

// Agregar los datos
$fila = 2;
while ($row = $result->fetch_assoc()) {
    $sheet->fromArray(array_values($row), NULL, "A$fila");
    $fila++;
}

// Configurar el archivo para descarga
$nombreArchivo = 'PQRS_Export_' . date('Y-m-d_His') . '.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>
