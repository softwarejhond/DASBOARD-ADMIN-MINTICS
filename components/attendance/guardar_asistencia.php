<?php
require_once __DIR__ . '/../../controller/conexion.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['error' => 'Datos no válidos']);
    exit;
}

$course_id = $data['course_id'] ?? null;
$modalidad = $data['modalidad'] ?? null;
$sede = $data['sede'] ?? null;
$class_date = $data['class_date'] ?? null;
$attendance = $data['attendance'] ?? [];

if (empty($course_id) || empty($modalidad) || empty($sede) || empty($class_date) || empty($attendance)) {
    echo json_encode(['error' => 'Faltan datos requeridos']);
    exit;
}

$sql = "INSERT INTO attendance_records 
        (student_id, course_id, modality, sede, class_date, attendance_status)
        VALUES (?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE attendance_status = VALUES(attendance_status)";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    echo json_encode(['error' => 'Error al preparar la consulta: ' . mysqli_error($conn)]);
    exit;
}

$errors = [];
foreach ($attendance as $student_id => $status) {
    mysqli_stmt_bind_param($stmt, "sissss", 
        $student_id,
        $course_id,
        $modalidad,
        $sede,
        $class_date,
        $status
    );
    
    if (!mysqli_stmt_execute($stmt)) {
        $errors[] = "Error al guardar para $student_id: " . mysqli_stmt_error($stmt);
    }
}

mysqli_stmt_close($stmt);

if (!empty($errors)) {
    echo json_encode(['error' => $errors]);
    exit;
}

echo json_encode(['success' => true, 'message' => 'Asistencias guardadas correctamente']);