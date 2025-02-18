<?php
require_once __DIR__ . '/../../controller/conexion.php';

header('Content-Type: application/json');

// Obtener y decodificar los datos JSON
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data || !isset($data['attendance']) || !isset($data['course_id']) || !isset($data['class_date'])) {
    echo json_encode(['success' => false, 'error' => 'Datos incompletos']);
    exit;
}

try {
    $conn->begin_transaction();

    foreach ($data['attendance'] as $recordId => $status) {
        // Validar el estado
        $validStates = ['presente', 'tarde', 'ausente', 'festivo'];
        if (!in_array($status, $validStates)) {
            throw new Exception('Estado de asistencia no válido');
        }

        $query = "UPDATE attendance_records 
                 SET attendance_status = ?
                 WHERE id = ? 
                 AND course_id = ? 
                 AND class_date = ?
                 AND (attendance_status = 'ausente' OR attendance_status = 'tarde')";
        
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            throw new Exception("Error en la preparación de la consulta: " . $conn->error);
        }

        $stmt->bind_param("ssis", $status, $recordId, $data['course_id'], $data['class_date']);
        
        if (!$stmt->execute()) {
            throw new Exception("Error al ejecutar la actualización: " . $stmt->error);
        }
    }

    $conn->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    $conn->rollback();
    error_log("Error en guardar_asistencia_individual.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}